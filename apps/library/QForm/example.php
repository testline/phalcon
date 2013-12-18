<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/lib/lib.php";
//$timeLog->log('Form Example start');

require_once $_SERVER['DOCUMENT_ROOT'] . "/lib/f/QForm.php";


$timeLog->log('libs loaded');

//dump($_POST);
//dump($_FILES);

$form = new QForm('exampleForm', '/lib/f/index.php');

// Options
$form->css_form_class = 'form';
$form->css_error_class = 'customErrorClass';
$form->css_valid_class = 'customValidClass';
$form->css_ignore_class = 'customIgnoreClass';
$form->dom_error_tag = 'label';
//$form->multipart = true;
//$form->ajax_submit = false; // Uncomment for form submission not via ajax
//$form->setHighlightHandlers('formHighlight', 'formUnHighlight');
//$form->enable_js_validation = false;
//$form->onfocusout = false; // Disable validation on focus out
//$form->onkeyup = false; // Disable validation on key up

// Fields' declaration
QFormText::setField($form, 'name', 'ULL');
QFormText::setFields($form, array('textField1' => 'Value 1', 'textField2' => 'Value 2', 'textField3' => null));
QFormText::setField($form, 'email', NULL);

QFormCheckBox::setField($form, 'checkboxSingle', 'checkBoxValue');
$form->getFieldByName('checkboxSingle')->checked_value = array('checkBoxValue');
QFormCheckBox::setField($form, 'checkboxMultiple', array(10, 11, 12));
$form->getFieldByName('checkboxMultiple')->checked_value = array(12, 10);

QFormRadioBox::setField($form, 'radioBox', array(10, 11));
$form->getFieldByName('radioBox')->checked_value = 11;

QFormSelectBox::setField($form, 'select', array(array(10 => 'Ten', 20 => 'Twenty', 30 => 'Thirty'), 'Choose numbers:')); //Title is optional
$form->getFieldByName('select')->selected_value = 30;

QFormHidden::setField($form, 'hidden', 'secret');
QFormHidden::setFields($form, array('hidden2' => 'secret2', 'hidden3' => 'secret3'));

QFormPassword::setField($form, 'password');
QFormPassword::setField($form, 'cpassword');

QFormTextArea::setField($form, 'textArea', NULL);

QFormFile::setField($form, 'file');

QFormText::setField($form, 'secretkey');


// Fields' rules
$form->setFieldsRules(array(
    'name' => 'required|maxlength[7]',
    'textField1' => 'minlength[4]',
    'email' => 'validemail|maxlength[100]',
    'checkboxSingle' => 'required',
    'checkboxMultiple' => 'required|minlength[2]',
    'radioBox' => 'required',
    'select' => 'required',
//    'password' => 'required|minlength[6]|maxlength[16]|twodigitsandtwoletters',
//    'cpassword' => 'required|equalTo[password]',
//    'textArea' => 'required',
//    'file' => 'required',
//    'secretkey' => 'required|maxlength[6]|secretkey[' . $form->form_id . ']',
));

// Placeholders
$form->setPlaceholders(array(
    'textField3' => 'Default value',
    'email' => 'Enter your email',
    'cpassword' => 'Enter password',
));

// Fields' css
$form->setFieldsCSS(array('secretkey'), 'customClass');

//dump($form->getFieldByName('name'));
//dump($form->form_elements['name'], 1, 10);
//edump($form);
//$form->setError('name', 'Custom error 0');
if ($form->is_submitted) {
    if ($form->validate()) {
        //dump($form->error_messages);
        $vals = $form->getPostValues();
        //$form->setError('name', 'Custom error');
        if ($form->ajax_submit)
            Debug::fb($vals);
        else {
            Debug::dump($vals);
            echo "<br>" . htmlspecialchars($vals['textArea']) . "<br>";
        }
//
////        $name = CProtect::string($vals['name'], 200);
////        $email = CProtect::string($vals['email'], 100);
////        $phone = CProtect::string($vals['phone'], 64);
////        $comment = CProtect::string($vals['comment'], 1000);
//        if (!count($form->error_messages)) {
//            dump($vals);
////            die('Fsubmitted');
//        }
    }
    $form->sendAjaxResponse(); // Uncomment for form submission via ajax.  Sends errors or successfulSubmitAction
}
$timeLog->log('form initialized');
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="form.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.validate.js"></script>
        <script type="text/javascript" src="js/jquery.form.js"></script>
        <script type="text/javascript" src="js/jquery.defaultvalue.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput.js"></script>
        <script type="text/javascript" src="js/formcustom.js"></script>


        <?/*<script type="text/javascript" src="js/jquery.defaultvalue.old.js"></script>*/?>
        <?/*<script type="text/javascript" src="js/old_jquery.js"></script>
        <script type="text/javascript" src="js/old_super_form.js"></script>*/?>
    </head>
    <body>
        <?= $form->start() ?>
        <p>
            <label for="<?= $form->form_id ?>_name">Name</label>
            <?= $form->d('name') ?><?= $form->dE('name') ?>
        </p>
        <p>
            <label for="<?= $form->form_id ?>_textField1">Text field 1</label>
            <?= $form->d('textField1') ?><?= $form->dE('textField1') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_textField2">Text field 2</label>
            <?= $form->d('textField2') ?><?= $form->dE('textField2') ?>
        </p>
        <p>
            <label for="<?= $form->form_id ?>_textField3">Text field 3</label>
            <?= $form->d('textField3') ?><?= $form->dE('textField3') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_email">Email</label>
            <?= $form->d('email') ?><?= $form->dE('email') ?>
        </p>


        <p>
            <label for="<?= $form->form_id ?>_checkboxSingle">Checkbox Single</label>
            <?= $form->d('checkboxSingle') ?><?= $form->dE('checkboxSingle') ?>
        </p>
        <p>
            <label for="<?= $form->form_id ?>_checkboxMultiple">Checkbox Multiple</label>
            <?= $form->d('checkboxMultiple', 0) ?><?= $form->d('checkboxMultiple', 1) ?><?= $form->d('checkboxMultiple', 2) ?>
            <?= $form->dE('checkboxMultiple') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_radioBox">RadioBox</label>
            <?= $form->d('radioBox', 0) ?><?= $form->d('radioBox', 1) ?>
            <?= $form->dE('radioBox') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_select">Select</label>
            <?= $form->d('select') ?><?= $form->dE('select') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_password">Password</label>
            <?= $form->d('password') ?><?= $form->dE('password') ?>
        </p>
        <p>
            <label for="<?= $form->form_id ?>_cpassword">Confirm Password</label>
            <?= $form->d('cpassword') ?><?= $form->dE('cpassword') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_textArea">Text Area</label>
            <?= $form->d('textArea') ?><?= $form->dE('textArea') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_file">File</label>
            <?= $form->d('file') ?><?= $form->dE('file') ?>
        </p>

        <p>
            <label for="<?= $form->form_id ?>_secretkey">Captcha</label>
                <a href="javascript:;" onclick="javascript:refreshSecretkey('<?= $form->form_id ?>');$('#<?= $form->form_id ?>_secretkey').focus();" title="Update code"><img id="<?= $form->form_id ?>_secretkey_img" src="/secretkey.php?type=<?= $form->form_id ?>&hash=<?= mktime() ?>" height="27" width="100" /></a>
                <?= $form->d('secretkey') ?><?= $form->dE('secretkey') ?>
        </p>

        <input type="submit" value="Submit">
        <?= $form->end() ?>

        <script type="text/javascript">
            function successfulSubmit<?= $form->form_id ?>Handler(responseText) {
//                $('#<?= $form->form_id ?>').remove();
//                $('#formAjaxSubmitSuccessBlock').show();
            }
        </script>

        <div id="formAjaxSubmitSuccessBlock" style="display:none;">Successful ajax submit</div>
    </body>
</html>





















<? exit; ?>
<script type="text/javascript">$().ready(function() {$("#form").validate(
    {onsubmit: true,
        errorClass: "error",
        validClass: "valid",
        ignore: ".ignore",
        errorElement: "p",
        onfocusout: false,
        onkeyup: false,
        onclick: false,
        invalidHandler: function(form, validator) {
            $("#form_loader").hide();$(".form_loader").hide();
        },
        rules:{
            "name":{"required":true,"maxlength":200},
            "email":{"required":true,"validemail":true,"maxlength":100},
            "phone":{"required":true}},
        messages:{
            "name":{"required":"Unspecified error when filling fields","maxlength":"Unspecified error when filling fields"},
            "email":{"required":"Unspecified error when filling fields","validemail":"Unspecified error when filling fields","maxlength":"Unspecified error when filling fields"},
            "phone":{"required":"Unspecified error when filling fields"}},
        submitHandler: function(form) {
            var f=$(form);
            var options = {
                dataType:  "xml",
                beforeSubmit:  function(formData, jqForm, options){
                    if(_submittedformForm)return false;
                    var form_id=jqForm.get(0).id;
                    for(var k in formData){
                        var obj=formData[k];
                        if(typeof(tinyMCE)!="undefined" && tinyMCE.get(form_id+"_"+obj.name)){
                            formData[k].value=tinyMCE.get(form_id+"_"+obj.name).getContent();
                            tinyMCE.execCommand("mceRemoveControl", false, form_id+"_"+obj.name);
                        }
                    }
                    if(typeof(beforeSubmitformHandler)=="function") {
                        var r = beforeSubmitformHandler(formData, jqForm, options);

                        if(!r)return false;
                    }
                    f.trigger("before_submit");
                    _submittedformForm=true;
                    $("#form_loader").show();
                    $(".form_loader").show();
                },success:  function(responseText, statusText, xhr, $form){
                    processXmlformResponse(responseText);
                    _submittedformForm=false;
                    $("#form_loader").hide();
                    $(".form_loader").hide();
                }

            };
            f.ajaxSubmit(options);
        }
    });
    function processXmlformResponse(responseXML){
        processXml(responseXML,"form", successformHandler)
    }
});
var _submittedformForm=false;
</script>