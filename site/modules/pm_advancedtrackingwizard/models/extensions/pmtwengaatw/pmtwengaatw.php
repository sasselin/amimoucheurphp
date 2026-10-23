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

class pmtwengaatw extends extensionTrackerClass
{
    public $template = 'twenga';
    public $extension_name = 'pmtwengaatw';
    public $displayName = 'Twenga';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'twenga_hashkey' => false,
            'twenga_tld' => 'fr',
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['twenga_hashkey'] = $vars['twenga_hashkey'];
            $datas['twenga_tld'] = $vars['twenga_tld'];
            $datas['active'] = $objTracking->active;
        }
        return $datas;
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        $tracker_vars = $this->getAllVar($id_tracking);
        if (isset($tracker_vars['twenga_hashkey']) && isset($tracker_vars['twenga_tld']) && !empty($tracker_vars['twenga_hashkey']) && !empty($tracker_vars['twenga_tld']) && in_array($page_name, array('category', 'product', 'order', 'order-confirmation'))) {
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
            if (Validate::isLoadedObject(Context::getContext()->customer)) {
                $customer = Context::getContext()->customer;
            }
            if (!Validate::isLoadedObject($customer)) {
                $customer = false;
            }
            $add_tag = '';
            if ($page_name == 'category') {
                $category = new Category((int)Tools::getValue('id_category'), $id_lang);
                if (Validate::isLoadedObject($category)) {
                    $add_tag = '
                    <div id="twcm_main" style="display:none;">
                        <div class="twcm_key">'.$tracker_vars['twenga_hashkey'].'</div>
                        <div class="twcm_event">category</div>
                        '.(Validate::isLoadedObject($customer) ? '<div class="twcm_user_id">'.(int)$customer->id.'</div>
                        <div class="twcm_user_firstname">'.$customer->firstname.'</div>
                        <div class="twcm_user_lastname">'.$customer->lastname.'</div>
                        <div class="twcm_user_email">'.$customer->email.'</div>' : '').'
                        <div class="twcm_category_name">'.$category->name.'</div>
                    </div>
                    ';
                }
            } elseif ($page_name == 'product') {
                $product = new Product((int)Tools::getValue('id_product'), false, $id_lang);
                if (Validate::isLoadedObject($product)) {
                    $product_category = false;
                    if ($product->id_category_default) {
                        $product_category = new Category((int)$product->id_category_default, $id_lang);
                    }
                    $add_tag = '
                    <div id="twcm_main" style="display:none;">
                        <div class="twcm_key">'.$tracker_vars['twenga_hashkey'].'</div>
                        <div class="twcm_event">product</div>
                        <div class="twcm_item">
                            <div class="twcm_item_id">'.(int)$product->id.'</div>
                            <div class="twcm_item_price_et">'.(int)$product->getPrice(false).'</div>
                            '.(Validate::isLoadedObject($product_category) ? '<div class="twcm_item_category_name">'.$product_category->name.'</div>' : '').'
                        </div>
                        '.(Validate::isLoadedObject($customer) ? '<div class="twcm_user_id">'.(int)$customer->id.'</div>
                        <div class="twcm_user_firstname">'.$customer->firstname.'</div>
                        <div class="twcm_user_lastname">'.$customer->lastname.'</div>
                        <div class="twcm_user_email">'.$customer->email.'</div>' : '').'
                    </div>';
                }
            } elseif ($page_name == 'order') {
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = '
                        <div class="twcm_item">
                            <div class="twcm_item_id">'.(int)$product['id_product'].'</div>
                            <div class="twcm_item_quantity">'.(int)$product['quantity'].'</div>
                            <div class="twcm_item_price_et">'.(float)$product['price'].'</div>
                            <div class="twcm_item_name">'.$product['name'].'</div>
                        </div>
                        ';
                    }
                    $add_tag = '
                    <div id="twcm_main" style="display:none;">
                        <div class="twcm_key">'.$tracker_vars['twenga_hashkey'].'</div>
                        <div class="twcm_event">basket</div>
                        '.(Validate::isLoadedObject($customer) ? '<div class="twcm_user_id">'.(int)$customer->id.'</div>
                        <div class="twcm_user_firstname">'.$customer->firstname.'</div>
                        <div class="twcm_user_lastname">'.$customer->lastname.'</div>
                        <div class="twcm_user_email">'.$customer->email.'</div>' : '').'
                        '.implode('', $product_list).'
                    </div>
                    ';
                }
            } elseif ($page_name == 'order-confirmation') {
                $currency = new Currency((int) Context::getContext()->currency->id);
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = '
                        <div class="twcm_item">
                            <div class="twcm_item_id">'.(int)$product['id_product'].'</div>
                            <div class="twcm_item_quantity">'.(int)$product['quantity'].'</div>
                            <div class="twcm_item_price_et">'.(float)$product['price'].'</div>
                            <div class="twcm_item_name">'.$product['name'].'</div>
                        </div>
                        ';
                    }
                    $orderTotal = $cart->getOrderTotal(true, Cart::BOTH);
                    $orderTotalWithoutTaxes = $cart->getOrderTotal(false, Cart::BOTH);
                    $orderTotalTax = $orderTotal - $orderTotalWithoutTaxes;
                    $orderTotalShipping = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
                    $add_tag = '
                    <div id="twcm_main" style="display:none;">
                        <div class="twcm_key">'.$tracker_vars['twenga_hashkey'].'</div>
                        <div class="twcm_event">transaction</div>
                        <div class="twcm_order_id">'.(int)$order->id.'</div>
                        <div class="twcm_order_amount_ati">'.(float)$orderTotal.'</div>
                        <div class="twcm_order_amount_shipping">'.(float)$orderTotalShipping.'</div>
                        <div class="twcm_order_amount_tax">'.(float)$orderTotalTax.'</div>
                        <div class="twcm_order_master_account_id">PrestaShop</div>
                        '.(Validate::isLoadedObject($currency) ? '<div class="twcm_order_currency">'.$currency->sign.'</div>' : '').'
                        '.(Validate::isLoadedObject($customer) ? '<div class="twcm_user_id">'.(int)$customer->id.'</div>
                        <div class="twcm_user_firstname">'.$customer->firstname.'</div>
                        <div class="twcm_user_lastname">'.$customer->lastname.'</div>
                        <div class="twcm_user_email">'.$customer->email.'</div>' : '').'
                        '.implode('', $product_list).'
                    </div>
                    ';
                }
            }
            $tag = '<script async="true" language="javascript" type="text/javascript" src="//tracker.twenga.'.$tracker_vars['twenga_tld'].'/tracker.js"></script>';
            $tag .= $add_tag;
            return $tag;
        }
        return '';
    }
}
