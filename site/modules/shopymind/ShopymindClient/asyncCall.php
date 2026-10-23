<?php

if(isset($_GET['action'])) {
	include_once dirname(__FILE__) . '/callback.php';

	switch($_GET['action']) {
		case 'saveOrder':
			ShopymindClient_Callback::saveOrder($_GET['id']);
			break;
		case 'saveProductCategory':
			ShopymindClient_Callback::saveProductCategory($_GET['id']);
			break;
		case 'deleteProductCategory':
			ShopymindClient_Callback::deleteProductCategory($_GET['id']);
			break;
		case 'saveProduct':
			ShopymindClient_Callback::saveProduct($_GET['id']);
			break;
		case 'deleteProduct':
			ShopymindClient_Callback::deleteProduct($_GET['id']);
			break;
		case 'saveCustomer':
			ShopymindClient_Callback::saveCustomer($_GET['id']);
			break;
        case 'deleteCustomer':
            ShopymindClient_Callback::deleteCustomer($_GET['id']);
            break;
        case 'getTableFields':
            echo ShopymindClient_Callback::getTableFields($_GET['table']);
            break;
		default:
			break;
	}
}
exit;