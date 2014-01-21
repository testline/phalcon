<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Collections as Collections;
use Admin\Backend\Models\Brands as Brands;

class CollectionsController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
//         $items = Collections::find();
//         $this->view->items = $items;
//         $this->view->title = 'Цвета';
//         $this->view->createButtonTitle = 'Добавть цвет';
//         $this->view->controller = 'collections';
//         $this->view->breadCrumbs = array(array('title'=> 'Цвета', 'url' => ''));
//         $this->view->pick("universal/references_index");
    }

    private function initForm($item, $brand = false) {
        $form = new \QForm('itemForm', '/admin/collections/save');
        \QFormHidden::setField($form, 'brand_id', $brand ? $brand->id : 0);
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Collections::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $brand = $this->getBrand($item->brands_id);

        $this->view->brand = $brand;
        $this->view->collection = $item;
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование коллекции';
    }

    public function createAction($brandId) {
        $item = new Collections();

        $brand = $this->getBrand((int) $brandId);
        $this->view->brand = $brand;

        $this->view->form = $this->initForm($item, $brand);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление коллекции';

        $this->view->pick("collections/edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;


        if ($updating) {
            $item = Collections::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
            $brand = false;
        } else {
            $item = new Collections();
            $brand = $this->getBrand((int) $_POST['brand_id']);
        }
        $form = $this->initForm($item);


        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();

                $fields = array(
                    'name' => $vals['name'],
                );
                if ($creating) {
                    $fields['brands_id'] = $brand->id;
                }

                if (!$item->save($fields))
                    die(json_encode(array('error' => 'Problem on saving')));

                if ($updating) {
                    $form->successfulSubmitAction .= "location.hash = '/brands/edit/$item->brands_id';";
                    $form->successfulSubmitAction .= "messageBoard('Коллекция сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = '/brands/edit/$brand->id';";
                    $form->successfulSubmitAction .= "messageBoard('Коллекция создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Collections::findFirst($itemId);
                if ($item != false)
                    $item->delete();
                $brandId = $item->brands_id;
            }
            $out->redirectHash = "/brands/edit/$brandId";
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

    private function getBrand($brandId) {
        $brandId = (int) $brandId;
        $brand = Brands::findFirst($brandId);
        if (!$brand)
            die(json_encode(array('error' => 'No such brand')));
        return $brand;
    }

}