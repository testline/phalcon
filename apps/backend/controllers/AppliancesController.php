<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Appliances as Appliances;

class AppliancesController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Appliances::find();
         $this->view->items = $items;
         $this->view->title = 'Применения';
         $this->view->createButtonTitle = 'Добавить применение';
         $this->view->controller = 'appliances';
         $this->view->breadCrumbs = array(array('title'=> 'Применения', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/appliances/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Appliances::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование применения';
        $this->view->breadCrumbs = array(array('title'=> 'Применения', 'url' => '#appliances'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Appliances();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление применения';
        $this->view->breadCrumbs = array(array('title'=> 'Применения', 'url' => '#appliances'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Appliances::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Appliances();
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
                    $form->successfulSubmitAction .= "messageBoard('Применение сохранено');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'appliances';";
                    $form->successfulSubmitAction .= "messageBoard('Применение создано');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Appliances::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'appliances';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}