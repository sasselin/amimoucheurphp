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

if (version_compare(_PS_VERSION_, '1.5.2.0', '<=') && !class_exists("ShopPrestaModule")) {
    class ShopPrestaModule extends ShopCore
    {
        // Abilty to add module shop asso table
        public static function PrestaModule_setAssoTable($table, $type = 'shop')
        {
            Shop::$asso_tables[$table] = array('type' => $type);
        }
    }
}
