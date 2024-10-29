<?php

/**
 * Gestisce la creazione dei parametri per una form
 * @example new ADFO_class_form()->set_sql('select ...')->get_form();
 */
namespace admin_form;

class  ADFO_class_form {
	/** @var ADFO_model $table_model */
    public $table_model;
	/** @var object $post get_post_dbp */
	public $post;
	/** @var array $add_fields_info Sono tutti i campi aggiunti nella query */
    private $add_fields_info;
	/** @var array $all_pri_ids Sono tutte le chiavi primarie delle tabelle interessate nella query */
    private $all_pri_ids;
	/** @var int $dbp_id */
    private $dbp_id;
	/** @var array $items L'elenco dei dati caricati dalla query */
	private $items;
	/** @var array $where_precompiled_temp */
	private $where_precompiled_temp;
	
	/** @var array $check_data_structure */
	private $check_data_structure;

	/**
	 * @param int|string $param dbp_id | sql
	 */
	public function __construct($param) 
	{
		if (is_numeric($param)) {
			$this->dbp_id = $param;
			$this->post  = ADFO_functions_list::get_post_dbp($this->dbp_id);
			if ($this->post != false) {
				$sql =  $this->post->post_content['sql'];
				$this->prepare_table_model($sql);
			}
		} else if (is_string($param) && stripos($param, 'select') !== false) {
			$this->prepare_table_model($param);
		}
	}


	/**
	 * Carica i dati di una form a partire dagli id.
	 *
	 * @param array $ids
	 * @return void
	 */
    public function get_data($ids) {
		if (!is_array($ids) && !is_object($ids)) {
			return [];
		}
		foreach ($ids as $column => $id) {
			$column = str_replace("`", "", $column );
			$column = "`".str_replace(".", "`.`", $column )."`";
			$filter[] = ['op' => "=", 'column' => sanitize_text_field($column), 'value' => absint($id)];
		}
		$this->table_model->list_add_where($filter);
		// Metto il limite a 50 perché c'è il limite di 1000 per i post
		$this->table_model->list_add_limit(0,50);
		$this->items = $this->table_model->get_list();
		if (count($this->items) > 0) {
			array_shift($this->items);
		}
        return $this->items;
    }

    /**
     * crea tutti i parametri per gestire una form
     *
     * @param bool $logic serve per la generazione dei form, se false invece lo si usa per il form list-form
     * @return array [$settings, $table_options]
     */
    public function get_form($logic = true) {
		$schema = $this->table_model->get_schema();
		$new_schema = $this->convert_table_to_group($schema);

		$this->where_precompiled_temp =  $this->table_model->get_default_values_from_query(); 


		$settings = $this->convert_schema_to_setting($new_schema);
		$settings = $this->add_where_precompiled_to_settings($settings);
	
		// ATTENZIONE table_options ora è un array di table_options!
		$table_options = $this->get_table_options($new_schema, $logic);

		if (!empty($this->dbp_id)) {
			$settings = $this->convert_post_content_form_to_setting($this->post->post_content, $settings);    
			$table_options = $this->convert_post_content_form_to_table_options($this->post->post_content, $table_options);
			foreach ($table_options as $table_key => &$table_option) {
				uasort($table_option, function ($a, $b) { 
					return  $a->order <=> $b->order; 
				});
				foreach ($table_option as $tbk=>$tbv) {
					$settings[$tbk]['___order___'] = $tbv->order; 
				}
			}
			// riordino i setting secondo table_option
			uasort($settings, function ($a, $b) { 
				return  $a['___order___'] <=> $b['___order___']; 
			});

			foreach ($settings as &$setting) {
				unset($setting['___order___']);
				uasort($setting, function ($a, $b) { return  $a->order <=> $b->order; });
			}
		}
		
		return [$settings, $table_options];
		//TODO questa sparisce perché il risultato di questa classe sarà array di classi
	}

	/**
	 * Preparo la query per la gestione del form inserendo tutti i campi
	 * Genero il table_model e le variabili: all_pri_ids e add_fields_info
	 *
	 * @return void
	 */
	private function prepare_table_model($sql) {
		$this->table_model = new ADFO_model();
		$this->table_model->prepare($sql);
		list($all_pri_ids, $add_fields_info) = $this->add_all_fields($this->table_model);
		$this->all_pri_ids = $all_pri_ids;
		$this->add_fields_info = $add_fields_info;
	}

	/**
	 * Aggiunge tutti i campi alla query
	 *
	 * @param ADFO_model $table_model
	 * @return array [all_pri_ids , all_fields]
	 */
	private function add_all_fields(&$table_model) {
        $current_query_select = $table_model->get_partial_query_select();
        $schema = $table_model->get_schema();
		if (!$schema) return [false,[]];
        // Preparo i dati:
        // e raggruppo i campi per tabella
        $all_pri_ids = [];
		$all_fields = [];
        $field_group = [];
        foreach ($schema as $sc) {
            if ($sc->orgtable != "") {
                if (!array_key_exists($sc->orgtable, $all_pri_ids)) {
					$all_fields[$sc->orgtable] = ADFO_fn::get_table_structure($sc->orgtable);
					 // mi segno la chiave primaria delle tabelle
                    $all_pri_ids[$sc->orgtable] = $this->get_primary_form_structure($all_fields[$sc->orgtable]);
                }
                // Raggruppo i campi per tabella (alias)
                if ($sc->table != "") {
                    if (!isset($field_group[$sc->table])) {
                        $field_group[$sc->table] = ['table'=>$sc->orgtable, 'alias_table'=>$sc->table, 'fields'=>[]];
                    }
                    $field_group[$sc->table]['fields'][] = $sc;
                }
            }
        }
        $all_pri_ids = array_filter($all_pri_ids);
  
        // verifico se c'è la chiave primaria, oppure segno che deve essere aggiunta
        $add_select_pri = [];
		$add_fields_info = [];
        foreach ( $field_group as $group) {
            if (isset($all_fields[$group['table']])) {
                //aggiungo tutti i campi che non esistono
				foreach ($all_fields[$group['table']] as $af) {
					$exist = false;
					foreach ($group['fields'] as $fields) {
						if ($fields->orgname == $af->Field) {
							$exist = true;
							break;
						}
					} 
					if (!$exist) {
						$alias = ADFO_fn::get_column_alias($group['alias_table']."_".$af->Field, $current_query_select);
						$add_select_pri[] =  '`'. $group['alias_table'].'`.`'.$af->Field.'` AS `'.$alias.'`';
						$current_query_select .= " ".$alias;
						$add_fields_info[] = ['table' => $group['alias_table'], 'orgname' => $af->Field, 'name'=> $alias];

					}
                }
            }
        }
        // aggiungo i nuovi select, ripeto la query e aggiorno table_items
        if (count($add_select_pri) > 0) {
            $table_model->list_add_select(implode(", ", $add_select_pri));
        }
		return [$all_pri_ids , $add_fields_info];
    }

	/**
	 * Estrae le chiave primarie
	 *
	 * @param array $columns
	 * @return void
	 */
	function get_primary_form_structure($columns) {
		$primary = '';
        $autoincrement = false;
        foreach ($columns as $col) {
            if ($col->Key == "PRI") {
                if ($primary == "") {
                    $primary = $col->Field;
                    if ($col->Extra == "auto_increment") {
                        $autoincrement = true;
                    }
                } else {
                    return '';
                }
            }
        }
        if ($autoincrement) {
            return $primary;
        } else {
            return '';
        }
	}

	/**
	 * I valori di values sono gestiti nello stesso formato di get_data
	 *
	 * @param array $values (array di oggetti!)
	 * @param Boolean $use_wp_fn Se usare le funzioni di wordpress 
     * wp_update_post & wp_update_user quando si aggiornano/creano utenti e post
	 * @return array
	 * ```json
	 * {"execute":"boolean", "details":"array}
	 * ```
	 */
	public function save_data($values, $use_wp_fn = true) {
		global $wpdb;
		
		list($settings, $table_options) = $this->get_form();
        $items_groups = $this->convert_items_to_groups($values, $settings, $table_options);
		$query_to_execute = [];
		foreach ($items_groups as $items) {
			foreach ($settings as $key=>$setting) {
				// trovo la tabella e la chiave primaria
				$table = "";
				$table_alias = "";
				$primary_name = "";
				$primary_value = "";
				$sql_to_save = [];
				foreach ($setting as $ss) {
					if ($ss->is_pri) {
						$table = $ss->orgtable;
						$table_alias = $ss->table;
						$primary_name = $ss->name;
					}
				}
				$pri_name = ADFO_fn::clean_string($table_alias).'.'.ADFO_fn::clean_string($primary_name);
				//print ("\n" .'array_key_exists(key, items): ' .array_key_exists($key, $items).' table: '.$table. ' primary_name: '.$primary_name ."\n" );
				// Preparo gli array di modifica dei dati
				if (array_key_exists($key, $items) && $table != "" && $primary_name != "") {
					// salvo la tabella
					foreach ($items[$key] as $val_key => $val_value) {
						if (is_array($val_value) && isset($val_value['id'])) {
							$val_value = $val_value['id'];
						}
						if (!array_key_exists($val_key, $setting) || $setting[$val_key]->name == "_dbp_alias_table_" ) {
							continue;	
						}
						if ($setting[$val_key]->name == $primary_name) {
							$primary_value = $val_value;
						} else {
							if (is_countable($val_value)) {
								$fn[$key] = maybe_serialize($val_value);
							}
							$sql_to_save[$setting[$val_key]->name] = $val_value;
							PinaCode::set_var(ADFO_fn::clean_string($table_alias).".".$setting[$val_key]->name, $val_value);
							
						}
					}

					/** PERMETTE DI RIMUOVERE UN RECORD */
					$exists  = 0;
					$can_remove = true;
					if ($primary_value != "") {
						$sql = $wpdb->prepare('SELECT * FROM `'.ADFO_fn::sanitize_key($table).'` WHERE `'.ADFO_fn::sanitize_key($primary_name).'` = %s', $primary_value);
						$exist_row = $wpdb->get_row($sql);
						$exists = is_object($exist_row);
						if ($exists == 0) {
							$sql_to_save[$primary_name] = $primary_value;
						} else {
							// permette di rimuovere un record solo se vengono passati tutti i dati come vuoti
							foreach ($exist_row as $k_row =>$v_row) {
								if (!property_exists($items[$key], $k_row)) {
									$can_remove = false;
									break;
								}
							}
						}
					}
					$remove_id_all_empty = false;
					if ($can_remove && $exists) {
						$remove_id_all_empty = true;
						foreach ($sql_to_save as $v) {
							if ($v != "" && $v != NULL) $remove_id_all_empty = false;
						}
						if ($remove_id_all_empty == true) {
							$sql_to_save = [];
						}
					}
					/** FINE PERMETTE DI RIMUOVERE UN RECORD */

					// METADATA 
					if (!$remove_id_all_empty && $table_alias != '') {
						$primary_key = ADFO_fn::get_primary_key(sanitize_text_field($table));
						if (isset($this->post->post_content['sql_metadata_table']) ) {
							$metadata_table_info = explode("::", $this->post->post_content['sql_metadata_table']);
							if (count($metadata_table_info) == 2 && $table == $metadata_table_info[1] && isset($sql_to_save['meta_value'])) {
								
								if ($sql_to_save['meta_value'] == "") { 
									// rimuovo il campo
									if ($exists == 1) {
										$query_to_execute[] = ['action'=>'delete', 'table'=>$table, 'sql_to_save'=>$sql_to_save, 'id'=> [$primary_key => $primary_value], 'table_alias'=>$table_alias, 'pri_val'=>$primary_value, 'pri_name'=>$primary_key, 'setting' => $setting];
									}
									$exists = 2;
								} else {
									// se non c'è la versione pro non posso gestire i calculated 
									// quindi parent_id e meta_key non vengono compilati correttamente!
									$metatable_info = ADFO_class_metadata::find_metadata_table_structure($metadata_table_info[1]);
									if (count($metatable_info) == 4 && isset($table_options[0][$key]->table_options)) {
										$tb = maybe_unserialize($table_options[0][$key]->table_options);
										// trovo il parent_id
										$sql_to_save[$metatable_info['parent_id']] =  "[%".$metadata_table_info[0]."]";
										// trovo il meta_key 
										if (is_array($tb)) {
											$tb = (object) $tb;
										}
										$sql_to_save['meta_key'] = $tb->value_key;
									}
								}
							}
						}
					}

					/**
					 * TODO POST TYPE
					 */
					
					if ($exists !== 2) {
						if (count($sql_to_save) == 0 && $exists && $can_remove) {
							$query_to_execute[] = ['action'=>'delete', 'table'=>$table, 'sql_to_save'=>$sql_to_save, 'id'=> [$primary_name=>$primary_value], 'table_alias'=>$table_alias, 'pri_val'=>$primary_value, 'pri_name'=>$primary_name, 'setting' => $setting];
						}
						if (count($sql_to_save) > 0) {
							if ($exists == 1) {
								$query_to_execute[] = ['action'=>'update', 'table'=>$table, 'sql_to_save'=>$sql_to_save, 'id'=> [$primary_name=>$primary_value], 'table_alias'=>$table_alias, 'pri_val'=>$primary_value, 'pri_name'=>$primary_name, 'setting' => $setting];
								
							} else {
								$query_to_execute[] = ['action'=>'insert', 'table'=>$table, 'sql_to_save'=>$sql_to_save, 'table_alias'=>$table_alias, 'pri_val'=>$primary_value, 'pri_name'=>$primary_name, 'setting' => $setting];
							}
						}
					}
					

				}
			}
		}
		$ris =  ADFO_functions_list::execute_query_savedata($query_to_execute, $this->dbp_id, 'php-form', $use_wp_fn);
		$execute = true;
		if (!is_array($ris)) $execute = false;
		foreach ($ris as $r) {
			if (!($r['result'] == true || ($r['result'] == false && $r['error'] == "" && $r['action']=="update"))) {
				$execute = false;
				break;
			}
		}
		return ['execute' => $execute, 'details' => $ris];
	}


	/**
	 * Trova una riga dei setting a partire dall'alias della tabella e dal nome(vero) del campo
	 * @param ADFO_field_param[][] $settings
	 * @param string $table_alias 
	 * @param string $field 
	 * @return ADFO_field_param
	 */
	static public function find_setting_row_from_table_field($settings, $table_alias, $field) {
		foreach ($settings as $setting) {
			foreach ($setting as $row) {
				if ($row->name == $field && $row->table == $table_alias) {
					return $row;
				}
			}
		}
		return false;
	}
	/**
	 * Trova il setting di un salvataggio a partire dal table_alias
	 * @param string $table_alias 
	 * @return ADFO_field_param
	 */
	public function find_setting_from_table_field($table_alias) {
		list($settings, $_) = $this->get_form();
		foreach ($settings as $setting) {
			foreach ($setting as $row) {
				if ($row->table == $table_alias) {
					return $setting;
				}
			}
		}
		return false;
	}

	/**
	 * Trova le options di un salvataggio a partire dal table_alias
	 * @param string $table_alias 
	 * @return \DbpDs_table_param
	 */
	public function find_option_from_table_field($table_alias) {
		list($_, $options) = $this->get_form();
		foreach ($options as $option) {
			foreach ($option as $row) {
				if ($row->table == $table_alias) {
					return $row;
				}
			}
		}
		return false;
	}

	/**
	 * Ritorna un array che dice che bottoni il form deve mostrare 
	 * @return array {'save':bool, 'delete':bool, 'trash':bool}
	 */
	public function get_btns_allow() {
		$del = true;
		$save = true;
		$trash = false;
		if (isset($this->dbp_id) && $this->dbp_id > 0) {
			$post = $this->post;
			if (isset($post->post_content["form_table"])) {
				$save = false;
				foreach ($post->post_content["form_table"] as $fk=>$fv) {
					if (!isset($fv['module_type'] ) || $fv['module_type'] == 'EDIT') {
						$save = true;
						break;
					}
				}
			}
			if (isset($post->post_content["delete_params"])) {
				$del = $post->post_content["delete_params"]->allow;
			}
			if (ADFO_functions_list::has_post_status($post)) {
				// trovo l'id dell'utente wordpress,
				$user_id = get_current_user_id();
				
				//poi il suo ruolo wordpress
				$user = get_user_by('id', $user_id);
				$roles = $user->roles;
				// verifico se il ruolo è quello di amministratore
				if (in_array('administrator', $roles)) {
					$trash = false;
					$del = true;
				} else {
					$trash = true;
					$del = false;
				}
			}
		}

		return ['save' => $save, 'delete' => $del, 'trash' => $trash];
	}

	/**
	 * Verifica i un array di inserimento e nel caso torna gli eventuali errori
	 */
	public function check_data_to_save($data) {
		$check = $this->get_check_data_structure();
		$return = ['___result___'=>true];
		foreach ($data as $key=>$value) {
			if (isset($check[$key])) {
				if ($check[$key]['required'] && $value == '') {
					$return[$key] = 'This field is required';
				} else if ($value != '') {
					if (strpos($value, "[%") !== false) {
						$return[$key]  = '';
						continue;
					}
					switch ($check[$key]['check']) {
						case 'select':
							$return[$key]  =  (isset($check[$key]['options'][$value])) ? '' : 'This value is not among the options entered';
							break;
						case 'date':
							$return[$key]  =  (preg_match("/^[0-9]{4}-(0[0-9]|1[0-2])-(0[0-9]|[0-2][0-9]|3[0-1])$/", $value)) ? '' : 'This is not a valid date';
							break;
						case 'datetime':
							$temp = explode(" ", $value);
							$return[$key]  =  (preg_match("/^[0-9]{4}-(0[0-9]|1[0-2])-(0[0-9]|[0-2][0-9]|3[0-1])$/", $temp[0])) ? '' : 'This is not a valid date ('.$temp[0].')';
							break;
						case 'decimal':
						case 'number' :
							$return[$key]  = (!is_numeric($value)) ? 'The field must be numeric' : '';
							break;		
						case 'string':
							$return[$key]  = (strlen($value) > $check[$key]['length']) ? 'Too length. Max length = '.$check[$key]['length'] : '';
							break;
						default:
							$return[$key] = '';
							break;
					}
				} else {
					$return[$key]  = '';
				}
			} else {
				$return[$key] = 'Column unknown';
			}
			if ($return[$key] != '') {
				$return['___result___'] = false;
			}
		}
		return $return;
	}

	/**
	 * Nella visualizzazione dei dati e modifica, se c'è una query che ritorna più righe di risultato 
	 * cerco di raggrupparli per tabelle
     * @param Array $table_items il risultato di model->get_list
	 * @return Array in cui ogni item è il risultato di una tabella
	 */
	private function convert_table_to_group($schemas) {
		// Divido in gruppi a seconda della tabella
		$temp_groups = [];
		if (!is_countable($schemas)) return []; 
		foreach ($schemas as $schema) {
		//	print "\n".$schema->table."\n";
			if ($schema->table != "") {
				if (!isset($temp_groups[$schema->table])) {
					$temp_groups[$schema->table] = [];
				}
				$temp_groups[$schema->table][$schema->name] = $schema;
				
			} else {
				//if (!isset($temp_groups['__orphan__'])) {
				//	$temp_groups['__orphan__'] = [];
				//}
				//$temp_groups['__orphan__'][$schema->name] =  '';
			}
		}
		$count_group = 0;
		$items = [];
		$group_unique = [];
		foreach ($temp_groups as $key=>$group) {
			$count_group++;
			$new_group = [];
			foreach($group as $k=>$g) {
				if (!isset($group_unique[$g->table."".$g->name])) {
					$group_unique[$g->table."".$g->name] = 1;
					$new_group[$k] = $g;
				}
			}
		//if ($key != '__orphan__') {
			$items["gr".$count_group] = $new_group;
		//	} else  {
				//$items[$key] = $group;
		//	}
		}
		return $items;
	}

	/**
	 * Converte i dati dallo schema a ADFO_field_param
	 *
	 * @param array $new_schema
	 * @return ADFO_field_param[]
	 * {'grxx':{'table_alias':ADFO_field_param	}}
	 */
	private function convert_schema_to_setting($new_schema) {
		$table_params = [];
		$count_form_block = 0;

		foreach ($new_schema as $key => $group) {
			$count_form_block++;
			foreach ($group as $field => $schema) {
				$edit_view = 'SHOW';
				$form_type = 'VARCHAR';
				$is_pri = 0;
				foreach ($this->add_fields_info as $afi) {

					if ($afi['table'] == $schema->table && $afi['orgname'] == $schema->orgname && $afi['name'] == $schema->name) {
						$edit_view = 'HIDE';
					} 
				}
				
				if (isset($this->all_pri_ids[$schema->orgtable]) && $this->all_pri_ids[$schema->orgtable] == $schema->orgname) {
					$form_type = 'PRI';
					$is_pri = 1;
				} else if ($form_type == "VARCHAR") {
					$form_type = ADFO_fn::h_type2txt($schema->type);
					if ($form_type == "DATE") {
						$temp_type_2 = ADFO_fn::h_type2txt($schema->type, false);
						if ($temp_type_2 == "DATETIME" || $temp_type_2 == "DATE") {
							$form_type = $temp_type_2;
						}
					} 
				}

				$table_params[$key][$field] = new ADFO_field_param([
					'name' => $schema->orgname,
					'orgtable'=> $schema->orgtable,
					'table'=> $schema->table,
					'label'=> $schema->name,
					'type' => $schema->type,
					'is_pri' => $is_pri,
					'edit_view' => $edit_view,
					'order' => $count_form_block + 1000,
					'field_name' => "edit_table[".$count_form_block."][".$schema->orgtable."][".$schema->orgname."][]",
					'form_type'=> $form_type
				]);
			}
			$table_params[$key]["_dbp_alias_table_"] = new ADFO_field_param([
                'name' =>  "_dbp_alias_table_",
                'table'=> $schema->orgtable,
                'field_name' => "edit_table[".$count_form_block."][".$schema->orgtable."][_dbp_alias_table_][]",
				'form_type'=>  "HIDDEN",
				'default_value' =>  $schema->table
			]);
		}
		
		return $table_params;
	}

	/**
	 * Table options 
	 * Ho aggiunto un livello di array perché in teoria potrebbero esserci più righe di items (mai testato!)
	 *
	 * @param array $new_schema
	 * @param bool $logic se true gestisce la creazione del form se false i parametri per list-form
	 * @return array [\dbpDs_table_param[]]
	 */
	private function get_table_options($new_schema, $logic) {
		
		if (empty($this->items)) {
			$items = [false];
		} else {
			$items = $this->items;
		}
		//TODO 	qui dovrei definire se è un edit o un add!
		//print "COUNT ITEMS: ".count ($items);
		foreach ($items as $item) {
			$table_options_temp = [];
			$count_form_block = 0;
			
			foreach ($new_schema as $key => $schema) {
				$kschema = '';
				foreach ($schema as $kschema=>$v) {
					if ($v->table != '') break;
				}
				$count_form_block++;
				$table_options_temp[$key] = new DbpDs_table_param();
				[$pri_orgname, $pri_name] = $this->get_primary_alias($schema);
				$table_options_temp[$key]->pri_name = $pri_name;
				$table_options_temp[$key]->pri_orgname = $pri_orgname;
				$table_options_temp[$key]->count_form_block = $count_form_block;
		
				$table_options_temp[$key]->table = $schema[$kschema]->table;
				$table_options_temp[$key]->orgtable = $schema[$kschema]->orgtable;
				if ($logic) {
					
					if (!empty($item) && isset($item->$pri_name) && $item->$pri_name > 0) {
						$table_options_temp[$key]->pri_value = $item->$pri_name;
						$table_options_temp[$key]->allow_create = 'HIDE';
					} else {
						$table_options_temp[$key]->allow_create = 'SHOW';
					}
					$table_options_temp[$key]->set_rand_frame_style();
					if ($count_form_block == 1) {
						$table_options_temp[$key]->allow_create = 'HIDE';
						$table_options_temp[$key]->frame_style = 'WHITE';
					} else if (!empty($item)) {
						if ($pri_name != "") {
							// il campo non è stato ancora inserito
							$table_options_temp[$key]->table_compiled = "edit_table[".$count_form_block."][".$schema[$kschema]->orgtable."][_dbp_leave_empty_][]";
						} 
						
					} 
				} else {
					$table_options_temp[$key]->allow_create = 'SHOW';
					if ($count_form_block == 1) {
						$table_options_temp[$key]->frame_style = 'WHITE';
					} else {
						$table_options_temp[$key]->set_rand_frame_style();
					}
				}
				$option = ADFO_fn::get_dbp_option_table($schema[$kschema]->orgtable);
				$table_options_temp[$key]->table_status = $option['status'];
				$table_options_temp[$key]->order = -1;
			}
			$table_options[] = $table_options_temp;
		}
		return $table_options;
	}

	/**
	 * Trova la chiave primaria della query (quindi con il nome della colonna della query) 
	 * @param array $new_schema
	 * @return array [pri_orgname, pri_name]
	 */
	private function get_primary_alias($new_schema) {
		reset ($new_schema);
		$key_schema = key($new_schema);
		$table = $new_schema[$key_schema]->table;
		$pri = "";
		if ($table == "") return '';
		if (isset($this->all_pri_ids[$new_schema[$key_schema]->orgtable])) {
			$pri = $this->all_pri_ids[$new_schema[$key_schema]->orgtable];
		}
		if ($pri != "") {
			foreach ($new_schema as $k=>$schema) {
				if ($schema->orgname == $pri) {
					return [$pri, $schema->name];
				}
			}
		}
		return '';
	}

	/**
	 * Se è una lista carico i dati dalla lista!
	 *
	 * @param [array] $post_content
	 * @param dbpDs_table_param[] $settings
	 * @return dbpDs_table_param[][]
	 */
	private function convert_post_content_form_to_table_options($post_content, $table_options) {
		if (array_key_exists('form_table', $post_content)) {
			foreach ($table_options as &$item_row) {
				foreach ($item_row as &$group) {
					$this->convert_post_content_form_to_table_options_single($post_content, $group);
				}
			}
			
		}
		return $table_options;
	}

	private function convert_post_content_form_to_table_options_single($post_content, &$group) {
		$form_field = false;
		$form_field2 = [];
		foreach ($post_content['form_table'] as $table => $form_field2) {	
			if ($table == $group->table) {
				$form_field = $form_field2;
				break;
			}
		}

		if (!$form_field) return false;
		$options = [];
		if (isset($form_field["table_options"])) {
			$group->table_options = $form_field["table_options"];
			if ($group->table_options != '') {
				$options = maybe_unserialize($group->table_options);
			}
		} 
		
		if (isset($post_content['sql_metadata_table']) && count($options) == 0) {
			$info_metadata = explode("::", $post_content['sql_metadata_table']);
			if (count($info_metadata) == 2 && $group->orgtable == $info_metadata[1]) {
				$metatable_info = ADFO_class_metadata::find_metadata_table_structure($info_metadata[1]);
				if (count($metatable_info) == 4) {
					// TODO value_key MANCA lo inserisco in fase di visualizzazione della form
					$option = ['type'=>'METADATA','orgtable'=>$group->orgtable,'table'=>$group->table, 'field_show'=>$metatable_info['meta_value'],'field_key'=>$metatable_info['meta_key'], 'value_key'=>'','field_conn_id'=>$metatable_info['parent_id'], 'value_conn_id' => $info_metadata[0]];
					$group->table_options = maybe_serialize($option);
				}
			}
			// LO RICOSTRUISCO
			// a:8:{s:4:"type";s:8:"METADATA";s:8:"orgtable";s:11:"wp_postmeta";s:5:"table";s:6:"poswpd";s:10:"field_show";s:10:"meta_value";s:9:"field_key";s:8:"meta_key";s:9:"value_key";s:7:"ts_name";s:13:"field_conn_id";s:7:"post_id";s:13:"value_conn_id";s:14:"wp_posts.ID";}
			//wp_posts.ID::wp_postmeta
		}
		if (isset($options['type']) && $options['type'] == "METADATA") {
			$group->show_title = 'HIDE';
			$group->allow_create = 'HIDE';
			$group->title = '';
			$group->frame_style = 'HIDDEN';
			$group->description = '';
			$group->module_type = 'EDIT';
		} else {

			$group->allow_create = isset($form_field['allow_create']) ? $form_field["allow_create"] : 'SHOW';
			$group->show_title = isset($form_field["show_title"]) ? $form_field["show_title"] : 'SHOW';
			if ($group->show_title == 'HIDE' || !isset($form_field["title"])) {
				$group->title = '';
			} else {
				$group->title = $form_field["title"];
			}
			$group->frame_style = $form_field["frame_style"];
			$group->description = isset($form_field["description"]) ? $form_field["description"] : '';
			$group->module_type = isset($form_field["module_type"]) ? $form_field["module_type"] : 'EDIT';
		}
		
		if (isset($form_field["order"])) {
			$group->order = $form_field["order"];
		} else {
			$group->order = 0;
		}

		$primary_key = ADFO_fn::get_primary_key($group->orgtable);
		foreach ($this->where_precompiled_temp as $wpt) {
			if ($group->table == $wpt[0] && $primary_key == $wpt[1]) {
				$group->precompiled_primary_id = $wpt;
			}
		}
	}

	/**
	 * Aggiunge i campi precompilati dalla query 
	 *
	 * @param ADFO_field_param[] $settings
	 * @return ADFO_field_param[]
	 */
	private function add_where_precompiled_to_settings($settings) {
		
		foreach ($settings as $group) {
			foreach ($group as $field) {
				if ($field->orgtable != "") {
					$field->js_rif = str_replace(" ","_", $field->table.".".$field->name);
					$primary_key = ADFO_fn::get_primary_key($field->orgtable);
					// campi calcolati dalla query
					foreach ($this->where_precompiled_temp as $wpt) {
						//print "<h2>".$field->table." == ".$wpt[0]."</h2>";
						if ($field->table == $wpt[0] && $field->name == $wpt[1] && $primary_key != $field->name) {
							if (empty($this->dbp_id)) {
								$field->form_type = "VARCHAR";
								$field->default_value = $wpt[2];
							} else {
								$field->form_type = "CALCULATED_FIELD";
								$field->custom_value = $wpt[2];
							}
							$field->where_precompiled = 1;
						}
					}	
				}
			}
		}
		return $settings;
		
	}
	/**
	 * Se è una lista carico i dati dalla lista!
	 *
	 * @param [type] $post_content
	 * @param ADFO_field_param[] $settings
	 * @return ADFO_field_param[][]
	 */
	private function convert_post_content_form_to_setting($post_content, $settings) {
		
		if (array_key_exists('form', $post_content)) {
			foreach ($settings as $group) {
				foreach ($group as $field) {
					$this->convert_post_content_form_to_setting_single($post_content, $field);
				}
			}
		}
		return $settings;
	}

	/**
	 * Funzione di servizio per la conversione dei setting dal form imposta i singoli campi con i parametri salvati in una lista
	 *
	 * @param [type] $post_content
	 * @param ADFO_field_param $field
	 * @return void
	 */
	private function convert_post_content_form_to_setting_single($post_content, &$field) {
		global $wpdb;
		$form_field = false;
		foreach ($post_content['form'] as $form_field2) {
			//print ("<p>" . $form_field2['table']." == ".$field->table." && ".$form_field2['name']." == ".$field->name  . "</p>");
			if ($form_field2['table'] == $field->table && $form_field2['name'] == $field->name  ) {
				$form_field = $form_field2;
				break;
			}
		}

		if (!$form_field) return false;
	
		
		//print ("<h4>trovato!!</h4>");
		if (isset($form_field['order'])) {
			$field->order = $form_field['order'];
		} else {
			$field->order = '';
		}
		if (isset($form_field['label']) && $form_field['label'] != "") {
			$field->label = $form_field['label'];
		} else {
			$field->label = $form_field['name'];
		}
		if (isset($form_field['note'])) {
			$field->note = $form_field['note'];
		}
		if (isset($form_field['options'])) {
			$field->options = ADFO_fn::parse_csv_options($form_field['options']);
		}

		if (isset($form_field['default_value'])) {
			$field->default_value =  $form_field['default_value'];
		}
		if (isset($form_field['required'])) {
			$field->required = $form_field['required'];
		}
		if (isset($form_field['autocomplete'])) {
			$field->autocomplete = $form_field['autocomplete'];
			// TODO devo generare una query per l'autocomplete con eventuali filtri trovati dalla query principale. Ad esempio meta_key="nome_utente" quindi devo cercare nell'autocomplete solo i nomi dei campi utente
			//var_dump ($post_content['sql']);
			//var_dump ($form_field);
			// ["table"]=> string(3) "m01"
			$sql_model = new ADFO_model();
			$sql_model->prepare($post_content['sql']);
			$from = $sql_model->get_partial_query_from(true);
			$create_query_where = [];
			$all_tables = [];
			foreach ($from as $f) {
				if ($f[1] != $form_field['table']) {
					$all_tables[] = $f[1];
				}
			}
			
			foreach ($from as $f) {
				if ($f[1] == $form_field['table']) {
					$f[2] = str_replace(" and ", " AND ", $f[2]);
					$list_of_where = explode(" AND ", $f[2]);
					foreach ($list_of_where as $low) {
						if (stripos($low, $f[1]) !== false) {
							// se non ci sono collegamenti ad altre tabelle
							$add_where = true;
							foreach ($all_tables as $at) {
								if (stripos($low, $at) !== false) {
									$add_where = false;
									break;
								}
							}
							if ($add_where) {
								$create_query_where[] = $low;
							}
						}
					}
				}
			}
			//var_dump($from);
			if (count($create_query_where) > 0) {
				$field->ac_sql = "SELECT ".$form_field['name']." FROM ".$form_field['orgtable']." ".$form_field['table']." WHERE ". implode(" AND ", $create_query_where);
			}
		}
		if (isset($form_field['custom_css_class'])) {
			$field->custom_css_class = $form_field['custom_css_class'];
		}
		if (isset($form_field['custom_value'])) {
			$field->custom_value = $form_field['custom_value'];
		}
		if (isset($form_field['form_type'])) {
			$field->form_type = $form_field['form_type'];
			if ( $field->form_type == "CREATION_DATE") {
				if ($field->type == "date") {
					$field->default_value = (new \DateTime('now',  wp_timezone()))->format('Y-m-d');
					$field->custom_value = (new \DateTime('now',  wp_timezone()))->format(get_option('date_format'));
				} else {
					$field->default_value = (new \DateTime('now',  wp_timezone()))->format('Y-m-d H:i:s');
					$field->custom_value = (new \DateTime('now',  wp_timezone()))->format(get_option('date_format')." ".get_option('time_format'));
				}
			}
			if ( $field->form_type == "LAST_UPDATE_DATE") {
				if ($field->type == "date") {
					$field->default_value = (new \DateTime('now',  wp_timezone()))->format('Y-m-d');
					$field->custom_value = (new \DateTime('now',  wp_timezone()))->format(get_option('date_format'));
				} else {
					$field->default_value = (new \DateTime('now',  wp_timezone()))->format('Y-m-d H:i:s');
					$field->custom_value = (new \DateTime('now',  wp_timezone()))->format(get_option('date_format')." ".get_option('time_format'));
				}
			}
			if ( $field->form_type == "RECORD_OWNER" || $field->form_type == "MODIFYING_USER" ) {
				$field->default_value = get_current_user_id();
				if ($field->default_value == 0) {
					$field->custom_value = __('Guest');
				} else {
					$user = wp_get_current_user();
					$field->custom_value = $user->user_login;
				}
			}
			if ( $field->form_type == "ORDER" ) {
				$sql = 'SELECT `'.$field->name.'` FROM `'.$field->orgtable.'` ORDER BY `'.$field->name.'` DESC LIMIT 1;';
				$last_val = $wpdb->get_var($sql);
				$last_val = absint($last_val) + 1;
				$field->default_value = $last_val;
			}
			if ( $field->form_type == "POST_STATUS" ) {
				$user_role = ADFO_functions_list::get_user_role($this->post);
				$buttons = ['publish' => _('Publish'), 'draft' => _('Draft')];
				// 'pending' => 'pending', 'private' => 'private'];
				if ($user_role == 'administrator') {
					if (ADFO_functions_list::has_post_author($this->post)) {
						$buttons = ['publish' => _('Publish'), 'pending' => _('Pending'), 'draft' => _('Draft'),  'inherit' => _('Inherit'), 'trash' => _('Trash')];
					} else {
						$buttons = ['publish' => _('Publish'), 'draft' => _('Draft'), 'trash' => _('Trash')];
					}
				} else if ($user_role == 'contributor' && ADFO_functions_list::has_post_author($this->post)) {
					$buttons = ['pending' => _('Ask for review'), 'draft' => _('Draft')];
				}
				$field->options = $buttons;
			}
			if ( $field->form_type == "CALCULATED_FIELD") {
				if (isset($form_field['where_precompiled'])) {
					$field->where_precompiled =  $form_field['where_precompiled'];
				} else {
					$field->where_precompiled = 0;
				}
				$field->custom_value_calc_when =  $form_field['custom_value_calc_when'];
			} elseif (isset($field->where_precompiled)) {
				$field->where_precompiled = 0;
			}
		
			if ( $field->form_type == "POST") {
				$field->post_types = $form_field['post_types'];
				$field->post_cats = (isset($form_field['post_cats'])) ? $form_field['post_cats'] : [];
				$field->is_multiple = isset($form_field['is_multiple']) ? $form_field['is_multiple'] : 0;
			}
			if ( $field->form_type == "USER" ) {
				$field->user_roles = isset($form_field['user_roles']) ? $form_field['user_roles'] : [];
				$field->is_multiple = isset($form_field['is_multiple']) ? $form_field['is_multiple'] : 0;
			} 
			if ( $field->form_type == "LOOKUP" && isset($form_field['lookup_id'])) {
				$field->lookup_id  = $form_field['lookup_id'];
				$field->lookup_sel_val = isset($form_field['lookup_sel_val']) ? $form_field['lookup_sel_val'] : '';
				$field->lookup_sel_txt = isset($form_field['lookup_sel_txt']) ? $form_field['lookup_sel_txt'] : '';
				$field->lookup_where = isset($form_field['lookup_where']) ? $form_field['lookup_where'] : '';
				$field->is_multiple = isset($form_field['is_multiple']) ? $form_field['is_multiple'] : 0;
			}
			if ( $field->form_type == "RANGE") {
				$field->range_min  = isset($form_field['range_min']) ? $form_field['range_min'] : 0;
				$field->range_max = isset($form_field['range_max']) ? $form_field['range_max'] : 100;
				$field->range_step = isset($form_field['range_step']) ? $form_field['range_step'] : 1;
			}
			if ($field->form_type == "MEDIA_GALLERY") {
				$field->is_multiple = isset($form_field['is_multiple']) ? $form_field['is_multiple'] : 0;
			}
			
		}
		if (isset($form_field['edit_view'])) {
			$field->edit_view = isset ($form_field['edit_view']) ? $form_field['edit_view'] : '';
		}
		if (isset($form_field['js_script'])) {
			$field->js_script = isset($form_field['js_script']) ? $form_field['js_script'] : '';
		}
	}

	/**
	 * Prepara un array per validare i dati.
	 * @return Array {'nome_campo':{'check':'tipo_check'}, }
	 */
	private function get_check_data_structure() {
		if (is_array($this->check_data_structure) && count($this->check_data_structure) > 0)  {
			return $this->check_data_structure;
		}
		$curr_schema = $this->table_model->get_schema();
		$tables_structures = [];
		$fields = [];
		foreach ($curr_schema as $schema_field) {
			$new_name = $schema_field->name;
			$fields[$new_name] = ['check' => '', 'required' => 0];	
			if (!isset($tables_structures[$schema_field->orgtable])) {
				$tables_structures[$schema_field->orgtable] = ADFO_fn::get_table_structure($schema_field->orgtable);
			} 
			
			foreach ($tables_structures[$schema_field->orgtable] as $field_name => $field_config) {
				//print ($new_name.' ('.$field_name . ') ' . $field_config->Field . ' = ' . $schema_field->orgname . "\n");
				if (isset($schema_field->orgname) && $field_config->Field == $schema_field->orgname) {
				//	print "OK! \n";
					if (substr($field_config->Type,0,3) == 'int' || substr($field_config->Type,0,6) == 'bigint') {
						$fields[$new_name]['check'] = 'number';
					} elseif (substr( $field_config->Type,0,7) == 'decimal') {
						$fields[$new_name]['check'] = 'decimal';
					} elseif (substr($field_config->Type,0,4) == 'text' || $field_config->Type == 'longtext') {
						$fields[$new_name]['check'] = 'text';
					} elseif (substr($field_config->Type,0,8) == 'datetime') {
						$fields[$new_name]['check'] = 'datetime';
					} elseif (substr($field_config->Type,0,4) == 'date') {
						$fields[$new_name]['check'] = 'date';
					}  elseif (substr($field_config->Type,0,7) == 'varchar') {
						$fields[$new_name]['check'] = 'string';
						$fields[$new_name]['length'] = str_replace(['varchar(',')'],'', $field_config->Type);
					} else {
						$fields[$new_name]['check'] = '';	
					}
					foreach ( $this->post->post_content['form'] as $form) {
						if ($form['name'] == $field_config->Field && $schema_field->table == $form['table']) {
							//var_dump ($form);
							if (isset($form['required']) && $form['required'] == '1') {
								$fields[$new_name]['required'] = 1;
							}
							if ($form['form_type'] == "PRI") {
								$fields[$new_name]['check'] = 'number';
							}
							if (in_array($form['form_type'], ['SELECT', 'RADIO'])) {
								$fields[$new_name]['check'] = 'select';
								$fields[$new_name]['options'] = ADFO_fn::parse_csv_options($form['options']);
							}
							/*
							if ($form['form_type'] == "CALCULATED_FIELD") {
								$fields[$new_name]['check'] = 'CALCULATED_FIELD';
								$fields[$new_name]['options'] = $form['custom_value'];
							}
							*/
						} 
						
					}
					break;
				}
			} 
		}
		$this->check_data_structure = $fields;
		return $fields;

	}


	/**
	 * Converte un array di array di strutture di dati (ADFO_data_structures) in un array di array di array
	 *
	 * @param array $structures
	 * @return array
	 */
	static public function data_structures_to_array($structures) {
		foreach ($structures as &$to) {
			foreach ($to as &$t) {	
				$t = $t->get_array();
			}
		}
		return $structures;	
	}

	/**
	 * Converte la struttura degli items estratti da get_data nella struttura dei setting
	 *
	 * @param array $items
	 * @param [type] $settings
	 * @todo BUG return array di Oggetti Al momento se la tab form non è mai stata salvata ritorna un array di array!!!!
	 * @return array 
	 */
	static public function convert_items_to_groups($items, $settings, $table_options) {
		$new_items = [];
		$temp_already_added = [];
		$temp_old_items = [];
		if (!is_countable($items) || count($items) == 0) {
			foreach ($settings as $ks => $st) {
				$temp_old_items[$ks] = new \stdClass;
				foreach ($st as $tk => $_) {
					$temp_old_items[$ks]->$tk = '';
				}
			}
			$items = [$temp_old_items];
			return $items;
 		} 
		
		foreach ($items as $item_key => $item) {
		
			$temp_item = [];
			foreach ($settings as $key=>$setting) {
				$added = false;
				foreach ($temp_already_added as $oa) { 
					if ($table_options[$item_key][$key]->table == $oa->table && $table_options[$item_key][$key]->pri_value ==  $oa->pri_value) {
						$added = true;
					}
				}
				if (!$added) {
					if (isset($table_options[$item_key][$key]->pri_value)) {
						$temp_already_added[] = (object)['table' => $table_options[$item_key][$key]->table, 'pri_value' => $table_options[$item_key][$key]->pri_value];
					}
					$temp_item[$key] = (object)[];
					foreach ($setting as $field=>&$setting_field) {
						if (property_exists($item, $field)) {
							if ($item->$field == null) {
								$temp_item[$key]->$field = '';
							} else {
								if ($setting_field->form_type == "DATETIME" || $setting_field->form_type == "DATE" ) {
									try {
										$temp = new \DateTime($item->$field, wp_timezone());
									} catch (\Exception $ex) {
										if ($setting_field->form_type == "DATETIME") {
											$item->$field = '0000-00-00 00:00:00';
										} else {
											$item->$field = '0000-00-00';
										}
									}
									if (is_a($temp, 'DateTime')) {
										if ($setting_field->form_type == "DATETIME") {
											$item->$field = $temp->format('Y-m-d\TH:i:s');
										} else {
											$item->$field = $temp->format('Y-m-d');
										}
									}
								}
								// ATTENZIONE: Se è un array deve avere l'id uguale al valore originale sempre!!!! altrimenti l'import pro sbaglia!
								if ($setting_field->form_type == "USER") {
									
									if (is_numeric($item->$field) && $item->$field > 0) {
										$user = get_user_by('ID', $item->$field);
										if ($user) {
											$item->$field = ['id'=>$item->$field, 'label'=> $user->user_login];
										}
									} else if (is_string($item->$field) && $item->$field != "" && $item->$field != "[]" && $item->$field != "0") {
										$json = json_decode(wp_unslash($item->$field), true);
										if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
											
											$item->$field = ['id'=>[], 'label'=>[]];
											foreach ($json as $v) {
												$user = get_user_by('ID', $v);
												if ($user) {
													$item->$field['id'][] = $v;
													$item->$field['label'][] = $user->user_login;
												}
											}
										}
									}
								}
								if ($setting_field->form_type == "POST") {
									if (is_numeric($item->$field) && $item->$field > 0) {
										$post = get_post($item->$field);
										if ($post) {
											$item->$field = ['id'=>$item->$field, 'label'=>$post->post_title];
										}
									} else if (is_string($item->$field) && $item->$field != "" && $item->$field != "[]" && $item->$field != "0") {
										$json = json_decode(wp_unslash($item->$field), true);
										if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
											$item->$field = ['id'=>[], 'label'=>[]];
											foreach ($json as $v) {
												$post = get_post($v);
												if ($post) {
													$item->$field['id'][] = $v;
													$item->$field['label'][] = $post->post_title;
												}
											}
										}
									}
								}
								// inserisco il valore già presente nel lookup
								if ($setting_field->form_type == "LOOKUP") {
									if (is_numeric($item->$field) && $item->$field > 0) {
										$table_model = new ADFO_model($setting_field->lookup_id);
										$table_model->list_add_where([['op'=>'=','column'=>$setting_field->lookup_sel_val, 'value'=>$item->$field]]);
										$ris = $table_model->get_list();
										if (is_array($ris) && count($ris) > 0) {
											$ris = array_pop($ris);
											$label = $setting_field->lookup_sel_txt;
											$item->$field = ['id'=>$item->$field, 'label'=>$ris->$label];
										}
									} else if (is_string($item->$field) && $item->$field != "" && $item->$field != "[]" && $item->$field != "0") {
										$json = json_decode(wp_unslash($item->$field), true);
										if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
											$item->$field = ['id'=>[], 'label'=>[]];
											foreach ($json as $v) {
												$table_model = new ADFO_model($setting_field->lookup_id);
												$table_model->list_add_where([['op'=>'=','column'=>$setting_field->lookup_sel_val, 'value'=>$v]]);
												$ris = $table_model->get_list();
												if (is_array($ris) && count($ris) > 0) {
													$ris = array_pop($ris);
													$label = $setting_field->lookup_sel_txt;
													$item->$field['id'][] = $v;
													$item->$field['label'][] = $ris->$label;
												}
											}
										}
									}
								}
								if ($setting_field->form_type == "MEDIA_GALLERY") {
									if (is_numeric($item->$field) && $item->$field > 0) {
										$attachment = get_post($item->$field);
										$url = wp_get_attachment_image_src($item->$field, 'thumbnail', true);
										$item->$field = ['id'=>$item->$field, 'url'=> $url[0], 'link'=> wp_get_attachment_url($item->$field), 'title'=> $attachment->post_title];
									} else if (is_string($item->$field) && $item->$field != "" && $item->$field != "[]" && $item->$field != "0") {
										// TODO campi multipli per i media gallery
										$json = json_decode(wp_unslash($item->$field), true);
										if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
											$item->$field = ['id'=>[], 'url'=>[], 'link'=> [], 'title'=>[]];
											foreach ($json as $v) {
												$attachment = get_post($v);
												$url = wp_get_attachment_image_src($v, 'thumbnail', true);
												$item->$field['id'][] = $v;
												$item->$field['url'][] = $url[0];
												$item->$field['link'][] = wp_get_attachment_url($v);
												$item->$field['title'][] = $attachment->post_title;
											}
										}
									}
								}

								if ($setting_field->form_type == "RECORD_OWNER") {
									if ($item->$field > 0) {
										$setting_field->custom_value = get_user_by('ID', $item->$field)->user_login;
									}
								}
								$temp_item[$key]->$field = $item->$field;
							}
						}
					}
				}
			}
			$new_items[] = $temp_item;	
		}
		return $new_items;
	}

}