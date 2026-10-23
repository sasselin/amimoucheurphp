<?php
/**
 * Adds canonical, hreflang and prev next tags to the header
 *
 * @package   gmseofields
 * @author    Dariusz Tryba (contact@greenmousestudio.com), based on Faktiva and ThirtyBees
 * @copyright Copyright (c) Green Mouse Studio (http://www.greenmousestudio.com)
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) exit;

class GmSeoFields extends Module
{
    private $nobotsControllers = array(
        '404',
        'address',
        'addresses',
        'attachment',
        'authentication',
        'cart',
        'discount',
        'footer',
        'get-file',
        'guest-tracking',
        'header',
        'history',
        'identity',
        'images.inc',
        'init',
        'my-account',
        'order',
        'order-opc',
        'order-slip',
        'order-detail',
        'order-follow',
        'order-return',
        'order-confirmation',
        'pagination',
        'password',
        'pdf-invoice',
        'pdf-order-return',
        'pdf-order-slip',
        'product-sort',
        'search',
        'statistics',
    );

    public function __construct()
    {
        $this->name = 'gmseofields';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'GreenMouseStudio.com';
        $this->bootstrap = true;
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('SEO Fields');
        $this->description = $this->l('Adds canonical, hreflang, prev, next and noindex tags to the header');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayHeader');
    }

    public function getContent()
    {
        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/gms.tpl');
    }
	
	public function hookDisplayHeaderT()
	{
		return $this->hookDisplayHeader();
	}

    public function hookDisplayHeader()
    {
        if ($this->handleNobots()) {
            // no need to add anything else as robots should ignore this page
            return;
        }
        //Nothing more neaded for ThirtyBees
        if (defined('_TB_VERSION_')) {
            return;
        }

        if (isset($this->context->controller->php_self)) {
            $this->php_self = $this->context->controller->php_self;
            return $this->getSeoFields();
        }
    }

    private function handleNobots()
    {
        if (in_array($this->context->controller->php_self, $this->nobotsControllers, true) || Tools::getValue('selected_filters')
        ) {
            $this->context->smarty->assign('nobots', true);
            return true;
        }
        return false;
    }

    public function getSeoFields()
    {
        $content = '';
        $languages = Language::getLanguages();
        $defaultLang = Configuration::get('PS_LANG_DEFAULT');
		$hreflang = null;
        switch ($this->php_self) {
            case 'product': // product page
                $idProduct = (int) Tools::getValue('id_product');
                $canonical = $this->context->link->getProductLink($idProduct);
                $hreflang = $this->getHrefLang('product', $idProduct, $languages, $defaultLang);

                break;

            case 'category':
                $idCategory = (int) Tools::getValue('id_category');
                $content .= $this->getRelPrevNext('category', $idCategory);
                $canonical = $this->context->link->getCategoryLink((int) $idCategory);
               $hreflang = $this->getHrefLang('category', $idCategory, $languages, $defaultLang);

                break;

            case 'manufacturer':
                $idManufacturer = (int) Tools::getValue('id_manufacturer');
                $content .= $this->getRelPrevNext('manufacturer', $idManufacturer);
               $hreflang = $this->getHrefLang('manufacturer', $idManufacturer, $languages, $defaultLang);

                if (!$idManufacturer) {
                    $canonical = $this->context->link->getPageLink('manufacturer');
                } else {
                    $canonical = $this->context->link->getManufacturerLink($idManufacturer);
                }

                break;

            case 'supplier':
                $idSupplier = (int) Tools::getValue('id_supplier');
                $content .= $this->getRelPrevNext('supplier', $idSupplier);
               $hreflang = $this->getHrefLang('supplier', $idSupplier, $languages, $defaultLang);

                if (!Tools::getValue('id_supplier')) {
                    $canonical = $this->context->link->getPageLink('supplier');
                } else {
                    $canonical = $this->context->link->getSupplierLink((int) Tools::getValue('id_supplier'));
                }

                break;

            case 'cms':
                $idCms = Tools::getValue('id_cms');
                $idCmsCategory = Tools::getValue('id_cms_category');
                if ($idCms) {
                    $canonical = $this->context->link->getCMSLink((int) $idCms);
                   $hreflang = $this->getHrefLang('cms', (int) $idCms, $languages, $defaultLang);
                } else {
                    $canonical = $this->context->link->getCMSCategoryLink((int) $idCmsCategory);
                   $hreflang = $this->getHrefLang('cms_category', (int) $idCmsCategory, $languages, $defaultLang);
                }
                break;

            default:
                $canonical = $this->context->link->getPageLink($this->php_self);
                $hreflang = $this->getHrefLang($this->php_self, 0, $languages, $defaultLang);
                break;
        }
        // build new content
        $content .= '<link rel="canonical" href="'.$canonical.'">'."\n";
        if (is_array($hreflang) && !empty($hreflang)) {
            foreach ($hreflang as $lang) {
                $content .= "$lang\n";
            }
        }

        return $content;
    }

    public function getHrefLang($entity, $idItem, $languages, $idLangDefault)
    {
        $links = array();
        foreach ($languages as $lang) {
            switch ($entity) {
                case 'product':
                    $lnk = $this->context->link->getProductLink((int) $idItem, null, null, null, $lang['id_lang']);
                    break;

                case 'category':
                    $lnk = $this->context->link->getCategoryLink((int) $idItem, null, $lang['id_lang']);
                    break;
                case 'manufacturer':
                    if (!$idItem) {
                        $lnk = $this->context->link->getPageLink('manufacturer', null, $lang['id_lang']);
                    } else {
                        $lnk = $this->context->link->getManufacturerLink((int) $idItem, null, $lang['id_lang']);
                    }
                    break;
                case 'supplier':
                    if (!$idItem) {
                        $lnk = $this->context->link->getPageLink('supplier', null, $lang['id_lang']);
                    } else {
                        $lnk = $this->context->link->getSupplierLink((int) $idItem, null, $lang['id_lang']);
                    }
                    break;
                case 'cms':
                    $lnk = $this->context->link->getCMSLink((int) $idItem, null, null, $lang['id_lang']);
                    break;
                case 'cms_category':
                    $lnk = $this->context->link->getCMSCategoryLink((int) $idItem, null, $lang['id_lang']);
                    break;
                default:
                    $lnk = $this->context->link->getPageLink($entity, null, $lang['id_lang']);
                    break;
            }

            // append page number
            if ($p = Tools::getValue('p')) {
                $lnk .= "?p=$p";
            }

            $links[] = '<link rel="alternate" href="'.$lnk.'" hreflang="'.$lang['language_code'].'">';
            if ($lang['id_lang'] == $idLangDefault) {
                $links[] = '<link rel="alternate" href="'.$lnk.'" hreflang="x-default">';
            }
        }

        return $links;
    }

    public function getRelPrevNext($entity, $idItem)
    {
        switch ($entity) {
            case 'category':
                $category = new Category((int) $idItem);
                $nbProducts = $category->getProducts(null, null, null, null, null, true);
                break;
            case 'manufacturer':
                $manufacturer = new Manufacturer($idItem);
                $nbProducts = $manufacturer->getProducts($manufacturer->id, null, null, null, null, null, true);
                break;
            case 'supplier':
                $supplier = new Supplier($idItem);
                $nbProducts = $supplier->getProducts($supplier->id, null, null, null, null, null, true);
                break;
            default:
                return '';
        }

        $p = Tools::getValue('p');
        $n = (int) Configuration::get('PS_PRODUCTS_PER_PAGE');

        $totalPages = ceil($nbProducts / $n);

        $linkprev = '';
        $linknext = '';
        $requestPage = $this->context->link->getPaginationLink($entity, $idItem, $n, false, 1, false);
        if (!$p) {
            $p = 1;
        }

        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            $requestPage = strtok($requestPage, '?');
        }

        if ($p > 1) { // we need prev
            $linkprev = $this->context->link->goPage($requestPage, $p - 1);
        }

        if ($totalPages > 1 && $p + 1 <= $totalPages) {
            $linknext = $this->context->link->goPage($requestPage, $p + 1);
        }

        $return = '';

        if ($linkprev) {
            $return .= '<link rel="prev" href="'.$linkprev.'">';
        }
        if ($linknext) {
            $return .= '<link rel="next" href="'.$linknext.'">';
        }
        return $return;
    }
}
