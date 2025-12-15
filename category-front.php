<?php
if (!defined('inc_admin_pages')) {
    die;
}

define('inc_panel_header', true);
include PATH . '/inc/header.php';

$user = new User();
$logged = $user->getLogged('user_auth,user_workshop');
$logged_auth = $logged->user_auth;

$page_auth = ['admin', 'partner', 'manager', 'user', 'sales', 'quality', 'workshop', 'purchasing', 'graphic_and_media'];
$workshop_auths = ['drawing', 'fabric', '3d', 'autocad', 'solidWork'];
$new_workshop_auths = ['fabric', '3d', 'autocad', 'solidWork'];
if (!in_array($logged_auth, $page_auth)) {
    header("Location:" . URL);
    die;
}

$catalog = new Catalog();
$pa = new ProductAttribute();

// Get all base categories
$where_base = [];
$where_base[] = ['status', '=', '1'];
$get_categories = $catalog->getBaseCategories('*', $where_base);
?>
<style>
    .submenu-box {
        position: fixed;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 8px 0;
        width: 240px;
        z-index: 9999;
        display: none;
    }

    .submenu-box a {
        padding: 10px 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        text-decoration: none;
        cursor: pointer;
        transition: 0.2s ease;
        font-size: 14px;
    }

    .submenu-box a:hover {
        background: #f0f4ff;
        color: #1a73e8;
        transform: translateX(4px);
    }

    .submenu-box a:hover i {
        color: #1a73e8;
    }

    .category-table {
        width: 100%;
        border-collapse: collapse;
    }

    .category-table th {
        background: #f8f9fa;
        padding: 12px;
        border: 1px solid #dee2e6;
        font-weight: 600;
        text-align: left;
    }

    .category-table td {
        padding: 12px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }

    .category-row {
        transition: background-color 0.2s;
    }

    .category-row:hover {
        background-color: #f8f9fa;
    }

    .category-row.selected {
        background-color: #e3f2fd;
    }

    .category-name-cell {
        cursor: pointer;
    }

    .category-name-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 50px;
    }

    .category-image-small {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    .no-image-box {
        width: 50px;
        height: 50px;
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 12px;
    }

    .category-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .category-name {
        font-weight: 500;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .category-icon {
        font-size: 16px;
    }

    .base-cat-icon {
        color: #ff9800;
    }

    .main-cat-icon {
        color: #2196f3;
    }

    .sub-cat-icon {
        color: #4caf50;
    }

    .category-details {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        gap: 12px;
    }

    .category-columns-container {
        display: flex;
        gap: 15px;
    }

    .category-column {
        flex: 1;
        min-width: 0;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .column-header {
        background: #f8f9fa;
        padding: 16px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .column-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .column-content {
        max-height: 400px;
        overflow-y: auto;
        padding: 10px;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }

    .empty-state i {
        font-size: 48px;
        color: #adb5bd;
        margin-bottom: 16px;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 20px;
    }

    .sr-no {
        font-weight: 500;
        color: #495057;
        text-align: center;
        width: 40px;
    }

    .menu-divider {
        height: 1px;
        background: #e9ecef;
        margin: 5px 0;
    }

    .menu-header {
        padding: 10px 15px;
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 5px;
    }

    .menu-danger {
        color: #dc3545 !important;
    }

    .menu-danger:hover {
        background: #f8d7da !important;
        color: #dc3545 !important;
    }

    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-item {
        padding: 8px;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
        cursor: pointer;
    }

    .category-item:hover {
        background: #f8f9fa;
        border-color: #dee2e6;
    }

    .category-item.selected {
        background: #e3f2fd;
        border-color: #2196f3;
    }

    .category-item-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-item-name {
        font-weight: 500;
        color: #333;
        flex: 1;
    }

    .category-item-details {
        font-size: 11px;
        color: #6c757d;
        display: flex;
        justify-content: end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .category-count {
        background: #e9ecef;
        color: #495057;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 500;
    }

    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .column-loading {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .create-btn-small {
        margin-left: auto;
        padding: 4px 12px;
        font-size: 12px;
    }

    .qualifications-list::-webkit-scrollbar {
        width: 6px;
    }

    .qualifications-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .qualifications-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .qualifications-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .form-check-input:checked {
        background-color: #2196f3;
        border-color: #2196f3;
    }

    #selectAllQualifications {
        transform: scale(1.2);
        margin-right: 8px;
    }

    /* New styles for qualification edit modal */
    .price-row {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .price-row:last-child {
        border-bottom: none;
    }
</style>

<div id="submenu" class="submenu-box"></div>
<div class="content">
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Category Management</h4>
                </div>
                <div class="col-sm-6 text-right">
                </div>
            </div>
        </div>

        <!-- Three Column Layout -->
        <div class="category-columns-container">
            <!-- Column 1: Base Categories -->
            <div class="category-column">
                <div class="column-header">
                    <i class="fa fa-th-large base-cat-icon"></i>
                    <h5 class="mb-0">Base Categories</h5>
                    <span class="category-count" id="baseCount">0</span>
                    <button class="btn btn-sm btn-success create-btn-small" onclick="showCreateModal('base')">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <div class="column-content" id="baseCategoriesColumn">
                    <div class="column-loading">
                        <div class="loading-spinner"></div>
                        <p class="mt-2">Loading base categories...</p>
                    </div>
                </div>
            </div>

            <!-- Column 2: Categories -->
            <div class="category-column">
                <div class="column-header">
                    <i class="fa fa-folder-open main-cat-icon"></i>
                    <h5 class="mb-0">Categories</h5>
                    <span class="category-count" id="mainCount">0</span>
                    <button class="btn btn-sm btn-success create-btn-small" id="createMainBtn" style="display: none;" onclick="showCreateModal('main')">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <div class="column-content" id="mainCategoriesColumn">
                    <div class="empty-state">
                        <i class="fa fa-folder-open"></i>
                        <p>Select a base category to view its categories</p>
                    </div>
                </div>
            </div>

            <!-- Column 3: Qualifications -->
            <div class="category-column">
                <div class="column-header">
                    <i class="fa fa-folder sub-cat-icon"></i>
                    <h5 class="mb-0">Qualifications</h5>
                    <span class="category-count" id="subCount">0</span>
                    <button class="btn btn-sm btn-success create-btn-small" id="createSubBtn" style="display: none;" onclick="showCreateSubCategoryModal()">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <div class="column-content" id="subCategoriesColumn">
                    <div class="empty-state">
                        <i class="fa fa-folder"></i>
                        <p>Select a category to view its Qualifications</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createCategoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryTitle">Create New Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_type" id="category_type">
                    <input type="hidden" name="parent_id" id="parent_id">

                    <div class="form-group">
                        <label for="category_name">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="category_image">Category Image</label>
                        <input type="file" class="form-control-file" id="category_image" name="image" accept="image/*">
                        <small class="form-text text-muted">Allowed: JPG, PNG, GIF, WEBP (Max: 5MB)</small>
                    </div>

                    <!-- Only show web fields for categories (type 'main') -->
                    <div id="web_fields_container" style="display: none;">
                        <div class="form-group">
                            <label for="web_category_title">Web Category Title (Optional)</label>
                            <input type="text" class="form-control" id="web_category_title" name="web_category_title">
                        </div>

                        <div class="form-group">
                            <label for="web_category_desc">Web Category Description (Optional)</label>
                            <textarea class="form-control" id="web_category_desc" name="web_category_desc" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal (Simplified for non-sub categories) -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editCategoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalTitle">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_type" id="edit_category_type">
                    <input type="hidden" name="id" id="edit_category_id">

                    <div class="form-group">
                        <label for="edit_category_name">Category Name *</label>
                        <input type="text" class="form-control" id="edit_category_name" name="name" required>
                    </div>

                    <div id="current_image_container" class="mb-3"></div>

                    <div id="edit_web_fields_container" style="display: none;">
                        <div class="form-group">
                            <label for="edit_web_category_title">Web Category Title (Optional)</label>
                            <input type="text" class="form-control" id="edit_web_category_title" name="web_category_title">
                        </div>

                        <div class="form-group">
                            <label for="edit_web_category_desc">Web Category Description (Optional)</label>
                            <textarea class="form-control" id="edit_web_category_desc" name="web_category_desc" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Image Modal -->
<div class="modal fade" id="changeImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="changeImageForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Change Category Image</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_type" id="change_image_type">
                    <input type="hidden" name="id" id="change_image_id">

                    <div id="current_image_preview" class="mb-3"></div>

                    <div class="form-group">
                        <label for="new_category_image">New Image</label>
                        <input type="file" class="form-control-file" id="new_category_image" name="image" accept="image/*" required>
                        <small class="form-text text-muted">Allowed: JPG, PNG, GIF, WEBP (Max: 5MB)</small>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remove_existing_image" name="remove_image" value="1">
                        <label class="form-check-label text-danger" for="remove_existing_image">Remove current image</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Sub Category (Qualification) Modal -->
<div class="modal fade" id="createSubCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="createSubCategoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Qualification</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="add_qualification" value="1">
                    <input type="hidden" name="category" id="create_parent_category_id">
                    <input type="hidden" name="base_category" id="create_parent_base_id">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 d-none">
                                    <div class="form-group">
                                        <label>Base Category</label>
                                        <input type="text" class="form-control" id="create_base_category_display" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 d-none">
                                    <div class="form-group">
                                        <label>Category *</label>
                                        <input type="text" class="form-control" id="create_category_display" readonly>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Qualification Name *</label>
                                        <select name="attr_name" class="form-control required">
                                            <?php
                                            $pa = new ProductAttribute();
                                            $get_attrs = $pa->getProductAttributes('DISTINCT attr_name', ['attr_id', 'DESC']);
                                            foreach ($get_attrs as $row) {
                                                echo '<option value="' . $row->attr_name . '">' . $row->attr_name . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Catalog *</label>
                                        <select name="catalog_id" class="form-control required">
                                            <option value="">Select Catalog</option>
                                            <?php
                                            $catalogs = $catalog->getCatalogs('catalog_id,catalog_name', [['catalog_status', '=', '1']]);
                                            foreach ($catalogs as $cat) { ?>
                                                <option value="<?= $cat->catalog_id ?>"><?= $cat->catalog_name ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="attr_type" class="form-control">
                                            <option value="">Select Type</option>
                                            <option value="other">Other</option>
                                            <option value="curtain">Curtain</option>
                                            <option value="bed">Bed</option>
                                            <option value="carpet">Carpet</option>
                                            <option value="fitout">Fitout</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customs Code *</label>
                                        <input type="text" name="attr_customs_code" class="form-control required">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Calculation Type *</label>
                                        <select name="calculate_type" class="form-control required">
                                            <option value="standart">Standart</option>
                                            <option value="boy">Length</option>
                                            <option value="en">Width</option>
                                            <option value="yuksek">Height</option>
                                            <option value="enboy">Width x Length</option>
                                            <option value="yukseken">Width x Height</option>
                                            <option value="yuksekboy">Height x Length</option>
                                            <option value="hepsi">All</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Online Status *</label>
                                        <select name="attr_stock_online_status" class="form-control required">
                                            <option value="Online">Online</option>
                                            <option value="Offline">Offline</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Wholesale Percentage</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <input type="number" name="wholesale_percentage" class="form-control" min="0" max="100" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="attr_desc" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="img-cont">
                                <div class="badge badge-warning text-wrap mb-2">
                                    Size: 400 X 400 (px)
                                </div>
                                <label>Qualification Image *</label>
                                <div class="form-group">
                                    <input type="file" name="product_img" class="form-control-file" accept="image/*" required>
                                </div>

                                <div class="form-group">
                                    <label>Icon Image (Small)</label>
                                    <input type="file" name="icon" class="form-control-file" accept="image/*">
                                    <small class="text-muted">Small icon for display (Optional)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Fields -->
                    <div class="row mt-4 d-none">
                        <div class="col-md-12">
                            <hr>
                            <h6>Price Settings</h6>
                            <hr>
                        </div>
                    </div>

                    <div id="create_price_fields_container" class="d-none">
                        <?php for ($i = 0; $i < 9; $i++) {
                            $desc_text = '';
                            if ($i == 7) {
                                $desc_text = 'Sewing/Assembly';
                            } else if ($i == 8) {
                                $desc_text = 'Installation';
                            }
                        ?>
                            <div class="row price-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Price Name</label>
                                        <input type="text" name="name[<?php echo $i; ?>]" class="form-control" placeholder="Price Name">
                                        <?php if ($desc_text != '') { ?>
                                            <small class="text-muted">* <?php echo $desc_text; ?></small>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="type[<?php echo $i; ?>]" class="form-control">
                                            <option value="plus">+ Plus</option>
                                            <option value="minus">- Minus</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Rate</label>
                                        <input type="number" name="rate[<?php echo $i; ?>]" class="form-control make-numeric" placeholder="0.00" step="0.01">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Qualification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Sub Category (Qualification) Modal -->
<div class="modal fade" id="editSubCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editSubCategoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Qualification</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_qualification" value="1">
                    <input type="hidden" name="attr_id" id="edit_attr_id">
                    <input type="hidden" name="base_category" id="edit_base_category_id">
                    <input type="hidden" name="category" id="edit_category_id">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 d-none">
                                    <div class="form-group">
                                        <label>Base Category</label>
                                        <input type="text" class="form-control" id="edit_base_category_display" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 d-none">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <input type="text" class="form-control" id="edit_category_display" readonly>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Qualification Name *</label>
                                        <select name="attr_name" id="edit_attr_name" class="form-control required">
                                            <?php
                                            $get_attrs = $pa->getProductAttributes('DISTINCT attr_name', ['attr_id', 'DESC']);
                                            foreach ($get_attrs as $row) {
                                                echo '<option value="' . $row->attr_name . '">' . $row->attr_name . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Catalog *</label>
                                        <select name="catalog_id" id="edit_catalog_id" class="form-control required">
                                            <option value="">Select Catalog</option>
                                            <?php
                                            $catalogs = $catalog->getCatalogs('catalog_id,catalog_name', [['catalog_status', '=', '1']]);
                                            foreach ($catalogs as $cat) { ?>
                                                <option value="<?= $cat->catalog_id ?>"><?= $cat->catalog_name ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="attr_type" id="edit_attr_type" class="form-control">
                                            <option value="">Select Type</option>
                                            <option value="other">Other</option>
                                            <option value="curtain">Curtain</option>
                                            <option value="bed">Bed</option>
                                            <option value="carpet">Carpet</option>
                                            <option value="fitout">Fitout</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customs Code *</label>
                                        <input type="text" name="attr_customs_code" id="edit_attr_customs_code" class="form-control required">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Calculation Type *</label>
                                        <select name="calculate_type" id="edit_calculate_type" class="form-control required">
                                            <option value="standart">Standart</option>
                                            <option value="boy">Length</option>
                                            <option value="en">Width</option>
                                            <option value="yuksek">Height</option>
                                            <option value="enboy">Width x Length</option>
                                            <option value="yukseken">Width x Height</option>
                                            <option value="yuksekboy">Height x Length</option>
                                            <option value="hepsi">All</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Online Status *</label>
                                        <select name="attr_stock_online_status" id="edit_attr_stock_online_status" class="form-control required">
                                            <option value="Online">Online</option>
                                            <option value="Offline">Offline</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Wholesale Percentage</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <input type="number" name="wholesale_percentage" id="edit_wholesale_percentage" class="form-control" min="0" max="100" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="attr_desc" id="edit_attr_desc" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="img-cont">
                                <div class="badge badge-warning text-wrap mb-2">
                                    Size: 400 X 400 (px)
                                </div>
                                <label>Qualification Image</label>
                                <div id="current_qual_image" class="mb-3 text-center"></div>
                                <div class="form-group">
                                    <input type="file" name="product_img" class="form-control-file" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>

                                <div class="form-group">
                                    <label>Icon Image (Small)</label>
                                    <div id="current_icon_preview" class="mb-2"></div>
                                    <input type="file" name="icon" class="form-control-file" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current icon</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Fields -->
                    <div class="row mt-4 d-none">
                        <div class="col-md-12">
                            <hr>
                            <h6>Price Settings</h6>
                            <hr>
                        </div>
                    </div>

                    <div id="edit_price_fields_container" class="d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <p>Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <p id="deleteWarningMessage" class="text-muted small"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteCategory()">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shiftCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="shiftCategoryForm">
                <div class="modal-header">
                    <h5 class="modal-title">Shift Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="shift_category" value="1">
                    <input type="hidden" name="source_type" id="source_type">
                    <input type="hidden" name="source_id" id="source_id">

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle mr-2"></i>
                        <strong>Moving:</strong> <span id="shift_source_name" class="font-weight-bold"></span>
                        <br>
                        <small>You can shift this category to another level or within the same level.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_type">Target Level *</label>
                                <select class="form-control" id="target_type" name="target_type" required onchange="loadTargetOptions()">
                                    <option value="">-- Select Target Level --</option>
                                    <option value="base">Base Category</option>
                                    <option value="main">Category</option>
                                    <option value="sub">Qualification</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_id">Target Category *</label>
                                <div id="target_options_container">
                                    <select class="form-control" id="target_id" name="target_id" required disabled>
                                        <option value="">Select target level first</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="shift_options_container" class="mt-3">
                        <!-- Dynamic options will be loaded here -->
                    </div>

                    <div id="shift_preview" class="mt-3 border p-3 rounded" style="display: none;">
                        <h6>Preview:</h6>
                        <div id="shift_preview_content"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Shift Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
define('inc_panel_footer', true);
include PATH . '/inc/footer.php';
?>

<script>
    // Global variables
    let selectedCategory = {
        type: '',
        id: '',
        name: ''
    };
    // Global variables for shift feature
    let shiftContext = {
        sourceType: '',
        sourceId: '',
        sourceName: '',
        targetType: '',
        targetId: ''
    };

    let selectedBaseId = null;
    let selectedMainId = null;
    let selectedMainName = null;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadBaseCategories();
    });

    // Load base categories
    function loadBaseCategories() {
        const column = document.getElementById('baseCategoriesColumn');
        column.innerHTML = '<div class="column-loading"><div class="loading-spinner"></div><p class="mt-2">Loading base categories...</p></div>';

        $.post(ajax_url + '/api', {
            get_base_categories: 1
        }, function(data) {
            var response = $.parseJSON(data);
            var status = response.status;
            var categories = response.data;

            if (status === 'success') {
                renderBaseCategories(categories);
                document.getElementById('baseCount').textContent = categories.length;
            } else {
                column.innerHTML = `<div class="empty-state">
                <i class="fa fa-exclamation-triangle"></i>
                <p>Error loading base categories</p>
            </div>`;
            }
        }).fail(function() {
            column.innerHTML = `<div class="empty-state">
            <i class="fa fa-exclamation-triangle"></i>
            <p>Error loading base categories</p>
        </div>`;
        });
    }

    // Render base categories
    function renderBaseCategories(categories) {
        const column = document.getElementById('baseCategoriesColumn');

        if (categories.length === 0) {
            column.innerHTML = `<div class="empty-state">
            <i class="fa fa-folder-open"></i>
            <p>No base categories found</p>
            </div>`;
            return;
        }

        let html = '<ul class="category-list">';
        categories.forEach((cat, index) => {
            const isSelected = selectedBaseId === cat.id;

            html += `
            <li class="category-item ${isSelected ? 'selected' : ''}" 
                onclick="selectBaseCategory(${cat.id}, '${escapeHtml(cat.name)}')"
                oncontextmenu="openCategoryMenu('base', ${cat.id}, '${escapeHtml(cat.name)}', event); return false;">
                <div class="category-item-header">
                    <div class="category-icon">
                        ${cat.icon ? `<img src="<?= URL ?>/uploads/${cat.icon}" alt="${cat.name}" style="width: 30px; height: 30px; object-fit: cover;">` : `<i class="fa fa-th-large"></i>`}
                    </div>
                    <span class="category-item-name">${cat.name}</span>
                </div>
            </li>
        `;
        });
        html += '</ul>';

        column.innerHTML = html;
    }

    // Select base category
    function selectBaseCategory(id, name) {
        selectedBaseId = id;
        selectedMainId = null;
        selectedMainName = null;

        // Update UI
        document.querySelectorAll('.category-item.selected').forEach(el => {
            el.classList.remove('selected');
        });
        event.target.closest('.category-item').classList.add('selected');

        // Show create button for categories
        document.getElementById('createMainBtn').style.display = 'block';
        document.getElementById('createMainBtn').setAttribute('onclick', `showCreateModal('main', ${id})`);

        // Load categories
        loadMainCategories(id);

        // Clear Qualifications
        document.getElementById('subCategoriesColumn').innerHTML = `<div class="empty-state">
        <i class="fa fa-folder"></i>
        <p>Select a category to view its qualifications</p>
    </div>`;
        document.getElementById('subCount').textContent = '0';
        document.getElementById('createSubBtn').style.display = 'none';
    }

    // Load categories for selected base
    function loadMainCategories(baseId) {
        const column = document.getElementById('mainCategoriesColumn');
        column.innerHTML = '<div class="column-loading"><div class="loading-spinner"></div><p class="mt-2">Loading categories...</p></div>';

        $.post(ajax_url + '/api', {
            get_main_categories_by_base: 1,
            base_id: baseId
        }, function(data) {
            var response = $.parseJSON(data);
            var status = response.status;
            var categories = response.data;

            if (status === 'success') {
                renderMainCategories(categories);
                document.getElementById('mainCount').textContent = categories.length;
            } else {
                column.innerHTML = `<div class="empty-state">
                <i class="fa fa-exclamation-triangle"></i>
                <p>Error loading categories</p>
            </div>`;
            }
        }).fail(function() {
            column.innerHTML = `<div class="empty-state">
            <i class="fa fa-exclamation-triangle"></i>
            <p>Error loading categories</p>
        </div>`;
        });
    }

    // Render categories
    function renderMainCategories(categories) {
        const column = document.getElementById('mainCategoriesColumn');

        if (categories.length === 0) {
            column.innerHTML = `<div class="empty-state">
            <i class="fa fa-folder-open"></i>
            <p>No categories found for this base category</p>
        </div>`;
            return;
        }

        let html = '<ul class="category-list">';
        categories.forEach((cat, index) => {
            const isSelected = selectedMainId === cat.id;

            html += `
            <li class="category-item ${isSelected ? 'selected' : ''}" 
                onclick="selectMainCategory(${cat.id}, '${escapeHtml(cat.category)}')"
                oncontextmenu="openCategoryMenu('main', ${cat.id}, '${escapeHtml(cat.category)}', event); return false;">
                <div class="category-item-header">
                    <div class="category-icon">
                        ${cat.image ? `<img src="<?= URL ?>/uploads/${cat.image}" alt="${cat.category}" style="width: 30px; height: 30px; object-fit: cover;">` : `<i class="fa fa-folder-open"></i>`}
                    </div>
                    <span class="category-item-name">${cat.category}</span>
                </div>
            </li>
        `;
        });
        html += '</ul>';

        column.innerHTML = html;
    }

    // Select category
    function selectMainCategory(id, name) {
        selectedMainId = id;
        selectedMainName = name;

        // Update UI
        document.querySelectorAll('#mainCategoriesColumn .category-item.selected').forEach(el => {
            el.classList.remove('selected');
        });
        event.target.closest('.category-item').classList.add('selected');

        // Show create button for qualifications
        document.getElementById('createSubBtn').style.display = 'block';
        document.getElementById('createSubBtn').setAttribute('onclick', `showCreateSubCategoryModal()`);

        // Load qualifications
        loadSubCategories(id);
    }

    // Load qualifications for selected main
    function loadSubCategories(mainId) {
        const column = document.getElementById('subCategoriesColumn');
        column.innerHTML = '<div class="column-loading"><div class="loading-spinner"></div><p class="mt-2">Loading qualifications...</p></div>';

        $.post(ajax_url + '/api', {
            get_qualifications: 1,
            web_menu_id: mainId
        }, function(data) {
            var response = $.parseJSON(data);
            var status = response.status;
            var categories = response.data;

            if (status === 'success') {
                renderSubCategories(categories);
                document.getElementById('subCount').textContent = categories.length;
            } else {
                column.innerHTML = `<div class="empty-state">
                <i class="fa fa-exclamation-triangle"></i>
                <p>Error loading qualifications</p>
            </div>`;
            }
        }).fail(function() {
            column.innerHTML = `<div class="empty-state">
            <i class="fa fa-exclamation-triangle"></i>
            <p>Error loading qualifications</p>
        </div>`;
        });
    }

    // Render qualifications
    function renderSubCategories(categories) {
        const column = document.getElementById('subCategoriesColumn');

        if (categories.length === 0) {
            column.innerHTML = `<div class="empty-state">
            <i class="fa fa-folder"></i>
            <p>No qualifications found for this category</p>
            </div>`;
            return;
        }

        let html = '<ul class="category-list">';
        categories.forEach((cat, index) => {
            html += `
            <li class="category-item" 
                oncontextmenu="openCategoryMenu('sub', ${cat.attr_id}, '${escapeHtml(cat.category || cat.attr_name)}', event); return false;">
                <div class="category-item-header">
                    <div class="category-icon">
                        ${cat.icon ? `<img src="<?= URL ?>/uploads/online-img/${cat.icon}" alt="${cat.attr_name}" style="width: 30px; height: 30px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= URL ?>/uploads/${cat.icon}'">` : `<i class="fa fa-graduation-cap"></i>`}
                    </div>
                    <span class="category-item-name">${cat.attr_name}</span>
                    <span class="category-item-details">
                        <span class="badge ${cat.attr_online_status === 'Online' ? 'badge-success' : 'badge-secondary'}">${cat.attr_online_status}</span>
                    </span>
                </div>
            </li>
        `;
        });
        html += '</ul>';

        column.innerHTML = html;
    }

    // Open category menu on right-click
    function openCategoryMenu(type, id, name, event) {
        event.preventDefault();
        event.stopPropagation();

        selectedCategory = {
            type,
            id,
            name
        };

        let submenu = document.getElementById("submenu");
        let menuItems = '';

        // Add menu header
        menuItems += `<div class="menu-header">
        <i class="fa fa-${type === 'base' ? 'th-large' : type === 'main' ? 'folder-open' : 'folder'} mr-1"></i>
        ${name}
    </div>`;

        if (type === 'base') {
            menuItems += `
            <a onclick="editCategory('base', ${id})">
                <i class="fa fa-edit mr-1"></i> Edit Base Category
            </a>
            <a onclick="showShiftModal('base', ${id}, '${escapeHtml(name)}')">
                <i class="fa fa-exchange mr-1"></i> Shift Base Category
            </a>
            <a onclick="duplicateCategory('base', ${id})">
                <i class="fa fa-copy mr-1"></i> Duplicate
            </a>
            <a onclick="changeImage('base', ${id})">
                <i class="fa fa-image mr-1"></i> Change Image
            </a>
            <div class="menu-divider"></div>
            <a class="menu-danger" onclick="showDeleteModal('base', ${id}, '${name}')">
                <i class="fa fa-trash mr-1"></i> Delete Base Category
            </a>
        `;
        } else if (type === 'main') {
            menuItems += `
            <a onclick="editCategory('main', ${id})">
                <i class="fa fa-edit mr-1"></i> Edit Category
            </a>
            <a onclick="showQualificationsTable(${id}, '${escapeHtml(name)}')">
                <i class="fa fa-exchange mr-1"></i> Shift Qualifications
            </a>
            <a onclick="showShiftModal('main', ${id}, '${escapeHtml(name)}')">
                <i class="fa fa-exchange mr-1"></i> Shift Entire Category
            </a>
            <a onclick="duplicateCategory('main', ${id})">
                <i class="fa fa-copy mr-1"></i> Duplicate
            </a>
            <a onclick="changeImage('main', ${id})">
                <i class="fa fa-image mr-1"></i> Change Image
            </a>
            <div class="menu-divider"></div>
            <a class="menu-danger" onclick="showDeleteModal('main', ${id}, '${name}')">
                <i class="fa fa-trash mr-1"></i> Delete Category
            </a>
        `;
        } else if (type === 'sub') {
            menuItems += `
            <a onclick="editSubCategory(${id})">
                <i class="fa fa-edit mr-1"></i> Edit Qualification
            </a>
            <a onclick="showShiftModal('sub', ${id}, '${escapeHtml(name)}')">
                <i class="fa fa-exchange mr-1"></i> Shift Qualification
            </a>
            <a onclick="duplicateCategory('sub', ${id})">
                <i class="fa fa-copy mr-1"></i> Duplicate
            </a>
            <a onclick="changeImage('sub', ${id})">
                <i class="fa fa-image mr-1"></i> Change Image
            </a>
            <div class="menu-divider"></div>
            <a class="menu-danger" onclick="showDeleteModal('sub', ${id}, '${name}')">
                <i class="fa fa-trash mr-1"></i> Delete Qualification
            </a>
        `;
        }

        submenu.innerHTML = menuItems;
        submenu.style.display = "block";

        // Position menu near click
        const clickX = event.clientX;
        const clickY = event.clientY;
        const menuWidth = 240;
        const menuHeight = submenu.offsetHeight;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        let left = clickX;
        let top = clickY;

        // Adjust if near right edge
        if (left + menuWidth > windowWidth) {
            left = windowWidth - menuWidth - 10;
        }

        // Adjust if near bottom edge
        if (top + menuHeight > windowHeight) {
            top = windowHeight - menuHeight - 10;
        }

        // Ensure minimum distance from edges
        left = Math.max(10, Math.min(left, windowWidth - menuWidth - 10));
        top = Math.max(10, Math.min(top, windowHeight - menuHeight - 10));

        submenu.style.left = left + 'px';
        submenu.style.top = top + 'px';
    }

    // Show shift modal for any category type
    function showShiftModal(type, id, name) {
        // Close menu
        document.getElementById("submenu").style.display = "none";

        // Set context
        shiftContext = {
            sourceType: type,
            sourceId: id,
            sourceName: name,
            targetType: '',
            targetId: ''
        };

        // Set form values
        $('#source_type').val(type);
        $('#source_id').val(id);
        $('#shift_source_name').text(name);

        // Clear previous selections
        $('#target_type').val('');
        $('#target_id').prop('disabled', true);
        $('#target_id').html('<option value="">Select target level first</option>');
        $('#shift_options_container').html('');
        $('#shift_preview').hide();

        // Show modal
        $('#shiftCategoryModal').modal('show');
    }

    // Load target options based on selected target type
    function loadTargetOptions() {
        const sourceType = shiftContext.sourceType;
        const sourceId = shiftContext.sourceId;
        const targetType = $('#target_type').val();

        if (!targetType) return;

        shiftContext.targetType = targetType;

        // Enable target select
        $('#target_id').prop('disabled', false);

        // Show loading
        $('#target_id').html('<option value="">Loading...</option>');

        // Load appropriate options based on source and target types
        $.post(ajax_url + '/api', {
            get_shift_targets: 1,
            source_type: sourceType,
            source_id: sourceId,
            target_type: targetType
        }, function(data) {
            var response = $.parseJSON(data);

            if (response.status === 'success') {
                const options = response.data;
                let html = '<option value="">-- Select Target --</option>';

                if (targetType === 'base') {
                    // For base targets
                    options.forEach(option => {
                        html += `<option value="${option.id}">${option.name}</option>`;
                    });
                } else if (targetType === 'main') {
                    // For main targets
                    options.forEach(option => {
                        html += `<option value="${option.id}">${option.category} (Base: ${option.base_category_name})</option>`;
                    });
                } else if (targetType === 'sub') {
                    // For sub targets
                    options.forEach(option => {
                        html += `<option value="${option.id}">${option.attr_name} (Category: ${option.category_name})</option>`;
                    });
                }

                $('#target_id').html(html);

                // Show/hide additional options based on selection
                $('#shift_options_container').html('');
                if (sourceType !== targetType) {
                    showShiftOptions();
                }
            } else {
                $('#target_id').html('<option value="">Error loading options</option>');
            }
        }).fail(function() {
            $('#target_id').html('<option value="">Error loading options</option>');
        });
    }

    // Show shift options based on operation type
    function showShiftOptions() {
        const sourceType = shiftContext.sourceType;
        const targetType = shiftContext.targetType;

        let optionsHtml = '';

        if (sourceType === 'base' && targetType === 'main') {
            // Base → Main
            optionsHtml = `
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                <strong>Warning:</strong> This will convert the base category into a main category.
                All existing main categories under this base will be moved to the target base.
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="convert_structure" name="convert_structure" value="1" checked>
                <label class="form-check-label" for="convert_structure">
                    Move all categories under this base to target
                </label>
            </div>
        `;
        } else if (sourceType === 'main' && targetType === 'base') {
            // Main → Base
            optionsHtml = `
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                <strong>Warning:</strong> This will convert the main category into a base category.
                All qualifications under this category will become main categories under the new base.
            </div>
        `;
        } else if (sourceType === 'main' && targetType === 'sub') {
            // Main → Sub
            optionsHtml = `
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                <strong>Warning:</strong> This will convert the category into a qualification.
                All qualifications under this category will be merged into the target category.
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="merge_qualifications" name="merge_qualifications" value="1" checked>
                <label class="form-check-label" for="merge_qualifications">
                    Merge existing qualifications with target
                </label>
            </div>
        `;
        } else if (sourceType === 'sub' && targetType === 'main') {
            // Sub → Main
            optionsHtml = `
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                <strong>Warning:</strong> This will convert the qualification into a main category.
                Products linked to this qualification will need manual review.
            </div>
        `;
        }

        $('#shift_options_container').html(optionsHtml);
    }

    // Load shift options based on selected target
    function loadShiftOptions() {
        const sourceType = shiftContext.sourceType;
        const targetType = shiftContext.targetType;

        if (!targetType || sourceType === targetType) return;

        let optionsHtml = '';

        if (sourceType === 'base' && targetType === 'main') {
            // Base → Main (convert base to main under another base)
            optionsHtml = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Note:</strong> This will convert the base category into a main category under the selected target base.
                    All existing main categories under this base will become sub-categories of the new main category.
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="convert_structure" name="convert_structure" value="1" checked>
                    <label class="form-check-label" for="convert_structure">
                        Convert structure (recommended)
                    </label>
                </div>
            `;
        } else if (sourceType === 'base' && targetType === 'sub') {
            // Base → Sub (convert base to sub under a main category)
            optionsHtml = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Note:</strong> This will convert the base category into a qualification under the selected target category.
                    The entire hierarchy will be flattened into a single qualification level.
                </div>
                <div class="form-group">
                    <label for="new_qualification_name">Qualification Name *</label>
                    <input type="text" class="form-control" id="new_qualification_name" name="new_qualification_name" placeholder="Enter new qualification name">
                </div>
            `;
        } else if (sourceType === 'main' && targetType === 'base') {
            // Main → Base (convert main to base)
            optionsHtml = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Note:</strong> This will convert the category into a base category.
                    All qualifications under this category will become main categories under the new base.
                </div>
            `;
        } else if (sourceType === 'main' && targetType === 'sub') {
            // Main → Sub (convert main to sub under another main)
            optionsHtml = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Note:</strong> This will convert the category into a qualification under the selected target category.
                    All qualifications under this category will be moved to the target category.
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="merge_qualifications" name="merge_qualifications" value="1" checked>
                    <label class="form-check-label" for="merge_qualifications">
                        Merge qualifications with target category
                    </label>
                </div>
            `;
        } else if (sourceType === 'sub' && targetType === 'base') {
            // Sub → Base (convert sub to base)
            optionsHtml = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Note:</strong> This will convert the qualification into a base category.
                    This creates a new base category with the qualification as its name.
                </div>
            `;
        } else if (sourceType === 'sub' && targetType === 'main') {
            // Sub → Main (convert sub to main)
            optionsHtml = `
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    <strong>Note:</strong> This will convert the qualification into a category under the selected target base.
                </div>
            `;
        }

        $('#shift_options_container').html(optionsHtml);
    }

    // Handle shift form submission
    $(document).on('submit', '#shiftCategoryForm', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const targetId = $('#target_id').val();
        const targetType = $('#target_type').val();

        if (!targetId || !targetType) {
            alert('Please select a target category');
            return;
        }

        // Get confirmation message
        const sourceType = $('#source_type').val();
        const sourceName = $('#shift_source_name').text();
        const targetName = $('#target_id option:selected').text();

        let confirmMessage = `Are you sure you want to shift "${sourceName}" to "${targetName}"?\n`;

        if (sourceType !== targetType) {
            confirmMessage += '\n⚠️ This operation changes the category level and may affect product relationships.';
        }

        if (confirm(confirmMessage)) {
            $.ajax({
                url: ajax_url + '/api',
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    // Show loading
                    $('#shiftCategoryForm button[type="submit"]')
                        .prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm mr-2"></span>Processing...');
                },
                success: function(data) {
                    var response = $.parseJSON(data);

                    if (response.status === 'success') {
                        alert('Category shifted successfully');
                        $('#shiftCategoryModal').modal('hide');
                        refreshColumns();
                    } else {
                        alert('Error shifting category: ' + response.message);
                        $('#shiftCategoryForm button[type="submit"]')
                            .prop('disabled', false)
                            .html('Shift Category');
                    }
                },
                error: function() {
                    alert('Error shifting category');
                    $('#shiftCategoryForm button[type="submit"]')
                        .prop('disabled', false)
                        .html('Shift Category');
                }
            });
        }
    });

    function showSimpleShiftModal(type, id, name) {
        // Close menu
        document.getElementById("submenu").style.display = "none";

        // For main categories, we'll show the qualifications table
        if (type === 'main') {
            showQualificationsTable(id, name);
        } else {
            // For base and sub, show the normal shift modal
            showShiftModal(type, id, name);
        }
    }

    // Close submenu on click anywhere
    document.addEventListener("click", function(event) {
        const submenu = document.getElementById("submenu");
        if (submenu && !submenu.contains(event.target)) {
            submenu.style.display = "none";
        }
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Show create modal
    function showCreateModal(type, parentId = null) {
        $('#createCategoryModal').modal('show');
        document.getElementById('category_type').value = type;
        document.getElementById('parent_id').value = parentId || '';

        // Set modal title and show/hide web fields
        if (type === 'base') {
            document.getElementById('createCategoryTitle').textContent = 'Create Base Category';
            document.getElementById('web_fields_container').style.display = 'none';
        } else if (type === 'main') {
            document.getElementById('createCategoryTitle').textContent = 'Create Category';
            document.getElementById('web_fields_container').style.display = 'block';
            // Parent ID is automatically set to selected base category
        }

        // Reset form
        document.getElementById('createCategoryForm').reset();
    }

    // Show create sub-category modal
    function showCreateSubCategoryModal() {
        if (!selectedMainId || !selectedMainName || !selectedBaseId) {
            alert('Please select a category first');
            return;
        }

        // Get base category name
        $.post(ajax_url + '/api', {
            get_category_details: 1,
            category_type: 'main',
            category_id: selectedMainId
        }, function(data) {
            var response = $.parseJSON(data);
            if (response.status === 'success') {
                const category = response.data;

                // Populate display fields
                $('#create_parent_category_id').val(selectedMainId);
                $('#create_parent_base_id').val(category.base_category);
                $('#create_base_category_display').val(category.base_category_name || 'Base Category');
                $('#create_category_display').val(selectedMainName);

                $('#createSubCategoryModal').modal('show');
            }
        });
    }

    // Edit category
    function editCategory(type, id) {
        // Close menu
        document.getElementById("submenu").style.display = "none";

        // For base and main categories, use simplified modal
        $.post(ajax_url + '/api', {
            get_category_details: 1,
            category_type: type,
            category_id: id
        }, function(data) {
            var response = $.parseJSON(data);
            var status = response.status;
            var category = response.data;

            if (status === 'success') {
                document.getElementById('edit_category_type').value = type;
                document.getElementById('edit_category_id').value = id;
                document.getElementById('edit_category_name').value = category.name || category.category;

                // Show/hide web fields
                if (type === 'main') {
                    document.getElementById('edit_web_fields_container').style.display = 'block';
                    document.getElementById('edit_web_category_title').value = category.web_category_title || '';
                    document.getElementById('edit_web_category_desc').value = category.web_category_desc || '';
                } else {
                    document.getElementById('edit_web_fields_container').style.display = 'none';
                }

                // Show current image
                let imageContainer = document.getElementById('current_image_container');
                if (category.image && category.image !== '') {
                    const imageUrl = type === 'base' ? `<?= URL ?>/uploads/${category.icon}` : `<?= URL ?>/uploads/${category.image}`;
                    imageContainer.innerHTML = `
                    <label>Current Image:</label><br>
                    <img src="${imageUrl}" 
                         alt="Image" 
                         style="max-width: 150px; max-height: 150px; object-fit: cover;" 
                         class="img-thumbnail">
                `;
                } else {
                    imageContainer.innerHTML = '<p class="text-muted">No current image</p>';
                }

                // Update modal title
                document.getElementById('editCategoryModalTitle').textContent = `Edit ${type === 'base' ? 'Base ' : ''}Category`;
                $('#editCategoryModal').modal('show');
            }
        });
    }

    // Edit sub-category (qualification)
    function editSubCategory(id) {
        // Close menu
        document.getElementById("submenu").style.display = "none";

        $.post(ajax_url + '/api', {
            get_category_details: 1,
            category_id: id,
            category_type: 'sub'
        }, function(data) {
            var response = $.parseJSON(data);

            if (response.status === 'success') {
                const qual = response.data;

                // Populate form fields
                $('#edit_attr_id').val(id);
                $('#edit_attr_name').val(qual.attr_name);
                $('#edit_catalog_id').val(qual.catalog_id);
                $('#edit_attr_type').val(qual.attr_type);
                $('#edit_attr_customs_code').val(qual.attr_customs_code);
                $('#edit_calculate_type').val(qual.calculate_type);
                $('#edit_attr_stock_online_status').val(qual.attr_online_status);
                $('#edit_wholesale_percentage').val(qual.wholesale_percentage);
                $('#edit_attr_desc').val(qual.attr_desc);

                // Set parent category info
                $('#edit_base_category_id').val(qual.base_category);
                $('#edit_category_id').val(qual.web_menu_id);
                $('#edit_base_category_display').val(qual.base_category_name || '');
                $('#edit_category_display').val(qual.category_name || '');

                // Show current images
                if (qual.online_product_img) {
                    $('#current_qual_image').html(`
                        <img src="<?= URL ?>/uploads/online-img/${qual.online_product_img}" 
                             alt="Qualification Image" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 200px;"  
                             onerror="this.onerror=null; this.src='<?= URL ?>/uploads/${qual.icon}'">
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="remove_product_img" name="remove_product_img" value="1">
                            <label class="form-check-label text-danger" for="remove_product_img">Remove current image</label>
                        </div>
                    `);
                } else {
                    $('#current_qual_image').html('<p class="text-muted">No current image</p>');
                }

                // Show current icon
                if (qual.icon) {
                    $('#current_icon_preview').html(`
                        <img src="<?= URL ?>/uploads/online-img/${qual.icon}" 
                             alt="Icon" 
                             class="img-thumbnail" 
                             style="max-width: 50px; max-height: 50px;"  
                             onerror="this.onerror=null; this.src='<?= URL ?>/uploads/${qual.icon}'">
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="remove_icon" name="remove_icon" value="1">
                            <label class="form-check-label text-danger" for="remove_icon">Remove current icon</label>
                        </div>
                    `);
                } else {
                    $('#current_icon_preview').html('<p class="text-muted">No current icon</p>');
                }

                // Load price fields
                loadPriceFields(id);

                $('#editSubCategoryModal').modal('show');
            } else {
                alert('Error loading qualification details');
            }
        }).fail(function() {
            alert('Error loading qualification details');
        });
    }

    // Load price fields for qualification
    function loadPriceFields(attrId) {
        $.post(ajax_url + '/api', {
            get_qualification_prices: 1,
            attr_id: attrId
        }, function(data) {
            var response = $.parseJSON(data);
            let html = '';

            if (response.status === 'success') {
                const prices = response.data;

                for (let i = 0; i < 9; i++) {
                    const price = prices[i] || {
                        name: '',
                        type: 'plus',
                        rate: ''
                    };
                    const desc_text = i === 7 ? 'Sewing/Assembly' : i === 8 ? 'Installation' : '';

                    html += `
                    <div class="row price-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Price Name</label>
                                <input type="text" name="name[${i}]" class="form-control" value="${price.name}" placeholder="Price Name">
                                <input type="hidden" name="price_id[${i}]" value="${price.price_id || ''}">
                                ${desc_text ? `<small class="text-muted">* ${desc_text}</small>` : ''}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type[${i}]" class="form-control">
                                    <option value="plus" ${price.type === 'plus' ? 'selected' : ''}>+ Plus</option>
                                    <option value="minus" ${price.type === 'minus' ? 'selected' : ''}>- Minus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Rate</label>
                                <input type="number" name="rate[${i}]" class="form-control make-numeric" value="${price.rate}" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                    </div>
                    `;
                }
            } else {
                // Default empty price fields
                for (let i = 0; i < 9; i++) {
                    const desc_text = i === 7 ? 'Sewing/Assembly' : i === 8 ? 'Installation' : '';
                    html += `
                    <div class="row price-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Price Name</label>
                                <input type="text" name="name[${i}]" class="form-control" placeholder="Price Name">
                                ${desc_text ? `<small class="text-muted">* ${desc_text}</small>` : ''}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type[${i}]" class="form-control">
                                    <option value="plus">+ Plus</option>
                                    <option value="minus">- Minus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Rate</label>
                                <input type="number" name="rate[${i}]" class="form-control make-numeric" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                    </div>
                    `;
                }
            }

            $('#edit_price_fields_container').html(html);
        }).fail(function() {
            alert('Error loading price fields');
        });
    }

    // Show delete confirmation modal
    function showDeleteModal(type, id, name) {
        selectedCategory = {
            type,
            id,
            name
        };

        document.getElementById('deleteCategoryName').textContent = name;

        let warningMessage = '';
        if (type === 'base') {
            warningMessage = 'All categories and qualifications under this base category will also be deleted.';
        } else if (type === 'main') {
            warningMessage = 'All qualifications under this category will also be deleted.';
        } else {
            warningMessage = 'This qualification will be permanently deleted.';
        }

        document.getElementById('deleteWarningMessage').textContent = warningMessage;

        // Close menu
        document.getElementById("submenu").style.display = "none";

        $('#deleteCategoryModal').modal('show');
    }

    // Confirm delete
    function confirmDeleteCategory() {
        const {
            type,
            id
        } = selectedCategory;

        // Perform AJAX delete
        $.post(ajax_url + '/api', {
            delete_category: 1,
            category_type: type,
            category_id: id
        }, function(data) {
            var response = $.parseJSON(data);
            var status = response.status;
            var message = response.message;

            if (status === 'success') {
                alert('Category deleted successfully');
                refreshColumns();
                $('#deleteCategoryModal').modal('hide');
            } else {
                alert('Error deleting category: ' + message);
            }
        }).fail(function() {
            alert('Error deleting category');
        });
    }

    // Duplicate category
    function duplicateCategory(type, id) {
        // Close menu
        document.getElementById("submenu").style.display = "none";

        if (confirm('Duplicate this category?')) {
            $.post(ajax_url + '/api', {
                duplicate_category: 1,
                category_type: type,
                category_id: id
            }, function(data) {
                var response = $.parseJSON(data);
                var status = response.status;
                var message = response.message;

                if (status === 'success') {
                    alert('Category duplicated successfully');
                    refreshColumns();
                } else {
                    alert('Error duplicating category: ' + message);
                }
            }).fail(function() {
                alert('Error duplicating category');
            });
        }
    }

    // Function to change image
    function changeImage(type, id) {
        // Close menu
        document.getElementById("submenu").style.display = "none";

        // Fetch category data
        $.post(ajax_url + '/api', {
            get_category_details: 1,
            category_type: type,
            category_id: id
        }, function(data) {
            var response = $.parseJSON(data);

            if (response.status === 'success') {
                const category = response.data;

                // Populate change image modal
                $('#change_image_type').val(type);
                $('#change_image_id').val(id);

                // Show current image
                let imagePreview = document.getElementById('current_image_preview');
                if ((category.image || category.icon) && type !== 'sub') {
                    const imageUrl = type === 'base' ?
                        `<?= URL ?>/uploads/${category.icon}` :
                        `<?= URL ?>/uploads/${category.image}`;

                    imagePreview.innerHTML = `
                    <label>Current Image:</label><br>
                    <img src="${imageUrl}" 
                         alt="Current Image" 
                         style="max-width: 200px; max-height: 200px; object-fit: cover;" 
                         class="img-thumbnail mb-2">
                    `;
                } else if (type === 'sub' && category.icon) {
                    imagePreview.innerHTML = `
                    <label>Current Icon:</label><br>
                    <img src="<?= URL ?>/uploads/online-img/${category.icon}" 
                         alt="Current Icon" 
                         style="max-width: 100px; max-height: 100px; object-fit: cover;" 
                         class="img-thumbnail mb-2" 
                         onerror="this.onerror=null; this.src='<?= URL ?>/uploads/${category.icon}'">
                    `;
                } else {
                    imagePreview.innerHTML = '<p class="text-muted">No current image</p>';
                }

                // Show modal
                $('#changeImageModal').modal('show');
            }
        });
    }

    // Refresh all columns
    function refreshColumns() {
        loadBaseCategories();

        if (selectedBaseId) {
            loadMainCategories(selectedBaseId);
        } else {
            document.getElementById('mainCategoriesColumn').innerHTML = `<div class="empty-state">
            <i class="fa fa-folder-open"></i>
            <p>Select a base category to view its categories</p>
        </div>`;
            document.getElementById('mainCount').textContent = '0';
            document.getElementById('createMainBtn').style.display = 'none';
        }

        if (selectedMainId) {
            loadSubCategories(selectedMainId);
        } else {
            document.getElementById('subCategoriesColumn').innerHTML = `<div class="empty-state">
            <i class="fa fa-folder"></i>
            <p>Select a category to view its qualifications</p>
        </div>`;
            document.getElementById('subCount').textContent = '0';
            document.getElementById('createSubBtn').style.display = 'none';
        }
    }

    // Handle create form submission
    $(document).on('submit', '#createCategoryForm', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('create_category', 1);

        $.ajax({
            url: ajax_url + '/api',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                var response = $.parseJSON(data);
                var status = response.status;
                var message = response.message;

                if (status === 'success') {
                    alert('Category created successfully');
                    $('#createCategoryModal').modal('hide');
                    refreshColumns();
                } else {
                    alert('Error creating category: ' + message);
                }
            },
            error: function() {
                alert('Error creating category');
            }
        });
    });

    // Handle create sub-category form submission
    $(document).on('submit', '#createSubCategoryForm', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: ajax_url + '/api',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                var response = $.parseJSON(data);

                if (response.status === 'success') {
                    alert('Qualification created successfully');
                    $('#createSubCategoryModal').modal('hide');
                    refreshColumns();
                } else {
                    alert('Error creating qualification: ' + response.message);
                }
            },
            error: function() {
                alert('Error creating qualification');
            }
        });
    });

    // Handle edit form submission
    $(document).on('submit', '#editCategoryForm', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('update_category', 1);

        $.ajax({
            url: ajax_url + '/api',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                var response = $.parseJSON(data);
                var status = response.status;
                var message = response.message;

                if (status === 'success') {
                    alert('Category updated successfully');
                    $('#editCategoryModal').modal('hide');
                    refreshColumns();
                } else {
                    alert('Error updating category: ' + message);
                }
            },
            error: function() {
                alert('Error updating category');
            }
        });
    });

    // Handle edit sub-category form submission
    $(document).on('submit', '#editSubCategoryForm', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: ajax_url + '/api',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                var response = $.parseJSON(data);

                if (response.status === 'success') {
                    alert('Qualification updated successfully');
                    $('#editSubCategoryModal').modal('hide');
                    refreshColumns();
                } else {
                    alert('Error updating qualification: ' + response.message);
                }
            },
            error: function() {
                alert('Error updating qualification');
            }
        });
    });

    // Handle change image form submission
    $(document).on('submit', '#changeImageForm', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('update_category', 1);

        $.ajax({
            url: ajax_url + '/api',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                var response = $.parseJSON(data);

                if (response.status === 'success') {
                    alert('Image updated successfully');
                    $('#changeImageModal').modal('hide');
                    refreshColumns();
                } else {
                    alert('Error updating image: ' + response.message);
                }
            },
            error: function() {
                alert('Error updating image');
            }
        });
    });

    // Shift category functionality
    function shiftCategory(type, id) {
        selectedCategory = {
            type,
            id
        };

        // Close menu
        document.getElementById("submenu").style.display = "none";

        // Fetch category details
        $.post(ajax_url + '/api', {
            get_category_details: 1,
            category_type: type,
            category_id: id
        }, function(data) {
            var response = $.parseJSON(data);
            if (response.status === 'success') {
                const category = response.data;
                selectedCategory.name = category.name || category.attr_name;

                if (type === 'sub') {
                    // Load available categories for shift
                    loadAvailableCategoriesForShift(id, category.web_menu_id);
                }
            }
        });
    }

    function loadAvailableCategoriesForShift(qualId, currentCategoryId) {
        $.post(ajax_url + '/api', {
            get_main_categories_for_shift: 1,
            exclude_id: currentCategoryId
        }, function(data) {
            var response = $.parseJSON(data);
            if (response.status === 'success') {
                showShiftModalForQualification(qualId, response.data);
            }
        });
    }

    function showShiftModalForQualification(qualId, categories) {
        let options = '<option value="">-- Select Category --</option>';
        categories.forEach(cat => {
            options += `<option value="${cat.id}">${cat.category}</option>`;
        });

        const modalHtml = `
        <div class="modal fade" id="shiftQualModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Shift Qualification</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select New Category:</label>
                            <select class="form-control" id="shift_target_category">
                                ${options}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="confirmShiftQualification()">Shift</button>
                    </div>
                </div>
            </div>
        </div>
        `;

        $('#shiftQualModal').remove();
        $('body').append(modalHtml);
        $('#shiftQualModal').modal('show');
    }

    function confirmShiftQualification() {
        const targetCategoryId = $('#shift_target_category').val();
        if (!targetCategoryId) {
            alert('Please select a target category');
            return;
        }

        if (confirm(`Move "${selectedCategory.name}" to selected category?`)) {
            $.post(ajax_url + '/api', {
                shift_qualification: 1,
                qual_id: selectedCategory.id,
                target_category_id: targetCategoryId
            }, function(data) {
                var response = $.parseJSON(data);
                if (response.status === 'success') {
                    alert('Qualification shifted successfully');
                    $('#shiftQualModal').modal('hide');
                    refreshColumns();
                } else {
                    alert('Error shifting qualification: ' + response.message);
                }
            });
        }
    }
</script>
