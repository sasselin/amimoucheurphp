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

class CreditCardOfflinePaymentBrands
{
    public static function getIssuers()
    {
        $issuers_array = array(
            0   =>  array(
                'name'      =>  'Visa',
                'imgName'   =>  'visa.gif',
                'length'    =>  array(13,16),
                'prefix'    =>  array(4),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            1   =>  array(
                'name'      =>  'Visa Electron',
                'imgName'   =>  'visa_electron.gif',
                'length'    =>  array(16),
                'prefix'    =>  array(417500,4917,4913),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            2   =>  array(
                'name'      =>  'MasterCard',
                'imgName'   =>  'mastercard.gif',
                'length'    =>  array(16),
                'prefix'    =>  array(51,52,53,54,55),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            3   =>  array(
                'name'      =>  'American Express',
                'imgName'   =>  'amex.gif',
                'length'    =>  array(15),
                'prefix'    =>  array(34,37),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            4   =>  array(
                'name'      =>  'Maestro',
                'imgName'   =>  'maestro.gif',
                'length'    =>  array(16,18),
                'prefix'    =>  array(5020,6),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            5   =>  array(
                'name'      =>  'DinersClub',
                'imgName'   =>  'diners.gif',
                'length'    =>  array(14,16),
                'prefix'    =>  array(300,301,302,303,304,305,36,38,55),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            6   =>  array(
                'name'      =>  'CarteBlanche',
                'imgName'   =>  'carteblanche.gif',
                'length'    =>  array(14),
                'prefix'    =>  array(300,301,302,303,304,305,36,38),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            7   =>  array(
                'name'      =>  'Discover',
                'imgName'   =>  'discover.gif',
                'length'    =>  array(16),
                'prefix'    =>  array(6011,650),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            8   =>  array(
                'name'      =>  'JCB',
                'imgName'   =>  'jcb.gif',
                'length'    =>  array(15,16),
                'prefix'    =>  array(3,1800,2131),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            9   =>  array(
                'name'      =>  'enRoute',
                'imgName'   =>  'enroute.gif',
                'length'    =>  array(15),
                'prefix'    =>  array(2014, 2149),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            10  =>  array(
                'name'      =>  'Solo',
                'imgName'   =>  'solo.gif',
                'length'    =>  array(16,18,19),
                'prefix'    =>  array(6334,6767),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            11  =>  array(
                'name'      =>  'Switch',
                'imgName'   =>  'unknown.gif',
                'length'    =>  array(16,18,19),
                'prefix'    =>  array(4903,4905,4911,4936,564182,633110,6333,6759),
                'check'     =>  true,
                'algorithm' => 'luhn'),
            12  =>  array(
                'name'      =>  'Isracard',
                'imgName'   =>  'isracard.gif',
                'length'    =>  array(8,9),
                'prefix'    =>  '',
                'check'     =>  true,
                'algorithm' => 'isracard'),
            13  =>  array(
                'name'      =>  'UnionPay',
                'imgName'   =>  'unionpay.gif',
                'length'    =>  array(16),
                'prefix'    =>  '',
                'check'     =>  true,
                'algorithm' => 'luhn'),
            );

        $authorized_issuers = explode(',', Configuration::get('CCOFFLINE_DATA_ISSUERS'));
        $issuers = array();
        foreach ($issuers_array as $k => $i) {
            $temp = array();
            $temp['id']         = $k;
            $temp['name']       = $i['name'];
            $temp['imgName']    = $i['imgName'];
            $temp['length']     = $i['length'];
            $temp['prefix']     = $i['prefix'];
            $temp['check']      = $i['check'];
            $temp['algorithm']  = $i['algorithm'];
            $temp['authorized'] = in_array($k, $authorized_issuers) ? true : false;

            $issuers[] = $temp;
        }
        return $issuers;
    }

    public static function getNameById($id_issuer)
    {
        $issuers = self::getIssuers();

        if (array_key_exists($id_issuer, $issuers)) {
            return $issuers[$id_issuer]['name'];
        } else {
            return 'Invalid issuer ID';
        }
    }

    public static function getInfoByIssuer($issuer_name)
    {
        $issuers = self::getIssuers();

        foreach ($issuers as $issuer) {
            if ($issuer['name'] == $issuer_name) {
                return $issuer;
            }
        }

        return false;
    }

    public static function setAuthorizedIssuers(array $authorized)
    {
        if (Configuration::updateValue('CCOFFLINE_DATA_ISSUERS', implode(',', $authorized))) {
            return true;
        }

        return false;
    }

    public static function setup()
    {
        $authorized = array_keys(self::getIssuers());
        self::setAuthorizedIssuers($authorized);
    }

    public static function remove()
    {
        Configuration::deleteByName('CCOFFLINE_DATA_ISSUERS');
    }
}
