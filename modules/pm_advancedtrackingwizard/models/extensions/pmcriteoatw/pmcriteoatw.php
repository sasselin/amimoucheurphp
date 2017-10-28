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

class pmcriteoatw extends extensionTrackerClass
{
    public $template = 'criteo';
    public $extension_name = 'pmcriteoatw';
    public $displayName = 'Criteo';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'criteo_accountid' => false,
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['criteo_accountid'] = $vars['criteo_accountid'];
            $datas['active'] = $objTracking->active;
        }
        return $datas;
    }
    private function hashEmail($email)
    {
        return md5(mb_convert_encoding(trim(Tools::strtolower($email)), "UTF-8", "ISO-8859-1"));
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        $tracker_vars = $this->getAllVar($id_tracking);
        if (isset($tracker_vars['criteo_accountid']) && !empty($tracker_vars['criteo_accountid']) && in_array($page_name, array('index', 'category', 'product', 'order', 'order-confirmation'))) {
            $cookie = Context::getContext()->cookie;
            $id_lang = $cookie->id_lang;
            if ($page_name == 'order-confirmation' && Tools::getValue('id_cart')) {
                $id_cart    = Tools::getValue('id_cart');
                $id_order   = Order::getOrderByCartId((int)$id_cart);
                $cart       = new Cart($id_cart);
                $order      = new Order($id_order);
            } else {
                if (isset($params['cart']) && Validate::isLoadedObject($params['cart'])) {
                    $id_cart = (int)$params['cart']->id;
                    $cart = $params['cart'];
                } else {
                    $cart = $this->context->cart;
                    if (Validate::isLoadedObject($cart)) {
                        $id_cart = $cart->id;
                    } else {
                        $id_cart = 0;
                    }
                }
            }
            $customer = false;
            $hashedEmail = $customerEmail = '';
            $siteType = $this->getMobileDeviceType();
            if (Validate::isLoadedObject(Context::getContext()->customer)) {
                $customer = Context::getContext()->customer;
            }
            if (!Validate::isLoadedObject($customer)) {
                $customer = false;
            } else {
                $hashedEmail = $this->hashEmail($customer->email);
                $customerEmail = trim($customer->email);
            }
            $add_tag = '';
            if ($page_name == 'index') {
                $add_tag = '
                <script type="text/javascript">
                    window.criteo_q = window.criteo_q || [];
                    window.criteo_q.push(
                        { event: "setAccount", account: '. $tracker_vars['criteo_accountid'] .' },
                        { event: "setSiteType", type: "'. $siteType .'" },
                        { event: "setHashedEmail", email: "'. $hashedEmail .'" },
                        { event: "viewHome" }
                    );
                </script>
                ';
            } elseif ($page_name == 'category') {
                $category = new Category((int)Tools::getValue('id_category'), $id_lang);
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
                        window.criteo_q = window.criteo_q || [];
                        window.criteo_q.push(
                            { event: "setAccount", account: '. $tracker_vars['criteo_accountid'] .' },
                            { event: "setSiteType", type: "'. $siteType .'" },
                            { event: "setHashedEmail", email: "'. $hashedEmail .'" },
                            { event: "viewList", item: ['. implode(', ', $categoryProductList) .'] }
                        );
                    </script>
                    ';
                }
            } elseif ($page_name == 'product') {
                $product = new Product((int)Tools::getValue('id_product'), false, $id_lang);
                if (Validate::isLoadedObject($product)) {
                    $add_tag = '
                    <script type="text/javascript">
                        window.criteo_q = window.criteo_q || [];
                        window.criteo_q.push(
                            { event: "setAccount", account: '. $tracker_vars['criteo_accountid'] .' },
                            { event: "setSiteType", type: "'. $siteType .'" },
                            { event: "setHashedEmail", email: "'. $hashedEmail .'" },
                            { event: "viewItem", item: "'. (int)$product->id .'" }
                        );
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
                        window.criteo_q = window.criteo_q || [];
                        window.criteo_q.push(
                            { event: "setAccount", account: '. $tracker_vars['criteo_accountid'] .' },
                            { event: "setSiteType", type: "'. $siteType .'" },
                            { event: "setEmail", email: "'. $customerEmail .'" },
                            { event: "viewBasket", item: ['. implode(',', $product_list) .'] }
                        );
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
                        window.criteo_q = window.criteo_q || [];
                        window.criteo_q.push(
                            { event: "setAccount", account: '. $tracker_vars['criteo_accountid'] .' },
                            { event: "setSiteType", type: "'. $siteType .'" },
                            { event: "setEmail", email: "'. $customerEmail .'" },
                            { event: "trackTransaction", id: "'. (int)$order->id .'", item: ['. implode(',', $product_list) .'] }
                        );
                    </script>
                    ';
                }
            }
            if ($add_tag) {
                $tag = '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
                $tag .= $add_tag;
                return $tag;
            }
        }
        return '';
    }
}
