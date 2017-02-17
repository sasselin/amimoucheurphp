<?php

/**
 * 2007-2015 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */
class AdminGShoppingController extends ModuleAdminController
{
    public function checkEmployeePermission()   {
        if ($this->tabAccess['view'] !== '1') {
            die (Tools::displayError('You do not have permission to view this.'));
        }
    }
    /** @var protected array cache filled with lang informations */
    protected static $rule_cache;
    protected static $_products;

    /**
     * Load the HTML form in the modalbox module school
     *
     * @access see JS
     * @return html
     */
    public function ajaxProcessLoadModuleSchool()
    {
        $this->checkEmployeePermission();
        exit($this->module->loadModuleSchool());
    }

    /**
     * Load the HTML form in the modalbox
     *
     * @access see JS
     * @param int $id_object
     * @param string $role
     * @param string $type
     * @return html
     */
    public function ajaxProcessLoadForm()
    {
        $this->checkEmployeePermission();
        $id_objet = (int)trim(pSQL(Tools::getValue('id_oject')));
        $id_category = (int)trim(pSQL(Tools::getValue('id_category')));
        $category_name = trim(pSQL(Tools::getValue('category_name')));
        $id_country = trim(pSQL(Tools::getValue('id_country')));

        exit($this->module->loadForm($id_objet, $id_category, $category_name, $id_country));
    }

    /**
     * Load the select taxonomy in the modalbox
     *
     * @access see JS
     * @param int $id_lang
     * @param string $type
     * @return html
     */
    public function ajaxProcessLoadTaxonomy()
    {
        $this->checkEmployeePermission();
        $id_lang = (int)trim(pSQL(Tools::getValue('id_lang')));

        exit($this->module->loadTaxonomy($id_lang));
    }

    /**
     * Load the selects for sub category and attributes
     *
     * @access see JS
     * @param int $id_lang
     * @param string $type
     * @return table
     */
    public function ajaxProcessModalAction()
    {
        $this->checkEmployeePermission();
        $id_lang = (int)trim(pSQL(Tools::getValue('id_lang')));
        $category = trim(pSQL(Tools::getValue('category')));
        $action = array(
            'taxonomy' => $this->module->loadTaxonomy($id_lang, $category),
            'attribute' => $this->module->loadAttributes($id_lang, $category),
        );

        exit(Tools::jsonEncode($action));
    }

    public function ajaxProcessLoadAttributesDetails()
    {
        $this->checkEmployeePermission();
        $id_attributes = trim(pSQL(Tools::getValue('id_attribute')));
        $id_lang = (int)trim(pSQL(Tools::getValue('id_lang')));
        $type = trim(pSQL(Tools::getValue('type')));

        exit($this->module->loadAttributesDetails($id_lang, $id_attributes, $type));
    }

    /**
     * Switch rule status
     *
     * @access see JS
     * @param int $id_object
     * @return int
     */
    public function ajaxProcessSwitchAction()
    {
        $this->checkEmployeePermission();
        $id_object = (int)trim(Tools::getValue('id_object'));
        $id_country = (int)trim(Tools::getValue('id_country'));
        $active = (int)trim(Tools::getValue('active', 0));

        exit($this->module->switchAction($id_object, $id_country, $active));
    }

    /**
     * Switch rule status
     *
     * @access see JS
     * @param int $id_object
     * @return int
     */
    public function ajaxProcessSaveCategory()
    {
        $this->checkEmployeePermission();
        $id_object = (int)trim(Tools::getValue('id_object'));
        $id_category = (int)trim(Tools::getValue('id_category'));
        $id_country = (int)trim(Tools::getValue('id_country'));
        $getparams = Tools::getValue('params');
        $addformat = explode('&', $getparams);

        if (!empty($addformat)) {
            foreach ($addformat as $formats) {
                $params[] = explode('=', $formats);
            }
            unset($addformat, $formats);

            /*
              $params[0] is the form name and $param[1] is the form value.
             */
            foreach ($params as &$param) {
                $is_lang = strpos($param[0], 'select_lang');
                $is_mother_cat = strpos($param[0], 'select_google_category');
                $is_sub_category = strpos($param[0], 'sub_category');
                $is_male = strpos($param[0], 'gender_male');
                $is_female = strpos($param[0], 'gender_female');
                $is_unisex = strpos($param[0], 'gender_unisex');
                $is_newborn = strpos($param[0], 'age_group_newborn');
                $is_infant = strpos($param[0], 'age_group_infant');
                $is_toddler = strpos($param[0], 'age_group_toddler');
                $is_kids = strpos($param[0], 'age_group_kids');
                $is_adult = strpos($param[0], 'age_group_adult');
                $is_color = strpos($param[0], 'select_color');
                $is_size = strpos($param[0], 'select_size');
                $is_material = strpos($param[0], 'select_material');
                $is_pattern = strpos($param[0], 'select_pattern');
                $is_gender = strpos($param[0], 'select_gender');
                $is_age_group = strpos($param[0], 'select_age_group');
                $value = trim($param[1]);

                if ($is_gender !== false && !empty($value)) {
                    if (strpos($value, 'feature') === false) {
                        $gender_type = 0;
                    } else {
                        $gender_type = 1;
                    }
                }

                if ($is_age_group !== false && !empty($value)) {
                    if (strpos($value, 'feature') === false) {
                        $age_group_type = 0;
                    } else {
                        $age_group_type = 1;
                    }
                }

                if ($is_lang !== false && !empty($value)) {
                    $id_lang = $value;
                } elseif ($is_mother_cat !== false) {
                    $value = explode('-',$value);
                    $google_category = str_replace('+', '', $value[0]);
                } elseif ($is_sub_category !== false) {
                    if($value)  {
                        $google_category = $value;
                    }
                } elseif ($is_male !== false) {
                    $male = $value;
                } elseif ($is_female !== false) {
                    $female = $value;
                } elseif ($is_unisex !== false) {
                    $unisex = $value;
                } elseif ($is_newborn !== false) {
                    $newborn = $value;
                } elseif ($is_infant !== false) {
                    $infant = $value;
                } elseif ($is_toddler !== false) {
                    $toddler = $value;
                } elseif ($is_kids !== false) {
                    $kids = $value;
                } elseif ($is_adult !== false) {
                    $adult = $value;
                } elseif ($is_color !== false) {
                    $result = explode('_', $value);
                    if (strpos($result[0], 'feature') === false) {
                        $color_type = 0;
                    } else {
                        $color_type = 1;
                    }
                    $color = (isset($result[1]) ? $result[1] : '');
                } elseif ($is_size !== false) {
                    $result = explode('_', $value);
                    if (strpos($result[0], 'feature') === false) {
                        $size_type = 0;
                    } else {
                        $size_type = 1;
                    }

                    $size = (isset($result[1]) ? $result[1] : '');
                } elseif ($is_material !== false) {
                    $result = explode('_', $value);
                    if (strpos($result[0], 'feature') === false) {
                        $material_type = 0;
                    } else {
                        $material_type = 1;
                    }

                    $material = (isset($result[1]) ? $result[1] : '');
                } elseif ($is_pattern !== false) {
                    $result = explode('_', $value);
                    if (strpos($result[0], 'feature') === false) {
                        $pattern_type = 0;
                    } else {
                        $pattern_type = 1;
                    }

                    $pattern = (isset($result[1]) ? $result[1] : '');
                }
            }
            unset($params, $param);

            $google_category = urldecode(str_replace('+', ' ', $google_category));
            $save_category = array(
                'id_category' => (int)$id_category,
                'google_category' => $google_category,
            );

            $save_lang_export = array(
                'id_object' => (int)$id_object,
                'id_lang_export' => (int)$id_lang,
            );

            $save_details = array(
                array('1', (int)$id_object, (int)$gender_type, 'male', (int)$male),
                array('2', (int)$id_object, (int)$gender_type, 'female', (int)$female),
                array('3', (int)$id_object, (int)$gender_type, 'unisex', (int)$unisex),
                array('4', (int)$id_object, (int)$age_group_type, 'newborn', (int)$newborn),
                array('5', (int)$id_object, (int)$age_group_type, 'infant', (int)$infant),
                array('6', (int)$id_object, (int)$age_group_type, 'toddler', (int)$toddler),
                array('7', (int)$id_object, (int)$age_group_type, 'kids', (int)$kids),
                array('8', (int)$id_object, (int)$age_group_type, 'adult', (int)$adult),
                array('9', (int)$id_object, (int)$color_type, 'color', (int)$color),
                array('10', (int)$id_object, (int)$size_type, 'size', (int)$size),
                array('11', (int)$id_object, (int)$material_type, 'material', (int)$material),
                array('12', (int)$id_object, (int)$pattern_type, 'pattern', (int)$pattern)
            );

            if (!empty($save_category)) {
                $this->module->replaceObj(GShopping::$google_category_table, $save_category);
                $this->module->replaceDetails(GShopping::$attributes_table, $save_details);
                $this->module->updateLang(GShopping::$categories_table, $save_lang_export);
                $this->module->buildFeed($id_country, $id_category, $id_lang, $id_object);
                exit($this->module->confirmation());
            } else
                exit($this->module->displayError('An error occurred while creating the export file'));
        }
    }

    public function ajaxProcessLoadExportLink()
    {
        $this->checkEmployeePermission();
        $id_country = (int)trim(Tools::getValue('id_country'));

        exit($this->module->loadExportLink($id_country));
    }

    public function ajaxProcessLoadExportInfo()
    {
        $this->checkEmployeePermission();
        $id_country = (int)trim(Tools::getValue('id_country'));

        exit($this->module->loadExportInfo($id_country));
    }

    public function ajaxProcessGenTaxonomyCache()
    {
        $this->checkEmployeePermission();
        exit($this->module->genTaxonomyCache());
    }

    /**
     * Reload DOM after performing an action
     * see (http://legacy.datatables.net/usage/server-side)
     *
     * @access see JS
     * @param string $role
     * @param string $type
     * @param string $sEcho
     * @param string $sSearch
     * @param string $iSortCol_0
     * @param string $iSortingCols
     * @param string $iDisplayStart
     * @param string $iDisplayLength
     * @return json
     */
    public function ajaxProcessReloadData()
    {
        $this->checkEmployeePermission();
        $filter = $order = $limit = '';
        $id_country = (int)trim(pSQL(Tools::getValue('id_country')));
        $id_object = (int)trim(Tools::getValue('id_object'));
        $echo = (int)trim(pSQL(Tools::getValue('sEcho')));
        $search = trim(pSQL(Tools::getValue('sSearch')));
        $sort_col = (int)trim(pSQL(Tools::getValue('iSortCol_0')));
        $sorting_cols = (int)trim(pSQL(Tools::getValue('iSortingCols')));
        $display_start = (int)trim(pSQL(Tools::getValue('iDisplayStart')));
        $display_length = (int)trim(pSQL(Tools::getValue('iDisplayLength')));
        $columns = array('mgt.id_object', 'mgt.id_country', 'mgt.id_category', 'cl.name', 'mgt.active');
        $count_columns = count($columns);

        /* search column filtering */
        if (isset($search) && !empty($search)) {
            $leftjoin = '';
            $filter = 'AND (';
            for ($i = 0; $i < $count_columns; $i++) {
                $search_x = trim(pSQL(Tools::getValue('search_'.$i)));
                $searchable_x = trim(pSQL(Tools::getValue('bSearchable_'.$i)));

                if (isset($searchable_x) && $searchable_x === 'true') {
                    $filter .= $columns[$i]." LIKE '%".$search."%' OR ";
                }
            }
            $filter = substr_replace($filter, '', -3);
            $filter .= ')';
        }

        /* Order column filtering */
        if (isset($sort_col)) {
            $order = 'ORDER BY ';
            for ($i = 0; $i < $sorting_cols; $i++) {
                $sort_dir_x = trim(pSQL(Tools::getValue('sSortDir_'.$i)));
                $sort_col_x = trim(pSQL(Tools::getValue('iSortCol_'.$i)));
                if ($sort_col_x) {
                    $order .= $columns[$sort_col_x - 1].' '.($sort_dir_x === 'asc' ? 'ASC' : 'DESC').', ';
                }
            }

            $order = substr_replace($order, '', -2);
            if (trim($order) === 'ORDER BY') {
                $order = '';
            }
        }

        /* Set limit */
        if (isset($display_start) && $display_length !== -1)
            $limit = ' LIMIT '.$display_start.', '.$display_length;

        $results = $this->module->getHistory($filter, $order, $limit, $id_country);
        $total_record = $this->module->countRules();
        $filtered_total = count($results);

        $data = array();
        $columns = array('id_country', 'id_category', 'id_shop', 'name', 'active');

        foreach ($results as &$result) {
            $row = array();

            foreach ($result as $key => $value) {
                if (in_array($key, $columns)) {
                    // Manage name from the cache
                    if ($key === 'name') {
                        $row[] = $value;
                    } elseif ($key === 'active') {
                        $row[] = $this->module->loadStatus($result, $value);
                    } else {
                        $row[] = $value;
                    }
                }
                if ($key === 'id_object') {
                    $row['DT_RowId'] = 'cat_'.$value;
                    $id_object = $value;
                }
            }
            unset($key, $value);
            $row[] = $this->module->loadActions($result, $id_country, $id_object);
            $data[] = $row;
        }
        unset($result, $results, $id_object);

        $output = array(
            'sEcho' => $echo,
            'iTotalRecords' => $total_record,
            'iTotalDisplayRecords' => $filtered_total,
            'aaData' => $data
        );

        exit(Tools::jsonEncode($output));
    }

}
