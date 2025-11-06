<?php
if (!defined('inc_admin_pages')) {
   die;
}

define('inc_panel_header', true);
include PATH . '/inc/header.php';

$user = new User();
$logged = $user->getLogged('user_id,user_auth,user_branch');
$logged_auth = $logged->user_auth;
$b = new Branch();
$item = $b->getBranch($logged->user_branch);
$cate = explode(",", $item->catalog_ids);

$page_auth = ['admin', 'manager', 'user', 'sales', 'ordermngr', 'partner', 'graphic_and_media', 'encounter'];
if (!in_array($logged_auth, $page_auth)) {
   header("Location:" . URL);
   die;
}

$customer = new Customer();
$pa = new ProductAttribute();
$p = new Product();

$set = array();
$get_settings = Settings::getAll();
if (count($get_settings) > 0) {
   foreach ($get_settings as $setting) {
      $set[$setting->set_name] = $setting->set_value;
   }
}

/* order ürün rowu için zorunlu define */
define('inc_base_order_product_rowlayout', true);

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

   /* Updated Material Layout - Image on left, details on right */
   .material-inputs-compact {
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

   .material-compact-image {
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

   .material-compact-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .material-compact-image i {
      color: #6c757d;
      font-size: 2rem;
   }

   .material-compact-fields {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
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
      padding: 0 12px;
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
      /* padding: 16px; */
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
      padding: 16px;
      border: 1px solid #e0e0e0;
   }

   /* Material inputs for pillow subcategories */
   .pillow-material-inputs-compact {
      display: grid;
      grid-template-columns: 160px 1fr;
      gap: 16px;
      align-items: start;
   }

   .pillow-material-compact-image {
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

   .pillow-material-compact-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .pillow-material-compact-image i {
      color: #6c757d;
      font-size: 2rem;
   }

   .pillow-material-compact-fields {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
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
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
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
      z-index: 1070;
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
      /* max-height: 60vh; */
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
      border-radius: 6px;
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
      min-width: 80px;
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

      .material-inputs-compact {
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

      .pillow-material-inputs-compact {
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
      height: 340px;
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
      z-index: 1080;
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
      max-width: none !important;
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
</style>

<!-- Start content -->
<div class="content">
   <div class="container-fluid">
      <div class="page-title-box">
         <div class="row align-items-center">
            <div class="col-sm-6">
               <h4 class="page-title"><?php echo get_lang_text('orderadd_page_title'); ?> New System </h4>
            </div>
            <div class="col-sm-6 text-right">
               <a href="<?php echo URL; ?>/index.php?page=orders" class="btn btn-success">
                  <?php echo get_lang_text('orderadd_btn_all_orders'); ?>
               </a>
            </div>
         </div> <!-- end row -->
      </div>
      <!-- end page-title -->

      <div class="row">
         <div class="col-12">
            <div class="card m-b-30">
               <div class="card-body">
                  <form id="add_order_form" enctype="multipart/form-data">
                     <input type="hidden" name="add_order_with_new" value="1">
                     <div class="row">
                        <div class="order-page-col col-md-3 col-sm-6">
                           <div class="form-group">
                              <label for="lblOrderDate"><?php echo get_lang_text('orderadd_input_order_date'); ?> *</label>
                              <input type="text" name="order_date" class="form-control make-datepicker required" id="lblOrderDate" placeholder="<?php echo get_lang_text('orderadd_input_order_date'); ?> *" value="<?php echo date('d.m.Y'); ?>">
                           </div>
                        </div>
                        <div class="order-page-col col-md-3 col-sm-6">
                           <div class="form-group">
                              <label for="lblOrderDeliveryDate"><?php echo get_lang_text('orderadd_input_delivery_date'); ?> *</label>
                              <input type="text" name="order_delivery_date" class="form-control make-datepicker order_delivery_date required" id="lblOrderDeliveryDate" placeholder="<?php echo get_lang_text('orderadd_input_delivery_date'); ?> *" value="<?php echo date('d.m.Y', strtotime($item->order_delivery_date)); ?>">
                              <input type="hidden" name="dlv_date_modified" class="form-control" id="date_modified" value="0">
                              <small class="text-danger date_modified-note d-none">User-modified date is not automatically calculated.</small>
                           </div>
                        </div>
                        <div class="order-page-col col-md-3 col-sm-6">
                           <div class="form-group">
                              <label for="lblOrderArcs"><?php echo get_lang_text('orderadd_input_arcs'); ?> *</label>
                              <input type="text" name="order_arcs" class="form-control required" placeholder="<?php echo get_lang_text('orderadd_input_arcs'); ?> *">
                           </div>
                        </div>
                        <div class="order-page-col col-md-3 col-sm-6">
                           <div class="form-group">
                              <label for="lblOrderCustomer"><?php echo get_lang_text('orderadd_input_customer'); ?> *</label>
                              <select name="customer_id" id="lblOrderCustomer" class="form-control make-it-select order-select-customer required">
                                 <option value=""><?php echo get_lang_text('orderadd_input_customer_select'); ?></option>
                                 <?php
                                 $customers_where = [['customer_status', '=', '1']];
                                 if ($logged->user_auth == 'user') {
                                    $customers_where[] = ['customer_added_branch', '=', $logged->user_branch];
                                 }
                                 $customers = $customer->getCustomers('customer_id,customer_name,customer_comm_rate', ['customer_name', 'ASC'], $customers_where);
                                 if (count($customers) > 0) {
                                    foreach ($customers as $c) {
                                 ?>
                                       <option value="<?php echo $c->customer_id; ?>" data-comm-rate="<?php echo formatExcelPrice($c->customer_comm_rate, 0); ?>"><?php echo $c->customer_name; ?></option>
                                 <?php
                                    }
                                 }
                                 ?>
                              </select>
                           </div>
                        </div>
                        <div class="order-page-col col-md-3 col-sm-6 full-w-s">
                           <div class="form-group">
                              <label for="lblOrderCustomerAddress"><?php echo get_lang_text('orderadd_input_delivery_address'); ?> *</label>
                              <select name="customer_address_id" id="lblOrderCustomerAddress" class="form-control make-it-select order-select-customer-address required" disabled>
                                 <option value=""><?php echo get_lang_text('orderadd_input_delivery_address_select'); ?></option>
                              </select>
                           </div>

                           <div class="form-group order-show-country" style="display: none;">
                              <label for="lblOrderAddress"><?php echo get_lang_text('orderadd_input_delivery_address_detail'); ?></label><br>
                              <div class="address-detail">
                                 <strong><?php echo get_lang_text('orderadd_input_delivery_address_detail_country'); ?></strong> : <span class="order-address-country"></span>
                                 <strong><?php echo get_lang_text('orderadd_input_delivery_address_detail_address'); ?></strong> : <span class="order-address-text"></span><br>
                              </div>
                           </div>
                        </div>

                        <div class="order-page-col col-md-3 col-sm-6 full-w-s">
                           <div class="form-group">
                              <label for="lblOrderExportRegistered"><?php echo get_lang_text('orderadd_input_export_register'); ?> *</label>
                              <select name="order_export_registered" id="lblOrderExportRegistered" class="form-control required">
                                 <option value="1"><?php echo get_lang_text('orderadd_input_export_register_yes'); ?></option>
                                 <option value="0"><?php echo get_lang_text('orderadd_input_export_register_no'); ?></option>
                              </select>
                           </div>
                        </div>

                        <div class="order-page-col col-md-3 col-sm-3">
                           <div class="form-group row">
                              <label class="col-sm-12"><?php echo get_lang_text('orderadd_input_tax'); ?> *</label>
                              <div class="col-sm-12">
                                 <select name="order_tax" id="lblOrderTax" class="form-control make-it-select required">
                                    <?php
                                    for ($i = 0; $i <= 24; $i++) {
                                    ?>
                                       <option value="<?php echo $i; ?>"><?php echo $i; ?>%</option>
                                    <?php
                                    }
                                    ?>
                                 </select>
                              </div>
                           </div>
                        </div>

                        <div class="order-page-col col-md-3 col-sm-3">
                           <div class="form-group row">
                              <label class="col-sm-12"><?php echo get_lang_text('orderadd_input_contracts'); ?> *</label>
                              <div class="col-sm-12">
                                 <select name="order_agreement" id="lblOrderAgreement" class="form-control make-it-select select-order-agreement required">
                                    <option value=""><?php echo get_lang_text('orderadd_input_contracts_select'); ?></option>
                                    <?php
                                    $agr_where = [['agr_status', '=', '1']];
                                    if ($logged_auth == 'user' || $logged_auth == 'partner') {
                                       $agr_where[] = ['branch_id', '=', $logged->user_branch];
                                    }
                                    $agreement = new Agreement();
                                    $agreements = $agreement->getAgreements('agr_id,agr_title', ['agr_title', 'ASC'], $agr_where);
                                    if (count($agreements) > 0) {
                                       foreach ($agreements as $agr) {
                                    ?>
                                          <option value="<?php echo $agr->agr_id; ?>"><?php echo $agr->agr_title; ?></option>
                                    <?php
                                       }
                                    }
                                    ?>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="order-page-col col-md-4 col-sm-6 full-w-s">
                           <div class="form-group">
                              <label for="lblOrderAgreement"><?php echo get_lang_text('orderadd_input_comm_amount'); ?></label>
                              <div class="input-group">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">$</span>
                                 </div>
                                 <input type="text" name="order_comm_amount" class="form-control make-numeric order-comm-amount" placeholder="<?php echo get_lang_text('orderadd_input_comm_amount'); ?>" autocomplete="off">
                              </div>
                           </div>

                           <div class="form-group">
                              <label for="lblOrderAgreementText"><?php echo get_lang_text('orderadd_input_contract_text'); ?> *</label>
                              <textarea name="order_agreement_text" id="lblOrderAgreementText" class="form-control order-agreement-text required" placeholder="<?php echo get_lang_text('orderadd_input_contract_text'); ?> *" disabled></textarea>
                              <small><?php echo get_lang_text('orderadd_input_contract_text_desc'); ?></small>
                           </div>
                        </div>

                        <div class="order-page-col col-md-4 col-sm-6 full-w-s">
                           <div class="form-group">
                              <label for="lblOrderTax"><?php echo get_lang_text('orderadd_input_comm_rate'); ?></label>
                              <div class="input-group">
                                 <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">%</span>
                                 </div>
                                 <input type="text" name="order_comm_rate" class="form-control make-numeric order-comm-rate" placeholder="<?php echo get_lang_text('orderadd_input_comm_rate'); ?>" autocomplete="off">
                              </div>
                           </div>

                           <div class="form-group">
                              <label for="lblOrderNotes"><?php echo get_lang_text('orderadd_input_order_notes'); ?></label>
                              <textarea name="order_notes" id="lblOrderNotes" class="form-control" rows="5" placeholder="<?php echo get_lang_text('orderadd_input_order_notes'); ?>"></textarea>
                              <small><?php echo get_lang_text('orderadd_input_order_notes_desc'); ?></small>
                           </div>

                           <div class="form-group show-extra-agreement-row row" style="display: none;">
                              <label for="lblOrderCustomerArc" class="col-sm-12"><?php echo get_lang_text('orderadd_input_contract_extra'); ?></label>
                              <div class="col-sm-6">
                                 <textarea name="order_extra_agreement_tr" class="form-control order-agreement-extra-tr" rows="8" placeholder="<?php echo get_lang_text('orderadd_input_contract_extra'); ?> (TR)" disabled></textarea>
                              </div>
                              <div class="col-sm-6">
                                 <textarea name="order_extra_agreement_en" class="form-control order-agreement-extra-en" rows="8" placeholder="<?php echo get_lang_text('orderadd_input_contract_extra'); ?> (EN)" disabled></textarea>
                              </div>
                           </div>
                        </div>

                        <div class="order-page-col col-md-4 col-sm-6 full-w-s">
                           <div class="form-group">
                              <label for="lblOrderCustomerArc"><?php echo get_lang_text('orderadd_input_customer_has_arc'); ?></label>
                              <select name="order_extra_agreement" id="lblOrderCustomerArc" class="form-control order-agreement-special-arc" disabled>
                                 <option value="0"><?php echo get_lang_text('orderadd_input_customer_has_arc_no'); ?></option>
                                 <option value="1"><?php echo get_lang_text('orderadd_input_customer_has_arc_yes'); ?></option>
                              </select>
                              <small><?php echo get_lang_text('orderadd_input_customer_has_arc_desc'); ?></small>
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
                           </div>
                           <div class="qualification-modal-footer">
                              <button type="button" class="btn btn-secondary" id="closeQualificationModal">Close</button>
                              <button type="button" class="btn btn-secondary" id="qualificationModalBackButton">Back</button>
                              <button type="button" class="btn btn-primary d-none" id="confirmAddQualification" disabled>Next</button>
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
                                       <input type="number" class="form-control" id="maxPrice" placeholder="10000" min="0">
                                    </div>
                                 </div>
                                 <div class="price-slider-container">
                                    <input type="range" class="form-range" id="priceRange" min="0" max="10000" step="100">
                                    <div class="price-labels">
                                       <span>$0</span>
                                       <span>$5,000</span>
                                       <span>$10,000</span>
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
                                       <input type="number" class="form-control" id="itemMaxPrice" placeholder="10000" min="0">
                                    </div>
                                 </div>
                                 <div class="price-slider-container">
                                    <input type="range" class="form-range" id="itemPriceRange" min="0" max="10000" step="100">
                                    <div class="price-labels">
                                       <span>$0</span>
                                       <span>$5,000</span>
                                       <span>$10,000</span>
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
                        <!-- Refresh Button (hidden initially) -->
                        <button
                           type="button"
                           id="refreshBtn"
                           class="btn btn-primary waves-effect waves-light btn-sb-form"
                           style="display: none;"
                           onclick="location.reload();">
                           Refresh
                        </button>
                        <button type="submit" class="btn btn-success waves-effect waves-light btn-sb-form" onclick="post_file_form('add_order_form','dont_refresh'); showRefreshBtn();">
                           <?php echo get_lang_text('orderadd_btn_finish_order'); ?>
                        </button>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php
define('inc_panel_footer', true);
include PATH . '/inc/footer.php';
?>
<script>
   function showRefreshBtn() {
      // Delay showing the Refresh button to allow post_file_form to finish
      setTimeout(function() {
         document.getElementById('refreshBtn').style.display = 'inline-block';
      }, 1500); // Adjust time as needed (in ms)
   }
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

   // Main function to calculate product total - works with your actual functions
   function calculateProductTotal(productId, roomId, productType = 'product') {
      console.log('Calculating product total for:', {
         productId,
         roomId,
         productType
      });

      let total = 0;
      const $productContent = $(`#product-${productId}-room${roomId}`);

      if (!$productContent.length) {
         console.log('Product content not found, might be variant-based product');
         return 0;
      }

      // Check if it's a variant-based product
      const $variantsSection = $productContent.find('.product-variants-section');
      if ($variantsSection.length > 0) {
         // Handle products with variants
         total += calculateVariantBasedProductTotal($productContent, productId, roomId, productType);
      } else {
         // Handle simple products
         total += calculateSimpleProductTotal($productContent, productId, roomId, productType);
      }

      // Update product total display
      updateProductTotalDisplay(productId, roomId, total);

      return total;
   }

   // Calculate simple products
   function calculateSimpleProductTotal($productContent, productId, roomId, productType) {
      let total = 0;

      if (productType === 'fitout') {
         total += calculateFitoutProductTotal($productContent, productId, roomId);
      } else if (productType === 'curtain') {
         total += calculateCurtainProductTotal($productContent, productId, roomId);
      } else {
         total += calculateStandardProductTotal($productContent, productId, roomId);
      }

      return total;
   }

   // Update the product total display
   function updateProductTotalDisplay(productId, roomId, total) {
      const $productTotal = $(`#product-total-${productId}-room${roomId}`);
      if ($productTotal.length) {
         $productTotal.text(total.toFixed(2));
         console.log(`Updated product ${productId} total: $${total.toFixed(2)}`);
      } else {
         console.log(`Product total element not found: #product-total-${productId}-room${roomId}`);
      }

      // Update room totals
      updateRoomTotals(roomId);
   }

   // Calculate variant-based products
   function calculateVariantBasedProductTotal($productContent, productId, roomId, productType) {
      let total = 0;

      // Calculate active variant
      const $activeVariant = $productContent.find('.product-variant-content.active');
      if ($activeVariant.length) {
         total += calculateVariantTotal($activeVariant, productId, roomId);
      }

      return total;
   }

   // Function to calculate standard product total
   function calculateStandardProductTotal($productContent, productId, roomId) {
      let total = 0;

      // Check if product has variants
      const hasVariants = $productContent.find('.product-variants-section').length > 0;

      if (hasVariants) {
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

   // Function to calculate variant total
   function calculateVariantTotal($variantContent, productId, roomId) {
      let variantTotal = 0;

      // Get dimensions and quantity
      const width = parseFloat($variantContent.find('.dimension-width').val()) || 0;
      const length = parseFloat($variantContent.find('.dimension-length').val()) || 0;
      const height = parseFloat($variantContent.find('.dimension-height').val()) || 0;
      const quantity = parseFloat($variantContent.find('.product-qty').val()) || 1;
      const calculateType = $variantContent.find('.calculate-type').val() || '';

      // Get base price (you might need to fetch this from product data)
      const basePrice = (parseFloat($variantContent.find('.unit-price').val()) || 0);
      const productDiscount = (parseFloat($variantContent.find('.product-discount').val()) || 0);
      
      const productPrice = calculatePrice(calculateType, basePrice, width, length, height, quantity);

      // Calculate material costs
      const materialCost = calculateMaterialCosts($variantContent);

      // Calculate variant total (base price × area × quantity + material costs)
      variantTotal = productPrice + materialCost;
      const variantTotalDiscount = variantTotal * productDiscount/100;
      variantTotal -= variantTotalDiscount;

      console.log('Variant total calculation:', {
         width,
         length,
         height,
         quantity,
         basePrice,
         materialCost,
         variantTotal
      });

      return variantTotal;
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
      const totalDiscount = total * productDiscount/100;
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

   // Function to calculate fitout product total
   function calculateFitoutProductTotal($productContent, productId, roomId) {
      let total = 0;

      // Calculate base product cost
      const baseWidth = parseFloat($productContent.find('.dimension-width').val()) || 0;
      const baseLength = parseFloat($productContent.find('.dimension-length').val()) || 0;
      const baseQuantity = parseFloat($productContent.find('.product-qty').val()) || 1;
      const area = baseWidth * baseLength;
      const basePrice = (parseFloat($productContent.find('.unit-price').val()) || 0);
      const productDiscount = (parseFloat($productContent.find('.product-discount').val()) || 0);

      total += basePrice * area * baseQuantity;
      const totalDiscount = total * productDiscount/100;
      total -= totalDiscount;

      // Calculate items cost
      $productContent.find('.items-tab').each(function() {
         const itemId = $(this).data('item-id');
         const $itemContent = $(`#item-${itemId}-${productId}-room${roomId}`);

         if ($itemContent.length) {
            total += calculateItemTotal($itemContent, itemId, productId, roomId);
         }
      });

      return total;
   }

   // Function to calculate curtain product total
   function calculateCurtainProductTotal($productContent, productId, roomId) {
      let total = 0;

      // Calculate base curtain cost
      const quantity = parseFloat($productContent.find('.product-qty').val()) || 1;
      const basePrice = parseFloat($productContent.find('.unit-price').val()) || 0;

      total += (basePrice * quantity);

      // Calculate material costs
      const materialCost = calculateMaterialCosts($productContent);
      total += materialCost;

      // Calculate accessories cost
      $productContent.find('.accessory-tab').each(function() {
         const accessoryId = $(this).data('accessory-id');
         const $accessoryContent = $(`#accessory-${accessoryId}-${productId}-room${roomId}`);

         if ($accessoryContent.length) {
            total += calculateAccessoryTotal($accessoryContent);
         }
      });

      return total;
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

      // Get base price (you might need to fetch this from item data)
      const basePrice = (parseFloat($itemContent.find('.item-unit-price').val()) || 0);
      const itemDiscount = (parseFloat($itemContent.find('.item-discount').val()) || 0);

      const itemPrice = calculatePrice(calculateType, basePrice, width, length, height, quantity);

      // Calculate material costs for the item
      const materialCost = calculateMaterialCosts($itemContent);

      // Calculate item total
      itemTotal = itemPrice + materialCost;
      const itemTotalDiscount = itemTotal * itemDiscount/100;
      itemTotal -= itemTotalDiscount;

      return itemTotal;
   }

   // Function to calculate accessory total
   function calculateAccessoryTotal($accessoryContent) {
      let accessoryTotal = 0;

      // Get selected accessory option and its price
      const $selectedOption = $accessoryContent.find('.accessory-options-select option:selected');
      const accessoryPrice = parseFloat($selectedOption.data('price')) || 0;

      accessoryTotal = accessoryPrice;

      return accessoryTotal;
   }

   // Function to calculate material costs
   function calculateMaterialCosts($contentElement) {
      let materialTotal = 0;

      // Calculate costs from all material inputs
      $contentElement.find('.material-group, .material-inputs-compact').each(function() {
         const $materialGroup = $(this);
         const areaWeight = parseFloat($materialGroup.find('.area-weight').val()) || 0;
         const $materialSelect = $materialGroup.find('.material-type-select option:selected');
         const unitPrice = parseFloat($materialSelect.data('price')) || 0;

         materialTotal += (areaWeight * unitPrice);
      });

      // Calculate pillow material costs
      $contentElement.find('.pillow-material-group, .pillow-material-inputs-compact').each(function() {
         const $pillowGroup = $(this);
         const quantity = parseFloat($pillowGroup.find('.pillow-quantity').val()) || 1;
         const length = parseFloat($pillowGroup.find('.pillow-length').val()) || 0;
         const width = parseFloat($pillowGroup.find('.pillow-width').val()) || 0;
         const $materialSelect = $pillowGroup.find('.material-type-select option:selected');
         const unitPrice = parseFloat($materialSelect.data('price')) || 0;

         // Calculate area for pillows (length × width) and multiply by quantity and unit price
         const pillowArea = (length * width) / 10000; // Convert cm² to m²
         materialTotal += (pillowArea * unitPrice * quantity);
      });

      return materialTotal;
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
         dimensions: { width: cw, length: cl, height: ch },
         quantity: qty,
         calculatedPrice: finalPrice,
         totalPrice
      });
      
      return totalPrice;
   }

   function updateCurtainInstallationPrice($checkbox) {
      const staticPrice = 200;
      const $priceLabel = $checkbox.closest('.curtain-control').find('.installation-price');

      if ($checkbox.is(':checked')) {
         $priceLabel.text(`+ $${staticPrice}`).show();
      } else {
         $priceLabel.hide();
      }
   }
   $(document).on('change', '.curtain-installation-needed-checkbox', function(){
      updateCurtainInstallationPrice($(this));
   })
</script>
<script>
   jQuery(function($) {
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
         currentProductId: null
      };

      // Filter state management
      const filterState = {
         product: {
            selectedBrand: '', // 0 means "All Brands"
            selectedStyles: [],
            minPrice: 0,
            maxPrice: 10000,
            dateSort: 'newest',
            inStock: true,
            outOfStock: false,
            searchTerm: ''
         },
         item: {
            selectedBrand: '', // 0 means "All Brands"
            selectedStyles: [],
            minPrice: 0,
            maxPrice: 10000,
            dateSort: 'newest',
            inStock: true,
            outOfStock: false,
            searchTerm: ''
         }
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

      // Brands Data
      const brands = [{
            id: 1,
            name: '360',
            color: '#0058a3',
         },
         {
            id: 2,
            name: 'Veneer',
            color: '#8b4513',
         },
         {
            id: 3,
            name: 'Her Aksesuar',
            color: '#2c5530',
         },
         {
            id: 4,
            name: 'Marquis Manor',
            color: '#d4af37',
         },
         {
            id: 5,
            name: 'Veneer Plus',
            color: '#2a9d8f',
         },
      ];

      // Styles Data
      const styles = [{
            id: 1,
            name: 'Modern',
            color: '#4361ee',
            description: 'Clean lines and contemporary design'
         },
         {
            id: 2,
            name: 'Contemporary',
            color: '#3a0ca3',
            description: 'Current trends and innovative designs'
         },
         {
            id: 3,
            name: 'Luxury',
            color: '#7209b7',
            description: 'Premium materials and exquisite craftsmanship'
         },
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
      function createVariantsTabs(product, variants, roomId) {
         return `
            <div class="product-variants-section" id="variants-section-${product.product_id}-room${roomId}">
               <div class="product-variants-tabs" id="variants-tabs-${product.product_id}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <button class="product-variant-tab ${index === 0 ? 'active' : ''}" 
                           data-variant="${variant.product_id}" data-product="${product.product_id}" data-type="${product.type}" data-available-in="${product.available_in}" data-room="${roomId}">
                        <div class="product-variant-header">
                           <span class="status-indicator status-empty"></span>
                           <span class="product-variant-title">${variant.product_name}</span>
                        </div>
                     </button>
                  `).join('')}
               </div>
               <div class="product-variants-content" id="variants-content-${product.product_id}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <div class="product-variant-content ${index === 0 ? 'active' : ''}" 
                        id="variant-${product.product_id}-${variant.product_id}-room${roomId}">
                        ${createVariantContentForSet(product, variant, roomId)}
                     </div>
                  `).join('')}
               </div>
            </div>
         `;
      }

      // Function to create material inputs for a category with multiple material groups
      function createMaterialInputsForCategory(product, variant, category, roomId, activeMaterials) {
         const variantId = product.available_in === 'size' ? variant.id : variant.product_id;
         const categoryData = activeMaterials[category];

         console.log('Creating material inputs for category:', {
            category: category,
            categoryData: categoryData,
            variantId: variantId
         });

         // If we have material groups (A, B, C, etc.), create inputs for each group
         if (categoryData.materialGroups && Object.keys(categoryData.materialGroups).length > 0) {
            return Object.entries(categoryData.materialGroups).map(([refLabel, materials]) => {
               console.log('Processing material group:', refLabel, materials);
               return createMaterialGroupInputs(product, variant, category, refLabel, materials, roomId, variantId, categoryData);
            }).join('');
         } else {
            // Fallback to single material input using active materials
            return createSingleMaterialInputs(product, variant, category, roomId, variantId, categoryData);
         }
      }

      // Function to create material inputs for a specific material group (A, B, C, etc.)
      function createMaterialGroupInputs(product, variant, category, refLabel, materials, roomId, variantId, categoryData) {
         console.log('Creating material group inputs:', {
            category: category,
            refLabel: refLabel,
            materials: materials,
            categoryData: categoryData
         });

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

         // Get available materials for this category
         const availableMaterials = categoryData.all_materials || [];

         // Get active material for this refLabel (if any)
         const activeMaterial = materials && materials.length > 0 ? materials[0] : null;
         console.log('activeMaterial Group:', activeMaterial);

         return `
            <div class="material-group" data-ref-label="${refLabel}">
                  <div class="material-group-header">
                     <h6>Material ${refLabel} ${activeMaterial?.alias_name ? `- ${activeMaterial.alias_name}` : ''}</h6>
                  </div>
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
                                    data-variant="${variantId}" data-category="${category}" data-ref-label="${refLabel}">
                                 <option value="">Select Material</option>
                                 ${availableMaterials.map(material => `
                                    <option value="${material.material_id}" 
                                          ${activeMaterial && activeMaterial.material_id == material.material_id ? 'selected' : ''}
                                          data-price="${material.material_price || 0}"
                                          data-image="${material.material_img || ''}">
                                          ${material.material_name}
                                    </option>
                                 `).join('')}
                              </select>
                        </div>
                        <div class="material-input">
                              <label>${measurementLabel}</label>
                              <input type="number" class="form-control area-weight" 
                                    placeholder="Enter ${measurementLabel.toLowerCase()}"
                                    data-variant="${variantId}" data-category="${category}" data-ref-label="${refLabel}"
                                    value="${activeMaterial ? activeMaterial[measurementField] || '' : ''}"
                                    step="0.01" min="0">
                        </div>
                     </div>
                  </div>
            </div>
         `;
      }

      // Function for single material input (fallback)
      function createSingleMaterialInputs(product, variant, category, roomId, variantId, categoryData) {
         console.log('Creating single material inputs:', {
            category: category,
            categoryData: categoryData
         });

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

         // Get available materials
         const availableMaterials = categoryData.all_materials || [];
         const activeMaterials = categoryData.active || [];
         const activeMaterial = activeMaterials.length > 0 ? activeMaterials[0] : null;

         return `
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
                              data-variant="${variantId}" data-category="${category}">
                              <option value="">Select Material</option>
                              ${availableMaterials.map(material => `
                                 <option value="${material.material_id}" 
                                    ${activeMaterial && activeMaterial.material_id == material.material_id ? 'selected' : ''}
                                    data-price="${material.material_price || 0}"
                                    data-image="${material.material_img || ''}">
                                    ${material.material_name}
                                 </option>
                              `).join('')}
                        </select>
                     </div>
                     <div class="material-input">
                        <label>${measurementLabel}</label>
                        <input type="number" class="form-control area-weight" 
                              placeholder="Enter ${measurementLabel.toLowerCase()}"
                              data-variant="${variantId}" data-category="${category}"
                              value="${activeMaterial ? activeMaterial[measurementField] || '' : ''}"
                              step="0.01" min="0">
                     </div>
                  </div>
            </div>
         `;
      }

      // Update your createStandardMaterialContent function
      function createStandardMaterialContent(product, variant, category, roomId, activeMaterials) {
         return createMaterialInputsForCategory(product, variant, category, roomId, activeMaterials);
      }

      // Updated function to create pillow subcategories with material groups
      function createPillowSubcategoriesForVariant(product, variant, roomId, activeMaterials) {
         const variantId = product.available_in === 'size' ? variant.id : variant.product_id;

         // Get pillow data from activeMaterials
         const pillowData = activeMaterials.pillow || {};

         console.log('Pillow data for variant:', {
            variantId: variantId,
            pillowData: pillowData,
            activeMaterials: activeMaterials
         });

         // If no pillow data, return empty
         if (!pillowData || Object.keys(pillowData).length === 0) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No pillow materials available for this product</p>
            </div>
        `;
         }

         // Create subcategories based on the available pillow data
         const subcategories = [];

         // Add default pillow if it exists
         if (pillowData.default_pillow) {
            subcategories.push({
               id: 'default_pillow',
               name: 'Default Pillow',
               type: 'default_pillow'
            });
         }

         // Add other pillow types if they exist
         if (pillowData.pillow_front) {
            subcategories.push({
               id: 'pillow_front',
               name: 'Pillow Front',
               type: 'pillow_front'
            });
         }

         if (pillowData.pillow_back) {
            subcategories.push({
               id: 'pillow_back',
               name: 'Pillow Back',
               type: 'pillow_back'
            });
         }

         if (pillowData.pillow_pipping) {
            subcategories.push({
               id: 'pillow_pipping',
               name: 'Pillow Pipping',
               type: 'pillow_pipping'
            });
         }

         if (subcategories.length === 0) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No pillow subcategories available</p>
            </div>
        `;
         }

         return `
        <div class="pillow-subcategories-section">
            <div class="pillow-subcategories-tabs" id="pillowTabs-${product.product_id}-${variantId}-room${roomId}">
                ${subcategories.map((subcat, index) => `
                    <button class="pillow-subcategory-tab ${index === 0 ? 'active' : ''}" 
                          data-subcategory="${subcat.id}" data-product="${product.product_id}" data-variant="${variantId}">
                        <div class="pillow-subcategory-header">
                            <span class="status-indicator status-empty"></span>
                            <span class="pillow-subcategory-title">${subcat.name}</span>
                        </div>
                    </button>
                `).join('')}
            </div>
            <div class="pillow-subcategories-content" id="pillowContent-${product.product_id}-${variantId}-room${roomId}">
                ${subcategories.map((subcat, index) => `
                    <div class="pillow-subcategory-content ${index === 0 ? 'active' : ''}" 
                       id="pillowSubcategory-${product.product_id}-${variantId}-room${roomId}-${subcat.id}">
                       <div class="pillow-subcategory-details">
                           ${createPillowMaterialContent(product, variant, subcat, pillowData, roomId, variantId)}
                       </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }

      // New function to create pillow material content based on subcategory type
      function createPillowMaterialContent(product, variant, subcat, pillowData, roomId, variantId) {
         const subcatData = pillowData[subcat.id];

         if (!subcatData) {
            return `
               <div class="no-materials-available">
                  <i class="fa fa-info-circle"></i>
                  <p>No materials available for ${subcat.name}</p>
               </div>
            `;
         }

         const activeMaterials = subcatData.active || [];
         const allMaterials = subcatData.all_materials || [];
         const materialGroups = subcatData.materialGroups || {};

         console.log('Creating pillow material content:', {
            subcategory: subcat.id,
            activeMaterials: activeMaterials,
            allMaterials: allMaterials,
            materialGroups: materialGroups
         });

         // If we have material groups, use them
         if (Object.keys(materialGroups).length > 0) {
            return Object.entries(materialGroups).map(([refLabel, materials]) => {
               return createPillowMaterialGroup(product, variant, subcat, refLabel, materials, allMaterials, roomId, variantId);
            }).join('');
         }
         // If we have active materials but no groups, create single input
         else if (activeMaterials.length > 0) {
            return createSinglePillowMaterialInput(product, variant, subcat, activeMaterials[0], allMaterials, roomId, variantId);
         }
         // If no active materials but have available materials, create empty input
         else if (allMaterials.length > 0) {
            return createSinglePillowMaterialInput(product, variant, subcat, null, allMaterials, roomId, variantId);
         }
         // No materials available
         else {
            return `
               <div class="no-materials-available">
                  <i class="fa fa-info-circle"></i>
                  <p>No materials available for ${subcat.name}</p>
               </div>
            `;
         }
      }

      // Function to create a pillow material group
      function createPillowMaterialGroup(product, variant, subcat, refLabel, materials, allMaterials, roomId, variantId) {
         const activeMaterial = materials && materials.length > 0 ? materials[0] : null;

         return `
            <div class="pillow-material-group" data-ref-label="${refLabel}">
                  <div class="pillow-material-group-header">
                     <h6>${subcat.name} - ${refLabel} ${activeMaterial?.alias_name ? `- ${activeMaterial.alias_name}` : ''}</h6>
                  </div>
                  <div class="pillow-material-inputs-compact">
                     <div class="pillow-material-compact-image">
                        ${activeMaterial?.material_img ? 
                              `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` : 
                              `<i class="fa fa-image"></i>`
                        }
                     </div>
                     <div class="pillow-material-compact-fields">
                        <div class="pillow-material-input">
                              <label>Material</label>
                              <select class="form-control material-type-select" 
                                    data-subcategory="${subcat.id}" data-variant="${variantId}" data-ref-label="${refLabel}">
                                 <option value="">Select Material</option>
                                 ${allMaterials.map(material => `
                                    <option value="${material.material_id}" 
                                          ${activeMaterial && activeMaterial.material_id == material.material_id ? 'selected' : ''}
                                          data-price="${material.material_price || 0}"
                                          data-image="${material.material_img || ''}">
                                          ${material.material_name}
                                    </option>
                                 `).join('')}
                              </select>
                        </div>
                        
                        ${subcat.id === 'default_pillow' ? `
                              <div class="pillow-material-input">
                                 <label>Quantity</label>
                                 <input type="number" class="form-control pillow-quantity" 
                                       placeholder="1" min="1" value="${activeMaterial?.quantity || 1}"
                                       data-subcategory="${subcat.id}" data-variant="${variantId}" data-ref-label="${refLabel}">
                              </div>
                              <div class="pillow-material-input">
                                 <label>Dimensions (cm)</label>
                                 <div class="pillow-dimensions d-flex align-items-center">
                                    <input type="number" class="form-control pillow-length" 
                                          placeholder="Length" step="0.1" min="0"
                                          value="${activeMaterial?.length_cm || activeMaterial?.dimensions?.length || ''}"
                                          data-subcategory="${subcat.id}" data-variant="${variantId}" data-ref-label="${refLabel}">
                                    <span class="dimension-separator">×</span>
                                    <input type="number" class="form-control pillow-width" 
                                          placeholder="Width" step="0.1" min="0"
                                          value="${activeMaterial?.width_cm || activeMaterial?.dimensions?.width || ''}"
                                          data-subcategory="${subcat.id}" data-variant="${variantId}" data-ref-label="${refLabel}">
                                 </div>
                              </div>
                        ` : ''}
                     </div>
                  </div>
            </div>
         `;
      }

      // Function for single pillow material input
      function createSinglePillowMaterialInput(product, variant, subcat, activeMaterial, allMaterials, roomId, variantId) {
         return `
            <div class="pillow-material-inputs-compact">
                  <div class="pillow-material-compact-image">
                     ${activeMaterial?.material_img ? 
                        `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` : 
                        `<i class="fa fa-image"></i>`
                     }
                  </div>
                  <div class="pillow-material-compact-fields">
                     <div class="pillow-material-input">
                        <label>Material</label>
                        <select class="form-control material-type-select" 
                              data-subcategory="${subcat.id}" data-variant="${variantId}">
                              <option value="">Select Material</option>
                              ${allMaterials.map(material => `
                                 <option value="${material.material_id}" 
                                    ${activeMaterial && activeMaterial.material_id == material.material_id ? 'selected' : ''}
                                    data-price="${material.material_price || 0}"
                                    data-image="${material.material_img || ''}">
                                    ${material.material_name}
                                 </option>
                              `).join('')}
                        </select>
                     </div>
                     
                     ${subcat.id === 'default_pillow' ? `
                        <div class="pillow-material-input">
                              <label>Quantity</label>
                              <input type="number" class="form-control pillow-quantity" 
                                    placeholder="1" min="1" value="${activeMaterial?.quantity || 1}"
                                    data-subcategory="${subcat.id}" data-variant="${variantId}">
                        </div>
                        <div class="pillow-material-input">
                              <label>Dimensions (cm)</label>
                              <div class="pillow-dimensions d-flex align-items-center">
                                 <input type="number" class="form-control pillow-length" 
                                       placeholder="Length" step="0.1" min="0"
                                       value="${activeMaterial?.length_cm || activeMaterial?.dimensions?.length || ''}"
                                       data-subcategory="${subcat.id}" data-variant="${variantId}">
                                 <span class="dimension-separator">×</span>
                                 <input type="number" class="form-control pillow-width" 
                                       placeholder="Width" step="0.1" min="0"
                                       value="${activeMaterial?.width_cm || activeMaterial?.dimensions?.width || ''}"
                                       data-subcategory="${subcat.id}" data-variant="${variantId}">
                              </div>
                        </div>
                     ` : ''}
                  </div>
            </div>
         `;
      }

      // Function to create curtain variant content with additional options
      function createCurtainVariantContent(product, variant, roomId) {
         const dims = variant.dims || {}; // fallback if undefined

         const width = dims.width || '';
         const length = dims.length || '';
         const height = dims.height || '';
         const standart_price = dims.standart_price || '';
         const unit_price = calculateUnitPrice(variant.calculate_type, dims);

         return `
            <div class="variant-details">
               <div class="compact-product-details">
                  <div class="compact-section-header">
                     <h6><i class="fa fa-cube mr-2"></i>${variant.product_name} - ${variant.product_desc}</h6>
                  </div>
                  <div class="compact-details-with-image">
                     <div class="compact-image-preview">
                        <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                     </div>
                     <div class="compact-details-fields">
                        <div class="compact-detail-group" style="width:200px;">
                           <label>Quantity</label>
                           <input type="number" class="form-control product-qty" 
                                 placeholder="0" step="1" min="1" value="1"
                                 data-variant="${variant.product_id}">
                        </div>
                        <div class="compact-detail-group" style="width:200px;">
                           <label>Discount(%)</label>
                           <input type="number" class="form-control product-discount" 
                                 placeholder="0" step="0.01" min="0" value="0"
                                 data-variant="${variant.product_id}">
                        </div>
                     </div>
                  </div>
               <!-- Add base price field for variant -->
               <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${variant.product_id}">
               <input type="hidden" class="calculate-type" value="${variant.calculate_type}" data-variant="${variant.product_id}">
               
               <!-- Material Section for Curtain Variant - Using existing reusable function -->
               ${createMaterialSection(product, variant, roomId)}
               
               <!-- CURTAIN OPTIONS SECTION - Fixed accessories layout -->
               <div class="curtain-options-section">
                  <h6><i class="fa fa-cog mr-2"></i>Curtain Options</h6>
                  <div class="curtain-controls">
                     <div class="curtain-control">
                        <label>Opening Direction</label>
                        <select class="form-control opening-direction" data-variant="${variant.product_id}">
                           <option value="">Select Direction</option>
                           <option value="left">Left Opening</option>
                           <option value="right">Right Opening</option>
                           <option value="center">Center Opening</option>
                           <option value="top">Top Opening</option>
                        </select>
                     </div>
                     <div class="curtain-control">
                        <label>Open With</label>
                        <select class="form-control open-with" data-variant="${variant.product_id}">
                           <option value="">Select Option</option>
                           <option value="cord">Cord</option>
                           <option value="wand">Wand</option>
                           <option value="motorized">Motorized</option>
                           <option value="manual">Manual</option>
                        </select>
                     </div>
                     <div class="curtain-control p-4">
                        <input type="checkbox" class="custom-control-input curtain-installation-needed-checkbox" id="curtain-installation${product.product_id}${variant.product_id}${roomId}" value="needed">
                        <label class="custom-control-label font-weight-bold" for="curtain-installation${product.product_id}${variant.product_id}${roomId}">
                           Installation Needed
                           <i class="fa fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="Charge will be shown based on selected county"></i>
                           <span class="installation-price text-success ml-2 font-weight-bold" style="display:none;"></span>
                        </label>
                     </div>
                     <div class="curtain-control">
                        <label>Control Side</label>
                        <select class="form-control control-side" data-variant="${variant.product_id}">
                           <option value="">Select Side</option>
                           <option value="left">Left Side</option>
                           <option value="right">Right Side</option>
                           <option value="both">Both Sides</option>
                        </select>
                     </div>
                  </div>
                  
                  <!-- Accessory Selection for Curtains - Using the same layout as before -->
                  <div class="curtain-accessories-section">
                     <h6 style="margin-top: 16px;"><i class="fa fa-plus-circle mr-2"></i>Additional Accessories</h6>
                     <div class="accessory-layout">
                        <div class="accessory-tabs-sidebar">
                           <div class="accessory-tabs-header">
                              <h6><i class="fa fa-list mr-2"></i>Accessories</h6>
                              <button type="button" class="btn btn-sm btn-primary add-accessory-btn" data-product="${product.product_id}" data-attr-id="${variant.attr_id}" data-type="${product.type}" data-available-in="${product.available_in}" data-variant="${variant.product_id}" data-room="${roomId}">
                                 <i class="fa fa-plus mr-1"></i> Add
                              </button>
                           </div>
                           <div class="accessory-tabs-container" id="accessory-tabs-${product.product_id}-${variant.product_id}-room${roomId}">
                              <div class="empty-accessory-tabs">
                                 <i class="fa fa-puzzle-piece"></i>
                                 <p>No accessories added yet</p>
                              </div>
                           </div>
                        </div>
                        <div class="accessory-details-content" id="accessory-details-${product.product_id}-${variant.product_id}-room${roomId}">
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
               </div>
            </div>
         `;
      }

      // Modified function to setup curtain options
      function setupCurtainOptions(product, roomId) {
         const productId = product.parent_id;
         $.post(ajax_url + '/api', {
            get_product_variants: 1,
            product_id: productId
         }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            console.log('selectedProductVariants:', response);
            if (response.status === 'success') {
               const variants = response.data;
               variants.forEach(variant => {
                  // Setup curtain option change handlers
                  $(`#variant-${productId}-${variant.product_id}-room${roomId} .opening-direction`).on('change', function() {
                     updateVariantStatus(productId, roomId, variant.product_id, variant.available_in, product.type);
                  });

                  $(`#variant-${productId}-${variant.product_id}-room${roomId} .open-with`).on('change', function() {
                     updateVariantStatus(productId, roomId, variant.product_id, variant.available_in, product.type);
                  });

                  $(`#variant-${productId}-${variant.product_id}-room${roomId} .control-side`).on('change', function() {
                     updateVariantStatus(productId, roomId, variant.product_id, variant.available_in, product.type);
                  });

               });
            }
         });
      }

      // Updated function to create variant content for sets
      function createVariantContentForSet(product, variant, roomId) {
         // Check if this is a curtain product
         const isCurtainProduct = product.type === 'curtain';

         if (isCurtainProduct) {
            return createCurtainVariantContent(product, variant, roomId);
         } else {
            const dims = variant.dims || {}; // fallback if undefined

            const width = dims.width || '';
            const length = dims.length || '';
            const height = dims.height || '';
            const standart_price = dims.standart_price || '';
            const unit_price = calculateUnitPrice(variant.calculate_type, dims);

            return `
               <div class="variant-details">
                  <div class="compact-product-details">
                     <div class="compact-section-header">
                           <h6><i class="fa fa-cube mr-2"></i>${variant.product_name} - ${variant.product_desc}</h6>
                     </div>
                     <div class="compact-details-with-image">
                           <div class="compact-image-preview">
                              <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                           </div>
                           <div class="compact-details-fields">
                              <div class="compact-detail-group">
                                 <label>Width (m)</label>
                                 <input type="number" class="form-control dimension-width" 
                                       value="${width}" placeholder="0.00" step="0.01" min="0" 
                                       data-variant="${variant.product_id}" ${getDisabledAttr(variant.calculate_type, 'w')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Length (m)</label>
                                 <input type="number" class="form-control dimension-length" 
                                       value="${length}" placeholder="0.00" step="0.01" min="0"
                                       data-variant="${variant.product_id}" ${getDisabledAttr(variant.calculate_type, 'l')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Height (m)</label>
                                 <input type="number" class="form-control dimension-height" 
                                       value="${height}" placeholder="0.00" step="0.01" min="0"
                                       data-variant="${variant.product_id}" ${getDisabledAttr(variant.calculate_type, 'h')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Quantity</label>
                                 <input type="number" class="form-control product-qty" 
                                       placeholder="0" step="1" min="1" value="1"
                                       data-variant="${variant.product_id}">
                              </div>
                              <div class="compact-detail-group">
                                 <label>Discount(%)</label>
                                 <input type="number" class="form-control product-discount" 
                                       placeholder="0" step="0.01" min="0" value="0"
                                       data-variant="${variant.product_id}">
                              </div>
                           </div>
                     </div>
                  <!-- Add base price field for variant -->
                  <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${variant.product_id}">
                  <input type="hidden" class="calculate-type" value="${variant.calculate_type}" data-variant="${variant.product_id}">
                  
                  <!-- Material Section - Use existing data -->
                  ${createMaterialSection(product, variant, roomId)}
                  </div>
               </div>
            `;
         }
      }

      // Updated function to create variant content for size variants
      function createVariantContentForSize(product, variant, roomId) {
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
                                       placeholder="0.00" step="0.01" min="0" value="${variant.width}" 
                                       data-variant="${variant.id}" ${getDisabledAttr(product.calculate_type, 'w')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Length (m)</label>
                                 <input type="number" class="form-control dimension-length" 
                                       placeholder="0.00" step="0.01" min="0" value="${variant.length}" 
                                       data-variant="${variant.id}" ${getDisabledAttr(product.calculate_type, 'l')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Height (m)</label>
                                 <input type="number" class="form-control dimension-height" 
                                       placeholder="0.00" step="0.01" min="0" value="${variant.height}"
                                       data-variant="${variant.id}" ${getDisabledAttr(product.calculate_type, 'h')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Quantity</label>
                                 <input type="number" class="form-control product-qty" 
                                       placeholder="0" step="1" min="1" value="1"
                                       data-variant="${variant.id}">
                              </div>
                              <div class="compact-detail-group" style="width:200px;">
                                 <label>Discount(%)</label>
                                 <input type="number" class="form-control product-discount" 
                                       placeholder="0" step="0.01" min="0" value="0"
                                       data-variant="${variant.id}">
                              </div>
                        </div>
                     </div>
                  <!-- Add base price field for size variant -->
                  <input type="hidden" class="unit-price" value="${variant.standart_price || 0}" data-variant="${variant.id}">
                  <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${variant.id}">
                  
                  <!-- Material Section - Use existing data -->
                  ${createMaterialSection(product, variant, roomId)}
                  </div>
            </div>
         `;
      }

      // Function to setup variants tabs functionality
      function setupVariantsTabs(product, variants, roomId) {
         const productId = product.product_id;
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

         // Setup material tabs for variants
         setupVariantMaterialTabs(product, variants, roomId);

         // Setup pillow subcategory tabs for variants
         setupVariantPillowSubcategoryTabs(product, variants, roomId);

         // Setup curtain options if this is a curtain product
         if (product.type === 'curtain') {
            setupCurtainOptions(product, roomId);
         }

         // Activate the first tab by default
         const $firstTab = $variantsTabs.find('.product-variant-tab').first();
         if ($firstTab.length) {
            $firstTab.trigger('click');
         }
      }

      // In setupVariantPillowSubcategoryTabs, add the handler setup:
      function setupVariantPillowSubcategoryTabs(product, variants, roomId) {
         const productId = product.product_id;
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

      // Updated function to setup material tabs for variants
      function setupVariantMaterialTabs(product, variants, roomId) {
         const productId = product.product_id;
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

      // Updated function to setup material tabs for product
      function setupMaterialTabsForProduct(product, variant, roomId) {
         const materialTabsId = `materialTabs-${product.product_id}-${variant.product_id}-room${roomId}`;

         setTimeout(() => {
            const $materialTabs = $(`#${materialTabsId}`);

            if ($materialTabs.length) {
               console.log('Setting up material tabs for product:', materialTabsId);

               // Remove existing handlers
               $materialTabs.off('click', '.material-tab');

               // Add new handler
               $materialTabs.on('click', '.material-tab', function(e) {
                  e.preventDefault();
                  const categoryId = $(this).data('category');
                  const materialTabsContentId = `materialTabsContent-${product.product_id}-${variant.product_id}-room${roomId}`;

                  console.log('Product material tab clicked:', categoryId);

                  // Deactivate all tabs and content
                  $(`#${materialTabsId} .material-tab`).removeClass('active');
                  $(`#${materialTabsContentId} .material-tab-content`).removeClass('active');

                  // Activate current tab and content
                  $(this).addClass('active');
                  $(`#materialContent-${product.product_id}-${variant.product_id}-room${roomId}-${categoryId}`).addClass('active');
               });
            }
         }, 100);
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

               if (!materialType || !areaWeight) {
                  allGroupsComplete = false;
               }
            });

            // If no groups, check single inputs
            if (!hasGroups) {
               const materialType = $materialContent.find('.material-type-select').val();
               const areaWeight = $materialContent.find('.area-weight').val();
               materialComplete = !!(materialType && areaWeight);
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
            const controlSide = $content.find('.control-side').val();

            curtainOptionsComplete = !!(openingDirection && openWith && controlSide);
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

                     const $option = $(`
                        <div class="qualification-option" data-category="${category.id}" data-attr-names="${category.attr_names}" data-product-names="${category.product_names}">
                            <div class="qualification-select-option-image">
                                <img src="<?= URL ?>/uploads/${category.image}" alt="${category.category}" onerror="this.src='https://picsum.photos/200/200?random=${category.category}'">
                            </div>
                            <div class="qualification-option-name">${category.category}</div>
                        </div>
                    `);

                     // Create individual tooltip for each option
                     const $tooltip = $(`
                        <div class="qualification-tooltip">
                            <strong>${category.category}</strong><br>
                            ${category.web_category_title || category.description || 'No description available'}
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
         // state.selectedMainCategory = null;
      }

      // Filter qualifications by main category
      function getQualificationsByMainCategory(mainCategoryId) {
         return qualifications.filter(qual => qual.main_category_id === mainCategoryId);
      }

      // Update Qualification Modal to show filtered qualifications
      function showQualificationModalFiltered(mainCategoryId, roomId) {
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
                  const $option = $(`
                     <div class="qualification-option" data-qualification="${qual.attr_ids}" data-product-names="${qual.product_names}">
                        <div class="qualification-select-option-image">
                           <img src="<?= URL ?>/uploads/online-img/${qual.online_product_img}" alt="${qual.attr_name}" onerror="this.src='https://picsum.photos/200/200?random=fallback${qual.attr_name}'">
                        </div>
                        <div class="qualification-option-name">${qual.attr_name}</div>
                     </div>
                  `);

                  // Create individual tooltip for each option
                  const $tooltip = $(`
                     <div class="qualification-tooltip">
                     <strong>${qual.attr_name || ''}</strong><br>
                     ${qual.attr_web_title}
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
                     $('#confirmAddQualification').prop('disabled', false);
                  });

                  $optionsContainer.append($option);
               });
            }
         });

         state.currentRoom = roomId;
         state.selectedQualification = null;
         $('#qualificationModal').fadeIn(300);
         $('#qualificationOptions .qualification-option').removeClass('selected');
         $('#confirmAddQualification').prop('disabled', true);
         $('#qualificationSearch').val('');
         $('.qualification-option').show();
      }

      // Search functionality for main categories
      function filterMainCategoryOptions(searchTerm) {
         const $options = $('.qualification-option', '#mainCategoryOptions');
         $options.hide();

         $options.each(function() {
            const $option = $(this);
            let attr_names = $(this).data('attr-names') ? $(this).data('attr-names').toLowerCase() : '';
            let product_names = $(this).data('product-names') ? $(this).data('product-names').toLowerCase() : '';
            const name = $option.find('.qualification-option-name').text().toLowerCase();

            if (name.includes(searchTerm.toLowerCase()) || attr_names.includes(searchTerm.toLowerCase()) || product_names.includes(searchTerm.toLowerCase())) {
               $option.show();
            }
         });
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
      function filterQualificationOptions(searchTerm) {
         const $options = $('.qualification-option');
         $options.hide();

         $options.each(function() {
            const $option = $(this);
            let product_names = $(this).data('product-names') ? $(this).data('product-names').toLowerCase() : '';
            const name = $option.find('.qualification-option-name').text().toLowerCase();

            if (name.includes(searchTerm.toLowerCase()) || product_names.includes(searchTerm.toLowerCase())) {
               $option.show();
            }
         });
      }

      $('#qualificationSearch').on('input', function() {
         filterQualificationOptions($(this).val());
      });

      // Only hide tooltips when modal closes, don't remove them from DOM
      $('#closeQualificationModal').on('click', function() {
         $('.qualification-tooltip').removeClass('visible'); // Just hide, don't remove
      });

      // Only hide tooltips when confirming, don't remove them from DOM
      $('#confirmAddQualification').on('click', function() {
         $('.qualification-tooltip').removeClass('visible'); // Just hide, don't remove
      });

      // Initialize multi-select modal
      function initializeProductSelectModal(qualifications, roomId) {
         const $optionsContainer = $('#multiSelectOptions');
         $optionsContainer.empty();
         state.selectedProducts = [];

         // Initialize brand radio tabs
         initializeBrandRadioTabs();

         // Initialize style checkbox tabs
         initializeStyleCheckboxTabs();

         // Apply initial filters
         applyProductFilters(qualifications);
      }

      // Initialize Brand Radio Tabs
      function initializeBrandRadioTabs() {
         const $brandTabs = $('#brandRadioTabs');
         $brandTabs.empty();

         // Add "All Brands" option
         const $allBrandsOption = $(`
            <div class="brand-radio-option">
               <input type="radio" id="brand-all" name="product-brand-selection" value="" class="brand-radio-input" checked>
               <label for="brand-all" class="brand-radio-label">
               <span class="brand-radio-name">All Brands</span>
               </label>
            </div>
         `);
         $brandTabs.append($allBrandsOption);

         $.post(ajax_url + '/api', {
            get_brands: 1,
         }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            console.log(response);
            if (response.status === 'success') {
               let brandss = response.data;
               brandss.forEach(brand => {
                  const $brandOption = $(`
               <div class="brand-radio-option">
                  <input type="radio" id="item-brand-${brand.catalog_id}" name="product-brand-selection" value="${brand.catalog_id}" class="brand-radio-input">
                  <label for="item-brand-${brand.catalog_id}" class="brand-radio-label">
                     <span class="brand-radio-name">${brand.catalog_name}</span>
                  </label>
               </div>
                  `);
                  $brandTabs.append($brandOption);
               });
            }
         });
      }



      // Initialize Style Checkbox Tabs
      function initializeStyleCheckboxTabs() {
         const $styleTabs = $('#styleCheckboxTabs');
         $styleTabs.empty();

         $.post(ajax_url + '/api', {
            get_product_styles: 1,
         }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            console.log(response);
            if (response.status === 'success') {
               let styles = response.data;

               styles.forEach(style => {
                  const $styleTab = $(`
                     <div class="style-checkbox-tab">
                     <div class="style-checkbox"></div>
                     <span class="style-checkbox-name">${style.type}</span>
                     </div>
                  `);

                  $styleTab.on('click', function() {
                     $(this).toggleClass('selected');

                     const styleId = style.id;
                     if ($(this).hasClass('selected')) {
                        filterState.product.selectedStyles.push(styleId);
                     } else {
                        filterState.product.selectedStyles = filterState.product.selectedStyles.filter(id => id !== styleId);
                     }

                     const qualifications = $('#productSelectModal').data('qualification');
                     applyProductFilters(qualifications);
                  });

                  $styleTabs.append($styleTab);
               });
            }
         });
      }

      // Apply Product Filters
      function applyProductFilters(qualificationIds) {
         const $optionsContainer = $('#multiSelectOptions');
         $optionsContainer.empty();
         // Show loading state
         $optionsContainer.html(`
            <div class="loading-state">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
                  <p>Loading products...</p>
            </div>
         `);
         let search_text = '';
         if (filterState.product.searchTerm) {
            search_text = filterState.product.searchTerm;
         }

         $.post(ajax_url + '/api', {
            get_products: 1,
            qualification_ids: qualificationIds,
            search_text: search_text
         }, function(data) {
            $optionsContainer.empty();
            // console.log(data);
            var response = $.parseJSON(data);
            console.log(response);
            if (response.status === 'success') {
               let filteredProducts = response.data;

               // Apply brand filter
               if (filterState.product.selectedBrand !== '') {
                  filteredProducts = filteredProducts.filter(product =>
                     product.catalog_id === filterState.product.selectedBrand
                  );
               }

               // Apply style filter
               if (filterState.product.selectedStyles.length > 0) {
                  filteredProducts = filteredProducts.filter(product =>
                     filterState.product.selectedStyles.includes(product.product_style_type)
                  );
               }

               // Apply price filter
               filteredProducts = filteredProducts.filter(product =>
                  product.standart_price >= filterState.product.minPrice &&
                  product.standart_price <= filterState.product.maxPrice
               );

               // Apply stock filter
               // if (!filterState.product.outOfStock) {
               //    filteredProducts = filteredProducts.filter(product => product.in_stock);
               // }

               // if (!filterState.product.inStock) {
               //    filteredProducts = filteredProducts.filter(product => !product.in_stock);
               // }

               // Apply search filter
               if (filterState.product.searchTerm) {
                  const searchTerm = filterState.product.searchTerm.toLowerCase();
                  filteredProducts = filteredProducts.filter(product =>
                     product.product_code.toLowerCase().includes(searchTerm) ||
                     product.product_name.toLowerCase().includes(searchTerm)
                  );
               }

               // Apply date sort
               filteredProducts.sort((a, b) => {
                  const dateA = new Date(a.product_add_date);
                  const dateB = new Date(b.product_add_date);
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
                  const brand = brands.find(b => b.id === product.catalog_id);
                  const style = styles.find(s => s.id === product.product_style_type);

                  const $option = $(`
                     <div class="multi-select-option" data-product-id="${product.product_id}" data-attr-id="${product.attr_id}">
                           <small class="brand-tag" style="background: ${brand?.color || '#6c757d'}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem;position:absolute;top:0;right:0;">
                                 ${product.catalog_name || 'Unknown'}
                           </small>
                           <div class="multi-select-option-header">
                              <div class="multi-select-option-image">
                                 <img src="${product.product_img}" alt="${product.product_name}">
                              </div>
                           </div>
                           <div class="multi-select-option-meta text-center">
                              <small class="name-tag px-1" style="font-weight: 600;">
                                 ${product.product_name}
                              </small>
                              <small class="price-tag px-1 d-none" style="color: #4361ee; font-weight: 600;">
                                 $${Number(product.standart_price || 0).toFixed(2)}
                              </small>
                           </div>
                     </div>
                  `);
                  $optionsContainer.append($option);
               });
            }
         });
      }

      // Initialize Advanced Filter Modal
      function initializeAdvancedFilterModal() {

         // Brand selection handler for product brand
         $(document).on('change', '.brand-radio-input[name="product-brand-selection"]', function() {
            filterState.product.selectedBrand = $(this).val();
            const qualifications = $('#productSelectModal').data('qualification');
            console.log('Selected product brand filter:', $(this).val(), 'Selected qualifications:', qualifications);
            applyProductFilters(qualifications);
         });
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
            filterState.product.maxPrice = parseInt($(this).val()) || 10000;
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

      // Reset Advanced Filters
      function resetAdvancedFilters() {
         // Reset form values
         $('#minPrice').val(0);
         $('#maxPrice').val(10000);
         $('#priceRange').val(10000);
         $('#newestFirst').prop('checked', true);
         $('#inStock').prop('checked', true);
         $('#outOfStock').prop('checked', false);

         // Reset filter state
         filterState.product.minPrice = 0;
         filterState.product.maxPrice = 10000;
         filterState.product.dateSort = 'newest';
         filterState.product.inStock = true;
         filterState.product.outOfStock = false;
      }

      // Apply Advanced Filters
      function applyAdvancedFilters() {
         const qualifications = $('#productSelectModal').data('qualification');
         applyProductFilters(qualifications);
      }

      // Show/Hide Advanced Filter Modal
      function showAdvancedFilterModal() {
         $('#advancedFilterModal').fadeIn(300);
      }

      function hideAdvancedFilterModal() {
         $('#advancedFilterModal').fadeOut(300);
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
            const productId = $('#itemSelectionModal').data('current-product');
            applyItemFilters();
         });
      }

      // Updated image upload functionality with larger image sizes
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
      }

      // Get next room number
      function getNextRoomNumber() {
         if (state.rooms.length === 0) return 1;
         return Math.max(...state.rooms) + 1;
      }

      // Add room to state
      function addRoomToState(roomNumber) {
         state.rooms.push(roomNumber);
         state.rooms.sort((a, b) => a - b);
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
         // state.selectedQualification = null;
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
         $('#productSelectModal').removeData('qualification');
         $('#productSelectModal').removeData('roomId');
      }

      function showItemSelectionModal(productId) {
         console.log('Opening item selection modal for product:', productId);
         state.currentProductId = productId;

         // Get the current room context
         const $activeProductTab = $('.product-tab.active');
         if ($activeProductTab.length) {
            const roomId = $activeProductTab.closest('.tab-pane').data('room');
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

         // Initialize filters for items
         initializeItemBrandRadioTabs();
         initializeItemStyleCheckboxTabs();

         // Apply initial filters
         applyItemFilters();

         $modal.fadeIn(300);
         $('#confirmSelectItem').prop('disabled', true);
      }

      // Initialize Brand Radio Tabs for Items
      function initializeItemBrandRadioTabs() {
         const $brandTabs = $('#itemBrandRadioTabs');
         $brandTabs.empty();

         // Add "All Brands" option
         const $allBrandsOption = $(`
        <div class="brand-radio-option">
            <input type="radio" id="item-brand-all" name="item-brand-selection" value="" class="brand-radio-input" checked>
            <label for="item-brand-all" class="brand-radio-label">
                <span class="brand-radio-name">All Brands</span>
            </label>
        </div>
    `);
         $brandTabs.append($allBrandsOption);

         $.post(ajax_url + '/api', {
            get_brands: 1,
         }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            console.log(response);
            if (response.status === 'success') {
               let brandss = response.data;
               brandss.forEach(brand => {
                  const $brandOption = $(`
               <div class="brand-radio-option">
                  <input type="radio" id="item-brand-${brand.catalog_id}" name="item-brand-selection" value="${brand.catalog_id}" class="brand-radio-input">
                  <label for="item-brand-${brand.catalog_id}" class="brand-radio-label">
                     <span class="brand-radio-name">${brand.catalog_name}</span>
                  </label>
               </div>
            `);
                  $brandTabs.append($brandOption);
               });
            }
         });
      }

      // Initialize Style Checkbox Tabs for Items
      function initializeItemStyleCheckboxTabs() {
         const $styleTabs = $('#itemStyleCheckboxTabs');
         $styleTabs.empty();

         $.post(ajax_url + '/api', {
            get_product_styles: 1,
         }, function(data) {
            // console.log(data);
            var response = $.parseJSON(data);
            console.log(response);
            if (response.status === 'success') {
               let styles = response.data;

               styles.forEach(style => {
                  const $styleTab = $(`
                     <div class="style-checkbox-tab">
                     <div class="style-checkbox"></div>
                     <span class="style-checkbox-name">${style.type}</span>
                     </div>
                  `);

                  $styleTab.on('click', function() {
                     $(this).toggleClass('selected');

                     const styleId = style.id;
                     if ($(this).hasClass('selected')) {
                        filterState.product.selectedStyles.push(styleId);
                     } else {
                        filterState.product.selectedStyles = filterState.product.selectedStyles.filter(id => id !== styleId);
                     }

                     const qualifications = $('#productSelectModal').data('qualification');
                     applyProductFilters(qualifications);
                  });

                  $styleTabs.append($styleTab);
               });
            }
         });
      }

      // Apply Item Filters
      function applyItemFilters() {
         const $optionsContainer = $('#itemMultiSelectOptions');
         $optionsContainer.empty();

         // Show loading state
         $optionsContainer.html(`
            <div class="loading-state">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
                  <p>Loading items...</p>
            </div>
         `);

         let search_text = '';
         if (filterState.item.searchTerm) {
            search_text = filterState.item.searchTerm;
         }

         $.post(ajax_url + '/api', {
            get_products: 1,
            is_fitout_items: 1,
            search_text: search_text
         }, function(data) {
            $optionsContainer.empty();
            var response = $.parseJSON(data);
            console.log('Items response:', response);

            if (response.status === 'success') {
               let filteredItems = response.data;

               // Apply brand filter
               if (filterState.item.selectedBrand !== '') {
                  filteredItems = filteredItems.filter(item =>
                     item.catalog_id === filterState.item.selectedBrand
                  );
               }

               // Apply style filter
               if (filterState.item.selectedStyles.length > 0) {
                  filteredItems = filteredItems.filter(item =>
                     filterState.item.selectedStyles.includes(item.product_style_type)
                  );
               }

               // Apply price filter
               filteredItems = filteredItems.filter(item =>
                  item.standart_price >= filterState.item.minPrice &&
                  item.standart_price <= filterState.item.maxPrice
               );

               // Apply stock filter
               // if (!filterState.item.outOfStock) {
               //     filteredItems = filteredItems.filter(item => item.in_stock);
               // }
               // if (!filterState.item.inStock) {
               //     filteredItems = filteredItems.filter(item => !item.in_stock);
               // }

               // Apply search filter
               if (filterState.item.searchTerm) {
                  const searchTerm = filterState.item.searchTerm.toLowerCase();
                  filteredItems = filteredItems.filter(item =>
                     item.product_code.toLowerCase().includes(searchTerm) ||
                     item.product_name.toLowerCase().includes(searchTerm)
                  );
               }

               // Apply date sort
               filteredItems.sort((a, b) => {
                  const dateA = new Date(a.product_add_date);
                  const dateB = new Date(b.product_add_date);
                  return filterState.item.dateSort === 'newest' ?
                     dateB - dateA : dateA - dateB;
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
                  const brand = brands.find(b => b.id === item.catalog_id);
                  const style = styles.find(s => s.id === item.product_style_type);

                  const $option = $(`
                     <div class="multi-select-option" data-item-id="${item.product_id}" data-attr-id="${item.attr_id}">
                           <small class="brand-tag" style="background: ${brand?.color || '#6c757d'}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem;position:absolute;top:0;right:0;">
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
               // Handle API error
               $optionsContainer.html(`
                  <div class="no-items-message" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d;">
                     <i class="fa fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.5;"></i>
                     <p>Error loading items: ${response.message || 'Unknown error'}</p>
                  </div>
               `);
            }
         }).fail(function(xhr, status, error) {
            // Handle AJAX failure
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
         state.currentRoomId = roomId;

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
      function addAccessoryToCurtainVariant(roomId, productId, variantId, accessory, accessoryOptions) {
         console.log('Adding accessory to curtain variant:', {
            roomId,
            productId,
            variantId,
            accessory,
            accessoryOptions
         });

         const $variantContent = $(`#variant-${productId}-${variantId}-room${roomId}`);
         const $tabsContainer = $variantContent.find(`#accessory-tabs-${productId}-${variantId}-room${roomId}`);
         const $emptyState = $tabsContainer.find('.empty-accessory-tabs');
         const $detailsBody = $variantContent.find(`#accessory-details-${productId}-${variantId}-room${roomId} .accessory-details-body`);
         const $emptyDetails = $detailsBody.find('.empty-accessory-selection');

         if ($emptyState.length) {
            $emptyState.remove();
         }

         const existingTab = $tabsContainer.find(`[data-accessory-id="${accessory.id}"]`);
         if (existingTab.length) {
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
            <option value="${color}">${color}</option>
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
                                    <option value="${option.accessory_id}" data-image="${option.accessory_img}">${option.accessory_name}</option>
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
            }
         });

         $tab.find('.accessory-tab-close').on('click', function(e) {
            e.stopPropagation();
            removeAccessoryFromCurtainVariant($tab, roomId, productId, variantId, accessory.id);
         });

         // ACTIVATE the newly added tab
         activateCurtainAccessoryTab($tab, roomId, productId, variantId);

         updateVariantStatus(productId, roomId, variantId, 'set', 'fitout');
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

      // NEW: Function to remove accessory from curtain variant
      function removeAccessoryFromCurtainVariant($tab, roomId, productId, variantId, accessoryId) {
         console.log('Removing accessory from curtain variant');

         $tab.remove();
         $(`#accessory-${accessoryId}-${productId}-${variantId}-room${roomId}`).remove();

         const $variantContent = $(`#variant-${productId}-${variantId}-room${roomId}`);
         const $tabsContainer = $variantContent.find(`#accessory-tabs-${productId}-${variantId}-room${roomId}`);
         const $detailsBody = $variantContent.find(`#accessory-details-${productId}-${variantId}-room${roomId} .accessory-details-body`);
         const $tabs = $tabsContainer.find('.accessory-tab');

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

      // Add product tab
      function addProductTab(roomId, product) {
         console.log('Adding product:', product.product_code, 'to room:', roomId);

         const $tabsContainer = $(`#productTabs-room${roomId}`);
         const $emptyState = $tabsContainer.find('.product-empty-state');

         if ($emptyState.length) {
            $emptyState.remove();
         }

         const productId = `product-${product.product_id}-room${roomId}`;
         const tabId = `${productId}-tab`;

         if ($tabsContainer.find(`[data-product="${product.product_id}"]`).length) {
            alert('This product has already been added to this room.');
            return;
         }

         const $tab = $(`
            <div class="product-tab" data-product="${product.product_id}" data-type="${product.type}" data-available-in="${product.available_in}" id="${tabId}">
               <div class="product-tab-icon" style="background: ${product.product_color};">
                  <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
               </div>
               <span class="product-tab-name">${product.product_name}</span>
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
            <div class="product-content" id="${productId}" style="display: none;">
               <div class="loading-state">
                  <i class="fa fa-spinner fa-spin fa-2x"></i>
                  <p>Loading ${product.product_name} details...</p>
               </div>
            </div>
         `);

         $contentArea.append($content);

         activateProductTab($tab);

         setTimeout(() => {
            loadProductContent(productId, product, roomId);
         }, 100);
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

      // Updated function to load product content
      function loadProductContent(contentId, product, roomId) {
         const $content = $(`#${contentId}`);
         const totalsHTML = createTotalsSection(product, roomId);

         // Check if product has variants
         if (product.has_variant && product.available_in === 'set') {
            $.post(ajax_url + '/api', {
               get_product_variants: 1,
               product_id: product.product_id
            }, function(data) {
               var response = $.parseJSON(data);
               if (response.status === 'success') {
                  const variants = response.data;
                  loadProductWithVariants($content, product, variants, roomId);
                  $content.append(totalsHTML);
               }
            });
         } else if (product.available_in === 'size') {
            let variants = [];
            try {
               const bedDimsJson = product?.product_bed_dims || '{}';
               const bedDimsObject = JSON.parse(bedDimsJson);
               variants = Object.entries(bedDimsObject).map(([key, value]) => ({
                  id: key,
                  size: key,
                  width: value.width || null,
                  length: value.length || null,
                  height: value.height || null,
                  standart_price: value.standart_price || null,
                  name: `${value.width || ''}x${value.length || ''}`,
                  active_materials: product.active_materials || {} // Pass materials to variants
               }));
            } catch (error) {
               console.error('Error parsing product_bed_dims:', error);
               variants = [];
            }

            loadProductWithVariants($content, product, variants, roomId);
            $content.append(totalsHTML);
         } else {
            // Create a mock variant object for simple products to use the same material function
            const mockVariant = {
               product_id: product.product_id,
               product_name: product.product_name,
               product_desc: product.product_desc || '',
               active_materials: product.active_materials || {}
            };
            if (product.type === 'fitout') {
               loadFitoutProductContent($content, product, roomId);
            } else if (product.type === 'curtain') {
               loadCurtainProductContent($content, product, mockVariant, roomId);
            } else {
               loadSimpleProductContent($content, product, mockVariant, roomId);
            }

            $content.append(totalsHTML);
         }
         calculateProductTotal(product.product_id, roomId, product.type);
      }

      // Load product with variants
      function loadProductWithVariants($content, product, variants, roomId) {

         // Create basic details section + variants
         var basicDetailsHTML = '';
         let variantsHTML;
         if (product.available_in == 'set') {
            // basicDetailsHTML = createBasicDetailsSection(product, roomId);
            variantsHTML = createVariantsTabs(product, variants, roomId);
         } else {
            variantsHTML = createVariantsRadioSelection(product, variants, roomId);
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
            setupVariantsTabs(product, variants, roomId);
         } else {
            setupVariantsRadioSelection(product, variants, roomId);
         }
      }

      // Setup variants radio selection (for size variants)
      function setupVariantsRadioSelection(product, variants, roomId) {
         const productId = product.product_id;
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
         });

         // Setup calculations for variants
         setupVariantCalculations(productId, roomId);

         // Setup material tabs for variants
         setupVariantMaterialTabs(product, variants, roomId);

         // Setup pillow subcategory tabs for variants
         setupVariantPillowSubcategoryTabs(product, variants, roomId);

         // Activate the first radio by default
         const $firstRadio = $variantsRadio.find('.variant-radio-input').first();
         if ($firstRadio.length) {
            $firstRadio.trigger('change');
         }
      }

      // Create variants radio selection (for size variants)
      function createVariantsRadioSelection(product, variants, roomId) {
         const productId = product.product_id; // Define productId here

         return `
            <div class="product-variants-section" id="variants-section-${productId}-room${roomId}">
               <div class="variant-radio-header">
                  <h6><i class="fa fa-ruler mr-2"></i>Select Mattress Size</h6>
               </div>
               <div class="variant-radio-container" id="variants-radio-${productId}-room${roomId}">
                  ${variants.map((variant, index) => `
                     <div class="variant-radio-option">
                        <input type="radio" 
                              id="variant-radio-${productId}-${variant.id}-room${roomId}" 
                              name="variant-selection-${productId}-room${roomId}" 
                              value="${variant.id}" 
                              ${index === 0 ? 'checked' : ''}
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
                     <div class="product-variant-content ${index === 0 ? 'active' : ''}" 
                        id="variant-${productId}-${variant.id}-room${roomId}">
                        ${createVariantContentForSize(product, variant, roomId)}
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

      function createTotalsSection(product, roomId) {
         return `
         <div class="product-totals-section" id="product-totals-${product.product_id}-room${roomId}">
            <div class="product-totals-row">
               <div class="product-totals-label">Total:</div>
               <div class="product-totals-amount">
                  $<span class="product-total-price" id="product-total-${product.product_id}-room${roomId}">0.00</span>
               </div>
            </div>
         </div>
         `;
      }

      function loadFitoutProductContent($content, product, roomId) {
         const dims = product.dims || {};
         const width = dims.width || '';
         const length = dims.length || dims.height || '';
         const standart_price = dims.standart_price || '';
         const unit_price = calculateUnitPrice(product.calculate_type, dims);

         const buttonText = `Add Item to ${product.team_name}`;
         const $wrapper = $(`
            <div class="product-details-wrapper">
               <div class="fitout-product-layout">
                  <div class="items-tabs-sidebar">
                     <div class="items-tabs-header">
                        <h6><i class="fa fa-list mr-2"></i>Items</h6>
                        <button type="button" class="btn btn-sm btn-primary add-product-item-btn" data-product="${product.product_id}" data-type="${product.type}" data-available-in="${product.available_in}" data-room="${roomId}">
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
                              <input type="number" class="form-control dimension-width" value="${width}" placeholder="0.00" step="0.01" min="0">
                           </div>
                           <div class="compact-header-group">
                              <label>Length/Height (m)</label>
                              <input type="number" class="form-control dimension-length" value="${length}" placeholder="0.00" step="0.01" min="0">
                           </div>
                           <div class="compact-header-group">
                              <label>Quantity</label>
                              <input type="number" class="form-control product-qty" placeholder="0" step="1" min="1" value="1">
                           </div>
                           <div class="compact-header-group">
                              <label>Discount(%)</label>
                              <input type="number" class="form-control product-discount" placeholder="0" step="0.01" min="0" value="0">
                           </div>
                        </div>
                     </div>
                     <!-- Add base price field for variant -->
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

      // Updated simple product content with materials
      function loadSimpleProductContent($content, product, variant, roomId) {

         const dims = product.dims || {}; // fallback if undefined

         const width = dims.width || '';
         const length = dims.length || '';
         const height = dims.height || '';
         const standart_price = dims.standart_price || '';
         const unit_price = calculateUnitPrice(product.calculate_type, dims);

         const $wrapper = $(`
            <div class="simple-product-content">
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
                                 <input type="number" class="form-control dimension-width" value="${width}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(product.calculate_type, 'w')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Length (m)</label>
                                 <input type="number" class="form-control dimension-length" value="${length}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(product.calculate_type, 'l')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Height (m)</label>
                                 <input type="number" class="form-control dimension-height" value="${height}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(product.calculate_type, 'h')}>
                              </div>
                              <div class="compact-detail-group">
                                 <label>Quantity</label>
                                 <input type="number" class="form-control product-qty" 
                                       placeholder="0" step="1" min="1" value="1">
                              </div>
                              <div class="compact-detail-group">
                                 <label>Discount(%)</label>
                                 <input type="number" class="form-control product-discount" 
                                       placeholder="0" step="0.01" min="0" value="0">
                              </div>
                        </div>
                     </div>
                  <!-- Add base price field for variant -->
                  <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${product.product_id}">
                  <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${product.product_id}">
                  
                  <!-- Material Section - Use existing data -->
                  ${createMaterialSection(product, variant, roomId)}
                  </div>
            </div>
         `);

         $content.html($wrapper);

         // Setup material tabs after content is loaded
         setTimeout(() => {
            setupMaterialTabsForProduct(product, variant, roomId);
            setupPillowSubcategoryTabs(product, variant, roomId);
         }, 100);
      }

      // Curtain product content with accessory section working like items section
      function loadCurtainProductContent($content, product, variant, roomId) {

         let materialCategories = [];

         if (
            product.product_active_materials &&
            typeof product.product_active_materials === 'string' &&
            product.product_active_materials.trim().startsWith('[')
         ) {
            try {
               materialCategories = JSON.parse(product.product_active_materials);
               if (!Array.isArray(materialCategories)) materialCategories = [];
            } catch (e) {
               materialCategories = [];
            }
         }

         const dims = product.dims || {}; // fallback if undefined

         const width = dims.width || '';
         const length = dims.length || '';
         const height = dims.height || '';
         const standart_price = dims.standart_price || '';
         const unit_price = calculateUnitPrice(product.calculate_type, dims);

         const $wrapper = $(`
            <div class="simple-product-content">
               <div class="compact-product-details">
                  <div class="compact-section-header">
                     <h6><i class="fa fa-cube mr-2"></i>${product.product_name} Details</h6>
                  </div>
                  <div class="compact-details-with-image">
                     <div class="compact-image-preview">
                        <img style="width:100%;height:100%;" src="${product.product_img}" alt="${product.product_name}">
                     </div>
                     <div class="compact-details-fields">
                        <div class="compact-detail-group" style="width:200px;">
                           <label>Quantity</label>
                           <input type="number" class="form-control product-qty" 
                                 placeholder="0" step="1" min="1" value="1"
                                 data-variant="${variant.product_id}">
                        </div>
                        <div class="compact-detail-group" style="width:200px;">
                           <label>Discount(%)</label>
                           <input type="number" class="form-control product-discount" 
                                 placeholder="0" step="0.01" min="0" value="0"
                                 data-variant="${variant.product_id}">
                        </div>
                     </div>
                  </div>
                  <!-- Add base price field -->
                  <input type="hidden" class="unit-price" value="${unit_price}" data-variant="${product.product_id}">
                  <input type="hidden" class="calculate-type" value="${product.calculate_type}" data-variant="${product.product_id}">

                  <!-- Material Section - Use existing data -->
                  ${createMaterialSection(product, variant, roomId)}

                  <div class="curtain-options-section">
                     <h6><i class="fa fa-cog mr-2"></i>Curtain Options</h6>
                     <div class="curtain-controls">
                        <div class="curtain-control">
                           <label>Opening Direction</label>
                           <select class="form-control opening-direction">
                              <option value="">Select Direction</option>
                              <option value="left">Left Opening</option>
                              <option value="right">Right Opening</option>
                              <option value="center">Center Opening</option>
                              <option value="top">Top Opening</option>
                           </select>
                        </div>
                        <div class="curtain-control">
                           <label>Open With</label>
                           <select class="form-control open-with">
                              <option value="">Select Option</option>
                              <option value="cord">Cord</option>
                              <option value="wand">Wand</option>
                              <option value="motorized">Motorized</option>
                              <option value="manual">Manual</option>
                           </select>
                        </div>
                        <div class="curtain-control p-4">
                           <input type="checkbox" class="custom-control-input curtain-installation-needed-checkbox" id="curtain-installation${product.product_id}${variant.product_id}${roomId}" value="needed">
                           <label class="custom-control-label font-weight-bold" for="curtain-installation${product.product_id}${variant.product_id}${roomId}">
                              Installation Needed
                              <i class="fa fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="Charge will be shown based on selected county"></i>
                              <span class="installation-price text-success ml-2 font-weight-bold" style="display:none;"></span>
                           </label>
                        </div>
                     </div>
                     
                     <h6 style="margin-top: 16px;"><i class="fa fa-plus-circle mr-2"></i>Accessories</h6>
                     <div class="accessory-layout">
                        <div class="accessory-tabs-sidebar">
                           <div class="accessory-tabs-header">
                              <h6><i class="fa fa-list mr-2"></i>Accessories</h6>
                              <button type="button" class="btn btn-sm btn-primary add-accessory-btn" data-product="${product.product_id}" data-attr-id="${product.attr_id}" data-variant="" data-room="${roomId}">
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
               </div>
            </div>
         `);

         $content.html($wrapper);

         // Setup material tabs after content is loaded
         setTimeout(() => {
            setupMaterialTabsForProduct(product, variant, roomId);
            setupPillowSubcategoryTabs(product, variant, roomId);
         }, 100);
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

         // Trigger calculation (if exists)
         $materialGroup.find('.area-weight').trigger('input');
      });

      // function to create material section with existing data
      function createMaterialSection(product, variant, roomId) {
         // Use the materials data that already comes with the product/variant
         const activeMaterials = variant.active_materials || product.active_materials || {};
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
                  <div class="material-tabs" id="materialTabs-${product.product_id}-${variantId}-room${roomId}">
                     ${materialCategories.map((category, index) => `
                        <button class="material-tab ${index === 0 ? 'active' : ''}" 
                              data-category="${category.id}">
                              ${category.name}
                        </button>
                     `).join('')}
                  </div>
                  <div class="material-tabs-content" id="materialTabsContent-${product.product_id}-${variantId}-room${roomId}">
                     ${materialCategories.map((category, index) => `
                        <div class="material-tab-content ${index === 0 ? 'active' : ''}" 
                           id="materialContent-${product.product_id}-${variantId}-room${roomId}-${category.id}">
                           ${category.id === 'pillow' ? 
                                 createPillowSubcategoriesForVariant(product, variant, roomId, activeMaterials) : 
                                 createStandardMaterialContent(product, variant, category.id, roomId, activeMaterials)
                           }
                        </div>
                     `).join('')}
                  </div>
            </div>
         `;
      }

      function createMaterialSectionForItem(item, productId, roomId) {
         // Use the materials data that already comes with the item
         const activeMaterials = item.active_materials || {};
         const itemName = item.product_name;
         const itemId = item.product_id;

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
                       ${category.id === 'pillow' ? 
                             createPillowSubcategoriesForItem(item, productId, roomId, activeMaterials) : 
                             createStandardMaterialContentForItem(item, category.id, roomId, activeMaterials)
                       }
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }

      // Function to create standard material content for items
      function createStandardMaterialContentForItem(item, category, roomId, activeMaterials) {
         return createMaterialInputsForCategory(item, item, category, roomId, activeMaterials);
      }

      // Function to create pillow subcategories for items
      function createPillowSubcategoriesForItem(item, productId, roomId, activeMaterials) {
         const itemId = item.product_id;

         // Get pillow data from activeMaterials
         const pillowData = activeMaterials.pillow || {};

         console.log('Pillow data for item:', {
            itemId: itemId,
            pillowData: pillowData,
            activeMaterials: activeMaterials
         });

         // If no pillow data, return empty
         if (!pillowData || Object.keys(pillowData).length === 0) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No pillow materials available for this item</p>
            </div>
        `;
         }

         // Create subcategories based on the available pillow data
         const subcategories = [];

         // Add default pillow if it exists
         if (pillowData.default_pillow) {
            subcategories.push({
               id: 'default_pillow',
               name: 'Default Pillow',
               type: 'default_pillow'
            });
         }

         // Add other pillow types if they exist
         if (pillowData.pillow_front) {
            subcategories.push({
               id: 'pillow_front',
               name: 'Pillow Front',
               type: 'pillow_front'
            });
         }

         if (pillowData.pillow_back) {
            subcategories.push({
               id: 'pillow_back',
               name: 'Pillow Back',
               type: 'pillow_back'
            });
         }

         if (pillowData.pillow_pipping) {
            subcategories.push({
               id: 'pillow_pipping',
               name: 'Pillow Pipping',
               type: 'pillow_pipping'
            });
         }

         if (subcategories.length === 0) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No pillow subcategories available</p>
            </div>
        `;
         }

         return `
        <div class="pillow-subcategories-section">
            <div class="pillow-subcategories-tabs" id="pillowTabs-${itemId}-${productId}-room${roomId}">
                ${subcategories.map((subcat, index) => `
                    <button class="pillow-subcategory-tab ${index === 0 ? 'active' : ''}" 
                          data-subcategory="${subcat.id}" data-item="${itemId}" data-product="${productId}">
                        <div class="pillow-subcategory-header">
                            <span class="status-indicator status-empty"></span>
                            <span class="pillow-subcategory-title">${subcat.name}</span>
                        </div>
                    </button>
                `).join('')}
            </div>
            <div class="pillow-subcategories-content" id="pillowContent-${itemId}-${productId}-room${roomId}">
                ${subcategories.map((subcat, index) => `
                    <div class="pillow-subcategory-content ${index === 0 ? 'active' : ''}" 
                       id="pillowSubcategory-${itemId}-${productId}-room${roomId}-${subcat.id}">
                       <div class="pillow-subcategory-details">
                           ${createPillowMaterialContentForItem(item, subcat, pillowData, productId, roomId, itemId)}
                       </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }

      // Function to create pillow material content for items
      function createPillowMaterialContentForItem(item, subcat, pillowData, productId, roomId, itemId) {
         const subcatData = pillowData[subcat.id];

         if (!subcatData) {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No materials available for ${subcat.name}</p>
            </div>
        `;
         }

         const activeMaterials = subcatData.active || [];
         const allMaterials = subcatData.all_materials || [];
         const materialGroups = subcatData.materialGroups || {};

         console.log('Creating pillow material content for item:', {
            subcategory: subcat.id,
            activeMaterials: activeMaterials,
            allMaterials: allMaterials,
            materialGroups: materialGroups
         });

         // If we have material groups, use them
         if (Object.keys(materialGroups).length > 0) {
            return Object.entries(materialGroups).map(([refLabel, materials]) => {
               return createPillowMaterialGroupForItem(item, subcat, refLabel, materials, allMaterials, productId, roomId, itemId);
            }).join('');
         }
         // If we have active materials but no groups, create single input
         else if (activeMaterials.length > 0) {
            return createSinglePillowMaterialInputForItem(item, subcat, activeMaterials[0], allMaterials, productId, roomId, itemId);
         }
         // If no active materials but have available materials, create empty input
         else if (allMaterials.length > 0) {
            return createSinglePillowMaterialInputForItem(item, subcat, null, allMaterials, productId, roomId, itemId);
         }
         // No materials available
         else {
            return `
            <div class="no-materials-available">
                <i class="fa fa-info-circle"></i>
                <p>No materials available for ${subcat.name}</p>
            </div>
        `;
         }
      }

      // Function to create a pillow material group for items
      function createPillowMaterialGroupForItem(item, subcat, refLabel, materials, allMaterials, productId, roomId, itemId) {
         const activeMaterial = materials && materials.length > 0 ? materials[0] : null;

         return `
        <div class="pillow-material-group" data-ref-label="${refLabel}">
            <div class="pillow-material-group-header">
                <h6>${subcat.name} - ${refLabel} ${activeMaterial?.alias_name ? `- ${activeMaterial.alias_name}` : ''}</h6>
            </div>
            <div class="pillow-material-inputs-compact">
                <div class="pillow-material-compact-image">
                    ${activeMaterial?.material_img ? 
                          `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` : 
                          `<i class="fa fa-image"></i>`
                    }
                </div>
                <div class="pillow-material-compact-fields">
                    <div class="pillow-material-input">
                        <label>Material</label>
                        <select class="form-control material-type-select" 
                              data-subcategory="${subcat.id}" data-item="${itemId}" data-ref-label="${refLabel}">
                            <option value="">Select Material</option>
                            ${allMaterials.map(material => `
                                <option value="${material.material_id}" 
                                      ${activeMaterial && activeMaterial.material_id == material.material_id ? 'selected' : ''}
                                      data-price="${material.material_price || 0}"
                                      data-image="${material.material_img || ''}">
                                      ${material.material_name}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    
                    ${subcat.id === 'default_pillow' ? `
                        <div class="pillow-material-input">
                            <label>Quantity</label>
                            <input type="number" class="form-control pillow-quantity" 
                                  placeholder="1" min="1" value="${activeMaterial?.quantity || 1}"
                                  data-subcategory="${subcat.id}" data-item="${itemId}" data-ref-label="${refLabel}">
                        </div>
                        <div class="pillow-material-input">
                            <label>Dimensions (cm)</label>
                            <div class="pillow-dimensions d-flex align-items-center">
                                <input type="number" class="form-control pillow-length" 
                                      placeholder="Length" step="0.1" min="0"
                                      value="${activeMaterial?.length_cm || activeMaterial?.dimensions?.length || ''}"
                                      data-subcategory="${subcat.id}" data-item="${itemId}" data-ref-label="${refLabel}">
                                <span class="dimension-separator">×</span>
                                <input type="number" class="form-control pillow-width" 
                                      placeholder="Width" step="0.1" min="0"
                                      value="${activeMaterial?.width_cm || activeMaterial?.dimensions?.width || ''}"
                                      data-subcategory="${subcat.id}" data-item="${itemId}" data-ref-label="${refLabel}">
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
      }

      // Function for single pillow material input for items
      function createSinglePillowMaterialInputForItem(item, subcat, activeMaterial, allMaterials, productId, roomId, itemId) {
         return `
        <div class="pillow-material-inputs-compact">
            <div class="pillow-material-compact-image">
                ${activeMaterial?.material_img ? 
                    `<img src="<?= URL ?>/uploads/material/${activeMaterial.material_img}" alt="${activeMaterial.material_name}" style="width:100%;height:100%;object-fit:cover;">` : 
                    `<i class="fa fa-image"></i>`
                }
            </div>
            <div class="pillow-material-compact-fields">
                <div class="pillow-material-input">
                    <label>Material</label>
                    <select class="form-control material-type-select" 
                          data-subcategory="${subcat.id}" data-item="${itemId}">
                        <option value="">Select Material</option>
                        ${allMaterials.map(material => `
                            <option value="${material.material_id}" 
                                ${activeMaterial && activeMaterial.material_id == material.material_id ? 'selected' : ''}
                                data-price="${material.material_price || 0}"
                                data-image="${material.material_img || ''}">
                                ${material.material_name}
                            </option>
                        `).join('')}
                    </select>
                </div>
                
                ${subcat.id === 'default_pillow' ? `
                    <div class="pillow-material-input">
                        <label>Quantity</label>
                        <input type="number" class="form-control pillow-quantity" 
                              placeholder="1" min="1" value="${activeMaterial?.quantity || 1}"
                              data-subcategory="${subcat.id}" data-item="${itemId}">
                    </div>
                    <div class="pillow-material-input">
                        <label>Dimensions (cm)</label>
                        <div class="pillow-dimensions d-flex align-items-center">
                            <input type="number" class="form-control pillow-length" 
                                  placeholder="Length" step="0.1" min="0"
                                  value="${activeMaterial?.length_cm || activeMaterial?.dimensions?.length || ''}"
                                  data-subcategory="${subcat.id}" data-item="${itemId}">
                            <span class="dimension-separator">×</span>
                            <input type="number" class="form-control pillow-width" 
                                  placeholder="Width" step="0.1" min="0"
                                  value="${activeMaterial?.width_cm || activeMaterial?.dimensions?.width || ''}"
                                  data-subcategory="${subcat.id}" data-item="${itemId}">
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
      }

      function loadPillowSubcategoriesForItem(item, productId, roomId) {
         const itemId = item.product_id;
         let materialCategories = [];

         if (
            item.product_active_materials &&
            typeof item.product_active_materials === 'string' &&
            item.product_active_materials.trim().startsWith('[')
         ) {
            try {
               materialCategories = JSON.parse(item.product_active_materials);
               if (!Array.isArray(materialCategories)) materialCategories = [];
            } catch (e) {
               materialCategories = [];
            }
         }
         const pillowCategory = materialCategories.find(cat => cat.id === 'pillow');
         if (!pillowCategory) return '';

         return `
            <div class="pillow-subcategories-section">
               <div class="pillow-subcategories-tabs" id="pillowTabs-${itemId}-${productId}-room${roomId}">
                  ${pillowCategory.subcategories.map((subcat, index) => `
                     <button class="pillow-subcategory-tab ${index === 0 ? 'active' : ''}" 
                           data-subcategory="${subcat.id}" data-item="${itemId}" data-product="${productId}" data-room="${roomId}">
                        <div class="pillow-subcategory-header">
                           <span class="status-indicator status-empty"></span>
                           <span class="pillow-subcategory-title">${subcat.name}</span>
                        </div>
                     </button>
                  `).join('')}
               </div>
               <div class="pillow-subcategories-content" id="pillowContent-${itemId}-${productId}-room${roomId}">
                  ${pillowCategory.subcategories.map((subcat, index) => `
                     <div class="pillow-subcategory-content ${index === 0 ? 'active' : ''}" 
                        id="pillowSubcategory-${itemId}-${productId}-room${roomId}-${subcat.id}">
                        <div class="pillow-subcategory-details">
                           <div class="pillow-material-inputs-compact">
                              <div class="pillow-material-compact-image">
                                 <i class="fa fa-image"></i>
                              </div>
                              <div class="pillow-material-compact-fields">
                                 <div class="pillow-material-input">
                                    <label>Material Grade</label>
                                    <select class="form-control material-grade" 
                                          data-subcategory="${subcat.id}">
                                       <option value="">Select Grade</option>
                                       <option value="standard">Standard</option>
                                       <option value="premium">Premium</option>
                                       <option value="economy">Economy</option>
                                    </select>
                                 </div>
                                 <div class="pillow-material-input">
                                    <label>Material</label>
                                    <select class="form-control material-type-select" 
                                          data-subcategory="${subcat.id}">
                                       <option value="">Select Material</option>
                                       ${pillowCategory.defaultMaterials.map(material => `
                                          <option value="${material.id}">${material.name}</option>
                                       `).join('')}
                                    </select>
                                 </div>
                                 <div class="pillow-material-input">
                                    <label>Area/Weight</label>
                                    <input type="text" class="form-control area-weight" 
                                          placeholder="Enter area or weight"
                                          data-subcategory="${subcat.id}">
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  `).join('')}
               </div>
            </div>
         `;
      }

      // NEW: Function to setup pillow subcategory tabs
      function setupPillowSubcategoryTabs(product, variant, roomId) {
         let productId = product.product_id;
         let variantId = variant.product_id;
         let availableIn = product.available_in;
         const $pillowTabs = $(`#pillowTabs-${productId}-${variantId}-room${roomId}`);
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

         // Activate the first tab by default
         const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
         if ($firstTab.length) {
            $firstTab.trigger('click');
         }
      }

      // Enhanced function to update pillow subcategory status with material groups
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

            const materialGrade = $group.find(`.material-grade[data-ref-label="${refLabel}"]`).val();
            const materialType = $group.find(`.material-type-select[data-ref-label="${refLabel}"]`).val();
            const areaWeight = $group.find(`.area-weight[data-ref-label="${refLabel}"]`).val();

            // For default pillow, also check quantity and dimensions
            let groupComplete = !!(materialGrade && materialType && areaWeight);

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
            const materialGrade = $content.find('.material-grade').val();
            const materialType = $content.find('.material-type-select').val();
            const areaWeight = $content.find('.area-weight').val();

            let singleComplete = !!(materialGrade && materialType && areaWeight);

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

         $pillowContent.find('.material-grade, .material-type-select, .area-weight, .pillow-quantity, .pillow-length, .pillow-width').on('input change', function() {
            const $input = $(this);
            const subcategoryId = $input.data('subcategory');
            const refLabel = $input.data('ref-label');

            updatePillowSubcategoryStatus(productId, variantId, availableIn, subcategoryId, roomId, type);
         });
      }

      // Add item to product with improved material section layout
      function addItemToProduct(roomId, productId, item) {
         const dims = item.dims || {};
         const width = dims.width || '';
         const length = dims.length || '';
         const height = dims.height || '';
         const standart_price = dims.standart_price || '';
         const unit_price = calculateUnitPrice(item.calculate_type, dims);

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
                              <label>Length (m)</label>
                              <input type="number" class="form-control item-length item-dims" value="${length}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(item.calculate_type, 'l')}>
                           </div>
                           <div class="detail-group">
                              <label>Width (m)</label>
                              <input type="number" class="form-control item-width item-dims" value="${width}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(item.calculate_type, 'w')}>
                           </div>
                           <div class="detail-group">
                              <label>Height (m)</label>
                              <input type="number" class="form-control item-height item-dims" value="${height}" placeholder="0.00" step="0.01" min="0" ${getDisabledAttr(item.calculate_type, 'h')}>
                           </div>
                           <div class="detail-group">
                              <label>Quantity</label>
                              <input type="number" class="form-control item-qty" placeholder="0" min="1" value="1">
                           </div>
                        <div class="detail-group" style="width:200px;">
                           <label>Discount(%)</label>
                           <input type="number" class="form-control item-discount" placeholder="0" step="0.01" min="0" value="0">
                        </div>
                     </div>
                  </div>
                  <!-- Add base price field for item -->
                  <input type="hidden" class="item-unit-price" value="${unit_price}" data-variant="${item.product_id}">
                  <input type="hidden" class="item-calculate-type" value="${item.calculate_type}" data-variant="${item.product_id}">
                  <div class="detail-group" style="margin-top: 12px;">
                     <label>Notes</label>
                     <textarea class="form-control item-notes" placeholder="Additional notes..." rows="2"></textarea>
                  </div>
               
                  ${createMaterialSectionForItem(item, productId, roomId)}
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

         $pillowContent.find('.material-grade, .material-type-select, .area-weight, .pillow-quantity, .pillow-length, .pillow-width').on('input change', function() {
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
            let groupComplete = !!(materialType && areaWeight);

            if (subcategoryId === 'default_pillow') {
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

            let singleComplete = !!(materialType && areaWeight);

            if (subcategoryId === 'default_pillow') {
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
      function addAccessoryToProduct(roomId, productId, accessory, accessoryOptions) {
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

         const colorOptions = blackOutColors.map(color => `
            <option value="${color}">${color}</option>
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
                                 <option value="${option.accessory_id}" data-image="${option.accessory_img}">${option.accessory_name}</option>
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
            }
         });

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

         updateRoomStatus(`room${roomId}`);
      }

      // ADDED: Remove accessory from product
      function removeAccessoryFromProduct($tab, roomId, productId, accessoryId) {
         console.log('Removing accessory from product');

         $tab.remove();
         $(`#accessory-${accessoryId}-${productId}-room${roomId}`).remove();

         const $productContent = $(`#product-${productId}-room${roomId}`);
         const $tabsContainer = $productContent.find('.accessory-tabs-container');
         const $detailsBody = $productContent.find('.accessory-details-body');
         const $tabs = $tabsContainer.find('.accessory-tab');

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

      // Call calculateProductTotal whenever product data changes
      $(document).on('input change', '.dimension-width, .dimension-length, .dimension-height, .product-qty, .product-discount, .item-width, .item-length, .item-height, .item-qty, .product-discount', function() {
         const $productContent = $(this).closest('.product-content');
         if ($productContent.length) {
            const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const roomId = $productContent.attr('id').match(/room(\d+)/)[1];
            const productType = $productContent.find('.product-tab.active').data('type') || 'product';

            calculateProductTotal(productId, roomId, productType);
         }
      });

      // Call calculateProductTotal whenever material data changes
      $(document).on('input change', '.area-weight, .material-type-select, .pillow-quantity, .pillow-length, .pillow-width', function() {
         const $productContent = $(this).closest('.product-content');
         if ($productContent.length) {
            const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
            const roomId = $productContent.attr('id').match(/room(\d+)/)[1];
            const productType = $productContent.find('.product-tab.active').data('type') || 'product';

            calculateProductTotal(productId, roomId, productType);
         }
      });

      // Call updateOrderTotals when tax percentage changes
      $('#lblOrderTax').on('change', function() {
         updateGrandTotals();
      });

      // Call updateOrderTotals when products are removed
      $(document).on('click', '.product-tab-close', function() {
         // Your existing removal code...

         // Update totals after removal
         setTimeout(updateOrderTotals, 100);
      });

      // Call updateOrderTotals when items are added/removed
      $(document).on('click', '.add-product-item-btn, .items-tab-close', function() {
         setTimeout(updateOrderTotals, 100);
      });

      // Call updateOrderTotals when accessories are added/removed
      $(document).on('click', '.add-accessory-btn, .accessory-tab-close', function() {
         setTimeout(updateOrderTotals, 100);
      });

      // Initialize totals when page loads
      $(document).ready(function() {
         setTimeout(updateOrderTotals, 1000);
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
         hideProductSelectModal();

         setTimeout(() => {
            console.log('Showing qualification modal again');
            showQualificationModalFiltered(state.selectedMainCategory, state.currentRoom);
         }, 100);
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
         $('#confirmAddQualification').prop('disabled', false);
         $('#confirmAddQualification').trigger('click');
      });

      $('#confirmAddQualification').on('click', function() {
         console.log('Confirm add qualification clicked');
         console.log('Current state:', {
            selectedQualification: state.selectedQualification,
            currentRoom: state.currentRoom
         });

         if (state.selectedQualification && state.currentRoom) {

            hideQualificationModal();

            setTimeout(() => {
               console.log('Showing multi-select modal with roomId:', state.currentRoom, ' And qualification:', state.selectedQualification);
               showProductSelectModal(state.selectedQualification, state.currentRoom);
            }, 100);
         }
      });

      $(document).on('click', '.multi-select-option[data-product-id]', function() {
         const productId = $(this).data('product-id');
         console.log('Product option clicked:', productId);

         // Single selection
         $('.multi-select-option[data-product-id]').removeClass('selected');
         $(this).addClass('selected');

         // Store single product ID
         state.selectedProducts = [productId];

         $('#confirmMultiSelect').prop('disabled', false);
      });

      $(document).on('click', '.multi-select-option[data-item-id]', function() {
         const itemId = $(this).data('item-id');

         // Single selection
         $('.multi-select-option[data-item-id]').removeClass('selected');
         $(this).addClass('selected');

         // Store single item ID
         state.selectedItems = [itemId];
         $('#confirmSelectItem').prop('disabled', false);
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
                     hideProductSelectModal();
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
                     calculateProductTotal(productId, roomId, 'fitout');
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
            if (state.currentVariantId && state.currentProductId && state.currentRoomId) {
               // This is for a curtain variant
               console.log('Adding accessory to curtain variant:', {
                  productId: state.currentProductId,
                  variantId: state.currentVariantId,
                  roomId: state.currentRoomId,
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
                        addAccessoryToCurtainVariant(state.currentRoomId, state.currentProductId, state.currentVariantId, state.selectedAccessory, accessoryOptions);
                        calculateProductTotal(state.currentProductId, state.currentRoomId, 'curtain');
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

               const productId = $activeProductTab.data('product');
               const roomId = $activeProductTab.closest('.tab-pane').data('room');

               console.log('Adding accessory to regular product:', {
                  productId: productId,
                  roomId: roomId,
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
                  console.log('selectedProduct:', response);
                  if (response.status === 'success') {
                     // Find the selected product
                     const accessoryOptions = response.data;

                     if (accessoryOptions) {
                        addAccessoryToProduct(roomId, productId, state.selectedAccessory, accessoryOptions);
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
            showItemSelectionModal(productId);
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

      $(document).on('click', '.product-tab-close', function(e) {
         e.stopPropagation();
         const $tab = $(this).closest('.product-tab');
         const $tabsContainer = $tab.closest('.product-tabs-container');

         // if ($tabsContainer.find('.product-tab').length <= 1) {
         //    alert('At least one product must remain in the room.');
         //    return;
         // }

         const productId = $tab.data('product');
         const roomId = $tabsContainer.attr('id').replace('productTabs-room', '');

         $(`#product-${productId}-room${roomId}`).remove();
         $tab.remove();

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
         const variantId = $variantContent.find('.dimension-width').first().data('variant');
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

      $(document).on('change', '.room-name-input', function() {
         const roomName = $(this).val().trim();
         const roomId = $(this).data('room-id');
         const tabId = `${roomId}-tab`;
         const roomNumber = $(`#${tabId}`).data('room');
         const $tab = $(`#${tabId} .room-title`);
         const $addRoomBtn = $(`.add-item-to-room-btn[data-room="${roomNumber}"]`);

         if (roomName) {
            $tab.text(roomName);
            $addRoomBtn.text('Add Item To ' + roomName);
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

      // Initialize Advanced Filter Modal for Items
      function initializeItemAdvancedFilterModal() {

         // Brand selection handler for item brand
         $(document).on('change', '.brand-radio-input[name="item-brand-selection"]', function() {
            filterState.item.selectedBrand = $(this).val();
            const productId = $('#itemSelectionModal').data('current-product');
            applyItemFilters();
         });

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
            filterState.item.maxPrice = parseInt($(this).val()) || 10000;
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

      // Reset Advanced Filters for Items
      function resetItemAdvancedFilters() {
         // Reset form values
         $('#itemMinPrice').val(0);
         $('#itemMaxPrice').val(10000);
         $('#itemPriceRange').val(10000);
         $('#itemNewestFirst').prop('checked', true);
         $('#itemInStock').prop('checked', true);
         $('#itemOutOfStock').prop('checked', false);

         // Reset filter state
         filterState.item.minPrice = 0;
         filterState.item.maxPrice = 10000;
         filterState.item.dateSort = 'newest';
         filterState.item.inStock = true;
         filterState.item.outOfStock = false;
      }

      // Apply Advanced Filters for Items
      function applyItemAdvancedFilters() {
         const productId = $('#itemSelectionModal').data('current-product');
         applyItemFilters(productId);
      }

      // Show/Hide Advanced Filter Modal for Items
      function showItemAdvancedFilterModal() {
         $('#itemAdvancedFilterModal').fadeIn(300);
      }

      function hideItemAdvancedFilterModal() {
         $('#itemAdvancedFilterModal').fadeOut(300);
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

      // initialization
      initializeMainCategoryModal();
      initializeAdvancedFilterModal();
      initializeItemAdvancedFilterModal(); // Add this line
      setupProductSearch();
      setupItemSearch();
      setupImageUpload();
      updateRoomStatus('room1');
      addRoomToState(1);

      console.log('System initialized successfully');
   });
</script>
