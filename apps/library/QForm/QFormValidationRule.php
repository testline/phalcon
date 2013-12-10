<?php

abstract class QFormValidationRule {

    protected $form = NULL;
    protected $form_element = NULL;
    protected static $instance = NULL;

    public function __construct($form, $form_element) {
        $this->form = $form;
        $this->form_element = $form_element;
    }

    public static function getInstance($form, $form_element, $className) {
        if (!self::$instance instanceof $className)
            self::$instance = new $className($form, $form_element);
        else {
            self::$instance->form = $form;
            self::$instance->form_element = $form_element;
        }
        return self::$instance;
    }

    abstract function validate($value = '', $params = NULL);

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, 'true', getFormError($dom_element_id, $rule));
    }

    protected function makeJSRule($rule, $ruleCond = 'true', $message = 'error') {
        $_rule = '"' . $rule . '":' . $ruleCond;
        $_message = '"' . $rule . '":"' . addslashes($message) . '"';
        return array('rule' => &$_rule, 'message' => &$_message);
    }

    protected function getForm() {
        return QForm::getInstance($this->form);
    }

}

class QRequiredRule extends QFormValidationRule {
    public function validate($value = '', $params = NULL) {
        if ($value == NULL)
            return false;
        elseif (empty($value) && !($value == 0))
            return false;
        return true;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        if ($params == 'false')
            return $this->makeJSRule($rule, 'false', getFormError($dom_element_id, $rule));
        else return $this->makeJSRule($rule, 'true', getFormError($dom_element_id, $rule));
    }

}

class QMaxLengthRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return mb_strlen($value) <= (int) $params;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, (int) $params, sprintf(getFormError($dom_element_id, $rule), $params));
    }

}

class QMinLengthRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        if (in_array($this->form_element->field_type, array('checkbox', 'radiobox'))) // CheckBox, RadioBox post arrays
            return sizeof($value) >= (int) $params || sizeof($value) == 0;
        return mb_strlen($value) >= (int) $params || mb_strlen($value) == 0;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, (int) $params, sprintf(getFormError($dom_element_id, $rule), $params));
    }

}

class QValidEmailRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return preg_match('/^[0-9a-z_\.-]+@[0-9a-z_^\.-]+\.[a-z]{2,6}$/i', $value) || mb_strlen($value) == 0;
    }

}

class QValidNicknameRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return preg_match('/^[0-9a-z_-]+$/i', $value);
    }

}

class QValidShortNameRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return sizeof($value) == 0 || preg_match('/^[0-9a-z_]+$/i', $value);
    }

}

class QUrlShortNameRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return strlen($value) == 0 || preg_match('/^[0-9a-z_A-Z\-]+$/i', $value);
    }

}

class QValidLatinRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return preg_match('/^[a-z0-9!@%#$\^&*()\[\]+_=<>?.\/\\{}~"\'’`:;,| \r\n\t~¢£¤¥¦§¨©«¬®¯°±²³´·¸¹º»ǀǁǂ\‒\–\—\―‖‗‘’‚‛“”„‟†‡•…′″‴‹›₤€₵℅ℓ№℗™■□▪▫◊○◌●◦-]+$/i', $value);
    }

}

class QValidPasswordRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return preg_match('/^[a-z0-9_\$\@\*\%\^\?\(\)+=-]+$/i', $value);
    }

}

class QEqualToRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return $value == $this->getForm()->getFieldValue($params);
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, '"#' . $this->getForm()->getFieldByName($params)->getId() . '"', getFormError($dom_element_id, $rule));
    }

}

class QCheckDateRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        $params = explode(',', $params);
        $day = $this->getForm()->getFieldValue($params[0]);
        $month = $this->getForm()->getFieldValue($params[1]);
        if (is_null($value) && is_null($day) && is_null($month))
            return true;
        $time = mktime(0, 0, 0, $month, $day, $value);
        return $value . sprintf('%02d', $month) . sprintf('%02d', $day) == date('Ymd', $time);
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        $params = explode(',', $params);
        return $this->makeJSRule($rule, '"' . $this->getForm()->getFieldByName($params[0])->getId() . ',' . $this->getForm()->getFieldByName($params[1])->getId() . '"', getFormError($dom_element_id, $rule));
    }

}

class QAcceptRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        if (!isset($value['name']))
            return true;
        return in_array(array_pop(explode('.', $value['name'])), explode(',', strtolower($params)));
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, '"' . $params . '"', getFormError($dom_element_id, $rule));
    }

}

class QRemoteRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return true;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, '"' . $params . '"', getFormError($dom_element_id, $rule));
    }

}

class QSecretkeyRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        $result = ($_SESSION['secretkey_' . $params] == $value) ? true : false;
        $_SESSION['secretkey_' . $params] = rand(1000, 99999);
        return $result;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, '"' . $params . '"', getFormError($dom_element_id, $rule));
    }

}

class QTwoDigitsAndTwoLettersRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        if ($value == '')
            return true;
        if (!preg_match('/.*[0-9].*[0-9].*/', $value))
            return false;
        if (!preg_match('/.*[a-zA-Z].*[a-zA-Z].*/', $value))
            return false;
        return true;
    }

}

class QNotEqualToRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return $value != $this->getForm()->getFieldValue($params);
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, '"#' . $this->getForm()->getFieldByName($params)->getId() . '"', getFormError($dom_element_id, $rule));
    }

}


// Numbers
class QNumberRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return $value == (int) $value;
    }

}

class QFloatRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        return $value == (float) $value;
    }

}

class QMinRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        if ($value == '')
            return true;
        return $value >= $params;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, $params, sprintf(getFormError($dom_element_id, $rule), $params));
    }
    // Maybe make наследование getJavaScriptRule для чисел
}

class QMaxRule extends QFormValidationRule {

    public function validate($value = '', $params = NULL) {
        if ($value == '')
            return true;
        return $value <= $params;
    }

    public function getJavaScriptRule($rule, $dom_element_id, $params = NULL) {
        return $this->makeJSRule($rule, $params, sprintf(getFormError($dom_element_id, $rule), $params));
    }

}


?>