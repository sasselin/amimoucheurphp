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

class CreditCardOfflinePaymentOrder
{
    public static function isCreditCardOrder($id_order)
    {
        $db = Db::getInstance();
        $result = $db->getRow('
            SELECT `id_pedido` FROM '._DB_PREFIX_.'ccofflinepayment
            WHERE `id_pedido` = '.$id_order);

        return (int)$result['id_pedido'] != 0 ? true : false;
    }

    public static function insertOrder($id_order, $cvv, $tipo_tarjeta, $pan, $nombre, $cedula, $direccion, $cp, $mes_caducidad, $ano_caducidad, $pin, $importe, $moneda, $city, $state, $country)
    {
        try {
            $db = Db::getInstance();
            $cvv = Tools::substr(pSQL($cvv), 0, 64);
            $tipo_tarjeta = Tools::substr(pSQL($tipo_tarjeta), 0, 20);
            $pan = Tools::substr(pSQL($pan), 0, 64);
            $nombre = Tools::substr(pSQL($nombre), 0, 255);
            $cedula = Tools::substr(pSQL($cedula), 0, 20);
            $direccion = Tools::substr(pSQL($nombre), 0, 255);
            $cp = Tools::substr(pSQL($cp), 0, 10);
            $pin = Tools::substr(pSQL($pin), 0, 10);
            $city = Tools::substr(pSQL($city), 0, 255);
            $state = Tools::substr(pSQL($state), 0, 255);
            $country = Tools::substr(pSQL($country), 0, 255);

            $db->Execute(
                'INSERT INTO `'._DB_PREFIX_.'ccofflinepayment`
                (`id_pedido`, `cvc`, `tipo_tarjeta`, `pan`, `nombre_titular`, `cedula`, `direccion`, `cp`, `mes_caducidad`, `ano_caducidad`, `pin`, `importe`, `moneda`, `city`, `state`, `country`)
                VALUES
                ("'.$id_order.'","'.$cvv.'","'.$tipo_tarjeta.'","'.$pan.'","'.$nombre.'","'.$cedula.'","'.$direccion.'","'.$cp.'","'.$mes_caducidad.'","'.$ano_caducidad.'","'.($pin ? $pin : 'null').'","'.$importe.'","'.$moneda.'","'.$city.'","'.$state.'","'.$country.'")'
            );
        } catch (Exception $e) {
            throw $e->getMessage();
        }
    }

    public static function getOrder($id_order)
    {
        $db = Db::getInstance();
        $result = $db->ExecuteS(
            'SELECT `id_pedido`, `cvc`, `tipo_tarjeta`, `pan`, `nombre_titular`, `cedula`, `direccion`, `cp`,
            `mes_caducidad`, `ano_caducidad`, `pin`, `importe`, `moneda`, `city`, `state`, `country`
            FROM `'._DB_PREFIX_.'ccofflinepayment`
            WHERE `id_pedido` ="'.$id_order.'";'
        );

        return array(
            'cvc'               => $result[0]['cvc'],
            'tipo_tarjeta'      => $result[0]['tipo_tarjeta'],
            'pan'               => $result[0]['pan'],
            'nombre_titular'    => $result[0]['nombre_titular'],
            'cedula'            => $result[0]['cedula'],
            'direccion'         => $result[0]['direccion'],
            'cp'                => $result[0]['cp'],
            'mes_caducidad'     => $result[0]['mes_caducidad'],
            'ano_caducidad'     => $result[0]['ano_caducidad'],
            'pin'               => $result[0]['pin'],
            'importe'           => $result[0]['importe'],
            'moneda'            => $result[0]['moneda'],
            'city'              => $result[0]['city'],
            'state'             => $result[0]['state'],
            'country'           => $result[0]['country']
        );
    }

    public static function setup()
    {
        $db = Db::getInstance();

        $db->execute(
            'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'ccofflinepayment (
            `id_pedido` INT NOT NULL PRIMARY KEY,
            `pan` varchar(64) NOT NULL,
            `cvc` varchar(64) NULL,
            `tipo_tarjeta` varchar(20) NULL,
            `nombre_titular` varchar(255) NOT NULL,
            `cedula` varchar(20),
            `direccion` varchar(255) NULL,
            `cp` varchar(10) NULL,
            `mes_caducidad` decimal(2,0) NULL,
            `ano_caducidad` decimal(4,0) NULL,
            `pin` decimal(10,0) NULL,
            `importe` decimal(12,2) NOT NULL,
            `moneda` varchar(50) NULL,
            `city` varchar(255) NULL,
            `state` varchar(255) NULL,
            `country` varchar(255) NULL,
            `fecha_transaccion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP)
            ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8'
        );

        $db->execute(
            'ALTER TABLE `'._DB_PREFIX_.'ccofflinepayment`
            CHANGE `pan` `pan` varchar(64) NOT NULL AFTER `id_pedido`,
            CHANGE `cvc` `cvc` varchar(64) NULL AFTER `pan`'
        );

        //Add columns for updates
        try {
            $db->execute('ALTER TABLE '._DB_PREFIX_.'ccofflinepayment ADD `pin` DECIMAL(10,0) NULL after `ano_caducidad`');
        } catch (Exception $e) {
            //Swallow the exception, as column already exists
        }

        try {
            $db->execute('ALTER TABLE '._DB_PREFIX_.'ccofflinepayment CHANGE `pan` varchar(64) NOT NULL after `id_pedido`');
        } catch (Exception $e) {
            //Swallow the exception, as column already exists
        }

        try {
            $db->execute('ALTER TABLE '._DB_PREFIX_.'ccofflinepayment CHANGE `cvc` varchar(64) NOT NULL after `pan`');
        } catch (Exception $e) {
                //Swallow the exception, as column already exists
        }

        try {
            $db->execute('ALTER TABLE '._DB_PREFIX_.'ccofflinepayment CHANGE `city` varchar(255) NOT NULL after `moneda`');
        } catch (Exception $e) {
                //Swallow the exception, as column already exists
        }

        try {
            $db->execute('ALTER TABLE '._DB_PREFIX_.'ccofflinepayment CHANGE `state` varchar(255) NOT NULL after `city`');
        } catch (Exception $e) {
                //Swallow the exception, as column already exists
        }

        try {
            $db->execute('ALTER TABLE '._DB_PREFIX_.'ccofflinepayment CHANGE `country` varchar(255) NOT NULL after `state`');
        } catch (Exception $e) {
                //Swallow the exception, as column already exists
        }
        return true;
    }

    public static function deleteCardInfo($id)
    {
        $db = Db::getInstance();
        $db->execute(
            'DELETE FROM `'._DB_PREFIX_.'ccofflinepayment`
            WHERE `id_pedido` = "'.$id.'"'
        );
    }

    public static function remove()
    {
        $db = Db::getInstance();
        $db->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'ccofflinepayment`');
        return true;
    }
}
