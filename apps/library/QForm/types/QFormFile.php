<?php

class QFormFile extends QFormElement {

    const TYPE = 'file';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public static function setFields(&$form, $fields) {
        $f = array();
        foreach ($fields as $k => $v)
            $f[$k] = self::getInstance($k);
        $form->multipart = true;
        $form->setFields($f);
    }

    public static function setField(&$form, $field, $value = NULL) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

    public function getPostValue() {
        return (isset($_FILES[$this->getName()]) && $_FILES[$this->getName()]['size'] && $_FILES[$this->getName()]['tmp_name']) ? $_FILES[$this->getName()] : NULL;
    }

    public function create() {
        return '<input id="' . $this->getId() . '" name="' . $this->getShortName() . '" type="file" ' . $this->getCSS() . ' ' . $this->getAttributes() . '/>';
    }

}

?>