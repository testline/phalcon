<?php

namespace Admin\Backend\Models;

class Users extends \Phalcon\Mvc\Model {

    public $id;
    public $users_groups_id;
    public $login;
    public $password;
    public $firstname;
    public $lastname;
    public $active;

    
}