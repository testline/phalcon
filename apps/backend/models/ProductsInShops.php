<?php

namespace Admin\Backend\Models;

class ProductsInShops extends \Phalcon\Mvc\Model {

    public $id;
    public $products_id;
    public $shops_id;
    public $price;
    public $rebate;
    public $available;
    public $availability_updated_date;

}