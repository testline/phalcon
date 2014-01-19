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

    static public function submitButton($title, $attributes) { ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="col-sm-10">
                <input type="submit" class="btn btn-default" <?= $attributes ?> value="<?= $title ?>">
            </div>
        </div>
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




    static public function formTabbedStart($titles, $active) { ?>
        <div class="tabbable tabbable-bordered">
            <ul class="nav nav-tabs">
                <? foreach ($titles as $k => $v) { $i ++; ?>
                    <li <?= $i == $active ? 'class="active"' : '' ?>><a _data-toggle="tab" href="<?= $v ?>"><?= $k ?></a></li>
                <? } ?>
<!--                <li class="active"><a data-toggle="tab" href="#tb1_b">Section 2</a></li>
                <li><a data-toggle="tab" href="#tb1_c">Section 3</a></li>-->
            </ul>
            <div class="tab-content">
<!--                <div id="tb1_a" class="tab-pane _active">
                    <p>Section 1</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi elit dui, porta ac scelerisque placerat, rhoncus vitae sem. Nulla eget libero enim, facilisis accumsan eros.</p>
                </div>
                <div id="tb1_b" class="tab-pane active">
                    <p>Section 2</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi elit dui, porta ac scelerisque placerat, rhoncus vitae sem. Nulla eget libero enim, facilisis accumsan eros.</p>
                </div>
                <div id="tb1_c" class="tab-pane">
                    <p>Section 3</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi elit dui, porta ac scelerisque placerat, rhoncus vitae sem. Nulla eget libero enim, facilisis accumsan eros.</p>
                </div>-->
<!--            </div>
        </div>-->
    <? }
    static public function formTabbedEnd() { ?>
        </div></div><br>
    <? }

}

?>