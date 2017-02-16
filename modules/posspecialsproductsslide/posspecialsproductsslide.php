<?php

class posspecialsproductsslide extends Module
{
	private $_html = '';
	private $_postErrors = array();

	function __construct()
	{
		$this->name = 'posspecialsproductsslide';
		$this->tab = 'Modules';
		$this->version = '1.0';
		$this->author = 'posthemes';
		parent::__construct();
		
		$this->displayName = ('Specials products slide');
		$this->description = $this->l('Adds a block displaying your current discounted products');
	}

	function install()
	{
		if (!Configuration::updateValue('SPECIAL_PRODUCTS_NBR', 6) OR !parent::install() OR !$this->registerHook('blockposition2') OR !$this->registerHook('header'))
			return false;
		return true;
	}

	public function getContent()
	{
		$output = '';
		$nbr = (int)Tools::getValue('SPECIAL_PRODUCTS_NBR');
		if (Tools::isSubmit('submitBlockViewed'))
		{
				Configuration::updateValue('SPECIAL_PRODUCTS_NBR', (int)$nbr);
				$output .= $this->displayConfirmation($this->l('Settings updated.'));
		}
		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Products to display'),
						'name' => 'SPECIAL_PRODUCTS_NBR',
						'class' => 'fixed-width-xs',
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
			
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitBlockViewed';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	function hookBlockPosition2($params)
	{
	global $smarty;
    global $cookie;
        $category = new Category(1);
        $nb = (int)Configuration::get('SPECIAL_PRODUCTS_NBR');
		
        $products = Product::getPricesDrop((int)$cookie->id_lang, 0, ((int)$nb ? $nb : 4), false);		
		
		$smarty->assign(array(
			'allow_buy_when_out_of_stock' => Configuration::get('PS_ORDER_OUT_OF_STOCK', false),
			'max_quantity_to_allow_display' => Configuration::get('PS_LAST_QTIES'),
			'category' => $category,
			'products' => $products,
			'currency' => new Currency(intval($params['cart']->id_currency)),
			'lang' => Language::getIsoById(intval($params['cookie']->id_lang)),
			'productNumber' => sizeof($products),
			'homeSize' => Image::getSize('home'),
		));
		return $this->display(__FILE__, 'posspecialsproductsslide.tpl');
	}
	public function getConfigFieldsValues()
	{		
		return array(
			'SPECIAL_PRODUCTS_NBR' => Tools::getValue('SPECIAL_PRODUCTS_NBR', Configuration::get('SPECIAL_PRODUCTS_NBR')),
		);
	}
	public function hookHeader($params)
	{
		$this->context->controller->addCSS(($this->_path).'posspecialsproductsslide.css', 'all');

	}
}
