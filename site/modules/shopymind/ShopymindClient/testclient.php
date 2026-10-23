<?php
if($_SERVER['REMOTE_ADDR'] != '88.165.142.51') {
	exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/callback.php');
$return = array();

require_once (dirname(__FILE__) . '/../../../config/config.inc.php');

if (isset($smarty)) {
    require_once (dirname(__FILE__) . '/../../../init.php');
}
	
echo 'Version de Prestashop : '._PS_VERSION_;

if(isset($_GET['type'])) {
	switch($_GET['type']) {
		case 'cart' :
		default :
			$return = ShopymindClient_Callback::getDroppedOutCart($_GET['id_shop'], $_GET['param1'], $_GET['param2'], false);
			break;
			
		case 'birthdayClients' :
			$return = ShopymindClient_Callback::getBirthdayClients($_GET['id_shop'], $_GET['date'], array(array('country' => 'FR')), $_GET['nbDays'], (isset($_GET['justCount']) ? $_GET['justCount'] : false));
			break;
		
		case 'inactiveClients' :
			$return = ShopymindClient_Callback::getInactiveClients($_GET['id_shop'], $_GET['date'], array(array('country' => 'FR')), $_GET['month'], false, false);
			break;
			
		case 'prices' :
			$return = ShopymindClient_Callback::checkIfPricesDisplayedTTC();
			break;
			
		case 'generateVoucher' :
			$return = ShopymindClient_Callback::generateVoucher($_GET['email'], 'percent', 10, false, false, 1, null, $_GET['id_shop'], false, false);
			break;
			
		case 'deleteUnusedVouchers' :
			$return = ShopymindClient_Callback::deleteUnusedVouchers();
			break;
			
		case 'saveProduct' :
			$return = ShopymindClient_Callback::saveProduct($_GET['id']);
			break;

		case 'syncProducts' :
			$return = isset($_GET['id']) ? ShopymindClient_Callback::syncProducts($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false), $_GET['id']) : ShopymindClient_Callback::syncProducts($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false));
			break;

		case 'syncProductsCategories' :
			if(isset($_GET['id'])) {
				$return = ShopymindClient_Callback::syncProductsCategories($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false), $_GET['id']);
			}
			else {
				$return = ShopymindClient_Callback::syncProductsCategories($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false));
			}
			break;

		case 'syncCustomers' :
			if(isset($_GET['id'])) {
				$return = ShopymindClient_Callback::syncCustomers($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false), $_GET['id']);
			}
			else {
				$return = ShopymindClient_Callback::syncCustomers($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false));
			}
			break;

		case 'syncOrders' :
			if(isset($_GET['id'])) {
				$return = ShopymindClient_Callback::syncOrders($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false), $_GET['id']);
			}
			else {
				$return = ShopymindClient_Callback::syncOrders($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false));
			}
			break;

        case 'syncNLSubscribers' :
			$return = ShopymindClient_Callback::syncNLSubscribers($_GET['id_shop'], (isset($_GET['start']) ? $_GET['start'] : 0), (isset($_GET['limit']) ? $_GET['limit'] : 10), (isset($_GET['last_update']) ? $_GET['last_update'] : false));
            break;

        case 'unsubCustomer' :
            $return = ShopymindClient_Callback::unsubCustomer($_GET['id_shop'], $_GET['id_customer'], $_GET['email']);
            break;

        case 'test' :
            $return = ShopymindClient_Callback::getTestData($_GET['id_shop']);
            break;
	}
}

/*$spm_key = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `spm_key` FROM `'._DB_PREFIX_.'spmcartoorder` WHERE (`id_cart` = 15800 OR `id_customer` = 3953 OR `id_order` = 3149) AND `date_upd` >= DATE_SUB("2013-10-16 19:33:47", INTERVAL 14 DAY) AND `is_converted` = 0 ORDER BY `date_upd` DESC');
print_r($spm_key);*/
//Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'discount` SET `cumulable_reduction` = 1 WHERE `name` LIKE "SPM-%"');
//$return = ShopymindClient_Callback::getTimezones(false);
//$return = ShopymindClient_Callback::getUser(2);

/*birthday without voucher*/
//$return = ShopymindClient_Callback::getBirthdayClients('2013-10-04 00:00:00',array(array('country' => 'FR')),0);

/*birthday with voucher*/
//$return = ShopymindClient_Callback::getBirthdayClients('store-1', date('Y-m-d 00:00:00'), array('type'=>'percent','amount'=>50,'nbDayValidate'=>10));

/*DroppedOutCart without voucher*/
//$return = ShopymindClient_Callback::getDroppedOutCart($_GET['shop'],$_GET['param1'],$_GET['param2'],false);

/*DroppedOutCart with voucher*/
//$return = ShopymindClient_Callback::getDroppedOutCart(3600,array(),array('type'=>'percent','amount'=>50,'nbDayValidate'=>10));

/*Good client by amount without voucher*/
//$return = ShopymindClient_Callback::getGoodClientsByAmount('2013-10-15 09:00:00', array(array('country' => 'FR')), 500, 10000, 'EUR', 365,180,false);

/*Good client by amount with voucher*/
//$return = ShopymindClient_Callback::getGoodClientsByAmount(103, 10, array(), array('type'=>'shipping','nbDayValidate'=>10));

/*Good client by number of order without voucher*/
//$return = ShopymindClient_Callback::getGoodClientsByNumberOrders('2013-09-13 17:00:00', array(array('country' => 'FR')), 1, 2000);

/*Good client by number of order with voucher*/
//$return = ShopymindClient_Callback::getGoodClientsByNumberOrders(2, 30, array(), array('type'=>'shipping','nbDayValidate'=>10));

/*Bad client by number of order without voucher*/
//$return = ShopymindClient_Callback::getMissingClients('2013-10-16 09:00:00', array(array('country' => 'FR')), 7);



/*Client by order status  without voucher*/
//$return = ShopymindClient_Callback::getOrdersByStatus('2013-09-30 00:00:00', array(array('country' => 'FR')),10, 1, false);

/*Client by order status  with voucher*/
//$return = ShopymindClient_Callback::getOrdersByStatus(10, 2, array(), array('type'=>'shipping','nbDayValidate'=>10));

//$return = ShopymindClient_Callback::getVoucherUnused('2013-10-10 00:00:00', array(array('country' => 'FR')), 15);

/*Order status*/
//$return = ShopymindClient_Callback::getOrdersStatus();

/*$order = New Order(3174);
ShopymindClient_Callback::checkNewOrder($order,$link);*/

//$return = ShopymindClient_Callback::syncProducts(1, 0, 10, false);

/*$return = ShopymindClient_Callback::syncProductsCategories(1, 0, 500, false);
echo '<pre>';
    print_r($return);
echo '</pre>';
$return = ShopymindClient_Callback::syncCustomers(null, 0, 500, false);
echo '<pre>';
    print_r($return);
echo '</pre>';
$return = ShopymindClient_Callback::syncOrders(null, 0, 500, '2014-01-01 00:00:00');
echo '<pre>';
    print_r($return);
echo '</pre>';*/

/*$return = ShopymindClient_Callback::generateVouchers(
    array(
		'type'	=> 'amount',
		'amountCurrency'	=> 'EUR',
		'minimumOrder'	=> 0,
		'nbDayValidate'	=> 14,
		'description'	=> 'test du bon'
	),
    array(array('email'	=> 'a.michel@shopymind.com', 'description' => 'test')),
    'store-1',
    'PRFX',
    null //'TEST-123456789'
);*/

//$cart = new Cart(21189);

//$return = ShopymindClient_Callback::getCartProducts(null,$cart,2);


echo '<pre>';
    print_r($return);
echo '</pre>';
?>
