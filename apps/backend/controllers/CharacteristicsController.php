<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Characteristics as Characteristics;
use Admin\Backend\Models\CharacteristicsListValues as CharacteristicsListValues;
use Admin\Backend\Models\Categories as Categories;

class CharacteristicsController extends \Phalcon\Mvc\Controller {

    public $category;

    private function initForm($characteristic) {
        $form = new \QForm('characteristicForm', '/admin/characteristics/save');
        \QFormHidden::setField($form, 'category_id', $this->category ? $this->category->id : 0);
        \QFormHidden::setField($form, '_id', $characteristic->id);
        \QFormText::setField($form, 'name', $characteristic->name);

        $types = array(
            'integer'       => 'Целое число',
            'float'         => 'Действительное число',
            'string'        => 'Строка',
            'boolean'       => 'Да/нет',
            'select'        => 'Список',
            'radioboxes'    => 'Радио',
        );
        \QFormSelectBox::setField($form, 'type', array($types, ''));
        if ($characteristic->type)
            $form->getFieldByName('type')->selected_value = $characteristic->type;

        \QFormCheckBox::setField($form, 'required', 1);
        $form->getFieldByName('required')->checked_value = array($characteristic->required);

        $form->setFieldsRules(array(
            'name' => 'required',
            'type' => 'required',
        ));
        return $form;
    }

    public function editAction($characteristicId) {
        $characteristic = Characteristics::findFirst((int) $characteristicId);
        if (!$characteristic)
            die(json_encode(array('error' => 'No such characteristic')));
        $this->view->form = $this->initForm($characteristic);
        $this->view->characteristic = $characteristic;

        $this->setCategory($characteristic->categories_id);
        $this->view->category = $this->category;

        $this->view->listValues = CharacteristicsListValues::find(array("characteristics_id = $characteristic->id"));
        $this->view->create = false; $this->view->edit = true;
    }

    public function createAction($categoryId) {
        $this->setCategory($categoryId);
        $characteristic = new Characteristics();
        $this->view->form = $this->initForm($characteristic);
        $this->view->create = true; $this->view->edit = false;
        $this->view->category = $this->category;
        $this->view->pick("characteristics/edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $characteristic = Characteristics::findFirst((int) $_POST['_id']);
            if (!$characteristic)
                die(json_encode(array('error' => 'No such characteristic')));
        } else {
            $characteristic = new Characteristics();
            $category_id = (int) $_POST['category_id'];
            $this->setCategory($category_id);
        }
        $form = $this->initForm($characteristic);


        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();


                $fields = array(
                    'name' => $vals['name'],
                    'type' => $vals['type'],
                    'required' => (int) $vals['required'] ? 1 : 0,
                );

                if ($creating) {
                    $fields['categories_id'] = $category_id;
                }

                // Delete list values
                $listIdsToDelete = explodeAndSanitize($_POST['listValuesToDelete']);
                if ($listIdsToDelete)
                    $this->di->get('db')->execute("DELETE FROM characteristics_list_values WHERE id IN (" . implode(',', $listIdsToDelete) . ")");

                $newListValues = array();
                if (in_array($fields['type'], array('select', 'radioboxes'))) {
                    // Add new list values
                    if (isset($_POST['newListValues'])) {
                        foreach ($_POST['newListValues'] as $k => $v)
                            $newListValues[] = (string) $v;

                        \Debug::fb($newListValues, '$newListValues');
                    }
                } else {
                    // Remove list values if type changed. E.g. from select to integer
                    if (in_array($characteristic->type, array('select', 'radioboxes')))
                        $this->di->get('db')->execute("DELETE FROM characteristics_list_values WHERE characteristics_id = $characteristic->id");
                }



                if (!$characteristic->save($fields))
                    die(json_encode(array('error' => 'Problem on saving')));
                // Add $newListValues
                if (sizeof($newListValues)) {
                    foreach ($newListValues as $k => $v) {
                        $this->di->get('db')->execute("INSERT INTO characteristics_list_values (characteristics_id, value) VALUES ({$characteristic->id}, '$v')");
                    }
                }


                if ($updating) {
                    $form->successfulSubmitAction .= "locationHashChanged();";
                    $form->successfulSubmitAction .= "messageBoard('Характеристика сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'characteristics/edit/{$characteristic->id}';";
                    $form->successfulSubmitAction .= "messageBoard('Характеристика создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

    public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $itemId) {
                $item = Characteristics::findFirst($itemId);
                $categoriesId = $item->categories_id;
                if ($item != false)
                    $item->delete();
            }
            $out->redirectHash = "categories/edit/$categoriesId";
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

    private function setCategory($categoryId) {
        $categoryId = (int) $categoryId;
        $this->category = Categories::findFirst($categoryId);
        if (!$this->category)
            die(json_encode(array('error' => 'No such category')));
    }

}