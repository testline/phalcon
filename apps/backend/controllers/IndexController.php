<?php

namespace Admin\Backend\Controllers;

class IndexController extends \Phalcon\Mvc\Controller {

    public function indexAction() {

    }

    public function route404Action() {
        die(json_encode(array('error' => 'Страница не найдена')));
    }

}