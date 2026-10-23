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
$_data = $obj_blocknewsplushelp->getItems();

$step = Configuration::get($name_module.'perpage_posts');

$paging = $obj_blocknewsplushelp->PageNav(0,$_data['count_all'],$step);

// strip tags for content
foreach($_data['items'] as $_k => $_item){
	$_data['items'][$_k]['content'] = strip_tags($_item['content']);
}

$smarty->assign(array('posts' => $_data['items'], 
					  'count_all' => $_data['count_all'],
					  'paging' => $paging
					  )
				);

$seo_text_data = $obj_blocknewsplushelp->getTranslateText();
$seo_text = $seo_text_data['seo_text'];
$smarty->assign('meta_title' , $seo_text);
$smarty->assign('meta_description' , $seo_text);
$smarty->assign('meta_keywords' , $seo_text);
				
$smarty->assign($name_module.'is_urlrewrite', Configuration::get($name_module.'is_urlrewrite'));
		

if(version_compare(_PS_VERSION_, '1.6', '>')){
 	$smarty->assign($name_module.'is16' , 1);
} else {
 	$smarty->assign($name_module.'is16' , 0);
}

include_once(dirname(__FILE__).'/blocknewsplus.php');
$obj_blocknewsplus = new blocknewsplus();

$smarty->assign($name_module.'pic', $obj_blocknewsplus->getCloudImgPath());

if (version_compare(_PS_VERSION_, '1.5', '>')  && version_compare(_PS_VERSION_, '1.6', '<')) {
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




if(version_compare(_PS_VERSION_, '1.5', '>')){
	
	if(version_compare(_PS_VERSION_, '1.6', '>')){
					
		$obj_front_c = new ModuleFrontController();
		$obj_front_c->module->name = 'blocknewsplus';
		$obj_front_c->setTemplate('items.tpl');
		
		$obj_front_c->setMedia();
		
		$obj_front_c->initHeader();
		$obj_front_c->initFooter();
		
		$obj_front_c->initContent();
		
		
		
		$obj_front_c->display();
		
	} else {
		echo $obj_blocknewsplus->renderTplItems();
	}
} else {
	echo Module::display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/items.tpl');
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