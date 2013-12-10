<?php

class QFormHidden extends QFormElement {

    const TYPE = 'hidden';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public static function setFields(&$form, $fields) {
        $f = array();
        foreach ($fields as $k => $v) {
            $f[$k] = QFormHidden::getInstance($k);
            $f[$k]->field_default_value = $v;
            $form->hidden_fields[] = $f[$k];
        }
        $form->setFields($f);
    }

    public static function setField(&$form, $field, $value = NULL) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

    public function create() {
        return '<input id="' . $this->getId() . '" name="' . $this->getShortName() . '" type="hidden"  value="' . $this->getHtmlValue() . '"' . ' ' . $this->getAttributes() . ' />';
    }

}

?>