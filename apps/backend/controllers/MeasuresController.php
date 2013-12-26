<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Measures as Measures;

class MeasuresController extends \Phalcon\Mvc\Controller {

     public function indexAction() {
         $items = Measures::find();
         $this->view->items = $items;
         $this->view->title = 'Единицы измерения';
         $this->view->createButtonTitle = 'Добавить единицу измерения';
         $this->view->controller = 'measures';
         $this->view->breadCrumbs = array(array('title'=> 'Единицы измерения', 'url' => ''));
         $this->view->pick("universal/references_index");
    }

    private function initForm($item) {
        $form = new \QForm('itemForm', '/admin/measures/save');
        \QFormHidden::setField($form, '_id', $item->id);
        \QFormText::setField($form, 'name', $item->name);

        $form->setFieldsRules(array(
            'name' => 'required',
        ));
        return $form;
    }

    public function editAction($itemId) {
        $item = Measures::findFirst((int) $itemId);
        if (!$item)
            die(json_encode(array('error' => 'No such item')));
        $this->view->form = $this->initForm($item);
        $this->view->create = false; $this->view->edit = true;
        $this->view->title = 'Редактирование единицы измерения';
        $this->view->breadCrumbs = array(array('title'=> 'Единицы измерения', 'url' => '#measures'), array('title'=> $item->name, 'url' => ''));
        $this->view->pick("universal/references_edit");
    }

    public function createAction() {
        $item = new Measures();
        $this->view->form = $this->initForm($item);
        $this->view->create = true; $this->view->edit = false;

        $this->view->title = 'Добавление единицы измерения';
        $this->view->breadCrumbs = array(array('title'=> 'Единицы измерения', 'url' => '#measures'), array('title'=> $this->view->title, 'url' => ''));

        $this->view->pick("universal/references_edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $item = Measures::findFirst((int) $_POST['_id']);
            if (!$item)
                die(json_encode(array('error' => 'No such item')));
        } else {
            $item = new Measures();
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
                    $form->successfulSubmitAction .= "messageBoard('Единица измерения сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'measures';";
                    $form->successfulSubmitAction .= "messageBoard('Единица измерения создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

   public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Measures::findFirst($itemId);
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = 'measures';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}