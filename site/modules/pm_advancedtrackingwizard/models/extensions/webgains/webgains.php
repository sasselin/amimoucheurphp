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

class webgains extends extensionTrackerClass
{
    public $template = 'webgains';
    public $extension_name = 'webgains';
    public $displayName = 'Webgains';
    public $tracker_page = 'order-confirmation';
    public function getFormVars($id_tracking)
    {
        $datas = array(
            'wg_pin' => false,
            'wg_prog_id' => false,
            'wg_event_id' => false,
            'wg_protocol' => false,
            'wg_lang' => false,
            'active' => false,
        );
        if ($id_tracking) {
            $objTracking = new AdvancedTrackingWizardClass($id_tracking);
            $vars = $this->getAllVar($id_tracking);
            $datas['wg_pin'] = $vars['wg_pin'];
            $datas['wg_prog_id'] = $vars['wg_prog_id'];
            $datas['wg_event_id'] = $vars['wg_event_id'];
            $datas['wg_protocol'] = $vars['wg_protocol'];
            $datas['wg_lang'] = $vars['wg_lang'];
            $datas['active'] = $objTracking->active;
        }
        $datas['options'] = array(
            'wg_lang' => array('fr_FR' =>'fr_FR', 'en_US'=>'en_US'),
            'wg_protocol' => array('http'=>'http', 'https'=>'https'),
        );
        return $datas;
    }
    public function createTag($params, $id_tracking, $page_name, $fromHook)
    {
        $id_cart    = Tools::getValue('id_cart');
        $id_order   = Order::getOrderByCartId((int) $id_cart);
        $cart       = new Cart($id_cart);
        $tracker_vars = $this->getAllVar($id_tracking);
        $wgOrderValue = $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING);
        $wgOrderReference = rawurlencode($id_order);
        $wgCurrency = '';
        $currency_isocode = '';
        if ($cart->id_currency) {
            $currency = new Currency((int) $cart->id_currency);
            $currency_isocode = $currency->iso_code;
        }
        $valid_currency = array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'SKK', 'USD');
        if (in_array($currency_isocode, $valid_currency)) {
            $wgCurrency = $currency_isocode;
        }
        $voucher = $cart->getDiscounts();
        $wgVoucherCode = sizeof($voucher) ? rawurlencode($voucher[0]['name']) : '';
        $wgComment= '';
        $wgMultiple=1;
        $wgItems = '';
        $wgCustomerID= '';
        $wgProductID= '';
        $wgSLang = 'php';
        $wgVersion = '1.2';
        $wgSubDomain= 'track';
        $wgCheckString = 'wgver='.$wgVersion.'&wgsubdomain='.$wgSubDomain.'&wglang='.($tracker_vars['wg_lang'] ? $tracker_vars['wg_lang'] : '').'&wgslang='.$wgSLang.'&wgprogramid='.($tracker_vars['wg_prog_id'] ? $tracker_vars['wg_prog_id'] : '').'&wgeventid='.($tracker_vars['wg_event_id'] ? $tracker_vars['wg_event_id'] : '').'&wgvalue='.$wgOrderValue.'&wgorderreference='.$wgOrderReference.'&wgcomment='.$wgComment.'&wgmultiple='.$wgMultiple.'&wgitems='.$wgItems.'&wgcustomerid='.$wgCustomerID.'&wgproductid='.$wgProductID.'&wgvouchercode='.$wgVoucherCode;
        $wgCheckSum = md5(($tracker_vars['wg_pin'] ? $tracker_vars['wg_pin'] : '').$wgCheckString);
        $wgQueryString = $wgCheckString.'&wgchecksum='.$wgCheckSum.'&wgCurrency='.$wgCurrency;
        $wgUri = '://'.$wgSubDomain.'.webgains.com/transaction.html?'.$wgQueryString;
        $tag = '<script language="javascript" type="text/javascript">
                if(location.protocol.toLowerCase() == "https:")
                    wgProtocol="https";
                else wgProtocol="http";
                    wgUri = wgProtocol + "'.$wgUri.'" + "&wgprotocol=" + wgProtocol + "&wglocation=" + location.href;';
        $tag .= "document.write('<sc'+'ript language=\"JavaScript\"  type=\"text/javascript\" src=\"'+wgUri+'\"></sc'+'ript>');
            </script>";
        $tag .= '<noscript>
                <img src="'.($tracker_vars['wg_protocol'] ? $tracker_vars['wg_protocol'] : 'http').'://'.$wgSubDomain.'.webgains.com/transaction.html?wgrs=1&'.$wgQueryString.'&wgprotocol='.$tracker_vars['wg_protocol'].'" alt="" width="1" height="1"/>
            </noscript>';
        return $tag;
    }
}
