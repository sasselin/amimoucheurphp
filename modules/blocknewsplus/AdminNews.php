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

ob_start();
	/*@ini_set('display_errors', 'on');	
	define('_PS_DEBUG_SQL_', true);
	define('_PS_DISPLAY_COMPATIBILITY_WARNING_', true);
	error_reporting(E_ALL|E_STRICT);
	*/
class AdminNews extends AdminTab{

	private $_is15;
	public function __construct()

	{
		$this->module = 'blocknewsplus';
		
		if(version_compare(_PS_VERSION_, '1.5', '>')){
			$this->multishop_context = Shop::CONTEXT_ALL;
			$this->_is15 = 1;
		} else {
			$this->_is15 = 0;
		}
		
		
		parent::__construct();
		
	}
	
	public function addJS(){
		
	}
	
	public function addCss(){
		
	}
	
	public function display()
	{
		echo '<style type="text/css">.warn{display:none!important}
									 #maintab20{display:none!important}
									 
		</style>';
		
		/*if (version_compare(_PS_VERSION_, '1.5', '<')){
			require_once(_PS_MODULE_DIR_.$this->module.'/backward_compatibility/backward.php');
		} else {
			$currentIndex = AdminController::$currentIndex;
		}*/

        if (version_compare(_PS_VERSION_, '1.6', '<')){
            require_once(_PS_MODULE_DIR_.$this->module.'/backward_compatibility/backward.php');
            $variables14 = variables_blocknewsplus14();
            $currentIndex = $variables14['currentindex'];
        } else {
            $currentIndex = AdminController::$currentIndex;
        }
		
		
		// include main class
		require_once(dirname(__FILE__) .  '/blocknewsplus.php');
		// instantiate
		$obj_main = new blocknewsplus();
		
		$tab = 'AdminNews';
		
		$token = $this->token;
		
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
        		
			Tools::redirectAdmin($currentIndex.'&tab='.$tab.'&configure='.$this->module.'&token='.$token);
		
		}
		
    	if (Tools::isSubmit("delete_item")) {
			if (Validate::isInt(Tools::getValue("id"))) {
				
				$data = array('id' => Tools::getValue("id"));
				$obj_blocknewsplus->deleteItem($data);
				
				$page = Tools::getValue("pageitems");
				Tools::redirectAdmin($currentIndex.'&tab='.$tab.'&configure='.$this->module.'&token='.$token.'&pageitems='.$page);
		
			}
			
		}
		
	if (Tools::isSubmit('cancel_item'))
        {
        	$page = Tools::getValue("pageitems");
        	Tools::redirectAdmin($currentIndex.'&tab='.$tab.'&configure='.$this->module.'&token='.$token.'&pageitems='.$page);
		
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
         				  'ids_related_posts'=>$ids_related_posts,
         				  'post_images' => $post_images,
         				  'cat_shop_association' => $cat_shop_association,
         				 );
         	if(sizeof($data_title_content_lang)>0)
         		$obj_blocknewsplus->updateItem($data);
         		
         	$page = Tools::getValue("pageitems");
         	Tools::redirectAdmin($currentIndex.'&tab='.$tab.'&configure='.$this->module.'&token='.$token.'&pageitems='.$page);
		
        }
		
		
		echo $obj_main->_jsandcss();
		echo $obj_main->drawNews(array('currentindex'=>$currentIndex,'controller'=>$tab));
		
				
	}
		

}

?>

