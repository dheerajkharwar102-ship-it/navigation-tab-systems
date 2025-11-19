<?php

/** Actions that can be performed after login */
define('inc_ajax_module_file', true);

/** User Operations */
$user_actions = [
   'add_user',
   'edit_user',
   'get_branch_item',
   'get_auth_item',
   'user_status_update'
];
if (array_intersect(array_keys($_POST), $user_actions) || ($_POST['delete_key'] == "delete_user")) {
   include PATH . '/ajax/modules/user.php';
   die;
}
/** /User Operations */

/** Customer Operations */
$customer_actions = [
   'add_customer',
   'edit_customer',
   'add_customer_address',
   'edit_customer_address',
   'get_customer_chat',
   'send_message',
   'reviews_status',
   'checkCustomerNameExisted',
   'checkCustomerNameDropdown',
   'save_customer_email',
   'set_read_book_appointment_status',
   'load_customers_options'
];
if (array_intersect(array_keys($_POST), $customer_actions) || in_array($_POST['delete_key'], ["delete_customer", "delete_customer_address", "delete_appointment"])) {
   include PATH . '/ajax/modules/customer.php';
   die;
}
/** /Customer Operations */

/** Agreement Operations */
$agr_actions = [
   'add_agreement',
   'edit_agreement',
   'load_agreement_text',
   'load_extra_agreement_texts',
   'load_agreements_for_branch'
];
if (array_intersect(array_keys($_POST), $agr_actions) || in_array($_POST['delete_key'], ["delete_agreement"])) {
   include PATH . '/ajax/modules/agreement.php';
   die;
}
/** /Agreement Operations */

/** Order Operations */
$order_actions = [
   'load_customer_addresses',
   'load_customer_address_detail',
   'translate_order_product_desc',
   'add_order',
   'edit_order',
   'upload_room_img',
   'add_order_with_new',
   'edit_order_with_new',
   'recover_order',
   'delete_order_image',
   'edit_logistics',
   'calculate_total_order_volume',
   'update_quality_control',
   'delete_quality_image',
   'add_order_file',
   'load_supplier_product_attrs',
   'load_product_images',
   'load_plan_product_images',
   'search_product_images',
   'search_related_product_images',
   'select_order_product',
   'get_product_bed_dim',
   'generate_material_options',
   'load_material_images',
   'load_materials_for_select',
   'search_material_images',
   'change_order_status',
   'update_order_item_in_stock',
   'order_product_create_delivery_date',
   'prepare_3d',
   'product_note_details',
   'add_prepare_3d_details',
   'upload_3d_product_file',
   'download_upload_3d_file',
   'delete_upload_drawing_file',
   'order_detail_stock',
   'get_order_detail_for_combo',
   'order_item_add_as_combination',
   'dfl_material_generate_for_comb',
   'new_system_dfl_material_generate_for_comb',
   'save_quality_control_edit_image',
   'order_change_room_img',
   'save_combination_edit_image',
   'newwwwwsystemmmm',
   'delete_created_combination',
   'assign_product_img_to_graphic_user',
   'graphic_complete_edit_img',
   'order_item_add_as_combination_new_changes',
   'logistic_add_dates',
   'get_curtain_accessory',
   'get_curtain_accessory_by_all',
   'get_base_category_catalog_id_order',
   'get_product_web_menu_order',
   'load_product_catalog_qualifications_base_category_order',
   'order_detail_stock_new',
   'fetch_order_id_items',
   'load_product_imagesold',
   'get_shipment_notes_data',
   'get_logistics_order_details',
   'edit_logistics_shipment',
   'get_logestic_data',
   'edit_logistic_product_data'
];

if (array_intersect(array_keys($_POST), $order_actions) || in_array($_POST['delete_key'], ["delete_order", "delete_order_file"])) {
   include PATH . '/ajax/modules/order.php';
   die;
}
/** /Order Operations */

/** /Order new layout operations */
$order_new_layout_actions = [
   'get_main_categories',
   'get_brands',
   'get_qualifications',
   'get_product_styles',
   'get_products',
   'get_product',
   'get_product_variants',
   'get_curtain_accessories',
   'upload_room_image',
   'add_order_with_newlayout',
   'get_order_for_edit',
   'update_order_with_newlayout'
];
if (array_intersect(array_keys($_POST), $order_new_layout_actions) || in_array($_POST['delete_key'], ["delete_order_new_layout", "delete_order_newlayout_file"])) {
   include PATH . '/ajax/modules/order-newlayout.php';
   die;
}
/** /Order new layout Operations */

/** /Order Design Operations */
$order_design_actions = [
   'add_order_design',
   'edit_order_design',
   'load_design_product_images',
   'delete_order_detail_design',
   'select_order_design_product'
];
if (array_intersect(array_keys($_POST), $order_design_actions) || in_array($_POST['delete_key'], ["delete_order_design", "delete_order_design_file"])) {
   include PATH . '/ajax/modules/order-design.php';
   die;
}
/** /Order Setting Operations */

/** /Order Design Operations */
$order_setting_actions = [
   'get_item',
   'get_items',
   'add_order_setting_item',
   'edit_order_setting_item',
   'save_multiplier'
];
if (array_intersect(array_keys($_POST), $order_setting_actions) || in_array($_POST['delete_key'], ["delete_order_setting", "delete_multiplier"])) {
   include PATH . '/ajax/modules/order-setting.php';
   die;
}
/** /Order Setting Operations */

/** Payment Operations */
$payment_actions = [
   'add_payment',
   'edit_payment',
   'expenses_add',
   'expenses_edit',
   'get_expense_info',
   'update_payment_status',
   'add_payment_details',
   'update_payment_details',
   'fetch_payment_history',
   'fetch_order_invoice',
   'pay_all_invoices_together',
   'factorycreditadd',
   'settle_invoice_with_credit',
   'get_credit_data',
   'get_credit_sender_data',
   'get_comession_and_amount_data',
   'pay_comession_payment_page',
   'fetch_payment_history_all_invoice_type'
];
if (array_intersect(array_keys($_POST), $payment_actions) || in_array($_POST['delete_key'], ["delete_payment", "delete_expens"])) {
   include PATH . '/ajax/modules/payment.php';
   die;
}
/** /Payment Operations */

/** Qualification Operations */
$qualification_actions = [
   'add_qualification',
   'edit_qualification',
   'get_web_menu',
   'edit_web_menu',
   'update_attr_products_price',
   'load_catalog_qualifications',
   'restore_qualification-category',
   'get_web_menu_curtain'
];
if (array_intersect(array_keys($_POST), $qualification_actions) || in_array($_POST['delete_key'], ["delete_qualification-category", "delete_qualification"])) {
   include PATH . '/ajax/modules/qualification.php';
}
/** /Qualification Operations */

/** Settings */
include PATH . '/ajax/modules/settings.php';
/** /Settings */

/** Profile */
include PATH . '/ajax/modules/profile.php';
/** /Profile */

/** Product Operations */
$prd_actions = [
   'get_selected_product_parrent_groups',
   'product_set_group',
   'assign_product',
   'update_product_dimension',
   'download_catalog_file',
   'upload_catalogue_file',
   'add_product',
   'add_curtain_product',
   'edit_curtain_product',
   'edit_product',
   'edit_product_prdmanager',
   'delete_product_detail_image',
   'delete_multi_image',
   'delete_prd_combo_image',
   'delete_product',
   'delete_product_combo_item',
   'restore_product',
   'update_product_stock',
   'load_product_catalog_qualifications',
   'load_pdf_catalog_attrs',
   'get_materialColor',
   'get_product_combination',
   'get_selected_product_combination',
   'add_material_combination',
   'edit_material_combination',
   'add_new_material_combination',
   'add_modify_material_combination',
   'add_already_material_combination',
   'get_material_combination_name',
   'get_selected_material_combination',
   'add_product_catalogue',
   'add_product_discount',
   'get_product_combinations',
   'edit_product_discount',
   'get_category_products',
   'get_material_produced_in',
   'get_combinations_product',
   'get_product_combination_stock',
   'get_product_child_sets',
   'delete_product_combination',
   'set_raw_material_in_product',
   'get_fabric',
   'upload_product_file',
   'product_modification_data',
   'download_product_file_item',
   'set_revise_product_data',
   'get_revise_view_data',
   'delete_revise_data',
   'load_product_catalog_qualifications_base_category',
   'get_product_web_menu',
   'get_base_category_catalog_id',
   'generate_meta_with_ai',
   'fetch_product_base_on_orderid',
   'save_manufacturing_price',
   'fetch_product_base_on_orderid_invoice',
   'upload_product_file_in_db',
   'delete_contract_file',
   'loading_category_options',
   'loading_base_category_options',
   'loading_brand_options',
   'update_products_price_by_group',
   'load_fitout_qualifications'
];
if (array_intersect(array_keys($_POST), $prd_actions) || in_array($_POST['delete_key'], ["delete_product_discount", "delete_parent_groups"]) || in_array($_POST['delete_product_file_key'], ["delete_this_product_file_item"])) {
   include PATH . '/ajax/modules/product.php';
   die;
}




/** /Product Operations */

/** Catalog Operations */
include PATH . '/ajax/modules/catalog.php';
/** /Catalog Operations */

/** Material Operations */
include PATH . '/ajax/modules/material.php';
/** /Material Operations */

/** Accessory Operations */
include PATH . '/ajax/modules/accessory.php';
/** /Accessory Operations */


/** ContactUs Operations */
include PATH . '/ajax/modules/contact_us.php';
/** /ContactUs Operations */

// echo '<pre>',var_dump($_POST),'</pre>';

/** Planning Operations */
include PATH . '/ajax/modules/planning.php';
/** /Planning Operations */

/** Branch Operations */
include PATH . '/ajax/modules/branch.php';
/** /Branch Operations */

/** Packaging Operations */
include PATH . '/ajax/modules/packaging.php';
/** /Packaging Operations */

/** Service Operations */
include PATH . '/ajax/modules/service.php';
/** /Service Operations */

/** Customer Satisfaction Operations */
include PATH . '/ajax/modules/satisfaction.php';
/** /Customer Satisfaction Operations */

/** White Collar Bonus Operations */
include PATH . '/ajax/modules/whitecollar.php';
/** /White Collar Bonus Operations */

/** Manufacturing Operations */
include PATH . '/ajax/modules/manufacturing.php';
/** /Manufacturing Operations */

/** Activity Operations */
include PATH . '/ajax/modules/activity.php';
/** /Activity Operations */

/** Scanned Product Operations */
include PATH . '/ajax/modules/scanned_product.php';
/** /Scanned Product Operations */

/** Task Operations */
include PATH . '/ajax/modules/task.php';
/** /Task Operations */

/** Shipment Operations */
include PATH . '/ajax/modules/shipment.php';
/** /Shipment Operations */

/** Partner Operations */
include PATH . '/ajax/modules/partner.php';
/** /Partner Operations */

/** Seller Operations */
include PATH . '/ajax/modules/seller.php';
/** /Seller Operations */

/** Position Operations */
include PATH . '/ajax/modules/position.php';
/** /Position Operations */

/** Invoice Operations */
include PATH . '/ajax/modules/invoice.php';
/** /Invoice Operations */

/** Human Resource Operations */
include PATH . '/ajax/modules/human_resource.php';
/** /Human Resource Operations */

/** Employee Task Operations */
include PATH . '/ajax/modules/employee-task.php';
/** /Employee Task Operations */

/** Raw Material Operations */
include PATH . '/ajax/modules/raw-material.php';
/** /Raw Material Operations */

/** Questionnaire Operations */
include PATH . '/ajax/modules/questionnaire.php';
/** /Questionnaire Operations */

/** Campaign Operations */
include PATH . '/ajax/modules/campaign.php';
/** /Campaign Operations */

/** Proforma Invoice Operations */
include PATH . '/ajax/modules/proforma-invoice.php';
/** /Proforma Invoice Operations */

/** Test Operations */
include PATH . '/ajax/modules/test.php';
/** /Test Operations */

/** App Operations */
include PATH . '/ajax/modules/app.php';
/** /App Operations */

/** Discount Operations */
include PATH . '/ajax/modules/discount.php';
/** /Discount Operations */

/** Kitchen Operations */
include PATH . '/ajax/modules/kitchen.php';
/** /Kitchen Operations */

/** Assets Operations */
include PATH . '/ajax/modules/assets.php';
/** /Assets Operations */
