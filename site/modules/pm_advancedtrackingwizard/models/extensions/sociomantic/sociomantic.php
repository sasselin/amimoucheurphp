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

class sociomantic extends extensionTrackerClass
{
    public $template = 'sociomantic';
    public $extension_name = 'sociomantic';
    public $displayName = 'Sociomantic';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'sm_adpan_id' => false,
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['sm_adpan_id'] = $vars['sm_adpan_id'];
            $datas['active'] = $objTracking->active;
        }
        return $datas;
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        if (in_array($page_name, array('index', 'category', 'product', 'order', 'order-confirmation'))) {
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
            $tracker_vars = $this->getAllVar($id_tracking);
            $add_tag = '';
            if ($page_name == 'category') {
                $category = new Category((int)Tools::getValue('id_category'));
                if (Validate::isLoadedObject($category)) {
                    $category_tree = $category->getParentsCategories((int) $this->context->language->id);
                    $final_category_tree = array();
                    if (is_array($category_tree)) {
                        foreach ($category_tree as $cat) {
                            $final_category_tree[] = $cat['name'];
                        }
                    }
                    $final_category_tree = array_reverse($final_category_tree);
                    $add_tag = 'var product = { category: '.json_encode($final_category_tree).' };';
                }
            } elseif ($page_name == 'product') {
                $product = new Product((int)Tools::getValue('id_product'));
                if (Validate::isLoadedObject($product)) {
                    $add_tag = 'var product = { identifier: "'.json_encode($product->id).'" };';
                }
            } elseif ($page_name == 'order') {
                if ($this->context->cookie->id_currency) {
                    $currency = new Currency((int)$this->context->cookie->id_currency);
                    $currency_code = $currency->iso_code;
                } else {
                    $currency_code = '';
                }
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = array(
                            'identifier' => $product['id_product'],
                            'amount' => round($product['price_wt'], 2),
                            'currency' => $currency_code,
                            'quantity' => (int)$product['quantity'],
                        );
                    }
                    $add_tag = 'var basket = { products: '.json_encode($product_list).' };';
                }
            } elseif ($page_name == 'order-confirmation') {
                if ($this->context->cookie->id_currency) {
                    $currency = new Currency((int)$this->context->currency->id_currency);
                    $currency_code = $currency->iso_code;
                } else {
                    $currency_code = '';
                }
                $products = $cart->getProducts();
                $product_list = array();
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $product_list[] = array(
                            'identifier' => $product['id_product'],
                            'amount' => round($product['price_wt'], 2),
                            'currency' => $currency_code,
                            'quantity' => (int)$product['quantity'],
                        );
                    }
                    $orderTotal = $cart->getOrderTotal(true, Cart::BOTH);
                    $add_tag = 'var basket = { products: '.json_encode($product_list).', transaction: '.json_encode($order->id).', amount: '.json_encode($orderTotal).', currency: '.json_encode($currency_code).' };';
                }
            }
            $tag = '<script type="text/javascript">
            '.(isset($add_tag) && !empty($add_tag) ? $add_tag : '').'
            (function(){
            var s = document.createElement(\'script\');
            var x = document.getElementsByTagName(\'script\')[0];
            s.type = \'text/javascript\';
            s.async = true;
            s.src = (\'https:\'==document.location.protocol?\'https://\':\'http://\')
            + \'eu-sonar.sociomantic.com/js/2010-07-01/adpan/'.rawurlencode($tracker_vars['sm_adpan_id']).'\';
            x.parentNode.insertBefore( s, x );
            })();
            </script>';
            return $tag;
        }
        return '';
    }
}
