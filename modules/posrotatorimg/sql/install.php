<?php

    // Init
    $sql = array();
	$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'image` ADD rotator tinyint(1) default 0';					