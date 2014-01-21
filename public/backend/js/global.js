ajaxParameters = {
    type: 'POST',
    timeout: 15000, // Formgen timeout in QForm.php
    error: function(req, error){
        if (error === 'error') error = req.statusText;
        messageBoard('There was a communication error: ' + error, 'warning');
    }
};
function ajax(parameters) {
    var success = parameters.success;
    parameters.success = function (data) {
        //console.log(data);
        try { data = eval('(' + data + ')'); } catch(err) { alert(data); }
        if (data.action !== undefined) {
            eval(data.action);
        }
        if (data.error === undefined) {
            success(data);
            if (data.message !== undefined) messageBoard(data.message);
        } else errorHandler(data);
        clearTimeout(timeout);
        $('#loadingOverlay').hide();
    }
    var timeout = setTimeout("$('#loadingOverlay').show()", 100);
    $.ajax($.extend({}, ajaxParameters, parameters));
}

function errorHandler(data) {
    if (data.error == 'auth')
        document.location = '/admin';
    messageBoard(htmlspecialchars(data.error), 'warning');

}
var notificationDefaults = {
    position: 'top-right', // top-left, top-right, bottom-left, or bottom-right
    speed: 'fast', // animations: fast, slow, or integer
    allowdupes: true, // true or false
    autoclose: 3000,  // delay in milliseconds. Set to 0 to remain open.
    classList: '' // arbitrary list of classes. Suggestions: success, warning, important, or info. Defaults to ''.
};
function messageBoard(text, type) {
    if (type === undefined) type = 'success';
    $.stickyNote(text, $.extend({}, notificationDefaults, {classList: 'stickyNote-' + type}))
}

window.addEventListener("hashchange", locationHashChanged, false);
function locationHashChanged() {
    var hash = location.hash.substring(1);

    var ajaxData = new Object();
    ajaxData.hash = hash;

    ajax({
        url:'/admin/' + hash,
        data : ajaxData,
        success : function(data) {
            $('#main_content').html(data.html);

            $('.page-title').html(data.title);
            if (typeof(data.breadCrumbs) != 'undefined') {
                var breadCrumbsHtml = '';
                for (var k in data.breadCrumbs) {
                    var v = data.breadCrumbs[k];
                    if (v.url == '')
                        breadCrumbsHtml += '<li><span>' + v.title + '</span></li>';
                    else breadCrumbsHtml += '<li><a href="' + v.url + '"><span>' + v.title + '</a></span></li>';
                }
                $('#breadcrumbs ul').html(breadCrumbsHtml);
            }
            window.scrollTo(0,0);
        }
    })

    // Highlight link in menu
    var hashSplit = hash.split('/');
    var controller = hashSplit[0];
    // Highlight first level link and open submenu/subtree if exists
    var firstLevelNewActiveLink = $('a.menu_link[href="#' + controller + '"], a.menu_link[data-highlightOn~="#' + controller + '"]');
    var firstLevelOldActiveLink = $('a.menu_link_opened');
    if (!firstLevelNewActiveLink.is(firstLevelOldActiveLink)) {
        $('a.menu_link_opened').parent().find('.menu_section_subtree, .submenu_section').slideUp();
        $('a.menu_link').removeClass('menu_link_opened');
        firstLevelNewActiveLink.addClass('menu_link_opened');
        firstLevelNewActiveLink.parent().find('.menu_section_subtree, .submenu_section').slideDown();//.css('display', 'block!important')
    }
    // Highlight second level link
    var secondLevelNewActiveLink = $('.submenu_section a[href="#' + controller + '"], .submenu_section a[data-highlightOn~="#' + controller + '"]');
    $('.submenu_section a').removeClass('active');
//    $('.submenu_section').hide();//!
    if (secondLevelNewActiveLink.length) {
        secondLevelNewActiveLink.addClass('active');
//        secondLevelNewActiveLink.parent().parent().css('display', 'block!important');//!
    }

    // Highlight block in header
    $('#icon_nav_h li').removeClass('active');
    $('#icon_nav_h a[href="#' + controller + '"], #icon_nav_h a[data-highlightOn~="#' + controller + '"]').parent().addClass('active');




    // Highlight current node in trees
    treeProducts.cancelSelectedNode();
    treeCategories.cancelSelectedNode();
    var selectedNode = treeProducts.getNodeByParam("url", '#' + hash, null);
    if (selectedNode) treeProducts.selectNode(selectedNode);
    var selectedNode = treeCategories.getNodeByParam("url", '#' + hash, null);
    if (selectedNode) treeCategories.selectNode(selectedNode);
}

var treeProducts, treeCategories;
function initializeTree(treeType, nodes) {
    window['tree' + treeType] = $.fn.zTree.init($("#tree" + treeType), {}, nodes);
    var selectedNode = window['tree' + treeType].getNodeByParam("url", location.hash, null);
    if (selectedNode) window['tree' + treeType].selectNode(selectedNode);
}

$(document).ready(function() {
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
            $('body').css('overflow','auto');
        }
    }


    // Refresh in case hash was the same (communication error or simple refresh)
    $('body').on('click', "a[href^='#']", function(event) {
        if (location.hash == $(this).attr('href'))
            locationHashChanged();
    });

    // Init product tree
    initializeTree('Products', treeNodesProducts);
    initializeTree('Categories', treeNodesCategories);

    locationHashChanged();

    $('body').on('click', '.deleteItem', function(event) {
        var itemId = $(this).attr('data-itemId');
        var messageId = $(this).attr('data-deleteConfirmationMessage');
        var controller = $(this).attr('data-controller');
        var deleteConfirmationMessage = typeof deleteConfirmationMessages[controller] != "undefined" ? deleteConfirmationMessages[controller] : deleteConfirmationMessages['default'];
        confirmDialog(deleteConfirmationMessage, {text : "Удалить", click :function() {
            deleteAction(controller, [itemId])
        }});
    });
});

function deleteAction(path, ids) {
    ajax({
        url:'/admin/' + path + '/delete',
        data : {
            ids : ids.join(',')
        },
        success : function(data) {
            if (data.redirectHash !== undefined) {
                if (location.hash == '#' + data.redirectHash)
                    locationHashChanged();
                else location.hash = data.redirectHash;
            }
        }
    });
}



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










// Service functions


function htmlspecialchars (string, quote_style, charset, double_encode) {
    var optTemp = 0,
    i = 0,
    noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}