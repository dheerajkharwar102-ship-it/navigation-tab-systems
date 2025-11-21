<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if (!defined('inc_ajax_module_file')) {
    die;
}
function get_itme_of($product)
{
    $available_in = $product->available_in; // No issues with this line
    $avl_arr = ['set', 'size'];
    if (isset($product->product_shapes)) {
        $product_shapes = json_decode($product->product_shapes, true); // Decodes JSON as an associative array
        if (count($product_shapes) > 1) {
            // $item_type = $product_shapes;
            $item_type = 'straight';
        }
    } else {
        if (in_array($available_in, $avl_arr)) {
            if ($product->group_item_count > 1) {
                $item_type = $available_in;
            } else {
                $item_type = 'normal';
            }
        } else {
            $item_type = 'normal';
        }
    }

    return $item_type;
}

function has_variant($product)
{
    $p = new Product();
    $get_variants = $p->getProducts('*', null, [['parent_set_id', '=', $product->product_id]]);
    $get_bed_dims = 0;
    if (!empty($product->product_bed_dims)) {
        $bed_dims = json_decode($product->product_bed_dims, true);
        if (is_array($bed_dims)) {
            $get_bed_dims = count($bed_dims);
        }
    }
    if (count($get_variants) > 0 || $get_bed_dims > 1) {
        return true;
    }
    return false;
}

function getActiveMaterialsWithAllOptions($p, $mt, $product_id)
{
    $active_materials = [
        'metal' => [
            'active' => [],
            'all_materials' => [],
            'materialGroups' => [] // Group by material_ref_label
        ],
        'wood' => [
            'active' => [],
            'all_materials' => [],
            'materialGroups' => []
        ],
        'marble' => [
            'active' => [],
            'all_materials' => [],
            'materialGroups' => []
        ],
        'glass' => [
            'active' => [],
            'all_materials' => [],
            'materialGroups' => []
        ],
        'fabric' => [
            'active' => [],
            'all_materials' => [],
            'materialGroups' => []
        ],
        'pillow' => [
            'active' => [],
            'all_materials' => [],
            'materialGroups' => []
        ]
    ];

    $material_types = ['metal', 'wood', 'marble', 'glass', 'fabric', 'pillow'];

    foreach ($material_types as $material_type) {
        // Get ACTIVE materials for this product
        $materials = get_default_combo_data($p, $material_type, $product_id);

        // Get ALL available materials for this category
        $all_materials = getAllMaterialsByCategory($mt, $material_type);

        // Group materials by material_ref_label
        $material_groups = [];

        if (count($materials) > 0) {
            foreach ($materials as $material) {
                $alias_name_text = getAliasNameById($p, $material->alias_name, $material_type);

                $get_material = getMaterialById($mt, $material->material);

                $material_data = [
                    'id' => $material->prd_mt_id ?? $material->dtl_id,
                    'material_id' => $material->material,
                    'material_name' => $get_material->material_name,
                    'material_img' => $get_material->material_img,
                    'material_price' => $get_material->material_price,
                    'alias_name' => $alias_name_text,
                    'alias_name_id' => $material->alias_name,
                    'material_ref_label' => $material->material_ref_label ?? 'A', // Default to 'A' if not set
                    'offline_image' => $material->offline_image,
                    'pointer_image' => $material->pointer_image,
                    'product_id' => $material->product_id,
                    'dtl_id' => $material->dtl_id
                ];

                // Add type-specific fields (your existing code)
                switch ($material_type) {
                    case 'metal':
                        $material_data['weight_kg'] = $material->length;
                        $material_data['produced_in'] = $material->produced_in ?? '';
                        break;
                    case 'wood':
                        $material_data['area_m2'] = $material->area;
                        $material_data['finish_type'] = $material->finish_type;
                        break;
                    case 'marble':
                        $material_data['area_m2'] = $material->area;
                        break;
                    case 'glass':
                        $material_data['area_m2'] = $material->length;
                        break;
                    case 'fabric':
                        $material_data['fabric_name'] = $material->fabric_name;
                        $material_data['area_m2'] = $material->length;
                        break;
                    case 'pillow':
                        $material_data['quantity'] = $material->quantity;
                        $material_data['length_cm'] = $material->length;
                        $material_data['width_cm'] = $material->width;
                        $material_data['notes'] = $material->notes;
                        $material_data['pillow_face'] = $material->pillow_face;
                        $material_data['pillow_back'] = $material->pillow_back;
                        $material_data['pipping'] = $material->pipping;
                        break;
                }

                $active_materials[$material_type]['active'][] = $material_data;

                // Group by material_ref_label
                $ref_label = $material->material_ref_label ?? 'A';
                if (!isset($material_groups[$ref_label])) {
                    $material_groups[$ref_label] = [];
                }
                $material_groups[$ref_label][] = $material_data;
            }
        }

        // Store all available materials for this category
        $active_materials[$material_type]['all_materials'] = $all_materials;

        // Store grouped materials
        $active_materials[$material_type]['materialGroups'] = $material_groups;
    }

    if (!empty($active_materials['pillow']['active'])) {
        $pillow_materials_all = getAllMaterialsByCategory($mt, 'fabric');
        $pillow_data = [
            'default_pillow' => [
                'active' => [],
                'all_materials' => $pillow_materials_all,
                'materialGroups' => $active_materials['pillow']['materialGroups']
            ],
            'pillow_front' => [
                'active' => [],
                'all_materials' => $pillow_materials_all,
                'materialGroups' => $active_materials['pillow']['materialGroups']
            ],
            'pillow_back' => [
                'active' => [],
                'all_materials' => $pillow_materials_all,
                'materialGroups' => $active_materials['pillow']['materialGroups']
            ],
            'pillow_pipping' => [
                'active' => [],
                'all_materials' => $pillow_materials_all,
                'materialGroups' => $active_materials['pillow']['materialGroups']
            ]
        ];

        // Process pillow materials and group them by material_ref_label
        $pillow_groups = [];

        foreach ($active_materials['pillow']['active'] as $pillow) {
            $ref_label = $pillow['material_ref_label'] ?? 'A';

            if (!isset($pillow_groups[$ref_label])) {
                $pillow_groups[$ref_label] = [
                    'default_pillow' => [],
                    'pillow_front' => [],
                    'pillow_back' => [],
                    'pillow_pipping' => []
                ];
            }

            // Default pillow data
            $default_pillow_data = [
                'id' => $pillow['id'],
                'material_id' => $pillow['material_id'],
                'material_name' => $pillow['material_name'],
                'material_img' => $pillow['material_img'],
                'alias_name' => $pillow['alias_name'],
                'material_ref_label' => $ref_label,
                'quantity' => $pillow['quantity'],
                'dimensions' => [
                    'length' => $pillow['length_cm'],
                    'width' => $pillow['width_cm']
                ],
                'notes' => $pillow['notes'],
                'offline_image' => $pillow['offline_image'],
                'pointer_image' => $pillow['pointer_image']
            ];

            $pillow_groups[$ref_label]['default_pillow'][] = $default_pillow_data;
            $pillow_data['default_pillow']['active'][] = $default_pillow_data;

            // Pillow front (face)
            if (!empty($pillow['pillow_face'])) {
                $get_material = getMaterialById($mt, $pillow['pillow_face']);
                $face_data = [
                    'id' => $pillow['id'] . '_face',
                    'material_id' => $pillow['pillow_face'],
                    'material_name' => $get_material->material_name,
                    'material_img' => $get_material->material_img,
                    'material_price' => $get_material->material_price,
                    'material_ref_label' => $ref_label,
                    'type' => 'face',
                    'parent_pillow_id' => $pillow['id']
                ];
                $pillow_groups[$ref_label]['pillow_front'][] = $face_data;
                $pillow_data['pillow_front']['active'][] = $face_data;
            }

            // Pillow back
            if (!empty($pillow['pillow_back'])) {
                $get_material = getMaterialById($mt, $pillow['pillow_back']);
                $back_data = [
                    'id' => $pillow['id'] . '_back',
                    'material_id' => $pillow['pillow_back'],
                    'material_name' => $get_material->material_name,
                    'material_img' => $get_material->material_img,
                    'material_price' => $get_material->material_price,
                    'material_ref_label' => $ref_label,
                    'type' => 'back',
                    'parent_pillow_id' => $pillow['id']
                ];
                $pillow_groups[$ref_label]['pillow_back'][] = $back_data;
                $pillow_data['pillow_back']['active'][] = $back_data;
            }

            // Pillow pipping
            if (!empty($pillow['pipping'])) {
                $get_material = getMaterialById($mt, $pillow['pipping']);
                $pipping_data = [
                    'id' => $pillow['id'] . '_pipping',
                    'material_id' => $pillow['pipping'],
                    'material_name' => $get_material->material_name,
                    'material_img' => $get_material->material_img,
                    'material_price' => $get_material->material_price,
                    'material_ref_label' => $ref_label,
                    'type' => 'pipping',
                    'parent_pillow_id' => $pillow['id']
                ];
                $pillow_groups[$ref_label]['pillow_pipping'][] = $pipping_data;
                $pillow_data['pillow_pipping']['active'][] = $pipping_data;
            }
        }

        // Store the grouped pillow data
        $pillow_data['materialGroups'] = $pillow_groups;
        $active_materials['pillow'] = $pillow_data;
    }

    // Remove empty categories
    foreach ($active_materials as $category => $data) {
        if (empty($data['active']) && empty($data['materialGroups'])) {
            unset($active_materials[$category]);
        }
    }

    return $active_materials;
}

// Helper function to get all materials by category
function getAllMaterialsByCategory($mt, $category)
{
    $where = [
        ['material_category', '=', $category],
        ['material_status', '=', 1]
    ];
    $materials = $mt->getMaterials('*', null, $where);

    return $materials;
}

// Helper function to get material name by ID
function getMaterialById($mt, $material_id)
{
    $get_material = $mt->getMaterial($material_id);
    return $get_material;
}

// Helper function to get alias name by ID
function getAliasNameById($p, $alias_id, $material_type)
{
    if (!$alias_id) return '';

    // Determine the filter based on material type
    $filter = ($material_type === 'fabric') ? [['id', '!=', 11]] : [['id', '=', 11]];

    $fabric_names = $p->getProductFabricAliasNames('*', null, $filter);

    if (count($fabric_names) > 0) {
        foreach ($fabric_names as $fabric_name) {
            if ($fabric_name->id == $alias_id) {
                return $fabric_name->fabric_name;
            }
        }
    }

    return '';
}

if (isset($_POST['get_main_categories'])) {
    $p = new Product();
    $pa = new ProductAttribute();

    $get_menus = $pa->getWebMenus('*', null, [['deleted', '=', 0]]);

    foreach ($get_menus as $menu) {
        $where = [];
        $where[] = ['online_category', '=', $menu->id];
        $where[] = ['attr_status', '=', '1'];
        $get_attrs = $pa->getProductAttributes('*', ['attr_name', 'ASC'], $where);
        $attr_names = '';
        $product_names = '';
        foreach ($get_attrs as $attr) {
            $attr_names .= $attr->attr_name . '-';
            $where = [];
            $where[] = ['product_status', '=', '1'];
            $where[] = ['attr_id', '=', $attr->attr_id];

            $get_products = $p->getProducts('team_name,product_code', null, $where);
            foreach ($get_products as $product) {
                $product_names .= $product->team_name . '-';
                $product_names .= $product->product_code . '-';
            }
        }
        $menu->attr_names = $attr_names;
        $menu->product_names = $product_names;
    }

    echo json_response('success', 'OK', $get_menus);
    die;
}

if (isset($_POST['get_qualifications'])) {

    $user = new User();
    $logged = $user->getLogged('user_id,user_auth');
    $logged_auth = $logged->user_auth;
    $page_auth = ['admin', 'manager', 'user', 'sales', 'quality', 'purchasing'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $main_category = clear_input(p('web_menu_id'));
    if ($main_category == '' || !is_numeric($main_category)) {
        echo json_response('error', get_lang_text('ajax_invalid_request'));
        die;
    }

    $p = new Product();
    $pa = new ProductAttribute();
    $where = [];
    $where[] = ['online_category', '=', $main_category];
    $where[] = ['attr_status', '=', '1'];
    $get_attrs = $pa->getProductAttributesUnique('*', ['attr_name', 'ASC'], $where, null, true);

    foreach ($get_attrs as $attr) {
        $attr_ids = $attr->attr_ids;
        $product_names = '';
        $where = [];
        $where[] = ['product_status', '=', '1'];
        $in = [['attr_id', explode('_', $attr_ids)]];

        $get_products = $p->getProducts('team_name,product_code', null, $where, null, null, $in);

        foreach ($get_products as $product) {
            $product_names .= $product->team_name . '-';
            $product_names .= $product->product_code . '-';
        }
        $attr->product_names = $product_names;
    }

    echo json_response('success', 'OK', $get_attrs);
    die;
}

if (isset($_POST['get_curtain_accessories'])) {
    $user = new User();
    $logged = $user->getLogged('user_id,user_auth');
    $logged_auth = $logged->user_auth;
    $page_auth = ['admin', 'manager', 'user', 'sales', 'quality', 'purchasing'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $acc = new Accessory();
    $pa = new ProductAttribute();


    $accessory_type = clear_input(p('accessory_type'));
    $attr_id = clear_input(p('attr_id'));

    $where = [];
    $where[] = ['crm_status', '=', '1'];
    $where[] = ['deleted', '=', '0'];
    $where[] = ['accessory_type', '=', $accessory_type];

    $get_accessories = $acc->getAccessories('*', ['accessory_id', 'DESC'], $where);

    echo json_response('success', 'OK', $get_accessories);
    die;
}

if (isset($_POST['get_product'])) {
    $user = new User();
    $logged = $user->getLogged('user_auth');
    $logged_auth = $logged->user_auth;
    $page_auth = ['admin', 'manager', 'graphic_and_media', 'user', 'sales', 'ordermngr', 'partner', 'purchasing', 'encounter'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $p = new Product();
    $pa = new ProductAttribute();
    $mt = new Material();
    // $o = new Order();
    // $ctl = new Catalog();
    // $branch = new Branch();

    $product_id = clear_input(p('product_id'));
    $attr_id = clear_input(p('attr_id'));;
    if ($product_id == '' && $attr_id == '') {
        echo json_response('error', get_lang_text('ajax_invalid_request'));
        die;
    }
    $product = null;
    if ($product_id != '') {
        $product = $p->getProduct($product_id);
    } else if ($attr_id != '') {
        $products = $p->getProducts('*', null, [['attr_id', '=', $attr_id], ['product_status', '=', '1'], ['parent_set_id', '=', '0']]);
        if (!empty($products)) {
            $product = $products[0];
        }
    }
    $product_name_text = '';
    if ($product->team_name == '') {
        $product_name_text = $product->product_code;
    } else {
        $product_name_text = $product->team_name . ' - ' . $product->product_code;
    }
    $product->product_name = $product_name_text;

    if (!empty($product->thumbnail_img) && !empty($product->product_img)) {
        $thumbnail_path = PATH . '/uploads/product-img/' . $product->thumbnail_img;
        $product_path = PATH . '/uploads/' . $product->product_img;

        if (file_exists($thumbnail_path) && file_exists($product_path)) {
            $image_link = URL . '/uploads/product-img/' . $product->thumbnail_img;
        } else if (!file_exists($thumbnail_path) && !file_exists($product_path)) {
            $image_link = 'img-error';
        } else {
            generate_image_in_webp($product);
            $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
            $image_link = URL . '/uploads/product-img/' . $check_file_name;
        }
    } else {
        if (empty($product->thumbnail_img) && file_exists(PATH . '/uploads/' . $product->product_img)) {
            generate_image_in_webp($product);
            $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
            $image_link = URL . '/uploads/product-img/' . $check_file_name;
        } else {
            $image_link = 'img-error';
        }
    }
    $product->product_img = $image_link;
    $product->has_variant = has_variant($product);

    $get_attr = $pa->getProductAttribute($product->attr_id);
    $product->type = $get_attr->attr_type;
    $product->calculate_type = $get_attr->calculate_type;
    $product->attr_rates = json_decode($get_attr->attr_rates, true);

    $dims = [];
    if (!empty($product->product_dims_data)) {
        $decoded = json_decode($product->product_dims_data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $dims = $decoded;
        }
    }

    $product->dims = !empty($dims) && isset($dims[0]) ? $dims[0] : [];


    $active_materials = getActiveMaterialsWithAllOptions($p, $mt, $product->product_id);
    $product->active_materials = $active_materials; // Actual material data

    echo json_response('success', 'OK', $product);
    die;
}
if (isset($_POST['get_product_variants'])) {
    $user = new User();
    $logged = $user->getLogged('user_auth');
    $logged_auth = $logged->user_auth;
    $page_auth = ['admin', 'manager', 'graphic_and_media', 'user', 'sales', 'ordermngr', 'partner', 'purchasing', 'encounter'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $p = new Product();
    $pa = new ProductAttribute();
    $mt = new Material();
    // $o = new Order();
    // $ctl = new Catalog();
    // $branch = new Branch();

    $product_id = clear_input(p('product_id'));
    $available_in = clear_input(p('available_in'));
    if ($product_id == '') {
        echo json_response('error', get_lang_text('ajax_invalid_request'));
        die;
    }
    $where = [];
    if (isset($_POST['available_in']) && $_POST['available_in'] == 'size') {
        $where[] = ['product_id', '=', $product_id];
    } else {
        $where[] = ['parent_set_id', '=', $product_id];
    }

    $products = $p->getProducts('*', null, $where);

    foreach ($products as $product) {
        $product_name_text = '';
        if ($product->team_name == '') {
            $product_name_text = $product->product_code;
        } else {
            $product_name_text = $product->team_name . ' - ' . $product->product_code;
        }
        $product->product_name = $product_name_text;

        if (!empty($product->thumbnail_img) && !empty($product->product_img)) {
            $thumbnail_path = PATH . '/uploads/product-img/' . $product->thumbnail_img;
            $product_path = PATH . '/uploads/' . $product->product_img;

            if (file_exists($thumbnail_path) && file_exists($product_path)) {
                $image_link = URL . '/uploads/product-img/' . $product->thumbnail_img;
            } else if (!file_exists($thumbnail_path) && !file_exists($product_path)) {
                $image_link = 'img-error';
            } else {
                generate_image_in_webp($product);
                $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
                $image_link = URL . '/uploads/product-img/' . $check_file_name;
            }
        } else {
            if (empty($product->thumbnail_img) && file_exists(PATH . '/uploads/' . $product->product_img)) {
                generate_image_in_webp($product);
                $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
                $image_link = URL . '/uploads/product-img/' . $check_file_name;
            } else {
                $image_link = 'img-error';
            }
        }
        $product->product_img = $image_link;
        $product->has_variant = has_variant($product);

        $get_attr = $pa->getProductAttribute($product->attr_id);
        $product->type = $get_attr->attr_type;
        $product->calculate_type = $get_attr->calculate_type;
        $product->attr_rates = json_decode($get_attr->attr_rates, true);

        $dims = [];
        if (!empty($product->product_dims_data)) {
            $decoded = json_decode($product->product_dims_data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $dims = $decoded;
            }
        }

        $product->dims = !empty($dims) && isset($dims[0]) ? $dims[0] : [];


        $active_materials = getActiveMaterialsWithAllOptions($p, $mt, $product->product_id);
        $product->active_materials = $active_materials; // Actual material data
    }

    echo json_response('success', 'OK', $products);
    die;
}

if (isset($_POST['get_brands'])) {
    $cat = new Catalog();
    $where = [];
    $where[] = ['catalog_status', '=', '1'];
    $get_catalogs = $cat->getCatalogs('*', $where);

    echo json_response('success', 'OK', $get_catalogs);
    die;
}

if (isset($_POST['get_product_styles'])) {
    $user = new User();
    $logged = $user->getLogged('user_id,user_auth');
    $logged_auth = $logged->user_auth;
    $page_auth = ['admin', 'manager', 'user', 'sales', 'quality', 'purchasing'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $p = new Product();

    $get_styles = $p->getProductStyleTypes();

    echo json_response('success', 'OK', $get_styles);
    die;
}

if (isset($_POST['get_products'])) {
    $user = new User();
    $logged = $user->getLogged('user_auth');
    $logged_auth = $logged->user_auth;
    $page_auth = ['admin', 'manager', 'graphic_and_media', 'user', 'sales', 'ordermngr', 'partner', 'purchasing', 'encounter'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $attr_ids = clear_input(p('qualification_ids')); // present every time
    $is_fitout = $_POST['is_fitout_items'] ?? 0;
    $product_cat = clear_input(p('product_cat') ?? ''); // not available when open first time
    $search_text = html_ent(p('search_text'));
    $product_style = p('product_styles') ?? [];
    if (!is_array($product_style)) {
        $product_style = [$product_style]; // force into array if single value comes
    }

    $pa = new ProductAttribute();
    $p = new Product();
    $o = new Order();
    $ctl = new Catalog();
    $branch = new Branch();

    $item_size = clear_input(p('item_size'));
    $current_count = clear_input(p('current_count'));

    if ($item_size > 0 && $search_text == '') {
        $limit = [$current_count, $item_size];
    } else {
        $limit = null;
    }

    $where_load_prd = [
        ['product_status', '=', '1'],
        ['parent_set_id', '=', '0']
    ];

    if (!empty($product_cat)) {
        $where_load_prd[] = ['product_supplier', '=', $product_cat];
    }
    if ($is_fitout == 1) {
        $where_load_prd[] = ['is_fitout', '=', $is_fitout];
    }
    if (!empty($search_text)) {
        $where_load_prd[] = ['product_code', 'LIKE', '%' . $search_text . '%'];
    }

    $product_in_clause = [];

    // Add first IN clause
    if (!empty($product_style)) {
        $product_in_clause[] = ['product_style_type', $product_style];
    }

    // Add second IN clause
    if (!empty($attr_ids)) {
        $product_in_clause[] = ['attr_id', explode('_', $attr_ids)];
    }

    $product_in_clause = !empty($product_in_clause) ? $product_in_clause : null;

    $return_data = [];
    $products = $p->getProducts(
        '*',
        ['product_id', 'DESC'],
        $where_load_prd,
        $limit,
        null,
        $product_in_clause
    );

    // Get ALL brands first (for the catalog name mapping)
    $get_all_catalogs = $ctl->getCatalogs('*', [['catalog_status', '=', '1']]);
    $ctl_arr = [];
    foreach ($get_all_catalogs as $catalog) {
        $ctl_arr[$catalog->catalog_id] = $catalog->catalog_name;
    }

    $products_data = [];
    $brands_with_products = []; // Track brands that have products

    if (!empty($products)) {
        foreach ($products as $product) {
            $product_name_text = '';
            if ($product->team_name == '') {
                $product_name_text = $product->product_code;
            } else {
                $product_name_text = $product->team_name . ' - ' . $product->product_code;
            }

            // Check if the headers indicate that the file exists
            $url = URL . '/uploads/' . $product->product_img;
            $headers = @get_headers($url);
            if ($headers && strpos($headers[0], '200') == false) {
                $p->deleteProduct($product->product_id);
            }

            // Track this product's brand
            if (isset($ctl_arr[$product->product_supplier])) {
                $brands_with_products[$product->product_supplier] = true;
            }

            $available_in = $product->available_in;
            $avl_arr = ['set', 'size'];
            if (isset($product->product_shapes)) {
                $product_shapes = json_decode($product->product_shapes, true);
                if (count($product_shapes) > 1) {
                    $item_type = $product_shapes[0];
                }
            } else {
                if (in_array($available_in, $avl_arr)) {
                    if ($product->group_item_count > 1) {
                        $item_type = $available_in;
                    } else {
                        $item_type = 'normal';
                    }
                } else {
                    $item_type = 'normal';
                }
            }

            $item_of = get_itme_of($product);
            $where_stock = [
                ['p.product_status', '=', '1']
            ];

            if (!empty($product_cat)) {
                $where_stock[] = ['p.product_supplier', '=', $product_cat];
            }
            $stock_product = $o->getOrdersStockItem('p.*, od.product_qty, o.branch_id, o.order_id, od.stock_branch, od.id,od.stock_qty, od.stock_image', null, $where_stock, [['p.product_id', '=', $product->product_id], ['p.parent_set_id', '=', $product->product_id]]);

            if (count($stock_product) > 0) {
                if ($item_of != 'normal') {
                    $item_of = 'stock_item';
                    $item_type = 'stock_set';
                } else {
                    $item_of = 'stock_item';
                    $item_type = $item_of;
                }
            }

            $get_prd_com_mat = $p->getProductCombinationFullData($product->product_id);
            if (isset($get_prd_com_mat) && count($get_prd_com_mat) > 1) {
                if ($item_type == 'stock_item') {
                    $item_of = 'material_combination';
                    $item_type = 'stock_combination';
                } else if ($item_type == 'stock_set') {
                    $item_of = 'material_combination';
                    $item_type = 'set_stock_combination';
                } else if ($item_type == 'set') {
                    $item_of = 'material_combination';
                    $item_type = 'set_combination';
                } else {
                    $item_of = 'material_combination';
                    $item_type = 'combination';
                }
            }

            $item_type_plan = '';
            $item_of_plan = '';
            if ($logged_auth == 'admin' || $logged_auth == 'sales') {
                $get_prd_plan_100 = $o->getOrdersItemPlan100('*', null, [['p.product_id', '=', $product->product_id], ['p.product_status', '=', '1']], null, [0, 2]);
                if (count($get_prd_plan_100) > 0) {
                    $item_of_plan = 'plan_item';
                    $item_type_plan = 'plan';
                }
            }

            if (!empty($product->thumbnail_img) && !empty($product->product_img)) {
                $thumbnail_path = PATH . '/uploads/product-img/' . $product->thumbnail_img;
                $product_path = PATH . '/uploads/' . $product->product_img;

                if (file_exists($thumbnail_path) && file_exists($product_path)) {
                    $image_link = URL . '/uploads/product-img/' . $product->thumbnail_img;
                } else if (!file_exists($thumbnail_path) && !file_exists($product_path)) {
                    $image_link = 'img-error';
                } else {
                    generate_image_in_webp($product);
                    $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
                    $image_link = URL . '/uploads/product-img/' . $check_file_name;
                }
            } else {
                if (empty($product->thumbnail_img) && file_exists(PATH . '/uploads/' . $product->product_img)) {
                    generate_image_in_webp($product);
                    $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
                    $image_link = URL . '/uploads/product-img/' . $check_file_name;
                } else {
                    $image_link = 'img-error';
                }
            }

            $standart_price = 0;

            if (!empty($product->product_dims_data)) {
                $dims = json_decode($product->product_dims_data, true);
                if (!empty($dims) && isset($dims[0]['standart_price']) && is_numeric($dims[0]['standart_price'])) {
                    $standart_price = (float)$dims[0]['standart_price'];
                }
            } elseif (!empty($product->product_bed_dims)) {
                $bed_dims = json_decode($product->product_bed_dims, true);
                if (is_array($bed_dims)) {
                    $prices = array_column($bed_dims, 'standart_price');
                    $prices = array_filter($prices, 'is_numeric');
                    if (!empty($prices)) $standart_price = min($prices);
                }
            }

            $get_attr = $pa->getProductAttribute($product->attr_id);
            $type = $get_attr->attr_type;

            $has_variant = has_variant($product);

            $product_array = [
                'product_add_date' => $product->product_add_date,
                'product_id' => $product->product_id,
                'attr_id' => $product->attr_id,
                'product_code' => $product->product_code,
                'product_name' => $product_name_text,
                'type' => $type,
                'available_in' => $product->available_in,
                'has_variant' => $has_variant,
                'product_style_type' => $product->product_style_type,
                'item_type' => $item_type,
                'item_of' => $item_of,
                'item_type_plan' => $item_type_plan,
                'item_of_plan' => $item_of_plan,
                'other_data' => [],
                'product_img' => $image_link,
                'catalog_id' => $product->product_supplier,
                'catalog_name' => $ctl_arr[$product->product_supplier],
                'standart_price' => $standart_price
            ];

            $products_data[] = $product_array;
        }
    }

    // Filter brands to only include those that have products
    $filtered_brands = [];
    foreach ($get_all_catalogs as $catalog) {
        if (isset($brands_with_products[$catalog->catalog_id])) {
            $filtered_brands[] = $catalog;
        }
    }

    // Get styles - you might also want to filter styles based on available products
    $get_styles = $p->getProductStyleTypes();

    // Return filtered brands, styles, and products together
    $response_data = [
        'brands' => $filtered_brands,
        'styles' => $get_styles,
        'products' => $products_data,
        'where_load_prd' => $where_load_prd,
        'limit' => $limit,
        'product_in_clause' => $product_in_clause
    ];

    echo json_response('success', 'OK', $response_data);
    die;
}

if (isset($_POST['add_order_with_newlayout'])) {
    $user = new User();
    $logged = $user->getLogged('user_id,user_auth,user_branch');
    $logged_auth = $logged->user_auth;
    $branch_id = $logged->user_branch;

    $page_auth = ['admin', 'manager', 'graphic_and_media', 'user', 'sales', 'ordermngr', 'partner', 'encounter'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    set_time_limit(0);

    // Initialize required classes
    $o = new Order();
    $p = new Product();
    $pa = new ProductAttribute();
    $m = new Material();
    $c = new Customer();
    $a = new Agreement();
    $plan = new Plan();

    // Get the order data from POST
    $order_data_json = $_POST['order_data'] ?? '{}';
    $order_data = json_decode($order_data_json, true);

    // echo '<pre>';
    // print_r($order_data);
    // echo '</pre>';
    // die;

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_response('error', 'Invalid order data format');
        die;
    }

    // Extract order-level data
    $order_date = date('Y-m-d', strtotime(clear_input($order_data['order_date'] ?? '')));
    $order_delivery_date = date('Y-m-d', strtotime(clear_input($order_data['order_delivery_date'] ?? '')));
    $customer_id = clear_input($order_data['customer_id'] ?? '');
    $customer_address_id = clear_input($order_data['customer_address_id'] ?? '');
    $order_arcs = html_ent($order_data['order_arcs'] ?? '');
    $order_agreement = clear_input($order_data['order_agreement'] ?? '');
    $order_agreement_text = html_ent(nl2br($order_data['order_agreement_text'] ?? ''));
    $order_tax = clear_input($order_data['order_tax'] ?? '');
    $order_export_registered = clear_input($order_data['order_export_registered'] ?? '');
    $order_notes = html_ent(nl2br($order_data['order_notes'] ?? ''));
    $order_comm_rate = clear_input($order_data['order_comm_rate'] ?? '');
    $order_comm_amount = clear_input($order_data['order_comm_amount'] ?? '');

    // Validation checks
    if (
        $order_date == '' || $customer_id == '' || $customer_address_id == '' || $order_arcs == '' ||
        $order_agreement == '' || $order_agreement_text == '' || $order_tax == '' || $order_export_registered == '' || $order_delivery_date == ''
    ) {
        echo json_response('error', get_lang_text('ajax_fill_required_fields'));
        die;
    }

    if (DateTime::createFromFormat('Y-m-d', $order_date) === false) {
        echo json_response('error', get_lang_text('ajax_order_date_error'));
        die;
    }

    if (DateTime::createFromFormat('Y-m-d', $order_delivery_date) === false) {
        echo json_response('error', get_lang_text('ajax_order_delivery_date_error'));
        die;
    }

    $order_date_time = strtotime($order_date);
    $order_delivery_date_time = strtotime($order_delivery_date);

    if ($order_date_time > $order_delivery_date_time) {
        echo json_response('error', get_lang_text('ajax_order_date_invalid'));
        die;
    }

    if ($o->isOrderDeliveryDateFull($order_delivery_date)) {
        echo json_response('error', get_lang_text('ajax_order_delivery_date_invalid'));
        die;
    }

    if (!$c->checkCustomerById($customer_id) || !$c->checkCustomerAddressById($customer_address_id) || !$a->checkAgreementById($order_agreement)) {
        echo json_response('error', get_lang_text('ajax_order_invalid_input_error'));
        die;
    }

    if ($order_comm_rate != '' && $order_comm_amount != '') {
        echo json_response('error', get_lang_text('ajax_order_comm_rate_amount_error'));
        die;
    }

    if ($order_comm_rate != '' && !is_numeric($order_comm_rate)) {
        echo json_response('error', get_lang_text('ajax_order_invalid_comm_rate'));
        die;
    }

    if ($order_comm_amount != '' && !is_numeric($order_comm_amount)) {
        echo json_response('error', get_lang_text('ajax_order_invalid_comm_amount'));
        die;
    }

    // Get customer and agreement data
    $customer = $c->getCustomer($customer_id, 'customer_id,customer_name,customer_email,customer_comm_rate');
    $customer_address = $c->getCustomerAddress($customer_address_id, $customer_id, 'adr_country,adr_text');
    $customer_address_country = Country::getCountry($customer_address->adr_country, 'country_name');
    $customer_full_address_text = $customer_address->adr_text . ' - ' . $customer_address_country->country_name;

    $get_agr = $a->getAgreement($order_agreement, 'branch_id');
    if ($logged_auth == 'user') {
        if ($logged->user_branch != $get_agr->branch_id) {
            echo json_response('error', get_lang_text('ajax_invalid_request'));
            die;
        }
    }

    // Process room-based order details
    $order_total_price = 0;
    $order_details = [];
    $detail_counter = 0;
    if (isset($order_data['rooms']) && is_array($order_data['rooms'])) {
        foreach ($order_data['rooms'] as $roomIndex => $roomData) {
            $room_id = clear_input($roomData['room_id'] ?? ($roomIndex + 1));
            $floor_name = clear_input($roomData['floor_name'] ?? '');
            $room_name = clear_input($roomData['room_name'] ?? '');

            // Process room image
            $room_image_name = '';
            if (isset($_FILES["room_image_{$room_id}"]) && !empty($_FILES["room_image_{$room_id}"]['name'])) {
                $room_image_name = uploadRoomImage($_FILES["room_image_{$room_id}"], $room_id);
            }

            $room_info = [
                'floor_name' => $floor_name,
                'room_name' => $room_name,
                'room_id' => $room_id,
                'room_image' => $room_image_name
            ];

            // Process products in this room
            if (isset($roomData['products']) && is_array($roomData['products'])) {
                foreach ($roomData['products'] as $productIndex => $productData) {
                    $detail_counter++;
                    $product_detail = processProductDataNewLayout($productData, $room_info, $detail_counter);
                    if ($product_detail) {
                        $order_details[] = $product_detail;
                        $order_total_price += $product_detail['total_price'];
                    }
                }
            }
        }
    }

    // echo '<pre>'; print_r($_POST); echo '</pre>';
    // die;
    // Prepare order data
    $get_currencies = getCurrencies(['TRY']);
    $order_usd_price = formatExcelPrice($get_currencies['TRY']['rate'], 4, '.', '');
    $add_date = date('Y-m-d H:i:s');

    $order_main_data = [
        'order_date' => $order_date,
        'customer_id' => $customer_id,
        'branch_id' => $get_agr->branch_id,
        'address_id' => $customer_address_id,
        'country_id' => $customer_address->adr_country,
        'address_text' => $customer_full_address_text,
        'order_arcs' => $order_arcs,
        'order_price' => $order_total_price,
        'order_usd_price' => $order_usd_price,
        'order_customer_comm' => $customer->customer_comm_rate ?? 0,
        'agreement_id' => $order_agreement,
        'agreement_text' => $order_agreement_text,
        'order_notes' => $order_notes,
        'order_tax' => $order_tax,
        'order_export_registered' => $order_export_registered,
        'added_user_id' => $logged->user_id,
        'order_add_date' => $add_date,
        'order_behavior' => 'new_system'
    ];

    // Handle commission fields
    if ($order_comm_amount != '') {
        $order_main_data['order_comm_amount'] = $order_comm_amount;
    } elseif ($order_comm_rate != '') {
        $order_main_data['order_comm_rate'] = $order_comm_rate;
    }

    // Insert order
    $insert = $o->addOrder($order_main_data);
    if ($insert) {
        $order_id = $insert;

        // Update delivery date
        $update_order_delivery_date = $o->updateOrderDeliveryDate($order_id, $order_delivery_date);

        // Handle commission field cleanup
        if ($order_comm_amount != '') {
            $o->updateOrderColAsNull('order_comm_rate', $order_id);
        } elseif ($order_comm_rate != '') {
            $o->updateOrderColAsNull('order_comm_amount', $order_id);
        }

        // Save order details
        foreach ($order_details as $detail_index => $detail) {
            $detail_id = saveOrderDetailNewLayout($o, $order_id, $detail, $detail_index + 1);
            if ($detail_id) {
                $add_plan_data = $plan->addPlan($order_id, $detail_id);
            }
        }

        echo json_response('success', get_lang_text('ajax_orderadd_success'));
        die;
    } else {
        echo json_response('error', get_lang_text('ajax_orderadd_error'));
        die;
    }
}

function processProductDataNewLayout($productData, $roomInfo, $product_order_no)
{
    $p = new Product();
    $pa = new ProductAttribute();
    $m = new Material();

    $product_id = clear_input($productData['product_id'] ?? '');
    $product_type = clear_input($productData['type'] ?? 'product');
    $available_in = clear_input($productData['available_in'] ?? '');
    $quantity = floatval($productData['quantity'] ?? 1);
    $discount = floatval($productData['discount'] ?? 0);
    $unit_price = floatval($productData['unit_price'] ?? 0);
    $calculate_type = clear_input($productData['calculate_type'] ?? 'standart');

    // Validate product
    if (!$p->checkProductById($product_id)) {
        throw new Exception(get_lang_text('ajax_order_invalid_product_selected'));
    }

    $product = $p->getProduct($product_id);
    $product_attr = $pa->getProductAttribute($product->attr_id);

    // Calculate base price based on product type and dimensions
    $base_price = calculateProductBasePriceNewLayout($product, $productData, $product_attr);

    // Calculate material costs
    $material_cost = 0;
    if (isset($productData['materials']) && is_array($productData['materials'])) {
        $material_result = calculateTotalMaterialsCostNewLayout($m, $productData['materials']);
        $material_cost = $material_result['cost'];
    }

    // Calculate surcharges
    $surcharge_total = 0;
    $detail_attr_rates = [];
    if (isset($productData['surcharges']) && is_array($productData['surcharges'])) {
        $surcharge_result = calculateSurchargesNewLayout($productData['surcharges'], $base_price + $material_cost);
        $surcharge_total = $surcharge_result['total'];
        $detail_attr_rates = $surcharge_result['rates'];
    }

    // Calculate subtotal and apply discount
    $subtotal = $base_price + $material_cost + $surcharge_total;
    $discount_amount = $subtotal * ($discount / 100);
    $final_price = $subtotal - $discount_amount;

    // Calculate total for quantity
    $total_price = $final_price * $quantity;

    // Prepare product dimensions data
    $attr_data = prepareProductDimensionsNewLayout($productData, $product_attr, $detail_attr_rates);

    // Prepare curtain data if applicable
    $curtain_data = '';
    if ($product_type === 'curtain') {
        $curtain_data = prepareCurtainDataNewLayout($productData, $product_attr);
    }

    // Prepare fitout items data if applicable
    $fitout_items_data = '';
    if ($product_type === 'fitout' && isset($productData['items']) && is_array($productData['items'])) {
        $fitout_items_data = prepareFitoutItemsDataNewLayout($productData['items']);
    }

    return [
        'product_id' => $product_id,
        'product_order_no' => $product_order_no,
        'product_room_index' => $roomInfo['room_id'],
        'product_room_img' => $roomInfo['room_image'] ?? '',
        'product_cat' => $product->product_supplier ?? '',
        'product_room_data' => json_encode($roomInfo, JSON_UNESCAPED_UNICODE),
        'detail_of' => 'main',
        'calculate_type' => $calculate_type,
        'product_qty' => $quantity,
        'product_price' => $final_price,
        'product_base_price' => $base_price,
        'product_discount' => $discount,
        'product_notes_tr' => $productData['notes_tr'] ?? '',
        'product_notes_en' => $productData['notes_en'] ?? '',
        'product_edited' => 0,
        'sponge_type' => $productData['sponge_type'] ?? '',
        'person_weight' => $productData['person_weight'] ?? 0,
        'wholesale_percentage' => $product->mfg_cost_percent ?? 0,
        'total_price' => $total_price,
        'materials' => $productData['materials'],
        'detail_attr' => $attr_data,
        'product_curtain_data' => $curtain_data,
        'fitout_items_data' => $fitout_items_data,
        'surcharges' => $detail_attr_rates
    ];
}

function prepareFitoutItemsDataNewLayout($items)
{
    $fitout_items_data = [];

    foreach ($items as $item) {
        $item_data = [
            'item_id' => $item['item_id'] ?? '',
            'width' => floatval($item['width'] ?? 0),
            'length' => floatval($item['length'] ?? 0),
            'height' => floatval($item['height'] ?? 0),
            'quantity' => floatval($item['quantity'] ?? 1),
            'discount' => floatval($item['discount'] ?? 0),
            'unit_price' => floatval($item['unit_price'] ?? 0),
            'calculate_type' => $item['calculate_type'] ?? 'standart',
            'notes' => $item['notes'] ?? ''
        ];

        // Add materials for the item if available
        if (isset($item['materials'])) {
            $item_data['materials'] = $item['materials'];
        }

        // Add surcharges for the item if available
        if (isset($item['surcharges'])) {
            $item_data['surcharges'] = $item['surcharges'];
        }

        $fitout_items_data[] = $item_data;
    }

    return json_encode($fitout_items_data, JSON_UNESCAPED_UNICODE);
}

function calculateProductBasePriceNewLayout($product, $productData, $productAttr)
{
    $calculate_type = $productAttr->calculate_type;
    $width = floatval($productData['width'] ?? 0);
    $length = floatval($productData['length'] ?? 0);
    $height = floatval($productData['height'] ?? 0);
    $quantity = floatval($productData['quantity'] ?? 1);
    $unit_price = floatval($productData['unit_price'] ?? 0);

    // If unit_price is provided, use it directly
    if ($unit_price > 0) {
        return calculatePrice($calculate_type, $unit_price, $width, $length, $height, $quantity);
    }

    // Otherwise calculate from product data
    $standart_price = $product->standart_price ?? 0;

    switch ($calculate_type) {
        case 'boy': // Length-based
            return ($standart_price * $length) * $quantity;
        case 'en': // Width-based
            return ($standart_price * $width) * $quantity;
        case 'yuksek': // Height-based
            return ($standart_price * $height) * $quantity;
        case 'enboy': // Area (width × length)
            return ($standart_price * ($width * $length)) * $quantity;
        case 'yukseken': // Area (width × height)
            return ($standart_price * ($width * $height)) * $quantity;
        case 'yuksekboy': // Area (length × height)
            return ($standart_price * ($length * $height)) * $quantity;
        case 'hepsi': // Volume (width × length × height)
            return ($standart_price * ($width * $length * $height)) * $quantity;
        case 'standart':
        default:
            return $standart_price * $quantity;
    }
}

function prepareProductDimensionsNewLayout($productData, $productAttr, $detail_attr_rates)
{
    $attr_data = [
        'attr_bed_dim' => 0,
        'attr_dims' => '',
        'attr_rates' => json_encode($detail_attr_rates, JSON_UNESCAPED_UNICODE),
    ];

    $dims_data = [];

    // Handle different product types
    if ($productAttr->attr_type == 'bed') {
        $bed_dim = clear_input($productData['bed_dim'] ?? '180200');

        if (isset($productData['selected_variant']) && is_array($productData['selected_variant'])) {
            $bed_dims = $productData['selected_variant'];
            $dims_data[] = [
                'width' => $bed_dims['width'] ?? 0,
                'length' => $bed_dims['length'] ?? 0,
                'height' => $bed_dims['height'] ?? 0,
                'standart_width' => $bed_dims['standart_width'] ?? 0,
                'standart_length' => $bed_dims['standart_length'] ?? 0,
                'standart_height' => $bed_dims['standart_height'] ?? 0,
                'quantity' => $bed_dims['quantity'] ?? 0,
                'discount' => $bed_dims['discount'] ?? 0,
                'unit_price' => $bed_dims['unit_price'] ?? 0,
                'bed_dim' => $bed_dim
            ];
            $attr_data['attr_bed_dim'] = $bed_dim;
        }
    } else {
        // Handle sets/variants
        if (isset($productData['sets']) && is_array($productData['sets']) && count($productData['sets']) > 0) {
            foreach ($productData['sets'] as $setData) {
                $dims_data[] = [
                    'width' => floatval($setData['width'] ?? 0),
                    'length' => floatval($setData['length'] ?? 0),
                    'height' => floatval($setData['height'] ?? 0),
                    'standart_width' => $setData['standart_width'] ?? 0,
                    'standart_length' => $setData['standart_length'] ?? 0,
                    'standart_height' => $setData['standart_height'] ?? 0,
                    'quantity' => floatval($setData['quantity'] ?? 1),
                    'unit_price' => floatval($setData['unit_price'] ?? 0),
                    'discount' => floatval($setData['discount'] ?? 0)
                ];
            }
        } else {
            $dims_data[] = [
                'width' => floatval($productData['width'] ?? 0),
                'length' => floatval($productData['length'] ?? 0),
                'height' => floatval($productData['height'] ?? 0),
                'standart_width' => $productData['standart_width'] ?? 0,
                'standart_length' => $productData['standart_length'] ?? 0,
                'standart_height' => $productData['standart_height'] ?? 0,
                'quantity' => floatval($productData['quantity'] ?? 1),
                'unit_price' => floatval($productData['unit_price'] ?? 0),
                'discount' => floatval($productData['discount'] ?? 0)
            ];
        }
    }

    // echo '<pre>';
    // print_r($productData);
    // echo '</pre>';
    // die;

    $attr_data['attr_dims'] = json_encode($dims_data, JSON_UNESCAPED_UNICODE);
    return $attr_data;
}

function calculateBedPrice($product, $productData, $productAttr)
{
    $bed_dim = clear_input($productData['bed_dim'] ?? '180200');
    $bed_dims = json_decode($product->product_bed_dims, true);

    if (!isset($bed_dims[$bed_dim])) {
        throw new Exception('Invalid bed dimension');
    }

    $dim_data = $bed_dims[$bed_dim];
    $standart_price = $product->standart_price;

    // Calculate based on dimension ratios (similar to old code)
    $price_multiplier = 1.0;
    switch ($bed_dim) {
        case '200200':
            $price_multiplier = 1.15;
            break;
        case '160200':
            $price_multiplier = 0.85;
            break;
        case '140200':
            $price_multiplier = 0.7225;
            break; // 0.85 * 0.85
        case '120200':
            $price_multiplier = 0.614125;
            break; // 0.85 * 0.85 * 0.85
        default:
            $price_multiplier = 1.0;
    }

    return $standart_price * $price_multiplier;
}

function calculateCurtainPrice($productData, $productAttr)
{
    $m = new Material();
    $total_price = 0;

    if (isset($productData['curtain_data']) && is_array($productData['curtain_data'])) {
        foreach ($productData['curtain_data'] as $curtain) {
            if (!empty($curtain['fabric']) && !empty($curtain['length']) && !empty($curtain['height'])) {
                $fabric_item = $m->getMaterial($curtain['fabric'], 'material_price');
                $unit_price = formatExcelPrice($fabric_item->material_price, 10, '.', '');

                $curtain_dims = $curtain['length'] * 3;
                $curtain_dims = ceil($curtain_dims / 100) * 100; // CURTAIN_PRICE_MULTIPLIER assumed as 100
                $curtain_dims = $curtain_dims * $curtain['height'] * 0.0001;
                $curtain_price = $curtain_dims * $unit_price;

                $total_price += $curtain_price;
            }
        }
    }

    return $total_price;
}

function calculateStandardPrice($calculateType, $product, $width, $length, $height)
{
    $standart_price = $product->standart_price;

    switch ($calculateType) {
        case 'boy':
            return ($standart_price / 100) * $length; // Assuming 100 as base length
        case 'en':
            return ($standart_price / 100) * $width; // Assuming 100 as base width
        case 'yuksek':
            return ($standart_price / 100) * $height; // Assuming 100 as base height
        case 'enboy':
            return ($standart_price / 10000) * ($width * $length);
        case 'yukseken':
            return ($standart_price / 10000) * ($width * $height);
        case 'yuksekboy':
            return ($standart_price / 10000) * ($length * $height);
        case 'hepsi':
            return ($standart_price / 1000000) * ($width * $length * $height);
        case 'standart':
        default:
            return $standart_price;
    }
}

function calculateSingleMaterialCostNewLayout($material, $category, $material_info)
{
    $material_price = $material->material_price ?? 0;

    // Handle different material types
    switch ($category) {
        case 'metal':
            $weight_kg = floatval($material_info[3] ?? 1); // Assuming weight is provided
            return $material_price * $weight_kg;

        case 'wood':
        case 'marble':
        case 'glass':
        case 'fabric':
            $area_m2 = floatval($material_info[3] ?? 1); // Assuming area is provided
            return $material_price * $area_m2;

        case 'pillow':
            if (isset($material_info[1]) && is_array($material_info[1])) {
                $pillow_data = $material_info[1];
                // Calculate pillow cost based on dimensions and quantity
                $quantity = floatval($pillow_data['quantity'] ?? 1);
                $length = floatval($pillow_data['dimensions']['length'] ?? 0);
                $width = floatval($pillow_data['dimensions']['width'] ?? 0);
                $area_m2 = ($length * $width) / 10000; // Convert cm² to m²
                return $material_price * $area_m2 * $quantity;
            }
            return $material_price;

        default:
            return $material_price;
    }
}

function calculateSurchargesNewLayout($surcharges, $base_amount)
{
    $total_surcharge = 0;
    $rates_data = [];

    foreach ($surcharges as $surcharge) {
        if ($surcharge['applied'] && is_numeric($surcharge['rate'])) {
            $surcharge_amount = $base_amount * ($surcharge['rate'] / 100);

            if ($surcharge['type'] === 'minus') {
                $surcharge_amount = -$surcharge_amount;
            }

            $total_surcharge += $surcharge_amount;

            $rates_data[] = [
                'name' => $surcharge['name'] ?? '',
                'type' => $surcharge['type'],
                'rate' => floatval($surcharge['rate'])
            ];
        }
    }

    return [
        'total' => $total_surcharge,
        'rates' => $rates_data
    ];
}

function prepareCurtainDataNewLayout($productData, $productAttr)
{
    if ($productAttr->attr_type != 'curtain') {
        return json_encode([], JSON_UNESCAPED_UNICODE);
    }

    $curtain_data = [];

    // Process curtain options
    if (isset($productData['curtain_data'])) {
        $curtain_data = $productData['curtain_data'];
    }

    return json_encode($curtain_data, JSON_UNESCAPED_UNICODE);
}

function saveOrderDetailNewLayout($o, $orderId, $detail, $row_number)
{
    $detailData = [
        'order_id' => $orderId,
        'product_id' => $detail['product_id'],
        'product_row_number' => $row_number,
        'product_room_index' => $detail['product_room_index'],
        'product_room_img' => $detail['product_room_img'],
        'product_cat' => $detail['product_cat'],
        'product_room_data' => $detail['product_room_data'],
        'detail_of' => $detail['detail_of'],
        'calculate_type' => $detail['calculate_type'],
        'product_qty' => $detail['product_qty'],
        'product_price' => $detail['product_price'],
        'product_base_price' => $detail['product_base_price'],
        'product_discount' => $detail['product_discount'],
        'product_curtain_data' => $detail['product_curtain_data'],
        'product_notes_tr' => $detail['product_notes_tr'],
        'product_notes_en' => $detail['product_notes_en'],
        'product_edited' => $detail['product_edited'],
        'sponge_type' => $detail['sponge_type'],
        'person_weight' => $detail['person_weight'],
        'wholesale_percentage' => $detail['wholesale_percentage'],
        'order_behavior' => 'new_system'
    ];

    // Add fitout items data if available
    if (!empty($detail['fitout_items_data'])) {
        $detailData['fitout_items_data'] = $detail['fitout_items_data'];
    }

    $detail_id = $o->addOrderDetails($detailData);

    if ($detail_id) {

        $attr_data = $detail['detail_attr'];
        $o->addOrderDetailAttr($attr_data, $orderId, $detail_id);

        if (isset($detail['materials']) && is_array($detail['materials'])) {
            saveOrderMaterialsNewLayout($o, $orderId, $detail_id, $detail['materials']);
        }
    }

    return $detail_id;
}

function calculateBedUnitPrice($product, $bed_dim)
{
    $bed_dims = json_decode($product->product_bed_dims, true);
    $standart_price = $product->standart_price ?? 0;

    if (!isset($bed_dims[$bed_dim]) || !isset($bed_dims['180200'])) {
        return 0;
    }

    $price_multiplier = 1.0;
    switch ($bed_dim) {
        case '200200':
            $price_multiplier = 1.15;
            break;
        case '160200':
            $price_multiplier = 0.85;
            break;
        case '140200':
            $price_multiplier = 0.7225;
            break;
        case '120200':
            $price_multiplier = 0.614125;
            break;
        default:
            $price_multiplier = 1.0;
    }

    return $standart_price * $price_multiplier;
}

// Calculate variant total
function calculateVariantTotal($variant, $productAttr)
{
    $width = floatval($variant['width']);
    $length = floatval($variant['length']);
    $height = floatval($variant['height']);
    $quantity = floatval($variant['quantity']);
    $unitPrice = floatval($variant['unit_price']);
    $calculateType = $productAttr->calculate_type;

    return calculatePrice($calculateType, $unitPrice, $width, $length, $height, $quantity);
}

// Calculate simple product total
function calculateSimpleProductTotal($productData, $productAttr)
{
    $width = floatval($productData['width']);
    $length = floatval($productData['length']);
    $height = floatval($productData['height']);
    $quantity = floatval($productData['quantity']);
    $unitPrice = floatval($productData['unit_price']);
    $calculateType = $productAttr->calculate_type;

    return calculatePrice($calculateType, $unitPrice, $width, $length, $height, $quantity);
}

// Universal price calculation function
function calculatePrice($calculateType, $unitPrice, $width, $length, $height, $quantity)
{
    $finalPrice = 0;

    switch ($calculateType) {
        case 'boy': // Length-based
            $finalPrice = $unitPrice * $length;
            break;
        case 'en': // Width-based
            $finalPrice = $unitPrice * $width;
            break;
        case 'yuksek': // Height-based
            $finalPrice = $unitPrice * $height;
            break;
        case 'enboy': // Area (width × length)
            $finalPrice = $unitPrice * ($width * $length);
            break;
        case 'yukseken': // Area (width × height)
            $finalPrice = $unitPrice * ($width * $height);
            break;
        case 'yuksekboy': // Area (length × height)
            $finalPrice = $unitPrice * ($length * $height);
            break;
        case 'hepsi': // Volume (width × length × height)
            $finalPrice = $unitPrice * ($width * $length * $height);
            break;
        case 'standart':
        default:
            $finalPrice = $unitPrice;
            break;
    }

    return $finalPrice * $quantity;
}

// Upload room image
function uploadRoomImage($file, $room_id)
{
    $allowed_ext = getAllowedImageTypes('ext');
    $allowed_mimes = getAllowedImageTypes('mime');

    $ext_explode = explode('.', $file['name']);
    $uzanti = strtolower(array_pop($ext_explode));
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($uzanti, $allowed_ext) || !in_array($mime, $allowed_mimes)) {
        return '';
    }

    $max_filesize_byte = MAX_IMG_SIZE * 1024 * 1024;
    if ($file['size'] > $max_filesize_byte) {
        return '';
    }

    $now = date('YmdHis') . microtime() . rand(0, 999);
    $new_image_name = md5($file['name'] . $now) . sha1($file['name'] . $now) . rand(1, 999) . '.jpg';

    $upload_path = PATH . '/uploads/' . $new_image_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_image_name;
    }

    return '';
}

if (isset($_POST['get_order_for_edit'])) {
    $order_id = clear_input($_POST['order_id']);

    $user = new User();
    $logged = $user->getLogged('user_id,user_auth,user_branch');
    $logged_auth = $logged->user_auth;

    $page_auth = ['admin', 'manager', 'graphic_and_media', 'user', 'sales', 'ordermngr', 'partner', 'encounter'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    $o = new Order();
    $p = new Product();
    $pa = new ProductAttribute();
    $mt = new Material();

    $order = $o->getOrder($order_id);

    if (!$order) {
        echo json_response('error', 'Order not found');
        die;
    }

    // Get order details with complete data
    $order_details = $o->getOrderDetails($order_id);

    $order_data = [
        'order_info' => $order,
        'order_details' => []
    ];

    foreach ($order_details as $detail) {
        // Get product data
        $product = $p->getProduct($detail->product_id);

        // Get product attribute
        $product_attr = $pa->getProductAttribute($product->attr_id);

        // Process product name
        $product_name_text = '';
        if ($product->team_name == '') {
            $product_name_text = $product->product_code;
        } else {
            $product_name_text = $product->team_name . ' - ' . $product->product_code;
        }
        $product->product_name = $product_name_text;

        // Process product image
        if (!empty($product->thumbnail_img) && !empty($product->product_img)) {
            $thumbnail_path = PATH . '/uploads/product-img/' . $product->thumbnail_img;
            $product_path = PATH . '/uploads/' . $product->product_img;

            if (file_exists($thumbnail_path) && file_exists($product_path)) {
                $image_link = URL . '/uploads/product-img/' . $product->thumbnail_img;
            } else if (!file_exists($thumbnail_path) && !file_exists($product_path)) {
                $image_link = 'img-error';
            } else {
                generate_image_in_webp($product);
                $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
                $image_link = URL . '/uploads/product-img/' . $check_file_name;
            }
        } else {
            if (empty($product->thumbnail_img) && file_exists(PATH . '/uploads/' . $product->product_img)) {
                generate_image_in_webp($product);
                $check_file_name = preg_replace('/\.\w+$/', '.webp', $product->product_img);
                $image_link = URL . '/uploads/product-img/' . $check_file_name;
            } else {
                $image_link = 'img-error';
            }
        }
        $product->product_img = $image_link;

        // Get active materials
        $active_materials = getActiveMaterialsWithAllOptions($p, $mt, $product->product_id);

        // Get detail attribute data
        $detail_attr = $o->getOrderDetailAttr($order_id, $detail->id);
        $attr_dims = [];
        $attr_rates = [];

        if ($detail_attr) {
            $attr_dims = json_decode($detail_attr->attr_dims, true) ?: [];
            $attr_rates = json_decode($detail_attr->attr_rates, true) ?: [];
        }

        // Prepare product data
        $product_data = [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'product_img' => $product->product_img,
            'type' => $product_attr->attr_type,
            'available_in' => $product->available_in,
            'has_variant' => has_variant($product),
            'calculate_type' => $product_attr->calculate_type,
            'attr_rates' => $product_attr->attr_rates,
            'active_materials' => $active_materials,
            'dims' => !empty($attr_dims) && isset($attr_dims[0]) ? $attr_dims[0] : []
        ];

        $detail_attr = $o->getOrderDetailAttr($order_id, $detail->id); 

        $detail_data = [
            'detail_id' => $detail->id,
            'product_id' => $detail->product_id,
            'product_type' => $product_attr->attr_type,
            'available_in' => $product->available_in,
            'quantity' => $detail->product_qty,
            'discount' => $detail->product_discount,
            'unit_price' => $detail->product_price,
            'calculate_type' => $detail->calculate_type,
            'room_data' => json_decode($detail->product_room_data, true) ?: [],
            'product_data' => $product_data,
            'detail_attr' => $detail_attr,
            'product_room_index' => $detail->product_room_index,
            'curtain_data' => json_decode($detail->product_curtain_data, true) ?: [],
            'fitout_items_data' => json_decode($detail->fitout_items_data, true) ?: []
        ];

        $order_data['order_details'][] = $detail_data;
    }

    echo json_response('success', 'Order data retrieved', $order_data);
    die;
}

if (isset($_POST['update_order_with_newlayout'])) {
    $order_id = clear_input($_POST['order_id']);

    $user = new User();
    $logged = $user->getLogged('user_id,user_auth,user_branch');
    $logged_auth = $logged->user_auth;

    $page_auth = ['admin', 'manager', 'graphic_and_media', 'user', 'sales', 'ordermngr', 'partner', 'encounter'];
    if (!in_array($logged_auth, $page_auth)) {
        echo json_response('error', get_lang_text('ajax_unauthorized_access'));
        die;
    }

    set_time_limit(0);

    // Initialize classes
    $o = new Order();
    $p = new Product();
    $pa = new ProductAttribute();
    $m = new Material();
    $c = new Customer();
    $a = new Agreement();
    $plan = new Plan();

    // Check if order exists and user has permission
    $existing_order = $o->getOrder($order_id);
    if (!$existing_order) {
        echo json_response('error', 'Order not found');
        die;
    }

    if ($logged_auth == 'user' && $logged->user_branch != $existing_order->branch_id) {
        echo json_response('error', get_lang_text('ajax_invalid_request'));
        die;
    }

    // Get the order data from POST
    $order_data_json = $_POST['order_data'] ?? '{}';
    $order_data = json_decode($order_data_json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_response('error', 'Invalid order data format');
        die;
    }

    // Extract order-level data
    $order_date = date('Y-m-d', strtotime(clear_input($order_data['order_date'] ?? '')));
    $order_delivery_date = date('Y-m-d', strtotime(clear_input($order_data['order_delivery_date'] ?? '')));
    $customer_id = clear_input($order_data['customer_id'] ?? '');
    $customer_address_id = clear_input($order_data['customer_address_id'] ?? '');
    $order_arcs = html_ent($order_data['order_arcs'] ?? '');
    $order_agreement = clear_input($order_data['order_agreement'] ?? '');
    $order_agreement_text = html_ent(nl2br($order_data['order_agreement_text'] ?? ''));
    $order_tax = clear_input($order_data['order_tax'] ?? '');
    $order_export_registered = clear_input($order_data['order_export_registered'] ?? '');
    $order_notes = html_ent(nl2br($order_data['order_notes'] ?? ''));
    $order_comm_rate = clear_input($order_data['order_comm_rate'] ?? '');
    $order_comm_amount = clear_input($order_data['order_comm_amount'] ?? '');
    $order_status = clear_input($order_data['order_status'] ?? 'quotation');
    $dlv_date_modified = clear_input($order_data['dlv_date_modified'] ?? '0');

    // Validation
    $valid_status = ['completed', 'quotation', 'revized', 'cancel'];
    if (!in_array($order_status, $valid_status)) {
        echo json_response('error', get_lang_text('ajax_invalid_request'));
        die;
    }

    // Validation checks
    if (
        $order_date == '' || $customer_id == '' || $customer_address_id == '' || $order_arcs == '' ||
        $order_agreement == '' || $order_agreement_text == '' || $order_tax == '' || $order_export_registered == '' || $order_delivery_date == ''
    ) {
        echo json_response('error', get_lang_text('ajax_fill_required_fields'));
        die;
    }

    if (DateTime::createFromFormat('Y-m-d', $order_date) === false) {
        echo json_response('error', get_lang_text('ajax_order_date_error'));
        die;
    }

    if (DateTime::createFromFormat('Y-m-d', $order_delivery_date) === false) {
        echo json_response('error', get_lang_text('ajax_order_delivery_date_error'));
        die;
    }

    $order_date_time = strtotime($order_date);
    $order_delivery_date_time = strtotime($order_delivery_date);

    if ($order_date_time > $order_delivery_date_time) {
        echo json_response('error', get_lang_text('ajax_order_date_invalid'));
        die;
    }

    if (!$c->checkCustomerById($customer_id) || !$c->checkCustomerAddressById($customer_address_id) || !$a->checkAgreementById($order_agreement)) {
        echo json_response('error', get_lang_text('ajax_order_invalid_input_error'));
        die;
    }

    if ($order_comm_rate != '' && $order_comm_amount != '') {
        echo json_response('error', get_lang_text('ajax_order_comm_rate_amount_error'));
        die;
    }

    if ($order_comm_rate != '' && !is_numeric($order_comm_rate)) {
        echo json_response('error', get_lang_text('ajax_order_invalid_comm_rate'));
        die;
    }

    if ($order_comm_amount != '' && !is_numeric($order_comm_amount)) {
        echo json_response('error', get_lang_text('ajax_order_invalid_comm_amount'));
        die;
    }

    // Get customer and agreement data
    $customer = $c->getCustomer($customer_id, 'customer_id,customer_name,customer_email,customer_comm_rate');
    $customer_address = $c->getCustomerAddress($customer_address_id, $customer_id, 'adr_country,adr_text');
    $customer_address_country = Country::getCountry($customer_address->adr_country, 'country_name');
    $customer_full_address_text = $customer_address->adr_text . ' - ' . $customer_address_country->country_name;

    $get_agr = $a->getAgreement($order_agreement, 'branch_id');
    if ($logged_auth == 'user') {
        if ($logged->user_branch != $get_agr->branch_id) {
            echo json_response('error', get_lang_text('ajax_invalid_request'));
            die;
        }
    }

    // Process room-based order details
    $order_total_price = 0;
    $order_details = [];
    $detail_counter = 0;

    if (isset($order_data['rooms']) && is_array($order_data['rooms'])) {
        foreach ($order_data['rooms'] as $roomIndex => $roomData) {
            $room_id = clear_input($roomData['room_id'] ?? ($roomIndex + 1));
            $floor_name = clear_input($roomData['floor_name'] ?? '');
            $room_name = clear_input($roomData['room_name'] ?? '');

            // Process room image
            $room_image_name = '';
            if (isset($_FILES["room_image_{$room_id}"]) && !empty($_FILES["room_image_{$room_id}"]['name'])) {
                $room_image_name = uploadRoomImage($_FILES["room_image_{$room_id}"], $room_id);
            } else {
                // Try to preserve existing room image
                $existing_image = findExistingRoomImage($order_id, $room_id);
                $room_image_name = $existing_image;
            }

            $room_info = [
                'floor_name' => $floor_name,
                'room_name' => $room_name,
                'room_id' => $room_id,
                'room_image' => $room_image_name
            ];

            // Process products in this room
            if (isset($roomData['products']) && is_array($roomData['products'])) {
                foreach ($roomData['products'] as $productIndex => $productData) {
                    $detail_counter++;
                    $product_detail = processProductDataNewLayout($productData, $room_info, $detail_counter);
                    if ($product_detail) {
                        $order_details[] = $product_detail;
                        $order_total_price += $product_detail['total_price'];
                    }
                }
            }
        }
    }

    // Prepare order data for update
    $get_currencies = getCurrencies(['TRY']);
    $order_usd_price = formatExcelPrice($get_currencies['TRY']['rate'], 4, '.', '');
    $update_date = date('Y-m-d H:i:s');

    $order_main_data = [
        'order_date' => $order_date,
        'customer_id' => $customer_id,
        'branch_id' => $get_agr->branch_id,
        'address_id' => $customer_address_id,
        'country_id' => $customer_address->adr_country,
        'address_text' => $customer_full_address_text,
        'order_arcs' => $order_arcs,
        'order_price' => $order_total_price,
        'order_usd_price' => $order_usd_price,
        'order_customer_comm' => $customer->customer_comm_rate ?? 0,
        'agreement_id' => $order_agreement,
        'agreement_text' => $order_agreement_text,
        'order_notes' => $order_notes,
        'order_tax' => $order_tax,
        'order_export_registered' => $order_export_registered,
        'order_update_date' => $update_date,
        'order_status' => $order_status,
        'dlv_date_modified' => $dlv_date_modified
    ];

    // Handle commission fields
    if ($order_comm_amount != '') {
        $order_main_data['order_comm_amount'] = $order_comm_amount;
    } elseif ($order_comm_rate != '') {
        $order_main_data['order_comm_rate'] = $order_comm_rate;
    }

    // Update order
    $update = $o->updateOrder($order_main_data, $order_id);

    if ($update) {
        // Add order edit log
        $add_order_edit_log = $o->addOrderLog($order_id, $logged->user_id, $update_date);

        // Handle commission field cleanup
        if ($order_comm_amount != '') {
            $o->updateOrderColAsNull('order_comm_rate', $order_id);
        } elseif ($order_comm_rate != '') {
            $o->updateOrderColAsNull('order_comm_amount', $order_id);
        }

        // Update delivery date
        $update_order_delivery_date = $o->updateOrderDeliveryDate($order_id, $order_delivery_date);

        // Remove existing details and add new ones
        $o->deleteAllOrderDetails($order_id);

        // Save new order details
        foreach ($order_details as $detail_index => $detail) {
            $detail_id = saveOrderDetailNewLayout($o, $order_id, $detail, $detail_index + 1);
            if ($detail_id) {
                $add_plan_data = $plan->addPlan($order_id, $detail_id);
            }
        }

        echo json_response('success', get_lang_text('ajax_orderedit_success'));
        die;
    } else {
        echo json_response('error', get_lang_text('ajax_orderedit_error'));
        die;
    }
}

function saveOrderMaterialsNewLayout($o, $orderId, $detailId, $materials)
{
    // Process main materials
    if (isset($materials['main']) && is_array($materials['main'])) {
        foreach ($materials['main'] as $set_level => $categories) {
            foreach ($categories as $category_key => $material_groups) {
                // Extract category from the key (e.g., "materialContent-6737-6737-room1-fabric" -> "fabric")
                $category_parts = explode('-', $category_key);
                $category = end($category_parts);
                
                // Handle regular material arrays (like fabric)
                if ($category !== 'pillow' && is_array($material_groups)) {
                    foreach ($material_groups as $material_info) {
                        if (is_array($material_info) && count($material_info) >= 2) {
                            list($ref_label, $material_id, $replacement) = array_pad($material_info, 3, '');

                            if (!empty($material_id)) {
                                $material_data = [
                                    'order_id' => $orderId,
                                    'detail_id' => $detailId,
                                    'material_id' => $material_id,
                                    'set_id' => $set_level,
                                    'ref_label' => $ref_label,
                                    'replacement' => $replacement,
                                    'mt_type' => $category,
                                    'image_type' => $category
                                ];

                                $o->addOrderDetailImageWithLabel($material_data);
                            }
                        }
                    }
                }
                // Handle pillow materials
                elseif ($category === 'pillow' && is_array($material_groups)) {
                    foreach ($material_groups as $pillow_info) {
                        if (is_array($pillow_info) && count($pillow_info) >= 2) {
                            $pillow_number = $pillow_info[0];
                            $pillow_data = $pillow_info[1];

                            if (is_array($pillow_data)) {
                                // Handle pillow with multiple sides
                                foreach ($pillow_data as $pillow_side => $side_material_id) {
                                    if (!empty($side_material_id)) {
                                        $material_data = [
                                            'order_id' => $orderId,
                                            'detail_id' => $detailId,
                                            'material_id' => $side_material_id,
                                            'set_id' => $set_level,
                                            'ref_label' => (string)$pillow_number,
                                            'replacement' => '',
                                            'image_type' => $category,
                                            'mt_type' => $category,
                                            'pillow_mt' => $pillow_side
                                        ];

                                        $o->addOrderDetailImageWithLabel($material_data);
                                    }
                                }
                            } else {
                                // Handle single pillow material (legacy format)
                                $material_data = [
                                    'order_id' => $orderId,
                                    'detail_id' => $detailId,
                                    'material_id' => $pillow_data,
                                    'set_id' => $set_level,
                                    'ref_label' => (string)$pillow_number,
                                    'replacement' => '',
                                    'image_type' => $category,
                                    'mt_type' => $category,
                                    'pillow_mt' => 'default'
                                ];

                                if (!empty($material_data['material_id'])) {
                                    $o->addOrderDetailImageWithLabel($material_data);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Process optional materials if needed
    if (isset($materials['optional']) && is_array($materials['optional'])) {
        foreach ($materials['optional'] as $set_level => $categories) {
            foreach ($categories as $category_key => $material_groups) {
                // Extract category from the key
                $category_parts = explode('-', $category_key);
                $category = end($category_parts);
                
                foreach ($material_groups as $material_info) {
                    if (is_array($material_info) && count($material_info) >= 2) {
                        list($ref_label, $material_id, $replacement) = array_pad($material_info, 3, '');

                        if (!empty($material_id)) {
                            $material_data = [
                                'order_id' => $orderId,
                                'detail_id' => $detailId,
                                'material_id' => $material_id,
                                'set_id' => $set_level,
                                'ref_label' => $ref_label,
                                'replacement' => $replacement,
                                'mt_type' => $category,
                                'image_type' => $category
                            ];

                            $o->addOrderDetailImageWithLabel($material_data);
                        }
                    }
                }
            }
        }
    }
}

function calculateTotalMaterialsCostNewLayout($m, $materials)
{
    $total_cost = 0;

    if (isset($materials['main']) && is_array($materials['main'])) {
        foreach ($materials['main'] as $set_level => $categories) {
            foreach ($categories as $category_key => $material_groups) {
                // Extract category from the key (e.g., "materialContent-6737-6737-room1-fabric" -> "fabric")
                $category_parts = explode('-', $category_key);
                $category = end($category_parts);
                
                // Handle regular materials (fabric, etc.)
                if ($category !== 'pillow' && is_array($material_groups)) {
                    foreach ($material_groups as $material_info) {
                        if (is_array($material_info) && count($material_info) >= 2) {
                            $material_id = $material_info[1];
                            if (!empty($material_id)) {
                                $material = $m->getMaterial($material_id);
                                if ($material) {
                                    $material_cost = calculateSingleMaterialCostNewLayout($material, $category, $material_info);
                                    $total_cost += $material_cost;
                                }
                            }
                        }
                    }
                }
                // Handle pillow materials
                elseif ($category === 'pillow' && is_array($material_groups)) {
                    foreach ($material_groups as $pillow_info) {
                        if (is_array($pillow_info) && count($pillow_info) >= 2) {
                            $pillow_number = $pillow_info[0];
                            $pillow_data = $pillow_info[1];

                            if (is_array($pillow_data)) {
                                // Pillow with multiple sides
                                foreach ($pillow_data as $pillow_side => $side_material_id) {
                                    if (!empty($side_material_id)) {
                                        $material = $m->getMaterial($side_material_id);
                                        if ($material) {
                                            $material_cost = calculateSingleMaterialCostNewLayout($material, $category, [$pillow_number, $side_material_id]);
                                            $total_cost += $material_cost;
                                        }
                                    }
                                }
                            } else {
                                // Single pillow material
                                if (!empty($pillow_data)) {
                                    $material = $m->getMaterial($pillow_data);
                                    if ($material) {
                                        $material_cost = calculateSingleMaterialCostNewLayout($material, $category, [$pillow_number, $pillow_data]);
                                        $total_cost += $material_cost;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return ['total_cost' => $total_cost];
}

function findExistingRoomImage($order_id, $room_no)
{
    $o = new Order();
    $details = $o->getOrderDetails($order_id);

    foreach ($details as $detail) {
        $room_data = json_decode($detail->product_room_data, true);
        if ($room_data && isset($room_data['room_no']) && $room_data['room_no'] == $room_no) {
            return $detail->product_room_img;
        }
    }
    return '';
}

// Helper function to find existing room detail
function findExistingRoomDetail($order_id, $room_no)
{
    $o = new Order();
    $details = $o->getOrderDetails($order_id);

    foreach ($details as $detail) {
        $room_data = json_decode($detail->product_room_data, true);
        if ($room_data && isset($room_data['room_no']) && $room_data['room_no'] == $room_no) {
            return $detail;
        }
    }
    return null;
}
