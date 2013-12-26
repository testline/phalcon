function trim( str, charlist ) {
    charlist = !charlist ? ' ' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
    var re = new RegExp('^[' + charlist + ']+|[' + charlist + ']+$', 'g');
    return str.replace(re, '');
}

function explode( delimiter, string ) {

    var emptyArray = {
        0: ''
    };

    if ( arguments.length != 2
        || typeof arguments[0] == 'undefined'
        || typeof arguments[1] == 'undefined' )
        {
        return null;
    }

    if ( delimiter === ''
        || delimiter === false
        || delimiter === null )
        {
        return false;
    }

    if ( typeof delimiter == 'function'
        || typeof delimiter == 'object'
        || typeof string == 'function'
        || typeof string == 'object' )
        {
        return emptyArray;
    }

    if ( delimiter === true ) {
        delimiter = '1';
    }

    return string.toString().split ( delimiter.toString() );
}

function refreshSecretkey(formId) {
    $('#' + formId + '_secretkey').val('');
    $('#' + formId + '_secretkey_img').attr({
        'src':'/secretkey.php?type='+formId+'&hash='+Math.round(Math.random(0)*1000) + 1
    });
}


jQuery.validator.addMethod("validemail", function(value, element, param) {
    if(trim(value)=='' && this.optional(element)==false)return false;
    var reg = /^[0-9a-z_\.-]+@[0-9a-z_^\.-]+\.[a-z]{2,6}$/i;
    return this.optional(element) || (reg.test(value) || reg.test(value));
},'Please enter valid email');


jQuery.validator.addMethod("checkDate", function(value, element, param) {
    var params = explode(',',param);
    var day=parseInt($('#'+params[0]).val());
    var month=parseInt($('#'+params[1]).val())-1;
    var year=parseInt($('#'+element.id).val());
    if(!this.optional(element) && (day+month+year==-1))return true;
    var date = new Date( year, month, day )
    if((day==date.getDate())&&(month==date.getMonth())&&(year==date.getFullYear())&&(year>1753))
        return true;
    return false;
});

jQuery.validator.addMethod("secretkey",  function(value, element, param) {
    if(trim(value)=='' && this.optional(element)==false)return true;
    var reg = /^[a-z0-9_-]+$/i;
    return reg.test(value);// || reg.test(value);
}, 'Please enter valid secretkey!');
jQuery.validator.addMethod("checkAge", function(value, element, param) {
    var params = explode(',',param);
    var day=parseInt($('#'+params[0]).val());
    var month=parseInt($('#'+params[1]).val())-1;
    var year=parseInt($('#'+element.id).val());
    var ageStart = params[2];
    var ageEnd   = params[3];
    if(!this.optional(element) && (day+month+year==-1))return true;
    var date = new Date( year, month, day )
    if((day==date.getDate())&&(month==date.getMonth())&&(year==date.getFullYear())&&(year>1753))
    {
        var nowTime = new Date();
        var date1   = new Date(nowTime.getFullYear()-ageEnd,nowTime.getMonth(), nowTime.getDate());
        var date2   = new Date(nowTime.getFullYear()-ageStart,nowTime.getMonth(), nowTime.getDate());
        return ((date.getTime()>=date1.getTime())&&(date.getTime()<=date2.getTime()));
    }
    return false;
});

jQuery.validator.addMethod("validnickname", function(value, element, param) {
    if(value=='')return false;
    var reg = /^[a-zA-Z0-9_]+$/i;
    return reg.test(value);
}, 'Please enter valid nickname!');

jQuery.validator.addMethod("validshortname", function(value, element, param) {
    if(value=='')return true;
    var reg = /^[a-zA-Z0-9_]+$/i;
    return reg.test(value);
}, 'Please enter valid shortname!');

jQuery.validator.addMethod("urlshortname", function(value, element, param) {
    if(value=='')return true;
    var reg = /^[a-zA-Z0-9_\-]+$/i;
    return reg.test(value);
}, 'Please enter valid shortname!');

jQuery.validator.addMethod("validlatin", function(value, element, param) {
    if(trim(value)=='' && this.optional(element)!=false)return true;
    var reg = /^[a-z0-9!@%#$\^&*()\[\]+_=<>?.\/\\{}~"'’`:;,| \r\n\t~¢£¤¥¦§¨©«¬®¯°±²³´·¸¹º»ǀǁǂ\‒\–\—\―‖‗‘’‚‛“”„‟†‡•…′″‴‹›₤€₵℅ℓ№℗™■□▪▫◊○◌●◦-]+$/i;
    return reg.test(value);
}, 'Please enter latin symbols only!');

jQuery.validator.addMethod("twodigitsandtwoletters", function(value, element, param) {
    return true;
    if(value=='')return true;
    var reg1 = /.*[0-9].*[0-9].*/;
    var reg2 = /.*[a-zA-Z].*[a-zA-Z].*/;
    return reg1.test(value) && reg2.test(value);
}, 'Password must have at least 2 digits and 2 letters!');

jQuery.validator.addMethod("notequalto", function(value, element, param) {
    return value != $(param).val();
}, 'Must be different fields!');

jQuery.validator.addMethod("float", function(value, element, param) {
    if(value=='')return true;
    var reg = /^-?[0-9]+([.][0-9]+)?$/;
    return reg.test(value)
}, 'Please enter float!');

function processXml(responseXML, formId, callback_function) {
    var form = $('#' + formId);
    $('[id^=error_]',form).hide();
    var count_errors = 0;
    //var errors = new Object();
    $('e', responseXML).each(function(i){
        count_errors++;
        var container	= $('c',$(this)).text();
        var message	= $('m',$(this)).text();
        //errors[container.substring(formId.length + 1)] = message;
        setTimeout("$('#" + formId + "').data('validator').showErrors({'" + container.substring(formId.length + 1) +  "':'" + message.replace(/'/g, "&#039;") + "'})", 10); // fix of keyup glitch (field unhighlights after error set)
    });

    if (count_errors) {
//        form.data('validator').showErrors(errors);
        if ($('#' + formId + '_secretkey_img').length)
            refreshSecretkey(formId);
    } else if (typeof(callback_function) == 'function')
        callback_function(formId);

    $('action', responseXML).each(function(){ // executing custom action
        try {
            eval($(this).text());
        }
        catch(err) { alert($(this).text()) }
    });
}

//function formHighlight(element_name,element_id,form_id){
//    $('#'+element_id).addClass('error');
//    $('.errorText').show();
//}
//function formUnHighlight(element_name,element_id,form_id){
//    $('#'+element_id).removeClass('error');
//    $('.errorText').hide();
//}

