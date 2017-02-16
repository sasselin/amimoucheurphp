<?php
/**
* Credit card offline payment
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2016 idnovate.com
*  @license   See above
*/

class CreditCardOfflinePaymentStates extends ObjectModel
{

    public static function getOrderStates($ids_only = false)
    {
        $returnStates = array();

        $states = OrderState::getOrderStates(Context::getContext()->cookie->id_lang);

        foreach ($states as $state) {
            if ($ids_only) {
                $returnStates[] = $state['id_order_state'];
            } else {
                $returnStates[] = $state;
            }
        }
        return $returnStates;
    }
}
