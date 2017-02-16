<?php
if (!defined('_PS_VERSION_'))
    exit;
class BlockSpecialsOverride extends BlockSpecials
{
    public function hookDisplayHomeTab($params)
    {
        if (Configuration::get('PS_CATALOG_MODE'))
            return;

        if (!$this->isCached('tab.tpl', $this->getCacheId('blockspecials-tab')))
            BlockSpecials::$cache_specials = Product::getPricesDrop((int)$params['cookie']->id_lang, 0, Configuration::get('BLOCKSPECIALS_SPECIALS_NBR'), false, 'random');

        if (BlockSpecials::$cache_specials === false)
            return false;

        return $this->display(__FILE__, 'tab.tpl', $this->getCacheId('blockspecials-tab'));
    }
}
