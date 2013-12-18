<?php

class Texts {

    public $texts;

    function Texts() {
        $this->texts = require($_SERVER['DOCUMENT_ROOT'] . "/apps/backend/texts/ru.php");
    }

    public function T($text) {
        return isset($this->texts[$text]) ? $this->texts[$text] : false;
    }

}

?>