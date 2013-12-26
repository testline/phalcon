<?php

namespace Admin\Backend\Controllers;

use Admin\Backend\Models\Products as Products;
use Admin\Backend\Models\Categories as Categories;

class ProductsController extends \Phalcon\Mvc\Controller {

    public function indexAction($categoryId = 0) {
        // Create filter for subcategories
        $filters = array();
        $categoryId = (int) $categoryId;
        if ($categoryId != 0) {
            $subcategories = $this->di->get('db')->query("SELECT category_id FROM categories_submissions WHERE parent_category_id = $categoryId")->fetchAll();
            $subcategoriesIds = array($categoryId);
            foreach ($subcategories as $k => $v)
                $subcategoriesIds[] = $v['category_id'];
            $filters[] = 'categories_id IN (' . implode(',', $subcategoriesIds) . ')';
        }

        $products = Products::find($filters);
        $this->view->products = $products;
        $this->view->categoryId = $categoryId;
    }

    private function initForm($product, $category = false) { // $category is set on creating
        $form = new \QForm('productForm', '/admin/products/save');
        \QFormHidden::setField($form, 'category_id', $category ? $category->id : 0);
        \QFormHidden::setField($form, '_id', $product->id);

        // Texts fields
        \QFormText::setField($form, 'code', $product->code);
        \QFormText::setField($form, 'name_r2', $product->name_r2);
        \QFormText::setField($form, 'name_original', $product->name_original);
        \QFormText::setField($form, 'name_ru', $product->name_ru);
        \QFormText::setField($form, 'name_ua', $product->name_ua);
        \QFormText::setField($form, 'price', $product->price);
        \QFormText::setField($form, 'weight', $product->weight);
        \QFormText::setField($form, 'length', $product->length);
        \QFormText::setField($form, 'width', $product->width);
        \QFormText::setField($form, 'height', $product->height);

        // Text areas
        \QFormTextArea::setField($form, 'short_description', $product->short_description);
        \QFormTextArea::setField($form, 'long_description', $product->long_description);

        // Selects
        $selects = array(
            'brands_id',
            'collections_id',
            'measures_id',
            'colors_id',
            'materials_id',
            'surfaces_id',
            'appliances_id',
            'styles_id',
            'forms_id',
            'installments_id',
            'sets_id',
        );
        foreach ($selects as $k => $v) {
            $selectValues = array();
            $r = $this->di->get('db')->query("SELECT * FROM " . substr($v, 0, -3))->fetchAll();
            foreach ($r as $v2)
                $selectValues[$v2['id']] = $v2['name'];

            \QFormSelectBox::setField($form, $v, array($selectValues, ''));
            if ($product->{$v})
                $form->getFieldByName($v)->selected_value = $product->{$v};
        }



        $form->setFieldsRules(array(
            'code' => '',
            'name_r2' => '',
            'name_original' => 'required',
            'name_ru' => '',
            'name_ua' => '',
            'price' => '',
            'weight' => '',
            'length' => '',
            'width' => '',
            'height' => '',
            'short_description' => '',
            'long_description' => '',
            //'collections_id'  => '',
            'brands_id' => '',
            'measures_id' => '',
            'colors_id' => '',
            'materials_id' => '',
            'surfaces_id' => '',
            'appliances_id' => '',
            'styles_id' => '',
            'forms_id' => '',
            'installments_id' => '',
            'sets_id' => '',
        ));
        return $form;
    }

    public function editAction($productId) {
        $product = Products::findFirst((int) $productId);
        if (!$product)
            die(json_encode(array('error' => 'No such product')));
        $this->view->form = $this->initForm($product);
        $this->view->product = $product;
        $this->view->categoryId = $product->categories_id;
        $this->view->create = false;
        $this->view->edit = true;
    }

    public function createAction($categoryId) {
        $category = $this->getCategory($categoryId);
        $product = new Products();
        $this->view->form = $this->initForm($product, $category);
        $this->view->create = true;
        $this->view->edit = false;
        $this->view->categoryId = $category->id;
        $this->view->pick("products/edit");
    }

    public function saveAction() {
        $updating = (bool) $_POST['_id'];
        $creating = !$updating;
        if ($updating) {
            $product = Products::findFirst((int) $_POST['_id']);
            if (!$product)
                die(json_encode(array('error' => 'No such product')));
            $category = false;
        } else {
            $product = new Products();
            $category = $this->getCategory((int) $_POST['category_id']);
        }
        $form = $this->initForm($product);


        if ($form->is_submitted) {
            if ($form->validate()) {
                $vals = $form->getPostValues();


                $fields = array(
                    'code' => $vals['code'],
                    'name_r2' => $vals['name_r2'],
                    'name_original' => $vals['name_original'],
                    'name_ru' => $vals['name_ru'],
                    'name_ua' => $vals['name_ua'],
                    'price' => $vals['price'],
                    'weight' => $vals['weight'],
                    'length' => $vals['length'],
                    'width' => $vals['width'],
                    'height' => $vals['height'],
                    'short_description' => $vals['short_description'],
                    'long_description' => $vals['long_description'],
                    //'collections_id'  => $vals['collections_id'],
                    'brands_id' => $vals['brands_id'],
                    'measures_id' => $vals['measures_id'],
                    'colors_id' => $vals['colors_id'],
                    'materials_id' => $vals['materials_id'],
                    'surfaces_id' => $vals['surfaces_id'],
                    'appliances_id' => $vals['appliances_id'],
                    'styles_id' => $vals['styles_id'],
                    'forms_id' => $vals['forms_id'],
                    'installments_id' => $vals['installments_id'],
                    'sets_id' => $vals['sets_id'],
                );
                if ($creating) {
                    $fields['categories_id'] = $category->id;
                }

                if (!$product->save($fields))
                    die(json_encode(array('error' => 'Problem on saving')));

                if ($updating) {
                    $form->successfulSubmitAction .= "locationHashChanged();";
                    $form->successfulSubmitAction .= "messageBoard('Товар сохранен');";
                } else {
                    $form->successfulSubmitAction .= "location.hash = 'products/edit/{$product->id}';";
                    $form->successfulSubmitAction .= "messageBoard('Товар создан');";
                }
            }
            $form->sendAjaxResponse();
        }
    }

    private function getCategory($categoryId) {
        $categoryId = (int) $categoryId;
        $category = Categories::findFirst($categoryId);
        if (!$category)
            die(json_encode(array('error' => 'No such category')));
        return $category;
    }

}