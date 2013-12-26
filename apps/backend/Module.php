<?php

namespace Admin\Backend;

class Module
{

	public function registerAutoloaders()
	{

		$loader = new \Phalcon\Loader();

		$loader->registerNamespaces(array(
			'Admin\Backend\Controllers' => '../apps/backend/controllers/',
			'Admin\Backend\Models' => '../apps/backend/models/',
			'Admin\Backend\Plugins' => '../apps/backend/plugins/',
		));

		$loader->register();
	}

	/**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	public function registerServices($di)
	{
		//Registering a dispatcher
		$di->set('dispatcher', function() {
			$dispatcher = new \Phalcon\Mvc\Dispatcher();

			//Attach a event listener to the dispatcher
			$eventManager = new \Phalcon\Events\Manager();
			$eventManager->attach('dispatch', new \Acl('backend'));
                        $eventManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception) {
                            //Handle 404 exceptions
                            if ($exception instanceof \Phalcon\Mvc\Dispatcher\Exception) {
                                $dispatcher->forward(array(
                                    'controller' => 'index',
                                    'action' => 'route404'
                                ));
                                return false;
                            }

                            //Handle other exceptions
//                            $dispatcher->forward(array(
//                                'controller' => 'index',
//                                'action' => 'show503'
//                            ));
                            \Debug::fb($exception->getMessage(), '$exception->getMessage()');
                            \Debug::fb($exception->getTraceAsString(), '$exception->getTraceAsString()');
                            die(json_encode(array('error' => $exception->getMessage())));
                            return false;
                        });

			$dispatcher->setEventsManager($eventManager);
			$dispatcher->setDefaultNamespace("Admin\Backend\Controllers\\");
			return $dispatcher;
		});

		//Registering the view component
		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir('../apps/backend/views/');
			return $view;
		});

                $di->set('tag', function() {
                        require $_SERVER["DOCUMENT_ROOT"] . '/apps/backend/views/Helper.php';
                        return new \Helper();
		});


	}

}