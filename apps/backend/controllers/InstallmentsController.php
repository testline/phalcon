<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Installments as Installments;

class InstallmentsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Installments::find();
         $this->view->items = $items;
         $this->view->title = 'Установка';
         $this->view->createButtonTitle = 'Добавить установку';
         $this->view->controller = 'installments';
         $this->view->breadCrumbs = array(array('title'=> 'Установка', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/installments/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Installments::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование установки';
        $this->view->breadCrumbs = array(array('title'=> 'Установка', 'url' => '#installments'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Installments();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление установки';
        $this->view->breadCrumbs = array(array('title'=> 'Установка', 'url' => '#installments'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Installments::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Installments();
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
                    $form->successfulSubmitAction .= "messageBoard('Установка сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'installments';";
                    $form->successfulSubmitAction .= "messageBoard('Установка создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Installments::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'installments';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}