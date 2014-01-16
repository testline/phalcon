<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Users as Users;

class LoginController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        if ($this->request->isPost()) {
            $login = $this->request->getPost("login", "string");
            $password = $this->request->getPost("password", "string");

            $user = Users::findFirst("active = 1 AND login = '$login' AND password = '$password'");

            if ($user) {
                $this->session->set('user', $user);
                header('Location: /admin' . $url);
                exit();
            } else {
                $this->view->login = $login;
                $this->view->password = $password;
                $this->view->loginError = true;
            }
        } else {
            $this->view->login = 'developer';
            $this->view->password = 'qqqq11';
        }
    }

    public function logoutAction() {
        $this->session->remove('user');
        header('Location: /login' . $url);
        exit();
        return $this->response->redirect('login');
    }

}