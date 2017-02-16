<?php
require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');
	$params = $_POST; 

	$id_product = $params['id_product'];
	$action = $params['action'];
	if($action==2) {
	$images= Image::getImages((int)Context::getContext()->language->id,$id_product);
		foreach($images as $image) {
			$id_image = $image['id_image'];
			$query = "SELECT * FROM " . _DB_PREFIX_ . "image WHERE id_image =$id_image";
			
			if(!Db::getInstance()->Execute($query)){
				return false;
			 } else {
				$result = Db::getInstance()->getRow($query);
				 if($result['rotator'] ==1) echo  $id_image; 
			 }
		}

		die;
	}
	
	$value = 0; 
	$data = array();
	$images= Image::getImages((int)Context::getContext()->language->id,$id_product);
	$id = $params['img_id']; 
	foreach($images as $image) {
		$id_image = $image['id_image'];
		
		if($id_image== $id) { $value=1; } else {$value=0;} 
		//	echo $id_image.'--'.$value;
		$query = "UPDATE " . _DB_PREFIX_ . "image SET rotator =$value WHERE id_image =$id_image";
		//echo $query; echo "<br>";
		if(!Db::getInstance()->Execute($query)){
			return false;
		} else {	
			$data [$image['id_image']]=array('id'=>$id,'id_product'=>$id_product);
		}	
	}
	
	$json = json_encode($data); 
	die(json_encode($json));

?>
