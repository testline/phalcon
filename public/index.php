<?php

ini_set('display_errors', true);
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('memory_limit', '128M');
ini_set('max_execution_time', '15');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('php_value default_charset', 'utf-8');

class Application extends \Phalcon\Mvc\Application
{

	/**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	protected function _registerServices()
	{
                $config = new Phalcon\Config\Adapter\Ini(__DIR__ . '/../apps/config/config.ini');


		$di = new \Phalcon\DI\FactoryDefault();

                $di->set('session', function() {
                    $session = new Phalcon\Session\Adapter\Files();
                    $session->start();
                    return $session;
                });

		$loader = new \Phalcon\Loader();
		$loader->registerDirs(
			array(
				__DIR__ . '/../apps/library/',
                                __DIR__ . '/../apps/library/QForm/',
			)
		)->register();

                $di->set('db', function() use ($config) {
                        return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                                "host" => $config->database->host,
                                "username" => $config->database->username,
                                "password" => $config->database->password,
                                "dbname" => $config->database->name
                        ));
                });

                $di->set('texts', function() {
                        return new \Texts();
                });



		//Registering a router
		$di->set('router', function(){

			$router = new \Phalcon\Mvc\Router();
                        $router->removeExtraSlashes(true);
                        $router->setDefaultModule("backend");

                        //Set 404 path
//                        $router->add("/:action", array(
//                            "module" => "backend",
//                            "controller" => "index",
//                            "action" => "route404"
//                        ));
                        $router->add("/admin", array(
				'module' => 'backend',
				'controller' => 'index',
                                'action' => 'index',
			));
                        $router->add("/admin/:controller", array(
				'module' => 'backend',
				'controller' => 1,
                                'action' => 'index',
			));
                        $router->add("/admin/:controller/:action", array(
				'module' => 'backend',
				'controller' => 1,
				'action' => 2,
			));

                        $router->add("/admin/:controller/:action/:params", array(
				'module' => 'backend',
				'controller' => 1,
				'action' => 2,
                                "params"     => 3,
			));

			return $router;
		});

		$this->setDI($di);
	}

	public function main()
	{
		$this->_registerServices();
		//Register the installed modules
		$this->registerModules(array(
			'backend' => array(
				'className' => 'Admin\Backend\Module',
				'path' => '../apps/backend/Module.php'
			)
		));
		echo $this->handle()->getContent();
	}

}

$application = new Application();
$application->main();


function explodeAndSanitize($string, $sanitizeType = 'int') {
    $out = false;
    if ($string) {
        $out = explode(',', $string);
        if ($out)
            foreach ($out as $k => $v)
                $out[$k] = (int) $v;
    }
    return $out;
}