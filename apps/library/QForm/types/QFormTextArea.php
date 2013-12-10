<?php

class QFormTextArea extends QFormText {

    const TYPE = 'textarea';

    public $css_default_class = 'input_textarea';
    public $field_maxlength = 65000;

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public static function setFields($form, $fields) {
        $f = array();
        foreach ($fields as $k => $v) {
            $f[$k] = self::getInstance($k);
            if (!is_null($v))
                $f[$k]->field_default_value = $v;
        }
        $form->setFields($f);
    }

    public static function setField(&$form, $field, $value = NULL) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

    public function getPostValue() {
        $value = isset($_POST[$this->getName()]) ? stripslashes($_POST[$this->getName()]) : NULL;
        if (!is_array($value))
            return CProtect::tiny_html($value, $this->field_maxlength);
        return $value;
    }

    public function create() {
        $disabled = ($this->disabled) ? 'disabled' : '';
        return '<textarea id="' . $this->getId() . '" name="' . $this->getShortName() . '" ' . $this->getCSS() . ' ' . ' ' . $this->getAttributes() . " $disabled {$this->getPlaceholder()}>" . $this->getHtmlValue() . "</textarea>";
    }

}

?>