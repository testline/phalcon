<?php

abstract class QFormElement {

    public $error_message = '';
    public $field_type = 'text';
    public $validation_rules = array();
    public $values = array();
    public $attributes = array();
    public $field_index = false;
    public $field_name = '';
    public $field_maxlength = 512;
    public $field_default_value = ''; // bad naming
    public $css_default_class = '';
    public $disabled = false; // if true, sent value is processed like all others
    public $form_id = NULL;
    public $placeholder = '';
    public $skipPHPValidation = false;

    abstract public function create();

    public function __construct($field_type = 'text', $field_name = '') {
        $this->field_type = $field_type;
        $this->field_name = $field_name;
    }

    final public function getForm() {
        return QForm::getInstance($this->form_id);
    }

    final public function setRules($rules) {
        $rules = explode('|', $rules);
        foreach ($rules as $k => $v) {
            if (!mb_strlen($v))
                continue;
            if (preg_match('/^([a-z0-9_]+)\[([a-z0-9_,\.:=&\?\/{}-]+)\]/i', $v, $matches))
                $this->validation_rules[$matches[1]] = $matches[2];
            else $this->validation_rules[$v] = '';
        }
    }

    final public function getCSS() {
        if (!empty($this->css_default_class))
            $class = $this->css_default_class;
        if (!empty($this->error_message))
            $class .= ' ' . $this->getForm()->css_error_class;
        if (strlen($class))
            return "class='$class'";
        return '';
    }

    public function getMaxLength() {//?
        if ($this->field_maxlength === false)
            return '';
        return 'maxlength="' . $this->field_maxlength . '"';
    }

    public function getName() {
        return $this->field_name;
    }

    public function getShortName() {
        return $this->getName();
    }

    public function getId($field_index = NULL) {
        if (is_null($field_index))
            return $this->form_id . '_' . $this->getName();
        return $this->form_id . '_' . $this->getName() . '_' . $field_index;
    }

    public function getValue($field_index = NULL) {
        if ($this->getForm()->is_submitted && is_null($field_index))
            return $this->getPostValue();
        else {
            if (is_array($this->field_default_value) && !is_null($field_index))
                return $this->field_default_value[$field_index];
            return $this->field_default_value;
        }
    }

    public function getHtmlValue() {
        return htmlspecialchars($this->getValue());
    }

    public function getPostValue() {
        $value = isset($_POST[$this->getName()]) ? stripslashes($_POST[$this->getName()]) : NULL;
        if (!is_array($value))
            return CProtect::string($value);
        return $value;
    }

    public function createErrorContainer() {
        $form = $this->getForm();
        return $this->_createErrorContainer($form->dom_error_tag, $form->css_error_class);
    }

    protected function _createErrorContainer($error_tag = '', $css_error_class = '') {
        return '<' . $error_tag . ' class="' . $css_error_class . '" id="error_' . $this->getId() . '" for="' . $this->getId() . '" generated="true">' . $this->error_message . '</' . $error_tag . '>';
    }

    final public function validate() {
        if ($this->skipPHPValidation)
            return array();
        $value = $this->getPostValue();
        foreach ($this->validation_rules as $rule => $rule_params) {
            $ruleObj = call_user_func_array(array('Q' . $rule . 'Rule', 'getInstance'), array($this->form_id, &$this, 'Q' . $rule . 'Rule'));
            if (!$ruleObj->validate($value, $rule_params)) {
                $error = $rule;
                break;
            }
        }
        if (!isset($error))
            return array();
        if (in_array($error, array('maxlength', 'minlength')))
            $this->error_message = sprintf(getFormError($this->getId(), $error), $rule_params);
        else $this->error_message = getFormError($this->getId(), $error);
        return array('container' => $this->getId(), 'message' => $this->error_message);
    }

    protected final function getAttributes() {
        $attr = array();
        foreach ($this->attributes as $k => $v)
            $attr[] = $k . '="' . htmlspecialchars($v) . '"';
        return implode(' ', $attr);
    }

    final public function generateJSValidation() {
        $rules = array();
        $messages = array();
        foreach ($this->validation_rules as $rule => $rule_params) {
            $r = 'Q' . $rule . 'Rule';
            $ruleObj = call_user_func_array(array($r, 'getInstance'), array($this->form_id, &$this, $r));
            $jsRule = $ruleObj->getJavaScriptRule($rule, $this->getId(), $rule_params);
            $rules[] = $jsRule['rule'];
            $messages[] = $jsRule['message'];
        }
        return array('rules' => $rules, 'messages' => $messages);
    }

    public static function setFieldRules(&$form, $field, $rules) {
        $form->setFieldsRules(array($field => $rules));
        return $form;
    }

    public function getPlaceholder() {
        return $this->placeholder ? "placeholder='{$this->placeholder}'" : '';
    }

}

?>