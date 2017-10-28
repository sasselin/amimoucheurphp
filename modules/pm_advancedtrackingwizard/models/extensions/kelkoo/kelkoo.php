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

class kelkoo extends extensionTrackerClass
{
    public $template = 'kelkoo';
    public $extension_name = 'kelkoo';
    public $displayName = 'Kelkoo';
    public $tracker_page = 'order-confirmation';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'org_id' => false,
            'event' => false,
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['org_id'] = $vars['klk_org_id'];
            $datas['event'] = $vars['klk_event'];
            $datas['active'] = $objTracking->active;
        }
        return $datas;
    }
    private function _getCarrierRulesWithCarrierName()
    {
        return Db::getInstance()->ExecuteS(
            'SELECT *
            FROM `'._DB_PREFIX_.'dateofdelivery_carrier_rule` dcr
            LEFT JOIN `'._DB_PREFIX_.'carrier` c ON (c.`id_carrier` = dcr.`id_carrier`)'
        );
    }
    private function _getCarrierRule($id_carrier_rule)
    {
        if (!(int)$id_carrier_rule) {
            return false;
        }
        return Db::getInstance()->getRow(
            'SELECT *
            FROM `'._DB_PREFIX_.'dateofdelivery_carrier_rule`
            WHERE `id_carrier_rule` = '.(int)$id_carrier_rule
        );
    }
    private function _getCarrierRuleWithIdCarrier($id_carrier)
    {
        if (!(int)$id_carrier) {
            return false;
        }
        return Db::getInstance()->getRow(
            'SELECT *
            FROM `'._DB_PREFIX_.'dateofdelivery_carrier_rule`
            WHERE `id_carrier` = '.(int)$id_carrier
        );
    }
    private function _isCarrierRuleExists($id_carrier_rule)
    {
        if (!(int)$id_carrier_rule) {
            return false;
        }
        return (bool)Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM `'._DB_PREFIX_.'dateofdelivery_carrier_rule`
            WHERE `id_carrier_rule` = '.(int)$id_carrier_rule
        );
    }
    private function _deleteByIdCarrierRule($id_carrier_rule)
    {
        if (!(int)$id_carrier_rule) {
            return false;
        }
        return Db::getInstance()->Execute(
            'DELETE FROM `'._DB_PREFIX_.'dateofdelivery_carrier_rule`
            WHERE `id_carrier_rule` = '.(int)$id_carrier_rule
        );
    }
    private function _isAlreadyDefinedForCarrier($id_carrier, $id_carrier_rule = 0)
    {
        if (!(int)$id_carrier) {
            return false;
        }
        return (bool)Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM `'._DB_PREFIX_.'dateofdelivery_carrier_rule`
            WHERE `id_carrier` = '.(int)$id_carrier.'
            '.((int)$id_carrier_rule != 0 ? 'AND `id_carrier_rule` != '.(int)$id_carrier_rule : '')
        );
    }
    private function _getDatesOfDelivery($id_carrier, $product_oos = false, $date = null)
    {
        if (!(int)$id_carrier) {
            return false;
        }
        $carrier_rule = $this->_getCarrierRuleWithIdCarrier((int)($id_carrier));
        if (empty($carrier_rule)) {
            return false;
        }
        if ($date != null && Validate::isDate($date)) {
            $date_now = strtotime($date);
        } else {
            $date_now = time();
        }
        if ($product_oos) {
            $date_now += Configuration::get('DOD_EXTRA_TIME_PRODUCT_OOS') * 24 * 3600;
        }
        if (!Configuration::get('DOD_PREPARATION_SATURDAY') && date('l', $date_now) == 'Saturday') {
            $date_now += 24 * 3600;
        }
        if (!Configuration::get('DOD_PREPARATION_SUNDAY') && date('l', $date_now) == 'Sunday') {
            $date_now += 24 * 3600;
        }
        $date_minimal_time = $date_now + ($carrier_rule['minimal_time'] * 24 * 3600) + (Configuration::get('DOD_EXTRA_TIME_PREPARATION') * 24 * 3600);
        $date_maximal_time = $date_now + ($carrier_rule['maximal_time'] * 24 * 3600) + (Configuration::get('DOD_EXTRA_TIME_PREPARATION') * 24 * 3600);
        if (!$carrier_rule['delivery_saturday'] && date('l', $date_minimal_time) == 'Saturday') {
            $date_minimal_time += 24 * 3600;
            $date_maximal_time += 24 * 3600;
        }
        if (!$carrier_rule['delivery_saturday'] && date('l', $date_maximal_time) == 'Saturday') {
            $date_maximal_time += 24 * 3600;
        }
        if (!$carrier_rule['delivery_sunday'] && date('l', $date_minimal_time) == 'Sunday') {
            $date_minimal_time += 24 * 3600;
            $date_maximal_time += 24 * 3600;
        }
        if (!$carrier_rule['delivery_sunday'] && date('l', $date_maximal_time) == 'Sunday') {
            $date_maximal_time += 24 * 3600;
        }
        $date_minimal_string = '';
        $date_maximal_string = '';
        $date_format = preg_split('/([a-z])/Ui', Configuration::get('DOD_DATE_FORMAT'), null, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($date_format as $elmt) {
            if ($elmt == 'l' || $elmt == 'F') {
                $date_minimal_string .= $this->l(date($elmt, $date_minimal_time));
                $date_maximal_string .= $this->l(date($elmt, $date_maximal_time));
            } elseif (preg_match('/[a-z]/Ui', $elmt)) {
                $date_minimal_string .= date($elmt, $date_minimal_time);
                $date_maximal_string .= date($elmt, $date_maximal_time);
            } else {
                $date_minimal_string .= $elmt;
                $date_maximal_string .= $elmt;
            }
        }
        return array(
            $date_minimal_string,
            $date_maximal_string
        );
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        $id_order   = (int) Tools::getValue('id_order');
        $id_cart    = (int) Tools::getValue('id_cart');
        $cart       = new Cart($id_cart);
        $order      = new Order($id_order);
        $org_id     = $this->getVar('klk_org_id', (int) $id_tracking);
        $orderValue = $cart->getOrderTotal(true, Cart::BOTH);
        $orderValue = round($orderValue, 2);
        $orderNumber = $id_order;
        $event = $this->getVar('klk_event', (int) $id_tracking);
        $currency_id = (int) $this->context->currency->id_currency;
        if ($currency_id) {
            $currency = new Currency($currency_id);
        }
        $reportInfo = '';
        $products = $cart->getProducts();
        if (is_array($products)) {
            foreach ($products as $product) {
                $product_id         =   $product['id_product'];
                $product_name       =   $product['name'];
                $product_price      =   round($product['price'], 2);
                $product_quantity   =   $product['quantity'];
                $reportInfo .= 'f1='.$product_id.'&f2='.$product_name.'&f3='.$product_price.'&f4='.$product_quantity.'|';
            }
        }
        if (file_exists(dirname(__FILE__) . '/../../../dateofdelivery/dateofdelivery.php') && Module::isInstalled('dateofdelivery')) {
            include_once(dirname(__FILE__) . '/../../../dateofdelivery/dateofdelivery.php');
            $datesDelivery = $this->_getDatesOfDelivery((int)($order->id_carrier), false, $order->date_add);
            if (is_array($datesDelivery) && isset($datesDelivery['1'])) {
                $expDeliveryDate = $datesDelivery['1'];
            } else {
                $expDeliveryDate = '';
            }
        } else {
            $expDeliveryDate = '';
        }
        $reportInfo = Tools::substr($reportInfo, 0, -1);
        $reportInfo = urlencode($reportInfo);
        $name = $this->context->cookie->customer_firstname . $this->context->cookie->customer_lastname;
        $email = $this->context->cookie->email;
        $review = 'name='.$name.'&email='.$email.'&expDeliveryDate='.$expDeliveryDate;
        $review = urlencode($review);
        $tag =  "<img src=\"http://tbs.tradedoubler.com/report?organization=".$org_id."&event=".$event."&orderNumber=".$orderNumber."&orderValue=".$orderValue."&currency=".$currency->iso_code."&reportInfo=".$reportInfo."&review=".$review."\" >";
        return $tag;
    }
}
