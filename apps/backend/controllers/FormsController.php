<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Forms as Forms;

class FormsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Forms::find();
         $this->view->items = $items;
         $this->view->title = 'Формы';
         $this->view->createButtonTitle = 'Добавить форму';
         $this->view->controller = 'forms';
         $this->view->breadCrumbs = array(array('title'=> 'Формы', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/forms/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Forms::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование формы';
        $this->view->breadCrumbs = array(array('title'=> 'Формы', 'url' => '#forms'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Forms();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление формы';
        $this->view->breadCrumbs = array(array('title'=> 'Формы', 'url' => '#forms'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Forms::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Forms();
        }
        $form = $this->initForm($item);


        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();

                $fields = array(
                    'name' => $vals['name'],
                );
                if ($creating) {
                }

                if (!$item->save($fields))
                    die(json_encode(array('error' => 'Problem on saving')));

                if ($updating) {
                    $form->successfulSubmitAction .= "locationHashChanged();";
                    $form->successfulSubmitAction .= "messageBoard('Форма сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'forms';";
                    $form->successfulSubmitAction .= "messageBoard('Форма создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Forms::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'forms';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}