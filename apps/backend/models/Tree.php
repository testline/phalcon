<?php
namespace Admin\Backend\Models;
class Tree {

    private $type;

    public static function getInstance() {
        return new self();
    }

    public function getCategoriesForTree($type = 'products | categories') {
        $this->type = $type;
        // Get all categories
        $this->categories =  \Phalcon\DI::getDefault()->get('db')->query("SELECT * FROM categories ORDER BY level, name_ru")->fetchAll();
        $this->treeData = array();
        // Recursively build tree
        foreach ($this->categories as $k => $v) {
            if ($v['level'] == 0) { // Start from roots
                $this->treeData[] = $v; // Add root to tree structure
                unset($this->categories[$k]); // Unset parsed node for speed
                $this->buildSubtree(&$this->treeData[sizeof($this->treeData) - 1]); // Pass link to added node
            } else break; // Roots are in the beginning of the array (ORDER BY level)
        }
        // Turn tree structure into required format by js plugin
        $this->treeDataJson = array();
        foreach ($this->treeData as $k => $v)
            $this->treeDataJson[] = $this->generateJsonForSubtree(&$this->treeData[$k]);
        return json_encode($this->treeDataJson);
    }
    public function buildSubtree($node) {
        foreach ($this->categories as $k => $v) { // Loop throught all categories
            if ($v['parent_category_id'] == $node['id']) { // If category's parent is $node, add to tree structure
                $node['children'][] = $v;
                unset($this->categories[$k]); // Unset parsed node for speed
                $this->buildSubtree(&$node['children'][sizeof($node['children']) - 1]); // Build from recently added
            }
        }
    }
    public function generateJsonForSubtree($node) {
        $out->name = $node['name_ru'];
        $out->target = '_self';
        if ($this->type == 'products')
            $out->url = '#products/index/' . $node['id'];
        else $out->url = '#categories/edit/' . $node['id'];

        // Recursively loop through children
        if ($node['children'])
            foreach ($node['children'] as $k => $v)
                $out->children[] = $this->generateJsonForSubtree(&$node['children'][$k]);
        return $out;
    }

    public function getUpdateTreesJsAction($useCDATA = false) {
        $treeNodesProducts = $this->getCategoriesForTree('products');
        $treeNodesCategories = $this->getCategoriesForTree('categories');
        $out = "initializeTree('Products', $treeNodesProducts);";
        $out .= "initializeTree('Categories', $treeNodesCategories);";
        if ($useCDATA)
            $out = "<![CDATA[$out]]>";
        return $out;
    }

}

?>