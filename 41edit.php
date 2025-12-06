<?php
if (!defined('inc_admin_pages')) {
    die;
}

define('inc_panel_header', true);
include PATH . '/inc/header.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$user = new User();
$logged = $user->getLogged('user_id,user_auth,user_branch');
$logged_auth = $logged->user_auth;
$b = new Branch();
$get_branch = $b->getBranch($logged->user_branch);
$cate = explode(",", $get_branch->catalog_ids);

$page_auth = ['admin', 'manager', 'user', 'sales', 'ordermngr', 'partner', 'graphic_and_media', 'encounter'];
if (!in_array($logged_auth, $page_auth)) {
    header("Location:" . URL);
    die;
}

$customer = new Customer();
$pa = new ProductAttribute();
$p = new Product();
$o = new Order();

// Get order ID from URL
$order_id = isset($_GET['id']) ? clear_input($_GET['id']) : 0;
if (!$order_id) {
    header("Location:" . URL . "/index.php?page=orders");
    die;
}

// Get order data
$order = $o->getOrder($order_id);
if (!$order) {
    header("Location:" . URL . "/index.php?page=orders");
    die;
}

$set = array();
$get_settings = Settings::getAll();
if (count($get_settings) > 0) {
    foreach ($get_settings as $setting) {
        $set[$setting->set_name] = $setting->set_value;
    }
}

function getProductShippingData($order_id)
{
    $product_shipping_data = [];
    $product_cus_data = [];

    // Get order basic info
    $o = new Order();
    $item = $o->getOrder($order_id);

    if (!$item) {
        return [];
    }

    // Set customer data
    $product_cus_data = [
        "customer_id" => $item->customer_id,
        "customer_addr_id" => $item->address_id,
        "order_id" => $order_id,
    ];

    // Get order details
    $item_details = $o->getOrderDetails($order_id, '*', null, ['product_row_number', 'DESC']);

    foreach ($item_details as $detail) {
        // Only process main products (not shapes/accessories)
        if (is_null($detail->detail_of) || $detail->detail_of == 'main') {
            $product_cat = $detail->product_cat;

            if (isset($product_shipping_data[$product_cat])) {
                // Update the product_count if product_cat already exists
                $product_shipping_data[$product_cat]['product_count'] += $detail->product_qty;
            } else {
                // Add a new entry if the product_cat doesn't exist
                $product_shipping_data[$product_cat] = [
                    "product_brand" => $product_cat,
                    "product_count" => $detail->product_qty,
                    "product_attr" => $detail->attr_id,
                    "row_index" => $detail->product_room_index,
                ];
            }
        }
    }

    // Convert associative array to indexed array and add customer data
    $product_shipping_data = array_values($product_shipping_data);

    if (!empty($product_shipping_data)) {
        $product_shipping_data[0]['customer_data'] = $product_cus_data;
    }

    return $product_shipping_data;
}

$product_shipping_data = getProductShippingData($order_id);

$togle_temp_count = 0;
?>
<style>
    .room-wrapper {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .nav-tabs {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 8px 12px 0;
        border-bottom: none;
        display: flex;
        align-items: center;
        min-height: 40px;
    }

    .nav-tabs .nav-link {
        border: none;
        border-radius: 6px 6px 0 0;
        padding: 6px 12px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        position: relative;
        margin-right: 4px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
    }

    .nav-tabs .nav-link:hover {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        transform: translateY(-1px);
    }

    .nav-tabs .nav-link.active {
        background: white;
        color: #4361ee;
        font-weight: 600;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
    }

    .room-header {
        display: flex;
        align-items: center;
        gap: 6px;
        border-radius: 6px 6px 0 0;
        padding: 6px 12px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        transition: all 0.2s ease;
        position: relative;
    }

    .room-header .room-title {
        color: white;
        font-weight: 600;
    }

    .room-header .close-room {
        color: rgba(255, 255, 255, 0.8);
    }

    .room-header .close-room:hover {
        color: white;
        background: rgba(255, 255, 255, 0.2);
    }

    .close-room {
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.2s ease;
        padding: 4px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }

    .close-room:hover {
        color: white;
        background: rgba(255, 255, 255, 0.2);
    }

    .nav-tabs .nav-link.active .close-room {
        color: #6c757d;
    }

    .nav-tabs .nav-link.active .close-room:hover {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.1);
    }

    .tab-content {
        padding: 0;
    }

    .tab-pane {
        padding: 0;
    }

    .product-tabs-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 400px;
    }

    .product-tabs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        min-height: 36px;
    }

    .room-info-form {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }

    .form-group-small {
        margin: 0;
        min-width: 120px;
    }

    .form-group-small label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
        margin-bottom: 4px;
        display: block;
    }

    .form-control-small {
        height: 28px;
        font-size: 0.8rem;
        padding: 4px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        width: 100%;
    }

    .form-control-small:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    .image-upload-container {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .image-preview {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-preview i {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .file-input-wrapper input[type=file] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .product-tabs-container {
        display: flex;
        gap: 4px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        flex-wrap: wrap;
        min-height: 48px;
        align-items: flex-start;
    }

    .product-tab {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 100px;
        position: relative;
        font-size: 0.8rem;
    }

    .product-tab:hover {
        border-color: #4361ee;
        transform: translateY(-1px);
        box-shadow: 0 1px 4px rgba(67, 97, 238, 0.15);
    }

    .product-tab.active {
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
        box-shadow: 0 2px 6px rgba(67, 97, 238, 0.2);
    }

    .product-tab-icon {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        color: white;
    }

    .product-tab-name {
        font-size: 0.8rem;
        font-weight: 500;
        color: #495057;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-tab-close {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        transition: all 0.2s ease;
        font-size: 0.65rem;
    }

    .product-tab-close:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .product-content-area {
        flex: 1;
        padding: 0;
        background: white;
        overflow: hidden;
    }

    .product-content {
        height: 100%;
        display: none;
    }

    .product-content.active {
        display: block;
    }

    .add-item-to-room-btn {
        background: linear-gradient(135deg, #2a9d8f, #1a6b63);
        border: none;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 6px 12px;
        color: white;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .add-item-to-room-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
        color: white;
    }

    .compact-product-details {
        background: white;
        border-radius: 6px;
        padding: 12px 16px;
        /* border: 1px solid #e0e0e0; */
        margin-bottom: 16px;
    }

    .compact-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .compact-detail-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .compact-detail-group label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
        margin-bottom: 0;
    }

    .compact-detail-group input {
        height: 28px;
        font-size: 0.8rem;
        padding: 4px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        width: 100%;
    }

    .compact-detail-group input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    .compact-detail-group input[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .compact-section-header {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f8f9fa;
    }

    .compact-section-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .product-header-section {
        display: none;
    }

    .fitout-product-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 16px;
        height: 100%;
        min-height: 500px;
    }

    .item-details-content {
        background: white;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .item-details-header {
        padding: 8px 16px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        border-radius: 6px 6px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .item-details-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .compact-header-details {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .compact-header-group {
        gap: 4px;
    }

    .compact-header-group label {
        font-size: 0.7rem;
        font-weight: 500;
        color: #495057;
        margin: 0;
        white-space: nowrap;
    }

    .compact-header-group input {
        height: 24px;
        font-size: 0.75rem;
        padding: 2px 6px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        width: 120px;
    }

    .compact-header-group input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    .compact-header-group input[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .item-details-body {
        flex: 1;
        padding: 16px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .items-tabs-sidebar {
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .items-tabs-header {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        background: white;
        border-radius: 6px 6px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .items-tabs-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .items-tabs-container {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
    }

    .items-tab {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 4px;
        border: 1px solid transparent;
    }

    .items-tab:hover {
        background: #e9ecef;
    }

    .items-tab.active {
        background: white;
        border-color: #4361ee;
        box-shadow: 0 1px 3px rgba(67, 97, 238, 0.2);
    }

    .items-tab-name {
        font-weight: 500;
        color: #495057;
        font-size: 0.8rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .items-tab-close {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        transition: all 0.2s ease;
        font-size: 0.65rem;
        opacity: 0;
    }

    .items-tab:hover .items-tab-close {
        opacity: 1;
    }

    .items-tab-close:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .add-item-section {
        padding: 12px;
        border-top: 1px solid #e0e0e0;
        background: white;
        border-radius: 0 0 6px 0;
    }

    .empty-item-selection {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-item-selection i {
        font-size: 3rem;
        margin-bottom: 12px;
        color: #adb5bd;
        opacity: 0.6;
    }

    .empty-item-selection p {
        margin: 0 0 16px 0;
        font-size: 0.9rem;
    }

    .empty-items-tabs {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-items-tabs i {
        font-size: 2.5rem;
        margin-bottom: 12px;
        color: #adb5bd;
        opacity: 0.6;
    }

    .empty-items-tabs p {
        margin: 0 0 16px 0;
        font-size: 0.85rem;
    }

    .product-dimensions {
        background: white;
        border-radius: 6px;
        padding: 12px;
        border: 1px solid #e0e0e0;
    }

    .product-dimensions h6 {
        margin: 0 0 8px 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .dimensions-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .dimension-input {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .dimension-input label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .dimension-input input {
        height: 28px;
        font-size: 0.8rem;
        padding: 4px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .dimension-input input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    .product-price {
        background: white;
        border-radius: 6px;
        padding: 12px;
        border: 1px solid #e0e0e0;
    }

    .product-price h6 {
        margin: 0 0 8px 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .price-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .price-input {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .price-input label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .price-input input {
        height: 28px;
        font-size: 0.8rem;
        padding: 4px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .price-input input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    .enhanced-category-item {
        background: white;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .enhanced-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f8f9fa;
    }

    .enhanced-item-name {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
    }

    .enhanced-item-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .detail-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-group label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .detail-group input,
    .detail-group textarea {
        font-size: 0.8rem;
        padding: 6px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .detail-group input:focus,
    .detail-group textarea:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    .enhanced-item-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 8px;
    }

    .btn-icon {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 4px;
        transition: all 0.2s ease;
        font-size: 0.75rem;
    }

    .remove-item {
        color: #6c757d;
        background: transparent;
    }

    .remove-item:hover {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        border: none;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 6px 12px;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
    }

    .btn-outline-primary {
        color: #4361ee;
        border-color: #4361ee;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 6px 12px;
        transition: all 0.2s ease;
    }

    .btn-outline-primary:hover {
        background: #4361ee;
        border-color: #4361ee;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 6px 12px;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .status-complete {
        background: linear-gradient(135deg, #2a9d8f, #2ec4b6);
    }

    .status-incomplete {
        background: linear-gradient(135deg, #ffb703, #ff9e00);
    }

    .status-empty {
        background: linear-gradient(135deg, #adb5bd, #6c757d);
    }

    .product-empty-state {
        text-align: center;
        margin: auto;
        padding: 20px 16px;
        color: #6c757d;
    }

    .product-empty-state i {
        font-size: 2rem;
        margin-bottom: 8px;
        color: #adb5bd;
        opacity: 0.6;
    }

    .product-empty-state p {
        margin: 0 0 12px 0;
        font-size: 0.85rem;
    }

    .loading-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 20px 16px;
        color: #6c757d;
    }

    .loading-state i {
        font-size: 1.5rem;
        margin-bottom: 8px;
        color: #4361ee;
    }

    .loading-state p {
        margin: 0;
        font-size: 0.85rem;
    }

    /* Material section styles */
    .material-section {
        background: white;
        border-radius: 6px;
        padding: 12px;
        border: 1px solid #e0e0e0;
        margin-top: 12px;
    }

    .material-section h6 {
        margin: 0 0 8px 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .material-inputs-compact,
    .material-inputs-compact-replacement {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 16px;
        align-items: start;
    }

    .material-input {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .material-input label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .material-input input,
    .material-input select {
        font-size: 0.8rem;
        padding: 6px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .material-input input:focus,
    .material-input select:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    /* Product image layout - UPDATED SIZES */
    .compact-image-preview {
        width: 160px;
        height: 160px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .compact-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .compact-image-preview i {
        color: #6c757d;
        font-size: 2rem;
    }

    .material-compact-image,
    .material-compact-image-replacement {
        width: 160px;
        height: 160px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .material-compact-image img,
    .material-compact-image-replacement img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .material-compact-image i,
    .material-compact-image-replacement i {
        color: #6c757d;
        font-size: 2rem;
    }

    .material-compact-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding-right: 16px;
    }

    .material-compact-fields .material-input:first-child {

        grid-column: 1 / -1;
        /* Spans all columns */
    }

    /* Enhanced item layout - UPDATED SIZES */
    .enhanced-image-preview {
        width: 200px;
        height: 200px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .enhanced-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .enhanced-image-preview i {
        color: #6c757d;
        font-size: 2.5rem;
    }

    /* Material tabs styling */
    .material-tabs {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        padding: 0 12px 0 0;
        overflow-x: auto;
    }

    .material-tab {
        padding: 8px 16px;
        border: none;
        background: transparent;
        color: #6c757d;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
    }

    .material-tab:hover {
        color: #4361ee;
    }

    .material-tab.active {
        color: #4361ee;
        border-bottom-color: #4361ee;
        background: rgba(67, 97, 238, 0.05);
    }

    .material-tab-content {
        padding-top: 8px;
        display: none;
    }

    .material-tab-content.active {
        display: block;
    }

    /* UPDATED: Pillow Subcategories Section with Horizontal Tabs */
    .pillow-subcategories-section {
        background: white;
        border-radius: 6px;
        padding: 0;
        border: 1px solid #e0e0e0;
        margin-top: 12px;
        overflow: hidden;
    }

    .pillow-subcategories-section h6 {
        margin: 0 0 12px 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px 12px 0;
    }

    /* Pillow Subcategories Tabs - Horizontal like Room Tabs */
    .pillow-subcategories-tabs {
        background: linear-gradient(135deg, #a8e6cf 0%, #56ab2f 100%);
        padding: 8px 12px 0;
        border-bottom: none;
        display: flex;
        align-items: center;
        min-height: 40px;
        margin: 0;
    }

    .pillow-subcategory-tab {
        border: none;
        border-radius: 6px 6px 0 0;
        padding: 6px 12px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        position: relative;
        margin-right: 4px;
        background: rgba(255, 255, 255, 0.2);
        /* Lighter background for better contrast */
        backdrop-filter: blur(8px);
        cursor: pointer;
    }

    .pillow-subcategory-tab:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-1px);
    }

    .pillow-subcategory-tab.active {
        background: white;
        color: #2a9d8f;
        /* Teal color for active state instead of blue */
        font-weight: 600;
        box-shadow: 0 -4px 12px rgba(42, 157, 143, 0.2);
        /* Teal shadow */
    }

    .pillow-subcategory-tab .status-indicator.status-complete {
        background: linear-gradient(135deg, #2a9d8f, #2ec4b6);
        /* Teal gradient */
    }

    .pillow-subcategory-tab .status-indicator.status-incomplete {
        background: linear-gradient(135deg, #e9c46a, #f4a261);
        /* Orange gradient */
    }

    .pillow-subcategory-tab .status-indicator.status-empty {
        background: linear-gradient(135deg, #adb5bd, #6c757d);
        /* Keep gray as is */
    }

    .pillow-subcategory-header {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pillow-subcategory-title {
        font-size: 0.85rem;
    }

    .pillow-subcategories-content {
        padding: 16px;
        background: white;
        border-radius: 0;
    }

    .pillow-subcategory-content {
        display: none;
    }

    .pillow-subcategory-content.active {
        display: block;
    }

    .pillow-subcategory-details {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 0 16px 16px;
        border: 1px solid #e0e0e0;
    }

    /* Material inputs for pillow subcategories */
    .pillow-material-inputs-compact,
    .pillow-material-inputs-compact-replacement {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 16px;
        align-items: start;
    }

    .pillow-material-compact-image,
    .pillow-material-compact-image-replacement {
        width: 160px;
        height: 160px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .pillow-material-compact-image img,
    .pillow-material-compact-image-replacement img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pillow-material-compact-image i,
    .pillow-material-compact-image-replacement i {
        color: #6c757d;
        font-size: 2rem;
    }

    .pillow-material-compact-fields {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .pillow-material-compact-fields .pillow-material-input:first-child {
        grid-column: 1 / -1;
        /* Spans all columns */
    }

    .pillow-material-input {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .pillow-material-input label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .pillow-material-input input,
    .pillow-material-input select {
        font-size: 0.8rem;
        padding: 6px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .pillow-material-input input:focus,
    .pillow-material-input select:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    /* Updated layouts to accommodate larger images */
    .compact-details-with-image {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 16px;
        align-items: start;
    }

    .enhanced-details-with-image {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 16px;
        align-items: start;
    }

    .product-details-header {
        padding: 8px 16px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-details-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .product-details-body {
        flex: 1;
        padding: 16px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .product-details-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .compact-details-fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }

    .enhanced-details-fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }

    .product-header-with-image {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-image-preview {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .header-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .header-image-preview i {
        color: #6c757d;
        font-size: 1rem;
    }

    .search-container {
        display: flex;
        padding: 8px 16px 0;
    }

    .multi-select-modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70%;
        height: 90vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
    }

    .multi-select-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        width: 100%;
        height: 100%;
        max-width: 100vw;
        max-height: 100vh;
        overflow: hidden;
    }

    .multi-select-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid #e0e0e0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .multi-select-modal-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }

    .multi-select-modal-body {
        padding: 6px 8px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .multi-select-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }

    .multi-select-option {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
        transform: translateY(-1px);
    }

    .multi-select-option:hover {
        border-color: #4361ee;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
    }

    .multi-select-option.selected {
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
    }

    .multi-select-option-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
    }

    .multi-select-modal-footer {
        padding: 8px 16px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        background: #f8f9fa;
    }

    .item-selection-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1060;
    }

    .item-selection-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow: hidden;
    }

    .item-selection-modal-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e0e0e0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .item-selection-modal-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }

    .item-selection-modal-body {
        padding: 12px 16px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .item-categories {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 16px;
        height: 400px;
    }

    .item-categories-sidebar {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px;
    }

    .item-category-content {
        flex: 1;
    }

    .item-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 8px;
        max-height: 350px;
        overflow-y: auto;
    }

    .item-option {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        background: white;
    }

    .item-option:hover {
        border-color: #4361ee;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
    }

    .item-option.selected {
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
    }

    .item-option-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        color: white;
        font-size: 1rem;
    }

    .item-option-name {
        font-weight: 500;
        color: #495057;
        font-size: 0.8rem;
        margin-bottom: 4px;
    }

    .item-option-description {
        color: #6c757d;
        font-size: 0.7rem;
        line-height: 1.2;
    }

    .item-selection-modal-footer {
        padding: 8px 16px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        background: #f8f9fa;
    }

    /* Additional styles for curtain options */
    .curtain-options-section {
        background: white;
        border-radius: 6px;
        padding: 12px;
        border: 1px solid #e0e0e0;
        margin-top: 12px;
    }

    .curtain-options-section h6 {
        margin: 0 0 12px 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .curtain-controls {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }

    .curtain-control {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .curtain-control label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .curtain-control select {
        font-size: 0.8rem;
        padding: 6px 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .curtain-control select:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
    }

    /* Accessory section layout - same as items section */
    .accessory-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 16px;
        height: 100%;
        min-height: 400px;
    }

    .accessory-tabs-sidebar {
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .accessory-tabs-header {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        background: white;
        border-radius: 6px 6px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .accessory-tabs-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .accessory-tabs-container {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
    }

    .accessory-tab {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 4px;
        border: 1px solid transparent;
    }

    .accessory-tab:hover {
        background: #e9ecef;
    }

    .accessory-tab.active {
        background: white;
        border-color: #4361ee;
        box-shadow: 0 1px 3px rgba(67, 97, 238, 0.2);
    }

    .accessory-tab-name {
        font-weight: 500;
        color: #495057;
        font-size: 0.8rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .accessory-tab-close {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        transition: all 0.2s ease;
        font-size: 0.65rem;
        opacity: 0;
    }

    .accessory-tab:hover .accessory-tab-close {
        opacity: 1;
    }

    .accessory-tab-close:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .accessory-details-content {
        background: white;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .accessory-details-header {
        padding: 8px 16px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        border-radius: 6px 6px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .accessory-details-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .accessory-details-body {
        flex: 1;
        padding: 16px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .empty-accessory-selection {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-accessory-selection i {
        font-size: 3rem;
        margin-bottom: 12px;
        color: #adb5bd;
        opacity: 0.6;
    }

    .empty-accessory-selection p {
        margin: 0 0 16px 0;
        font-size: 0.9rem;
    }

    .empty-accessory-tabs {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-accessory-tabs i {
        font-size: 2.5rem;
        margin-bottom: 12px;
        color: #adb5bd;
        opacity: 0.6;
    }

    .empty-accessory-tabs p {
        margin: 0 0 16px 0;
        font-size: 0.85rem;
    }

    .accessory-option {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .accessory-option-image {
        width: 80px;
        height: 80px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .accessory-option-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .accessory-option-image i {
        color: #6c757d;
        font-size: 1.5rem;
    }

    .accessory-option-details {
        flex: 1;
    }

    .accessory-option-name {
        font-weight: 600;
        color: #495057;
        font-size: 0.85rem;
        margin-bottom: 4px;
    }

    .accessory-option-description {
        color: #6c757d;
        font-size: 0.75rem;
        line-height: 1.3;
    }

    /* Radio Button Variant Selection Styles */
    .variant-radio-header {
        padding: 12px 16px;
        /* border-bottom: 1px solid #e0e0e0;
      border-radius: 6px 6px 0 0; */
        background: #f8f9fa;
    }

    .variant-radio-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .variant-radio-container {
        display: flex;
        gap: 12px;
        padding: 16px;
        background: white;
        /* border-bottom: 1px solid #e0e0e0; */
        flex-wrap: wrap;
    }

    .variant-radio-option {
        display: flex;
        align-items: center;
    }

    .variant-radio-input {
        display: none;
    }

    .variant-radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }

    .variant-radio-label:hover {
        border-color: #4361ee;
        background: #f8f9fa;
    }

    .variant-radio-input:checked+.variant-radio-label {
        border: 2px solid rgba(107, 70, 255, 0.8);
        background: linear-gradient(135deg, rgba(107, 70, 255, 0.15) 0%, rgba(255, 15, 123, 0.15) 100%);
        backdrop-filter: blur(10px);
        box-shadow:
            0 8px 25px rgba(107, 70, 255, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .variant-radio-name {
        font-weight: 500;
        color: #495057;
        font-size: 0.85rem;
    }

    /* Compact Product Totals Section */
    .product-totals-section {
        margin-top: 16px;
        padding-top: 16px;
        /* border-top: 1px solid #e0e0e0; */
        display: flex;
        justify-content: flex-end;
    }

    .product-totals-row {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8f9fa;
        padding: 8px 16px;
        border-top-left-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .product-totals-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
    }

    .product-totals-amount {
        font-weight: 700;
        color: #4361ee;
        font-size: 1rem;
    }

    .product-total-price {
        display: inline-block;
        text-align: right;
    }

    /* Grand Totals Section */
    .grand-totals-wrapper {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #4361ee;
    }

    .grand-totals-section {
        background: white;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .grand-totals-header {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        color: white;
        padding: 12px 20px;
    }

    .grand-totals-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .grand-totals-content {
        padding: 20px;
    }

    .grand-totals-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .grand-totals-row:last-child {
        border-bottom: none;
    }

    .grand-totals-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.95rem;
    }

    .grand-totals-amount {
        font-weight: 600;
        color: #495057;
        font-size: 0.95rem;
    }

    .grand-total-final {
        margin-top: 8px;
        padding-top: 12px;
        border-top: 2px solid #e9ecef;
    }

    .grand-total-final .grand-totals-label {
        font-weight: 700;
        color: #4361ee;
        font-size: 1.1rem;
    }

    .grand-total-final .grand-totals-amount {
        font-weight: 700;
        color: #4361ee;
        font-size: 1.1rem;
    }

    .preview-header {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        color: white;
        padding: 8px 12px;
    }

    .preview-header h6 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .accessory-selection-container {
        display: flex;
        align-items: stretch;
        gap: 0;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .accessory-selection-container:focus-within {
        border-color: #4361ee;
        box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
    }

    .accessory-options-select {
        flex: 1;
        border: none;
        border-radius: 0;
        padding: 12px 16px;
        background: transparent;
    }

    .accessory-options-select:focus {
        outline: none;
        box-shadow: none;
    }

    .preview-content {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .preview-image {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-image i {
        color: #6c757d;
        font-size: 1rem;
    }

    /* Hover effects */
    .accessory-selection-container:hover {
        border-color: #4361ee;
    }

    /* Responsive Adjustments */
    @media (max-width: 1200px) {

        .multi-select-options {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .item-categories {
            grid-template-columns: 1fr;
            height: auto;
        }

        .item-categories-sidebar {
            order: 2;
        }

        .accessory-layout {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .accessory-tabs-sidebar {
            min-height: 200px;
        }

        .pillow-subcategories-tabs {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 768px) {
        .product-tabs-header {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }

        .room-info-form {
            flex-direction: column;
            gap: 8px;
        }

        .form-group-small {
            min-width: auto;
        }

        .product-tabs-container {
            padding: 8px;
        }

        .nav-tabs {
            flex-wrap: wrap;
        }

        .nav-tabs .nav-link {
            margin-bottom: 4px;
        }

        .multi-select-options {
            grid-template-columns: 1fr;
        }

        .dimensions-inputs,
        .price-inputs {
            grid-template-columns: 1fr;
        }

        .enhanced-item-details {
            grid-template-columns: 1fr;
        }

        .product-header-grid {
            grid-template-columns: 1fr;
        }

        .compact-details-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .fitout-product-layout {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .items-tabs-sidebar {
            min-height: 200px;
        }

        .item-details-content {
            min-height: 400px;
        }

        .item-details-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .compact-header-details {
            width: 100%;
            justify-content: space-between;
        }

        .compact-header-group input {
            width: 70px;
        }

        .compact-details-with-image {
            grid-template-columns: 1fr;
        }

        .enhanced-details-with-image {
            grid-template-columns: 1fr;
        }

        .material-inputs-compact,
        .material-inputs-compact-replacement {
            grid-template-columns: 1fr;
        }

        .material-compact-fields {
            grid-template-columns: 1fr;
        }

        .pillow-subcategories-tabs {
            flex-direction: column;
            padding: 8px;
        }

        .pillow-subcategory-tab {
            margin-bottom: 4px;
            border-radius: 4px;
        }

        .pillow-material-inputs-compact,
        .pillow-material-inputs-compact-replacement {
            grid-template-columns: 1fr;
        }

        .pillow-material-compact-fields {
            grid-template-columns: 1fr;
        }

        .curtain-controls {
            grid-template-columns: 1fr;
        }

        .product-variants-tabs,
        .set-products-tabs {
            flex-direction: column;
            padding: 8px;
        }

        .product-variant-tab,
        .set-product-tab {
            margin-bottom: 4px;
            border-radius: 4px;
        }

        .variant-management-buttons {
            flex-direction: column;
        }

        .set-variant-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .variant-product-selection {
            grid-template-columns: 1fr;
        }

        .accessory-selection-container {
            flex-direction: column;
        }

        .preview-image {
            width: 35px;
            height: 35px;
        }
    }

    .items-tabs-container::-webkit-scrollbar {
        width: 4px;
    }

    .items-tabs-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .items-tabs-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .item-details-body::-webkit-scrollbar {
        width: 4px;
    }

    .item-details-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .item-details-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .multi-select-modal-body::-webkit-scrollbar {
        width: 4px;
    }

    .multi-select-modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .item-selection-modal-body::-webkit-scrollbar {
        width: 4px;
    }

    .item-selection-modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .accessory-tabs-container::-webkit-scrollbar {
        width: 4px;
    }

    .accessory-tabs-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .accessory-tabs-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .accessory-details-body::-webkit-scrollbar {
        width: 4px;
    }

    .accessory-details-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .accessory-details-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .pillow-subcategories-content::-webkit-scrollbar {
        width: 4px;
    }

    .pillow-subcategories-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .pillow-subcategories-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .product-variants-tabs {
        background: linear-gradient(135deg, #ff9a00, #ff6b6b);
        padding: 8px 12px 0;
        border-bottom: none;
        display: flex;
        align-items: center;
        min-height: 40px;
        margin: 0;
        flex-wrap: wrap;
    }

    .set-products-tabs {
        background: linear-gradient(135deg, #4ecdc4, #44a08d);
        padding: 8px 12px 0;
        border-bottom: none;
        display: flex;
        align-items: center;
        min-height: 40px;
        margin: 16px 0 0 0;
        flex-wrap: wrap;
        border-radius: 6px 6px 0 0;
    }

    .product-variant-tab {
        border: none;
        border-radius: 6px 6px 0 0;
        padding: 6px 12px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        position: relative;
        margin-right: 4px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        cursor: pointer;
        white-space: nowrap;
    }

    .product-variant-tab:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-1px);
    }

    .product-variant-tab.active {
        background: white;
        color: #ff6b6b;
        font-weight: 600;
        box-shadow: 0 -4px 12px rgba(255, 107, 107, 0.2);
    }

    .set-product-tab {
        border: none;
        border-radius: 6px 6px 0 0;
        padding: 6px 12px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        position: relative;
        margin-right: 4px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        cursor: pointer;
        white-space: nowrap;
    }

    .set-product-tab:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-1px);
    }

    .set-product-tab.active {
        background: white;
        color: #44a08d;
        font-weight: 600;
        box-shadow: 0 -4px 12px rgba(68, 160, 141, 0.2);
    }

    .set-product-content.active {
        display: block;
    }

    /* Empty state for variants */
    .empty-variants-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .product-variant-header {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .product-variant-title {
        font-size: 0.85rem;
    }

    .product-variants-content {
        padding: 16px;
        background: white;
        /* border: 1px solid #e0e0e0;
      border-radius: 0 0 6px 6px; */
        border-top: none;
        min-height: 400px;
    }

    .product-variant-content {
        display: none;
    }

    .product-variant-content.active {
        display: block;
    }

    /* Status indicators for variants */
    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .status-complete {
        background: linear-gradient(135deg, #2a9d8f, #2ec4b6);
    }

    .status-incomplete {
        background: linear-gradient(135deg, #ffb703, #ff9e00);
    }

    .status-empty {
        background: linear-gradient(135deg, #adb5bd, #6c757d);
    }

    /* Variant management buttons */
    .variant-management-buttons {
        display: flex;
        gap: 8px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
    }

    /* Set-specific styling */
    .set-variant-content {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .set-variant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e0e0e0;
    }

    .set-variant-name {
        font-weight: 600;
        color: #495057;
        font-size: 1rem;
    }

    /* Individual product in set styling */
    .set-product-item {
        background: white;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #e0e0e0;
        position: relative;
    }

    .set-product-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .set-product-name {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
    }

    /* Empty state for variants */
    .empty-variants-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-variants-state i {
        font-size: 2.5rem;
        margin-bottom: 12px;
        color: #adb5bd;
        opacity: 0.6;
    }

    .empty-variants-state p {
        margin: 0 0 16px 0;
        font-size: 0.85rem;
    }

    /* Add product to set section */
    .add-product-to-set-section {
        background: white;
        border-radius: 6px;
        padding: 16px;
        border: 1px solid #e0e0e0;
        margin-top: 16px;
    }

    /* Variant product selection */
    .variant-product-selection {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .variant-product-option {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
        text-align: center;
    }

    .variant-product-option:hover {
        border-color: #4361ee;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
    }

    .variant-product-option.selected {
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
    }

    .variant-product-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        color: white;
        font-size: 1rem;
    }

    .variant-product-name {
        font-weight: 500;
        color: #495057;
        font-size: 0.8rem;
        margin-bottom: 4px;
    }

    .variant-product-description {
        color: #6c757d;
        font-size: 0.7rem;
        line-height: 1.2;
    }
</style>
<style>
    /* Qualification Modal - Fixed Tooltip Overlap Solution */
    .qualification-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
    }

    .qualification-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        width: 90%;
        max-width: 50%;
        max-height: 80vh;
        overflow: hidden;
    }

    .qualification-modal-header {
        display: flex;
        padding: 16px 20px;
        border-bottom: 1px solid #e0e0e0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
    }

    .qualification-modal-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .search-container {
        padding: 16px 20px 8px;
        border-bottom: 1px solid #f0f0f0;
    }

    .search-input {
        width: 100%;
        padding: 8px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 25px;
        font-size: 0.9rem;
        background: #f8f9fa;
    }

    .search-input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        background: white;
    }

    .qualification-modal-body {
        padding: 10px;
        max-height: 50vh;
        overflow-y: auto;
        position: relative;
    }

    /* Mobile-style icon grid */
    .qualification-options {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 5px;
        justify-items: center;
    }

    .qualification-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 5px;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        border: 2px solid transparent;
        width: 100%;
        max-width: 100px;
        position: relative;
    }

    .qualification-option:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
    }

    .qualification-option.selected {
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee15 0%, #3a0ca315 100%);
        box-shadow: 0 8px 16px rgba(67, 97, 238, 0.2);
    }

    .qualification-option-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .qualification-option:hover .qualification-option-icon {
        transform: scale(1.1);
    }

    .qualification-option-name {
        font-weight: 600;
        color: #495057;
        font-size: 0.75rem;
        text-align: center;
        line-height: 1.2;
    }

    /* Smart Tooltip System - No Overlap */
    .qualification-tooltip {
        position: fixed;
        background: rgba(0, 0, 0, 0.95);
        color: white;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.8rem;
        line-height: 1.4;
        white-space: normal;
        /* Changed from nowrap to normal */
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1060;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        pointer-events: none;
        min-width: 200px;
        text-align: center;
        max-width: 300px;
        word-wrap: break-word;
        /* Added to handle long words */
        overflow-wrap: break-word;
        /* Modern alternative */
    }

    .qualification-tooltip::after {
        content: '';
        position: absolute;
        border: 6px solid transparent;
    }

    /* Tooltip will be positioned dynamically via JavaScript */
    .qualification-tooltip.visible {
        opacity: 1;
        visibility: visible;
    }

    .qualification-modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #f8f9fa;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .qualification-options {
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
        }

        .qualification-option {
            max-width: 80px;
            padding-top: 4px;
        }

        .qualification-option-icon {
            width: 50px;
            height: 50px;
            font-size: 1.3rem;
        }

        .qualification-tooltip {
            font-size: 0.75rem;
            padding: 10px 12px;
            min-width: 160px;
            max-width: 250px;
        }
    }

    @media (max-width: 480px) {
        .qualification-options {
            grid-template-columns: repeat(2, 1fr);
            gap: 3px;
        }

        .qualification-modal-content {
            width: 95%;
        }
    }

    /* Scrollbar styling */
    .qualification-modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .qualification-modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .qualification-modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
</style>
<style>
    /* Filter Layout Styles */
    .filter-layout {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 16px;
        height: 320px;
    }

    .modal-fullscreen .filter-layout {
        height: 380px;
    }

    /* Style Filter Sidebar */
    .style-filter-sidebar {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 12px;
        border: 1px solid #e0e0e0;
    }

    .style-filter-header {
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    .style-filter-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .style-checkbox-tabs {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .style-checkbox-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
        border: 1px solid #e0e0e0;
    }

    .style-checkbox-tab:hover {
        border-color: #4361ee;
        background: #f8f9fa;
    }

    .style-checkbox-tab.selected {
        border-color: #4361ee;
        background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
    }

    .style-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #dee2e6;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .style-checkbox-tab.selected .style-checkbox {
        background: #4361ee;
        border-color: #4361ee;
    }

    .style-checkbox-tab.selected .style-checkbox::after {
        content: '✓';
        color: white;
        font-size: 10px;
        font-weight: bold;
    }

    .style-checkbox-name {
        font-size: 0.8rem;
        font-weight: 500;
        color: #495057;
        flex: 1;
    }

    /* Brand Selection Section */
    .brand-selection-section {
        background: white;
        border-bottom: 1px solid #e0e0e0;
    }

    .brand-radio-tabs {
        display: flex;
        gap: 8px;
        padding: 10px 10px 0px;
        overflow-x: auto;
        background: #f8f9fa;
    }

    .brand-radio-option {
        display: flex;
        align-items: center;
    }

    .brand-radio-input {
        display: none;
    }

    .brand-radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
        white-space: nowrap;
        font-size: 0.8rem;
    }

    .brand-radio-label:hover {
        border-color: #4361ee;
        background: #f8f9fa;
    }

    .brand-radio-input:checked+.brand-radio-label {
        border-color: #4361ee;
        background: #4361ee;
        color: white;
    }

    .brand-radio-name {
        font-weight: 500;
    }

    /* Products Grid Container */
    .products-grid-container {
        flex: 1;
        overflow-y: auto;
    }

    .multi-select-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
        padding: 8px;
    }

    /* Header Actions */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: 20px;
    }

    .filter-toggle-button {
        background: rgba(255, 255, 255, 0.2);
        /* border: 1px solid rgba(255, 255, 255, 0.3);
      color: white; */
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .filter-toggle-button:hover {
        border-radius: 5px;
    }

    /* Advanced Filter Modal */
    .advanced-filter-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
    }

    .advanced-filter-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow: hidden;
    }

    .advanced-filter-modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e0e0e0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .advanced-filter-modal-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .btn-close-filter {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .btn-close-filter:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .advanced-filter-modal-body {
        padding: 20px;
        max-height: 50vh;
        overflow-y: auto;
    }

    .advanced-filter-modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }

    /* Filter Sections */
    .filter-section {
        margin-bottom: 24px;
    }

    .filter-section h6 {
        margin: 0 0 12px 0;
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* Price Range Filter */
    .price-range-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .price-input-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .price-input-group label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #495057;
    }

    .price-input-group input {
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .price-slider-container {
        margin-top: 8px;
    }

    .price-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 4px;
        font-size: 0.7rem;
        color: #6c757d;
    }

    /* Date Filter */
    .date-filter-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .form-check-input {
        width: 16px;
        height: 16px;
        margin: 0;
    }

    .form-check-label {
        font-size: 0.8rem;
        color: #495057;
        margin-left: 20px;
    }

    /* Availability Filter */
    .availability-filter {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* Product image styles */
    .multi-select-option-image {
        width: 100%;
        height: 150px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .qualification-select-option-image {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .multi-select-option-image img,
    .qualification-select-option-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .multi-select-option-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .multi-select-option-name {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        line-height: 1.3;
        flex: 1;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .filter-layout {
            grid-template-columns: 1fr;
            height: auto;
        }

        .style-filter-sidebar {
            order: 2;
        }

        .brand-radio-tabs {
            flex-wrap: wrap;
        }

        .multi-select-options {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .advanced-filter-modal-content {
            width: 95%;
            max-width: none;
            margin: 20px;
        }
    }

    .load-more-div {
        text-align: center;
        margin-top: 12px;
        margin-bottom: 8px;
    }

    /* Add these styles for the nested pillow tabs */
    .pillow-subcategories-section {
        margin-top: 10px;
    }

    .pillow-subcategories-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 1px solid #dee2e6;
    }

    .pillow-subcategory-tab {
        padding: 8px 15px;
        background: #f1f3f9;
        border: 1px solid #d1d6e6;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pillow-subcategory-tab:hover {
        background: #e6e9f6;
    }

    .pillow-subcategory-tab.active {
        background: #3a56cd;
        color: white;
        border-color: #3a56cd;
    }

    .pillow-subcategory-header {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pillow-subcategory-title {
        font-weight: 500;
    }

    .pillow-subcategories-content {
        margin-top: 10px;
    }

    .pillow-subcategory-content {
        display: none;
    }

    .pillow-subcategory-content.active {
        display: block;
    }

    .pillow-material-group-header h6 {
        font-size: 14px;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 8px;
        padding-left: 0;
        border-bottom: 1px dashed #dee2e6;
    }
</style>
<style>
    /* CSS for zoom in / zoom out modals */
    /* Modal Zoom Functionality */
    .modal-controls {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-left: auto;
    }

    .zoom-toggle {
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        color: #6c757d;
        font-size: 14px;
    }

    .zoom-toggle:hover {
        background: #4361ee;
        border-color: #4361ee;
        color: white;
        transform: scale(1.05);
    }

    .zoom-toggle:active {
        transform: scale(0.95);
    }

    /* Fullscreen modal styles */
    .modal-fullscreen {
        position: fixed !important;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100% !important;
        margin: 0 !important;
        z-index: 9999 !important;
        background: rgba(0, 0, 0, 0.5) !important;
        padding: 20px !important;
    }

    .modal-fullscreen .modal-content {
        width: 100% !important;
        height: 100% !important;
        max-width: none !important;
        max-height: none !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3) !important;
        border: 2px solid #4361ee !important;
    }

    .modal-fullscreen .modal-body {
        flex: 1 !important;
        max-height: none !important;
        overflow-y: auto !important;
    }

    /* Specific styles for qualification modals */
    .qualification-modal.modal-fullscreen .qualification-modal-content {
        width: 100%;
        height: 100%;
        max-width: none;
        max-height: none;
        border-radius: 12px;
    }

    .qualification-modal.modal-fullscreen .qualification-modal-body {
        flex: 1;
        max-height: 60vh;
        overflow-y: auto;
        padding: 10px;
    }

    .surcharges-section {
        margin-top: 12px;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .surcharges-section h6 {
        margin-bottom: 12px;
        color: #333;
        font-weight: 600;
    }

    .surcharges-container {
        display: ruby;
    }

    .surcharge-item {
        padding: 8px 0;
    }

    .surcharge-item .form-check-label {
        font-weight: 500;
        color: #555;
    }

    .surcharge-rate {
        font-weight: 600;
        margin-left: 5px;
    }

    /* .surcharge-rate.text-success::before {
      content: "+";
   }

   .surcharge-rate.text-danger::before {
      content: "-";
   } */

    .installation-control {
        padding: 12px 0 !important;
        margin-top: 12px;
        margin-left: 24px;
    }

    .installation-control .custom-control-label {
        display: flex !important;
        align-items: center !important;
        font-size: 14px !important;
        color: #495057 !important;
        cursor: pointer !important;
    }

    .installation-control .custom-checkbox {
        padding-left: 0 !important;
    }

    .installation-control .custom-control-input:checked~.custom-control-label::before {
        border-color: #4361ee;
        background-color: #4361ee;
    }

    .installation-control .custom-control-label::before {
        top: 0.25rem;
    }

    .installation-control .custom-control-label::after {
        top: 0.25rem;
    }

    /* Add these styles to your CSS */
    .compact-image-preview,
    .enhanced-image-preview {
        position: relative;
        overflow: hidden;
    }

    .image-upload-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .compact-image-preview:hover .image-upload-overlay,
    .enhanced-image-preview:hover .image-upload-overlay {
        opacity: 1;
    }

    .image-upload-label {
        color: white;
        cursor: pointer;
        padding: 10px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transition: background 0.3s ease;
    }

    .image-upload-label:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .material-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .related-btn-div {
        display: block;
    }

    .related-btn-div button {
        padding: 0 2px !important;
        margin-bottom: 5px;
    }

    /* Add these styles to your CSS */
    .material-label-tabs-container {
        margin-top: 10px;
    }

    .material-label-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 1px solid #dee2e6;
    }

    .material-label-tab {
        padding: 6px 12px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }

    .material-label-tab:hover {
        background: #e9ecef;
    }

    .material-label-tab.active {
        background: #4361ee;
        color: white;
        border-color: #4361ee;
    }

    .material-label-tab-content {
        display: none;
    }

    .material-label-tab-content.active {
        display: block;
    }

    .pillow-type-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .pillow-type-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
    }

    /* Consistent styling for ALL material label tabs */
    .material-label-tabs-container {
        margin-top: 10px;
    }

    .material-label-tabs {
        display: flex !important;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 1px solid #dee2e6;
        min-height: 40px;
    }

    .material-label-tab {
        padding: 6px 12px !important;
        background: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        font-size: 14px !important;
        transition: all 0.2s !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 32px;
        position: relative !important;
        z-index: 1 !important;
    }

    .material-label-tab:hover {
        background: #e9ecef !important;
        border-color: #adb5bd !important;
    }

    .material-label-tab.active {
        background: #4361ee !important;
        color: white !important;
        border-color: #4361ee !important;
        font-weight: 500;
    }

    .material-label-tab-content {
        display: none !important;
        animation: fadeIn 0.3s ease;
    }

    .material-label-tab-content.active {
        display: block !important;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Make sure all tab content is properly shown */
    .material-tabs-content {
        min-height: 200px;
    }

    .material-label-content {
        min-height: 150px;
    }

    /* Vertical Pillow Subcategory Tabs */
    .pillow-subcategories-section {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .pillow-subcategories-tabs {
        display: flex;
        flex-direction: column;
        width: 180px;
        min-width: 180px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px;
        margin-top: 10px;
        border: 1px solid #e9ecef;
    }

    .pillow-subcategory-tab {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        margin-bottom: 5px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: left;
        width: 100%;
    }

    .pillow-subcategory-tab:hover {
        background: #f1f3f5;
        border-color: #adb5bd;
    }

    .pillow-subcategory-tab.active {
        background: #4361ee;
        border-color: #4361ee;
        color: white;
    }

    .pillow-subcategory-tab.active .pillow-subcategory-title,
    .pillow-subcategory-tab.active .status-indicator {
        color: white;
    }

    .pillow-subcategory-header {
        display: flex;
        align-items: center;
        width: 100%;
    }

    .pillow-subcategory-title {
        font-size: 13px;
        font-weight: 500;
        margin-left: 8px;
        flex: 1;
        color: #555;
    }

    .pillow-subcategories-content {
        flex: 1;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 0;
        /* padding: 15px; */
        min-height: 200px;
    }

    .pillow-subcategory-content {
        display: none;
    }

    .pillow-subcategory-content.active {
        display: block;
    }
</style>
<!-- Start content -->
<div class="content">
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Order - Order #<?php echo $order->order_id; ?></h4>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?php echo URL; ?>/index.php?page=orders" class="btn btn-success">
                        <?php echo get_lang_text('orderadd_btn_all_orders'); ?>
                    </a>
                    <a href="<?php echo URL; ?>/index.php?page=order-show&id=<?php echo $order_id; ?>" target="_blank" class="btn btn-info">
                        View Order
                    </a>
                </div>
            </div> <!-- end row -->
        </div>
        <!-- end page-title -->

        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form id="edit_order_form" enctype="multipart/form-data">
                            <div class="row">
                                <div class="order-page-col col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label for="lblOrderDate"><?= get_lang_text('orderadd_input_order_date'); ?> *</label>
                                        <input type="text" name="order_date" class="form-control make-datepicker required" id="lblOrderDate" placeholder="<?= get_lang_text('orderadd_input_order_date'); ?> *" value="<?= date('d.m.Y', strtotime($order->order_date)); ?>">
                                    </div>
                                </div>
                                <div class="order-page-col col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label for="lblOrderDeliveryDate"><?= get_lang_text('orderadd_input_delivery_date'); ?> *</label>
                                        <input type="text" name="order_delivery_date" class="form-control make-datepicker order_delivery_date required" id="lblOrderDeliveryDate" placeholder="<?= get_lang_text('orderadd_input_delivery_date'); ?> *" value="<?= date('d.m.Y', strtotime($order->order_delivery_date)); ?>">
                                        <input type="hidden" name="dlv_date_modified" class="form-control" id="date_modified" value="<?= $order->dlv_date_modified ?>">
                                        <small class="text-danger date_modified-note d-none">User-modified date is not automatically calculated.</small>
                                    </div>
                                </div>
                                <div class="order-page-col col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label for="lblOrderArcs"><?= get_lang_text('orderadd_input_arcs'); ?> *</label>
                                        <input type="text" name="order_arcs" class="form-control required" placeholder="<?= get_lang_text('orderadd_input_arcs'); ?> *" value="<?= $order->order_arcs; ?>">
                                    </div>
                                </div>
                                <div class="order-page-col col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label for="lblOrderCustomer"><?= get_lang_text('orderadd_input_customer'); ?> *</label>
                                        <select name="customer_id" id="lblOrderCustomer" class="form-control make-it-select order-select-customer required">
                                            <option value=""><?= get_lang_text('orderadd_input_customer_select'); ?></option>
                                            <?php
                                            $customer = new Customer();
                                            $customers_where = [['customer_status', '=', '1']];
                                            if ($logged->user_auth == 'user') {
                                                /* bayi veya merkez satış girişi yaptıysa sadece kendi bayisindeki siparişleri görebilir */
                                                $customers_where[] = ['customer_added_branch', '=', $logged->user_branch];
                                            }
                                            $customers = $customer->getCustomers('customer_id,customer_name,customer_comm_rate', ['customer_name', 'ASC'], $customers_where);
                                            if (count($customers) > 0) {
                                                foreach ($customers as $c) {
                                            ?>
                                                    <option <?= $order->customer_id == $c->customer_id ? ' selected' : null; ?> value="<?= $c->customer_id; ?>" data-comm-rate="<?= formatExcelPrice($c->customer_comm_rate, 0); ?>"><?= $c->customer_name; ?></option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="order-page-col col-md-3 col-sm-6 full-w-s">
                                    <div class="form-group">
                                        <label for="lblOrderCustomerAddress"><?= get_lang_text('orderadd_input_delivery_address'); ?> *</label>
                                        <select name="customer_address_id" id="lblOrderCustomerAddress" class="form-control make-it-select order-select-customer-address required">
                                            <?php
                                            $customer_addresses = $customer->getCustomerAddresses($order->customer_id, 'adr_id,adr_title', ['adr_title', 'ASC']);
                                            if (count($customer_addresses) > 0) {
                                                foreach ($customer_addresses as $ca) {
                                            ?>
                                                    <option <?= $order->address_id == $ca->adr_id ? ' selected' : null; ?> value="<?= $ca->adr_id; ?>"><?= html_ent_decode($ca->adr_title); ?></option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group order-show-country" style="display: block;">
                                        <label for="lblOrderAddress"><?= get_lang_text('orderadd_input_delivery_address_detail'); ?></label><br>
                                        <div class="address-detail">
                                            <?php
                                            $customer_address = $customer->getCustomerAddress($order->address_id, $order->customer_id, 'adr_country,adr_text');
                                            $address_country = Country::getCountry($customer_address->adr_country, 'country_name');
                                            ?>
                                            <strong> <?= get_lang_text('orderadd_input_delivery_address_detail_country'); ?></strong> : <span class="order-address-country"><?= $address_country->country_name; ?></span>
                                            <strong> <?= get_lang_text('orderadd_input_delivery_address_detail_address'); ?></strong> : <span class="order-address-text"><?= html_ent_decode($customer_address->adr_text); ?></span><br>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-page-col col-md-3 col-sm-6 full-w-s">
                                    <div class="form-group">
                                        <label for="lblOrderExportRegistered"><?= get_lang_text('orderadd_input_export_register'); ?> *</label>
                                        <select name="order_export_registered" id="lblOrderExportRegistered" class="form-control required">
                                            <option <?= $order->order_export_registered == '1' ? ' selected' : null; ?> value="1"><?= get_lang_text('orderadd_input_export_register_yes'); ?></option>
                                            <option <?= $order->order_export_registered == '0' ? ' selected' : null; ?> value="0"><?= get_lang_text('orderadd_input_export_register_no'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="order-page-col col-md-6 col-sm-6 full-w">
                                    <?php
                                    $comm_rate_disabled = '';
                                    $comm_amount_disabled = '';
                                    if ($order->order_comm_rate != '') {
                                        $comm_rate_disabled = '';
                                        $comm_amount_disabled = ' disabled';
                                    } else if ($order->order_comm_amount != '') {
                                        $comm_rate_disabled = ' disabled';
                                        $comm_amount_disabled = '';
                                    }
                                    ?>
                                    <div class="form-group row">
                                        <label class="col-sm-12"><?= get_lang_text('orderedit_input_comm'); ?></label>
                                        <div class="col-sm-6">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">%</span>
                                                </div>
                                                <input type="text" name="order_comm_rate" class="form-control make-numeric order-comm-rate" placeholder="<?= get_lang_text('orderadd_input_comm_rate'); ?>" value="<?= $order->order_comm_rate; ?>" autocomplete="off" <?= $comm_rate_disabled; ?>>
                                            </div>
                                            <small><?= get_lang_text('orderadd_input_comm_rate'); ?></small>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">$</span>
                                                </div>
                                                <input type="text" name="order_comm_amount" class="form-control make-numeric order-comm-amount" placeholder="<?= get_lang_text('orderadd_input_comm_amount'); ?>" value="<?= $order->order_comm_amount; ?>" autocomplete="off" <?= $comm_amount_disabled; ?>>
                                            </div>
                                            <small><?= get_lang_text('orderadd_input_comm_amount'); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-page-col col-md-6 col-sm-6 full-w-s">
                                    <div class="form-group">
                                        <label for="lblOrderAgreement"><?= get_lang_text('orderadd_input_contracts'); ?> *</label>
                                        <select name="order_agreement" id="lblOrderAgreement" class="form-control make-it-select select-order-agreement required">
                                            <option value=""><?= get_lang_text('orderadd_input_contracts_select'); ?></option>
                                            <?php
                                            $agreement = new Agreement();
                                            $agr_where = [['agr_status', '=', '1']];
                                            if ($logged_auth == 'user' || $logged_auth == 'partner') {
                                                $agr_where[] = ['branch_id', '=', $logged->user_branch];
                                            }
                                            $agreements = $agreement->getAgreements('agr_id,agr_title', ['agr_title', 'ASC'], $agr_where);
                                            if (count($agreements) > 0) {
                                                foreach ($agreements as $agr) { ?>
                                                    <option <?= $order->agreement_id == $agr->agr_id ? ' selected' : null; ?> value="<?= $agr->agr_id; ?>"><?= $agr->agr_title; ?></option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="lblOrderAgreementText"><?= get_lang_text('orderadd_input_contract_text'); ?> *</label>
                                        <textarea name="order_agreement_text" id="lblOrderAgreementText" rows="15" class="form-control order-agreement-text required" placeholder="<?= get_lang_text('orderadd_input_contract_text'); ?> *"><?= str_replace('<br />', '', html_ent_decode($order->agreement_text)); ?></textarea>
                                        <small><?= get_lang_text('orderadd_input_contract_text_desc'); ?></small>
                                    </div>
                                </div>
                                <div class="order-page-col col-md-6 col-sm-6 full-w-s">
                                    <div class="form-group">
                                        <label for="lblOrderTax"><?= get_lang_text('orderadd_input_tax'); ?> *</label>
                                        <select name="order_tax" id="lblOrderTax" class="form-control make-it-select required">
                                            <?php
                                            for ($i = 0; $i <= 24; $i++) { ?>
                                                <option <?= $order->order_tax == $i ? ' selected' : null; ?> value="<?= $i; ?>"><?= $i; ?>%</option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="lblOrderNotes"><?= get_lang_text('orderadd_input_order_notes'); ?></label>
                                        <textarea name="order_notes" id="lblOrderNotes" class="form-control" rows="5" placeholder="<?= get_lang_text('orderadd_input_order_notes'); ?>"><?= str_replace('<br />', '', html_ent_decode($order->order_notes)); ?></textarea>
                                        <small><?= get_lang_text('orderadd_input_order_notes_desc'); ?></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="lblOrderCustomerArc"><?= get_lang_text('orderadd_input_customer_has_arc'); ?></label>
                                        <select name="order_extra_agreement" id="lblOrderCustomerArc" class="form-control order-agreement-special-arc">
                                            <option <?= $order->agreement_extra == '0' ? ' selected' : null; ?> value="0"><?= get_lang_text('orderadd_input_customer_has_arc_no'); ?></option>
                                            <option <?= $order->agreement_extra == '1' ? ' selected' : null; ?> value="1"><?= get_lang_text('orderadd_input_customer_has_arc_yes'); ?></option>
                                        </select>
                                        <small><?= get_lang_text('orderadd_input_customer_has_arc_desc'); ?></small>
                                    </div>
                                    <div class="form-group row show-extra-agreement-row" style="display: <?= $order->agreement_extra == '1' ? 'flex' : 'none'; ?>;">
                                        <label for="lblOrderCustomerArc" class="col-sm-12"><?= get_lang_text('orderadd_input_contract_extra'); ?></label>
                                        <div class="col-sm-6">
                                            <textarea name="order_extra_agreement_tr" class="form-control order-agreement-extra-tr" rows="8" placeholder="<?= get_lang_text('orderadd_input_contract_extra'); ?> (TR)" <?= $order->agreement_extra == '0' ? ' disabled' : null; ?>><?= str_replace('<br />', '', html_ent_decode($order->agreement_extra_tr)); ?></textarea>
                                        </div>
                                        <div class="col-sm-6">
                                            <textarea name="order_extra_agreement_en" class="form-control order-agreement-extra-en" rows="8" placeholder="<?= get_lang_text('orderadd_input_contract_extra'); ?> (EN)" <?= $order->agreement_extra == '0' ? ' disabled' : null; ?>><?= str_replace('<br />', '', html_ent_decode($order->agreement_extra_en)); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lblOrderStatus"><?= get_lang_text('orderedit_input_order_status'); ?> *</label>
                                        <select name="order_status" id="lblOrderStatus" class="form-control required">
                                            <option <?= $order->order_status == 'completed' ? ' selected' : null; ?> value="completed"><?= get_lang_text('orderedit_input_order_status_completed'); ?></option>
                                            <option <?= $order->order_status == 'quotation' ? ' selected' : null; ?> value="quotation"><?= get_lang_text('orderedit_input_order_status_proposal'); ?></option>
                                            <option <?= $order->order_status == 'revized' ? ' selected' : null; ?> value="revized"><?= get_lang_text('orderedit_input_order_status_revised'); ?></option>
                                            <option <?= $order->order_status == 'cancel' ? ' selected' : null; ?> value="cancel"><?= get_lang_text('orderedit_input_order_status_cancel'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <div class="room-wrapper">
                                <ul class="nav nav-tabs" id="roomTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active room-tab" id="room1-tab" data-toggle="tab" href="#room1" role="tab"
                                            aria-controls="room1" aria-selected="true" data-room="1">
                                            <div class="room-header" style="background: linear-gradient(135deg, #4361ee, #3a0ca3);">
                                                <span class="status-indicator status-empty"></span>
                                                <span class="room-title">Room 1</span>
                                                <span class="close-room ml-2" title="Remove room">
                                                    <i class="fa fa-times"></i>
                                                </span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item ml-auto pl-2">
                                        <button type="button" class="btn btn-sm btn-primary add-room-btn" id="addRoomBtn">
                                            <i class="fa fa-plus mr-1"></i> Add Room
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="roomTabsContent">
                                    <div class="tab-pane fade show active" id="room1" role="tabpanel" aria-labelledby="room1-tab" data-room="1">
                                        <div class="product-tabs-wrapper">
                                            <div class="product-tabs-header" style="border-left: 4px solid #4361ee;">
                                                <div class="room-info-form">
                                                    <div class="form-group-small">
                                                        <label for="floorName-room1">Floor Name</label>
                                                        <input type="text" class="form-control-small floor-name-input" id="floorName-room1" data-room-id="room1" placeholder="Enter floor name">
                                                    </div>
                                                    <div class="form-group-small">
                                                        <label for="roomName-room1">Room Name</label>
                                                        <input type="text" class="form-control-small room-name-input" id="roomName-room1" data-room-id="room1" placeholder="Enter room name">
                                                    </div>
                                                    <div class="form-group-small">
                                                        <label>Room Image</label>
                                                        <div class="image-upload-container">
                                                            <div class="image-preview" id="imagePreview-room1">
                                                                <i class="fa fa-image"></i>
                                                            </div>
                                                            <div class="file-input-wrapper">
                                                                <button type="button" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fa fa-upload mr-1"></i> Upload
                                                                </button>
                                                                <input type="file" class="room-image-input" id="roomImage-room1" data-file-type="image" data-room="1">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm add-item-to-room-btn" data-room="1">
                                                    <i class="fa fa-plus mr-1"></i> Add Item To Room 1
                                                </button>
                                            </div>
                                            <div class="product-tabs-container" id="productTabs-room1">
                                                <div class="product-empty-state">
                                                    <i class="fa fa-cube"></i>
                                                    <p>No products added yet</p>
                                                </div>
                                            </div>
                                            <div class="product-content-area" id="productContent-room1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grand-totals-wrapper">
                                <div class="grand-totals-section" id="grand-totals-section">
                                    <div class="grand-totals-header">
                                        <h5><i class="fa fa-receipt mr-2"></i>Order Summary</h5>
                                    </div>
                                    <div class="grand-totals-content">
                                        <div class="grand-totals-row">
                                            <div class="grand-totals-label">Subtotal:</div>
                                            <div class="grand-totals-amount">
                                                $<span class="grand-subtotal" id="grand-subtotal">0.00</span>
                                            </div>
                                        </div>
                                        <div class="grand-totals-row">
                                            <div class="grand-totals-label">Tax (<span id="tax-percentage">0</span>%):</div>
                                            <div class="grand-totals-amount">
                                                $<span class="grand-tax" id="grand-tax">0.00</span>
                                            </div>
                                        </div>
                                        <div class="grand-totals-row grand-total-final">
                                            <div class="grand-totals-label">Grand Total:</div>
                                            <div class="grand-totals-amount">
                                                $<span class="grand-total" id="grand-total">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Category Modal -->
                            <div class="qualification-modal" id="mainCategoryModal">
                                <div class="qualification-modal-content">
                                    <div class="qualification-modal-header">
                                        <h5><i class="fa fa-layer-group mr-2"></i>Select Main Category</h5>
                                    </div>
                                    <div class="search-container">
                                        <input type="text" class="search-input" id="mainCategorySearch" placeholder="Search main categories...">
                                    </div>
                                    <div class="qualification-modal-body">
                                        <div class="qualification-options" id="mainCategoryOptions"></div>
                                        <div class="multi-select-options" id="catProductOptions"></div>
                                    </div>
                                    <div class="qualification-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="closeMainCategoryModal">Close</button>
                                        <button type="button" class="btn btn-primary d-none" id="confirmMainCategory" disabled>Next</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Qualification Modal -->
                            <div class="qualification-modal" id="qualificationModal">
                                <div class="qualification-modal-content">
                                    <div class="qualification-modal-header">
                                        <h5><i class="fa fa-plus-circle mr-2"></i>Select Qualification</h5>
                                    </div>
                                    <div class="search-container">
                                        <input type="text" class="search-input" id="qualificationSearch" placeholder="Search qualifications...">
                                    </div>
                                    <div class="qualification-modal-body">
                                        <div class="qualification-options" id="qualificationOptions"></div>
                                        <div class="multi-select-options" id="qualProductOptions"></div>
                                    </div>
                                    <div class="qualification-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="closeQualificationModal">Close</button>
                                        <button type="button" class="btn btn-secondary" id="qualificationModalBackButton">Back</button>
                                        <button type="button" class="btn btn-primary d-none" id="confirmQualificationSelect" disabled>Next</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Multi-Select Products Modal -->
                            <div class="multi-select-modal" id="productSelectModal">
                                <div class="multi-select-modal-content">
                                    <div class="multi-select-modal-header">
                                        <h5><i class="fa fa-layer-group mr-2"></i>Select Products</h5>
                                    </div>

                                    <!-- Brand Selection - Horizontal Radio Tabs -->
                                    <div class="brand-selection-section">
                                        <div class="brand-radio-tabs" id="brandRadioTabs">
                                            <!-- Brands will be populated here -->
                                        </div>
                                    </div>

                                    <div class="search-container">
                                        <input type="text" class="search-input" id="productSearch" placeholder="Search products...">
                                        <div class="header-actions">
                                            <button type="button" class="btn btn-outline-secondary btn-sm filter-toggle-button" id="filterToggleButton">
                                                <i class="fa fa-filter"></i> Filters
                                            </button>
                                        </div>
                                    </div>

                                    <div class="multi-select-modal-body">
                                        <div class="filter-layout">
                                            <!-- Style Selection - Vertical Checkbox Tabs -->
                                            <div class="style-filter-sidebar">
                                                <div class="style-filter-header">
                                                    <h6>Styles</h6>
                                                </div>
                                                <div class="style-checkbox-tabs" id="styleCheckboxTabs">
                                                    <!-- Styles will be populated here -->
                                                </div>
                                            </div>

                                            <!-- Products Grid -->
                                            <div class="products-grid-container">
                                                <div class="multi-select-options" id="multiSelectOptions"></div>
                                                <div class="load-more-div text-center mt-3">
                                                    <button type="button" class="btn btn-outline-primary" id="loadMoreProductsBtn">
                                                        <i class="fa fa-sync-alt mr-1"></i> Load More Products
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="multi-select-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="closeProductSelectModal">Close</button>
                                        <button type="button" class="btn btn-secondary" id="productSelectModalBackButton">Back</button>
                                        <button type="button" class="btn btn-primary" id="confirmMultiSelect" disabled>Add Selected Product</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Filter Modal -->
                            <div class="advanced-filter-modal" id="advancedFilterModal">
                                <div class="advanced-filter-modal-content">
                                    <div class="advanced-filter-modal-header">
                                        <h5><i class="fa fa-sliders-h mr-2"></i>Advanced Filters</h5>
                                        <button type="button" class="btn-close-filter" id="closeAdvancedFilter">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="advanced-filter-modal-body">
                                        <!-- Price Range Filter -->
                                        <div class="filter-section">
                                            <h6>Price Range</h6>
                                            <div class="price-range-inputs">
                                                <div class="price-input-group">
                                                    <label>Min Price</label>
                                                    <input type="number" class="form-control" id="minPrice" placeholder="0" min="0">
                                                </div>
                                                <div class="price-input-group">
                                                    <label>Max Price</label>
                                                    <input type="number" class="form-control" id="maxPrice" placeholder="50000" min="0">
                                                </div>
                                            </div>
                                            <div class="price-slider-container">
                                                <input type="range" class="form-range" id="priceRange" min="0" max="50000" step="100">
                                                <div class="price-labels">
                                                    <span>$0</span>
                                                    <span>$5,000</span>
                                                    <span>$50,000</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Date Filter -->
                                        <div class="filter-section">
                                            <h6>Date Added</h6>
                                            <div class="date-filter-options">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="dateFilter" id="newestFirst" value="newest" checked>
                                                    <label class="form-check-label" for="newestFirst">
                                                        Newest First
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="dateFilter" id="oldestFirst" value="oldest">
                                                    <label class="form-check-label" for="oldestFirst">
                                                        Oldest First
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Availability Filter -->
                                        <div class="filter-section d-none">
                                            <h6>Availability</h6>
                                            <div class="availability-filter">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="inStock" checked>
                                                    <label class="form-check-label" for="inStock">
                                                        In Stock
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="outOfStock">
                                                    <label class="form-check-label" for="outOfStock">
                                                        Out of Stock
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="advanced-filter-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="resetFilters">Reset All</button>
                                        <button type="button" class="btn btn-primary" id="applyFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Item Selection Modal -->
                            <div class="multi-select-modal" id="itemSelectionModal">
                                <div class="multi-select-modal-content">
                                    <div class="multi-select-modal-header">
                                        <h5><i class="fa fa-cube mr-2"></i>Select Item</h5>
                                    </div>

                                    <!-- Brand Selection - Horizontal Radio Tabs -->
                                    <div class="brand-selection-section">
                                        <div class="brand-radio-tabs" id="itemBrandRadioTabs">
                                            <!-- Brands will be populated here -->
                                        </div>
                                    </div>

                                    <div class="search-container">
                                        <input type="text" class="search-input" id="itemSearch" placeholder="Search items...">
                                        <div class="header-actions">
                                            <button type="button" class="btn btn-outline-secondary btn-sm filter-toggle-button" id="itemFilterToggleButton">
                                                <i class="fa fa-filter"></i> Filters
                                            </button>
                                        </div>
                                    </div>

                                    <div class="multi-select-modal-body">
                                        <div class="filter-layout">
                                            <!-- Style Selection - Vertical Checkbox Tabs -->
                                            <div class="style-filter-sidebar">
                                                <div class="style-filter-header">
                                                    <h6>Styles</h6>
                                                </div>
                                                <div class="style-checkbox-tabs" id="itemStyleCheckboxTabs">
                                                    <!-- Styles will be populated here -->
                                                </div>
                                            </div>

                                            <!-- Items Grid -->
                                            <div class="products-grid-container">
                                                <div class="multi-select-options" id="itemMultiSelectOptions"></div>
                                                <div class="load-more-div text-center mt-3">
                                                    <button type="button" class="btn btn-outline-primary" id="loadMoreItemsBtn">
                                                        <i class="fa fa-sync-alt mr-1"></i> Load More Items
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="multi-select-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="closeItemSelectionModal">Close</button>
                                        <button type="button" class="btn btn-primary" id="confirmSelectItem" disabled>Add Item</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Filter Modal for Items -->
                            <div class="advanced-filter-modal" id="itemAdvancedFilterModal">
                                <div class="advanced-filter-modal-content">
                                    <div class="advanced-filter-modal-header">
                                        <h5><i class="fa fa-sliders-h mr-2"></i>Advanced Filters - Items</h5>
                                        <button type="button" class="btn-close-filter" id="closeItemAdvancedFilter">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="advanced-filter-modal-body">
                                        <!-- Price Range Filter -->
                                        <div class="filter-section">
                                            <h6>Price Range</h6>
                                            <div class="price-range-inputs">
                                                <div class="price-input-group">
                                                    <label>Min Price</label>
                                                    <input type="number" class="form-control" id="itemMinPrice" placeholder="0" min="0">
                                                </div>
                                                <div class="price-input-group">
                                                    <label>Max Price</label>
                                                    <input type="number" class="form-control" id="itemMaxPrice" placeholder="50000" min="0">
                                                </div>
                                            </div>
                                            <div class="price-slider-container">
                                                <input type="range" class="form-range" id="itemPriceRange" min="0" max="50000" step="100">
                                                <div class="price-labels">
                                                    <span>$0</span>
                                                    <span>$5,000</span>
                                                    <span>$50,000</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Date Filter -->
                                        <div class="filter-section">
                                            <h6>Date Added</h6>
                                            <div class="date-filter-options">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="itemDateFilter" id="itemNewestFirst" value="newest" checked>
                                                    <label class="form-check-label" for="itemNewestFirst">
                                                        Newest First
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="itemDateFilter" id="itemOldestFirst" value="oldest">
                                                    <label class="form-check-label" for="itemOldestFirst">
                                                        Oldest First
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Availability Filter -->
                                        <div class="filter-section d-none">
                                            <h6>Availability</h6>
                                            <div class="availability-filter">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="itemInStock" checked>
                                                    <label class="form-check-label" for="itemInStock">
                                                        In Stock
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="itemOutOfStock">
                                                    <label class="form-check-label" for="itemOutOfStock">
                                                        Out of Stock
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="advanced-filter-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="itemResetFilters">Reset All</button>
                                        <button type="button" class="btn btn-primary" id="itemApplyFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Accessory Selection Modal -->
                            <div class="item-selection-modal" id="accessorySelectionModal">
                                <div class="item-selection-modal-content">
                                    <div class="item-selection-modal-header">
                                        <h5><i class="fa fa-plus-circle mr-2"></i>Select Accessory</h5>
                                    </div>
                                    <div class="item-selection-modal-body">
                                        <div class="item-options" id="accessoryOptions"></div>
                                    </div>
                                    <div class="item-selection-modal-footer">
                                        <button type="button" class="btn btn-secondary" id="closeAccessorySelectionModal">Close</button>
                                        <button type="button" class="btn btn-primary" id="confirmSelectAccessory" disabled>Add Accessory</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-success waves-effect waves-light btn-sb-form" id="edit_order_btn">
                                    Update Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var product_shipping_edit_data = <?= json_encode($product_shipping_data, JSON_HEX_TAG) ?>;
    var product_shipping_data = (product_shipping_edit_data.length > 0) ? product_shipping_edit_data : []; //product_shipping_data for delivery data calculation
    var shit_capa = ['26', '63', '129']; // Use JavaScript array for seating capacity for sofa
</script>
<?php
define('inc_panel_footer', true);
include PATH . '/inc/footer.php';
?>
<script>
    loading_div(); // Show loading first time
    document.addEventListener('DOMContentLoaded', function() {
        // Check if screen is large (lg breakpoint typically 992px+)
        const isLargeScreen = window.matchMedia('(min-width: 992px)').matches;

        if (isLargeScreen) {
            const btn = document.querySelector('.button-menu-mobile.open-left.waves-effect');
            if (btn) btn.click();
        }
    });
</script>
<script>
    // Safest approach - silently remove hash without interfering with Bootstrap
    function quietlyRemoveHash() {
        var currentHash = window.location.hash;
        if (currentHash && currentHash.includes('room')) {
            history.replaceState(null, null, ' ');
        }
    }

    // Run on page load
    $(document).ready(function() {
        quietlyRemoveHash();
    });

    // Run periodically to catch any hash changes
    setInterval(quietlyRemoveHash, 100);

    // Also run when URL changes
    window.addEventListener('popstate', quietlyRemoveHash);
</script>
<script>
    // Zoom In / Zoom Out Modals
    // Universal function to make any modal zoomable
    function makeModalZoomable(modalSelector, options = {}) {
        const defaults = {
            zoomButtonClass: 'zoom-toggle',
            zoomButtonHtml: '<i class="fa fa-expand"></i>',
            zoomButtonTitle: 'Toggle fullscreen',
            insertInHeader: true,
            customHeaderSelector: null
        };

        const config = {
            ...defaults,
            ...options
        };

        $(modalSelector).each(function() {
            const $modal = $(this);
            const modalId = $modal.attr('id');
            const zoomBtnId = `zoom-${modalId}`;

            // Check if zoom button already exists
            if ($modal.find(`.${config.zoomButtonClass}`).length > 0) {
                return; // Already zoomable
            }

            // Create zoom button
            const $zoomBtn = $(`
            <button type="button" class="${config.zoomButtonClass}" id="${zoomBtnId}" title="${config.zoomButtonTitle}">
                ${config.zoomButtonHtml}
            </button>
        `);

            // Insert zoom button in the modal header
            let $header;
            if (config.customHeaderSelector) {
                $header = $modal.find(config.customHeaderSelector);
            } else if (config.insertInHeader) {
                $header = $modal.find('.modal-header, .qualification-modal-header, .modal-header-class, [class*="header"]').first();
            }

            if ($header && $header.length) {
                // Create modal controls container if it doesn't exist
                let $controls = $header.find('.modal-controls');
                if ($controls.length === 0) {
                    $controls = $('<div class="modal-controls"></div>');
                    $header.append($controls);
                }
                $controls.append($zoomBtn);
            } else {
                // Fallback: prepend to modal content
                $modal.find('.modal-content, .qualification-modal-content').first().prepend($zoomBtn);
            }

            // Add zoom functionality
            $zoomBtn.on('click', function(e) {
                e.stopPropagation();
                const $btn = $(this);
                const $icon = $btn.find('i');

                if ($modal.hasClass('modal-fullscreen')) {
                    // Zoom out
                    $modal.removeClass('modal-fullscreen');
                    $icon.removeClass('fa-compress').addClass('fa-expand');
                    $btn.attr('title', 'Expand to fullscreen');
                } else {
                    // Zoom in
                    $modal.addClass('modal-fullscreen');
                    $icon.removeClass('fa-expand').addClass('fa-compress');
                    $btn.attr('title', 'Exit fullscreen');
                }
            });
        });
    }

    // Initialize zoom for all modals on document ready
    $(document).ready(function() {

        // You can also target specific modals
        makeModalZoomable('#productSelectModal');
        makeModalZoomable('#itemSelectionModal');

        // Add escape key support
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.modal-fullscreen').each(function() {
                    const $modal = $(this);
                    $modal.find('.zoom-toggle').trigger('click');
                });
            }
        });
    });
</script>
<script>
    // Global variables to track totals
    const orderTotals = {
        rooms: {},
        grandSubtotal: 0,
        grandTax: 0,
        grandTotal: 0
    };

    // function to calculate fitout product base price
    function calculateFitoutBasePrice($productContent, productId, roomId) {
        let baseTotal = 0;

        // Get base product dimensions and price
        const baseWidth = parseFloat($productContent.find('.dimension-width').val()) || 0;
        const baseLength = parseFloat($productContent.find('.dimension-length').val()) || 0;
        const baseQuantity = parseFloat($productContent.find('.product-qty').val()) || 1;
        const basePrice = (parseFloat($productContent.find('.unit-price').val()) || 0);
        const productDiscount = (parseFloat($productContent.find('.product-discount').val()) || 0);

        // Calculate base area and price
        const area = baseWidth * baseLength;
        baseTotal += basePrice * area * baseQuantity;

        // Apply discount
        const totalDiscount = baseTotal * productDiscount / 100;
        baseTotal -= totalDiscount;

        console.log('Fitout base calculation:', {
            baseWidth,
            baseLength,
            baseQuantity,
            basePrice,
            area,
            productDiscount,
            baseTotal
        });

        return baseTotal;
    }


    // function to calculate surcharges
    function calculateSurcharges($contentElement, baseTotal, productType = 'product') {
        let surchargeTotal = 0;

        // Calculate surcharges from checked checkboxes
        $contentElement.find('.surcharge-checkbox:checked').each(function() {
            const surchargeType = $(this).data('surcharge-type');
            const surchargeRate = parseFloat($(this).data('surcharge-rate')) || 0;

            let surchargeAmount = 0;

            // For percentage-based surcharges
            if (surchargeRate > 0) {
                surchargeAmount = baseTotal * (surchargeRate / 100);
            }

            if (surchargeType === 'plus') {
                surchargeTotal += surchargeAmount;
            } else if (surchargeType === 'minus') {
                surchargeTotal -= surchargeAmount;
            }

            console.log('Surcharge calculation:', {
                name: $(this).data('surcharge-name'),
                type: surchargeType,
                rate: surchargeRate,
                amount: surchargeAmount,
                surchargeTotal: surchargeTotal,
                baseTotal: baseTotal
            });
        });

        return surchargeTotal;
    }

    // Enhanced fitout product total calculation
    function calculateFitoutProductTotal($productContent, productId, roomId) {
        console.log('Calculating fitout product total for:', productId, 'room:', roomId);

        let total = 0;

        // Calculate base product cost
        total += calculateFitoutBasePrice($productContent, productId, roomId);

        // Calculate items cost
        $productContent.find('.items-tab').each(function() {
            const itemId = $(this).data('item-id');
            const $itemContent = $(`#item-${itemId}-${productId}-room${roomId}`);

            if ($itemContent.length) {
                const itemTotal = calculateItemTotal($itemContent, itemId, productId, roomId);
                total += itemTotal;

                console.log('Fitout item total:', {
                    itemId,
                    itemTotal,
                    runningTotal: total
                });
            }
        });

        console.log('Fitout product base total before surcharges:', total);

        // Calculate surcharges for the fitout product
        const surchargeTotal = calculateSurcharges($productContent, total, 'fitout');
        total += surchargeTotal;

        console.log('Final fitout product total:', total);
        return total;
    }

    // Enhanced function to calculate product total with better product type detection
    function calculateProductTotal(productId, roomId, productType = null) {

        console.log('Calculating product total for:', {
            productId,
            roomId,
            productType
        });

        // Cache the product content element
        const $productContent = $(`#product-${productId}-room${roomId}`);
        if (!$productContent.length) {
            console.log('Product content not found');
            return 0;
        }

        let total = 0;

        try {
            // If productType is not provided, detect it automatically
            if (!productType) {
                productType = detectProductType($productContent, productId, roomId);
            }

            console.log('Detected product type:', productType);

            const $variantsSection = $productContent.find('.product-variants-section');
            const $variantsRadioContainer = $productContent.find('.variant-radio-container');

            // Use a single check for product type instead of multiple if-else
            switch (productType) {
                case 'fitout':
                    total = calculateFitoutProductTotal($productContent, productId, roomId);
                    break;
                case 'curtain':
                    // Check if it's a variant-based product
                    if ($variantsSection.length > 0) {
                        total = calculateCurtainVariantTotal($productContent, productId, roomId);
                    } else {
                        total = calculateCurtainProductTotal($productContent, productId, roomId);
                    }
                    break;
                default:
                    // Check if it's a variant-based product
                    if ($variantsSection.length > 0 && $variantsRadioContainer.length == 0) {
                        total = calculateVariantBasedProductTotal($productContent, productId, roomId, productType);
                    } else {
                        total = calculateStandardProductTotal($productContent, productId, roomId);
                    }
            }

            // Calculate surcharges
            const surchargeTotal = calculateSurcharges($productContent, total, productType);
            total += surchargeTotal;

            // Update display
            updateProductTotalDisplay(productId, roomId, total);

            return total;

        } catch (error) {
            console.error('Error in calculateProductTotal:', error);
            return 0;
        }
    }

    // New function to detect product type reliably
    function detectProductType($productContent, productId, roomId) {
        // Method 1: Check the active product tab data
        const $activeTab = $(`#product-${productId}-room${roomId}-tab`);
        if ($activeTab.length) {
            const tabProductType = $activeTab.data('type');
            if (tabProductType) {
                return tabProductType;
            }
        }

        // Method 2: Check product content structure
        if ($productContent.find('.items-tabs-container').length > 0) {
            return 'fitout';
        }

        if ($productContent.find('.curtain-options-section').length > 0) {
            return 'curtain';
        }

        // Method 3: Check for variant sections
        if ($productContent.find('.product-variants-section').length > 0) {
            // Check if it's a curtain variant
            if ($productContent.find('.curtain-options-section').length > 0) {
                return 'curtain';
            }
            return 'product'; // Default for variant products
        }

        // Method 4: Fallback to checking the closest product tab
        const $closestTab = $productContent.closest('.tab-pane').find(`.product-tab[data-product="${productId}"]`);
        if ($closestTab.length) {
            const closestType = $closestTab.data('type');
            if (closestType) {
                return closestType;
            }
        }

        // Default fallback
        return 'product';
    }

    function calculateMaterialCosts($contentElement) {
        let materialTotal = 0;

        // Calculate non-pillow materials (regular material groups)
        $contentElement.find('.material-group .material-inputs-compact').each(function() {
            const $materialGroup = $(this);
            const areaWeight = parseFloat($materialGroup.find('.area-weight').val()) || 0;
            const fabricLength = parseFloat($materialGroup.find('.curtain-fabric-length').val()) || 0;
            const fabricHeight = parseFloat($materialGroup.find('.curtain-fabric-height').val()) || 0;
            let unitPrice = parseFloat($materialGroup.find('.material-type-select option:selected').data('price')) || 0;
            const standardMaterialUnitPrice = parseFloat($materialGroup.find('.material-type-select').data('standard-material-price')) || 0;

            const $replacementContainer = $materialGroup.closest('.material-grid').find('.material-inputs-compact-replacement');
            if ($replacementContainer.length) {
                const hasReplacementType = $replacementContainer.find('.material-type-replacement').length > 0 &&
                    $replacementContainer.find('.material-type-replacement').val() !== '';
                const hasReplacement = $replacementContainer.find('.material-replacement').length > 0 &&
                    $replacementContainer.find('.material-replacement').val() !== '';
                console.log('hasReplacementType:', hasReplacementType);
                console.log('hasReplacement:', hasReplacement);
                if (hasReplacementType && hasReplacement) {
                    const unitPriceReplacement = parseFloat($replacementContainer.find('.material-replacement option:selected').data('price')) || 0;
                    console.log('unitPriceReplacement:', unitPriceReplacement);
                    unitPrice = unitPriceReplacement;
                }
            }

            if (fabricLength > 0 && fabricHeight > 0) {
                const fabricArea = (fabricLength * fabricHeight) / 1000;
                materialTotal += (fabricArea * unitPrice);
            } else {
                materialTotal += areaWeight * (unitPrice - standardMaterialUnitPrice);
            }
        });

        // Calculate pillow materials from the new label-based structure
        $contentElement.find('.pillow-material-group .pillow-material-inputs-compact').each(function() {
            const $pillowGroup = $(this);
            const label = $pillowGroup.data('label') || '';
            const subcategory = $pillowGroup.data('subcategory') || 'default';
            const mt_of = $pillowGroup.data('mt_of') || subcategory;

            const quantity = parseFloat($pillowGroup.find('.pillow-quantity').val()) || 1;
            const length = parseFloat($pillowGroup.find('.pillow-length').val()) || 0;
            const width = parseFloat($pillowGroup.find('.pillow-width').val()) || 0;
            const newMaterialPrice = parseFloat($pillowGroup.find('.material-type-select option:selected').data('price')) || 0;
            const selectedMatPrice = parseFloat($pillowGroup.find('.material-type-select').data('standard-material-price')) || 0;

            let selectedMatPrdPrice = 0;
            let newMatPrdPrice = 0;

            let pipingLength = 0;
            let area = 0;
            let lengthM = length / 100;
            let widthM = width / 100;

            if (mt_of === "piping" || mt_of === "pipping") {
                pipingLength = (widthM + lengthM) * 2 * 0.3;
            } else {
                area = lengthM * widthM;
            }

            if (subcategory === "default") {
                // Double-sided pillow price calculation for default
                selectedMatPrdPrice = selectedMatPrice * area * quantity * 2;
                newMatPrdPrice = newMaterialPrice * area * quantity * 2;
            } else {
                if (mt_of === "piping" || mt_of === "pipping") {
                    selectedMatPrdPrice = pipingLength * selectedMatPrice * quantity;
                    newMatPrdPrice = pipingLength * newMaterialPrice * quantity;
                } else {
                    selectedMatPrdPrice = area * selectedMatPrice * quantity;
                    newMatPrdPrice = area * newMaterialPrice * quantity;
                }
            }

            materialTotal += (newMatPrdPrice - selectedMatPrdPrice);
        });

        console.log('Final materials cost:', {
            materialTotal: materialTotal
        });

        return materialTotal;
    }

    // Update the product total display
    function updateProductTotalDisplay(productId, roomId, total) {
        const $productTotal = $(`#product-total-${productId}-room${roomId}`);
        $productTotal.text(total.toFixed(2));
        console.log(`Updated product ${productId} total: $${total.toFixed(2)}`);

        // Update room totals
        updateRoomTotals(roomId);
    }

    // Calculate variant-based products
    function calculateVariantBasedProductTotal($productContent, productId, roomId, productType) {
        let total = 0;

        $productContent.find('.product-variant-content').each(function() {
            total += calculateVariantTotal($(this), productId, roomId);
        });

        return total;
    }

    // Function to calculate standard product total
    function calculateStandardProductTotal($productContent, productId, roomId) {
        let total = 0;

        // Check if product has variants
        const hasVariants = $productContent.find('.product-variants-section').length > 0;

        if (hasVariants) {
            // $productContent.find('.product-variant-content.active').each(function() {});
            // Calculate total for all variants
            $productContent.find('.product-variant-content.active').each(function() {
                total += calculateVariantTotal($(this), productId, roomId);
            });
        } else {
            // Calculate single product total
            total += calculateSingleProductTotal($productContent, productId, roomId);
        }

        return total;
    }

    function calculateVariantTotal($variantContent, productId, roomId) {
        let variantTotal = 0;

        // Get dimensions and quantity
        const width = parseFloat($variantContent.find('.dimension-width').val()) || 0;
        const length = parseFloat($variantContent.find('.dimension-length').val()) || 0;
        const height = parseFloat($variantContent.find('.dimension-height').val()) || 0;
        const quantity = parseFloat($variantContent.find('.product-qty').val()) || 1;
        const calculateType = $variantContent.find('.calculate-type').val() || '';

        // Get base price
        const basePrice = (parseFloat($variantContent.find('.unit-price').val()) || 0);
        const productDiscount = (parseFloat($variantContent.find('.product-discount').val()) || 0);

        // Calculate product price based on dimensions and calculation type
        const productPrice = calculatePrice(calculateType, basePrice, width, length, height, quantity);

        // Calculate material costs
        const materialCost = calculateMaterialCosts($variantContent);

        // Calculate variant total
        variantTotal = productPrice + materialCost;
        const variantTotalDiscount = variantTotal * productDiscount / 100;
        variantTotal -= variantTotalDiscount;

        console.log('Standard variant total calculation:', {
            width,
            length,
            height,
            quantity,
            basePrice,
            calculateType,
            productPrice,
            materialCost,
            productDiscount,
            variantTotalDiscount,
            variantTotal
        });

        return variantTotal;
    }

    // Function to handle curtain variant calculations
    function calculateCurtainVariantTotal($productContent, productId, roomId) {
        console.log('Calculating curtain variant total for:', productId, 'room:', roomId);
        let total = 0;
        // First calculate the standard variant total (like other products)
        // $productContent.find('.product-variant-content.active').each(function() {});
        $productContent.find('.product-variant-content').each(function() {
            total += calculateVariantTotal($(this), productId, roomId);
            console.log('Curtain variant base total:', total);

            // Then add curtain-specific charges
            const quantity = parseFloat($(this).find('.product-qty').val()) || 1;

            // Calculate motor charge
            const $openWithSelect = $(this).find('.open-with');
            if ($openWithSelect.length && $openWithSelect.val() === 'motor') {
                const motorCharge = 300 * quantity;
                total += motorCharge;
                console.log('Motor charge added:', motorCharge);
            }

            // Calculate installation charge
            const $installationCheckbox = $(this).find('.curtain-installation-needed-checkbox');
            if ($installationCheckbox.length && $installationCheckbox.is(':checked')) {
                const installationCharge = 200 * quantity;
                total += installationCharge;
                console.log('Installation charge added:', installationCharge);
            }

            // Calculate accessories cost for curtain variant
            let accessoriesTotal = 0;
            $(this).find('.accessory-tab').each(function() {
                const accessoryId = $(this).data('accessory-id');
                const accessoryTotal = calculateAccessoryTotal($(this), quantity);
                accessoriesTotal += accessoryTotal;
            });

            total += accessoriesTotal;
            console.log('Accessories total:', accessoriesTotal);
            console.log('Final curtain variant total:', total);
        });

        return total;
    }

    // Optimized curtain product total calculation
    function calculateCurtainProductTotal($productContent, productId, roomId) {
        console.log('Calculating curtain product total for:', productId, 'room:', roomId);

        // First calculate as standard product
        let total = 0;
        const productDiscount = (parseFloat($productContent.find('.product-discount').val()) || 0);
        // Calculate material costs
        const materialCost = calculateMaterialCosts($productContent);

        // Calculate total (base price × area × quantity + material costs)
        total += materialCost;

        console.log('Curtain product base total:', total);

        // Then add curtain-specific charges
        const quantity = parseFloat($productContent.find('.product-qty').val()) || 1;

        // Calculate motor charge
        const $openWithSelect = $productContent.find('.open-with');
        if ($openWithSelect.length && $openWithSelect.val() === 'motor') {
            const motorCharge = 300 * quantity;
            total += motorCharge;
            console.log('Motor charge added:', motorCharge);
        }

        // Calculate installation charge
        const $installationCheckbox = $productContent.find('.curtain-installation-needed-checkbox');
        if ($installationCheckbox.length && $installationCheckbox.is(':checked')) {
            const installationCharge = 200 * quantity;
            total += installationCharge;
            console.log('Installation charge added:', installationCharge);
        }

        // Calculate accessories cost
        let accessoriesTotal = 0;
        $productContent.find('.accessory-tab').each(function() {
            const accessoryId = $(this).data('accessory-id');
            const accessoryTotal = calculateAccessoryTotal($(this), quantity);
            accessoriesTotal += accessoryTotal;
        });

        total += accessoriesTotal;
        console.log('Accessories total:', accessoriesTotal);

        const totalDiscount = total * productDiscount / 100;
        console.log('Discount:', totalDiscount);
        total -= totalDiscount;
        console.log('Final curtain product total:', total);

        return total;
    }

    // Function to calculate single product total (without variants)
    function calculateSingleProductTotal($productContent, productId, roomId) {
        let total = 0;

        // Get dimensions and quantity
        const width = parseFloat($productContent.find('.dimension-width').val()) || 0;
        const length = parseFloat($productContent.find('.dimension-length').val()) || 0;
        const height = parseFloat($productContent.find('.dimension-height').val()) || 0;
        const quantity = parseFloat($productContent.find('.product-qty').val()) || 1;
        const calculateType = $productContent.find('.calculate-type').val() || '';

        // Get base price
        const basePrice = (parseFloat($productContent.find('.unit-price').val()) || 0);
        const productDiscount = (parseFloat($productContent.find('.product-discount').val()) || 0);

        const productPrice = calculatePrice(calculateType, basePrice, width, length, height, quantity);

        // Calculate material costs
        const materialCost = calculateMaterialCosts($productContent);

        // Calculate total (base price × area × quantity + material costs)
        total = productPrice + materialCost;
        const totalDiscount = total * productDiscount / 100;
        total -= totalDiscount;

        console.log('Single product total calculation:', {
            width,
            length,
            height,
            quantity,
            basePrice,
            materialCost,
            total
        });

        return total;
    }

    // Enhanced accessory calculation that works for both products and variants
    function calculateAccessoryTotal($accessoryTab, productQuantity) {
        const accessoryId = $accessoryTab.data('accessory-id');

        // Find the closest product or variant content
        const $parentContent = $accessoryTab.closest('.product-content, .variant-details');
        const parentId = $parentContent.attr('id');

        let accessoryContentId;
        let $accessoryContent;

        if ($parentContent.hasClass('variant-details')) {
            // This is a variant accessory
            const productId = $parentContent.closest('.product-content').attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const variantId = $parentContent.find('.product-qty').first().data('variant');
            const roomId = $parentContent.closest('[id*="room"]').attr('id').match(/room(\d+)/)[1];
            accessoryContentId = `accessory-${accessoryId}-${productId}-${variantId}-room${roomId}`;
            $accessoryContent = $parentContent.find(`#${accessoryContentId}`);
        } else {
            // This is a regular product accessory
            const productId = parentId.replace('product-', '').replace(/-room\d+$/, '');
            const roomId = parentId.match(/room(\d+)/)[1];
            accessoryContentId = `accessory-${accessoryId}-${productId}-room${roomId}`;
            $accessoryContent = $parentContent.find(`#${accessoryContentId}`);
        }

        if (!$accessoryContent.length) {
            console.log('Accessory content not found:', accessoryContentId);
            return 0;
        }

        // Get selected accessory option and its price
        const $selectedOption = $accessoryContent.find('.accessory-options-select option:selected');
        const accessoryPrice = parseFloat($selectedOption.data('price')) || 0;
        const priceType = $selectedOption.data('price-type') || ''; // fixed or per_unit
        const priceDependsOn = $selectedOption.data('price-depends-on') || ''; // e.g., 'quantity', 'length', etc.

        let accessoryTotal = 0;

        if (priceType === 'Per Piece') {
            // Price is per unit, multiply by product quantity
            accessoryTotal = accessoryPrice * productQuantity;
        } else if (priceType === 'Per Meter') {
            $multiplier = 0.2; // Default multiplier
            if (priceDependsOn === 'length') {
                $length = parseFloat($parentContent.find('.curtain-fabric-length').val()) || 0;
                accessoryTotal = accessoryPrice * $length * $multiplier * productQuantity;
            } else if (priceDependsOn === 'height') {
                $height = parseFloat($parentContent.find('.curtain-fabric-height').val()) || 0;
                accessoryTotal = accessoryPrice * $height * $multiplier * productQuantity;
            } else if (priceDependsOn === 'length and height') {
                $length = parseFloat($parentContent.find('.curtain-fabric-length').val()) || 0;
                $height = parseFloat($parentContent.find('.curtain-fabric-height').val()) || 0;
                accessoryTotal = accessoryPrice * $length * $height * ($multiplier * $multiplier) * productQuantity;
            }
        } else {
            // Default to fixed price
            accessoryTotal = accessoryPrice;
        }

        console.log('Accessory calculation:', {
            accessoryId,
            accessoryPrice,
            priceType,
            productQuantity,
            accessoryTotal
        });

        return accessoryTotal;
    }

    // Enhanced function to calculate curtain motor charge
    function calculateCurtainMotorCharge($productContent, productId, roomId) {
        let motorCharge = 0;

        // Check if motor is selected in curtain options
        const $openWithSelect = $productContent.find('.open-with');
        if ($openWithSelect.length && $openWithSelect.val() === 'motor') {
            motorCharge = 300; // $300 charge for motor
            console.log('Motor charge applied: $300');
        }

        return motorCharge;
    }

    // Function to calculate item total
    function calculateItemTotal($itemContent, itemId, productId, roomId) {
        let itemTotal = 0;

        // Get item dimensions and quantity
        const width = parseFloat($itemContent.find('.item-width').val()) || 0;
        const length = parseFloat($itemContent.find('.item-length').val()) || 0;
        const height = parseFloat($itemContent.find('.item-height').val()) || 0;
        const quantity = parseFloat($itemContent.find('.item-qty').val()) || 1;
        const calculateType = $itemContent.find('.item-calculate-type').val() || '';

        // Get base price
        const basePrice = (parseFloat($itemContent.find('.item-unit-price').val()) || 0);
        const itemDiscount = (parseFloat($itemContent.find('.item-discount').val()) || 0);

        // Calculate item price based on calculation type
        const itemPrice = calculatePrice(calculateType, basePrice, width, length, height, quantity);

        // Calculate material costs for the item
        const materialCost = calculateMaterialCosts($itemContent);

        // Calculate item total
        itemTotal = itemPrice + materialCost;
        const itemTotalDiscount = itemTotal * itemDiscount / 100;
        itemTotal -= itemTotalDiscount;

        console.log('Item total calculation:', {
            itemId,
            width,
            length,
            height,
            quantity,
            basePrice,
            calculateType,
            itemPrice,
            materialCost,
            itemDiscount,
            itemTotal
        });

        return itemTotal;
    }

    // Function to update room totals
    function updateRoomTotals(roomId) {
        let roomSubtotal = 0;

        // Calculate total for all products in the room
        $(`#productTabs-room${roomId} .product-tab`).each(function() {
            const productId = $(this).data('product');
            const productType = $(this).data('type');
            const productTotal = parseFloat($(`#product-total-${productId}-room${roomId}`).text()) || 0;
            roomSubtotal += productTotal;
        });

        // Store room total
        orderTotals.rooms[roomId] = roomSubtotal;

        // Update grand totals
        updateGrandTotals();
    }

    // Function to update grand totals
    function updateGrandTotals() {
        let grandSubtotal = 0;

        // Sum all room totals
        Object.values(orderTotals.rooms).forEach(roomTotal => {
            grandSubtotal += roomTotal;
        });

        // Get tax percentage and calculate tax
        const taxPercentage = parseFloat($('#lblOrderTax').val()) || 0;
        const grandTax = grandSubtotal * (taxPercentage / 100);
        const grandTotal = grandSubtotal + grandTax;

        // Update global state
        orderTotals.grandSubtotal = grandSubtotal;
        orderTotals.grandTax = grandTax;
        orderTotals.grandTotal = grandTotal;

        // Update UI
        $('#grand-subtotal').text(grandSubtotal.toFixed(2));
        $('#grand-tax').text(grandTax.toFixed(2));
        $('#grand-total').text(grandTotal.toFixed(2));
        $('#tax-percentage').text(taxPercentage);

        console.log('Grand totals updated:', orderTotals);
    }

    // Common function to call whenever there are changes
    function updateOrderTotals() {
        console.log('Updating all order totals...');

        // Recalculate totals for all rooms and products
        $('.tab-pane').each(function() {
            const roomId = $(this).attr('id');
            if (roomId && roomId.startsWith('room')) {
                updateRoomTotals(roomId.replace('room', ''));
            }
        });
    }

    /**
     * Determines if a dimension input field should be disabled 
     * based on the product's calculate type.
     * @param {string} calculateType - e.g., "standart", "boy", "en", "yuksek", "enboy", "yukseken", "yuksekboy", "hepsi"
     * @param {string} field - "width", "length", or "height"
     * @returns {string} - "disabled" or ""
     */
    function getDisabledAttr(calculateType, field) {
        const activeFields = {
            standart: [],
            boy: ["l"],
            en: ["w"],
            yuksek: ["h"],
            enboy: ["w", "l"],
            yukseken: ["w", "h"],
            yuksekboy: ["l", "h"],
            hepsi: ["w", "l", "h"]
        };

        const allowed = activeFields[calculateType] || [];
        return allowed.includes(field) ? "" : "readonly";
    }

    /**
     * Calculates unit price based on the product's calculate type.
     * @param {string} calculateType 
     * @param {number} standartPrice 
     * @param {object} dims - {standart_width, standart_length, standart_height, width, length, height}
     * @returns {number} - unit price
     */
    function calculateUnitPrice(calculateType, dims) {
        const sw = parseFloat(dims.width || 1);
        const sl = parseFloat(dims.length || 1);
        const sh = parseFloat(dims.height || 1);
        const standartPrice = parseFloat(dims.standart_price || 0);

        switch (calculateType) {
            case 'boy':
                return standartPrice / sl;
            case 'en':
                return standartPrice / sw;
            case 'yuksek':
                return standartPrice / sh;
            case 'enboy':
                return standartPrice / (sw * sl);
            case 'yukseken':
                return standartPrice / (sw * sh);
            case 'yuksekboy':
                return standartPrice / (sl * sh);
            case 'hepsi':
                return standartPrice / (sw * sl * sh);
            case 'standart':
            default:
                return standartPrice;
        }
    }

    function calculatePrice(calculateType, unitPrice, customWidth, customLength, customHeight, quantity = 1) {
        // Parse custom dimensions (ensure they are numbers, default to 0 if invalid)
        const cw = parseFloat(customWidth) || 0;
        const cl = parseFloat(customLength) || 0;
        const ch = parseFloat(customHeight) || 0;
        const qty = parseFloat(quantity) || 1;

        let finalPrice = 0;

        switch (calculateType) {
            case 'boy': // Length-based
                finalPrice = unitPrice * cl;
                break;

            case 'en': // Width-based
                finalPrice = unitPrice * cw;
                break;

            case 'yuksek': // Height-based
                finalPrice = unitPrice * ch;
                break;

            case 'enboy': // Area (width × length)
                finalPrice = unitPrice * (cw * cl);
                break;

            case 'yukseken': // Area (width × height)
                finalPrice = unitPrice * (cw * ch);
                break;

            case 'yuksekboy': // Area (length × height)
                finalPrice = unitPrice * (cl * ch);
                break;

            case 'hepsi': // Volume (width × length × height)
                finalPrice = unitPrice * (cw * cl * ch);
                break;

            case 'standart':
            default:
                finalPrice = unitPrice;
                break;
        }

        // Multiply by quantity
        const totalPrice = finalPrice * qty;

        console.log('Price calculation:', {
            calculateType,
            unitPrice,
            dimensions: {
                width: cw,
                length: cl,
                height: ch
            },
            quantity: qty,
            calculatedPrice: finalPrice,
            totalPrice
        });

        return totalPrice;
    }

    // function to update curtain installation price
    function updateCurtainInstallationPrice($checkbox) {
        const staticPrice = 200;
        const $priceLabel = $checkbox.closest('.curtain-control').find('.installation-price');

        if ($checkbox.is(':checked')) {
            $priceLabel.text(`+ $${staticPrice}`).show();
        } else {
            $priceLabel.hide();
        }

        // Trigger recalculation
        const $productContent = $checkbox.closest('.product-content, .variant-details');
        if ($productContent.length) {
            const productId = $productContent.closest('[id*="product-"]').attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const roomId = $productContent.closest('[id*="room"]').attr('id').match(/room(\d+)/)[1];

            calculateProductTotal(productId, roomId, 'curtain');
        }
    }

    // Optimized curtain installation handler
    $(document).on('change', '.curtain-installation-needed-checkbox', function() {
        const $checkbox = $(this);

        // Update price display immediately
        updateCurtainInstallationPrice($checkbox);
    });

    // Optimized motor selection handler
    $(document).on('change', '.open-with', function() {
        const $productContent = $(this).closest('.product-content, .variant-details');
        if ($productContent.length) {
            const productId = $productContent.closest('[id*="product-"]').attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const roomId = $productContent.closest('[id*="room"]').attr('id').match(/room(\d+)/)[1];

            console.log('Open with changed, recalculating...');
            calculateProductTotal(productId, roomId, 'curtain');
        }
    });
</script>
<script>
    let currentCountProduct = 0;
    let currentCountItem = 0;
    let searchType = '';
    let sofasubmenu = false;
    // Global variable to store order data
    var currentOrderData = null;
    // State management
    const state = {
        rooms: [],
        currentRoom: null,
        selectedMainCategory: null,
        selectedQualification: null,
        selectedProducts: [],
        selectedMaterialCategory: null,
        selectedPillowSubcategory: null,
        currentProductType: null,
        selectedItems: [],
        selectedAccessory: null,
        selectedAccessoryAttrId: null,
        currentProductId: null,
        currentProductName: null
    };

    // Filter state management
    const filterState = {
        product: {
            selectedBrand: '', // 0 means "All Brands"
            selectedStyles: [],
            minPrice: 0,
            maxPrice: 50000,
            dateSort: 'newest',
            inStock: true,
            outOfStock: false,
            searchTerm: ''
        },
        item: {
            selectedBrand: '', // 0 means "All Brands"
            selectedStyles: [],
            minPrice: 0,
            maxPrice: 50000,
            dateSort: 'newest',
            inStock: true,
            outOfStock: false,
            searchTerm: ''
        }
    };

    const bedSizeMap = {
        "120200": "120x200",
        "140200": "140x200",
        "160200": "160x200",
        "180200": "180x200",
        "200200": "200x200"
    };

    const roomColors = [
        'linear-gradient(135deg, #4361ee, #3a0ca3)', // Blue
        'linear-gradient(135deg, #f72585, #b5179e)', // Pink
        'linear-gradient(135deg, #4cc9f0, #4895ef)', // Light Blue
        'linear-gradient(135deg, #ff9a00, #ff6b6b)', // Orange
        'linear-gradient(135deg, #4ecdc4, #44a08d)', // Teal
        'linear-gradient(135deg, #7209b7, #3a0ca3)', // Purple
        'linear-gradient(135deg, #f48c06, #dc2f02)', // Red-Orange
        'linear-gradient(135deg, #2a9d8f, #264653)', // Green
        'linear-gradient(135deg, #e63946, #a8dadc)', // Red-Teal
        'linear-gradient(135deg, #ffafcc, #cdb4db)', // Pastel
        'linear-gradient(135deg, #ff6b6b, #ee5a52)', // Coral
        'linear-gradient(135deg, #a8e6cf, #56ab2f)', // Mint Green
        'linear-gradient(135deg, #ffd166, #ff9e00)', // Gold
        'linear-gradient(135deg, #118ab2, #073b4c)', // Deep Blue
        'linear-gradient(135deg, #9d4edd, #5a189a)' // Violet
    ];

    // Curtain accessory options
    const curtainAccessoryTypes = [{
            id: 'side_holder',
            name: 'Side Holder',
            description: 'Curtain side holders and accessories',
            icon: 'fa-grip-lines-vertical',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)' // Purple gradient
        },
        {
            id: 'black_out',
            name: 'Black Out',
            description: 'Black out lining and accessories',
            icon: 'fa-moon',
            color: 'linear-gradient(135deg, #2b2d42, #1d1e2c)' // Dark blue/black gradient
        },
        {
            id: 'decorative_rail',
            name: 'Decorative Rail',
            description: '',
            icon: 'fa-grip-lines',
            color: 'linear-gradient(135deg, #f72585, #b5179e)' // Pink gradient
        },
        {
            id: 'ropeholder',
            name: 'Rope holder',
            description: '',
            icon: 'fa-rope',
            color: 'linear-gradient(135deg, #4cc9f0, #4895ef)' // Light blue gradient
        },
        {
            id: 'box',
            name: 'Box',
            description: '',
            icon: 'fa-box',
            color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)' // Orange/red gradient
        },
        {
            id: 'motor',
            name: 'Motor',
            description: '',
            icon: 'fa-cog',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)' // Teal gradient
        }
    ];

    // Add these CSS styles
    const searchStyles = `
      .product-loading, .product-error, .no-products-found {
         grid-column: 1 / -1;
         text-align: center;
         padding: 20px;
         color: #6c757d;
      }
      .product-loading i, .product-error i, .no-products-found i {
         font-size: 2rem;
         margin-bottom: 10px;
         display: block;
      }
      .product-error {
         color: #dc3545;
      }
      .no-products-found {
         color: #17a2b8;
      }
   `;

    let allCategoryMaterials = [];

    function loadAllMaterials() {
        $.post(ajax_url + '/api', {
            get_materials_by_category: 1,
            category: 'all'
        }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            if (response.status === 'success') {
                allCategoryMaterials = response.data;
            }
        });
    }
    loadAllMaterials();

    // Function to get materials by category
    function getMaterials(category) {
        // Filter to get all materials with the matching category
        return allCategoryMaterials.filter(mat => mat.material_category == category) || [];
    }

    // Function to load order data
    function loadOrderData(orderId) {
        $.post(ajax_url + '/api', {
            get_order_for_edit: 1,
            order_id: orderId
        }, function(data) {
            try {
                var response = typeof data === 'string' ? JSON.parse(data) : data;
                if (response.status === 'success') {
                    currentOrderData = response.data;
                    console.log(currentOrderData);

                    // Use Promise to track when all data is loaded
                    loadAllOrderData()
                        .then(() => {
                            // All data loaded successfully
                            console.log('All order data loaded completely');
                            loading_div(); // Hide loading second time when everything is done
                        })
                        .catch((error) => {
                            console.error('Error loading order data:', error);
                            notify_it('error', 'Error loading order data: ' + error.message);
                            loading_div();
                        });

                } else {
                    console.error('Error loading order: ', response.message);
                    notify_it('error', 'Error loading order: ' + response.message);
                    loading_div();
                }
            } catch (e) {
                console.error('Error parsing order data:', e);
                notify_it('error', 'Error loading order data');
                loading_div();
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            notify_it('error', 'Network error loading order data');
            loading_div();
        });
    }

    // Main function to handle all asynchronous data loading with Promise
    function loadAllOrderData() {
        return new Promise((resolve, reject) => {
            try {
                if (!currentOrderData.order_details || currentOrderData.order_details.length === 0) {
                    console.log('No order details to load');
                    resolve(); // No data to load
                    return;
                }

                // Group details by room
                const rooms = {};
                currentOrderData.order_details.forEach(detail => {
                    const roomData = detail.room_data;
                    if (!roomData) return;

                    const roomNo = detail.product_room_index || '1';
                    const roomId = `room${roomNo}`;
                    if (!rooms[roomId]) {
                        rooms[roomId] = {
                            room_id: roomId,
                            floor_name: roomData.floor_name || '',
                            room_name: roomData.room_name || '',
                            room_image: roomData.room_image || '',
                            products: []
                        };
                    }

                    rooms[roomId].products.push({
                        detail_id: detail.detail_id,
                        product_id: detail.product_id,
                        quantity: detail.quantity,
                        discount: detail.discount,
                        detail_attr: detail.detail_attr,
                        curtain_data: detail.curtain_data,
                        fitout_items_data: detail.fitout_items_data,
                        detail_images: detail.detail_images,
                        room_data: detail.room_data,
                        product_notes_tr: detail.product_notes_tr,
                        product_notes_en: detail.product_notes_en
                    });
                });

                console.log('Rooms to populate:', rooms);

                // Create array of promises for all room and product loading operations
                const loadPromises = [];

                // Create rooms and collect product loading promises
                Object.values(rooms).forEach((room, index) => {
                    const roomNumber = index + 1;
                    console.log('Creating room from data:', {
                        roomData: room,
                        roomNumber: roomNumber
                    });

                    const roomLoadPromise = createRoomFromDataWithPromise(room, roomNumber);
                    loadPromises.push(roomLoadPromise);
                });

                // Wait for all rooms and products to be loaded
                Promise.all(loadPromises)
                    .then(() => {
                        console.log('All rooms and products loaded successfully');

                        // Initialize edit mode
                        initializeEditMode();

                        // Update totals after everything is loaded
                        setTimeout(() => {
                            updateOrderTotals();
                            console.log('Order totals updated after full data load');
                            resolve(); // Resolve when everything is complete
                        }, 1000);
                    })
                    .catch((error) => {
                        console.error('Error loading rooms or products:', error);
                        reject(error);
                    });

            } catch (error) {
                console.error('Error in loadAllOrderData:', error);
                reject(error);
            }
        });
    }

    // Function to create room from data that returns a Promise
    function createRoomFromDataWithPromise(roomData, roomNumber) {
        return new Promise((resolve, reject) => {
            try {
                if (roomNumber === 1) {
                    // Update existing first room
                    updateRoomData(roomNumber, roomData);
                } else {
                    // Add new room
                    addRoomForEdit(roomNumber, roomData);
                }

                // If no products in this room, resolve immediately
                if (!roomData.products || roomData.products.length === 0) {
                    console.log(`No products to load for room ${roomNumber}`);
                    resolve();
                    return;
                }

                // Collect all product loading promises for this room
                const productPromises = [];

                // Add products to room
                roomData.products.forEach((productData, index) => {
                    const productPromise = new Promise((productResolve, productReject) => {
                        // Stagger product loading to avoid overwhelming the system
                        setTimeout(() => {
                            addProductToRoomForEditWithPromise(roomData.room_id, productData)
                                .then(() => {
                                    console.log(`Product ${index + 1} loaded for room ${roomNumber}`);
                                    productResolve();
                                })
                                .catch((error) => {
                                    console.error(`Error adding product ${index + 1} to room ${roomNumber}:`, error);
                                    productReject(error);
                                });
                        }, index * 200); // Stagger by 200ms per product
                    });
                    productPromises.push(productPromise);
                });

                // Wait for all products in this room to be loaded
                Promise.all(productPromises)
                    .then(() => {
                        console.log(`All ${productPromises.length} products loaded for room ${roomNumber}`);
                        resolve();
                    })
                    .catch((error) => {
                        console.error(`Error loading products for room ${roomNumber}:`, error);
                        // Even if some products fail, we still resolve to continue with other rooms
                        resolve();
                    });

            } catch (error) {
                console.error(`Error creating room ${roomNumber}:`, error);
                reject(error);
            }
        });
    }

    // Updated function to add product to room that returns a Promise
    function addProductToRoomForEditWithPromise(roomId, productData) {
        return new Promise((resolve, reject) => {
            console.log('Adding product to room for edit:', {
                roomId: roomId,
                productData: productData
            });

            // Ensure roomId is properly formatted
            const roomNumber = roomId.replace('room', '');
            state.currentRoom = roomNumber;

            // Get product info first
            $.post(ajax_url + '/api', {
                get_product: 1,
                product_id: productData.product_id
            }, function(data) {
                try {
                    var response = typeof data === 'string' ? JSON.parse(data) : data;
                    if (response.status === 'success') {
                        const product = response.data;
                        product.detail_id = productData.detail_id;

                        // Map the API data to expected format
                        const existingData = mapApiDataToExpectedFormat(productData);
                        product.existing_data = existingData;

                        console.log('Mapped existing data for product:', {
                            productId: product.product_id,
                            existingData: existingData
                        });

                        // Add product tab with explicit room number
                        addProductTab(roomNumber, product, existingData)
                            .then(() => {
                                console.log(`Product ${product.product_id} fully loaded for room ${roomNumber}`);
                                resolve();
                            })
                            .catch((error) => {
                                console.error(`Error in addProductTab for product ${product.product_id}:`, error);
                                reject(error);
                            });

                    } else {
                        reject(new Error(`Failed to load product data: ${response.message}`));
                    }
                } catch (e) {
                    console.error('Error loading product:', e);
                    reject(e);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX Error loading product:', status, error);
                reject(new Error(`Network error loading product: ${error}`));
            });
        });
    }

    function loadProductContentWithPromise(contentId, product, roomId, productInstanceId, existingData = null) {
        return new Promise((resolve) => {
            const $content = $(`#${contentId}`);
            const totalsHTML = createTotalsSection(productInstanceId, roomId);

            console.log('Loading product content with existing data:', {
                contentId,
                productId: product.product_id,
                roomId,
                existingData: existingData
            });

            // Check if product has variants
            if (product.has_variant && product.available_in === 'set') {
                $.post(ajax_url + '/api', {
                    get_product_variants: 1,
                    product_id: product.product_id
                }, function(data) {
                    var response = $.parseJSON(data);
                    if (response.status === 'success') {
                        const variants = response.data;
                        loadProductWithVariants($content, product, variants, roomId, existingData, productInstanceId);
                        $content.append(totalsHTML);
                        if (product.type === 'curtain' && existingData && existingData.curtain_data) {
                            variants.forEach((variant, index) => {
                                const blackout_color = existingData?.curtain_data ? existingData.curtain_data[index + 1].configuration.blackout_color : '';
                                if (existingData.curtain_data[index + 1] && existingData.curtain_data[index + 1].accessories) {
                                    existingData.curtain_data[index + 1].accessories.forEach(accessory => {
                                        selectedAccessory = curtainAccessoryTypes.find(type => type.id === accessory.type);
                                        console.log('Loading accessory for variant:', {
                                            variantId: variant.product_id,
                                            accessory: accessory,
                                            selectedAccessory: curtainAccessoryTypes.find(type => type.id === accessory.type)
                                        });
                                        $.post(ajax_url + '/api', {
                                            get_curtain_accessories: 1,
                                            accessory_type: selectedAccessory.name,
                                            attr_id: variant.attr_id
                                        }, function(data) {
                                            // console.log(data);
                                            var response = $.parseJSON(data);
                                            console.log('selectedProduct:', response);
                                            if (response.status === 'success') {
                                                // Find the selected product
                                                const accessoryOptions = response.data;

                                                if (accessoryOptions) {
                                                    addAccessoryToCurtainVariant(roomId, productInstanceId, variant.product_id, curtainAccessoryTypes.find(type => type.id === accessory.type), accessoryOptions, accessory, blackout_color);
                                                } else {
                                                    console.error('Could not find accessories');
                                                }
                                            }
                                        });
                                    });
                                }
                            });
                        }
                        setTimeout(() => {
                            variants.forEach(variant => {
                                setupMaterialTabsForProduct(product, variant, roomId, productInstanceId);
                                setupPillowSubcategoryTabsForProduct(product, variant, roomId, productInstanceId);
                            });
                            resolve();
                        }, 1000);
                    } else {
                        resolve();
                    }
                });
            } else if (product.available_in === 'size') {
                let variants = [];
                try {
                    const bedDimsJson = product?.product_bed_dims || product?.product_dims_data || '{}';
                    const bedDimsObject = JSON.parse(bedDimsJson);
                    variants = Object.entries(bedDimsObject).map(([key, value]) => ({
                        id: key,
                        size: key,
                        width: value.width || null,
                        length: value.length || null,
                        height: value.height || null,
                        standart_price: value.standart_price || null,
                        name: bedSizeMap[key] || `Size ${parseInt(key) + 1}`,
                        active_materials: product.active_materials || {}
                    }));
                } catch (error) {
                    console.error('Error parsing product_bed_dims:', error);
                    variants = [];
                }
                loadProductWithVariants($content, product, variants, roomId, existingData, productInstanceId);
                $content.append(totalsHTML);
                // Initialize pillow tabs after content is loaded
                setTimeout(() => {
                    variants.forEach(variant => {
                        setupMaterialTabsForProduct(product, variant, roomId, productInstanceId);
                        setupPillowSubcategoryTabsForProduct(product, variant, roomId, productInstanceId);
                    });
                    resolve();
                }, 1000);
            } else {
                // For simple products, fitout, and curtain products
                if (product.type === 'fitout') {
                    loadFitoutProductContent($content, product, roomId, existingData, productInstanceId);
                    if (existingData && existingData.items) {
                        // Load fitout items if existing data is present
                        existingData.items.forEach(existingItem => {
                            $.post(ajax_url + '/api', {
                                get_product: 1,
                                product_id: existingItem.item_id
                            }, function(data) {
                                // console.log(data);
                                var response = $.parseJSON(data);
                                console.log('selectedProduct:', response);
                                if (response.status === 'success') {
                                    // Find the selected product
                                    const selectedItem = response.data;

                                    if (selectedItem) {
                                        addItemToProduct(roomId, productInstanceId, selectedItem, existingItem);
                                        setTimeout(() => {
                                            resolve();
                                        }, 1000);
                                    } else {
                                        console.error('Could not find selected item');
                                    }
                                }
                            });
                        });
                    }
                } else if (product.type === 'curtain') {
                    loadCurtainProductContent($content, product, product, roomId, productInstanceId, existingData);
                } else {
                    loadSimpleProductContent($content, product, product, roomId, existingData, productInstanceId);
                }
                if (product.type === 'curtain' && existingData && existingData.curtain_data) {
                    existingData.curtain_data[1].accessories.forEach(accessory => {
                        const blackout_color = existingData?.curtain_data ? existingData.curtain_data[1].configuration.blackout_color : '';
                        selectedAccessory = curtainAccessoryTypes.find(type => type.id === accessory.type);
                        $.post(ajax_url + '/api', {
                            get_curtain_accessories: 1,
                            accessory_type: selectedAccessory.name,
                            attr_id: product.attr_id
                        }, function(data) {
                            // console.log(data);
                            var response = $.parseJSON(data);
                            if (response.status === 'success') {
                                // Find the selected product
                                const accessoryOptions = response.data;
                                console.log('accessoryOptions:', accessoryOptions);

                                if (accessoryOptions) {
                                    addAccessoryToProduct(roomId, productInstanceId, curtainAccessoryTypes.find(type => type.id === accessory.type), accessoryOptions, accessory, blackout_color);
                                } else {
                                    console.error('Could not find accessories');
                                }
                            }
                        });
                    });
                }

                $content.append(totalsHTML);
                setTimeout(() => {
                    setupMaterialTabsForProduct(product, product, roomId, productInstanceId);
                    setupPillowSubcategoryTabsForProduct(product, product, roomId, productInstanceId);
                    resolve();
                }, 1000);
            }
        });
    }

    // Function to update existing room data
    function updateRoomData(roomNumber, roomData) {
        const roomId = `room${roomNumber}`;
        $(`#floorName-${roomId}`).val(roomData.floor_name || '');
        $(`#roomName-${roomId}`).val(roomData.room_name || '');

        // Update room image if exists
        if (roomData.room_image) {
            const imageUrl = '<?php echo URL; ?>/uploads/' + roomData.room_image;
            $(`#imagePreview-${roomId}`).html(`<img src="${imageUrl}" alt="Room image" style="width:100%;height:100%;object-fit:cover;">`);
        }

        // Update room tab title
        const floorName = roomData.floor_name || '';
        const roomName = roomData.room_name || '';
        if (floorName && roomName) {
            $(`#${roomId}-tab .room-title`).text(`${floorName}-${roomName}`);
            $(`.add-item-to-room-btn[data-room="${roomNumber}"]`).text(`Add Item To ${floorName}-${roomName}`);
        }
    }

    // Function to add room for edit mode
    function addRoomForEdit(roomNumber, roomData) {
        const roomId = 'room' + roomNumber;
        const roomColor = roomColors[(roomNumber - 1) % roomColors.length];
        const mainColor = roomColor.split(',')[1].trim();

        const $tabLi = $(`
            <li class="nav-item">
                <a class="nav-link room-tab"
                id="${roomId}-tab"
                data-toggle="tab"
                href="#${roomId}"
                role="tab"
                aria-controls="${roomId}"
                data-room="${roomNumber}">
                <div class="room-header" style="background: ${roomColor};">
                    <span class="status-indicator status-empty"></span>
                    <span class="room-title">${roomData.floor_name && roomData.room_name ? `${roomData.floor_name}-${roomData.room_name}` : `Room ${roomNumber}`}</span>
                    <span class="close-room ml-2" title="Remove room">
                        <i class="fa fa-times"></i>
                    </span>
                </div>
                </a>
            </li>
        `);
        $('#roomTabs .nav-item:has(.add-room-btn)').before($tabLi);

        const $pane = $(`
            <div class="tab-pane fade"
                id="${roomId}"
                role="tabpanel"
                aria-labelledby="${roomId}-tab"
                data-room="${roomNumber}">
                <div class="product-tabs-wrapper">
                <div class="product-tabs-header" style="border-left: 4px solid ${mainColor};">
                    <div class="room-info-form">
                        <div class="form-group-small">
                            <label for="floorName-${roomId}">Floor Name</label>
                            <input type="text" class="form-control-small floor-name-input"
                            id="floorName-${roomId}" data-room-id="${roomId}"
                            placeholder="Enter floor name" value="${roomData.floor_name || ''}">
                        </div>
                        <div class="form-group-small">
                            <label for="roomName-${roomId}">Room Name</label>
                            <input type="text" class="form-control-small room-name-input"
                            id="roomName-${roomId}" data-room-id="${roomId}"
                            placeholder="Enter room name" value="${roomData.room_name || ''}">
                        </div>
                        <div class="form-group-small">
                            <label>Room Image</label>
                            <div class="image-upload-container">
                            <div class="image-preview" id="imagePreview-${roomId}">
                                ${roomData.room_image ? 
                                    `<img src="<?php echo URL; ?>/uploads/${roomData.room_image}" alt="Room image" style="width:100%;height:100%;object-fit:cover;">` : 
                                    `<i class="fa fa-image"></i>`
                                }
                            </div>
                            <div class="file-input-wrapper">
                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-upload mr-1"></i> ${roomData.room_image ? 'Change' : 'Upload'}
                                </button>
                                <input type="file" class="room-image-input"
                                    id="roomImage-${roomId}"
                                    data-file-type="image"
                                    data-room="${roomNumber}">
                            </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm add-item-to-room-btn" data-room="${roomNumber}">
                        <i class="fa fa-plus mr-1"></i> Add Item To ${roomData.floor_name && roomData.room_name ? `${roomData.floor_name}-${roomData.room_name}` : `Room ${roomNumber}`}
                    </button>
                </div>
                <div class="product-tabs-container" id="productTabs-room${roomNumber}">
                    <div class="product-empty-state">
                        <i class="fa fa-cube"></i>
                        <p>No products added yet</p>
                    </div>
                </div>
                <div class="product-content-area" id="productContent-room${roomNumber}">
                </div>
                </div>
            </div>
        `);

        $('#roomTabsContent').append($pane);
        addRoomToState(roomNumber);
    }
    // When making API calls or collecting data, extract original product ID
    function extractOriginalProductId(productInstanceId) {
        // If productInstanceId contains underscore, it's a duplicate - extract original part
        if (productInstanceId.includes('_')) {
            return productInstanceId.split('_')[0];
        }
        // Otherwise, it's the original product ID
        return productInstanceId;
    }
    // Function to handle existing data
    function addProductTab(roomId, product, existingData = null) {
        console.log('Adding product tab with room context:', {
            roomId: roomId,
            product: product.product_id,
            existingData: existingData
        });

        const $tabsContainer = $(`#productTabs-room${roomId}`);
        const $emptyState = $tabsContainer.find('.product-empty-state');

        if ($emptyState.length) {
            $emptyState.remove();
        }

        // Generate unique product ID for duplicates
        let productInstanceId = product.product_id;
        let instanceCount = 1;

        // Check if product already exists and add suffix
        while ($tabsContainer.find(`[data-product="${productInstanceId}"]`).length) {
            productInstanceId = `${product.product_id}_${instanceCount}`;
            instanceCount++;
        }

        state.currentProductId = productInstanceId;

        const contentId = `product-${productInstanceId}-room${roomId}`;
        const tabId = `${contentId}-tab`;

        if ($tabsContainer.find(`[data-product="${productInstanceId}"]`).length) {
            console.log('Product already exists in this room');
            alert('This product has already been added to this room.');
            return Promise.resolve(); // Return resolved promise if product already exists
        }

        const $tab = $(`
            <div class="product-tab" data-product="${productInstanceId}" data-type="${product.type}" data-available-in="${product.available_in}" data-detail-id="${product.detail_id || ''}" id="${tabId}">
            <div class="product-tab-icon" style="background: ${product.product_color};">
                <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
            </div>
            <span class="product-tab-name">${product.product_name}${instanceCount > 1 ? ` (${instanceCount - 1})` : ''}</span>
            <div class="product-tab-close" title="Remove product">
                <i class="fa fa-times"></i>
            </div>
            </div>
        `);

        $tabsContainer.append($tab);

        const $contentArea = $(`#productContent-room${roomId}`);
        const $emptyContent = $contentArea.find('.product-empty-state');

        if ($emptyContent.length) {
            $emptyContent.remove();
        }

        const $content = $(`
            <div class="product-content" id="${contentId}" data-stock="${product.stock_qty || 0}" data-supplier="${product.product_supplier}" data-attr="${product.attr_id}" style="display: none;">
            <div class="loading-state">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Loading ${product.product_name} details...</p>
            </div>
            </div>
        `);

        $contentArea.append($content);

        // Activate the tab first
        activateProductTab($tab);

        // Return a promise that resolves when content is fully loaded
        return new Promise((resolve) => {
            // Load content with proper delay
            setTimeout(() => {
                loadProductContentWithPromise(contentId, product, roomId, productInstanceId, existingData)
                    .then(() => {
                        // Calculate total after content is loaded
                        setTimeout(() => {
                            console.log('Calculating total for product:', productInstanceId, 'room:', roomId);
                            calculateProductTotal(productInstanceId, roomId);
                            resolve();
                        }, 1500);
                    });
            }, 200);
        });
    }

    // Function to submit the updated order with proper data structure
    function submitUpdatedOrder() {
        const orderData = collectOrderData();

        // Validate required fields
        if (!validateOrderData(orderData)) {
            return;
        }

        // Show loading state
        $('.btn-sb-form').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        // Use FormData to handle file uploads
        const formData = new FormData();

        // Add order data as JSON
        formData.append('order_data', JSON.stringify(orderData));
        formData.append('update_order_with_newlayout', '1');
        formData.append('order_id', <?php echo $order_id; ?>);

        // Add room images
        $('.room-image-input').each(function() {
            const file = this.files[0];
            const roomId = $(this).data('room');
            if (file) {
                formData.append(`room_image_${roomId}`, file);
            }
        });

        $.ajax({
            url: ajax_url + "/api",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                console.log('Submited data:', orderData);
                var response = typeof data === 'string' ? JSON.parse(data) : data;

                if (response.status === "success") {
                    $('.btn-sb-form').prop('disabled', false).html('Update Order');
                    alert('Order updated successfully!');
                    // window.location.href = '<?= URL ?>/index.php?page=order-show&id=<?php echo $order_id; ?>';
                } else {
                    alert('Error: ' + response.message);
                    $('.btn-sb-form').prop('disabled', false).html('Update Order');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Network error occurred. Please try again.');
                $('.btn-sb-form').prop('disabled', false).html('Update Order');
            }
        });
    }

    // Enhanced validation function for update
    function validateOrderData(orderData) {
        if (!orderData.order_date || !orderData.order_delivery_date || !orderData.customer_id) {
            alert('Please fill in all required fields.');
            return false;
        }

        if (orderData.rooms.length === 0) {
            alert('Please add at least one room with products.');
            return false;
        }

        let hasProducts = false;
        orderData.rooms.forEach(room => {
            if (room.products && room.products.length > 0) {
                hasProducts = true;
            }
        });

        if (!hasProducts) {
            alert('Please add at least one product to your order.');
            return false;
        }

        return true;
    }

    // Initialize edit mode with enhanced data collection
    function initializeEditMode() {
        // Update form submission for edit
        $('#edit_order_btn').on('click', function(e) {
            e.preventDefault();
            submitUpdatedOrder();
        });

        // Update material tab handlers for edit mode
        setTimeout(() => {
            // Setup material label tabs for all existing products
            $('.product-content').each(function() {
                const contentId = $(this).attr('id');
                const productId = contentId.replace('product-', '').replace(/-room\d+$/, '');
                const roomId = contentId.match(/room(\d+)/)[1];

                // Get variant ID if it's a variant product
                const $variantContent = $(this).find('.product-variant-content.active');
                const variantId = $variantContent.length ?
                    $variantContent.find('.product-qty').first().data('variant') :
                    $(this).find('.product-qty').first().data('variant') || productId;

                setupAllMaterialLabelTabs(productId, variantId, roomId);
            });
        }, 1500);
        // Update totals calculation for edit mode
        setTimeout(() => {
            updateOrderTotals();
            console.log('Order totals initialized for edit mode');
        }, 2000);
    }

    // Initialize Main Category Modal
    function initializeMainCategoryModal() {
        const $optionsContainer = $('#mainCategoryOptions');
        $optionsContainer.empty();

        // Show loading state
        $optionsContainer.html(`
            <div class="loading-state">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
                  <p>Loading categories...</p>
            </div>
         `);

        $.post(ajax_url + '/api', {
            get_main_categories: 1,
        }, function(data) {
            //   console.log(data);
            try {
                var response = typeof data === 'string' ? JSON.parse(data) : data;
                console.log(response);

                // Clear loading state
                $optionsContainer.empty();

                if (response.status === 'success') {
                    let mainCategories = response.data;

                    mainCategories.forEach((category, index) => {
                        // Generate random image URL using Picsum
                        $randomImg = `https://picsum.photos/200/200?random=${category.category}'`;

                        const $option = $(`
                            <div class="qualification-option" data-category="${category.id}" data-attr-names="${category.attr_names}" data-product-names="${category.product_names}">
                                <div class="qualification-select-option-image">
                                    <img src="<?= URL ?>/uploads/${category.image}" alt="${category.category}">
                                </div>
                                <div class="qualification-option-name">${category.category}</div>
                            </div>
                        `);

                        // Create individual tooltip for each option
                        const $tooltip = $(`
                            <div class="qualification-tooltip">
                                <strong>${category.category}</strong><br>
                                ${category.web_category_title || category.description || ''}
                            </div>
                        `);

                        $('body').append($tooltip);

                        let hoverTimeout;

                        $option.hover(
                            function(e) {
                                clearTimeout(hoverTimeout);
                                const $this = $(this);

                                hoverTimeout = setTimeout(() => {
                                    positionTooltip($tooltip, $this, e);
                                    $tooltip.addClass('visible');
                                }, 100);
                            },
                            function() {
                                clearTimeout(hoverTimeout);
                                $tooltip.removeClass('visible');
                            }
                        );

                        $option.click(function() {
                            $('.qualification-option').removeClass('selected');
                            $(this).addClass('selected');
                            state.selectedMainCategory = $(this).data('category');
                            $('#confirmMainCategory').prop('disabled', false);
                        });

                        $optionsContainer.append($option);
                    });
                } else {
                    $optionsContainer.html(`
                        <div class="error-state">
                            <i class="fa fa-exclamation-triangle"></i>
                            <p>Failed to load categories</p>
                        </div>
                    `);
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                $optionsContainer.html(`
                    <div class="error-state">
                        <i class="fa fa-exclamation-triangle"></i>
                        <p>Error loading categories</p>
                    </div>
                `);
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            $optionsContainer.html(`
                <div class="error-state">
                    <i class="fa fa-exclamation-triangle"></i>
                    <p>Network error loading categories</p>
                </div>
            `);
        });
    }

    // Initialize Advanced Filter Modal
    function initializeAdvancedFilterModal() {

        // Price range slider
        $('#priceRange').on('input', function() {
            const value = $(this).val();
            $('#minPrice').val(0);
            $('#maxPrice').val(value);
            filterState.product.maxPrice = parseInt(value);
        });

        // Price input handlers
        $('#minPrice').on('input', function() {
            filterState.product.minPrice = parseInt($(this).val()) || 0;
        });

        $('#maxPrice').on('input', function() {
            filterState.product.maxPrice = parseInt($(this).val()) || 50000;
            $('#priceRange').val(filterState.product.maxPrice);
        });

        // Date filter
        $('input[name="dateFilter"]').on('change', function() {
            filterState.product.dateSort = $(this).val();
        });

        // Availability filter
        $('#inStock').on('change', function() {
            filterState.product.inStock = $(this).is(':checked');
        });

        $('#outOfStock').on('change', function() {
            filterState.product.outOfStock = $(this).is(':checked');
        });

        // Reset filters
        $('#resetFilters').on('click', function() {
            resetAdvancedFilters();
        });

        // Apply filters
        $('#applyFilters').on('click', function() {
            applyAdvancedFilters();
            hideAdvancedFilterModal();
        });
    }
    // Apply Advanced Filters
    function applyAdvancedFilters() {
        const qualifications = $('#productSelectModal').data('qualification');
        applyProductFilters(qualifications);
    }

    function hideAdvancedFilterModal() {
        $('#advancedFilterModal').fadeOut(300);
    }

    // Initialize Advanced Filter Modal for Items
    function initializeItemAdvancedFilterModal() {

        // Price range slider
        $('#itemPriceRange').on('input', function() {
            const value = $(this).val();
            $('#itemMinPrice').val(0);
            $('#itemMaxPrice').val(value);
            filterState.item.maxPrice = parseInt(value);
        });

        // Price input handlers
        $('#itemMinPrice').on('input', function() {
            filterState.item.minPrice = parseInt($(this).val()) || 0;
        });

        $('#itemMaxPrice').on('input', function() {
            filterState.item.maxPrice = parseInt($(this).val()) || 50000;
            $('#itemPriceRange').val(filterState.item.maxPrice);
        });

        // Date filter
        $('input[name="itemDateFilter"]').on('change', function() {
            filterState.item.dateSort = $(this).val();
        });

        // Availability filter
        $('#itemInStock').on('change', function() {
            filterState.item.inStock = $(this).is(':checked');
        });

        $('#itemOutOfStock').on('change', function() {
            filterState.item.outOfStock = $(this).is(':checked');
        });

        // Reset filters
        $('#itemResetFilters').on('click', function() {
            resetItemAdvancedFilters();
        });

        // Apply filters
        $('#itemApplyFilters').on('click', function() {
            applyItemAdvancedFilters();
            hideItemAdvancedFilterModal();
        });
    }
    // Apply Advanced Filters for Items
    function applyItemAdvancedFilters() {
        applyItemFilters();
    }

    function hideItemAdvancedFilterModal() {
        $('#itemAdvancedFilterModal').fadeOut(300);
    }
    // Search functionality for product modal
    function setupProductSearch() {
        $('#productSearch').on('input', function() {
            filterState.product.searchTerm = $(this).val();
            const qualifications = $('#productSelectModal').data('qualification');
            applyProductFilters(qualifications);
        });
    }

    // Search functionality for item modal
    function setupItemSearch() {
        $(document).on('input', '#itemSearch', function() {
            filterState.item.searchTerm = $(this).val();
            applyItemFilters();
        });
    }

    // Brand filter handlers setup
    function setupBrandFilterHandlers() {

        // Add single handler for product brand selection
        $(document).on('change', '.brand-radio-input[name="product-brand-selection"]', function() {
            // Update visual state - ensure only one is selected
            $('.brand-radio-input[name="product-brand-selection"]').prop('checked', false);
            $(this).prop('checked', true);

            filterState.product.selectedBrand = $(this).val();
            console.log('Selected product brand:', filterState.product.selectedBrand);

            setTimeout(() => {
                const qualifications = $('#productSelectModal').data('qualification');
                if (qualifications) {
                    applyProductFilters(qualifications);
                }
            }, 100);
        });
    }

    $(document).on('change', '.material-type-replacement', function() {
        const selectedMaterialType = $(this).val();
        const $materialSelect = $(this).closest('.material-compact-fields').find('.material-replacement');
        const category = $(this).data('category');

        if (selectedMaterialType) {
            // Clear the material select and show loading
            $materialSelect.html('<option value="">Loading materials...</option>');

            loadMaterials($materialSelect, selectedMaterialType);
        } else {
            let optionsHtml = '<option value="">Select Material</option>';
            $materialSelect.html(optionsHtml);
        }
    });

    function loadMaterials(selectElement, category) {
        $.post(ajax_url + '/api', {
            get_materials_by_category: 1,
            category: category
        }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            if (response.status === 'success') {
                var options_html = response.data;
                selectElement.html(options_html);
            }
        });
    }

    // Apply Product Filters function
    function applyProductFilters(qualificationIds, product_id = null, product_cat = null, search_item_of = null, action = null) {
        product_id = product_id || '';
        product_cat = product_cat || filterState.product.selectedBrand;
        search_item_of = search_item_of || '';
        action = action || '';

        $('#productSelectModal')
            .data('qualification', qualificationIds)
            .data('roomId', state.currentRoom);

        $('#productSelectModal').fadeIn(300);
        $('#multiSelectOptions .multi-select-option').removeClass('selected');
        $('#confirmMultiSelect').prop('disabled', true);
        $('.multi-select-option').show();

        const $optionsContainer = $('#multiSelectOptions');
        const $loadMoreBtn = $('#loadMoreProductsBtn');

        let search_text = '';
        if (filterState.product.searchTerm) {
            search_text = filterState.product.searchTerm;
        }
        if (currentCountProduct === 0 || search_text !== '' || action !== 'load_more') {
            currentCountProduct = 0;
            $optionsContainer.empty();
            // Show loading state
            $optionsContainer.html(`
               <div class="loading-state">
                     <i class="fa fa-spinner fa-spin fa-2x"></i>
                     <p>Loading products...</p>
               </div>
            `);
        }

        let itemSize = 12;
        // Show loading state on load more button
        if (currentCountProduct > 0) {
            $loadMoreBtn.prop('disabled', true).html(`
                  <i class="fa fa-spinner fa-spin mr-1"></i> Loading...
            `);
        }

        $.post(ajax_url + '/api', {
            get_products: 1,
            qualification_ids: qualificationIds,
            search_text: search_text,
            item_size: itemSize,
            current_count: currentCountProduct,
            product_id: product_id,
            product_cat: product_cat,
            search_item_of: search_item_of
        }, function(data) {
            // Reset load more button
            $loadMoreBtn.prop('disabled', false).html(`
                  <i class="fa fa-sync-alt mr-1"></i> Load More Products
            `);
            if (currentCountProduct === 0 || search_text !== '' || action !== 'load_more') {
                $optionsContainer.empty();
            }

            try {
                var response = typeof data === 'string' ? JSON.parse(data) : data;
                console.log('Products response:', response);

                if (response.status === 'success') {
                    currentCountProduct += itemSize;
                    let data = response.data;
                    let brands = data['brands'] || [];
                    let styles = data['styles'] || [];
                    let filteredProducts = data['products'] || [];
                    searchType = data['search_type'];

                    // Reinitialize filters with new data - but only setup handlers once
                    initializeBrandRadioTabs(brands);
                    initializeStyleCheckboxTabs(styles);

                    console.log('Before filtering:', {
                        totalProducts: filteredProducts.length,
                        selectedBrand: filterState.product.selectedBrand,
                        selectedStyles: filterState.product.selectedStyles,
                        searchTerm: filterState.product.searchTerm
                    });

                    // Apply brand filter
                    if (filterState.product.selectedBrand && filterState.product.selectedBrand !== '') {
                        filteredProducts = filteredProducts.filter(product =>
                            String(product.catalog_id) === String(filterState.product.selectedBrand)
                        );
                        console.log('After brand filter:', filteredProducts.length);
                    }

                    // Apply style filter
                    if (filterState.product.selectedStyles && filterState.product.selectedStyles.length > 0) {
                        filteredProducts = filteredProducts.filter(product => {
                            const productStyle = String(product.product_style_type || '');
                            return filterState.product.selectedStyles.some(selectedStyle =>
                                String(selectedStyle) === productStyle
                            );
                        });
                        console.log('After style filter:', filteredProducts.length);
                    }

                    // Apply price filter
                    filteredProducts = filteredProducts.filter(product => {
                        const price = parseFloat(product.standart_price) || 0;
                        return price >= filterState.product.minPrice &&
                            price <= filterState.product.maxPrice;
                    });
                    console.log('After price filter:', filteredProducts.length);

                    // Apply search filter
                    if (filterState.product.searchTerm) {
                        const searchTerm = filterState.product.searchTerm.toLowerCase();
                        filteredProducts = filteredProducts.filter(product =>
                            (product.product_code && product.product_code.toLowerCase().includes(searchTerm)) ||
                            (product.product_name && product.product_name.toLowerCase().includes(searchTerm))
                        );
                        console.log('After search filter:', filteredProducts.length);
                    }

                    // Apply date sort
                    filteredProducts.sort((a, b) => {
                        const dateA = new Date(a.product_add_date || 0);
                        const dateB = new Date(b.product_add_date || 0);
                        return filterState.product.dateSort === 'newest' ?
                            dateB - dateA : dateA - dateB;
                    });

                    // Display filtered products
                    if (filteredProducts.length === 0) {
                        $optionsContainer.html(`
                              <div class="no-products-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                                 <i class="fa fa-search" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                                 <p>No products found matching your criteria</p>
                              </div>
                        `);
                        return;
                    }

                    filteredProducts.forEach(product => {
                        const other_buttons = searchType !== 'special' ? getOtherRelatedButtons(product) : ['', ''];
                        const more_related_button = other_buttons[0];
                        const plan_create_button = other_buttons[1];
                        const $option = $(`
                              <div class="multi-select-option" data-product-id="${product.product_id}" data-attr-id="${product.attr_id}">
                                 <small class="brand-tag" style="background: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem;position:absolute;top:0;right:0;">
                                    ${product.catalog_name || 'Unknown'}
                                 </small>
                                 <div class="multi-select-option-header">
                                    <div class="multi-select-option-image">
                                          <img src="${product.product_img}" alt="${product.product_name}" onerror="this.src='https://via.placeholder.com/150'">
                                    </div>
                                 </div>
                                 <div class="multi-select-option-meta text-center">
                                    <small class="name-tag px-1" style="font-weight: 600;">
                                          ${product.product_name}
                                    </small>
                                    <small class="price-tag px-1 d-none" style="color: #4361ee; font-weight: 600;">
                                          $${Number(product.standart_price || 0).toFixed(2)}
                                    </small>
                                    <div class="related-btn-div">
                                       ${more_related_button}${plan_create_button}
                                    </div>
                                 </div>
                              </div>
                        `);
                        $optionsContainer.append($option);
                    });

                    console.log('Final displayed products:', filteredProducts.length);
                } else {
                    $optionsContainer.html(`
                        <div class="no-products-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                              <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                              <p>Error loading products: ${response.message || 'Unknown error'}</p>
                        </div>
                     `);
                }
            } catch (error) {
                console.error('Error processing products response:', error);
                $optionsContainer.html(`
                     <div class="no-products-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p>Error processing products data</p>
                     </div>
                  `);
            }

        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            $optionsContainer.html(`
                  <div class="no-products-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                     <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                     <p>Network error loading products</p>
                  </div>
            `);
        });
    }

    function getOtherRelatedButtons(item) {
        let more_related_button = "";

        let search_item_of = item.item_of;

        if (item.item_type == "stock_combination") {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','material_combination')\">Combination</button>" +
                "</div>" +
                "</div>" +
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','stock_item')\">View Stock</button>" +
                "</div>" +
                "</div>";
        } else if (item.item_type == "stock_set") {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','stock_item')\">View Stock</button>" +
                "</div>" +
                "</div>" +
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','set')\">More Related</button>" +
                "</div>" +
                "</div>";
        } else if (item.item_type == "set_stock_combination") {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','material_combination')\">Combination</button>" +
                "</div>" +
                "</div>" +
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','stock_item')\">View Stock</button>" +
                "</div>" +
                "</div>" +
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','set')\">More Related</button>" +
                "</div>" +
                "</div>";
        } else if (item.item_type == "set_combination") {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','material_combination')\">Combination</button>" +
                "</div>" +
                "</div>" +
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','set')\">More Related</button>" +
                "</div>" +
                "</div>";
        } else if (item.item_type == "combination") {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','material_combination')\">Combination</button>" +
                "</div>" +
                "</div>";
        } else if (item.item_type == "stock_item") {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','stock_item')\">View Stock</button>" +
                "</div>" +
                "</div>";
        }

        if (
            more_related_button == "" &&
            (item.item_type === "set" || item.item_type === "size")
        ) {
            more_related_button =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-info" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','" +
                item.item_type +
                "')\">More Related</button>" +
                "</div>" +
                "</div>";
        }

        let plan_create_btn = "";
        if (item.item_type_plan == "plan") {
            plan_create_btn =
                '<div class="row d-flex justify-content-center mx-1">' +
                '<div class="btn-group w-100 mb-1" role="group" aria-label="Basic outlined example">' +
                '<button type="button" class="btn btn-sm btn-outline-warning" onclick="applyProductFilters(\'' +
                item.attr_id +
                "','" +
                item.product_id +
                "','" +
                item.catalog_id +
                "','plan_products')\">Show Plan Item</button>" +
                "</div>" +
                "</div>";
        }
        return [more_related_button, plan_create_btn];
    }

    // Style filter handlers setup
    function setupStyleFilterHandlers() {
        $(document).on('click', '.product-style-checkbox', function() {
            $(this).toggleClass('selected');
            const styleId = $(this).data('style-id');

            // Toggle the checkbox visual state
            const $checkbox = $(this).find('.style-checkbox');
            if ($(this).hasClass('selected')) {
                // Add to selected styles if not already present
                if (!filterState.product.selectedStyles.includes(styleId)) {
                    filterState.product.selectedStyles.push(styleId);
                }
            } else {
                // Remove from selected styles
                filterState.product.selectedStyles = filterState.product.selectedStyles.filter(id => id !== styleId);
            }

            console.log('Selected styles:', filterState.product.selectedStyles);

            setTimeout(() => {
                const qualifications = $('#productSelectModal').data('qualification');
                if (qualifications) {
                    applyProductFilters(qualifications);
                }
            }, 100);
        });
    }

    // function for item brand filter handlers
    function setupItemBrandFilterHandlers() {

        // Add single handler for item brand selection
        $(document).on('change', '.brand-radio-input[name="item-brand-selection"]', function() {
            $('.brand-radio-input[name="item-brand-selection"]').prop('checked', false);
            $(this).prop('checked', true);

            filterState.item.selectedBrand = $(this).val();
            console.log('Selected item brand:', filterState.item.selectedBrand);

            setTimeout(() => {
                applyItemFilters();
            }, 100);
        });
    }

    function setupItemStyleFilterHandlers() {
        $(document).on('click', '.item-style-checkbox', function() {
            $(this).toggleClass('selected');
            const styleId = $(this).data('style-id');

            if ($(this).hasClass('selected')) {
                if (!filterState.item.selectedStyles.includes(styleId)) {
                    filterState.item.selectedStyles.push(styleId);
                }
            } else {
                filterState.item.selectedStyles = filterState.item.selectedStyles.filter(id => id !== styleId);
            }

            setTimeout(() => {
                applyItemFilters();
            }, 100);
        });
    }

    // image upload functionality with larger image sizes
    function setupImageUploadOld() {
        $(document).on('change', '.room-image-input', function() {
            const file = this.files[0];
            const roomId = $(this).data('room');
            const $preview = $(`#imagePreview-room${roomId}`);

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    $preview.html(`<img src="${e.target.result}" alt="Room image">`);
                }

                reader.readAsDataURL(file);
            }
        });

        // Header image upload
        $(document).on('change', '.header-image-input', function() {
            const file = this.files[0];
            const $preview = $(this).closest('.product-header-with-image').find('.header-image-preview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    $preview.html(`<img src="${e.target.result}" alt="Header image">`);
                }

                reader.readAsDataURL(file);
            }
        });
    }

    function setupImageUpload() {
        $(document).on('change', '.room-image-input', function() {
            const file = this.files[0];
            const roomId = $(this).data('room');
            const $preview = $(`#imagePreview-room${roomId}`);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.html(`<img src="${e.target.result}" alt="Room image">`);
                }
                reader.readAsDataURL(file);
            }
        });

        // Header image upload
        $(document).on('change', '.header-image-input', function() {
            const file = this.files[0];
            const $preview = $(this).closest('.product-header-with-image').find('.header-image-preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.html(`<img src="${e.target.result}" alt="Header image">`);
                }
                reader.readAsDataURL(file);
            }
        });

        // NEW: Product image upload for curtain products
        $(document).on('change', '.product-image-input', function() {
            const file = this.files[0];
            const $preview = $(this).closest('.compact-image-preview, .enhanced-image-preview');
            const productId = $(this).data('product');
            const roomId = $(this).data('room');
            const variantId = $(this).data('variant') || '';

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.html(`<img src="${e.target.result}" alt="Product image" style="width:100%;height:100%;object-fit:cover;">`);

                    // Store the uploaded image data for later submission
                    const imageKey = variantId ?
                        `product_${productId}_variant_${variantId}_room_${roomId}` :
                        `product_${productId}_room_${roomId}`;

                    if (!window.uploadedProductImages) window.uploadedProductImages = {};
                    window.uploadedProductImages[imageKey] = {
                        file: file,
                        dataUrl: e.target.result
                    };
                }
                reader.readAsDataURL(file);
            }
        });
    }

    function updateRoomStatus(roomId) {
        const $roomPane = $(`#${roomId}`);
        const $statusIndicator = $(`#${roomId}-tab.status-indicator`);

        let hasItems = false;
        let allComplete = true;

        $roomPane.find('.enhanced-category-item').each(function() {
            hasItems = true;
            const $qty = $(this).find('.item-qty');
            const $itemLength = $(this).find('.item-length');
            const $itemWidth = $(this).find('.item-width');
            const $itemHeight = $(this).find('.item-height');
            const $name = $(this).find('.enhanced-item-name');

            if (!$qty.val() || !$itemLength.val() || !$itemWidth.val() || !$itemHeight.val() || !$name.val()) {
                allComplete = false;
                return false;
            }
        });

        $statusIndicator.removeClass('status-empty status-incomplete status-complete');

        if (!hasItems) {
            $statusIndicator.addClass('status-empty');
        } else if (allComplete) {
            $statusIndicator.addClass('status-complete');
        } else {
            $statusIndicator.addClass('status-incomplete');
        }
    }

    // Add room to state
    function addRoomToState(roomNumber) {
        state.rooms.push(roomNumber);
        state.rooms.sort((a, b) => a - b);
    }

    // Enhanced event handlers for fitout products and items
    function setupFitoutCalculationHandlers() {
        // Fitout item calculations
        $(document).on('input change', '.item-details .item-width, .item-details .item-length, .item-details .item-height, .item-details .item-qty, .item-details .item-discount', function() {
            const $itemContent = $(this).closest('.item-details');
            if ($itemContent.length) {
                const $productContent = $itemContent.closest('.product-content');
                const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
                const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

                console.log('Fitout item change detected, recalculating...');
                calculateProductTotal(productId, roomId, 'fitout');
            }
        });

        // Fitout material calculations
        $(document).on('input change', '.item-details .area-weight, .item-details .material-type-select', function() {
            const $itemContent = $(this).closest('.item-details');
            if ($itemContent.length) {
                const $productContent = $itemContent.closest('.product-content');
                const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
                const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

                console.log('Fitout item material change detected, recalculating...');
                calculateProductTotal(productId, roomId, 'fitout');
            }
        });
    }

    $(document).on('click', '#loadMoreProductsBtn', function() {
        const qualifications = $('#productSelectModal').data('qualification');
        if (qualifications) {
            applyProductFilters(qualifications, null, null, null, 'load_more');
        }
    });
    $(document).on('click', '#loadMoreItemsBtn', function() {
        applyItemFilters('load_more');
    });

    // Load order data when page is ready
    $(document).ready(function() {
        // initialization
        initializeMainCategoryModal();
        initializeAdvancedFilterModal();
        initializeItemAdvancedFilterModal();
        setupProductSearch();
        setupItemSearch();
        setupBrandFilterHandlers();
        setupStyleFilterHandlers();
        setupItemBrandFilterHandlers();
        setupItemStyleFilterHandlers();
        setupImageUpload();
        updateRoomStatus('room1');
        addRoomToState(1);
        setupFitoutCalculationHandlers();

        const orderId = <?php echo $order_id; ?>;
        loadOrderData(orderId);
    });
</script>
<script>
    function getProductItems(parentProductId) {
        return products.filter(product => product.parent_id === parentProductId && product.type === 'item');
    }

    function getItemCategories(parentProductId) {
        const items = getProductItems(parentProductId);
        const categories = {};

        items.forEach(item => {
            if (!categories[item.category]) {
                categories[item.category] = {
                    name: item.category.charAt(0).toUpperCase() + item.category.slice(1),
                    items: []
                };
            }
            categories[item.category].items.push(item);
        });

        return categories;
    }

    // Function to create variants tabs for a product
    function createVariantsTabs(product, variants, roomId, existingData, productInstanceId) {
        return `
            <div class="product-variants-section" id="variants-section-${productInstanceId}-room${roomId}">
               <div class="product-variants-tabs" id="variants-tabs-${productInstanceId}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <button class="product-variant-tab ${index === 0 ? 'active' : ''}" 
                           data-variant="${variant.product_id}" data-product="${productInstanceId}" data-type="${product.type}" data-available-in="${product.available_in}" data-room="${roomId}">
                        <div class="product-variant-header">
                           <span class="status-indicator status-empty"></span>
                           <span class="product-variant-title">${variant.product_name}</span>
                        </div>
                     </button>
                  `).join('')}
               </div>
               <div class="product-variants-content" id="variants-content-${productInstanceId}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <div class="product-variant-content ${index === 0 ? 'active' : ''}" 
                        id="variant-${productInstanceId}-${variant.product_id}-room${roomId}">
                        ${createVariantContentForSet(product, variant, roomId, existingData, index, productInstanceId)}
                     </div>
                  `).join('')}
               </div>
            </div>
         `;
    }

    $(document).on('change', '.material-replacement', function() {
        const selectedOption = $(this).find('option:selected');
        const imageUrl = selectedOption.data('image');
        const $imageContainer = $(this).closest('.material-inputs-compact-replacement').find('.material-compact-image-replacement');

        if (imageUrl) {
            $imageContainer.html(`<img src="<?= URL ?>/uploads/material/${imageUrl}" alt="${selectedOption.data('name')}" style="width:100%;height:100%;object-fit:cover;">`);
        } else {
            $imageContainer.html('<i class="fa fa-image"></i>');
        }
    });

    // Function to create curtain variant content with additional options
    function createCurtainVariantContent(product, variant, roomId, existingData, index, productInstanceId) {
        const dims = variant.dims || {}; // fallback if undefined

        const width = dims.width || '';
        const length = dims.length || '';
        const height = dims.height || '';
        const standart_price = dims.standart_price || '';
        const unit_price = calculateUnitPrice(variant.calculate_type, dims);

        const quantity = existingData?.quantity || 1;
        const discount = existingData?.discount || 0;
        const open_with = existingData?.curtain_data ? existingData.curtain_data[index + 1].configuration.open_with : '';
        const opening_direction = existingData?.curtain_data ? existingData.curtain_data[index + 1].configuration.opening_direction : '';
        const installation_needed = existingData?.curtain_data ? existingData.curtain_data[index + 1].configuration.installation === 'needed' : false;

        return `
            <div class="variant-details">
                <div class="compact-product-details">
                    <div class="compact-section-header">
                        <h6><i class="fa fa-cube mr-2"></i>${variant.product_name} - ${variant.product_desc}</h6>
                    </div>
                    <div class="compact-details-with-image">
                        <div class="compact-image-preview">
                            <img style="width:100%;height:100%;" src="${variant.product_img}" alt="${variant.product_name}">
                            <!-- Image upload overlay for curtain products -->
                            <div class="image-upload-overlay">
                                <label class="image-upload-label">
                                    <i class="fa fa-camera"></i>
                                    <input type="file" class="product-image-input d-none" 
                                            data-product="${productInstanceId}" 
                                            data-variant="${variant.product_id}"
                                            data-room="${roomId}"
                                            accept="image/*">
                                </label>
                            </div>
                        </div>
                        <div class="compact-details-fields">
                            <div class="compact-detail-group">
                                <label>Quantity</label>
                                <input type="number" class="form-control product-qty" 
                                        placeholder="0" step="1" min="1" value="${quantity}"
                                        data-variant="${variant.product_id}">
                            </div>
                            <div class="compact-detail-group">
                                <label>Discount(%)</label>
                                <input type="number" class="form-control product-discount" 
                                        placeholder="0" step="0.01" min="0" value="${discount}"
                                        data-variant="${variant.product_id}">
                            </div>
                            <div class="compact-detail-group">
                                <label>Notes(TR)</label>
                                <textarea class="form-control product-notes-tr" 
                                        placeholder="Enter notes in Turkish"
                                        data-variant="${variant.product_id}">${existingData && existingData.product_notes_tr ? existingData.product_notes_tr : ''}</textarea>
                            </div>
                            <div class="compact-detail-group">
                                <label>Notes(EN)</label>
                                <textarea class="form-control product-notes-en" 
                                        placeholder="Enter notes in English"
                                        data-variant="${variant.product_id}">${existingData && existingData.product_notes_en ? existingData.product_notes_en : ''}</textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Add base price field for variant -->
                    <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${variant.product_id}">
                    <input type="hidden" class="calculate-type" value="${variant.calculate_type}" data-variant="${variant.product_id}">
                    
                    <!-- Material Section for Curtain Variant - Using existing reusable function -->
                    ${createMaterialSection(product, variant, roomId, existingData, productInstanceId, index)}
                    
                    <!-- CURTAIN OPTIONS SECTION - Fixed accessories layout -->
                    <div class="curtain-options-section">
                        <h6><i class="fa fa-cog mr-2"></i>Curtain Options</h6>
                        <div class="curtain-controls">
                        <div class="curtain-control">
                            <label>Opening Direction</label>
                            <select class="form-control opening-direction" data-variant="${variant.product_id}">
                                <option value="" ${opening_direction === '' ? 'selected' : ''}>Select Direction</option>
                                <option value="two" ${opening_direction === 'two' ? 'selected' : ''}>Two Directions</option>
                                <option value="left" ${opening_direction === 'left' ? 'selected' : ''}>Left Opening</option>
                                <option value="right" ${opening_direction === 'right' ? 'selected' : ''}>Right Opening</option>
                            </select>
                        </div>
                        <div class="curtain-control">
                            <label>Open With</label>
                            <select class="form-control open-with" data-variant="${variant.product_id}">
                                <option value="" ${open_with === '' ? 'selected' : ''}>Select Option</option>
                                <option value="motor" ${open_with === 'motor' ? 'selected' : ''}>Motor</option>
                                <option value="manual" ${open_with === 'manual' ? 'selected' : ''}>Manual</option>
                            </select>
                        </div>
                        <div class="curtain-control installation-control">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input curtain-installation-needed-checkbox" 
                                        id="curtain-installation${productInstanceId}${variant.product_id}${roomId}" 
                                        value="needed" ${installation_needed ? 'checked' : ''}
                                        data-variant="${variant.product_id}">
                                <label class="custom-control-label font-weight-bold" 
                                        for="curtain-installation${productInstanceId}${variant.product_id}${roomId}">
                                    <i class="fa fa-tools mr-2"></i>Installation Needed
                                    <i class="fa fa-info-circle text-muted ml-1" 
                                        data-toggle="tooltip" 
                                        data-placement="top" 
                                        title="Professional installation service - $200"></i>
                                    <span class="installation-price text-success ml-2 font-weight-bold" style="${!installation_needed?'display:none;':''}">+ $200</span>
                                </label>
                            </div>
                        </div>
                        </div>
                        
                        <!-- Accessory Selection for Curtains - Using the same layout as before -->
                        <div class="curtain-accessories-section">
                        <h6 style="margin-top: 16px;"><i class="fa fa-plus-circle mr-2"></i>Additional Accessories</h6>
                        <div class="accessory-layout">
                            <div class="accessory-tabs-sidebar">
                                <div class="accessory-tabs-header">
                                    <h6><i class="fa fa-list mr-2"></i>Accessories</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-accessory-btn" data-product="${productInstanceId}" data-attr-id="${variant.attr_id}" data-type="${product.type}" data-available-in="${product.available_in}" data-variant="${variant.product_id}" data-room="${roomId}">
                                    <i class="fa fa-plus mr-1"></i> Add
                                    </button>
                                </div>
                                <div class="accessory-tabs-container" id="accessory-tabs-${productInstanceId}-${variant.product_id}-room${roomId}">
                                    <div class="empty-accessory-tabs">
                                    <i class="fa fa-puzzle-piece"></i>
                                    <p>No accessories added yet</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accessory-details-content" id="accessory-details-${productInstanceId}-${variant.product_id}-room${roomId}">
                                <div class="accessory-details-header">
                                    <div class="product-header-with-image">
                                    <div class="header-image-preview">
                                        <i class="fa fa-puzzle-piece"></i>
                                    </div>
                                    <h6><i class="fa fa-info-circle mr-2"></i>Accessory Details</h6>
                                    </div>
                                </div>
                                <div class="accessory-details-body">
                                    <div class="empty-accessory-selection">
                                    <i class="fa fa-hand-pointer"></i>
                                    <p>Select an accessory to view and edit details</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Surcharges Section -->
                    ${createSurchargesSection(product, variant, roomId)}
                </div>
            </div>
         `;
    }

    // Modified function to setup curtain options
    function setupCurtainOptions(product, variants, roomId, productInstanceId) {
        const productId = productInstanceId;
        variants.forEach(variant => {
            // Setup curtain option change handlers
            $(`#variant-${productId}-${variant.product_id}-room${roomId} .opening-direction`).on('change', function() {
                updateVariantStatus(productId, roomId, variant.product_id, variant.available_in, product.type);
            });

            $(`#variant-${productId}-${variant.product_id}-room${roomId} .open-with`).on('change', function() {
                updateVariantStatus(productId, roomId, variant.product_id, variant.available_in, product.type);
            });
        });
    }

    // Updated function to create variant content for sets
    function createVariantContentForSet(product, variant, roomId, existingData, index, productInstanceId) {
        // Check if this is a curtain product
        const isCurtainProduct = product.type === 'curtain';

        if (isCurtainProduct) {
            return createCurtainVariantContent(product, variant, roomId, existingData, index, productInstanceId);
        } else {
            const dims = variant.dims || {}; // fallback if undefined

            let width = dims.width || '';
            let length = dims.length || '';
            let height = dims.height || '';
            const standart_price = dims.standart_price || '';
            const unit_price = calculateUnitPrice(variant.calculate_type, dims);

            if (existingData && existingData.attr_dims) {
                const existingDims = existingData.attr_dims[index] || {};
                width = existingDims.width || width;
                length = existingDims.length || length;
                height = existingDims.height || height;
            }
            const quantity = existingData?.quantity || 1;
            const discount = existingData?.discount || 0;

            return `
                  <div class="variant-details">
                     <div class="compact-product-details">
                        <div class="compact-section-header">
                              <h6><i class="fa fa-cube mr-2"></i>${variant.product_name} - ${variant.product_desc}</h6>
                        </div>
                        <div class="compact-details-with-image">
                              <div class="compact-image-preview">
                                 <img style="width:100%;height:100%;" src="${variant.product_img}" alt="${variant.product_name}">
                              </div>
                              <div class="compact-details-fields">
                                 <div class="compact-detail-group">
                                    <label>Width (m)</label>
                                    <input type="number" class="form-control dimension-width" 
                                          value="${width}" data-standart_width="${width}" placeholder="0.00" step="0.01" min="0" 
                                          data-variant="${variant.product_id}" ${getDisabledAttr(variant.calculate_type, 'w')}>
                                 </div>
                                 <div class="compact-detail-group">
                                    <label>Length (m)</label>
                                    <input type="number" class="form-control dimension-length" 
                                          value="${length}" data-standart_length="${length}" placeholder="0.00" step="0.01" min="0"
                                          data-variant="${variant.product_id}" ${getDisabledAttr(variant.calculate_type, 'l')}>
                                 </div>
                                 <div class="compact-detail-group">
                                    <label>Height (m)</label>
                                    <input type="number" class="form-control dimension-height" 
                                          value="${height}" data-standart_height="${height}" placeholder="0.00" step="0.01" min="0"
                                          data-variant="${variant.product_id}" ${getDisabledAttr(variant.calculate_type, 'h')}>
                                 </div>
                                 <div class="compact-detail-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control product-qty" 
                                          placeholder="0" step="1" min="1" value="${quantity}"
                                          data-variant="${variant.product_id}">
                                 </div>
                                 <div class="compact-detail-group">
                                    <label>Discount(%)</label>
                                    <input type="number" class="form-control product-discount" 
                                          placeholder="0" step="0.01" min="0" value="${discount}"
                                          data-variant="${variant.product_id}">
                                 </div>
                                 <div class="compact-detail-group">
                                    <label>Notes(TR)</label>
                                    <textarea class="form-control product-notes-tr" 
                                          placeholder="Enter notes in Turkish" 
                                          data-variant="${variant.product_id}">${existingData && existingData.product_notes_tr ? existingData.product_notes_tr : ''}</textarea>
                                 </div>
                                 <div class="compact-detail-group">
                                    <label>Notes(EN)</label>
                                    <textarea class="form-control product-notes-en" 
                                          placeholder="Enter notes in English" 
                                          data-variant="${variant.product_id}">${existingData && existingData.product_notes_en ? existingData.product_notes_en : ''}</textarea>
                                 </div>
                              </div>
                        </div>
                        <!-- Add base price field for variant -->
                        <input type="hidden" class="standart-price" value="${standart_price}" data-variant="${variant.product_id}">
                        <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${variant.product_id}">
                        <input type="hidden" class="calculate-type" value="${variant.calculate_type}" data-variant="${variant.product_id}">
                        
                        <!-- Material Section - Use existing data -->
                        ${createMaterialSection(product, variant, roomId, existingData, productInstanceId, index)}
                        
                        <!-- Surcharges Section -->
                        ${createSurchargesSection(product, variant, roomId)}
                     </div>
                  </div>
            `;
        }
    }

    // function to create variant content for size variants
    function createVariantContentForSize(product, variant, roomId, existingData, productInstanceId, index) {
        let width = variant.width;
        let length = variant.length;
        let height = variant.height;
        const standart_price = variant.standart_price;
        const unit_price = calculateUnitPrice(product.calculate_type, variant);
        console.log('creating variant content for size:', {
            roomId: roomId,
            productInstanceId: productInstanceId,
            variant: variant.id,
            existingData: existingData
        })
        if (existingData && existingData.attr_bed_dim && variant.id === existingData.bed_dim) {
            const existingDims = existingData.attr_bed_dim[0] || {};
            width = existingDims.width || width;
            length = existingDims.length || length;
            height = existingDims.height || height;
        }
        const quantity = existingData?.quantity || 1;
        const discount = existingData?.discount || 0;
        return `
            <div class="variant-details">
                  <div class="compact-product-details">
                    <div class="compact-section-header">
                        <h6><i class="fa fa-cube mr-2"></i>Headboard Dimension</h6>
                    </div>
                    <div class="compact-details-with-image">
                        <div class="compact-image-preview">
                                <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                        </div>
                        <div class="compact-details-fields">
                                <div class="compact-detail-group">
                                    <label>Width (m)</label>
                                    <input type="number" class="form-control dimension-width" 
                                    placeholder="0.00" step="0.01" min="0" value="${width}" data-standart_width="${width}" 
                                    data-variant="${variant.id}" ${getDisabledAttr(product.calculate_type, 'w')}>
                                </div>
                                <div class="compact-detail-group">
                                    <label>Length (m)</label>
                                    <input type="number" class="form-control dimension-length" 
                                    placeholder="0.00" step="0.01" min="0" value="${length}" data-standart_length="${length}" 
                                    data-variant="${variant.id}" ${getDisabledAttr(product.calculate_type, 'l')}>
                                </div>
                                <div class="compact-detail-group">
                                    <label>Height (m)</label>
                                    <input type="number" class="form-control dimension-height" 
                                    placeholder="0.00" step="0.01" min="0" value="${height}" data-standart_height="${height}"
                                    data-variant="${variant.id}" ${getDisabledAttr(product.calculate_type, 'h')}>
                                </div>
                                <div class="compact-detail-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control product-qty" 
                                    placeholder="0" step="1" min="1" value="${quantity}"
                                    data-variant="${variant.id}">
                                </div>
                                <div class="compact-detail-group">
                                    <label>Discount(%)</label>
                                    <input type="number" class="form-control product-discount" 
                                    placeholder="0" step="0.01" min="0" value="${discount}"
                                    data-variant="${variant.id}">
                                </div>
                                <div class="compact-detail-group">
                                    <label>Notes(TR)</label>
                                    <textarea class="form-control product-notes-tr" 
                                        placeholder="Enter notes in Turkish" 
                                        data-variant="${variant.id}">${existingData && existingData.product_notes_tr ? existingData.product_notes_tr : ''}</textarea>
                                </div>
                                <div class="compact-detail-group">
                                    <label>Notes(EN)</label>
                                    <textarea class="form-control product-notes-en" 
                                    placeholder="Enter notes in English" 
                                    data-variant="${variant.id}">${existingData && existingData.product_notes_en ? existingData.product_notes_en : ''}</textarea>
                                </div>
                        </div>
                    </div>
                    <!-- Add base price field for size variant -->
                    <input type="hidden" class="standart-price" value="${standart_price}" data-variant="${variant.id}">
                    <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${variant.id}">
                    <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${variant.id}">
                    
                    <!-- Material Section - Use existing data -->
                    ${createMaterialSection(product, variant, roomId, existingData, productInstanceId, index)}
                    
                    <!-- Surcharges Section -->
                    ${createSurchargesSection(product, variant, roomId)}
                  </div>
            </div>
         `;
    }

    // Function to setup variants tabs functionality
    function setupVariantsTabs(product, variants, roomId, productInstanceId) {
        const productId = productInstanceId;
        const $variantsTabs = $(`#variants-tabs-${productId}-room${roomId}`);
        const $variantsContent = $(`#variants-content-${productId}-room${roomId}`);

        if (!$variantsTabs.length) return;

        // Main variant tabs click handler
        $variantsTabs.find('.product-variant-tab').on('click', function(e) {
            e.preventDefault();
            const variantId = $(this).data('variant');
            const availableIn = $(this).data('available-in');

            // Deactivate all tabs and content
            $variantsTabs.find('.product-variant-tab').removeClass('active');
            $variantsContent.find('.product-variant-content').removeClass('active');

            // Activate current tab and content
            $(this).addClass('active');
            $(`#variant-${productId}-${variantId}-room${roomId}`).addClass('active');

            updateVariantStatus(productId, roomId, variantId, availableIn, product.type);
        });

        // Setup calculations for variants
        setupVariantCalculations(productId, roomId);

        setTimeout(() => {
            // Setup material tabs for variants
            setupVariantMaterialTabs(product, variants, roomId, productInstanceId);
            // Setup pillow subcategory tabs for variants
            setupVariantPillowSubcategoryTabs(product, variants, roomId, productInstanceId);
        }, 500);

        // Setup curtain options if this is a curtain product
        if (product.type === 'curtain') {
            setupCurtainOptions(product, variants, roomId, productInstanceId);
        }

        // Activate the first tab by default
        const $firstTab = $variantsTabs.find('.product-variant-tab').first();
        if ($firstTab.length) {
            $firstTab.trigger('click');
        }
    }

    function setupVariantPillowSubcategoryTabs(product, variants, roomId, productInstanceId) {
        const productId = productInstanceId;
        const availableIn = product.available_in;

        variants.forEach(variant => {
            let variantId = product.available_in === 'size' ? variant.id : variant.product_id;
            const pillowTabsId = `pillowTabs-${productId}-${variantId}-room${roomId}`;
            const $pillowTabs = $(`#${pillowTabsId}`);
            const $pillowContent = $(`#pillowContent-${productId}-${variantId}-room${roomId}`);

            if (!$pillowTabs.length) return;

            // Tab click handler
            $pillowTabs.find('.pillow-subcategory-tab').on('click', function(e) {
                e.preventDefault();
                const subcategoryId = $(this).data('subcategory');

                // Deactivate all tabs and content
                $pillowTabs.find('.pillow-subcategory-tab').removeClass('active');
                $pillowContent.find('.pillow-subcategory-content').removeClass('active');

                // Activate current tab and content
                $(this).addClass('active');
                $(`#pillowSubcategory-${productId}-${variantId}-room${roomId}-${subcategoryId}`).addClass('active');

                updatePillowSubcategoryStatus(productId, variantId, availableIn, subcategoryId, roomId, product.type);
            });

            // Setup material group handlers
            setupPillowMaterialGroupHandlers(productId, variantId, roomId, availableIn, product.type);

            // Activate the first tab by default
            const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
            if ($firstTab.length) {
                $firstTab.trigger('click');
            }
        });
    }

    // Function to setup variant calculations
    function setupVariantCalculations(productId, roomId) {
        // Dimension calculations for variants
        $(`#variants-content-${productId}-room${roomId} .dimension-width, #variants-content-${productId}-room${roomId} .dimension-length`).on('input', function() {
            const variantId = $(this).data('variant');
            const availableIn = $(this).data('available-in');
            const type = $(this).data('type');

            updateVariantStatus(productId, roomId, variantId, availableIn, type);
        });
    }

    // function to setup material tabs for variants
    function setupVariantMaterialTabs(product, variants, roomId, productInstanceId) {
        const productId = productInstanceId;
        const availableIn = product.available_in;

        variants.forEach(variant => {
            let variantId = product.available_in === 'size' ? variant.id : variant.product_id;
            const materialTabsId = `materialTabs-${productId}-${variantId}-room${roomId}`;

            // Use a small delay to ensure DOM is ready
            setTimeout(() => {
                const $materialTabs = $(`#${materialTabsId}`);

                if ($materialTabs.length) {
                    console.log('Setting up material tabs for:', materialTabsId);

                    // Remove existing handlers to prevent duplicates
                    $materialTabs.off('click', '.material-tab');

                    // Add new handler
                    $materialTabs.on('click', '.material-tab', function(e) {
                        e.preventDefault();
                        const categoryId = $(this).data('category');
                        const materialTabsContentId = `materialTabsContent-${productId}-${variantId}-room${roomId}`;

                        console.log('Material tab clicked:', categoryId);

                        // Deactivate all tabs and content
                        $(`#${materialTabsId} .material-tab`).removeClass('active');
                        $(`#${materialTabsContentId} .material-tab-content`).removeClass('active');

                        // Activate current tab and content
                        $(this).addClass('active');
                        $(`#materialContent-${productId}-${variantId}-room${roomId}-${categoryId}`).addClass('active');

                        updateVariantStatus(productId, roomId, variantId, availableIn, product.type);
                    });
                }
            }, 100);
        });
    }

    // Add these setup functions to edit script
    function setupMaterialTabsForProduct(product, variant, roomId, productInstanceId) {
        const materialTabsId = `materialTabs-${productInstanceId}-${variant.product_id}-room${roomId}`;

        // Setup main category tabs
        $(document).off('click', `#${materialTabsId} .material-tab`);
        $(document).on('click', `#${materialTabsId} .material-tab`, function(e) {
            e.preventDefault();
            const categoryId = $(this).data('category');
            const materialTabsContentId = `materialTabsContent-${productInstanceId}-${variant.product_id}-room${roomId}`;

            console.log('Material category tab clicked:', categoryId);

            // Deactivate all category tabs and content
            $(`#${materialTabsId} .material-tab`).removeClass('active');
            $(`#${materialTabsContentId} .material-tab-content`).removeClass('active');

            // Activate current tab and content
            $(this).addClass('active');
            $(`#materialContent-${productInstanceId}-${variant.product_id}-room${roomId}-${categoryId}`).addClass('active');

            // Setup label tabs for this category
            setTimeout(() => {
                setupMaterialLabelTabsForCategory(categoryId, productInstanceId, variant.product_id, roomId);
            }, 100);
        });

        // Setup label tabs for ALL material categories with a delay
        setTimeout(() => {
            setupAllMaterialLabelTabs(productInstanceId, variant.product_id, roomId);
        }, 500);
    }

    function setupAllMaterialLabelTabs(productInstanceId, variantId, roomId) {
        console.log('Setting up label tabs for all material categories...');

        // Find all material category containers
        $(`[id^="materialContent-${productInstanceId}-${variantId}-room${roomId}-"]`).each(function() {
            const $categoryContent = $(this);
            const idParts = $categoryContent.attr('id').split('-');
            const categoryId = idParts[idParts.length - 1];

            console.log('Found category content:', categoryId);

            // Setup label tabs for this category
            setupMaterialLabelTabsForCategory(categoryId, productInstanceId, variantId, roomId);
        });
    }

    function setupMaterialLabelTabsForCategory(categoryId, productInstanceId, variantId, roomId) {
        const labelTabsId = `${categoryId}LabelTabs-${productInstanceId}-${variantId}-room${roomId}`;
        const $labelTabs = $(`#${labelTabsId}`);

        if ($labelTabs.length) {
            console.log('Setting up label tabs for category:', categoryId, 'with ID:', labelTabsId);

            // Remove existing handlers to prevent duplicates
            $(document).off('click', `#${labelTabsId} .material-label-tab`);

            // Add new handler using event delegation
            $(document).on('click', `#${labelTabsId} .material-label-tab`, function(e) {
                e.preventDefault();
                e.stopPropagation();

                const label = $(this).data('label');
                const category = $(this).data('category');
                console.log('Material label tab clicked:', label, 'for category:', category);

                const $labelContent = $(`#${category}LabelContent-${productInstanceId}-${variantId}-room${roomId}`);

                if (!$labelContent.length) {
                    console.error('Label content not found for category:', category);
                    return;
                }

                // Deactivate all label tabs and content
                $labelTabs.find('.material-label-tab').removeClass('active');
                $labelContent.find('.material-label-tab-content').removeClass('active');

                // Activate current label tab and content
                $(this).addClass('active');
                $(`#${category}LabelContent-${productInstanceId}-${variantId}-room${roomId}-${label}`).addClass('active');
            });

            // Activate first label tab if it exists
            setTimeout(() => {
                const $firstLabelTab = $labelTabs.find('.material-label-tab').first();
                if ($firstLabelTab.length) {
                    $firstLabelTab.trigger('click');
                }
            }, 100);
        } else {
            console.log('No label tabs found for category:', categoryId, 'retrying in 300ms');
            // Retry if tabs aren't loaded yet
            setTimeout(() => setupMaterialLabelTabsForCategory(categoryId, productInstanceId, variantId, roomId), 300);
        }
    }

    // Function to update variant status indicator
    function updateVariantStatus(productId, roomId, variantId, availableIn, type) {
        let $statusIndicator;

        if (availableIn === 'size') {
            // For radio buttons
            $statusIndicator = $(`#variant-radio-${productId}-${variantId}-room${roomId}`)
                .closest('.variant-radio-label')
                .find('.status-indicator');
        } else {
            // For tabs
            $statusIndicator = $(`#variants-tabs-${productId}-room${roomId} .product-variant-tab[data-variant="${variantId}"]`)
                .find('.status-indicator');
        }

        const $content = $(`#variant-${productId}-${variantId}-room${roomId}`);

        // Check main variant fields
        const width = $content.find('.dimension-width').val();
        const length = $content.find('.dimension-length').val();

        // Check active material category
        const activeMaterialTab = $content.find('.material-tab.active');
        const activeCategory = activeMaterialTab.data('category');

        let materialComplete = false;

        if (activeCategory === 'pillow') {
            // For pillow category
            let allPillowComplete = true;
            const $pillowTabs = $(`#pillowTabs-${productId}-${variantId}-room${roomId}`);
            if ($pillowTabs.length) {
                $pillowTabs.find('.pillow-subcategory-tab').each(function() {
                    const $pillowStatus = $(this).find('.status-indicator');
                    if (!$pillowStatus.hasClass('status-complete')) {
                        allPillowComplete = false;
                        return false;
                    }
                });
                materialComplete = allPillowComplete;
            }
        } else {

            // For other material categories - check each material group
            const $materialContent = $(`#materialContent-${productId}-${variantId}-room${roomId}-${activeCategory}`);
            let allGroupsComplete = true;
            let hasGroups = false;

            // Check material groups
            $materialContent.find('.material-group').each(function() {
                hasGroups = true;
                const $group = $(this);
                const materialType = $group.find('.material-type-select').val();
                const areaWeight = $group.find('.area-weight').val();

                if (!materialType) {
                    allGroupsComplete = false;
                }
            });

            // If no groups, check single inputs
            if (!hasGroups) {
                const materialType = $materialContent.find('.material-type-select').val();
                const areaWeight = $materialContent.find('.area-weight').val();
                materialComplete = !!(materialType);
            } else {
                materialComplete = allGroupsComplete;
            }
        }

        // Check if this is a curtain product for additional options
        const isCurtainProduct = type === 'curtain';

        let curtainOptionsComplete = true;
        if (isCurtainProduct) {
            const openingDirection = $content.find('.opening-direction').val();
            const openWith = $content.find('.open-with').val();

            curtainOptionsComplete = !!(openingDirection && openWith);
        }

        $statusIndicator.removeClass('status-empty status-incomplete status-complete');

        const basicComplete = !!(width && length && materialComplete);
        const allComplete = isCurtainProduct ? (basicComplete && curtainOptionsComplete) : basicComplete;

        if (!width && !length && !materialComplete && (!isCurtainProduct || !curtainOptionsComplete)) {
            $statusIndicator.addClass('status-empty');
        } else if (allComplete) {
            $statusIndicator.addClass('status-complete');
        } else {
            $statusIndicator.addClass('status-incomplete');
        }
    }

    // Show Main Category Modal
    function showMainCategoryModal(roomId) {
        console.log('Opening main category modal for room:', roomId);
        state.currentRoom = roomId;
        state.selectedMainCategory = null;
        $('#mainCategoryModal').fadeIn(300);
        $('#mainCategoryOptions .qualification-option').removeClass('selected');
        $('#confirmMainCategory').prop('disabled', true);
        $('#mainCategorySearch').val('');
        $('.qualification-option').show();
    }

    // Hide Main Category Modal
    function hideMainCategoryModal() {
        $('#mainCategoryModal').fadeOut(300);
        $('#catProductOptions').html('');
    }

    // Filter qualifications by main category
    function getQualificationsByMainCategory(mainCategoryId) {
        return qualifications.filter(qual => qual.main_category_id === mainCategoryId);
    }

    // Qualification Modal to show filtered qualifications
    function showQualificationModalFiltered(mainCategoryId, roomId) {
        sofasubmenu = false;
        console.log('Opening qualification modal for main category:', mainCategoryId, 'room:', roomId);

        const $optionsContainer = $('#qualificationOptions');
        $optionsContainer.empty();
        // Show loading state
        $optionsContainer.html(`
            <div class="loading-state">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
                  <p>Loading qualifications...</p>
            </div>
         `);

        $.post(ajax_url + '/api', {
            get_qualifications: 1,
            web_menu_id: mainCategoryId
        }, function(data) {
            // Clear loading state
            $optionsContainer.empty();
            // console.log(data);
            var response = $.parseJSON(data);
            console.log(response);
            if (response.status === 'success') {
                const filteredQualifications = response.data;
                filteredQualifications.forEach((qual, index) => {
                    let sofa_types = '#L Shape Sofa #U Shape Sofa #Semi Circle Sofa #Straight Sofa';
                    let display_class = sofa_types.includes(qual.attr_name) ? ' d-none' : '';
                    const $option = $(`
                     <div class="qualification-option${display_class}" data-qualification="${qual.attr_ids}" data-product-names="${qual.product_names}">
                        <div class="qualification-select-option-image">
                           <img src="<?= URL ?>/uploads/online-img/${qual.online_product_img}" alt="${qual.attr_name}" onerror="this.src='https://picsum.photos/200/200?random=fallback${qual.attr_name}'">
                        </div>
                        <div class="qualification-option-name">${qual.attr_name}</div>
                     </div>
                  `);

                    // Create individual tooltip for each option
                    const $tooltip = $(`
                     <div class="qualification-tooltip">
                     <strong>${qual.attr_name}</strong><br>
                     ${qual.attr_web_title || ''}
                     </div>
                  `);

                    $('body').append($tooltip);

                    let hoverTimeout;

                    $option.hover(
                        function(e) {
                            clearTimeout(hoverTimeout);
                            const $this = $(this);

                            hoverTimeout = setTimeout(() => {
                                positionTooltip($tooltip, $this, e);
                                $tooltip.addClass('visible');
                            }, 100);
                        },
                        function() {
                            clearTimeout(hoverTimeout);
                            $tooltip.removeClass('visible');
                        }
                    );

                    $option.click(function() {
                        $('.qualification-option').removeClass('selected');
                        $(this).addClass('selected');
                        state.selectedQualification = $(this).data('qualification');
                        $('#confirmQualificationSelect').prop('disabled', false);
                    });

                    $optionsContainer.append($option);
                });
            }
        });

        state.currentRoom = roomId;
        state.selectedQualification = null;
        $('#qualificationModal').fadeIn(300);
        $('#qualificationOptions .qualification-option').removeClass('selected');
        $('#confirmQualificationSelect').prop('disabled', true);
        $('#qualificationSearch').val('');
        $('.qualification-option').show();
    }

    // Search functionality for main categories
    function filterMainCategoryOptions(searchTerm) {
        const $options = $('.qualification-option', '#mainCategoryOptions');
        $options.hide();

        // Filter existing options immediately
        const searchLower = searchTerm.toLowerCase();
        let hasMatches = false;

        $options.each(function() {
            const $option = $(this);
            let attr_names = $(this).data('attr-names') ? $(this).data('attr-names').toLowerCase() : '';
            let product_names = $(this).data('product-names') ? $(this).data('product-names').toLowerCase() : '';
            const name = $option.find('.qualification-option-name').text().toLowerCase();

            if (name.includes(searchLower) || attr_names.includes(searchLower) || product_names.includes(searchLower)) {
                $option.show();
                hasMatches = true;
            }
        });

        // If empty search, show all and don't load products
        if (!searchTerm || searchTerm.trim() === '') {
            $options.show();
            return;
        }

        loadProductOptionsToDiv(searchTerm.trim(), 'catProductOptions');
    }

    // Function to load product options WITHOUT Promise
    function loadProductOptionsToDiv(searchTerm, targetDivId) {
        const $targetDiv = $(`#${targetDivId}`);
        if (!$targetDiv.length) {
            $targetDiv.html('');
            console.error(`Target div #${targetDivId} not found`);
            return;
        }

        // If search term is empty, clear product results and return
        if (!searchTerm || searchTerm.trim() === '') {
            $targetDiv.html('');
            return;
        }

        $targetDiv.html(`
        <div class="product-loading">
            <i class="fa fa-spinner fa-spin"></i>
            <span>Searching products...</span>
        </div>
    `);

        $.post(ajax_url + '/api', {
            get_products: 1,
            search_text: searchTerm,
            item_size: 12,
            current_count: 0
        }, function(data) {
            // Remove loading indicator
            $targetDiv.find('.product-loading').remove();

            try {
                const response = typeof data === 'string' ? JSON.parse(data) : data;

                if (response.status === 'success') {
                    const products = response.data['products'] || [];
                    const searchType = response.data['search_type'];

                    // Add new product results
                    products.forEach(product => {
                        const other_buttons = searchType !== 'special' ? getOtherRelatedButtons(product) : ['', ''];
                        const more_related_button = other_buttons[0];
                        const plan_create_button = other_buttons[1];

                        const productHTML = `
                        <div class="multi-select-option" data-product-id="${product.product_id}" data-attr-id="${product.attr_id}">
                            <small class="brand-tag">
                                ${product.catalog_name || 'Unknown'}
                            </small>
                            <div class="multi-select-option-header">
                                <div class="multi-select-option-image">
                                    <img src="${product.product_img}" alt="${product.product_name}" onerror="this.src='https://via.placeholder.com/150'">
                                </div>
                            </div>
                            <div class="multi-select-option-meta text-center">
                                <small class="name-tag">
                                    ${product.product_name}
                                </small>
                                <small class="price-tag d-none">
                                    $${Number(product.standart_price || 0).toFixed(2)}
                                </small>
                                <div class="related-btn-div">
                                    ${more_related_button}${plan_create_button}
                                </div>
                            </div>
                        </div>
                    `;
                        $targetDiv.append(productHTML);
                    });
                }
            } catch (error) {
                // Remove loading indicator
                $targetDiv.find('.product-loading').remove();
                console.error('Error parsing response:', error);
            }
        }).fail(function(xhr, status, error) {
            // Remove loading indicator
            $targetDiv.find('.product-loading').remove();
            console.log('Network error. Please check your connection.');
        });
    }

    // Add styles to the page
    if (!$('#search-styles').length) {
        $('head').append(`<style id="search-styles">${searchStyles}</style>`);
    }

    function positionTooltip($tooltip, $element, event) {
        const elementRect = $element[0].getBoundingClientRect();
        const tooltipRect = $tooltip[0].getBoundingClientRect();
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight
        };

        // Default position (above element)
        let top = elementRect.top - tooltipRect.height - 10;
        let left = elementRect.left + (elementRect.width / 2) - (tooltipRect.width / 2);

        // Check boundaries and adjust
        if (top < 10) {
            // Doesn't fit above, show below
            top = elementRect.bottom + 10;
        }

        if (left < 10) {
            left = 10;
        } else if (left + tooltipRect.width > viewport.width - 10) {
            left = viewport.width - tooltipRect.width - 10;
        }

        // Final boundary check
        if (top + tooltipRect.height > viewport.height - 10) {
            top = viewport.height - tooltipRect.height - 10;
        }

        if (top < 10) {
            top = 10;
        }

        // Position the tooltip
        $tooltip.css({
            top: top + 'px',
            left: left + 'px'
        });
    }

    // function to work for both modals
    function updateAllTooltipPositions() {
        $('.qualification-option').each(function() {
            const $option = $(this);
            // Find the corresponding tooltip - this will work for both modals
            const $tooltip = $('.qualification-tooltip').eq($(this).index());

            if ($option.is(':hover') && $tooltip.hasClass('visible')) {
                positionTooltip($tooltip, $option, null);
            }
        });
    }

    // Listen for window events
    $(window).on('scroll resize', function() {
        updateAllTooltipPositions();
    });

    // Search functionality
    function filterQualificationOptions(searchTerm, applySearchFilterOnProduct = true) {
        $('#qualificationOptions .qualification-option').removeClass('selected');
        const $options = $('.qualification-option');
        $options.hide();

        // Filter existing options immediately
        const searchLower = searchTerm.toLowerCase();
        let hasMatches = false;

        $options.each(function() {
            const $option = $(this);
            let product_names = $(this).data('product-names') ? $(this).data('product-names').toLowerCase() : '';
            const name = $option.find('.qualification-option-name').text().toLowerCase();

            if (name.includes(searchLower) || (applySearchFilterOnProduct && product_names.includes(searchLower))) {
                $option.removeClass('d-none').show();
                hasMatches = true;
            }
        });

        // If empty search, show all and don't load products
        if (!searchTerm || searchTerm.trim() === '') {
            $options.show();
            return;
        }

        if (applySearchFilterOnProduct) {
            loadProductOptionsToDiv(searchTerm.trim(), 'qualProductOptions');
        }
    }

    $('#qualificationSearch').on('input', function() {
        filterQualificationOptions($(this).val());
    });

    // Only hide tooltips when modal closes, don't remove them from DOM
    $('#closeQualificationModal').on('click', function() {
        $('.qualification-tooltip').removeClass('visible'); // Just hide, don't remove
    });

    // Only hide tooltips when confirming, don't remove them from DOM
    $('#confirmQualificationSelect').on('click', function() {
        $('.qualification-tooltip').removeClass('visible'); // Just hide, don't remove
    });

    // Initialize multi-select modal
    function initializeProductSelectModal(qualifications, roomId) {
        const $optionsContainer = $('#multiSelectOptions');
        $optionsContainer.empty();
        state.selectedProducts = [];

        // Apply initial filters
        applyProductFilters(qualifications);
    }

    // Brand Radio Tabs initialization
    function initializeBrandRadioTabs(brands) {
        const $brandTabs = $('#brandRadioTabs');
        $brandTabs.empty();

        // Add "All Brands" option
        const isAllBrandsSelected = !filterState.product.selectedBrand || filterState.product.selectedBrand === '';
        const $allBrandsOption = $(`
            <div class="brand-radio-option">
                  <input type="radio" id="brand-all" name="product-brand-selection" value="" class="brand-radio-input" ${isAllBrandsSelected ? 'checked' : ''}>
                  <label for="brand-all" class="brand-radio-label">
                     <span class="brand-radio-name">All Brands</span>
                  </label>
            </div>
         `);
        $brandTabs.append($allBrandsOption);

        brands.forEach(brand => {
            const isSelected = String(filterState.product.selectedBrand) === String(brand.catalog_id);
            const $brandOption = $(`
                  <div class="brand-radio-option">
                     <input type="radio" id="product-brand-${brand.catalog_id}" name="product-brand-selection" value="${brand.catalog_id}" class="brand-radio-input" ${isSelected ? 'checked' : ''}>
                     <label for="product-brand-${brand.catalog_id}" class="brand-radio-label">
                        <span class="brand-radio-name">${brand.catalog_name}</span>
                     </label>
                  </div>
            `);
            $brandTabs.append($brandOption);
        });
    }

    // Style Checkbox Tabs initialization
    function initializeStyleCheckboxTabs(styles) {
        const $styleTabs = $('#styleCheckboxTabs');
        $styleTabs.empty();

        styles.forEach(style => {
            // Convert both to string for consistent comparison
            const styleIdStr = String(style.id);
            const isSelected = filterState.product.selectedStyles.some(selectedId =>
                String(selectedId) === styleIdStr
            );
            const $styleTab = $(`
                  <div class="style-checkbox-tab product-style-checkbox ${isSelected ? 'selected' : ''}" data-style-id="${style.id}">
                     <div class="style-checkbox"></div>   
                     <span class="style-checkbox-name">${style.type}</span>
                  </div>
            `);

            $styleTabs.append($styleTab);
        });
    }

    // Reset Filters function
    function resetAdvancedFilters() {
        // Reset form values
        $('#minPrice').val(0);
        $('#maxPrice').val(50000);
        $('#priceRange').val(50000);
        $('#newestFirst').prop('checked', true);
        $('#inStock').prop('checked', true);
        $('#outOfStock').prop('checked', false);

        // Reset filter state
        filterState.product.selectedBrand = '';
        filterState.product.selectedStyles = [];
        filterState.product.minPrice = 0;
        filterState.product.maxPrice = 50000;
        filterState.product.dateSort = 'newest';
        filterState.product.inStock = true;
        filterState.product.outOfStock = false;

        // Reset UI elements - PROPERLY
        $('.brand-radio-input[name="product-brand-selection"]').prop('checked', false);
        $('#brand-all').prop('checked', true); // Select "All Brands"

        // Reset style checkboxes visually
        $('.style-checkbox-tab').removeClass('selected');
        $('.style-checkbox').html(''); // Clear check icons

        console.log('Filters reset:', filterState.product);

        // Apply filters immediately after reset
        const qualifications = $('#productSelectModal').data('qualification');
        applyProductFilters(qualifications);
    }

    // Show/Hide Advanced Filter Modal
    function showAdvancedFilterModal() {
        $('#advancedFilterModal').fadeIn(300);
    }

    // Get next room number
    function getNextRoomNumber() {
        if (state.rooms.length === 0) return 1;
        return Math.max(...state.rooms) + 1;
    }

    // Remove room from state
    function removeRoomFromState(roomNumber) {
        state.rooms = state.rooms.filter(num => num !== roomNumber);
    }

    // Renumber all rooms
    function renumberRooms() {
        const $roomTabs = $('#roomTabs .room-tab').get();

        $roomTabs.forEach((tab, index) => {
            const roomNumber = index + 1;
            const roomColor = roomColors[(roomNumber - 1) % roomColors.length];
            const mainColor = roomColor.split(',')[1].trim();

            const $tab = $(tab);
            const oldRoomId = $tab.attr('id').replace('-tab', '');
            const newRoomId = `room${roomNumber}`;

            // Update tab
            $tab.attr('id', `${newRoomId}-tab`);
            $tab.attr('href', `#${newRoomId}`);
            $tab.attr('aria-controls', newRoomId);
            $tab.data('room', roomNumber);
            $tab.find('.room-title').text(`Room ${roomNumber}`);
            $tab.find('.room-header').css('background', roomColor);

            // Update pane
            const $pane = $(`#${oldRoomId}`);
            $pane.attr('id', newRoomId);
            $pane.attr('aria-labelledby', `${newRoomId}-tab`);
            $pane.data('room', roomNumber);
            $pane.find('.product-tabs-header').css('border-left-color', mainColor);

            // Update product containers
            $pane.find('.product-tabs-container').attr('id', `productTabs-room${roomNumber}`);
            $pane.find('.product-content-area').attr('id', `productContent-room${roomNumber}`);

            // Update form fields
            $pane.find('#floorName-' + oldRoomId).attr('id', 'floorName-' + newRoomId);
            $pane.find('#roomName-' + oldRoomId).attr('id', 'roomName-' + newRoomId);
            $pane.find('#roomImage-' + oldRoomId).attr('id', 'roomImage-' + newRoomId).data('room', roomNumber);
            $pane.find('#imagePreview-' + oldRoomId).attr('id', 'imagePreview-' + newRoomId);

            // Update buttons
            $pane.find('.add-item-to-room-btn').data('room', roomNumber);

            // Update product tabs
            $pane.find('.product-tab').each(function() {
                const $productTab = $(this);
                const productId = $productTab.data('product');
                const newTabId = `product-${productId}-room${roomNumber}`;

                $productTab.attr('id', `${newTabId}-tab`);

                const $productContent = $(`#product-${productId}-${oldRoomId}`);
                if ($productContent.length) {
                    $productContent.attr('id', newTabId);
                }
            });
        });

        // Update state
        state.rooms = $roomTabs.map((tab, index) => index + 1);
    }

    function hideQualificationModal() {
        $('#qualificationModal').fadeOut(300);
        $('#qualProductOptions').html('');
    }

    function showProductSelectModal(qualifications, roomId) {
        console.log('Opening multi-select modal for:', qualifications, 'room:', roomId);

        $('#productSelectModal')
            .data('qualification', qualifications)
            .data('roomId', roomId);

        state.currentRoom = roomId;

        initializeProductSelectModal(qualifications, roomId);

        $('#productSelectModal').fadeIn(300);
        $('#multiSelectOptions .multi-select-option').removeClass('selected');
        $('#confirmMultiSelect').prop('disabled', true);
        $('#productSearch').val('');
        $('.multi-select-option').show();
    }

    function hideProductSelectModal() {
        $('#productSelectModal').fadeOut(300);
        state.selectedProducts = [];
        filterState.product.searchTerm = '';
        currentCountProduct = 0;
        $('#productSelectModal').removeData('qualification');
        $('#productSelectModal').removeData('roomId');
    }

    function showItemSelectionModal(productId, roomId) {
        console.log('Opening item selection modal for product:', productId);
        state.currentProductId = productId;
        const tabId = `product-${productId}-room${roomId}-tab`;
        const $tab = $(`#${tabId}`);
        if ($tab.length) {
            state.currentProductName = $tab.find('.product-tab-name').text().trim();
        }

        // Get the current room context
        const $activeProductTab = $('.product-tab.active');
        if ($activeProductTab.length) {
            $('#itemSelectionModal').data('current-room', roomId);
            $('#itemSelectionModal').data('current-product', productId);

            console.log('Current context for item selection:', {
                roomId: roomId,
                productId: productId
            });
        } else {
            console.error('No active product tab found');
            alert('Please select a product tab first.');
            return;
        }

        const $modal = $('#itemSelectionModal');
        const $modalBody = $modal.find('.item-selection-modal-body');

        // Clear existing content
        $modalBody.empty();

        // Create the same filter layout as product modal
        const filterLayoutHTML = `
            <!-- Brand Selection - Horizontal Radio Tabs -->
            <div class="brand-selection-section">
                  <div class="brand-radio-tabs" id="itemBrandRadioTabs">
                     <!-- Brands will be populated here -->
                  </div>
            </div>

            <div class="search-container">
                  <input type="text" class="search-input" id="itemSearch" placeholder="Search items...">
            </div>

            <div class="filter-layout">
                  <!-- Style Selection - Vertical Checkbox Tabs -->
                  <div class="style-filter-sidebar">
                     <div class="style-filter-header">
                        <h6>Styles</h6>
                     </div>
                     <div class="style-checkbox-tabs" id="itemStyleCheckboxTabs">
                        <!-- Styles will be populated here -->
                     </div>
                  </div>

                  <!-- Items Grid -->
                  <div class="products-grid-container">
                     <div class="multi-select-options" id="itemMultiSelectOptions"></div>
                  </div>
            </div>
         `;

        $modalBody.html(filterLayoutHTML);

        // Apply initial filters
        applyItemFilters();

        $modal.fadeIn(300);
        $('#confirmSelectItem').prop('disabled', true);
    }

    // Initialize Brand Radio Tabs for Items
    function initializeItemBrandRadioTabs(brands) {
        const $brandTabs = $('#itemBrandRadioTabs');
        $brandTabs.empty();
        const isAllBrandsSelected = !filterState.item.selectedBrand || filterState.item.selectedBrand === '';
        // Add "All Brands" option
        const $allBrandsOption = $(`
            <div class="brand-radio-option">
                  <input type="radio" id="item-brand-all" name="item-brand-selection" value="" class="brand-radio-input"  ${isAllBrandsSelected ? 'checked' : ''}>
                  <label for="item-brand-all" class="brand-radio-label">
                     <span class="brand-radio-name">All Brands</span>
                  </label>
            </div>
         `);
        $brandTabs.append($allBrandsOption);

        brands.forEach(brand => {
            const isSelected = String(filterState.item.selectedBrand) === String(brand.catalog_id);
            const $brandOption = $(`
               <div class="brand-radio-option">
                  <input type="radio" id="item-brand-${brand.catalog_id}" name="item-brand-selection" value="${brand.catalog_id}" class="brand-radio-input" ${isSelected ? 'checked' : ''}>
                  <label for="item-brand-${brand.catalog_id}" class="brand-radio-label">
                     <span class="brand-radio-name">${brand.catalog_name}</span>
                  </label>
               </div>
            `);
            $brandTabs.append($brandOption);
        });
    }

    // Initialize Style Checkbox Tabs for Items
    function initializeItemStyleCheckboxTabs(styles) {
        const $styleTabs = $('#itemStyleCheckboxTabs');
        $styleTabs.empty();

        styles.forEach(style => {
            // Convert both to string for consistent comparison
            const styleIdStr = String(style.id);
            const isSelected = filterState.item.selectedStyles.some(selectedId =>
                String(selectedId) === styleIdStr
            );
            const $styleTab = $(`
                  <div class="style-checkbox-tab item-style-checkbox ${isSelected ? 'selected' : ''}" data-style-id="${style.id}">
                     <div class="style-checkbox"></div>
                     <span class="style-checkbox-name">${style.type}</span>
                  </div>
            `);

            $styleTabs.append($styleTab);
        });
    }

    const getProductTabNameFirstPart = tabName =>
        tabName?.trim()?.split('-')?.[0]?.trim() || '';

    // Apply Item Filters
    function applyItemFilters(action = null) {
        action = action || '';

        const $optionsContainer = $('#itemMultiSelectOptions');
        const $loadMoreBtn = $('#loadMoreItemsBtn');
        let search_text = '';
        if (filterState.item.searchTerm) {
            search_text = filterState.item.searchTerm;
        }
        if (currentCountItem === 0 || search_text !== '' || action !== '') {
            currentCountItem = 0;
            $optionsContainer.empty();

            // Show loading state
            $optionsContainer.html(`
            <div class="loading-state">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
                  <p>Loading items...</p>
            </div>
         `);
        }

        let itemSize = 12;
        // Show loading state on load more button
        if (currentCountItem > 0) {
            $loadMoreBtn.prop('disabled', true).html(`
                  <i class="fa fa-spinner fa-spin mr-1"></i> Loading...
            `);
        }
        let is_fitout_type = getProductTabNameFirstPart(state.currentProductName).toLowerCase();

        $.post(ajax_url + '/api', {
            get_products: 1,
            is_fitout_items: 1,
            is_fitout_type: is_fitout_type,
            search_text: search_text,
            item_size: itemSize,
            current_count: currentCountItem
        }, function(data) {
            // Reset load more button
            $loadMoreBtn.prop('disabled', false).html(`
                  <i class="fa fa-sync-alt mr-1"></i> Load More Products
            `);
            if (currentCountItem === 0 || search_text !== '' || action !== 'load_more') {
                // Clear loading state
                $optionsContainer.empty();
            }

            try {
                var response = $.parseJSON(data);
                console.log('Items response:', response);

                if (response.status === 'success') {
                    currentCountItem += itemSize;
                    let data = response.data;
                    let brands = data['brands'] || [];
                    let styles = data['styles'] || [];
                    let filteredItems = data['products'] || [];

                    // Initialize item filters
                    initializeItemBrandRadioTabs(brands);
                    initializeItemStyleCheckboxTabs(styles);

                    // Apply brand filter
                    if (filterState.item.selectedBrand && filterState.item.selectedBrand !== '') {
                        filteredItems = filteredItems.filter(item =>
                            String(item.catalog_id) === String(filterState.item.selectedBrand)
                        );
                        console.log('After brand filter:', filteredItems.length);
                    }

                    // Apply style filter
                    if (filterState.item.selectedStyles && filterState.item.selectedStyles.length > 0) {
                        filteredItems = filteredItems.filter(item => {
                            const productStyle = String(item.product_style_type || '');
                            return filterState.item.selectedStyles.some(selectedStyle =>
                                String(selectedStyle) === productStyle
                            );
                        });
                        console.log('After style filter:', filteredItems.length);
                    }

                    // Apply price filter
                    filteredItems = filteredItems.filter(item => {
                        const price = parseFloat(item.standart_price) || 0;
                        return price >= filterState.item.minPrice && price <= filterState.item.maxPrice;
                    });

                    // Apply search filter
                    if (filterState.item.searchTerm) {
                        const searchTerm = filterState.item.searchTerm.toLowerCase();
                        filteredItems = filteredItems.filter(item =>
                            (item.product_code && item.product_code.toLowerCase().includes(searchTerm)) ||
                            (item.product_name && item.product_name.toLowerCase().includes(searchTerm))
                        );
                    }

                    // Apply date sort
                    filteredItems.sort((a, b) => {
                        const dateA = new Date(a.product_add_date || 0);
                        const dateB = new Date(b.product_add_date || 0);
                        return filterState.item.dateSort === 'newest' ? dateB - dateA : dateA - dateB;
                    });

                    // Display filtered items
                    if (filteredItems.length === 0) {
                        $optionsContainer.html(`
                              <div class="no-items-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                                 <i class="fa fa-search" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                                 <p>No items found matching your criteria</p>
                              </div>
                        `);
                        return;
                    }

                    filteredItems.forEach(item => {
                        const $option = $(`
                              <div class="multi-select-option" data-item-id="${item.product_id}" data-attr-id="${item.attr_id}">
                                 <small class="brand-tag" style="background: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem;position:absolute;top:0;right:0;">
                                    ${item.catalog_name || 'Unknown'}
                                 </small>
                                 <div class="multi-select-option-header">
                                    <div class="multi-select-option-image">
                                          <img src="${item.product_img}" alt="${item.product_name}">
                                    </div>
                                 </div>
                                 <div class="multi-select-option-meta text-center">
                                    <small class="name-tag px-1" style="font-weight: 600;">
                                          ${item.product_name}
                                    </small>
                                    <small class="price-tag px-1 d-none" style="color: #4361ee; font-weight: 600;">
                                          $${Number(item.standart_price || 0).toFixed(2)}
                                    </small>
                                 </div>
                              </div>
                        `);
                        $optionsContainer.append($option);
                    });

                } else {
                    $optionsContainer.html(`
                        <div class="no-items-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                              <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                              <p>Error loading items: ${response.message || 'Unknown error'}</p>
                        </div>
                     `);
                }
            } catch (error) {
                console.error('Error processing items response:', error);
                $optionsContainer.html(`
                     <div class="no-items-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p>Error processing items data</p>
                     </div>
                  `);
            }

        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            $optionsContainer.html(`
                  <div class="no-items-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                     <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                     <p>Network error loading items</p>
                  </div>
            `);
        });
    }

    function hideItemSelectionModal() {
        $('#itemSelectionModal').fadeOut(300);
        state.currentProductType = null;
        state.selectedItems = [];
        filterState.item.searchTerm = '';
        currentCountItem = 0;
        // Clear search input when closing
        $('#itemSearch').val('');
    }

    // Show accessory selection modal
    function showAccessorySelectionModal(productId, variantId, attrId, roomId) {
        console.log('Opening accessory selection modal for curtain variant:', {
            productId,
            variantId,
            attrId,
            roomId
        });

        state.currentProductId = productId;
        state.currentVariantId = variantId;
        state.currentRoom = roomId;

        const $modal = $('#accessorySelectionModal');
        const $accessoryOptions = $('#accessoryOptions');

        $accessoryOptions.empty();

        curtainAccessoryTypes.forEach(accessory => {
            const $option = $(`
            <div class="item-option" data-accessory-id="${accessory.id}" data-attr-id="${attrId}">
               <div class="item-option-icon" style="background: ${accessory.color};">
                  <i class="fa ${accessory.icon}"></i>
               </div>
               <div class="item-option-name">${accessory.name}</div>
            </div>
         `);
            $accessoryOptions.append($option);
        });

        $modal.fadeIn(300);
        $('#confirmSelectAccessory').prop('disabled', true);
    }

    const blackOutColors = ['Red', 'Green', 'Blue', 'Black', 'Gray'];

    // Function to add accessory to curtain variant
    function addAccessoryToCurtainVariant(roomId, productId, variantId, accessory, accessoryOptions, existingAccessory = null, existingBlackoutColor = null) {
        console.log('Adding accessory to curtain variant:', {
            roomId,
            productId,
            variantId,
            accessory,
            accessoryOptions,
            existingAccessory
        });

        const $variantContent = $(`#variant-${productId}-${variantId}-room${roomId}`);
        const $tabsContainer = $variantContent.find(`#accessory-tabs-${productId}-${variantId}-room${roomId}`);
        const $emptyState = $tabsContainer.find('.empty-accessory-tabs');
        const $detailsBody = $variantContent.find(`#accessory-details-${productId}-${variantId}-room${roomId} .accessory-details-body`);
        const $emptyDetails = $detailsBody.find('.empty-accessory-selection');
        const existing_accessory_id = existingAccessory ? existingAccessory.id : '';
        const existing_blackout_color = existingBlackoutColor || '';

        if ($emptyState.length) {
            $emptyState.remove();
        }

        const existingTab = $tabsContainer.find(`[data-accessory-id="${accessory.id}"]`);
        if (existingTab.length) {
            console.log('This accessory has already been added. (Curtain variant)');
            alert('This accessory has already been added.');
            return;
        }

        const tabId = `accessory-${accessory.id}-${productId}-${variantId}-room${roomId}`;
        const $tab = $(`
            <div class="accessory-tab" data-accessory-id="${accessory.id}" id="${tabId}-tab">
               <span class="accessory-tab-name">${accessory.name}</span>
               <div class="accessory-tab-close" title="Remove accessory">
                  <i class="fa fa-times"></i>
               </div>
            </div>
         `);

        $tabsContainer.append($tab);

        const colorOptions = blackOutColors.map(color => `
            <option value="${color}" ${existing_blackout_color == color?'selected':''}>${color}</option>
         `).join('');
        let blackoutColorSelector = '';
        if (accessory.name === 'Black Out') {
            blackoutColorSelector = `
            <div class="detail-group">
               <label>Blackout Color</label>
               <select class="form-control accessory-blackout-color">
                  <option value="">Select Color</option>
                  ${colorOptions}
               </select>
            </div>
            `;
        }
        // Accessory details content
        const $detailsContent = $(`
            <div class="accessory-details" id="${tabId}" style="display: none;">
               <div class="enhanced-category-item">
                  <div class="enhanced-item-header">
                     <div class="enhanced-item-name">${accessory.name}</div>
                  </div>
                  <div class="enhanced-details-with-image">
                     <div class="enhanced-image-preview" id="${accessory.id}-preview">
                        <i class="fa fa-image"></i>
                     </div>
                     <div class="enhanced-details-fields">
                        <div class="detail-group">
                           <label>Accessory</label>
                           <div class="accessory-selection-container">
                              <select class="form-control accessory-options-select">
                                 <option value="">Select</option>
                                 ${accessoryOptions.map(option => `
                                    <option value="${option.accessory_id}" data-accessory-type="${accessory.id}" data-price="${option.price}" data-price_type="${option.price_type}" data-price_depends_on="${option.price_depends_on}" data-image="${option.accessory_img}" ${existing_accessory_id == option.accessory_id?'selected':''}>${option.accessory_name}</option>
                                 `).join('')}
                              </select>
                           </div>
                        </div>
                        ${blackoutColorSelector}
                     </div>
                  </div>
               </div>
            </div>
         `);

        if ($emptyDetails.length) {
            $emptyDetails.remove();
        }

        $detailsBody.append($detailsContent);

        // Setup accessory type selection handler
        $(`#${tabId} .accessory-options-select`).on('change', function() {
            const selectedOptionId = $(this).val();
            const previewId = `${accessory.id}-preview`;
            const $preview = $(`#${previewId}`);

            if (selectedOptionId) {
                const $selectedOption = $(this).find('option:selected');
                const img = $selectedOption.data('image');
                $preview.html(`<img src="<?= URL ?>/uploads/material/${img}" alt="Accessory image">`);
                calculateProductTotal(productId, roomId, 'curtain');
            }
        });

        if (existingAccessory) {
            $(`#${tabId} .accessory-options-select`).trigger('change');
        }

        $tab.find('.accessory-tab-close').on('click', function(e) {
            e.stopPropagation();
            removeAccessoryFromCurtainVariant($tab, roomId, productId, variantId, accessory.id);
        });

        // ACTIVATE the newly added tab
        activateCurtainAccessoryTab($tab, roomId, productId, variantId);

        updateVariantStatus(productId, roomId, variantId, 'set', 'curtain');
    }

    function activateCurtainAccessoryTab($tab, roomId, productId, variantId) {
        const accessoryId = $tab.data('accessory-id');
        const $variantContent = $(`#variant-${productId}-${variantId}-room${roomId}`);

        console.log('Activating curtain accessory tab:', {
            accessoryId,
            productId,
            variantId,
            roomId
        });

        // Deactivate all tabs and hide all details in this variant's accessory section
        $variantContent.find('.accessory-tab').removeClass('active');
        $variantContent.find('.accessory-details').hide();

        // Activate current tab and show its details
        $tab.addClass('active');

        const detailsId = `accessory-${accessoryId}-${productId}-${variantId}-room${roomId}`;
        const $details = $(`#${detailsId}`);

        if ($details.length) {
            $details.show();
            console.log('Showing curtain accessory details:', detailsId);
        } else {
            console.error('Curtain accessory details container not found:', detailsId);
            // Debug: Log all available accessory details in this variant
            $variantContent.find('.accessory-details').each(function() {
                console.log('Available accessory detail:', $(this).attr('id'));
            });
        }
    }

    function removeAccessoryFromCurtainVariant($tab, roomId, productId, variantId, accessoryId) {
        console.log('Removing accessory from curtain variant');

        $tab.remove();
        $(`#accessory-${accessoryId}-${productId}-${variantId}-room${roomId}`).remove();

        const $variantContent = $(`#variant-${productId}-${variantId}-room${roomId}`);
        const $tabsContainer = $variantContent.find(`#accessory-tabs-${productId}-${variantId}-room${roomId}`);
        const $detailsBody = $variantContent.find(`#accessory-details-${productId}-${variantId}-room${roomId} .accessory-details-body`);
        const $tabs = $tabsContainer.find('.accessory-tab');

        // Update totals after removal
        calculateProductTotal(productId, roomId, 'curtain');

        if ($tabs.length === 0) {
            $tabsContainer.html(`
            <div class="empty-accessory-tabs">
                  <i class="fa fa-puzzle-piece"></i>
                  <p>No accessories added yet</p>
            </div>
         `);

            $detailsBody.html(`
            <div class="empty-accessory-selection">
                  <i class="fa fa-hand-pointer"></i>
                  <p>Select an accessory to view and edit details</p>
            </div>
         `);
        } else {
            const $firstTab = $tabs.first();
            activateCurtainAccessoryTab($firstTab, roomId, productId, variantId);
        }

        updateVariantStatus(productId, roomId, variantId, 'set', 'fitout');
    }

    function hideAccessorySelectionModal() {
        $('#accessorySelectionModal').fadeOut(300);
        state.currentProductId = null;
        state.selectedAccessory = null;
        state.selectedAccessoryAttrId = null;
    }

    // Activate product tab
    function activateProductTab($tab) {
        const productId = $tab.data('product');
        const roomId = $tab.closest('.tab-pane').data('room');

        console.log('Activating product tab:', {
            productId: productId,
            roomId: roomId
        });

        // Deactivate all tabs in this room
        $(`#productTabs-room${roomId} .product-tab`).removeClass('active');
        $(`#productContent-room${roomId} .product-content`).hide();

        // Activate current tab
        $tab.addClass('active');
        $(`#product-${productId}-room${roomId}`).show();
    }

    // Load product with variants
    function loadProductWithVariants($content, product, variants, roomId, existingData, productInstanceId) {
        console.log('Loading product with variants in loadProductWithVariants:', {
            product,
            variants,
            roomId,
            existingData
        });
        // Create basic details section + variants
        var basicDetailsHTML = '';
        let variantsHTML;
        if (product.available_in == 'set') {
            // basicDetailsHTML = createBasicDetailsSection(product, roomId);
            variantsHTML = createVariantsTabs(product, variants, roomId, existingData, productInstanceId);
        } else {
            variantsHTML = createVariantsRadioSelection(product, variants, roomId, existingData, productInstanceId);
        }

        const $wrapper = $(`
            <div class="product-with-variants">
               ${basicDetailsHTML}
               <div class="product-variants-section">
                  ${variantsHTML}
               </div>
            </div>
         `);

        $content.html($wrapper);

        // Setup the appropriate variant selection method
        if (product.available_in == 'set') {
            setupVariantsTabs(product, variants, roomId, productInstanceId);
        } else {
            setupVariantsRadioSelection(product, variants, roomId, productInstanceId);
        }
    }

    // Setup variants radio selection (for size variants)
    function setupVariantsRadioSelection(product, variants, roomId, productInstanceId) {
        const productId = productInstanceId;
        const $variantsRadio = $(`#variants-radio-${productId}-room${roomId}`);
        const $variantsContent = $(`#variants-content-${productId}-room${roomId}`);

        if (!$variantsRadio.length) return;

        // Radio button change handler
        $variantsRadio.find('.variant-radio-input').on('change', function() {
            const variantId = $(this).val();

            // Hide all variant content
            $variantsContent.find('.product-variant-content').removeClass('active');

            // Show selected variant content
            $(`#variant-${productId}-${variantId}-room${roomId}`).addClass('active');

            updateVariantStatus(productId, roomId, variantId, 'size', product.type);
            setTimeout(() => {
                calculateProductTotal(productId, roomId);
            }, 500);
        });

        // Setup calculations for variants
        setupVariantCalculations(productId, roomId);

        // Setup material tabs for variants
        setupVariantMaterialTabs(product, variants, roomId, productInstanceId);

        // Setup pillow subcategory tabs for variants
        setupVariantPillowSubcategoryTabs(product, variants, roomId, productInstanceId);

        // Activate the first radio by default
        // const $firstRadio = $variantsRadio.find('.variant-radio-input').first();
        // if ($firstRadio.length) {
        //     $firstRadio.trigger('change');
        // }
    }

    // Create variants radio selection (for size variants)
    function createVariantsRadioSelection(product, variants, roomId, existingData, productInstanceId) {
        const productId = productInstanceId; // Define productId here
        const bed_dim = existingData && existingData.bed_dim ? existingData.bed_dim : (existingData && existingData.attr_bed_dim ? '180200' : '0');
        console.log('in createVariantsRadioSelection:', {
            roomId: roomId,
            productId: productId,
            existingData: existingData,
            bed_dim: bed_dim
        })

        return `
            <div class="product-variants-section" id="variants-section-${productId}-room${roomId}">
               <div class="variant-radio-header">
                  <h6><i class="fa fa-ruler mr-2"></i>Select${product.product_bed_dims ? ' Mattress' : ''} Size</h6>
               </div>
               <div class="variant-radio-container" id="variants-radio-${productId}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <div class="variant-radio-option">
                        <input type="radio" 
                              id="variant-radio-${productId}-${variant.id}-room${roomId}" 
                              name="variant-selection-${productId}-room${roomId}" 
                              value="${variant.id}" 
                              ${(variant.id === bed_dim || bed_dim == index) ? 'checked' : ''}
                              class="variant-radio-input">
                        <label for="variant-radio-${productId}-${variant.id}-room${roomId}" 
                              class="variant-radio-label">
                           <span class="variant-radio-name">${variant.name}</span>
                           <span class="status-indicator status-empty"></span>
                        </label>
                     </div>
                  `).join('')}
               </div>
               <div class="product-variants-content" id="variants-content-${productId}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <div class="product-variant-content ${(variant.id === bed_dim || bed_dim == index) ? 'active' : ''}" 
                        id="variant-${productId}-${variant.id}-room${roomId}">
                        ${createVariantContentForSize(product, variant, roomId, existingData, productId, index)}
                     </div>
                  `).join('')}
               </div>
            </div>
         `;
    }

    // Basic details section for products with variants
    function createBasicDetailsSection(product, roomId) {
        return `
            <div class="basic-details-section">
               <div class="compact-product-details">
                     <div class="compact-section-header">
                        <h6><i class="fa fa-info-circle mr-2"></i>Basic Details</h6>
                     </div>
                     <div class="compact-details-with-image">
                        <div class="compact-image-preview">
                           <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                        </div>
                        <div class="compact-details-fields">
                           <div class="compact-detail-group">
                           <span class="detail-label">${product.product_name}</span>
                           </div>
                        </div>
                     </div>
               </div>
            </div>
         `;
    }

    function createTotalsSection(productInstanceId, roomId) {
        return `
         <div class="product-totals-section" id="product-totals-${productInstanceId}-room${roomId}">
            <div class="product-totals-row">
               <div class="product-totals-label">Total:</div>
               <div class="product-totals-amount">
                  $<span class="product-total-price" id="product-total-${productInstanceId}-room${roomId}">0.00</span>
               </div>
            </div>
         </div>
         `;
    }

    function loadFitoutProductContent($content, product, roomId, existingData, productInstanceId, index = 0) {
        console.log('Loading fitout product content in loadFitoutProductContent:', {
            product,
            roomId,
            existingData
        });
        const dims = product.dims || {};
        let width = dims.width || '';
        let length = dims.length || '';
        let height = dims.height || '';
        const standart_price = dims.standart_price || '';
        const unit_price = calculateUnitPrice(product.calculate_type, dims);

        if (existingData && existingData.attr_dims) {
            const attrDims = existingData.attr_dims[index];
            if (attrDims.width) {
                width = attrDims.width;
            }
            if (attrDims.length) {
                length = attrDims.length;
            }
            if (attrDims.height) {
                height = attrDims.height;
            }
        }
        const quantity = existingData && existingData.quantity ? existingData.quantity : 1;
        const discount = existingData && existingData.discount ? existingData.discount : 0;

        const buttonText = `Add Item to ${product.team_name}`;
        const $wrapper = $(`
            <div class="product-details-wrapper">
               <div class="fitout-product-layout">
                  <div class="items-tabs-sidebar">
                     <div class="items-tabs-header">
                        <h6><i class="fa fa-list mr-2"></i>Items</h6>
                        <button type="button" class="btn btn-sm btn-primary add-product-item-btn" data-product="${productInstanceId}" data-type="${product.type}" data-available-in="${product.available_in}" data-room="${roomId}">
                           <i class="fa fa-plus mr-1"></i> ${buttonText}
                        </button>
                     </div>
                     <div class="items-tabs-container">
                        <div class="empty-items-tabs">
                           <i class="fa fa-cube"></i>
                           <p>No items added yet</p>
                        </div>
                     </div>
                  </div>
                  <div class="item-details-content">
                     <div class="product-details-header">
                        <div class="product-header-with-image">
                           <div class="header-image-preview">
                              <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                           </div>
                           <h6><i class="fa fa-info-circle mr-2"></i>${product.team_name} Details</h6>
                        </div>
                        <div class="compact-header-details">
                           <div class="compact-header-group">
                              <label>Width (m)</label>
                              <input type="number" class="form-control dimension-width" value="${width}" data-standart_width="${width}" placeholder="0.00" step="0.01" min="0">
                           </div>
                           <div class="compact-header-group">
                              <label>Length/Height (m)</label>
                              <input type="number" class="form-control dimension-length" value="${length}" data-standart_length="${length}" placeholder="0.00" step="0.01" min="0">
                           </div>
                           <div class="compact-header-group">
                              <label>Quantity</label>
                              <input type="number" class="form-control product-qty" placeholder="0" step="1" min="1" value="${quantity}">
                           </div>
                           <div class="compact-header-group">
                              <label>Discount(%)</label>
                              <input type="number" class="form-control product-discount" placeholder="0" step="0.01" min="0" value="${discount}">
                           </div>
                        </div>
                     </div>
                     <!-- Add base price field for variant -->
                     <input type="hidden" class="standart-price" value="${standart_price}" data-variant="${product.product_id}">
                     <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${product.product_id}">
                     <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${product.product_id}">
                     <div class="product-details-body">
                        <div class="empty-item-selection">
                           <i class="fa fa-hand-pointer"></i>
                           <p>Select an item to view and edit details</p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         `);

        $content.html($wrapper);
    }

    // simple product content with materials and surcharges
    function loadSimpleProductContent($content, product, variant, roomId, existingData, productInstanceId, index = 0) {
        console.log('Loading simple product content in loadSimpleProductContent:', {
            product,
            variant,
            roomId,
            existingData
        });
        const dims = product.dims || {}; // fallback if undefined

        let width = dims.width || '';
        let length = dims.length || '';
        let height = dims.height || '';
        const standart_price = dims.standart_price || '';
        const unit_price = calculateUnitPrice(product.calculate_type, dims);

        if (existingData && existingData.attr_dims) {
            const attrDims = existingData.attr_dims[index];
            if (attrDims.width) {
                width = attrDims.width;
            }
            if (attrDims.length) {
                length = attrDims.length;
            }
            if (attrDims.height) {
                height = attrDims.height;
            }
        }
        const quantity = existingData && existingData.quantity ? existingData.quantity : 1;
        const discount = existingData && existingData.discount ? existingData.discount : 0;
        const notes_tr = existingData && existingData.product_notes_tr ? existingData.product_notes_tr : '';
        const notes_en = existingData && existingData.product_notes_en ? existingData.product_notes_en : '';

        const $wrapper = $(`
            <div class="simple-product-details">
                  <div class="compact-product-details">
                     <div class="compact-section-header">
                        <h6><i class="fa fa-cube mr-2"></i>${product.product_name} Details</h6>
                     </div>
                     <div class="compact-details-with-image">
                        <div class="compact-image-preview">
                              <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                        </div>
                        <div class="compact-details-fields">
                              <div class="compact-detail-group">
                                 <label>Width (m)</label>
                                 <input type="number" class="form-control dimension-width" value="${width}" data-standart_width="${width}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(product.calculate_type, 'w')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Length (m)</label>
                                 <input type="number" class="form-control dimension-length" value="${length}" data-standart_length="${length}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(product.calculate_type, 'l')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Height (m)</label>
                                 <input type="number" class="form-control dimension-height" value="${height}" data-standart_height="${height}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(product.calculate_type, 'h')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Quantity</label>
                                 <input type="number" class="form-control product-qty" 
                                    placeholder="0" step="1" min="1" value="${quantity}">
                              </div>
                              <div class="compact-detail-group">
                                 <label>Discount(%)</label>
                                 <input type="number" class="form-control product-discount" 
                                    placeholder="0" step="0.01" min="0" value="${discount}">
                              </div>
                              <div class="compact-detail-group">
                                 <label>Notes(TR)</label>
                                 <textarea class="form-control product-notes-tr" 
                                    placeholder="Enter notes in Turkish" value="${notes_tr}"></textarea>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Notes(EN)</label>
                                 <textarea class="form-control product-notes-en" 
                                    placeholder="Enter notes in English" value="${notes_en}"></textarea>
                              </div>
                        </div>
                     </div>
                     <!-- Add base price field for variant -->
                     <input type="hidden" class="standart-price" value="${standart_price}" data-variant="${product.product_id}">
                     <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${product.product_id}">
                     <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${product.product_id}">
                     
                     <!-- Material Section - Use existing data -->
                     ${createMaterialSection(product, variant, roomId, existingData, productInstanceId)}
                     
                     <!-- Surcharges Section -->
                     ${createSurchargesSection(product, variant, roomId)}
                  </div>
            </div>
         `);

        $content.html($wrapper);

        // Setup material tabs after content is loaded
        setTimeout(() => {
            setupMaterialTabsForProduct(product, variant, roomId, productInstanceId);
            setupPillowSubcategoryTabsForProduct(product, variant, roomId, productInstanceId);
        }, 1000);
    }

    // Curtain product content with accessory section working like items section
    function loadCurtainProductContent($content, product, variant, roomId, productInstanceId, existingData) {
        console.log('Loading curtain product content in loadCurtainProductContent:', {
            product,
            variant,
            roomId,
            existingData
        });
        const dims = product.dims || {};
        const unit_price = calculateUnitPrice(product.calculate_type, dims);
        const quantity = existingData && existingData.quantity ? existingData.quantity : 1;
        const discount = existingData && existingData.discount ? existingData.discount : 0;
        const open_with = existingData?.curtain_data ? existingData.curtain_data[1].configuration.open_with : '';
        const opening_direction = existingData?.curtain_data ? existingData.curtain_data[1].configuration.opening_direction : '';
        const installation_needed = existingData?.curtain_data ? existingData.curtain_data[1].configuration.installation === 'needed' : false;
        // Use a flag to track if handlers are already set up

        // Clean up curtain handlers
        const productKey = `curtain-${productInstanceId}-room${roomId}`;
        if (window.curtainHandlers && window.curtainHandlers[productKey]) {
            console.log('Curtain handlers already set up for:', productKey);
            return;
        }

        const $wrapper = $(`
            <div class="simple-product-details">
                <div class="compact-product-details">
                    <div class="compact-section-header">
                        <h6><i class="fa fa-cube mr-2"></i>${product.product_name} Details</h6>
                    </div>
                    <div class="compact-details-with-image">
                        <div class="compact-image-preview">
                            <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                            <!-- Image upload overlay for curtain products -->
                            <div class="image-upload-overlay">
                                <label class="image-upload-label">
                                    <i class="fa fa-camera"></i>
                                    <input type="file" class="product-image-input d-none" 
                                        data-product="${productInstanceId}" 
                                        data-variant="${variant.product_id}"
                                        data-room="${roomId}"
                                        accept="image/*">
                                </label>
                            </div>
                        </div>
                        <div class="compact-details-fields">
                            <div class="compact-detail-group">
                                <label>Quantity</label>
                                <input type="number" class="form-control product-qty" 
                                    placeholder="0" step="1" min="1" value="${quantity}"
                                    data-variant="${variant.product_id}">
                            </div>
                            <div class="compact-detail-group">
                                <label>Discount(%)</label>
                                <input type="number" class="form-control product-discount" 
                                    placeholder="0" step="0.01" min="0" value="${discount}"
                                    data-variant="${variant.product_id}">
                            </div>
                            <div class="compact-detail-group">
                                <label>Notes(TR)</label>
                                <textarea class="form-control product-notes-tr" 
                                    placeholder="Enter notes in Turkish"
                                    data-variant="${variant.product_id}">${existingData && existingData.product_notes_tr ? existingData.product_notes_tr : ''}</textarea>
                            </div>
                            <div class="compact-detail-group">
                                <label>Notes(EN)</label>
                                <textarea class="form-control product-notes-en" 
                                    placeholder="Enter notes in English"
                                    data-variant="${variant.product_id}">${existingData && existingData.product_notes_en ? existingData.product_notes_en : ''}</textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Add base price field -->
                    <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${product.product_id}">
                    <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${product.product_id}">

                    <!-- Material Section -->
                    ${createMaterialSection(product, variant, roomId, existingData, productInstanceId)}

                    <div class="curtain-options-section">
                        <h6><i class="fa fa-cog mr-2"></i>Curtain Options</h6>
                        <div class="curtain-controls">
                            <div class="curtain-control">
                                <label>Opening Direction</label>
                                <select class="form-control opening-direction">
                                    <option value="" ${opening_direction === '' ? 'selected' : ''}>Select Direction</option>
                                    <option value="two" ${opening_direction === 'two' ? 'selected' : ''}>Two Directions</option>
                                    <option value="left" ${opening_direction === 'left' ? 'selected' : ''}>Left Opening</option>
                                    <option value="right" ${opening_direction === 'right' ? 'selected' : ''}>Right Opening</option>
                                </select>
                            </div>
                            <div class="curtain-control">
                                <label>Open With</label>
                                <select class="form-control open-with">
                                    <option value="" ${open_with === '' ? 'selected' : ''}>Select Option</option>
                                    <option value="motor" ${open_with === 'motor' ? 'selected' : ''}>Motor</option>
                                    <option value="manual" ${open_with === 'manual' ? 'selected' : ''}>Manual</option>
                                </select>
                            </div>
                            <div class="curtain-control installation-control">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input curtain-installation-needed-checkbox" 
                                        id="curtain-installation${productInstanceId}${roomId}" 
                                        value="needed" ${installation_needed ? 'checked' : ''}>
                                    <label class="custom-control-label font-weight-bold" 
                                        for="curtain-installation${productInstanceId}${roomId}">
                                        <i class="fa fa-tools mr-2"></i>Installation Needed
                                        <i class="fa fa-info-circle text-muted ml-1" 
                                        data-toggle="tooltip" 
                                        data-placement="top" 
                                        title="Professional installation service - $200"></i>
                                        <span class="installation-price text-success ml-2 font-weight-bold" style="${!installation_needed?'display:none;':''}">+ $200</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <h6 style="margin-top: 16px;"><i class="fa fa-plus-circle mr-2"></i>Accessories</h6>
                        <div class="accessory-layout">
                            <div class="accessory-tabs-sidebar">
                                <div class="accessory-tabs-header">
                                    <h6><i class="fa fa-list mr-2"></i>Accessories</h6>
                                    <button type="button" class="btn btn-sm btn-primary add-accessory-btn" data-product="${productInstanceId}" data-attr-id="${product.attr_id}" data-variant="" data-room="${roomId}">
                                        <i class="fa fa-plus mr-1"></i> Add
                                    </button>
                                </div>
                                <div class="accessory-tabs-container">
                                    <div class="empty-accessory-tabs">
                                        <i class="fa fa-puzzle-piece"></i>
                                        <p>No accessories added yet</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accessory-details-content">
                                <div class="accessory-details-header">
                                    <div class="product-header-with-image">
                                        <div class="header-image-preview">
                                            <i class="fa fa-puzzle-piece"></i>
                                        </div>
                                        <h6><i class="fa fa-info-circle mr-2"></i>Accessory Details</h6>
                                    </div>
                                </div>
                                <div class="accessory-details-body">
                                    <div class="empty-accessory-selection">
                                        <i class="fa fa-hand-pointer"></i>
                                        <p>Select an accessory to view and edit details</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Surcharges Section -->
                    ${createSurchargesSection(product, variant, roomId)}
                </div>
            </div>
        `);

        $content.html($wrapper);

        // Set up curtain-specific handlers with proper cleanup
        setupCurtainProductHandlers(product, roomId, productInstanceId);

        // Initialize handler tracking
        if (!window.curtainHandlers) window.curtainHandlers = {};
        window.curtainHandlers[productKey] = true;

        console.log('Curtain product content loaded with optimized handlers');
    }

    function setupCurtainProductHandlers(product, roomId, productInstanceId) {
        const productId = productInstanceId;
        const $productContent = $(`#product-${productId}-room${roomId}`);

        if (!$productContent.length) {
            console.error('Product content not found for handler setup');
            return;
        }

        // Use event delegation with more specific selectors
        const $curtainSection = $productContent.find('.curtain-options-section');

        // Remove any existing handlers first
        $curtainSection.off('change', '.opening-direction');
        $curtainSection.off('change', '.open-with');
        $curtainSection.off('change', '.curtain-installation-needed-checkbox');

        // Setup optimized change handlers with debouncing
        $curtainSection.on('change', '.opening-direction', debounce(function() {
            console.log('Opening direction changed');
            calculateProductTotal(productId, roomId, 'curtain');
        }, 300));

        $curtainSection.on('change', '.open-with', debounce(function() {
            console.log('Open with changed');
            calculateProductTotal(productId, roomId, 'curtain');
        }, 300));

        $curtainSection.on('change', '.curtain-installation-needed-checkbox', function() {
            const $checkbox = $(this);
            updateCurtainInstallationPrice($checkbox);
        });
    }

    // Debounce function to prevent excessive recalculations
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Update the material type select change handler to work with both products and items
    $(document).on('change', '.material-type-select', function() {
        const $option = $(this).find('option:selected');
        const unitPrice = parseFloat($option.data('price')) || 0;
        const image = $option.data('image') || '';
        const subcategory = $(this).data('subcategory') || '';
        const isItem = $(this).data('item'); // Check if this is for an item

        // Detect category prefix (e.g., 'pillow')
        const cat = subcategory !== '' ? 'pillow' : '';
        const prefix = isItem ? `${cat}${cat ? '-' : ''}` : `${cat}${cat ? '-' : ''}`;

        // Build proper class selectors with dot separation
        const $materialGroup = $(this).closest(`.${prefix}material-group, .${prefix}material-inputs-compact`);
        const $imageContainer = $materialGroup.find(`.${prefix}material-compact-image`);

        // Update image
        if (image) {
            $imageContainer.html(
                `<img src="<?= URL ?>/uploads/material/${image}" alt="${$option.text()}" style="width:100%;height:100%;object-fit:cover;">`
            );
        } else {
            $imageContainer.html('<i class="fa fa-image"></i>');
        }
    });
    // Update the material type select change handler to work with both products and items
    $(document).on('change', '.material-replacement', function() {
        const $option = $(this).find('option:selected');
        const unitPrice = parseFloat($option.data('price')) || 0;
        const image = $option.data('image') || '';
        const subcategory = $(this).data('subcategory') || '';
        const isItem = $(this).data('item'); // Check if this is for an item

        // Detect category prefix (e.g., 'pillow')
        const cat = subcategory !== '' ? 'pillow' : '';
        const prefix = isItem ? `${cat}${cat ? '-' : ''}` : `${cat}${cat ? '-' : ''}`;

        // Build proper class selectors with dot separation
        const $materialGroup = $(this).closest(`.${prefix}material-group, .${prefix}material-inputs-compact-replacement`);
        const $imageContainer = $materialGroup.find(`.${prefix}material-compact-image-replacement`);

        // Update image
        if (image) {
            $imageContainer.html(
                `<img src="<?= URL ?>/uploads/material/${image}" alt="${$option.text()}" style="width:100%;height:100%;object-fit:cover;">`
            );
        } else {
            $imageContainer.html('<i class="fa fa-image"></i>');
        }
    });

    function createMaterialSection(product, variant, roomId, existingData, productInstanceId) {
        // Use the materials data that already comes with the product/variant
        const activeMaterials = product.available_in === 'size' ? (product.active_materials || {}) : (variant.active_materials || {});
        const productName = product.available_in === 'size' ? product.product_name : variant.product_name;
        const variantId = product.available_in === 'size' ? variant.id : variant.product_id;

        console.log('Creating material section:', {
            activeMaterials: activeMaterials,
            productName: productName,
            variantId: variantId
        });

        // Get available material categories from activeMaterials
        const materialCategories = Object.keys(activeMaterials).filter(cat =>
            activeMaterials[cat] &&
            (activeMaterials[cat].materialGroups || activeMaterials[cat].active || activeMaterials[cat].all_materials)
        ).map(category => {
            return {
                id: category,
                name: category.charAt(0).toUpperCase() + category.slice(1),
                active_materials: activeMaterials[category]
            };
        });

        // If no materials available, show message
        if (materialCategories.length === 0) {
            return `
            <div class="material-section">
                <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${productName}</h6>
                <div class="no-materials-available">
                    <i class="fa fa-info-circle"></i>
                    <p>No materials found for this product</p>
                </div>
            </div>
        `;
        }

        return `
        <div class="material-section">
            <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${productName}</h6>
            <div class="material-tabs" id="materialTabs-${productInstanceId}-${variantId}-room${roomId}">
                ${materialCategories.map((category, index) => `
                    <button class="material-tab ${index === 0 ? 'active' : ''}" 
                          data-category="${category.id}">
                        ${category.name}
                    </button>
                `).join('')}
            </div>
            <div class="material-tabs-content" id="materialTabsContent-${productInstanceId}-${variantId}-room${roomId}">
                ${materialCategories.map((category, index) => `
                    <div class="material-tab-content ${index === 0 ? 'active' : ''}" 
                       id="materialContent-${productInstanceId}-${variantId}-room${roomId}-${category.id}">
                       ${createMaterialContentWithLabels(product, variant, category, roomId, activeMaterials, productInstanceId, existingData)}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    }

    // Add this new function to edit script
    function createMaterialContentWithLabels(product, variant, category, roomId, activeMaterials, productInstanceId, existingData) {
        const categoryData = activeMaterials[category.id];
        const variantId = product.available_in === 'size' ? variant.id : variant.product_id;

        console.log('Creating material content with labels for category:', {
            category: category.id,
            categoryData: categoryData,
            variantId: variantId
        });

        if (!categoryData) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No materials available for this category</p>
            </div>
        `;
        }

        // Check if this is a pillow category - show special pillow structure
        if (category.id === 'pillow') {
            return createPillowStructure(product, variant, categoryData, roomId, productInstanceId, variantId, existingData);
        }

        // For non-pillow categories
        // Get material groups or active materials to determine label tabs
        const materialGroups = categoryData.materialGroups || {};
        const activeMaterialsList = categoryData.active || [];

        // Determine label tabs based on available data
        let labelTabs = [];

        if (Object.keys(materialGroups).length > 0) {
            // Use material group labels (A, B, C, etc.)
            labelTabs = Object.keys(materialGroups).map((label, index) => ({
                label: label,
                index: index + 1
            }));
        } else if (activeMaterialsList.length > 0) {
            // Create numeric labels based on active materials count
            for (let i = 1; i <= activeMaterialsList.length; i++) {
                labelTabs.push({
                    label: i.toString(),
                    index: i
                });
            }
        } else {
            // Default to single tab
            labelTabs.push({
                label: '1',
                index: 1
            });
        }

        if (labelTabs.length === 0) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No materials available for this category</p>
            </div>
        `;
        }

        return `
        <div class="material-label-tabs-container">
            <div class="material-label-tabs" id="${category.id}LabelTabs-${productInstanceId}-${variantId}-room${roomId}">
                ${labelTabs.map((labelTab, index) => `
                    <button class="material-label-tab ${index === 0 ? 'active' : ''}" 
                          data-label="${labelTab.label}" 
                          data-category="${category.id}"
                          data-label-index="${labelTab.index}"
                          id="${category.id}-label-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}">
                        ${category.name} ${labelTab.label}
                    </button>
                `).join('')}
            </div>
            <div class="material-label-content" id="${category.id}LabelContent-${productInstanceId}-${variantId}-room${roomId}">
                ${labelTabs.map((labelTab, index) => `
                    <div class="material-label-tab-content ${index === 0 ? 'active' : ''}" 
                       id="${category.id}LabelContent-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}">
                       ${createMaterialLabelContent(product, variant, category.id, labelTab.label, categoryData, roomId, productInstanceId, variantId, existingData)}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    }

    // Add this function to your edit script
    function createPillowStructure(product, variant, pillowData, roomId, productInstanceId, variantId, existingData) {
        // Determine label tabs
        let labelTabs = [];
        const firstPillowType = Object.keys(pillowData)[0];
        const materialGroups = pillowData[firstPillowType]?.materialGroups || {};

        if (Object.keys(materialGroups).length > 0) {
            labelTabs = Object.keys(materialGroups).map((label, index) => ({
                label: label,
                index: index + 1
            }));
        } else {
            const maxEntries = Math.max(...Object.values(pillowData).map(data =>
                data.active ? data.active.length : 0
            ), 1);

            for (let i = 1; i <= maxEntries; i++) {
                labelTabs.push({
                    label: i.toString(),
                    index: i
                });
            }
        }

        if (labelTabs.length === 0) {
            labelTabs.push({
                label: '1',
                index: 1
            });
        }

        // Define all pillow subcategories
        const pillowSubcategories = [{
                id: 'default',
                name: 'Default Pillow'
            },
            {
                id: 'front',
                name: 'Pillow Front'
            },
            {
                id: 'back',
                name: 'Pillow Back'
            },
            {
                id: 'piping',
                name: 'Pillow Piping'
            }
        ];

        // Filter to only show subcategories that have data in pillowData
        const availableSubcategories = pillowSubcategories.filter(subcat =>
            pillowData[subcat.id] &&
            (pillowData[subcat.id].materialGroups ||
                pillowData[subcat.id].active ||
                pillowData[subcat.id].all_materials)
        );

        return `
        <div class="material-label-tabs-container">
            <div class="material-label-tabs" id="pillowLabelTabs-${productInstanceId}-${variantId}-room${roomId}">
                ${labelTabs.map((labelTab, index) => `
                <button class="material-label-tab ${index === 0 ? 'active' : ''}"
                    data-label="${labelTab.label}"
                    data-category="pillow"
                    data-label-index="${labelTab.index}"
                    id="pillow-label-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}">
                    Pillow ${labelTab.label}
                </button>
                `).join('')}
            </div>
            <div class="material-label-content" id="pillowLabelContent-${productInstanceId}-${variantId}-room${roomId}">
                ${labelTabs.map((labelTab, index) => `
                <div class="material-label-tab-content ${index === 0 ? 'active' : ''}"
                    id="pillowLabelContent-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}">
                    <div class="pillow-subcategories-section">
                        <div class="pillow-subcategories-tabs" id="pillowSubTabs-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}">
                            ${availableSubcategories.map((subcat, subIndex) => `
                            <button class="pillow-subcategory-tab ${subIndex === 0 ? 'active' : ''}"
                                data-subcategory="${subcat.id}"
                                data-label="${labelTab.label}"
                                data-product="${productInstanceId}"
                                data-variant="${variantId}"
                                data-room="${roomId}"
                                id="pillow-sub-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}-${subcat.id}">
                                <div class="pillow-subcategory-header">
                                    <span class="status-indicator status-empty"></span>
                                    <span class="pillow-subcategory-title">${subcat.name}</span>
                                </div>
                            </button>
                            `).join('')}
                        </div>
                        <div class="pillow-subcategories-content" id="pillowSubContent-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}">
                            ${availableSubcategories.map((subcat, subIndex) => `
                            <div class="pillow-subcategory-content ${subIndex === 0 ? 'active' : ''}"
                                id="pillowSubcategory-${productInstanceId}-${variantId}-room${roomId}-${labelTab.label}-${subcat.id}">
                                <div class="pillow-subcategory-details">
                                    ${createPillowSubcategoryContent(product, variant, subcat, labelTab.label, pillowData, roomId, productInstanceId, variantId, existingData)}
                                </div>
                            </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                `).join('')}
            </div>
        </div>
        `;
    }

    function createPillowSubcategoryContent(product, variant, subcat, label, pillowData, roomId, productInstanceId, variantId, existingData) {
        const subcatData = pillowData[subcat.id];

        if (!subcatData) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No materials available for ${subcat.name}</p>
            </div>
            `;
        }

        const materialGroups = subcatData.materialGroups || {};
        const activeMaterials = subcatData.active || [];
        const allMaterials = subcatData.all_materials || [];

        console.log('Creating pillow subcategory content:', {
            subcategory: subcat.id,
            label: label,
            materialGroups: materialGroups,
            activeMaterials: activeMaterials,
            existingData: existingData
        });

        // Find the material for this specific label
        let materialsForLabel = [];
        if (materialGroups[label]) {
            materialsForLabel = materialGroups[label];
        } else if (activeMaterials.length > 0) {
            // If no materialGroups but we have active materials, use the one at the label index
            const labelIndex = parseInt(label.replace(/\D/g, '')) - 1 || 0;
            if (activeMaterials[labelIndex]) {
                materialsForLabel = [activeMaterials[labelIndex]];
            }
        }

        // Check for existing material data
        let existingMaterialData = null;
        if (existingData?.materials?.main?.[1]?.pillow) {
            const existingPillows = existingData.materials.main[1].pillow;
            existingMaterialData = existingPillows.find(pillow => {
                // Check if this pillow entry has data for our subcategory
                const pillowData = pillow[1];
                return pillowData && pillowData[subcat.id];
            });
        }

        if (materialsForLabel.length === 0 && allMaterials.length > 0) {
            // No specific materials for this label, show empty selection
            return createSinglePillowMaterialInputForLabel(product, variant, subcat, label, null, allMaterials, roomId, variantId, existingMaterialData);
        }

        // Create material input for this label and subcategory
        const activeMaterial = materialsForLabel.length > 0 ? materialsForLabel[0] : null;
        const standardMaterialPrice = allMaterials.find(mat => mat.material_id == activeMaterial.material_id)?.material_price || 0;

        // Get existing material ID for this subcategory
        let existingMaterialId = '';
        if (existingMaterialData && existingMaterialData[1]) {
            existingMaterialId = existingMaterialData[1][subcat.id] || '';
        }

        return `
    <div class="pillow-material-group" data-label="${label}" data-subcategory="${subcat.id}" data-mt_of="${subcat.id}">
        <div class="pillow-material-group-header">
            <h6>${subcat.name} - Label ${label}</h6>
        </div>
        <div class="material-grid">
            <div class="pillow-material-inputs-compact" data-mt_of="${subcat.id}">
                <div class="pillow-material-compact-image">
                    ${existingMaterialId ?
                    `<img src="<?= URL ?>/uploads/material/${allMaterials.find(mat => mat.material_id == existingMaterialId)?.material_img || ''}" alt="" style="width:100%;height:100%;object-fit:cover;">` :
                    activeMaterial?.material_img ?
                    `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` :
                    `<i class="fa fa-image"></i>`
                    }
                </div>
                <div class="pillow-material-compact-fields">
                    <div class="pillow-material-input">
                        <label>Material</label>
                        <select class="form-control material-type-select"
                            data-standard-material-price="${standardMaterialPrice}"
                            data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                            <option value="">Select Material</option>
                            ${allMaterials.map(material => `
                            <option value="${material.material_id}"
                                ${existingMaterialId==material.material_id ? 'selected' :
                                (activeMaterial && activeMaterial.material_id==material.material_id ? 'selected' : '' )}
                                data-price="${material.material_price || 0}" 
                                data-image="${material.material_img || ''}">
                                ${material.material_name}
                            </option>
                            `).join('')}
                        </select>
                    </div>

                    ${subcat.id === 'default' ? `
                    <div class="pillow-material-input d-none">
                        <label>Quantity</label>
                        <input type="number" class="form-control pillow-quantity"
                            placeholder="1" min="1" value="${existingMaterialData ? existingMaterialData[2] || 1 : activeMaterial?.quantity || 1}"
                            data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                    </div>
                    <div class="pillow-material-input d-none">
                        <label>Dimensions (cm)</label>
                        <div class="pillow-dimensions d-flex align-items-center">
                            <input type="number" class="form-control pillow-length"
                                placeholder="Length" step="0.1" min="0"
                                value="${existingMaterialData ? existingMaterialData[3] || '' : activeMaterial?.length_cm || activeMaterial?.dimensions?.length || ''}"
                                data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                            <span class="dimension-separator">×</span>
                            <input type="number" class="form-control pillow-width"
                                placeholder="Width" step="0.1" min="0"
                                value="${existingMaterialData ? existingMaterialData[4] || '' : activeMaterial?.width_cm || activeMaterial?.dimensions?.width || ''}"
                                data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    </div>
    `;
    }

    function createSinglePillowMaterialInputForLabel(product, variant, subcat, label, activeMaterial, allMaterials, roomId, variantId, existingMaterialData) {
        const standardMaterialPrice = allMaterials.find(mat => mat.material_id == activeMaterial.material_id)?.material_price || 0;

        // Get existing material ID for this subcategory
        let existingMaterialId = '';
        if (existingMaterialData && existingMaterialData[1]) {
            existingMaterialId = existingMaterialData[1][subcat.id] || '';
        }

        return `
        <div class="material-grid">
            <div class="pillow-material-inputs-compact" data-label="${label}" data-subcategory="${subcat.id}" data-mt_of="${subcat.id}">
                <div class="pillow-material-compact-image">
                    ${existingMaterialId ?
                    `<img src="<?= URL ?>/uploads/material/${allMaterials.find(mat => mat.material_id == existingMaterialId)?.material_img || ''}" alt="" style="width:100%;height:100%;object-fit:cover;">` :
                    activeMaterial?.material_img ?
                    `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` :
                    `<i class="fa fa-image"></i>`
                    }
                </div>
                <div class="pillow-material-compact-fields">
                    <div class="pillow-material-input">
                        <label>Material</label>
                        <select class="form-control material-type-select"
                            data-standard-material-price="${standardMaterialPrice}"
                            data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                            <option value="">Select Material</option>
                            ${allMaterials.map(material => `
                            <option value="${material.material_id}"
                                ${existingMaterialId==material.material_id ? 'selected' :
                                (activeMaterial && activeMaterial.material_id==material.material_id ? 'selected' : '' )}
                                data-price="${material.material_price || 0}" 
                                data-image="${material.material_img || ''}">
                                ${material.material_name}
                            </option>
                            `).join('')}
                        </select>
                    </div>

                    ${subcat.id === 'default' ? `
                    <div class="pillow-material-input d-none">
                        <label>Quantity</label>
                        <input type="number" class="form-control pillow-quantity"
                            placeholder="1" min="1" value="${existingMaterialData ? existingMaterialData[2] || 1 : activeMaterial?.quantity || 1}"
                            data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                    </div>
                    <div class="pillow-material-input d-none">
                        <label>Dimensions (cm)</label>
                        <div class="pillow-dimensions d-flex align-items-center">
                            <input type="number" class="form-control pillow-length"
                                placeholder="Length" step="0.1" min="0"
                                value="${existingMaterialData ? existingMaterialData[3] || '' : activeMaterial?.length_cm || activeMaterial?.dimensions?.length || ''}"
                                data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                            <span class="dimension-separator">×</span>
                            <input type="number" class="form-control pillow-width"
                                placeholder="Width" step="0.1" min="0"
                                value="${existingMaterialData ? existingMaterialData[4] || '' : activeMaterial?.width_cm || activeMaterial?.dimensions?.width || ''}"
                                data-subcategory="${subcat.id}" data-label="${label}" data-variant="${variantId}">
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
        `;
    }

    function createMaterialLabelContent(product, variant, category, label, categoryData, roomId, productInstanceId, variantId, existingData, index = 0) {
        const materialGroups = categoryData.materialGroups || {};
        const activeMaterialsList = categoryData.active || [];
        const allMaterials = categoryData.all_materials || [];

        console.log('Creating material label content:', {
            category: category,
            label: label,
            materialGroups: materialGroups,
            activeMaterials: activeMaterialsList,
            existingData: existingData
        });

        // Find materials for this specific label
        let materialsForLabel = [];
        let existingMaterialData = null;

        // Check for existing material data
        if (existingData?.materials?.main?.[index + 1]?.[category]) {
            const existingMaterials = existingData.materials.main[index + 1][category];
            existingMaterialData = existingMaterials.find(mat => mat[0] === label);
        }

        if (materialGroups[label]) {
            materialsForLabel = materialGroups[label];
        } else if (activeMaterialsList.length > 0) {
            // If no materialGroups but we have active materials, use the one at the label index
            const labelIndex = parseInt(label.replace(/\D/g, '')) - 1 || 0;
            if (activeMaterialsList[labelIndex]) {
                materialsForLabel = [activeMaterialsList[labelIndex]];
            } else if (activeMaterialsList[0]) {
                // Fallback to first material
                materialsForLabel = [activeMaterialsList[0]];
            }
        }

        // Create material input for this label
        const activeMaterial = materialsForLabel.length > 0 ? materialsForLabel[0] : null;
        const standardMaterialPrice = allMaterials.find(mat => mat.material_id == activeMaterial.material_id)?.material_price || 0;

        // Get measurement field based on category
        const getMeasurementField = (category) => {
            const measurementFields = {
                'metal': 'weight_kg',
                'wood': 'area_m2',
                'marble': 'area_m2',
                'glass': 'area_m2',
                'fabric': 'area_m2'
            };
            return measurementFields[category] || 'area_m2';
        };

        const measurementField = getMeasurementField(category);
        const measurementLabel = category === 'metal' ? 'Weight (Kg)' : 'Area (m²)';
        const isCurtainFabric = (category === 'fabric' && product.type === 'curtain');

        // Get existing values
        const existingMaterialId = existingMaterialData ? existingMaterialData[1] : '';
        const existingAreaWeight = existingMaterialData ? existingMaterialData[4] || '' : '';
        const existingReplacement = existingMaterialData ? existingMaterialData[2] || '' : '';
        const existingReplacementType = existingMaterialData ? existingMaterialData[3] || '' : '';
        console.log('existingMaterialId:', existingMaterialId);
        console.log('existingAreaWeight:', existingAreaWeight);
        console.log('existingReplacement:', existingReplacement);
        console.log('existingReplacementType:', existingReplacementType);

        let replacementMaterials = [];
        let replacementImage = '';
        let replacementName = '';

        if (existingReplacementType) {
            try {
                const materials = getMaterials(existingReplacementType);
                replacementMaterials = materials || [];
                console.log('replacementMaterials:', replacementMaterials);
                let currentReplacementMaterial = replacementMaterials.find(mat => mat.material_id == existingReplacement) || null;
                replacementImage = currentReplacementMaterial?.material_img || '';
                replacementName = currentReplacementMaterial?.material_name || '';
            } catch (error) {
                console.error('Error fetching replacement materials:', error);
                replacementMaterials = [];
            }
        }

        const generateReplacementOptions = (materials) => {
            if (!materials || materials.length === 0) {
                return '<option value="">No materials available</option>';
            }
            return materials.map(material => `
                <option value="${material.material_id}"
                    ${existingReplacement==material.material_id ? 'selected' : '' }
                    data-image="${material.material_img || ''}"
                    data-name="${material.material_name || ''}" 
                    data-price="${material.material_price || 0}">
                    ${material.material_name}
                </option>
            `).join('');
        };

        // Helper function to handle missing getMaterials function
        const safeGetMaterials = getMaterials(category) || []

        const replacementTypeOptions = ['fabric', 'glass', 'metal', 'wood']
            .filter(material_type => material_type !== category)
            .map(material_type => `
                <option value="${material_type}" ${existingReplacementType===material_type?'selected':''}>
                    ${material_type.charAt(0).toUpperCase() + material_type.slice(1)}
                </option>
            `).join('');

        return `
        <div class="material-group" data-category="${category}" data-label="${label}">
            <div class="material-group-header">
                <h6>${category.charAt(0).toUpperCase() + category.slice(1)} - Label ${label} ${activeMaterial?.alias_name ? `- ${activeMaterial.alias_name}` : ''}</h6>
            </div>
            <div class="material-grid">
                <div class="material-inputs-compact">
                    <div class="material-compact-image">
                        ${activeMaterial?.material_img ?
                        `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` :
                        `<i class="fa fa-image"></i>`
                        }
                    </div>
                    <div class="material-compact-fields">
                        <div class="material-input">
                            <label>Material</label>
                            <select class="form-control material-type-select"
                                data-standard-material-price="${standardMaterialPrice}"
                                data-category="${category}" data-label="${label}" data-variant="${variantId}">
                                <option value="">Select Material</option>
                                ${allMaterials.map(material => `
                                <option value="${material.material_id}"
                                    ${(existingMaterialId && existingMaterialId==material.material_id) ? 'selected' :
                                    (activeMaterial && activeMaterial.material_id==material.material_id ? 'selected' : '' )}
                                    data-price="${material.material_price || 0}" 
                                    data-image="${material.material_img || ''}">
                                    ${material.material_name}
                                </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="material-input d-none">
                            <label>${measurementLabel}</label>
                            <input type="number" class="form-control area-weight"
                                placeholder="Enter ${measurementLabel.toLowerCase()}"
                                data-category="${category}" data-label="${label}" data-variant="${variantId}"
                                value="${existingAreaWeight || (activeMaterial ? activeMaterial[measurementField] || '' : '')}"
                                step="0.01" min="0">
                        </div>
                        <div class="material-input${!isCurtainFabric?' d-none':''}">
                            <label>Length</label>
                            <input type="number" class="form-control curtain-fabric-length"
                                placeholder="Enter Length (m)"
                                data-category="${category}" data-label="${label}" data-variant="${variantId}"
                                value="${existingData?.curtain_data?.[index + 1]?.curtain?.length || ''}"
                                step="0.01" min="0">
                        </div>
                        <div class="material-input${!isCurtainFabric?' d-none':''}">
                            <label>Height</label>
                            <input type="number" class="form-control curtain-fabric-height"
                                placeholder="Enter Height (m)"
                                data-category="${category}" data-label="${label}" data-variant="${variantId}"
                                value="${existingData?.curtain_data?.[index + 1]?.curtain?.height || ''}"
                                step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="material-inputs-compact-replacement">
                    <div class="material-compact-image-replacement">
                        ${replacementImage != '' ?
                        `<img src="<?= URL ?>/uploads/material/${replacementImage}" alt="${replacementName}" style="width:100%;height:100%;object-fit:cover;">` :
                        `<i class="fa fa-image"></i>`
                        }
                    </div>
                    <div class="material-compact-fields">
                        <div class="material-input">
                            <label>Replacement</label>
                            <select class="form-control material-type-replacement"
                                data-variant="${variantId}" data-category="${category}" data-ref-label="${label}">
                                <option value="">Select Type</option>
                                ${replacementTypeOptions}
                            </select>
                            <select class="form-control material-replacement"
                                data-variant="${variantId}" data-category="${category}" data-ref-label="${label}">
                                <option value="">Select Material</option>
                                ${generateReplacementOptions(replacementMaterials)}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    function createMaterialSectionForItem(item, productId, roomId, existingData, levelIndex = 0) {
        // Use the materials data that already comes with the item
        const activeMaterials = item.active_materials || {};
        const itemName = item.product_name;
        const itemId = item.product_id;
        const existingMaterials = existingData && existingData.materials ? existingData.materials : {};

        console.log('Creating material section for item:', {
            activeMaterials: activeMaterials,
            itemName: itemName,
            itemId: itemId
        });

        // Get available material categories from activeMaterials
        const materialCategories = Object.keys(activeMaterials).filter(cat =>
            activeMaterials[cat] &&
            (activeMaterials[cat].materialGroups || activeMaterials[cat].active || activeMaterials[cat].all_materials)
        ).map(category => {
            return {
                id: category,
                name: category.charAt(0).toUpperCase() + category.slice(1),
                active_materials: activeMaterials[category]
            };
        });

        // If no materials available, show message
        if (materialCategories.length === 0) {
            return `
    <div class="material-section">
        <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${itemName}</h6>
        <div class="no-materials-available">
            <i class="fa fa-info-circle"></i>
            <p>No materials found for this item</p>
        </div>
    </div>
    `;
        }

        return `
    <div class="material-section">
        <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${itemName}</h6>
        <div class="material-tabs" id="materialTabs-${itemId}-${productId}-room${roomId}">
            ${materialCategories.map((category, index) => `
            <button class="material-tab ${index === 0 ? 'active' : ''}"
                data-category="${category.id}">
                ${category.name}
            </button>
            `).join('')}
        </div>
        <div class="material-tabs-content" id="materialTabsContent-${itemId}-${productId}-room${roomId}">
            ${materialCategories.map((category, index) => `
            <div class="material-tab-content ${index === 0 ? 'active' : ''}"
                id="materialContent-${itemId}-${productId}-room${roomId}-${category.id}">
                ${createMaterialContentWithLabels(product, variant, category, roomId, activeMaterials, productInstanceId)}
            </div>
            `).join('')}
        </div>
    </div>
    `;
    }

    // Function to setup pillow subcategory tabs

    function setupPillowSubcategoryTabsForProduct(product, variant, roomId, productInstanceId) {
        const productId = productInstanceId;
        const variantId = variant.product_id || variant.id;

        console.log('Setting up pillow tabs for:', {
            productId,
            variantId,
            roomId
        });

        // Wait for the DOM to be fully ready - pillow tabs are nested inside label tabs
        setTimeout(() => {
            // Debug first
            debugPillowNestedStructure(productId, variantId, roomId);
            // For each label tab (1, 2, 3, etc.), setup its nested pillow subcategory tabs
            $(`#pillowLabelContent-${productId}-${variantId}-room${roomId} .pillow-subcategories-section`).each(function(index) {
                const $pillowSection = $(this);
                const labelTab = $(`#pillowLabelTabs-${productId}-${variantId}-room${roomId} .material-label-tab`).eq(index);
                const label = labelTab.data('label') || (index + 1);

                const pillowTabsId = `pillowSubTabs-${productId}-${variantId}-room${roomId}-${label}`;
                const $pillowTabs = $(`#${pillowTabsId}`);
                const $pillowContent = $(`#pillowSubContent-${productId}-${variantId}-room${roomId}-${label}`);

                if (!$pillowTabs.length) {
                    console.log('No pillow tabs found for label:', label, 'ID:', pillowTabsId);
                    return;
                }

                console.log('Found pillow tabs for label:', label, 'count:', $pillowTabs.find('.pillow-subcategory-tab').length);

                // Remove any existing click handlers
                $(document).off('click', `#${pillowTabsId} .pillow-subcategory-tab`);

                // Use event delegation for dynamic elements
                $(document).on('click', `#${pillowTabsId} .pillow-subcategory-tab`, function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const subcategoryId = $(this).data('subcategory');
                    console.log('Pillow subcategory tab clicked for label', label, ':', subcategoryId);

                    // Deactivate all tabs and content in this label section
                    $pillowTabs.find('.pillow-subcategory-tab').removeClass('active');
                    $pillowContent.find('.pillow-subcategory-content').removeClass('active');

                    // Activate current tab and content
                    $(this).addClass('active');
                    const $targetContent = $(`#pillowSubcategory-${productId}-${variantId}-room${roomId}-${label}-${subcategoryId}`);

                    if ($targetContent.length) {
                        $targetContent.addClass('active');
                        updatePillowSubcategoryStatus(productId, variantId, product.available_in, subcategoryId, roomId, product.type);
                    } else {
                        console.error('Pillow subcategory content not found for:', {
                            productId,
                            variantId,
                            label,
                            subcategoryId
                        });
                    }
                });

                // Activate the first tab by default
                const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
                if ($firstTab.length) {
                    $firstTab.trigger('click');
                }
            });
        }, 1000); // Increased delay for nested structure
    }

    function debugPillowNestedStructure(productId, variantId, roomId) {
        console.log('=== DEBUG PILLOW NESTED STRUCTURE ===');

        // Check main label tabs
        const labelTabsId = `pillowLabelTabs-${productId}-${variantId}-room${roomId}`;
        const $labelTabs = $(`#${labelTabsId}`);
        console.log('Label tabs:', {
            id: labelTabsId,
            exists: $labelTabs.length,
            count: $labelTabs.find('.material-label-tab').length,
            labels: $labelTabs.find('.material-label-tab').map(function() {
                return $(this).data('label');
            }).get()
        });

        // Check each label's nested pillow tabs
        $labelTabs.find('.material-label-tab').each(function(index) {
            const label = $(this).data('label');
            const pillowTabsId = `pillowSubTabs-${productId}-${variantId}-room${roomId}-${label}`;
            const $pillowTabs = $(`#${pillowTabsId}`);

            console.log(`Label "${label}" pillow tabs:`, {
                id: pillowTabsId,
                exists: $pillowTabs.length,
                count: $pillowTabs.find('.pillow-subcategory-tab').length,
                subcategories: $pillowTabs.find('.pillow-subcategory-tab').map(function() {
                    return $(this).data('subcategory');
                }).get()
            });
        });

        console.log('=== END DEBUG ===');
    }

    // function to update pillow subcategory status with material groups
    function updatePillowSubcategoryStatus(productId, variantId, availableIn, subcategoryId, roomId, type) {
        const $tab = $(`#pillowTabs-${productId}-${variantId}-room${roomId} .pillow-subcategory-tab[data-subcategory="${subcategoryId}"]`);
        const $statusIndicator = $tab.find('.status-indicator');

        const $content = $(`#pillowSubcategory-${productId}-${variantId}-room${roomId}-${subcategoryId}`);

        // Check all material groups for this subcategory
        let allGroupsComplete = true;
        let hasGroups = false;

        $content.find('.pillow-material-group').each(function() {
            hasGroups = true;
            const $group = $(this);
            const refLabel = $group.data('ref-label');

            const materialType = $group.find(`.material-type-select[data-ref-label="${refLabel}"]`).val();
            const areaWeight = $group.find(`.area-weight[data-ref-label="${refLabel}"]`).val();

            // For default pillow, also check quantity and dimensions
            let groupComplete = !!(materialType);

            if (subcategoryId === 'default-pillow') {
                const quantity = $group.find(`.pillow-quantity[data-ref-label="${refLabel}"]`).val();
                const length = $group.find(`.pillow-length[data-ref-label="${refLabel}"]`).val();
                const width = $group.find(`.pillow-width[data-ref-label="${refLabel}"]`).val();
                groupComplete = groupComplete && !!(quantity && length && width);
            }

            if (!groupComplete) {
                allGroupsComplete = false;
            }
        });

        // If no groups found, check single inputs
        if (!hasGroups) {
            const materialType = $content.find('.material-type-select').val();
            const areaWeight = $content.find('.area-weight').val();

            // let singleComplete = !!(materialType && areaWeight);
            let singleComplete = !!(materialType);

            if (subcategoryId === 'default-pillow') {
                const quantity = $content.find('.pillow-quantity').val();
                const length = $content.find('.pillow-length').val();
                const width = $content.find('.pillow-width').val();
                singleComplete = singleComplete && !!(quantity && length && width);
            }

            allGroupsComplete = singleComplete;
            hasGroups = true; // We have at least the single inputs
        }

        $statusIndicator.removeClass('status-empty status-incomplete status-complete');

        if (!hasGroups) {
            $statusIndicator.addClass('status-empty');
        } else if (allGroupsComplete) {
            $statusIndicator.addClass('status-complete');
        } else {
            $statusIndicator.addClass('status-incomplete');
        }

        // Update parent variant status
        updateVariantStatus(productId, roomId, variantId, availableIn, type);
    }

    // Setup event handlers for pillow material groups
    function setupPillowMaterialGroupHandlers(productId, variantId, roomId, availableIn, type) {
        const $pillowContent = $(`#pillowContent-${productId}-${variantId}-room${roomId}`);

        $pillowContent.find('.area-weight, .material-type-select, .pillow-quantity, .pillow-length, .pillow-width').on('input change', function() {
            const $input = $(this);
            const subcategoryId = $input.data('subcategory');
            const refLabel = $input.data('ref-label');

            updatePillowSubcategoryStatus(productId, variantId, availableIn, subcategoryId, roomId, type);
        });
    }

    // Utility to normalize attr_rates safely
    function normalizeAttrRates(attrRates) {
        if (!attrRates) return [];

        // If it's a JSON string, parse it
        if (typeof attrRates === 'string') {
            try {
                attrRates = JSON.parse(attrRates);
            } catch (e) {
                console.warn('Invalid attrRates JSON:', attrRates);
                return [];
            }
        }

        // Convert object → array if necessary
        if (!Array.isArray(attrRates)) {
            attrRates = Object.values(attrRates);
        }

        return attrRates;
    }

    // Function to create surcharges section (for main product)
    function createSurchargesSection(product, variant, roomId) {
        return ''; // Temporarily disable surcharges for main products
        const attrRates = product.available_in === 'size' ?
            (product.attr_rates || []) :
            (variant?.attr_rates || []);

        const surchargeList = normalizeAttrRates(attrRates);

        console.log("createSurchargesSection:", surchargeList);

        const activeSurcharges = surchargeList.filter(
            rate => parseFloat(rate.rate) > 0
        );

        if (activeSurcharges.length === 0) return '';

        const variantId = product.available_in === 'size' ?
            variant.id :
            variant.product_id;

        return `
    <div class="surcharges-section">
        <h6><i class="fa fa-plus-circle mr-2"></i>Additional Surcharges</h6>
        <div class="surcharges-container">
            ${activeSurcharges.map(surcharge => `
            <div class="surcharge-item">
                <div class="form-check">
                    <input type="checkbox"
                        class="form-check-input surcharge-checkbox"
                        id="surcharge-${surcharge.name.replace(/\s+/g, '-').toLowerCase()}-${product.product_id}-${variantId}-room${roomId}"
                        data-surcharge-name="${surcharge.name}"
                        data-surcharge-type="${surcharge.type}"
                        data-surcharge-rate="${surcharge.rate}"
                        data-variant="${variantId}">
                    <label class="form-check-label" for="surcharge-${surcharge.name.replace(/\s+/g, '-').toLowerCase()}-${product.product_id}-${variantId}-room${roomId}">
                        ${surcharge.name}
                        <span class="surcharge-rate ${surcharge.type === 'plus' ? 'text-success' : 'text-danger'}">
                            (${surcharge.type === 'plus' ? '+' : '-'}${surcharge.rate}%)
                        </span>
                    </label>
                </div>
            </div>
            `).join('')}
        </div>
    </div>
    `;
    }

    // Function to create surcharges section (for items)
    function createSurchargesSectionForItem(item, productId, roomId) {
        return ''; // Temporarily disable surcharges for items
        const attrRates = normalizeAttrRates(item.attr_rates);

        console.log("createSurchargesSectionForItem:", attrRates);

        const activeSurcharges = attrRates.filter(
            rate => parseFloat(rate.rate) > 0
        );

        if (activeSurcharges.length === 0) return '';

        const itemId = item.product_id;

        return `
    <div class="surcharges-section">
        <h6><i class="fa fa-plus-circle mr-2"></i>Additional Surcharges</h6>
        <div class="surcharges-container">
            ${activeSurcharges.map(surcharge => `
            <div class="surcharge-item">
                <div class="form-check">
                    <input type="checkbox"
                        class="form-check-input surcharge-checkbox"
                        id="surcharge-${surcharge.name.replace(/\s+/g, '-').toLowerCase()}-${itemId}-${productId}-room${roomId}"
                        data-surcharge-name="${surcharge.name}"
                        data-surcharge-type="${surcharge.type}"
                        data-surcharge-rate="${surcharge.rate}"
                        data-item="${itemId}">
                    <label class="form-check-label" for="surcharge-${surcharge.name.replace(/\s+/g, '-').toLowerCase()}-${itemId}-${productId}-room${roomId}">
                        ${surcharge.name}
                        <span class="surcharge-rate ${surcharge.type === 'plus' ? 'text-success' : 'text-danger'}">
                            (${surcharge.type === 'plus' ? '+' : '-'}${surcharge.rate}%)
                        </span>
                    </label>
                </div>
            </div>
            `).join('')}
        </div>
    </div>
    `;
    }

    // Add item to product with improved material section layout
    function addItemToProduct(roomId, productId, item, existingItem = null) {
        const dims = item.dims || {};
        let width = dims.width || '';
        let length = dims.length || '';
        let height = dims.height || '';
        const standart_price = dims.standart_price || '';
        const unit_price = calculateUnitPrice(item.calculate_type, dims);

        if (existingItem) {
            width = existingItem.width || width;
            length = existingItem.length || length;
            height = existingItem.height || height;
        }
        const quantity = existingItem ? (existingItem.quantity || 1) : 1;
        const discount = existingItem ? (existingItem.discount || 0) : 0;
        const notes = existingItem ? (existingItem.notes || '') : '';

        console.log('Adding item to product:', item.product_name, 'room:', roomId, 'product:', productId);

        // Get the correct product content container
        const $productContent = $(`#product-${productId}-room${roomId}`);
        if (!$productContent.length) {
            console.error('Product content not found:', `#product-${productId}-room${roomId}`);
            return;
        }

        const $tabsContainer = $productContent.find('.items-tabs-container');
        const $emptyState = $tabsContainer.find('.empty-items-tabs');
        const $detailsBody = $productContent.find('.product-details-body');
        const $emptyDetails = $detailsBody.find('.empty-item-selection');

        if ($emptyState.length) {
            $emptyState.remove();
        }

        // Check if item already exists
        const existingTab = $tabsContainer.find(`[data-item-id="${item.product_id}"]`);
        if (existingTab.length) {
            alert('This item has already been added.');
            return;
        }

        const tabId = `item-${item.product_id}-${productId}-room${roomId}`;
        const $tab = $(`
    <div class="items-tab" data-item-id="${item.product_id}" id="${tabId}-tab">
        <span class="items-tab-name">${item.product_name}</span>
        <div class="items-tab-close" title="Remove item">
            <i class="fa fa-times"></i>
        </div>
    </div>
    `);

        $tabsContainer.append($tab);

        // Check if details content already exists before creating
        if (!$(`#${tabId}`).length) {
            // Create item details content with proper material section
            const $detailsContent = $(`
    <div class="item-details" id="${tabId}" style="display: none;" data-active-materials='${JSON.stringify(item.active_materials || {})}'>
        <div class="enhanced-category-item">
            <div class="enhanced-item-header">
                <div class="enhanced-item-name">${item.product_name}</div>
            </div>
            <div class="enhanced-details-with-image">
                <div class="enhanced-image-preview">
                    <img src="${item.product_img}" alt="${item.product_name}" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="enhanced-details-fields">
                    <div class="detail-group">
                        <label>Width (m)</label>
                        <input type="number" class="form-control item-width item-dims" value="${width}" data-standart_width="${width}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(item.calculate_type, 'w' )}>
                    </div>
                    <div class="detail-group">
                        <label>Length (m)</label>
                        <input type="number" class="form-control item-length item-dims" value="${length}" data-standart_length="${length}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(item.calculate_type, 'l' )}>
                    </div>
                    <div class="detail-group">
                        <label>Height (m)</label>
                        <input type="number" class="form-control item-height item-dims" value="${height}" data-standart_height="${height}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(item.calculate_type, 'h' )}>
                    </div>
                    <div class="detail-group">
                        <label>Quantity</label>
                        <input type="number" class="form-control item-qty" placeholder="0" min="1" value="${quantity}">
                    </div>
                    <div class="detail-group">
                        <label>Discount(%)</label>
                        <input type="number" class="form-control item-discount" placeholder="0" step="0.01" min="0" value="${discount}">
                    </div>
                    <div class="detail-group">
                        <label>Notes</label>
                        <textarea class="form-control item-notes" placeholder="Additional notes..." rows="2">${notes}</textarea>
                    </div>
                </div>
            </div>
            <!-- Add base price field for item -->
            <input type="hidden" class="standart-price" value="${standart_price}" data-variant="${item.product_id}">
            <input type="hidden" class="item-unit-price" value="${unit_price}" data-variant="${item.product_id}">
            <input type="hidden" class="item-calculate-type" value="${item.calculate_type}" data-variant="${item.product_id}">

            ${createMaterialSectionForItem(item, productId, roomId, existingItem)}

            <!-- Surcharges Section -->
            ${createSurchargesSectionForItem(item, productId, roomId)}
        </div>
    </div>
    `);

            if ($emptyDetails.length) {
                $emptyDetails.remove();
            }

            $detailsBody.append($detailsContent);
        }

        // Activate the new tab
        activateItemTab($tab);

        // Setup event handlers for the new item
        setupMaterialTabsForItem(item.product_id, productId, roomId);
        setupPillowSubcategoryTabsForItem(item.product_id, productId, roomId);

        // Setup close button
        $tab.find('.items-tab-close').on('click', function(e) {
            e.stopPropagation();
            removeItemFromProduct($tab);
        });

        updateRoomStatus(`room${roomId}`);
    }

    // Enhanced function to setup pillow subcategory tabs for items
    function setupPillowSubcategoryTabsForItem(itemId, productId, roomId) {
        const pillowTabsId = `pillowTabs-${itemId}-${productId}-room${roomId}`;

        // Use a small delay to ensure DOM is ready
        setTimeout(() => {
            const $pillowTabs = $(`#${pillowTabsId}`);
            const $pillowContent = $(`#pillowContent-${itemId}-${productId}-room${roomId}`);

            if (!$pillowTabs.length) return;

            // Tab click handler
            $pillowTabs.find('.pillow-subcategory-tab').on('click', function(e) {
                e.preventDefault();
                const subcategoryId = $(this).data('subcategory');

                // Deactivate all tabs and content
                $pillowTabs.find('.pillow-subcategory-tab').removeClass('active');
                $pillowContent.find('.pillow-subcategory-content').removeClass('active');

                // Activate current tab and content
                $(this).addClass('active');
                $(`#pillowSubcategory-${itemId}-${productId}-room${roomId}-${subcategoryId}`).addClass('active');

                updatePillowSubcategoryStatusForItem(itemId, productId, subcategoryId, roomId);
            });

            // Setup pillow material group handlers for items
            setupPillowMaterialGroupHandlersForItem(itemId, productId, roomId);

            // Activate the first tab by default
            const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
            if ($firstTab.length) {
                $firstTab.trigger('click');
            }
        }, 100);
    }

    // Function to setup pillow material group handlers for items
    function setupPillowMaterialGroupHandlersForItem(itemId, productId, roomId) {
        const $pillowContent = $(`#pillowContent-${itemId}-${productId}-room${roomId}`);

        $pillowContent.find('.area-weight, .material-type-select, .pillow-quantity, .pillow-length, .pillow-width').on('input change', function() {
            const $input = $(this);
            const subcategoryId = $input.data('subcategory');
            const refLabel = $input.data('ref-label');

            updatePillowSubcategoryStatusForItem(itemId, productId, subcategoryId, roomId);
        });
    }

    // Function to update pillow subcategory status for items
    function updatePillowSubcategoryStatusForItem(itemId, productId, subcategoryId, roomId) {
        const $tab = $(`#pillowTabs-${itemId}-${productId}-room${roomId} .pillow-subcategory-tab[data-subcategory="${subcategoryId}"]`);
        const $statusIndicator = $tab.find('.status-indicator');

        const $content = $(`#pillowSubcategory-${itemId}-${productId}-room${roomId}-${subcategoryId}`);

        // Check all material groups for this subcategory
        let allGroupsComplete = true;
        let hasGroups = false;

        $content.find('.pillow-material-group').each(function() {
            hasGroups = true;
            const $group = $(this);
            const refLabel = $group.data('ref-label');

            const materialType = $group.find(`.material-type-select[data-ref-label="${refLabel}"]`).val();
            const areaWeight = $group.find(`.area-weight[data-ref-label="${refLabel}"]`).val();

            // For default pillow, also check quantity and dimensions
            let groupComplete = !!(materialType);

            if (subcategoryId === 'default') {
                const quantity = $group.find(`.pillow-quantity[data-ref-label="${refLabel}"]`).val();
                const length = $group.find(`.pillow-length[data-ref-label="${refLabel}"]`).val();
                const width = $group.find(`.pillow-width[data-ref-label="${refLabel}"]`).val();
                groupComplete = groupComplete && !!(quantity && length && width);
            }

            if (!groupComplete) {
                allGroupsComplete = false;
            }
        });

        // If no groups found, check single inputs
        if (!hasGroups) {
            const materialType = $content.find('.material-type-select').val();
            const areaWeight = $content.find('.area-weight').val();

            let singleComplete = !!(materialType);

            if (subcategoryId === 'default') {
                const quantity = $content.find('.pillow-quantity').val();
                const length = $content.find('.pillow-length').val();
                const width = $content.find('.pillow-width').val();
                singleComplete = singleComplete && !!(quantity && length && width);
            }

            allGroupsComplete = singleComplete;
            hasGroups = true;
        }

        $statusIndicator.removeClass('status-empty status-incomplete status-complete');

        if (!hasGroups) {
            $statusIndicator.addClass('status-empty');
        } else if (allGroupsComplete) {
            $statusIndicator.addClass('status-complete');
        } else {
            $statusIndicator.addClass('status-incomplete');
        }
    }

    // Add accessory to curtain product
    function addAccessoryToProduct(roomId, productId, accessory, accessoryOptions, existingAccessory = null, existingBlackoutColor = null) {
        console.log('Adding accessory to product:', accessory.name, 'room:', roomId, 'product:', productId, 'options:', accessoryOptions);

        const $productContent = $(`#product-${productId}-room${roomId}`);
        const $tabsContainer = $productContent.find('.accessory-tabs-container');
        const $emptyState = $tabsContainer.find('.empty-accessory-tabs');
        const $detailsBody = $productContent.find('.accessory-details-body');
        const $emptyDetails = $detailsBody.find('.empty-accessory-selection');

        if ($emptyState.length) {
            $emptyState.remove();
        }

        const existingTab = $tabsContainer.find(`[data-accessory-id="${accessory.id}"]`);
        if (existingTab.length) {
            console.log('This accessory has already been added.(curtain single)');
            alert('This accessory has already been added.');
            return;
        }

        const tabId = `accessory-${accessory.id}-${productId}-room${roomId}`;
        const $tab = $(`
    <div class="accessory-tab" data-accessory-id="${accessory.id}" id="${tabId}-tab">
        <span class="accessory-tab-name">${accessory.name}</span>
        <div class="accessory-tab-close" title="Remove accessory">
            <i class="fa fa-times"></i>
        </div>
    </div>
    `);

        $tabsContainer.append($tab);

        const existing_blackout_color = existingBlackoutColor || '';
        const existing_accessory_id = existingAccessory ? existingAccessory.id : '';

        const colorOptions = blackOutColors.map(color => `
    <option value="${color}" ${existing_blackout_color==color?'selected':''}>${color}</option>
    `).join('');
        let blackoutColorSelector = '';
        if (accessory.name === 'Black Out') {
            blackoutColorSelector = `
    <div class="detail-group">
        <label>Blackout Color</label>
        <select class="form-control accessory-blackout-color">
            <option value="">Select Color</option>
            ${colorOptions}
        </select>
    </div>
    `;
        }

        // Accessory details content
        const $detailsContent = $(`
    <div class="accessory-details" id="${tabId}" style="display: none;">
        <div class="enhanced-category-item">
            <div class="enhanced-item-header">
                <div class="enhanced-item-name">${accessory.name}</div>
            </div>
            <div class="enhanced-details-with-image">
                <div class="enhanced-image-preview" id="${accessory.id}-preview">
                    <i class="fa fa-image"></i>
                </div>
                <div class="enhanced-details-fields">
                    <div class="detail-group">
                        <label>Accessory</label>
                        <div class="accessory-selection-container">
                            <select class="form-control accessory-options-select">
                                <option value="">Select</option>
                                ${accessoryOptions.map(option => `
                                <option value="${option.accessory_id}" data-accessory-type="${accessory.id}" data-price="${option.price}" data-price_type="${option.price_type}" data-accessory-type="${accessory.name}" data-price_depends_on="${option.price_depends_on}" data-image="${option.accessory_img}" ${existing_accessory_id==option.accessory_id?'selected':''}>${option.accessory_name}</option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                    ${blackoutColorSelector}
                </div>
            </div>
        </div>
    </div>
    `);

        if ($emptyDetails.length) {
            $emptyDetails.remove();
        }

        $detailsBody.append($detailsContent);

        activateAccessoryTab($tab, roomId, productId);

        // Accessory type selection handler
        $(`#${tabId} .accessory-options-select`).on('change', function() {
            const selectedOptionId = $(this).val();
            const previewId = `${accessory.id}-preview`;
            const $preview = $(`#${previewId}`);

            if (selectedOptionId) {
                const $selectedOption = $(this).find('option:selected');
                const img = $selectedOption.data('image');
                $preview.html(`<img src="<?= URL ?>/uploads/material/${img}" alt="Accessory image">`);
                calculateProductTotal(productId, roomId, 'curtain');
            }
        });
        if (existingAccessory) {
            $(`#${tabId} .accessory-options-select`).trigger('change');
        }

        $tab.find('.accessory-tab-close').on('click', function(e) {
            e.stopPropagation();
            removeAccessoryFromProduct($tab, roomId, productId, accessory.id);
        });
    }

    // Function to setup material tabs and calculations for items
    function setupMaterialTabsForItem(itemId, productId, roomId) {
        const materialTabsId = `materialTabs-${itemId}-${productId}-room${roomId}`;

        // Use a small delay to ensure DOM is ready
        setTimeout(() => {
            const $materialTabs = $(`#${materialTabsId}`);

            if ($materialTabs.length) {
                console.log('Setting up material tabs for item:', materialTabsId);

                // Remove existing handlers to prevent duplicates
                $materialTabs.off('click', '.material-tab');

                // Add new handler
                $materialTabs.on('click', '.material-tab', function(e) {
                    e.preventDefault();
                    const categoryId = $(this).data('category');
                    const materialTabsContentId = `materialTabsContent-${itemId}-${productId}-room${roomId}`;

                    console.log('Item material tab clicked:', categoryId);

                    // Deactivate all tabs and content
                    $(`#${materialTabsId} .material-tab`).removeClass('active');
                    $(`#${materialTabsContentId} .material-tab-content`).removeClass('active');

                    // Activate current tab and content
                    $(this).addClass('active');
                    $(`#materialContent-${itemId}-${productId}-room${roomId}-${categoryId}`).addClass('active');
                });
            }
        }, 100);
    }

    function activateItemTab($tab) {
        const itemId = $tab.data('item-id');

        // Get room and product from the closest product content container
        const $productContent = $tab.closest('.product-content');
        if (!$productContent.length) {
            console.error('Product content not found for tab activation');
            return;
        }

        const productContentId = $productContent.attr('id');
        const productId = productContentId.replace('product-', '').replace(/-room\d+$/, '');
        const roomId = productContentId.match(/room(\d+)/)[1];

        console.log('Activating item tab:', {
            itemId,
            roomId,
            productId
        });

        // Deactivate all tabs and hide all details in this product
        $productContent.find('.items-tab').removeClass('active');
        $productContent.find('.item-details').hide();

        // Activate current tab and show its details
        $tab.addClass('active');

        const detailsId = `item-${itemId}-${productId}-room${roomId}`;
        const $details = $(`#${detailsId}`);

        if ($details.length) {
            $details.show();
            console.log('Showing details for:', detailsId);
        } else {
            console.error('Details container not found:', detailsId);
        }
    }

    // Activate accessory tab
    function activateAccessoryTab($tab, roomId, productId) {
        const accessoryId = $tab.data('accessory-id');
        const $productContent = $(`#product-${productId}-room${roomId}`);

        console.log('Activating regular accessory tab:', {
            accessoryId,
            productId,
            roomId
        });

        // Deactivate all tabs and hide all details in this product's accessory section
        $productContent.find('.accessory-tab').removeClass('active');
        $productContent.find('.accessory-details').hide();

        // Activate current tab and show its details
        $tab.addClass('active');

        const detailsId = `accessory-${accessoryId}-${productId}-room${roomId}`;
        const $details = $(`#${detailsId}`);

        if ($details.length) {
            $details.show();
            console.log('Showing regular accessory details:', detailsId);
        } else {
            console.error('Regular accessory details container not found:', detailsId);
            // Debug: Log all available accessory details in this product
            $productContent.find('.accessory-details').each(function() {
                console.log('Available accessory detail:', $(this).attr('id'));
            });
        }
    }

    function removeItemFromProduct($tab) {
        const itemId = $tab.data('item-id');

        // Get room and product from the closest product content container
        const $productContent = $tab.closest('.product-content');
        const productContentId = $productContent.attr('id');
        const productId = productContentId.replace('product-', '').replace(/-room\d+$/, '');
        const roomId = productContentId.match(/room(\d+)/)[1];

        console.log('Removing item from product:', {
            itemId,
            roomId,
            productId
        });

        // Remove the tab
        $tab.remove();

        // Remove the details content
        const detailsId = `item-${itemId}-${productId}-room${roomId}`;
        $(`#${detailsId}`).remove();

        const $tabsContainer = $productContent.find('.items-tabs-container');
        const $detailsBody = $productContent.find('.product-details-body');
        const $tabs = $tabsContainer.find('.items-tab');

        if ($tabs.length === 0) {
            $tabsContainer.html(`
    <div class="empty-items-tabs">
        <i class="fa fa-cube"></i>
        <p>No items added yet</p>
    </div>
    `);

            $detailsBody.html(`
    <div class="empty-item-selection">
        <i class="fa fa-hand-pointer"></i>
        <p>Select an item to view and edit details</p>
    </div>
    `);
        } else {
            const $firstTab = $tabs.first();
            activateItemTab($firstTab);
        }

        // Update totals after removal
        calculateProductTotal(productId, roomId, 'fitout');
        updateRoomStatus(`room${roomId}`);
    }

    function removeAccessoryFromProduct($tab, roomId, productId, accessoryId) {
        console.log('Removing accessory from product');

        $tab.remove();
        $(`#accessory-${accessoryId}-${productId}-room${roomId}`).remove();

        const $productContent = $(`#product-${productId}-room${roomId}`);
        const $tabsContainer = $productContent.find('.accessory-tabs-container');
        const $detailsBody = $productContent.find('.accessory-details-body');
        const $tabs = $tabsContainer.find('.accessory-tab');

        // Update totals after removal
        calculateProductTotal(productId, roomId, 'curtain');

        if ($tabs.length === 0) {
            $tabsContainer.html(`
    <div class="empty-accessory-tabs">
        <i class="fa fa-puzzle-piece"></i>
        <p>No accessories added yet</p>
    </div>
    `);

            $detailsBody.html(`
    <div class="empty-accessory-selection">
        <i class="fa fa-hand-pointer"></i>
        <p>Select an accessory to view and edit details</p>
    </div>
    `);
        } else {
            const $firstTab = $tabs.first();
            activateAccessoryTab($firstTab, roomId, productId);
        }
    }

    function activateFirstAccessoryTab(roomId, productId, variantId = null) {
        if (variantId) {
            // This is a curtain variant
            const $variantContent = $(`#variant-${productId}-${variantId}-room${roomId}`);
            const $firstTab = $variantContent.find('.accessory-tab').first();
            if ($firstTab.length) {
                activateCurtainAccessoryTab($firstTab, roomId, productId, variantId);
            }
        } else {
            // This is a regular product
            const $productContent = $(`#product-${productId}-room${roomId}`);
            const $firstTab = $productContent.find('.accessory-tab').first();
            if ($firstTab.length) {
                activateAccessoryTab($firstTab, roomId, productId);
            }
        }
    }

    // Event handlers
    $('#addRoomBtn').on('click', function() {
        const roomNumber = getNextRoomNumber();
        const roomId = 'room' + roomNumber;
        const roomColor = roomColors[(roomNumber - 1) % roomColors.length];

        // Extract the main color for borders (first color in gradient)
        const mainColor = roomColor.split(',')[1].trim();

        const $tabLi = $(`
    <li class="nav-item">
        <a class="nav-link room-tab"
            id="${roomId}-tab"
            data-toggle="tab"
            href="#${roomId}"
            role="tab"
            aria-controls="${roomId}"
            data-room="${roomNumber}">
            <div class="room-header" style="background: ${roomColor};">
                <span class="status-indicator status-empty"></span>
                <span class="room-title">Room ${roomNumber}</span>
                <span class="close-room ml-2" title="Remove room">
                    <i class="fa fa-times"></i>
                </span>
            </div>
        </a>
    </li>
    `);
        $('#roomTabs .nav-item:has(.add-room-btn)').before($tabLi);

        const $pane = $(`
    <div class="tab-pane fade"
        id="${roomId}"
        role="tabpanel"
        aria-labelledby="${roomId}-tab"
        data-room="${roomNumber}">
        <div class="product-tabs-wrapper">
            <div class="product-tabs-header" style="border-left: 4px solid ${mainColor};">
                <div class="room-info-form">
                    <div class="form-group-small">
                        <label for="floorName-${roomId}">Floor Name</label>
                        <input type="text" class="form-control-small floor-name-input"
                            id="floorName-${roomId}" data-room-id="${roomId}"
                            placeholder="Enter floor name">
                    </div>
                    <div class="form-group-small">
                        <label for="roomName-${roomId}">Room Name</label>
                        <input type="text" class="form-control-small room-name-input"
                            id="roomName-${roomId}" data-room-id="${roomId}"
                            placeholder="Enter room name">
                    </div>
                    <div class="form-group-small">
                        <label>Room Image</label>
                        <div class="image-upload-container">
                            <div class="image-preview" id="imagePreview-${roomId}">
                                <i class="fa fa-image"></i>
                            </div>
                            <div class="file-input-wrapper">
                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-upload mr-1"></i> Upload
                                </button>
                                <input type="file" class="room-image-input"
                                    id="roomImage-${roomId}"
                                    data-file-type="image"
                                    data-room="${roomNumber}">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm add-item-to-room-btn" data-room="${roomNumber}">
                    <i class="fa fa-plus mr-1"></i> Add Item To Room ${roomNumber}
                </button>
            </div>
            <div class="product-tabs-container" id="productTabs-room${roomNumber}">
                <div class="product-empty-state">
                    <i class="fa fa-cube"></i>
                    <p>No products added yet</p>
                </div>
            </div>
            <div class="product-content-area" id="productContent-room${roomNumber}">
            </div>
        </div>
    </div>
    `);

        $('#roomTabsContent').append($pane);
        $(`#${roomId}-tab`).tab('show');
        updateRoomStatus(roomId);
        addRoomToState(roomNumber);
    });

    $(document).on('click', '.add-item-to-room-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        let roomId = $(this).data('room');
        console.log('Add item to room button clicked, roomId from data:', roomId);

        if (!roomId) {
            const $roomPane = $(this).closest('.tab-pane');
            if ($roomPane.length) {
                roomId = $roomPane.data('room');
                console.log('RoomId from pane data:', roomId);
            }
        }

        if (roomId) {
            console.log('Final roomId for main category modal:', roomId);
            showMainCategoryModal(roomId);
        } else {
            console.error('Could not determine roomId for product');
        }
        setTimeout(updateOrderTotals, 500);
    });

    // Replace the generic dimension change handlers with more specific ones
    $(document).on('input change', '.product-content .dimension-width, .product-content .dimension-length, .product-content .dimension-height, .product-content .product-qty, .product-content .product-discount, .product-content .surcharge-checkbox', function() {
        const $productContent = $(this).closest('.product-content');
        if ($productContent.length) {
            const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

            // Use debouncing for calculations
            debounce(() => {
                calculateProductTotal(productId, roomId);
            }, 300)();
        }
    });

    // More specific material change handlers
    $(document).on('input change', '.product-content .area-weight, .product-content .curtain-fabric-length, .product-content .curtain-fabric-height, .product-content .material-type-select, .product-content .material-replacement, .product-content .material-type-replacement, .product-content .pillow-quantity, .product-content .pillow-length, .product-content .pillow-width', function() {
        const $productContent = $(this).closest('.product-content');
        if ($productContent.length) {
            const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

            debounce(() => {
                calculateProductTotal(productId, roomId);
            }, 300)();
        }
    });

    // Call updateOrderTotals when tax percentage changes
    $('#lblOrderTax').on('change', function() {
        updateGrandTotals();
    });

    // Call updateOrderTotals when items are added/removed
    $(document).on('click', '.add-product-item-btn, .items-tab-close', function() {
        setTimeout(updateOrderTotals, 100);
    });

    // Call updateOrderTotals when accessories are added/removed
    $(document).on('click', '.add-accessory-btn, .accessory-tab-close', function() {
        setTimeout(updateOrderTotals, 100);
    });

    // Main Category Modal Event Handlers
    $('#closeMainCategoryModal').on('click', hideMainCategoryModal);
    $('#confirmMainCategory').on('click', function() {
        console.log('Confirm main category clicked');
        console.log('Current state:', {
            selectedMainCategory: state.selectedMainCategory,
            currentRoom: state.currentRoom
        });

        if (state.selectedMainCategory && state.currentRoom) {

            hideMainCategoryModal();

            setTimeout(() => {
                console.log('Showing qualification modal for main category:', state.selectedMainCategory, 'room:', state.currentRoom);
                showQualificationModalFiltered(state.selectedMainCategory, state.currentRoom);
            }, 100);
        }
    });

    $('#mainCategorySearch').on('input', function() {
        filterMainCategoryOptions($(this).val());
    });

    $('#mainCategoryModal').on('click', function(e) {
        if (e.target === this) hideMainCategoryModal();
    });

    // Hide tooltips when main category modal closes
    $('#closeMainCategoryModal').on('click', function() {
        $('.qualification-tooltip').removeClass('visible');
    });

    $('#confirmMainCategory').on('click', function() {
        $('.qualification-tooltip').removeClass('visible');
    });

    $(document).on('click', '#qualificationModalBackButton', function() {
        console.log('Back button clicked in qualification modal');
        hideQualificationModal();

        setTimeout(() => {
            console.log('Showing main category modal again');
            showMainCategoryModal(state.currentRoom);
        }, 100);
    });

    $(document).on('click', '#productSelectModalBackButton', function() {
        console.log('Back button clicked in multi-select modal');
        if (searchType === 'special') {
            state.selectedProducts = [];
            filterState.product.searchTerm = '';
            currentCountProduct = 0;
            applyProductFilters(state.selectedQualification);
        } else {
            hideProductSelectModal();

            setTimeout(() => {
                console.log('Showing qualification modal again');
                showQualificationModalFiltered(state.selectedMainCategory, state.currentRoom);
            }, 100);
        }
    });

    $(document).on('click', '#productSelectModalBackButtonComb', function() {
        currentCountProduct = 0;
        $('#ModalCombinationCreate').hide();
        applyProductFilters(state.selectedQualification);
    });

    $(document).on('click', '#mainCategoryOptions .qualification-option', function() {
        console.log('Main category option clicked:', $(this).data('category'));
        $('.qualification-option').removeClass('selected');
        $(this).addClass('selected');
        state.selectedMainCategory = $(this).data('category');
        $('#confirmMainCategory').prop('disabled', false);
        $('#confirmMainCategory').trigger('click');
    });

    $(document).on('click', '#qualificationOptions .qualification-option', function() {
        console.log('Qualification option clicked:', $(this).data('qualification'));
        $('.qualification-option').removeClass('selected');
        $(this).addClass('selected');
        state.selectedQualification = $(this).data('qualification');
        $('#confirmQualificationSelect').prop('disabled', false);
        $('#confirmQualificationSelect').trigger('click');
    });

    $('#confirmQualificationSelect').on('click', function() {
        console.log('Confirm add qualification clicked');
        console.log('Current state:', {
            selectedQualification: state.selectedQualification,
            currentRoom: state.currentRoom
        });

        if (state.selectedQualification && state.currentRoom) {
            if (String(state.selectedQualification || '').split('_').some(id => [142, 181, 180, 178, 179, 183].includes(Number(id)))) {
                hideQualificationModal();
                $.post(ajax_url + '/api', {
                    get_product: 1,
                    attr_id: state.selectedQualification
                }, function(data) {
                    // console.log(data);
                    var response = $.parseJSON(data);
                    console.log('selectedProduct:', response);
                    if (response.status === 'success') {
                        // Find the selected product
                        const selectedProductData = response.data;

                        if (selectedProductData) {
                            state.selectedProducts = [selectedProductData.product_id];
                            addProductTab(state.currentRoom, selectedProductData);
                        } else {
                            console.error('Could not find selected product');
                            alert('Error: Could not find the selected product.');
                        }
                    }
                });
            } else if (!sofasubmenu && String(state.selectedQualification || '').split('_').some(id => [63, 100, 129, 149].includes(Number(id)))) {
                console.log('Selected Sofas Qualification');
                filterQualificationOptions('sofa', false);
                sofasubmenu = true;
            } else {
                hideQualificationModal();
                console.log('Showing multi-select modal with roomId:', state.currentRoom, ' And qualification:', state.selectedQualification);
                showProductSelectModal(state.selectedQualification, state.currentRoom);
            }
        }
    });

    $(document).on('click', '.multi-select-option[data-product-id]', function(e) {
        if (!($(e.target).is('button') || $(e.target).closest('button').length)) {
            const productId = $(this).data('product-id');
            console.log('Product option clicked:', productId);

            $('#productSelectModal')
                .data('qualification', $(this).data('attr-id'))
                .data('roomId', state.currentRoom);

            // Single selection
            $('.multi-select-option[data-product-id]').removeClass('selected');
            $(this).addClass('selected');

            // Store single product ID
            state.selectedProducts = [productId];
            $('#confirmMultiSelect').prop('disabled', false);
            $('#confirmMultiSelect').trigger('click');
        }
    });

    $(document).on('click', '.multi-select-option[data-item-id]', function(e) {
        if (!($(e.target).is('button') || $(e.target).closest('button').length)) {
            const itemId = $(this).data('item-id');

            // Single selection
            $('.multi-select-option[data-item-id]').removeClass('selected');
            $(this).addClass('selected');

            // Store single item ID
            state.selectedItems = [itemId];
            $('#confirmSelectItem').prop('disabled', false);
            $('#confirmSelectItem').trigger('click');
        }
    });

    $('#confirmMultiSelect').on('click', function() {
        console.log('Confirm product selection clicked');

        const qualification = $('#productSelectModal').data('qualification');
        const roomId = $('#productSelectModal').data('roomId');

        console.log('Data for product addition:', {
            qualification: qualification,
            roomId: roomId,
            selectedProducts: state.selectedProducts
        });

        if (state.selectedProducts.length > 0 && roomId && qualification) {
            const selectedProductId = state.selectedProducts[0];
            hideMainCategoryModal();
            hideQualificationModal();
            hideProductSelectModal();
            console.log('Selected product to add:', selectedProductId);

            $.post(ajax_url + '/api', {
                get_product: 1,
                product_id: selectedProductId
            }, function(data) {
                // console.log(data);
                var response = $.parseJSON(data);
                console.log('selectedProduct:', response);
                if (response.status === 'success') {
                    // Find the selected product
                    const selectedProductData = response.data;

                    if (selectedProductData) {
                        addProductTab(roomId, selectedProductData);
                    } else {
                        console.error('Could not find selected product');
                        alert('Error: Could not find the selected product.');
                    }
                }
            });
        } else {
            console.error('Missing data for product addition');
            alert('Please select a product to continue.');
        }
    });

    $('#confirmSelectItem').on('click', function() {
        console.log('Confirm item selection clicked');

        const roomId = $('#itemSelectionModal').data('current-room');
        const productId = $('#itemSelectionModal').data('current-product');

        const productContent = $(`#product-${productId}-room${roomId}`);
        const dimensionWidth = productContent.find('.dimension-width').val();
        const dimensionLength = productContent.find('.dimension-length').val();

        if (dimensionWidth == 0 || dimensionLength == 0) {
            alert('Please enter Width and Length dimensions before adding an item.');
            return;
        }

        console.log('Context from modal:', {
            roomId: roomId,
            productId: productId,
            selectedItem: state.selectedItems
        });

        if (state.selectedItems && roomId && productId) {
            const selectedItemId = state.selectedItems[0];
            console.log('Adding item with confirmed context:', {
                roomId: roomId,
                productId: productId,
                selectedItem: selectedItemId
            });

            $.post(ajax_url + '/api', {
                get_product: 1,
                product_id: selectedItemId
            }, function(data) {
                // console.log(data);
                var response = $.parseJSON(data);
                console.log('selectedProduct:', response);
                if (response.status === 'success') {
                    // Find the selected product
                    const selectedItem = response.data;

                    if (selectedItem) {
                        addItemToProduct(roomId, productId, selectedItem);
                        setTimeout(() => {
                            calculateProductTotal(productId, roomId, 'fitout');
                        }, 1000);
                        hideItemSelectionModal();
                    } else {
                        console.error('Could not find selected item');
                        alert('Error: Could not find the selected item.');
                    }
                }
            });
        } else {
            console.error('Missing data for item selection');
            alert('Error: Missing context information. Please try again.');
        }
    });

    // Add accessory option selection handler
    $(document).on('click', '#accessoryOptions .item-option', function() {
        console.log('Accessory option clicked:', $(this).data('accessory-id'));

        // Remove selection from all accessory options
        $('#accessoryOptions .item-option').removeClass('selected');

        // Add selection to clicked option
        $(this).addClass('selected');

        const accessoryId = $(this).data('accessory-id');
        const attrId = $(this).data('attr-id');

        // Find the selected accessory
        state.selectedAccessory = curtainAccessoryTypes.find(accessory => accessory.id === accessoryId);
        state.selectedAccessoryAttrId = attrId;

        if (state.selectedAccessory && state.selectedAccessoryAttrId) {
            console.log('Selected accessory:', state.selectedAccessory);
            console.log('Selected attr_id:', state.selectedAccessoryAttrId);
            $('#confirmSelectAccessory').prop('disabled', false);
        } else {
            console.error('Could not find selected accessory with ID:', accessoryId);
            $('#confirmSelectAccessory').prop('disabled', true);
        }
    });

    $('#confirmSelectAccessory').on('click', function() {
        console.log('Confirm accessory selection clicked');

        if (state.selectedAccessory && state.selectedAccessoryAttrId) {
            const accessoryObj = state.selectedAccessory;
            const accessoryType = accessoryObj.name;
            // Check if this is for a curtain variant or regular product
            if (state.currentVariantId && state.currentProductId && state.currentRoom) {
                // This is for a curtain variant
                console.log('Adding accessory to curtain variant:', {
                    productId: state.currentProductId,
                    variantId: state.currentVariantId,
                    roomId: state.currentRoom,
                    accessory: state.selectedAccessory,
                    attrId: state.selectedAccessoryAttrId
                });

                $.post(ajax_url + '/api', {
                    get_curtain_accessories: 1,
                    accessory_type: accessoryType,
                    attr_id: state.selectedAccessoryAttrId
                }, function(data) {
                    // console.log(data);
                    var response = $.parseJSON(data);
                    console.log('selectedProduct:', response);
                    if (response.status === 'success') {
                        // Find the selected product
                        const accessoryOptions = response.data;

                        if (accessoryOptions) {
                            addAccessoryToCurtainVariant(state.currentRoom, state.currentProductId, state.currentVariantId, state.selectedAccessory, accessoryOptions);
                            calculateProductTotal(state.currentProductId, state.currentRoom, 'curtain');
                            hideAccessorySelectionModal();
                        } else {
                            console.error('Could not find accessories');
                            alert('Error: Could not find accessories.');
                        }
                    }
                });
            } else {
                // This is for a regular product (existing functionality)
                const $activeProductTab = $('.product-tab.active');
                if ($activeProductTab.length === 0) {
                    console.error('No active product tab found');
                    alert('Please select a product tab first.');
                    return;
                }

                console.log('Adding accessory to regular product:', {
                    productId: state.currentProductId,
                    roomId: state.currentRoom,
                    accessory: state.selectedAccessory,
                    attr_id: state.selectedAccessoryAttrId
                });

                $.post(ajax_url + '/api', {
                    get_curtain_accessories: 1,
                    accessory_type: accessoryType,
                    attr_id: state.selectedAccessoryAttrId
                }, function(data) {
                    // console.log(data);
                    var response = $.parseJSON(data);
                    if (response.status === 'success') {
                        // Find the selected product
                        const accessoryOptions = response.data;
                        console.log('accessoryOptions:', accessoryOptions);

                        if (accessoryOptions) {
                            addAccessoryToProduct(state.currentRoom, state.currentProductId, state.selectedAccessory, accessoryOptions);
                            hideAccessorySelectionModal();
                        } else {
                            console.error('Could not find accessories');
                            alert('Error: Could not find accessories.');
                        }
                    }
                });
            }
        } else {
            console.error('Missing accessory for selection');
            alert('Please select an accessory to add.');
        }
    });

    $(document).on('click', '.add-product-item-btn', function() {
        const productId = $(this).data('product');

        // Get room context
        let roomId = $(this).data('room');
        if (!roomId) {
            const $productContent = $(this).closest('.product-content');
            if ($productContent.length) {
                const contentId = $productContent.attr('id');
                const roomMatch = contentId.match(/room(\d+)/);
                if (roomMatch) roomId = roomMatch[1];
            }
        }

        console.log('Add product item button clicked - Context:', {
            productId: productId,
            roomId: roomId
        });

        if (roomId && productId) {
            state.currentRoom = roomId;
            showItemSelectionModal(productId, roomId);
        } else {
            console.error('Could not determine room or product context');
            alert('Error: Could not determine room context. Please try again.');
        }
    });

    $(document).on('click', '.add-accessory-btn', function() {
        const productId = $(this).data('product');
        const attrId = $(this).data('attr-id');
        const variantId = $(this).data('variant');
        const roomId = $(this).data('room');
        console.log('Add accessory button clicked:', productId);
        showAccessorySelectionModal(productId, variantId, attrId, roomId);
    });

    $(document).on('click', '.close-room', function(e) {
        e.stopPropagation();
        const $tab = $(this).closest('a.room-tab');
        const totalRooms = $('#roomTabs a.room-tab').length;

        if (totalRooms <= 1) {
            alert('At least one room must be present.');
            return;
        }
        const confirmed = confirm('Are you sure you want to remove this room? This will remove all products and items within the room.');
        if (!confirmed) {
            return;
        }

        const roomId = $tab.attr('href').replace('#', '');
        const isActive = $tab.hasClass('active');

        $tab.closest('.nav-item').remove();
        $(`#${roomId}`).remove();

        renumberRooms();

        // Update totals after room removal
        updateGrandTotals();

        if (isActive) {
            const $remainingTabs = $('#roomTabs a.room-tab');
            if ($remainingTabs.length > 0) {
                const $firstTab = $remainingTabs.first();
                $firstTab.tab('show');
            }
        }
    });

    $(document).on('click', '.product-tab', function(e) {
        if (!$(e.target).closest('.product-tab-close').length) {
            activateProductTab($(this));
        }
    });

    // Clean up handlers when product tab is removed
    $(document).on('click', '.product-tab-close', function(e) {
        e.stopPropagation();
        const $tab = $(this).closest('.product-tab');
        const $tabsContainer = $tab.closest('.product-tabs-container');
        const productId = $tab.data('product');
        const roomId = $tabsContainer.attr('id').replace('productTabs-room', '');

        // Clean up curtain handlers
        const productKey = `curtain-${productId}-room${roomId}`;
        if (window.curtainHandlers && window.curtainHandlers[productKey]) {
            delete window.curtainHandlers[productKey];
        }

        // Rest of your removal code...
        $(`#product-${productId}-room${roomId}`).remove();
        $tab.remove();

        updateRoomTotals(roomId);
        updateGrandTotals();

        if ($tab.hasClass('active')) {
            const $firstTab = $tabsContainer.find('.product-tab').first();
            if ($firstTab.length) {
                activateProductTab($firstTab);
            }
        }
    });

    $(document).on('click', '.items-tab', function(e) {
        // Don't activate if clicking the close button
        if ($(e.target).closest('.items-tab-close').length) {
            return;
        }

        const $tab = $(this);
        activateItemTab($tab);
    });

    // Accessory tab click handler
    $(document).on('click', '.product-content .accessory-tab', function(e) {
        // Don't activate if clicking the close button
        if ($(e.target).closest('.accessory-tab-close').length) {
            return;
        }

        const $tab = $(this);
        const $productContent = $tab.closest('.product-content');
        const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
        const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

        console.log('Regular accessory tab clicked:', {
            productId,
            roomId
        });
        activateAccessoryTab($tab, roomId, productId);
    });

    $(document).on('click', '.variant-details .accessory-tab', function(e) {
        // Don't activate if clicking the close button
        if ($(e.target).closest('.accessory-tab-close').length) {
            return;
        }

        const $tab = $(this);
        const $variantContent = $tab.closest('.variant-details');
        const variantId = $variantContent.find('.product-qty').first().data('variant');
        const $productContent = $variantContent.closest('.product-content');
        const productId = $productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
        const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

        console.log('Curtain accessory tab clicked:', {
            productId,
            variantId,
            roomId
        });
        activateCurtainAccessoryTab($tab, roomId, productId, variantId);
    });

    $(document).on('input change', '.room-name-input, .floor-name-input', function() {
        const roomId = $(this).data('room-id');
        const floorName = $(`#floorName-${roomId}`).val().trim();
        const roomName = $(`#roomName-${roomId}`).val().trim();
        const tabId = `${roomId}-tab`;
        const roomNumber = $(`#${tabId}`).data('room');
        const $tab = $(`#${tabId} .room-title`);
        const $addRoomBtn = $(`.add-item-to-room-btn[data-room="${roomNumber}"]`);
        const roomTabName = `${floorName}-${roomName}`;
        if (roomName && floorName) {
            $tab.text(roomTabName);
            $addRoomBtn.text('Add Item To ' + roomTabName);
        } else {
            $tab.text('Room ' + roomNumber);
            $addRoomBtn.text('Add Item To Room ' + roomNumber);
        }
    });

    $('#closeQualificationModal').on('click', hideQualificationModal);
    $('#closeProductSelectModal').on('click', hideProductSelectModal);
    $('#closeItemSelectionModal').on('click', hideItemSelectionModal);
    $('#closeAccessorySelectionModal').on('click', hideAccessorySelectionModal);

    $('#qualificationModal').on('click', function(e) {
        if (e.target === this) hideQualificationModal();
    });
    $('#productSelectModal').on('click', function(e) {
        if (e.target === this) hideProductSelectModal();
    });
    $('#itemSelectionModal').on('click', function(e) {
        if (e.target === this) hideItemSelectionModal();
    });
    $('#accessorySelectionModal').on('click', function(e) {
        if (e.target === this) hideAccessorySelectionModal();
    });

    // Filter toggle button
    $('#filterToggleButton').on('click', function() {
        showAdvancedFilterModal();
    });

    // Close advanced filter modal
    $('#closeAdvancedFilter').on('click', function() {
        hideAdvancedFilterModal();
    });

    // Close advanced filter modal when clicking outside
    $('#advancedFilterModal').on('click', function(e) {
        if (e.target === this) hideAdvancedFilterModal();
    });


    function getMeasurementLabel(category) {
        const labels = {
            'metal': 'Weight (Kg)',
            'wood': 'Area (m²)',
            'marble': 'Area (m²)',
            'glass': 'Area (m²)',
            'fabric': 'Area (m²)'
        };
        return labels[category] || 'Measurement';
    }

    function getMeasurementValue(material, category) {
        const values = {
            'metal': material.weight_kg,
            'wood': material.area_m2,
            'marble': material.area_m2,
            'glass': material.area_m2,
            'fabric': material.area_m2
        };
        return values[category] || '';
    }

    function getMeasurementPlaceholder(category) {
        const placeholders = {
            'metal': 'Enter weight in Kg',
            'wood': 'Enter area in m²',
            'marble': 'Enter area in m²',
            'glass': 'Enter area in m²',
            'fabric': 'Enter area in m²'
        };
        return placeholders[category] || 'Enter measurement';
    }

    // Reset Advanced Filters for Items
    function resetItemAdvancedFilters() {
        // Reset form values
        $('#itemMinPrice').val(0);
        $('#itemMaxPrice').val(50000);
        $('#itemPriceRange').val(50000);
        $('#itemNewestFirst').prop('checked', true);
        $('#itemInStock').prop('checked', true);
        $('#itemOutOfStock').prop('checked', false);

        // Reset filter state
        filterState.item.selectedStyles = [];
        filterState.item.selectedBrand = '';
        filterState.item.minPrice = 0;
        filterState.item.maxPrice = 50000;
        filterState.item.dateSort = 'newest';
        filterState.item.inStock = true;
        filterState.item.outOfStock = false;

        // Reset UI elements - PROPERLY
        $('.brand-radio-input[name="item-brand-selection"]').prop('checked', false);
        $('#item-brand-all').prop('checked', true); // Select "All Brands"

        // Reset style checkboxes visually
        $('.style-checkbox-tab').removeClass('selected');
        $('.style-checkbox').html(''); // Clear check icons

        console.log('Filters reset:', filterState.item);

        // Apply filters immediately after reset
        applyItemFilters();
    }

    // Show/Hide Advanced Filter Modal for Items
    function showItemAdvancedFilterModal() {
        $('#itemAdvancedFilterModal').fadeIn(300);
    }

    // Add event listener for item filter toggle button
    $(document).on('click', '#itemFilterToggleButton', function() {
        showItemAdvancedFilterModal();
    });

    // Close item advanced filter modal
    $(document).on('click', '#closeItemAdvancedFilter', function() {
        hideItemAdvancedFilterModal();
    });

    // Close item advanced filter modal when clicking outside
    $('#itemAdvancedFilterModal').on('click', function(e) {
        if (e.target === this) hideItemAdvancedFilterModal();
    });
</script>
<script>
    // Function to collect all order data and submit for update
    function collectOrderData() {
        const orderData = {
            update_order_with_newlayout: 1,
            order_id: <?php echo $order_id; ?>,
            order_date: $('#lblOrderDate').val(),
            order_delivery_date: $('#lblOrderDeliveryDate').val(),
            customer_id: $('#lblOrderCustomer').val(),
            customer_address_id: $('#lblOrderCustomerAddress').val(),
            order_arcs: $('input[name="order_arcs"]').val(),
            order_agreement: $('#lblOrderAgreement').val(),
            order_agreement_text: $('#lblOrderAgreementText').val(),
            order_tax: $('#lblOrderTax').val(),
            order_export_registered: $('#lblOrderExportRegistered').val(),
            order_notes: $('#lblOrderNotes').val(),
            order_comm_rate: $('#lblOrderCommRate').val() || '',
            order_comm_amount: $('#lblOrderCommAmount').val() || '',
            order_status: $('#lblOrderStatus').val() || 'quotation',
            dlv_date_modified: $('#lblDlvDateModified').val() || '0',
            // New room-based data
            rooms: []
        };

        // Collect room data
        $('.tab-pane').each(function() {
            const $roomPane = $(this);
            const roomId = $roomPane.data('room');

            const roomData = {
                room_id: roomId,
                floor_name: $(`#floorName-room${roomId}`).val(),
                room_name: $(`#roomName-room${roomId}`).val(),
                products: []
            };

            // Collect products in this room
            $(`#productTabs-room${roomId} .product-tab`).each(function() {
                const $productTab = $(this);
                const productId = $productTab.data('product');
                const productType = $productTab.data('type');
                const availableIn = $productTab.data('available-in');
                const detailId = $productTab.data('detail-id'); // Get detail_id for updates

                const productData = collectProductDataForUpdate(productId, roomId, productType, availableIn, detailId);
                if (productData) {
                    roomData.products.push(productData);
                }
            });

            orderData.rooms.push(roomData);
        });

        return orderData;
    }

    // Function to collect product data with new structure for update
    function collectProductDataForUpdate(productId, roomId, productType, availableIn, detailId = null) {
        const $productContent = $(`#product-${productId}-room${roomId}`);
        const originalProductId = extractOriginalProductId(String(productId));
        if (!$productContent.length) return null;

        const productData = {
            product_id: originalProductId,
            detail_id: detailId, // Include detail_id for updates
            type: productType,
            available_in: availableIn,
            quantity: parseFloat($productContent.find('.product-qty').val()) || 1,
            discount: parseFloat($productContent.find('.product-discount').val()) || 0,
            unit_price: parseFloat($productContent.find('.unit-price').val()) || 0,
            calculate_type: $productContent.find('.calculate-type').val() || 'standart',
            notes_tr: $productContent.find('.product-notes-tr').val() || '',
            notes_en: $productContent.find('.product-notes-en').val() || '',
            sponge_type: $productContent.find('.sponge-type').val() || '',
            person_weight: parseFloat($productContent.find('.person-weight').val()) || 0,
            wholesale_percentage: parseFloat($productContent.find('.wholesale-percentage').val()) || 0
        };

        // Collect dimensions
        const width = parseFloat($productContent.find('.dimension-width').val()) || 0;
        const length = parseFloat($productContent.find('.dimension-length').val()) || 0;
        const height = parseFloat($productContent.find('.dimension-height').val()) || 0;
        const standart_width = parseFloat($productContent.find('.dimension-width').data('standart_width')) || 0;
        const standart_length = parseFloat($productContent.find('.dimension-length').data('standart_length')) || 0;
        const standart_height = parseFloat($productContent.find('.dimension-height').data('standart_height')) || 0;
        const standart_price = parseFloat($productContent.find('.standart-price').val()) || 0;
        const quantity = parseFloat($productContent.find('.product-qty').val()) || 0;
        const discount = parseFloat($productContent.find('.product-discount').val()) || 0;
        const unit_price = parseFloat($productContent.find('.unit-price').val()) || 0;
        const notes_tr = $productContent.find('.product-notes-tr').val() || '';
        const notes_en = $productContent.find('.product-notes-en').val() || '';

        productData.width = width;
        productData.length = length;
        productData.height = height;
        productData.standart_width = standart_width;
        productData.standart_length = standart_length;
        productData.standart_height = standart_height;
        productData.standart_price = standart_price;
        productData.standart_width = standart_width;
        productData.quantity = quantity;
        productData.discount = discount;
        productData.unit_price = unit_price;
        productData.notes_tr = notes_tr;
        productData.notes_en = notes_en;

        // Collect bed dimension for bed products
        if (productType === 'bed') {
            const $selectedVariant = $productContent.find('.variant-radio-input:checked');
            if ($selectedVariant.length) {
                productData.bed_dim = $selectedVariant.val();
            }
        }

        // Collect sets/variants data for detail_attr
        if (availableIn === 'set') {
            productData.sets = collectSetsDataForUpdate(productId, roomId, productType);
        }

        // Collect selected variant for size products
        if (availableIn === 'size') {
            const selectedVariant = $productContent.find('.variant-radio-input:checked').val();
            if (selectedVariant) {
                productData.bed_dim = selectedVariant;
                productData.selected_size = collectSizeData(productId, selectedVariant, roomId, productType);
            }
        }

        // Collect curtain-specific data for product_curtain_data
        if (productType === 'curtain') {
            productData.curtain_data = collectCurtainDataNewLayout(productId, roomId);
        }

        // Collect fitout items
        if (productType === 'fitout') {
            productData.items = collectItemsDataNewLayout(productId, roomId);
        }

        // Collect materials with proper structure
        productData.materials = collectMaterialsDataNewLayout(productId, roomId);

        // Collect surcharges for attr_rates
        productData.surcharges = collectSurchargesDataNewLayout(productId, roomId);

        return productData;
    }

    // Function to collect items data for fitout products in update
    function collectItemsDataNewLayout(productId, roomId) {
        const items = [];

        $(`#product-${productId}-room${roomId} .items-tab`).each(function() {
            const $item = $(this);
            const itemId = $item.data('item-id');
            const $itemContent = $(`#item-${itemId}-${productId}-room${roomId}`);

            if ($itemContent.length) {
                const itemData = {
                    item_id: itemId,
                    width: parseFloat($itemContent.find('.item-width').val()) || 0,
                    length: parseFloat($itemContent.find('.item-length').val()) || 0,
                    height: parseFloat($itemContent.find('.item-height').val()) || 0,
                    standart_width: parseFloat($itemContent.find('.item-width').data('standart_width')) || 0,
                    standart_length: parseFloat($itemContent.find('.item-length').data('standart_length')) || 0,
                    standart_height: parseFloat($itemContent.find('.item-height').data('standart_height')) || 0,
                    standart_price: parseFloat($itemContent.find('.standart-price').val()) || 0,
                    quantity: parseFloat($itemContent.find('.item-qty').val()) || 1,
                    discount: parseFloat($itemContent.find('.item-discount').val()) || 0,
                    notes: $itemContent.find('.item-notes').val() || '',
                    unit_price: parseFloat($itemContent.find('.item-unit-price').val()) || 0,
                    calculate_type: $itemContent.find('.item-calculate-type').val() || 'standart',
                    materials: collectMaterialsDataNewLayout(productId, roomId, null, itemId),
                    surcharges: collectSurchargesDataNewLayout(itemId, roomId)
                };

                items.push(itemData);
            }
        });

        return items;
    }

    // Updated function to collect curtain data for product_curtain_data
    function collectCurtainDataNewLayout(productId, roomId) {
        const $productContent = $(`#product-${productId}-room${roomId}`);
        const curtainData = {};

        // Check if this is a variant-based curtain or single curtain
        const hasVariants = $productContent.find('.product-variants-section').length > 0;

        if (hasVariants) {
            // Collect curtain data for each variant/set
            $productContent.find('.product-variant-content').each(function() {
                const $variant = $(this);
                const variantId = $variant.find('.product-qty').first().data('variant');
                const setLevel = $variant.index() + 1;

                curtainData[setLevel] = collectCurtainSetData($variant, productId, variantId, roomId);
            });
        } else {
            // Single curtain product - use the product content directly
            curtainData[1] = collectCurtainSetData($productContent, productId, null, roomId);
        }

        return curtainData;
    }

    // Fixed function to collect curtain set data
    function collectCurtainSetData($container, productId, variantId, roomId) {
        const curtainSet = {};
        const fabricMaterials = [];

        console.log('Collecting curtain set data for:', {
            container: $container.attr('id'),
            productId,
            variantId,
            roomId
        });

        // First, try to find fabric material groups with ref_labels
        $container.find('.material-group[data-ref-label]').each(function() {
            const $materialGroup = $(this);
            const refLabel = $materialGroup.data('ref-label');

            // Check if this is a fabric material by looking at the parent tab content
            const $materialTabContent = $materialGroup.closest('.material-tab-content');
            const tabContentId = $materialTabContent.attr('id') || '';

            // Check if this is fabric category by ID or by content
            const isFabric = tabContentId.includes('-fabric') ||
                $materialGroup.find('.curtain-fabric-length').length > 0 ||
                $materialGroup.find('.curtain-fabric-height').length > 0;

            if (isFabric) {
                const materialType = $materialGroup.find('.material-type-select').val();
                const length = parseFloat($materialGroup.find('.curtain-fabric-length').val()) || 0;
                const height = parseFloat($materialGroup.find('.curtain-fabric-height').val()) || 0;
                const $selectedOption = $materialGroup.find('.material-type-select option:selected');
                const unitPrice = parseFloat($selectedOption.data('price')) || 0;

                console.log('Found fabric material group:', {
                    refLabel,
                    materialType,
                    length,
                    height,
                    unitPrice,
                    isFabric
                });

                if (materialType) {
                    fabricMaterials.push({
                        ref_label: refLabel,
                        material_id: materialType,
                        length: length,
                        height: height,
                        unit_price: unitPrice,
                        price: calculateCurtainPrice(length, height, unitPrice)
                    });
                }
            }
        });

        // If no fabric groups found with ref_labels, check for single fabric input
        if (fabricMaterials.length === 0) {
            console.log('No fabric groups found, checking for single fabric input...');

            // Try different possible IDs for the fabric content
            const possibleFabricIds = [
                `materialContent-${variantId ? `variant-${productId}-${variantId}` : `product-${productId}`}-room${roomId}-fabric`,
                `materialContent-${productId}-${variantId || ''}-room${roomId}-fabric`,
                `materialContent-${productId}-room${roomId}-fabric`
            ];

            let $fabricContent = null;
            for (const fabricId of possibleFabricIds) {
                $fabricContent = $(`#${fabricId}`);
                if ($fabricContent.length) {
                    console.log('Found fabric content with ID:', fabricId);
                    break;
                }
            }

            if (!$fabricContent || !$fabricContent.length) {
                // Try to find any fabric content by searching for curtain-specific inputs
                $fabricContent = $container.find('.material-tab-content').has('.curtain-fabric-length, .curtain-fabric-height').first();
            }

            if ($fabricContent && $fabricContent.length) {
                const materialType = $fabricContent.find('.material-type-select').val();
                const length = parseFloat($fabricContent.find('.curtain-fabric-length').val()) || 0;
                const height = parseFloat($fabricContent.find('.curtain-fabric-height').val()) || 0;
                const $selectedOption = $fabricContent.find('.material-type-select option:selected');
                const unitPrice = parseFloat($selectedOption.data('price')) || 0;

                console.log('Single fabric input data:', {
                    materialType,
                    length,
                    height,
                    unitPrice
                });

                if (materialType) {
                    fabricMaterials.push({
                        ref_label: 'A', // Default ref_label
                        material_id: materialType,
                        length: length,
                        height: height,
                        unit_price: unitPrice,
                        price: calculateCurtainPrice(length, height, unitPrice)
                    });
                }
            } else {
                console.log('No fabric content found at all');
            }
        }

        console.log('Final fabric materials collected:', fabricMaterials);

        // Create curtain entries for each fabric material
        fabricMaterials.forEach((fabricMat, index) => {
            const curtainKey = `curtain${index + 1}`;
            curtainSet[curtainKey] = {
                ref_label: fabricMat.ref_label,
                fabric: fabricMat.material_id,
                length: fabricMat.length,
                height: fabricMat.height,
                unit_price: fabricMat.unit_price,
                price: fabricMat.price
            };
        });

        // FIXED: Collect accessory configuration with proper blackout color
        const accessoryConfig = {
            curtain_opening_direction: $container.find('.opening-direction').val() || '',
            curtain_open_with: $container.find('.open-with').val() || '',
            curtain_motor_price: $container.find('.open-with').val() === 'motor' ? '300' : '0',
            curtain_installation: $container.find('.curtain-installation-needed-checkbox').is(':checked') ? 'needed' : '',
            curtain_installation_price: $container.find('.curtain-installation-needed-checkbox').is(':checked') ? '200' : '0',
            accessory_types: [],
            accessory_ids: [],
            accessory_prices: {},
            accessory_price_types: {},
            accessory_price_depends_on: {},
            accessory_total_prices: {},
            accessory_blackout_color: '' // Initialize as empty string
        };

        // FIXED: Collect accessories with dynamic blackout color per accessory
        $container.find('.accessory-details').each(function() {
            const $accessoryDetail = $(this);
            const $selectedOption = $accessoryDetail.find('.accessory-options-select option:selected');

            if ($selectedOption.length && $selectedOption.val()) {
                const accessoryType = $selectedOption.data('accessory-type') || '';
                const accessoryId = $selectedOption.val();
                const price = parseFloat($selectedOption.data('price')) || 0;
                const priceType = $selectedOption.data('price-type') || '';
                const dependsOn = $selectedOption.data('price-depends-on') || '';

                accessoryConfig.accessory_types.push(accessoryType);
                accessoryConfig.accessory_ids.push(accessoryId);
                accessoryConfig.accessory_prices[accessoryId] = price;
                accessoryConfig.accessory_price_types[accessoryId] = priceType;
                accessoryConfig.accessory_price_depends_on[accessoryId] = dependsOn;

                // FIXED: Get blackout color specifically for this accessory if it's a blackout type
                if (accessoryType === 'black_out' || accessoryType === 'blackout') {
                    const blackoutColor = $accessoryDetail.find('.accessory-blackout-color').val() || '';
                    accessoryConfig.accessory_blackout_color = blackoutColor;
                    console.log('Found blackout color:', blackoutColor, 'for accessory:', accessoryId);
                }

                // Calculate total price based on type and dependencies
                let totalPrice = 0;
                if (priceType === 'Per Piece') {
                    const productQuantity = parseFloat($container.find('.product-qty').val()) || 1;
                    totalPrice = price * productQuantity;
                } else if (priceType === 'Per Meter') {
                    totalPrice = calculateCurtainBaseTotal(curtainSet, price, priceType, dependsOn);
                } else {
                    totalPrice = price;
                }

                accessoryConfig.accessory_total_prices[accessoryId] = Math.round(totalPrice * 100) / 100;
            }
        });

        curtainSet.Accessory = accessoryConfig;

        return curtainSet;
    }

    // Helper function to calculate curtain base total (for percentage calculations)
    function calculateCurtainBaseTotal(curtainSet, accessoryPrice, priceType, dependsOn) {
        let baseTotal = 0;
        let $multiplier = 0.2;
        // Sum up the lengths of all curtains for 'Per Meter' calculations
        for (const key in curtainSet) {
            if (key.startsWith('curtain')) {
                if (dependsOn === 'length') {
                    baseTotal += curtainSet[key].length * accessoryPrice;
                } else if (dependsOn === 'height') {
                    baseTotal += curtainSet[key].height * accessoryPrice;
                } else if (dependsOn === 'length and height') {
                    baseTotal += (curtainSet[key].length * curtainSet[key].height) * accessoryPrice;
                }
            }
        }
        return baseTotal;
    }

    // Updated curtain price calculation function
    function calculateCurtainPrice(length, height, unitPrice) {
        // For curtains, price is typically: (length × height) × unit price
        const area = length * height;
        const totalPrice = area * unitPrice;

        console.log('Curtain price calculation:', {
            length: length,
            height: height,
            area: area,
            unitPrice: unitPrice,
            totalPrice: totalPrice
        });

        return Math.round(totalPrice * 100) / 100;
    }

    // Function to collect variant data for update
    function collectSizeData(productId, variantId, roomId, productType) {
        const $variant = $(`#variant-${productId}-${variantId}-room${roomId}`);

        const variantData = {
            variant_id: variantId,
            width: parseFloat($variant.find('.dimension-width').val()) || 0,
            length: parseFloat($variant.find('.dimension-length').val()) || 0,
            height: parseFloat($variant.find('.dimension-height').val()) || 0,
            standart_width: parseFloat($variant.find('.dimension-width').data('standart_width')) || 0,
            standart_length: parseFloat($variant.find('.dimension-length').data('standart_length')) || 0,
            standart_height: parseFloat($variant.find('.dimension-height').data('standart_height')) || 0,
            standart_price: parseFloat($variant.find('.standart-price').val()) || 0,
            quantity: parseFloat($variant.find('.product-qty').val()) || 1,
            discount: parseFloat($variant.find('.product-discount').val()) || 0,
            unit_price: parseFloat($variant.find('.unit-price').val()) || 0,
            notes_tr: $variant.find('.product-notes-tr').val() || '',
            notes_en: $variant.find('.product-notes-en').val() || '',
            materials: collectMaterialsDataNewLayout(productId, roomId, variantId),
            surcharges: collectSurchargesDataNewLayout(productId, roomId, variantId)
        };

        return variantData;
    }

    // Function to collect surcharges data for attr_rates in update
    function collectSurchargesDataNewLayout(productId, roomId, variantId = null) {
        const surcharges = [];
        const prefix = variantId ? `variant-${productId}-${variantId}` : `product-${productId}`;

        $(`#${prefix}-room${roomId} .surcharge-checkbox:checked`).each(function() {
            const $surcharge = $(this);
            surcharges.push({
                applied: true,
                name: $surcharge.data('surcharge-name'),
                type: $surcharge.data('surcharge-type'), // 'plus' or 'minus'
                rate: parseFloat($surcharge.data('surcharge-rate')) || 0
            });
        });

        return surcharges;
    }

    // Update the collectMaterialsDataNewLayout function in edit script
    function collectMaterialsDataNewLayout(productId, roomId, variantId = null, itemId = null, index = 0) {
        const materials = {
            main: {}
        };

        let prefix;
        if (itemId) {
            prefix = `${itemId}-${productId}`;
        } else if (variantId) {
            prefix = `${productId}-${variantId}`;
        } else {
            prefix = `${productId}`;
        }

        const setLevel = index + 1;
        if (!materials.main[setLevel]) {
            materials.main[setLevel] = {};
        }

        // Collect regular materials
        $(`[id^="materialContent-${prefix}-room${roomId}-"]`).each(function() {
            const $categoryContent = $(this);
            const category = $categoryContent.attr('id').replace(`materialContent-${prefix}-room${roomId}-`, '');

            if (category === 'pillow') {
                // Handle pillow materials separately
                materials.main[setLevel][category] = collectPillowMaterials($categoryContent, setLevel);
                return;
            }

            if (!materials.main[setLevel][category]) {
                materials.main[setLevel][category] = [];
            }

            // Collect material groups for this category
            $categoryContent.find('.material-group').each(function() {
                const $materialGroup = $(this);
                const label = $materialGroup.data('label') || 'A';
                const materialId = $materialGroup.find('.material-type-select').val();
                const replacement = $materialGroup.find('.material-replacement').val() || '';
                const replacementType = $materialGroup.find('.material-type-replacement').val() || '';

                if (materialId) {
                    const $selectedOption = $materialGroup.find('.material-type-select option:selected');
                    const standardPrice = parseFloat($materialGroup.find('.material-type-select').data('standard-material-price')) || 0;
                    const areaWeight = parseFloat($materialGroup.find('.area-weight').val()) || 0;

                    materials.main[setLevel][category].push([
                        label,
                        materialId,
                        replacement,
                        replacementType,
                        standardPrice
                    ]);
                }
            });
        });

        return materials;
    }

    function collectPillowMaterials($pillowContent, setLevel) {
        const pillowMaterials = [];

        // Collect each pillow label tab content
        $pillowContent.find('.pillow-subcategory-content.active').each(function() {
            const $subcategoryContent = $(this);
            const subcategory = $subcategoryContent.attr('id').split('-').pop();

            $subcategoryContent.find('.pillow-material-group').each(function() {
                const $pillowGroup = $(this);
                const label = $pillowGroup.data('label') || 'A';
                const materialId = $pillowGroup.find('.material-type-select').val();

                if (materialId) {
                    const $selectedOption = $pillowGroup.find('.material-type-select option:selected');
                    const standardPrice = parseFloat($pillowGroup.find('.material-type-select').data('standard-material-price')) || 0;
                    const quantity = parseFloat($pillowGroup.find('.pillow-quantity').val()) || 1;
                    const length = parseFloat($pillowGroup.find('.pillow-length').val()) || 0;
                    const width = parseFloat($pillowGroup.find('.pillow-width').val()) || 0;

                    const pillowData = {
                        [subcategory]: materialId
                    };

                    pillowMaterials.push([
                        label,
                        pillowData,
                        quantity,
                        length,
                        width,
                        standardPrice
                    ]);
                }
            });
        });

        return pillowMaterials;
    }

    // Function to collect sets/variants data for detail_attr in update
    function collectSetsDataForUpdate(productId, roomId, productType) {
        const sets = [];

        $(`#variants-content-${productId}-room${roomId} .product-variant-content`).each(function() {
            const $variant = $(this);
            const variantId = $variant.find('.product-qty').first().data('variant');
            const setLevel = $variant.index() + 1;

            const setData = {
                set_level: setLevel,
                variant_id: variantId,
                width: parseFloat($variant.find('.dimension-width').val()) || 0,
                length: parseFloat($variant.find('.dimension-length').val()) || 0,
                height: parseFloat($variant.find('.dimension-height').val()) || 0,
                standart_width: parseFloat($variant.find('.dimension-width').data('standart_width')) || 0,
                standart_length: parseFloat($variant.find('.dimension-length').data('standart_length')) || 0,
                standart_height: parseFloat($variant.find('.dimension-height').data('standart_height')) || 0,
                standart_price: parseFloat($variant.find('.standart-price').val()) || 0,
                quantity: parseFloat($variant.find('.product-qty').val()) || 1,
                discount: parseFloat($variant.find('.product-discount').val()) || 0,
                unit_price: parseFloat($variant.find('.unit-price').val()) || 0,
                notes_tr: $variant.find('.product-notes-tr').val() || '',
                notes_en: $variant.find('.product-notes-en').val() || '',
                materials: collectMaterialsDataNewLayout(productId, roomId, variantId),
                surcharges: collectSurchargesDataNewLayout(productId, roomId, variantId)
            };

            // For curtain variants, collect curtain-specific data
            if (productType === 'curtain') {
                setData.curtain_data = collectCurtainSetData($variant, productId, variantId, roomId);
            }

            sets.push(setData);
        });

        return sets;
    }

    // Function to collect curtain options data
    function collectCurtainOptionsData(productId, roomId) {
        const $productContent = $(`#product-${productId}-room${roomId}`);

        return {
            opening_direction: $productContent.find('.opening-direction').val(),
            open_with: $productContent.find('.open-with').val(),
            installation_needed: $productContent.find('.curtain-installation-needed-checkbox').is(':checked')
        };
    }

    function mapCurtainMaterials(detail) {
        const materialsBySet = {};

        // Pre-check: validate input
        if (!detail || typeof detail !== 'object' || !detail.curtain_data) {
            return materialsBySet;
        }

        const curtainData = detail.curtain_data;
        if (Object.keys(curtainData).length === 0) {
            return materialsBySet;
        }

        Object.entries(curtainData).forEach(([setIndex, setData]) => {
            // Pre-check: validate set data
            if (!setData || typeof setData !== 'object') {
                materialsBySet[setIndex] = createDefaultSet();
                return;
            }

            const curtainData = setData[`curtain${setIndex}`] || {};
            const accessoryData = setData.Accessory || {};

            materialsBySet[setIndex] = {
                curtain: {
                    ref_label: curtainData.ref_label || '',
                    material_id: curtainData.material_id || '',
                    length: Number(curtainData.length) || 0,
                    height: Number(curtainData.height) || 0,
                    unit_price: Number(curtainData.unit_price) || 0,
                    price: Number(curtainData.price) || 0
                },
                configuration: {
                    opening_direction: accessoryData.curtain_opening_direction || '',
                    open_with: accessoryData.curtain_open_with || '',
                    motor_price: Number(accessoryData.curtain_motor_price) || 0,
                    installation: accessoryData.curtain_installation || '',
                    installation_price: Number(accessoryData.curtain_installation_price) || 0,
                    blackout_color: accessoryData.accessory_blackout_color || ''
                },
                accessories: getSafeAccessories(accessoryData)
            };
        });

        return materialsBySet;
    }

    function createDefaultSet() {
        return {
            curtain: {
                ref_label: '',
                material_id: '',
                length: 0,
                height: 0,
                unit_price: 0,
                price: 0
            },
            configuration: {
                opening_direction: '',
                open_with: '',
                motor_price: 0,
                installation: '',
                installation_price: 0,
                blackout_color: ''
            },
            accessories: []
        };
    }

    function getSafeAccessories(accessoryData) {
        if (!accessoryData || !Array.isArray(accessoryData.accessory_types)) {
            return [];
        }

        const accessoryTypes = accessoryData.accessory_types || [];
        const accessoryIds = accessoryData.accessory_ids || [];

        return accessoryTypes.map((type, index) => {
            const accessoryId = accessoryIds[index] || '';

            return {
                type: type || '',
                id: accessoryId,
                price: Number(accessoryData.accessory_prices?.[accessoryId]) || 0,
                price_type: accessoryData.accessory_price_types?.[accessoryId] || '',
                price_depends_on: accessoryData.accessory_price_depends_on?.[accessoryId] || '',
                total_price: Number(accessoryData.accessory_total_prices?.[accessoryId]) || 0
            };
        });
    }
    // Function to map API data to expected format for population
    function mapApiDataToExpectedFormat(detail) {
        const expectedData = {
            detail_id: detail.detail_id,
            product_id: detail.product_id,
            quantity: parseFloat(detail.quantity) || 1,
            discount: parseFloat(detail.discount) || 0,
            product_notes_tr: detail.product_notes_tr,
            product_notes_en: detail.product_notes_en
        };

        // Parse dimension data from attr_dims
        if (detail.detail_attr && detail.detail_attr.attr_dims) {
            expectedData.attr_dims = JSON.parse(detail.detail_attr.attr_dims);
        }

        // Map bed dimension
        if (detail.detail_attr && detail.detail_attr.attr_dims && detail.detail_attr.attr_bed_dim) {
            expectedData.attr_bed_dim = JSON.parse(detail.detail_attr.attr_dims);
            expectedData.bed_dim = detail.detail_attr.attr_bed_dim;
        }

        // Parse surcharges from attr_rates
        if (detail.detail_attr && detail.detail_attr.attr_rates) {
            expectedData.attr_rates = JSON.parse(detail.detail_attr.attr_rates);
        }

        // Map curtain data
        if (detail.curtain_data && Object.keys(detail.curtain_data).length > 0) {
            expectedData.curtain_data = mapCurtainMaterials(detail);
        }

        // Map fitout items
        if (detail.fitout_items_data && detail.fitout_items_data.length > 0) {
            expectedData.items = detail.fitout_items_data.map(item => ({
                item_id: item.item_id,
                width: parseFloat(item.width) || 0,
                length: parseFloat(item.length) || 0,
                height: parseFloat(item.height) || 0,
                quantity: parseFloat(item.quantity) || 1,
                discount: parseFloat(item.discount) || 0,
                unit_price: parseFloat(item.unit_price) || 0,
                calculate_type: item.calculate_type || 'standart',
                notes: item.notes || ''
            }));
        }

        // Map materials from detail_images
        if (detail.detail_images && detail.detail_images.length > 0) {
            if (!expectedData.materials) {
                expectedData.materials = {
                    main: {}
                };
            }
            expectedData.materials = mapDetailImagesToMaterials(detail.detail_images, expectedData.materials);
        }

        return expectedData;
    }

    // Function to map detail_images to materials structure
    function mapDetailImagesToMaterials(detailImages, existingMaterials) {
        const materials = existingMaterials || {
            main: {}
        };

        detailImages.forEach(image => {
            const setLevel = image.set_id || '1';
            const category = image.image_type; // fabric, pillow, etc.
            const refLabel = image.ref_label || 'A';
            const mt_type = image.mt_type || '';

            if (!materials.main[setLevel]) {
                materials.main[setLevel] = {};
            }
            if (!materials.main[setLevel][category]) {
                materials.main[setLevel][category] = [];
            }

            // Check if this material already exists for this refLabel
            const existingIndex = materials.main[setLevel][category].findIndex(item =>
                item[0] === refLabel
            );

            if (existingIndex === -1) {
                // For pillow materials with multiple parts
                if (category === 'pillow' && image.pillow_mt) {
                    let replacement_key = `${image.pillow_mt}_replacement`;
                    const pillowData = {};
                    pillowData[image.pillow_mt] = image.material_id;
                    pillowData[replacement_key] = image.replacement || '';

                    materials.main[setLevel][category].push([
                        refLabel,
                        pillowData
                    ]);
                } else {
                    // Regular materials
                    materials.main[setLevel][category].push([
                        refLabel,
                        image.material_id,
                        image.replacement || '',
                        mt_type
                    ]);
                }
            } else {
                // Update existing pillow material with additional parts
                if (category === 'pillow' && image.pillow_mt) {
                    const existingPillow = materials.main[setLevel][category][existingIndex][1];
                    if (typeof existingPillow === 'object') {
                        existingPillow[image.pillow_mt] = image.material_id;
                    }
                }
            }
        });

        return materials;
    }

    // Enhanced debug function
    function debugExistingData(productId, roomId, existingData) {
        console.log('=== EXISTING DATA DEBUG ===');
        console.log('Product:', productId, 'Room:', roomId);
        console.log('Basic fields:', {
            quantity: existingData.quantity,
            discount: existingData.discount,
            width: existingData.width,
            length: existingData.length,
            height: existingData.height,
            unit_price: existingData.unit_price,
            bed_dim: existingData.bed_dim
        });
        console.log('Surcharges:', existingData.surcharges);
        console.log('Curtain options:', existingData.curtain_options);
        console.log('Materials structure:', existingData.materials);
        console.log('Items:', existingData.items);
        console.log('=== END DEBUG ===');
    }

    $(document).on('click', '#loadMoreProducts', function() {
        loadMoreProducts();
    });
    $(document).on('click', '#loadMoreItems', function() {
        loadMoreItems();
    });

    // generate_delivery_date(product_shipping_data)
    $('#lblOrderDeliveryDate').on('click', function() {
        $('#date_modified').val('1');
        $('.date_modified-note').removeClass('d-none');
    });


    $(document).on("input", ".product-qty", function() {
        const $this = $(this);
        const $productContent = $this.closest('.product-content');

        // Basic validation
        if (!$productContent.length) return;

        var product_count = Math.max(0, parseInt($this.val()) || 0),
            stock_product_count = Math.max(0, parseInt($productContent.data("stock")) || 0),
            product_brand = Math.max(0, parseInt($productContent.data("supplier")) || 0),
            product_attr = Math.max(0, parseInt($productContent.data("attr")) || 0),
            row_index = Math.max(0, parseInt($productContent.data("hidden-product-row-index")) || 0);

        if (stock_product_count > 0) {
            if (product_count > stock_product_count) {
                const $inputElement = $this; // Store reference
                swal({
                    icon: "warning",
                    title: "Alert!",
                    text: "The selected product quantity exceeds the available stock. Do you want to adjust the quantity to match the stock limit?",
                    showCancelButton: true,
                    confirmButtonText: "Yes, adjust quantity",
                    cancelButtonText: "No",
                    allowOutsideClick: false,
                    showLoaderOnConfirm: true,
                }).then(function(isConfirm) {
                    if (isConfirm) {
                        $inputElement.val(stock_product_count);
                        console.log("Quantity updated to stock limit:", stock_product_count);
                    } else {
                        $inputElement.val("");
                    }
                }).catch(function(error) {
                    console.error('SweetAlert error:', error);
                    $inputElement.val(stock_product_count); // Default safe behavior
                });
                return; // Stop execution here
            }
        }

        if ($("#date_modified").val() == "0") {
            // Validate if product_shipping_data exists
            if (typeof product_shipping_data === 'undefined') {
                console.error('product_shipping_data is not defined');
                return;
            }

            var product_info = {
                product_brand: product_brand,
                product_count: product_count,
                product_attr: product_attr,
                row_index: row_index,
            };

            var existingProduct = product_shipping_data.find(function(old_item) {
                return (
                    old_item.product_brand === product_info.product_brand &&
                    old_item.row_index === product_info.row_index
                );
            });

            if (existingProduct) {
                existingProduct.product_count = product_info.product_count;
            } else {
                var sameBrandProduct = product_shipping_data.find(function(old_item) {
                    return (
                        old_item.product_brand === product_info.product_brand &&
                        old_item.row_index !== product_info.row_index
                    );
                });

                if (sameBrandProduct) {
                    sameBrandProduct.product_count = (sameBrandProduct.product_count || 0) + product_info.product_count;
                } else {
                    product_shipping_data.push(product_info);
                }
            }

            if (typeof generate_delivery_date === 'function') {
                generate_delivery_date(product_shipping_data);
            }
        }
    });
</script>
