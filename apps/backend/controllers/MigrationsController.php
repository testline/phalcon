<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Migrations as Migrations;

class MigrationsController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        $r = $this->di->get('db')->query("SHOW TABLES LIKE 'migrations'")->fetchAll();
        if (!sizeof($r))
            $this->di->get('db')->query("CREATE TABLE `migrations` (
            `id` int( 11 ) NOT NULL AUTO_INCREMENT ,
            `date` datetime DEFAULT NULL ,
            `file` varchar( 200 ) COLLATE utf8_unicode_ci DEFAULT NULL ,
            PRIMARY KEY ( `id` )
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci");


        $path = $_SERVER["DOCUMENT_ROOT"] . "/migrations";
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ('.' === $file)
                    continue;
                if ('..' === $file)
                    continue;

                $e = explode('.', $file);
                if ($e[sizeof($e) - 1] == 'php')
                    $files[] = $e[0];
            }
            sort($files);




            $executedMigrations = array();
            $executedMigrationsRaw = Migrations::find();
            foreach ($executedMigrationsRaw as $k => $v)
                $executedMigrations[] = $v->file;

            $newMigrationsExecutedCount = 0;
            foreach ($files as $k => $migration) {
                if (!in_array($migration, $executedMigrations)) {
                    echo "<br><b>$migration.php</b><br>";
                    require_once $_SERVER["DOCUMENT_ROOT"] . "/migrations/$migration.php";
                    $migration::migrate($this->di);

                    $m = new Migrations();
                    $m->save(array('file' => $migration, 'date' => date("Y-m-d H:i:s")));
                    $newMigrationsExecutedCount ++;
                }
            }
            closedir($handle);
        }
        echo "<br><br><b>Finished. Migrations executed: $newMigrationsExecutedCount</b>";
    }

}