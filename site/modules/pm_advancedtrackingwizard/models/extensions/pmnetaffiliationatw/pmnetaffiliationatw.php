<?php
/**
 * Advanced Tracking Wizard
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

class pmnetaffiliationatw extends extensionTrackerClass
{
    public $template = 'netaffiliation';
    public $extension_name = 'pmnetaffiliationatw';
    public $displayName = 'Net Affiliation';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'netaffiliation_projectid' => false,
            'netaffiliation_lead_projectid' => false,
            'netaffiliation_mclic' => false,
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['netaffiliation_projectid'] = $vars['netaffiliation_projectid'];
            $datas['netaffiliation_lead_projectid'] = $vars['netaffiliation_lead_projectid'];
            $datas['netaffiliation_mclic'] = $vars['netaffiliation_mclic'];
            $datas['active'] = $objTracking->active;
        }
        return $datas;
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        static $hasPartnerTagVente = false;
        $tracker_vars = $this->getAllVar($id_tracking);
        $trackingVenteEnabled = isset($tracker_vars['netaffiliation_mclic']) && !empty($tracker_vars['netaffiliation_mclic']) && in_array($page_name, array('order-confirmation'));
        $partnertagEnabled = isset($tracker_vars['netaffiliation_projectid']) && !empty($tracker_vars['netaffiliation_projectid']) && is_numeric($tracker_vars['netaffiliation_projectid']) && in_array($page_name, array('index', 'category', 'product', 'order', 'order-confirmation'));
        $partnertagLeadEnabled = isset($tracker_vars['netaffiliation_lead_projectid']) && !empty($tracker_vars['netaffiliation_lead_projectid']) && is_numeric($tracker_vars['netaffiliation_lead_projectid']) && in_array($page_name, array('index', 'customer-form', 'customer-created'));
        $global_tag = '';
        $md5_email = '';
        if ($partnertagEnabled || $partnertagLeadEnabled || $trackingVenteEnabled) {
            $cookie = Context::getContext()->cookie;
            $id_lang = $cookie->id_lang;
            $id_customer = $customer = false;
            if ($page_name == 'order-confirmation' && Tools::getValue('id_cart')) {
                $id_cart    = Tools::getValue('id_cart');
                $id_order   = Order::getOrderByCartId((int)$id_cart);
                $cart       = new Cart($id_cart);
                $order      = new Order($id_order);
                $id_customer = (int)$order->id_customer;
            } else {
                if (isset($params['cart']) && Validate::isLoadedObject($params['cart'])) {
                    $id_cart = (int)$params['cart']->id;
                    $cart = $params['cart'];
                    $id_customer = $cart->id_customer;
                } else {
                    $cart = $this->context->cart;
                    if (Validate::isLoadedObject($cart)) {
                        $id_cart = $cart->id;
                        $id_customer = $cart->id_customer;
                    } else {
                        $id_cart = 0;
                    }
                }
            }
            $siteType = $this->getMobileDeviceType();
            if (Validate::isLoadedObject(Context::getContext()->customer)) {
                $customer = Context::getContext()->customer;
            }
            if (Validate::isLoadedObject($customer)) {
                $id_customer = $customer->id;
                $md5_email = md5($customer->email);
            }
            if ($cookie->id_currency) {
                $currency = new Currency((int)$cookie->id_currency);
                $currency_code = $currency->iso_code;
            } else {
                $currency_code = false;
            }
        }
        if ($trackingVenteEnabled && $page_name == 'order-confirmation') {
            $orderTotalWithoutShipping = $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING);
            $global_tag .= '
            <img src="//action.metaffiliation.com/trk.php?mclic='. $tracker_vars['netaffiliation_mclic'] .'&argmon='. $orderTotalWithoutShipping .'&argann='. (int)$order->id .'&altid='. urlencode($md5_email) .'&nacur='. urlencode($currency_code) .'" width="1" height="1" border="0" />
            ';
        }
        if ($partnertagEnabled) {
            $add_tag = '';
            if ($page_name == 'index') {
                $add_tag = '
                <script type="text/javascript">
                    window.ptag_params = {
                        zone: "homepage",
                        customerId: "'. ($id_customer ? (int)$id_customer : '') .'",
                        siteType: "'. $siteType .'",
                        m_md5: "'. $md5_email .'"
                    };
                </script>
                ';
            } elseif ($page_name == 'category') {
                $id_category = (int)Tools::getValue('id_category');
                $category = new Category($id_category, $id_lang);
                $pageNb = (int)Tools::getValue('p', 1);
                if ($pageNb <= 0) {
                    $pageNb = 1;
                }
                $order_by_values = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity', 7 => 'reference');
                $order_way_values = array(0 => 'asc', 1 => 'desc');
                $orderBy = Tools::strtolower(Tools::getValue('orderby', $order_by_values[(int)Configuration::get('PS_PRODUCTS_ORDER_BY')]));
                $orderWay = Tools::strtolower(Tools::getValue('orderway', $order_way_values[(int)Configuration::get('PS_PRODUCTS_ORDER_WAY')]));
                if (!in_array($orderBy, $order_by_values)) {
                    $orderBy = $order_by_values[0];
                }
                if (!in_array($orderWay, $order_way_values)) {
                    $orderWay = $order_way_values[0];
                }
                $productList = $category->getProducts($id_lang, $pageNb, 3, $orderBy, $orderWay);
                $categoryProductList = array();
                if (is_array($productList) && sizeof($productList)) {
                    foreach ($productList as $productRow) {
                        $categoryProductList[] = (int)$productRow['id_product'];
                    }
                }
                if (Validate::isLoadedObject($category)) {
                    $add_tag = '
                    <script type="text/javascript">
                        window.ptag_params = {
                            zone: "listing",
                            categoryId: "'. $id_category .'",
                            products: ['. implode(', ', $categoryProductList) .'],
                            customerId: "'. ($id_customer ? (int)$id_customer : '') .'",
                            siteType: "'. $siteType .'",
                            m_md5: "'. $md5_email .'"
                        };
                    </script>
                    ';
                }
            } elseif ($page_name == 'product') {
                $product = new Product((int)Tools::getValue('id_product'), false, $id_lang);
                if (Validate::isLoadedObject($product)) {
                    $add_tag = '
                    <script type="text/javascript">
                        window.ptag_params = {
                            zone: "product",
                            productId: "'. (int)$product->id .'",
                            categoryId: "'. (isset($product->id_category_default) && $product->id_category_default ? (int)$product->id_category_default : '') .'",
                            customerId: "'. ($id_customer ? (int)$id_customer : '') .'",
                            siteType: "'. $siteType .'",
                            m_md5: "'. $md5_email .'"
                        };
                    </script>
                    ';
                }
            } elseif ($page_name == 'order') {
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = '{ id: "' . (int)$product['id_product'] . '", price: ' . (float)$product['price'] . ', quantity: ' . (int)$product['quantity'] . '}';
                    }
                    $add_tag = '
                    <script type="text/javascript">
                        window.ptag_params = {
                            zone: "basket",
                            products: ['. implode(',', $product_list) .'],
                            currency: "'. ($currency_code ? $currency_code : '') .'",
                            customerId: "'. ($id_customer ? (int)$id_customer : '') .'",
                            siteType: "'. $siteType .'",
                            m_md5: "'. $md5_email .'"
                        };
                    </script>
                    ';
                }
            } elseif ($page_name == 'order-confirmation') {
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = '{ id: "' . (int)$product['id_product'] . '", price: ' . (float)$product['price'] . ', quantity: ' . (int)$product['quantity'] . '}';
                    }
                    $add_tag = '
                    <script type="text/javascript">
                        window.ptag_params = {
                            zone: "transaction",
                            products: ['. implode(',', $product_list) .'],
                            transactionId: "'. (int)$order->id .'",
                            currency: "'. ($currency_code ? $currency_code : '') .'",
                            customerId: "'. ($id_customer ? (int)$id_customer : '') .'",
                            siteType: "'. $siteType .'",
                            m_md5: "'. $md5_email .'"
                        };
                    </script>
                    ';
                }
            }
            if ($add_tag) {
                $hasPartnerTagVente = true;
                $global_tag .= '<script type="text/javascript" src="//img.metaffiliation.com/u/'. fmod($tracker_vars['netaffiliation_projectid'], 41) .'/p'. $tracker_vars['netaffiliation_projectid'] .'.js"></script>';
                $global_tag .= $add_tag;
            }
        }
        if ($partnertagLeadEnabled && !$hasPartnerTagVente) {
            $original_page_name = PM_AdvancedTrackingWizard::getPageName($this->_smarty);
            $allowDependingContext = ($fromHook != 'create-account-form' || ($fromHook == 'create-account-form' && !in_array($original_page_name, array('index', 'category', 'product', 'order', 'order-confirmation'))));
            if ($allowDependingContext) {
                if ($page_name == 'index') {
                    $global_tag .= '<script type="text/javascript" src="//img.metaffiliation.com/u/'. fmod($tracker_vars['netaffiliation_lead_projectid'], 41) .'/p'. $tracker_vars['netaffiliation_lead_projectid'] .'.js?zone=accueil"></script>';
                } elseif ($page_name == 'customer-form') {
                    $global_tag .= '<script type="text/javascript" src="//img.metaffiliation.com/u/'. fmod($tracker_vars['netaffiliation_lead_projectid'], 41) .'/p'. $tracker_vars['netaffiliation_lead_projectid'] .'.js?zone=lead_inscription"></script>';
                } elseif ($page_name == 'customer-created') {
                    $global_tag .= '<script type="text/javascript" src="//img.metaffiliation.com/u/'. fmod($tracker_vars['netaffiliation_lead_projectid'], 41) .'/p'. $tracker_vars['netaffiliation_lead_projectid'] .'.js?zone=lead_confirmation&id_lead='. (int)$id_customer .'&m_md5='. urlencode($md5_email) .'"></script>';
                }
            }
        }
        return $global_tag;
    }
}
