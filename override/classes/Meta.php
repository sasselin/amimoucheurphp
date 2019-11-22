<?php
class Meta extends MetaCore
{
	public static function getProductMetas($id_product, $id_lang, $page_name)
    {
        $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description_short`
				FROM `'._DB_PREFIX_.'product` p
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product`'.Shop::addSqlRestrictionOnLang('pl').')
				'.Shop::addSqlAssociation('product', 'p').'
				WHERE pl.id_lang = '.(int)$id_lang.'
					AND pl.id_product = '.(int)$id_product.'
					AND product_shop.active = 1';
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            if (empty($row['meta_description'])) {
                $row['meta_description'] = strip_tags($row['description_short']);
            }
			
			$newP = new Product((int)$id_product);
			$parCat = new Category((int)$newP->id_category_default, (int)$id_lang);			
			$row['name'] .= ' - '.$parCat->name;
			
            return Meta::completeMetaTags($row, $row['name']);
        }

        return Meta::getHomeMetas($id_lang, $page_name);
    }
	
    public static function getCategoryMetas($id_category, $id_lang, $page_name, $title = '')
    {
        if (!empty($title)) {
            $title = ' - '.$title;
        }
        $page_number = (int)Tools::getValue('p');
        $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description`
				FROM `'._DB_PREFIX_.'category_lang` cl
				WHERE cl.`id_lang` = '.(int)$id_lang.'
					AND cl.`id_category` = '.(int)$id_category.Shop::addSqlRestrictionOnLang('cl');

        $cache_id = 'Meta::getCategoryMetas'.(int)$id_category.'-'.(int)$id_lang;
        if (!Cache::isStored($cache_id) || true) {
            if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                if (empty($row['meta_description'])) {
                    $row['meta_description'] = strip_tags($row['description']);
                }

                // Paginate title
                if (!empty($row['meta_title'])) {
                    $row['meta_title'] = $title.$row['meta_title'].(!empty($page_number) ? ' - Page '.$page_number : '').' - '.Configuration::get('PS_SHOP_NAME');
                } else {
                    $row['meta_title'] = $row['name'].(!empty($page_number) ? ' - Page '.$page_number : '').' - '.Configuration::get('PS_SHOP_NAME');
                }

                if (!empty($title)) {
                    $row['meta_title'] = $title.(!empty($page_number) ? ' - Page '.$page_number : '').' - '.Configuration::get('PS_SHOP_NAME');
                }

                $result = Meta::completeMetaTags($row, $row['name']);
            } else {
                $result = Meta::getHomeMetas($id_lang, $page_name);
            }
            Cache::store($cache_id, $result);
            return $result;
        }
        return Cache::retrieve($cache_id);
    }
}
