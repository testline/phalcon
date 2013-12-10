ajaxParameters = {
    type: 'POST',
    timeout: 15000, // Formgen timeout in QForm.php
    error: function(req, error){
        if (error === 'error') error = req.statusText;
        messageBoard('There was a communication error: ' + error, 'error');
    }
};
function ajax(parameters) {
    var success = parameters.success;
    parameters.success = function (data) {
        data = eval('(' + data + ')');
        if (data.error === undefined) {
            success(data);
            if (data.message !== undefined) messageBoard(data.message);
        } else errorHandler(data);
        clearTimeout(timeout);
        $('#loadingOverlay').hide();
        if (data.treeData !== undefined)
            initializeTree({ 'initialTreeData' : eval('(' + data.treeData + ')'), 'initialNodeSelectedInTree' : data.selectedNode }, adminTree);
    }
    var timeout = setTimeout("$('#loadingOverlay').show()", 100);
    $.ajax($.extend({}, ajaxParameters, parameters));
    //$('#tooltip').hide();
}

function errorHandler(data) {
    if (data.error == 'auth')
        document.location = '/control';
    messageBoard(htmlspecialchars(data.error), 'error');//!
}
function messageBoard(text, type) {
//    if (type === undefined) type = 'success';
//    $('#messageBoard').prepend('<p class="' + type + '">' + text + '</p>').find('>:first-child').delay(type == 'success' ? 3000 : 5000).fadeOut(1000);
//    $('#messageBoard p:hidden').remove();
//    $('#gritter-without-image').click(function () {
        $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: text,
            // (string | mandatory) the text inside the notification
            text: ' '
        });

//        return false;
//    });
}

window.addEventListener("hashchange", locationHashChanged, false);
function locationHashChanged() {
    var hash = location.hash.substring(1);
    var hashSplit = hash.split(':');

    var ajaxData = new Object();
    ajaxData.hash = hash;

    ajax({
        url:'/admin/' + hash,
        data : ajaxData,
        success : function(data) {
            $('#boiler').html(data.html);
            $('.page-title').html(data.title);
            if (typeof(data.breadCrumbs) != 'undefined') {
                var breadCrumbsHtml = '';
                var i = 0;
                for (var k in data.breadCrumbs) {
                    i ++;
                    var v = data.breadCrumbs[k];
                    if (i == Object.keys(data.breadCrumbs).length)
                        breadCrumbsHtml += '<li>' + k + '<span class="divider-last">&nbsp;</span></li>';
                    else breadCrumbsHtml += '<li><a href="' + v + '">' + k + '</a><span class="divider">&nbsp;</span></li>';

                }
                $('.breadcrumb li').not('.first-breadcrumb').remove();
                $('.breadcrumb').append(breadCrumbsHtml);
            }
        }
    })
}

$(document).ready(function() {
    if (location.hash != '')
        locationHashChanged();

    $(document).keydown(function(e) {
        if (e.keyCode == 27) { // Escape
           closeTopPopup();
           //hideImageUploader();
           //closeMapPointSelect();
        }
    });
    function closeTopPopup() {
        if ($('#confirmDialog').length) {
            // Dialog
            closeConfirmDialog();
        } else {
            // Popup tree
            $('#loadingOverlay').hide();
//            $('#popupTreeContainer').hide();
//            $('#popupTreeOverlay').hide();
            $('body').css('overflow','auto');
        }
    }



    // Refresh in case hash was the same (communication error or simple refresh)
    $('body').on('click', "a[href^='#']", function(event) {
        if (location.hash == $(this).attr('href'))
            locationHashChanged();
    });

});


// Dialog
function confirmDialog(text, actionButton) {
    $('body').css('overflow','hidden');
    var confirmHtml = '<div id="confirmDialogOverlay"></div>';
    confirmHtml += '<div id="confirmDialog"><p>' + text + '</p>';

    if (actionButton !== undefined) {
        confirmHtml += '<button type="button" id="cancelConfirmDialog" onclick="closeConfirmDialog()" class="btn">Отмена</button>';
        confirmHtml += '<button type="button" id="continueConfirmDialog" class="btn btn-primary">Да</button>';
    } else {
        confirmHtml += '<button type="button" id="cancelConfirmDialog" onclick="closeConfirmDialog()" class="btn">Ок</button>';
    }
    confirmHtml += '</div>';
    $('body').append(confirmHtml);
    $('#confirmDialog').css('top', Math.round(($(window).height() - $('#confirmDialog').height()) / 2)).css('left', Math.round(($(window).width() - $('#confirmDialog').width()) / 2));

    if (actionButton !== undefined) {
        $('#confirmDialog button#continueConfirmDialog').click(function(event){
            actionButton.click();
            closeConfirmDialog();
        }).focus().attr("tabindex", 1001);
    }
    $('#cancelConfirmDialog').attr("tabindex", 1002).focusout(function(event){
        $('#continueConfirmDialog').focus();
    });
}
function closeConfirmDialog() {
    $('#confirmDialogOverlay').remove();
    $('#confirmDialog').remove();
    $('body').css('overflow','auto');
}