<?php
class QFormText extends QFormElement {

    const TYPE                  =   'text';
    public $css_default_class	=   'input_text';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public static function setFields(&$form, $fields){
        $f = array();
        foreach($fields as $k=>$v) {
            $f[$k] = self::getInstance($k);
            if(!is_null($v))
                $f[$k]->field_default_value = $v;
        }
        $form->setFields($f);
    }

    public static function setField(&$form, $field, $value = NULL){
        self::setFields($form, array($field  =>  $value));
        return $form->form_elements[$field];
    }

    public function create() {
        return "<input id=\"{$this->getId()}\" name='{$this->getShortName()}' type='text' {$this->getCSS()} value=\"{$this->getHtmlValue()}\" {$this->getMaxLength()} {$this->getAttributes()} {$this->getPlaceholder()}" . ($this->disabled ? ' disabled="disabled" ' : '') . ' />';
    }

}
?>