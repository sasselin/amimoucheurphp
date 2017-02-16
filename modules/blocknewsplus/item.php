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

$_GET['controller'] = 'all'; 
$_GET['fc'] = 'module';
$_GET['module'] = 'blocknewsplus';
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

$name_module = 'blocknewsplus';


if (version_compare(_PS_VERSION_, '1.5', '<')){
	require_once(_PS_MODULE_DIR_.$name_module.'/backward_compatibility/backward.php');
} else{
	$smarty = Context::getContext()->smarty;
}

include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
$obj_blocknewsplushelp = new blocknewsplushelp();

if(version_compare(_PS_VERSION_, '1.6', '>')){
$_http_host = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__; 
} else {
$_http_host = _PS_BASE_URL_.__PS_BASE_URI__;
}

$post_id = Tools::getValue('item_id');

$post_id = $obj_blocknewsplushelp->getTransformSEOURLtoIDItem(array('id'=>$post_id));

$_info_cat = $obj_blocknewsplushelp->getItem(array('id' => $post_id,'site'=>1));

##################
if(sizeof($_info_cat['item'])==0){

$_iso_lng = $obj_blocknewsplushelp->getLangISO();
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("HTTP/1.1 301 Moved Permanently");
Tools::redirect(_PS_BASE_URL_.__PS_BASE_URI__.$_iso_lng.'news');

}
######################

$title = isset($_info_cat['item'][0]['title'])?$_info_cat['item'][0]['title']:'';
$seo_description = isset($_info_cat['item'][0]['seo_description'])?$_info_cat['item'][0]['seo_description']:'';
$seo_keywords = isset($_info_cat['item'][0]['seo_keywords'])?$_info_cat['item'][0]['seo_keywords']:''; 

$smarty->assign('meta_title' , $title);
$smarty->assign('meta_description' , $seo_description);
$smarty->assign('meta_keywords' , $seo_keywords);

$smarty->assign($name_module.'is_urlrewrite', Configuration::get($name_module.'is_urlrewrite'));

if(version_compare(_PS_VERSION_, '1.6', '>')){
 	$smarty->assign($name_module.'is16' , 1);
} else {
 	$smarty->assign($name_module.'is16' , 0);
}

if (version_compare(_PS_VERSION_, '1.5', '>') && version_compare(_PS_VERSION_, '1.6', '<')) {
				if (isset(Context::getContext()->controller)) {
					$oController = Context::getContext()->controller;
				}
				else {
					$oController = new FrontController();
					$oController->init();
				}
				// header
				$oController->setMedia();
				@$oController->displayHeader();
			}
			else {
				if(version_compare(_PS_VERSION_, '1.5', '<'))
					include_once(dirname(__FILE__).'/../../header.php');
			}



$step = Configuration::get($name_module.'perpage_posts');

### related posts ###
$related_posts = $_info_cat['item'][0]['related_posts']; 
$data_related_posts = $obj_blocknewsplushelp->getRelatedPostsForPost(array('related_data'=>$related_posts,'post_id'=>$post_id));
### related posts ###
				
$smarty->assign(array('posts' => $_info_cat['item'],'related_posts'=>$data_related_posts));



include_once(dirname(__FILE__).'/blocknewsplus.php');
$obj_blocknewsplus = new blocknewsplus();


$smarty->assign($name_module.'pic', $obj_blocknewsplus->getCloudImgPath());

if(version_compare(_PS_VERSION_, '1.5', '>')){
	if(version_compare(_PS_VERSION_, '1.6', '>')){
					
		$obj_front_c = new ModuleFrontController();
		$obj_front_c->module->name = 'blocknewsplus';
		$obj_front_c->setTemplate('item.tpl');
		
		$obj_front_c->setMedia();
		
		$obj_front_c->initHeader();
		$obj_front_c->initFooter();
		
		$obj_front_c->initContent();
		
		
		
		$obj_front_c->display();
		
	} else {
		echo $obj_blocknewsplus->renderTplItem();
	}
} else {
	echo Module::display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/item.tpl');
}
if (version_compare(_PS_VERSION_, '1.5', '>')  && version_compare(_PS_VERSION_, '1.6', '<')) {
				if (isset(Context::getContext()->controller)) {
					$oController = Context::getContext()->controller;
				}
				else {
					$oController = new FrontController();
					$oController->init();
				}
				// footer
				@$oController->displayFooter();
			}
			else {
				if(version_compare(_PS_VERSION_, '1.5', '<'))
					include_once(dirname(__FILE__).'/../../footer.php');
			}