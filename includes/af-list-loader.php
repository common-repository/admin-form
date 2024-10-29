<?php
/**
 * Gestisco il filtri e hook per le liste
 */
namespace admin_form;

class  ADFO_list_loader {

    public function __construct() {
        // Questa una chiamata che deve rispondere un csv
        add_action( 'admin_post_dbp_create_list', [$this, 'create_list']);	
        add_action('admin_head', [$this, 'echo_dbp_id_variables_script']);
        add_action( 'wp_ajax_af_get_list_columns', [$this, 'get_list_columns']);
        add_action( 'wp_ajax_af_get_table_columns', [$this, 'get_table_columns']);
    }
    /**
     * Crea una nuova lista (POST)
     */
    function create_list() {
        global $wpdb, $ADFO_admin_loader;
        ADFO_fn::require_init();

        // SE c'è una query la scrivo
        if (!isset($_REQUEST['choose_tables_query'])) {
            wp_redirect( admin_url("admin.php?page=admin_form&section=list-all&msg=create_list_error"));
            die();
        }
        $title = sanitize_text_field($_REQUEST['new_title']);
       // TODO: if (!is_admin()) return;
        $create_list = array(
            'post_title'    => $title,
            'post_content'  => '',
            'post_excerpt'  => wp_kses_post(wp_unslash($_REQUEST['new_description'])),
            'post_status'   => 'publish',
            'comment_status' =>'closed',
            'post_author'   => get_current_user_id(),
            'post_type' => 'dbp_list'
        );
        $id = wp_insert_post($create_list);
        if (is_wp_error($id) || $id == 0) {
            wp_redirect( admin_url("admin.php?page=admin_form&section=list-all&msg=create_list_error"));
            die();
        } else {
            $post = ADFO_functions_list::get_post_dbp($id);
            if ($post == false) {
                wp_redirect( admin_url("admin.php?page=admin_form&section=list-all&msg=create_list_error"));
                die();
            }
            if ($_REQUEST['choose_tables_query'] == 'create_new_table') {
                $table_name = str_replace("_","", ADFO_fn::clean_string(sanitize_text_field($_REQUEST['new_title'])));
                // il nome della tabella
                $table_name = str_replace($wpdb->prefix, '', ADFO_fn::sanitize_key($table_name));
                $count = 0;
                if ($table_name == "") {
                    $table_name = uniqid();
                }
                $table_name = $wpdb->prefix."adfo_".$table_name;
                $table_name_temp = $table_name;
                while (ADFO_fn::exists_table($table_name)) {
                    $count ++;
                    $table_name = $table_name_temp ."_". $count;
                }
                $table_as = ADFO_fn::get_table_alias($table_name);

                $charset = $wpdb->get_var('SELECT @@character_set_database as cs');
                if ($charset == "") {
                    $charset = 'utf8mb4';
                }
                $ris = $wpdb->query('CREATE TABLE `'.ADFO_fn::sanitize_key($table_name).'` ( `adfo_id` INT UNSIGNED NOT NULL AUTO_INCREMENT , PRIMARY KEY (`adfo_id`))   ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET='.$charset);
                
                $sql = 'SELECT `'.$table_as.'`.* FROM `'.$table_name.'` `'.$table_as.'`';
                // METTO LA TABELLA IN DRAFT MODE!
                ADFO_fn::update_dbp_option_table_status($table_name, 'DRAFT');
            } else {
                if ($_REQUEST['choose_tables_query'] == 'create_new_post_type') {
                    $table_name = $wpdb->posts;
                } else {
                    $table_name = sanitize_text_field($_REQUEST['mysql_table_name']);
                }
                $table_as = ADFO_fn::get_table_alias($table_name);
                if ($table_name == $wpdb->posts ) {
                    // impost i setting_post_form
                    $fields = ADFO_functions_list::setting_post_form($table_as, $table_name);
                    foreach ($fields as $field) {
                        $post->post_content['form'][] = $field->get_array();
                    }
                    $lists = ADFO_functions_list::setting_post_structure($table_as, $table_name);
                    foreach ($lists as $key=>$list) {
                        $post->post_content['list_setting'][$key] = $list->get_for_saving_in_the_db();
                    }                    
                    $post->post_content['form_table'] = ['pos'=>['frame_style'=>'HIDDEN', 'order'=>0]];
                }
            }
           
            $post->post_content['sql_from'] = [$table_as => ADFO_fn::sanitize_key($table_name)];
            
            $table_model = new ADFO_model($post->post_content['sql_from']);
            $table_model->list_add_limit(0, 1);
            $items = $table_model->get_list();
            if (isset($post->post_content['list_setting'])) {
                $list_setting = $post->post_content['list_setting'];
            } else {
                $list_setting = [];
            }
            $setting_custom_list =  ADFO_functions_list::get_list_structure_config($items, $list_setting);
            $table_model->remove_limit();
            $table_model->remove_order();
            $post->post_content['sql'] = $table_model->get_current_query();

            $post->post_content['list_setting'] = [];
            foreach ($setting_custom_list as $column_key => $list) {
                $post->post_content['list_setting'][$column_key] =  $list->get_for_saving_in_the_db();
                $post->post_content['list_setting'][$column_key]['inherited'] = 1;
            }
            // Verifico se c'è una tabella metadata
            $return_table = ADFO_class_metadata::find_metadata_tables($table_model);
            if (count($return_table) == 1) {
                $post->post_content['sql_metadata_table'] = key($return_table);
            }
          
            $post->post_content['frontend_view'] = ['type'=>'TABLE_BASE', 'table_style_color' => 'light', 'table_pagination_position'=>'down', 'table_pagination_style'=>'numeric', 'table_sort'=>'icon1', 'table_search'=>'simple','table_update'=>'ajax', 'detail_type'=>'TABLE'];
            /**
             * POST TYPE
             * @since 1.7.0 Aggiunti i tipi di form.
             * type = choose_table_from_db|create_new_post_type|create_new_table
             * post_type = [name =>' ']
             */
            // aggiungo choose_tables_query sanificato
            $post->post_content['type'] = sanitize_text_field($_REQUEST['choose_tables_query']);
            // post_type
            if (($post->post_content['type'] == 'choose_table_from_db' && isset($_REQUEST['mysql_table_name']) && $_REQUEST['mysql_table_name'] == $wpdb->posts)) {
                if (trim(sanitize_text_field($_REQUEST['new_post_type'])) != "") {
                    $post_type = ADFO_functions_list::generate_post_type($_REQUEST['new_post_type']);
                    $post->post_content['post_type'] = ['name'=>$post_type];
                }
            } else if ($post->post_content['type'] == 'create_new_post_type') {
                $post_type = ADFO_functions_list::generate_post_type($title);
                $post->post_content['post_type'] = ['name'=> $post_type];
            }
            // 
            /**
             * POST TYPE: Verifico se esiste già il post type
             * @since 1.7.0 Aggiunti i tipi di form.
             * I post_type da registrare li salvo il una options perché li devo caricare sempre 
             * soprattutto per il frontend
             */
            if (isset($post->post_content['post_type']['name']) && $post->post_content['post_type']['name'] != "") {
                $adfo_post_types = get_option('adfo_post_types', []);
                if (!isset($adfo_post_types[$post->post_content['post_type']['name']] )) {
                    $adfo_post_types[$post->post_content['post_type']['name']] = ['adfo_ids' =>[]];
                }
                if(!post_type_exists($post->post_content['post_type']['name'])) {
                    $adfo_post_types[$post->post_content['post_type']['name']]['slug'] = $post->post_content['post_type']['name'];
                    $adfo_post_types[$post->post_content['post_type']['name']]['adfo_ids'] = [$id];  
                } else {
                    $adfo_post_types[$post->post_content['post_type']['name']]['adfo_ids'][] = $id;
                }
                update_option('adfo_post_types', $adfo_post_types, true);
                $ADFO_admin_loader->init_post_type();
                flush_rewrite_rules();
            }
            // Salvo le chiavi primarie e lo schema
            $post->post_content['primaries'] = $table_model->get_pirmaries();	
            $table_model->add_primary_ids();
            $table_model->list_add_limit(0,1);
			$table_model->get_list();
            $table_model->update_items_with_setting($post);
			$post->post_content['schema'] = reset($table_model->items);

            $dbp_admin_show  = ['page_title'=>sanitize_text_field($title), 'menu_title'=>sanitize_text_field($title), 'menu_icon'=>'dashicons-database', 'menu_position' => 120, 'capability'=>'dbp_manage_'.$id, 'slug'=> 'dbp_'.$id, 'show' => 1, 'status'=>'publish'];
            add_post_meta($id,'_dbp_admin_show', $dbp_admin_show, true);
            ADFO_fn::save_list_config($id, $post->post_content);
            $role = get_role( 'administrator' );
            $role->add_cap( 'dbp_manage_'.$id, true );
            // ridirigo alla gestione della form 
            wp_redirect( admin_url("admin.php?page=admin_form&section=list-form&msg=list_created&dbp_id=".$id));
            die();
        }
    }

    /**
     * Aggiungo l'id della lista se è presente così da poterla richiare negli ajax senza dovermela passare ogni volta
     */
    function echo_dbp_id_variables_script() {
       
		if (isset($_REQUEST['dbp_id']) && $_REQUEST['dbp_id'] > 0 && isset($_REQUEST['page']) &&  (substr($_REQUEST['page'],0,3) == 'dbp' || $_REQUEST['page'] == "admin_form")) {
			 ?>
<script> var dbp_global_list_id = <?php echo absint($_REQUEST['dbp_id']); ?>;</script>
			 <?php 
		}
	 }

    
    
    /**
     * Ritorna l'elenco delle colonne di una tabella
     * @deprecated?
     */
    function get_list_columns() {
        ADFO_fn::require_init();
        $dbp_id = absint($_REQUEST['dbp_id']);
        $list = ADFO::get_list_columns($dbp_id, ADFO_fn::get_request('searchable',true, 'boolean'));
    
        $primaries = ADFO::get_primaries_id($dbp_id);
        //$dbp_id;
        $json_result = ['list' => $list, 'rif' => $_REQUEST['rif'], 'pri'=>array_shift($primaries)];
        wp_send_json($json_result);
		die();
    }

    /**
     * Ritorna l'elenco delle colonne di una tabella
     */
    function get_table_columns() {
        ADFO_fn::require_init();
        
        $table_rif = ADFO_Fn::sanitize_key($_REQUEST['table_rif']);
        $list = ADFO_fn::get_table_structure($table_rif, true);
        $primary = ADFO_fn::get_primary_key($table_rif);
        $pos = array_search($primary, $list);
        if ($pos !== false) {
            unset($list[$pos]);
        }
        $json_result = ['list' => $list, 'rif' => $_REQUEST['rif'], 'pri'=>$primary];
        wp_send_json($json_result);
		die();
    }
}

new ADFO_list_loader();