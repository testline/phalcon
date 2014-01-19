<?php

namespace Admin\Backend\Controllers;
use Admin\Backend\Models\Categories as Categories;
use Admin\Backend\Models\Users as Users;
use Admin\Backend\Models\UsersGroups as UsersGroups;

class CategoriesController extends \Phalcon\Mvc\Controller {

    const  MAX_SUBCATEGORY_LEVEL = 4; // Starting from 0
    public $parentCategory;

    public function indexAction() {
        $categories = Categories::find(array(
            "parent_category_id = 0",
            "order" => "name_ru",
        ));
        $this->view->categories = $categories;
    }

    private function initForm($category) {
        $form = new \QForm('categoryForm', '/admin/categories/save');
        \QFormHidden::setField($form, 'parent_category_id', $this->parentCategory ? $this->parentCategory->id : 0);
        \QFormHidden::setField($form, '_id', $category->id);
        \QFormText::setField($form, 'name_ru', $category->name_ru);
        \QFormText::setField($form, 'name_ua', $category->name_ua);

        $form->setFieldsRules(array(
            'name_ru' => 'required',
            'name_ua' => 'required',
//            'name_ru' => 'required|maxlength[50]|minlength[4]',
//            'name_ua' => 'required|maxlength[50]|minlength[4]',
        ));
        return $form;
    }

    public function editAction($categoryId) {
        $category = Categories::findFirst((int) $categoryId);
        if (!$category)
            die(json_encode(array('error' => 'No such category')));
        $this->view->form = $this->initForm($category);
        $this->view->category = $category;
        $subcategories = Categories::find(array(
            "parent_category_id = $category->id",
            "order" => "name_ru",
        ));
        $this->view->subcategories = $subcategories;
        $this->view->isRootCategory = $category->level == 0;
        $this->view->showSubcategoriesBlock = $category->level < CategoriesController::MAX_SUBCATEGORY_LEVEL;

        $this->view->parentCategory = $category->level > 0 ? Categories::findFirst($category->parent_category_id) : false;

        $this->view->create = false; $this->view->edit = true;
    }

    public function createAction($parentCategoryId) {
        $this->setParentCategory($parentCategoryId);
        $category = new Categories();
        $this->view->form = $this->initForm($category);
        $this->view->create = true; $this->view->edit = false;
        $this->view->parentCategory = $this->parentCategory;
        $this->view->pick("categories/edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id']; $creating = !$updating;
        if ($updating) {
            $category = Categories::findFirst((int) $_POST['_id']);
            if (!$category)
                die(json_encode(array('error' => 'No such category')));
        } else {
            $category = new Categories();
            $parent_category_id = (int) $_POST['parent_category_id'];
            $this->setParentCategory($parent_category_id);
        }
        $form = $this->initForm($category);


        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();


                $fields = array(
                    'name_ru' => $vals['name_ru'],
                    'name_ua' => $vals['name_ua'],
                );
                if ($creating) {
                    $fields['parent_category_id'] = $parent_category_id;
                    $fields['level'] = $this->parentCategory ? $this->parentCategory->level + 1 : 0;
                }

                if (!$category->save($fields))
                    die(json_encode(array('error' => 'Problem on saving')));

                if ($creating && $this->parentCategory) {
                    // Add data to submissions
                    $submissions[] = array('parent_category_id' => $this->parentCategory->id, 'distance' => 1);
                    $parentSubmissions = $this->di->get('db')->query("SELECT * FROM categories_submissions WHERE category_id = {$this->parentCategory->id}")->FetchAll();
                    foreach ($parentSubmissions as $k => $v)
                        $submissions[] = array('parent_category_id' => $v['parent_category_id'], 'distance' => $v['distance'] + 1);
                    foreach ($submissions as $k => $v)
                        $submissions[$k]['category_id'] = $category->id;
                    $q = dbInsertQuery('categories_submissions', $submissions, true);
                    $this->di->get('db')->execute($q);
                }


                if ($updating) {
                    $form->successfulSubmitAction .= "locationHashChanged();";
                    $form->successfulSubmitAction .= "messageBoard('Категория сохранена');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'categories/edit/{$category->id}';";
                    $form->successfulSubmitAction .= "messageBoard('Категория создана');";
                }
                // Update trees with possible new category name
                $form->successfulSubmitAction .= \Admin\Backend\Models\Tree::getInstance()->getUpdateTreesJsAction(true);
            }
            $form->sendAjaxResponse();
        }
    }

    public function deleteAction() {
        $ids = explodeAndSanitize($_POST['ids']);
        if ($ids) {
            foreach ($ids as $categoryId) {
                $category = Categories::findFirst($categoryId);
                if ($category == false)
                    continue;

                $this->di->get('db')->execute("DELETE FROM characteristics_list_values WHERE characteristics_id IN (SELECT id FROM characteristics WHERE categories_id = $categoryId)");
                $this->di->get('db')->execute("DELETE FROM characteristics WHERE categories_id = $categoryId");
                $this->di->get('db')->execute("DELETE FROM categories WHERE id
                    IN (SELECT category_id FROM categories_submissions WHERE parent_category_id = $categoryId)
                    OR id = $categoryId
                        ");
                $this->di->get('db')->execute("DELETE FROM categories_submissions WHERE category_id
                    IN (SELECT category_id FROM (SELECT category_id FROM categories_submissions WHERE parent_category_id = $categoryId) workaround)
                    OR category_id = $categoryId
                        ");
            }
            $out->redirectHash = $category->parent_category_id ? 'categories/edit/' . $category->parent_category_id : 'categories';
            $out->action = \Admin\Backend\Models\Tree::getInstance()->getUpdateTreesJsAction();
        } else $out->error = 'No ids';

        die(json_encode($out));
    }

    private function setParentCategory($parentCategoryId) {
        $parentCategoryId = (int) $parentCategoryId;
        if ($parentCategoryId == 0)
            $this->parentCategory = false;
        else {
            $this->parentCategory = Categories::findFirst($parentCategoryId);
            if (!$this->parentCategory)
                die(json_encode(array('error' => 'No such parent category')));
        }
    }

    public function accessAction($categoryId) {
        $category = Categories::findFirst((int) $categoryId);
        if (!$category)
            die(json_encode(array('error' => 'No such category')));
        $this->view->category = $category;

        $this->view->isRootCategory = $category->level == 0;
        if ($category->level != 0)
            die(json_encode(array('error' => 'Not a parent category')));


        $groupAccessRaw = $this->di->get('db')->query("SELECT * FROM access_groups WHERE record_id = {$category->id} AND type = 'category'")->FetchAll();
        foreach ($groupAccessRaw as $k => $v)
            $groupAccess[$v['group_id']] = $v['access'];
        $userAccessRaw = $this->di->get('db')->query("SELECT * FROM access_users WHERE record_id = {$category->id} AND type = 'category'")->FetchAll();
        foreach ($userAccessRaw as $k => $v)
            $userAccess[$v['user_id']] = $v['access'];

        //\Debug::fb($userAccess, '$userAccess');

        $usersGroupsRaw = UsersGroups::find("id != 1");
        $usersRaw = Users::find("users_groups_id != 1");
        foreach ($usersGroupsRaw as $k => $g) {
            $group = array('name' => $g->name, 'access' => isset($groupAccess[$g->id]) ? $groupAccess[$g->id] : 0, 'model' => $g);
            foreach ($usersRaw as $k2 => $u) {
                if ($u->users_groups_id == $g->id)
                    $group['users'][] = array('access' => isset($userAccess[$u->id]) ? $userAccess[$u->id] : 0, 'model' => $u);
            }
            $usersGroups[] = $group;
        }
        //\Debug::fb($usersGroups, '$usersGroups');

        $this->view->usersGroups = $usersGroups;
        $this->view->access = true;
        $this->view->pick("categories/edit");
        //$this->view->showSubcategoriesBlock = $category->level < CategoriesController::MAX_SUBCATEGORY_LEVEL;

        //$this->view->parentCategory = $category->level > 0 ? Categories::findFirst($category->parent_category_id) : false;

        //$this->view->create = false; $this->view->edit = true;
    }

    public function saveAccessAction($categoryId) {
        $category = Categories::findFirst((int) $categoryId);
        if (!$category)
            die(json_encode(array('error' => 'No such category')));
        $this->view->category = $category;

        $this->view->isRootCategory = $category->level == 0;
        if ($category->level != 0)
            die(json_encode(array('error' => 'Not a parent category')));

        \Debug::fb($_POST);

        $usersGroupsRaw = UsersGroups::find("id != 1");
        $usersRaw = Users::find("users_groups_id != 1");
        foreach ($usersGroupsRaw as $k => $g)
            $groups[] = array('group_id' => $g->id, 'type' => 'category', 'record_id' => $category->id, 'access' => isset($_POST["group_$g->id"]) ? 1 : 0);
        foreach ($usersRaw as $k2 => $u)
            $users[] = array('user_id' => $u->id, 'type' => 'category', 'record_id' => $category->id, 'access' => isset($_POST["user_$u->id"]) ? 1 : 0);
        //\Debug::efb($users, '$users');
        //$q = dbInsertQuery('access_groups', $groups, true);
        //\Debug::efb($q, '$q');
        $this->di->get('db')->execute("DELETE FROM access_groups WHERE record_id = $category->id");
        $this->di->get('db')->execute("DELETE FROM access_users WHERE record_id = $category->id");

        $this->di->get('db')->execute(dbInsertQuery('access_groups', $groups, true));
        $this->di->get('db')->execute(dbInsertQuery('access_users', $users, true));
        die(json_encode(array('message' => 'Доступ обновлён')));

        //\Debug::fb($usersGroups, '$usersGroups');

//        $this->view->usersGroups = $usersGroups;
//        $this->view->access = true;
//        $this->view->pick("categories/edit");
        //$this->view->showSubcategoriesBlock = $category->level < CategoriesController::MAX_SUBCATEGORY_LEVEL;

        //$this->view->parentCategory = $category->level > 0 ? Categories::findFirst($category->parent_category_id) : false;

        //$this->view->create = false; $this->view->edit = true;
    }

}

function dbInsertQuery($table, $inserts, $multiple = false, $type = 'INSERT') { // $type = INSERT | REPLACE
    $fields = array();
    if (!$multiple) {
        $values = array();
        foreach ($inserts as $k => $v) {
            $fields[] = "`$k`";
            $values[] = "'" . mysql_escape_string(trim($v)) . "'";
        }
        $q = "$type INTO `$table` (" . implode(",", $fields) . ") VALUES (" . implode(",", $values) . ")";
    } else {
        foreach ($inserts[0] as $k => $v)
            $fields[] = "`$k`";
        foreach ($inserts as $k => $v) {
            foreach ($v as $k2 => $v2)
                $inserts[$k][$k2] = "'" . mysql_escape_string(trim($v2)) . "'";
            $qValues[] = "(" . implode(",", $inserts[$k]) . ")";
        }
        $q = "$type INTO `$table` (" . implode(",", $fields) . ") VALUES " . implode(",", $qValues) . "";
    }
    return $q;
}