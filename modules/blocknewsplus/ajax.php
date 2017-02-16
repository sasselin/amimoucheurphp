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

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
ob_start(); 
$status = 'success';
$message = '';

$name_module = 'blocknewsplus';

if (version_compare(_PS_VERSION_, '1.5', '<')){
	require_once(_PS_MODULE_DIR_.$name_module.'/backward_compatibility/backward.php');
} else{
	$smarty = Context::getContext()->smarty;
}


include_once(dirname(__FILE__).'/classes/blocknewsplushelp.class.php');
$obj_blocknewsplushelp = new blocknewsplushelp();



		
		
$action = Tools::getValue('action');

switch ($action){
	case 'pagenav':
		$page = (int) Tools::getValue('page');
		$_html_page_nav = '';
		$_html = '';
		
		$name_module = 'blocknewsplus';
		
		$step = Configuration::get($name_module.'perpage_posts');
		$_data = $obj_blocknewsplushelp->getItems(array('start'=>$page,'step'=>$step));
		
		// strip tags for content
		foreach($_data['items'] as $_k => $_item){
			$_data['items'][$_k]['content'] = strip_tags($_item['content']);
		}
		
		$paging = $obj_blocknewsplushelp->PageNav($page,$_data['count_all'],$step);
		
		$_html_page_nav = $paging;
		
		$smarty->assign($name_module.'is_urlrewrite', Configuration::get($name_module.'is_urlrewrite'));
		
		$is_friendly_url = $obj_blocknewsplushelp->isURLRewriting();
		$_is_friendly_url = $is_friendly_url;
		$_iso_lng = $obj_blocknewsplushelp->getLangISO();
		if($_is_friendly_url){
			$smarty->assign($name_module.'iso_lng', $_iso_lng);
		} else {
			$smarty->assign($name_module.'iso_lng', '');
		}
		
		$smarty->assign(array('posts' => $_data['items'], 
							  'count_all' => $_data['count_all'],
							  'paging' => $paging
							  )
						);

        include_once(dirname(__FILE__).'/blocknewsplus.php');
        $obj_blocknewsplus = new blocknewsplus();

        $smarty->assign($name_module.'pic', $obj_blocknewsplus->getCloudImgPath());
					
		ob_start();
		if(defined('_MYSQL_ENGINE_')){

			echo $obj_blocknewsplus->renderTplListCat();
		} else {
			echo Module::display(dirname(__FILE__).'/blocknewsplus.php', 'views/templates/hooks/list.tpl');
		}
		$_html = ob_get_clean();
		
	break;
	case 'deleteimg':
		$item_id = Tools::getValue('item_id');
		$obj_blocknewsplushelp->deleteImg(array('id'=>$item_id));
	break;
	default:
		$status = 'error';
		$message = 'Unknown parameters!';
	break;
}


$response = new stdClass();
$content = ob_get_clean();
$response->status = $status;
$response->message = $message;	
if($action == "pagenav")
	$response->params = array('content' => $_html, 'page_nav' => $_html_page_nav );
else
	$response->params = array('content' => $content);
echo Tools::jsonEncode($response);

?>