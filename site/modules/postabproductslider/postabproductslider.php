<?php

class postabproductslider extends Module {
	var $_postErrors  = array();
	public function __construct() {
		$this->name 		= 'postabproductslider';
		$this->tab 			= 'front_office_features';
		$this->version 		= '1.5';
		$this->author 		= 'posthemes';
		$this->displayName 	= $this->l('Product Tabs Slider');
		$this->description 	= $this->l('Product Tabs Slider');
        
		parent :: __construct();
       
	}
	
	public function install() {

	    Configuration::updateValue($this->name . '_show_new', 1);
        Configuration::updateValue($this->name . '_show_sale', 1);
        Configuration::updateValue($this->name . '_show_feature', 1);
        Configuration::updateValue($this->name . '_show_best', 0);
		Configuration::updateValue($this->name . '_p_limit', 12);
		
        Configuration::updateValue($this->name . '_p_on_row', 4);
		Configuration::updateValue($this->name . '_tab_effect', 'wiggle');
		Configuration::updateValue($this->name . '_items_mobile', 1);
		Configuration::updateValue($this->name . '_min_item', 4);
        Configuration::updateValue($this->name . '_items_desktop', 4);
		Configuration::updateValue($this->name . '_items_desktop_Small', 3);
		Configuration::updateValue($this->name . '_items_tablet',2);
		
		return parent :: install()
			&& $this->registerHook('blockPosition3')
			&& $this->registerHook('header')
			&& $this->registerHook('actionOrderStatusPostUpdate')
			&& $this->registerHook('addproduct')
			&& $this->registerHook('tabsProducts')
			&& $this->registerHook('updateproduct')
			&& $this->registerHook('deleteproduct');
	}

      public function uninstall() {
        $this->_clearCache('productab.tpl');
        return parent::uninstall();
    }

  
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
    
    
    public function hookHeader($params){
        if ($this->psversion()==5){
            /* $this->context->controller->addCSS(($this->_path).'producttab.css', 'all'); */
//			$this->context->controller->addCSS(($this->_path).'animate.delay.css', 'all');
//            $this->context->controller->addCSS(($this->_path).'animate.min.css', 'all');

        } else {
			/* Tools::addCSS(($this->_path).'producttab.css'); */
			//Tools::addCSS(($this->_path).'animate.delay.css');
			//Tools::addCSS(($this->_path).'animate.min.css');

        }
    }
    
    
    // Hook Home
	public function hookblockPosition3($params) {
	        $nb = Configuration::get($this->name . '_p_limit');
			$newProducts = Product::getNewProducts((int) Context::getContext()->language->id, 0, ($nb ? $nb : 5));
			$specialProducts = Product::getPricesDrop((int) Context::getContext()->language->id, 0, ($nb ? $nb : 5));
			ProductSale::fillProductSales();
			$bestseller =  $this->getBestSales ((int) Context::getContext()->language->id, 0, ($nb ? $nb : 5), null,  null);
			$category = new Category(Context::getContext()->shop->getCategory(), (int) Context::getContext()->language->id);
         	$featureProduct = $category->getProducts((int) Context::getContext()->language->id, 0, ($nb ? $nb : 5));

      
			if(!$newProducts) $newProducts = null;
			if(!$bestseller) $bestseller = null;
			if(!$specialProducts) $specialProducts = null;
			
			$productTabslider = array();
			if(Configuration::get($this->name . '_show_new')) {
				$productTabslider[] = array('id'=>'new_product', 'name' => $this->l('New Arrival'), 'productInfo' => $newProducts);
			}
			if(Configuration::get($this->name . '_show_sale')) {
				$productTabslider[] = array('id'=> 'special_product','name' => $this->l('OnSale'), 'productInfo' =>  $specialProducts);
			}
			if(Configuration::get($this->name . '_show_best')) {
				$productTabslider[] = array('id'=>'besseller_product','name' => $this->l('Bestseller'), 'productInfo' =>  $bestseller);
			}
			if(Configuration::get($this->name . '_show_feature')) {
				$productTabslider[] = array('id'=>'feature_product','name' => $this->l('Featured Products'), 'productInfo' =>  $featureProduct);
			}
	
				$options = array(
					'items_mobile' => Configuration::get($this->name . '_items_mobile'),		
					'items_desktop_Small' => Configuration::get($this->name . '_items_desktop_Small'),
					'items_tablet' => Configuration::get($this->name . '_items_tablet'),
					 'show_des' => Configuration::get($this->name . '_show_des'),
					'show_arrow' => Configuration::get($this->name . '_show_arrow'),
					'show_ctr' => Configuration::get($this->name . '_show_ctr'),
					'min_item' => Configuration::get($this->name . '_min_item'),
					'items_desktop' => Configuration::get($this->name . '_items_desktop'),  
					'show_price' => Configuration::get($this->name . '_show_price'),
					
				);

            $this->smarty->assign(array(
                'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
                'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
				'tab_effect' => Configuration::get($this->name . '_tab_effect'),
	
            ));
			$this->context->smarty->assign('productTabslider', $productTabslider);
			$this->context->smarty->assign('slideOptions', $options);
		return $this->display(__FILE__, 'producttabslider.tpl');
	}
	
	  public function getContent() {
        $output = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitPosTabProduct')) {
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

        Configuration::updateValue($this->name . '_show_new', Tools::getValue('show_new'));
        Configuration::updateValue($this->name . '_show_sale', Tools::getValue('show_sale'));
        Configuration::updateValue($this->name . '_show_feature', Tools::getValue('show_feature'));
        Configuration::updateValue($this->name . '_show_best', Tools::getValue('show_best'));
		Configuration::updateValue($this->name . '_items_mobile', Tools::getValue('items_mobile'));
        Configuration::updateValue($this->name . '_p_limit', Tools::getValue('p_limit'));
		Configuration::updateValue($this->name . '_tab_effect', Tools::getValue('tab_effect'));
		
        Configuration::updateValue($this->name . '_items_desktop_Small', Tools::getValue('items_desktop_Small'));
        Configuration::updateValue($this->name . '_items_tablet', Tools::getValue('items_tablet'));
     
        Configuration::updateValue($this->name . '_show_arrow', Tools::getValue('show_arrow'));
        Configuration::updateValue($this->name . '_show_ctr', Tools::getValue('show_ctr'));
        Configuration::updateValue($this->name . '_min_item', Tools::getValue('min_item'));
        Configuration::updateValue($this->name . '_items_desktop', Tools::getValue('items_desktop'));


        $this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
    }
	
	private function _displayForm(){ 
	
	 $tabEffect = array();
		$tabEffect = array(
			'none' => 'None', 
			'hinge' => 'Hinge', 
			'flash' => 'Flash', 
			'shake' => 'Shake',
			'bounce' => 'Bounce',
			'tada' => 'Tada' ,
			'swing' => 'Swing', 
			'wobble' => 'Wobble', 
			'pulse' => 'Pulse', 
			'flip' => 'Flip', 
			'flipInX' => 'FlipInX', 
			'flipInY' => 'FlipInY', 
			'fadeIn' => 'FadeIn', 
			'bounceInUp' => 'BounceInUp', 
			'fadeInLeft' => 'FadeInLeft', 
			'rollIn' => 'RollIn', 
			'lightSpeedIn' => 'LightSpeedIn', 
			'wiggle' => 'Wiggle', 
			'rotateIn' => 'RotateIn', 
			'rotateInUpLeft' => 'RotateInUpLeft', 
			'rotateInUpRight' => 'RotateInUpRight'

		);
         $this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                  <fieldset>
                    <legend><img src="../img/admin/cog.gif" alt="" class="middle" />' . $this->l('Settings') . '</legend>
					<label>'.$this->l('Effect Tab: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml($tabEffect,'tab_effect',   (Tools::getValue('tab_effect') ? Tools::getValue('tab_effect') : Configuration::get($this->name . '_tab_effect')));
                       $this->_html .='
                    </div>
                    
					<label>'.$this->l('Show New Products: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_new',   (Tools::getValue('show_new') ? Tools::getValue('show_new') : Configuration::get($this->name . '_show_new')));
                       $this->_html .='
                    </div>
					
					<label>'.$this->l('Show special Products: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_sale',  (Tools::getValue('show_sale') ? Tools::getValue('show_sale') : Configuration::get($this->name . '_show_sale')));
                       $this->_html .='
                    </div>
					
					<label>'.$this->l('Show Bestselling Products: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_best',  (Tools::getValue('show_best') ? Tools::getValue('show_best') : Configuration::get($this->name . '_show_best')));
                       $this->_html .='
                    </div>
					
					<label>'.$this->l('Show Feature Products: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_feature',  (Tools::getValue('show_feature') ? Tools::getValue('show_feature') : Configuration::get($this->name . '_show_feature')));
                       $this->_html .='
                    </div>
                     <label>'.$this->l('Products Limit: ').'</label>
                    <div class="margin-form">
                            <input type = "text"  name="p_limit" value ='.(Tools::getValue('p_limit')?Tools::getValue('p_limit'): Configuration::get($this->name.'_p_limit')).' ></input>
                    </div>
                    <input type="submit" name="submitPosTabProduct" value="'.$this->l('Update').'" class="button" />
                     </fieldset>
		</form>';
		return $this->_html;
	}

	private function _installHookCustomer(){
		$hookspos = array(
				'tabsProducts',
			); 
		foreach( $hookspos as $hook ){
			if( Hook::getIdByName($hook) ){
				
			} else {
				$new_hook = new Hook();
				$new_hook->name = pSQL($hook);
				$new_hook->title = pSQL($hook);
				$new_hook->add();
				$id_hook = $new_hook->id;
			}
		}
		return true;
	}
	public static function getBestSales($id_lang, $page_number = 0, $nb_products = 10, $order_by = null, $order_way = null)
	{
		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;
		$final_order_by = $order_by;
		$order_table = ''; 		
		if (is_null($order_by) || $order_by == 'position' || $order_by == 'price') $order_by = 'sales';
		if ($order_by == 'date_add' || $order_by == 'date_upd')
			$order_table = 'product_shop'; 				
		if (is_null($order_way) || $order_by == 'sales') $order_way = 'DESC';
		$groups = FrontController::getCurrentCustomerGroups();
		$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
		$interval = Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20;
		
		$prefix = '';
		if ($order_by == 'date_add')
			$prefix = 'p.';
		
		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
					pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`,
					pl.`meta_keywords`, pl.`meta_title`, pl.`name`,
					m.`name` AS manufacturer_name, p.`id_manufacturer` as id_manufacturer,
					MAX(image_shop.`id_image`) id_image, il.`legend`,
					ps.`quantity` AS sales, t.`rate`, pl.`meta_keywords`, pl.`meta_title`, pl.`meta_description`,
					DATEDIFF(p.`date_add`, DATE_SUB(NOW(),
					INTERVAL '.$interval.' DAY)) > 0 AS new
				FROM `'._DB_PREFIX_.'product_sale` ps
				LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
				'.Shop::addSqlAssociation('product', 'p', false).'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`)
					AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
					AND tr.`id_state` = 0
				LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
				'.Product::sqlStock('p').'
				WHERE product_shop.`active` = 1
					AND product_shop.`visibility` != \'none\'
					AND p.`id_product` IN (
						SELECT cp.`id_product`
						FROM `'._DB_PREFIX_.'category_group` cg
						LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
						WHERE cg.`id_group` '.$sql_groups.'
					)
				GROUP BY product_shop.id_product
				ORDER BY '.(!empty($order_table) ? '`'.pSQL($order_table).'`.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way).'
				LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if ($final_order_by == 'price')
			Tools::orderbyPrice($result, $order_way);
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}

}