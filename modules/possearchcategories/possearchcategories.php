<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
if (!defined('_PS_VERSION_'))
	exit;
	//print_r(_PS_VERSION_);die;
class Possearchcategories extends Module
{
	private $spacer_size = '1';
	protected static $js_def = array();
	public function __construct()
	{
		$this->name = 'possearchcategories';
		$this->tab = 'Search and filter';
		$this->version = 1.6;
		$this->author = 'Posthemes';
		$this->need_instance = 0;
		$this->bootstrap =true ;
		parent::__construct();
		$this->displayName = $this->l('Quick search categories ');
		$this->description = $this->l('Adds a quick search field categories to your website.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}
	public function install()
	{
		if (!parent::install() || !$this->registerHook('top') || !$this->registerHook('header')
			||!Configuration::updateValue('CATE_ON',1))
			return false;
		return true;
	}

	public function uninstall(){
		Configuration::deleteByName('CATE_ON');
		return parent::uninstall();
	}

	public function getContent(){
		if(Tools::isSubmit('submitUpdate')){
			Configuration::UpdateValue('CATE_ON',
				Tools::getValue('CATE_ON'));
			$this->html = $this->displayConfirmation($this->l('Settings updated successfully.'));
		}
		$this->html .= $this->renderForm();
		return $this->html;

	}

	public function renderForm(){
		if (version_compare(_PS_VERSION_,'1.6','<')) {
			$fields_form = array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Settings'),
						'icon' => 'icon-cogs'
					),
					'input' => array(
						array(
							'type'      => 'radio',
							'class'     => 't',
							'label'     => $this->l('Enable list categories'),
							'desc'      => $this->l('Would you like show  categories ?'),
							'name'      => 'CATE_ON',
							'is_bool'   => true,
							'values'    => array(
								array(
									'id'    => 'active_on',
									'value' => 1,
									'label' => $this->l('Enabled')
								),
								array(
									'id'    => 'active_off',
									'value' => 0,
									'label' => $this->l('Disabled')
								)
							),
						),
					),
					'submit' => array(
						'title' => $this->l('Save'),
					),
				),
			);

		}
		if (version_compare(_PS_VERSION_,'1.6','>=')) {
			$fields_form = array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Settings'),
						'icon' => 'icon-cogs'
					),
					'input' => array(
						array(
							'type'      => 'switch',
							'label'     => $this->l('Enable list categories'),
							'desc'      => $this->l('Would you like show  categories ?'),
							'name'      => 'CATE_ON',
							'values'    => array(
								array(
									'id'    => 'active_on',
									'value' => 1,
									'label' => $this->l('Enabled')
								),
								array(
									'id'    => 'active_off',
									'value' => 0,
									'label' => $this->l('Disabled')
								)
							),
						),
					),
					'submit' => array(
						'title' => $this->l('Save'),
					),
				),
			);
		}
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitUpdate';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'CATE_ON' => Tools::getValue('CATE_ON', Configuration::get('CATE_ON'))
		);
	}
	public function hookHeader($params)
	{
		if (Configuration::get('PS_SEARCH_AJAX'))
			$this->context->controller->addJqueryPlugin('autocomplete');
		$this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
		$this->context->controller->addCSS(($this->_path).'possearch.css', 'all');
		$this->context->controller->addCSS(($this->_path).'bootstrap-select.css', 'all');
		$this->context->controller->addJS(($this->_path).'bootstrap-select.js', 'all');
		if (Configuration::get('PS_SEARCH_AJAX'))
		{
			$this->addJsDef(array('search_url' => $this->context->link->getPageLink('search', Tools::usingSecureMode())));
		}
	}
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	public function hookRightColumn($params)
	{

		if (Tools::getValue('search_query') || !$this->isCached('possearch.tpl', $this->getCacheId()))
		{
			$this->calculHookCommon($params);
			$this->smarty->assign(array(
				'possearch_type' => 'block',
				'search_query' => (string)Tools::getValue('search_query')
				)
			);
		}
		$this->addJsDef(array('possearch_type' => 'block'));
	//	Media::addJsDef(array('possearch_type' => 'block'));
		return $this->display(__FILE__, 'possearch.tpl', Tools::getValue('search_query') ? null : $this->getCacheId());
	}

	public function hookTop($params)
	{
		global $cookie ;
		$key = $this->getCacheId('blocksearch-top');
		$categories = $this->getCategories((int)($cookie->id_lang) ) ;
		$cate_on = (int)Configuration::get('CATE_ON');
		$categories_option = $this->getCategoryOption(1, (int)$cookie->id_lang, (int) Shop::getContextShopID());
		if (Tools::getValue('search_query') || !$this->isCached('possearch-top.tpl', $key))
		{
			$this->calculHookCommon($params);
			$this->smarty->assign(array(
				'possearch_type' => 'top',
				'cate_on' =>$cate_on,
				'categories_option'=>$categories_option,
				'categories' =>$categories,
				'search_query' => (string)Tools::getValue('search_query')
				)
			);
		}
		$this->addJsDef(array('possearch_type' => 'block'));
	//	Media::addJsDef(array('possearch_type' => 'top'));
		return $this->display(__FILE__, 'possearch-top.tpl', Tools::getValue('search_query') ? null : $key);
	}

	private function getCategoryOption($id_category = 1, $id_lang = false, $id_shop = false, $recursive = true) {
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
		$category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);
		if (is_null($category->id))
			return;
		if ($recursive)
		{
			$children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
			if($category->level_depth=='2'){
				$spacer='';
			}else{
				$spacer = str_repeat('-', $this->spacer_size * (int)$category->level_depth);
			}

		}
		$shop = (object) Shop::getShop((int)$category->getShopID());
		if($category->name!='Root' && $category->name!='Home'){
			$this->_html .= '<option value="'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.' </option>';
		}
			if (isset($children) && count($children))
			foreach ($children as $child)
				$this->getCategoryOption((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
		return $this->_html;
	}

	public static function getCategories($id_lang = false, $active = true, $order = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
	{
		if (!Validate::isBool($active))
			die(Tools::displayError());
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'category` c
			'.Shop::addSqlAssociation('category', 'c').'
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
			WHERE 1 '.$sql_filter.' '.($id_lang ? 'AND `id_lang` = '.(int)$id_lang : '').'
			'.($active ? 'AND `active` = 1' : '').'
			'.(!$id_lang ? 'GROUP BY c.id_category' : '').'
			'.($sql_sort != '' ? $sql_sort : 'ORDER BY c.`level_depth` ASC, category_shop.`position` ASC').'
			'.($sql_limit != '' ? $sql_limit : '')
		);
		if (!$order)
			return $result;
		$categories = array();
		foreach ($result as $row)
			$categories[$row['id_parent']][$row['id_category']]['infos'] = $row;
		return $categories;
	}
	public function hookDisplayNav($params)
	{
		return $this->hookTop($params);
	}
	private function calculHookCommon($params)
	{
		$this->smarty->assign(array(
			'ENT_QUOTES' =>		ENT_QUOTES,
			'search_ssl' =>		Tools::usingSecureMode(),
			'ajaxsearch' =>		Configuration::get('PS_SEARCH_AJAX'),
			'instantsearch' =>	Configuration::get('PS_INSTANT_SEARCH'),
			'self' =>			dirname(__FILE__),
		));
		return true;
	}
	public  function addJsDef($js_def)
	{
		if (is_array($js_def))
			foreach ($js_def as $key => $js)
				Possearchcategories::$js_def[$key] = $js;
		elseif ($js_def)
			Possearchcategories::$js_def[] = $js_def;
	}
}

