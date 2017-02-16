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

class blocknewsplusAllModuleFrontController extends ModuleFrontController
{
	
	public function init()
	{
		$http_referrer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$news_exists = stripos($http_referrer, 'news');
		
		// news
		if($news_exists != false){
			
		
		include_once(dirname(__FILE__).'../../../classes/blocknewsplushelp.class.php');
		$obj = new blocknewsplushelp();
		$is_friendly_url = $obj->isURLRewriting();
		
		if($is_friendly_url){
			
			
		if(Tools::strlen($http_referrer)>0){
				
			$lang_iso_redirect = Language::getIsoById((int)(Tools::getValue("id_lang")));
		
			$languages = Language::getLanguages(false);
			foreach ($languages as $language){
				$iso_array = Language::getIsoById((int)($language['id_lang']));
				if(preg_match('/\/'.$iso_array.'\//i',$http_referrer)){
					$iso_code = $iso_array;
					break;
				}
			}
			$seo_friendly_url = '';
			$item_seo_url = '';
			#### for change language ###
			$post_seo_url = Tools::getValue('item_id');
			if($post_seo_url){
			$id_post = $obj->getIdItemifFriendlyURLEnable(array('seo_url'=>$post_seo_url,
																'id_lang'=>Language::getIdByIso($iso_code))
														  );
			$seo_friendly_url = $obj->getSEOFriendlyURLifFriendlyURLEnable(array('id_post'=>$id_post,
																				 'id_lang'=>Language::getIdByIso($lang_iso_redirect)
																				 )
																	   );
			if(empty($seo_friendly_url)) $seo_friendly_url = $post_seo_url;
			$item_seo_url = $post_seo_url;
			}
			#### for change language ###
			
																		   
			
			
				$to = '/'.$iso_code.'/';
				$from = '/'.$lang_iso_redirect.'/';
				$http_referrer = str_replace($to,$from,$http_referrer);
				
				$to = $item_seo_url;
				$from = $seo_friendly_url;
				$http_referrer = str_replace($to,$from,$http_referrer);
				
			
		} else {
			
			if(version_compare(_PS_VERSION_, '1.6', '>')){
			$_http_host = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__; 
			} else {
			$_http_host = _PS_BASE_URL_.__PS_BASE_URI__;
			}

			$request_uri = isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'';
			$query_string = isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'';
			
			
			if(Tools::strlen($query_string)==0){
				$explode_request_url = explode("/",$request_uri);
				$lang_iso = isset($explode_request_url[1])?"/".$explode_request_url[1]:"";
				$http_referrer = $_http_host.$lang_iso."/news";
			}
				
			$post_seo_url = Tools::getValue('item_id');
			if($post_seo_url){
				$explode_request_url = explode("/",$request_uri);
				$lang_iso = isset($explode_request_url[1])?"/".$explode_request_url[1]:"";
				$http_referrer = $_http_host.$lang_iso."/news/".$post_seo_url;
			}
			
			
				
		}
		
		}
		
		}
		header("HTTP/1.1 301 Moved Permanently");
		Tools::redirect($http_referrer);
		parent::init();
	}
	
	public function setMedia()
	{
		parent::setMedia();
		
	}

	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$this->setTemplate('all.tpl');
		
	}
}