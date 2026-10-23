<?php
/**
 * StorePrestaModules SPM LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 /*
 * 
 * @author    StorePrestaModules SPM
 * @category content_management
 * @package blocknewsplus
 * @copyright Copyright StorePrestaModules SPM
 * @license   StorePrestaModules SPM
 */

class blocknewsplus extends Module
{
	private $_step = 10;
	private $_is15;
	private $_is16;
	private $_is_friendly_url;
	private $_iso_lng;
    private $_is_cloud;
	
	
	public function __construct()
	{
		$this->name = 'blocknewsplus';
		$this->tab = 'content_management';
		$this->version = '1.2.6';
		$this->author = 'SPM';
		$this->module_key = '6b8e75daca14a7999eee29b0ba2b2018';
		$this->confirmUninstall = $this->l('Are you sure you want to remove it ? Be careful, all your configuration and your data will be lost');
		
		
		require_once(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		
		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('News');
		$this->description = $this->l('Add News');

        if (defined('_PS_HOST_MODE_'))
            $this->_is_cloud = 1;
        else
            $this->_is_cloud = 0;

        // for test
        //$this->_is_cloud = 1;
        // for test


        if($this->_is_cloud){
            $this->path_img_cloud = "modules/".$this->name."/upload/";
        } else {
            $this->path_img_cloud = "upload/".$this->name."/";

        }
		
		if(version_compare(_PS_VERSION_, '1.6', '>')){
 	 		$this->bootstrap = false;
 	 	}
 	 	
 	 	
		if(version_compare(_PS_VERSION_, '1.5', '>')){
			$this->_is15 = 1;
		} else {
			$this->_is15 = 0;
		}
		
		if(version_compare(_PS_VERSION_, '1.6', '>')){
 	 		$this->_is16 = 1;
 	 	} else {
 	 		$this->_is16 = 0;
 	 	}
 	 	
 	 	include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj = new blocknewsplushelp();
		$is_friendly_url = $obj->isURLRewriting();
		$this->_is_friendly_url = $is_friendly_url;
		$this->_iso_lng = $obj->getLangISO();
		
	
		
		$this->initContext();
		
	}

	private function initContext()
	{

		$this->context = Context::getContext();
		
		if (version_compare(_PS_VERSION_, '1.5', '>')){
			$this->context->currentindex = AdminController::$currentIndex;
		} else {

            $variables14 = variables_blocknewsplus14();
            $this->context->currentindex = $variables14['currentindex'];

		
		}
		
	
	}

    public function getCloudImgPath(){
        return $this->path_img_cloud;
    }
	
	public function install()
	{
		
		if (!parent::install())
			return false;

		if($this->_is16 == 1){
			Configuration::updateValue($this->name.'news_home', 1);
			Configuration::updateValue($this->name.'news_footer', 1);
		
		}
		Configuration::updateValue($this->name.'news_left', 1);
		
		
		Configuration::updateValue($this->name.'faq_blc', 5);
		Configuration::updateValue($this->name.'perpage_posts', 5);
		
		Configuration::updateValue($this->name.'news_h', 1);
		
		
		$this->generateRewriteRules();
		
		if($this->_is15 == 1)
	 		$this->createAdminTabs();
	 	else
	 		$this->createAdminTabs14();
		
		if (!$this->registerHook('leftColumn') 
			OR !$this->registerHook('rightColumn')
			OR !$this->registerHook('Header') 
			OR !$this->registerHook('home')
			OR !$this->registerHook('footer')  
			OR !$this->_installDB()
            OR !($this->_is_cloud? true : $this->_createFolderAndSetPermissions())
			OR !((version_compare(_PS_VERSION_, '1.6', '>'))? $this->registerHook('DisplayBackOfficeHeader') : true)
			 )
			return false;
		
		
		return true;
	}
	
	public function hookDisplayBackOfficeHeader()
	{
	
		if(version_compare(_PS_VERSION_, '1.6', '>')){
			$base_dir = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__;
		} else {
			$base_dir = _PS_BASE_URL_.__PS_BASE_URI__;
		}
	
	
		$css = '';
		$css .= '<style type="text/css">
		.icon-AdminNew:before {
		content: url("'.$base_dir.'modules/'.$this->name.'/AdminNews.gif");
	}
	</style>
	';
		return $css;
	}
	
	
	public function uninstall()
	{
		
		if($this->_is15 == 1)
			$this->uninstallTab();
		else
			$this->uninstallTab14();
		
		
		if (!parent::uninstall() OR !$this->uninstallTable())
			return false;
		return true;
	}
	
	private function createAdminTabs(){
		
		
			@copy_custom_blocknewsplus(dirname(__FILE__)."/AdminNews.gif",_PS_ROOT_DIR_."/img/t/AdminNews.gif");
		
		 	$langs = Language::getLanguages();
            
          
            $tab0 = new Tab();
            $tab0->class_name = "AdminNew";
            $tab0->module = $this->name;
            $tab0->id_parent = 0; 
            foreach($langs as $l){
                    $tab0->name[$l['id_lang']] = $this->l('News');
            }
            $tab0->save();
            $main_tab_id = $tab0->id;

            unset($tab0);
            
            $tab1 = new Tab();
            $tab1->class_name = "AdminNews";
            $tab1->module = $this->name;
            $tab1->id_parent = $main_tab_id; 
            foreach($langs as $l){
                    $tab1->name[$l['id_lang']] = $this->l('Moderate News');
            }
            $tab1->save();

            unset($tab1);
        

	}
	
	private function createAdminTabs14(){
			@copy_custom_blocknewsplus(dirname(__FILE__)."/AdminNews.gif",_PS_ROOT_DIR_."/img/t/AdminNews.gif");
		
		 	$langs = Language::getLanguages();
            
          
            $tab0 = new Tab();
            $tab0->class_name = "AdminNews";
            $tab0->module = $this->name;
            $tab0->id_parent = 0; 
            foreach($langs as $l){
                    $tab0->name[$l['id_lang']] = $this->l('News');
            }
            $tab0->save();

	}
	
	private function uninstallTab(){
		
		
		$tab_id = Tab::getIdFromClassName("AdminNew");
		if($tab_id){
			$tab = new Tab($tab_id);
			$tab->delete();
		}
		
		$tab_id = Tab::getIdFromClassName("AdminNews");
		if($tab_id){
			$tab = new Tab($tab_id);
			$tab->delete();
		}


		@unlink(_PS_ROOT_DIR_."/img/t/AdminNews.gif");
	}
	
	private function uninstallTab14(){
		
		$tab_id = Tab::getIdFromClassName("AdminNews");
		if($tab_id){
			$tab = new Tab($tab_id);
			$tab->delete();
		}
		
		@unlink(_PS_ROOT_DIR_."/img/t/AdminNews.gif");
	}
	
	private function _installDB(){
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'blocknewsplus` (
							  `id` int(11) NOT NULL auto_increment,
							  `img` text, 
							  `ids_shops` varchar(1024) NOT NULL default \'0\',
							  `status` int(11) NOT NULL default \'1\',
							  `related_posts` varchar(1024) NOT NULL default \'0\',
							  `time_add` timestamp NOT NULL default CURRENT_TIMESTAMP,
							  PRIMARY KEY  (`id`)
							) ENGINE='.(defined('_MYSQL_ENGINE_')?_MYSQL_ENGINE_:"MyISAM").' DEFAULT CHARSET=utf8;';
		if (!Db::getInstance()->Execute($sql))
			return false;
			
		$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'blocknewsplus_data` (
							  `id_item` int(11) NOT NULL,
							  `id_lang` int(11) NOT NULL,
							  `seo_description` text,
							  `seo_keywords` varchar(5000) default NULL,
							  `seo_url` varchar(5000) default NULL,
							  `title` varchar(5000) NOT NULL,
							  `content` text NOT NULL,
							  KEY `id_item` (`id_item`)
							) ENGINE='.(defined('_MYSQL_ENGINE_')?_MYSQL_ENGINE_:"MyISAM").' DEFAULT CHARSET=utf8';
		Db::getInstance()->Execute($query);
			
		return true;
	}
	
	private function generateRewriteRules(){
            
            if(Configuration::get('PS_REWRITING_SETTINGS')){

                $rules = "#blocknewsplus - not remove this comment \n";
                
                $physical_uri = array();
                
                if($this->_is15){
	                foreach (ShopUrl::getShopUrls() as $shop_url)
					{
	                    if(in_array($shop_url->physical_uri,$physical_uri)) continue;
	                    
	                  $rules .= "RewriteRule ^(.*)news/?$ ".$shop_url->physical_uri."modules/".$this->name."/items.php [QSA,L] \n";
	                  $rules .= "RewriteRule ^(.*)news/([0-9a-zA-Z-_]+)/?$ ".$shop_url->physical_uri."modules/".$this->name."/item.php?item_id=$2 [QSA,L] \n";
				   
	                    
	                    $physical_uri[] = $shop_url->physical_uri;
	                } 
                } else{
                	$rules .= "RewriteRule ^(.*)news/?$ /modules/".$this->name."/items.php [QSA,L] \n"; 
	                $rules .= "RewriteRule ^(.*)news/([0-9a-zA-Z-_]+)/?$ /modules/".$this->name."/item.php?item_id=$2 [QSA,L] \n";
				     
                }
                $rules .= "#blocknewsplus \n\n";
                
                $path = _PS_ROOT_DIR_.'/.htaccess';

                  if(is_writable($path)){
                      
                      $existingRules = Tools::file_get_contents($path);
                      
                      if(!strpos($existingRules, "blocknewsplus")){
                        $handle = fopen($path, 'w');
                        fwrite($handle, $rules.$existingRules);
                        fclose($handle);
                      }
                  }
              }
        }
	
	
	private function _createFolderAndSetPermissions(){
		
		$prev_cwd = getcwd();
		
		$module_dir = dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR;
		@chdir($module_dir);
		//folder avatars
		$module_dir_img = $module_dir."blocknewsplus".DIRECTORY_SEPARATOR; 
		@mkdir($module_dir_img, 0777);

		@chdir($prev_cwd);
		
		return true;
	} 
	
	public function uninstallTable() {
		Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blocknewsplus');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blocknewsplus_data');
		return true;
	}
	
	
	
	public function hookLeftColumn($params)
	{
		$smarty = $this->context->smarty;
		include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj_blocknewsplushelp = new blocknewsplushelp();
    	$_data = $obj_blocknewsplushelp->getItemsBlock();
		//echo "<pre>"; var_dump($_data['items']);
    	$smarty->assign(array($this->name.'itemsblock' => $_data['items']
							  )
						);
		
		
		$smarty->assign($this->name.'news_left', Configuration::get($this->name.'news_left'));
		$smarty->assign($this->name.'is_urlrewrite', Configuration::get($this->name.'is_urlrewrite'));
		
		$smarty->assign($this->name.'is16', $this->_is16);
		if($this->_is_friendly_url){
			$smarty->assign($this->name.'iso_lng', $this->_iso_lng);
		} else {
			$smarty->assign($this->name.'iso_lng', '');
		}
        $smarty->assign($this->name.'pic', $this->path_img_cloud);
		return $this->display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/left.tpl');	
	}
	
	public function hookRightColumn($params)
	{
		$smarty = $this->context->smarty;
		include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj_blocknewsplushelp = new blocknewsplushelp();
    	$_data = $obj_blocknewsplushelp->getItemsBlock();
		
		$smarty->assign(array($this->name.'itemsblock' => $_data['items']
							  )
						);
		$smarty->assign($this->name.'news_right', Configuration::get($this->name.'news_right'));
		$smarty->assign($this->name.'is_urlrewrite', Configuration::get($this->name.'is_urlrewrite'));
		
	$smarty->assign($this->name.'is16', $this->_is16);
		if($this->_is_friendly_url){
			$smarty->assign($this->name.'iso_lng', $this->_iso_lng);
		} else {
			$smarty->assign($this->name.'iso_lng', '');
		}
        $smarty->assign($this->name.'pic', $this->path_img_cloud);
		return $this->display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/right.tpl');
	}
	
	public function hookHome($params)
	{
		$smarty = $this->context->smarty;
		include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj_blocknewsplushelp = new blocknewsplushelp();
    	$_data = $obj_blocknewsplushelp->getItemsBlock(array('home'=>1));
		
		$smarty->assign(array($this->name.'itemsblock' => $_data['items']
							  )
						);
		$smarty->assign($this->name.'news_home', Configuration::get($this->name.'news_home'));
		$smarty->assign($this->name.'is_urlrewrite', Configuration::get($this->name.'is_urlrewrite'));
		
		$smarty->assign($this->name.'news_h', Configuration::get($this->name.'news_h'));
		
		$smarty->assign($this->name.'is15', $this->_is15);
		$smarty->assign($this->name.'is16', $this->_is16);
		if($this->_is_friendly_url){
			$smarty->assign($this->name.'iso_lng', $this->_iso_lng);
		} else {
			$smarty->assign($this->name.'iso_lng', '');
		}
        $smarty->assign($this->name.'pic', $this->path_img_cloud);
		return $this->display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/home.tpl');
	}
	
	public function hookFooter($params)
	{
		$smarty = $this->context->smarty;
		include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj_blocknewsplushelp = new blocknewsplushelp();
    	$_data = $obj_blocknewsplushelp->getItemsBlock();
		
		$smarty->assign(array($this->name.'itemsblock' => $_data['items']
							  )
						);
		$smarty->assign($this->name.'news_footer', Configuration::get($this->name.'news_footer'));
		$smarty->assign($this->name.'is_urlrewrite', Configuration::get($this->name.'is_urlrewrite'));
		
		$smarty->assign($this->name.'is16', $this->_is16);
		if($this->_is_friendly_url){
			$smarty->assign($this->name.'iso_lng', $this->_iso_lng);
		} else {
			$smarty->assign($this->name.'iso_lng', '');
		}
        $smarty->assign($this->name.'pic', $this->path_img_cloud);
		return $this->display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/footer.tpl');
	}
	
	public function hookHeader($params){
    	$smarty = $this->context->smarty;
    	
    	$smarty->assign($this->name.'is15', $this->_is15);
    	
		if(version_compare(_PS_VERSION_, '1.5', '>')){
    		$this->context->controller->addCSS(($this->_path).'views/css/blocknewsplus.css', 'all');
    		$this->context->controller->addJS($this->_path.'views/js/blocknewsplus.js');
    	}
    	
    	
    	$item_id = Tools::getValue('item_id');
    	$is_news_page = 0;
    	if($item_id){
    		$is_news_page = 1;
    		
    		
    		include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
			$obj_blocknewsplushelp = new blocknewsplushelp();
			
			$item_id = $obj_blocknewsplushelp->getTransformSEOURLtoIDItem(array('id'=>$item_id));
				
			$_info_cat = $obj_blocknewsplushelp->getItem(array('id' => $item_id,'site'=>1));
    		
			$name = isset($_info_cat['item'][0]['title'])?$_info_cat['item'][0]['title']:'';
    		$img = isset($_info_cat['item'][0]['img_orig'])?$_info_cat['item'][0]['img_orig']:'';
    		
    		$smarty->assign($this->name.'name', $name);
    		$smarty->assign($this->name.'img', $img);
    	}
    	
    	$smarty->assign($this->name.'is_news', $is_news_page);
        $smarty->assign($this->name.'pic', $this->path_img_cloud);
    	
    	return $this->display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/head.tpl');
    
    }
    
     public function getContent()
    {
    	$cookie = $this->context->cookie;
    	$currentIndex = $this->context->currentindex;
    	include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj_blocknewsplus = new blocknewsplushelp();
		
    	
		
		if (Tools::isSubmit('submit_item'))
        {
	        if($this->_is15){
		    		$cat_shop_association = Tools::getValue("cat_shop_association");
		    } else{
		    		$cat_shop_association = array(0=>0);
		    }
		    
		    $ids_related_posts = Tools::getValue("ids_related_posts");
		    
        	$seo_url = Tools::getValue("seo_url");
    		$languages = Language::getLanguages(false);
	    	$data_title_content_lang = array();
	    	foreach ($languages as $language){
	    		$id_lang = $language['id_lang'];
	    		$title = Tools::getValue("title_".$id_lang);
	    		$content = Tools::getValue("content_".$id_lang);
	    		$post_seokeywords = Tools::getValue("post_seokeywords_".$id_lang);
	    		$post_seodescription = Tools::getValue("post_seodescription_".$id_lang);
	    		
	    		
	    		if(Tools::strlen($title)>0 && Tools::strlen($content)>0 && !empty($cat_shop_association))
	    		{
	    			$data_title_content_lang[$id_lang] = array('title' => $title,
	    									 				    'content' => $content,
	    														'post_seodescription' => $post_seodescription,
	    													    'post_seokeywords' => $post_seokeywords,
	    			 										    'seo_url' => $seo_url
	    													    
	    													    );		
	    		}
	    	}
	    	
        	$item_status = Tools::getValue("item_status");
        	
        	$data = array( 'data_title_content_lang'=>$data_title_content_lang,
         				 	'item_status' => $item_status,
        					'ids_related_posts'=>$ids_related_posts,
        				  'cat_shop_association' => $cat_shop_association,
         				
         				  );
         	if(sizeof($data_title_content_lang)>0)
         		$obj_blocknewsplus->saveItem($data);
        		
			Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)($cookie->id_employee)).'');
		}
		
    	if (Tools::isSubmit("delete_item")) {
			if (Validate::isInt(Tools::getValue("id"))) {
				
				$data = array('id' => Tools::getValue("id"));
				$obj_blocknewsplus->deleteItem($data);
				
				$page = Tools::getValue("pageitems");
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)($cookie->id_employee)).'&pageitems='.$page);
			}
			
		}
		
    	if (Tools::isSubmit('submit_news'))
        {
        	 Configuration::updateValue($this->name.'news_home', Tools::getValue('news_home'));
        	 Configuration::updateValue($this->name.'news_left', Tools::getValue('news_left'));
        	 Configuration::updateValue($this->name.'news_right', Tools::getValue('news_right'));
        	 Configuration::updateValue($this->name.'news_footer', Tools::getValue('news_footer'));
        	 
        	 Configuration::updateValue($this->name.'news_h', Tools::getValue('news_h'));
        	 
        	
        	 Configuration::updateValue($this->name.'is_urlrewrite', Tools::getValue('is_urlrewrite'));
        	
        	 Configuration::updateValue($this->name.'faq_blc', Tools::getValue('faq_blc'));
        	 Configuration::updateValue($this->name.'perpage_posts', Tools::getValue('perpage_posts'));
        }
       
    	if (Tools::isSubmit('cancel_item'))
        {
        	$page = Tools::getValue("pageitems");
        	Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)($cookie->id_employee)).'&pageitems='.$page);
		}
		
		if (Tools::isSubmit('update_item'))
        {
        	$id = Tools::getValue("id");
     		$seo_url = Tools::getValue("seo_url");
    		
        	if($this->_is15){
		    		$cat_shop_association = Tools::getValue("cat_shop_association");
		    } else{
		    		$cat_shop_association = array(0=>0);
		    }
		    
		    $ids_related_posts = Tools::getValue("ids_related_posts");
     		
        	$languages = Language::getLanguages(false);
	    	$data_title_content_lang = array();
	    	foreach ($languages as $language){
	    		$id_lang = $language['id_lang'];
	    		$title = Tools::getValue("title_".$id_lang);
	    		$content = Tools::getValue("content_".$id_lang);
	    		$post_seokeywords = Tools::getValue("post_seokeywords_".$id_lang);
	    		$post_seodescription = Tools::getValue("post_seodescription_".$id_lang);
	    		
	    		
	    		if(Tools::strlen($title)>0 && Tools::strlen($content)>0 && !empty($cat_shop_association))
	    		{
	    			$data_title_content_lang[$id_lang] = array('title' => $title,
	    									 				    'content' => $content,
	    			 											'post_seokeywords' => $post_seokeywords,
	    			 										   'post_seodescription' => $post_seodescription,
	    													  'seo_url'=>$seo_url
	    													    );		
	    		}
	    	}
        	
         	$item_status = Tools::getValue("item_status");
        	$post_images = Tools::getValue("post_images");
        	
         	$data = array('data_title_content_lang'=>$data_title_content_lang,
        				  'id' => $id,
         				  'item_status' => $item_status,
         				  'post_images' => $post_images,
         					'ids_related_posts'=>$ids_related_posts,
         				  'cat_shop_association' => $cat_shop_association,
         				 );
         	if(sizeof($data_title_content_lang)>0)
         		$obj_blocknewsplus->updateItem($data);
         		
         	$page = Tools::getValue("pageitems");
         	Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)($cookie->id_employee)).'&pageitems='.$page);
		
        }
    	
    	$this->_html = '';
        $this->_html .= $this->_jsandcss();	
        $this->_html .= $this->_displayForm();
        return $this->_html;
    	
    }
    
    private function _drawNewsSettings(){
    	
     	$_html = '';
		$_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" >';
		
		$_html .= '<style type="text/css">table.block-settings td{padding:3px}</style>';
		
		$_html .= '
		<fieldset>
					<legend><img src="../modules/'.$this->name.'/logo.gif"  />
					'.$this->displayName.'</legend>';
		
		$_html .= '<table style="width:100%" class="block-settings">';
    	
		$_html .= '<tr>';
    	$_html .= '<td style="text-align:right;width:40%;padding:0 20px 0 0">';
    	
    	$_html .= '<b>'.$this->l('Enable or Disable URL rewriting').':</b>';
    	
    	$_html .= '</td>';
    	$_html .= '<td style="text-align:left">';
		$_html .=  '
					<input type="radio" value="1" id="text_list_on" name="is_urlrewrite" onclick="enableOrDisableTools(1)"
							'.(Tools::getValue('is_urlrewrite', Configuration::get($this->name.'is_urlrewrite')) ? 'checked="checked" ' : '').'>
					<label for="dhtml_on" class="t"> 
						<img alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" src="../img/admin/enabled.gif">
					</label>
					<input type="radio" value="0" id="text_list_off" name="is_urlrewrite" onclick="enableOrDisableTools(0)"
						   '.(!Tools::getValue('is_urlrewrite', Configuration::get($this->name.'is_urlrewrite')) ? 'checked="checked" ' : '').'>
					<label for="dhtml_off" class="t">
						<img alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" src="../img/admin/disabled.gif">
					</label>
					
				';
		
		
		$_html .= '<script type="text/javascript">
			    	function enableOrDisableTools(id)
						{
						if(id==0){
							$("#block-tools-settings").hide(200);
						} else {
							$("#block-tools-settings").show(200);
						}
							
						}
					</script>';
		$_html .= '</td>';
		$_html .= '</tr>';
		
		$_html .= '<tr>';
    	$_html .= '<td style="text-align:right;width:40%;padding:0 20px 0 0">';
    	
    	$_html .= '<b>'.$this->l('News per Page:').'</b>';
    	
    	$_html .= '</td>';
    	$_html .= '<td style="text-align:left">';
		$_html .=  '
					<input type="text" name="perpage_posts"  
			               value="'.Tools::getValue('perpage_posts', Configuration::get($this->name.'perpage_posts')).'"
			               >
				';
		$_html .= '</td>';
		$_html .= '</tr>';
		
		$_html .= '<tr>';
    	$_html .= '<td style="text-align:right;width:35%;padding:0 20px 0 0">';
    	
    	$_html .= '<b>'.$this->l('The number of items in the "Block Last News":').'</b>';
    	
    	$_html .= '</td>';
    	$_html .= '<td style="text-align:left">';
		$_html .=  '
					<input type="text" name="faq_blc"  
			               value="'.Tools::getValue('faq_blc', Configuration::get($this->name.'faq_blc')).'"
			               >
				';
		$_html .= '</td>';
		$_html .= '</tr>';
		
		
		
		####
		$_html .= '<tr>';
    	$_html .= '<td style="text-align:right;width:40%;padding:0 20px 0 0">';
    	
		$_html .= '<b>'.$this->l('Position Block Last News:').'</b>';
		
		$_html .= '</td>';
    	$_html .= '<td style="text-align:left">';
		
				//$_html .= '<div class="margin-form choose_hooks">';
	    		$_html .= '<table style="width:66%;">
	    				<tr>
	    					<td style="width: 33%">'.$this->l('Left Column').'</td>
	    					<td style="width: 33%">'.$this->l('Right Column').'</td>
	    					
	    				</tr>
	    				<tr>
	    					<td>
	    						<input type="checkbox" name="news_left" '.((Tools::getValue($this->name.'news_left', Configuration::get($this->name.'news_left')) ==1)?'checked':'').'  value="1"/>
	    					</td>
	    					<td>
	    						<input type="checkbox" name="news_right" '.((Tools::getValue($this->name.'news_right', Configuration::get($this->name.'news_right')) ==1)?'checked':'') .' value="1"/>
	    					</td>
	    					
	    				</tr>
	    				<tr>
	    					<td>'.$this->l('Footer').'</td>
	    					<td>'.$this->l('Home').'</td>
	    					
	    				</tr>
	    				<tr>
	    					<td>
	    						<input type="checkbox" name="news_footer" '.((Tools::getValue($this->name.'news_footer', Configuration::get($this->name.'news_footer')) ==1)?'checked':'').' value="1"/>
	    					</td>
	    					<td>
	    						<input type="checkbox" name="news_home" '.((Tools::getValue($this->name.'news_home', Configuration::get($this->name.'news_home')) ==1)?'checked':'').' value="1"/>
	    					</td>
	    					
	    				</tr>
	    				
	    			</table>';
	    //$_html .= '</div>';
		$_html .= '</td>';
		$_html .= '</tr>';
				
		###
		
		
		
		$_html .= '<tr>';
    	$_html .= '<td style="text-align:right;width:35%;padding:0 20px 0 0">';
    	
    	$_html .= '<b>'.$this->l('"Block Last News" on the home page').':</b>';
    	
    	$_html .= '</td>';
    	$_html .= '<td style="text-align:left">';
		$_html .=  '
					<select class=" select" name="news_h" 
							id="defaultgroup">
						<option '.((Tools::getValue('news_h', Configuration::get($this->name.'news_h')) == '1') ? 'selected="selected" ' : '').' value="1">'.$this->l('Horizontal News').'</option>
						<option '.((Tools::getValue('news_h', Configuration::get($this->name.'news_h')) == '2') ? 'selected="selected" ' : '').' value="2">'.$this->l('News in Blocks').'</option>
						</select>
				';
		$_html .= '</td>';
		$_html .= '</tr>';
		
		
    	$_html .= '</table>';
			
			$_html .= '<p class="center" style="border: 1px solid #EBEDF4; padding: 10px; margin-top: 10px;">
					<input type="submit" name="submit_news" value="'.$this->l('Update settings').'" 
                		   class="button"  />
                	</p>';
					
					
		$_html .= '</fieldset>	';
		$_html .= '</form>';
		
		$_html .= '<br/><br/>';
    	
    	$_html .= '<div id="block-tools-settings" '.(Configuration::get($this->name.'is_urlrewrite')==1?'style="display:block"':'style="display:none"').'>';
    	
    	$_html .= '<fieldset>
					<legend>'.$this->l('Tools').'</legend>';
    	$_html .= $this->_hint();
    	$_html .= '</fieldset>';
    	$_html .= '</div>';
		
		return $_html;
    }
    
    public function drawNews($data = null){
    	$cookie = $this->context->cookie;
    	
    	$currentIndex = $this->context->currentindex;
    	
    	$currentIndex = isset($data['currentindex'])?$data['currentindex']:$currentIndex;
    	$controller = isset($data['controller'])?$data['controller']:'AdminModules';
    	$token = isset($data['token'])?$data['token']:Tools::getAdminToken($controller.(int)(Tab::getIdFromClassName($controller)).(int)($cookie->id_employee));
    	
    	$related_posts = 0;
		$id = 0;
     	$i=0;
     	$_html = '';
		
		$_html .= '<fieldset>
					<legend><img src="../modules/'.$this->name.'/logo.gif" />
					'.$this->l('Moderate News').'</legend>';
		
		
		
		include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
		$obj_blocknewsplus = new blocknewsplushelp();
		
		
		if(Tools::isSubmit("edit_item")){
			
			$divLangName = "ccontent¤title¤post_seokeywords¤post_seodescription";
			
			
			$_data = $obj_blocknewsplus->getItem(array('id'=>(int)Tools::getValue("id")));
			
			$id = $_data['item'][0]['id'];
			$img = $_data['item'][0]['img'];
			$status = $_data['item'][0]['status'];
			$data_content = isset($_data['item']['data'])?$_data['item']['data']:'';
			$related_posts=isset($_data['item'][0]['related_posts'])?$_data['item'][0]['related_posts']:array();
			
			$_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">';
    		
    		$_html .= '<label>'.$this->l('Title:').'</label>
    					<div class="margin-form">';
    		
			$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	    	$languages = Language::getLanguages(false);
	    	
	    	foreach ($languages as $language){
			$id_lng = (int)$language['id_lang'];
	    	$title = isset($data_content[$id_lng]['title'])?$data_content[$id_lng]['title']:"";
			
			$_html .= '	<div id="title_'.$language['id_lang'].'" 
							 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
							 >

						<input type="text" style="width:400px"   
								  id="title_'.$language['id_lang'].'" 
								  name="title_'.$language['id_lang'].'" 
								  value="'.htmlentities(Tools::stripslashes($title), ENT_COMPAT, 'UTF-8').'"/>
						</div>';
	    	}
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'title');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
    		$_html .= '<div style="clear:both"></div>';
							
			$_html .= '</div>';
			
			
			
			if(Configuration::get($this->name.'is_urlrewrite') == 1){
			// identifier
			$cookie = $this->context->cookie;
    	
			$current_lng =  $cookie->id_lang;
			$seo_url = isset($_data['item']['data'][$current_lng]['seo_url'])?$_data['item']['data'][$current_lng]['seo_url']:"";
		    		
			$_html .= '<label>'.$this->l('Identifier (SEO URL)').'</label>';
	    	
	    	$_html .= '<div class="margin-form">';
	    	
				
				$_html .= '
							<input type="text" style="width:400px"   
									  id="seo_url" 
									  name="seo_url" 
									  value="'.$seo_url.'"/>
							<p>(eg: domain.com/news/identifier)</p>
							';
		    $_html .=  '</div>';
			}
		
    		$_html .= '<label>'.$this->l('SEO Keywords').'</label>';
    			
    	
    	
	    	$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		    $languages = Language::getLanguages(false);
	    	
	    	$_html .= '<div class="margin-form">';
	    	
			foreach ($languages as $language){
				$id_lng = (int)$language['id_lang'];
		    	$seo_keywords = isset($_data['item']['data'][$id_lng]['seo_keywords'])?$_data['item']['data'][$id_lng]['seo_keywords']:"";
		    	
				$_html .= '	<div id="post_seokeywords_'.$language['id_lang'].'" 
								 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
								 >
							<textarea id="post_seokeywords_'.$language['id_lang'].'" 
									  name="post_seokeywords_'.$language['id_lang'].'" 
									  cols="80" rows="10"  
				                	   >'.htmlentities(Tools::stripslashes($seo_keywords), ENT_COMPAT, 'UTF-8').'</textarea>
							
							</div>';
		    	}
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'post_seokeywords');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
				        
			$_html .=  '</div>';
				        
		
    	
	    	$_html .= '<label>'.$this->l('SEO Description').'</label>';
	    			
	    	
	    	
	    	$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		    $languages = Language::getLanguages(false);
	    	
	    	$_html .= '<div class="margin-form">';
	    	
			foreach ($languages as $language){
				$id_lng = (int)$language['id_lang'];
		    	$seo_description = isset($_data['item']['data'][$id_lng]['seo_description'])?$_data['item']['data'][$id_lng]['seo_description']:"";
		    	
				$_html .= '	<div id="post_seodescription_'.$language['id_lang'].'" 
								 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
								 >
							<textarea id="post_seodescription_'.$language['id_lang'].'" 
									  name="post_seodescription_'.$language['id_lang'].'" 
									  cols="80" rows="10"  
				                	   >'.htmlentities(Tools::stripslashes($seo_description), ENT_COMPAT, 'UTF-8').'</textarea>
							
							</div>';
		    	}
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'post_seodescription');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
				        
			$_html .=  '</div>';
			
			
			
    		
    		$_html .= '<label>'.$this->l('Logo Image').'</label>
    			
    				<div class="margin-form">
					<input type="file" name="post_image" id="post_image" ';
    	if($this->_is16 == 0){
    	 $_html .= 'class="customFileInput"';
    	} 
    	 $_html .= '/>
					<p>Allow formats *.jpg; *.jpeg; *.png; *.gif.</p>';

    	
	    	if(Tools::strlen($img)>0){
	    	$_html .= '<div id="post_images_list">';
	    		$_html .= '<div style="float:left;margin:10px" id="post_images_id">';
	    		$_html .= '<table width=100%>';
	    		
	    		$_html .= '<tr><td align="left">';
	    		$_html .= '<input type="radio" checked name="post_images"/>';
	    		
	    		$_html .= '</td>';
	    		
	    		$_html .= '<td align="right">';
	    		
	    			$_html .= '<a href="javascript:void(0)" title="'.$this->l('Delete').'"  
	    						onclick = "delete_img('.$id.');"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>
	    					';
	    		
	    		$_html .= '</td>';
	    		
	    		$_html .= '<tr>';
	    		$_html .= '<td colspan=2>';
                if($this->_is_cloud){
                    $_html .= '<img src="../modules/'.$this->name.'/upload/'.$img.'" style="width:50px;height:50px"/>';
                } else {
                    $_html .= '<img src="../upload/'.$this->name.'/'.$img.'" style="width:50px;height:50px"/>';
                }

	    		//$_html .= '<img src="../upload/'.$this->name.'/'.$img.'" style="width:50px;height:50px"/>';
	    		$_html .= '</td>';
	    		$_html .= '</tr>';
	    		
	    		$_html .= '</table>';
	    		
	    		$_html .= '</div>';
	    	//}
	    	$_html .= '<div style="clear:both"></div>';
	    	$_html .= '</div>';
	    	}
	    	
	    	$_html .= '</div>';
    		
    		
    		if(defined('_MYSQL_ENGINE_')){
	    		
    		$_html .= '<label>'.$this->l('Content:').'</label>
	    					<div class="margin-form" >';
    		
    		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	    	$languages = Language::getLanguages(false);
	    	
	    	foreach ($languages as $language){
	    	$id_lng = (int)$language['id_lang'];
	    	$content = isset($data_content[$id_lng]['content'])?$data_content[$id_lng]['content']:"";
			
			$_html .= '	<div id="ccontent_'.$language['id_lang'].'" 
							 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;';
			
	    	if($this->_is16 == 1){ $_html .= 'width:80%;'; }
			
			$_html .= ' "
							 >

						<textarea class="rte" cols="25" rows="10" ';

			if($this->_is16 == 0){
				$_html .= 'style="width:400px" ';
			}  
			
			$_html .= ' id="content_'.$language['id_lang'].'" 
								  name="content_'.$language['id_lang'].'">'.htmlentities(Tools::stripslashes($content), ENT_COMPAT, 'UTF-8').'</textarea>

					</div>';
	    	}
	    	
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'ccontent');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
	    	
			$_html .= '<div style="clear:both"></div>';
			
			$_html .= '</div>';
	    	}else{
	    		
	    		$_html .= '<label>'.$this->l('Content:').'</label>
	    					<div class="margin-form">';
	    					
	    		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		    	$languages = Language::getLanguages(false);
		    	$divLangName = "ccontent";
		    	
		    	foreach ($languages as $language){
				$id_lng = (int)$language['id_lang'];
	    		$content = isset($data_content[$id_lng]['content'])?$data_content[$id_lng]['content']:"";
			
				$_html .= '	<div id="ccontent_'.$language['id_lang'].'" 
								 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
								 >
	
							<textarea class="rte" cols="25" rows="10" style="width:400px"   
									  id="content_'.$language['id_lang'].'" 
									  name="content_'.$language['id_lang'].'">'.htmlentities(Tools::stripslashes($content), ENT_COMPAT, 'UTF-8').'</textarea>
	
						</div>';
		    	}
				ob_start();
				$this->displayFlags($languages, $defaultLanguage, $divLangName, 'ccontent');
				$displayflags = ob_get_clean();
				$_html .= $displayflags;
		    	
				$_html .= '<div style="clear:both"></div>';
				
				$_html .= '</div>';
	    	}
	    	
	    	
	    	
	    	### related posts ####
		
		$_data_cat  = $obj_blocknewsplus->getRelatedPosts(array('admin'=>1,'id'=>$id)); 
		
    	$_html .= '<label>'.$this->l('Related News').'</label>
    					<div class="margin-form">';
		
    	$_html .= '
		<div style="height:140px; overflow-x:hidden; overflow-y:scroll; padding:0;" class="margin-form">
		
		<table cellspacing="0" cellpadding="0" style="min-width: '.(($this->_is15 == 0)?'550px':'600px').'" class="table">
            <tr>
				<th style="width:40px;"></th>
				<th style="width:30px;">ID</th>
				<th>'.$this->l('Title').'</th>
				<th>'.$this->l('Lang').'</th>
            </tr>';
            
			$y=0;	
			$related_posts = explode(",",$related_posts);
			foreach($_data_cat['related_posts'] as $_item){
				$name = isset($_item['title'])?$_item['title']:'';
				$id_pr = isset($_item['id'])?$_item['id']:'';
				
				$ids_lng = isset($_item['ids_lng'])?$_item['ids_lng']:array();
				$lang_for_related_posts = array();
				foreach($ids_lng as $lng_id){
					$data_lng = Language::getLanguage($lng_id);
					$lang_for_related_posts[] = $data_lng['iso_code']; 
				}
				$lang_for_related_posts = implode(",",$lang_for_related_posts);
				
				if(Tools::strlen($name)==0) continue;
				
		       $_html .= '
		       		<tr class="'.(($y%2==0)?'':'alt_row').'">
						<td>
							<input type="checkbox" value="'.$id_pr.'"  id="groupRelated_'.$id_pr.'"
								   class="groupBox" name="ids_related_posts[]"
								   '.(in_array($id_pr,$related_posts)?'checked="checked"':'').' />
						</td>
						<td>'.$id_pr.'</td>
						<td><label class="t" for="groupRelated_'.$id_pr.'">'.$name.'</label></td>
						<td>'.$lang_for_related_posts.'</td>
					</tr>';
		       $y++;
			}
	

      	 $_html .= '
       	</table>
									
		</div>';
		
		
		$_html .=  '</div>';
		
		### related posts ####
	    	
	    	
	    	$_html .= '<label>'.$this->l('Status').'</label>
				<div class = "margin-form">';
				
			$_html .= '<select name="item_status" style="width:100px">
						<option value=1 '.(($status==1)?"selected=\"true\"":"").'>'.$this->l('Enabled').'</option>
						<option value=0 '.(($status==0)?"selected=\"true\"":"").'>'.$this->l('Disabled').'</option>
					   </select>';
				
				
			$_html .= '</div>';
			
			
			if($this->_is15){
	    	// shop association
	    	$_html .= '<div class="clear"></div>';
	    	$_html .= '<label>'.$this->l('Shop association').':</label>';
	    	$_html .= '<div class="margin-form">';
	
			$_html .= '<table width="50%" cellspacing="0" cellpadding="0" class="table">
							<tr>
								<th>Shop</th>
							</tr>';
			$u = 0;
			
			
			
			$shops = Shop::getShops();
			$shops_tmp = explode(",",isset($_data['item'][0]['ids_shops'])?$_data['item'][0]['ids_shops']:"");
			
			$count_shops = sizeof($shops);
			foreach($shops as $_shop){
				$id_shop = $_shop['id_shop'];
				$name_shop = $_shop['name'];
				 $_html .= '<tr>
							<td>
								<img src="../img/admin/lv2_'.((($count_shops-1)==$u)?"f":"b").'.png" alt="" style="vertical-align:middle;">
								<label class="child">';
			 
				
					$_html .= '<input type="checkbox"  
									   name="cat_shop_association[]" 
									   value="'.$id_shop.'" '.((in_array($id_shop,$shops_tmp))?'checked="checked"':'').' 
									   class="input_shop" 
									   />
									'.$name_shop.'';
					
					$_html .= '</label>
							</td>
						</tr>';
			 $u++;
			}
		
			$_html .= '</table>';
				
			$_html .= '</div>';
																	
	    	}
	    	// shop association
	    
			
    	
    		$_html .= '<label>&nbsp;</label>
						<div class = "margin-form"  style="margin-top:10px">
						<input type="submit" name="cancel_item" value="'.$this->l('Cancel').'" 
                		   class="button"  />&nbsp;&nbsp;&nbsp;
						<input type="submit" name="update_item" value="'.$this->l('Update').'" 
                		   class="button"  />
                		  </div>';
    		
    		$_html .= '</form>';
    		
		}else{
			
			$divLangName = "ccontent¤title¤post_seokeywords¤post_seodescription";
			$name = "";
			$content = "";
			$seo_url  = "";
			$seo_keywords = "";
			$seo_description = "";
			
    		$_html .= '<a href="javascript:void(0)" onclick="$(\'#add-question-form\').show(200);$(\'#link-add-question-form\').hide(200)"
    					id="link-add-question-form"	
					  style="border: 1px solid rgb(222, 222, 222); padding: 5px; margin-bottom: 10px; display: block; font-size: 16px; color: maroon; text-align: center; font-weight: bold; text-decoration: underline;"
					  >'.$this->l('Add New Item').'</a>';
    		
    		$_html .= '<div style="border: 1px solid rgb(222, 222, 222);padding-top:10px;display:none" id="add-question-form">';
			$_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">';
    		
    		$_html .= '<label>'.$this->l('Title:').'</label>
    					<div class="margin-form">';
    		
    		
    		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	    	$languages = Language::getLanguages(false);
	    	
	    	foreach ($languages as $language){
			$id_lng = (int)$language['id_lang'];
	    	
			$_html .= '	<div id="title_'.$language['id_lang'].'" 
							 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
							 >

						<input type="text" style="width:400px"   
								  id="title_'.$language['id_lang'].'" 
								  name="title_'.$language['id_lang'].'" 
								  value="'.htmlentities(Tools::stripslashes($name), ENT_COMPAT, 'UTF-8').'"/>
						</div>';
	    	}
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'title');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
    		
			$_html .= '</div>';
			
			
			
			
			if(Configuration::get($this->name.'is_urlrewrite') == 1){
			// identifier
			$cookie = $this->context->cookie;
    	
			$current_lng =  $cookie->id_lang;
				
			$_html .= '<label>'.$this->l('Identifier (SEO URL)').'</label>';
	    	
	    	$_html .= '<div class="margin-form">';
	    	
				
				$_html .= '
							<input type="text" style="width:400px"   
									  id="seo_url" 
									  name="seo_url" 
									  value="'.$seo_url.'"/>
							<p>(eg: domain.com/news/identifier)</p>
							';
		    $_html .=  '</div>';
			}
		
	    	$_html .= '<label>'.$this->l('SEO Keywords').'</label>';
	    			
	    	
	    	
	    	$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		    $languages = Language::getLanguages(false);
	    	
	    	$_html .= '<div class="margin-form">';
	    	
			foreach ($languages as $language){
				$id_lng = (int)$language['id_lang'];
		    	
				$_html .= '	<div id="post_seokeywords_'.$language['id_lang'].'" 
								 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
								 >
							<textarea id="post_seokeywords_'.$language['id_lang'].'" 
									  name="post_seokeywords_'.$language['id_lang'].'" 
									  cols="80" rows="10"  
				                	   >'.htmlentities(Tools::stripslashes($seo_keywords), ENT_COMPAT, 'UTF-8').'</textarea>
							
							</div>';
		    	}
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'post_seokeywords');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
				        
			$_html .=  '</div>';
			        
		
    	
	    	$_html .= '<label>'.$this->l('SEO Description').'</label>';
	    			
	    	
	    	
	    	$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		    $languages = Language::getLanguages(false);
	    	
	    	$_html .= '<div class="margin-form">';
	    	
			foreach ($languages as $language){
				$id_lng = (int)$language['id_lang'];
		    	
				$_html .= '	<div id="post_seodescription_'.$language['id_lang'].'" 
								 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
								 >
							<textarea id="post_seodescription_'.$language['id_lang'].'" 
									  name="post_seodescription_'.$language['id_lang'].'" 
									  cols="80" rows="10"  
				                	   >'.htmlentities(Tools::stripslashes($seo_description), ENT_COMPAT, 'UTF-8').'</textarea>
							
							</div>';
		    	}
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'post_seodescription');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
				        
			$_html .=  '</div>';
			        
		
			
			
			
    		
    		$_html .= '<label>'.$this->l('Logo Image').'</label>
    			
    				<div class="margin-form">
					<input type="file" name="post_image" id="post_image" ';
    	if($this->_is16 == 0){
    	 $_html .= 'class="customFileInput"';
    	} 
    	 $_html .= '/>
					<p>Allow formats *.jpg; *.jpeg; *.png; *.gif.</p>';
    	
	    	$_html .= '</div>';
    		
    		if(defined('_MYSQL_ENGINE_')){
	    	
    			
    		$_html .= '<label>'.$this->l('Content:').'</label>
	    					<div class="margin-form" >';
    			$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	    	$languages = Language::getLanguages(false);
	    	
	    	foreach ($languages as $language){

			$_html .= '	<div id="ccontent_'.$language['id_lang'].'" 
							 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;';
			
			if($this->_is16 == 1){ $_html .= 'width:80%;'; }
							 
			$_html .= ' ">
			
			
						<textarea class="rte" cols="25" rows="10" ';

			if($this->_is16 == 0){
				$_html .= 'style="width:400px" ';
			}   
			$_html .= 'id="content_'.$language['id_lang'].'" 
								  name="content_'.$language['id_lang'].'">'.htmlentities(Tools::stripslashes($content), ENT_COMPAT, 'UTF-8').'</textarea>

					</div>';
			
	    	}
	    	
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'ccontent');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
			$_html .= '</div>';
			
	    	}else{
	    		
	    	$_html .= '<label>'.$this->l('Content:').'</label>
	    					<div class="margin-form" >';
    			$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
	    	$languages = Language::getLanguages(false);
	    	
	    	foreach ($languages as $language)

			$_html .= '	<div id="ccontent_'.$language['id_lang'].'" 
							 style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;"
							 >

						<textarea class="rte" cols="25" rows="10" style="width:400px"   
								  id="content_'.$language['id_lang'].'" 
								  name="content_'.$language['id_lang'].'">'.htmlentities(Tools::stripslashes($content), ENT_COMPAT, 'UTF-8').'</textarea>

					</div>';
			ob_start();
			$this->displayFlags($languages, $defaultLanguage, $divLangName, 'ccontent');
			$displayflags = ob_get_clean();
			$_html .= $displayflags;
			$_html .= '<div style="clear:both"></div>';
			$_html .= '</div>';
	    	}
	    	
	    	
	    ### related posts ####
		
		$_data_cat  = $obj_blocknewsplus->getRelatedPosts(array('admin'=>1,'id'=>$id)); 
		
    	$_html .= '<label>'.$this->l('Related News').'</label>
    					<div class="margin-form">';
		
    	$_html .= '
		<div style="height:140px; overflow-x:hidden; overflow-y:scroll; padding:0;" class="margin-form">
		
		<table cellspacing="0" cellpadding="0" style="min-width: '.(($this->_is15 == 0)?'550px':'600px').'" class="table">
            <tr>
				<th style="width:40px;"></th>
				<th style="width:30px;">ID</th>
				<th>'.$this->l('Title').'</th>
				<th>'.$this->l('Lang').'</th>
            </tr>';
            
			$y=0;	
			$related_posts = explode(",",$related_posts);
			foreach($_data_cat['related_posts'] as $_item){
				$name = isset($_item['title'])?$_item['title']:'';
				$id_pr = isset($_item['id'])?$_item['id']:'';
				
				$ids_lng = isset($_item['ids_lng'])?$_item['ids_lng']:array();
				$lang_for_related_posts = array();
				foreach($ids_lng as $lng_id){
					$data_lng = Language::getLanguage($lng_id);
					$lang_for_related_posts[] = $data_lng['iso_code']; 
				}
				$lang_for_related_posts = implode(",",$lang_for_related_posts);
				
				if(Tools::strlen($name)==0) continue;
				
		       $_html .= '
		       		<tr class="'.(($y%2==0)?'':'alt_row').'">
						<td>
							<input type="checkbox" value="'.$id_pr.'"  id="groupRelated_'.$id_pr.'"
								   class="groupBox" name="ids_related_posts[]"
								   '.(in_array($id_pr,$related_posts)?'checked="checked"':'').' />
						</td>
						<td>'.$id_pr.'</td>
						<td><label class="t" for="groupRelated_'.$id_pr.'">'.$name.'</label></td>
						<td>'.$lang_for_related_posts.'</td>
					</tr>';
		       $y++;
			}
	

      	 $_html .= '
       	</table>
									
		</div>';
		
		
		$_html .=  '</div>';
		
		### related posts ####	
	    	
	    	
		$_html .= '<label>'.$this->l('Status').'</label>
				<div class = "margin-form" style="padding: 0pt 0pt 10px 130px;">';
				
		$_html .= '<select name="item_status">
					<option value=1 selected="true">'.$this->l('Enabled').'</option>
					<option value=0>'.$this->l('Disabled').'</option>
				   </select>';
			$_html .= '</div>';
			
		
			
		if($this->_is15){
    	// shop association
    	$_html .= '<div class="clear"></div>';
    	$_html .= '<label>'.$this->l('Shop association').':</label>';
    	$_html .= '<div class="margin-form">';

		$_html .= '<table width="50%" cellspacing="0" cellpadding="0" class="table">
						<tr>
							<th>Shop</th>
						</tr>';
		$u = 0;
		
		$shops = Shop::getShops();
		$shops_tmp = array();
		
		$count_shops = sizeof($shops);
		foreach($shops as $_shop){
			$id_shop = $_shop['id_shop'];
			$name_shop = $_shop['name'];
			 $_html .= '<tr>
						<td>
							<img src="../img/admin/lv2_'.((($count_shops-1)==$u)?"f":"b").'.png" alt="" style="vertical-align:middle;">
							<label class="child">';
		 
			
				$_html .= '<input type="checkbox"  
								   name="cat_shop_association[]" 
								   value="'.$id_shop.'" '.((in_array($id_shop,$shops_tmp))?'checked="checked"':'').' 
								   class="input_shop" 
								   />
								'.$name_shop.'';
				
				$_html .= '</label>
						</td>
					</tr>';
		 $u++;
		}
	
		$_html .= '</table>';
			
		$_html .= '</div>';
																
    	}
    	// shop association
			
    	
    		$_html .= '<label>&nbsp;</label>
						<div class = "margin-form"  style="margin-top:10px">
						<input type="button" value="'.$this->l('Cancel').'" 
                		   class="button"  
                		   onclick="$(\'#link-add-question-form\').show(200);$(\'#add-question-form\').hide(200);" 
                		   />&nbsp;&nbsp;&nbsp;
						<input type="submit" name="submit_item" value="'.$this->l('Save').'" 
                		   class="button"  />
                		  </div>';
    		
    		$_html .= '</form>';
    		$_html .= '</div>';
		
    		$_html .= '<br/>';
    		
			$_html .= '<table class = "table" width = 100%>
			<tr>
				<th width="5%">'.$this->l('ID').'</th>
				<th width="10%">'.$this->l('Image').'</th>';
			
			if($this->_is15){
					$_html .= '<th width=100>'.$this->l('Shop').'</th>';
    			}
				$_html .= '<th width=50>'.$this->l('Language').'</th>';
			
			$_html .= '<th>'.$this->l('Title').'</th>
				<th width="20%">'.$this->l('Date').'</th>
				<th width="5%">'.$this->l('Status').'</th>
				<th width = "7%">'.$this->l('Action').'</th>
			</tr>';
			
			$start = (int)Tools::getValue("pageitems");
			$_data = $obj_blocknewsplus->getItems(array('admin'=>1,'start' => $start,'step'=>$this->_step));
			$_items = $_data['items'];
			
			$paging = $obj_blocknewsplus->PageNav($start,$_data['count_all'],$this->_step, 
											array('admin' => 1,'currentIndex'=>$currentIndex,
												  'token' => $token,
												  'item' => 'items'
											));
			
			if(sizeof($_items)>0){
				
				foreach($_items as $_item){
					//echo "<pre>"; var_dump($_item);
					$i=1;
					$id = $_item['id'];
					$title = $_item['title'];
					$img = $_item['img'];
					$status = $_item['status'];
					$date = $_item['time_add'];
					
				$ids_lng = isset($_item['ids_lng'])?$_item['ids_lng']:array();
				$lang_for_item = array();
				foreach($ids_lng as $lng_id){
					$data_lng = Language::getLanguage($lng_id);
					$lang_for_item[] = $data_lng['iso_code']; 
				}
				$lang_for_item = implode(",",$lang_for_item);
	
				if($this->_is15){
					$ids_shops = explode(",",$_item['ids_shops']);
					
					$shops = Shop::getShops();
					$name_shop = array();
					foreach($shops as $_shop){
						$id_shop_lists = $_shop['id_shop'];
						if(in_array($id_shop_lists,$ids_shops))
							$name_shop[] = $_shop['name'];
					}
					$name_shop = implode(",<br/>",$name_shop);
				}
			
					$_html .= 
						'<tr>
						<td style = "color:black;">'.$id.'</td>';
					
					$_html .= '<td style = "color:black;" align="center">';
					
					if($img) {
                        if($this->_is_cloud){
                            $_html .= '<img src="../modules/'.$this->name.'/upload/'.$img.'" style="width:40px;height:40px"/>';
                        } else {
                            $_html .= '<img src="../upload/'.$this->name.'/'.$img.'" style="width:40px;height:40px"/>';
                        }
                        //$_html .= '<img src="../upload/' . $this->name . '/' . $img . '" style="width:40px;height:40px" />';
                    }else
						$img = '&nbsp;';
					$_html .= '</td>';
					
					
					if($this->_is15){
						$_html .= '<td style = "color:black;">'.$name_shop.'</td>';
					}
					$_html .= '<td style = "color:black;">'.$lang_for_item.'</td>';
					
					
					$_html .= '<td style = "color:black;">'.$title.'</td>';
					$_html .= '<td style = "color:black;">'.$date.'</td>';
					if($status)
						$_html .= '<td align="center"><img alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" src="../img/admin/enabled.gif"></td>';
					else
						$_html .= '<td align="center"><img alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" src="../img/admin/disabled.gif"></td>';
				
			
					$_html .= '<td>
				
								 <input type = "hidden" name = "id" value = "'.$id.'"/>
								 <a href="'.$currentIndex.'&configure='.$this->name.'&token='.$token.'&edit_item&id='.(int)($id).'&pageitems='.$start.'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a> 
								 <a href="'.$currentIndex.'&configure='.$this->name.'&token='.$token.'&delete_item&id='.(int)($id).'&pageitems='.$start.'" title="'.$this->l('Delete').'"  onclick = "javascript:return confirm(\''.$this->l('Are you sure you want to remove this item?').'\');"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>'; 
								 $_html .= '</form>
							 </td>';
					$_html .= '</tr>';
				}
			} else {
			$_html .= '<tr><td colspan="8" style="text-align:center;font-weight:bold;padding:10px">'.$this->l('Items not found').'</td></tr>';	
			}
			
			$_html .= '</table>';
		}
			
		     if($i!=0){
		    	$_html .= '<div style="margin:5px">';
		    	$_html .= $paging;
		    	$_html .= '</div>';
		    	}
		
		$_html .=	'</fieldset>'; 
		
		
     	return $_html;
    }
    
    private function _displayForm()
     {
     	$_html = '';
		
     	$_html .= $this->_drawNewsSettings();
     	
     	$_html .= '<br/><br/>';
		
     	$_html .= $this->drawNews();
     	
     	
     	
     	return $_html;
     }

     
	private function _hint(){
    	$_html = '';
    	
    	$_html .= '<p style="display: block; font-size: 11px; width: 95%; margin-bottom:20px;position:relative" class="hint clear">
    	<b style="color:#585A69">'.$this->l('If url rewriting doesn\'t works, check that this above lines exist in your current .htaccess file, if no, add it manually on top of your .htaccess file').':</b>
    	<br/><br/>
    	<code>
		RewriteRule ^(.*)news/?$ /modules/'.$this->name.'/items.php [QSA,L]
		</code>
		<br/>
		<code>
		RewriteRule ^(.*)news/([0-9a-zA-Z-_]+)/?$ /modules/'.$this->name.'/item.php?item_id=$2 [QSA,L]  
		</code>
		
			<br/><br/>
		</p>';
    	
    	return $_html;
    }
    
    
     public function _jsandcss(){
    	$_html = '';
    	
     if(version_compare(_PS_VERSION_, '1.6', '>')){
    	$_html .=  '<link rel="stylesheet" media="screen" type="text/css" href="../modules/'.$this->name.'/views/css/prestashop16.css" />';
    		
    	}
    	// custom-input-file
    	
    	$_html .= '<link rel="stylesheet" href="../modules/'.$this->name.'/views/css/custom-input-file.css" type="text/css" />';
    	$_html .= '<script type="text/javascript" src="../modules/'.$this->name.'/views/js/custom-input-file.js"></script>';
    	
    	
     $cookie = $this->context->cookie;
    
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER["PHP_SELF"]);
		
		if(defined('_MYSQL_ENGINE_') && Tools::substr(_PS_VERSION_,0,3) != '1.5'){
		$_html .=  '
			<script type="text/javascript">	
			var iso = \''.$isoTinyMCE.'\' ;
			var pathCSS = \''._THEME_CSS_DIR_.'\' ;
			var ad = \''.$ad.'\' ;
			</script>';
			$_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>';
		$_html .= '
		<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>';
		} 
		
		if(version_compare(_PS_VERSION_, '1.5', '>')  || 
			!defined('_MYSQL_ENGINE_')){
			
			if(version_compare(_PS_VERSION_, '1.5', '>')){
				$_html .=  '
			<script type="text/javascript">	
			var iso = \''.$isoTinyMCE.'\' ;
			var pathCSS = \''._THEME_CSS_DIR_.'\' ;
			var ad = \''.$ad.'\' ;
			</script>';
				$_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>';
			} else {
				$_html .=  '
			<script type="text/javascript">	
			var iso = \''.$isoTinyMCE.'\' ;
			var pathCSS = \''._THEME_CSS_DIR_.'\' ;
			var ad = \''.$ad.'\' ;
			</script>';
				$_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
				';
			}
			
			
			
		$_html .= '<script type="text/javascript">
					tinyMCE.init({
						mode : "specific_textareas",
						theme : "advanced",
						editor_selector : "rte",';
		if(version_compare(_PS_VERSION_, '1.5', '>')){
			 $_html .= 'skin:"cirkuit",';
		}
			$_html  .=  'editor_deselector : "noEditor",';
			
			if(version_compare(_PS_VERSION_, '1.6', '<')){
			$_html .=  'plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						//Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
					';
			}else{
			$_html .= 'toolbar1 : "code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,blockquote,colorpicker,pasteword,|,bullist,numlist,|,outdent,indent,|,link,unlink,|,cleanup,|,media,image",
		   			   plugins : "colorpicker link image paste pagebreak table contextmenu filemanager table code media autoresize textcolor",
		   			   ';
			}
		
						
						
		  $_html .=	   'content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
						document_base_url : "'.__PS_BASE_URI__.'",';
		  if(!defined('_MYSQL_ENGINE_')){
		  $_html .=		'width: "550",';
		  } else {
		  	if(version_compare(_PS_VERSION_, '1.5', '>'))
		  		$_html .=		'width: "650",';
		  	else
		  		$_html .= 'width: "400",';
		  }
		  
		  $_html .=	    'height: "auto",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",';
			
			if(version_compare(_PS_VERSION_, '1.5', '>')){
			$_html .= 	'elements : "nourlconvert,ajaxfilemanager",
						 file_browser_callback : "ajaxfilemanager",';
			} else {
			$_html .= 	'elements : "nourlconvert",';
			}
			
			$_html .=	'entity_encoding: "raw",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
						
					});
		</script>';
		
		}


         $_html .= '<style type="text/css">';
         if(version_compare(_PS_VERSION_, '1.6', '>')){
             $_html .= '.nobootstrap{min-width:inherit!important}
			.nobootstrap fieldset, .nobootstrap legend{
			background-color:white!important;
			border-radius: 4px;
		}

		.nobootstrap .display-form input,
		.nobootstrap .display-form select{
		border-radius: 4px;
		}

		.nobootstrap .display-form .update-button{
		border:0px!important
		}


		.displayed_flag {
		float: left;
		margin: 4px 0 0 4px;
		}

		.language_flags {
		display: none;
		float: left;
		background: #FFF;
		margin: 4px;
		padding: 8px;
		width: 80px;
		border: 1px solid #555;
		}
		.pointer {
		cursor: pointer;
		}

		.display-form .alert-success {
		background-color: #dff0d8;
		border-color: #d6e9c6;
		color: #3c763d;
		padding:10px;
		}
		.display-form .alert-danger
		{
		background-color: #f2dede;
		border-color: #ebccd1;
		color: #a94442;
		padding:10px;
		}

		';


         } else {
             $_html .= '.update-button{border: 1px solid #EBEDF4;}';
         }
         $_html .= '</style>';
		
    	return $_html;
    }	
    
    
	public function renderTplItems(){
		return Module::display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/items.tpl');
	}
	
	public function renderTplListCat(){
		return Module::display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/list.tpl');
	}
	public function renderTplItem(){
		return Module::display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/item.tpl');
	}
	
	public function translateText(){
		return array('seo_text'=> $this->l('News'),
					 'page'=>$this->l('Page'));
	}
	
}