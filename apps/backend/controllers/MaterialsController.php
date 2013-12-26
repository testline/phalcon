<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Materials as Materials;

class MaterialsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Materials::find();
         $this->view->items = $items;
         $this->view->title = 'Материал';
         $this->view->createButtonTitle = 'Добавить материал';
         $this->view->controller = 'materials';
         $this->view->breadCrumbs = array(array('title'=> 'Материалы', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/materials/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Materials::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование материала';
        $this->view->breadCrumbs = array(array('title'=> 'Материалы', 'url' => '#materials'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Materials();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление материала';
        $this->view->breadCrumbs = array(array('title'=> 'Материалы', 'url' => '#materials'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Materials::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Materials();
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
                    $form->successfulSubmitAction .= "messageBoard('Материал сохранён');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'materials';";
                    $form->successfulSubmitAction .= "messageBoard('Материал создан');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Materials::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'materials';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}