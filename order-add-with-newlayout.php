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
      background: linear-gradient(135deg, #4361ee, #3a0ca3);
      border: none;
      border-radius: 4px;
      padding: 6px 12px;
      color: white;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
   }

   .add-item-to-room-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
   }

   .compact-product-details {
      background: white;
      border-radius: 6px;
      padding: 12px 16px;
      border: 1px solid #e0e0e0;
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

   .complex-product-layout {
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
      display: flex;
      align-items: center;
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
      width: 70px;
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
      padding: 16px;
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

   /* Modal Styles */
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
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 500px;
      max-height: 80vh;
      overflow: hidden;
   }

   .qualification-modal-header {
      padding: 12px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
   }

   .qualification-modal-header h5 {
      margin: 0;
      font-weight: 600;
      font-size: 1rem;
   }

   .search-container {
      padding: 8px 16px 0;
   }

   .search-input {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      font-size: 0.8rem;
   }

   .search-input:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .qualification-modal-body {
      padding: 12px 16px;
      max-height: 50vh;
      overflow-y: auto;
   }

   .qualification-options {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 8px;
   }

   .qualification-option {
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      background: white;
   }

   .qualification-option:hover {
      border-color: #4361ee;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
   }

   .qualification-option.selected {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
   }

   .qualification-option-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
   }

   .qualification-option-icon {
      width: 32px;
      height: 32px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 0.9rem;
   }

   .qualification-option-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
   }

   .qualification-option-description {
      color: #6c757d;
      font-size: 0.75rem;
      line-height: 1.3;
   }

   .qualification-modal-footer {
      padding: 8px 16px;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      background: #f8f9fa;
   }

   .multi-select-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
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
      width: 90%;
      max-width: 800px;
      max-height: 80vh;
      overflow: hidden;
   }

   .multi-select-modal-header {
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
      padding: 12px 16px;
      max-height: 60vh;
      overflow-y: auto;
   }

   .multi-select-options {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 12px;
   }

   .multi-select-option {
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      background: white;
   }

   .multi-select-option:hover {
      border-color: #4361ee;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
   }

   .multi-select-option.selected {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
   }

   .multi-select-option-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
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

   .multi-select-option-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
   }

   .multi-select-option-description {
      color: #6c757d;
      font-size: 0.75rem;
      line-height: 1.3;
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

   .item-category-tabs {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .item-category-tab {
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.8rem;
      font-weight: 500;
   }

   .item-category-tab:hover {
      background: #e9ecef;
   }

   .item-category-tab.active {
      background: #4361ee;
      color: white;
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
      grid-template-columns: 1fr 1fr;
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
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
      border-radius: 6px 6px 0 0;
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
      border-bottom: 1px solid #e0e0e0;
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
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 6px rgba(67, 97, 238, 0.2);
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
      border-top: 1px solid #e0e0e0;
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

   /* Accessory Total Price Field */
   .accessory-total-price {
      border-top: 1px solid #e9ecef;
   }

   .accessory-total-price label {
      font-weight: 600;
      color: #495057;
   }

   .accessory-total-price .form-control {
      font-weight: 600;
      color: #4361ee;
      background: #f8f9fa;
   }

   /* new css */
   /* Accessory Selection with Preview Layout */
   .accessory-type-with-preview {
      grid-column: 1 / -1;
   }

   .accessory-selection-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      align-items: start;
   }

   .accessory-type-select {
      width: 100%;
   }

   .accessory-option-preview {
      background: #f8f9fa;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      padding: 12px;
      min-height: 80px;
   }

   .preview-content {
      display: flex;
      align-items: center;
      gap: 12px;
   }

   .preview-image {
      width: 50px;
      height: 50px;
      border-radius: 4px;
      border: 1px solid #dee2e6;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
   }

   .preview-image i {
      color: #6c757d;
      font-size: 1.1rem;
   }

   .preview-details {
      flex: 1;
   }

   .preview-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.85rem;
      margin-bottom: 4px;
   }

   .preview-description {
      color: #6c757d;
      font-size: 0.75rem;
      line-height: 1.3;
   }

   /* Style when an option is selected */
   .accessory-option-preview.has-selection {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
   }

   .accessory-option-preview.has-selection .preview-name {
      color: #4361ee;
   }

   /* Responsive Adjustments */
   @media (max-width: 1200px) {
      .qualification-options {
         grid-template-columns: 1fr;
      }

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

      .qualification-modal-content {
         width: 95%;
         margin: 8px;
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

      .complex-product-layout {
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
         width: 60px;
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
         grid-template-columns: 1fr;
         gap: 12px;
      }

      .accessory-option-preview {
         min-height: 60px;
      }

      .preview-image {
         width: 40px;
         height: 40px;
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

   .qualification-modal-body::-webkit-scrollbar {
      width: 4px;
   }

   .qualification-modal-body::-webkit-scrollbar-track {
      background: #f1f1f1;
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
      border-radius: 0 0 6px 6px;
      border: 1px solid #e0e0e0;
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

   .add-variant-btn {
      background: linear-gradient(135deg, #ff9a00, #ff6b6b);
      border: none;
      border-radius: 4px;
      padding: 6px 12px;
      color: white;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 4px;
   }

   .add-variant-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(255, 107, 107, 0.3);
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

   .remove-set-btn {
      background: rgba(220, 53, 69, 0.1);
      border: 1px solid #dc3545;
      color: #dc3545;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 0.75rem;
      transition: all 0.2s ease;
   }

   .remove-set-btn:hover {
      background: #dc3545;
      color: white;
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

   .remove-product-from-set-btn {
      background: rgba(108, 117, 125, 0.1);
      border: 1px solid #6c757d;
      color: #6c757d;
      border-radius: 4px;
      padding: 2px 6px;
      font-size: 0.7rem;
      transition: all 0.2s ease;
   }

   .remove-product-from-set-btn:hover {
      background: #6c757d;
      color: white;
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

   .add-product-to-set-btn {
      background: linear-gradient(135deg, #4ecdc4, #44a08d);
      border: none;
      border-radius: 4px;
      padding: 6px 12px;
      color: white;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.2s ease;
   }

   .add-product-to-set-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(78, 205, 196, 0.3);
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
                                 <div class="room-header">
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
                                 <div class="product-tabs-header">
                                    <div class="room-info-form">
                                       <div class="form-group-small">
                                          <label for="floorName-room1">Floor Name</label>
                                          <input type="text" class="form-control-small" id="floorName-room1" placeholder="Enter floor name">
                                       </div>
                                       <div class="form-group-small">
                                          <label for="roomName-room1">Room Name</label>
                                          <input type="text" class="form-control-small" id="roomName-room1" placeholder="Enter room name">
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
                                    <button type="button" class="btn btn-sm btn-primary add-item-to-room-btn" data-room="1">
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
                                    <div class="product-empty-state">
                                       <i class="fa fa-hand-pointer"></i>
                                       <p>Select a product to configure details</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Qualification Modal -->
                     <div class="qualification-modal" id="qualificationModal">
                        <div class="qualification-modal-content">
                           <div class="qualification-modal-header">
                              <h5><i class="fa fa-plus-circle mr-2"></i>Select Product Type</h5>
                           </div>
                           <div class="search-container">
                              <input type="text" class="search-input" id="qualificationSearch" placeholder="Search product types...">
                           </div>
                           <div class="qualification-modal-body">
                              <div class="qualification-options" id="qualificationOptions"></div>
                           </div>
                           <div class="qualification-modal-footer">
                              <button type="button" class="btn btn-secondary" id="closeQualificationModal">Cancel</button>
                              <button type="button" class="btn btn-primary" id="confirmAddQualification" disabled>Next</button>
                           </div>
                        </div>
                     </div>

                     <!-- Multi-Select Products Modal -->
                     <div class="multi-select-modal" id="multiSelectModal">
                        <div class="multi-select-modal-content">
                           <div class="multi-select-modal-header">
                              <h5><i class="fa fa-layer-group mr-2"></i>Select Products</h5>
                           </div>
                           <div class="search-container">
                              <input type="text" class="search-input" id="productSearch" placeholder="Search products...">
                           </div>
                           <div class="multi-select-modal-body">
                              <div class="multi-select-options" id="multiSelectOptions"></div>
                           </div>
                           <div class="multi-select-modal-footer">
                              <button type="button" class="btn btn-secondary" id="closeMultiSelectModal">Cancel</button>
                              <button type="button" class="btn btn-primary" id="confirmMultiSelect" disabled>Add Selected Products</button>
                           </div>
                        </div>
                     </div>

                     <!-- Item Selection Modal -->
                     <div class="item-selection-modal" id="itemSelectionModal">
                        <div class="item-selection-modal-content">
                           <div class="item-selection-modal-header">
                              <h5><i class="fa fa-cube mr-2"></i>Select Item</h5>
                           </div>
                           <div class="search-container">
                              <input type="text" class="search-input" id="itemSearch" placeholder="Search items...">
                           </div>
                           <div class="item-selection-modal-body">
                              <div class="item-categories">
                                 <div class="item-categories-sidebar">
                                    <div class="item-category-tabs" id="itemCategoryTabs"></div>
                                 </div>
                                 <div class="item-category-content">
                                    <div class="item-options" id="itemOptions"></div>
                                 </div>
                              </div>
                           </div>
                           <div class="item-selection-modal-footer">
                              <button type="button" class="btn btn-secondary" id="closeItemSelectionModal">Cancel</button>
                              <button type="button" class="btn btn-primary" id="confirmSelectItem" disabled>Add Item</button>
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
                              <button type="button" class="btn btn-secondary" id="closeAccessorySelectionModal">Cancel</button>
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
      const btn = document.querySelector('.button-menu-mobile.open-left.waves-effect');
      if (btn) btn.click();
   });
</script>

<script>
   jQuery(function($) {
      // State management
      // State management
      const state = {
         rooms: [],
         currentRoom: null,
         selectedQualification: null,
         selectedProducts: [],
         selectedMaterialCategory: null,
         selectedPillowSubcategory: null,
         currentProductType: null,
         selectedItem: null,
         selectedAccessory: null,
         currentProductId: null
      };

      // Qualifications Data
      const qualifications = [{
            id: 1,
            name: 'Fitout',
            description: 'Interior construction, walls, ceilings, and flooring',
            icon: 'fa-paint-roller',
            color: 'linear-gradient(135deg, #4361ee, #3a0ca3)'
         },
         {
            id: 2,
            name: 'Curtains',
            description: 'Window treatments, blinds, and curtain systems',
            icon: 'fa-window-restore',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)'
         },
         {
            id: 3,
            name: 'Beds',
            description: 'Bed frames, mattresses, and bedroom furniture',
            icon: 'fa-bed',
            color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)'
         },
         {
            id: 4,
            name: 'Dining Sets',
            description: 'Complete dining room furniture sets',
            icon: 'fa-utensils',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)'
         },
         {
            id: 5,
            name: 'Sofa Sets',
            description: 'Living room sofa and furniture sets',
            icon: 'fa-couch',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)'
         },
         {
            id: 6,
            name: 'Wardrobes',
            description: 'Bedroom wardrobes and storage solutions',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)'
         },
         {
            id: 7,
            name: 'Armchairs',
            description: 'Armchair solutions',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)'
         }
      ];

      // Products Data with parent_id for variants and items
      const products = [
         // Fitout Products (parent_id: null)
         {
            id: 101,
            name: 'Wall',
            description: 'Wall construction and finishing',
            icon: 'fa-wall',
            color: 'linear-gradient(135deg, #ff6b6b, #ee5a52)',
            type: 'complex',
            parent_id: null,
            qualification_id: 1
         },
         // Wall Items (children with parent_id: 101)
         {
            id: 111,
            name: 'Drywall',
            description: 'Standard drywall panels',
            icon: 'fa-layer-group',
            color: '#ff6b6b',
            type: 'item',
            parent_id: 101,
            qualification_id: 1,
            category: 'construction'
         },
         {
            id: 112,
            name: 'Wall Studs',
            description: 'Metal or wood studs',
            icon: 'fa-grip-lines',
            color: '#ee5a52',
            type: 'item',
            parent_id: 101,
            qualification_id: 1,
            category: 'construction'
         },
         {
            id: 113,
            name: 'Wall Paint',
            description: 'Interior wall paint',
            icon: 'fa-paint-roller',
            color: '#4361ee',
            type: 'item',
            parent_id: 101,
            qualification_id: 1,
            category: 'finishing'
         },
         {
            id: 114,
            name: 'Wallpaper',
            description: 'Wall covering material',
            icon: 'fa-scroll',
            color: '#3a0ca3',
            type: 'item',
            parent_id: 101,
            qualification_id: 1,
            category: 'finishing'
         },

         {
            id: 102,
            name: 'Ceiling',
            description: 'Ceiling systems and fixtures',
            icon: 'fa-border-all',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            type: 'complex',
            parent_id: null,
            qualification_id: 1
         },
         // Ceiling Items (children with parent_id: 102)
         {
            id: 121,
            name: 'Ceiling Tiles',
            description: 'Acoustic ceiling tiles',
            icon: 'fa-border-all',
            color: '#4ecdc4',
            type: 'item',
            parent_id: 102,
            qualification_id: 1,
            category: 'materials'
         },
         {
            id: 122,
            name: 'Gypsum Board',
            description: 'Ceiling gypsum boards',
            icon: 'fa-square',
            color: '#44a08d',
            type: 'item',
            parent_id: 102,
            qualification_id: 1,
            category: 'materials'
         },

         {
            id: 103,
            name: 'Ground',
            description: 'Flooring and ground works',
            icon: 'fa-square',
            color: 'linear-gradient(135deg, #45b7d1, #4a7bd6)',
            type: 'complex',
            parent_id: null,
            qualification_id: 1
         },
         // Ground Items (children with parent_id: 103)
         {
            id: 131,
            name: 'Floor Tiles',
            description: 'Ceramic or porcelain tiles',
            icon: 'fa-th-large',
            color: '#45b7d1',
            type: 'item',
            parent_id: 103,
            qualification_id: 1,
            category: 'flooring'
         },
         {
            id: 132,
            name: 'Hardwood',
            description: 'Hardwood flooring',
            icon: 'fa-tree',
            color: '#8b4513',
            type: 'item',
            parent_id: 103,
            qualification_id: 1,
            category: 'flooring'
         },

         // Curtain Products (parent_id: null)
         {
            id: 201,
            name: 'Blinds',
            description: 'Window blinds and shades',
            icon: 'fa-grip-lines',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
            type: 'curtains',
            parent_id: null,
            qualification_id: 2
         },
         {
            id: 202,
            name: 'Chiffon',
            description: 'Sheer chiffon curtains',
            icon: 'fa-scroll',
            color: 'linear-gradient(135deg, #f72585, #b5179e)',
            type: 'curtains',
            parent_id: null,
            qualification_id: 2
         },
         {
            id: 203,
            name: 'Main Curtains',
            description: 'Primary curtain panels',
            icon: 'fa-window-restore',
            color: 'linear-gradient(135deg, #4361ee, #3a0ca3)',
            type: 'curtains',
            parent_id: null,
            qualification_id: 2
         },
         {
            id: 204,
            name: 'Main Curtains with Blinds',
            description: 'Curtains with integrated blinds',
            icon: 'fa-layer-group',
            color: 'linear-gradient(135deg, #4cc9f0, #4895ef)',
            type: 'curtains',
            parent_id: null,
            qualification_id: 2
         },
         {
            id: 205,
            name: 'Main Curtains with Chiffon',
            description: 'Curtains with chiffon overlay',
            icon: 'fa-layer-group',
            color: 'linear-gradient(135deg, #f72585, #7209b7)',
            type: 'curtains',
            parent_id: null,
            qualification_id: 2
         },
         {
            id: 206,
            name: 'Main Curtains With Blind and Chiffon',
            description: 'Complete window treatment system',
            icon: 'fa-layer-group',
            color: 'linear-gradient(135deg, #3a0ca3, #4361ee)',
            type: 'curtains',
            parent_id: null,
            qualification_id: 2
         },

         // Beds (parent product)
         {
            id: 301,
            name: 'Beds',
            description: 'Bed frames, mattresses, and bedroom furniture',
            icon: 'fa-bed',
            color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)',
            type: 'simple',
            hasVariants: true,
            variantType: 'size',
            parent_id: null,
            qualification_id: 3
         },
         // Bed Variants (children with parent_id)
         {
            id: 302,
            name: 'Single Bed',
            description: 'Single Bed 90x190cm',
            icon: 'fa-bed',
            color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)',
            type: 'simple',
            parent_id: 301,
            qualification_id: 3,
            basePrice: 299.00
         },
         {
            id: 303,
            name: 'Double Bed',
            description: 'Double Bed 140x190cm',
            icon: 'fa-bed',
            color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)',
            type: 'simple',
            parent_id: 301,
            qualification_id: 3,
            basePrice: 399.00
         },
         {
            id: 304,
            name: 'Queen Bed',
            description: 'Queen Bed 160x200cm',
            icon: 'fa-bed',
            color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)',
            type: 'simple',
            parent_id: 301,
            qualification_id: 3,
            basePrice: 499.00
         },

         // Dining Sets (parent product)
         {
            id: 401,
            name: 'Dining Sets',
            description: 'Complete dining room furniture sets',
            icon: 'fa-utensils',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            type: 'simple',
            hasVariants: true,
            variantType: 'set',
            parent_id: null,
            qualification_id: 4
         },
         // Dining Set Variants (children with parent_id)
         {
            id: 402,
            name: 'Basic Dining Set',
            description: 'Basic 6-seater dining set',
            icon: 'fa-utensils',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            type: 'simple',
            parent_id: 401,
            qualification_id: 4,
            basePrice: 899.00
         },
         {
            id: 403,
            name: 'Premium Dining Set',
            description: 'Premium 8-seater dining set',
            icon: 'fa-utensils',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            type: 'simple',
            parent_id: 401,
            qualification_id: 4,
            basePrice: 1499.00
         },
         {
            id: 404,
            name: 'Luxury Dining Set',
            description: 'Luxury dining set with extras',
            icon: 'fa-utensils',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            type: 'simple',
            parent_id: 401,
            qualification_id: 4,
            basePrice: 2299.00
         },

         // Sofa Sets (parent product)
         {
            id: 501,
            name: 'Sofa Sets',
            description: 'Living room sofa and furniture sets',
            icon: 'fa-couch',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
            type: 'simple',
            hasVariants: true,
            variantType: 'set',
            parent_id: null,
            qualification_id: 5
         },
         // Sofa Set Variants (children with parent_id)
         {
            id: 502,
            name: '3-Piece Sofa Set',
            description: '3-piece sofa set',
            icon: 'fa-couch',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
            type: 'simple',
            parent_id: 501,
            qualification_id: 5,
            basePrice: 1299.00
         },
         {
            id: 503,
            name: '4-Piece Sofa Set',
            description: '4-piece sofa set with coffee table',
            icon: 'fa-couch',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
            type: 'simple',
            parent_id: 501,
            qualification_id: 5,
            basePrice: 1899.00
         },

         // Wardrobes (parent product)
         {
            id: 601,
            name: 'Wardrobes',
            description: 'Bedroom wardrobes and storage solutions',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
            type: 'simple',
            hasVariants: true,
            variantType: 'size',
            parent_id: null,
            qualification_id: 6
         },

         // Wardrobe Variants (children with parent_id)
         {
            id: 602,
            name: 'Small Wardrobe',
            description: 'Small Wardrobe 120x200cm',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
            type: 'simple',
            parent_id: 601,
            qualification_id: 6,
            basePrice: 399.00
         },
         {
            id: 603,
            name: 'Medium Wardrobe',
            description: 'Medium Wardrobe 180x200cm',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
            type: 'simple',
            parent_id: 601,
            qualification_id: 6,
            basePrice: 599.00
         },
         {
            id: 604,
            name: 'Large Wardrobe',
            description: 'Large Wardrobe 240x200cm',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
            type: 'simple',
            parent_id: 601,
            qualification_id: 6,
            basePrice: 799.00
         },
         // Armchair (single product)
         {
            id: 605,
            name: 'Armchair',
            description: 'Armchair solutions',
            icon: 'fa-archive',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
            type: 'simple',
            hasVariants: false,
            variantType: 'set',
            parent_id: null,
            qualification_id: 7
         }
      ];

      // Material Categories Data
      const materialCategories = [{
            id: 'metal',
            name: 'Metal',
            description: 'Steel, aluminum, brass, and other metal materials',
            icon: 'fa-hammer',
            color: 'linear-gradient(135deg, #6c757d, #495057)',
            defaultMaterials: [{
                  id: 'steel',
                  name: 'Steel',
                  description: 'Carbon steel material'
               },
               {
                  id: 'aluminum',
                  name: 'Aluminum',
                  description: 'Lightweight aluminum'
               },
               {
                  id: 'brass',
                  name: 'Brass',
                  description: 'Brass alloy material'
               },
               {
                  id: 'copper',
                  name: 'Copper',
                  description: 'Copper material'
               }
            ]
         },
         {
            id: 'wood-wallpaper',
            name: 'Wood & Wallpaper',
            description: 'Wood materials and wall covering papers',
            icon: 'fa-tree',
            color: 'linear-gradient(135deg, #8b4513, #a0522d)',
            defaultMaterials: [{
                  id: 'oak',
                  name: 'Oak Wood',
                  description: 'Solid oak wood'
               },
               {
                  id: 'pine',
                  name: 'Pine Wood',
                  description: 'Pine wood material'
               },
               {
                  id: 'vinyl-wallpaper',
                  name: 'Vinyl Wallpaper',
                  description: 'Vinyl wall covering'
               },
               {
                  id: 'fabric-wallpaper',
                  name: 'Fabric Wallpaper',
                  description: 'Fabric-based wallpaper'
               }
            ]
         },
         {
            id: 'marble',
            name: 'Marble',
            description: 'Natural and engineered marble stones',
            icon: 'fa-gem',
            color: 'linear-gradient(135deg, #c0c0c0, #a9a9a9)',
            defaultMaterials: [{
                  id: 'carrara',
                  name: 'Carrara Marble',
                  description: 'White Carrara marble'
               },
               {
                  id: 'calacatta',
                  name: 'Calacatta Marble',
                  description: 'Luxury Calacatta marble'
               },
               {
                  id: 'engineered-marble',
                  name: 'Engineered Marble',
                  description: 'Composite marble'
               },
               {
                  id: 'travertine',
                  name: 'Travertine',
                  description: 'Natural travertine stone'
               }
            ]
         },
         {
            id: 'glass',
            name: 'Glass',
            description: 'Various types of glass materials',
            icon: 'fa-wine-glass',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            defaultMaterials: [{
                  id: 'clear-glass',
                  name: 'Clear Glass',
                  description: 'Transparent glass'
               },
               {
                  id: 'tinted-glass',
                  name: 'Tinted Glass',
                  description: 'Colored glass'
               },
               {
                  id: 'tempered-glass',
                  name: 'Tempered Glass',
                  description: 'Safety tempered glass'
               },
               {
                  id: 'frosted-glass',
                  name: 'Frosted Glass',
                  description: 'Frosted finish glass'
               }
            ]
         },
         {
            id: 'fabric-rope',
            name: 'Fabric & Rope',
            description: 'Textiles, fabrics, and rope materials',
            icon: 'fa-scroll',
            color: 'linear-gradient(135deg, #ff6b6b, #ee5a52)',
            defaultMaterials: [{
                  id: 'cotton',
                  name: 'Cotton Fabric',
                  description: 'Natural cotton material'
               },
               {
                  id: 'polyester',
                  name: 'Polyester Fabric',
                  description: 'Synthetic polyester'
               },
               {
                  id: 'nylon-rope',
                  name: 'Nylon Rope',
                  description: 'Strong nylon rope'
               },
               {
                  id: 'hemp-rope',
                  name: 'Hemp Rope',
                  description: 'Natural hemp rope'
               }
            ]
         },
         {
            id: 'pillow',
            name: 'Pillow',
            description: 'Pillow materials and components',
            icon: 'fa-couch',
            color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
            subcategories: [{
                  id: 'default-pillow',
                  name: 'Default Pillow',
                  description: 'Standard pillow material',
                  icon: 'fa-cube'
               },
               {
                  id: 'pillow-face',
                  name: 'Pillow Face',
                  description: 'Pillow front surface material',
                  icon: 'fa-square'
               },
               {
                  id: 'pillow-back',
                  name: 'Pillow Back',
                  description: 'Pillow back surface material',
                  icon: 'fa-square'
               },
               {
                  id: 'piping',
                  name: 'Piping',
                  description: 'Pillow piping and edges',
                  icon: 'fa-grip-lines'
               }
            ],
            defaultMaterials: [{
                  id: 'memory-foam',
                  name: 'Memory Foam',
                  description: 'Comfort memory foam'
               },
               {
                  id: 'poly-fill',
                  name: 'Polyester Fill',
                  description: 'Synthetic pillow filling'
               },
               {
                  id: 'feathers',
                  name: 'Feathers',
                  description: 'Natural feather filling'
               },
               {
                  id: 'cotton-cover',
                  name: 'Cotton Cover',
                  description: 'Cotton pillow cover'
               }
            ]
         }
      ];

      // Curtain accessory options
      const curtainAccessories = [{
            id: 'side-holder',
            name: 'Side Holder',
            description: 'Curtain side holders and accessories',
            icon: 'fa-grip-lines-vertical',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
            options: [{
                  id: 'holder1',
                  name: 'Classic Side Holder',
                  description: 'Traditional side holder design',
                  image: 'holder1.jpg'
               },
               {
                  id: 'holder2',
                  name: 'Modern Side Holder',
                  description: 'Contemporary side holder design',
                  image: 'holder2.jpg'
               },
               {
                  id: 'holder3',
                  name: 'Decorative Side Holder',
                  description: 'Ornamental side holder design',
                  image: 'holder3.jpg'
               }
            ]
         },
         {
            id: 'black-out',
            name: 'Black Out',
            description: 'Black out lining and accessories',
            icon: 'fa-moon',
            color: 'linear-gradient(135deg, #2b2d42, #1d1e2c)',
            options: [{
                  id: 'blackout1',
                  name: 'Standard Black Out',
                  description: 'Basic black out lining',
                  image: 'blackout1.jpg'
               },
               {
                  id: 'blackout2',
                  name: 'Thermal Black Out',
                  description: 'Insulated black out lining',
                  image: 'blackout2.jpg'
               },
               {
                  id: 'blackout3',
                  name: 'Premium Black Out',
                  description: 'Luxury black out lining',
                  image: 'blackout3.jpg'
               }
            ]
         }
      ];

      // Helper functions to get products by qualification
      function getProductsByQualification(qualificationId) {
         return products.filter(product =>
            product.qualification_id === qualificationId && product.parent_id === null
         );
      }

      function getProductVariants(parentProductId) {
         return products.filter(product => product.parent_id === parentProductId && product.type !== 'item');
      }

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

      // Function to check if a product has variants
      function productHasVariants(productId) {
         const product = products.find(p => p.id === productId);
         return product ? product.hasVariants : false;
      }

      // Helper functions to detect variant type
      function isSizeVariantProduct(productId) {
         const product = products.find(p => p.id === productId);
         return product && product.variantType === 'size';
      }

      function isSetVariantProduct(productId) {
         const product = products.find(p => p.id === productId);
         return product && product.variantType === 'set';
      }

      function productHasItems(productId) {
         return getProductItems(productId).length > 0;
      }

      // Function to get product variant type
      function getProductVariantType(productId) {
         const product = products.find(p => p.id === productId);
         return product ? product.variantType : null;
      }

      // Function to get variants for a product
      function getVariants(productId) {
         return getProductVariants(productId);
      }

      // Function to create variants tabs for a product
      function createVariantsTabs(productId, variants, roomId) {
         return `
    <div class="product-variants-section" id="variants-section-${productId}-room${roomId}">
        <div class="product-variants-tabs" id="variants-tabs-${productId}-room${roomId}">
            ${variants.map((variant, index) => `
                <button class="product-variant-tab ${index === 0 ? 'active' : ''}" 
                        data-variant="${variant.id}" data-product="${productId}" data-room="${roomId}">
                    <div class="product-variant-header">
                        <span class="status-indicator status-empty"></span>
                        <span class="product-variant-title">${variant.name}</span>
                    </div>
                </button>
            `).join('')}
        </div>
        <div class="product-variants-content" id="variants-content-${productId}-room${roomId}">
            ${variants.map((variant, index) => `
                <div class="product-variant-content ${index === 0 ? 'active' : ''}" 
                     id="variant-${productId}-${variant.id}-room${roomId}">
                    ${createVariantContent(productId, variant, roomId)}
                </div>
            `).join('')}
        </div>
    </div>
    `;
      }

      // Function to create standard material content (non-pillow categories)
      function createStandardMaterialContent(productId, variant, category, roomId) {
         return `
        <div class="material-inputs-compact">
            <div class="material-compact-image">
                <i class="fa fa-image"></i>
            </div>
            <div class="material-compact-fields">
                <div class="material-input">
                    <label>Material Grade</label>
                    <select class="form-control material-grade" 
                            data-variant="${variant.id}" data-category="${category.id}">
                        <option value="">Select Grade</option>
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                        <option value="economy">Economy</option>
                    </select>
                </div>
                <div class="material-input">
                    <label>Material Type</label>
                    <select class="form-control material-type-select" 
                            data-variant="${variant.id}" data-category="${category.id}">
                        <option value="">Select Material</option>
                        ${category.defaultMaterials.map(material => `
                            <option value="${material.id}">${material.name}</option>
                        `).join('')}
                    </select>
                </div>
                <div class="material-input">
                    <label>Area/Weight</label>
                    <input type="text" class="form-control area-weight" 
                           placeholder="Enter area or weight"
                           data-variant="${variant.id}" data-category="${category.id}">
                </div>
            </div>
        </div>
    `;
      }
      // Function to create content for variants (both size and set)
      function createVariantContent(productId, variant, roomId) {
         return `
        <div class="variant-details">
            <div class="compact-product-details">
                <div class="compact-section-header">
                    <h6><i class="fa fa-cube mr-2"></i>${variant.name} - ${variant.description}</h6>
                </div>
                <div class="compact-details-with-image">
                    <div class="compact-image-preview">
                        <i class="fa fa-image"></i>
                    </div>
                    <div class="compact-details-fields">
                        <div class="compact-detail-group">
                            <label>Width (m)</label>
                            <input type="number" class="form-control dimension-width" 
                                   placeholder="0.00" step="0.01" min="0" 
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Length (m)</label>
                            <input type="number" class="form-control dimension-length" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Height (m)</label>
                            <input type="number" class="form-control dimension-height" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Unit Price ($)</label>
                            <input type="number" class="form-control unit-price" 
                                   value="${variant.basePrice.toFixed(2)}" step="0.01" min="0" readonly
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Total Price ($)</label>
                            <input type="number" class="form-control total-price" 
                                   placeholder="0.00" step="0.01" min="0" readonly
                                   data-variant="${variant.id}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Material Section for Variant - Pillow subcategories will be shown only when Pillow tab is active -->
            <div class="material-section">
                <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${variant.name}</h6>
                <div class="material-tabs" id="materialTabs-${productId}-${variant.id}-room${roomId}">
                    ${materialCategories.map(category => `
                        <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" 
                                data-category="${category.id}">
                            ${category.name}
                        </button>
                    `).join('')}
                </div>
                <div class="material-tabs-content" id="materialTabsContent-${productId}-${variant.id}-room${roomId}">
                    ${materialCategories.map((category, index) => `
                        <div class="material-tab-content ${index === 0 ? 'active' : ''}" 
                             id="materialContent-${productId}-${variant.id}-room${roomId}-${category.id}">
                            ${category.id === 'pillow' ? 
                                createPillowSubcategoriesForVariant(productId, variant, roomId) : 
                                createStandardMaterialContent(productId, variant, category, roomId)
                            }
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
      }

      // Function to create pillow subcategories for variant
      function createPillowSubcategoriesForVariant(productId, variant, roomId) {
         const pillowCategory = materialCategories.find(cat => cat.id === 'pillow');
         if (!pillowCategory) return '';

         return `
        <div class="pillow-subcategories-section">
            <div class="pillow-subcategories-tabs" id="pillowTabs-${productId}-${variant.id}-room${roomId}">
                ${pillowCategory.subcategories.map((subcat, index) => `
                    <button class="pillow-subcategory-tab ${index === 0 ? 'active' : ''}" 
                            data-subcategory="${subcat.id}" data-product="${productId}" data-variant="${variant.id}">
                        <div class="pillow-subcategory-header">
                            <span class="status-indicator status-empty"></span>
                            <span class="pillow-subcategory-title">${subcat.name}</span>
                        </div>
                    </button>
                `).join('')}
            </div>
            <div class="pillow-subcategories-content" id="pillowContent-${productId}-${variant.id}-room${roomId}">
                ${pillowCategory.subcategories.map((subcat, index) => `
                    <div class="pillow-subcategory-content ${index === 0 ? 'active' : ''}" 
                         id="pillowSubcategory-${productId}-${variant.id}-room${roomId}-${subcat.id}">
                        <div class="pillow-subcategory-details">
                            <div class="pillow-material-inputs-compact">
                                <div class="pillow-material-compact-image">
                                    <i class="fa fa-image"></i>
                                </div>
                                <div class="pillow-material-compact-fields">
                                    <div class="pillow-material-input">
                                        <label>Material Grade</label>
                                        <select class="form-control material-grade" 
                                                data-subcategory="${subcat.id}" data-variant="${variant.id}">
                                            <option value="">Select Grade</option>
                                            <option value="standard">Standard</option>
                                            <option value="premium">Premium</option>
                                            <option value="economy">Economy</option>
                                        </select>
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Material Type</label>
                                        <select class="form-control material-type-select" 
                                                data-subcategory="${subcat.id}" data-variant="${variant.id}">
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
                                               data-subcategory="${subcat.id}" data-variant="${variant.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control pillow-qty" 
                                               placeholder="0" min="1" value="1"
                                               data-subcategory="${subcat.id}" data-variant="${variant.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Unit Price ($)</label>
                                        <input type="number" class="form-control pillow-unit-price" 
                                               placeholder="0.00" step="0.01" min="0"
                                               data-subcategory="${subcat.id}" data-variant="${variant.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Total Price ($)</label>
                                        <input type="number" class="form-control pillow-total-price" 
                                               placeholder="0.00" step="0.01" min="0" readonly
                                               data-subcategory="${subcat.id}" data-variant="${variant.id}">
                                    </div>
                                </div>
                            </div>
                            <div class="pillow-material-input" style="margin-top: 12px; grid-column: 1 / -1;">
                                <label>Additional Notes</label>
                                <textarea class="form-control pillow-notes" 
                                          placeholder="Enter additional notes for ${subcat.name}..." 
                                          rows="2" data-subcategory="${subcat.id}" data-variant="${variant.id}"></textarea>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }

      // Function to create content for size variants
      function createSizeVariantContent(productId, variant, roomId) {
         return `
        <div class="variant-details">
            <div class="compact-product-details">
                <div class="compact-section-header">
                    <h6><i class="fa fa-cube mr-2"></i>${variant.name} - ${variant.description}</h6>
                </div>
                <div class="compact-details-with-image">
                    <div class="compact-image-preview">
                        <i class="fa fa-image"></i>
                    </div>
                    <div class="compact-details-fields">
                        <div class="compact-detail-group">
                            <label>Width (m)</label>
                            <input type="number" class="form-control dimension-width" 
                                   placeholder="0.00" step="0.01" min="0" 
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Length (m)</label>
                            <input type="number" class="form-control dimension-length" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Height (m)</label>
                            <input type="number" class="form-control dimension-height" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Unit Price ($)</label>
                            <input type="number" class="form-control unit-price" 
                                   placeholder="0.00" step="0.01" min="0" readonly
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Total Price ($)</label>
                            <input type="number" class="form-control total-price" 
                                   placeholder="0.00" step="0.01" min="0" readonly
                                   data-variant="${variant.id}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Material Section for Size Variant -->
            <div class="material-section">
                <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${variant.name}</h6>
                <div class="material-tabs" id="materialTabs-${productId}-${variant.id}-room${roomId}">
                    ${materialCategories.map(category => `
                        <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" 
                                data-category="${category.id}">
                            ${category.name}
                        </button>
                    `).join('')}
                </div>
                <div class="material-tabs-content" id="materialTabsContent-${productId}-${variant.id}-room${roomId}">
                    ${materialCategories.map((category, index) => `
                        <div class="material-tab-content ${index === 0 ? 'active' : ''}" 
                             id="materialContent-${productId}-${variant.id}-room${roomId}-${category.id}">
                            <div class="material-inputs-compact">
                                <div class="material-compact-image">
                                    <i class="fa fa-image"></i>
                                </div>
                                <div class="material-compact-fields">
                                    <div class="material-input">
                                        <label>Material Grade</label>
                                        <select class="form-control material-grade" 
                                                data-variant="${variant.id}">
                                            <option value="">Select Grade</option>
                                            <option value="standard">Standard</option>
                                            <option value="premium">Premium</option>
                                            <option value="economy">Economy</option>
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Material Type</label>
                                        <select class="form-control material-type-select" 
                                                data-variant="${variant.id}">
                                            <option value="">Select Material</option>
                                            ${category.defaultMaterials.map(material => `
                                                <option value="${material.id}">${material.name}</option>
                                            `).join('')}
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Area/Weight</label>
                                        <input type="text" class="form-control area-weight" 
                                               placeholder="Enter area or weight"
                                               data-variant="${variant.id}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
      }

      // Function to create content for individual variants (size variants)
      function createIndividualVariantContent(productId, variant, roomId) {
         return `
        <div class="variant-details">
            <div class="compact-product-details">
                <div class="compact-section-header">
                    <h6><i class="fa fa-cube mr-2"></i>${variant.name} Details</h6>
                    <button type="button" class="btn btn-sm remove-variant-btn"
                            data-product="${productId}" data-variant="${variant.id}" data-room="${roomId}">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="compact-details-with-image">
                    <div class="compact-image-preview">
                        <i class="fa fa-image"></i>
                    </div>
                    <div class="compact-details-fields">
                        <div class="compact-detail-group">
                            <label>Width (m)</label>
                            <input type="number" class="form-control dimension-width" 
                                   placeholder="0.00" step="0.01" min="0" 
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Length (m)</label>
                            <input type="number" class="form-control dimension-length" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Height (m)</label>
                            <input type="number" class="form-control dimension-height" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Description</label>
                            <input type="text" class="form-control variant-description" 
                                   value="${variant.description}" 
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Unit Price ($)</label>
                            <input type="number" class="form-control unit-price" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-variant="${variant.id}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Total Price ($)</label>
                            <input type="number" class="form-control total-price" 
                                   placeholder="0.00" step="0.01" min="0" readonly
                                   data-variant="${variant.id}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Material Section for Individual Variant -->
            <div class="material-section">
                <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${variant.name}</h6>
                <div class="material-tabs" id="materialTabs-${productId}-${variant.id}-room${roomId}">
                    ${materialCategories.map(category => `
                        <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" 
                                data-category="${category.id}">
                            ${category.name}
                        </button>
                    `).join('')}
                </div>
                <div class="material-tabs-content" id="materialTabsContent-${productId}-${variant.id}-room${roomId}">
                    ${materialCategories.map((category, index) => `
                        <div class="material-tab-content ${index === 0 ? 'active' : ''}" 
                             id="materialContent-${productId}-${variant.id}-room${roomId}-${category.id}">
                            <div class="material-inputs-compact">
                                <div class="material-compact-image">
                                    <i class="fa fa-image"></i>
                                </div>
                                <div class="material-compact-fields">
                                    <div class="material-input">
                                        <label>Material Grade</label>
                                        <select class="form-control material-grade" 
                                                data-variant="${variant.id}">
                                            <option value="">Select Grade</option>
                                            <option value="standard">Standard</option>
                                            <option value="premium">Premium</option>
                                            <option value="economy">Economy</option>
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Material Type</label>
                                        <select class="form-control material-type-select" 
                                                data-variant="${variant.id}">
                                            <option value="">Select Material</option>
                                            ${category.defaultMaterials.map(material => `
                                                <option value="${material.id}">${material.name}</option>
                                            `).join('')}
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Area/Weight</label>
                                        <input type="text" class="form-control area-weight" 
                                               placeholder="Enter area or weight"
                                               data-variant="${variant.id}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
      }

      // Function to create content for set variants
      function createSetVariantContent(productId, variant, roomId) {
         return `
        <div class="set-variant-content">
            <div class="set-variant-header">
                <div class="set-variant-name">${variant.name} - ${variant.description}</div>
            </div>
            
            <!-- Set Products Tabs -->
            <div class="set-products-tabs" id="set-products-tabs-${productId}-${variant.id}-room${roomId}">
                ${variant.products.map((product, index) => `
                    <button class="set-product-tab ${index === 0 ? 'active' : ''}" 
                            data-product="${product.id}" data-variant="${variant.id}" data-set="${productId}">
                        <div class="product-variant-header">
                            <span class="status-indicator status-empty"></span>
                            <span class="product-variant-title">${product.name}</span>
                        </div>
                    </button>
                `).join('')}
            </div>
            
            <!-- Set Products Content -->
            <div class="set-products-content" id="set-products-content-${productId}-${variant.id}-room${roomId}">
                ${variant.products.map((product, index) => `
                    <div class="set-product-content ${index === 0 ? 'active' : ''}" 
                         id="set-product-${productId}-${variant.id}-${product.id}-room${roomId}">
                        ${createSetProductContent(productId, variant.id, product, roomId)}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }
      // Function to create individual product content within a set
      function createSetProductContent(productId, variantId, product, roomId) {
         const quantityField = product.quantity ? `
        <div class="compact-detail-group">
            <label>Quantity</label>
            <input type="number" class="form-control product-quantity" 
                   value="${product.quantity}" min="1" readonly
                   data-product="${product.id}" data-variant="${variantId}">
        </div>
    ` : '';

         return `
        <div class="set-product-item">
            <div class="compact-product-details">
                <div class="compact-section-header">
                    <h6><i class="fa fa-cube mr-2"></i>${product.name}</h6>
                </div>
                <div class="compact-details-with-image">
                    <div class="compact-image-preview">
                        <i class="fa fa-image"></i>
                    </div>
                    <div class="compact-details-fields">
                        <div class="compact-detail-group">
                            <label>Width (m)</label>
                            <input type="number" class="form-control dimension-width" 
                                   placeholder="0.00" step="0.01" min="0" 
                                   data-product="${product.id}" data-variant="${variantId}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Length (m)</label>
                            <input type="number" class="form-control dimension-length" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-product="${product.id}" data-variant="${variantId}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Height (m)</label>
                            <input type="number" class="form-control dimension-height" 
                                   placeholder="0.00" step="0.01" min="0"
                                   data-product="${product.id}" data-variant="${variantId}">
                        </div>
                        ${quantityField}
                        <div class="compact-detail-group">
                            <label>Unit Price ($)</label>
                            <input type="number" class="form-control unit-price" 
                                   value="${product.basePrice.toFixed(2)}" step="0.01" min="0" readonly
                                   data-product="${product.id}" data-variant="${variantId}">
                        </div>
                        <div class="compact-detail-group">
                            <label>Total Price ($)</label>
                            <input type="number" class="form-control total-price" 
                                   placeholder="0.00" step="0.01" min="0" readonly
                                   data-product="${product.id}" data-variant="${variantId}">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Material Section for Individual Product in Set -->
            <div class="material-section">
                <h6><i class="fa fa-layer-group mr-2"></i>Material Selection for ${product.name}</h6>
                <div class="material-tabs" id="materialTabs-${productId}-${variantId}-${product.id}-room${roomId}">
                    ${materialCategories.map(category => `
                        <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" 
                                data-category="${category.id}">
                            ${category.name}
                        </button>
                    `).join('')}
                </div>
                <div class="material-tabs-content" id="materialTabsContent-${productId}-${variantId}-${product.id}-room${roomId}">
                    ${materialCategories.map((category, index) => `
                        <div class="material-tab-content ${index === 0 ? 'active' : ''}" 
                             id="materialContent-${productId}-${variantId}-${product.id}-room${roomId}-${category.id}">
                            <div class="material-inputs-compact">
                                <div class="material-compact-image">
                                    <i class="fa fa-image"></i>
                                </div>
                                <div class="material-compact-fields">
                                    <div class="material-input">
                                        <label>Material Grade</label>
                                        <select class="form-control material-grade" 
                                                data-product="${product.id}" data-variant="${variantId}">
                                            <option value="">Select Grade</option>
                                            <option value="standard">Standard</option>
                                            <option value="premium">Premium</option>
                                            <option value="economy">Economy</option>
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Material Type</label>
                                        <select class="form-control material-type-select" 
                                                data-product="${product.id}" data-variant="${variantId}">
                                            <option value="">Select Material</option>
                                            ${category.defaultMaterials.map(material => `
                                                <option value="${material.id}">${material.name}</option>
                                            `).join('')}
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Area/Weight</label>
                                        <input type="text" class="form-control area-weight" 
                                               placeholder="Enter area or weight"
                                               data-product="${product.id}" data-variant="${variantId}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
      }

      // Function to setup variants tabs functionality
      function setupVariantsTabs(productId, roomId) {
         const $variantsTabs = $(`#variants-tabs-${productId}-room${roomId}`);
         const $variantsContent = $(`#variants-content-${productId}-room${roomId}`);

         if (!$variantsTabs.length) return;

         // Main variant tabs click handler
         $variantsTabs.find('.product-variant-tab').on('click', function(e) {
            e.preventDefault();
            const variantId = $(this).data('variant');

            // Deactivate all tabs and content
            $variantsTabs.find('.product-variant-tab').removeClass('active');
            $variantsContent.find('.product-variant-content').removeClass('active');

            // Activate current tab and content
            $(this).addClass('active');
            $(`#variant-${productId}-${variantId}-room${roomId}`).addClass('active');

            updateVariantStatus(productId, roomId, variantId);
         });

         // Setup calculations for variants
         setupVariantCalculations(productId, roomId);

         // Setup material tabs for variants
         setupVariantMaterialTabs(productId, roomId);

         // Setup pillow subcategory tabs for variants
         setupVariantPillowSubcategoryTabs(productId, roomId);

         // Activate the first tab by default
         const $firstTab = $variantsTabs.find('.product-variant-tab').first();
         if ($firstTab.length) {
            $firstTab.trigger('click');
         }
      }

      // Function to setup variant pillow subcategory tabs
      function setupVariantPillowSubcategoryTabs(productId, roomId) {
         const variants = getVariants(productId);

         variants.forEach(variant => {
            const pillowTabsId = `pillowTabs-${productId}-${variant.id}-room${roomId}`;
            const $pillowTabs = $(`#${pillowTabsId}`);
            const $pillowContent = $(`#pillowContent-${productId}-${variant.id}-room${roomId}`);

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
               $(`#pillowSubcategory-${productId}-${variant.id}-room${roomId}-${subcategoryId}`).addClass('active');

               updatePillowSubcategoryStatus(productId, variant.id, subcategoryId, roomId);
            });

            // Setup price calculations for pillow subcategories
            $(`#pillowContent-${productId}-${variant.id}-room${roomId} .pillow-qty, #pillowContent-${productId}-${variant.id}-room${roomId} .pillow-unit-price`).on('input', function() {
               const subcategoryId = $(this).data('subcategory');
               updatePillowSubcategoryStatus(productId, variant.id, subcategoryId, roomId);
            });

            // Activate the first tab by default
            const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
            if ($firstTab.length) {
               $firstTab.trigger('click');
            }
         });
      }

      // Function to setup set product tabs
      function setupSetProductTabs(productId, roomId) {
         const variants = getVariants(productId);

         variants.forEach(variant => {
            if (variant.products) {
               const $setTabs = $(`#set-products-tabs-${productId}-${variant.id}-room${roomId}`);
               const $setContent = $(`#set-products-content-${productId}-${variant.id}-room${roomId}`);

               if ($setTabs.length) {
                  $setTabs.find('.set-product-tab').on('click', function(e) {
                     e.preventDefault();
                     const productIdAttr = $(this).data('product');

                     // Deactivate all tabs and content
                     $setTabs.find('.set-product-tab').removeClass('active');
                     $setContent.find('.set-product-content').removeClass('active');

                     // Activate current tab and content
                     $(this).addClass('active');
                     $(`#set-product-${productId}-${variant.id}-${productIdAttr}-room${roomId}`).addClass('active');

                     updateSetProductStatus(productId, variant.id, productIdAttr, roomId);
                  });

                  // Activate first set product tab
                  const $firstSetTab = $setTabs.find('.set-product-tab').first();
                  if ($firstSetTab.length) {
                     $firstSetTab.trigger('click');
                  }
               }
            }
         });
      }

      // Function to setup variant calculations
      function setupVariantCalculations(productId, roomId) {
         // Dimension calculations for variants
         $(`#variants-content-${productId}-room${roomId} .dimension-width, #variants-content-${productId}-room${roomId} .dimension-length`).on('input', function() {
            const variantId = $(this).data('variant');
            const width = parseFloat($(`#variants-content-${productId}-room${roomId} .dimension-width[data-variant="${variantId}"]`).val()) || 0;
            const length = parseFloat($(`#variants-content-${productId}-room${roomId} .dimension-length[data-variant="${variantId}"]`).val()) || 0;
            const unitPrice = parseFloat($(`#variants-content-${productId}-room${roomId} .unit-price[data-variant="${variantId}"]`).val()) || 0;
            const area = width * length;
            const totalPrice = unitPrice * area;

            $(`#variants-content-${productId}-room${roomId} .total-price[data-variant="${variantId}"]`).val(totalPrice.toFixed(2));

            updateVariantStatus(productId, roomId, variantId);
         });

         // Pillow subcategory calculations
         $(`#variants-content-${productId}-room${roomId} .pillow-qty, #variants-content-${productId}-room${roomId} .pillow-unit-price`).on('input', function() {
            const variantId = $(this).data('variant');
            const subcategoryId = $(this).data('subcategory');
            const qty = parseFloat($(`#variants-content-${productId}-room${roomId} .pillow-qty[data-subcategory="${subcategoryId}"][data-variant="${variantId}"]`).val()) || 0;
            const unitPrice = parseFloat($(`#variants-content-${productId}-room${roomId} .pillow-unit-price[data-subcategory="${subcategoryId}"][data-variant="${variantId}"]`).val()) || 0;
            const totalPrice = qty * unitPrice;

            $(`#variants-content-${productId}-room${roomId} .pillow-total-price[data-subcategory="${subcategoryId}"][data-variant="${variantId}"]`).val(totalPrice.toFixed(2));

            updatePillowSubcategoryStatus(productId, variantId, subcategoryId, roomId);
         });
      }

      // Function to setup variant material tabs
      function setupVariantMaterialTabs(productId, roomId) {
         const variants = getVariants(productId);

         variants.forEach(variant => {
            const materialTabsId = `materialTabs-${productId}-${variant.id}-room${roomId}`;

            setTimeout(() => {
               if ($(`#${materialTabsId}`).length) {
                  $(`#${materialTabsId} .material-tab`).off('click').on('click', function(e) {
                     e.preventDefault();
                     const categoryId = $(this).data('category');
                     const materialTabsContentId = `materialTabsContent-${productId}-${variant.id}-room${roomId}`;

                     $(`#${materialTabsId} .material-tab`).removeClass('active');
                     $(this).addClass('active');

                     $(`#${materialTabsContentId} .material-tab-content`).removeClass('active');
                     $(`#materialContent-${productId}-${variant.id}-room${roomId}-${categoryId}`).addClass('active');

                     updateVariantStatus(productId, roomId, variant.id);
                  });
               }
            }, 100);
         });
      }

      // Function to update set product status
      function updateSetProductStatus(productId, variantId, productIdAttr, roomId) {
         const $tab = $(`#set-products-tabs-${productId}-${variantId}-room${roomId} .set-product-tab[data-product="${productIdAttr}"]`);
         const $statusIndicator = $tab.find('.status-indicator');

         const $content = $(`#set-product-${productId}-${variantId}-${productIdAttr}-room${roomId}`);
         const width = $content.find('.dimension-width').val();
         const length = $content.find('.dimension-length').val();
         const materialGrade = $content.find('.material-grade').val();
         const materialType = $content.find('.material-type-select').val();

         $statusIndicator.removeClass('status-empty status-incomplete status-complete');

         if (!width && !length && !materialGrade && !materialType) {
            $statusIndicator.addClass('status-empty');
         } else if (width && length && materialGrade && materialType) {
            $statusIndicator.addClass('status-complete');
         } else {
            $statusIndicator.addClass('status-incomplete');
         }

         // Update parent set status
         updateVariantStatus(productId, roomId, variantId);
      }

      // Function to setup set product selection
      function setupSetProductSelection(productId, roomId) {
         const variants = getProductVariants(productId);

         variants.forEach(variant => {
            if (!variant.isIndividualProduct) {
               const selectionId = `product-selection-${productId}-${variant.id}`;

               $(`#${selectionId} .variant-product-option`).on('click', function() {
                  $(this).toggleClass('selected');

                  // Enable/disable add button based on selection
                  const hasSelection = $(`#${selectionId} .variant-product-option.selected`).length > 0;
                  $(`#variants-content-${productId}-room${roomId} .add-product-to-set-btn[data-variant="${variant.id}"]`).prop('disabled', !hasSelection);
               });
            }
         });
      }

      // Function to update variant status indicator
      function updateVariantStatus(productId, roomId, variantId) {
         let $statusIndicator;

         if (isSizeVariantProduct(productId)) {
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

         // Check main variant fields (SAME LOGIC FOR BOTH)
         const width = $content.find('.dimension-width').val();
         const length = $content.find('.dimension-length').val();

         // Check active material category (SAME LOGIC FOR BOTH)
         const activeMaterialTab = $content.find('.material-tab.active');
         const activeCategory = activeMaterialTab.data('category');

         let materialComplete = false;

         if (activeCategory === 'pillow') {
            // For pillow category (SAME LOGIC FOR BOTH)
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
            // For other material categories (SAME LOGIC FOR BOTH)
            const materialGrade = $content.find(`.material-grade[data-category="${activeCategory}"]`).val();
            const materialType = $content.find(`.material-type-select[data-category="${activeCategory}"]`).val();
            materialComplete = !!(materialGrade && materialType);
         }

         $statusIndicator.removeClass('status-empty status-incomplete status-complete');

         if (!width && !length && !materialComplete) {
            $statusIndicator.addClass('status-empty');
         } else if (width && length && materialComplete) {
            $statusIndicator.addClass('status-complete');
         } else {
            $statusIndicator.addClass('status-incomplete');
         }
      }

      // Function to add variants to product content
      function addVariantsToProduct(productId, roomId) {
         const $productContent = $(`#product-${productId}-room${roomId}`);

         // Check if product has variants
         if (productHasVariants(productId)) {
            const variantsHTML = createVariantsTabs(productId, roomId);

            // Remove existing variants section if any
            $productContent.find('.product-variants-section').remove();

            // Add variants section after the main product content
            $productContent.find('.simple-product-content').after(variantsHTML);

            // Setup variants functionality
            setupVariantsTabs(productId, roomId);
         }
      }

      // Event handlers for variant management
      $(document).on('click', '.add-variant-btn', function() {
         const productId = $(this).data('product');
         const roomId = $(this).data('room');
         const variantType = getProductVariantType(productId);

         // In a real implementation, this would open a modal to add a new variant
         alert(`Add new ${variantType} variant for ${productId} in room ${roomId}`);
      });

      $(document).on('click', '.manage-variants-btn', function() {
         const productId = $(this).data('product');
         const roomId = $(this).data('room');

         // In a real implementation, this would open a modal to manage variants
         alert(`Manage variants for ${productId} in room ${roomId}`);
      });

      $(document).on('click', '.remove-variant-btn', function() {
         const productId = $(this).data('product');
         const variantId = $(this).data('variant');
         const roomId = $(this).data('room');

         if (confirm('Are you sure you want to remove this variant?')) {
            // Remove variant tab and content
            $(`#variants-tabs-${productId}-room${roomId} .product-variant-tab[data-variant="${variantId}"]`).remove();
            $(`#variant-${productId}-${variantId}-room${roomId}`).remove();

            // Activate first remaining tab
            const $firstTab = $(`#variants-tabs-${productId}-room${roomId} .product-variant-tab`).first();
            if ($firstTab.length) {
               $firstTab.trigger('click');
            } else {
               // No variants left, show empty state
               $(`#variants-content-${productId}-room${roomId}`).html(`
                <div class="empty-variants-state">
                    <i class="fa fa-layer-group"></i>
                    <p>No variants added yet</p>
                </div>
            `);
            }
         }
      });

      $(document).on('click', '.remove-set-btn', function() {
         const productId = $(this).data('product');
         const variantId = $(this).data('variant');
         const roomId = $(this).data('room');

         if (confirm('Are you sure you want to remove this set?')) {
            // Remove set tab and content
            $(`#variants-tabs-${productId}-room${roomId} .product-variant-tab[data-variant="${variantId}"]`).remove();
            $(`#variant-${productId}-${variantId}-room${roomId}`).remove();

            // Activate first remaining tab
            const $firstTab = $(`#variants-tabs-${productId}-room${roomId} .product-variant-tab`).first();
            if ($firstTab.length) {
               $firstTab.trigger('click');
            }
         }
      });

      $(document).on('click', '.remove-product-from-set-btn', function(e) {
         e.preventDefault();
         const productId = $(this).data('product');
         const variantId = $(this).data('variant');
         const itemId = $(this).data('item');
         const roomId = $(this).closest('.product-variant-content').attr('id').split('-room')[1];

         if (confirm('Are you sure you want to remove this product from the set?')) {
            $(`#set-products-${productId}-${variantId}-room${roomId} .set-product-item[data-product="${itemId}"]`).remove();
            updateVariantStatus(productId, roomId, variantId);
         }
      });

      $(document).on('click', '.add-product-to-set-btn', function() {
         const productId = $(this).data('product');
         const variantId = $(this).data('variant');
         const roomId = $(this).data('room');
         const selectionId = `product-selection-${productId}-${variantId}`;

         // Get selected products
         const selectedProducts = $(`#${selectionId} .variant-product-option.selected`);

         selectedProducts.each(function() {
            const selectedProductId = $(this).data('product-id');
            const productData = availableProductsForSets.find(p => p.id === selectedProductId);

            if (productData) {
               // Create new product in set
               const newProduct = {
                  id: productData.id,
                  name: productData.name,
                  qualification: productData.qualification,
                  type: 'furniture',
                  quantity: 1
               };

               const productHTML = createSetProductContent(productId, variantId, newProduct, roomId);
               $(`#set-products-${productId}-${variantId}-room${roomId}`).append(productHTML);

               // Setup material tabs for new product
               setupVariantMaterialTabs(productId, roomId);

               // Setup calculations for new product
               setupVariantCalculations(productId, roomId);

               // Deselect the option
               $(this).removeClass('selected');
            }
         });

         // Disable add button after adding
         $(this).prop('disabled', true);

         updateVariantStatus(productId, roomId, variantId);
      });

      // Modified loadProductContent function to include variants
      function loadProductContentWithVariants(contentId, product) {
         const $content = $(`#${contentId}`);

         if (product.type === 'complex') {
            loadComplexProductContent($content, product);
         } else if (product.type === 'curtains') {
            loadCurtainProductContent($content, product);
         } else {
            loadSimpleProductContent($content, product);
         }

         // Add variants if the product has them
         const roomId = $content.closest('.tab-pane').data('room');
         addVariantsToProduct(product.id, roomId);

         // Setup pillow subcategories if the product has them
         setupPillowSubcategoryTabs(product.id);
      }


      // Fitout products (Wall, Ceiling, Ground)
      const fitoutProducts = [{
            id: 'wall',
            name: 'Wall',
            description: 'Wall construction and finishing',
            icon: 'fa-wall',
            color: 'linear-gradient(135deg, #ff6b6b, #ee5a52)',
            type: 'complex'
         },
         {
            id: 'ceiling',
            name: 'Ceiling',
            description: 'Ceiling systems and fixtures',
            icon: 'fa-border-all',
            color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
            type: 'complex'
         },
         {
            id: 'ground',
            name: 'Ground',
            description: 'Flooring and ground works',
            icon: 'fa-square',
            color: 'linear-gradient(135deg, #45b7d1, #4a7bd6)',
            type: 'complex'
         }
      ];

      // ADDED: Curtain products
      const curtainProducts = [{
            id: 'blinds',
            name: 'Blinds',
            description: 'Window blinds and shades',
            icon: 'fa-grip-lines',
            color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
            type: 'curtains'
         },
         {
            id: 'chiffon',
            name: 'Chiffon',
            description: 'Sheer chiffon curtains',
            icon: 'fa-scroll',
            color: 'linear-gradient(135deg, #f72585, #b5179e)',
            type: 'curtains'
         },
         {
            id: 'main-curtains',
            name: 'Main Curtains',
            description: 'Primary curtain panels',
            icon: 'fa-window-restore',
            color: 'linear-gradient(135deg, #4361ee, #3a0ca3)',
            type: 'curtains'
         },
         {
            id: 'main-curtains-blinds',
            name: 'Main Curtains with Blinds',
            description: 'Curtains with integrated blinds',
            icon: 'fa-layer-group',
            color: 'linear-gradient(135deg, #4cc9f0, #4895ef)',
            type: 'curtains'
         },
         {
            id: 'main-curtains-chiffon',
            name: 'Main Curtains with Chiffon',
            description: 'Curtains with chiffon overlay',
            icon: 'fa-layer-group',
            color: 'linear-gradient(135deg, #f72585, #7209b7)',
            type: 'curtains'
         },
         {
            id: 'main-curtains-blind-chiffon',
            name: 'Main Curtains With Blind and Chiffon',
            description: 'Complete window treatment system',
            icon: 'fa-layer-group',
            color: 'linear-gradient(135deg, #3a0ca3, #4361ee)',
            type: 'curtains'
         }
      ];

      // Initialize qualification modal
      function initializeQualificationModal() {
         const $optionsContainer = $('#qualificationOptions');
         $optionsContainer.empty();

         qualifications.forEach(qual => {
            const $option = $(`
            <div class="qualification-option" data-qualification="${qual.id}">
               <div class="qualification-option-header">
                  <div class="qualification-option-icon" style="background: ${qual.color};">
                     <i class="fa ${qual.icon}"></i>
                  </div>
                  <div class="qualification-option-name">${qual.name}</div>
               </div>
               <div class="qualification-option-description">${qual.description}</div>
            </div>
         `);
            $optionsContainer.append($option);
         });
      }

      // Initialize multi-select modal
      function initializeMultiSelectModal(qualification) {
         const $optionsContainer = $('#multiSelectOptions');
         $optionsContainer.empty();
         state.selectedProducts = [];

         // Get products for this qualification
         const productsToShow = getProductsByQualification(qualification.id);

         productsToShow.forEach(product => {
            const $option = $(`
            <div class="multi-select-option" data-product-id="${product.id}">
               <div class="multi-select-option-header">
                  <div class="multi-select-option-icon" style="background: ${product.color};">
                     <i class="fa ${product.icon}"></i>
                  </div>
                  <div class="multi-select-option-name">${product.name}</div>
               </div>
               <div class="multi-select-option-description">${product.description}</div>
            </div>
         `);
            $optionsContainer.append($option);
         });
      }

      // Search functionality for qualification modal
      function setupQualificationSearch() {
         $('#qualificationSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.qualification-option').each(function() {
               const $option = $(this);
               const name = $option.find('.qualification-option-name').text().toLowerCase();
               const description = $option.find('.qualification-option-description').text().toLowerCase();

               if (name.includes(searchTerm) || description.includes(searchTerm)) {
                  $option.show();
               } else {
                  $option.hide();
               }
            });
         });
      }

      // Search functionality for product modal
      function setupProductSearch() {
         $('#productSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.multi-select-option').each(function() {
               const $option = $(this);
               const name = $option.find('.multi-select-option-name').text().toLowerCase();
               const description = $option.find('.multi-select-option-description').text().toLowerCase();

               if (name.includes(searchTerm) || description.includes(searchTerm)) {
                  $option.show();
               } else {
                  $option.hide();
               }
            });
         });
      }

      // Search functionality for item modal
      function setupItemSearch() {
         $('#itemSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.item-option').each(function() {
               const $option = $(this);
               const name = $option.find('.item-option-name').text().toLowerCase();
               const description = $option.find('.item-option-description').text().toLowerCase();

               if (name.includes(searchTerm) || description.includes(searchTerm)) {
                  $option.show();
               } else {
                  $option.hide();
               }
            });
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
            const $tab = $(tab);
            const oldRoomId = $tab.attr('id').replace('-tab', '');
            const newRoomId = `room${roomNumber}`;

            // Update tab
            $tab.attr('id', `${newRoomId}-tab`);
            $tab.attr('href', `#${newRoomId}`);
            $tab.attr('aria-controls', newRoomId);
            $tab.data('room', roomNumber);
            $tab.find('.room-title').text(`Room ${roomNumber}`);

            // Update pane
            const $pane = $(`#${oldRoomId}`);
            $pane.attr('id', newRoomId);
            $pane.attr('aria-labelledby', `${newRoomId}-tab`);
            $pane.data('room', roomNumber);

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

      // Modal management functions
      function showQualificationModal(roomId) {
         console.log('Opening qualification modal for room:', roomId);
         state.currentRoom = roomId;
         state.selectedQualification = null;
         $('#qualificationModal').fadeIn(300);
         $('#qualificationOptions .qualification-option').removeClass('selected');
         $('#confirmAddQualification').prop('disabled', true);
         $('#qualificationSearch').val('');
         $('.qualification-option').show();
      }

      function hideQualificationModal() {
         $('#qualificationModal').fadeOut(300);
         state.selectedQualification = null;
      }

      function showMultiSelectModal(qualification, roomId) {
         console.log('Opening multi-select modal for:', qualification.name, 'room:', roomId);

         $('#multiSelectModal')
            .data('qualification', qualification)
            .data('roomId', roomId);

         state.currentRoom = roomId;

         initializeMultiSelectModal(qualification);

         $('#multiSelectModal').fadeIn(300);
         $('#multiSelectOptions .multi-select-option').removeClass('selected');
         $('#confirmMultiSelect').prop('disabled', true);
         $('#productSearch').val('');
         $('.multi-select-option').show();
      }

      function hideMultiSelectModal() {
         $('#multiSelectModal').fadeOut(300);
         state.selectedProducts = [];
         $('#multiSelectModal').removeData('qualification');
         $('#multiSelectModal').removeData('roomId');
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
         const $categoryTabs = $('#itemCategoryTabs');
         const $itemOptions = $('#itemOptions');

         $categoryTabs.empty();
         $itemOptions.empty();

         // Get items for this product
         const categories = getItemCategories(productId);
         const categoryKeys = Object.keys(categories);

         if (categoryKeys.length === 0) {
            $itemOptions.html('<div class="no-items-message">No items available for this product</div>');
            $('#confirmSelectItem').prop('disabled', true);
            $modal.fadeIn(300);
            return;
         }

         let firstCategory = null;

         if (categoryKeys.length > 1) {
            categoryKeys.forEach((catKey, index) => {
               const categoryInfo = categories[catKey];
               if (index === 0) firstCategory = catKey;

               const $tab = $(`
            <div class="item-category-tab ${index === 0 ? 'active' : ''}" data-category="${catKey}">
               ${categoryInfo.name}
            </div>
        `);
               $categoryTabs.append($tab);
            });
         } else {
            $categoryTabs.hide();
            firstCategory = categoryKeys[0];
         }

         if (firstCategory) {
            loadItemCategory(firstCategory, categories[firstCategory]);
         }

         $('#itemSearch').val('');
         $modal.fadeIn(300);
         $('#confirmSelectItem').prop('disabled', true);
      }

      function loadItemCategory(categoryKey, categoryInfo) {
         const $itemOptions = $('#itemOptions');
         $itemOptions.empty();

         categoryInfo.items.forEach(item => {
            const $option = $(`
            <div class="item-option" data-item-id="${item.id}">
               <div class="item-option-icon" style="background: ${item.color};">
                  <i class="fa ${item.icon}"></i>
               </div>
               <div class="item-option-name">${item.name}</div>
               <div class="item-option-description">${item.description}</div>
            </div>
         `);
            $itemOptions.append($option);
         });
      }

      function hideItemSelectionModal() {
         $('#itemSelectionModal').fadeOut(300);
         state.currentProductType = null;
         state.selectedItem = null;
         // ADDED: Clear search input when closing
         $('#itemSearch').val('');
      }

      // ADDED: Show accessory selection modal
      function showAccessorySelectionModal(productId) {
         console.log('Opening accessory selection modal for product:', productId);
         state.currentProductId = productId;

         const $modal = $('#accessorySelectionModal');
         const $accessoryOptions = $('#accessoryOptions');

         $accessoryOptions.empty();

         curtainAccessories.forEach(accessory => {
            const $option = $(`
            <div class="item-option" data-accessory-id="${accessory.id}">
               <div class="item-option-icon" style="background: ${accessory.color};">
                  <i class="fa ${accessory.icon}"></i>
               </div>
               <div class="item-option-name">${accessory.name}</div>
               <div class="item-option-description">${accessory.description}</div>
            </div>
         `);
            $accessoryOptions.append($option);
         });

         $modal.fadeIn(300);
         $('#confirmSelectAccessory').prop('disabled', true);
      }

      function hideAccessorySelectionModal() {
         $('#accessorySelectionModal').fadeOut(300);
         state.currentProductId = null;
         state.selectedAccessory = null;
      }

      // Add product tab
      function addProductTab(roomId, product) {
         console.log('Adding product:', product.name, 'to room:', roomId);

         const $tabsContainer = $(`#productTabs-room${roomId}`);
         const $emptyState = $tabsContainer.find('.product-empty-state');

         if ($emptyState.length) {
            $emptyState.remove();
         }

         const productId = `product-${product.id}-room${roomId}`;
         const tabId = `${productId}-tab`;

         if ($tabsContainer.find(`[data-product="${product.id}"]`).length) {
            alert('This product has already been added to this room.');
            return;
         }

         const $tab = $(`
        <div class="product-tab" data-product="${product.id}" id="${tabId}">
           <div class="product-tab-icon" style="background: ${product.color};">
              <i class="fa ${product.icon}"></i>
           </div>
           <span class="product-tab-name">${product.name}</span>
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
              <p>Loading ${product.name} details...</p>
           </div>
        </div>
     `);

         $contentArea.append($content);

         activateProductTab($tab);

         setTimeout(() => {
            loadProductContent(productId, product, roomId);
         }, 500);
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

      // Load product content based on type and variants
      function loadProductContent(contentId, product, roomId) {
         const $content = $(`#${contentId}`);
         const totalsHTML = createTotalsSection(product, roomId);

         // Check if product has variants
         if (product.hasVariants) {
            const variants = getProductVariants(product.id);
            if (variants.length > 0) {
               loadProductWithVariants($content, product, variants);
               $content.append(totalsHTML);
               return;
            }
         }

         // Load based on product type
         if (product.type === 'complex') {
            loadComplexProductContent($content, product);
         } else if (product.type === 'curtains') {
            loadCurtainProductContent($content, product);
         } else {
            loadSimpleProductContent($content, product);
         }

         // Setup material tabs
         setupMaterialTabs(product.id);

         // Setup pillow subcategories if needed
         if (!productHasVariants(product.id)) {
            setupPillowSubcategoryTabs(product.id);
         }

         $content.append(totalsHTML);
      }

      // Load product with variants
      function loadProductWithVariants($content, product, variants) {
         const roomId = $content.closest('.tab-pane').data('room');

         // Create basic details section + variants
         var basicDetailsHTML = createBasicDetailsSection(product, roomId);
         if (isSizeVariantProduct(product.id) || product.hasVariants === false) {
            basicDetailsHTML = '';
         }

         // Choose between radio buttons (size) or tabs (set) based on variant type
         let variantsHTML;
         if (isSizeVariantProduct(product.id)) {
            variantsHTML = createVariantsRadioSelection(product.id, variants, roomId);
         } else {
            variantsHTML = createVariantsTabs(product.id, variants, roomId);
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
         if (isSizeVariantProduct(product.id)) {
            setupVariantsRadioSelection(product.id, roomId);
         } else {
            setupVariantsTabs(product.id, roomId);
         }
      }

      // Setup variants radio selection (for size variants)
      function setupVariantsRadioSelection(productId, roomId) {
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

            updateVariantStatus(productId, roomId, variantId);
         });

         // Setup calculations for variants
         setupVariantCalculations(productId, roomId);

         // Setup material tabs for variants
         setupVariantMaterialTabs(productId, roomId);

         // Setup pillow subcategory tabs for variants
         setupVariantPillowSubcategoryTabs(productId, roomId);

         // Activate the first radio by default
         const $firstRadio = $variantsRadio.find('.variant-radio-input').first();
         if ($firstRadio.length) {
            $firstRadio.trigger('change');
         }
      }

      // Create variants radio selection (for size variants)
      function createVariantsRadioSelection(productId, variants, roomId) {
         return `
    <div class="product-variants-section" id="variants-section-${productId}-room${roomId}">
        <div class="variant-radio-header">
            <h6><i class="fa fa-ruler mr-2"></i>Select Size</h6>
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
                    ${createVariantContent(productId, variant, roomId)}
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
                     <i class="fa fa-image"></i>
                  </div>
                  <div class="compact-details-fields">
                     <div class="compact-detail-group">
                     <span class="detail-label">${product.name}</span>
                     </div>
                  </div>
               </div>
         </div>
      </div>
      `;
      }

      function createTotalsSection(product, roomId) {
         return `
    <div class="product-totals-section" id="product-totals-${product.id}-room${roomId}">
        <div class="product-totals-row">
            <div class="product-totals-label">Total:</div>
            <div class="product-totals-amount">
                $<span class="product-total-price" id="product-total-${product.id}-room${roomId}">0.00</span>
            </div>
        </div>
    </div>
    `;
      }

      function createGrandTotalsSection() {
         return `
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
    `;
      }

      function loadComplexProductContent($content, product) {
         const buttonText = `Add Item to ${product.name}`;
         const roomId = $content.closest('.tab-pane').data('room');

         const $wrapper = $(`
    <div class="product-details-wrapper">
        <div class="complex-product-layout">
            <div class="items-tabs-sidebar">
                <div class="items-tabs-header">
                    <h6><i class="fa fa-list mr-2"></i>Items</h6>
                    <button type="button" class="btn btn-sm btn-primary add-product-item-btn" data-product="${product.id}" data-room="${roomId}">
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
                            <i class="fa ${product.icon}"></i>
                        </div>
                        <h6><i class="fa fa-info-circle mr-2"></i>${product.name} Details</h6>
                    </div>
                    <div class="compact-header-details">
                        <div class="compact-header-group">
                            <label>Width (m)</label>
                            <input type="number" class="form-control dimension-width" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="compact-header-group">
                            <label>Length/Height (m)</label>
                            <input type="number" class="form-control dimension-length" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="compact-header-group">
                            <label>Unit Price ($)</label>
                            <input type="number" class="form-control unit-price" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="compact-header-group">
                            <label>Total Price ($)</label>
                            <input type="number" class="form-control total-price" placeholder="0.00" step="0.01" min="0" readonly>
                        </div>
                    </div>
                </div>
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

         setupDimensionCalculations(product.id);
         setupPriceCalculations(product.id);
      }

      // UPDATED: Material section with image on left, details on right and pillow subcategory tabs
      function loadSimpleProductContent($content, product) {
         const $wrapper = $(`
        <div class="simple-product-content">
           <div class="compact-product-details">
              <div class="compact-section-header">
                 <h6><i class="fa fa-cube mr-2"></i>${product.name} Details</h6>
              </div>
              <div class="compact-details-with-image">
                 <div class="compact-image-preview">
                    <i class="fa fa-image"></i>
                 </div>
                 <div class="compact-details-fields">
                    <div class="compact-detail-group">
                       <label>Width (m)</label>
                       <input type="number" class="form-control dimension-width" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Length (m)</label>
                       <input type="number" class="form-control dimension-length" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Height (m)</label>
                       <input type="number" class="form-control dimension-height" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Unit Price ($)</label>
                       <input type="number" class="form-control unit-price" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Total Price ($)</label>
                       <input type="number" class="form-control total-price" placeholder="0.00" step="0.01" min="0" readonly>
                    </div>
                 </div>
              </div>
           </div>
           
           <!-- UPDATED Material Section with Image on Left and Details on Right -->
           <div class="material-section">
              <h6><i class="fa fa-layer-group mr-2"></i>Material Selection</h6>
              <div class="material-tabs" id="materialTabs-${product.id}">
                 ${materialCategories.map(category => `
                    <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" data-category="${category.id}">
                       ${category.name}
                    </button>
                 `).join('')}
              </div>
              <div class="material-tabs-content" id="materialTabsContent-${product.id}">
                 ${materialCategories.map((category, index) => `
                    <div class="material-tab-content ${index === 0 ? 'active' : ''}" id="materialContent-${product.id}-${category.id}">
                       ${category.id === 'pillow' ? 
                          loadPillowSubcategories(product.id) : 
                          // Standard material layout for non-pillow categories (Image on left, details on right)
                          `<div class="material-inputs-compact">
                              <div class="material-compact-image">
                                 <i class="fa fa-image"></i>
                              </div>
                              <div class="material-compact-fields">
                                 <div class="material-input">
                                    <label>Material Grade</label>
                                    <select class="form-control material-grade">
                                       <option value="">Select Grade</option>
                                       <option value="standard">Standard</option>
                                       <option value="premium">Premium</option>
                                       <option value="economy">Economy</option>
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Material Type</label>
                                    <select class="form-control material-type-select">
                                       <option value="">Select Material</option>
                                       ${category.defaultMaterials.map(material => `
                                          <option value="${material.id}">${material.name}</option>
                                       `).join('')}
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Area/Weight</label>
                                    <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                 </div>
                              </div>
                           </div>`
                       }
                    </div>
                 `).join('')}
              </div>
           </div>
        </div>
     `);

         $content.html($wrapper);

         setupDimensionCalculations(product.id);
         setupPriceCalculations(product.id);
         setupMaterialTabs(product.id);
      }

      // ADDED: Curtain product content with accessory section working like items section
      function loadCurtainProductContent($content, product) {
         const $wrapper = $(`
        <div class="simple-product-content">
           <div class="compact-product-details">
              <div class="compact-section-header">
                 <h6><i class="fa fa-cube mr-2"></i>${product.name} Details</h6>
              </div>
              <div class="compact-details-with-image">
                 <div class="compact-image-preview">
                    <i class="fa fa-image"></i>
                 </div>
                 <div class="compact-details-fields">
                    <div class="compact-detail-group">
                       <label>Width (m)</label>
                       <input type="number" class="form-control dimension-width" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Length (m)</label>
                       <input type="number" class="form-control dimension-length" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Height (m)</label>
                       <input type="number" class="form-control dimension-height" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Unit Price ($)</label>
                       <input type="number" class="form-control unit-price" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Total Price ($)</label>
                       <input type="number" class="form-control total-price" placeholder="0.00" step="0.01" min="0" readonly>
                    </div>
                 </div>
              </div>
           </div>
           
           <!-- UPDATED Material Section with Image on Left and Details on Right -->
           <div class="material-section">
              <h6><i class="fa fa-layer-group mr-2"></i>Material Selection</h6>
              <div class="material-tabs" id="materialTabs-${product.id}">
                 ${materialCategories.map(category => `
                    <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" data-category="${category.id}">
                       ${category.name}
                    </button>
                 `).join('')}
              </div>
              <div class="material-tabs-content" id="materialTabsContent-${product.id}">
                 ${materialCategories.map((category, index) => `
                    <div class="material-tab-content ${index === 0 ? 'active' : ''}" id="materialContent-${product.id}-${category.id}">
                       ${category.id === 'pillow' ? 
                          loadPillowSubcategories(product.id) : 
                          // Standard material layout for non-pillow categories (Image on left, details on right)
                          `<div class="material-inputs-compact">
                              <div class="material-compact-image">
                                 <i class="fa fa-image"></i>
                              </div>
                              <div class="material-compact-fields">
                                 <div class="material-input">
                                    <label>Material Grade</label>
                                    <select class="form-control material-grade">
                                       <option value="">Select Grade</option>
                                       <option value="standard">Standard</option>
                                       <option value="premium">Premium</option>
                                       <option value="economy">Economy</option>
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Material Type</label>
                                    <select class="form-control material-type-select">
                                       <option value="">Select Material</option>
                                       ${category.defaultMaterials.map(material => `
                                          <option value="${material.id}">${material.name}</option>
                                       `).join('')}
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Area/Weight</label>
                                    <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                 </div>
                              </div>
                           </div>`
                       }
                    </div>
                 `).join('')}
              </div>
           </div>
           
           <!-- UPDATED: Curtain Options Section with Accessory Layout like Items Section -->
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
              </div>
              
              <h6 style="margin-top: 16px;"><i class="fa fa-plus-circle mr-2"></i>Accessories</h6>
              <div class="accessory-layout">
                 <div class="accessory-tabs-sidebar">
                    <div class="accessory-tabs-header">
                       <h6><i class="fa fa-list mr-2"></i>Accessories</h6>
                       <button type="button" class="btn btn-sm btn-primary add-accessory-btn" data-product="${product.id}">
                          <i class="fa fa-plus mr-1"></i> Add Accessory
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
     `);

         $content.html($wrapper);

         setupDimensionCalculations(product.id);
         setupPriceCalculations(product.id);
         setupMaterialTabs(product.id);
      }

      // NEW: Function to load pillow subcategories with horizontal tabs
      function loadPillowSubcategories(productId) {
         const pillowCategory = materialCategories.find(cat => cat.id === 'pillow');
         if (!pillowCategory) return '';

         return `
        <div class="pillow-subcategories-section">
            <div class="pillow-subcategories-tabs" id="pillowTabs-${productId}">
                ${pillowCategory.subcategories.map((subcat, index) => `
                    <button class="pillow-subcategory-tab ${index === 0 ? 'active' : ''}" 
                            data-subcategory="${subcat.id}" data-product="${productId}">
                        <div class="pillow-subcategory-header">
                            <span class="status-indicator status-empty"></span>
                            <span class="pillow-subcategory-title">${subcat.name}</span>
                        </div>
                    </button>
                `).join('')}
            </div>
            <div class="pillow-subcategories-content" id="pillowContent-${productId}">
                ${pillowCategory.subcategories.map((subcat, index) => `
                    <div class="pillow-subcategory-content ${index === 0 ? 'active' : ''}" 
                         id="pillowSubcategory-${productId}-${subcat.id}">
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
                                        <label>Material Type</label>
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
                                    <div class="pillow-material-input">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control pillow-qty" 
                                               placeholder="0" min="1" value="1"
                                               data-subcategory="${subcat.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Unit Price ($)</label>
                                        <input type="number" class="form-control pillow-unit-price" 
                                               placeholder="0.00" step="0.01" min="0"
                                               data-subcategory="${subcat.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Total Price ($)</label>
                                        <input type="number" class="form-control pillow-total-price" 
                                               placeholder="0.00" step="0.01" min="0" readonly
                                               data-subcategory="${subcat.id}">
                                    </div>
                                </div>
                            </div>
                            <div class="pillow-material-input" style="margin-top: 12px; grid-column: 1 / -1;">
                                <label>Additional Notes</label>
                                <textarea class="form-control pillow-notes" 
                                          placeholder="Enter additional notes for ${subcat.name}..." 
                                          rows="2" data-subcategory="${subcat.id}"></textarea>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }

      function createMaterialSectionForItem(itemId, productId, roomId) {
         return `
        <!-- Material Tabs Section -->
        <div class="material-section">
            <h6><i class="fa fa-layer-group mr-2"></i>Material Selection</h6>
            <div class="material-tabs" id="materialTabs-${itemId}-${productId}-room${roomId}">
                ${materialCategories.map(category => `
                    <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" data-category="${category.id}">
                        ${category.name}
                    </button>
                `).join('')}
            </div>
            <div class="material-tabs-content" id="materialTabsContent-${itemId}-${productId}-room${roomId}">
                ${materialCategories.map((category, index) => `
                    <div class="material-tab-content ${index === 0 ? 'active' : ''}" id="materialContent-${itemId}-${productId}-room${roomId}-${category.id}">
                        ${category.id === 'pillow' ? 
                            loadPillowSubcategoriesForItem(itemId, productId, roomId) : 
                            // Standard material layout for non-pillow categories
                            `<div class="material-inputs-compact">
                                <div class="material-compact-image">
                                    <i class="fa fa-image"></i>
                                </div>
                                <div class="material-compact-fields">
                                    <div class="material-input">
                                        <label>Material Grade</label>
                                        <select class="form-control material-grade">
                                            <option value="">Select Grade</option>
                                            <option value="standard">Standard</option>
                                            <option value="premium">Premium</option>
                                            <option value="economy">Economy</option>
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Material Type</label>
                                        <select class="form-control material-type-select">
                                            <option value="">Select Material</option>
                                            ${category.defaultMaterials.map(material => `
                                                <option value="${material.id}">${material.name}</option>
                                            `).join('')}
                                        </select>
                                    </div>
                                    <div class="material-input">
                                        <label>Area/Weight</label>
                                        <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                    </div>
                                </div>
                            </div>`
                        }
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }

      function loadPillowSubcategoriesForItem(itemId, productId, roomId) {
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
                                        <label>Material Type</label>
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
                                    <div class="pillow-material-input">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control pillow-qty" 
                                               placeholder="0" min="1" value="1"
                                               data-subcategory="${subcat.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Unit Price ($)</label>
                                        <input type="number" class="form-control pillow-unit-price" 
                                               placeholder="0.00" step="0.01" min="0"
                                               data-subcategory="${subcat.id}">
                                    </div>
                                    <div class="pillow-material-input">
                                        <label>Total Price ($)</label>
                                        <input type="number" class="form-control pillow-total-price" 
                                               placeholder="0.00" step="0.01" min="0" readonly
                                               data-subcategory="${subcat.id}">
                                    </div>
                                </div>
                            </div>
                            <div class="pillow-material-input" style="margin-top: 12px; grid-column: 1 / -1;">
                                <label>Additional Notes</label>
                                <textarea class="form-control pillow-notes" 
                                          placeholder="Enter additional notes for ${subcat.name}..." 
                                          rows="2" data-subcategory="${subcat.id}"></textarea>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
      }
      // NEW: Function to setup pillow subcategory tabs
      function setupPillowSubcategoryTabs(productId) {
         const $pillowTabs = $(`#pillowTabs-${productId}`);
         const $pillowContent = $(`#pillowContent-${productId}`);

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
            $(`#pillowSubcategory-${productId}-${subcategoryId}`).addClass('active');

            updatePillowSubcategoryStatus(productId, subcategoryId);
         });

         // Setup price calculations for pillow subcategories
         setupPillowPriceCalculations(productId);

         // Activate the first tab by default
         const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
         if ($firstTab.length) {
            $firstTab.trigger('click');
         }
      }

      // NEW: Function to setup pillow price calculations
      function setupPillowPriceCalculations(productId) {
         $(`#pillowContent-${productId} .pillow-qty, #pillowContent-${productId} .pillow-unit-price`).on('input', function() {
            const subcategoryId = $(this).data('subcategory');
            const qty = parseFloat($(`#pillowContent-${productId} .pillow-qty[data-subcategory="${subcategoryId}"]`).val()) || 0;
            const unitPrice = parseFloat($(`#pillowContent-${productId} .pillow-unit-price[data-subcategory="${subcategoryId}"]`).val()) || 0;
            const totalPrice = qty * unitPrice;

            $(`#pillowContent-${productId} .pillow-total-price[data-subcategory="${subcategoryId}"]`).val(totalPrice.toFixed(2));

            updatePillowSubcategoryStatus(productId, subcategoryId);
         });
      }

      // NEW: Function to update pillow subcategory status
      function updatePillowSubcategoryStatus(productId, variantId, subcategoryId, roomId) {
         const $tab = $(`#pillowTabs-${productId}-${variantId}-room${roomId} .pillow-subcategory-tab[data-subcategory="${subcategoryId}"]`);
         const $statusIndicator = $tab.find('.status-indicator');

         const $content = $(`#pillowSubcategory-${productId}-${variantId}-room${roomId}-${subcategoryId}`);
         const materialGrade = $content.find('.material-grade').val();
         const materialType = $content.find('.material-type-select').val();
         const areaWeight = $content.find('.area-weight').val();
         const qty = $content.find('.pillow-qty').val();
         const unitPrice = $content.find('.pillow-unit-price').val();

         $statusIndicator.removeClass('status-empty status-incomplete status-complete');

         if (!materialGrade && !materialType && !areaWeight && !qty && !unitPrice) {
            $statusIndicator.addClass('status-empty');
         } else if (materialGrade && materialType && areaWeight && qty && unitPrice) {
            $statusIndicator.addClass('status-complete');
         } else {
            $statusIndicator.addClass('status-incomplete');
         }

         // Update parent variant status
         updateVariantStatus(productId, roomId, variantId);
      }

      function setupDimensionCalculations(productId) {
         $(`#product-${productId}-room${state.currentRoom} .dimension-width, #product-${productId}-room${state.currentRoom} .dimension-length`).on('input', function() {
            const width = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-width`).val()) || 0;
            const length = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-length`).val()) || 0;
            const area = width * length;
            const unitPrice = parseFloat($(`#product-${productId}-room${state.currentRoom} .unit-price`).val()) || 0;
            const totalPrice = unitPrice * area;
            $(`#product-${productId}-room${state.currentRoom} .total-price`).val(totalPrice.toFixed(2));
         });
      }

      function setupPriceCalculations(productId) {
         $(`#product-${productId}-room${state.currentRoom} .unit-price`).on('input', function() {
            const unitPrice = parseFloat($(this).val()) || 0;
            const width = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-width`).val()) || 0;
            const length = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-length`).val()) || 0;
            const area = width * length;
            const totalPrice = unitPrice * area;
            $(`#product-${productId}-room${state.currentRoom} .total-price`).val(totalPrice.toFixed(2));
         });
      }

      // Setup material tabs functionality
      function setupMaterialTabs(productId) {
         $(`#materialTabs-${productId} .material-tab`).on('click', function(e) {
            e.preventDefault();
            const categoryId = $(this).data('category');

            $(`#materialTabs-${productId} .material-tab`).removeClass('active');
            $(this).addClass('active');

            $(`#materialTabsContent-${productId} .material-tab-content`).removeClass('active');
            $(`#materialContent-${productId}-${categoryId}`).addClass('active');
         });
      }

      // Add item to product with improved material section layout
      function addItemToProduct(roomId, productId, item) {
         console.log('Adding item to product:', item.name, 'room:', roomId, 'product:', productId);

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
         const existingTab = $tabsContainer.find(`[data-item-id="${item.id}"]`);
         if (existingTab.length) {
            alert('This item has already been added.');
            return;
         }

         const tabId = `item-${item.id}-${productId}-room${roomId}`;
         const $tab = $(`
        <div class="items-tab" data-item-id="${item.id}" id="${tabId}-tab">
            <span class="items-tab-name">${item.name}</span>
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
            <div class="item-details" id="${tabId}" style="display: none;">
                <div class="enhanced-category-item">
                    <div class="enhanced-item-header">
                        <div class="enhanced-item-name">${item.name}</div>
                    </div>
                    <div class="enhanced-details-with-image">
                        <div class="enhanced-image-preview">
                            <i class="fa fa-image"></i>
                        </div>
                        <div class="enhanced-details-fields">
                            <div class="detail-group">
                                <label>Quantity</label>
                                <input type="number" class="form-control item-qty" placeholder="0" min="1" value="1">
                            </div>
                            <div class="detail-group">
                                <label>Unit Price</label>
                                <input type="number" class="form-control item-price" placeholder="0.00" min="0" step="0.01" value="0.00">
                            </div>
                            <div class="detail-group">
                                <label>Length (m)</label>
                                <input type="number" class="form-control item-length item-dims" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div class="detail-group">
                                <label>Width (m)</label>
                                <input type="number" class="form-control item-width item-dims" placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div class="detail-group">
                                <label>Height (m)</label>
                                <input type="number" class="form-control item-height item-dims" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="detail-group" style="margin-top: 12px;">
                        <label>Notes</label>
                        <textarea class="form-control item-notes" placeholder="Additional notes..." rows="2"></textarea>
                    </div>
                </div>
                
                ${createMaterialSectionForItem(item.id, productId, roomId)}
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
         setupMaterialTabsForItem(item.id, productId, roomId);
         setupPillowSubcategoryTabsForItem(item.id, productId, roomId);

         // Setup close button
         $tab.find('.items-tab-close').on('click', function(e) {
            e.stopPropagation();
            removeItemFromProduct($tab);
         });

         updateRoomStatus(`room${roomId}`);
      }

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
            });

            // Setup price calculations for pillow subcategories
            setupPillowPriceCalculationsForItem(itemId, productId, roomId);

            // Activate the first tab by default
            const $firstTab = $pillowTabs.find('.pillow-subcategory-tab').first();
            if ($firstTab.length) {
               $firstTab.trigger('click');
            }
         }, 100);
      }

      function setupPillowPriceCalculationsForItem(itemId, productId, roomId) {
         $(`#pillowContent-${itemId}-${productId}-room${roomId} .pillow-qty, #pillowContent-${itemId}-${productId}-room${roomId} .pillow-unit-price`).on('input', function() {
            const subcategoryId = $(this).data('subcategory');
            const qty = parseFloat($(`#pillowContent-${itemId}-${productId}-room${roomId} .pillow-qty[data-subcategory="${subcategoryId}"]`).val()) || 0;
            const unitPrice = parseFloat($(`#pillowContent-${itemId}-${productId}-room${roomId} .pillow-unit-price[data-subcategory="${subcategoryId}"]`).val()) || 0;
            const totalPrice = qty * unitPrice;

            $(`#pillowContent-${itemId}-${productId}-room${roomId} .pillow-total-price[data-subcategory="${subcategoryId}"]`).val(totalPrice.toFixed(2));
         });
      }

      // ADDED: Add accessory to curtain product
      function addAccessoryToProduct(roomId, productId, accessory) {
         console.log('Adding accessory to product:', accessory.name, 'room:', roomId, 'product:', productId);

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

         // Accessory details content
         const $detailsContent = $(`
    <div class="accessory-details" id="${tabId}" style="display: none;">
        <div class="enhanced-category-item">
            <div class="enhanced-item-header">
                <div class="enhanced-item-name">${accessory.name}</div>
            </div>
            <div class="enhanced-details-with-image">
                <div class="enhanced-image-preview">
                    <i class="fa fa-image"></i>
                </div>
                <div class="enhanced-details-fields">
                    <div class="detail-group accessory-type-with-preview">
                        <label>Accessory Type</label>
                        <div class="accessory-selection-container">
                            <select class="form-control accessory-type-select">
                                <option value="">Select ${accessory.name} Type</option>
                                ${accessory.options.map(option => `
                                    <option value="${option.id}">${option.name}</option>
                                `).join('')}
                            </select>
                            <div class="accessory-option-preview" id="${accessory.id}-preview">
                                <div class="preview-content">
                                    <div class="preview-image">
                                        <i class="fa fa-image"></i>
                                    </div>
                                    <div class="preview-details">
                                        <div class="preview-name">No selection</div>
                                        <div class="preview-description">Select an option to see preview</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-group">
                        <label>Quantity</label>
                        <input type="number" class="form-control accessory-qty" placeholder="0" min="1" value="1">
                    </div>
                    <div class="detail-group">
                        <label>Unit Price ($)</label>
                        <input type="number" class="form-control accessory-price" placeholder="0.00" min="0" step="0.01" value="0.00">
                    </div>
                    <div class="detail-group accessory-total-price">
                        <label>Total Price ($)</label>
                        <input type="number" class="form-control accessory-total" placeholder="0.00" step="0.01" min="0" readonly>
                    </div>
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
         $(`#${tabId} .accessory-type-select`).on('change', function() {
            const selectedOptionId = $(this).val();
            const previewId = `${accessory.id}-preview`;

            if (selectedOptionId) {
               const selectedOption = accessory.options.find(opt => opt.id === selectedOptionId);
               if (selectedOption) {
                  $(`#${tabId} #${previewId} .preview-name`).text(selectedOption.name);
                  $(`#${tabId} #${previewId} .preview-description`).text(selectedOption.description);
                  $(`#${tabId} #${previewId}`).addClass('has-selection');
               }
            } else {
               $(`#${tabId} #${previewId} .preview-name`).text('No selection');
               $(`#${tabId} #${previewId} .preview-description`).text('Select an option to see preview');
               $(`#${tabId} #${previewId}`).removeClass('has-selection');
            }
         });

         // Calculation for accessory total price
         $(`#${tabId} .accessory-qty, #${tabId} .accessory-price`).on('input', function() {
            const qty = parseFloat($(`#${tabId} .accessory-qty`).val()) || 0;
            const price = parseFloat($(`#${tabId} .accessory-price`).val()) || 0;
            const total = qty * price;

            $(`#${tabId} .accessory-total`).val(total.toFixed(2));
         });

         $tab.find('.accessory-tab-close').on('click', function(e) {
            e.stopPropagation();
            removeAccessoryFromProduct($tab, roomId, productId, accessory.id);
         });
      }

      // Setup material tabs for specific item
      function setupMaterialTabsForItem(itemId, productId, roomId) {
         const materialTabsId = `
               materialTabs - $ {
                  itemId
               } - $ {
                  productId
               } - room$ {
                  roomId
               }
               `;

         // Use a small delay to ensure DOM is ready
         setTimeout(() => {
            if ($(`
               #$ {
                  materialTabsId
               }
               `).length) {
               $(`
               #$ {
                  materialTabsId
               }.material - tab`).off('click').on('click', function(e) {
                  e.preventDefault();
                  const categoryId = $(this).data('category');
                  const materialTabsContentId = `
               materialTabsContent - $ {
                  itemId
               } - $ {
                  productId
               } - room$ {
                  roomId
               }
               `;

                  $(`
               #$ {
                  materialTabsId
               }.material - tab`).removeClass('active');
                  $(this).addClass('active');

                  $(`
               #$ {
                  materialTabsContentId
               }.material - tab - content`).removeClass('active');
                  $(`
               #materialContent - $ {
                  itemId
               } - $ {
                  productId
               } - room$ {
                  roomId
               } - $ {
                  categoryId
               }
               `).addClass('active');
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

         const detailsId = `
               item - $ {
                  itemId
               } - $ {
                  productId
               } - room$ {
                  roomId
               }
               `;
         const $details = $(`
               #$ {
                  detailsId
               }
               `);

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

         $productContent.find('.accessory-tab').removeClass('active');
         $productContent.find('.accessory-details').hide();

         $tab.addClass('active');
         $(`#accessory-${accessoryId}-${productId}-room${roomId}`).show();
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
         const detailsId = `
               item - $ {
                  itemId
               } - $ {
                  productId
               } - room$ {
                  roomId
               }
               `;
         $(`
               #$ {
                  detailsId
               }
               `).remove();

         const $tabsContainer = $productContent.find('.items-tabs-container');
         const $detailsBody = $productContent.find('.product-details-body');
         const $tabs = $tabsContainer.find('.items-tab');

         if ($tabs.length === 0) {
            $tabsContainer.html(` <
               div class = "empty-items-tabs" >
               <
               i class = "fa fa-cube" > < /i> <
               p > No items added yet < /p> <
                  /div>
               `);

            $detailsBody.html(` <
               div class = "empty-item-selection" >
               <
               i class = "fa fa-hand-pointer" > < /i> <
               p > Select an item to view and edit details < /p> <
                  /div>
               `);
         } else {
            const $firstTab = $tabs.first();
            activateItemTab($firstTab);
         }

         updateRoomStatus(`
               room$ {
                  roomId
               }
               `);
      }

      // ADDED: Remove accessory from product
      function removeAccessoryFromProduct($tab, roomId, productId, accessoryId) {
         console.log('Removing accessory from product');

         $tab.remove();
         $(`
               #accessory - $ {
                  accessoryId
               } - $ {
                  productId
               } - room$ {
                  roomId
               }
               `).remove();

         const $productContent = $(`
               #product - $ {
                  productId
               } - room$ {
                  roomId
               }
               `);
         const $tabsContainer = $productContent.find('.accessory-tabs-container');
         const $detailsBody = $productContent.find('.accessory-details-body');
         const $tabs = $tabsContainer.find('.accessory-tab');

         if ($tabs.length === 0) {
            $tabsContainer.html(` <
               div class = "empty-accessory-tabs" >
               <
               i class = "fa fa-puzzle-piece" > < /i> <
               p > No accessories added yet < /p> <
                  /div>
               `);

            $detailsBody.html(` <
               div class = "empty-accessory-selection" >
               <
               i class = "fa fa-hand-pointer" > < /i> <
               p > Select an accessory to view and edit details < /p> <
                  /div>
               `);
         } else {
            const $firstTab = $tabs.first();
            activateAccessoryTab($firstTab, roomId, productId);
         }
      }

      function updateRoomStatus(roomId) {
         const $roomPane = $(`
               #$ {
                  roomId
               }
               `);
         const $statusIndicator = $(`
               #$ {
                  roomId
               } - tab.status - indicator`);

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

      // Event handlers
      $('#addRoomBtn').on('click', function() {
         const roomNumber = getNextRoomNumber();
         const roomId = 'room' + roomNumber;

         const $tabLi = $(` <
                  li class = "nav-item" >
                  <
                  a class = "nav-link room-tab"
               id = "${roomId}-tab"
               data - toggle = "tab"
               href = "#${roomId}"
               role = "tab"
               aria - controls = "${roomId}"
               data - room = "${roomNumber}" >
                  <
                  div class = "room-header" >
                  <
                  span class = "status-indicator status-empty" > < /span> <
                  span class = "room-title" > Room $ {
                     roomNumber
                  } < /span> <
                  span class = "close-room ml-2"
               title = "Remove room" >
                  <
                  i class = "fa fa-times" > < /i> <
                  /span> <
                  /div> <
                  /a> <
                  /li>
               `);
         $('#roomTabs .nav-item:has(.add-room-btn)').before($tabLi);

         const $pane = $(` <
               div class = "tab-pane fade"
               id = "${roomId}"
               role = "tabpanel"
               aria - labelledby = "${roomId}-tab"
               data - room = "${roomNumber}" >
                  <
                  div class = "product-tabs-wrapper" >
                  <
                  div class = "product-tabs-header" >
                  <
                  div class = "room-info-form" >
                  <
                  div class = "form-group-small" >
                  <
                  label
               for = "floorName-${roomId}" > Floor Name < /label> <
                  input type = "text"
               class = "form-control-small"
               id = "floorName-${roomId}"
               placeholder = "Enter floor name" >
                  <
                  /div> <
                  div class = "form-group-small" >
                  <
                  label
               for = "roomName-${roomId}" > Room Name < /label> <
                  input type = "text"
               class = "form-control-small"
               id = "roomName-${roomId}"
               placeholder = "Enter room name" >
                  <
                  /div> <
                  div class = "form-group-small" >
                  <
                  label > Room Image < /label> <
                  div class = "image-upload-container" >
                  <
                  div class = "image-preview"
               id = "imagePreview-${roomId}" >
                  <
                  i class = "fa fa-image" > < /i> <
                  /div> <
                  div class = "file-input-wrapper" >
                  <
                  button type = "button"
               class = "btn btn-sm btn-outline-primary" >
               <
               i class = "fa fa-upload mr-1" > < /i> Upload <
               /button> <
               input type = "file"
               class = "room-image-input"
               id = "roomImage-${roomId}"
               data - file - type = "image"
               data - room = "${roomNumber}" >
                  <
                  /div> <
                  /div> <
                  /div> <
                  /div> <
                  button type = "button"
               class = "btn btn-sm btn-primary add-item-to-room-btn"
               data - room = "${roomNumber}" >
                  <
                  i class = "fa fa-plus mr-1" > < /i> Add Item To Room ${roomNumber} <
                  /button> <
                  /div> <
                  div class = "product-tabs-container"
               id = "productTabs-room${roomNumber}" >
                  <
                  div class = "product-empty-state" >
                  <
                  i class = "fa fa-cube" > < /i> <
                  p > No products added yet < /p> <
                  /div> <
                  /div> <
                  div class = "product-content-area"
               id = "productContent-room${roomNumber}" >
                  <
                  div class = "product-empty-state" >
                  <
                  i class = "fa fa-hand-pointer" > < /i> <
                  p > Select a product to configure details < /p> <
                  /div> <
                  /div> <
                  /div> <
                  /div>
               `);

         $('#roomTabsContent').append($pane);
         $(`
               #$ {
                  roomId
               } - tab`).tab('show');
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
            console.log('Final roomId for qualification modal:', roomId);
            showQualificationModal(roomId);
         } else {
            console.error('Could not determine roomId for product');
         }
      });

      $(document).on('click', '.qualification-option', function() {
         console.log('Qualification option clicked:', $(this).data('qualification'));
         $('.qualification-option').removeClass('selected');
         $(this).addClass('selected');
         state.selectedQualification = $(this).data('qualification');
         $('#confirmAddQualification').prop('disabled', false);
      });

      $('#confirmAddQualification').on('click', function() {
         console.log('Confirm add qualification clicked');
         console.log('Current state:', {
            selectedQualification: state.selectedQualification,
            currentRoom: state.currentRoom
         });

         if (state.selectedQualification && state.currentRoom) {
            const qualification = qualifications.find(q => q.id === state.selectedQualification);
            if (qualification) {
               const roomId = state.currentRoom;

               hideQualificationModal();

               setTimeout(() => {
                  console.log('Showing multi-select modal with roomId:', roomId);
                  showMultiSelectModal(qualification, roomId);
               }, 100);
            }
         }
      });

      $(document).on('click', '.multi-select-option', function() {
         const productId = $(this).data('product-id');
         console.log('Product option clicked:', productId);

         // Single selection
         $('.multi-select-option').removeClass('selected');
         $(this).addClass('selected');

         // Store single product ID
         state.selectedProducts = [productId];

         $('#confirmMultiSelect').prop('disabled', false);
      });

      $('#confirmMultiSelect').on('click', function() {
         console.log('Confirm product selection clicked');

         const qualification = $('#multiSelectModal').data('qualification');
         const roomId = $('#multiSelectModal').data('roomId');

         console.log('Data for product addition:', {
            qualification: qualification,
            roomId: roomId,
            selectedProducts: state.selectedProducts
         });

         if (state.selectedProducts.length > 0 && roomId && qualification) {
            const selectedProductId = state.selectedProducts[0];

            // Find the selected product
            const selectedProduct = products.find(product => product.id === selectedProductId);

            console.log('Selected product to add:', selectedProduct);

            if (selectedProduct) {
               addProductTab(roomId, selectedProduct);
               hideMultiSelectModal();
            } else {
               console.error('Could not find selected product');
               alert('Error: Could not find the selected product.');
            }
         } else {
            console.error('Missing data for product addition');
            alert('Please select a product to continue.');
         }
      });

      $('#confirmSelectItem').on('click', function() {
         console.log('Confirm item selection clicked');

         const roomId = $('#itemSelectionModal').data('current-room');
         const productId = $('#itemSelectionModal').data('current-product');

         console.log('Context from modal:', {
            roomId: roomId,
            productId: productId,
            selectedItem: state.selectedItem
         });

         if (state.selectedItem && roomId && productId) {
            console.log('Adding item with confirmed context:', {
               roomId: roomId,
               productId: productId,
               item: state.selectedItem
            });

            addItemToProduct(roomId, productId, state.selectedItem);
            hideItemSelectionModal();
         } else {
            console.error('Missing data for item selection');
            alert('Error: Missing context information. Please try again.');
         }
      });

      // Accessory selection handlers
      $(document).on('click', '#itemOptions .item-option', function() {
         console.log('Item option clicked:', $(this).data('item-id'));
         $('#itemOptions .item-option').removeClass('selected');
         $(this).addClass('selected');

         const itemId = $(this).data('item-id');

         // FIX: Use products array instead of itemData
         state.selectedItem = products.find(item => item.id === itemId);

         if (state.selectedItem) {
            console.log('Selected item:', state.selectedItem);
            $('#confirmSelectItem').prop('disabled', false);
         } else {
            console.error('Could not find selected item with ID:', itemId);
            $('#confirmSelectItem').prop('disabled', true);
         }
      });

      // FIX: Add accessory option selection handler
      $(document).on('click', '#accessoryOptions .item-option', function() {
         console.log('Accessory option clicked:', $(this).data('accessory-id'));

         // Remove selection from all accessory options
         $('#accessoryOptions .item-option').removeClass('selected');

         // Add selection to clicked option
         $(this).addClass('selected');

         const accessoryId = $(this).data('accessory-id');

         // Find the selected accessory
         state.selectedAccessory = curtainAccessories.find(accessory => accessory.id === accessoryId);

         if (state.selectedAccessory) {
            console.log('Selected accessory:', state.selectedAccessory);
            $('#confirmSelectAccessory').prop('disabled', false);
         } else {
            console.error('Could not find selected accessory with ID:', accessoryId);
            $('#confirmSelectAccessory').prop('disabled', true);
         }
      });

      // UPDATE: Also fix the confirmSelectAccessory handler to get the correct context
      $('#confirmSelectAccessory').on('click', function() {
         console.log('Confirm accessory selection clicked');

         if (state.selectedAccessory && state.currentProductId) {
            // Get the active product tab to determine room and product context
            const $activeProductTab = $('.product-tab.active');
            if ($activeProductTab.length === 0) {
               console.error('No active product tab found');
               alert('Please select a product tab first.');
               return;
            }

            const productId = $activeProductTab.data('product');
            const roomId = $activeProductTab.closest('.tab-pane').data('room');

            console.log('Adding accessory to:', {
               productId: productId,
               roomId: roomId,
               accessory: state.selectedAccessory
            });

            addAccessoryToProduct(roomId, productId, state.selectedAccessory);
            hideAccessorySelectionModal();
         } else {
            console.error('Missing accessory or product context for selection');
            alert('Please select an accessory to add.');
         }
      });

      $(document).on('click', '.item-category-tab', function() {
         const categoryKey = $(this).data('category');
         console.log('Item category tab clicked:', categoryKey);
         $('.item-category-tab').removeClass('active');
         $(this).addClass('active');

         const productId = $('#itemSelectionModal').data('current-product');
         const categories = getItemCategories(productId);
         loadItemCategory(categoryKey, categories[categoryKey]);

         $('#itemSearch').val('');
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

      // ADDED: Add accessory button handler
      $(document).on('click', '.add-accessory-btn', function() {
         const productId = $(this).data('product');
         console.log('Add accessory button clicked:', productId);
         showAccessorySelectionModal(productId);
      });

      $(document).on('click', '.close-room', function(e) {
         e.stopPropagation();
         const $tab = $(this).closest('a.room-tab');
         const totalRooms = $('#roomTabs a.room-tab').length;

         if (totalRooms <= 1) {
            alert('At least one room must be present.');
            return;
         }

         const roomId = $tab.attr('href').replace('#', '');
         const isActive = $tab.hasClass('active');

         $tab.closest('.nav-item').remove();
         $(`
               #$ {
                  roomId
               }
               `).remove();

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

         if ($tabsContainer.find('.product-tab').length <= 1) {
            alert('At least one product must remain in the room.');
            return;
         }

         const productId = $tab.data('product');
         const roomId = $tabsContainer.attr('id').replace('productTabs-room', '');

         $(`
               #product - $ {
                  productId
               } - room$ {
                  roomId
               }
               `).remove();
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

      // ADDED: Accessory tab click handler
      $(document).on('click', '.accessory-tab', function() {
         const $tab = $(this);
         const $productContent = $tab.closest('.product-content');
         const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
         const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

         if (!$tab.find('.accessory-tab-close').is(':hover')) {
            activateAccessoryTab($tab, roomId, productId);
         }
      });

      $('#closeQualificationModal').on('click', hideQualificationModal);
      $('#closeMultiSelectModal').on('click', hideMultiSelectModal);
      $('#closeItemSelectionModal').on('click', hideItemSelectionModal);
      $('#closeAccessorySelectionModal').on('click', hideAccessorySelectionModal);

      $('#qualificationModal').on('click', function(e) {
         if (e.target === this) hideQualificationModal();
      });
      $('#multiSelectModal').on('click', function(e) {
         if (e.target === this) hideMultiSelectModal();
      });
      $('#itemSelectionModal').on('click', function(e) {
         if (e.target === this) hideItemSelectionModal();
      });
      $('#accessorySelectionModal').on('click', function(e) {
         if (e.target === this) hideAccessorySelectionModal();
      });

      // Initialize
      initializeQualificationModal();
      setupQualificationSearch();
      setupProductSearch();
      setupItemSearch();
      setupImageUpload();
      addRoomToState(1);
      updateRoomStatus('room1');

      $(document).ready(function() {
         $('.room-wrapper').after(createGrandTotalsSection());
      });

      console.log('System initialized successfully with pillow subcategories horizontal tabs');
   });
</script>
