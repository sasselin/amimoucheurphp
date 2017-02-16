<?php
	/**
	 * Created by PhpStorm.
	 * User: thucn_000
	 * Date: 6/9/14
	 * Time: 9:57 AM
	 */
	class Search extends  SearchCore {
		public static function sanitize($string, $id_lang, $indexation = false, $iso_code = false)
		{
			$string = trim($string);
			if (empty($string))
				return '';
			$string = Tools::strtolower(strip_tags($string));
			$string = html_entity_decode($string, ENT_NOQUOTES, 'utf-8');
			$string = preg_replace('/(['.PREG_CLASS_NUMBERS.']+)['.PREG_CLASS_PUNCTUATION.']+(?=['.PREG_CLASS_NUMBERS.'])/u', '\1', $string);
			$string = preg_replace('/['.PREG_CLASS_SEARCH_EXCLUDE.']+/u', ' ', $string);
			if ($indexation)
				$string = preg_replace('/[._-]+/', ' ', $string);
			else
			{
				$string = preg_replace('/[._]+/', '', $string);
				$string = ltrim(preg_replace('/([^ ])-/', '$1 ', ' '.$string));
				$string = preg_replace('/[._]+/', '', $string);
				$string = preg_replace('/[^\s]-+/', '', $string);
			}

			$blacklist = Tools::strtolower(Configuration::get('PS_SEARCH_BLACKLIST', $id_lang));
			if (!empty($blacklist))
			{
				$string = preg_replace('/(?<=\s)('.$blacklist.')(?=\s)/Su', '', $string);
				$string = preg_replace('/^('.$blacklist.')(?=\s)/Su', '', $string);
				$string = preg_replace('/(?<=\s)('.$blacklist.')$/Su', '', $string);
				$string = preg_replace('/^('.$blacklist.')$/Su', '', $string);
			}

			if (!$indexation)
			{
				$words = explode(' ', $string);
				$processed_words = array();
				// search for aliases for each word of the query
				foreach ($words as $word)
				{
					$alias = new Alias(null, $word);
					if (Validate::isLoadedObject($alias))
						$processed_words[] = $alias->search;
					else
						$processed_words[] = $word;
				}
				$string = implode(' ', $processed_words);
			}

			// If the language is constituted with symbol and there is no "words", then split every chars
			if (in_array($iso_code, array('zh', 'tw', 'ja')) && function_exists('mb_strlen'))
			{
				// Cut symbols from letters
				$symbols = '';
				$letters = '';
				foreach (explode(' ', $string) as $mb_word)
					if (strlen(Tools::replaceAccentedChars($mb_word)) == mb_strlen(Tools::replaceAccentedChars($mb_word)))
						$letters .= $mb_word.' ';
					else
						$symbols .= $mb_word.' ';

				if (preg_match_all('/./u', $symbols, $matches))
					$symbols = implode(' ', $matches[0]);
				$string = $letters.$symbols;
			}
			elseif ($indexation)
			{
				$minWordLen = (int)Configuration::get('PS_SEARCH_MINWORDLEN');
				if ($minWordLen > 1)
				{
					$minWordLen -= 1;
					$string = preg_replace('/(?<=\s)[^\s]{1,'.$minWordLen.'}(?=\s)/Su', ' ', $string);
					$string = preg_replace('/^[^\s]{1,'.$minWordLen.'}(?=\s)/Su', '', $string);
					$string = preg_replace('/(?<=\s)[^\s]{1,'.$minWordLen.'}$/Su', '', $string);
					$string = preg_replace('/^[^\s]{1,'.$minWordLen.'}$/Su', '', $string);
				}
			}

			$string = trim(preg_replace('/\s+/', ' ', $string));
			return $string;
		}
		public static function find($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position',
		                            $order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null,$id_category=null)
		{
			if (!$context)
				$context = Context::getContext();
			$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
			// TODO : smart page management
			if ($page_number < 1) $page_number = 1;
			if ($page_size < 1) $page_size = 1;
			if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
				return false;
			$intersect_array = array();
			$score_array = array();
			$words = explode(' ', Search::sanitize($expr, $id_lang, false, $context->language->iso_code));
			foreach ($words as $key => $word)
				if (!empty($word) && strlen($word) >= (int)Configuration::get('PS_SEARCH_MINWORDLEN'))
				{
					$word = str_replace('%', '\\%', $word);
					$word = str_replace('_', '\\_', $word);
					$intersect_array[] = 'SELECT si.id_product
					FROM '._DB_PREFIX_.'search_word sw
					LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
					WHERE sw.id_lang = '.(int)$id_lang.'
						AND sw.id_shop = '.$context->shop->id.'
						AND sw.word LIKE
					'.($word[0] == '-'
							? ' \''.pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
							: '\''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
						);
					if ($word[0] != '-')
						$score_array[] = 'sw.word LIKE \''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\'';
				}
				else
					unset($words[$key]);
			if (!count($words))
				return ($ajax ? array() : array('total' => 0, 'result' => array()));
			$score = '';
			if (count($score_array))
				$score = ',(
				SELECT SUM(weight)
				FROM '._DB_PREFIX_.'search_word sw
				LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
				WHERE sw.id_lang = '.(int)$id_lang.'
					AND sw.id_shop = '.$context->shop->id.'
					AND si.id_product = p.id_product
					AND ('.implode(' OR ', $score_array).')
			) position';

			$sql_groups = '';
			if (Group::isFeatureActive())
			{
				$groups = FrontController::getCurrentCustomerGroups();
				$sql_groups = 'AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
			}
			$results = $db->executeS('
		SELECT cp.`id_product`
		FROM `'._DB_PREFIX_.'category_product` cp
		'.(Group::isFeatureActive() ? 'INNER JOIN `'._DB_PREFIX_.'category_group` cg ON cp.`id_category` = cg.`id_category`' : '').'
		INNER JOIN `'._DB_PREFIX_.'category` c ON cp.`id_category` = c.`id_category`
		INNER JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
		'.Shop::addSqlAssociation('product', 'p', false).'
		WHERE c.`active` = 1
		'.($id_category !=  null? 'AND c.`id_category` = '.$id_category.'':'').'
		AND product_shop.`active` = 1
		AND product_shop.`visibility` IN ("both", "search")
		AND product_shop.indexed = 1
		'.$sql_groups);
			$eligible_products = array();
			foreach ($results as $row)
				$eligible_products[] = $row['id_product'];
			foreach ($intersect_array as $query)
			{
				$eligible_products2 = array();
				foreach ($db->executeS($query) as $row)
					$eligible_products2[] = $row['id_product'];

				$eligible_products = array_intersect($eligible_products, $eligible_products2);
				if (!count($eligible_products))
					return ($ajax ? array() : array('total' => 0, 'result' => array()));
			}
			$eligible_products = array_unique($eligible_products);
			$product_pool = '';
			foreach ($eligible_products as $id_product)
				if ($id_product)
					$product_pool .= (int)$id_product.',';
			if (empty($product_pool))
				return ($ajax ? array() : array('total' => 0, 'result' => array()));
			$product_pool = ((strpos($product_pool, ',') === false) ? (' = '.(int)$product_pool.' ') : (' IN ('.rtrim($product_pool, ',').') '));
			if ($ajax)
			{
				$sql = 'SELECT DISTINCT p.id_product, pl.name pname, cl.name cname,
						cl.link_rewrite crewrite, pl.link_rewrite prewrite '.$score.'
					FROM '._DB_PREFIX_.'product p
					INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
						p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
					)
					'.Shop::addSqlAssociation('product', 'p').'
					INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (
						product_shop.`id_category_default` = cl.`id_category`
						AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
					)
					WHERE p.`id_product` '.$product_pool.'
					ORDER BY position DESC LIMIT 10';
				return $db->executeS($sql);
			}

			if (strpos($order_by, '.') > 0)
			{
				$order_by = explode('.', $order_by);
				$order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
			}
			$alias = '';
			if ($order_by == 'price')
				$alias = 'product_shop.';
			else if ($order_by == 'date_upd')
				$alias = 'p.';
			$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
			 MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` manufacturer_name '.$score.', MAX(product_attribute_shop.`id_product_attribute`) id_product_attribute,
				DATEDIFF(
					p.`date_add`,
					DATE_SUB(
						NOW(),
						INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
					)
				) > 0 new
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa	ON (p.`id_product` = pa.`id_product`)
				'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
				'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				WHERE p.`id_product` '.$product_pool.'
				GROUP BY product_shop.id_product
				'.($order_by ? 'ORDER BY  '.$alias.$order_by : '').($order_way ? ' '.$order_way : '').'
				LIMIT '.(int)(($page_number - 1) * $page_size).','.(int)$page_size;
			$result = $db->executeS($sql);
			$sql = 'SELECT COUNT(*)
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE p.`id_product` '.$product_pool;
			$total = $db->getValue($sql);
			if (!$result)
				$result_properties = false;
			else
				$result_properties = Product::getProductsProperties((int)$id_lang, $result);
			return array('total' => $total,'result' => $result_properties);
		}


	}