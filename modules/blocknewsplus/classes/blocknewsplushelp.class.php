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

class blocknewsplushelp extends Module{
	
	
	private $_width = 400;
	private $_height = 400;
	private $_name = 'blocknewsplus';
	private $_id_shop;
	private $_is15;
    private $_is_cloud;
	
	private $_width_img_block = 70;
	private $_width_img_list = 100;
	private $_width_img_item = 200;
	private $_width_img_home = 100;
	
	public function __construct(){
		if(version_compare(_PS_VERSION_, '1.5', '>')){
			$this->_id_shop = Context::getContext()->shop->id;
			$this->_is15 = 1;
		} else {
			$this->_id_shop = 0;
			$this->_is15 = 0;
		}

        if (defined('_PS_HOST_MODE_'))
            $this->_is_cloud = 1;
        else
            $this->_is_cloud = 0;




        // for test
        //$this->_is_cloud = 1;
        // for test


        if($this->_is_cloud){
            $this->path_img_cloud = DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR;
        } else {
            $this->path_img_cloud = DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR.$this->_name.DIRECTORY_SEPARATOR;

        }
		
		if (version_compare(_PS_VERSION_, '1.5', '<')){
			require_once(_PS_MODULE_DIR_.$this->_name.'/backward_compatibility/backward.php');
		}
		
		$this->initContext();
	}
	
	private function initContext()
	{
		$this->context = Context::getContext();
	}
	
	public function getItems($_data = null){
		$admin = isset($_data['admin'])?$_data['admin']:0;
		$start = isset($_data['start'])?$_data['start']:0;
		$step = isset($_data['step'])?$_data['step']:10;
		if($admin){
			
			$sql = '
			SELECT pc.*
			FROM `'._DB_PREFIX_.'blocknewsplus` pc
			ORDER BY pc.`id` DESC
			LIMIT '.(int)$start.' ,'.(int)$step.'';
			$items = Db::getInstance()->ExecuteS($sql);
			
			
			
			foreach($items as $k => $_item){
				
				if(Tools::strlen($_item['img'])>0){
					$this->generateThumbImages(array('img'=>$_item['img'], 
		    												 'width'=>$this->_width_img_block,
		    												 'height'=>$this->_width_img_block 
		    												)
		    											);
		    		$img = Tools::substr($_item['img'],0,-4)."-".$this->_width_img_block."x".$this->_width_img_block.".jpg";
		    	} else {
		    		$img = $_item['img'];
				}
				
				$items[$k]['img'] = $img;
				
				
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].'
				');
				
				$cookie = $this->context->cookie;
		
				$defaultLanguage =  $cookie->id_lang;
				
				$tmp_title = '';
				
				// languages
				$languages_tmp_array = array();
				
				
				foreach ($items_data as $item_data){
		    		$languages_tmp_array[] = $item_data['id_lang'];
					
		    		$title = isset($item_data['title'])?$item_data['title']:'';
		    		if(Tools::strlen($tmp_title)==0){
		    			if(Tools::strlen($title)>0)
		    					$tmp_title = $title; 
		    		}
		    		
		    		
		    		if($defaultLanguage == $item_data['id_lang']){
		    			$items[$k]['title'] = $item_data['title'];
		    		} 
		    		
		    		
		    	}
		    	
		    	// languages
		    	$items[$k]['ids_lng'] = $languages_tmp_array;
		    	
		    	
		    	if(@Tools::strlen($items[$k]['title'])==0)
		    		$items[$k]['title'] = $tmp_title;
		    	
			}
			

			$data_count = Db::getInstance()->getRow('
			SELECT COUNT(`id`) AS "count"
			FROM `'._DB_PREFIX_.'blocknewsplus` pc
			');
			
		} else {
			$step = Configuration::get($this->_name.'perpage_posts');
			
			$cookie = $this->context->cookie;
		
			$current_language = (int)$cookie->id_lang;
			
			$sql = '
			SELECT pc.*
			FROM `'._DB_PREFIX_.'blocknewsplus` pc 
			LEFT JOIN `'._DB_PREFIX_.'blocknewsplus_data` pc_d
			on(pc.id = pc_d.id_item)
			WHERE pc.status = 1 and pc_d.id_lang = '.(int)$current_language.' 
			AND FIND_IN_SET('.(int)$this->_id_shop.',pc.ids_shops)
			ORDER BY pc.`id` DESC
			LIMIT '.(int)$start.' ,'.(int)$step.'';
			$items_tmp = Db::getInstance()->ExecuteS($sql);
			
			$items = array();
			
			foreach($items_tmp as $k => $_item){
				
				
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].'
				');
				
				
				
				foreach ($items_data as $item_data){
		    		
		    		if($current_language == $item_data['id_lang']){
		    			$items[$k]['title'] = $item_data['title'];
		    			$items[$k]['content'] = $item_data['content'];
		    			$items[$k]['id'] = $_item['id'];
		    			################### img #######################
		    			if(Tools::strlen($_item['img'])>0){
							$this->generateThumbImages(array('img'=>$_item['img'], 
				    												 'width'=>$this->_width_img_list,
				    												 'height'=>$this->_width_img_list 
				    												)
				    											);
				    		$img = Tools::substr($_item['img'],0,-4)."-".$this->_width_img_list."x".$this->_width_img_list.".jpg";
				    	} else {
				    		$img = $_item['img'];
						}
				    		
				    	$items[$k]['img'] = $img;
		    			################### img ########################
		    			$items[$k]['time_add'] = $_item['time_add'];
		    			
		    			$items[$k]['seo_keywords'] = $item_data['seo_keywords'];
		    			$items[$k]['seo_description'] = $item_data['seo_description'];
		    			$items[$k]['seo_url'] = $item_data['seo_url'];
						
		    		} 
		    	}
		    }
			

			$data_count = Db::getInstance()->getRow('
			SELECT COUNT(pc.`id`) AS "count"
			FROM `'._DB_PREFIX_.'blocknewsplus` pc LEFT JOIN `'._DB_PREFIX_.'blocknewsplus_data` pc_d
			on(pc.id = pc_d.id_item)
			WHERE pc.status = 1 and pc_d.id_lang = '.(int)$current_language.' 
			AND FIND_IN_SET('.(int)$this->_id_shop.',pc.ids_shops)
			');
			
		}
		return array('items' => $items, 'count_all' => $data_count['count']);
	}
	
	public function saveItem($data){
	
		$ids_shops = implode(",",isset($data['cat_shop_association'])?$data['cat_shop_association']:array());
		
		$item_status = $data['item_status'];
		
		$related_posts = implode(",",isset($data['ids_related_posts'])?$data['ids_related_posts']:array());
		if(Tools::strlen($related_posts)==0) $related_posts = 0;
			
		$sql = 'INSERT into `'._DB_PREFIX_.'blocknewsplus` SET
							   `ids_shops` = "'.pSQL($ids_shops).'",
							   `related_posts` = "'.pSQL($related_posts).'",
							   `status` = \''.(int)($item_status).'\'
							   ';
		Db::getInstance()->Execute($sql);
		
		
		$post_id = Db::getInstance()->Insert_ID();
		
		foreach($data['data_title_content_lang'] as $language => $item){
		
		$title = $item['title'];
		$content = $item['content'];
		$post_seokeywords = $item['post_seokeywords'];
		$post_seodescription = $item['post_seodescription'];
		
		$seo_url_pre = Tools::strlen($item['seo_url'])>0?$item['seo_url']:$title;
	    $seo_url = $this->_translit($seo_url_pre);
		
	    $sql = 'SELECT count(*) as count
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE seo_url = "'.pSQL($seo_url,true).'" AND id_lang = '.(int)$language;
		$data_seo_url = Db::getInstance()->GetRow($sql);
		
		if($data_seo_url['count'] != 0)
			$seo_url = $seo_url."-".Tools::strtolower(Tools::passwdGen(6));
		
		$sql = 'INSERT into `'._DB_PREFIX_.'blocknewsplus_data` SET
							   `id_item` = \''.(int)($post_id).'\',
							   `id_lang` = \''.(int)($language).'\',
							   `title` = \''.pSQL($title).'\',
							   `content` = "'.pSQL($content, true).'",
							   `seo_keywords` = \''.pSQL($post_seokeywords).'\',
							   `seo_description` = \''.pSQL($post_seodescription).'\',
							   `seo_url` = "'.pSQL($seo_url).'"
							   ';
		
		Db::getInstance()->Execute($sql);
		
		
		}
		
		$this->saveImage(array('post_id' => $post_id));
		
	}
	
	
private function  _translit( $str )
	{
    $str  = str_replace(array("®","'",'"','`','?','!','.','=',':','&','+',',','’', ')', '(', '$', '{', '}'), array(''), $str );
		
	$arrru = array ("А","а","Б","б","В","в","Г","г","Д","д","Е","е","Ё","ё","Ж","ж","З","з","И","и","Й","й","К","к","Л","л","М","м","Н","н", "О","о","П","п","Р","р","С","с","Т","т","У","у","Ф","ф","Х","х","Ц","ц","Ч","ч","Ш","ш","Щ","щ","Ъ","ъ","Ы","ы","Ь", "ь","Э","э","Ю","ю","Я","я",
    " ","-",",","«","»","+","/","(",")",".");

    $arren = array ("a","a","b","b","v","v","g","g","d","d","e","e","e","e","zh","zh","z","z","i","i","y","y","k","k","l","l","m","m","n","n", "o","o","p","p","r","r","s","s","t","t","u","u","ph","f","h","h","c","c","ch","ch","sh","sh","sh","sh","","","i","i","","","e", "e","yu","yu","ya","ya",
    "-","-","","","","","","","","");

	$textout = '';
    $textout = str_replace($arrru,$arren,$str);
    
    $textout = str_replace("--","-",$textout);
    
    $separator = "-";
    $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
    $special_cases = array( '&' => 'and');
    $textout = mb_strtolower( trim( $textout ), 'UTF-8' );
    $textout = str_replace( array_keys($special_cases), array_values( $special_cases), $textout );
    $textout = preg_replace( $accents_regex, '$1', htmlentities( $textout, ENT_QUOTES, 'UTF-8' ) );
    $textout = preg_replace("/[^a-z0-9]/u", "$separator", $textout);
    $textout = preg_replace("/[$separator]+/u", "$separator", $textout);
    
    if(Tools::strlen($textout)==0)
    	$textout = Tools::strtolower(Tools::passwdGen(6));
    	
     return Tools::strtolower($textout);
	}
	
public function saveImage($data = null){
		
		$error = 0;
		$error_text = '';
		
		$post_id = $data['post_id'];
		$post_images = isset($data['post_images'])?$data['post_images']:'';
		
		$files = $_FILES['post_image'];
		
		############### files ###############################
		if(!empty($files['name']))
			{
		      if(!$files['error'])
		      {
				  $type_one = $files['type'];
				  $ext = explode("/",$type_one);
				  
				  if(strpos('_'.$type_one,'image')<1)
				  {
				  	$error_text = $this->l('Invalid file type, please try again!');
				  	$error = 1;

				  }elseif(!in_array($ext[1],array('png','x-png','gif','jpg','jpeg','pjpeg'))){
				  	$error_text = $this->l('Wrong file format, please try again!');
				  	$error = 1;
				  	
				  } else {
				  	
				  		$info_post = $this->getItem(array('id'=>$post_id));
				  		$post_item = $info_post['item'];
				  		$img_post = $post_item[0]['img'];
				  		
				  		if(Tools::strlen($img_post)>0){
				  			// delete old avatars
				  			$name_thumb = current(explode(".",$img_post));
				  			unlink(dirname(__FILE__).$this->path_img_cloud.$name_thumb.".jpg");
				  			
				  			$posts_block_img = dirname(__FILE__).$this->path_img_cloud.$name_thumb.'-'.$this->_width_img_block.'x'.$this->_width_img_block.'.jpg';
							@unlink($posts_block_img);
						
							$lists_img = dirname(__FILE__).$this->path_img_cloud.$name_thumb.'-'.$this->_width_img_list.'x'.$this->_width_img_list.'.jpg';
							@unlink($lists_img);
						
							$post_img = dirname(__FILE__).$this->path_img_cloud.$name_thumb.'-'.$this->_width_img_item.'x'.$this->_width_img_item.'.jpg';
							@unlink($post_img);
				  			
				  		} 
							
					  	srand((double)microtime()*1000000);
					 	$uniq_name_image = uniqid(rand());
					 	$type_one = Tools::substr($type_one,6,Tools::strlen($type_one)-6);
					 	$filename = $uniq_name_image.'.'.$type_one; 
					 	
						move_uploaded_file($files['tmp_name'], dirname(__FILE__).$this->path_img_cloud.$filename);
						
						$name_img = dirname(__FILE__).$this->path_img_cloud.$filename;
						$dir_without_ext = dirname(__FILE__).$this->path_img_cloud.$uniq_name_image;
						
						
						$this->copyImage(array('dir_without_ext'=>$dir_without_ext,
												'name'=>$name_img)
										);
						
						
						// image in the block "Last News"				
						$this->copyImage(array('dir_without_ext'=>$dir_without_ext,
											   'name'=>$name_img,
											   'width'=>$this->_width_img_block,
											   'height'=>$this->_width_img_block
											   )
										);
						// image in the block "Last News"	
						
						// Image in lists posts				
						$this->copyImage(array('dir_without_ext'=>$dir_without_ext,
											   'name'=>$name_img,
											   'width'=>$this->_width_img_list,
											   'height'=>$this->_width_img_list
											   )
										);
						// Image in lists posts					
										
						// Image on item page			
						$this->copyImage(array('dir_without_ext'=>$dir_without_ext,
											   'name'=>$name_img,
											   'width'=>$this->_width_img_item,
											   'height'=>$this->_width_img_item
											   )
										);
						// Image on item page					
						
						// delete original image				
						@unlink(dirname(__FILE__).$this->path_img_cloud.$uniq_name_image.".".$ext[1]);
						
						
										
										
						$img_return = $uniq_name_image.'.jpg';
			  		
				  		$this->_updateImgToItem(array('post_id' => $post_id,
				  									  'img' =>  $img_return
				  									  )
				  								);

				  }
				}
				else
					{
					### check  for errors ####
			      	switch($files['error'])
						{
							case '1':
								$error_text = $this->l('The size of the uploaded file exceeds the ').ini_get('upload_max_filesize').'b';
								break;
							case '2':
								$error_text = $this->l('The size of  the uploaded file exceeds the specified parameter  MAX_FILE_SIZE in HTML form.');
								break;
							case '3':
								$error_text = $this->l('Loaded only a portion of the file');
								break;
							case '4':
								$error_text = $this->l('The file was not loaded (in the form user pointed the wrong path  to the file). ');
								break;
							case '6':
								$error_text = $this->l('Invalid  temporary directory.');
								break;
							case '7':
								$error_text = $this->l('Error writing file to disk');
								break;
							case '8':
								$error_text = $this->l('File download aborted');
								break;
							case '999':
							default:
								$error_text = $this->l('Unknown error code!');
							break;
						}
						$error = 1;
			      	########
					   
					}
			}  else {
				//var_dump($post_images); exit;
				if($post_images != "on"){
				$this->_updateImgToItem(array('post_id' => $post_id,
				  							  'img' =>  ""
				  							  )
				  						);
				}
			}
			
		return array('error' => $error,
					 'error_text' => $error_text);
	
	
	}
	
	private function _updateImgToItem($data = null){
		
		$post_id = $data['post_id'];
		$img = $data['img'];
			
		// update
		$sql = 'UPDATE `'._DB_PREFIX_.'blocknewsplus` SET
							   `img` = \''.pSQL($img).'\'
							   WHERE id = '.(int)$post_id.'
							   ';
		Db::getInstance()->Execute($sql);
		
	}
	
	public function deleteItem($data){
		
		
		$id = $data['id'];
		
		$this->deleteImg(array('id'=>$id));
	
		
		$sql = 'DELETE FROM `'._DB_PREFIX_.'blocknewsplus`
					   WHERE id ='.(int)$id.'';
		Db::getInstance()->Execute($sql);
		
		$sql = 'DELETE FROM `'._DB_PREFIX_.'blocknewsplus_data`
					   WHERE id_item ='.(int)$id.'';
		Db::getInstance()->Execute($sql);
		
	}
	
	public function deleteImg($data = null){
		$id = $data['id'];
		
		$info_post = $this->getItem(array('id'=>$id));
  		$img = $info_post['item'][0]['img'];
				  		
		$this->_updateImgToItem(array('post_id' => $id,
				  					  'img' =>  ""
				  					 )
				  				);
				  				
		@unlink(dirname(__FILE__).$this->path_img_cloud.$img);
		
		$name_thumb = current(explode(".",$img));
		
		
		$posts_block_img = dirname(__FILE__).$this->path_img_cloud.$name_thumb.'-'.$this->_width_img_block.'x'.$this->_width_img_block.'.jpg';
		@unlink($posts_block_img);
						
		$lists_img = dirname(__FILE__).$this->path_img_cloud.$name_thumb.'-'.$this->_width_img_list.'x'.$this->_width_img_list.'.jpg';
		@unlink($lists_img);
						
		$post_img = dirname(__FILE__).$this->path_img_cloud.$name_thumb.'-'.$this->_width_img_item.'x'.$this->_width_img_item.'.jpg';
		@unlink($post_img);
		
	}
	
	public function getItem($_data){
		$id = (int)$_data['id'];
		$site = isset($_data['site'])?$_data['site']:0;
		if($site){
			$cookie = $this->context->cookie;
		
			$current_language = (int)$cookie->id_lang;
			
			$sql = '
			SELECT pc.*
			FROM `'._DB_PREFIX_.'blocknewsplus` pc 
			LEFT JOIN  `'._DB_PREFIX_.'blocknewsplus_data` pc_d
			ON(pc_d.id_item = pc.id)
			WHERE pc.id = '.(int)$id.' AND pc.status = 1 
			and pc_d.id_lang = '.(int)$current_language.' 
			AND FIND_IN_SET('.(int)$this->_id_shop.',pc.ids_shops)';
			
			
			$item = Db::getInstance()->ExecuteS($sql);
			
			
			
			foreach($item as $k => $_item){
				
				if(Tools::strlen($_item['img'])>0){
					
				$item[$k]['img_orig'] = $_item['img'];	
				$this->generateThumbImages(array('img'=>$_item['img'], 
												 'width'=>$this->_width_img_item,
												 'height'=>$this->_width_img_item 
												)
											);
				$img = Tools::substr($_item['img'],0,-4)."-".$this->_width_img_item."x".$this->_width_img_item.".jpg";
				} else {
					$img = $_item['img'];
				}
					    		
				$item[$k]['img'] = $img;
				
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].'
				');
				
				foreach ($items_data as $item_data){
						if($current_language == $item_data['id_lang']){
		    			$item[$k]['title'] = $item_data['title'];
		    			$item[$k]['content'] = $item_data['content'];
		    			$item[$k]['seo_keywords'] = $item_data['seo_keywords'];
		    			$item[$k]['seo_description'] = $item_data['seo_description'];
		    			$item[$k]['seo_url'] = $item_data['seo_url'];
						}
		    	}
		    	
			}
			
		} else { 	
			
			$sql = '
			SELECT pc.*
			FROM `'._DB_PREFIX_.'blocknewsplus` pc
			WHERE id = '.(int)$id;
			
			$item = Db::getInstance()->ExecuteS($sql);
			
			foreach($item as $k => $_item){
				
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].'
				');
				
				foreach ($items_data as $item_data){
		    			$item['data'][$item_data['id_lang']]['title'] = $item_data['title'];
		    			$item['data'][$item_data['id_lang']]['content'] = $item_data['content'];
		    			$item['data'][$item_data['id_lang']]['seo_keywords'] = $item_data['seo_keywords'];
		    			$item['data'][$item_data['id_lang']]['seo_description'] = $item_data['seo_description'];
		    			$item['data'][$item_data['id_lang']]['seo_url'] = $item_data['seo_url'];
		    	}
		    	
			}
		}
	   return array('item' => $item);
	}
	
	public function getTransformSEOURLtoIDItem($_data){

		if(Configuration::get($this->_name.'is_urlrewrite') == 1 && !is_numeric($_data['id'])){
			$id = $_data['id'];
			$sql = '
					SELECT pc.id_item as id
					FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
					WHERE seo_url = "'.pSQL($id).'"';
			$data_id = Db::getInstance()->GetRow($sql);
			$id = $data_id['id'];
		} else {
			$id = (int)$_data['id'];
		}
		
		return $id;
	}	
	
	
	
	public function updateItem($data){
		$ids_shops = implode(",",isset($data['cat_shop_association'])?$data['cat_shop_association']:array());
		
		$id = $data['id'];
		
		$related_posts = implode(",",isset($data['ids_related_posts'])?$data['ids_related_posts']:array());
		if(Tools::strlen($related_posts)==0) $related_posts = 0;
		
		$item_status = $data['item_status'];
		$post_images = $data['post_images'];
		
		// update
		$sql = 'UPDATE `'._DB_PREFIX_.'blocknewsplus` SET
							   `ids_shops` = "'.pSQL($ids_shops).'",
							   `related_posts` = "'.pSQL($related_posts).'",
							   `status` = \''.(int)($item_status).'\'
							   WHERE id = '.(int)$id.'
							   ';
		Db::getInstance()->Execute($sql);
		
		/// delete data
		$sql = 'DELETE FROM `'._DB_PREFIX_.'blocknewsplus_data` WHERE id_item = '.(int)$id.'';
		Db::getInstance()->Execute($sql);
		foreach($data['data_title_content_lang'] as $language => $item){
		
		$title = $item['title'];
		$content = $item['content'];
		$post_seokeywords = $item['post_seokeywords'];
		$post_seodescription = $item['post_seodescription'];
		$seo_url_pre = Tools::strlen($item['seo_url'])>0?$item['seo_url']:$title;
	    $seo_url = $this->_translit($seo_url_pre);
	
		
	    $sql = 'SELECT count(*) as count
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE seo_url = "'.pSQL($seo_url,true).'" AND id_lang = '.(int)$language;
		$data_seo_url = Db::getInstance()->GetRow($sql);
		
		if($data_seo_url['count'] != 0)
			$seo_url = $seo_url."-".Tools::strtolower(Tools::passwdGen(6));
			
		$sql = 'INSERT into `'._DB_PREFIX_.'blocknewsplus_data` SET
							   `id_item` = \''.(int)($id).'\',
							   `id_lang` = \''.(int)($language).'\',
							   `title` = \''.pSQL($title).'\',
							   `content` = "'.pSQL($content,true).'",
							   `seo_keywords` = \''.pSQL($post_seokeywords).'\',
							   `seo_description` = \''.pSQL($post_seodescription).'\',
							   `seo_url` = \''.pSQL($seo_url).'\'
							   
							   ';
		Db::getInstance()->Execute($sql);
		
		}
		
		$this->saveImage(array('post_id' => $id,'post_images' => $post_images ));
	}
	
	public function getItemsBlock($data = null){
	
			$width_img_block = isset($data['home'])?$this->_width_img_home:$this->_width_img_block;
			$cookie = $this->context->cookie;
		
			$current_language = (int)$cookie->id_lang;
			
			$limit  = Configuration::get($this->_name.'faq_blc');
			$sql = '
			SELECT pc.*
			FROM `'._DB_PREFIX_.'blocknewsplus` pc 
			LEFT JOIN `'._DB_PREFIX_.'blocknewsplus_data` pc_d
			ON(pc.id = pc_d.id_item) 
			WHERE pc.status = 1 and pc_d.id_lang = '.(int)$current_language.' 
			AND FIND_IN_SET('.(int)$this->_id_shop.',pc.ids_shops)
			ORDER BY pc.`id` DESC LIMIT '.(int)$limit;
			
			$items = Db::getInstance()->ExecuteS($sql);
			$items_tmp = array();
			foreach($items as $k => $_item){
				
				
				
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].'
				');
				
				
				
				foreach ($items_data as $item_data){
		    		if($current_language == $item_data['id_lang']){
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['title'] = $item_data['title'];
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['content'] = $item_data['content'];
		    			################### img #########################
		    			if(Tools::strlen($_item['img'])>0){
							$this->generateThumbImages(array('img'=>$_item['img'], 
				    												 'width'=>$width_img_block,
				    												 'height'=>$width_img_block 
				    												)
				    											);
				    		$img = Tools::substr($_item['img'],0,-4)."-".$width_img_block."x".$width_img_block.".jpg";
				    	} else {
				    		$img = $_item['img'];
						}
				    	
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['img'] = $img;
		    			################### img #########################
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['time_add'] = $_item['time_add'];
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['id'] = $_item['id'];
		    			
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['seo_keywords'] = $item_data['seo_keywords'];
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['seo_description'] = $item_data['seo_description'];
		    			$items_tmp[$k]['data'][$item_data['id_lang']]['seo_url'] = $item_data['seo_url'];
						
		    		}
		    	}
		    	
			}
		return array('items' => $items_tmp );
	}
	
	
	
public function copyImage($data){
	
		$filename = $data['name'];
		$dir_without_ext = $data['dir_without_ext'];
		
		$is_height_width = 0;
		if(isset($data['width']) && isset($data['height'])){
			$is_height_width = 1;
		}
			
		
		$width = isset($data['width'])?$data['width']:$this->_width;
		$height = isset($data['height'])?$data['height']:$this->_height;
		
		$width_orig_custom = $width;
		$height_orig_custom = $height;
		
		if (!$width){ $width = 85;}
		if (!$height){ $height = 85;}
		// Content type
		$size_img = getimagesize($filename);
		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($filename);
		$ratio_orig = $width_orig/$height_orig;
		
		if($width_orig>$height_orig){
		$height =  $width/$ratio_orig;
		}else{ 
		$width = $height*$ratio_orig;
		}
		if($width_orig<$width){
			$width = $width_orig;
			$height = $height_orig;
		}
	
		$image_p = imagecreatetruecolor($width, $height);
		$bgcolor=ImageColorAllocate($image_p, 255, 255, 255);
		//   
		imageFill($image_p, 5, 5, $bgcolor);
	
		if ($size_img[2]==2){ $image = imagecreatefromjpeg($filename);}                         
		else if ($size_img[2]==1){  $image = imagecreatefromgif($filename);}                         
		else if ($size_img[2]==3) { $image = imagecreatefrompng($filename); }
	
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		// Output
		
		if ($is_height_width)
			$users_img = $dir_without_ext.'-'.$width_orig_custom.'x'.$height_orig_custom.'.jpg';
		else
		 	$users_img = $dir_without_ext.'.jpg';
		
		if ($size_img[2]==2)  imagejpeg($image_p, $users_img, 100);                         
		else if ($size_img[2]==1)  imagejpeg($image_p, $users_img, 100);                        
		else if ($size_img[2]==3)  imagejpeg($image_p, $users_img, 100);
		imageDestroy($image_p);
		imageDestroy($image);
		//unlink($filename);

	}
	
public function generateThumbImages($data){
		
		$filename = $data['img'];
		$orig_name_img= $data['img'];
		$width = $data['width'];
		$height = $data['height'];
		
		$filename = Tools::substr($filename,0,-4)."-".$width."x".$height.".jpg";
		
		$name_img = dirname(__FILE__).$this->path_img_cloud.$filename;
		
		
		if(@filesize($name_img)==0){
		
		$uniq_name_image_without_ext = current(explode(".",$orig_name_img));
		
		$dir_without_ext = dirname(__FILE__).$this->path_img_cloud.$uniq_name_image_without_ext;
						
		$this->copyImage(
			array('dir_without_ext'=>$dir_without_ext,
			      'name'=>dirname(__FILE__).$this->path_img_cloud.$orig_name_img,
				  'width'=>$width,
				  'height'=>$height
				  )
				    );	
		}
		
		
						
	}
	
	
public function PageNav($start,$count,$step, $_data =null )
	{
		$_admin = isset($_data['admin'])?$_data['admin']:null;
		
		$data_translate = $this->getTranslateText();
		
		$res = '';
		$product_count = $count;
		$res .= '<div class="pages">';
		$res .= '<span>'.$data_translate['page'].':</span>';
		$res .= '<span class="nums">';
		
		$start1 = $start;
			for ($start1 = ($start - $step*4 >= 0 ? $start - $step*4 : 0); $start1 < ($start + $step*5 < $product_count ? $start + $step*5 : $product_count); $start1 += $step)
				{
					$par = (int)($start1 / $step) + 1;
					if ($start1 == $start)
						{
						
						$res .= '<b>'. $par .'</b>';
						}
					else
						{
							if($_admin){
								$currentIndex = $_data['currentIndex'];
								$token = $_data['token'];
								$item = $_data['item'];
								$res .= '<a href="'.$currentIndex.'&page'.$item.'='.($start1 ? $start1 : 0).'&configure='.$this->_name.'&token='.$token.'" >'.$par.'</a>';
							} else {
								
								$res .= '<a href="javascript:void(0)" onclick="go_page_news( '.($start1 ? $start1 : 0).' )">'.$par.'</a>';
								
							}
						}
				}
		
		$res .= '</span>';
		$res .= '</div>';
		
		
		return $res;
	}
	
	public function getTranslateText(){
		include_once(dirname(__FILE__).'/../blocknewsplus.php');
		$obj = new blocknewsplus();
    	$data = $obj->translateText();
    	
		return array('seo_text'=> $data['seo_text'],
					 'page'=>$data['page']);
	}
	


    public function getLangISO(){
        $cookie = $this->context->cookie;
        $id_lang = (int)$cookie->id_lang;

        $all_laguages = Language::getLanguages(true);

        if($this->isURLRewriting() && sizeof($all_laguages)>1)
            $iso_lang = Language::getIsoById((int)($id_lang))."/";
        else
            $iso_lang = '';

        return $iso_lang;

    }
    
    public function isURLRewriting(){
    	$_is_rewriting_settings = 0;
    	if(Configuration::get('PS_REWRITING_SETTINGS') && Configuration::get($this->_name.'is_urlrewrite') == 1){
			$_is_rewriting_settings = 1;
		} 
		return $_is_rewriting_settings;
    }
    
    
	public function getIdItemifFriendlyURLEnable($data){
			$seo_url = $data['seo_url'];
		    $id_lang = $data['id_lang'];
			$sql = '
					SELECT pc.id_item as id_item
					FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
					WHERE pc.seo_url = "'.pSQL($seo_url).'" and pc.id_lang = '.(int)$id_lang;
			$data_id = Db::getInstance()->GetRow($sql);
			$id_item = $data_id['id_item'];
			
			return $id_item;
	}
	
	public function getSEOFriendlyURLifFriendlyURLEnable($data){
			$id_post = $data['id_post'];
		    $id_lang = $data['id_lang'];
			$sql = '
					SELECT pc.seo_url
					FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
					WHERE pc.id_item = "'.(int)$id_post.'" and pc.id_lang = '.(int)$id_lang;
			$data_id = Db::getInstance()->GetRow($sql);
			$seo_url = $data_id['seo_url'];
			
			return $seo_url;
	}
	
public function getRelatedPosts($_data){
		$admin = isset($_data['admin'])?$_data['admin']:null;
		$items = array();
		if($admin){
			$id = isset($_data['id'])?$_data['id']:0;
			$cookie = $this->context->cookie;
			$current_language = (int)$cookie->id_lang;
			
			
			$sql = '
			SELECT pc.* 
			FROM `'._DB_PREFIX_.'blocknewsplus` pc 
			WHERE pc.status = 1 AND id != '.(int)$id.'
			AND FIND_IN_SET('.(int)$this->_id_shop.',pc.ids_shops) 
			ORDER BY pc.`time_add` DESC';
			
			
			$posts = Db::getInstance()->ExecuteS($sql);
			
			
			foreach($posts as $k => $_item){
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].'
				');
				
				
				$tmp_title = '';
				$tmp_id = '';
				$tmp_time_add = '';

				// languages
				$languages_tmp_array = array();
				
				foreach ($items_data as $item_data){
					$languages_tmp_array[] = $item_data['id_lang'];
		    		
		    		$title = isset($item_data['title'])?$item_data['title']:'';
		    		$id = isset($item_data['id_item'])?$item_data['id_item']:'';
		    		$time_add = isset($posts[$k]['time_add'])?$posts[$k]['time_add']:'';
		    		
		    		if(Tools::strlen($tmp_title)==0){
		    			if(Tools::strlen($title)>0)
		    					$tmp_title = $title; 
		    		}
		    		
					if(Tools::strlen($tmp_id)==0){
		    			if(Tools::strlen($id)>0)
		    					$tmp_id = $id; 
		    		}
		    		
					if(Tools::strlen($tmp_time_add)==0){
		    			if(Tools::strlen($time_add)>0)
		    					$tmp_time_add = $time_add; 
		    		}
		    		
		    		if($current_language == $item_data['id_lang']){
		    			$items[$k]['title'] = $item_data['title'];
		    			$items[$k]['seo_url'] = $item_data['seo_url'];
		    			$items[$k]['id'] = $id;
		    			$items[$k]['time_add'] = $time_add;
		    		}
		    		
		    	}
		    	
		    	if(@Tools::strlen($items[$k]['title'])==0)
		    		$items[$k]['title'] = $tmp_title;
		    		
		    	if(@Tools::strlen($items[$k]['id'])==0)
		    		$items[$k]['id'] = $tmp_id;
		    		
		    	if(@Tools::strlen($items[$k]['time_add'])==0)
		    		$items[$k]['time_add'] = $tmp_time_add;
		    	
		    	
		    	// languages
		    	$items[$k]['ids_lng'] = $languages_tmp_array;
			}
			
			$data_count_related_posts = Db::getInstance()->getRow('
			SELECT COUNT(`id`) AS "count"
			FROM `'._DB_PREFIX_.'blocknewsplus` WHERE status = 1 
			');
			
		} else {
			
			$id = isset($_data['id'])?$_data['id']:0;
			$cookie = $this->context->cookie;
			$current_language = (int)$cookie->id_lang;
			$related_data = $_data['related_data'];
			
			$sql = '
			SELECT pc.* 
			FROM `'._DB_PREFIX_.'blocknewsplus` pc 
			WHERE pc.status = 1 AND pc.id IN('.pSQL($related_data).')
			AND FIND_IN_SET('.(int)$this->_id_shop.',pc.ids_shops) 
			ORDER BY pc.`time_add` DESC';
			
			$posts = Db::getInstance()->ExecuteS($sql);
			
			
			foreach($posts as $k => $_item){
				$items_data = Db::getInstance()->ExecuteS('
				SELECT pc.*
				FROM `'._DB_PREFIX_.'blocknewsplus_data` pc
				WHERE pc.id_item = '.(int)$_item['id'].' AND id_lang='.(int)$current_language.'
				');
				
				
				$tmp_title = '';
				$tmp_id = '';
				$tmp_time_add = '';

				
				foreach ($items_data as $item_data){
					
		    		$title = isset($item_data['title'])?$item_data['title']:'';
		    		$id = isset($item_data['id_item'])?$item_data['id_item']:'';
		    		$time_add = isset($posts[$k]['time_add'])?$posts[$k]['time_add']:'';
		    		
		    		if(Tools::strlen($tmp_title)==0){
		    			if(Tools::strlen($title)>0)
		    					$tmp_title = $title; 
		    		}
		    		
					if(Tools::strlen($tmp_id)==0){
		    			if(Tools::strlen($id)>0)
		    					$tmp_id = $id; 
		    		}
		    		
					if(Tools::strlen($tmp_time_add)==0){
		    			if(Tools::strlen($time_add)>0)
		    					$tmp_time_add = $time_add; 
		    		}
		    		
		    		if($current_language == $item_data['id_lang']){
		    			$items[$k]['title'] = $item_data['title'];
		    			$items[$k]['seo_url'] = $item_data['seo_url'];
		    			$items[$k]['id'] = $id;
		    			$items[$k]['time_add'] = $time_add;
		    		}
		    		
		    	}
		    	
		    	if(@Tools::strlen($items[$k]['title'])==0)
		    		$items[$k]['title'] = $tmp_title;
		    		
		    	if(@Tools::strlen($items[$k]['id'])==0)
		    		$items[$k]['id'] = $tmp_id;
		    		
		    	if(@Tools::strlen($items[$k]['time_add'])==0)
		    		$items[$k]['time_add'] = $tmp_time_add;
		    	
		    	
		  	}
			
			$data_count_related_posts = Db::getInstance()->getRow('
			SELECT COUNT(`id`) AS "count"
			FROM `'._DB_PREFIX_.'blocknewsplus` WHERE status = 1  AND FIND_IN_SET('.(int)$this->_id_shop.',ids_shops) 
			');	
		} 
		
		
		return array('related_posts' => $items, 'count_all' => $data_count_related_posts['count'] );
	}
	
	
	public function getRelatedPostsForPost($data){
		
			$related_data = $data['related_data'];
			$post_id = $data['post_id'];
		
			$data_rel_posts  = $this->getRelatedPosts(array('id'=>$post_id,'related_data'=>$related_data)); 
				
			$data_posts = array();
			
			foreach($data_rel_posts['related_posts'] as $_item){
				$name = isset($_item['title'])?$_item['title']:'';
				$id_post = isset($_item['id'])?$_item['id']:'';
				$seo_url = $_item['seo_url'];
				
				$data_posts[] = array('title'=>$name,'url'=>$seo_url,'id'=>$id_post);
		       
			}
		
		return $data_posts;
	}
	
}