<?php

class posrotatorimg extends Module {
	
	public function __construct() {
		$this->name 		= 'posrotatorimg';
		$this->tab 			= 'front_office_features';
		$this->version 		= '1.1';
		$this->author 		= 'posthemes';
		$this->displayName 	= $this->l('Rotator img');
		$this->description 	= $this->l('Rotator img');
        
		parent :: __construct();
       
	}
	
	public function install() {
	   // Install SQL
	
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		return parent :: install()
            && $this->registerHook('footer')
			&& $this->registerHook('rotatorImg')
            && $this->registerHook('header')
            ;
	}
	
	public function uninstall(){

		include(dirname(__FILE__).'/sql/uninstall_sql.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		return parent::uninstall();
	}

  
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
	
	public function hookRotatorImg($params) {
		$idproduct = $params['product']['id_product'];
		$images= Image::getImages($this->context->language->id,$idproduct);
		$imageNew = array();
			foreach($images as $key => $val) {
				if($val['rotator'] == 1) {
					$imageNew[$key] = $val; 
					break; 
				}
			}
				$this->smarty->assign(
					array('rotator_img'=>$imageNew,
					'idproduct'=>$idproduct,
					'product'=>$params['product'],
					));

		return $this->display(__FILE__, 'rotator.tpl');
	}    
	
	public function hookdisplayHeader($params)
	{
		$this->context->controller->addCSS($this->_path.'css/posrotatorimg.css', 'all');
	}
    
    
    // Hook footer
	public function hookFooter($params) {
		return $this->display(__FILE__, 'footer.tpl');
	}        
	
}