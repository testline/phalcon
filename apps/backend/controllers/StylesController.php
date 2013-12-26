<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Styles as Styles;

class StylesController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Styles::find();
         $this->view->items = $items;
         $this->view->title = 'Стиль';
         $this->view->createButtonTitle = 'Добавить стиль';
         $this->view->controller = 'styles';
         $this->view->breadCrumbs = array(array('title'=> 'Стиль', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/styles/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Styles::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование стиля';
        $this->view->breadCrumbs = array(array('title'=> 'Стили', 'url' => '#styles'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Styles();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление стиля';
        $this->view->breadCrumbs = array(array('title'=> 'Стили', 'url' => '#styles'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Styles::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Styles();
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
                    $form->successfulSubmitAction .= "messageBoard('Стиль сохранён');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'styles';";
                    $form->successfulSubmitAction .= "messageBoard('Стиль создан');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Styles::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'styles';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}