CREATE TABLE IF NOT EXISTS PREFIXmodule_gshopping_table (
	id_object int(11) unsigned NOT NULL auto_increment,
	id_country int(11) unsigned NOT NULL,
	id_shop int(11) unsigned NOT NULL,
	id_category int(11) unsigned NOT NULL,
	active tinyint(1) unsigned NOT NULL,
	id_lang_export int(11) unsigned NOT NULL,
	PRIMARY KEY (id_object),
	KEY `id_country` (`id_country`, `id_shop`, `id_category`)
) ENGINE=ENGINE_DEFAULT DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS PREFIXmodule_gshopping_category (
	id_category int(11) unsigned NOT NULL,
	google_category varchar(255) NOT NULL,
	PRIMARY KEY (id_category),
	KEY `google_category` (`google_category`)
) ENGINE=ENGINE_DEFAULT DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS PREFIXmodule_gshopping_attributes (
    id_parameter int(11) unsigned NOT NULL,
    id_object int(11) unsigned NOT NULL,
    type tinyint(1) unsigned NOT NULL,
    type_attribute enum('male', 'female', 'unisex', 'newborn', 'infant', 'toddler', 'kids', 'adult', 'color', 'size', 'material', 'pattern'),
    attribute_value int(11) unsigned NOT NULL,
    PRIMARY KEY (id_parameter, id_object),
    KEY `type` (`type`),
    KEY `type_attribute` (`type_attribute`),
    KEY `attribute_value` (`attribute_value`)
) ENGINE=ENGINE_DEFAULT DEFAULT CHARSET=utf8;
