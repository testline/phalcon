<?php

class Helper extends \Phalcon\Tag {

    static public function editButtonInTable($controller, $itemId) {
        return "<a href='#$controller/edit/$itemId'><i class='icon-pencil'></i> Редактировать</a>&nbsp;";
    }

    static public function deleteButtonInTable($controller, $itemId) {
        return "<span class='deleteItem spanLikeLink' data-itemId='$itemId' data-deleteConfirmationMessage='$controller' data-controller='$controller'><i class='icon-remove'></i> Удалить</span>";
    }


    static public function addButton($href, $title) { ?>
        <a href="<?= $href ?>" class="btn btn-success buttonWithMarginTop"><i class="glyphicon glyphicon-plus"></i><?= $title ?></a>
    <? }




    static public function formBlockStart($title) { ?>
        <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title"><?= $title ?></h4>
        </div>
        <div class="panel-body">
    <? }
    static public function formBlockEnd() { ?>
        </div></div>
    <? }

}

?>