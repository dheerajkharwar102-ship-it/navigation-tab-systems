<?php

if (!defined('inc_ajax_module_file')) {
    die;
}

// Get base categories
if (isset($_POST['get_base_categories'])) {
    try {
        $catalog = new Catalog();
        $where = [['status', '=', '1']];
        $baseCategories = $catalog->getBaseCategories('*', $where);

        echo json_response('success', 'OK', $baseCategories);
    } catch (Exception $e) {
        error_log("Error in get_base_categories: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}

// Get main categories by base ID
if (isset($_POST['get_main_categories_by_base'])) {
    try {
        $base_id = clear_input(p('base_id'));

        if (!$base_id || !is_numeric($base_id)) {
            echo json_response('error', 'Invalid base category ID');
            die;
        }

        $pa = new ProductAttribute();
        $where = [
            ['deleted', '=', 0],
            ['base_category', '=', $base_id]
        ];

        $mainCategories = $pa->getWebMenus('*', null, $where);

        echo json_response('success', 'OK', $mainCategories);
    } catch (Exception $e) {
        error_log("Error in get_main_categories_by_base: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}
// Get main categories by base ID
if (isset($_POST['get_main_categories_for_shift'])) {
    try {
        $exclude_id = clear_input(p('exclude_id'));

        if (!$exclude_id || !is_numeric($exclude_id)) {
            echo json_response('error', 'Invalid exclude category ID');
            die;
        }

        $pa = new ProductAttribute();
        $where = [
            ['deleted', '=', 0],
            ['id', '!=', $exclude_id]
        ];

        $mainCategories = $pa->getWebMenus('*', null, $where);

        echo json_response('success', 'OK', $mainCategories);
    } catch (Exception $e) {
        error_log("Error in get_main_categories_by_base: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}

// Get category details
if (isset($_POST['get_category_details'])) {
    try {
        $category_type = clear_input(p('category_type'));
        $category_id = clear_input(p('category_id'));

        if (!$category_id || !is_numeric($category_id)) {
            echo json_response('error', 'Invalid category ID');
            die;
        }

        if ($category_type === 'base') {
            $catalog = new Catalog();
            $category = $catalog->getBaseCategory($category_id);
        } elseif ($category_type === 'main') {
            $pa = new ProductAttribute();
            $category = $pa->getWebMenu($category_id);
        } elseif ($category_type === 'sub') {
            $pa = new ProductAttribute();
            $category = $pa->getProductAttribute($category_id);
        } else {
            echo json_response('error', 'Invalid category type');
            die;
        }

        if ($category) {
            echo json_response('success', 'OK', $category);
        } else {
            echo json_response('error', 'Category not found');
        }
    } catch (Exception $e) {
        error_log("Error in get_category_details: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}

// Create category
if (isset($_POST['create_category'])) {
    try {
        $category_type = clear_input(p('category_type'));
        $name = clear_input(p('name'));

        if (empty($name)) {
            echo json_response('error', 'Category name is required');
            die;
        }

        if ($category_type === 'base') {
            // Create base category
            $catalog = new Catalog();

            // Handle image upload
            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $image = uploadFile($_FILES['image'], 'uploads/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            }

            $data = [
                'name' => $name,
                'icon' => $image,
                'status' => 1
            ];

            $result = $catalog->addBaseCategory($data);

            if ($result) {
                echo json_response('success', 'Base category created successfully');
            } else {
                echo json_response('error', 'Failed to create base category');
            }
        } elseif ($category_type === 'main') {
            // Create main category
            $base_category = clear_input(p('parent_id'));

            if (!$base_category || !is_numeric($base_category)) {
                echo json_response('error', 'Base category is required');
                die;
            }

            $pa = new ProductAttribute();

            // Handle image upload
            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $image = uploadFile($_FILES['image'], 'uploads/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            }

            $data = [
                'category' => $name,
                'web_category_title' => clear_input(p('web_category_title')),
                'web_category_desc' => clear_input(p('web_category_desc')),
                'image' => $image,
                'base_category' => $base_category,
                'deleted' => 0
            ];

            $result = $pa->addWebMenu($data);

            if ($result) {
                echo json_response('success', 'Main category created successfully');
            } else {
                echo json_response('error', 'Failed to create main category');
            }
        } else {
            echo json_response('error', 'Invalid category type');
        }
    } catch (Exception $e) {
        error_log("Error in create_category: " . $e->getMessage());
        echo json_response('error', 'An error occurred: ' . $e->getMessage());
    }
    die;
}

// Update category
if (isset($_POST['update_category'])) {
    try {
        $category_type = clear_input(p('category_type'));
        $id = clear_input(p('id'));
        $name = clear_input(p('name'));
        $web_category_title = clear_input(p('web_category_title'));
        $web_category_desc = clear_input(p('web_category_desc'));

        if (!$id || !is_numeric($id)) {
            echo json_response('error', 'Invalid category ID');
            die;
        }

        if ($category_type === 'base') {
            $catalog = new Catalog();

            $data = [];

            if (!empty($name)) {
                $data['name'] = $name;
            }

            // Handle image upload or removal
            $remove_image = p('remove_image') == 'on';
            if ($remove_image) {
                $data['icon'] = '';
            } elseif (!empty($_FILES['image']['name'])) {
                $data['icon'] = uploadFile($_FILES['image'], 'uploads/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            }

            $result = $catalog->updateBaseCategory($data, $id);

            if ($result) {
                echo json_response('success', 'Base category updated successfully');
            } else {
                echo json_response('error', 'Failed to update base category');
            }
        } elseif ($category_type === 'main') {
            $pa = new ProductAttribute();
            $data = [];

            if (!empty($name)) {
                $data['category'] = $name;
            }
            if (!empty($name)) {
                $data['web_category_title'] = $web_category_title;
            }
            if (!empty($name)) {
                $data['web_category_desc'] = $web_category_desc;
            }

            // Handle image upload or removal
            $remove_image = p('remove_image') == 'on';
            if ($remove_image) {
                $data['image'] = '';
            } elseif (!empty($_FILES['image']['name'])) {
                $data['image'] = uploadFile($_FILES['image'], 'uploads/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            }

            $result = $pa->updateWebMenu($data, $id);

            if ($result) {
                echo json_response('success', 'Category updated successfully');
            } else {
                echo json_response('error', 'Failed to update category');
            }
        } elseif ($category_type == 'sub') {
            $pa = new ProductAttribute();
            $data = [];

            $remove_image = p('remove_image') == 'on';
            if ($remove_image) {
                $data['icon'] = '';
            } elseif (!empty($_FILES['image']['name'])) {
                $data['icon'] = uploadFile($_FILES['image'], 'uploads/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            }

            $update_date = date('Y-m-d H:i:s');
            $data['attr_update_date'] = $update_date;

            $update = $pa->updateProductAttribute($data, $id);
            if ($update) {
                echo json_response('success', 'Qualification image updated successfully');
                die;
            } else {
                echo json_response('error', 'Failed to change image');
                die;
            }
        } else {
            echo json_response('error', 'Invalid category type');
        }
    } catch (Exception $e) {
        error_log("Error in update_category: " . $e->getMessage());
        echo json_response('error', 'An error occurred: ' . $e->getMessage());
    }
    die;
}

// Delete category
if (isset($_POST['delete_category'])) {
    try {
        $category_type = clear_input(p('category_type'));
        $category_id = clear_input(p('category_id'));

        if (!$category_id || !is_numeric($category_id)) {
            echo json_response('error', 'Invalid category ID');
            die;
        }

        if ($category_type === 'base') {
            $catalog = new Catalog();
            $result = $catalog->deleteBaseCategory($category_id);

            if ($result) {
                echo json_response('success', 'Base category deleted successfully');
            } else {
                echo json_response('error', 'Failed to delete base category');
            }
        } elseif ($category_type === 'main') {
            $p = new Product();
            // Soft delete main category and all its sub categories
            $result = $p->softDeleteWebMenu($category_id);

            if ($result) {
                echo json_response('success', 'Main category and all sub-categories deleted successfully');
            } else {
                echo json_response('error', 'Failed to delete main category');
            }
        } elseif ($category_type === 'sub') {
            $pa = new ProductAttribute();
            // Soft delete sub category
            $result = $pa->deleteProductAttribute($category_id);

            if ($result) {
                echo json_response('success', 'Sub category deleted successfully');
            } else {
                echo json_response('error', 'Failed to delete sub category');
            }
        } else {
            echo json_response('error', 'Invalid category type');
        }
    } catch (Exception $e) {
        error_log("Error in delete_category: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}

// Duplicate category
if (isset($_POST['duplicate_category'])) {
    try {
        $category_type = clear_input(p('category_type'));
        $category_id = clear_input(p('category_id'));

        if (!$category_id || !is_numeric($category_id)) {
            echo json_response('error', 'Invalid category ID');
            die;
        }

        if ($category_type === 'base') {
            $catalog = new Catalog();
            $category = $catalog->getBaseCategory($category_id);
            if ($category) {
                $category_array = (array) $category;

                // Create new data array
                $data = [];
                foreach ($category_array as $key => $value) {
                    if ($key === 'id') {
                        continue; // Skip the ID
                    }
                    if ($key === 'name') {
                        $data[$key] = $value . ' (Copy)'; // Append " (Copy)" to name
                    } else {
                        $data[$key] = $value; // Copy other fields as-is
                    }
                }

                $result = $catalog->addBaseCategory($data);

                if ($result) {
                    echo json_response('success', 'Base category duplicated successfully');
                } else {
                    echo json_response('error', 'Failed to duplicate base category');
                }
            } else {
                echo json_response('error', 'Category not found');
            }
        } elseif ($category_type === 'main') {
            $pa = new ProductAttribute();
            $category = $pa->getWebMenu($category_id);

            if ($category) {
                $category_array = (array) $category;

                // Create new data array
                $data = [];
                foreach ($category_array as $key => $value) {
                    if ($key === 'id') {
                        continue; // Skip the ID
                    }
                    if ($key === 'category') {
                        $data[$key] = $value . ' (Copy)'; // Append " (Copy)" to name
                    } else {
                        $data[$key] = $value; // Copy other fields as-is
                    }
                }

                $result = $pa->addWebMenu($data);

                if ($result) {
                    echo json_response('success', 'Category duplicated successfully');
                } else {
                    echo json_response('error', 'Failed to duplicate category');
                }
            } else {
                echo json_response('error', 'Category not found');
            }
        } elseif ($category_type === 'sub') {
            $pa = new ProductAttribute();
            $category = $pa->getProductAttribute($category_id);

            if ($category) {
                $category_array = (array) $category;

                // Create new data array
                $data = [];
                foreach ($category_array as $key => $value) {
                    if ($key === 'id') {
                        continue; // Skip the ID
                    }
                    if ($key === 'attr_name') {
                        $data[$key] = $value . ' (Copy)'; // Append " (Copy)" to name
                    } else {
                        $data[$key] = $value; // Copy other fields as-is
                    }
                }

                $result = $pa->addProductAttribute($data);

                if ($result) {
                    echo json_response('success', 'Category duplicated successfully');
                } else {
                    echo json_response('error', 'Failed to duplicate category');
                }
            } else {
                echo json_response('error', 'Category not found');
            }
        } else {
            echo json_response('error', 'Invalid category type');
        }
    } catch (Exception $e) {
        error_log("Error in duplicate_category: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}

// Helper function for file upload
function uploadFile($file, $upload_dir, $allowed_types = [])
{
    if (empty($file['name'])) {
        return '';
    }

    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Check for upload errors
    if ($file_error !== UPLOAD_ERR_OK) {
        return '';
    }

    // Check file size (max 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        return '';
    }

    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Check allowed file types
    if (!empty($allowed_types) && !in_array($file_ext, $allowed_types)) {
        return '';
    }

    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_ext;
    $destination = PATH . '/' . $upload_dir . $new_filename;

    // Move uploaded file
    if (move_uploaded_file($file_tmp, $destination)) {
        return $new_filename;
    }

    return '';
}


// new script

// Get shift targets
if (isset($_POST['get_shift_targets'])) {
    try {
        $source_type = clear_input(p('source_type'));
        $source_id = clear_input(p('source_id'));
        $target_type = clear_input(p('target_type'));
        
        if (!$source_type || !$target_type) {
            echo json_response('error', 'Invalid parameters');
            die;
        }
        
        $pa = new ProductAttribute();
        $catalog = new Catalog();
        $targets = [];
        
        if ($target_type === 'base') {
            // Get all base categories except the source
            $where = [['status', '=', '1']];
            if ($source_type === 'base') {
                $where[] = ['id', '!=', $source_id];
            }
            $bases = $catalog->getBaseCategories('id,name', $where);
            foreach ($bases as $base) {
                $targets[] = [
                    'id' => $base->id,
                    'name' => $base->name
                ];
            }
        } 
        elseif ($target_type === 'main') {
            // Get all main categories
            $where = [['deleted', '=', '0']];
            if ($source_type === 'main') {
                $where[] = ['id', '!=', $source_id];
            }
            $mains = $pa->getWebMenus('id,category,base_category', null, $where);
            
            // Get base category names
            $base_names = [];
            $base_ids = [];
            foreach ($mains as $main) {
                $base_ids[] = $main->base_category;
            }
            
            if (!empty($base_ids)) {
                $bases = $catalog->getBaseCategories('id,name', [['id', 'IN', array_unique($base_ids)]]);
                foreach ($bases as $base) {
                    $base_names[$base->id] = $base->name;
                }
            }
            
            foreach ($mains as $main) {
                $targets[] = [
                    'id' => $main->id,
                    'category' => $main->category,
                    'base_category_name' => $base_names[$main->base_category] ?? 'Unknown'
                ];
            }
        }
        elseif ($target_type === 'sub') {
            // Get all qualifications
            $where = [['attr_status', '=', '1']];
            if ($source_type === 'sub') {
                $where[] = ['attr_id', '!=', $source_id];
            }
            $subs = $pa->getProductAttributes('attr_id,attr_name,online_category', null, $where);
            
            // Get category names
            $category_names = [];
            $category_ids = [];
            foreach ($subs as $sub) {
                if ($sub->online_category) {
                    $category_ids[] = $sub->online_category;
                }
            }
            
            if (!empty($category_ids)) {
                $categories = $pa->getWebMenus('id,category', null, [['id', 'IN', array_unique($category_ids)]]);
                foreach ($categories as $cat) {
                    $category_names[$cat->id] = $cat->category;
                }
            }
            
            foreach ($subs as $sub) {
                $targets[] = [
                    'id' => $sub->attr_id,
                    'attr_name' => $sub->attr_name,
                    'category_name' => $category_names[$sub->online_category] ?? 'Unknown'
                ];
            }
        }
        
        echo json_response('success', 'OK', $targets);
        
    } catch (Exception $e) {
        error_log("Error in get_shift_targets: " . $e->getMessage());
        echo json_response('error', 'An error occurred: ' . $e->getMessage());
    }
    die;
}

// Shift category
if (isset($_POST['shift_category'])) {
    try {
        $source_type = clear_input(p('source_type'));
        $source_id = clear_input(p('source_id'));
        $target_type = clear_input(p('target_type'));
        $target_id = clear_input(p('target_id'));
        $new_qualification_name = clear_input(p('new_qualification_name'));
        $convert_structure = clear_input(p('convert_structure'));
        $merge_qualifications = clear_input(p('merge_qualifications'));
        
        if (!$source_type || !$source_id || !$target_type || !$target_id) {
            echo json_response('error', 'Missing required parameters');
            die;
        }
        
        $pa = new ProductAttribute();
        $catalog = new Catalog();
        $p = new Product();
        
        // Start transaction
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            // Get source details
            $source_details = null;
            if ($source_type === 'base') {
                $source = $catalog->getBaseCategories('*', [['id', '=', $source_id]]);
                if (!empty($source)) $source_details = (array)$source[0];
            } elseif ($source_type === 'main') {
                $source = $pa->getWebMenus('*', null, [['id', '=', $source_id]]);
                if (!empty($source)) $source_details = (array)$source[0];
            } elseif ($source_type === 'sub') {
                $source = $pa->getProductAttributes('*', null, [['attr_id', '=', $source_id]]);
                if (!empty($source)) $source_details = (array)$source[0];
            }
            
            if (empty($source_details)) {
                throw new Exception('Source not found');
            }
            
            // Handle different shift scenarios
            if ($source_type === $target_type) {
                // Same level shift
                if ($source_type === 'base') {
                    // Base to base - update parent relationships
                    // In current structure, base categories don't have parent
                    // Just update the sort order or other fields
                    $catalog->updateBaseCategory($source_id, [
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                elseif ($source_type === 'main') {
                    // Main to main - update base_category
                    $pa->updateWebMenu([
                        'base_category' => $target_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], $source_id);
                }
                elseif ($source_type === 'sub') {
                    // Sub to sub - update online_category
                    $pa->updateProductAttribute([
                        'online_category' => $target_id,
                        'attr_update_date' => date('Y-m-d H:i:s')
                    ], $source_id);
                }
            }
            else {
                // Different level shift - complex conversion
                if ($source_type === 'base' && $target_type === 'main') {
                    // Base → Main conversion
                    // 1. Get target main category details
                    $target_main = $pa->getWebMenus('*', null, [['id', '=', $target_id]]);
                    if (empty($target_main)) {
                        throw new Exception('Target category not found');
                    }
                    $target_main = (array)$target_main[0];
                    
                    // 2. Get all main categories under this base
                    $old_mains = $pa->getWebMenus('*', null, [['base_category', '=', $source_id], ['deleted', '=', '0']]);
                    
                    if ($convert_structure == '1') {
                        // Create new main category from base
                        $new_main_data = [
                            'category' => $source_details['name'] . ' (Shifted)',
                            'base_category' => $target_main['base_category'],
                            'image' => $source_details['icon'],
                            'deleted' => '0',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $new_main_id = $pa->addWebMenu($new_main_data);
                        
                        // Move all old mains to be qualifications under new main
                        foreach ($old_mains as $old_main) {
                            // Get all qualifications under this old main
                            $qualifications = $pa->getProductAttributes('*', null, [['online_category', '=', $old_main->id], ['attr_status', '=', '1']]);
                            
                            foreach ($qualifications as $qual) {
                                // Update qualification to point to new main
                                $pa->updateProductAttribute([
                                    'online_category' => $new_main_id,
                                    'attr_update_date' => date('Y-m-d H:i:s')
                                ], $qual->attr_id);
                            }
                            
                            // Soft delete old main
                            $pa->updateWebMenu([
                                'deleted' => '1',
                                'updated_at' => date('Y-m-d H:i:s')
                            ], $old_main->id);
                        }
                    } else {
                        // Move each old main to target base
                        foreach ($old_mains as $old_main) {
                            $pa->updateWebMenu([
                                'base_category' => $target_main['base_category'],
                                'updated_at' => date('Y-m-d H:i:s')
                            ], $old_main->id);
                        }
                    }
                    
                    // Soft delete the old base
                    $catalog->updateBaseCategory([
                        'status' => '0',
                        'updated_at' => date('Y-m-d H:i:s')
                    ], $source_id);
                    
                }
                elseif ($source_type === 'main' && $target_type === 'base') {
                    // Main → Base conversion
                    // 1. Create new base category from main
                    $new_base_data = [
                        'name' => $source_details['category'] . ' (Shifted)',
                        'icon' => $source_details['image'],
                        'status' => '1',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $new_base_id = $catalog->addBaseCategory($new_base_data);
                    
                    // 2. Get all qualifications under this main
                    $qualifications = $pa->getProductAttributes('*', null, [['online_category', '=', $source_id], ['attr_status', '=', '1']]);
                    
                    // 3. Create new main categories for each qualification
                    foreach ($qualifications as $qual) {
                        $new_main_data = [
                            'category' => $qual->attr_name,
                            'base_category' => $new_base_id,
                            'image' => $qual->icon,
                            'deleted' => '0',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $new_main_id = $pa->addWebMenu($new_main_data);
                        
                        // Update qualification to point to new main
                        $pa->updateProductAttribute([
                            'online_category' => $new_main_id,
                            'attr_update_date' => date('Y-m-d H:i:s')
                        ], $qual->attr_id);
                    }
                    
                    // 4. Soft delete old main
                    $pa->updateWebMenu([
                        'deleted' => '1',
                        'updated_at' => date('Y-m-d H:i:s')
                    ], $source_id);
                    
                }
                elseif ($source_type === 'main' && $target_type === 'sub') {
                    // Main → Sub conversion
                    // 1. Get target sub category to understand its structure
                    $target_sub = $pa->getProductAttributes('*', null, [['attr_id', '=', $target_id]]);
                    if (empty($target_sub)) {
                        throw new Exception('Target qualification not found');
                    }
                    $target_sub = (array)$target_sub[0];
                    
                    // 2. Get all qualifications under this main
                    $qualifications = $pa->getProductAttributes('*', null, [['online_category', '=', $source_id], ['attr_status', '=', '1']]);
                    
                    if ($merge_qualifications == '1') {
                        // Move all qualifications to target main
                        foreach ($qualifications as $qual) {
                            $pa->updateProductAttribute([
                                'online_category' => $target_sub['online_category'],
                                'attr_update_date' => date('Y-m-d H:i:s')
                            ], $qual->attr_id);
                        }
                    } else {
                        // Create new qualification from main
                        $new_qual_data = [
                            'attr_name' => $source_details['category'],
                            'catalog_id' => $target_sub['catalog_id'],
                            'online_category' => $target_sub['online_category'],
                            'attr_desc' => $source_details['web_category_desc'] ?? '',
                            'attr_customs_code' => 'SHIFTED_' . date('YmdHis'),
                            'calculate_type' => $target_sub['calculate_type'],
                            'attr_type' => $target_sub['attr_type'],
                            'attr_stock_status' => $target_sub['attr_stock_status'],
                            'attr_online_status' => $target_sub['attr_online_status'],
                            'online_product_img' => $source_details['image'],
                            'icon' => $source_details['image'],
                            'attr_status' => '1',
                            'attr_add_date' => date('Y-m-d H:i:s'),
                            'attr_update_date' => date('Y-m-d H:i:s'),
                            'wholesale_percentage' => $target_sub['wholesale_percentage']
                        ];
                        
                        $new_qual_id = $pa->addProductAttribute($new_qual_data);
                        
                        // Move existing qualifications under this main to new qualification's category
                        foreach ($qualifications as $qual) {
                            $pa->updateProductAttribute([
                                'online_category' => $target_sub['online_category'],
                                'attr_update_date' => date('Y-m-d H:i:s')
                            ], $qual->attr_id);
                        }
                    }
                    
                    // 3. Soft delete old main
                    $pa->updateWebMenu([
                        'deleted' => '1',
                        'updated_at' => date('Y-m-d H:i:s')
                    ], $source_id);
                    
                }
                elseif ($source_type === 'sub' && $target_type === 'main') {
                    // Sub → Main conversion
                    // 1. Get target main details
                    $target_main = $pa->getWebMenus('*', null, [['id', '=', $target_id]]);
                    if (empty($target_main)) {
                        throw new Exception('Target category not found');
                    }
                    $target_main = (array)$target_main[0];
                    
                    // 2. Create new main from sub
                    $new_main_data = [
                        'category' => $source_details['attr_name'],
                        'base_category' => $target_main['base_category'],
                        'image' => $source_details['icon'] ?? $source_details['online_product_img'],
                        'deleted' => '0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $new_main_id = $pa->addWebMenu($new_main_data);
                    
                    // 3. Update products that use this qualification to point to new main
                    // This depends on your product structure
                    $products = $p->getProducts('*', null, [['attr_id', '=', $source_id]]);
                    foreach ($products as $product) {
                        // Update product to use new main (if applicable)
                        // This depends on how products are linked to mains
                        if (property_exists($product, 'web_menu_id')) {
                            $p->updateProduct([
                                'web_menu_id' => $new_main_id
                            ], $product->product_id);
                        }
                    }
                    
                    // 4. Disable old sub
                    $pa->updateProductAttribute([
                        'attr_status' => '2',
                        'attr_update_date' => date('Y-m-d H:i:s')
                    ], $source_id);
                    
                }
                elseif ($source_type === 'sub' && $target_type === 'base') {
                    // Sub → Base conversion
                    // 1. Create new base from sub
                    $new_base_data = [
                        'name' => $source_details['attr_name'],
                        'icon' => $source_details['icon'] ?? $source_details['online_product_img'],
                        'status' => '1',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $new_base_id = $catalog->addBaseCategory($new_base_data);
                    
                    // 2. Create new main under this base
                    $new_main_data = [
                        'category' => $source_details['attr_name'] . ' Category',
                        'base_category' => $new_base_id,
                        'image' => $source_details['icon'] ?? $source_details['online_product_img'],
                        'deleted' => '0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $new_main_id = $pa->addWebMenu($new_main_data);
                    
                    // 3. Update the sub to point to new main
                    $pa->updateProductAttribute([
                        'online_category' => $new_main_id,
                        'attr_update_date' => date('Y-m-d H:i:s')
                    ], $source_id);
                    
                    // 4. Update products if needed
                    // (Products remain linked to the same sub qualification)
                    
                }
                else {
                    throw new Exception('Unsupported shift operation');
                }
            }
            
            $db->commit();
            echo json_response('success', 'Category shifted successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Error in shift_category: " . $e->getMessage());
        echo json_response('error', 'An error occurred: ' . $e->getMessage());
    }
    die;
}

// Shift qualifications in bulk (for main category shift)
if (isset($_POST['shift_qualifications'])) {
    try {
        $source_main_id = clear_input(p('source_main_id'));
        $moves = json_decode(p('moves'), true);
        
        if (!$source_main_id || !$moves) {
            echo json_response('error', 'Invalid parameters');
            die;
        }
        
        $pa = new ProductAttribute();
        $errors = [];
        $success_count = 0;
        
        // Start transaction
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            foreach ($moves as $move) {
                $qual_id = $move['qual_id'];
                $target_category_id = $move['target_category_id'];
                
                // Update qualification
                $update_result = $pa->updateProductAttribute([
                    'online_category' => $target_category_id,
                    'attr_update_date' => date('Y-m-d H:i:s')
                ], $qual_id);
                
                if ($update_result) {
                    $success_count++;
                } else {
                    $errors[] = "Failed to move qualification ID: $qual_id";
                }
            }
            
            $db->commit();
            
            if (empty($errors)) {
                echo json_response('success', "Successfully moved $success_count qualification(s)");
            } else {
                echo json_response('warning', "Moved $success_count qualification(s), but had errors: " . implode(', ', $errors));
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Error in shift_qualifications: " . $e->getMessage());
        echo json_response('error', 'An error occurred: ' . $e->getMessage());
    }
    die;
}

// Get qualification prices
if (isset($_POST['get_qualification_prices'])) {
    try {
        $attr_id = clear_input(p('attr_id'));
        
        if (!$attr_id || !is_numeric($attr_id)) {
            echo json_response('error', 'Invalid attribute ID');
            die;
        }
        
        $pa = new ProductAttribute();
        $qual = $pa->getProductAttribute($attr_id, 'attr_rates');
        
        $prices = [];
        if ($qual && !empty($qual->attr_rates)) {
            $rates = json_decode($qual->attr_rates, true);
            if ($rates) {
                for ($i = 0; $i < 9; $i++) {
                    if (isset($rates[$i])) {
                        $prices[$i] = [
                            'name' => $rates[$i]['name'] ?? '',
                            'type' => $rates[$i]['type'] ?? 'plus',
                            'rate' => $rates[$i]['rate'] ?? ''
                        ];
                    } else {
                        $prices[$i] = [
                            'name' => '',
                            'type' => 'plus',
                            'rate' => ''
                        ];
                    }
                }
            } else {
                // Initialize empty prices
                for ($i = 0; $i < 9; $i++) {
                    $prices[$i] = [
                        'name' => '',
                        'type' => 'plus',
                        'rate' => ''
                    ];
                }
            }
        } else {
            // Initialize empty prices
            for ($i = 0; $i < 9; $i++) {
                $prices[$i] = [
                    'name' => '',
                    'type' => 'plus',
                    'rate' => ''
                ];
            }
        }
        
        echo json_response('success', 'OK', $prices);
        
    } catch (Exception $e) {
        error_log("Error in get_qualification_prices: " . $e->getMessage());
        echo json_response('error', 'An error occurred');
    }
    die;
}
