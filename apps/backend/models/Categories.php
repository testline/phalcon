<?php

namespace Admin\Backend\Models;

class Categories extends \Phalcon\Mvc\Model {

    public $id;
    public $name_ru;
    public $name_ua;
    public $parent_category_id;
    public $level;

    public function getParentCategoriesBreadCrumbs($categoryId) {
        $result = $this->di->get('db')->query("SELECT name_ru, id FROM categories,
                                                                        (SELECT parent_category_id, distance FROM categories_submissions WHERE category_id = $categoryId) hierarchy
                                                        WHERE categories.id = hierarchy.parent_category_id
                                                        ORDER BY hierarchy.distance DESC")->fetchAll();
        $breadCrumbs = array();
        foreach ($result as $k => $v)
            $breadCrumbs[] = array('title'=> $v['name_ru'], 'url' => '#categories/edit/' . $v['id']);
        return $breadCrumbs;
    }




}