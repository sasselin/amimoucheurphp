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

class pmzanoxatw extends extensionTrackerClass
{
    public $template = 'zanox';
    public $extension_name = 'pmzanoxatw';
    public $displayName = 'Zanox';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'zanox_id_checkout' => '',
            'zanox_id_basket' => '',
            'zanox_id_product' => '',
            'zanox_id_category' => '',
            'zanox_id_generic' => '',
            'zanox_id_home' => '',
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['zanox_id_checkout'] = $vars['zanox_id_checkout'];
            $datas['zanox_id_basket'] = $vars['zanox_id_basket'];
            $datas['zanox_id_product'] = $vars['zanox_id_product'];
            $datas['zanox_id_category'] = $vars['zanox_id_category'];
            $datas['zanox_id_generic'] = $vars['zanox_id_generic'];
            $datas['zanox_id_home'] = $vars['zanox_id_home'];
            $datas['active'] = $objTracking->active;
        }
        return $datas;
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        $tracker_vars = $this->getAllVar($id_tracking);
        $cookie = Context::getContext()->cookie;
        $link = Context::getContext()->link;
        $id_lang = $cookie->id_lang;
        if ($cookie->id_currency) {
            $currency = new Currency((int)$cookie->id_currency);
            $currency_code = $currency->iso_code;
        } else {
            $currency_code = false;
        }
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
        $tag = '';
        switch ($page_name) {
            case 'index':
                $tracking_var = 'zanox_id_home';
                $tag .= '
                <script type="text/javascript">
                var zx_language = "'. Language::getIsoById($cookie->id_lang) .'";
                </script>';
                break;
            case 'product':
                $tracking_var = 'zanox_id_product';
                $product = new Product((int)Tools::getValue('id_product'), false, $id_lang);
                if (Validate::isLoadedObject($product)) {
                    $product_category = false;
                    $product_manufacturer = false;
                    if ($product->id_category_default) {
                        $product_category = new Category((int)$product->id_category_default, $id_lang);
                    }
                    if ($product->id_manufacturer) {
                        $product_manufacturer = new Manufacturer((int)$product->id_manufacturer, $id_lang);
                    }
                    $tag .= '
                    <script type="text/javascript">
                    var zx_identifier ="'. $product->id .'";
                    var zx_fn = '. Tools::jsonEncode($product->name) .';
                    var zx_description = '. Tools::jsonEncode($product->description_short) .';
                    var zx_amount = "'. $product->getPrice(false) .'";
                    var zx_category = '. Tools::jsonEncode($product_category->name) .';
                    var zx_brand = '. Tools::jsonEncode($product_manufacturer->name) .';
                    var zx_currency = '. Tools::jsonEncode($currency_code) .';
                    var zx_url = "'. $link->getProductLink($product) .'";
                    var zx_photo = "'. $link->getImageLink($product->link_rewrite, $product->id) .'";
                    var zx_language = "'. Language::getIsoById($cookie->id_lang) .'";
                    </script>';
                }
                break;
            case 'category':
                $tracking_var = 'zanox_id_category';
                $category = new Category((int)Tools::getValue('id_category'), $id_lang);
                if (Validate::isLoadedObject($category)) {
                    $tag .= '
                    <script type="text/javascript">
                    var zx_category = '. Tools::jsonEncode($category->name) .';
                    var zx_language = "'. Language::getIsoById($cookie->id_lang) .'";
                    </script>';
                }
                break;
            case 'order':
                $tracking_var = 'zanox_id_basket';
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = array(
                            'identifier' => (int)$product['id_product'],
                            'amount' => (float)$product['price'],
                            'currency' => $currency_code,
                            'quantity' => (int)$product['quantity'],
                        );
                    }
                }
                $tag .= '
                <script type="text/javascript">
                var zx_products = '. Tools::jsonEncode($product_list) .';
                var zx_language = "'. Language::getIsoById($cookie->id_lang) .'";
                </script>';
                break;
            case 'order-confirmation':
                $tracking_var = 'zanox_id_checkout';
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = array(
                            'identifier' => (int)$product['id_product'],
                            'amount' => (float)$product['price'],
                            'currency' => $currency_code,
                            'quantity' => (int)$product['quantity'],
                        );
                    }
                }
                $orderTotalWithoutTaxes = $cart->getOrderTotal(false, Cart::BOTH);
                $orderTotalProducts = $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);
                $tag .= '
                <script type="text/javascript">
                var zx_products = '. Tools::jsonEncode($product_list) .';
                var zx_transaction = "'. (int)$order->id .'";
                var zx_total_amount = "'. (float)$orderTotalWithoutTaxes .'";
                var zx_total_currency = '. Tools::jsonEncode($currency_code) .';
                var zx_language = "'. Language::getIsoById($cookie->id_lang) .'";
                </script>';
                $tag .= '
                <!-- START of the zanox affiliate HTML code -->
                <script type="text/javascript" src="https://ad.zanox.com/pps/?16377C927439748&mode=[[1]]&CID=[[XXX]]&CustomerID=[[' . (int)$order->id_customer . ']]&OrderID=[[' . (int)$order->id . ']]&CurrencySymbol=[[' . $currency_code . ']]&TotalPrice=[[' . (float)$orderTotalProducts . ']]&PartnerID=[[XXX]]">
                </script>
                <noscript>
                <img src="https://ad.zanox.com/pps/?16377C927439748&mode=[[2]]&CID=[[XXX]]&CustomerID=[[' . (int)$order->id_customer . ']]&OrderID=[[' . (int)$order->id . ']]&CurrencySymbol=[[' . $currency_code . ']]&TotalPrice=[[' . (float)$orderTotalProducts . ']]&PartnerID=[[XXX]]" width="1" height="1" />
                </noscript>
                <!-- END zanox-affiliate HTML-Code -->
                ';
                break;
            case 'generic':
            default:
                $tracking_var = 'zanox_id_generic';
                $tag .= '
                <script type="text/javascript">
                var zx_language = "'. Language::getIsoById($cookie->id_lang) .'";
                </script>';
                break;
        }
        if (isset($tracker_vars[$tracking_var]) && !empty($tracker_vars[$tracking_var])) {
            $tag .= '
            <!-- Zanox Mastertag Code -->
            <div class="zx_'. $tracker_vars[$tracking_var] .' zx_mediaslot">
                <script type="text/javascript">
                    window._zx = window._zx || [];
                    window._zx.push({"id":"'. $tracker_vars[$tracking_var] .'"});
                    (function(d) {
                        var s = d.createElement("script"); s.async = true;
                        s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//static.zanox.com/scripts/zanox.js";
                        var a = d.getElementsByTagName("script")[0]; a.parentNode.insertBefore(s, a);
                    }(document));
                </script>
            </div>
            <!-- Zanox Mastertag Code -->
            ';
            return $tag;
        }
        return '';
    }
}
