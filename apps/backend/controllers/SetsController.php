<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Sets as Sets;

class SetsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Sets::find();
         $this->view->items = $items;
         $this->view->title = 'Комплектация';
         $this->view->createButtonTitle = 'Добавить комплектацию';
         $this->view->controller = 'sets';
         $this->view->breadCrumbs = array(array('title'=> 'Комплектации', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/sets/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Sets::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование комплектации';
        $this->view->breadCrumbs = array(array('title'=> 'Комплектации', 'url' => '#sets'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Sets();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление комплектации';
        $this->view->breadCrumbs = array(array('title'=> 'Комплектации', 'url' => '#sets'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Sets::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Sets();
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
                    $form->successfulSubmitAction .= "messageBoard('Комплектация сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'sets';";
                    $form->successfulSubmitAction .= "messageBoard('Комплектация создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Sets::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'sets';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}