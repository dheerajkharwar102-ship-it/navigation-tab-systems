<?php

class Product
{
   private $table;
   private $table_stock;
   private $table_stock_history;

   private $table_product_combination;
   private $table_product_materials;
   private $table_product_combination_details;

   private $table_product_catalogue;
   private $table_product_discount;

   private $table_product_style_types;
   private $table_product_shapes;
   private $table_product_fabric_name;
   private $table_materials;
   private $table_product_library_modification_history;
   private $table_product_revise_data;
   private $table_main_category;

   public function __construct()
   {
      $this->table = 'hd_products';
      $this->table_stock = 'hd_product_stock';
      $this->table_stock_history = 'hd_product_stock_history';

      $this->table_product_combination = 'hd_product_combination';
      $this->table_product_materials = 'hd_product_materials';
      $this->table_product_combination_details = 'hd_product_combination_details';

      $this->table_product_style_types = 'hd_product_style_types';
      $this->table_product_shapes = 'hd_product_shapes';
      $this->table_product_catalogue = 'hd_product_catalogue';
      $this->table_product_discount = 'hd_product_discount';
      $this->table_product_fabric_name = 'hd_product_fabric_name';
      $this->table_materials = 'hd_materials';
      $this->table_product_library_modification_history = 'hd_product_library_modification_history';
      $this->table_product_revise_data = 'hd_product_revise_data';
      $this->table_main_category = 'hd_web_menu';
   }

   public function getProducts($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null, $is_null = null, $or = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {
            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }
            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      // Updated IN clause handling for multiple IN clauses
      if (!is_null($in_clause) && is_array($in_clause) && !empty($in_clause)) {
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         // Check if it's a single IN clause or multiple
         if (isset($in_clause[0]) && is_array($in_clause[0])) {
            // Multiple IN clauses
            foreach ($in_clause as $clause) {
               if (is_array($clause) && count($clause) >= 2) {
                  $in_text = '';
                  $sql_text .= " " . $clause[0] . " IN(";

                  if (is_array($clause[1])) {
                     foreach ($clause[1] as $value) {
                        $in_text .= "'" . $value . "', ";
                     }
                     $in_text = rtrim($in_text, ', ');
                  } else {
                     $in_text = "'" . $clause[1] . "'";
                  }

                  $sql_text .= $in_text . ") AND";
               }
            }
            $sql_text = rtrim($sql_text, ' AND');
         } else {
            // Single IN clause (backward compatibility)
            $in_text = '';
            if (is_array($in_clause) && count($in_clause) > 0) {
               $sql_text .= " " . $in_clause[0] . " IN(";
               if (is_array($in_clause[1])) {
                  foreach ($in_clause[1] as $clause) {
                     $in_text .= "'" . $clause . "', ";
                  }
                  $in_text = rtrim($in_text, ', ');
               } else {
                  $in_text = $in_clause[1];
               }
               $sql_text .= $in_text . ")";
            } else {
               return false;
            }
         }
      }

      if (!is_null($is_null)) {
         if ($sql_text != '') {
            $sql_text .= ' AND ';
         } else {
            $sql_text .= ' WHERE ';
         }
         $sql_text .= $is_null[0] . ' ' . $is_null[1];
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      if (!is_null($or)) {
         if (is_array($or) && count($or) > 0) {
            $or_end = '';
            if ($sql_text != '') {
               $sql_text .= ' AND(';
               $or_end = ')';
            } else {
               $sql_text .= ' WHERE';
            }
            foreach ($or as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' OR";
            }
            $sql_text = rtrim($sql_text, ' OR');
            $sql_text .= $or_end;
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProductsOld($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null, $is_null = null, $or = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($is_null)) {
         if ($sql_text != '') {
            $sql_text .= ' AND ';
         } else {
            $sql_text .= ' WHERE ';
         }
         $sql_text .= $is_null[0] . ' ' . $is_null[1];
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      if (!is_null($or)) {
         if (is_array($or) && count($or) > 0) {
            /* $or = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' OR';
            // $sql_text .= ' AND';

            foreach ($or as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' OR";
            }

            $sql_text = rtrim($sql_text, ' OR');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getHeavyProducts($minWeight = 300, $limit = null)
   {
      global $dbs;

      $sql = "SELECT * FROM " . $this->table . "
            WHERE JSON_UNQUOTE(JSON_EXTRACT(product_dims_data, '$[0].weight')) + 0 > " . (float)$minWeight;

      if (!is_null($limit) && is_array($limit) && count($limit) == 2) {
         $sql .= " LIMIT " . (int)$limit[0] . ", " . (int)$limit[1];
      }

      $items = $dbs->get_results($sql);
      return !empty($items) ? $items : [];
   }

   public function getBaseCategoriesByCatalog_id($catalog_id)
   {
      global $dbs;

      $sql = "SELECT wm.* FROM `hd_product_attr` pa JOIN hd_web_menu wm ON pa.online_category = wm.id where pa.catalog_id = {$catalog_id} GROUP BY wm.base_category;";
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getBaseCategoriesByWebCat($web_cat_id)
   {
      global $dbs;

      $sql = "SELECT * FROM `hd_web_menu` where id = {$web_cat_id};";
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }
   public function getProductCatalogsByBaseCatAttr($base_cat, $attr_id)
   {
      global $dbs;

      $sql = "SELECT DISTINCT cat.catalog_id,cat.catalog_name FROM `hd_product_attr` pa JOIN `hd_web_menu` wm ON pa.online_category = wm.id JOIN `hd_catalogs` cat ON cat.catalog_id = pa.catalog_id where wm.base_category = {$base_cat} AND pa.attr_id = {$attr_id} GROUP BY wm.base_category;";
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }
   public function getBaseCategories()
   {
      global $dbs;

      $sql = "SELECT wm.* FROM `hd_product_attr` pa JOIN hd_web_menu wm ON pa.online_category = wm.id GROUP BY wm.base_category;";
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProduct($product_id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table . " WHERE product_id = " . $product_id . $sql_text;
      // $item = $dbs->get_row($sql);
      $items = $dbs->get_results($sql);
      if ($items) {
         $item = $items[0];
      } else {
         $item = '';
      }
      return $item;

      // echo '<pre>',var_dump($items),'<pre>';
   }

   public function addProduct($data)
   {
      global $dbs;

      $sql = "INSERT INTO " . $this->table . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function updateProduct($data, $product_id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table . " SET " . $sql_text . " WHERE product_id = " . $product_id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }
   public function updateStandardPrice($jsonString, $multiplier)
   {
      // If empty or null, return the same value
      if (empty($jsonString)) {
         return $jsonString;
      }

      $data = json_decode($jsonString, true);

      // If decoding fails, return original
      if ($data === null) {
         return $jsonString;
      }

      // Case 1: Array of objects (indexed)
      if (isset($data[0]) && is_array($data[0])) {
         foreach ($data as &$obj) {
            if (isset($obj['standart_price'])) {
               $obj['standart_price'] = round($obj['standart_price'] * $multiplier);
            }
         }
      }
      // Case 2: Associative array with keys like "120200"
      else {
         foreach ($data as &$obj) {
            if (is_array($obj) && isset($obj['standart_price'])) {
               $obj['standart_price'] = round($obj['standart_price'] * $multiplier);
            }
         }
      }

      return json_encode($data, JSON_UNESCAPED_UNICODE);
   }

   public function updateProductsPrice($multiplier, $where = null, $in_clause = null)
   {
      global $dbs;
      $tbl = $this->table;
      $sql_text = '';
      if (is_array($where) && !empty($where)) {
         $sql_text .= ' WHERE';
         foreach ($where as $w) {
            $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
         }

         $sql_text = rtrim($sql_text, ' AND');
      } elseif (is_array($in_clause) && !empty($in_clause)) {
         $sql_text .= " WHERE" . $in_clause[0] . " IN(";
         $in_text = '';
         if (is_array($in_clause[1])) {
            foreach ($in_clause[1] as $clause) {
               $in_text .= "'" . $clause . "', ";
            }
            $in_text = rtrim($in_text, ', ');
         } else {
            $in_text = $in_clause[1];
         }
         $sql_text .= $in_text . ")";
      }
      $update = false;
      $sql = "SELECT * FROM " . $this->table . $sql_text;
      $get_products = $dbs->get_results($sql);
      foreach ($get_products as $product) {
         $product_id = $product->product_id;
         $product_dims_data = $product->product_dims_data;
         $product_dims_data = $this->updateStandardPrice($product_dims_data, $multiplier);
         $product_bed_dims = $product->product_bed_dims;
         $product_bed_dims = $this->updateStandardPrice($product_bed_dims, $multiplier);
         $standart_price = $product->standart_price;
         $standart_price = round($standart_price * $multiplier);

         $sql = "UPDATE {$tbl} SET standart_price = {$standart_price}, product_dims_data = '{$product_dims_data}', product_bed_dims = '{$product_bed_dims}' WHERE product_id = {$product_id}";
         $update = $dbs->query($sql);
         // echo '<pre>', var_dump($sql), '</pre>';

      }
      // $sql = "UPDATE {$tbl} SET standart_price = ROUND(standart_price * {$multiplier}, 0), product_dims_data = JSON_SET(product_dims_data, '$[0].standart_price', ROUND(standart_price * {$multiplier}, 0)){$sql_text}";
      // $update = $dbs->query($sql);
      // echo '<pre>', var_dump($sql), '</pre>';
      if ($update) {
         return true;
      } else {
         return false;
      }
   }


   public function deletProductparrents_group($parrent_id)
   {
      global $dbs;

      $sql = "UPDATE " . $this->table . " SET set_parents_group_id = NULL WHERE set_parents_group_id = " . $parrent_id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function updateProductByIds($data, $in_clause = null)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }
      $sql_text = rtrim($sql_text, ', ');

      $in_text = '';
      if (is_array($in_clause) && count($in_clause) > 0) {
         $sql_in_text = " " . $in_clause[0] . " IN(";
         if (is_array($in_clause[1])) {
            foreach ($in_clause[1] as $clause) {
               $in_text .= "'" . $clause . "', ";
            }
            $in_text = rtrim($in_text, ', ');
         } else {
            $in_text = $in_clause[1];
         }
         $sql_in_text .= $in_text . ")";
      }

      $sql = "UPDATE " . $this->table . " SET " . $sql_text . " WHERE " .  $sql_in_text;
      // echo '<pre>', var_dump($sql), '</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function checkProductById($product_id)
   {
      global $dbs;
      $varmi = $dbs->get_var("SELECT COUNT(product_id) FROM " . $this->table . " WHERE product_id = " . $product_id);
      if ($varmi > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function checkProductCombinationById($combo_id)
   {
      global $dbs;
      $varmi = $dbs->get_var("SELECT COUNT(id) FROM " . $this->table_product_combination . " WHERE id = " . $combo_id);
      if ($varmi > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function checkProductByCode($product_code, $not_product_id = null)
   {
      global $dbs;
      $sql_text = '';
      if (!is_null($not_product_id)) {
         $sql_text = ' AND product_id != ' . $not_product_id;
      }

      $varmi = $dbs->get_var("SELECT COUNT(product_id) FROM " . $this->table . " WHERE product_code = '" . $product_code . "'" . $sql_text);
      if ($varmi > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function deleteProduct($product_id)
   {
      global $dbs;
      $now = date('Y-m-d H:i:s');

      $delete = $dbs->query("UPDATE " . $this->table . " SET product_status = 2, product_update_date = '" . $now . "' WHERE product_id = " . $product_id);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function addWebMenu($data)
   {
      global $dbs;
      $sql = "INSERT INTO " . $this->table_main_category . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // print("<pre>" . var_dump($sql) . "</pre>");
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function updateWebMenu($data, $id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_main_category . " SET " . $sql_text . " WHERE id = " . $id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }


   public function softDeleteWebMenu($id)
   {
      global $dbs;
      $now = date('Y-m-d H:i:s');

      $deleted = $dbs->query("UPDATE " . $this->table_main_category . " SET deleted = 1 WHERE id = " . $id);
      if ($deleted) {
         return true;
      } else {
         return false;
      }
   }


   public function restoreProduct($product_id)
   {
      global $dbs;
      $now = date('Y-m-d H:i:s');

      $delete = $dbs->query("UPDATE " . $this->table . " SET product_status = 1, product_update_date = '" . $now . "' WHERE product_id = " . $product_id);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function checkProductStock($product_id)
   {
      global $dbs;

      $check = $dbs->get_var("SELECT COUNT(product_id) FROM " . $this->table_stock . " WHERE product_id = " . $product_id);
      if ($check > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function getProductStock($product_id)
   {
      global $dbs;

      if ($this->checkProductStock($product_id)) {
         $stock = $dbs->get_var("SELECT stock_count FROM " . $this->table_stock . " WHERE product_id = " . $product_id);
      } else {
         $stock = 0;
      }

      return $stock;
   }

   public function getBulkProductStock($product_ids)
   {
      global $dbs;

      $return_data = [];
      $current_stock = [];

      $in_text = '';
      $sql_text = " WHERE product_id IN(";
      foreach ($product_ids as $product_id) {
         $in_text .= "'" . $product_id . "', ";
      }
      $in_text = rtrim($in_text, ', ');
      $sql_text .= $in_text . ")";

      $stocks = $dbs->get_results("SELECT product_id,stock_count FROM " . $this->table_stock . $sql_text);
      if (count($stocks) > 0) {
         foreach ($stocks as $stock) {
            $current_stock[$stock->product_id] = $stock->stock_count;
         }
      }

      foreach ($product_ids as $product_id) {
         $stock_count = 0;
         if ($current_stock[$product_id] != '') {
            $stock_count = $current_stock[$product_id];
         }

         $return_data[$product_id] = ['product_id' => $product_id, 'stock_count' => $stock_count];
      }

      return $return_data;
   }

   public function setStock($product_id, $stock_count)
   {
      global $dbs;

      if ($this->checkProductStock($product_id)) {
         $update = $dbs->query("UPDATE " . $this->table_stock . " SET stock_count = '" . $stock_count . "', update_date = '" . date('Y-m-d H:i:s') . "' WHERE product_id = " . $product_id);
      } else {
         $update = $dbs->query("INSERT INTO " . $this->table_stock . "(product_id,stock_count) VALUES('" . $product_id . "','" . $stock_count . "')");
      }

      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function addStockHistory($data)
   {
      global $dbs;

      $cols_text = '';
      $values_text = '';
      foreach ($data as $key => $item) {
         $cols_text .= $key . ',';
         $values_text .= "'" . $item . "',";
      }

      $cols_text = rtrim($cols_text, ',');
      $values_text = rtrim($values_text, ',');

      $insert = $dbs->query("INSERT INTO " . $this->table_stock_history . "(" . $cols_text . ") VALUES(" . $values_text . ")");
      if ($insert) {
         return true;
      } else {
         return false;
      }
   }

   public function getStockHistory($where = null, $order = null)
   {
      global $dbs;

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' WHERE';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $items = $dbs->get_results("SELECT * FROM " . $this->table_stock_history . $sql_text . $order_text);
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getStockTypes()
   {
      $types = [
         'add' => get_lang_text('productstock_stock_type_add'),
         'remove' => get_lang_text('productstock_stock_type_remove'),
         'orderadd' => get_lang_text('productstock_stock_type_orderadd'),
         'ordercancel' => get_lang_text('productstock_stock_type_ordercancel'),
      ];
      return $types;
   }

   public function getProductStyleTypes($cols = '*', $order = null, $where = null, $limit = null, $between = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }


      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_style_types . $sql_text . $order_text;
      $items = $dbs->get_results($sql);
      // '<pre>',var_dump($sql),'<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }


   public function CheckProductCombination($prd_id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination . " WHERE product_id = " . $prd_id . $sql_text;
      // $item = $dbs->get_row($sql);
      $items = $dbs->get_results($sql);
      // '<pre>',var_dump($sql),'<pre>';
      if (!empty($items)) {
         return $items[0];
      } else {
         return false;
      }
   }


   public function getProductCombinationFullData($prd_id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination_details . " AS pcd
               RIGHT JOIN " . $this->table_product_materials . " AS pm on pm.id=pcd.material_reference
               RIGHT JOIN " . $this->table_product_combination . " AS pc ON pm.combination_id = pc.id
               RIGHT JOIN " . $this->table . " as p ON p.product_id=pc.product_id WHERE   pc.product_id =" . $prd_id . $sql_text;
      // $item = $dbs->get_row($sql);
      $items = $dbs->get_results($sql);
      // echo '<pre>',var_dump($sql),'<pre>';
      // pm.product_id=" . $prd_id . " AND
      if (!empty($items)) {
         return $items;
      } else {
         return false;
      }
   }


   public function addProductCombination($data)
   {
      global $dbs;
      $sql = "INSERT INTO " . $this->table_product_combination . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function getProductCombination($id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination . " WHERE id = " . $id . $sql_text;
      $item = $dbs->get_row($sql);
      // echo  '<pre>', var_dump($sql), '<pre>';
      return $item;
   }

   public function getProductCombinations($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }


      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               if ($w[2] == 'NULL') {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " " . $w[2] . " AND";
               } else {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
               }
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination . $sql_text . $order_text . $group_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }
   public function getNumsProductCombinationsForMaterialAddedOnGivenGivenDate($addDate)
   {
      global $dbs;
      $sql = "SELECT DISTINCT product_id FROM " . $this->table_product_combination . " c LEFT JOIN " . $this->table_product_combination_details . " cd ON c.id = cd.combination_id  WHERE Date(c.date_time) = '" . $addDate . "' AND cd.material_default = 1";
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }
   public function getActiveProductCombinations($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }


      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               if ($w[2] == 'NULL') {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " " . $w[2] . " AND";
               } else {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
               }
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination . " AS pc
               RIGHT JOIN " . $this->table . " as p ON p.product_id = pc.product_id " . $sql_text . $order_text . $group_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getAllParentProductCombinations($cols = '*', $where = null)
   {

      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               if ($w[2] == 'NULL') {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " " . $w[2] . " AND";
               } else {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
               }
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      // $sql="SELECT" . $cols . " FROM hd_product_combination AS pc
      //    RIGHT JOIN hd_products AS p ON p.product_id = pc.product_id
      //    WHERE pc.product_id IN (
      //       SELECT product_id
      //       FROM hd_product_combination
      //       WHERE combination_name IS NOT NULL
      //       GROUP BY product_id
      //       HAVING COUNT(*) > 1
      //    )
      //    AND combination_name IS NOT NULL
      //    AND p.product_status = '1' GROUP BY p.product_id";
      $sql = "SELECT" . $cols . " FROM hd_product_combination AS pc
         RIGHT JOIN hd_products AS p ON p.product_id = pc.product_id
         WHERE pc.product_id IN (
            SELECT product_id
            FROM hd_product_combination
            WHERE combination_name IS NOT NULL
            GROUP BY product_id
         )
         AND combination_name IS NOT NULL
         AND p.product_status = '1' GROUP BY p.product_id";
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }



   public function updateProductCombination($data, $id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_combination . " SET " . $sql_text . " WHERE id = " . $id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function deleteProductCombination($id)
   {
      global $dbs;

      $delete = $dbs->query("DELETE FROM " . $this->table_product_combination . " WHERE id = " . $id);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }


   public function addProductMaterial($data)
   {
      global $dbs;
      $sql = "INSERT INTO " . $this->table_product_materials . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function getProductMaterials($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_materials . $sql_text . $order_text . $group_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>',var_dump($sql),'<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProductMaterial($id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_materials . " WHERE id = " . $id . $sql_text;
      $item = $dbs->get_row($sql);
      // '<pre>',var_dump($sql),'<pre>';
      return $item;
   }

   public function updateProductMaterial($data, $id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_materials . " SET " . $sql_text . " WHERE id = " . $id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function updateProductMaterialDafault($data, $id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_materials . " SET " . $sql_text . " WHERE material_default = '1' AND id = " . $id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function deleteProductMaterial($id)
   {
      global $dbs;

      $delete = $dbs->query("DELETE FROM " . $this->table_product_materials . " WHERE id = " . $id);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }


   public function addProductCombinationDetail($data)
   {
      global $dbs;
      $sql = "INSERT INTO " . $this->table_product_combination_details . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function getProductCombinationDetails($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null)
   {
      global $dbs;

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination_details . $sql_text . $order_text;
      $items = $dbs->get_results($sql);
      // '<pre>',var_dump($sql),'<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProductCombinationDetail($id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination_details . " WHERE id = " . $id . $sql_text;
      $item = $dbs->get_row($sql);
      // '<pre>',var_dump($sql),'<pre>';
      return $item;
   }

   public function getProductCombinationDetailByrRef($ref_id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination_details . " WHERE material_reference = " . $ref_id . $sql_text;
      $item = $dbs->get_row($sql);
      // '<pre>',var_dump($sql),'<pre>';
      return $item;
   }


   public function updateProductCombinationDetail($data, $id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_combination_details . " SET " . $sql_text . " WHERE id = " . $id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function updateAllProductCombinationDetail($data, $id)
   {
      global $dbs;

      $sql = "UPDATE " . $this->table_product_combination_details . " SET material = " . $data . " WHERE material = " . $id;

      $update = $dbs->query($sql);

      if ($update) {
         return true;
      } else {
         return false;
      }
   }


   public function updateProductCombinationDetailByrRef($data, $ref_id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_combination_details . " SET " . $sql_text . " WHERE material_reference = " . $ref_id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function updateProductCombinationDetailByrRefDefault($data, $ref_id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_combination_details . " SET " . $sql_text . " WHERE material_default = '1' AND material_reference = " . $ref_id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }


   public function getProductCombDtlsByMtf($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null, $default = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_clauses = [];
         foreach ($order as $o) {
            $order_clauses[] = $o[0] . ' ' . $o[1];
         }
         $order_text = ' ORDER BY ' . implode(', ', $order_clauses);
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      if (!is_null($default)) {
         if ($default != 2) {
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }
            $sql_text .= ' pcd.material_default = ' . $default;
         }
      } else {
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }
         $sql_text .= ' pcd.material_default = 1';
      }

      // AS pcd INNER JOIN hd_product_materials AS pm ON pcd.material_reference=pm.id
      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination_details . " AS pcd INNER JOIN " . $this->table_product_materials . " AS pm ON pcd.material_reference = pm.id " . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProductCombDtlsByMtfSingle($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null)
   {
      global $dbs;

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }
      // AS pcd INNER JOIN hd_product_materials AS pm ON pcd.material_reference=pm.id
      $sql = "SELECT " . $cols . " FROM " . $this->table_product_combination_details . " AS pcd INNER JOIN " . $this->table_product_materials . " AS pm ON pcd.material_reference = pm.id " . $sql_text . $order_text;
      // echo '<pre>',var_dump($sql),'<pre>';
      $items = $dbs->get_row($sql);
      if (!empty($items)) {
         return $items;
      } else {
         return false;
      }
   }

   public function deleteProductCombinationDetail($id)
   {
      global $dbs;

      $sql = "DELETE FROM " . $this->table_product_combination_details . " WHERE id = " . $id;
      $delete = $dbs->query($sql);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function deleteProductCombinationDetailByRef($ref_id)
   {
      global $dbs;

      $sql = "DELETE FROM " . $this->table_product_combination_details . " WHERE material_reference = " . $ref_id;
      $delete = $dbs->query($sql);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function getDefaultProductCombDtls($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null)
   {
      global $dbs;

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' AND';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {
            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_materials . " AS pm INNER JOIN " . $this->table_product_combination_details . " AS pcd ON pm.id = pcd.material_reference WHERE pcd.material_default = 1" . $sql_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }




   public function addProductCatalogue($data)
   {
      global $dbs;

      $sql = "INSERT INTO " . $this->table_product_catalogue . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo "";
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function getProductCatalogues($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null)
   {
      global $dbs;

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_catalogue . $sql_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>',var_dump($sql),'<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProductdiscount($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null)
   {
      global $dbs;

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_discount . $sql_text . $order_text;
      $items = $dbs->get_results($sql);
      // '<pre>',var_dump($sql),'<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function addProductDiscount($data)
   {
      global $dbs;

      $sql = "INSERT INTO " . $this->table_product_discount . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo "";
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function deleteProductDiscount($id)
   {
      global $dbs;

      $delete = $dbs->query("DELETE FROM " . $this->table_product_discount . " WHERE id = " . $id);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function getDiscount($id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $item = $dbs->get_row("SELECT " . $cols . " FROM " . $this->table_product_discount . " WHERE id = " . $id . $sql_text);
      return $item;
   }

   public function updateProductDiscount($data, $product_id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_discount . " SET " . $sql_text . " WHERE id = " . $product_id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function addProductShape($data)
   {
      global $dbs;

      $sql = "INSERT INTO " . $this->table_product_shapes . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }


   public function getProductShapes($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_shapes . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }


   public function getProductShape($shape_id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_shapes . " WHERE id = " . $shape_id . $sql_text;
      $item = $dbs->get_row($sql);
      // '<pre>',var_dump($sql),'<pre>';
      return $item;
   }


   public function updateProductShape($data, $shape_id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_shapes . " SET " . $sql_text . " WHERE id = " . $shape_id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   public function getProductFabricAliasNames($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_fabric_name . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function getProductFabricAliasName($id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_fabric_name . " WHERE id = " . $id . $sql_text;
      $item = $dbs->get_row($sql);
      // '<pre>',var_dump($sql),'<pre>';
      return $item;
   }

   public function addProductFabricAliasName($data)
   {
      global $dbs;

      $sql = "INSERT INTO " . $this->table_product_fabric_name . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function getMaterialName($id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_materials . " WHERE material_id = " . $id . $sql_text;
      $item = $dbs->get_row($sql);
      // '<pre>',var_dump($sql),'<pre>';
      return $item;
   }

   public function getProductModificationLibrary($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_library_modification_history . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function addProductModificationLibrary($data)
   {
      global $dbs;

      $sql = "INSERT INTO " . $this->table_product_library_modification_history . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // echo '<pre>',var_dump($sql),'<pre>';
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function addReviseData($data)
   {
      global $dbs;
      $sql = "INSERT INTO " . $this->table_product_revise_data . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
      // print("<pre>" . var_dump($sql) . "</pre>");
      $insert = $dbs->query($sql);
      if ($insert) {
         return $dbs->insert_id;
      } else {
         return false;
      }
   }

   public function getReviseData($product_id, $cols = '*', $where = null)
   {
      global $dbs;

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table_product_revise_data . " WHERE product_id = " . $product_id . $sql_text;
      // $item = $dbs->get_row($sql);
      $items = $dbs->get_results($sql);
      if ($items) {
         $item = $items[0];
      } else {
         $item = '';
      }
      return $item;

      // echo '<pre>',var_dump($items),'<pre>';
   }
   public function getReviseProductsData($cols = '*', $order = null, $where = null, $limit = null, $between = null, $in_clause = null, $group_by = null, $is_null = null)
   {
      global $dbs;

      $group_text = '';
      if (!is_null($group_by)) {
         $group_text .= ' GROUP BY ' . $group_by;
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
            /* $where = array(array('col_name','operator','deger'),array('col_name','>','5')); */
            if ($sql_text != '') {
               $sql_text .= ' AND';
            } else {
               $sql_text .= ' WHERE';
            }

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($between)) {
         /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         if (is_array($between) && count($between) > 0) {

            foreach ($between as $b) {
               $sql_text .= " " . $b[0] . " BETWEEN '" . $b[1] . "' AND '" . $b[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         if ($sql_text != '') {
            $sql_text .= ' AND';
         } else {
            $sql_text .= ' WHERE';
         }

         $in_text = '';
         if (is_array($in_clause) && count($in_clause) > 0) {
            $sql_text .= " " . $in_clause[0] . " IN(";
            if (is_array($in_clause[1])) {
               foreach ($in_clause[1] as $clause) {
                  $in_text .= "'" . $clause . "', ";
               }
               $in_text = rtrim($in_text, ', ');
            } else {
               $in_text = $in_clause[1];
            }
            $sql_text .= $in_text . ")";
         } else {
            return false;
         }
      }

      if (!is_null($is_null)) {
         if ($sql_text != '') {
            $sql_text .= ' AND ';
         } else {
            $sql_text .= ' WHERE ';
         }
         $sql_text .= $is_null[0] . ' ' . $is_null[1];
      }

      if (!is_null($limit)) {
         $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
      }


      $sql = "SELECT " . $cols . " FROM " . $this->table_product_revise_data . $sql_text . $group_text . $order_text;
      $items = $dbs->get_results($sql);
      // echo '<pre>', var_dump($sql), '<pre>';
      if (!empty($items)) {
         return $items;
      } else {
         return [];
      }
   }

   public function deleteReviseProductData($id)
   {
      global $dbs;

      $delete = $dbs->query("DELETE FROM " . $this->table_product_revise_data . " WHERE product_id = " . $id);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function getMaterialLastOneYear()
   {
      global $dbs;

      $sql = "SELECT
            m.material_name,
            m.material_category,
            SUM(CASE
                WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'fabric'
                   THEN cd.length
                WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'metal'
                   THEN cd.length
                WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'glass'
                   THEN cd.length
                WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'wood'
                   THEN cd.area
                WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'marble'
                   THEN cd.area
                WHEN cd.length IS NOT NULL AND cd.length != '' AND cd.width IS NOT NULL AND cd.width != '' AND cd.quantity IS NOT NULL AND cd.quantity != '' AND m.material_category = 'pillow'
                   THEN cd.length * cd.width * cd.quantity
                ELSE 0
            END) AS total_area,
            SUM(CASE
                WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'fabric'
                   THEN (cd.length) * m.material_price
                WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'metal'
                   THEN cd.length * m.material_price
                WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'glass'
                   THEN cd.length * m.material_price
                WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'wood'
                   THEN cd.area * m.material_price
                WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'marble'
                   THEN cd.area * m.material_price
               WHEN cd.length IS NOT NULL AND cd.length != '' AND cd.width IS NOT NULL AND cd.width != '' AND cd.quantity IS NOT NULL AND cd.quantity != '' AND cd.pipping IS NULL AND m.material_category = 'pillow'
                  THEN (cd.length * cd.width * cd.quantity * m.material_price)/100
               WHEN cd.length IS NOT NULL AND cd.length != '' AND cd.width IS NOT NULL AND cd.width != '' AND cd.quantity IS NOT NULL AND cd.quantity != '' AND cd.pipping IS NOT NULL AND m.material_category = 'pillow'
                  THEN (2*(cd.length + cd.width)* 30 * 1.1 * cd.quantity * m.material_price)/100
                ELSE 0
            END) AS total_material_cost
         FROM hd_orders o
         JOIN hd_order_details od ON o.order_id = od.order_id
         JOIN hd_product_materials pm ON od.product_id = pm.product_id
         JOIN hd_product_combination_details cd ON pm.combination_id = cd.combination_id AND pm.id = cd.material_reference
         JOIN hd_materials m ON cd.material = m.material_id
         WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
         AND m.material_price IS NOT NULL
         GROUP BY cd.material";

      $results = $dbs->get_results($sql);

      if (!empty($results)) {
         $unitMap = [
            'fabric' => 'm',
            'metal' => 'kg',
            'glass' => 'm',
            'wood' => 'm²',
            'marble' => 'm²',
            'pillow' => 'm²'
         ];

         $total_material_cost = 0;
         $total_material = 0;
         foreach ($results as $row) {
            $unit = $unitMap[$row->material_category] ?? '';
            // echo "Material: " . $row->material_name . "<br>";
            // echo "Total Area: " . $row->total_area . " " . $unit . "<br>";
            // echo "Total Cost: $" . number_format($row->total_material_cost, 2) . "<br>";
            if ($row->total_area > 0) {
               $cost_per_unit = $row->total_material_cost / $row->total_area;
               // echo "Cost per Unit: $" . number_format($cost_per_unit, 2) . " / $unit<br>";
               $total_material += $row->total_area;
            }
            // echo "<h2>Total Material Used = $total_material Unit</h2>";
            $total_material_cost += $row->total_material_cost;
         }

         // echo "Total Material Cost (last 1 year): $" . number_format($total_material_cost, 2) . "<br><br>";
         return $total_material;
      } else {
         // echo "No material cost data found in the last 1 year.";
         return 0;
      }
   }

   public function getMaterialByProductId($product_id)
   {
      global $dbs;

      $sql = "SELECT
        m.material_name,
        m.material_category,
        SUM(CASE
            WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'fabric'
               THEN cd.length
            WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'metal'
               THEN cd.length
            WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'glass'
               THEN cd.length
            WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'wood'
               THEN cd.area
            WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'marble'
               THEN cd.area
            WHEN cd.length IS NOT NULL AND cd.length != '' AND cd.width IS NOT NULL AND cd.width != '' AND cd.quantity IS NOT NULL AND cd.quantity != '' AND m.material_category = 'pillow'
               THEN cd.length * cd.width * cd.quantity
            ELSE 0
        END) AS total_area,
        SUM(CASE
            WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'fabric'
               THEN cd.length * m.material_price
            WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'metal'
               THEN cd.length * m.material_price
            WHEN cd.length IS NOT NULL AND cd.length != '' AND m.material_category = 'glass'
               THEN cd.length * m.material_price
            WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'wood'
               THEN cd.area * m.material_price
            WHEN cd.area IS NOT NULL AND cd.area != '' AND m.material_category = 'marble'
               THEN cd.area * m.material_price
            WHEN cd.length IS NOT NULL AND cd.length != '' AND cd.width IS NOT NULL AND cd.width != '' AND cd.quantity IS NOT NULL AND cd.pipping != '' AND cd.pipping IS NULL AND m.material_category = 'pillow'
               THEN (cd.length * cd.width * cd.quantity * m.material_price)/100
            WHEN cd.length IS NOT NULL AND cd.length != '' AND cd.width IS NOT NULL AND cd.width != '' AND cd.quantity IS NOT NULL AND cd.pipping != '' AND cd.pipping IS NOT NULL AND m.material_category = 'pillow'
               THEN (2*(cd.length + cd.width) * 30 * 1.1 * cd.quantity * m.material_price)/100
            ELSE 0
        END) AS total_material_cost
      FROM hd_product_materials pm
      JOIN hd_product_combination_details cd
         ON pm.combination_id = cd.combination_id
         AND pm.id = cd.material_reference
      JOIN hd_materials m ON cd.material = m.material_id
      WHERE pm.product_id = $product_id
      AND m.material_price IS NOT NULL
      GROUP BY cd.material";

      $results = $dbs->get_results($sql);

      if (!empty($results)) {
         $unitMap = [
            'fabric' => 'm',
            'metal' => 'kg',
            'glass' => 'm',
            'wood' => 'm²',
            'marble' => 'm²',
            'pillow' => 'm²'
         ];
         $total_material_cost = 0;
         $total_material = 0;
         foreach ($results as $row) {
            $unit = $unitMap[$row->material_category] ?? '';
            // echo "Material: " . $row->material_name . "<br>";
            // echo "Total Area: " . $row->total_area . " " . $unit . "<br>";
            // echo "Total Cost: $" . number_format($row->total_material_cost, 2) . "<br><br>";
            if ($row->total_area > 0) {
               $cost_per_unit = $row->total_material_cost / $row->total_area;
               // echo "Cost per Unit: $" . number_format($cost_per_unit, 2) . " / $unit<br><br>";
               $total_material += $row->total_area;
            }
            $total_material_cost += $row->total_material_cost;
         }
         return ['total_material' => $total_material, 'total_material_cost' => $total_material_cost];
         // echo "Total Material Cost: $" . number_format($total_material_cost, 2) . "<br><br>";
      } else {
         // echo "No materials found for product_id = $product_id.";
         return ['total_material' => 0, 'total_material_cost' => 0];
      }
   }
   public function getIndicationPrice($product_id)
   {
      $payment = new Payment();
      $totalExpenseLastYear = $payment->getTotalExpensesUsdLastOneYear();
      $totalMaterialLastYear = $this->getMaterialLastOneYear();
      $totalMaterialOfProduct = $this->getMaterialByProductId($product_id);
      $manufacturingCostOfProduct = ($totalExpenseLastYear / $totalMaterialLastYear) * $totalMaterialOfProduct['total_material'];
      $indicationPrice = $totalMaterialOfProduct['total_material_cost'] + $manufacturingCostOfProduct;
      $indicationPrice = ($indicationPrice / 3) * 5;
      return $indicationPrice;
   }

   public function deleteProductCombinationDetailByCombinationId($id)
   {
      global $dbs;

      $sql = "DELETE FROM " . $this->table_product_combination_details . " WHERE combination_id = " . $id;
      $delete = $dbs->query($sql);
      if ($delete) {
         return true;
      } else {
         return false;
      }
   }

   public function updateProductCombinationDetailCombinationId($data, $id)
   {
      global $dbs;

      $sql_text = '';
      foreach ($data as $key => $item) {
         $sql_text .= $key . " = '" . $item . "', ";
      }

      $sql_text = rtrim($sql_text, ', ');

      $sql = "UPDATE " . $this->table_product_combination_details . " SET " . $sql_text . " WHERE combination_id = " . $id;
      // echo '<pre>',var_dump($sql),'</pre>';
      $update = $dbs->query($sql);
      if ($update) {
         return true;
      } else {
         return false;
      }
   }

   // Function to update product styles using ChatGPT for all products
   public function updateProductStyles()
   {

      // Get all products
      $products = $this->getProducts(
         'product_id, product_img, product_desc, product_short_desc, product_full_desc, product_style_type'
      );

      if (empty($products)) {
         return ['success' => false, 'message' => 'No products found'];
      }

      $updatedCount = 0;
      $errorCount = 0;
      $totalProducts = count($products);

      echo "Starting style update for {$totalProducts} products...\n";

      foreach ($products as $index => $product) {
         $current = $index + 1;
         echo "Processing product {$current}/{$totalProducts} (ID: {$product->product_id})... ";

         try {
            // Prepare the prompt for ChatGPT
            $prompt = $this->createStyleAnalysisPrompt($product);

            // Call ChatGPT to analyze the style using existing function
            $styleResult = callChatGPT($prompt);

            // Parse the response to get the style type
            $styleType = $this->parseStyleResponse($styleResult);

            if ($styleType !== null) {
               // Update the product with the determined style
               $updateData = ['product_style_type' => $styleType];
               $result = $this->updateProduct($updateData, $product->product_id);

               if ($result) {
                  $updatedCount++;
                  echo "✓ Updated with style: {$styleType}\n";
               } else {
                  $errorCount++;
                  echo "✗ Database update failed\n";
               }
            } else {
               $errorCount++;
               echo "✗ Could not determine style\n";
            }
         } catch (Exception $e) {
            $errorCount++;
            echo "✗ Error: " . $e->getMessage() . "\n";
            continue;
         }

         // Add a small delay to avoid hitting API rate limits (only if not last product)
         if ($current < $totalProducts) {
            sleep(2); // Increased to 2 seconds for better rate limiting
         }
      }

      echo "\n=== COMPLETED ===\n";
      echo "Total products processed: {$totalProducts}\n";
      echo "Successfully updated: {$updatedCount}\n";
      echo "Errors: {$errorCount}\n";

      return [
         'success' => true,
         'updated' => $updatedCount,
         'errors' => $errorCount,
         'total' => $totalProducts
      ];
   }

   // Helper function to create the prompt for style analysis
   private function createStyleAnalysisPrompt($product)
   {
      $prompt = "Analyze this furniture product and determine its style category. 
Categories: 
1 = Modern (clean lines, minimalistic, functional, neutral colors)
2 = Contemporary (current trends, often blends styles, innovative materials)
3 = Luxury (high-end, premium materials, ornate details, sophisticated)

Product Information:
";

      // Add description if available - limit length to avoid token limits
      if (!empty($product->product_desc)) {
         $prompt .= "Description: " . substr($product->product_desc, 0, 500) . "\n";
      }

      if (!empty($product->product_short_desc)) {
         $prompt .= "Short Description: " . substr($product->product_short_desc, 0, 300) . "\n";
      }

      if (!empty($product->product_full_desc)) {
         $prompt .= "Full Description: " . substr($product->product_full_desc, 0, 500) . "\n";
      }

      // Add image information if available
      if (!empty($product->product_img)) {
         $prompt .= "Image file available: " . URL . "/uploads/" . $product->product_img . "\n";
      }

      $prompt .= "\nBased on the available information, determine the most appropriate style category (1, 2, or 3). 
Respond ONLY with the number (1, 2, or 3) and nothing else. 
If you cannot determine the style, respond with '0'.";

      return $prompt;
   }

   // Helper function to parse ChatGPT response
   private function parseStyleResponse($response)
   {
      // Clean the response and extract the style type
      $cleanedResponse = trim($response);

      // Look for numbers 1, 2, or 3 in the response
      if (preg_match('/[123]/', $cleanedResponse, $matches)) {
         return (int)$matches[0];
      }

      return null;
   }
}
