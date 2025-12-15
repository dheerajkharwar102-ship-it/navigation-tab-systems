<?php

class Catalog
{
    private $table;
    private $customer_catalogue_table;
    private $base_category_table;

    public function __construct()
    {
        $this->table = 'hd_catalogs';
        $this->customer_catalogue_table = 'hd_customer_catalogue';
        $this->base_category_table = 'hd_base_category';
    }

    public function getCatalogs($cols = '*', $where = null, $in_clause = null, $order = null, $between = null, $limit = null)
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

        if (!is_null($between)) {
            /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
            if (is_array($where) && count($where) > 0) {
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
            if ((is_array($where) && count($where) > 0) || (is_array($between) && count($between) > 0)) {
                $sql_text .= ' AND';
            } else {
                $sql_text .= ' WHERE';
            }

            $in_text = '';
            if (is_array($in_clause) && count($in_clause) > 0) {
                $sql_text .= " " . $in_clause[0] . " IN(";
                foreach ($in_clause[1] as $clause) {
                    $in_text .= "'" . $clause . "', ";
                }
                $in_text = rtrim($in_text, ', ');
                $sql_text .= $in_text . ")";
            } else {
                return false;
            }
        }

        if (!is_null($limit)) {
            $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
        }

        $orders = $dbs->get_results("SELECT " . $cols . " FROM " . $this->table . $sql_text . $order_text);
        if (!empty($orders)) {
            return $orders;
        } else {
            return [];
        }
    }

    public function addBaseCategory($data)
    {
        global $dbs;
        $sql = "INSERT INTO " . $this->base_category_table . " (" . implode(', ', array_keys($data)) . ") VALUES ('" . implode("', '", $data) . "')";
        // print("<pre>" . var_dump($sql) . "</pre>");
        $insert = $dbs->query($sql);
        if ($insert) {
            return $dbs->insert_id;
        } else {
            return false;
        }
    }
    public function updateBaseCategory($data, $id)
    {
        global $dbs;

        $sql_text = '';
        foreach ($data as $key => $item) {
            $sql_text .= $key . " = '" . $item . "', ";
        }

        $sql_text = rtrim($sql_text, ', ');

        $sql = "UPDATE " . $this->base_category_table . " SET " . $sql_text . " WHERE id = " . $id;
        // echo '<pre>',var_dump($sql),'</pre>';
        $update = $dbs->query($sql);
        if ($update) {
            return true;
        } else {
            return false;
        }
    }
    public function getBaseCategories($cols = '*', $where = null, $in_clause = null, $order = null, $between = null, $limit = null)
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

        if (!is_null($between)) {
            /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
            if (is_array($where) && count($where) > 0) {
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
            if ((is_array($where) && count($where) > 0) || (is_array($between) && count($between) > 0)) {
                $sql_text .= ' AND';
            } else {
                $sql_text .= ' WHERE';
            }

            $in_text = '';
            if (is_array($in_clause) && count($in_clause) > 0) {
                $sql_text .= " " . $in_clause[0] . " IN(";
                foreach ($in_clause[1] as $clause) {
                    $in_text .= "'" . $clause . "', ";
                }
                $in_text = rtrim($in_text, ', ');
                $sql_text .= $in_text . ")";
            } else {
                return false;
            }
        }

        if (!is_null($limit)) {
            $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
        }

        $orders = $dbs->get_results("SELECT " . $cols . " FROM " . $this->base_category_table . $sql_text . $order_text);
        if (!empty($orders)) {
            return $orders;
        } else {
            return [];
        }
    }

    public function getBaseCategory($category_id, $cols = '*', $where = null)
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

        // echo "SELECT ".$cols." FROM ".$this->table." WHERE catalog_id = ".$catalog_id.$sql_text;

        $items = $dbs->get_results("SELECT " . $cols . " FROM " . $this->base_category_table . " WHERE id = " . $category_id . $sql_text);
        if ($items) {
            $item = $items[0];
        } else {
            $item = '';
        }
        return $item;
    }

    public function deleteBaseCategory($id)
    {
        global $dbs;

        $delete = $dbs->query("UPDATE " . $this->base_category_table . " SET status = 0 WHERE id = " . $id);
        if ($delete) {
            return true;
        } else {
            return false;
        }
    }

    public function getCatalog($catalog_id, $cols = '*', $where = null)
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

        // echo "SELECT ".$cols." FROM ".$this->table." WHERE catalog_id = ".$catalog_id.$sql_text;

        $order = $dbs->get_results("SELECT " . $cols . " FROM " . $this->table . " WHERE catalog_id = " . $catalog_id . $sql_text);
        return $order;
    }

    public function addCatalog($data)
    {
        global $dbs;

        $insert = $dbs->query("INSERT INTO " . $this->table . "(catalog_name,catalog_add_date) VALUES('" . $data['catalog_name'] . "','" . $data['catalog_add_date'] . "')");
        if ($insert) {
            return $dbs->insert_id;
        } else {
            return false;
        }
    }

    public function updateCatalog($data, $catalog_id)
    {
        global $dbs;

        $sql_text = '';
        foreach ($data as $key => $item) {
            $sql_text .= $key . " = '" . $item . "', ";
        }

        $sql_text = rtrim($sql_text, ', ');

        $update = $dbs->query("UPDATE " . $this->table . " SET " . $sql_text . " WHERE catalog_id = " . $catalog_id);
        if ($update) {
            return true;
        } else {
            return false;
        }
    }

    public function checkCatalogById($catalog_id)
    {
        global $dbs;
        $varmi = $dbs->get_var("SELECT COUNT(catalog_id) FROM " . $this->table . " WHERE catalog_id = " . $catalog_id);
        if ($varmi > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteCatalog($catalog_id)
    {
        global $dbs;

        $delete = $dbs->query("UPDATE " . $this->table . " SET catalog_status = 2 WHERE catalog_id = " . $catalog_id);
        if ($delete) {
            return true;
        } else {
            return false;
        }
    }

    public function getCustomerCatalogs($cols = '*', $where = null, $in_clause = null, $order = null, $between = null, $limit = null)
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

        if (!is_null($between)) {
            /** $between = [['col_name','start','end'],['order_date','start','end'],...] */
            if (is_array($where) && count($where) > 0) {
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
            if ((is_array($where) && count($where) > 0) || (is_array($between) && count($between) > 0)) {
                $sql_text .= ' AND';
            } else {
                $sql_text .= ' WHERE';
            }

            $in_text = '';
            if (is_array($in_clause) && count($in_clause) > 0) {
                $sql_text .= " " . $in_clause[0] . " IN(";
                foreach ($in_clause[1] as $clause) {
                    $in_text .= "'" . $clause . "', ";
                }
                $in_text = rtrim($in_text, ', ');
                $sql_text .= $in_text . ")";
            } else {
                return false;
            }
        }

        if (!is_null($limit)) {
            $order_text .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
        }

        $orders = $dbs->get_results("SELECT " . $cols . " FROM " . $this->customer_catalogue_table . $sql_text . $order_text);
        if (!empty($orders)) {
            return $orders;
        } else {
            return [];
        }
    }
}
