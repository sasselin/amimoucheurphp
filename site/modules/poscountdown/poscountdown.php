<?php
/**
 */
if (!defined('_PS_VERSION_'))
    exit;

class Poscountdown extends Module
{
    public function __construct()
    {
        $this->name = 'poscountdown';
        $this->version = '1.6';
        $this->author = 'posthemes';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->html = array();
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        parent::__construct();
        $this->displayName = $this->l('Pos Countdown time price ');
        $this->description = $this->l('Module config time countdown price');
        $this->_searched_email = null;
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayHeader') ||
            !$this->registerHook('displayHome') ||
            !$this->registerHook('timecountdown') ||
            !$this->registerHook('displayProductTab')||
            !$this->registerHook('displayFooterProduct')

        ){
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

  
    public function hookdisplayHeader($params)
    {
        $this->context->controller->addJS($this->_path . 'js/jquery.plugin.js', 'all');
         $this->context->controller->addJS($this->_path . 'js/jquery.countdown.js', 'all');
        $this->context->controller->addCSS($this->_path . 'css/jquery.countdown.css', 'all');
    }

    public function hooktimecountdown($params)
    {
        $product= $params['product'];
        if(isset($product->specificPrice)){
            $id_product = (int)$product->specificPrice['id_product'];
            $id_cate = (int)$product->id_category_default;
            $enddate =  (int)strtotime($product->specificPrice['to']);
            $this->context->smarty->assign(array(
                'idproduct' => $id_product,
                'enddate' => $enddate,
                'id_cate'=>$id_cate
            ));
        }
        else {
            if(isset($params['product']['specific_prices'])){
            $id_product = (int)$params['product']['id_product'];
            $id_cate = (int)$params['product']['id_category_default'];
            if(isset($params['productCate'])){
                $id_cate =   $params['productCate']['id'];
                $this->context->smarty->assign('id_cate',$id_cate);
            }
            $enddate =  $params['product']['specific_prices']['to'];
            $this->context->smarty->assign(array(
                'idproduct' => $id_product,
                'enddate' => $enddate,
                'id_cate'=>$id_cate
            ));
            }
        }
        return $this->display(__FILE__,'countdown.tpl');
    }


}