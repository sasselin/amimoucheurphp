<?php

if (!defined('_PS_VERSION_'))
    exit;

class Posfeatureproduct extends Module {

    private $_html = '';
    private $_postErrors = array();

    function __construct() {
        $this->name = 'posfeatureproduct';
        $this->tab = 'front_office_features';
        $this->version = '1.1';
        $this->author = 'Posthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        parent::__construct();

        $this->displayName = $this->l('Featured products with slider on the homepage.');
        $this->description = $this->l('Displays featured products in any where of your homepage.');
    }

    function install() {
        $this->_clearCache('posfeatureproduct.tpl');
        Configuration::updateValue('POSFEATUREPRODUCT', 8);
        Configuration::updateValue($this->name . '_qty_products', 9);

        if (!parent::install()
                || !$this->registerHook('header')
                || !$this->registerHook('blockposition3')
                || !$this->registerHook('addproduct')
                || !$this->registerHook('updateproduct')
                || !$this->registerHook('deleteproduct')
        )
            return false;
        return true;
    }

    public function uninstall() {
        Configuration::deleteByName($this->name . '_qty_products');

        $this->_clearCache('posfeatureproduct.tpl');
        return parent::uninstall();
    }

    public function getContent() {
        $output = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitPostFeaturedProduct')) {
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        }
        return $output . $this->_displayForm();
    }

    public function getSelectOptionsHtml($options = NULL, $name = NULL, $selected = NULL) {
        $html = "";
        $html .='<select name =' . $name . ' style="width:130px">';
        if (count($options) > 0) {
            foreach ($options as $key => $val) {
                if (trim($key) == trim($selected)) {
                    $html .='<option value=' . $key . ' selected="selected">' . $val . '</option>';
                } else {
                    $html .='<option value=' . $key . '>' . $val . '</option>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }

    private function _postProcess() {
        Configuration::updateValue($this->name . '_qty_products', Tools::getValue('qty_products'));



        $this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
    }

    private function _displayForm() { 
        $this->_html .= '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                  <fieldset>
                    <legend><img src="../img/admin/cog.gif" alt="" class="middle" />' . $this->l('Settings') . '</legend>';
					$this->_html .='
                     <label>' . $this->l('Qty of Products: ') . '</label>
                    <div class="margin-form">
                            <input type = "text"  name="qty_products" value =' . (Tools::getValue('qty_products') ? Tools::getValue('qty_products') : Configuration::get($this->name . '_qty_products')) . ' ></input>
                    </div>
                    <input type="submit" name="submitPostNewProduct" value="' . $this->l('Update') . '" class="button" />
                     </fieldset>
		</form>';
        return $this->_html;
    }

    public function hookDisplayHeader($params) {
        $this->hookHeader($params);
    }

    public function hookHeader($params) {
       // $this->context->controller->addCSS(($this->_path) . 'css/posfeatureproduct.css', 'all');
//                $this->context->controller->addJS($this->_path.'js/modernizr.custom.17475.js');
//                $this->context->controller->addJS($this->_path.'js/jquerypp.custom.js');
//                $this->context->controller->addJS($this->_path.'js/jquery.elastislide.js');
        $this->context->controller->addJS($this->_path . 'js/pos.bxslider.min.js');
    }

    public function getSlideshowHtml() {

        if (!$this->isCached('posfeatureproduct.tpl', $this->getCacheId('posfeatureproduct'))) {
            $slideOptions = array(
                'qty_products' => Configuration::get($this->name . '_qty_products'),
            );
            //echo "<pre>"; print_r($slideOptions);
            $category = new Category(Context::getContext()->shop->getCategory(), (int) Context::getContext()->language->id);
            $nb = (int) Configuration::get($this->name . '_qty_products');
            $products = $category->getProducts((int) Context::getContext()->language->id, 1, ($nb ? $nb : 8));
            if(!$products) return ;
            $this->smarty->assign(array(
                'products' => $products,
                'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
                'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
                'slideOptions' => $slideOptions
            ));
        }
        return $this->display(__FILE__, 'posfeatureproduct.tpl', $this->getCacheId('posfeatureproduct'));
    }

    public function hookBlockPosition3($params) {
        return $this->getSlideshowHtml();
    }
	
	public function hookDisplayHome($params) {
        return $this->getSlideshowHtml();
    }

    public function hookAddProduct($params) {
        $this->_clearCache('posfeatureproduct.tpl');
    }

    public function hookUpdateProduct($params) {
        $this->_clearCache('posfeatureproduct.tpl');
    }

    public function hookDeleteProduct($params) {
        $this->_clearCache('posfeatureproduct.tpl');
    }

}
