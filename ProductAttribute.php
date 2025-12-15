<?php

class ProductAttribute
{
    public $table;
   public $table_products;
    public $table_price_updates;
    public $table_web_menu;

    public function __construct()
    {
        $this->table = 'hd_product_attr';
      $this->table_products = 'hd_products';
        $this->table_price_updates = 'hd_product_price_updates';
        $this->table_web_menu = 'hd_web_menu';

    }

    public function getProductAttributes($cols = '*', $order = null, $where = null, $in_clause = null)
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

        $sql = "SELECT " . $cols . " FROM " . $this->table . $sql_text . $order_text;
      // print("<pre>".var_dump($sql)."</pre>");
      $attrs = $dbs->get_results($sql);
      if (!empty($attrs)) {
         return $attrs;
      } else {
         return [];
      }
   }

   public function getProductAttributesUnique($cols = '*', $order = null, $where = null, $in_clause = null, $groupByName = false)
   {
      global $dbs;

      // If grouping by name, we need to handle the SELECT clause differently
      if ($groupByName) {
         if ($cols === '*') {
               $cols = "
                  MIN(attr_id) as attr_id,
                  attr_name,
                  attr_desc,
                  attr_customs_code,
                  calculate_type,
                  attr_type,
                  attr_stock_status,
                  attr_rates,
                  attr_status,
                  GROUP_CONCAT(DISTINCT catalog_id) as catalog_ids,
                  GROUP_CONCAT(DISTINCT online_category) as online_categories,
                  COUNT(*) as duplicate_count,
                  MIN(attr_add_date) as attr_add_date,
                  MIN(attr_update_date) as attr_update_date,
                  online_product_img,
                  icon,
                  attr_online_status,
                  wholesale_percentage,
                  attr_web_title,
                  attr_web_desc
               ";
         }
         
         // Replace GROUP_CONCAT for attr_id with underscore separator
         if (strpos($cols, 'MIN(attr_id) as attr_id') !== false) {
               $cols = str_replace(
                  'MIN(attr_id) as attr_id', 
                  "GROUP_CONCAT(DISTINCT attr_id SEPARATOR '_') as attr_ids", 
                  $cols
               ).',MIN(attr_id) as attr_id';
         }
      }

      $order_text = '';
      if (!is_null($order)) {
         $order_text = ' ORDER BY ' . $order[0] . ' ' . $order[1];
      }

      $sql_text = '';
      if (!is_null($where)) {
         if (is_array($where) && count($where) > 0) {
               $sql_text .= ' WHERE';
               foreach ($where as $w) {
                  $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
               }
               $sql_text = rtrim($sql_text, ' AND');
         } else {
               return false;
         }
      }

      if (!is_null($in_clause)) {
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

      // Add GROUP BY if requested
      $group_text = '';
      if ($groupByName) {
         $group_text = ' GROUP BY attr_name';
      }

      $sql = "SELECT " . $cols . " FROM " . $this->table . $sql_text . $group_text . $order_text;
      
      // For debugging
      // error_log("Product Attributes SQL: " . $sql);
      
      $attrs = $dbs->get_results($sql);
      if (!empty($attrs)) {
         return $attrs;
      } else {
         return [];
      }
   }

   public function getProductAttributesGroupByCatalog($cols = '*', $order = null, $where = null, $in_clause = null)
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

      $sql = "SELECT " . $cols . " FROM " . $this->table . $sql_text . " GROUP BY catalog_id " . $order_text;
      // print("<pre>".var_dump($sql)."</pre>");
      $attrs = $dbs->get_results($sql);
      if (!empty($attrs)) {
         return $attrs;
      } else {
         return [];
      }
   }

   public function getProductAttributesDistinctOnlineCategory($cols = '*', $order = null, $where = null, $in_clause = null)
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

      $sql = "SELECT " . $cols . " FROM " . $this->table . $sql_text . " GROUP BY online_category " . $order_text;
      // print("<pre>" . var_dump($sql) . "</pre>");
        $attrs = $dbs->get_results($sql);
        if (!empty($attrs)) {
            return $attrs;
        } else {
            return [];
        }
    }

    public function getProductAttribute($attribute_id, $cols = '*', $where = null)
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

        $attr = $dbs->get_row("SELECT " . $cols . " FROM " . $this->table . " WHERE attr_id = " . $attribute_id . $sql_text);
        return $attr;
    }

    public function checkProductAttributeById($attribute_id)
    {
        global $dbs;
        $varmi = $dbs->get_var("SELECT COUNT(attr_id) FROM " . $this->table . " WHERE attr_id = " . $attribute_id);
        if ($varmi > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkProductAttributeByName($attribute_name, $not_in_id = null, $catalog_id = null)
    {
        global $dbs;

        $sql_text = '';
        if ($not_in_id) {
            $sql_text .= ' AND attr_id != ' . $not_in_id;
        }

        if ($catalog_id) {
            $sql_text .= ' AND catalog_id = ' . $catalog_id;
        }

        $varmi = $dbs->get_var("SELECT COUNT(attr_id) FROM " . $this->table . " WHERE attr_name = '" . $attribute_name . "'" . $sql_text);
        if ($varmi > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function addProductAttribute($data)
    {
        global $dbs;

        // $insert = $dbs->query("INSERT INTO " . $this->table . "(catalog_id,attr_name,attr_desc,attr_customs_code,calculate_type,attr_type,attr_stock_status,attr_rates,attr_add_date,attr_online_status,online_product_img) VALUES('" . $data['catalog_id'] . "','" . $data['attr_name'] . "','" . $data['attr_desc'] . "','" . $data['attr_customs_code'] . "','" . $data['calculate_type'] . "','" . $data['attr_type'] . "','" . $data['attr_stock_status'] . "','" . $data['attr_rates'] . "','" . $data['attr_add_date'] . "','" . $data['attr_online_status'] . "','" . $data['online_product_img'] . "')");
        $sql = "INSERT INTO " . $this->table . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
        $insert = $dbs->query($sql);
        if ($insert) {
            return true;
        } else {
            return false;
        }
    }
 
    public function updateProductAttribute($data, $attribute_id)
    {
        global $dbs;

        // $update = $dbs->query("UPDATE " . $this->table . " SET catalog_id = '" . $data['catalog_id'] . "', attr_name = '" . $data['attr_name'] . "', attr_desc = '" . $data['attr_desc'] . "', attr_customs_code = '" . $data['attr_customs_code'] . "', calculate_type = '" . $data['calculate_type'] . "', attr_type = '" . $data['attr_type'] . "', attr_stock_status = '" . $data['attr_stock_status'] . "', attr_rates = '" . $data['attr_rates'] . "', attr_status = '" . $data['attr_status'] . "', attr_update_date = '" . $data['attr_update_date'] . "', attr_online_status = '" . $data['attr_online_status'] . "', online_product_img = '" . $data['online_product_img'] . "' WHERE attr_id = " . $attribute_id);
        $sql_text = '';
        foreach ($data as $key => $item) {
           $sql_text .= $key . " = '" . $item . "', ";
        }
  
        $sql_text = rtrim($sql_text, ', ');
  
        $sql = "UPDATE " . $this->table . " SET " . $sql_text . "  WHERE attr_id = " . $attribute_id;
        // print("<pre>".var_dump($sql)."</pre>");
        $update = $dbs->query($sql);
  
        if ($update) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteProductAttribute($attribute_id)
    {
        global $dbs;
        $delete = $dbs->query("UPDATE " . $this->table . " SET attr_status = 2 WHERE attr_id = " . $attribute_id);
        if ($delete) {
            return true;
        } else {
            return false;
        }
    }

    public function getPriceUpdates($attr_id = null)
    {
        global $dbs;

        $sql_text = '';
        if ($attr_id != '') {
            $sql_text = ' WHERE attr_id = ' . $attr_id;
        }

        $items = $dbs->get_results("SELECT * FROM " . $this->table_price_updates . $sql_text . " ORDER BY id DESC");
        if (!empty($items)) {
            return $items;
        } else {
            return [];
        }
    }

    public function addPriceUpdate($data, $attr_id)
    {
        global $dbs;

        $insert = $dbs->query("INSERT INTO " . $this->table_price_updates . "(attr_id,updated_user,price_rate,rate_type,update_date,old_price_data) VALUES('" . $attr_id . "','" . $data['updated_user'] . "','" . $data['price_rate'] . "','" . $data['rate_type'] . "','" . $data['update_date'] . "','" . $data['old_price_data'] . "')");
        if ($insert) {
            return true;
        } else {
            return false;
        }
    }

    public function getStockedAttrIds()
    {
        global $dbs;

        $return_ids = [];
        $items = $dbs->get_results("SELECT attr_id FROM " . $this->table . " WHERE attr_stock_status = 'yes'");
        if (count($items) > 0) {
            foreach ($items as $item) {
                if (!in_array($item->attr_id, $return_ids)) {
                    $return_ids[] = $item->attr_id;
                }
            }
        }

        return $return_ids;
    }

    public function getStockCount()
    {
        global $dbs;

        $return_ids = [];
        $stockCount = $dbs->get_var("SELECT count('attr_id') as total_count FROM " . $this->table . " WHERE attr_stock_status = 'no' and attr_status = '1'");


        return $stockCount;
    }

    
    public function checkWebMenu($text)
    {
        global $dbs;
      $sql = "SELECT id FROM " . $this->table_web_menu . " WHERE category = '" . $text . "'";
        $varmi = $dbs->get_var($sql);
        if ($varmi > 0) {
            return $varmi;
        } else {
            return false;
        }
    }

    public function addWebMenu($data)
    {
        global $dbs;

        $sql = "INSERT INTO " . $this->table_web_menu . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
        $insert = $dbs->query($sql);
        if ($insert) {
            return true;
        } else {
            return false;
        }
    }

    public function getWebMenus($cols = '*', $order = null, $where = null, $in_clause = null)
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

        $sql = "SELECT " . $cols . " FROM " . $this->table_web_menu . $sql_text . $order_text;
        $attrs = $dbs->get_results($sql);
        if (!empty($attrs)) {
            return $attrs;
        } else {
            return [];
        }
    }

    public function getWebMenu($id, $cols = '*', $where = null)
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

        $attr = $dbs->get_row("SELECT " . $cols . " FROM " .  $this->table_web_menu  . " WHERE id = " . $id . $sql_text);
        return $attr;
    }

    public function updateWebMenu($data, $id)
    {
        global $dbs;

        // $update = $dbs->query("UPDATE " . $this->table_web_menu . " SET catalog_id = '" . $data['catalog_id'] . "', attr_name = '" . $data['attr_name'] . "', attr_desc = '" . $data['attr_desc'] . "', attr_customs_code = '" . $data['attr_customs_code'] . "', calculate_type = '" . $data['calculate_type'] . "', attr_type = '" . $data['attr_type'] . "', attr_stock_status = '" . $data['attr_stock_status'] . "', attr_rates = '" . $data['attr_rates'] . "', attr_status = '" . $data['attr_status'] . "', attr_update_date = '" . $data['attr_update_date'] . "', attr_online_status = '" . $data['attr_online_status'] . "', online_product_img = '" . $data['online_product_img'] . "' WHERE attr_id = " . $attribute_id);
        $sql_text = '';
        foreach ($data as $key => $item) {
           $sql_text .= $key . " = '" . $item . "', ";
        }
  
        $sql_text = rtrim($sql_text, ', ');
  
        $sql = "UPDATE " . $this->table_web_menu . " SET " . $sql_text . "  WHERE id = " . $id;
        // print("<pre>".var_dump($sql)."</pre>");
        $update = $dbs->query($sql);
  
        if ($update) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteWebMenu($id)
    {
        global $dbs;
      $sql = "UPDATE " . $this->table_web_menu . " SET deleted = 1 WHERE id = " . $id;
        $delete = $dbs->query($sql);
        if ($delete) {
            return true;
        } else {
            return false;
        }
    }

   public function getFitoutAttributes($cols = '*', $order = null, $where = null, $in_clause = null)
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
            $sql_text .= ' AND';

            foreach ($where as $w) {
               $sql_text .= " " . $w[0] . " " . $w[1] . " '" . $w[2] . "' AND";
            }

            $sql_text = rtrim($sql_text, ' AND');
         } else {
            return false;
         }
      }

      if (!is_null($in_clause)) {
         /** $in_clause = ['col_name',['clause1','clause2','clause3',...]] */
         $sql_text .= ' AND';

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

      $sql = "
            SELECT {$cols}
            FROM {$this->table} AS pa
            WHERE EXISTS (
               SELECT 1 
               FROM {$this->table_products} AS p
               WHERE pa.attr_id = p.attr_id 
                  AND p.is_fitout != 0
            )
            {$sql_text}
            {$order_text}
         ";
      // print("<pre>".var_dump($sql)."</pre>");
      $attrs = $dbs->get_results($sql);
      if (!empty($attrs)) {
         return $attrs;
      } else {
         return [];
      }
   }
}
