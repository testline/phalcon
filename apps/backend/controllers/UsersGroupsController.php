<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Users as Users;
use Admin\Backend\Models\UsersGroups as UsersGroups;

class UsersgroupsController extends \Phalcon\Mvc\Controller {

    private function initForm($id) {
        if ($id)
            $group = UsersGroups::findFirst($id);
        else
            $group = new UsersGroups();

        $form = new \QForm('userGroupForm', '/admin/users-groups/save');
        \QFormHidden::setField($form, '_id', $id);

        \QFormText::setField($form, 'name', $group->name);
        \QFormCheckBox::setField($form, 'user_editing', 1);
        $form->getFieldByName('user_editing')->checked_value = array($group->user_editing);
        \QFormCheckBox::setField($form, 'access_editing', 1);
        $form->getFieldByName('access_editing')->checked_value = array($group->access_editing);
        \QFormCheckBox::setField($form, 'sections_editing', 1);
        $form->getFieldByName('sections_editing')->checked_value = array($group->sections_editing);

        $form->setFieldsRules(array(
            'name' => 'required|maxlength[50]|minlength[3]',
        ));
        return $form;
    }

    public function editAction($id) {
        $this->view->form = $this->initForm((int) $id);
    }

    public function createAction() {
        $this->view->form = $this->initForm(0);
        $this->view->create = true;
        $this->view->pick("usersgroups/edit");
    }

    public function saveAction() {
        $form = $this->initForm(0);
        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();
                if ($vals['_id']) { // Updating
                    $group = UsersGroups::findFirst((int) $vals['_id']);
                } else {            // Creating
                    $group = new UsersGroups();
                }
                $group->save(array(
                    'name' => $vals['name'],
                    'user_editing' => (int) $vals['user_editing'],
                    'access_editing' => (int) $vals['access_editing'],
                    'sections_editing' => (int) $vals['sections_editing'],
                ));
                if ($vals['_id']) { // Updating
                    $form->successfulSubmitAction .= "locationHashChanged();";
                    $form->successfulSubmitAction .= "messageBoard('Группа пользователей сохранена');";
                } else {            // Creating
                    $form->successfulSubmitAction .= "location.hash = 'users';";
                    $form->successfulSubmitAction .= "messageBoard('Группа пользователей создана');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

    public function deleteAction() {
        $userGroupId = (int) $_POST['ids'];
        $group = UsersGroups::findFirst($userGroupId);
        if ($group) {
            $this->di->get('db')->execute("DELETE FROM users WHERE users_groups_id = $userGroupId");
            $group->delete();
            $out->redirectHash = 'users';
        } else $out->error = 'No group with this id';

        die(json_encode($out));
    }

}

