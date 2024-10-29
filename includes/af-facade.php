<?php
/**
* funzioni pubbliche
* Se stai in function.php ricordati di chiamarle attraverso il namespace: admin_form\ADFO::get_list(...);
*/
namespace admin_form;

use stdClass;

class  ADFO
{

    static $frontend_template_engine_data = [];
    /**
     * Carica una lista da un id e ne ritorna l'html. Praticamente la stessa cosa che fa lo shortcode!
     * @param Int $post_id
     * @param Boolean $only_table Se stampare i filtri e la form che la raggruppa oppure no (true)
     * @param Array $params I parametri aggiuntivi che verranno salvati in [%params
     * @param String $prefix Il prefisso delle variabili che verranno passate
     */
    static function get_list($post_id, $only_table = false, $params = [], $prefix="") {
        ADFO_fn::require_init();
        $post_id = absint($post_id);
        $custom_data = apply_filters('adfo_frontend_get_list', '', $post_id);
        if ($custom_data != "") {
            return $custom_data;
        }
        $ori_params =  PinaCode::get_var('params');
        $ori_globals = PinaCode::get_var('global');
        PinaCode::set_var('params', $params);
        $show_trappings = true;
        if (ADFO_fn::is_form_open()) {
            // la form che uso di solito per gestire le tabelle è stata già aperta e non chiusa
            // da un'altra quindi si sta renderizzando una lista dentro un'altra lista
            // per cui disabilito ordinamento, paginazione e ricerca!!
            $show_trappings = false;
        }
        ADFO_fn::set_open_form();
        $list =  new ADFO_render_list($post_id, null, $prefix);
        if ($only_table || !$show_trappings) {
            $list->hide_div_container();
            // setto il div
            if (isset($_REQUEST['dbp_div_id'])) {
                $divid = sanitize_text_field($_REQUEST['dbp_div_id']);
                $list->set_uniqid($divid);
            }
        }
        if (!$show_trappings) {
            // Faccio finta che la form è stata già create
            $list->block_opened = true;
            $list->frontend_view_setting['table_sort'] = false;
        }
        
        if ( $list->get_frontend_view('type', 'TABLE_BASE') == "TABLE_BASE") {
            ADFO_fn::remove_hide_columns($list->table_model);
            ob_start();
            $show_table = true;
            if (isset($list->frontend_view_setting['checkif']) && $list->frontend_view_setting['checkif'] == 1 && isset($list->frontend_view_setting['if_textarea']) && $list->frontend_view_setting['if_textarea'] != '') {
                $show_table = (boolean)trim(PinaCode::math_and_logic($list->frontend_view_setting['if_textarea']));
            } 
            if ($show_table) {
                //draw_export_btn
                if ($list->get_frontend_view('table_export') != '' && $show_trappings) {
                    $list->export_btn();
                }
                if ($list->get_frontend_view('table_search') == 'simple' && $show_trappings) {
                    $list->search();
                }
                if (in_array($list->get_frontend_view('table_pagination_position'), ["up",'both']) && $show_trappings) {
                    $list->pagination();
                }
                if (($list->no_result == '' || empty($list->no_result)) || count($list->table_model->items) > 1) {
                     $list->table();
                } else {
                    echo wp_kses_post($list->no_result); 
                }
                if (in_array($list->get_frontend_view('table_pagination_position'), ["down",'both']) && $show_trappings) {
                    $list->pagination();
                }
                if ($show_trappings) {
                    $list->end();
                } 
            } else {
                if ($list->get_frontend_view('table_update') != "none") {
                    ob_start() ;
                    $list->open_block(false);
                    $result[] = ob_get_clean();
                    ob_start();
                    $list->search();
                    $search = ob_get_clean();
                    PinaCode::set_var('html.search',  $search);
                    ob_start();
                    $list->pagination();
                    $pagination = ob_get_clean();
                    PinaCode::set_var('html.pagination',  $pagination);
                   
                } else {
                    PinaCode::set_var('html.pagination',  '');
                    PinaCode::set_var('html.search',  '');
                }
                ob_start();
                $list->table();
                $table = ob_get_clean();
                PinaCode::set_var('html.table',  $table);
                PinaCode::set_var('html.no_result',  $list->no_result);
                PinaCode::set_var('data',  $list->table_model->items);
                echo wp_kses_post(PinaCode::execute_shortcode($list->frontend_view_setting['content_else']));
            }
            PinaCode::set_var('global',  $ori_globals);
            PinaCode::set_var('params', $ori_params);
            if ($show_trappings) {
                ADFO_fn::set_close_form();
            }
            return ob_get_clean();
        } else {
            // EDITOR
            $result = [];
            $items = $list->table_model->items;
            $text = $list->get_frontend_view('content');
            if ($list->get_frontend_view('table_update') != "none") {
                ob_start() ;
                $list->open_block(false);
                $result[] = ob_get_clean();
                ob_start();
                $list->search();
                $search = ob_get_clean();
                PinaCode::set_var('html.search',  $search);
            } else {
                PinaCode::set_var('html.search',  '');
            }
            if (is_array($items)) {
                $table_header = reset($list->table_model->items);  
                $first_row = array_shift($items);
                $first_row = array_map(function($el) {return $el->name;}, $first_row);
                PinaCode::set_var('total_row', absint($list->table_model->get_count()));
                PinaCode::set_var('key',0);
                $first_row = ADFO_fn::remove_hide_columns_in_row($table_header, $first_row);
                PinaCode::set_var('data',  $first_row);
                if ($list->get_frontend_view('table_update') != "none") {
                    ob_start();
                    $list->pagination();
                    $pagination = ob_get_clean();
                    PinaCode::set_var('html.pagination',  $pagination);
                } else {
                    PinaCode::set_var('html.pagination',  '');
                }
            } 
            if (!is_array($items) || !$show_trappings) {
                PinaCode::set_var('total_row', 0);
                PinaCode::set_var('key',0);
                PinaCode::set_var('data',  []);
                PinaCode::set_var('html.pagination',    '');
                PinaCode::set_var('html.search',    '');
            }
            $result[] = PinaCode::execute_shortcode($list->get_frontend_view('content_header'));
            
            if (is_array($items) && $text != "") {
                foreach ($items as $key=> $item) {
                    //PinaCode::set_var('primaries', ADFO_fn::data_primaries_values( $primaries, $table_header, $item));
                    PinaCode::set_var('key', ($key+1));
                    //$item = ADFO_fn::remove_hide_columns_in_row($table_header, $item);

                    PinaCode::set_var('data', $item);
                    // aggiungo i value e converto i dati
                    foreach ($item as $k=>$i) {

                    }
                    $temp = PinaCode::execute_shortcode($text);
                    if (is_array($temp)) {
                        $result[] = json_encode($temp); 
                    } else {
                        $result[] = $temp; 
                    }
                }
            }
            PinaCode::set_var('data', []);
            $result[] = PinaCode::execute_shortcode($list->get_frontend_view('content_footer'));
           
            if ($list->get_frontend_view('table_update') != "none") {
                ob_start() ;
                $list->end();
                $result[] = ob_get_clean();
            }
            if (isset($list->frontend_view_setting['popup_type']) && isset($list->frontend_view_setting['popup_type']) != '') {
                foreach ($result as &$res) {
                    $res = str_replace("js-dbp-popup", "js-dbp-popup js-dbp-popup-mode-".$list->frontend_view_setting['popup_type'], $res);
                }
            }
            PinaCode::set_var('global',  $ori_globals);
            PinaCode::set_var('params', $ori_params);
            if ($show_trappings) {
                ADFO_fn::set_close_form();
            }
            return implode("",$result);
        }
        if ($show_trappings) {
            ADFO_fn::set_close_form();
        }
        PinaCode::set_var('global',  $ori_globals);
        PinaCode::set_var('params', $ori_params);
    }

    /**
     * Ritorna la pagina per un singolo articolo
     * @param int $dbp_id
     * @param int|array $ids
     */
    static function get_single($dbp_id, $ids) {
        ob_start();
        $dbp_post = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($dbp_post == false) {
            return ob_get_clean();
        }
        $result = self::get_detail($dbp_id, $ids, false);

        if ($result === false) {
            return ob_get_clean();
        }
		if (isset($dbp_post->post_content['frontend_view']['detail_type']) && $dbp_post->post_content['frontend_view']['detail_type'] == "PHP") {
			do_action('dbp_detail_'.$dbp_id, $result);
		} else if (isset($dbp_post->post_content['frontend_view']['detail_type']) && $dbp_post->post_content['frontend_view']['detail_type'] == "CUSTOM") {
            $detail_template = $dbp_post->post_content['frontend_view']['detail_template'];
            PinaCode::set_var('data', $result) ;
            $ris = PinaCode::execute_shortcode($detail_template);
            if (is_array($ris) || is_object($ris)) {
                echo wp_unslash(json_encode($ris));
            } else {
                echo $ris;
            }
		} else {
             // rimuovo eventuali chiavi primarie originali ?! 
            $name_requests2 = self::get_primaries_id($dbp_id, false);
            foreach ($result as $k => $r) {
                if (in_array($k, $name_requests2)) {
                    unset($result->$k);
                }
            }
            // stampo un detail di default
            ?><table class="dbp-table"><?php 
            foreach ($result as $k=>$r) {
                $set = null;
                if (isset($dbp_post->post_content["list_setting"][$k])) {
                    $set = $dbp_post->post_content["list_setting"][$k];
                    $label = $set->title;
                } else {
                    $label = $k;
                }
                ?><tr>
                    <td><?php echo $label; ?></td>
                    <td><?php echo $r; ?></td>
                </tr><?php 
            }
			?></table><?php
		}
        return ob_get_clean();
    }

    /**
     * Ritorna la classe che genera la tabella
     * return \ADFO_render_list;
     */
    static function render($dbp_id, $mode = null) {
        ADFO_fn::require_init();
        return new ADFO_render_list($dbp_id, $mode);
    }

    /**
     * Calcola il totale dei record dei dati estratti da una lista
     * @param number $post_id l'id della lista
     * @param boolean $filter se aggiungere i filtri oppure no alla query
     * @return int -1 se non riesce a fare il conto
     */
    static function get_total($post_id, $filter = false) {
        $post        = ADFO_functions_list::get_post_dbp($post_id);
        if (!$post) {
            return 0;
        }
        if ($filter) {
            $table_model = ADFO_functions_list::get_model_from_list_params($post->post_content);
        } else {
            $table_model = new ADFO_model();
            if (isset($post->post_content['sql'])) {
                $table_model->prepare($post->post_content['sql']);
            } else {
                $table_model = false;
            }
        }
        ADFO_functions_list::add_post_type_settings($table_model, $post);
        if ($table_model != false && $table_model->sql_type() == "select") {
            return $table_model->get_count();
        }
        return -1;
       
    }

     /**
     * Carica tutte le liste dbp
     * @return array 
     * ```json
     * {'id':'title','id':'...'}
     * ```
     */
    static function get_lists_names() {
        global $wpdb;
        $query_lists = $wpdb->get_results('SELECT ID, post_title FROM '.$wpdb->posts.' WHERE post_type ="dbp_list" AND `post_status` = "publish" ORDER BY post_title');
        $lists = [];
        if (is_countable($query_lists)) {
            foreach ($query_lists as $ql) {
                $lists[$ql->ID] = $ql->post_title;
            }
        }
        return $lists;
    }

    /**
     * Ritorna l'elenco delle colonne di una lista
     * @param int $dbp_id 
     * @param bool searchable 
     * @param bool extend 
     * @return ADFO_list_setting[]|array
     * SE extend è false
     * ```json
     * {'field_name':'field_title','field_name':'...'} 
     * ```
     * Se extend è true ADFO_list_setting[]
     */
    static function get_list_columns($dbp_id, $searchable = true, $extend = false) {
        $post  = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($post == false) {
            return [];
        }
        /**
         * @var ADFO_list_setting[] $lists
         */
        $lists = [];
        /**
         * @var ADFO_list_setting[] $list_setting
         */
        $list_setting = [];
        if (!isset($post->post_content) ) {
            return [];
        }
       
        $sql = @$post->post_content['sql'];
        if ($sql != "") {
            $table_model = new ADFO_model();
            $table_model->prepare($sql);
            $table_model->list_add_limit(0, 1);
            $model_items = $table_model->get_list();
            if ($table_model->sql_type() == "select") {
                $list_setting = ADFO_functions_list::get_list_structure_config($model_items, $post->post_content['list_setting']);
      
            } else {
                return [];  
            }
        } else {
            return [];
        }
       
        
        if ($extend) {
            if (!$searchable) {
                $lists = $list_setting;
            } else {
                foreach ($list_setting as $key=>$pcls) {
                    if (!$searchable || ($searchable &&  $pcls->searchable != "no" && $pcls->mysql_table != '')) { 
                        $lists[$pcls->name] = $pcls;
                    }
                }
            }
        } else {
            foreach ($list_setting as $key=>$pcls) {
                if (!$searchable || ($searchable &&  $pcls->searchable != "no" && $pcls->mysql_table != '')) { 
                 
                    $lists[$pcls->name] = ($pcls->isset('title')) ? $pcls->title : $key;
                }
            }
        }
        return $lists;
    }

    /**
     * Ritorna l'elenco delle colonne di una lista con l'url request da un lato, il nome dell'altro
     * @param int $dbp_id 
     * @param bool searchable 
     * @param bool extend 
     * @return ADFO_list_setting[]|array
     * SE extend è false
     * ```json
     * {'field_name':'field_title','field_name':'...'} 
     * ```
     * Se extend è true ADFO_list_setting[]
     */
    static function get_ur_list_columns($dbp_id) {
        $list_settings = self::get_list_columns($dbp_id, true, true);
        $lists = [];
        foreach ($list_settings as $list_setting) {
            $lists[$list_setting->name_request] = $list_setting->name;
        }
        return $lists;
    }

    /**
     * Ritorna l'elenco delle chiavi primarie di una lista. I campi estratti sono gli alias!
     * @todo verificare questa nota: TODO NON funziona, vorrei che fosse salvato nella lista comunque sto lavorando su model->add_primary_ids
     * @param int $dbp_id
     * @todo Ora i parametri sono salvati in post_content['primaries'];
     * @return array [table=>primary_name, ]
     */
    static function get_primaries_id($dbp_id, $name_request = true) {
        $post  = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($post == false) {
            return [];
        }
        $sql =  @$post->post_content['sql'];
        $primaries = [];
        if ( $sql != "") {
            $table_model = new ADFO_model();
            $table_model->prepare($sql);
            $primaries = $table_model->get_pirmaries(true);
            if ($name_request) {
                $list_settings = $post->post_content["list_setting"];
                foreach ($primaries as &$n) {
                    if (isset($list_settings[$n])) {
                        $n = $list_settings[$n]->name_request;
                    }
                }
                
            }
        }
        return $primaries;
    }


    /**
     * Ritorna i valori delle primary id da una riga di una lista estratta con get_data
     * gli id di ritorno devono essere [table_alias.orgname => value, ...]
     * @param int $dbp_id
     * @param obj $row una singola riga di un get_data
     * @param string $return array|string|object
     * @return mixed
     */
    static function get_ids_value_from_list($dbp_id, $row, $return = 'string') {
        $dbp_post = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($dbp_post == false) {
            return false;
        }
        //$dbp_post->post_content->schema; 

        $id_values = (object)[];
        foreach ($row as $key => $value) {
            foreach ($dbp_post->post_content['schema'] as $field => $field_data) { 
               
                if ($field == $key && (isset( $field_data['pri']) && $field_data['pri'] == '1')) {
                    $key_id = $field_data['table'].".". $field_data['original_name'];
                    $id_values->$key_id = $value ;
                }
            }
        }

        if ($return == 'string') {
            return rtrim(strtr(base64_encode(json_encode($id_values)), '+/', '-_'), '=');
        } else if ($return == 'array') {
            return  (array) $id_values;   
        } else {
            return $id_values;
        }
    }

    


    /**
     * Ritornano i dati o il model di una lista
     * @todo ADD WHERE deve caricare gli alias dei campi che però dovrei trasformare in table_alias.field da schema! Questo per semplificare la lettura agli utenti!
     * @param [type] $dbp_id
     * @param string $return items|schema|model|schema+items|raw
     * se diverso da null e diverso da '' 
     * ritorna direttamente il campo singolo senza array
     * @param array $add_where  [[op:'', column:'',value:'' ], ... ]
     * @param string $limit
     * @param string $order_field
     * @param string $order ASC|DESC
     * @param bool  $raw_data Se false elabora i dati estratti con list_setting, altrimenti restuisce i dati così come sono stati estratti dalla query
     * @return mixed
     * @todo aggiungere i risultati lavorati dalle impostazioni oppure no.
     */
    static function get_data($dbp_id, $return = "items", $add_where = null, $limit = null, $order_field = null, $order="ASC", $raw_data = false) {
        if (!class_exists('ADFO_functions_list')) {
            ADFO_fn::require_init();
        }
        $post = false;
        $return = strtolower($return);
        if ($return == 'raw') {
            $form = new ADFO_class_form($dbp_id);
            $table_model = $form->table_model;
        } else {
            $post   = ADFO_functions_list::get_post_dbp($dbp_id);
            if ($post == false) {
                return false;
            }
            $sql =  $post->post_content['sql'];
            if ( $sql == "") return false;
            $table_model = new ADFO_model();
            $table_model->prepare($sql);
            ADFO_functions_list::add_post_type_settings($table_model, $post);
            if (isset($post->post_content['sql_order']) && $order_field == null) {
                if (isset($post->post_content['sql_order']['field']) && isset($post->post_content['sql_order']['sort'])) {
                $table_model->list_add_order($post->post_content['sql_order']['field'], $post->post_content['sql_order']['sort']);
                }
            }
            if (isset($post->post_content['sql_limit']) && $limit == null) {
                $table_model->list_add_limit(0, $post->post_content['sql_limit']);
            }
        }
        if ($add_where != null) {
            $table_model->list_add_where($add_where);
        }
        if ($limit != null) {
            $table_model->list_add_limit(0, $limit);
        }
        if ($order_field != null) {
            $table_model->list_add_order($order_field, $order);
        }
        $table_model->add_primary_ids();
        $table_model->get_list();
       
        // prevengo l'htmlentities e il substr del testo.
        if (!$raw_data && $return != 'raw') {
            $table_model->update_items_with_setting($post, false, -1);
        }
      
        $items = $table_model->items;
        foreach ($items as $iskey => $item) {
            foreach ($item as $ikey => $_) {
                if (substr($ikey,0,8) == '____af__') {
                    unset($items[$iskey]->$ikey);
                }
            }
        } 
        if (is_countable($items) && $table_model->last_error == "") {
            //items|schema|model|schema+items
            if ($return == 'items' || $return == 'raw') {
                array_shift($items);
                return $items;
            } else if ($return == 'schema') {
                $schema = array_shift($items);
                return $schema;
            } else if ($return == 'model') {
                return $table_model;
            } else {
                return $items;
            }
            
        } else{
            return false;
        }
    }
     
    /**
     * Data la lista estrae uno o più record a partire dagli ID e ritorna i dati grezzi!
     * Questi dati possono essere usati come base di partenza per salvare poi i dati.
     *
     * @param int $dbp_id
     * @param array|int $dbp_ids [alias_table.pri_key=>val, ...] per un singolo ID perché una query può avere più primary Id a causa di left join per cui li accetto tutti. Se è un integer invece lo associo al primo pri_id che mi ritorna. pri_key accetta sia il nome della colonna che il name_request
     * @param boolean  $raw_data 
     * @return \stdClass|false se torna false non bisogna esegure il template engine
     */
    static function get_detail($dbp_id, $dbp_ids, $raw_data = true) {
        if (!class_exists('ADFO_functions_list')) {
            ADFO_fn::require_init();
        }

        $dbp_post = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($dbp_post == false) {
            return false;
        }
        // qui ci devono essere tutte le chiavi primarie!
        $name_requests = self::get_primaries_id($dbp_id);
        $where = [];
        $columns = ADFO::get_ur_list_columns($dbp_id);
        if (is_object($dbp_ids)) {
            $dbp_ids = (array)$dbp_ids;   
        }
        if (is_string($dbp_ids)) {
             //provo a vedere se è una stringa
             $temp = ADFO_fn::ids_url_decode($dbp_ids);
             if (is_array($temp)) {
                $dbp_ids = $temp;
             }
             //var_dump($dbp_post->post_content["list_setting"]);
        }
        if (is_array($dbp_ids)) {
            foreach ($name_requests as $name_request) {
                $field_setting = $dbp_post->post_content["list_setting"][$columns[$name_request]];
                $query_var = "";
                if (array_key_exists($name_request, $dbp_ids))  {
                    // name_request
                    $query_var = $dbp_ids[$name_request];
                } else if (array_key_exists($columns[$name_request], $dbp_ids))  {
                    // nome della colonna
                 
                    $query_var = $dbp_ids[$columns[$name_request]];
                } else if (array_key_exists($field_setting->table.".".$field_setting->name, $dbp_ids))  {
                    // nome della colonna
                    $query_var = $dbp_ids[$field_setting->table.".".$field_setting->name];
                }
                if (absint($query_var) > 0) {
                    $where[] = ['op' => '=', 'column' => $field_setting->mysql_name, 'value' => esc_sql(absint($query_var)) ];
                }
            }
        } else {
            $name_request = reset($name_requests);
            if (isset($columns[$name_request]) && isset($dbp_post->post_content["list_setting"][$columns[$name_request]])) {
                $field_setting = $dbp_post->post_content["list_setting"][$columns[$name_request]];
                if (absint($dbp_ids) > 0) {
                    $where[] = ['op' => '=', 'column' => $field_setting->mysql_name, 'value' => esc_sql(absint($dbp_ids)) ];
                }
            }
        }
        if (count($where) == 0) {
            return false;
        }
        $results = ADFO::get_data($dbp_id, (($raw_data) ? 'raw' : 'items') , $where, 1, null, 'ASC', $raw_data);
        self::$frontend_template_engine_data[$dbp_id] = $results;
        if (is_array($results) && count($results) == 1) {
          
            return reset($results);
        } else {
            return false;
        }
        return false;
    }


    /**
     * Estrae i dati di un record come get_detail e rimuove chiavi primarie e campi calcolati
     * Questi dati possono essere usati come base di partenza per salvare poi i dati.
     * Se i dati vengono inviati poi a save data viene fatta una copia del record.
   
     * @param int $dbp_id
     * @param array|int $dbp_ids [pri_key=>val, ...] per un singolo ID perché una query può avere più primary Id a causa di left join per cui li accetto tutti. Se è un integer invece lo associo al primo pri_id che mi ritorna. pri_key accetta sia il nome della colonna che il name_request
     * @example clona un campo:
     * ```php
     * $item = ADFO::get_clone_detail(1, 3);
     * $ris = ADFO::save_data(1, $item);
     * ```
     *
     * @return \stdClass|false se torna false non bisogna esegure il template engine
     */
    static function get_clone_detail($dbp_id, $dbp_ids) {
        if (!class_exists('ADFO_functions_list')) {
            ADFO_fn::require_init();
        }

        $dbp_post = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($dbp_post == false) {
            return false;
        }
        // qui ci devono essere tutte le chiavi primarie!
        $name_requests = self::get_primaries_id($dbp_id);

        $where = [];
        $columns = ADFO::get_ur_list_columns($dbp_id);
        if (is_string($dbp_ids)) {
             //provo a vedere se è una stringa
             $temp = ADFO_fn::ids_url_decode($dbp_ids);
             if (is_array($temp)) {
                $dbp_ids = $temp;
             }
        }
        if (is_array($dbp_ids)) {
            foreach ($name_requests as $name_request) {
                $field_setting = $dbp_post->post_content["list_setting"][$columns[$name_request]];
                $query_var = "";
                if (array_key_exists($name_request, $dbp_ids))  {
                    // name_request
                 
                    $query_var = $dbp_ids[$name_request];
                } else if (array_key_exists($columns[$name_request], $dbp_ids))  {
                    // nome della colonna
                 
                    $query_var = $dbp_ids[$columns[$name_request]];
                }
                if (absint($query_var) > 0) {
                    $where[] = ['op' => '=', 'column' => $field_setting->mysql_name, 'value' => esc_sql(absint($query_var)) ];
                }
            }
        } else {
            $name_request = reset($name_requests);
            $field_setting = $dbp_post->post_content["list_setting"][$columns[$name_request]];
            if (absint($dbp_ids) > 0) {
                $where[] = ['op' => '=', 'column' => $field_setting->mysql_name, 'value' => esc_sql(absint($dbp_ids)) ];
            }
        }
        if (count($where) == 0) {
            return false;
        }
        $results = ADFO::get_data($dbp_id, 'raw' , $where, 1, null, 'ASC', true);
        self::$frontend_template_engine_data[$dbp_id] = $results;
        if (is_array($results) && count($results) == 1) {
            $detail_data = reset($results);
            $return = new stdClass;
            // rimuovo eventuali chiavi primarie.
            foreach ($detail_data as $k => $r) {
                if (!in_array($k, $name_requests)) {
                    $return->$k = $r;
                }
            }
            // rimuovo eventuali chiavi primarie originali ?! 
            $name_requests2 = self::get_primaries_id($dbp_id, false);
            foreach ($detail_data as $k => $r) {
                if (in_array($k, $name_requests2)) {
                    unset($return->$k);
                }
            }
            // rimuovo i campi calcolati
            // se non è mai stato salvato il form lo ricalcolo al volo.
            if (!is_array($dbp_post->post_content["form"]) || count($dbp_post->post_content["form"]) == 0 ) {
                $dbp_post->post_content["form"] = self::get_save_data_structure($dbp_id, true);
            }
            
            foreach ($dbp_post->post_content["form"] as $field) {
                $field_name =  $field["name"];
                if (property_exists($return, $field_name) && $field["form_type"] == "CALCULATED_FIELD") {
                    unset($return->$field_name);
                }
            }
            return $return;
        } else {
            return false;
        }
        return false;
    }

    /**
     * Ritorna la struttura della classe get_form per il salvataggio dei dati
     * @param int $dbp_id The id of the list
     * @param boolean $default_post_content_form Se true ritorna la struttura di post_content['form'] 
     * Se non è stato ancora mai salvato il tab form post_content['form'] ritorna un array vuoto!
     * @return array
     */
    static function get_save_data_structure($dbp_id, $default_post_content_form = false) {
        if (!class_exists('ADFO_class_form')) {
            ADFO_fn::require_init();
        }
        $form = new ADFO_class_form($dbp_id);
        if (!$default_post_content_form) {
            list($settings, $_) = $form->get_form();
            return $settings;
        } else {
            list($settings, $_) = $form->get_form(false);
            $form_new = [];
            foreach ($settings as $setting_block) {
                foreach ($setting_block as $key => $setting) {
                    $temp = [ "note"=> "","options"=> "","required"=> "","custom_css_class"=> "","default_value"=> "","js_script"=> "", "custom_value"=> ""
             ];
                    $sett = $setting->get_array();
                    if ($key != '_dbp_alias_table_') {
                        foreach ($sett as $sk => $st) {
                            $temp[$sk] = $st;
                        }
                        $temp['name'] = $temp['label'];
                        $form_new[] = $temp;
                    }
                }
            }
            return $form_new;
        }
    }

    /**
     * Salva i dati a partire da un ID o una query. 
     * Per fare l'update devono essere inserite le chiavi primarie
     * @param String $dbp_id è l'id della lista, ma accetta anche una stringa con una query di select
     * @param Array $data i Dati da aggiornare hanno la stessa struttura della query del select!
     * @param Boolean $use_wp_fn Se usare le funzioni di wordpress per gli articoli e gli utenti 
     * wp_update_post & wp_update_user quando si aggiornano/creano utenti e post
     * @example
     * ```php
     * $item = ADFO::get_detail(1, 3);
     * $item->my_field = "modified";
     * $ris = ADFO::save_data(1, $item);
     * ```
     * @return array
     */
    static function save_data($dbp_id, $data, $use_wp_fn = true) {
        if (!class_exists('ADFO_class_form')) {
            ADFO_fn::require_init();
        }
        if (is_array($data)) {
            foreach ($data as $d) {
                if (!is_object($d)) {
                   return false; 
                }
            }
        }
        $form = new ADFO_class_form($dbp_id);
        if (is_a($data, 'stdClass')) {
            $data = [$data];
        }
        $result = $form->save_data($data, $use_wp_fn);
        return $result;
    }

    /**
     * Ritorna la stringa per l'id della stringa.
     * will be removed in 1.9.0
     * @param number $post_id
     * @param object $item
     * return string|false 
     * @DEPRECATED since 1.8.0
     * @see ADFO::get_ids_value_from_list
     */
    static function get_id_string($post_id, $item) {
        if (!is_object($item)) return false;
        if (!class_exists('ADFO_class_form')) {
            ADFO_fn::require_init();
        }
        $post        = ADFO_functions_list::get_post_dbp($post_id);
        return  self::get_ids_value_from_list($post_id, $item);
    }

    /**
	 * Dati gli id di un record di una lista e dei dati da aggiornare nel formato di una lista, torna l'array per salvare i dati con save_data
     * @since 1.8.0
     * @param int $dbp_id The id of the list
     * @param array $ids gli id della lista
     * @param object $item i dati della lista da aggiornare
	 * @return array elenco di dati per il form o il salvataggio
	 */
	static public function update_list_to_form($dbp_id, $ids, $item) {
        if (!class_exists('ADFO_class_form')) {
            ADFO_fn::require_init();
        }
        $new_item = ADFO::get_detail($dbp_id, $ids);
      
        $post = ADFO_functions_list::get_post_dbp($dbp_id);
        if ($post == false) {
            return false;
        }
        $post_content = $post->post_content;
        $list_setting = $post_content["list_setting"];
        //var_dump ($list_setting);
        //$new_item = ADFO_functions_list::convert_id_table( $dbp_id, $item); 
        $form = new ADFO_class_form($dbp_id);
        list($settings, $_) = $form->get_form();
		//var_dump ($settings);
        foreach ($settings as $key=>$setting) {
            foreach ($setting as $field => $setting_field) {
                $add = false;
                foreach ($list_setting as $list_field => $list_s) {
                    $add = true;
                    if (isset($item->$list_field) && $list_s->table == $setting_field->table && $list_s->orgname == $setting_field->name ) {
                        $new_item->$field = $item->$field;
                    } 
                } 
                if (!$add) {
                     if (isset($setting_field->default_value ) && $setting_field->default_value != "" ) {
                        $new_item->$field = $setting_field->default_value;
                        // se è un form_type calcuated field metto custom value
                    } else if (isset($setting_field->form_type ) && $setting_field->form_type == "calculated_field" && (!isset($new_item->$field) || $new_item->$field == "") ) {
                        $new_item->$field = $setting_field->custom_value;
                    }
                }
            }
        }
		
		return $new_item;
	}

    /**
     * Disegna i bottoni per l'esportazione dei record
     * l'esportazione dei record in sql vengono interpretati come se ogni lista fosse una sola tabella. Questo per nascondere nel frontend la vera struttura del db.
     * @since 1.8.0
     * @param int post_id 
     * @param string $format csv|sql 
     */
    static function draw_export_btn($post_id, $title ="download", $format="csv", $btn_class="") {
        $nonce = wp_create_nonce( 'adfo_frontend_export_data' );
        ?><a href="<?php echo esc_url(admin_url('admin-ajax.php?action=adfo_frontend_export_data&adfo_id='. base64_encode(absint($post_id)).'&format='. esc_attr($format).'&nonce='. $nonce)); ?>" class="<?php echo esc_attr($btn_class); ?>"><?php echo $title; ?></a><?php
    }
}
