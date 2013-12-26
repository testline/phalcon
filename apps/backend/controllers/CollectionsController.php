<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Colors as Colors;

class ColorsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Colors::find();
         $this->view->items = $items;
         $this->view->title = 'Цвета';
         $this->view->createButtonTitle = 'Добавть цвет';
         $this->view->controller = 'colors';
         $this->view->breadCrumbs = array(array('title'=> 'Цвета', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/colors/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Colors::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование цвета';
        $this->view->breadCrumbs = array(array('title'=> 'Цвета', 'url' => '#colors'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Colors();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление цвета';
        $this->view->breadCrumbs = array(array('title'=> 'Цвета', 'url' => '#colors'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Colors::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Colors();
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
                    $form->successfulSubmitAction .= "messageBoard('Цвет сохранён');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'colors';";
                    $form->successfulSubmitAction .= "messageBoard('Цвет создан');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Colors::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'colors';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}