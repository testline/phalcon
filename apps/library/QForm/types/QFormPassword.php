<?php

class QFormPassword extends QFormText {

    const TYPE = 'password';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public function create() {
        return '<input id="' . $this->getId() . '" name="' . $this->getShortName() . '" type="password" ' . $this->getCSS() . ' value="' . $this->getHtmlValue() . '" ' . $this->getMaxLength() . ' ' . $this->getAttributes() . $this->getPlaceholder() . '/>';
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

    public static function setField(&$form, $field, $value = NULL) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

}

?>