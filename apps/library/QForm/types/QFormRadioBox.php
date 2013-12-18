<?php

class QFormRadioBox extends QFormElement {

    const TYPE = 'radiobox';

    public $checked_value = NULL;
    public $css_default_class = 'input_radiobox';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public static function setFields(&$form, $fields) {
        $f = array();
        foreach ($fields as $k => $v) {
            $f[$k] = self::getInstance($k);
            if (!is_null($v))
                $f[$k]->field_default_value = $v;
        }
        $form->setFields($f);
    }

    public static function setField(&$form, $field, $value) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

    public function getShortName() {
        return $this->getName();
    }

    protected function _createErrorContainer($error_tag = '', $css_error_class = '') {
        return '<' . $error_tag . ' class="' . $css_error_class . '" id="error_' . $this->getId() . '" for="' . $this->getName() . '" generated="true">' . $this->error_message . '</' . $error_tag . '>';
    }

    public function create($index = 0) {
        if ($this->getForm()->is_submitted)
            $checked = ($this->field_default_value[$index] == $this->getPostValue()) ? 'checked' : '';
        else
            $checked = (!is_null($this->checked_value) && $this->checked_value == $this->field_default_value[$index]) ? 'checked' : '';
        $disabled = ($this->disabled) ? 'disabled' : '';
        return '<input id="' . $this->getId($index) . '" name="' . $this->getShortName() . '" type="radio" ' . $this->getCSS() . ' value="' . $this->field_default_value[$index] . '" ' . $checked . ' ' . $disabled . ' ' . $this->getAttributes() . ' />';
    }

//    public function getInputBlock() {
//        $values = $this->getValue();
//        $t = array();
//        foreach ($values as $k => $v)
//            $t[] = $this->getForm()->d($this->field_name, $k) . ' ' . $v;
//        return $t;
//    }
//
//    public function getErrorBlock() {
//        return $this->getForm()->dE($this->field_name);
//    }

}

?>