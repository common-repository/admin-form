<?php 
/**
 * Tutte le funzioni che servono per gestire le liste
 * 
 */
namespace admin_form;

class  ADFO_functions_list {
    /**
     * Elabora le impostazioni di una lista (la prima riga) e ne ritorna l'array della configurazione 
     * Sostituendolo allo schema 
     * @param array $model_items i risultati di una query get_list() $table_model->items;
     * @param ADFO_list_setting[] $list_setting 
     * @return ADFO_list_setting[]
     */
    static function get_list_structure_config($model_items, $list_setting) {
        
        if (!is_array($model_items) || count($model_items) == 0) return false;

        //$columns = array_column($list_setting, 'order');
        //array_multisort($columns, SORT_ASC, $list_setting);
      
        uasort($list_setting, function($a, $b) { 
            if (!is_a($a, 'admin_form\ADFO_list_setting')|| !is_a($b, 'admin_form\ADFO_list_setting') ) return 0;
            return $a->order <=> $b->order;
        });
        // Imposta rispetto alla list_setting l'elenco delle colonne
        $temp_items_2 = array_shift($model_items);   
        if (!is_array($temp_items_2) || count($temp_items_2) == 0) return false;
        $count = 799;
        /**
         * @var ADFO_list_setting[] $temp_items
         */
        $temp_items = [];
        ADFO_functions_list::private_add_name_request($temp_items_2);
        if (is_array($temp_items_2)) {
            foreach ($temp_items_2 as $key=>$item) {
                $temp_list_structue = new ADFO_list_setting();
                foreach ($item['schema'] as $ki =>$vi) {
                    $temp_list_structue->$ki = $vi;
                }
                $count++;
                $temp_list_structue->type = ADFO_fn::h_type2txt($item['schema']->type); 
                $temp_list_structue->title = $item['schema']->name;
                $temp_list_structue->toggle = 'SHOW';
                $temp_list_structue->view = ADFO_fn::h_type2txt($item['schema']->type);
                $temp_list_structue->custom_code = '';
                $temp_list_structue->order = $count;
                $temp_list_structue->origin = 'FIELD';
                if ($temp_list_structue->view ==' NUMERIC') {
                    $temp_list_structue->searchable = '=';
                } else {
                    $temp_list_structue->searchable = 'LIKE';
                }
                
                $temp_list_structue->mysql_name = ADFO_functions_list::private_get_mysql_name($item['schema']);
                if (isset($item['schema']->orgtable)) {
                    $temp_list_structue->mysql_table = $item['schema']->orgtable;
                }
                if ($temp_list_structue->mysql_name == "") {
                    $temp_list_structue->searchable = 'no';
                }
                $temp_list_structue->custom_param = '';
                $temp_list_structue->format_values = '';
                $temp_list_structue->format_styles = '';
                $temp_list_structue->inherited = 0;
                $temp_items[$key] = $temp_list_structue;
            }
        }
        
        /**
         * @var ADFO_list_setting[] $temp_items
         */
        $items = [];
        foreach ($list_setting as $key => $column_setting) {
            if (is_array($column_setting)) {
                $column_setting = (new ADFO_list_setting())->set_from_array($column_setting);
            }
            if (array_key_exists($key, $temp_items)) {
                $items[$key] = $temp_items[$key];
                unset($temp_items[$key]);
            } else if ($column_setting->type == "CUSTOM") {
                $items[$key] = (new ADFO_list_setting())->set_from_array(['name'=>$key,'type'=>'CUSTOM', 'origin'=>'CUSTOM', 'mysql_name'=>'', 'mysql_table'=>'', 'name_request'=>'']);
                $column_setting->view = 'CUSTOM';
            } else {
                continue;
            }
            
            if (is_a($column_setting, 'admin_form\ADFO_list_setting')) {
                $column_setting_array = $column_setting->get_array();
            } 
           
            foreach ($column_setting_array as $ks=>$vs) {
                if ($vs != "" && !in_array($ks, ['type','name','orgname','table','orgtable','mysql_name','mysql_table'])) {
                    $items[$key]->$ks = $vs;
                }
            }   
        }
        return array_merge($items, $temp_items);
    }
    

    /**
     * Trova il post e converte il content con i default
     * [list_setting] la struttura dei singoli campi della lista sia frontend che backend
     * [list_general_setting] le impostazioni della lista (sia frontend che backend)
     * [frontend_view]
     * @param int $post_id
     * @return object;
     */
    static function get_post_dbp($post_id) {
        if (absint($post_id) == 0) return false;
        $post = get_post(absint($post_id));
        if (!$post || $post->post_status != 'publish') return false;
        if (is_object($post)) {
            $post->post_content = get_post_meta($post_id, '_dbp_list_config', true);
            if (!$post) return false;
            if ($post->post_type != 'dbp_list') return false;
            $post->post_content = self::convert_post_content_to_list_params($post->post_content);
            return $post;
        } else {
            return false;
        }
    }

    /**
     * Tutte le volte che c'è get_post_dbp poi dovrei chiamare questa funzione
     */
    static function no_post_dbp($post) {
        if (!is_object($post) || $post == false || $post == null) {
            die(__('The list does not exist', 'admin_form'));
        }
    }

    /**
     * Converte il post_content nella struttura della lista
     *
     * @param string $post_content
     * @return array
     */
    static function convert_post_content_to_list_params($post_content = '') {
        global $wpdb;
        ADFO_fn::require_init();
        $content = maybe_unserialize($post_content);
        if (is_object($content)) {
            $content = (array)$content;
        }
        if (!is_array($content)) {
            $content = [];
        } 
     
        if (!array_key_exists('list_setting', $content)) {
            $content['list_setting'] = [];
        } else {
            $new_list_setting = [];
            if (is_array($content['list_setting'])) {
                foreach ($content['list_setting'] as $key=>$list ) {
                    $new_list_setting[$key] = (new ADFO_list_setting())->set_from_array($list);
                }
            }
            $content['list_setting'] = $new_list_setting;
        }

        if (!array_key_exists('frontend_view', $content)) {
            $content['frontend_view'] = [];
        }
        if (!array_key_exists('list_general_setting', $content)) {
            $content['list_general_setting'] = ['text_length' => 80, 'obj_depth'=>3];
        }
        if (!array_key_exists('form', $content)) {
            $content['form'] = [];
        } 
        $content['sql_from'] = (isset($content['sql_from'])) ? $content['sql_from'] : [];
        if (array_key_exists('sql', $content)) {
            $content['sql'] = wp_unslash($content['sql']);
        } else if (isset($content['sql_from'])) {
            $table_model = new ADFO_model($content['sql_from']);
            $content['sql'] = $table_model->get_current_query();
        } else {
            $content['sql'] = '';
        }

        if (!array_key_exists('primaries', $content) || !is_array($content['primaries'])) {
            $content['primaries'] = [];
        }
        
        if (!array_key_exists('schema', $content) || !is_array($content['schema'])) {
            $content['schema'] = [];
        }

        $content['post_status'] = isset($content['post_status']) ? $content['post_status'] : [];
        $content['post_status']['permission'] = isset($content['post_status']['permission']) ? $content['post_status']['permission'] : ['editor'=>'editor', 'author'=>'author', 'contributor'=>'contributor', 'subscriber'=>'subscriber', 'guest'=>'guest'];


        $content['show_desc'] = isset($content['show_desc']) ? $content['show_desc'] : 0;

        if (array_key_exists('delete_params', $content)) {
            if (!is_a($content['delete_params'], 'admin_form\dbpDs_list_delete_params') ) {
                $content['delete_params'] = new DbpDs_list_delete_params($content['delete_params']);
            }
        } else {
            $content['delete_params'] =  new DbpDs_list_delete_params();
        }
        /**
         * POST TYPE
         * @since 1.7.0 Aggiunti i tipi di form.
         * type = choose_table_from_db|create_new_post_type|create_new_table|''
         * todo pro aggiungere from_query
         * post_type = [name=>'','slug'=>'']
         */
        if (!in_array($wpdb->posts, $content['sql_from'])) {
            $content['post_type'] = ['name'=>'','slug'=>''];
        } else {
            if (isset($content['post_type']) && !is_array($content['post_type'])) {
                $content['post_type'] = [];
            }
            $content['post_type']['name'] = (isset($content['post_type']['name'])) ? $content['post_type']['name'] : '';
            $content['post_type']['slug'] = (isset($content['post_type']['slug'])) ? $content['post_type']['slug'] : $content['post_type']['name'];
        }
        $content['type'] = (isset($content['type'])) ? $content['type'] : '';
        
        /**
         * @sinse 2.0 
         * Aggiungo la proprietà is_multiple se è una lista e il campo è multiplo
         */
        foreach ($content['list_setting'] as $k=>$sett) {
            if (is_array($sett)) {
                $sett = (object)$sett;
            }
    
            foreach ($content['form'] as $post_form) {
                if ($sett->name == $post_form['name'] && $sett->table == $post_form['table'] && isset($post_form['is_multiple']) && $post_form['is_multiple'] == 1) {
                        $content['list_setting'][$k]->is_multiple = 1; 
                }
            }
        }
        return $content;
    }

    /**
     * torna tutte le colonne di tutte le tabelle interessate in una query
     * @param array $item La prima riga dei risultati di una query di ADFO_model NON Convertiti in update_items_with_setting
     */
    static function get_all_fields_from_query($item) {
        global $wpdb;
        $tables =  $fields = [];
        foreach ($item as $e) {
            if (!array_key_exists($e['schema']->orgtable, $tables)) {
                $tables[$e['schema']->orgtable] = $wpdb->get_results('SHOW COLUMNS FROM `'.ADFO_fn::sanitize_key($e["schema"]->orgtable).'`');
            }
            if (!array_key_exists($e['schema']->table, $fields)) {
                $temp_list =[];
                foreach ($tables[$e['schema']->orgtable] as $tso) {
                    $temp_list[] = '`' . $e['schema']->table . '`.`' . $tso->Field . '`';
                }
                $fields[$e['schema']->table] = $temp_list;
            }
        }

    }

    /**
     * Inserisce nella query di table_model il limit, l'order ed eventuali filtri 
     * @param array $post_content
     * @return ADFO_model
     */
    public static function get_model_from_list_params($post_content) {
        $sql =  $post_content['sql'];
        if ( $sql != "") {
            $table_model = new ADFO_model();
            $table_model->prepare($sql);
            if ($table_model->sql_type() == "select") {
                if (isset($post_content['sql_limit'])) {
                    $table_model->limit = (int)$post_content['sql_limit'];
                }
                if (isset($post_content['sql_order'])) {
                    if (isset($post_content['sql_order']['field']) && isset($post_content['sql_order']['sort'])) {
                    $table_model->list_add_order($post_content['sql_order']['field'], $post_content['sql_order']['sort']);
                    }
                }
             
                // aggiungo eventuali dbp_extra_attr 
                if (isset($_REQUEST['dbp_extra_attr'])) {
                    $dbp_extra_attr = wp_kses_post(wp_unslash($_REQUEST['dbp_extra_attr']));
                    $extra_attr = json_decode(base64_decode( $dbp_extra_attr ), true);
                     if (json_last_error() == JSON_ERROR_NONE) {
                         if (isset($extra_attr['request'])) {
                             foreach ($extra_attr['request'] as $key=>$val) {
                                $_REQUEST[$key] = $val;
                             }
                             pinacode::set_var('request', $extra_attr['request']);
                         }
                         if (isset($extra_attr['params'])) {
                            $params = (array)PinaCode::get_var('params');
				            $params = array_merge($extra_attr['params'], $params);
				            PinaCode::set_var('params', $params);
                         }
                         if (isset($extra_attr['data'])) {
                             pinacode::set_var('data', $extra_attr['data']);
                         }
                     } 
                } 
                if (isset($post_content['sql_filter']) && is_array($post_content['sql_filter'])) {
                    $table_model->list_add_where($post_content['sql_filter'], 'AND', false);
                }
                return $table_model;
            }
            return false;
        } else {
            return false;
        }
    }
     /**
     * estraggo le variabili pinacode dei filtri request, params
     * @TODO da rivedere ora che i params vengono passati dentro la funzione stessa...
     * @param array $post_content
     * @return array; 
     */
    public static function get_extra_params_from_list_params($sql_filter) {
        
        $extra_value_pina = [];
        if (isset($sql_filter) && is_array($sql_filter)) {
            $shortcode_param = [];
            $shortcode_request = [];
            $shortcode_data = [];
            foreach ($sql_filter as $filter) {
                if (isset($filter['value'])) {
                    $shortcode_param = array_merge($shortcode_param, ADFO_functions_list::get_pinacode_params($filter['value']));
                    $shortcode_request = array_merge($shortcode_request, ADFO_functions_list::get_pinacode_params($filter['value'],'[%request'));
                    $shortcode_data = array_merge($shortcode_data, ADFO_functions_list::get_pinacode_params($filter['value'],'[%data'));
                    
                }
            }

            $param = PinaCode::execute_shortcode('[%params]');
            if (is_array($param)) {
                foreach ($shortcode_param as $val) {
                    if (isset($param[$val])) {
                        $temp = $param[$val];
                        if ($temp != '' && !is_countable($temp)) {
                            if (!isset($extra_value_pina['params'])) {
                                $extra_value_pina['params'] = [];
                            }
                            $extra_value_pina['params'][$val] = $temp;
                        }
                    }
                }
            }
            $param = PinaCode::execute_shortcode('[%request]');
            if (is_array($param)) {
                foreach ($shortcode_request as $val) {
                    if (isset($param[$val])) {
                        $temp = $param[$val];
                        if ($temp != '' && !is_countable($temp)) {
                            if (!isset($extra_value_pina['request'])) {
                                $extra_value_pina['request'] = [];
                            }
                            $extra_value_pina['request'][$val] = $temp;
                        }
                    }
                }
            }

            $param = PinaCode::execute_shortcode('[%data]');
            if (is_array($param)) {
                foreach ($shortcode_data as $val) {
                    $temp = $param[$val];
                    if ($temp != '' && !is_countable($temp)) {
                        if (!isset($extra_value_pina['data'])) {
                            $extra_value_pina['data'] = [];
                        }
                        $extra_value_pina['data'][$val] = $temp;
                    }
                }
            }
            
        }
        return $extra_value_pina;  
    }
     /**
     * Funzione per aggiungere limit (paginazione), order e altri where nel frontend.  Il risultato lo mette dentro il model. 
     * @param ADFO_model $table_model
     * @param array $post_content  Il content della lista
     * @param int $list_id  L'id della list
     * @param string $prefix Nelle chiamate post/get/ajax sceglie un prefisso per non impicciarsi
     */
    static function add_frontend_request_filter_to_model(&$table_model, $post, $list_id, $prefix = "") {
        global $wpdb;
        if ($prefix == "") {
            $request_path = "dbp".$list_id;
        } else {
            $request_path = $prefix;
        }
        $post_content = $post->post_content;
        $list_settings =  $post_content['list_setting'];
        $table_model->get_count();
        $table_limit 			= $table_model->limit;
        $table_limit_start 		= ADFO_fn::get_request_limit_start( $request_path .'_page', 1, ceil( $table_model->total_items / $table_limit )) ;
        if ($table_limit_start == 0) {
            $limit_start = 0;
        } else {
            $limit_start = ($table_limit_start -1) * $table_limit;
        }
        $table_model->list_add_limit($limit_start, $table_limit);
       
        // order
        $table_sort = ADFO_fn::get_request($request_path . '_sort', false); 

        if ($table_sort) {
            $sorts = explode(".", $table_sort);
           
            if (count($sorts) > 1) {
                $table_sort_order = array_pop($sorts);
                $table_sort_field =  ADFO_fn::get_val_from_head_column_name($list_settings, implode(".",$sorts), 'mysql_name' );
                
                if ($table_sort_field != "") {
                    $table_model->list_add_order($table_sort_field, $table_sort_order);
                }
            }
        }
        // search
     
        $search = wp_unslash(ADFO_fn::get_request($request_path . '_search', false)); 
        if ($search) {
            $filter =[] ; //[[op:'', column:'',value:'' ], ... ];
            foreach ($list_settings as $list_setting) {
                if ($list_setting->is_multiple == 1) {
                    if ($list_setting->view == 'USER') {
                        $user = $wpdb->get_row("SELECT * FROM wp_users WHERE (ID = ".intval($search)." OR user_login = '".esc_sql($search)."' OR user_email = '".esc_sql($search)."' OR user_nicename = '".esc_sql($search)."') LIMIT 1");
                     
                        if ($user) {
                            $filter[] = ['op'=>'LIKE', 'column'=> $list_setting->mysql_name, 'value' =>'"'.$user->ID.'"'];

                        } 

                    }
                    if ($list_setting->view == 'POST' || $list_setting->view == 'MEDIA_GALLERY') {
                        $post = $wpdb->get_row("SELECT * FROM wp_posts WHERE (ID = ".intval($search)." OR post_title = '".esc_sql($search)."' OR post_name = '".esc_sql($search)."') LIMIT 1");
                        if ($post) { 
                            $filter[] = ['op'=>'LIKE', 'column'=> $list_setting->mysql_name, 'value' =>'"'.$post->ID.'"'];
                        } 
                    }
                    if ($list_setting->view == 'LOOKUP') {
                        $table = '`'.esc_sql($list_setting->lookup_id).'`';
                        $id = $list_setting->lookup_sel_val;
                        $sel = "";
                        if (is_array($list_setting->lookup_sel_txt)) {
                            $sels = [];
                            foreach ($list_setting->lookup_sel_txt as $value) {
                                $sels[] = '`'.esc_sql($value).'` LIKE "'.esc_sql($search).'%"';
                            }
                            $sel = implode(" OR ",$sels);
                        
                            $sql = "SELECT ".$id." as val FROM $table WHERE ".implode(" OR ",$sels)." LIMIT 1";
                            
                            $ris = $wpdb->get_row($sql);
                            if ($ris) {
                                $filter[] = ['op'=>'LIKE', 'column'=> $list_setting->mysql_name, 'value' =>'"'.$ris->val.'"'];
                            } 
                        }
                    }
                } else {
                   // sono le colonne generate in più da altri lookup
                    if ($list_setting->view == '') {
                       continue;
                    }
                    if ($list_setting->searchable == "LIKE" && $list_setting->mysql_name != "") {
                        $filter[] = ['op'=>'LIKE', 'column'=> $list_setting->mysql_name, 'value' =>$search];
                    }
                    if ($list_setting->searchable == "=" && $list_setting->mysql_name != "") {
                        $filter[] = ['op'=>'=', 'column'=> $list_setting->mysql_name, 'value' =>$search];
                    }
                }
            }
          
            if (count($filter) > 0) {
                $table_model->list_add_where($filter, 'OR');
            }
        }
        $filter =[] ; //[[op:'', column:'',value:'' ], ... ];
        $request = ADFO_fn::sanitize_text_recursive($_REQUEST);
        foreach ($request as $req=>$req_val) {

            if (substr($req,0, strlen($request_path)) == $request_path && $req != $request_path . '_search') {
                $request_field = substr($req, strlen($request_path)+1);
                $filter_temp =  ADFO_fn::convert_head_column_in_filter_array($list_settings, $request_field, $req_val);
                if ($filter_temp != false) {
                    $filter[] =$filter_temp ;
                }

            }
        }
        if (count($filter) > 0) {
            $table_model->list_add_where($filter, 'AND');
        }

        $search = wp_unslash(ADFO_fn::get_request($request_path . '_search', false)); 
    }

    /**
     * In una stringa pinacode trova le variabili [%params.xxx] che sono le variabili scelti per gli shortcode
     * 
     * @param string $string
     * @param string $shortcode il parametro da cercare
     * @return array
     */
    public static function get_pinacode_params($string, $shortcode='[%params') {
        $start = 0;
        $shortcode_param = [];
        $length = strlen($shortcode)+1;
        do {
            $find = stripos($string, $shortcode, $start);
            if ($find !== false) {
                $end1 = stripos($string, ' ', $find + $length);
                $end2 = stripos($string,']',  $find + $length);
                if ($end1 !== false && $end2 !== false) {
                    $end = min($end1, $end2);
                } else if ($end1 !== false) {
                    $end = $end1;
                } else {
                    $end = $end2;
                }
                $param = trim(substr($string, $find + $length, $end - ($find + $length)));
                if (strlen($param) > 1 && strpos($param, ".") === false) {  
                    $shortcode_param[] = $param;
                }
                $start = $end+1;
            }
        } while ($find !== false);
        return $shortcode_param;
    }

    /**
     * Stampo il modulo per selezionare le categorie nel campo post
     * @param int $catId
     * @param int $depth
     * @param int $count_field
     * @param array $selected_cats
     * @return string
     */
    static function form_categ_tree($catId, $depth, $count_field, $selected_cats){
        $depth += 1;  
        $output ='';
        $args = 'hierarchical=1&taxonomy=category&hide_empty=0&parent=';    
        $categories = get_categories($args . $catId);
        if(count($categories) > 0) {
            foreach ($categories as $category) {
                if (is_array($selected_cats)) {
                    $checked = (in_array($category->cat_ID, $selected_cats)) ? ' checked="checked"' : '';
                } else {
                    $checked = "";
                }
                $output .=  '<label class="dbp-form-cat dbp-form-cat-' . $depth .'"><input type="checkbox" class="js-name-with-count" name="fields_post_cats['.$count_field.'][]" value="'.$category->cat_ID.'"' . $checked . '>'. $category->cat_name . '</label>';
                $output .=  self::form_categ_tree($category->cat_ID, $depth, $count_field, $selected_cats);
            }
        }
        return $output;
    }

    /**
     * Stampo il modulo per selezionare i ruoli nel campo post
     * @param int $count_field
     * @param array $selected_cats
     * @return string
     */
    static function form_user_roles( $count_field, $selected_roles){
        global $wp_roles;
        $roles = $wp_roles->roles;
        $output = "";
        foreach ($roles as $key=>$rl) {
            if (is_array($selected_roles)) {
                $checked = (in_array($key, $selected_roles)) ? ' checked="checked"' : '';
            } else {
                $checked = "";
            }
            $output .=  '<label class="dbp-form-cat "><input type="checkbox" name="fields_user_roles['.$count_field.'][]" class="js-name-with-count" value="'.$key.'"' . $checked . '>'. $rl['name'] . '</label>';
        }
        return $output;
    }


    /**
     * il nome del campo così non lo devo ricalcolare ogni volta, ma soprattutto lo salvo sul list_setting per convertire i parametri della ricerca e del search nei request
     * @param object $item
     */
    private static function private_get_mysql_name($item) {

        if (@$item->orgname != "" && $item->table != "" ) {							
            $original_field_name = '`'.$item->table.'`.`'.$item->orgname.'`';
            
        } else if (@$item->orgname != "" && $item->orgtable ) {							
            $original_field_name = '`'.$item->orgtable.'`.`'.$item->orgname.'`';
        } else if (@$item->orgname != "" ) {							
            $original_field_name = '`'.$item->orgname.'`';
        } else {
            $original_field_name = '';
        }
        return $original_field_name;
    }

    /**
     * Gli passo tutto un array come riferimento l'header di $model->get_list e ci aggiunge la variabile name_request
     * Il namerequest serve perché quando invio una richiesta da un form questo è il nome del campo che invio
     */
    private static function private_add_name_request(&$temp_items_2) {
        $names = array();
        foreach ($temp_items_2 as &$item_o) {
            $item = $item_o['schema'];
            $calculate_name = "";
            if ($item->name != "") {
                $temp_exp = explode("_", str_replace(" ","_", $item->name));
                if (count($temp_exp) == 1) {
                    $name1 = ADFO_functions_list::private_clean(6,  $item->name );
                } else {
                    $first =  array_shift($temp_exp);
                    $name1 = ADFO_functions_list::private_clean(4, implode("", $temp_exp), $first );
                }
                $name2 = ADFO_functions_list::private_clean(15, $item->name);
                $name3 = ADFO_functions_list::private_clean(4,  $item->name, $item->table);
                $name4 = ADFO_functions_list::private_clean(8, $item->name, $item->table);
                if ( !in_array($name1, $names) ) {
                    $calculate_name =  $name1;
                } elseif (!in_array($name2, $names) ) {
                    $calculate_name =  $name2;
                } elseif ($item->table != "" && !in_array( $name3, $names) ) {
                        $calculate_name =  $name3 ;
                }   elseif ($item->table != "" && !in_array( $name4, $names) ) {
                    $calculate_name =  $name4 ;
                }
            } 
        
            if (in_array( $calculate_name, $names) || $calculate_name == "") {
                $calculate_name =  ADFO_fn::clean_string(ADFO_fn::get_uniqid());
            } 
            $item_o['schema']->name_request = $calculate_name;
            $names[] = $calculate_name;
        }
    }

    /**
     * @param int $substr
     * @param string $var1
     * @param string $var2
     * @return string
     */
    private static function private_clean($substr, $var1, $var2 = "") {
        $var1 = substr(ADFO_fn::clean_string($var1), 0, $substr);
        if ($var2 != "") {
            $var2 = substr(ADFO_fn::clean_string($var2), 0, $substr);
            return $var2."_".$var1;
        }
        return $var1;
    }

    /**
     * Salva i dati di una query o di una lista nel database 
     * 
     * @param Array $query_to_execute
     * ```json
     * {"action":"string", "table":"string", "sql_to_save":"array", "id": "array", "table_alias":"string", "pri_val":"string", "pri_name":"string", "setting" : "array"}
     * ```
     * @param $dbp_id la lista da salvare
     * @param string $origin Un testo che viene passato ai filtri
     * @param Boolean $use_wp_fn Se usare le funzioni di wordpress 
     * wp_update_post & wp_update_user quando si aggiornano/creano utenti e post
     * @return array
     * ```json
     *  {"action":"string", "result":"boolean", "table":"string", "table_alias":"string, "id":"int", "error"=>"string", "sql":"array"};
     * ```
     */
    static public function execute_query_savedata($query_to_execute, $dbp_id = 0, $origin = "", $use_wp_fn = true) {
        global $wpdb;
        $queries_executed = [];
     
		if (count($query_to_execute) > 0) {
            $metadata_table = '';
			if ($dbp_id > 0) {
                $query_to_execute = apply_filters('adfo_save_data', $query_to_execute, $dbp_id, $origin);
                $post       = ADFO_functions_list::get_post_dbp($dbp_id);
                if ($post == false) {
                    return false;
                }
                if (isset($post->post_content['sql_metadata_table'])) {
                    $metadata_table_temp = explode("::", $post->post_content['sql_metadata_table']); 
                    if (count($metadata_table_temp) == 2) {
                        $metadata_table = $metadata_table_temp[1];
                    }
                }
			}
            // prima faccio gli insert, e li trasformo in update, poi rieseguo tutto con update!
            foreach ($query_to_execute as $key=>$qtx) {
             
                ADFO_admin_loader::$saved_queries->change = [];
                $query_to_execute[$key]['already_inserted'] = false;
                foreach ($qtx['sql_to_save'] as $le => $val) {
                    if (is_countable($val)) {
                        $qtx['sql_to_save'][$le] = \maybe_serialize($val);
                    }
                }
               
                if ($qtx['action'] == 'insert') {
					if ($qtx['table'] == $wpdb->posts && $use_wp_fn) {
						if (isset($qtx['sql_to_save']['ID'])) {
							unset($qtx['sql_to_save']['ID']);
						}
                        remove_action( 'post_updated', 'wp_save_post_revision' );
                        remove_action('pre_post_update', 'wp_save_post_revision');// stop revisions
                     
                        if (!isset($qtx['sql_to_save']['post_author']) || $qtx['sql_to_save']['post_author'] == "") {
                            $qtx['sql_to_save']['post_author'] = get_current_user_id();
                        }
						$ris_insert = wp_insert_post($qtx['sql_to_save']);
                  
                        add_action('pre_post_update', 'wp_save_post_revision');//  enable revisions 	
                        add_action( 'post_updated', 'wp_save_post_revision' );
						if( is_wp_error( $ris_insert ) ) {
							$error = $ris_insert->get_error_message();
							$ris_insert = false;
						}
					} else if ($qtx['table'] == $wpdb->users && $use_wp_fn) {
						if (isset($qtx['sql_to_save']['ID'])) {
							unset($qtx['sql_to_save']['ID']);
						}
						$ris_insert = wp_insert_user($qtx['sql_to_save']);
						if( is_wp_error( $ris_insert ) ) {
							$error = $ris_insert->get_error_message();
							$ris_insert = false;
						}
					} else if ($metadata_table == $qtx['table'] && isset($qtx['sql_to_save']['meta_key']) && isset($qtx['sql_to_save']['meta_value'])) {
                        $query_to_execute[$key]['action'] = 'insert_meta';
                        continue;
                        
                    } else {
                        $ris_insert = ($wpdb->insert($qtx['table'], $qtx['sql_to_save'])) ? $wpdb->insert_id : false;
						$error = $wpdb->last_error;
					}
					if ($ris_insert == false) {
						$queries_executed[] = ['action'=>'insert', 'result'=>false, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>-1, 'error'=>$wpdb->last_error, 'sql' => $qtx['sql_to_save'], 'query'=>''];
					} else {
						PinaCode::set_var(ADFO_fn::clean_string($qtx['table_alias']).".".$qtx['pri_name'], $ris_insert);
                    	$queries_executed[] = ['action'=>'insert', 'result'=>true, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>$ris_insert, 'error'=>'', 'sql' => $qtx['sql_to_save'], 'query'=>implode("<br>",ADFO_admin_loader::$saved_queries->change)];
                        $query_to_execute[$key]['action'] = 'update';
                        $query_to_execute[$key]['pri_val'] = $ris_insert;
                        $query_to_execute[$key]['id']= [$qtx['pri_name'] => $ris_insert];
                        $query_to_execute[$key]['already_inserted'] = true;
                        $query_to_execute[$key]['sql_to_save'] = $qtx['sql_to_save'];
                        // TODO se non ci sono campi calcolati è inutile rifare l'update!!!
                    }
				}
            }
			foreach ($query_to_execute as $key => $qtx) {
            
                $error = '';
                if ($dbp_id > 0 && isset($qtx['setting']) && is_countable($qtx['setting'])) {
                    foreach ($qtx['setting'] as $setting) {
                        if ( $setting->form_type == "CALCULATED_FIELD") {
                            // print ("\n CALCULATED FIELD");
                            // aggiungo i campi calcolati Le variabili pinacode devono essere state già impostate!
                            if ( $setting->custom_value_calc_when == "EVERY_TIME" || ($setting->custom_value_calc_when == "EMPTY" && (!isset($qtx['sql_to_save'][$setting->name]) || $qtx['sql_to_save'][$setting->name] == "" || empty($qtx['sql_to_save'][$setting->name]))) ) {
                                $qtx['sql_to_save'][$setting->name] =  PinaCode::execute_shortcode($setting->custom_value);
                             //  print ("\n EXECUTE CALC (".$setting->name.") ".$setting->custom_value ." = ".$qtx['sql_to_save'][$setting->name]);
                            }
                        }
                       
                        if ( $setting->form_type == "TIME") {
                            $time_temp = explode(":", $qtx['sql_to_save'][$setting->name]);
                            if (count($time_temp) == 2 && is_numeric($time_temp[0]) && is_numeric($time_temp[1])) {
                                $qtx['sql_to_save'][$setting->name]  = ($time_temp[0] * 3600) + ($time_temp[1] * 60);
                            }
                        }
                    }
                }
                foreach ($qtx['sql_to_save'] as $sql_key => $val) {
                    if (is_countable($val)) {
                        $qtx['sql_to_save'][$sql_key] = \maybe_serialize($val);
                    }
                    $qtx['sql_to_save'][$sql_key] = PinaCode::execute_shortcode( $val );
                }
				
                ADFO_admin_loader::$saved_queries->change = [];
				if ($qtx['action'] == 'update') {
                    PinaCode::set_var(ADFO_fn::clean_string($qtx['table_alias']).".".$qtx['pri_name'],  $qtx['pri_val']);
					if ($qtx['table'] == $wpdb->posts && $use_wp_fn) {
						$qtx['sql_to_save']['ID'] = $qtx['pri_val']; 
                        remove_action( 'post_updated', 'wp_save_post_revision' );
                        remove_action('pre_post_update', 'wp_save_post_revision');// stop revisions
						$ris_update = wp_update_post($qtx['sql_to_save']);
                        add_action('pre_post_update', 'wp_save_post_revision');//  enable revisions again
                        add_action( 'post_updated', 'wp_save_post_revision' );
						if( is_wp_error( $ris_update ) ) {
							$error = $ris_update->get_error_message();
							$ris_update = false;
						}
					} else if ($qtx['table'] == $wpdb->users && $use_wp_fn) {
						$qtx['sql_to_save']['ID'] = $qtx['pri_val']; 
						$ris_update = wp_update_user($qtx['sql_to_save']);
						if( is_wp_error( $ris_update ) ) {
							$error = $ris_update->get_error_message();
							$ris_update = false;
						}
					} else {
						$ris_update = $wpdb->update($qtx['table'], $qtx['sql_to_save'], $qtx['id']);
						$error = $wpdb->last_error;
					}
                    if (!$query_to_execute[$key]['already_inserted']) {
                        if ($ris_update == false) {
                            $queries_executed[] = ['action'=>'update', 'result'=>false, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>$qtx['pri_val'], 'error'=>$error, 'sql' => $qtx['sql_to_save'], 'query'=>''];
                        } else {
                            $queries_executed[] = ['action'=>'update', 'result'=>true, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>$qtx['pri_val'], 'error'=>'', 'sql' => $qtx['sql_to_save'], 'query'=> implode("<br>",ADFO_admin_loader::$saved_queries->change)];
                        }
                    }
				}
	
                if ($qtx['action'] == 'delete') {
                    $rows = $wpdb->delete( $qtx['table'], $qtx['id']);
                    $error = $wpdb->last_error;
                    if ($rows > 0) {
                        $queries_executed[] = ['action'=>'delete', 'result'=>true, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>$qtx['pri_val'], 'error'=>'', 'query'=>implode("<br>",ADFO_admin_loader::$saved_queries->change)];
                    } else {
                        $queries_executed[] = ['action'=>'delete', 'result'=>false, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>$qtx['pri_val'], 'error'=> $error, 'query'=>implode("<br>",ADFO_admin_loader::$saved_queries->change)];
                    }
                }
                if ($qtx['action'] == 'insert_meta') {
                    $remove = $qtx['sql_to_save'];
                    unset($remove['meta_value']);
                    if (count($remove) == 2) {
                        $wpdb->delete($qtx['table'], $remove);
                    }
                   
                    $ris_insert = ($wpdb->insert($qtx['table'], $qtx['sql_to_save'])) ? $wpdb->insert_id : false;
                    $error = $wpdb->last_error;
                    if ($ris_insert == false) {
                        $queries_executed[] = ['action'=>'insert meta', 'result'=>false, 'table'=>$qtx['table'], 'table_alias'=>$qtx['table_alias'], 'id'=>$qtx['pri_val'], 'error'=>$error, 'sql' => $qtx['sql_to_save'], 'query'=>''];
                    } 
                }
			}
		}

		return $queries_executed;
	}

    /**
     * Aggiunge eventuali lookup alla query sql 
     * @param ADFO_model $table_model
     * @param object $post
     */
    static function add_lookups_column(&$table_model, &$post) {
        
        $lookups = false;
        $new_select = [];
        foreach ($post->post_content['list_setting'] as $key => $setting) {

            if ($setting->is_multiple == 1) continue;
            
            if ($setting->view == "LOOKUP" && $setting->lookup_sel_val != '' && is_countable($setting->lookup_sel_txt) ) {
                $lookups = true;
                // lookup_id: // tabella da aggiungere
                // lookup_sel_val: primary key
                // lookup_sel_txt: i campi da aggiungere
                // mysql_name la colonna da comparare con ` ???
                $query = $table_model->get_current_query();
                $alias = 'lk_'.ADFO_fn::get_table_alias($key, $query);
                $pri = '`'.esc_sql($alias).'`.`'.esc_sql($setting->lookup_sel_val).'`';
                $table_model->list_add_from(' LEFT JOIN `'.esc_sql($setting->lookup_id).'` `'.$alias.'` ON  '.esc_sql($setting->mysql_name).' =  '.$pri);
                foreach ($setting->lookup_sel_txt as $new_field) {
                    $new_alias = $key."_".ADFO_fn::clean_string($new_field);
                    $new_select[] = '`'.esc_sql($alias).'`.`'.esc_sql($new_field).'` AS `'.esc_sql($new_alias).'`';
                }
            } 
        }
        // devo sostituire  tutti i select mancano i campi non legati a tabelle (e i campi aggiunti con custom_value?!)
        if ($lookups) {
            $table_model->list_add_select(implode(', ',  $new_select));
        } 
    }

    /**
     * Aggiunge il post_type alla query sql 
     * @param ADFO_model $table_model
     * @param object $post
     * @since 1.7.0
     */
    static function add_post_type_settings(&$table_model, $post, $frontend = false) {
        $current_post_alias = self::get_post_type_alias($post);
        if ($current_post_alias != '') {
            $table_model->list_add_where( [['op'=>'=', 'column' => '`'.$current_post_alias.'`.`post_type`', 'value' => $post->post_content['post_type']['name'] ]], 'AND', false);
        }
        if ($frontend) {   
            if (ADFO_functions_list::has_post_status($post)) {
                $post_status_field = ADFO_functions_list::get_post_status_field($post);
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_status_field, 'value' => 'publish' ]], 'AND', false);
            }
        }
    }

    /**
     * @since 1.7.0
     * Solo in amministrazione serve per gestire il publish, draft e trash
     * @param ADFO_model $table_model
     * @param object $post
     */
    static function add_post_type_status(&$table_model, $post, $post_status = '') {
        $post_status_field = self::get_post_status_field($post);
        $post_author_field = self::get_post_author_field($post);
        // var_dump($user_roles);
        //print ("USER ROLE: ".$user_role);
        if ($post_author_field != '') {
            $user = wp_get_current_user();  
            $user_role = ADFO_functions_list::get_user_role($post);
            if ($user_role == 'contributor') {
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_author_field, 'value' => $user->ID ]], 'AND', false);
            } else if ($user_role == 'author') {
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_author_field, 'value' => $user->ID ]], 'AND', false);
            } else if ($user_role != 'editor' && $user_role != 'administrator') {
                // se non hai un ruolo valido non puoi vedere nulla ?!
                $table_model->list_add_where( [['op'=>'=', 'column' => '"1"', 'value' => '2' ]], 'AND', false);
            }
        }

        if ($post_status_field != '')  {
            if ($post_status == 'trash'){
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_status_field, 'value' => 'trash' ]], 'AND', false);
            } else  if ($post_status == 'draft'){
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_status_field, 'value' => 'draft' ]], 'AND', false);
            }  else  if ($post_status == 'publish'){
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_status_field, 'value' => 'publish' ]], 'AND', false);
            } else  if ($post_status == 'pending'){
                $table_model->list_add_where( [['op'=>'=', 'column' => $post_status_field, 'value' => 'pending' ]], 'AND', false);
            } else {
                $table_model->list_add_where( [['op'=>'!=', 'column' => $post_status_field, 'value' => ['trash'] ]], 'AND', false);
            }
            //print "<p>".$table_model->get_current_query()."</p>";
        }
    }

    /**
     * @since 1.7.0
     * Restituisce l'alias della tabella post se esiste. Lo posso usare per verifivare se 
     * una lista è un tipo post type valido
     * @param object $post
     * @params string $return = 'mysql_name' | 'alias' | 'field'
     * @return mixed
     */
    static function get_post_status_field($post, $return = 'mysql_name') {
        global $wpdb;
        if (isset($post->post_content['form']) && is_array($post->post_content['form'])) {
            foreach ($post->post_content['form'] as $field) {
                if ($field['form_type'] == 'POST_STATUS') {
                    if ($return == 'field') {
                        return $field;
                    } else if ($return == 'mysql_name') {
                        return '`'.$field['table'].'`.`'.$field['name'].'`';
                    } else if ($return == 'alias') {
                        return $field['name'];
                    }
                }
            }
        }
        return '';
    }
    
    /**
     * @since 1.7.0
     * Restituisce l'alias della tabella post se esiste. Lo posso usare per verifivare se 
     * una lista è un tipo post type valido
     * @param object $post
     * @params string $return = 'mysql_name' | 'alias' | 'field'
     * @return string
     */
    static function get_post_author_field($post, $return = 'mysql_name') {
        global $wpdb;
        if (isset($post->post_content['form']) && is_array($post->post_content['form'])) {
            foreach ($post->post_content['form'] as $field) {
                if ($field['form_type'] == 'RECORD_OWNER') {
                    if ($return == 'field') {
                        return $field;
                    } else if ($return == 'mysql_name') {
                        return '`'.$field['table'].'`.`'.$field['name'].'`';
                    } else if ($return == 'alias') {
                        return $field['name'];
                    }
                }
            }
        }
        return '';
    }

    /**
     * @since 1.7.0
     * Restituisce l'alias della tabella post se esiste. Lo posso usare per verifivare se 
     * una lista è un tipo post type valido
     * @param object $post
     * @return string
     */
    static function get_post_type_alias($post) {
        global $wpdb;
        if ($post->post_content['post_type']['name'] == '' ) return;
        $count_post_tables = 0;
        $current_post_alias = '';
        if (isset($post->post_content['sql_from']) && is_array($post->post_content['sql_from'])) {
            foreach ($post->post_content['sql_from'] as $alias => $from) {
                if ($from == $wpdb->posts) {
                    $current_post_alias = $alias;
                    $count_post_tables++;
                }
            }
        }
        if ($count_post_tables != 1) return '';
        return $current_post_alias;
    }

    /**
     * Trovo dentro form se c'è un campo di tipo post_status
     * @since 1.7.0
     * @param object $post
     * @return boolean
     */ 
    static function has_post_status($post) {
        if (isset($post->post_content['form']) && is_array($post->post_content['form'])) {
            foreach ($post->post_content['form'] as $field) {
                if ($field['form_type'] == 'POST_STATUS') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Trovo dentro form se c'è un campo di tipo author
     * @since 1.7.0
     * @param object $post
     * @return boolean
     */ 
    static function has_post_author($post) {
        if (isset($post->post_content['form']) && is_array($post->post_content['form'])) {
            foreach ($post->post_content['form'] as $field) {
                if ($field['form_type'] == 'RECORD_OWNER') {
                    return true;
                }
            }
        }
        return false;
    }

     /**
     * Trovo dentro form se c'è un campo di tipo order
     * @since 1.8.0
     * @params string $return = 'mysql_name' | 'alias' | 'field'
     * @return mixed
     */
    static function get_order_field($post, $return = 'mysql_name') {
    
        if (isset($post->post_content['form']) && is_array($post->post_content['form'])) {
            foreach ($post->post_content['form'] as $field) {
                if ($field['form_type'] == 'ORDER') {
                    
                    if ($return == 'field') {
                        return $field;
                    } else if ($return == 'mysql_name') {
                        return '`'.$field['table'].'`.`'.$field['name'].'`';
                    } else if ($return == 'alias') {
                        return $field['name'];
                    }
                }
            }
        }
        return '';
    }


    /**
     * Ritorna il gruppo di un utente considerando che a seconda del post type inserito è possibile
     * cambiare quale gruppo ha i permessi di contributor, quali di author e quali di editor.
     * Se non ha questi permessi né quello di amministratore ritorna una stringa vuota
     * @since 1.7.0
     */
    static function get_user_role($post) {
        $user = wp_get_current_user();  
        $user_roles = $user->roles;
        if (!self::has_post_author($post) ) {
            return (in_array('administrator', $user_roles)) ? 'administrator' : '';
        }
        $permission = isset($post->post_content['post_status']['permission']) ? $post->post_content['post_status']['permission'] : [];
        $user_role = '';
        $default_role = '';
        $none = 0;
        foreach ($permission as $convert_role => $role) {
            if ($role == '_no_body') {
                $none++;
            }
            if ($role == '_all_others') {
                $default_role = $convert_role;
            }
            if (in_array($role, $user_roles)) {
                $user_role = $convert_role;
                break;
            }
        }
        if ($user_role == '' && $default_role != '') {
            $user_role = $default_role;
        }
        if (in_array('administrator', $user_roles)) {
            $user_role = 'administrator';
        }

        if ($none == count($permission)) {
            $user_role = 'editor';
        }

        return $user_role;
    }

    /**
     * @since 1.7.0
     * @param object $post
     * @param ADFO_model $table_model
     * @return boolean
     */
    static function count_table_by_status($post) {
        global $wpdb;
        $sql =  $post->post_content['sql'];
        $post_stati =  ['publish' => 0, 'pending' => 0, 'draft' => 0, 'auto-draft' => 0, 'future' => 0, 'private' => 0, 'inherit' => 0, 'trash' => 0];
        if ( $sql != "") {
            $table_model = new ADFO_model();
            $table_model->prepare($sql);
            $table_model->remove_limit();
            $post_author_field = self::get_post_author_field($post);
            if ($post_author_field != '') {
                $user = wp_get_current_user();  
                $user_roles = $user->roles;
                $user_role = self::get_user_role($post);
                if ($user_role == 'contributor') {
                    $table_model->list_add_where( [['op'=>'=', 'column' => $post_author_field, 'value' => $user->ID ]]);
                } else if ($user_role == 'author') {
                    $table_model->list_add_where( [['op'=>'=', 'column' => $post_author_field, 'value' => $user->ID ]]);
                } else if ($user_role != 'editor' && !in_array('administrator', $user_roles)) {
                    return [];
                }
            }
            if (isset($post_content['sql_filter']) && is_array($post_content['sql_filter'])) {
                $table_model->list_add_where($post_content['sql_filter']);
            }
            self::add_post_type_settings($table_model, $post);
            // trovo il campo post_status
            $post_status = self::get_post_status_field($post);
            // faccio il distinct del campo post_status
            $table_model->list_change_select('DISTINCT '.$post_status." as post_status");
            $post_status_names = $table_model->get_list();
            $post_model_query = $table_model->get_current_query();
            if (is_array($post_status_names) && count($post_status_names) > 1) {
                array_shift($post_status_names);
                foreach ($post_status_names as $pst) {
                    $table_model_count = new ADFO_model();
                    $table_model_count->prepare($post_model_query);
                    // conto quanti record ci sono per ogni stato
                    $table_model_count->list_change_select('COUNT(*) as count');
                    $table_model_count->list_add_where([['op'=>'=', 'column'=>$post_status, 'value'=>$pst->post_status]]);
                    $count = $wpdb->get_var($table_model_count->get_current_query());
                    $post_stati[$pst->post_status] = $count;
                }
            }
        }
        return $post_stati;
    }

     /**
     * Aggiunge eventuali USER O POST alla query sql per sostituire le colonne POST e USER
     * @param ADFO_model $table_model
     */
    static function add_post_user_column(&$table_model, &$post) {
        global $wpdb;
        $lookups = false;
        $new_select = [];
        foreach ($post->post_content['list_setting'] as $key=>$setting) {
         
            if ($setting->view == "POST" ) {
                // trovo il form per vedere se è un is_multple o no
                $is_multiple = false;
                foreach ($post->post_content['form'] as $field) {
                    if ($field['name'] == $setting->name && $field['table'] == $setting->table && isset($field['is_multiple']) && $field['is_multiple'] == 1) {
                        $is_multiple = true;
                    }
                }
                if ($is_multiple) continue;
                $lookups = true;
                $query = $table_model->get_current_query();
                $alias = ADFO_fn::get_table_alias('post', $query);
                $pri = '`'.esc_sql($alias).'`.`ID`';
                $table_model->list_add_from(' LEFT JOIN `'.$wpdb->posts.'` `'.$alias.'` ON  '.esc_sql($setting->mysql_name).' =  '.$pri);

                $alias2 = $alias."_".ADFO_fn::get_table_alias('title', $query);
                $new_select[] = '`'.esc_sql($alias).'`.`post_title` AS `'.esc_sql($alias2).'`';
                $post->post_content['list_setting'][$alias2] = clone $setting;
                $setting->toggle = 'HIDE';
                $post->post_content['list_setting'][$alias2]->title = ($setting->title != '') ? $setting->title : $key;
                $post->post_content['list_setting'][$alias2]->type = NULL;
                if ($setting->custom_param == 1) {
                    $post->post_content['list_setting'][$alias2]->custom_param = $key;
                    $post->post_content['list_setting'][$alias2]->view = '_POST_LINK';
                } else {
                    $post->post_content['list_setting'][$alias2]->view = 'TEXT';
                }
                $post->post_content['list_setting'][$alias2]->mysql_name = '`'.$alias.'`.`post_title`';
                $post->post_content['list_setting'][$alias2]->searchable = 'LIKE';
            } else if ($setting->view == "USER" ) {
                // trovo il form per vedere se è un is_multple o no
                $is_multiple = false;
                foreach ($post->post_content['form'] as $field) {
                    if ($field['name'] == $setting->name && $field['table'] == $setting->table && isset($field['is_multiple']) && $field['is_multiple'] == 1) {
                       $is_multiple = true;
                    }
                }
                if ($is_multiple) continue;
                $lookups = true;
                $query = $table_model->get_current_query();
                $alias = ADFO_fn::get_table_alias('users', $query);
                $pri = '`'.esc_sql($alias).'`.`ID`';
                $table_model->list_add_from(' LEFT JOIN `'.$wpdb->users.'` `'.$alias.'` ON  '.esc_sql($setting->mysql_name).' =  '.$pri);
                
                $alias2 = $alias."_".ADFO_fn::get_table_alias('name', $query);
                $new_select[] = '`'.esc_sql($alias).'`.`user_nicename` AS `'.esc_sql($alias2).'`';
                $post->post_content['list_setting'][$alias2] = clone $setting;
                $setting->toggle = 'HIDE';
                $post->post_content['list_setting'][$alias2]->title = ($setting->title != '') ? $setting->title : $key;
                $post->post_content['list_setting'][$alias2]->type = NULL;

                if ($setting->custom_param == 1) {
                    $post->post_content['list_setting'][$alias2]->custom_param = $key;
                    $post->post_content['list_setting'][$alias2]->view = '_USER_LINK';
                } else {
                    $post->post_content['list_setting'][$alias2]->view = 'TEXT';
                }
                
                $post->post_content['list_setting'][$alias2]->mysql_name = '`'.$alias.'`.`user_nicename`';
                $post->post_content['list_setting'][$alias2]->searchable = 'LIKE';
            } 
        }
        // devo sostituire  tutti i select mancano i campi non legati a tabelle (e i campi aggiunti con custom_value?!)
        if ($lookups) {
            $table_model->list_add_select(implode(', ',  $new_select));
        }
    }

    /**
     * Setta i parametri per la form di inserimento quando c'è una tabella post.
     * @param string $table_as
     * @param string $table_name
     * @return ADFO_field_param[]
     */
    static function setting_post_form($table_as, $table_name) {
        $fields = [];
        $fields[] = (new ADFO_field_param())->set_from_array([
            'name'=>'ID',
            'order' => 1,
            'table' => $table_as, 
            'orgtable' => $table_name,
            'edit_view' => 'SHOW',
            'label' => 'ID',
            'form_type'=> 'PRI'
        ]);
        $fields[] = (new ADFO_field_param())->set_from_array([
            'name'=>'post_title',
            'order' => 2,
            'table' => $table_as,
            'orgtable' => $table_name,
            'edit_view' => 'SHOW',
            'label' => 'Title',
            'form_type'=> 'VARCHAR',
            'required' => 1,
        ]);
        $fields[] = (new ADFO_field_param())->set_from_array([
            'name'=>'post_content',
            'order' => 3,
            'table' => $table_as,
            'orgtable' => $table_name,
            'edit_view' => 'HIDE',
            'label' => $field,
            'form_type'=> 'TEXT'
        ]);
        $fields[] = (new ADFO_field_param())->set_from_array([
            'name'=>'post_author',
            'order' => 4,
            'table' => $table_as,
            'orgtable' => $table_name,
            'edit_view' => 'SHOW',
            'label' => 'Author',
            'form_type'=> 'RECORD_OWNER'
        ]);
        $fields[] = (new ADFO_field_param())->set_from_array([
            'name'=>'post_status',
            'order' => 5,
            'table' => $table_as,
            'orgtable' => $table_name,
            'edit_view' => 'SHOW',
            'label' => 'Status',
            'default_value' => 'publish',
            'form_type'=> 'POST_STATUS'
        ]);
        $other_fields = [  'post_type', 'post_status', 'guid','post_excerpt','post_name','post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt','comment_status', 'ping_status', 'post_password',  'post_content_filtered', 'post_parent',  'menu_order','post_mime_type', 'comment_count','to_ping', 'pinged' ];
        $count = 6;
        foreach ($other_fields as $field) {
            $fields[] = (new ADFO_field_param())->set_from_array([
                'name'=>$field,
                'order' => $count,
                'table' => $table_as,
                'orgtable' => $table_name,
                'edit_view' => 'HIDE',
                'label' => $field,
                'form_type'=> 'TEXT'
            ]);
            $count++;
        }
        return $fields;
    }


    /**
     * Setting post list structure configure
     * @param string $table_as
     * @param string $table_name
     * @return ADFO_list_setting[]
     */
    static function setting_post_structure($table_as, $table_name) {
        $other_fields = ['ID','post_title', 'post_content', 'post_author', 'post_excerpt', 'post_type', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order','post_mime_type', 'comment_count', 'post_date_gmt', 'post_date'];
        $count = 1;
        $list_setting = [];
        foreach ($other_fields as $field) {
            $view = 'TEXT';
            switch($field) {
                case 'post_title':
                    $view = 'PERMALINK';
                    break;
                case 'post_author':
                    $view = 'USER';
                    break;
                case 'post_content':
                    $view = 'EDITOR_TINYMCE';
                    break;
                case 'post_date':
                case 'post_date_gmt':
                case 'post_modified':
                case 'post_modified_gmt':
                    $view = 'DATETIME';
                    break;
            }
            $list_setting[$field] = (new ADFO_list_setting())->set_from_array(
                ['toggle'=>((in_array($field, ['ID', 'post_title', 'post_author', 'post_status'])) ? 'SHOW': 'HIDE'),
                'title'	=> $field, 
                'view'	=> $view,
                'order'	=> $count,
                'type' 	=> 'FIELD',
                'mysql_name' 	=> $table_name,
                'mysql_table' 	=> '`'.$table_as.'`.`'.$field.'`',
                'name_request' 	=> $field,
                'inherited' => 1
                ]
            );
            if ($field == 'post_title') {
                $list_setting[$field]->custom_param = 'ID';
            }
            $count++;
        }
        return $list_setting;
    }

    /**
     * Genera un post type unico
     */
    static function generate_post_type($post_type_origin) {
        $post_type = substr(strtolower(preg_replace('/[^A-Za-z0-9\_]/', '-',  $post_type_origin)), 0, 20);
        $post_type = str_replace('--', '-', $post_type);
        if ($post_type_origin != $post_type) {
            $count_post_type = 1;
            while (post_type_exists($post_type) && $count_post_type < 100) {
                $post_type = substr(strtolower(preg_replace('/[^A-Za-z0-9\_]/', '-',  $post_type_origin)), 0, 18);
                $post_type = str_replace('--', '-', $post_type);
                $post_type .= str_pad($count_post_type, 2, "0", STR_PAD_LEFT);
                $count_post_type++;
            }
        }
        return $post_type;
    }

    /**
     * conta il numero di post per post_status per un singolo utente
     */
    static function count_post_by_status($post_type, $user_id) {
        global $wpdb;
        $sql = "SELECT post_status, count(*) as count FROM `".$wpdb->posts."` WHERE post_type = '".esc_sql($post_type)."' AND post_author = ".absint($user_id)." GROUP BY post_status";
        $result = $wpdb->get_results($sql);
        $count = [];
        $statuses = get_post_stati( [], 'objects' );
        foreach ($statuses as $status) {
            $count[$status->name] = 0;
        }
        foreach ($result as $row) {
            $count[$row->post_status] = $row->count;
        }
        return $count;
    }



    /**
     * Converte gli id delle tabelle da table.id (table_alias.orgname) a table_id (alias del campo della query)
     * @DEPRECATED???
     */
    static function convert_id_table($dbp_id, $dbp_ids) {
        $dbp_post = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($dbp_post == false) return [];
        // qui ci devono essere tutte le chiavi primarie!
        $ids_alias = ADFO::get_primaries_id($dbp_id, false);
       
        $result_ids = [];
        $columns = ADFO::get_ur_list_columns($dbp_id);
        if (is_array($dbp_ids)) {
            foreach ($ids_alias as $id_alias) {
                $field_setting = $dbp_post->post_content["list_setting"][$id_alias];
                $query_var = "";
                if (array_key_exists($id_alias, $dbp_ids))  {
                    // name_request
                    $query_var = $dbp_ids[$id_alias];
                } else if (array_key_exists($field_setting->table.".".$field_setting->orgname, $dbp_ids))  {
                    // nome della colonna
                    $query_var = $dbp_ids[$field_setting->table.".".$field_setting->orgname];
                }
                if (absint($query_var) > 0) {
                    $result_ids[$id_alias] = absint($query_var);
                }
            }
        } else {
            $result_ids = $dbp_ids;
        } 
        return $result_ids;
    }

}