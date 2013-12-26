<?php

namespace Admin\Backend\Models;

class Categories extends \Phalcon\Mvc\Model {

    public $id;
    public $name_ru;
    public $name_ua;
    public $parent_category_id;
    public $level;

    public function getBreadCrumbs($categoryId, $whatFor = 'For Category') {
        $categoryId = (int) $categoryId;
        $q = "SELECT name_ru, id FROM categories,
                                            (
                                                SELECT parent_category_id, distance FROM categories_submissions WHERE category_id = $categoryId
                                                 UNION
                                                SELECT $categoryId parent_category_id, 0 distance
                                            ) parent_categories
                            WHERE categories.id = parent_categories.parent_category_id
                            ORDER BY parent_categories.distance DESC";
        if ($whatFor == 'For Category') {
//            $q = "SELECT name_ru, id FROM categories,
//                                                (SELECT parent_category_id, distance FROM categories_submissions WHERE category_id = $categoryId) parent_categories
//                                WHERE categories.id = parent_categories.parent_category_id
//                                ORDER BY parent_categories.distance DESC";
            $url = '#categories/edit/';
        } else { // For Product
            $url = '#products/index/';
        }
        $result = $this->di->get('db')->query($q)->fetchAll();

        $breadCrumbs = array();
        foreach ($result as $k => $v)
            $breadCrumbs[] = array('title'=> $v['name_ru'], 'url' => $url . $v['id']);
        return $breadCrumbs;
    }




}