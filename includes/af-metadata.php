<?php
/**
 * Gestisce la creazione dei parametri per una form
 * La classe si s
 * @example new ADFO_class_form()->set_sql('select ...')->get_form();
 */
namespace admin_form;

class  ADFO_class_metadata {
    /**
     * Trova tutte le tabelle "metadata" da un elenco di tabelle.
     * @param ADFO_model $table_model
     * @return array
     */
    static function find_metadata_tables($table_model) {
        if ($table_model->sql_type() != "select")   return [];
       
        $tables = [];
        $sql_schema = $table_model->get_schema();
    
        $pris = [];
        $already_inserted = [];
        if (!is_countable($sql_schema))  return [];
       
        foreach ($sql_schema as $field) {
            if (isset($field->orgtable) && $field->orgtable != "" && isset($field->table)) {
                $table = $field->orgtable;
                // devo trovare la primary key
                if (!isset($pris[$field->orgtable])) {
                    $pris[$field->orgtable] = ADFO_fn::get_primary_key($field->orgtable);
                }
                // se non è già stata inserita
                if (!in_array($table, $already_inserted) && $pris[$field->orgtable] != "") {
                    $already_inserted[] = $table;
                    $tables[] = [$table  ."meta", $field->table.".".$pris[$field->orgtable]];
                    $tables[] = [$table  ."_meta", $field->table.".".$pris[$field->orgtable]];
                    if (substr($table,-1) == "s") {
                        if (substr($table,-4) == "ches" || substr($table,-4) == "shes" || substr($table,-3) == "ses" || substr($table,-3) == "xes" || substr($table,-3) == "zes") {
                            $singular = substr($table,0, -2);
                        } else {
                            $singular = substr($table,0, -1);
                        }
                        $tables[] = [$singular ."meta", $field->table.".".$pris[$field->orgtable]];
                        $tables[] = [$singular ."_meta", $field->table.".".$pris[$field->orgtable]];
                    }
                }
            }
        }
   
        $all_tables = ADFO_fn::get_table_list();
        $return_table = [];
      
        foreach ($all_tables['tables'] as $sql_table) {
            $sql_table_name = '';
            foreach ($tables as $val_tab) {
                if ($sql_table == $val_tab[0]) {
                    $sql_table_name =  $val_tab[1]."::".$sql_table;
                    break;
                }
            }
            if ($sql_table_name != "") {
                $metatable_info = self::find_metadata_table_structure($sql_table);
                if (is_countable($metatable_info) && count($metatable_info) == 4) {
                    $return_table[$sql_table_name] = $sql_table;
                    break;
                }
            }
        } 
        return $return_table;
    }

    /**
     * Trovo da una tabella la struttura dei metadata meta_key, meta_value ecc...
     */
    
    static function find_metadata_table_structure($sql_table) {
        global $wpdb;
		$structure = ADFO_fn::get_table_structure($sql_table);
		$table = substr($sql_table,strlen($wpdb->prefix));
		$table = str_replace(["_meta","meta"],"", $table);
		if (substr($table,-1) == "s") {
			$table = substr($sql_table,0, -2);
		}
	
		$columns = [];
		if (count($structure) > 3) {
			foreach ($structure as $field) {
				if ($field->Key == "PRI") {
					$columns['pri'] = $field->Field;
				} elseif ($field->Field != "meta_key" && $field->Field != "meta_value") {
					$columns['parent_id'] = $field->Field;
				} elseif (stripos($field->Field, '_key') !== false ) {
                    $columns['meta_key'] = $field->Field;
				} elseif (stripos($field->Field, '_value') !== false ) {
                    $columns['meta_value'] = $field->Field;
                }
			}
		}
		return $columns;
    }


    /**
     * Verifico se è già stato inserito un metavalue
     * @param ADFO_model $table_model
     * @return boolean
     */
    static function is_inserted_meta($table_model, $sql_table, $meta_key) {
        $metavalues = self::meta_values($table_model, $sql_table);
        return (in_array($meta_key, $metavalues));
    }

    /**
     * Aggiunge alla query la nuova metakey. Modifica table_model.
     * @param ADFO_model $table_model
     * @param string $meta_parent_id il nome della colonna della tabella meta collegata alla tabella principale (es: post_id)
     * @param string $parent_table il nome della tabella a cui si collega la meta (es: wp_posts)
     * @param string $parent_table_id il nome della colonna primary id a cui si collega la meta (es: ID)
     * @return array l'alias della tabella,  $meta_value_alias1 l'alias del meta_value, $meta_value_alias2 l'alias dela primary key
     */
    static function add_sql_meta_data(&$table_model, $sql_table, $meta_key,  $meta_parent_id, $parent_table, $parent_table_id) {
     
        // manca il primary_id della tabella principale!
        $table = $sql_table; // la tabella dei meta
        $meta_pri = ADFO_fn::get_primary_key($table);
        $sql = $table_model->get_current_query();
       // $from_sql = $table_model->get_partial_query_from();
        $alias = $meta_value_alias1 = $meta_value_alias2 = "";
        if ($sql != "" && $table_model->sql_type() == "select") {
         
            $alias = ADFO_fn::get_table_alias($table, $sql, str_replace("_", "", $meta_key));
            $temp_sql_from = ' LEFT JOIN `'.$table.'` `'.$alias.'` ON `'.$alias.'`.`'.$meta_parent_id.'` = `'.$parent_table.'`.`'.$parent_table_id.'` AND `'.$alias.'`.`meta_key` = \''.esc_sql($meta_key).'\'';
            $meta_value_alias1 = ADFO_fn::get_column_alias($meta_key, $sql);
            $meta_value_alias2 = ADFO_fn::get_column_alias($alias."_".$meta_pri, $sql);
            $temp_sql_select = '`'.$alias.'`.`meta_value` AS `'.$meta_value_alias1.'`, `'.$alias.'`.`'.$meta_pri.'` AS `'.$meta_value_alias2.'`';
            $table_model->list_add_select( $temp_sql_select );
            $table_model->list_add_from( $temp_sql_from );
        }
        return [$alias, $meta_value_alias1, $meta_value_alias2];
    }

    /**
     * Modifica table_model Rimuove dalla query la nuova metakey. Modifica table_model.
     * @param ADFO_model $table_model
     * @param string $meta_parent_id il nome della colonna della tabella meta collegata alla tabella principale (es: post_id)
     * @param string $table_alias Basta che il table_alias o il meta_key siano diversi da ""
     * @param string $meta_key il nome della colonna primary id a cui si collega la meta (es: ID) Basta che il table_alias o il meta_key siano diversi da ""
     * @return void
     */
    static function remove_sql_meta_data(&$table_model, $table_alias, $meta_key = "") {
        $select_sql = $table_model->get_partial_query_select(true);
        $from_sql = $table_model->get_partial_query_from(true);
        //var_dump ($from_sql);
        $select_to_remove = [];
        $new_from = [];
        foreach ($from_sql as $from) {
            $add = true;
            if ($table_alias != '' && $meta_key != '') {
                if (stripos($from[2], $meta_key) !== false && str_replace(["`", ' '], '', $table_alias) == str_replace(["`",' '], '',$from[1])) {
                    $select_to_remove[] =  str_replace(["`", ' '], '', $from[1]);
                    $add = false;
                } 
            } else if ($table_alias != '' ) {
                if (str_replace(["`", ' '], '', $table_alias) == str_replace(["`",' '], '',$from[1])) {
                    $select_to_remove[] =  str_replace(["`", ' '], '', $from[1]);
                    $add = false;
                } 
            } else if ($meta_key != '' ) {
                if (stripos($from[2], $meta_key) !== false) {
                    $select_to_remove[] =  str_replace(["`", ' '], '', $from[1]);
                    $add = false;
                } 
            }
            
            if ($add) {
                $new_from[] = $from[3];
            }
        }
        // Ricostruisco il select
        $new_select = [];
        foreach ($select_sql as $rebuild_select) {
            if (!in_array($rebuild_select[0], $select_to_remove)) {
                $new_select[] = $rebuild_select[3];
            }
        }
        $table_model->list_change_select(implode(", ", $new_select));
        $table_model->list_change_from(implode(' ', $new_from));
    }

    /**
     * Data una query estraggo tutti i metavalues inseriti
     * @param ADFO_model $table_model
     * @return array
     */
    static private function meta_values($table_model, $sql_table) {
        if ($table_model->sql_type() != "select")   return false;
		$selected = [];
		$from_sql = $table_model->get_partial_query_from(true);
	
		foreach ($from_sql as $from) {
			// sto in una condizione
			if (stripos($from[2], 'meta_key') !== false && str_replace(["`", ' '], '', $sql_table) == str_replace(["`",' '], '',$from[0])) {
				$from2 = explode("meta_key", $from[2]) ;
				if (count($from2) == 2) {
					$from_selected = array_pop($from2);
					$from_selected_temp = explode(" AND ", str_ireplace(" OR ", " AND ", $from_selected));
					$selected[] = str_replace(["=","`",'"',"'", ' '], '', array_shift($from_selected_temp));
				}
			}
			
		}
        return $selected;
    }

}