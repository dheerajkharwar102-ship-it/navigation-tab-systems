<?php

if (!defined('inc_ajax_module_file')) {
   die;
}

$logged_id = crypt_data($_SESSION['hmdcr_logged'], 'd');

if (isset($_POST['add_qualification'])) {

   $user = new User();
   $pa = new ProductAttribute();
   $ctl = new Catalog();

   $logged = $user->getLogged('user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth) && $logged_id != '18') {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $base_category = p('base_category');
   $category = p('category');
   $catalog_id = html_ent(p('catalog_id'));
   $attr_name = html_ent(p('attr_name'));
   $attr_desc = html_ent(p('attr_desc'));
   $attr_customs_code = html_ent(p('attr_customs_code'));
   $calculate_type = clear_input(p('calculate_type'));
   $attr_type = clear_input(p('attr_type'));
   $attr_stock_status = clear_input(p('attr_stock_status'));
   $attr_online_status = clear_input(p('attr_stock_online_status'));
   $wholesale_percentage = clear_input(p('wholesale_percentage'));
   $valid_types = ['plus', 'minus'];
   $valid_stock_types = ['yes', 'no'];
   $valid_attr_types = ['other', 'curtain', 'bed', 'carpet', 'decoration'];
   $product_img = $_FILES['product_img'];
   $icon = $_FILES['icon'];

   $valid_calc_types = ['standart', 'boy', 'en', 'yuksek', 'enboy', 'yukseken', 'yuksekboy', 'hepsi'];

   $select_category_id = $pa->checkWebMenu($category);
   if ($select_category_id === false) {
      $data = [
         'category' => $category,
         'base_category' => $base_category,
      ];
      $select_category_id = $pa->addWebMenu($data);
   }



   if ($catalog_id == '' || $attr_name == '' || $attr_customs_code == '' || $calculate_type == '' || $attr_type == '' || $attr_stock_status == '' || $product_img['name'] == '') {
      echo json_response('error', get_lang_text('ajax_fill_required_fields'));
      die;
   }

   if (!in_array($calculate_type, $valid_calc_types) || !in_array($attr_stock_status, $valid_stock_types) || !in_array($attr_type, $valid_attr_types)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }


   if ($pa->checkProductAttributeByName($attr_name, null, $catalog_id)) {
      echo json_response('error', get_lang_text('ajax_attradd_attr_exists'));
      die;
   }

   if (!$ctl->checkCatalogById($catalog_id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $rate_data = [];
   foreach ($_POST['name'] as $index => $val) {
      $name = clear_input($val);
      $type = clear_input($_POST['type'][$index]);
      $rate = clear_input($_POST['rate'][$index]);

      if ($name != '') {
         if (!in_array($type, $valid_types)) {
            echo json_response('error', get_lang_text('ajax_invalid_request'));
            die;
         }

         if ($rate == '') {
            echo json_response('error', get_lang_text('ajax_fill_required_fields'));
            die;
         }
         $rate_data[$index] = ['name' => $name, 'type' => $type, 'rate' => $rate];
      }
   }

   $rate_data_json = json_encode($rate_data, JSON_UNESCAPED_UNICODE);

   $allowed_ext = getAllowedImageTypes('ext');
   $allowed_mimes = getAllowedImageTypes('mime');

   $ext_explode = explode('.', $product_img['name']);
   $uzanti = strtolower(array_pop($ext_explode));
   $mime = mime_content_type($product_img['tmp_name']);

   if (!in_array($uzanti, $allowed_ext) || !in_array($mime, $allowed_mimes)) {
      echo json_response('error', get_lang_text('ajax_img_type_error'));
      die;
   }

   $ext_explode_icon = explode('.', $icon['name']);
   $uzanti_icon = strtolower(array_pop($ext_explode_icon));
   $mime_icon = mime_content_type($icon['tmp_name']);

   if (!in_array($uzanti_icon, $allowed_ext) || !in_array($mime_icon, $allowed_mimes)) {
      echo json_response('error', get_lang_text('ajax_img_type_error'));
      die;
   }

   $max_filesize_byte = MAX_IMG_SIZE * 1024 * 1024;
   if ($product_img['size'] > $max_filesize_byte || $icon['size'] > $max_filesize_byte) {
      echo json_response('error', get_lang_text('max_img_size', ['max_img_size' => MAX_IMG_SIZE]));
      die;
   }

   $now = date('YmdHis') . microtime() . rand(0, 999);
   $new_product_image_name = md5($product_img['name'] . $now) . sha1($product_img['name'] . $now) . rand(1, 999) . '.jpg';
   $new_icon_name = md5($icon['name'] . $now) . sha1($icon['name'] . $now) . rand(1, 999) . '.jpg';

   $add_date = date('Y-m-d H:i:s');
   $data = [
      'catalog_id' => $catalog_id,
      'attr_name' => $attr_name,
      'attr_desc' => $attr_desc,
      'online_category' => $select_category_id,
      'attr_customs_code' => $attr_customs_code,
      'calculate_type' => $calculate_type,
      'attr_type' => $attr_type,
      'attr_stock_status' => $attr_stock_status,
      'attr_online_status' => $attr_online_status,
      'online_product_img' => $new_product_image_name,
      'icon' => $new_icon_name,
      'attr_rates' => $rate_data_json,
      'attr_add_date' => $add_date,
      'wholesale_percentage' => $wholesale_percentage
   ];

   $insert = $pa->addProductAttribute($data);
   // print_r($data);
   if ($insert) {
      $upload_path = PATH . '/uploads/online-img/' . $new_product_image_name;
      $upload_path_icon = PATH . '/uploads/online-img/' . $new_icon_name;
      include PATH . '/helpers/SimpleImage.php';
      $image = new SimpleImage();
      $image->fromFile($product_img['tmp_name'])
         ->autoOrient()
         ->bestFit(800, 800)
         ->toFile($upload_path, 'image/jpeg');
      compressImage($upload_path);

      $image->fromFile($icon['tmp_name'])
         ->autoOrient()
         ->bestFit(800, 800)
         ->toFile($upload_path_icon, 'image/jpeg');
      compressImage($upload_path_icon);

      echo json_response('success', get_lang_text('ajax_attradd_success'));
      die;
   } else {
      echo json_response('error', get_lang_text('ajax_attradd_error'));
      die;
   }
}

if (isset($_POST['edit_qualification'])) {
   $user = new User();
   $pa = new ProductAttribute();
   $ctl = new Catalog();
   $logged = $user->getLogged('user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth) && $logged_id != '18') {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $base_category = p('base_category');
   $category = p('category');
   $attr_id = clear_input(p('attr_id'));
   $catalog_id = clear_input(p('catalog_id'));
   $attr_name = html_ent(p('attr_name'));
   $attr_desc = html_ent(p('attr_desc'));
   $attr_customs_code = html_ent(p('attr_customs_code'));
   $calculate_type = clear_input(p('calculate_type'));
   $attr_type = clear_input(p('attr_type'));
   $attr_stock_status = clear_input(p('attr_stock_status'));
   $attr_status = clear_input(p('attr_status'));
   $attr_online_status = clear_input(p('attr_stock_online_status'));
   $wholesale_percentage = clear_input(p('wholesale_percentage'));
   $valid_status = [1, 2, '1', '2'];
   $valid_calc_types = ['standart', 'boy', 'en', 'yuksek', 'enboy', 'yukseken', 'yuksekboy', 'hepsi'];
   $valid_types = ['plus', 'minus'];
   $valid_stock_types = ['yes', 'no'];
   $valid_attr_types = ['other', 'curtain', 'bed', 'carpet', 'decoration', 'fitout'];
   $product_img = $_FILES['product_img'];
   $icon = $_FILES['icon'];


   $select_category_id = $pa->checkWebMenu($category);
   if ($select_category_id === false) {
      $data = [
         'category' => $category,
         'base_category' => $base_category,
      ];
      $select_category_id = $pa->addWebMenu($data);
   }

   if ($catalog_id == '' || $attr_name == '' || $attr_customs_code == '' || $calculate_type == '' || $attr_type == '' || $attr_stock_status == '') {
      echo json_response('error', get_lang_text('ajax_fill_required_fields'));
      die;
   }

   if (!in_array($calculate_type, $valid_calc_types) || !in_array($attr_stock_status, $valid_stock_types) || !in_array($attr_type, $valid_attr_types)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   if ($logged_auth == 'admin' || $logged_id == '18') {
      if ($attr_status == '') {
         echo json_response('error', get_lang_text('ajax_fill_required_fields'));
         die;
      }

      if (!in_array($attr_status, $valid_status)) {
         echo json_response('error', get_lang_text('ajax_invalid_request'));
         die;
      }
   }

   if (!$ctl->checkCatalogById($catalog_id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   if ($pa->checkProductAttributeByName($attr_name, $attr_id, $catalog_id)) {
      echo json_response('error', get_lang_text('ajax_attradd_attr_exists'));
      die;
   }

   if (!$pa->checkProductAttributeById($attr_id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $rate_data = [];
   foreach ($_POST['name'] as $index => $val) {
      $name = clear_input($val);
      $type = clear_input($_POST['type'][$index]);
      $rate = clear_input($_POST['rate'][$index]);

      if ($name != '') {
         if (!in_array($type, $valid_types)) {
            echo json_response('error', get_lang_text('ajax_invalid_request'));
            die;
         }
         if ($rate == '') {
            echo json_response('error', get_lang_text('ajax_fill_required_fields'));
            die;
         }
         $rate_data[$index] = ['name' => $name, 'type' => $type, 'rate' => $rate];
      }
   }

   $rate_data_json = json_encode($rate_data, JSON_UNESCAPED_UNICODE);

   $item = $pa->getProductAttribute($attr_id, 'online_product_img, icon');

   // online category image upload
   if ($product_img['name'] != '') {
      $allowed_ext = getAllowedImageTypes('ext');
      $allowed_mimes = getAllowedImageTypes('mime');

      $ext_explode = explode('.', $product_img['name']);
      $uzanti = strtolower(array_pop($ext_explode));
      $mime = mime_content_type($product_img['tmp_name']);

      if (!in_array($uzanti, $allowed_ext) || !in_array($mime, $allowed_mimes)) {
         echo json_response('error', get_lang_text('ajax_img_type_error'));
         die;
      }

      $max_filesize_byte = MAX_IMG_SIZE * 1024 * 1024;

      if ($product_img['size'] > $max_filesize_byte) {
         echo json_response('error', get_lang_text('max_img_size', ['max_img_size' => MAX_IMG_SIZE]));
         die;
      }

      $now = date('YmdHis') . microtime() . rand(0, 999);
      $extension = strtolower(pathinfo($product_img['name'], PATHINFO_EXTENSION));
      $new_product_image_name = md5($product_img['name'] . $now) . sha1($product_img['name'] . $now) . rand(1, 999) . '.' . $extension;
   } else {
      $new_product_image_name = $item->online_product_img;
   }

   // icon image upload
   if ($icon['name'] != '') {
      $allowed_ext = getAllowedImageTypes('ext');
      $allowed_mimes = getAllowedImageTypes('mime');

      $ext_explode = explode('.', $icon['name']);
      $uzanti = strtolower(array_pop($ext_explode));
      $mime = mime_content_type($icon['tmp_name']);

      if (!in_array($uzanti, $allowed_ext) || !in_array($mime, $allowed_mimes)) {
         echo json_response('error', get_lang_text('ajax_img_type_error'));
         die;
      }

      $max_filesize_byte = MAX_IMG_SIZE * 1024 * 1024;

      if ($icon['size'] > $max_filesize_byte) {
         echo json_response('error', get_lang_text('max_img_size', ['max_img_size' => MAX_IMG_SIZE]));
         die;
      }

      $now = date('YmdHis') . microtime() . rand(0, 999);
      $extension = strtolower(pathinfo($icon['name'], PATHINFO_EXTENSION));
      $new_icon_name = md5($icon['name'] . $now) . sha1($icon['name'] . $now) . rand(1, 999) . '.' . $extension;
   } else {
      $new_icon_name = $item->icon;
   }

   $update_date = date('Y-m-d H:i:s');
   $data = [
      'catalog_id' => $catalog_id,
      'attr_name' => $attr_name,
      'attr_desc' => $attr_desc,
      'online_category' => $select_category_id,
      'attr_customs_code' => $attr_customs_code,
      'calculate_type' => $calculate_type,
      'attr_type' => $attr_type,
      'attr_stock_status' => $attr_stock_status,
      'attr_online_status' => $attr_online_status,
      'online_product_img' => $new_product_image_name,
      'icon' => $new_icon_name,
      'attr_rates' => $rate_data_json,
      'attr_update_date' => $update_date,
      'wholesale_percentage' => $wholesale_percentage
   ];


   if ($logged_auth == 'admin' || $logged_id == '18') {
      $data['attr_status'] = $attr_status;
   }

   $update = $pa->updateProductAttribute($data, $attr_id);
   if ($update) {
      if ($product_img['name'] != '') {
         @unlink(PATH . '/uploads/online-img/' . $item->online_product_img);
         $upload_path = PATH . '/uploads/online-img/' . $new_product_image_name;
         include PATH . '/helpers/SimpleImage.php';
         $image = new SimpleImage();
         $image
            ->fromFile($product_img['tmp_name'])
            ->autoOrient()
            ->bestFit(800, 800)
            ->toFile($upload_path, 'image/webp');
         compressImage($upload_path);
      }
      if ($icon['name'] != '') {
         @unlink(PATH . '/uploads/online-img/' . $item->icon);
         $upload_path = PATH . '/uploads/online-img/' . $new_icon_name;
         include PATH . '/helpers/SimpleImage.php';
         $image = new SimpleImage();
         $image
            ->fromFile($icon['tmp_name'])
            ->autoOrient()
            ->bestFit(800, 800)
            ->toFile($upload_path, 'image/webp');
         compressImage($upload_path);
      }
      echo json_response('success', get_lang_text('ajax_attredit_success'));
      die;
   } else {
      echo json_response('error', get_lang_text('ajax_attredit_error'));
      die;
   }
}

if (isset($_POST['get_web_menu'])) {
   $pa = new ProductAttribute();
   $base_category = isset($_POST['base_category']) ? $_POST['base_category'] : '';


   $where = [['base_category', '=', $base_category]];

   $get_menu = $pa->getWebMenus('*', null, $where);
   if (count($get_menu) > 0) {
      $option = '';
      foreach ($get_menu as $menu) {
         $option .= '<option value="' . $menu->category . '">' . $menu->category . '</option>';
      }
      echo json_response('success', 'okay', $option);
      die;
   } else {
      $option = '<option value=" ">No record found</option>';
      echo json_response('success', 'some-error', $option);
      die;
   }
}

if (isset($_POST['get_web_menu_curtain'])) {
   $pa = new ProductAttribute();
   $base_category = isset($_POST['base_category']) ? $_POST['base_category'] : '';

   $where = [['base_category', '=', $base_category]];

   $get_menu = $pa->getWebMenus('*', null, $where);
   if (count($get_menu) > 0) {
      $option = '';
      foreach ($get_menu as $menu) {
         if ($menu->category == 'Curtains') {
            $option .= '<option value="' . $menu->category . '">' . $menu->category . '</option>';
         }
      }
      echo json_response('success', 'okay', $option);
      die;
   } else {
      $option = '<option value=" ">No record found</option>';
      echo json_response('success', 'some-error', $option);
      die;
   }
}

if (isset($_POST['edit_web_menu'])) {
   $user = new User();
   $pa = new ProductAttribute();
   $logged = $user->getLogged('user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth)) {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $id = clear_input(p('web_id'));
   $category = p('category');
   $base_category = p('base_category');

   $data = [
      'category' => $category,
      'base_category' => $base_category,
   ];

   // $select_category_id = $pa->checkWebMenu($category);
   // if ($select_category_id === false) {
   $update = $pa->updateWebMenu($data, $id);
   if ($update) {
      echo json_response('success', 'Qualification category updated successfully');
      die;
   } else {
      echo json_response('error', 'Qualification category could not be updated.');
      die;
   }
   // } else {
   //    echo json_response('error', 'Category name cannot be changed because it already exists.');
   //    die;
   // }
}

if (isset($_POST['delete_item']) && @$_POST['delete_key'] == 'delete_qualification-category') {
   $user = new User();
   $logged = $user->getLogged('user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth)) {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $id = intval(clear_input(p('item_id')));

   if ($id == '' || !is_numeric($id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $pa = new ProductAttribute();
   $delete = $pa->deleteWebMenu($id);
   if ($delete) {
      echo json_response('success', 'Qualification category deleted successfully.');
      die;
   } else {
      echo json_response('error', 'Qualification category could not be deleted.');
      die;
   }
}


if (isset($_POST['restore_this_item']) && @$_POST['action'] == 'restore_qualification-category') {
   $user = new User();
   $pa = new ProductAttribute();
   $logged = $user->getLogged('user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth)) {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $id = intval(clear_input(p('id')));

   if ($id == '' || !is_numeric($id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }


   $data = [
      'deleted' => 0,
   ];

   $update = $pa->updateWebMenu($data, $id);
   if ($update) {
      echo json_response('success', 'Qualification category updated successfully');
      die;
   } else {
      echo json_response('error', 'Qualification category could not be updated.');
      die;
   }
}

if (isset($_POST['delete_item']) && @$_POST['delete_key'] == 'delete_qualification') {
   //    ini_set('display_errors', 1);
   // ini_set('display_startup_errors', 1);
   // error_reporting(E_ALL);
   $user = new User();
   $logged = $user->getLogged('user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth) && $logged_id != '18') {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $id = intval(clear_input(p('item_id')));

   if ($id == '' || !is_numeric($id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $pa = new ProductAttribute();

   if (!$pa->checkProductAttributeById($id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $delete = $pa->deleteProductAttribute($id);
   if ($delete) {
      echo json_response('success', get_lang_text('ajax_attrdel_success'));
      die;
   } else {
      echo json_response('error', get_lang_text('ajax_attrdel_error'));
      die;
   }
}

if (isset($_POST['update_attr_products_price'])) {
   $user = new User();
   $logged = $user->getLogged('user_id,user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth)) {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $attr_id = clear_input(p('attr_id'));
   $price_rate = clear_input(str_replace(',', '', p('price_rate')));
   $rate_type = clear_input(p('rate_type'));
   $valid_rate_types = ['plus', 'minus'];

   if ($attr_id == '' || $price_rate == '' || $rate_type == '') {
      echo json_response('error', get_lang_text('ajax_fill_required_fields'));
      die;
   }

   if ($price_rate <= 0) {
      echo json_response('error', get_lang_text('ajax_fill_required_fields'));
      die;
   }

   if (!in_array($rate_type, $valid_rate_types)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $p = new Product();
   $p_attr = new ProductAttribute();
   $ctl = new Catalog();


   if (!$p_attr->checkProductAttributeById($attr_id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $where = [['attr_id', '=', $attr_id]];

   $get_attr = $p_attr->getProductAttribute($attr_id, 'attr_type');

   $old_attr_products = [];
   $update_product_data = [];
   $tmp_dims_data = [];
   $products = $p->getProducts('product_id,standart_price,product_dims_data,product_set_of', null, $where);
   foreach ($products as $product) {
      $old_attr_products[] = ['product_id' => $product->product_id, 'standart_price' => (string)formatExcelPrice($product->standart_price, 0, '', ''), 'dims_data' => json_decode($product->product_dims_data)];

      if ($get_attr->attr_type == 'bed') {
         $rated_price = formatExcelPrice((($product->standart_price / 100) * $price_rate), 0, '', '');
         if ($rate_type == 'minus') {
            $rated_price = $rated_price * -1;
         }
         $new_price = $product->standart_price + $rated_price;
      } else if ($get_attr->attr_type == 'curtain') {
         $new_price = 0;
      } else {
         $dims = json_decode($product->product_dims_data, true);
         for ($i = 0; $i < $product->product_set_of; $i++) {
            $dim_item = $dims[$i];
            $dim_price = formatExcelPrice($dim_item['standart_price'], 0, '', '');
            $rated_price = formatExcelPrice((($dim_price / 100) * $price_rate), 0, '', '');
            if ($rate_type == 'minus') {
               $rated_price = $rated_price * -1;
            }
            $new_price = $dim_price + $rated_price;

            $dim_item['standart_price'] = (string)$new_price;
            $tmp_dims_data[] = $dim_item;
         }
         $new_price = 0;
      }

      $update_product_data[] = ['product_id' => $product->product_id, 'standart_price' => (string)formatExcelPrice($new_price, 0, '', ''), 'product_dims_data' => $tmp_dims_data];
      $tmp_dims_data = [];
   }

   $insert_data = [
      'updated_user' => $logged->user_id,
      'price_rate' => $price_rate,
      'rate_type' => $rate_type,
      'update_date' => date('Y-m-d H:i:s'),
      'old_price_data' => json_encode($old_attr_products),
   ];

   $insert = $p_attr->addPriceUpdate($insert_data, $attr_id);
   if ($insert) {
      foreach ($update_product_data as $data) {
         $prd_data = [
            'standart_price' => $data['standart_price'],
            'product_dims_data' => json_encode($data['product_dims_data'])
         ];
         $update_product_price = $p->updateProduct($prd_data, $data['product_id']);
      }

      echo json_response('success', get_lang_text('ajax_attrprice_success'));
      die;
   } else {
      echo json_response('error', get_lang_text('ajax_attrprice_error'));
      die;
   }
}

if (isset($_POST['load_catalog_qualifications'])) {
   $user = new User();
   $logged = $user->getLogged('user_id,user_auth');
   $logged_auth = $logged->user_auth;
   $page_auth = ['admin'];
   if (!in_array($logged_auth, $page_auth)) {
      echo json_response('error', get_lang_text('ajax_unauthorized_access'));
      die;
   }

   $catalog_id = clear_input(p('catalog_id'));

   $ctl = new Catalog();
   $pa = new ProductAttribute();

   if (!$ctl->checkCatalogById($catalog_id)) {
      echo json_response('error', get_lang_text('ajax_invalid_request'));
      die;
   }

   $return_data = [];
   $return_data[] = ['attr_id' => '', 'attr_name' => get_lang_text('whitecollaradd_input_qualification_select')];
   $attrs = $pa->getProductAttributes('attr_id,attr_name', null, [['catalog_id', '=', $catalog_id], ['attr_status', '=', '1']]);
   if (count($attrs) > 0) {
      foreach ($attrs as $attr) {
         $return_data[] = ['attr_id' => $attr->attr_id, 'attr_name' => $attr->attr_name];
      }
   }

   echo json_response('success', 'OK', $return_data);
   die;
}
