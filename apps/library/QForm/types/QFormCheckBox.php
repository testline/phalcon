<?php

//redo checked mechanism
class QFormCheckBox extends QFormElement {

    const TYPE = 'checkbox';

    public $checked_value = NULL;
    public $css_default_class = 'input_checkbox';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public function getShortName() {
        if (is_array($this->field_default_value))
            return $this->getName() . '[]';
        return $this->getName();
    }

    public function getDefaultValue($index = NULL) {
        if (is_array($this->field_default_value) && !(is_null($index) || $index === false))
            return $this->field_default_value[$index];
        return $this->field_default_value;
    }

    public function getPostValue() {
        return isset($_POST[$this->getName()]) ? ($_POST[$this->getName()]) : NULL;
    }

    protected function _createErrorContainer($error_tag = '', $css_error_class = '') {
        return '<' . $error_tag . ' class="' . $css_error_class . '" id="error_' . $this->getId() . '" for="' . $this->getShortName() . '" generated="true">' . $this->error_message . '</' . $error_tag . '>';
    }

    public function create($index = NULL) {
        if ($this->getForm()->is_submitted) {
            if (is_array($this->field_default_value)) {
                $checkBoxPostValue = $this->getPostValue();
                $checked = is_array($checkBoxPostValue) && (in_array($this->getDefaultValue($index), $checkBoxPostValue)) ? 'checked' : '';
            } else $checked = ($this->field_default_value == $this->getPostValue()) ? 'checked' : '';
        } else {
            if (is_array($this->checked_value))
                $checked = (in_array($this->getDefaultValue($index), $this->checked_value)) ? 'checked' : '';
            elseif (!is_null($this->checked_value))
                $checked = ($this->field_default_value == $this->checked_value) ? 'checked' : '';
        }
        $disabled = ($this->disabled) ? 'disabled' : '';
        return '<input id="' . $this->getId($index) . '" name="' . $this->getShortName() . '" type="checkbox" ' . $this->getCSS() . ' value="' . $this->getDefaultValue($index) . '" ' . $checked . ' ' . $disabled . ' ' . $this->getAttributes() . ' />';
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

    public static function checkedValues(&$form, $fields) {//!FINISH IT
        $f = array();
        foreach ($fields as $k => $v) {
            //$form->getFieldByName('checkboxSingle')->checked_value
            $f[$k] = self::getInstance($k);
            if (!is_null($v))
                $f[$k]->field_default_value = $v;
        }
        $form->setFields($f);
        return $form;
    }

    public static function setField(&$form, $field, $value = NULL) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

}

?>