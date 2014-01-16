<?php

namespace Admin\Backend\Plugins;

use \Phalcon\Events\Event;
use \Phalcon\Mvc\Dispatcher;
use \Phalcon\Mvc\User\Plugin;


class Security extends Plugin {

    public function __construct($dependencyInjector) {
        $this->_dependencyInjector = $dependencyInjector;
    }

    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        $user = $this->session->get('user');
        if (!$user) {
            if ($controller != 'login') {
                header('Location: /login' . $url);
                exit();
            }
        } else {

            
           // \Debug::edump($user);
        }
    }

}