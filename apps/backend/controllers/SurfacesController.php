<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Surfaces as Surfaces;

class SurfacesController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Surfaces::find();
         $this->view->items = $items;
         $this->view->title = 'Поверхности';
         $this->view->createButtonTitle = 'Добавить поверхность';
         $this->view->controller = 'surfaces';
         $this->view->breadCrumbs = array(array('title'=> 'Поверхности', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/surfaces/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Surfaces::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование поверхности';
        $this->view->breadCrumbs = array(array('title'=> 'Поверхности', 'url' => '#surfaces'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Surfaces();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление поверхности';
        $this->view->breadCrumbs = array(array('title'=> 'Поверхности', 'url' => '#surfaces'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Surfaces::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Surfaces();
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
                    $form->successfulSubmitAction .= "messageBoard('Поверхность сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'surfaces';";
                    $form->successfulSubmitAction .= "messageBoard('Поверхность создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Surfaces::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'surfaces';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}