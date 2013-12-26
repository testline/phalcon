<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Users as Users;
use Admin\Backend\Models\UsersGroups as UsersGroups;

class UsersController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        $usersGroupsRaw = UsersGroups::find();
        foreach ($usersGroupsRaw as $k => $v) {
            $usersGroups[$v->id] = array();
            $usersGroups[$v->id]['group'] = $v;
            $usersGroups[$v->id]['users'] = array();
        }
        $users = Users::find();
        foreach ($users as $k => $v)
            $usersGroups[$v->users_groups_id]['users'][] = $v;
        $this->view->users = $users;
        $this->view->usersGroups = $usersGroups;
    }

    private function initForm($user) {
        $form = new \QForm('userForm', '/admin/users/save');
        \QFormHidden::setField($form, '_id', $user->id);
        \QFormHidden::setField($form, '_groupId', $user->users_groups_id);

        \QFormText::setField($form, 'login', $user->login);
        \QFormPassword::setField($form, 'password', $user->password);
        \QFormPassword::setField($form, 'cpassword', $user->password);
        \QFormText::setField($form, 'firstname', $user->firstname);
        \QFormText::setField($form, 'lastname', $user->lastname);
        \QFormCheckBox::setField($form, 'active', 1);
        $form->getFieldByName('active')->checked_value = array($user->active);

        $form->setFieldsRules(array(
            'login' => 'required|maxlength[15]|minlength[4]',
            'password' => 'required|minlength[6]|maxlength[16]|twodigitsandtwoletters',
            'cpassword' => 'required|equalTo[password]',
//            'firstname' => 'required',
//            'lastname' => 'required',
        ));
        return $form;
    }

    public function editAction($id) {
        $user = Users::findFirst((int) $id);
        if (!$user)
            die(json_encode(array('error' => 'No such user')));

        $this->view->group = UsersGroups::findFirst($user->users_groups_id);
        $this->view->form = $this->initForm($user);
    }

    public function createAction($usersGroupId) {
        $group = UsersGroups::findFirst($usersGroupId);
        if (!$group) die(json_encode(array('error' => 'Wrong group')));
        $user = new Users();
        $user->users_groups_id = $group->id;
        $this->view->form = $this->initForm($user);
        $this->view->group = $group;
        $this->view->create = true;
        $this->view->pick("users/edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $user = Users::findFirst((int) $_POST['_id']);
            if (!$user)
                die(json_encode(array('error' => 'No such user')));
        } else $user = new Users();
        $form = $this->initForm($user);


        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();

                $fields = array(
                    'login' => $vals['login'],
                    'password' => $vals['password'],
                    'firstname' => $vals['firstname'],
                    'lastname' => $vals['lastname'],
                    'active' => (int) $vals['active'],
                );

                // Validate user group on create
                if ($creating) {
                    $usersGroupId = (int) $vals['_groupId'];
                    $group = UsersGroups::findFirst($usersGroupId);
                    if (!$group) die('Wrong group');
                    $fields['users_groups_id'] = $usersGroupId;
                }

                $user->save($fields);
                if ($updating) {
                    $form->successfulSubmitAction .= "locationHashChanged();";
                    $form->successfulSubmitAction .= "messageBoard('Пользователь сохранён');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'users';";
                    $form->successfulSubmitAction .= "messageBoard('Пользователь создан');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

    public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $userId) {
                $user = Users::findFirst($userId);
                if ($user != false)
                    $user->delete();
            }
            $out->redirectHash = 'users';
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

}