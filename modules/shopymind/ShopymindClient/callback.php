<?php
/**
 * Callback
 *
 * @package ShopymindClient
 * @copyright Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license New BSD license (http://license.idvive.com)
 * @author Yannick Dalbin <yannick@idvive.com>
 * @version $Id callback.php 2013-04-23$
 */
require_once (dirname(__FILE__) . '/../../../config/config.inc.php');
if (isset($smarty))
    require_once (dirname(__FILE__) . '/../../../init.php');
if (! class_exists('ShopyMind', false))
    require_once (dirname(__FILE__) . '/../shopymind.php');
if (file_exists(dirname(__FILE__) . '/callback_override.php'))
    require_once (dirname(__FILE__) . '/callback_override.php');

class ShopymindClient_Callback
{

    /**
     * Permet de récupérer les informations utilisateur(s)
     * en fonction de son identifiant
     *
     * Paramètre : $id -> L'ID de l'utilisateur ou liste d'utilisateurs
     *
     * Retour attendu : Tableau attendu (ou tableau de tableaux) :
     * [
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'locale'
     * ]
     *
     * @param mixed|array $id
     * @return array
     */
    public static function getUser($id)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        if (_PS_VERSION_ < '1.5.4') {
            $query = '
                SELECT a.`active`, a.`deleted`, a.`optin`, a.`newsletter`, a.`birthday`, a.`date_add`, a.`date_upd`, a.`id_customer`, a.`id_gender`, a.`lastname`, a.`firstname`, a.`email`,c.`iso_code`, SUBSTRING(d.`accept_language`,1,2) AS `iso_code2`,`country`.`iso_code` as `country_code`' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? ', a.`id_shop`' : ', "0" AS `id_shop`') . '
                FROM `' . _DB_PREFIX_ . 'customer` a
                LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
                LEFT JOIN `' . _DB_PREFIX_ . 'cart` b ON (a.`id_customer` = b.`id_customer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'lang` c ON (b.`id_lang` = c.`id_lang`)
                LEFT JOIN `' . _DB_PREFIX_ . 'guest` d ON (a.`id_customer` = d.`id_customer`)
                WHERE a.`id_customer` IN(' . (implode(', ', array_map('pSQL', (array) $id))) . ')
                GROUP BY a.`email`';
        } else {
            $query = '
                SELECT a.`active`, a.`deleted`, a.`optin`, a.`newsletter`, a.`birthday`, a.`date_add`, a.`date_upd`, a.`id_customer`, a.`id_gender`, a.`lastname`, a.`firstname`, a.`email`, b.`iso_code`, `country`.`iso_code` as `country_code` ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? ', a.`id_shop`' : ', "0" AS `id_shop`') . '
                FROM `' . _DB_PREFIX_ . 'customer` a
                LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
                LEFT JOIN `' . _DB_PREFIX_ . 'lang` b ON (a.`id_lang` = b.`id_lang`)
                WHERE a.`id_customer` IN(' . (implode(', ', array_map('pSQL', (array) $id))) . ')
                GROUP BY a.`email`';
        }

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && count($results)) {
            foreach ($results as $row) {
                // Mlle = Mme
                if ($row['id_gender'] === 3)
                    $row['id_gender'] = 2;
                $return[] = array(
                    'id_customer' => $row['id_customer'],
                    'shop_id_shop' => $row['id_shop'],
                    'locale' => self::getLocaleFromSqlRow($row),
                    'first_name' => $row['firstname'],
                    'last_name' => $row['lastname'],
                    'birthday' => (isset($row['birthday']) ? $row['birthday'] : 0),
                    'email' => $row['email'],
                    'optin' => $row['optin'],
                    'newsletter' => $row['newsletter'],
                    'customer_since' => $row['date_add'],
                    'customer_update' => $row['date_upd'],
                    'gender' => (isset($row['id_gender']) && ($row['id_gender'] == 1 || $row['id_gender'] == 2) ? $row['id_gender'] : 0),
                    'date_last_order' => self::getDateLastOrder($row['id_customer']),
                    'nb_order' => self::countCustomerOrder($row['id_customer']),
                    'sum_order' => self::sumCustomerOrder($row['id_customer']),
                    'nb_order_year' => self::countCustomerOrderYear($row['id_customer']),
                    'sum_order_year' => self::sumCustomerOrderYear($row['id_customer']),
                    'groups' => self::getSpecificCustomerGroups($row['id_customer']),
                    'active' => ((int) $row['deleted'] === 1 || (int) $row['active'] === 0) ? 0 : 1,
                    'addresses' => self::getSpecificCustomerAddresses($row['id_customer'])
                );
            }
        }
        return (sizeof($return) === 1 ? $return[0] : $return);
    }

    /**
     * Permet de récupérer la liste des statuts de commandes
     *
     *
     * Retour attendu : Un tableau de liste de statuts
     * [
     * 'key' => 'value',
     * ]
     *
     * @return array
     */
    public static function getOrdersStatus()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        if (_PS_VERSION_ < "1.4.7" || (_PS_VERSION_ >= "1.5.0.0" && _PS_VERSION_ < "1.5.0.5")) {
            $query = 'SELECT os.`id_order_state`, osl.`name`
        FROM `' . _DB_PREFIX_ . 'order_state` os
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) Configuration::get('PS_LANG_DEFAULT') . ')
        ORDER BY `name` ASC';
        } else {
            $query = 'SELECT os.`id_order_state`, osl.`name`
        FROM `' . _DB_PREFIX_ . 'order_state` os
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) Configuration::get('PS_LANG_DEFAULT') . ')
        WHERE deleted = 0
        ORDER BY `name` ASC';
        }
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[$row['id_order_state']] = $row['name'];
            }
        }
        return $return;
    }

    /**
     * Récupération de la liste des timezones de la boutique (des clients)
     *
     * Retour attendu : un tableau avec la liste des fuseaux horaires des clients de la boutique
     * [
     * [
     * 'country_code': 'US',
     * 'region_code': 'GA',
     * ]
     * ]
     *
     * @param string|false $lastUpdate
     * @return array
     */
    public static function getTimezones($id_shop, $lastUpdate)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $query = 'SELECT `country`.`iso_code` as `country_code`, `state`.`iso_code` as `region_code`
            FROM `' . _DB_PREFIX_ . 'address` AS `address`
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` AS `a` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE (`address`.`id_customer` IS NOT NULL AND `address`.`id_customer` != 0)' . ($lastUpdate ? ' AND `address`.`date_upd` >= "' . pSQL($lastUpdate) . '"' : '') . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            GROUP BY country_code,region_code';

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    /**
     * Récupérer la liste des clients qui fêtent leur anniversaire
     *
     * Params : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbDays = Nombre de jours de l'envoi avant la date anniv
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @return array
     */
    public static function getBirthdayClients($id_shop, $dateReference, $timezones, $nbDays = 0, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        $birthDate = date("m", strtotime($dateReference . ' + ' . $nbDays . ' days')) . '-' . date("d", strtotime($dateReference . ' + ' . $nbDays . ' days'));

        if (_PS_VERSION_ < '1.5.4') {
            $query = 'SELECT a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE DATE_FORMAT(a.`birthday`, "%m-%d") = "' . pSQL($birthDate) . '" AND ' . $timezonesWhere . '
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
            AND a.deleted = 0
            AND a.active = 1
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            GROUP BY a.`id_customer`
            ORDER BY `address`.`date_upd` DESC';
        } else {
            $query = 'SELECT a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE DATE_FORMAT(a.`birthday`, "%m-%d") = "' . pSQL($birthDate) . '" AND ' . $timezonesWhere . '
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
            AND a.deleted = 0
            AND a.active = 1
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            GROUP BY a.`id_customer`
            ORDER BY `address`.`date_upd` DESC';
        }

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Récupérer la liste des clients qui fêtent leur anniversaire de création de compte client
     *
     * Params : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbDays = Nombre de jours de l'envoi avant la date anniv
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @return array
     */
    public static function getBirthdayClientsSignUp($id_shop, $dateReference, $timezones, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();

        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        if (_PS_VERSION_ < '1.5.4') {
            $query = 'SELECT a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE DATE_FORMAT(a.`date_add`, "%m-%d") = DATE_FORMAT("' . pSQL($dateReference) . '", "%m-%d") AND DATE_FORMAT(a.`date_add`, "%Y") < DATE_FORMAT("' . pSQL($dateReference) . '", "%Y") AND ' . $timezonesWhere . '
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
           AND a.deleted = 0
            AND a.active = 1
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            GROUP BY a.`id_customer`
            ORDER BY `address`.`date_upd` DESC';
        } else {
            $query = 'SELECT a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE DATE_FORMAT(a.`date_add`, "%m-%d") = DATE_FORMAT("' . pSQL($dateReference) . '", "%m-%d") AND DATE_FORMAT(a.`date_add`, "%Y") < DATE_FORMAT("' . pSQL($dateReference) . '", "%Y") AND ' . $timezonesWhere . '
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
            AND a.deleted = 0
            AND a.active = 1
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
             GROUP BY a.`id_customer`
            ORDER BY `address`.`date_upd` DESC';
        }

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer les paniers abandonnés depuis un nombre de secondes
     *
     * Paramètre : $nbSeconds -> Nombre d'heure depuis l'abandon
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'currency',
     * 'products' [
     * [
     * 'designation',
     * 'qty',
     * 'price',
     * 'image_url',
     * 'product_url'
     * ]
     * ],
     * 'link_cart',
     * 'locale', (optionnel),
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $nbSecondsMaxInterval Nombre de secondes maximum d'inactivité du panier
     * @param int $nbSeconds
     * @return array
     */
    public static function getDroppedOutCart($id_shop, $nbSeconds, $nbSecondsMaxInterval, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $context = self::getContext($id_shop);
        $link = $context->link;
        $return = array();
        $date = date('Y-m-d H:i:s');
        $image_size = self::getOptimumImageSize();

        // Get dropped carts
        $query = '  SELECT d2.`id_order`, c.`date_add`, c.`id_cart`, e.`iso_code` AS currency_iso_code, e.`conversion_rate` as tax_rate, b.`id_lang`, c.`id_customer`' . (_PS_VERSION_ >= 1.5 ? ', c.`id_shop`' : '') . '
                    FROM `' . _DB_PREFIX_ . 'cart` c
                    LEFT JOIN `' . _DB_PREFIX_ . 'lang` b ON (c.`id_lang` = b.`id_lang`)
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders d ON (d.id_cart = c.id_cart)
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders d2 ON (d2.`id_customer` = c.`id_customer` AND d2.`date_add` >= DATE_SUB("' . pSQL($date) . '",  INTERVAL 7 DAY) )
                    LEFT JOIN `' . _DB_PREFIX_ . 'currency` e ON (e.`id_currency` = c.`id_currency`)
                    WHERE c.date_upd >= DATE_SUB("' . pSQL($date) . '", INTERVAL ' . (int) ($nbSeconds + $nbSecondsMaxInterval) . ' SECOND)
                    AND c.date_upd <= DATE_SUB("' . pSQL($date) . '", INTERVAL ' . (int) $nbSeconds . ' SECOND)
                    AND c.id_customer IS NOT NULL
                    AND c.id_customer != 0
                    AND d.id_order IS NULL
                    AND d2.`id_order` IS NULL
                    ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'c') : '') . '
                    GROUP BY c.`id_cart`';

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $id_lang = $row['id_lang'];
                $cart = new Cart($row['id_cart']);
                $returnProducts = self::getCartProducts($id_shop, $cart, $id_lang);
                if ($returnProducts && is_array($returnProducts) && sizeof($returnProducts)) {

                    // Lien vers panier
                    if (self::isMultiShop($id_shop))
                        $link_cart = self::getPageLink($link, 'order', true, $id_lang, null, false, $id_shop);
                    elseif (isset($row['id_shop']))
                        $link_cart = self::getPageLink($link, 'order', true, $id_lang, null, false, $row['id_shop']);
                    else
                        $link_cart = (_PS_VERSION_ < 1.5 ? (_PS_VERSION_ < '1.4.0.2' ? _PS_BASE_URL_ . __PS_BASE_URI__ . 'order.php' : $link->getPageLink('order.php', true, $id_lang)) : $link->getPageLink('order', true, $id_lang));

                    $return[] = array(
                        'sum_cart' => $cart->getOrderTotal(),
                        'currency' => $row['currency_iso_code'],
                        'tax_rate' => $row['tax_rate'],
                        'id_cart' => $row['id_cart'],
                        'date_cart' => $row['date_add'],
                        'link_cart' => $link_cart,
                        'products' => $returnProducts,
                        'customer' => self::getUser($row['id_customer'])
                    );
                }
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des bons clients
     * par montant de commande sur une période donnée
     *
     * Paramètres : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $amount -> Le montant total de commandes
     * $duration -> Le nombre de jours comptabilisé
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste d'utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $amount
     * @param int $duration
     * @return array
     */
    public static function getGoodClientsByAmount($id_shop, $dateReference, $timezones, $amount, $amountMax, $duration, $nbDaysLastOrder, $relaunchOlder = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        if (version_compare(_PS_VERSION_, '1.4.0.17', '<')) {
            $query = 'SELECT (SELECT SUM(`orders`.`total_paid`*currency.`conversion_rate`) FROM `' . _DB_PREFIX_ . 'orders` `orders` LEFT JOIN `' . _DB_PREFIX_ . 'currency` currency ON (orders.`id_currency` = currency.`id_currency`) WHERE `orders`.valid = 1  AND DATE_SUB("' . pSQL($dateReference) . '",INTERVAL ' . (int) $duration . ' DAY) <= `orders`.`date_add` AND ' . $timezonesWhere . ' AND  `orders`.`id_customer` = a.`id_customer` ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'orders') : '') . ') `total`, a.`id_customer`
          FROM `' . _DB_PREFIX_ . 'orders` a
          LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder ON (lastorder.`id_customer` = a.`id_customer` AND lastorder.`valid` = 1 AND lastorder.`date_add` >= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) ($nbDaysLastOrder - 1) . ' DAY))
          ' . ($relaunchOlder ? 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND lastorder2.`valid` = 1 AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") >= DATE_FORMAT(DATE_SUB(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY), INTERVAL ' . (int) $relaunchOlder . ' MONTH), "%Y-%m-%d"))' : 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY), "%Y-%m-%d"))') . '
          LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_address` = `a`.`id_address_invoice`)
          LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
          LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
          WHERE a.valid = 1
          AND DATE_SUB("' . pSQL($dateReference) . '",INTERVAL ' . (int) $duration . ' DAY) <= a.`date_add` AND ' . $timezonesWhere . '
          AND lastorder.`id_order` IS NULL
          AND lastorder2.`id_order` IS NOT NULL
          ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
          GROUP BY a.`id_customer`
      HAVING `total` >= ' . (float) $amount . '
      ' . ($amountMax ? ' AND `total` <= ' . (float) $amountMax : '');
        } else {
            $query = 'SELECT  (SELECT SUM(`orders`.`total_paid`*`orders`.`conversion_rate`) FROM `' . _DB_PREFIX_ . 'orders` `orders` WHERE `orders`.valid = 1  AND DATE_SUB("' . pSQL($dateReference) . '",INTERVAL ' . (int) $duration . ' DAY) <= `orders`.`date_add` AND ' . $timezonesWhere . ' AND  `orders`.`id_customer` = a.`id_customer` ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'orders') : '') . ') `total`, a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'orders` a
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder ON (lastorder.`id_customer` = a.`id_customer` AND lastorder.`valid` = 1 AND lastorder.`date_add` >= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) ($nbDaysLastOrder - 1) . ' DAY))
            ' . ($relaunchOlder ? 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND lastorder2.`valid` = 1 AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") >= DATE_FORMAT(DATE_SUB(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY), INTERVAL ' . (int) $relaunchOlder . ' MONTH), "%Y-%m-%d"))' : 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY), "%Y-%m-%d"))') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_address` = `a`.`id_address_invoice`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE a.valid = 1
            AND DATE_SUB("' . pSQL($dateReference) . '",INTERVAL ' . (int) $duration . ' DAY) <= a.`date_add` AND ' . $timezonesWhere . '
            AND lastorder.`id_order` IS NULL
            AND lastorder2.`id_order` IS NOT NULL
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            GROUP BY a.`id_customer`
        HAVING `total` >= ' . (float) $amount . '
        ' . ($amountMax ? ' AND `total` <= ' . (float) $amountMax : '');
        }

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des bons clients
     * par nombre de commandes sur une période donnée
     *
     * Paramètres : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbOrder -> Le nombre de commandes passées
     * $duration -> Le nombre de jours comptabilisé
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste d'utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $nbOrder
     * @param int $duration
     * @return array
     */
    public static function getGoodClientsByNumberOrders($id_shop, $dateReference, $timezones, $nbOrder, $nbOrderMax, $duration, $nbDaysLastOrder, $relaunchOlder = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        $query = 'SELECT (SELECT COUNT(`orders`.`id_order`) FROM `' . _DB_PREFIX_ . 'orders` `orders` WHERE `orders`.valid = 1  AND DATE_SUB("' . pSQL($dateReference) . '",INTERVAL ' . (int) $duration . ' DAY) <= `orders`.`date_add` AND ' . $timezonesWhere . ' AND  `orders`.`id_customer` = a.`id_customer` ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'orders') : '') . ') `total`, a.`id_customer`
        FROM `' . _DB_PREFIX_ . 'orders` a
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder ON (lastorder.`id_customer` = a.`id_customer` AND lastorder.`valid` = 1 AND lastorder.`date_add` >= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) ($nbDaysLastOrder - 1) . ' DAY))
        ' . ($relaunchOlder ? 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND lastorder2.`valid` = 1 AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") >= DATE_FORMAT(DATE_SUB(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY), INTERVAL ' . (int) $relaunchOlder . ' MONTH), "%Y-%m-%d"))' : 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND lastorder2.`valid` = 1 AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY), "%Y-%m-%d"))') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_address` = `a`.`id_address_invoice`)
        LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
        LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
        WHERE a.valid = 1
        AND DATE_SUB("' . pSQL($dateReference) . '",INTERVAL ' . (int) $duration . ' DAY) <= a.`date_add` AND ' . $timezonesWhere . '
        AND lastorder.`id_order` IS NULL
        AND lastorder2.`id_order` IS NOT NULL
        ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
        GROUP BY a.`id_customer`
    HAVING `total` >= ' . (int) $nbOrder . '
    ' . ($nbOrderMax ? ' AND `total` <= ' . (int) $nbOrderMax : '');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des bons clients
     * par nombre de commandes sur une période donnée
     *
     * Paramètres : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbOrder -> Le nombre de commandes passées
     * $duration -> Le nombre de jours comptabilisé
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste d'utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $nbOrder
     * @param int $duration
     * @return array
     */
    public static function getInactiveClients($id_shop, $dateReference, $timezones, $nbMonthsLastOrder, $relaunchOlder = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        $query = 'SELECT lastorder.`id_order`, COUNT(a.`id_order`) `total`, a.`id_customer`
        FROM `' . _DB_PREFIX_ . 'orders` a
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder ON (lastorder.`id_customer` = a.`id_customer` AND lastorder.`valid` = 1 AND lastorder.`date_add` >= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) ($nbMonthsLastOrder) . ' MONTH))
        ' . ($relaunchOlder ? 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND lastorder2.`valid` = 1 AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") >= DATE_FORMAT(DATE_SUB(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbMonthsLastOrder . ' MONTH), INTERVAL ' . (int) $relaunchOlder . ' MONTH), "%Y-%m-%d"))' : 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` lastorder2 ON (lastorder2.`id_customer` = a.`id_customer` AND lastorder2.`valid` = 1 AND DATE_FORMAT(lastorder2.`date_add`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbMonthsLastOrder . ' MONTH), INTERVAL 1 DAY), "%Y-%m-%d"))') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_address` = `a`.`id_address_invoice`)
        LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
        LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
        WHERE a.valid = 1
        AND lastorder.`id_order` IS NULL
        AND lastorder2.`id_order` IS NOT NULL
        AND ' . $timezonesWhere . '
        ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
        GROUP BY a.`id_customer`
    HAVING `total` > 0';
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des clients n'ayants pas effectués de commandes
     * depuis un certaint temps
     *
     * Paramètres : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbDays -> Le nombre de jours d'innactivité. Si $nbDays = 0, alors récupérer la
     * liste des clients n'ayant jamais effectués de commandes
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste d'utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $nbDays
     * @return array
     */
    public static function getMissingClients($id_shop, $dateReference, $timezones, $nbDays = 0, $relaunchOlder = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        if (_PS_VERSION_ < '1.5.4') {
            $query = 'SELECT a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` AS `order` ON (`order`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE `order`.`id_order` IS NULL
            AND DATE_FORMAT(`a`.`date_add`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDays . ' DAY), "%Y-%m-%d")
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
            AND ' . $timezonesWhere . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            AND a.deleted = 0
            AND a.active = 1
            GROUP BY a.`id_customer`';
        } else {
            $query = 'SELECT a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` AS `order` ON (`order`.`id_customer` = `a`.`id_customer`)
             LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE `order`.`id_order` IS NULL
            AND DATE_FORMAT(`a`.`date_add`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDays . ' DAY), "%Y-%m-%d")
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
            AND ' . $timezonesWhere . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            AND a.deleted = 0
            AND a.active = 1
            GROUP BY a.`id_customer`';
        }
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer les commandes ayant un certain statut, depuis $nbDays jours
     *
     * Paramètre : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbDays -> Nombre de jours depuis que la commande a été passée
     * $idStatus -> L'ID du statut concerné par la relance
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * Retour attendu : Un tableau de liste utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'currency',
     * 'total_amount',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'products' [
     * [
     * 'designation',
     * 'qty',
     * 'price',
     * 'currency',
     * 'image_url',
     * 'product_url',
     * ]
     * ],
     * 'locale', (optionnel),
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $nbDays
     * @return array
     */
    public static function getOrdersByStatus($id_shop, $dateReference, $timezones, $nbDays, $idStatus, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $context = self::getContext($id_shop);
        $link = $context->link;
        $return = array();

        $timezonesWhere = self::generateTimezonesWhere($timezones);

        if (! $timezonesWhere)
            return false;

        $image_size = self::getOptimumImageSize();
        if (_PS_VERSION_ < '1.5.0.13') {
            $query = 'SELECT  d2.`id_order`, a.`date_add`, a.`total_paid`, e.`iso_code` AS currency_iso_code, a.`id_order`, a.`id_customer`, a.`id_lang`' . (_PS_VERSION_ >= 1.5 ? ', a.`id_shop`' : '') . '
            FROM `' . _DB_PREFIX_ . 'orders` a
            LEFT JOIN ' . _DB_PREFIX_ . 'orders d2 ON (d2.`id_customer` = a.`id_customer` AND d2.`date_add` > a.`date_add` )
            LEFT JOIN `' . _DB_PREFIX_ . 'order_history` d ON (d.`id_order` = a.`id_order`)
            LEFT JOIN `' . _DB_PREFIX_ . 'currency` e ON (e.`id_currency` = a.`id_currency`)
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE  d.`id_order_state` = ' . (int) $idStatus . '
            AND d2.`id_order` IS NULL
            AND d.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `' . _DB_PREFIX_ . 'order_history` moh WHERE moh.`id_order` = a.`id_order` GROUP BY moh.`id_order`)
            AND `address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1)
            AND d.`date_add` >= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDays . ' DAY)
            AND d.`date_add` <= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) ($nbDays - 1) . ' DAY)  AND ' . $timezonesWhere . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            GROUP BY a.`id_customer`';
        } else {
            $query = 'SELECT d2.`id_order`, a.`date_add`, a.`total_paid`, e.`iso_code` AS currency_iso_code, a.`id_order`, a.`id_customer`, a.`id_lang`, a.`id_shop`
            FROM `' . _DB_PREFIX_ . 'orders` a
            LEFT JOIN ' . _DB_PREFIX_ . 'orders d2 ON (d2.`id_customer` = a.`id_customer` AND d2.`date_add` > a.`date_add` )
            LEFT JOIN `' . _DB_PREFIX_ . 'order_history` d ON (d.`id_order` = a.`id_order`)
            LEFT JOIN `' . _DB_PREFIX_ . 'currency` e ON (e.`id_currency` = a.`id_currency`)
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `a`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE a.`current_state` = ' . (int) $idStatus . '
            AND d2.`id_order` IS NULL
            AND `address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = a.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1)
            AND d.`date_add` >= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) $nbDays . ' DAY)
            AND d.`date_add` <= DATE_SUB("' . pSQL($dateReference) . '", INTERVAL ' . (int) ($nbDays - 1) . ' DAY) AND ' . $timezonesWhere . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
             GROUP BY a.`id_customer`';
        }
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $id_lang = $row['id_lang'];
                $order = new Order($row['id_order']);
                if (_PS_VERSION_ < '1.5.0.1')
                    $total_paid = $order->total_paid_real;
                else
                    $total_paid = $order->total_paid_tax_incl;
                if (! $total_paid && isset($order->total_paid) && $order->total_paid)
                    $total_paid = $order->total_paid;
                    // Get products of current order
                $returnProducts = self::getOrderProducts($order);
                if ($returnProducts && is_array($returnProducts) && sizeof($returnProducts)) {
                    $return[] = array(
                        'currency' => $row['currency_iso_code'],
                        'total_amount' => $total_paid,
                        'shipping_number' => $order->shipping_number,
                        'id_order' => $row['id_order'],
                        'date_order' => $row['date_add'],
                        'products' => $returnProducts,
                        'customer' => self::getUser($row['id_customer'])
                    );
                }
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des clients n'ayant pas utilisé leurs bon de réduction
     *
     * Paramètre : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbDayExpiration -> Nombre de jours ou le bon de réduction sera expiré
     *
     * Retour attendu : Un tableau de liste d'utilisateur
     * [
     * [
     * 'id_customer',
     * 'last_name',
     * 'first_name',
     * 'email_address',
     * 'gender',(1 = homme, 2 = femme, 0 = inconnu)(optionnel)
     * 'locale', (optionnel)
     * 'voucher_number' (optionnel)
     * ]
     * ]
     *
     * @param int $nbDayExpiration
     * @return array
     */
    public static function getVoucherUnused($id_shop, $dateReference, $timezones, $nbDayExpiration, $startBy = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        if (_PS_VERSION_ < 1.5) {
            $query = 'SELECT e.`name` AS `code`, e.`id_customer`, e.`date_to`
            FROM `' . _DB_PREFIX_ . 'discount` e
            LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `e`.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
            WHERE DATE_FORMAT(e.`date_to`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL -' . (int) $nbDayExpiration . ' DAY), "%Y-%m-%d")
            AND e.`active` = 1 AND e.`quantity` > 0
            AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = e.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
            AND e.id_customer IS NOT NULL
           AND e.id_customer != 0
            AND ' . $timezonesWhere . '
             AND e.`name` NOT LIKE "SPM-%"
            ' . ($startBy ? ' AND e.`name` LIKE "' . pSQL($startBy) . '%"' : '') . '
            GROUP BY e.`id_discount`';
        } else {
            $query = 'SELECT e.`code`, `e`.`id_customer`, e.`date_to`
                FROM `' . _DB_PREFIX_ . 'cart_rule` e
                LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule_shop crs ON e.id_cart_rule = crs.id_cart_rule
                LEFT JOIN `' . _DB_PREFIX_ . 'address` AS `address` ON (`address`.`id_customer` = `e`.`id_customer`)
              LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
              LEFT JOIN `' . _DB_PREFIX_ . 'state` AS `state` ON (`address`.`id_state` = `state`.`id_state`)
                WHERE DATE_FORMAT(e.`date_to`, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . pSQL($dateReference) . '", INTERVAL -' . (int) $nbDayExpiration . ' DAY), "%Y-%m-%d")
                AND e.`active` = 1 AND e.`quantity` > 0
                AND (`address`.`id_address` = (SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` `address2` WHERE `address2`.`id_customer` = e.`id_customer` ORDER BY `address2`.`date_upd` DESC LIMIT 1) OR `address`.`id_address` IS NULL)
                AND e.id_customer IS NOT NULL
             AND e.id_customer != 0
                AND ' . $timezonesWhere . '
                AND e.`code` NOT LIKE "SPM-%"
                ' . ($startBy ? ' AND e.`code` LIKE "' . pSQL($startBy) . '%"' : '') . '
                AND (
          e.shop_restriction = 0
          ' . ((self::isMultiShop($id_shop)) ? ' OR crs.id_shop = ' . (int) $id_shop : '') . '
        )
                GROUP BY e.`id_cart_rule`';
        }
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = array(
                    'voucher_number' => $row['code'],
                    'voucher_date_limit' => $row['date_to'],
                    'customer' => self::getUser($row['id_customer'])
                );
            }
        }
        return ($justCount ? array(
            'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des emails présents dans la base
     * Cette méthode est utilisée pour récupérer les emails lors de l'activation du retageting.
     * Elle permet à terme de savoir si l'email existe ou non afin de ne pas considérer que le visiteur est anonyme.
     * Une fois récupérer, l'email est crypté md5 et n'est ainsi pas exploitable en tant que tel.
     */
    public static function getExistingEmails($id_shop, $start, $limit, $lastUpdate)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $query = 'SELECT a.`email`, a.`id_customer`
            FROM `' . _DB_PREFIX_ . 'customer` a
            WHERE 1
            ' . ($lastUpdate ? ' AND a.`date_upd` >= "' . pSQL($lastUpdate) . '"' : '') . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            ORDER BY `id_customer`
            ' . ($limit ? ' LIMIT ' . (int) $start . ', ' . (int) $limit : '');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[$row['id_customer']] = $row['email'];
            }
        }
        return $return;
    }

    /**
     * *************** **
     * SYNC REQUESTS *
     * * *************** *
     */

    /**
     * Permet de récupérer la liste des contacts présents dans la base
     * Cette méthode est utilisée pour la fonctionnalité de campagne SMS et Email
     */
    public static function syncCustomers($id_shop, $start, $limit, $lastUpdate, $id_customer = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);

		$id_customer = (!is_array($id_customer) && $id_customer > 0) ? array($id_customer) : $id_customer;

        $query = 'SELECT ' . ($justCount ? 'COUNT(a.`id_customer`) AS `nbCutomers`' : 'a.`id_customer`') . '
            FROM `' . _DB_PREFIX_ . 'customer` a
            WHERE ' . ($id_customer && is_array($id_customer) ? '`id_customer` IN (' . implode(',', $id_customer) . ')' : '1') . '
            ' . ($lastUpdate ? ' AND a.`date_upd` >= "' . pSQL($lastUpdate) . '"' : '') . '
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'a') : '') . '
            ' . ($justCount ? '' : 'ORDER BY `id_customer`') . '
            ' . ($limit && ! $justCount ? ' LIMIT ' . (int) $start . ', ' . (int) $limit : '');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($justCount)
            return array(
                'count' => $results[0]['nbCutomers']
            );
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = self::getUser($row['id_customer']);
            }
        }
        return $return;
    }

    public static function syncProducts($id_shop, $start, $limit, $lastUpdate, $id_product = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);

		$id_product = (!is_array($id_product) && $id_product > 0) ? array($id_product) : $id_product;

        $query = 'SELECT ' . ($justCount ? 'COUNT(b.`id_product`) AS `total`' : 'b.`id_product`') . '
            FROM `' . _DB_PREFIX_ . 'product` b
                ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('product', 'b') : '') . '
            WHERE ' . ($id_product && is_array($id_product) ? 'b.`id_product` IN (' . implode(',', $id_product) . ')' : '1') . '
            ' . ($lastUpdate ? ' AND b.`date_upd` >= "' . pSQL($lastUpdate) . '"' : '') . '

            ' . ($justCount ? '' : 'ORDER BY b.`id_product`') . '
            ' . ($limit && ! $justCount ? ' LIMIT ' . (int) $start . ', ' . (int) $limit : '');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($justCount)
            return array(
                'count' => $results[0]['total']
            );
        if ($results && is_array($results) && count($results))
            foreach ($results as $row)
                $return = array_merge($return, self::getProduct($row['id_product']));
        return $return;
    }

    public static function syncProductsCategories($id_shop, $start, $limit, $lastUpdate, $id_category = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);

		$id_category = (!is_array($id_category) && $id_category > 0) ? array($id_category) : $id_category;

        $query = '  SELECT ' . ($justCount ? 'COUNT(b.`id_category`) AS `total`' : 'b.`id_category`') . '
                    FROM `' . _DB_PREFIX_ . 'category` b
                        ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('category', 'b') : '') . '
                    WHERE ' . ($id_category && is_array($id_category) ? 'b.`id_category` IN (' . implode(',', $id_category) . ')' : '1') . '
                    ' . ($lastUpdate ? ' AND b.`date_upd` >= "' . pSQL($lastUpdate) . '"' : '') . '
                    ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false) : '') . '
                    ' . ($justCount ? '' : 'ORDER BY b.`id_category`') . '
                    ' . ($limit && ! $justCount ? ' LIMIT ' . (int) $start . ', ' . (int) $limit : '');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($justCount)
            return array(
                'count' => $results[0]['total']
            );
        if ($results && is_array($results) && sizeof($results))
            foreach ($results as $row)
                $return = array_merge($return, self::getProductCategory($row['id_category']));
        return $return;
    }

    public static function syncOrders($id_shop, $start, $limit, $lastUpdate, $id_order = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        if($lastUpdate) {
            $date = new DateTime($lastUpdate);
            $date->sub(new DateInterval('P12M'));
            $dateAddLimit = $date->format('Y-m-d H:i:s');
        }

		$id_order = (!is_array($id_order) && $id_order > 0) ? array($id_order) : $id_order;

        $query = '  SELECT ' . ($justCount ? 'COUNT(`id_order`) AS `total`' : '`id_order`') . '
                    FROM `' . _DB_PREFIX_ . 'orders`
                    WHERE ' . ($id_order && is_array($id_order) ? '`id_order` IN (' . implode(',', $id_order) . ')' : '1') . '
                    ' . ($lastUpdate ? ' AND `date_upd` >= "' . pSQL($lastUpdate) . '"' : '') . '
                    ' . (isset($dateAddLimit) && $dateAddLimit ? ' AND `date_add` >= "'.pSQL($dateAddLimit).'"' : '') . '
                    ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false) : '') . '
                    ' . ($justCount ? '' : 'ORDER BY `id_order`') . '
                    ' . ($limit && ! $justCount ? ' LIMIT ' . (int) $start . ', ' . (int) $limit : '');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($justCount)
            return array(
                'count' => $results[0]['total']
            );
        if ($results && is_array($results) && sizeof($results))
            foreach ($results as $row)
                $return[] = self::getOrder($row['id_order']);
        return $return;
    }

    public static function getOrder($id_order, $orderState = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        if (is_object($id_order)) {
            $order = $id_order;
        } else {
            $order = new Order($id_order);
        }
        $currency = new Currency($order->id_currency);
        $cart = new Cart($order->id_cart);
        if (_PS_VERSION_ < '1.5.0.1')
            $total_paid = $order->total_paid_real;
        else
            $total_paid = $order->total_paid_tax_incl;
        if (! $total_paid && isset($order->total_paid) && $order->total_paid)
            $total_paid = $order->total_paid;
        $voucher_used = self::getOrderVoucher($order);
        $voucher_amount = $order->total_discounts;

        $amount_without_tax = $order->total_products;

        $return = array(
            'shop_id_shop' => (isset($order->id_shop) && self::isMultiShop($order->id_shop) ? $order->id_shop : null),
            'order_is_confirm' => ($orderState && is_object($orderState) ? intval($orderState->logable) : $order->valid),
            'order_reference' => isset($order->reference) ? $order->reference : $order->id,
            'id_cart' => $order->id_cart,
            'id_status' => ($orderState && is_object($orderState) ? $orderState->id : $order->getCurrentState()),
            'date_cart' => ($cart->date_upd !== null && $cart->date_upd ? $cart->date_upd : $order->date_add),
            'id_order' => $order->id,
            'lang' => Language::getIsoById($order->id_lang),
            'amount' => $total_paid,
            'amount_without_tax' => $amount_without_tax,
            'tax_rate' => $currency->conversion_rate,
            'currency_rate' => $currency->conversion_rate,
            'currency' => $currency->iso_code,
            'date_order' => $order->date_add,
            'date_update' => $order->date_upd,
            'voucher_used' => $voucher_used,
            'voucher_amount' => $voucher_amount,
            'products' => self::getSyncOrderProducts($order),
            'customer' => self::getUser($order->id_customer),
            'shipping_number' => $order->shipping_number
        );
        return $return;
    }

    public static function getSyncOrderProducts($order)
    {
        $context = self::getContext();
        if (! defined('_PS_BASE_URL_'))
            require_once (dirname(__FILE__) . '/../../../init.php');
        if ($context->cart === null || ! is_object($context->cart) || ! isset($context->cart->id) || ! $context->cart->id)
            $context->cart = new Cart($order->id_cart);

        $orderProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT b.`id_product`, b.`id_manufacturer`, a.`product_price`, a.`product_quantity`, a.`product_attribute_id`
                FROM `' . _DB_PREFIX_ . 'order_detail` a
                JOIN `' . _DB_PREFIX_ . 'product` b ON (b.`id_product` = a.`product_id`)
                WHERE a.`id_order` = ' . (int) $order->id . '
                GROUP BY `id_order_detail`');

        $returnProducts = array();
        if ($orderProducts && is_array($orderProducts) && count($orderProducts)) {
            foreach ($orderProducts as $row) {
                $product_categories = (method_exists('Product', 'getProductCategories') ? Product::getProductCategories($row['id_product']) : Product::getIndexedCategories($row['id_product']));

                $returnProducts[] = array(
                    'id_product' => $row['id_product'],
                    'id_combination' => $row['product_attribute_id'],
                    'id_manufacturer' => $row['id_manufacturer'],
                    'price' => $row['product_price'],
                    'qty' => $row['product_quantity'],
                    'id_categories' => $product_categories
                );
            }
        }

        return $returnProducts;
    }

    /**
     * ******************* **
     * END SYNC REQUESTS *
     * * ******************* *
     */
    public static function getCarts($id_shop, $id_cart)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $context = self::getContext($id_shop);
        $link = $context->link;
        // Get cart
        $query = 'SELECT c.`date_upd`, c.`date_add`, c.`id_cart`, e.`iso_code` AS currency_iso_code, e.`conversion_rate` as tax_rate, b.`id_lang`, c.`id_customer`' . (_PS_VERSION_ >= 1.5 ? ', c.`id_shop`' : '') . '
            FROM `' . _DB_PREFIX_ . 'cart` c
            LEFT JOIN `' . _DB_PREFIX_ . 'lang` b ON (c.`id_lang` = b.`id_lang`)
            LEFT JOIN ' . _DB_PREFIX_ . 'orders d ON (d.id_cart = c.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'currency` e ON (e.`id_currency` = c.`id_currency`)
            WHERE
            c.`id_cart`  IN(' . (implode(', ', array_map('pSQL', (array) $id_cart))) . ')
            AND d.id_order IS NULL
            ' . (self::isMultiShop($id_shop) ? Shop::addSqlRestriction(false, 'c') : '') . '
            GROUP BY c.`id_cart`';
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $cart = new Cart($row['id_cart']);
                $id_lang = $row['id_lang'];
                $returnProducts = self::getCartProducts($id_shop, $cart, $id_lang);
                if ($returnProducts && is_array($returnProducts) && sizeof($returnProducts)) {
                    // Lien vers panier
                    if (self::isMultiShop($id_shop))
                        $link_cart = self::getPageLink($link, 'order', true, $id_lang, null, false, $id_shop);
                    elseif (isset($row['id_shop']))
                        $link_cart = self::getPageLink($link, 'order', true, $id_lang, null, false, $row['id_shop']);
                    else
                        $link_cart = (_PS_VERSION_ < 1.5 ? (_PS_VERSION_ < '1.4.0.2' ? _PS_BASE_URL_ . __PS_BASE_URI__ . 'order.php' : $link->getPageLink('order.php', true, $id_lang)) : $link->getPageLink('order', true, $id_lang));

                    $return[] = array(
                        'sum_cart' => $cart->getOrderTotal(),
                        'currency' => $row['currency_iso_code'],
                        'tax_rate' => $row['tax_rate'],
                        'id_cart' => $row['id_cart'],
                        'date_cart' => $row['date_add'],
                        'date_upd' => $row['date_upd'],
                        'link_cart' => $link_cart,
                        'products' => $returnProducts,
                        'customer' => (isset($row['id_customer']) && $row['id_customer'] ? self::getUser($row['id_customer']) : false)
                    );
                }
            }
        }
        return $return;
    }

    public static function generateVouchers($voucherInfos, $emails, $id_shop)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $vouchers = array();

        if ($voucherInfos && is_array($voucherInfos) && $emails && is_array($emails) && sizeof($emails)) {
            foreach ($emails as $email) {
                if (is_array($email) && isset($email['email']) && isset($email['description'])) {
                    $voucher = self::generateVoucher($email['email'], $voucherInfos['type'], ($voucherInfos['type'] == 'shipping' ? false : $voucherInfos['amount']), ($voucherInfos['type'] == 'amount' ? $voucherInfos['amountCurrency'] : false), $voucherInfos['minimumOrder'], $voucherInfos['nbDayValidate'], (isset($email['description']) && (boolean) $email['description'] ? $email['description'] : null), $id_shop, (isset($voucherInfos['dynamicPrefix']) ? $voucherInfos['dynamicPrefix'] : false), (isset($voucherInfos['duplicateCode']) ? $voucherInfos['duplicateCode'] : false));
                    $email = $email['email'];
                } else
                    $voucher = self::generateVoucher($email, $voucherInfos['type'], ($voucherInfos['type'] == 'shipping' ? false : $voucherInfos['amount']), ($voucherInfos['type'] == 'amount' ? $voucherInfos['amountCurrency'] : false), $voucherInfos['minimumOrder'], $voucherInfos['nbDayValidate'], null, $id_shop, (isset($voucherInfos['dynamicPrefix']) ? $voucherInfos['dynamicPrefix'] : false), (isset($voucherInfos['duplicateCode']) ? $voucherInfos['duplicateCode'] : false));
                if (! $voucher)
                    continue;
                $vouchers[$email] = $voucher;
            }
        }
        return $vouchers;
    }

    public static function getDateLastOrder($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
    SELECT MAX(`date_add`) AS `date_add`
    FROM `' . _DB_PREFIX_ . 'orders`
    WHERE `id_customer` = ' . (int) $id_customer . ' AND `valid` = 1');
        return isset($result['date_add']) ? $result['date_add'] : 0;
    }

    public static function countCustomerOrder($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $date = date('Y-m-d H:i:s');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
    SELECT COUNT(`id_order`) AS `nbOrder`
    FROM `' . _DB_PREFIX_ . 'orders`
    WHERE `id_customer` = ' . (int) $id_customer . ' AND `valid` = 1');
        return isset($result['nbOrder']) ? $result['nbOrder'] : 0;
    }

    public static function sumCustomerOrder($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $date = date('Y-m-d H:i:s');
        if (version_compare(_PS_VERSION_, '1.4.1.0', '<')) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
      SELECT SUM(a.`total_paid`*b.`conversion_rate`) AS `sumOrder`
      FROM `' . _DB_PREFIX_ . 'orders` a
      LEFT JOIN `' . _DB_PREFIX_ . 'currency` b ON (a.`id_currency` = b.`id_currency`)
      WHERE a.`id_customer` = ' . (int) $id_customer . ' AND `valid` = 1');
        } else {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
      SELECT SUM(`total_paid`*`conversion_rate`) AS `sumOrder`
      FROM `' . _DB_PREFIX_ . 'orders`
      WHERE `id_customer` = ' . (int) $id_customer . ' AND `valid` = 1');
        }
        return isset($result['sumOrder']) ? $result['sumOrder'] : 0;
    }

    public static function countCustomerOrderYear($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $date = date('Y-m-d H:i:s');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
    SELECT COUNT(`id_order`) AS `nbOrder`
    FROM `' . _DB_PREFIX_ . 'orders`
    WHERE `id_customer` = ' . (int) $id_customer . ' AND `valid` = 1 AND `date_add` >= DATE_SUB("' . pSQL($date) . '", INTERVAL 1 YEAR)');
        return isset($result['nbOrder']) ? $result['nbOrder'] : 0;
    }

    public static function sumCustomerOrderYear($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $date = date('Y-m-d H:i:s');
        if (version_compare(_PS_VERSION_, '1.4.1.0', '<')) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
      SELECT SUM(a.`total_paid`*b.`conversion_rate`) AS `sumOrder`
      FROM `' . _DB_PREFIX_ . 'orders` a
      LEFT JOIN `' . _DB_PREFIX_ . 'currency` b ON (a.`id_currency` = b.`id_currency`)
      WHERE a.`id_customer` = ' . (int) $id_customer . ' AND `valid` = 1 AND a.`date_add` >= DATE_SUB("' . pSQL($date) . '", INTERVAL 1 YEAR)');
        } else {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
      SELECT SUM(`total_paid`*`conversion_rate`) AS `sumOrder`
      FROM `' . _DB_PREFIX_ . 'orders`
      WHERE `id_customer` = ' . (int) $id_customer . ' AND `valid` = 1 AND `date_add` >= DATE_SUB("' . pSQL($date) . '", INTERVAL 1 YEAR)');
        }
        return isset($result['sumOrder']) ? $result['sumOrder'] : 0;
    }

    public static function getSpecificCustomerGroups($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
    SELECT `id_group`
    FROM `' . _DB_PREFIX_ . 'customer_group`
    WHERE `id_customer` = ' . (int) $id_customer);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $return[] = $row['id_group'];
            }
        }
        return $return;
    }

    public static function getSpecificCustomerAddresses($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT DISTINCT
                `address`.`id_address`,
                `address`.`firstname`,
                `address`.`lastname`,
                `address`.`phone` AS `phone1`,
                `address`.`phone_mobile` AS `phone2`,
                `address`.`city`,
                `address`.`company`,
                `address`.`address1`,
                `address`.`address2`,
                `address`.`postcode`,
                `address`.`other`,
                `address`.`active`,
                `address`.`deleted`,
                `country`.`iso_code` AS `country_code`
        FROM `' . _DB_PREFIX_ . 'address` AS `address`
        LEFT JOIN `' . _DB_PREFIX_ . 'country` AS `country` ON (`address`.`id_country` = `country`.`id_country`)
        WHERE `address`.`id_customer` = ' . (int) $id_customer);

        if ($results && is_array($results) && sizeof($results))
            foreach ($results as $row)
                $return[] = array(
                    'id_address' => (isset($row['id_address']) && $row['id_address'] ? $row['id_address'] : ''),
                    'first_name' => (isset($row['firstname']) && $row['firstname'] ? $row['firstname'] : ''),
                    'last_name' => (isset($row['lastname']) && $row['lastname'] ? $row['lastname'] : ''),
                    'phone1' => (isset($row['phone1']) && $row['phone1'] ? $row['phone1'] : ''),
                    'phone2' => (isset($row['phone2']) && $row['phone2'] ? $row['phone2'] : ''),
                    'company' => (isset($row['company']) && $row['company'] ? $row['company'] : ''),
                    'address1' => (isset($row['address1']) && $row['address1'] ? $row['address1'] : ''),
                    'address2' => (isset($row['address2']) && $row['address2'] ? $row['address2'] : ''),
                    'postcode' => (isset($row['postcode']) && $row['postcode'] ? $row['postcode'] : ''),
                    'city' => (isset($row['city']) && $row['city'] ? $row['city'] : ''),
                    'other' => (isset($row['other']) && $row['other'] ? $row['other'] : ''),
                    'active' => (int) $row['active'] & ! (int) $row['deleted']
                );

        return $return;
    }

    public static function getIdCustomersByEmail($email)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
            SELECT `id_customer`
            FROM `' . _DB_PREFIX_ . 'customer`
            WHERE `email` = \'' . pSQL($email) . '\'');
        return isset($result['id_customer']) ? $result['id_customer'] : false;
    }

    public static function generateVoucher($email, $type, $amount = false, $amountCurrency = false, $minimumOrder = false, $nbDayValidate, $description = null, $id_shop, $dynamicPrefix, $duplicateCode)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $date = date('Y-m-d H:i:s');
        $voucher_date_limit = date('Y-m-d 23:59:59', mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $nbDayValidate, date("Y")));

        if ($dynamicPrefix === false)
            $coupon_code = 'SPM-' . strtoupper(Tools::passwdGen(4));
        else
            $coupon_code = 'SPM-' . $dynamicPrefix . '-' . strtoupper(Tools::passwdGen(4));

        /**
         * Should we clone an existing rule or create a new one.
         */
        if ($duplicateCode !== false) {

            if (_PS_VERSION_ < 1.5)
                $id_cart_rule = Discount::discountExists($duplicateCode);
            else
                $id_cart_rule = CartRule::getIdByCode($duplicateCode);

            if (! $id_cart_rule || $id_cart_rule == '' || ! count($id_cart_rule))
                return false;

            if (_PS_VERSION_ < 1.5)
                $cartRule = new Discount($id_cart_rule['id_discount']);
            else
                $cartRule = new CartRule($id_cart_rule);

            $cartRule2 = clone $cartRule;
            $cartRule2->id = null;

            if (_PS_VERSION_ < 1.5) {
                $cartRule2->name = $coupon_code;

                // Set description
                foreach ($cartRule2->description as $k => $v)
                    $cartRule2->description[$k] = isset($description) ? $description : $coupon_code;

                    // Set categories
                $categories = Discount::getCategories($cartRule->id);
                $cartRule2->categories = array();
                if (is_array($categories) && count($categories))
                    foreach ($categories as $k => $category)
                        $cartRule2->categories[] = $category['id_category'];

                    // Minimum Order
                if ($minimumOrder) {
                    $cartRule2->minimal = (float) $minimumOrder;
                    if (property_exists('Discount', 'include_tax'))
                        $cartRule2->include_tax = 1;
                } else
                    $cartRule2->minimal = 0;

                    // Forces group and customer restriction to none.
                $cartRule2->id_group = 0;
                $cartRule2->id_customer = 0;
            } else {
                $cartRule2->code = $coupon_code;
                foreach ($cartRule2->name as $k => $v)
                    $cartRule2->name[$k] = $coupon_code;

                if ($minimumOrder) {
                    $cartRule->minimum_amount = (float) $minimumOrder;
                    $cartRule->minimum_amount_tax = 1;
                    $cartRule->minimum_amount_currency = Configuration::get('PS_CURRENCY_DEFAULT');
                } else
                    $cartRule->minimum_amount = 0;

                $cartRule2->group_restriction = 0;
                $cartRule2->id_customer = 0;
            }

            $cartRule2->date_to = $voucher_date_limit;
            $cartRule2->date_from = $date;
            $cartRule2->quantity = 1;
            $cartRule2->quantity_per_user = 1;
            $cartRule2->active = 1;

            if (_PS_VERSION_ < 1.5) {
                if (! $cartRule2->add(true, false, $cartRule2->categories))
                    return false;
            } else {
                if (! $cartRule2->add())
                    return false;
            }

            // Copy Cart Rule Conditions
            if (_PS_VERSION_ > 1.5) {
                Db::getInstance()->execute('
                  INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`)
                  (SELECT ' . (int) $cartRule2->id . ', id_shop FROM `' . _DB_PREFIX_ . 'cart_rule_shop` WHERE `id_cart_rule` = ' . (int) $cartRule->id . ')');
                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_carrier` (`id_cart_rule`, `id_carrier`)
                    (SELECT ' . (int) $cartRule2->id . ', id_carrier FROM `' . _DB_PREFIX_ . 'cart_rule_carrier` WHERE `id_cart_rule` = ' . (int) $cartRule->id . ')');

                Db::getInstance()->execute('
                  INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_country` (`id_cart_rule`, `id_country`)
                  (SELECT ' . (int) $cartRule2->id . ', id_country FROM `' . _DB_PREFIX_ . 'cart_rule_country` WHERE `id_cart_rule` = ' . (int) $cartRule->id . ')');

                Db::getInstance()->execute('
                     INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`)
                     (SELECT ' . (int) $cartRule2->id . ', IF(id_cart_rule_1 != ' . (int) $cartRule->id . ', id_cart_rule_1, id_cart_rule_2) FROM `' . _DB_PREFIX_ . 'cart_rule_combination`
                     WHERE `id_cart_rule_1` = ' . (int) $cartRule->id . ' OR `id_cart_rule_2` = ' . (int) $cartRule->id . ')');

                // Copy products/category filters
                $products_rules_group_source = Db::getInstance()->ExecuteS('
                SELECT id_product_rule_group,quantity FROM `' . _DB_PREFIX_ . 'cart_rule_product_rule_group`
                WHERE `id_cart_rule` = ' . (int) $cartRule->id . ' ');

                foreach ($products_rules_group_source as $product_rule_group_source) {
                    Db::getInstance()->execute('
                  INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
                  VALUES (' . (int) $cartRule2->id . ',' . (int) $product_rule_group_source['quantity'] . ')');
                    $id_product_rule_group_destination = Db::getInstance()->Insert_ID();

                    $products_rules_source = Db::getInstance()->ExecuteS('
                  SELECT id_product_rule,type FROM `' . _DB_PREFIX_ . 'cart_rule_product_rule`
                  WHERE `id_product_rule_group` = ' . (int) $product_rule_group_source['id_product_rule_group'] . ' ');

                    foreach ($products_rules_source as $product_rule_source) {
                        Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule` (`id_product_rule_group`, `type`)
                    VALUES (' . (int) $id_product_rule_group_destination . ',"' . pSQL($product_rule_source['type']) . '")');
                        $id_product_rule_destination = Db::getInstance()->Insert_ID();

                        $products_rules_values_source = Db::getInstance()->ExecuteS('
                    SELECT id_item FROM `' . _DB_PREFIX_ . 'cart_rule_product_rule_value`
                    WHERE `id_product_rule` = ' . (int) $product_rule_source['id_product_rule'] . ' ');

                        foreach ($products_rules_values_source as $product_rule_value_source)
                            Db::getInstance()->execute('
                      INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_value` (`id_product_rule`, `id_item`)
                      VALUES (' . (int) $id_product_rule_destination . ',' . (int) $product_rule_value_source['id_item'] . ')');
                    }
                }
            }
        } else {

            if (_PS_VERSION_ < 1.5) {
                $cartRule = new Discount();
                $cartRule->name = $coupon_code;
                if ($type == 'percent') {
                    $cartRule->id_discount_type = 1;
                    $cartRule->value = $amount;
                } elseif ($type == 'amount') {
                    $cartRule->id_discount_type = 2;
                    $cartRule->value = (float) $amount;
                    $id_currency_amount = false;
                    if ($amountCurrency)
                        $id_currency_amount = Currency::getIdByIsoCode($amountCurrency);
                    if (! $id_currency_amount)
                        $id_currency_amount = Configuration::get('PS_CURRENCY_DEFAULT');
                    $cartRule->id_currency = (int) $id_currency_amount;
                } elseif ($type == 'shipping') {
                    $cartRule->id_discount_type = 3;
                    $cartRule->value = 0;
                }
                $languages = Language::getLanguages(true);
                foreach ($languages as $language)
                    $cartRule->description[(int) $language['id_lang']] = isset($description) ? $description : $coupon_code;
                if ($minimumOrder) {
                    $cartRule->minimal = (float) $minimumOrder;
                    if (property_exists('Discount', 'include_tax'))
                        $cartRule->include_tax = 1;
                } else
                    $cartRule->minimal = 0;
                $cartRule->cumulable = (bool) Configuration::get('SPM_voucherCumulableVoucher');
                $cartRule->cumulable_reduction = (bool) Configuration::get('SPM_voucherCumulablePromo');
            } else {
                $cartRule = new CartRule();
                if ($type == 'percent')
                    $cartRule->reduction_percent = (float) $amount;
                elseif ($type == 'amount') {
                    $cartRule->reduction_amount = (float) $amount;
                    $cartRule->reduction_tax = 1;
                    $id_currency_amount = false;
                    if ($amountCurrency)
                        $id_currency_amount = Currency::getIdByIsoCode($amountCurrency);
                    if (! $id_currency_amount)
                        $id_currency_amount = Configuration::get('PS_CURRENCY_DEFAULT');
                    $cartRule->reduction_currency = (int) $id_currency_amount;
                } elseif ($type == 'shipping')
                    $cartRule->free_shipping = 1;
                $cartRule->code = $coupon_code;
                $languages = Language::getLanguages(true);
                foreach ($languages as $language)
                    $cartRule->name[(int) $language['id_lang']] = isset($description) ? $description : $coupon_code;

                if ($minimumOrder) {
                    $cartRule->minimum_amount = (float) $minimumOrder;
                    $cartRule->minimum_amount_tax = 1;
                    $cartRule->minimum_amount_currency = Configuration::get('PS_CURRENCY_DEFAULT');
                } else
                    $cartRule->minimum_amount = 0;
                $cartRule->cart_rule_restriction = ((bool) Configuration::get('SPM_voucherCumulableVoucher') === false ? 1 : 0);
            }

            if ((int) Configuration::get('SPM_voucherNominative')) {
                $id_customer = self::getIdCustomersByEmail($email);
                if ($id_customer)
                    $cartRule->id_customer = (int) $id_customer;
            }
            $cartRule->date_to = $voucher_date_limit;
            $cartRule->date_from = $date;
            $cartRule->quantity = 1;
            $cartRule->quantity_per_user = 1;
            $cartRule->active = 1;
            if (self::isMultiShop($id_shop)) {
                $cartRule->shop_restriction = true;
            }
            if (! $cartRule->add())
                return false;
            if (self::isMultiShop($id_shop)) {
                $cartRule->shop_restriction = true;
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`) VALUES (' . (int) $cartRule->id . ',' . (int) $id_shop . ') ON DUPLICATE KEY UPDATE `id_shop` = ' . (int) $id_shop . '');
            }
        }

        return array(
            'voucher_number' => $coupon_code,
            'voucher_date_limit' => $voucher_date_limit
        );
    }

    public static function getLocaleFromSqlRow($row)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        if (isset($row['iso_code']) && $row['iso_code'])
            $lang = $row['iso_code'];
        elseif (isset($row['iso_code2']) && $row['iso_code2'])
            $lang = $row['iso_code2'];
        else
            $lang = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        if (isset($row['country_code']) && $row['country_code'])
            $country = $row['country_code'];
        else
            $country = '00';

        return strtolower($lang) . '_' . strtoupper($country);
    }

    public static function getCustomerGroups($id_shop)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $context = self::getContext($id_shop);
        $return = array();
        if (self::isMultiShop($id_shop))
            $results = Group::getGroups((int) Configuration::get('PS_LANG_DEFAULT'), (int) $id_shop);
        else
            $results = Group::getGroups((int) Configuration::get('PS_LANG_DEFAULT'));
        if ($results) {
            foreach ($results as $row) {
                $return[$row['id_group']] = $row['name'];
            }
        }
        return $return;
    }

    public static function getCountries()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $results = Country::getCountries((int) Configuration::get('PS_LANG_DEFAULT'), true);
        if ($results) {
            foreach ($results as $row) {
                $return[$row['iso_code']] = $row['name'];
            }
        }
        return $return;
    }

    public static function getCurrencies()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $results = Currency::getCurrencies();
        if ($results) {
            foreach ($results as $row) {
                $return[$row['iso_code']] = $row['sign'] . ' ' . $row['name'];
            }
        }
        return $return;
    }

    public static function getLangs($id_shop)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $context = self::getContext($id_shop);
        $return = array();
        if (self::isMultiShop($id_shop))
            $results = Language::getLanguages(true, $id_shop);
        else
            $results = Language::getLanguages(true);

        if ($results) {
            foreach ($results as $row) {
                $return[$row['iso_code']] = $row['name'];
            }
        }
        return $return;
    }

    public static function getCarriers()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        $results = Carrier::getCarriers((int) Configuration::get('PS_LANG_DEFAULT'), true);
        if ($results) {
            foreach ($results as $row) {
                $return[(isset($row['id_reference']) ? $row['id_reference'] : $row['id_carrier'])] = $row['name'];
            }
        }
        return $return;
    }

    public static function getShops()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $return = array();
        if (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            $shops = Shop::getShops(false);
            foreach ($shops as $shop) {
                $return[$shop['id_shop']] = $shop['name'];
            }
        }

        return $return;
    }

    public static function getTestData($id_shop, $lang = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $context = self::getContext($id_shop);

        $link = $context->link;
        $return = array();
        $id_lang = false;
        if ($lang)
            $id_lang = Language::getIdByIso($lang);
        if (! $id_lang)
            $id_lang = Configuration::get('PS_LANG_DEFAULT');

            // Lien vers panier
        if (self::isMultiShop($id_shop))
            $return['link_cart'] = self::getPageLink($link, 'order', true, $id_lang, null, false, $id_shop);
        else
            $return['link_cart'] = (_PS_VERSION_ < 1.5 ? (_PS_VERSION_ < '1.4.0.2' ? _PS_BASE_URL_ . __PS_BASE_URI__ . 'order.php' : $link->getPageLink('order.php', true, $id_lang)) : $link->getPageLink('order', true, $id_lang));
            // Article au hasard
        $return['products'] = self::getProducts($id_shop, $lang, false, true);
        return $return;
    }

    protected static function getPageLink($link, $controller, $ssl = null, $id_lang = null, $request = null, $request_url_encode = false, $id_shop = null)
    {
        if (_PS_VERSION_ > '1.5.4.1') {
            return $link->getPageLink($controller, $ssl, $id_lang, null, false, $id_shop);
        } else {

            $controller = Tools::strReplaceFirst('.php', '', $controller);
            if (! $id_lang)
                $id_lang = (int) Context::getContext()->language->id;

            if (! is_array($request)) {
                // @FIXME html_entity_decode has been added due to '&amp;' => '%3B' ...
                $request = html_entity_decode($request);
                if ($request_url_encode)
                    $request = urlencode($request);
                parse_str($request, $request);
            }

            $uri_path = Dispatcher::getInstance()->createUrl($controller, $id_lang, $request, false, '', $id_shop);

            return self::getBaseLink($id_shop, $ssl) . self::getLangLink($id_lang, null, $id_shop) . ltrim($uri_path, '/');
        }
    }

    protected static function getBaseLink($id_shop = null, $ssl = null)
    {
        $ssl_enable = Configuration::get('PS_SSL_ENABLED');
        if ($ssl === null) {
            $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            $ssl = $force_ssl;
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null)
            $shop = new Shop($id_shop);
        else
            $shop = Context::getContext()->shop;

        $base = (($ssl && $ssl_enable) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);

        return $base . $shop->getBaseURI();
    }

    protected static function getLangLink($id_lang = null, Context $context = null)
    {
        if (! $context)
            $context = Context::getContext();

        if (! (int) Configuration::get('PS_REWRITING_SETTINGS') || ! Language::isMultiLanguageActivated())
            return '';

        if (! $id_lang)
            $id_lang = $context->language->id;

        return Language::getIsoById($id_lang) . '/';
    }

    public static function deleteUnusedVouchers()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $date = date('Y-m-d H:i:s');
        if (_PS_VERSION_ < 1.5) {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_discount` AS `id`
                FROM `' . _DB_PREFIX_ . 'discount`
                WHERE `name` LIKE "SPM-%" AND `date_to` <= DATE_SUB("' . pSQL($date) . '", INTERVAL ' . (Configuration::get('SPM_DELETE_VOUCHER_INTERVAL') === false ? 7 : (int) Configuration::get('SPM_DELETE_VOUCHER_INTERVAL')) . ' DAY)');
        } else {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_cart_rule` AS `id`
                FROM `' . _DB_PREFIX_ . 'cart_rule`
                WHERE `code` LIKE "SPM-%" AND `date_to` <= DATE_SUB("' . pSQL($date) . '", INTERVAL ' . (Configuration::get('SPM_DELETE_VOUCHER_INTERVAL') === false ? 7 : (int) Configuration::get('SPM_DELETE_VOUCHER_INTERVAL')) . ' DAY)');
        }
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                if (_PS_VERSION_ < 1.5) {
                    $cartRule = new Discount($row['id']);
                } else {
                    $cartRule = new CartRule($row['id']);
                }
                $cartRule->delete();
            }
        }
        return true;
    }

    public static function getProducts($id_shop, $lang = false, $products = false, $random = false, $maxProducts = 3)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $context = self::getContext($id_shop);
        $link = $context->link;
        $return = array();
        $id_lang = false;
        if ($lang)
            $id_lang = Language::getIdByIso($lang);
        if (! $id_lang)
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        $image_size = self::getOptimumImageSize();
        $resultProducts = array();
        if ($products) {
            $resultProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT b.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` b
              ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('product', 'b') : '') . '
                WHERE b.`id_product` IN (' . implode(',', array_map('pSQL', (array) $products)) . ') AND b.`active` = 1');
        } elseif ($random) {
            $resultProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT b.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` b
              ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('product', 'b') : '') . '
                WHERE b.`active` = 1
                ORDER BY RAND()
                LIMIT ' . ($maxProducts ? (int) $maxProducts : 3));
        }
        if ($resultProducts && is_array($resultProducts) && sizeof($resultProducts)) {
            foreach ($resultProducts as $row2) {
                $return[] = self::getProduct($row2['id_product'], $id_lang);
            }
        }
        return $return;
    }

    public static function findProducts($id_shop, $lang = false, $search)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $context = self::getContext($id_shop);
        $link = $context->link;
        $return = array();
        if (strlen($search) < 3)
            return $return;
        $id_lang = false;
        if ($lang)
            $id_lang = Language::getIdByIso($lang);
        if (! $id_lang)
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        $image_size = self::getOptimumImageSize();
        $resultProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT b.`id_product`, c.`name`, d.`id_image`, e.`legend`, c.`link_rewrite` AS product_link_rewrite
            FROM `' . _DB_PREFIX_ . 'product` b
          ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('product', 'b') : '') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` c ON (b.`id_product` = c.`id_product` AND c.`id_lang` = ' . (int) $id_lang . '' . (self::isMultiShop($id_shop) ? Shop::addSqlRestrictionOnLang('c') : '') . ')
            ' . (self::isMultiShop($id_shop) ? 'LEFT JOIN `'._DB_PREFIX_.'image_shop` d
			ON (d.`id_product` = b.`id_product` AND d.cover=1 AND d.id_shop='.(int)$id_shop.')' :
           'LEFT JOIN `' . _DB_PREFIX_ . 'image` d ON (d.`id_product` = b.`id_product` AND d.`cover` = 1)') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` e ON (d.`id_image` = e.`id_image` AND e.`id_lang` = ' . (int) $id_lang . ')
            WHERE (c.`name` LIKE "%' . pSQL($search) . '%" OR b.`reference` LIKE "%' . pSQL($search) . '%" OR b.`reference` LIKE "%' . pSQL($search) . '%"  OR b.`ean13` LIKE "%' . pSQL($search) . '%" OR b.`id_product` LIKE "%' . pSQL($search) . '%") AND b.`active` = 1
            GROUP BY b.`id_product`
            ORDER BY c.`name` ASC');
        if ($resultProducts && is_array($resultProducts) && sizeof($resultProducts)) {
            foreach ($resultProducts as $row2) {
                if (isset($row2['id_image2']) && $row2['id_image2'])
                    $row2['id_image'] = $row2['id_image2'];

                $row2['id_image'] = Product::defineProductImage($row2, $id_lang);
                // Get price
                $price = Product::getPriceStatic($row2['id_product'], ! Tax::excludeTaxeOption(), false, 2, NULL, false, true, 1, true);
                // Get image URL
                $image_url = $link->getImageLink((int) $row2['product_link_rewrite'], $row2['id_image'], $image_size);
                if (! preg_match('#^http#i', $image_url))
                    $image_url = _PS_BASE_URL_ . $image_url;

                    // Get product combinations
                $combinaisons = self::getProductCombinations($row2['id_product'], $id_lang);
                // Return
                $return[] = array(
                    'id' => $row2['id_product'],
                    'name' => $row2['name'],
                    'combinations' => $combinaisons
                );
            }
        }
        return $return;
    }

    public static function findCategories($id_shop, $lang = false, $search)
    {
        $return = array();
        if (strlen($search) < 3)
            return $return;
        $id_lang = false;
        if ($lang)
            $id_lang = Language::getIdByIso($lang);
        if (! $id_lang)
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        $context = self::getContext($id_shop);
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT *
    FROM `' . _DB_PREFIX_ . 'category` a
         ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('category', 'a') : '') . '
    LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` b ON (a.`id_category` = b.`id_category`' . (self::isMultiShop($id_shop) ? Shop::addSqlRestrictionOnLang('b') : '') . ')
    WHERE `id_lang` = ' . (int) $id_lang . ' AND `active` = 1
         AND (b.`name` LIKE "%' . pSQL($search) . '%" OR a.`id_category` LIKE "%' . pSQL($search) . '%")
         GROUP BY a.`id_category`
    ORDER BY `name` ASC');
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row2) {
                // Return
                $return[] = array(
                    'id' => $row2['id_category'],
                    'name' => $row2['name']
                );
            }
        }
        return $return;
    }

    public static function findManufacturers($id_shop, $lang = false, $search)
    {
        $return = array();
        if (strlen($search) < 3)
            return $return;
        $id_lang = false;
        if ($lang)
            $id_lang = Language::getIdByIso($lang);
        if (! $id_lang)
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        $context = self::getContext($id_shop);
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT *
    FROM `' . _DB_PREFIX_ . 'manufacturer` a
         ' . (self::isMultiShop($id_shop) ? Shop::addSqlAssociation('manufacturer', 'a') : '') . '
    WHERE (`name` LIKE "%' . pSQL($search) . '%" OR a.`id_manufacturer` LIKE "%' . pSQL($search) . '%")
    ORDER BY `name` ASC');

        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row2) {
                // Return
                $return[] = array(
                    'id' => $row2['id_manufacturer'],
                    'name' => $row2['name']
                );
            }
        }
        return $return;
    }

    public static function getProductCombinations($id_product, $id_lang)
    {
        $product = new Product($id_product);
        if (! method_exists($product, 'getAttributeCombinations'))
            $product_combinations = $product->getAttributeCombinaisons($id_lang);
        else
            $product_combinations = $product->getAttributeCombinations($id_lang);
        $combinations = array();
        if (is_array($product_combinations)) {
            foreach ($product_combinations as $k => $combinaison) {
                $combArray[$combinaison['id_product_attribute']]['attributes'][] = array(
                    $combinaison['group_name'],
                    $combinaison['attribute_name'],
                    $combinaison['id_attribute']
                );
            }
        }
        if (isset($combArray)) {
            foreach ($combArray as $id_product_attribute => $product_attribute) {
                $full_combination_name = '';
                foreach ($product_attribute['attributes'] as $attribute) {
                    $full_combination_name .= addslashes(htmlspecialchars($attribute[0])) . ' - ' . addslashes(htmlspecialchars($attribute[1])) . ', ';
                }
                $full_combination_name = rtrim($full_combination_name, ', ');
                $combinations[] = array(
                    'id' => $id_product_attribute,
                    'name' => $full_combination_name
                );
            }
        }
        return $combinations;
    }

    public static function getOptimumImageSize()
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
            $productImageSize = Configuration::get('SPM_productImageSize');
            if($productImageSize) return $productImageSize;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
    SELECT `name`
    FROM `' . _DB_PREFIX_ . 'image_type`
    WHERE `width` >= 200 AND height >= 200 AND `products` = 1
        ORDER BY `width` ASC');
        return isset($result['name']) ? $result['name'] : false;
    }

    public static function generateTimezonesWhere($timezones, $country_alias = 'country', $country_field = 'iso_code', $state_alias = 'state', $state_field = 'iso_code')
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        if (is_array($timezones) and ! sizeof($timezones))
            return false;
        $where = array();
        foreach ($timezones as $zones) {
            if (! is_array($zones) || (! isset($zones['region']) && ! isset($zones['country'])) || (isset($zones['country']) && ! $zones['country']))
                $where[] = '(`' . pSQL($country_alias) . '`.`' . pSQL($country_field) . '` IS NULL)';
            elseif (! isset($zones['region']) || ! $zones['region'])
                $where[] = '(`' . pSQL($country_alias) . '`.`' . pSQL($country_field) . '` = "' . pSQL($zones['country']) . '")';
            else
                $where[] = '(`' . pSQL($country_alias) . '`.`' . pSQL($country_field) . '` = "' . pSQL($zones['country']) . '" AND `' . pSQL($state_alias) . '`.`' . pSQL($state_field) . '` = "' . pSQL($zones['region']) . '")';
        }
        if (sizeof($where))
            return '(' . implode(' OR ', $where) . ')';
        return false;
    }

    public static function getCartVouchers()
    {
        $context = self::getContext();
        $cart = $context->cart;
        $discounts = array();
        if (_PS_VERSION_ < '1.5.0.1') {
            foreach ((array) $cart->getDiscounts(true) as $discount) {
                $discounts[] = $discount['name'];
            }
        } else {
            foreach ((array) $cart->getCartRules() as $discount) {
                $discounts[] = $discount['name'];
            }
        }
        return $discounts;
    }

    public static function getOrderVoucher($order)
    {
        $voucherUsed = array();
        if (_PS_VERSION_ < '1.5.0.1')
            $vouchersOrder = $order->getDiscounts();
        else
            $vouchersOrder = $order->getCartRules();
        if ($vouchersOrder && is_array($vouchersOrder) && sizeof($vouchersOrder)) {
            foreach ($vouchersOrder as $voucher) {
                if (_PS_VERSION_ < 1.5 && isset($voucher['id_discount'])) {
                    $cartRule = new Discount($voucher['id_discount']);
                    $voucherUsed[] = $cartRule->name;
                } elseif (isset($voucher['id_cart_rule'])) {
                    $cartRule = new CartRule($voucher['id_cart_rule']);
                    $voucherUsed[] = $cartRule->code;
                } else
                    $voucherUsed[] = $voucher['name'];
            }
        }
        return $voucherUsed;
    }

    public static function getCartProducts($id_shop, $cart, $id_lang)
    {
        $context = self::getContext($id_shop);
        $link = $context->link;
        $image_size = self::getOptimumImageSize();
        $cartProducts = $cart->getProducts();

        $returnProducts = array();
        if ($cartProducts && is_array($cartProducts) && sizeof($cartProducts)) {

            foreach ($cartProducts as $row) {
                $id_product_attribute = (isset($row['id_product_attribute']) && $row['id_product_attribute'] !== '') ? $row['id_product_attribute'] : false;

                $returnProducts[] = array_merge(self::getProduct($row['id_product'], $id_lang, $id_product_attribute), array(
                    'quantity' => $row['cart_quantity']
                ));
            }
        }
        return $returnProducts;
    }

    public static function getOrderProducts($order)
    {
        if(isset($order->id_shop) && self::isMultiShop($order->id_shop)) {
            $context = self::getContext($order->id_shop);
        }
        else
            $context = self::getContext();
        if (! defined('_PS_BASE_URL_')) {
            require_once (dirname(__FILE__) . '/../../../init.php');
        }
        if ($context->link === null || ! is_object($context->link))
            $context->link = new Link();
        if ($context->cart === null || ! is_object($context->cart) || ! isset($context->cart->id) || ! $context->cart->id)
            $context->cart = new Cart($order->id_cart);

        $image_size = self::getOptimumImageSize();
        $orderProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT b.`id_product`, b.`id_manufacturer`, c.`name`, a.`product_quantity`, a.`product_attribute_id`, MAX(d.`id_image`) id_image, f.`id_image` AS `id_image2`, e.`legend`, c.`link_rewrite` AS product_link_rewrite
                FROM `' . _DB_PREFIX_ . 'order_detail` a
                JOIN `' . _DB_PREFIX_ . 'product` b ON (b.`id_product` = a.`product_id`)
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` c ON (b.`id_product` = c.`id_product` AND c.`id_lang` = ' . (int) $order->id_lang . ')
                ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` p ON (p.`id_product` = b.`id_product`)
                    LEFT JOIN `'._DB_PREFIX_.'image_shop` d
            ON (d.`id_image` = p.`id_image` AND d.cover=1 AND d.id_shop='.(int)$context->shop->id.')' :
           'LEFT JOIN `' . _DB_PREFIX_ . 'image` d ON (d.`id_product` = b.`id_product` AND d.`cover` = 1)') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` e ON (d.`id_image` = e.`id_image` AND e.`id_lang` = ' . (int) $order->id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` f ON (f.`id_product_attribute` = a.`product_attribute_id`)
                WHERE a.`id_order` = ' . (int) $order->id . '
                GROUP BY `id_order_detail`');
        $returnProducts = array();
        if ($orderProducts && is_array($orderProducts) && sizeof($orderProducts)) {
            foreach ($orderProducts as $row) {
                $returnProducts[] = self::getProduct($row['id_product'], $order->id_lang);
            }
        }

        return $returnProducts;
    }

    public static function checkSyncDate($type)
    {
        return true;
        $pass = true;
        $lastCall = Configuration::get('SPM_SYNCLASTCALL_'.strtoupper($type));

        if (!is_null($lastCall) && $lastCall && $lastCall != '') {
            $datetime1 = new DateTime();
            $datetime2 = new DateTime($lastCall);
            $interval = ($datetime1->format('U')-$datetime2->format('U'))/60;
            if($interval < 1) {
                $pass = false;
            }
        }
        return $pass;
    }

    public static function setSyncDate($type)
    {
        Configuration::updateValue('SPM_SYNCLASTCALL_'.strtoupper($type),date('Y-m-d H:i:s'));
    }

    public static function saveProduct($id_product)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $pass = self::checkSyncDate('product');
        if($pass) {
            include_once (dirname(__FILE__) . '/bin/Notify.php');
            ShopymindClient_Notify::saveProduct($id_product);
            self::setSyncDate('product');
        }
    }

    public static function saveProductCategory($id_category)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $pass = self::checkSyncDate('productCategory');
        if($pass) {
            include_once (dirname(__FILE__) . '/bin/Notify.php');
            ShopymindClient_Notify::saveProductCategory($id_category);
            self::setSyncDate('productCategory');
        }
    }

    public static function saveCustomer($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $pass = self::checkSyncDate('customer');
        if($pass) {
            include_once (dirname(__FILE__) . '/bin/Notify.php');
            ShopymindClient_Notify::saveCustomer($id_customer);
            self::setSyncDate('customer');
        }
    }

    public static function saveOrder($id_order)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        $pass = self::checkSyncDate('order');
        if($pass) {
            include_once (dirname(__FILE__) . '/bin/Notify.php');
            ShopymindClient_Notify::saveOrder($id_order);
            self::setSyncDate('order');
        }
    }

    public static function deleteProduct($id_product)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        include_once (dirname(__FILE__) . '/bin/Notify.php');
        $params = array(
            'id_product' => $id_product
        );
        if (! count($params))
            return false;
        ShopymindClient_Notify::deleteProduct($params);
    }

    public static function deleteProductCategory($id_category)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        include_once (dirname(__FILE__) . '/bin/Notify.php');
        $params = array(
            'id_category' => $id_category
        );
        if (! count($params))
            return false;
        ShopymindClient_Notify::deleteProductCategory($params);
    }

    public static function deleteCustomer($id_customer)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        include_once (dirname(__FILE__) . '/bin/Notify.php');
        $params = array(
            'id_customer' => $id_customer
        );
        if (! count($params))
            return false;
        ShopymindClient_Notify::deleteCustomer($params);
    }

    /**
     * Not used for now.
     * public static function deleteCustomerGroup($id_group) {
     * if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
     * return call_user_func_array(array (
     * 'ShopymindClient_CallbackOverride',
     * __FUNCTION__
     * ), func_get_args());
     * include_once (dirname(__FILE__) . '/bin/Notify.php');
     * $params = array('id_group'=>$id_group);
     * if(!count($params)) return false;
     * ShopymindClient_Notify::deleteCustomerGroup($params);
     * }
     */
    public static function getProductAttributeCombinationsById($id_product, $id_product_attribute, $id_lang)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        if ((_PS_VERSION_ >= 1.5) && ! Combination::isFeatureActive())
            return array();

        $sql = 'SELECT pa.*, a.`id_attribute`, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name, al.`name` AS attribute_name
        ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? ',product_attribute_shop.*' : '') . '
        FROM `' . _DB_PREFIX_ . 'product_attribute` pa
        ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlAssociation('product_attribute', 'pa') : '') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
        WHERE pa.`id_product` = ' . (int) $id_product . '
        AND pa.`id_product_attribute` = ' . (int) $id_product_attribute . '
        GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
        ORDER BY pa.`id_product_attribute`';

        $res = Db::getInstance()->executeS($sql);

        // Get quantity of each variations
        foreach ($res as $key => $row) {
            if (_PS_VERSION_ < 1.5)
                $quantity = Product::getQuantity($row['id_product'], $row['id_product_attribute']);
            else
                $quantity = StockAvailable::getQuantityAvailableByProduct($row['id_product'], $row['id_product_attribute']);
            $res[$key]['quantity'] = $quantity;
        }

        return $res;
    }

    public static function getProductCategory($id_category)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $context = self::getContext();
        $resultCategory = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT a.`date_add`, a.`date_upd`, a.`id_parent`, a.`id_category`, a.`active`, b.`name`, b.`description`, b.`link_rewrite`, b.`id_lang`
                FROM `' . _DB_PREFIX_ . 'category` a
              ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlAssociation('category', 'a') : '') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` b ON (a.`id_category` = b.`id_category`' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlRestrictionOnLang('b') : '') . ')
                WHERE a.`id_category` = ' . (int) $id_category . '');
        $return = array();
        if ($resultCategory && is_array($resultCategory) && sizeof($resultCategory)) {
            $link = $context->link;
            foreach ($resultCategory as $row) {
                $lang = Language::getIsoById($row['id_lang']);
                if (! $lang)
                    continue;
                    // Get product URL
                if (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
                    $category_url = $link->getCategoryLink((int) $row['id_category'], null, (int) $row['id_lang'], null, $context->shop->id);
                } else
                    $category_url = $link->getCategoryLink((int) $row['id_category'], null, (int) $row['id_lang']);

                $return[] = array(
                    'shop_id_shop' => (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? $context->shop->id : false),
                    'id_category' => $row['id_category'],
                    'id_parent_category' => $row['id_parent'],
                    'lang' => $lang,
                    'name' => $row['name'],
                    'description' => trim(strip_tags(nl2br($row['description']))),
                    'link' => $category_url,
                    'date_creation' => $row['date_add'],
                    'date_update' => $row['date_upd'],
                    'active' => $row['active']
                );
            }
        }
        return $return;
    }

    public static function getProduct($id_product, $id_lang = false, $id_product_attribute = null)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $context = self::getContext();
        $image_size = self::getOptimumImageSize();

        $resultProduct = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT b.`available_for_order`, b.`active`, b.`date_add`, b.`date_upd`, b.`quantity`, b.`reference`, b.`id_manufacturer`,b.`id_product`, c.`id_lang`, c.`name`, c.`description_short`, c.`description`, MAX(d.`id_image`) `id_image`, e.`legend`, c.`link_rewrite` AS product_link_rewrite
            FROM `' . _DB_PREFIX_ . 'product` b
            ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlAssociation('product', 'b') : '') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` c ON (b.`id_product` = c.`id_product`' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlRestrictionOnLang('c') : '') . ($id_lang ? 'AND c.id_lang = ' . (int) $id_lang : '') . ')
            ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? '
            LEFT JOIN `' . _DB_PREFIX_ . 'image` p ON (p.`id_product` = b.`id_product`)
            LEFT JOIN `'._DB_PREFIX_.'image_shop` d
			ON (p.`id_image` = d.`id_image` AND d.cover=1 AND d.id_shop='.(int)$context->shop->id.')' :
           'LEFT JOIN `' . _DB_PREFIX_ . 'image` d ON (d.`id_product` = b.`id_product` AND d.`cover` = 1)') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` e ON (d.`id_image` = e.`id_image` AND e.`id_lang` = c.`id_lang`)
            WHERE b.`id_product` = ' . (int) $id_product.($id_lang ? '' : ' GROUP BY c.`id_lang`'));

        $returnProduct = array();
        if ($resultProduct && is_array($resultProduct) && count($resultProduct)) {

            $currency = Currency::getDefaultCurrency();
            $link = $context->link;
            $product_categories = (method_exists('Product', 'getProductCategories') ? Product::getProductCategories($id_product) : Product::getIndexedCategories($id_product));

            foreach ($resultProduct as $row) {
                $active = (boolean) ($row['available_for_order'] && $row['active']);
                $lang = Language::getIsoById($row['id_lang']);

                if (! $lang)
                    continue;

                $row['id_image'] = Product::defineProductImage($row, $row['id_lang']);
                // Get price
                $price = Product::getPriceStatic($row['id_product'], ! Tax::excludeTaxeOption(), false, 6, null, false, false);
                // Get price discount
                $price_discount = Product::getPriceStatic($row['id_product'], ! Tax::excludeTaxeOption(), false, 6, null, false, true);

                // Get image URL
                $image_url = $link->getImageLink((int) $row['product_link_rewrite'], $row['id_image'], $image_size);

                if (! preg_match('#^http#i', $image_url))
                    $image_url = _PS_BASE_URL_ . $image_url;

                if (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
                    // Get product URL
                    $product_url = $link->getProductLink((int) $row['id_product'], null, null, null, (int) $row['id_lang'], $context->shop->id);
                    $image_url = self::getRealMultiShopUrl($image_url, $context->shop->id);
                } else
                    $product_url = $link->getProductLink((int) $row['id_product'], null, null, null, (int) $row['id_lang']);

                $combination = self::getAllProductCombination($row['id_product'], $row['id_lang'], $image_size, $id_product_attribute);

                if (isset($id_product_attribute)) {

                    $returnProduct[] = array(
                        'shop_id_shop' => (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? $context->shop->id : false),
                        'id_product' => $row['id_product'],
                        'reference' => $combination['reference'],
                        'lang' => $lang,
                        'name' => $row['name'],
                        'combination_name' => $combination['combination_name'],
                        'description_short' => trim(strip_tags(nl2br($row['description_short']))),
                        'description' => trim(strip_tags(nl2br($row['description']))),
                        'product_link' => isset($combination['product_link']) && $combination['product_link'] !== '' ? $combination['product_link'] : $product_url,
                        'image_link' => isset($combination['image_link']) && $combination['image_link'] !== '' ? $combination['image_link'] : $image_url,
                        'id_combination' => $combination['id_combination'],
                        'id_categories' => $product_categories,
                        'id_manufacturer' => $row['id_manufacturer'],
                        'currency' => $currency->iso_code,
                        'price' => isset($combination['price']) && $combination['price'] !== '' ? $combination['price'] : $price,
                        'price_discount' => isset($combination['price_discount']) && $combination['price_discount'] !== '' ? $combination['price_discount'] : $price_discount,
                        'quantity_remaining' => isset($combination['quantity']) && $combination['quantity'] !== '' ? $combination['quantity'] : $row['quantity'],
                        'date_creation' => $row['date_add'],
                        'date_update' => $row['date_upd'],
                        'active' => $active
                    );
                } else {
                    $returnProduct[] = array(
                        'shop_id_shop' => (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? $context->shop->id : false),
                        'id_product' => $row['id_product'],
                        'reference' => $row['reference'],
                        'lang' => $lang,
                        'name' => $row['name'],
                        'description_short' => trim(strip_tags(nl2br($row['description_short']))),
                        'description' => trim(strip_tags(nl2br($row['description']))),
                        'product_link' => $product_url,
                        'image_link' => $image_url,
                        'combinations' => $combination,
                        'id_categories' => $product_categories,
                        'id_manufacturer' => $row['id_manufacturer'],
                        'currency' => $currency->iso_code,
                        'price' => $price,
                        'price_discount' => $price_discount,
                        'quantity_remaining' => $row['quantity'],
                        'date_creation' => $row['date_add'],
                        'date_update' => $row['date_upd'],
                        'active' => $active
                    );
                }
            }
        }

        return $id_lang ? reset($returnProduct) : $returnProduct;
    }

    public static function getRealMultiShopUrl($url, $id_shop)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
            if (_PS_VERSION_ < 1.5 || (int) $id_shop === (int) Configuration::get('PS_SHOP_DEFAULT'))
                return $url;
                if (Configuration::get('PS_SSL_ENABLED')) {
                    $main_domain_default = Db::getInstance()->getValue('SELECT CONCAT(domain_ssl,physical_uri,virtual_uri)
                                FROM ' . _DB_PREFIX_ . 'shop_url
                              WHERE main=1 AND id_shop = ' . (int) Configuration::get('PS_SHOP_DEFAULT'));
                    $main_domain = Db::getInstance()->getValue('SELECT CONCAT(domain_ssl,physical_uri,virtual_uri)
                                FROM ' . _DB_PREFIX_ . 'shop_url
                                WHERE main=1 AND id_shop = ' . (int) $id_shop);
                } else {
                    $main_domain_default = Db::getInstance()->getValue('SELECT CONCAT(domain,physical_uri,virtual_uri)
                                FROM ' . _DB_PREFIX_ . 'shop_url
                              WHERE main=1 AND id_shop = ' . (int) Configuration::get('PS_SHOP_DEFAULT'));
                    $main_domain = Db::getInstance()->getValue('SELECT CONCAT(domain,physical_uri,virtual_uri)
                                FROM ' . _DB_PREFIX_ . 'shop_url
                                WHERE main=1 AND id_shop = ' . (int) $id_shop);
                }
                if (! preg_match('#' . preg_quote($main_domain_default) . '#', $url))
                    return $url;
                    return preg_replace('#' . preg_quote($main_domain_default) . '#', $main_domain, $url);
    }

    public static function getAllProductCombination($id_product, $id_lang = false, $image_size, $id_product_attribute = null)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $context = self::getContext();
        $link = $context->link;
        $returnCombination = array();
        $resultProduct = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT f.`id_product_attribute`, f.`default_on`, f.`quantity`, c.`name`, MAX(d.`id_image`) AS product_img_default, pai.`id_image`, e.`legend`, c.`link_rewrite` AS product_link_rewrite
            FROM `' . _DB_PREFIX_ . 'product_attribute` f
            ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlAssociation('product_attribute', 'f') : '') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` c ON (f.`id_product` = c.`id_product`' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? Shop::addSqlRestrictionOnLang('c') : '') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai ON (pai.`id_product_attribute` = f.`id_product_attribute`)
            ' . (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') ? 'LEFT JOIN `' . _DB_PREFIX_ . 'image` p ON (p.`id_product` = f.`id_product`)
                    LEFT JOIN `'._DB_PREFIX_.'image_shop` d
            ON (d.`id_image` = p.`id_image` AND d.cover=1 AND d.id_shop='.(int)$context->shop->id.')' :
           'LEFT JOIN `' . _DB_PREFIX_ . 'image` d ON (d.`id_product` = f.`id_product` AND d.`cover` = 1)') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` e ON (e.`id_image` = pai.`id_image` AND e.`id_lang` = c.`id_lang`)
            WHERE f.`id_product` = ' . (int) $id_product . '
            AND c.`id_lang` = ' . (int) $id_lang . (isset($id_product_attribute) ? ' AND f.`id_product_attribute` = ' . (int) $id_product_attribute : '') . ' GROUP BY f.`id_product_attribute`');

        if ($resultProduct && is_array($resultProduct) && count($resultProduct)) {
            foreach ($resultProduct as $row) {

                $combinationImage = false;
                $combination_name = '';
                $combination_values = array();
                $combinations = self::getProductAttributeCombinationsById($id_product, $row['id_product_attribute'], $id_lang);

                if (method_exists('Product','getCombinationImageById'))
                    $combinationImage = Product::getCombinationImageById($row['id_product_attribute'], $id_lang);

                $combinationImage['id_product'] = $id_product;

                if (isset($row['id_image']))
                    $combinationImage['id_image'] = $row['id_image'];
                elseif (isset($row['product_img_default']))
                    $combinationImage['id_image'] = $row['product_img_default'];

                if ($combinations && count($combinations)) {
                    // Generate combination name
                    $qty = false;

                    foreach ($combinations as $row2) {
                        $combination_name .= $row2['public_group_name'] . ' ' . $row2['attribute_name'] . ' ';
                        $combination_values[] = array(
                            'name' => $row2['public_group_name'],
                            'value' => $row2['attribute_name']
                        );
                        if ($row2['reference'])
                            $row['reference'] = $row2['reference'];
                        $row['quantity'] = $row2['quantity'];
                    }
                    $combination_name = trim($combination_name);
                }

                if ($combinationImage && count($combinationImage))
                    $row['id_image'] = Product::defineProductImage($combinationImage, $id_lang);
                    // Get price
                $price = Product::getPriceStatic($id_product, ! Tax::excludeTaxeOption(), $row['id_product_attribute'], 6, null, false, false);
                // Get price discount
                $price_discount = Product::getPriceStatic($id_product, ! Tax::excludeTaxeOption(), $row['id_product_attribute'], 6, null, false, true);

                // Get image URL
                $image_url = $link->getImageLink((int) $row['product_link_rewrite'], $row['id_image'], $image_size);
                if (! preg_match('#^http#i', $image_url))
                    $image_url = _PS_BASE_URL_ . $image_url;

                if (_PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
                    // Get product URL
                    $product_url = $link->getProductLink((int) $id_product, null, null, null, (int) $id_lang, $context->shop->id);
                    $image_url = self::getRealMultiShopUrl($image_url, $context->shop->id);
                } else
                    $product_url = $link->getProductLink((int) $id_product, null, null, null, (int) $id_lang);

                $returnCombination[] = array(
                    'reference' => (isset($row['reference']) && $row['reference'] ? $row['reference'] : ''),
                    'product_link' => (isset($product_url) && $product_url ? $product_url : ''),
                    'image_link' => $image_url,
                    'combination_name' => $combination_name,
                    'id_combination' => $row['id_product_attribute'],
                    'values' => $combination_values,
                    'price' => $price,
                    'price_discount' => $price_discount,
                    'quantity_remaining' => $row['quantity'],
                    'default' => (int)$row['default_on']
                );
            }
        }

        return isset($id_product_attribute) ? reset($returnCombination) : $returnCombination;
    }

    public static function getApiIdentification()
    {
        return Configuration::get('SPM_apiIdentification');
    }

    public static function getApiPassword()
    {
        return Configuration::get('SPM_apiPassword');
    }

    public static function getApiClientUrl()
    {
        return (Configuration::get('SPM_apiClientUrl') ? (string) Configuration::get('SPM_apiClientUrl') : 'https://app.shopimind.com/rest');
    }

    public static function getContext($id_shop = false)
    {
        $module = new ShopyMind();
        $context = $module->getContext();
        if ($id_shop && isset($context->shop) && $context->shop->id && self::isMultiShop($id_shop)) {
            Shop::setContext(1, $id_shop);
            $shop = new Shop($id_shop);
            Context::getContext()->shop = $shop;
            $context->shop = $shop;
        }
        return $context;
    }

    public static function isMultiShop($id_shop)
    {
        return (bool) ($id_shop !== null && (int) $id_shop && _PS_VERSION_ >= 1.5 && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE'));
    }

    public static function isMultiShopContext($context)
    {
        if (isset($context->shop) && isset($context->shop->id) && self::isMultiShop($context->shop->id))
            return true;
        return false;
    }
}