<?php
require_once dirname(__FILE__) . '/callback.php';
return array (
  'api' =>
  array (
    'identifiant' => ShopymindClient_Callback::getApiIdentification(),
    'password' => ShopymindClient_Callback::getApiPassword(),
    'url' => ShopymindClient_Callback::getApiClientUrl()
  ),
);