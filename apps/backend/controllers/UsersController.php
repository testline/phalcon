<?php

namespace Admin\Backend\Controllers;

use Admin\Backend\Models\Users as Users;

class UsersController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        $users = Users::find();
        $this->view->users = $users;
    }


    private function initForm($id) {
        //sleep(2);
        if ($id)
            $user = Users::findFirst($id);
        else
            $user = new Users();

        $form = new \QForm('userForm', '/admin/users/save');
        \QFormHidden::setField($form, '_id', $id);

        \QFormText::setField($form, 'login', $user->login);
        \QFormPassword::setField($form, 'password');
        \QFormText::setField($form, 'firstname', $user->firstname);
        \QFormText::setField($form, 'lastname', $user->lastname);

        $form->setFieldsRules(array(
            'login' => 'required|maxlength[15]|minlength[4]',
//            'firstname' => 'required|maxlength[15]',
//            'lastname' => 'required|maxlength[15]',
        ));
        $form->successfulSubmitAction .= "locationHashChanged();";
        $form->successfulSubmitAction .= "messageBoard('Пользователь сохранён');";
        return $form;
    }

    public function editAction($id) {
        $this->view->form = $this->initForm((int) $id);
    }

    public function createAction() {
        $this->view->form = $this->initForm(0);
        $this->view->create = true;
        $this->view->pick("users/edit");
    }

    public function saveAction() {
        $this->view->disable();
        $form = $this->initForm(0);
        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();
                //\Debug::fb($vals);
                if ($vals['_id']) { // Updating
                    $user = Users::findFirst((int) $vals['_id']);
                } else { // Creating
                    $user = new Users();
                }
                $user->save(array(
                    'login' => $vals['login'],
                    'password' => $vals['password'],
                    'firstname' => $vals['firstname'],
                    'lastname' => $vals['lastname'],
                ));
//                foreach ($user->getMessages() as $message) {
//                    \Debug::fb($message, 'nn');
//                }
//                $form->setError('name', 'Custom error');
            }
            $form->sendAjaxResponse(); // Sends errors or successfulSubmitAction
        }
    }

}
