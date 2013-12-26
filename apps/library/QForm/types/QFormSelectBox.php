<?php

class QFormSelectBox extends QFormElement {

    const TYPE = 'selectbox';

    public $null_title = NULL;
    public $selected_value = NULL;
    public $css_default_class = 'form-control';//'input_select';

    public static function getInstance($field_name = '') {
        return new self(self::TYPE, $field_name);
    }

    public static function setFields(&$form, $fields) {
        $f = array();
        foreach ($fields as $k => $v) {
            $f[$k] = self::getInstance($k);
            $f[$k]->setValues($v[0], $v[1]);
        }
        $form->setFields($f);
    }

    public static function setField(&$form, $field, $value = NULL) {
        self::setFields($form, array($field => $value));
        return $form->form_elements[$field];
    }

    public function setValues($values, $null_title = NULL) {
        $this->values = $values;
        $this->null_title = $null_title;
    }

    public function create() {
        if ($this->getForm()->is_submitted)
            $val = $this->getPostValue();
        else $val = $this->selected_value;
        $disabled = ($this->disabled) ? 'disabled' : '';
        $select = '<select id="' . $this->getId() . '" name="' . $this->getShortName() . '"  ' . $this->getCSS() . ' ' . $this->getAttributes() . " $disabled>";
        if (!is_null($this->null_title))
            $select .= '<option value="">' . $this->null_title . '</option>';
        foreach ($this->values as $k => $v) {
            $selected = (!is_null($val) && $val == $k) ? 'selected' : '';
            $select .= '<option ' . $selected . ' value="' . $k . '">' . $v . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

}

?>