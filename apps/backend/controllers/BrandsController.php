<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Brands as Brands;
use Admin\Backend\Models\Collections as Collections;

class BrandsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {

         $items = Brands::find();
         $this->view->items = $items;
         $this->view->title = 'Бренды';
         $this->view->createButtonTitle = 'Добавить бренд';
         $this->view->controller = 'brands';
         $this->view->breadCrumbs = array(array('title'=> 'Бренды', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/brands/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Brands::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));


        $collections = Collections::find("brands_id = $item->id");

        $this->view->brand = $item;
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование бренда';
        $this->view->breadCrumbs = array(array('title'=> 'Бренды', 'url' => '#brands'), array('title'=> $item->name, 'url' => ''));
        $this->view->items = $collections;
        //$this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Brands();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление бренда';
        $this->view->breadCrumbs = array(array('title'=> 'Бренды', 'url' => '#brands'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Brands::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Brands();
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
                    $form->successfulSubmitAction .= "messageBoard('Бренд сохранён');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'brands';";
                    $form->successfulSubmitAction .= "messageBoard('Бренд создан');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Brands::findFirst($itemId);
                if ($item != false) {
                    $this->di->get('db')->execute("DELETE FROM collections WHERE brands_id = $item->id");
                    $item->delete();
                }
            }
            $out->redirectHash = 'brands';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}