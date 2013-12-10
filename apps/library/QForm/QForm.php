<?php
class QForm {

    public $error_messages = array();
    public $form_elements = array();
    public $form_id = '';
    public $multipart = false;
    public $action = '';
    public $form_attributes = array();
    public $css_form_class = 'form-horizontal';
    public $css_error_class = 'error';//help-inline
    public $css_valid_class = 'valid';
    public $css_ignore_class = 'ignore';
    public $dom_error_tag = 'span';
    public $is_submitted = false;
    public $enable_js_validation = true;
    public $hidden_fields = array();
    public $errorContainer = NULL;
    public $highlight_handler = NULL;
    public $unhighlight_handler = NULL;
    public $placeholders = array();
    public $on_submit = true;
    public $onfocusout = true;
    public $onkeyup = false;
    public $ajax_submit = true;
    public $hasSuccessfulSubmitHandler = false; // use or not to use   'successfulSubmit' . $this->form_id . 'Handler  js function on client side
    public $successfulSubmitAction = false; // js code executed on client after successful submit
    public $ajaxTimeout = 30000;
    public $ajaxError = false;
    public $ajaxSubmitData = array(); // data sent to server on ajax submit
    protected static $instance = NULL;

    public function __construct($id, $action = '') {
        $this->form_id = $id;
        $this->action = $action;
        if (1 || defined('CONTROL')) // For backend only
            $this->ajaxError = "function(req, error){
                _submitted{$this->form_id}Form=false;
                if (error === 'error') error = req.statusText;
                messageBoard('There was a communication error on submitting form: ' + error, 'error')
            }";
        if (isset($_POST['form_' . $id . '_submitted']) && $_POST['form_' . $id . '_submitted'] == 1)
            $this->is_submitted = true;
        self::$instance[$id] = $this;
    }

    public static function getInstance($id = '', $action = '') {
        if (!self::$instance[$id] instanceof self)
            self::$instance[$id] = new self($id, $action);
        return self::$instance[$id];
    }

    public function _setAttribute($name, $value) {
        $this->form_attributes[$name] = $value;
    }

    public function setHighlightHandlers($highlight_handler = NULL, $unhighlight_handler = NULL) { //?
        $this->highlight_handler = $highlight_handler;
        $this->unhighlight_handler = $unhighlight_handler;
    }

    public function validate() {
        $errors = array();
        foreach ($this->form_elements as $k => &$v) {
            if (is_array($v)) {
                foreach ($v as $k1 => &$v1) {
                    $err = $v1->validate();
                    if (!empty($err))
                        $errors[] = $err;
                }
            } else {
                $err = $v->validate();
                if (!empty($err))
                    $errors[] = $err;
            }
        }
        $this->error_messages = &$errors;
        return count($errors) == 0;
    }

//    public function displayAjaxErrors() {
//        header('Content-type: text/xml');
//        echo '<xml>'; //! was 'errors'
//        foreach ($this->error_messages as $k => &$v)
//            echo '<e><c>' . $v['container'] . '</c><m><![CDATA[' . $v['message'] . ']]></m></e>';
//        if ($this->successfulSubmitAction)
//            echo "<action>$this->successfulSubmitAction</action>";
//        echo '</xml>';
//        exit();
//    }

    public function setError($element, $message) {
        $form_element = $this->getFieldByName($element);
        $form_element->error_message = $message;
        //Debug::fb($form_element);
                                                // fix for triggering error for multiple checkboxes
        $container = $form_element->getId() .   ($form_element->field_type == 'checkbox' && is_array($form_element->field_default_value) ? '[]' : '');
        $this->error_messages[] = array('container' => $container, 'message' => $message);
    }

    public function start() {
        QFormHidden::setField($this, 'form_' . $this->form_id . '_submitted', 1);
        $css_class = strlen($this->css_form_class) ? 'class="' . $this->css_form_class . '"' : '';
        $hiddenFieldsHtml = '';
        foreach ($this->hidden_fields as $k => &$v)
            $hiddenFieldsHtml .= $v->create();
        //($this->ajax_submit ? 'onsubmit="return false"' : '')
        return '<form id="' . $this->form_id . '" action="' . $this->action . '" method="post" ' . ($this->multipart ? 'enctype="multipart/form-data"' : '') . ' ' . $css_class . '>' . $hiddenFieldsHtml;
    }

    public function end() {
        return '</form>' . ($this->enable_js_validation ? $this->generateJSValidation() : '');
    }

    public function setFields(&$fields = array()) {
        foreach ($fields as $k => &$v) {
            $v->form_id = $this->form_id;
            $this->form_elements[$v->getName()] = $v;
        }
    }

    public function getFieldValue($form_element = NULL) {
        return $this->getFieldByName($form_element)->getPostValue();
    }

    public function getPostValues($get_form_elements = array()) {
        $values = array();
        $count_get_form_elements = count($get_form_elements);
        foreach ($this->form_elements as $k => $v) {
            if (!isset($_POST[$k]) && !isset($_FILES[$k]))
                continue;
            if ($count_get_form_elements > 0 && !in_array($k, $get_form_elements))
                continue;
            if (is_array($v))
                $values[$k] = $this->getFieldValue($k);
            else
                $values[$k] = $this->getFieldValue($k);
        }
        return $values;
    }

    public function getFieldByName($shortname) { // consider removing
        if (isset($this->form_elements[$shortname]))
            return $this->form_elements[$shortname];
        return false;
    }

    public function getFieldId($shortname) {
        if (isset($this->form_elements[$shortname]))
            return $this->form_elements[$shortname]->getId();
        return false;
    }

    public function d($field_name = '', $field_index = false) {
        return preg_replace('/ +/', ' ', $this->getFieldByName($field_name)->create($field_index));
    }

    public function dE($field_name = '', $field_index = false) {
        return $this->getFieldByName($field_name)->createErrorContainer();
    }

    public function generateJSValidation() {
        $js = array();
        $rules = array();
        $messages = array();
        foreach ($this->form_elements as $k => &$v) {
            if (is_array($v)) {
                foreach ($v as $k1 => &$v1) {
                    $res = $v1->generateJSValidation();
                    if (count($res['rules']) && count($res['messages'])) {
                        $rules[$v1->getShortName()] = '"' . $v1->getShortName() . '"' . ':{' . implode(',', $res['rules']) . '}';
                        $messages[$v1->getShortName()] = '"' . $v1->getShortName() . '"' . ':{' . implode(',', $res['messages']) . '}';
                    }
                }
            } else {
                $res = $v->generateJSValidation();
                $sh_name = str_replace('-{0}', '', $v->getShortName());
                if (count($res['rules']) && count($res['messages'])) {
                    $rules[$v->getShortName()] = '"' . $sh_name . '"' . ':{' . implode(',', $res['rules']) . '}';
                    $messages[$v->getShortName()] = '"' . $sh_name . '"' . ':{' . implode(',', $res['messages']) . '}';
                }
            }
        }
        //,

        /*        highlight: function(element, errorClass) {
          setTimeout('chName("'+element.name+'")',100);
          //chName(element.name);
          },
          unhighlight: function(element, errorClass) {
          //setTimeout('uchName("'+element.name+'")',1);
         */

        // For old version of Default Value 1.2 plugin (non HTML5 placeholders). Set QFormElement::getPlaceholder() to return '';
//        $placeholders = '';
//        if (sizeof($this->placeholders))
//            foreach ($this->placeholders as $field_shortname => $val)
//                $placeholders.='$("#' . $this->getFieldId($field_shortname) . '").defaultValue("' . addslashes($val) . '");';
        // For new version of Default Value 1.4.2 plugin (HTML5 placeholders)
        if (sizeof($this->placeholders)) {
            $placeholders = array();
            foreach ($this->placeholders as $field => $placeholder)
                $placeholders[] = "#{$this->form_id}_$field";
            $placeholders = "$('" . implode(',', $placeholders) . "').defaultValue();";
        }


        $on_submit = $this->on_submit ? 'onsubmit: true,' : 'onsubmit: false,';
        $highlight_handler = (is_null($this->highlight_handler)) ? '' : 'highlight: function(element, errorClass) {setTimeout("' . $this->highlight_handler . '(\'"+element.name+"\',\'"+element.id+"\',\'' . $this->form_id . '\')",50);},';
        $unhighlight_handler = (is_null($this->unhighlight_handler)) ? '' : 'unhighlight: function(element, errorClass) {' . $this->unhighlight_handler . '(element.name,element.id,"' . $this->form_id . '");},';
        $error_container = (!is_null($this->errorContainer)) ? 'errorContainer:"' . $this->errorContainer . '",errorLabelContainer: "' . array_shift(explode(',', $this->errorContainer)) . '",' : '';
        $js_rules = 'rules:{' . implode(',', $rules) . '},messages:{' . implode(',', $messages) . '}';
        if (sizeof($this->ajaxSubmitData)) {
            foreach ($this->ajaxSubmitData as $k => $v)
                $ajaxSubmitDataString[] = "$k: $v";
            $ajaxSubmitDataString = ',' . implode(',', $ajaxSubmitDataString);
        }
        $ajax_submit = ($this->ajax_submit) ? (',submitHandler: function(form) {var f=$(form);var options = {' . ($this->ajaxTimeout ? "timeout: $this->ajaxTimeout," : '') . ($this->ajaxError ? "error: $this->ajaxError," : '') . ' dataType: "xml",
            data : {
                "submittedViaAjax" : 1' .
                $ajaxSubmitDataString .
            '},
            beforeSubmit: function(formData, jqForm, options){
                if(_submitted' . $this->form_id . 'Form)return false;
                            var form_id=jqForm.get(0).id;
                            for(var k in formData){
                                    var obj=formData[k];
                                    if(typeof(tinyMCE)!="undefined" && tinyMCE.get(form_id+"_"+obj.name)){
                                            formData[k].value=tinyMCE.get(form_id+"_"+obj.name).getContent();
                                            //tinyMCE.execCommand("mceRemoveControl", false, form_id+"_"+obj.name);
                                    }
                            }
                 if(typeof(beforeSubmit' . $this->form_id . 'Handler)=="function") {
                     var r = beforeSubmit' . $this->form_id . 'Handler(formData, jqForm, options);

                     if(!r)return false;
                 }
                f.trigger("before_submit");
                _submitted' . $this->form_id . 'Form=true;
                timeout' . $this->form_id . ' = setTimeout("$(\'#loadingOverlay\').show()", 100);
                //$("#' . $this->form_id . '_loader").show();
                //$(".' . $this->form_id . '_loader").show();
            },
        success:  function(responseText, statusText, xhr, $form){
                processXml' . $this->form_id . 'Response(responseText);
               _submitted' . $this->form_id . 'Form=false;
               //$("#' . $this->form_id . '_loader").hide();
               //$(".' . $this->form_id . '_loader").hide();
               clearTimeout(timeout' . $this->form_id . ');
               $("#loadingOverlay").hide();
            }

        };f.ajaxSubmit(options);}') : ',submitHandler: function(form) {form.submit();}';

        //$processXmlfunction = ($this->ajax_submit) ? ('function processXml' . $this->form_id . 'Response(responseXML){processXml(responseXML,"' . $this->form_id . '", ' . 'successfulSubmit' . $this->form_id . 'Handler' . ')}') : '';
        $processXmlfunction = ($this->ajax_submit) ? ('function processXml' . $this->form_id . 'Response(responseXML){processXml(responseXML,"' . $this->form_id . '"' . ($this->hasSuccessfulSubmitHandler ? ",successfulSubmit{$this->form_id}Handler" : '') . ')}') : '';
        Debug::fb($processXmlfunction, '$processXmlfunction');
        return '<script type="text/javascript">$().ready(function() {' . $placeholders . '$("#' . $this->form_id . '").validate({' . $on_submit . $error_container . $highlight_handler . $unhighlight_handler . 'errorClass: "' . $this->css_error_class . '",validClass: "' . $this->css_valid_class . '",ignore: ".' . $this->css_ignore_class . '",errorElement: "' . $this->dom_error_tag . '",' . ($this->onfocusout ? '' : 'onfocusout:false,') . ($this->onkeyup ? '' : 'onkeyup: false,') . 'onclick: false,invalidHandler: function(form, validator) {$("#' . $this->form_id . '_loader").hide();$(".' . $this->form_id . '_loader").hide();},' . $js_rules . $ajax_submit . '});' . $processXmlfunction . '});var _submitted' . $this->form_id . 'Form=false;</script>';
    }

    public function setFieldsCSS($fields, $css = '') {
        foreach ($fields as $k => $v)
            $this->getFieldByName($v)->css_default_class = $css;
    }

    public function setFieldsRules($fields) {
        foreach ($fields as $k => $v)
            if (is_array($this->form_elements[$k]))
                $this->getFieldByName($k, 0)->setRules($v);
            else $this->getFieldByName($k)->setRules($v);
    }

    function setDisabledFields($fields = array()) { //Doesnt work on all fields
        foreach ($fields as $k => $v) {
            if (is_array($this->form_elements[$v])) {
                foreach ($this->form_elements[$v] as $k1 => $v1)
                    $this->form_elements[$v][$k1]->setDisabled();
            } else $this->form_elements[$v]->setDisabled();
        }
    }

    public function sendAjaxResponse() {  // sends errors or successfulSubmitAction    //! was 'checkAjaxSubmit'
        if ($this->is_submitted && $this->ajax_submit && isset($_POST['submittedViaAjax'])) {
            //$this->displayAjaxErrors();
            header('Content-type: text/xml');
            echo '<xml>'; //! was 'errors'
            foreach ($this->error_messages as $k => &$v)
                echo '<e><c>' . $v['container'] . '</c><m><![CDATA[' . $v['message'] . ']]></m></e>';
            if ($this->successfulSubmitAction)
                echo "<action>$this->successfulSubmitAction</action>";
            echo '</xml>';
            exit();
        }
    }

    function setPlaceholders($placeholders) {
        foreach ($placeholders as $field => $placeholder)
            $this->form_elements[$field]->placeholder = $placeholder;
        $this->placeholders = array_merge($this->placeholders, $placeholders);
    }

}
//require_once ("QLang.php");
require_once ("QFormElement.php");
require_once ("QFormValidationRule.php");
require_once ("types/QFormText.php");
require_once ("types/QFormPassword.php");
require_once ("types/QFormTextArea.php");
require_once ("types/QFormHidden.php");
require_once ("types/QFormRadioBox.php");
require_once ("types/QFormCheckBox.php");
require_once ("types/QFormSelectBox.php");
require_once ("types/QFormFile.php");
//require_once ("CProtect.php");
//require_once ("CClearText.php");

function getFormError($element = '', $rule = '') {
    if (defined('CONTROL')) {
        global $CONTROLTEXTS;
        $TEXTS = &$CONTROLTEXTS;
    } else global $TEXTS;
    $arr = explode('_', $element);
    array_shift($arr);
    $element = implode('_', $arr);
    if (preg_match('/(.+)-([0-9]+)$/iU', $element, $matches) && isset($TEXTS['errors.' . $matches[1] . '-{0}.' . $rule]))
        return str_replace('{0}', $matches[2], $TEXTS['errors.' . $matches[1] . '-{0}.' . $rule]);
    elseif (isset($TEXTS['errors.' . $element . '.' . $rule]))
        return $TEXTS['errors.' . $element . '.' . $rule];
    elseif (isset($TEXTS['errors.' . $rule]))
        return $TEXTS['errors.' . $rule];
    return 'Unspecified error';
}
?>