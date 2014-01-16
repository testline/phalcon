<?php

namespace Admin\Backend\Controllers;

class IndexController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        if ($this->request->isPost()) {
            $this->view->pick("index/indexContent");
        }
    }

    public function route404Action() {
        if ($this->request->isPost()) {
            die(json_encode(array(
    //            'error' => 'Страница не найдена',
                'html' => 'Страница не найдена',
                'breadCrumbs' => array(),
            )));
        } else {
            die('Страница не найдена');
        }

    }

}