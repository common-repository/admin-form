<?php

/**
 * Il controller amministrativo specifico per le liste (page=admin_form)
 * @internal
 */
namespace admin_form;

if (!defined('WPINC')) die;
class  ADFO_list_admin 
{
	/**
	 * @var Int $max_show_items Numero massimo di elementi da caricare per un select
	 */
	var $max_show_items = 500; 
	/**
	 * @var String $last_error
	 */
	var $last_error = "";
    /**
	 * Viene caricato alla visualizzazione della pagina
     */

    function controller() {
		
		wp_enqueue_style( 'admin-form-css' , plugin_dir_url( __FILE__ ) . 'css/admin-form.css',[],ADFO_VERSION);
		wp_register_script( 'admin-form-all', plugin_dir_url( __FILE__ ) . 'js/admin-form-all.js',[],ADFO_VERSION);
		wp_add_inline_script( 'admin-form-all', 'dbp_cookiepath = "'.esc_url(COOKIEPATH).'";'."\n  var dbp_cookiedomain =\"".esc_url(COOKIE_DOMAIN).'";', 'before' );
		wp_enqueue_script( 'admin-form-all' );

		// $dbp = new ADFO_fn();
		ADFO_fn::require_init();
		$temporaly_files = new ADFO_temporaly_files();
		/**
		 * @var $section Definisce il tab che sta visualizzando
		 */
		$section =  isset($_REQUEST['section']) ? sanitize_text_field($_REQUEST['section']) : 'home';
		/**
		 * @var $action Definisce l'azione
		 */
		$action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'string';
		//print $section." ".$action;	
		$msg =  $msg_error = '';
		if (isset($_COOKIE['dbp_msg'])) {
			$msg = wp_kses_post( wp_unslash($_COOKIE['dbp_msg']));
		}
		if (isset($_COOKIE['dbp_error'])) {
			$msg_error = wp_kses_post( wp_unslash($_COOKIE['dbp_error']));
		}
		
		switch ($section) {
			case 'list-sql-edit' :
				$this->list_sql_edit();
				break;
			case 'list-browse' :
				$this->list_browse();
				break;
			case 'list-structure' :
				$this->list_structure();
				break;
			case 'list-setting' :
				$this->list_setting();
				break;
			case 'list-example' :
				$this->list_example();
				break;
			case 'list-form' :
				$this->list_form();
				break;
			default :
				do_action('adfo_list_admin_controller', $section);
				if ( apply_filters('adfo_list_admin_controller_show_list_all', true, $section)) {
					$this->list_all();
				}
				break;
		}
	}


    private function list_all() {
		global $wpdb;
		wp_register_script( 'af-new-list', plugin_dir_url( __FILE__ ) . 'js/af-new-list.js',false, ADFO_VERSION);
		wp_add_inline_script( 'af-new-list', 'dbp_admin_post = "'.esc_url( admin_url("admin-post.php")).'";', 'before' );
		wp_enqueue_script( 'af-new-list' );
		
        // $dbp = new ADFO_fn();
		$section = isset($_REQUEST['section']) ? sanitize_text_field($_REQUEST['section']) : 'list-all';
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'string';
		$msg = $msg_error = "";
		$id = isset($_REQUEST['dbp_id']) ? absint($_REQUEST['dbp_id']) : 0;
		if ($action == "publish-list" ) {
			if ($id > 0) {
				$dbp_admin_show = get_post_meta($id,'_dbp_admin_show', true);
				if(is_countable($dbp_admin_show)) {
					$dbp_admin_show['status'] = 'publish';
					update_post_meta($id, '_dbp_admin_show', $dbp_admin_show);
				}
				wp_publish_post($id);
				$msg = __('List published','admin_form');
			}
		}
		if ($action == "remove-list" ) {
			if ($id > 0) {
				wp_delete_post($id, true);
				$msg = __('List removed','admin_form');
				// verifico se l'id era tra i post_type
				$dbp_list_config = get_option('adfo_post_types');
				if (is_array($dbp_list_config)) {
					$unregister = false;
					foreach ($dbp_list_config as $key => $value) {
						if (isset($value['adfo_ids']) && in_array($id, $value['adfo_ids'])) {
							// rimuovo l'id dalla lista
							$dbp_list_config[$key]['adfo_ids'] = array_diff($dbp_list_config[$key]['adfo_ids'], array($id));
							if (count($dbp_list_config[$key]['adfo_ids']) == 0) {
								// se non ci sono più id rimuovo la lista
								unset($dbp_list_config[$key]);
								unregister_post_type($key);
								$unregister = true;
							}
						}
					}
					if ($unregister) {
						flush_rewrite_rules();
					}
					update_option('adfo_post_types', $dbp_list_config);
				}
			}
			$action = "show-trashed";
		}
		if ($action == "trash-list" ) {
			if ($id > 0) {
				$dbp_admin_show = get_post_meta($id,'_dbp_admin_show', true);
				
				if(is_countable($dbp_admin_show)) {
					$dbp_admin_show['status'] = 'trash';
					update_post_meta($id, '_dbp_admin_show', $dbp_admin_show);
				}
				wp_trash_post($id);
				$msg = __('List trashed','admin_form');
			}
		}
		if ($action == "clone-list" ) {
			if ($id > 0) {
				$dbp_list_config = get_post_meta($id,'_dbp_list_config', true);
				$post = get_post($id);
				$create_list = array(
					'post_title'    => $post->post_title."_clone",
					'post_content'  => '',
					'post_excerpt'  => 'List copied from id: '.$id,
					'post_status'   => 'publish',
					'comment_status' =>'closed',
					'post_author'   => get_current_user_id(),
					'post_type' => 'dbp_list'
				);
				$new_id = wp_insert_post($create_list);
				add_post_meta($new_id, '_dbp_list_config', $dbp_list_config);
				//add_post_meta($new_id, '_dbp_admin_show', $dbp_list_config);
				$msg = sprintf(__("The list has been copied. The original is the list with id: %s",'admin_form'), $id);
			}
		}
		if ($action == "show-trashed" ) {
			$args = array(
				'post_status' => 'trash',
				'numberposts' => -1,
				'post_type'   => 'dbp_list'
			);
		} else {
			$args = array(
				'post_status' => 'publish',
				'numberposts' => -1,
				'post_type'   => 'dbp_list'
			);
		}
		
		$post_count_sql = $wpdb->get_results("SELECT post_status, COUNT( * ) AS num_posts FROM ".$wpdb->posts." WHERE post_type = 'dbp_list' GROUP BY post_status");
	
		$post_count = ['publish'=>0,'trash'=>0];
		if (count($post_count_sql) > 0) {
			foreach ($post_count_sql as $p) {
				$post_count[$p->post_status] = $p->num_posts;
			}
		}
		$list_page = get_posts( $args );
		foreach ($list_page as $key=>$post) {
			$post_content = ADFO_functions_list::convert_post_content_to_list_params($post->post_content);
			if (isset($post_content['sql_filter']) && is_countable($post_content['sql_filter'])) {
				$shortcode_param = [];
				foreach ($post_content['sql_filter'] as $filter) {
					if (isset($filter['value'])) {
						$shortcode_param = array_merge($shortcode_param, ADFO_functions_list::get_pinacode_params($filter['value']));	
					}
				}
				$list_page[$key] = (object)(array)$list_page[$key] ;
				if (count($shortcode_param) > 0) {
					$list_page[$key]->shortcode_param = " ".implode(", ", $shortcode_param)."";
				} else {
					$list_page[$key]->shortcode_param = "";
				}
			}
			$list_page[$key]->post_content = $post_content;
		}
		
        $render_content = "/af-content-list-all.php";
        require(dirname( __FILE__ ) . "/partials/af-page-base.php");
    }

	/**
	 * Modifica la query di una lista
	 */
	private function list_sql_edit() {
		global $wp_roles, $wp_filter, $wpdb;
		wp_enqueue_script( 'af-sql-editor-js', plugin_dir_url( __FILE__ ) . 'js/af-sql-editor.js',[],ADFO_VERSION);
		wp_enqueue_script( 'database-list-sql-js', plugin_dir_url( __FILE__ ) . 'js/af-list-sql.js',[],ADFO_VERSION);
		wp_enqueue_script( 'jquery-ui-sortable');
        // $dbp = new ADFO_fn();
		$section = isset($_REQUEST['section']) ? sanitize_text_field($_REQUEST['section']) : 'list-all';
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'string';
		$msg_error = "";
		$msg = "";
		$ris_list_saved = NULL;
		$show_query = false;
		if ($action == 'list-sql-save') {
			list($ris, $show_query) = $this->list_sql_save();
			if ($ris === true) {
				$ris_list_saved  = true;
				$msg = 'saved';
			} else {
				$ris_list_saved  = false;
				$msg_error = $ris;
			}
		}
		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
		$render_content = "/af-content-list-sql-edit.php";
		$sql = "";
		$info_rows = [];
		$sql_order = ['field'=>'', 'sort'=>''];
		$sql_filter = [];
		$post_allow_delete = [];
		$sql_limit = 100;
		if ($id > 0) {
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			$list_title = $post->post_title;
			$post_excerpt = $post->post_excerpt;
			$sql = @$post->post_content['sql'];
			if (isset($post->post_content['sql_limit'])) {
				$sql_limit = (int)$post->post_content['sql_limit'];
			}
			if (isset($post->post_content['sql_order']) && is_array($post->post_content['sql_order'])) {
				$sql_order = $post->post_content['sql_order'];
			} 
			if (isset($post->post_content['sql_filter']) && is_array($post->post_content['sql_filter'])) {
				$sql_filter  = $post->post_content['sql_filter'];
			}
			
			if (isset($post->post_content['delete_params']) && is_a($post->post_content['delete_params'], 'admin_form\dbpDs_list_delete_params')) {
				$post_allow_delete = $post->post_content['delete_params']->remove_tables_alias;
			}
			$sql_metadata_table = isset($post->post_content['sql_metadata_table']) ? $post->post_content['sql_metadata_table'] : '' ;
		} else {
			$msg_error = __('You have not selected any list', 'admin_form');
		}
		if ($sql_limit < 1) {
			$sql_limit = 100;
		}
		$table_model = new ADFO_model();
		$table_model->prepare($sql);
		
		if ($sql != "") {
			if ($table_model->sql_type() != "select") {
				$show_query = true;
				$msg_error = sprintf(__('Only a single select query is allowed in the lists %s', 'admin_form'), $sql);
			} else {
				$table_model->list_add_limit(0, 1);
				$items = $table_model->get_list();
				if ($table_model->last_error != "") {
					$show_query = false;
					$msg_error = __('<h2>Query error:</h2>'.$table_model->last_error, 'admin_form');
				} else {
					if ($msg != "") {
						$msg .= "<br>";
					}
					
					$info_rows = $table_model->get_all_fields_from_query();
					$info_rows = array_merge([''=>'Select column'], $info_rows);
					$info_ops =  [ '=' => __('= (Equals)',  'admin_form'),
					'!=' => __('!= (Does Not Equal)',  'admin_form'),
					'>'  => __('> (Greater Than)',  'admin_form'),
					'>='  => __('>= (Greater or Equal To)',  'admin_form'),
					'<'  => __('< (Less Than)',  'admin_form'),
					'<='  => __('<= (Less or Equal To)',  'admin_form'),
					'LIKE'  => __('%LIKE% (Search Text)',  'admin_form'),
					'LIKE%'  => __('LIKE% (Text start with)',  'admin_form'),
					'NOT LIKE'  => __('NOT LIKE (Exclude Text)',  'admin_form'),
					'IN'  => __('IN (Match in array)',  'admin_form'),
					'NOT IN'  => __('NOT IN (Not found in array)',  'admin_form')
					];

					$pinacode_fields = ['data'=>[], 'params'=>['example'], 'request'=>['example'], 'total_row'];
					$items =  ADFO_functions_list::get_list_structure_config($table_model->items, $post->post_content['list_setting']);
					foreach ($items as $key=>$item) {
						$pinacode_fields['data'][] = $item->name;
					}
					ADFO_fn::echo_pinacode_variables_script($pinacode_fields);
				}
			}
		} else {
			$show_query = false;
			$msg_error = __('First you need to write a query to extract the list data. ', 'admin_form');
		}

		$metadata_tables = ADFO_class_metadata::find_metadata_tables($table_model);

		$dbp_admin_show = get_post_meta($id,'_dbp_admin_show', true);
		if(!is_countable($dbp_admin_show)) {
			$dbp_admin_show = [];
		}
		if (!isset($dbp_admin_show['menu_icon'])) {
			$dbp_admin_show['menu_icon'] = 'dashicons-database-view';
		}
		if (!isset($dbp_admin_show['menu_position'])) {
			$dbp_admin_show['menu_position'] = 101;
		}
        require(dirname( __FILE__ ) . "/partials/af-page-base.php");
    }

	/**
	 * Salva la query di una lista
	 * @return array ([string|true], bool)
	 */
	private function list_sql_save() {
		global $wp_roles;
		
		// $dbp = new ADFO_fn();
		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
		// Override del salvataggio
		// ATTENZIONE QUESTO VUOL DIRE CHE SE C'È LA VERSIONE PRO SOVRASCRIVE TUTTA LA FUNZIONE!
		/*
		$custom_save = ['return'=>[], 'saved'=>false, 'show_query' => false, 'dbp_id' => $id ];
		$custom_save = apply_filters( 'dbp_list_sql_save', $custom_save );
		if ($custom_save['saved'] === true) {
			return [$custom_save['return'], $custom_save['show_query']];
		}
		*/
		if (!isset($_REQUEST['dbp_list_form_nonce']) || !wp_verify_nonce($_REQUEST['dbp_list_form_nonce'], 'dbp_list_form')) {
			return [ __('Security check failed', 'admin_form'), false];
		}
	
		$return = [];
		$error= "";
		$error_query = "";
		if ($id > 0) {
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			if (!isset($post->post_content['sql_from']) || !is_array($post->post_content['sql_from']) || (!isset($post->post_content['sql']) || $post->post_content['sql'] == '' )) {
				$error = __('The query is required', 'admin_form');
			}
			// TODO se metto il limit nella query vorrei che passasse qui!
			$post->post_content['sql_limit'] = absint($_REQUEST['sql_limit']);
			if ($_REQUEST['sql_order']['field'] != "") {
				$post->post_content['sql_order'] = ['field'=>sanitize_text_field($_REQUEST['sql_order']['field']),'sort'=>sanitize_text_field($_REQUEST['sql_order']['sort'])] ;
			} else {
				if (isset($post->post_content['sql_order'])) {
					unset($post->post_content['sql_order']);
				}
			}
			if (isset($_REQUEST['sql_metadata_table'])) {
				$post->post_content['sql_metadata_table'] = sanitize_text_field($_REQUEST['sql_metadata_table']);
			}
			// VERSIONE PRO
			if (isset($_REQUEST['custom_query']) && $_REQUEST['custom_query'] !== '') {
				
				$table_model = new ADFO_model();
				$table_model->prepare(wp_unslash($_REQUEST['custom_query']));
				if ($table_model->sql_type() != "select") {
					$error_query = sprintf(__('Only a single select query is allowed in the lists %s', 'admin_form'), $table_model->get_current_query());
					$show_query = true;
				} else {
					$primaries = $table_model->add_primary_ids();
					$table_model->list_add_limit(0, 1);
					$items = $table_model->get_list();
					//  se aggiungo qualche primary id, lo metto hidden in list view formatting!
					if (count($primaries) > 0) {
						foreach ($primaries as $k_pry => $v_pry) {
							$setting_custom_list =  ADFO_functions_list::get_list_structure_config($items,  $post->post_content['list_setting']);
							foreach ($setting_custom_list as $sett_key => $setting) {
								if ($setting->orgname == $v_pry && $setting->table == $k_pry) {
									$post->post_content['list_setting'][$sett_key] = (new ADFO_list_setting())->set_from_array(['name'=>$k_pry, 'title' => $v_pry, 'format_values' => '', 'inherited' => 1, 'toggle' => 'HIDE']);
									break;
								}
							}
						}
					}
					if ($table_model->last_error == "") {
						$post->post_content['sql'] = html_entity_decode($table_model->get_current_query());
					} else {
						$error_query = sprintf(__("I didn't save the query because it was wrong!.<br><h3>Error:</h3>%s<h3>Query:</h3>%s",'admin_form'), $table_model->last_error, nl2br(wp_kses_post( wp_unslash( $_REQUEST['custom_query'] )) ));
					}
				}

				$post->post_content['delete_params'] = ['remove_tables_alias'=>[]];
				if (!isset($_REQUEST['remove_tables_alias']) || !is_array($_REQUEST['remove_tables_alias'])) {
					$remove_tables_alias_request = [];
				} else {
					$remove_tables_alias_request = ADFO_fn::sanitize_text_recursive($_REQUEST['remove_tables_alias']);	
				}
				foreach ($remove_tables_alias_request as $remove_tables_alias=>$allow) {
					$post->post_content['delete_params']['remove_tables_alias'][sanitize_text_field($remove_tables_alias)] = absint($allow);
				}

				// Verifico che nella query non vengano cambiati gli alias delle tabelle
				if ($error_query == "") {
					$from_query = $table_model->get_partial_query_from(true);
					if (isset($post->post_content['sql_from'])) {
						foreach ($post->post_content['sql_from'] as $table_alias => $table) {
							$find = false;
							foreach ($from_query as $f) {
								// Ho invertito $f[1] con $f[0] così funziona, da verificare.
								if ($f[1] == $table_alias && $f[0] == $table) {
									$find = true;
									break;
								}
							}
							if (!$find) {
								$return[] = sprintf(__('The settings have been saved, but you have changed the name of a query table (%s as %s). <br>This can cause an unexpected operation in the management of the list. <br>In these cases it is preferable to create a new form.', 'admin_form'), $table, $table_alias);
							}
						}
					} 
					$from = [];
					foreach ($from_query as $f) {
						$from[$f[1]] = $f[0]; 
					}
					$post->post_content['sql_from'] = $from;
				
					// Salvo le chiavi primarie e lo schema
					$post->post_content['primaries'] = $table_model->get_pirmaries();
					
					ADFO_functions_list::add_post_type_settings($table_model, $post);
					$table_model->update_items_with_setting($post);
					$post->post_content['schema'] = reset($table_model->items);
				} else {
					if (isset($post->post_content['primaries'])) unset($post->post_content['primaries']);
					if (isset($post->post_content['schema'])) unset($post->post_content['schema']);
					if (isset($post->post_content['sql_from'])) unset($post->post_content['sql_from']);
				}


			} else {
				// versione non Pro
				if (isset($post->post_content['sql'])) {
					$table_model = new ADFO_model();
					$table_model->prepare($post->post_content['sql']);
				} else {
					$table_model = new ADFO_model($post->post_content['sql_from']);
				}
				//Allow delete 
				/**  @TODO: questo non era solo per la versione pro?!? */
				$post->post_content['delete_params'] = [];
				foreach ($post->post_content['sql_from'] as $table_alias=>$_) {
					$post->post_content['delete_params']['remove_tables_alias'][sanitize_text_field($table_alias)] = 1;
				}
			}
			$table_model->list_add_limit(0, 1);
			$items = $table_model->get_list();
			if (!isset($post->post_content['sql_from'])) {
				$from_query = $table_model->get_partial_query_from(true);
				$from = [];
				foreach ($from_query as $f) {
					$from[$f[1]] = $f[0]; 
				}
				$post->post_content['sql_from'] = $from;
			}
			$table_model->add_primary_ids();
			$table_model->update_items_with_setting($post);
			if (is_array($table_model->items)) {
				$post->post_content['schema'] = reset($table_model->items);
			}
			// DEVO RICALCOLARE form_table che mi serve per capire se ci sono i bottoni dell'edit e del delete
			// la configurazione delle tabelle
			if (!isset($post->post_content['form_table']) || !is_array($post->post_content['form_table'])) {
				$post->post_content['form_table'] = [];
			}
			
			$post->post_content['show_desc'] = isset($_REQUEST['show_desc']) ? absint($_REQUEST['show_desc']) : 0;
			
			$style_list = ['WHITE','BLUE','GREEN','RED','YELLOW','PURPLE','BROWN'];
			$count_order = 0;
			foreach ($post->post_content['sql_from'] as $table_alias => $_single_from) {
				if (array_key_exists($table_alias, $post->post_content['form_table'])) {
					continue;
				} 
				$post->post_content['form_table'][$table_alias] = [
					'allow_create' => 'SHOW',	
					'show_title' => 'SHOW',
					'frame_style' => $style_list[rand(0,6)],
					'title' => '',
					'description' => '', 	
					'module_type' =>'EDIT',
					'order' => $count_order++
				];
			}
			//unset($post->post_content['sql']);
			
			$post->post_content['sql_filter'] = [];
			
			if (isset($_REQUEST['sql_filter_field']) && is_countable($_REQUEST['sql_filter_field'])) {
				foreach ($_REQUEST['sql_filter_field'] as $key=>$field) {
					$field = sanitize_text_field($field);
					$key = sanitize_text_field($key);

					if ($field != "" && trim($_REQUEST['sql_filter_val'][$key]) != "") {
						$post->post_content['sql_filter'][] = [
							'column' => $field, 
							'op' => sanitize_text_field($_REQUEST['sql_filter_op'][$key]), 'value' => wp_kses_post( wp_unslash(trim($_REQUEST['sql_filter_val'][$key]))),
							'required' => sanitize_text_field($_REQUEST['sql_filter_required'][$key])];
					} else {
						if ($_REQUEST['sql_filter_val'][$key] != "") {
							$return[] = __('a filter could not be saved because a field was not chosen to associate it with', 'admin_form');
						} else if ($field != "") {
							$return[] = sprintf(__("I have not saved the filter associated with the <b>%s</b> field because it has no parameters to pass. If you want to filter the list by shortcode attributes use %s.", 'admin_form'), $field, '[%params.attr_name]');
						}
					}
				}
			} 
			
			/**
			 * @var ADFO_list_setting[] $setting_custom_list
			 */
			$setting_custom_list =  ADFO_functions_list::get_list_structure_config($items, $post->post_content['list_setting']);
			if (is_array($setting_custom_list)) {
				foreach ($setting_custom_list as $key_list=>$list) {
					$post->post_content['list_setting'][$key_list] = $list->get_for_saving_in_the_db();
				}
			}

			$post_title = (!isset($_REQUEST['post_title'])) ? '' : sanitize_text_field($_REQUEST['post_title']);
			
			/**
			 * POST TYPE
			 * @since 1.7.0
			 * Salvo i post_type se esiste
			 */
			if (isset($_REQUEST['post_type_name'])) {
				$old_post_content_post_type = $post->post_content['post_type']['name'];
				$old_post_content_post_slug = $post->post_content['post_type']['slug'];
				$post->post_content['post_type'] = ['name'=> substr(strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_',  $_REQUEST['post_type_name'])), 0, 20)];
				if (!isset($_REQUEST['post_type_slug'])) {
					$_REQUEST['post_type_slug'] = $_REQUEST['post_type_name'];
				}
				$post->post_content['post_type']['slug'] = substr(strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_',  $_REQUEST['post_type_slug'])), 0, 20);
				if ($post->post_content['post_type']['slug'] == "") {
					$post->post_content['post_type']['slug'] = $post->post_content['post_type']['name'];
				}
				$post_types = get_option('adfo_post_types');
				$add_new = false;
				if ($old_post_content_post_type != $post->post_content['post_type']['name']) {
					
					if (!is_array($post_types)) {
						$post_types = [];
					}
					$add_new = true;
					if (isset($post_types[$old_post_content_post_type])) {
						if (in_array($id, $post_types[$old_post_content_post_type]["adfo_ids"]) && count($post_types[$old_post_content_post_type]["adfo_ids"]) == 1) {
							unset($post_types[$old_post_content_post_type]);
						} else {
							$post_types[$old_post_content_post_type]["adfo_ids"] = array_diff($post_types[$old_post_content_post_type]["adfo_ids"], [$id]);
							$post_types[$old_post_content_post_type]['slug'] = $post->post_content['post_type']['slug'];
						}
					}
				} else if ($old_post_content_post_type == $post->post_content['post_type']['name']) {
					$add_new = false;
					$post_types[$old_post_content_post_type]['slug'] = $post->post_content['post_type']['slug'];
				} 
				if ($add_new) {
					if (!isset($post_types[$post->post_content['post_type']['name']])) {
						$post_types[$post->post_content['post_type']['name']] = [
							'adfo_ids' => [$id],
							'slug' => $post->post_content['post_type']['slug']
						];
					} else {
						$post_types[$post->post_content['post_type']['name']]['adfo_ids'][] = $id;
						$post_types[$post->post_content['post_type']['name']]['slug'] = $post->post_content['post_type']['slug'];
					}
				}
				$post_types = update_option('adfo_post_types', $post_types);
			}

			if (isset($_REQUEST['post_status_permission'])) {
				$post->post_content['post_status']['permission'] = [
					'editor' => sanitize_text_field($_REQUEST['post_status_permission']['editor']),
					'author' => sanitize_text_field($_REQUEST['post_status_permission']['author'])
				];
				if (isset($_REQUEST['post_status_permission']['contributor'])) {
					$post->post_content['post_status']['permission']['contributor'] = sanitize_text_field( $_REQUEST['post_status_permission']['contributor']);
				};
			}

			if ($post_title != "") {
				wp_update_post(array(
					'ID'           => $id,
					'post_title' 	=> $post_title,
					'post_excerpt' 	=> wp_kses_post( wp_unslash($_REQUEST['post_excerpt']))
				));
				ADFO_fn::save_list_config($id, $post->post_content);
			} else {
				$return[] = __('The title is required', 'admin_form');
			}
			

			// permessi e menu admin
			$old = get_post_meta($id,'_dbp_admin_show', true);
			$title =  ($post_title != "") ? $post_title : sanitize_text_field($_REQUEST['menu_title']);

			$dbp_admin_show  = [
				'page_title'    => $title, 
				'menu_title'    => sanitize_text_field($_REQUEST['menu_title']),
				'menu_icon'     => sanitize_text_field(trim($_REQUEST['menu_icon'])),
				'menu_position' => absint($_REQUEST['menu_position']),
				'capability'    => 'dbp_manage_'.$id,
				'slug'			=> 'dbp_'.$id,
				'show'			=> (isset($_REQUEST['show_admin_menu']) && $_REQUEST['show_admin_menu'] == 1) ? 1 : 0,
				'status'		=> 'publish'
			];
		
			if (isset($_REQUEST['show_admin_menu']) && $_REQUEST['show_admin_menu']) {
				if ($old != false) {
					update_post_meta($id, '_dbp_admin_show', $dbp_admin_show);
				} else {
					add_post_meta($id,'_dbp_admin_show', $dbp_admin_show, false);
				}
				foreach ($wp_roles->get_names() as $role_key => $_role_label) { 
					$role = get_role( $role_key );
					
					if (isset( $_REQUEST['add_role_cap']) && in_array($role_key, $_REQUEST['add_role_cap'])) {
						$role->add_cap( 'dbp_manage_'.$id, true );
					} else {
						$role->remove_cap('dbp_manage_'.$id);
					}
				}
			} else {
				delete_post_meta($id, '_dbp_admin_show');
			}

			if (isset($_REQUEST['post_type_name'])) {
				$ADFO_admin_loader = new ADFO_admin_loader();
				$ADFO_admin_loader->init_post_type();
                flush_rewrite_rules();
			}
			

		} else {
			$return[] = __('You have not selected any list', 'admin_form');
		}
		$show_query = false;
		if ($error_query != "") {
			$return[] = $error_query;
			$show_query = true;
		}
		if ($error != "") {
			$return[] = $error;
		}
		$return = (count($return) == 0) ? true : implode("<br>", $return);
		return [$return, $show_query];
	}

	/**
	 * L'elenco dei dati estratti da una lista
	 */
	private function list_browse() {
		global $wpdb;
		wp_add_inline_script( 'database-press-js', 'dbp_admin_post = "'.esc_url( admin_url("admin-post.php")).'";', 'before' );
		wp_enqueue_script( 'database-press-js', plugin_dir_url( __FILE__ ) . 'js/admin-form.js',[], ADFO_VERSION);

		wp_enqueue_script( 'database-form2-js', plugin_dir_url( __FILE__ ) . 'js/af-form2.js',[], ADFO_VERSION);
		$file = plugin_dir_path( __FILE__  );
	
		wp_add_inline_script( 'dbp_frontend_js', 'dbp_post = "'.esc_url( admin_url('admin-ajax.php')).'";', 'before' );
		wp_enqueue_script( 'dbp_frontend_js' );

		$action = isset($_REQUEST['action_query']) ? sanitize_text_field($_REQUEST['action_query']) : ''; 
		$msg_error = $msg = $msg_warning = "";
		
		$id = isset($_REQUEST['dbp_id']) ? absint($_REQUEST['dbp_id']) : 0; 
		$render_content = "/af-content-list-browse.php";
		$html_content = "";

		
		if ($id > 0) {
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			if ($post == false) {
				?><script>window.location.href = '<?php echo admin_url("admin.php?page=admin_form"); ?>';</script><?php
				die;
			}
			if (isset($post->post_content["sql_filter"]) && count($post->post_content["sql_filter"]) > 0) {
				$msg_warning  = __('Filters set by the settings tab were found. Filters are excluded in this tab to allow administrators to manage all the data. If you want to see the filtered data click on the menu item generated by this form. Filters are also applied in the frontend.', 'admin_form');
			}
			$list_title = $post->post_title;
			$description = $post->post_excerpt;
			$sql = @$post->post_content['sql'];
			if ($sql == "") {
				$link = admin_url("admin.php?page=admin_form&section=list-sql-edit&dbp_id=".$id);
				$msg_error = '<a href="' . $link . '">'.__('You have to config the query first!', 'admin_form')."</a>";
			}
			// TODO se c'è un limit nella query dovrebbe settare la paginazione?!
			$table_model 		= new ADFO_model();
			$list_of_columns 	= ADFO_fn::get_all_columns();

			$table_model->prepare($sql);
			//print "<p>".$sql."</p>";
			if ($table_model->sql_type() == "multiqueries") {
				//  NON GESTISCO MULTIQUERY NELLE LISTE
				$msg_error = __('No Multiquery permitted in list', 'admin_form');
			} else if ($table_model->sql_type() == "select") {
				//print "<p>TYPE SELECT</p>";
				// se sto renderizzando questa tabella una form è stata già aperta
				ADFO_fn::set_open_form(); 
				// cancello le righe selezionate!
				
				if ($action == "delete_rows" && isset($_REQUEST["remove_ids"]) && is_array($_REQUEST["remove_ids"])) {
					$remove_ids = ADFO_fn::get_request("remove_ids");
					$result_delete = ADFO_fn::delete_rows($remove_ids, '', $id);
					if ($result_delete['error'] != "") {
						$msg_error = $result_delete;
					} else {
						$msg = __('The data has been removed', 'admin_form');
					}
				}

				if ($action == "delete_from_sql") {
					if (class_exists('ADFO_fn_pro')) {
						$result_delete = ADFO_fn_pro::dbp_delete_from_sql_from_request();
						if ($result_delete != "") {
							$msg_error = $result_delete;
						} else {
							$msg = __('The data has been removed', 'admin_form');
						}
					}
				}

				if ( ADFO_fn::get_request('filter.limit', 0) == 0) {
					if (isset($post->post_content['sql_limit']) &&  (int)$post->post_content['sql_limit'] > 0) {
						$sql_limit  = (int)$post->post_content['sql_limit'];
					} else {
						$sql_limit  = 100;
					}
					$_REQUEST['filter']['limit'] = $sql_limit ;
					$table_model->list_add_limit(0, $sql_limit);
				}
				if ( ADFO_fn::get_request('filter.sort.field', '') == '') {
					if (isset($post->post_content['sql_order']['sort']) &&  isset($post->post_content['sql_order']['field'])) {
						$_REQUEST['sort']['field'] = $post->post_content['sql_order']['field'] ;
						$table_model->list_add_order($post->post_content['sql_order']['field'], $post->post_content['sql_order']['sort']);
					}
				}
				//if ($action == "delete_rows") die();
				ADFO_functions_list::add_lookups_column($table_model, $post);
				ADFO_functions_list::add_post_type_settings($table_model, $post);
				ADFO_functions_list::add_post_user_column($table_model, $post);
				$search = '';
				// SEARCH in all columns
				if (isset($_REQUEST['search']) && $_REQUEST['search'] != "") {					
					$search = wp_kses_post( wp_unslash($_REQUEST['search']));
					
					// ["searchable"]=>string(4) "LIKE" ["mysql_name"]=>  string(15) "`tes`.`adfo_id`"
					// ["is_multiple"]=>0|1 ["view"] => Lookup
					if ( in_array($action, ['search','order','limit_start','change_limit','filter'])) {
						// TODO se è search deve rimuovere prima tutti i where!!!!
						$filter =[] ;
						foreach ($post->post_content["list_setting"] as $setting) {
							if ($setting->searchable == "LIKE" && $setting->is_multiple == 0) {
								$filter[] = ['op'=>'LIKE', 'column'=> $setting->mysql_name, 'value' =>$search];
							} else if ($setting->searchable == "=" && $setting->is_multiple == 0) {
								$filter[] = ['op'=>'=', 'column'=> $setting->mysql_name, 'value' =>$search];
							} if ($setting->is_multiple == 1) {
								if ($setting->view == 'USER') {
									$user = $wpdb->get_row("SELECT * FROM wp_users WHERE (ID = ".intval($search)." OR user_login = '".esc_sql($search)."' OR user_email = '".esc_sql($search)."' OR user_nicename = '".esc_sql($search)."') LIMIT 1");
								
									if ($user) {
										$filter[] = ['op'=>'LIKE', 'column'=> $setting->mysql_name, 'value' =>'"'.$user->ID.'"'];
									} 

								}
								if ($setting->view == 'POST' || $setting->view == 'MEDIA_GALLERY') {
									$post_temp = $wpdb->get_row("SELECT * FROM wp_posts WHERE (ID = ".intval($search)." OR post_title = '".esc_sql($search)."' OR post_name = '".esc_sql($search)."') LIMIT 1");
									if ($post_temp) { 
										$filter[] = ['op'=>'LIKE', 'column'=> $setting->mysql_name, 'value' =>'"'.$post_temp->ID.'"'];
									} 
								}
								if ($setting->view == 'LOOKUP') {
									$table = '`'.esc_sql($setting->lookup_id).'`';
									$lookup_id = $setting->lookup_sel_val;
									$sel = "";
									if (is_array($setting->lookup_sel_txt)) {
										$sels = [];
										foreach ($setting->lookup_sel_txt as $searchsl) {
											$sels[] = '`'.esc_sql($searchsl).'` LIKE "'.esc_sql($search).'%"';
										}
										$sel = implode(" OR ",$sels);

										$sql = "SELECT ".$lookup_id." as val FROM $table WHERE ".implode(" OR ",$sels)." LIMIT 1";
										

										$ris = $wpdb->get_row($sql);
										if ($ris) {
											$filter[] = ['op'=>'LIKE', 'column'=> $setting->mysql_name, 'value' =>'"'.$ris->val.'"'];
										} 
									}
								}
							}
						}
						
						if (count($filter) > 0) {
							$table_model->list_add_where($filter, 'OR');
						}
					} 
				}
				//TODO copio il post e poi lo risetto perché se imposto i setting  qui dà un sacco di warning
				// solo che mi serve qui per i request filter!
				// VErificare perché su ad-admin-menu non dà questo tipo di problema e fixarlo
				$original_post = clone $post;
				$table_model->update_items_with_setting($post);
				$count_sql_query = ADFO_fn::add_request_filter_to_model($table_model, $this->max_show_items, $post);
				$post = $original_post;
				$table_items = $table_model->get_list();
				
				//print "<p>QUERY: ".$table_model->get_current_query()."</p>";
				
				if ($count_sql_query != '') {
					$table_model->total_items = $wpdb->get_var($count_sql_query);
				}
				
				/*
				if ($table_model->last_error !== false) {
					$table_model->prepare($sql);
					$table_items = $table_model->get_list();
					
				} 
				*/
			
				$table_model->update_items_with_setting($post);
				ADFO_fn::items_add_action($table_model, $id);
				$table_model->check_for_filter();
				ADFO_fn::remove_hide_columns($table_model);
				$html_table   = new ADFO_html_table();
				$html_content = $html_table->template_render($table_model); // lo uso nel template
				//print (get_class($table_model) );	
				ADFO_fn::set_close_form(); 
			} else {
				$msg_error = __('You need to create a select query for the lists', 'admin_form');
			}
		}  else {
			$msg_error = __('You have not selected any list', 'admin_form');
		}
		require(dirname( __FILE__ ) . "/partials/af-page-base.php");
	}

	/**
	 * La struttura prevede di gestire quali campi visualizzare e come
	 */
	private function list_structure() {
		global $wpdb;
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'af-list-structure-js', plugin_dir_url( __FILE__ ) . 'js/af-list-structure.js',[],ADFO_VERSION);
		wp_enqueue_script( 'af-sql-editor-js', plugin_dir_url( __FILE__ ) . 'js/af-sql-editor.js',[],ADFO_VERSION);

		// $dbp = new ADFO_fn();
		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'string';
		$msg = "";
		$msg_error = "";
		if ($action == 'list-structure-save') {
			$this->list_structure_save();
			$msg = __("Saved", 'admin_form');
		}
		$table_model = new ADFO_model();
		$table_model2 = new ADFO_model();
		/**
		 * @var ADFO_list_setting[] $items
		 */
		$items = []; 
		if ($id > 0) {
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			$total_row = ADFO::get_total($id);
			$select_array_test = [];
			for($xtr = 1; ($xtr <= $total_row && $xtr < 100); $xtr++) {
				$select_array_test[$xtr] =  $xtr;
			}

			$list_title = $post->post_title;
			if (array_key_exists('sql', $post->post_content)) {
				$sql = $post->post_content['sql'];
				$table_model->prepare($sql);
				$list = $table_model->get_list();

				$table_model2->prepare($sql);
				ADFO_functions_list::add_lookups_column($table_model2, $post);
				ADFO_functions_list::add_post_type_settings($table_model2, $post);
				$table_model2->get_list();
				if ($table_model2->last_error !== false) {
					$table_model2->prepare($sql);
					$table_model2->get_list();
					
				} 
				$items = ADFO_functions_list::get_list_structure_config($table_model2->items, $post->post_content['list_setting']);
				
			} else {
				$link = admin_url("admin.php?page=admin_form&section=list-sql-edit&dbp_id=".$id);
				$msg_error = '<a href="' . $link . '">'.__('You have to config the query first!', 'admin_form')."</a>";
			}
			$pinacode_fields = [];
			
			$primaries = $table_model->get_pirmaries();
			if (is_countable($items)) {
				foreach ($items as $key=>$item) {	
					$pinacode_fields[] = $item->name;
				}
				ADFO_fn::echo_pinacode_variables_script(['data'=>$pinacode_fields]);
			}
			//
			$render_content = "/af-content-list-structure.php";
		} else {
			$msg_error = __('You have not selected any list', 'admin_form');
		}
		if ($items === false) {
			$msg_error =  __('Something is wrong, check the query', 'admin_form');
		}
		require(dirname( __FILE__ ) . "/partials/af-page-base.php");
	}

	/**
	 * Salva la struttura di una lista
	 * @return String error message
	 */
	private function list_structure_save() {
		// $dbp = new ADFO_fn();

		if (!isset($_REQUEST['dbp_list_form_nonce']) || !wp_verify_nonce($_REQUEST['dbp_list_form_nonce'], 'dbp_list_form')) {
			return '';
		}

		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
		if ($id > 0) {
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			unset($post->post_content['list_setting']);
			$count = 0;
			$list_setting = [];
			if (isset($_REQUEST['fields_toggle']) && is_countable($_REQUEST['fields_toggle'])) {
				foreach ($_REQUEST['fields_toggle'] as $key=>$ft) {
					$ft = sanitize_text_field($ft);
					$key = sanitize_text_field($key);
					if ($key === 0 ) continue;
					$count++;
					$column_key = $key;
					if (isset($_REQUEST['fields_origin'][$key]) && $_REQUEST['fields_origin'][$key] == 'CUSTOM' && is_numeric($key)) {
						$title = sanitize_text_field(@$_REQUEST['fields_title'][$key]);
						$column_key = ADFO_fn::clean_string($title);
					} 
					if (isset($header[$column_key])) {
						unset($header[$column_key]);
					}
					$fields_lookup_sel_txt = [];
					if (isset($_REQUEST['fields_lookup_sel_txt'][$key]) && is_countable($_REQUEST['fields_lookup_sel_txt'][$key])) {
						foreach ($_REQUEST['fields_lookup_sel_txt'][$key] as $ktxt=>$vtxt) {
							$fields_lookup_sel_txt[sanitize_text_field($ktxt)] = sanitize_text_field($vtxt);
						}
					}
					if (isset($_REQUEST['fields_custom_param'][$key])) {
						$custom_param =  wp_kses_post( wp_unslash(@$_REQUEST['fields_custom_param'][$key]));
					} else {
						$custom_param = "";
					}

					if ($_REQUEST['fields_custom_view'][$key] == 'COLUMN_SELECT') {
						$custom_param =  wp_kses_post( wp_unslash(@$_REQUEST['fields_custom_param_select'][$key]));
					}
					$custom_code =  (isset($_REQUEST['fields_custom_code'][$key])) ? wp_unslash(@$_REQUEST['fields_custom_code'][$key]) : '';
					//wp_unslash(@$_REQUEST['fields_format_values'][$key])
					$format_value = (isset($_REQUEST['fields_format_values'][$key])) ? wp_unslash(@$_REQUEST['fields_format_values'][$key]) : '';
					$format_style = (isset($_REQUEST['fields_format_styles'][$key])) ? wp_unslash(@$_REQUEST['fields_format_styles'][$key]) : '';
					
					$list_setting[$column_key] = (new ADFO_list_setting())->set_from_array(
						['toggle'=>$ft,
						'title'	=> sanitize_text_field(@$_REQUEST['fields_title'][$key]), 
						'view'	=> sanitize_text_field(@$_REQUEST['fields_custom_view'][$key]),
						
						'custom_code'	=>  $custom_code,
						'order'	=> sanitize_text_field(@$_REQUEST['fields_order'][$key]),
						'type' 	=> sanitize_text_field(@$_REQUEST['fields_origin'][$key]),
						'width' => sanitize_text_field(@$_REQUEST['fields_width'][$key]),
						'align' => sanitize_text_field(@$_REQUEST['fields_align'][$key]),
						'mysql_name' 	=> sanitize_text_field(@$_REQUEST['fields_mysql_name'][$key]),
						'mysql_table' 	=> sanitize_text_field(@$_REQUEST['fields_mysql_table'][$key]),
						'name_request' 	=> sanitize_text_field(@$_REQUEST['fields_name_request'][$key]),
						'searchable' 	=> sanitize_text_field(@$_REQUEST['fields_searchable'][$key]),
						'custom_param' 	=> $custom_param,
						'format_values' =>  $format_value,
						'format_styles'	=>  $format_style,
						'lookup_id' 	=> sanitize_text_field(@$_REQUEST['fields_lookup_id'][$key]),
						'lookup_sel_val' => sanitize_text_field(@$_REQUEST['fields_lookup_sel_val'][$key]),
						'lookup_sel_txt' => $fields_lookup_sel_txt,
						'inherited' => sanitize_text_field(@$_REQUEST['inherited'][$key])
						]
					);
				}
			}
		
			// lo faccio sia prima che dopo per attivare i lookups e quindi salvarli
			$post->post_content['list_setting'] = $list_setting;
			$model = new ADFO_model();
			$model->prepare($post->post_content['sql']);
			$model->list_add_limit(0,1);
			$model->add_primary_ids();
			
			ADFO_functions_list::add_lookups_column($model, $post);
			ADFO_functions_list::add_post_type_settings($model, $post);
			$model_items = $model->get_list();
			$model->update_items_with_setting($post);
			$post->post_content['schema'] = reset($model->items);
			// aggiungo i metadati di schema estratti dalla query
			$list_setting = ADFO_functions_list::get_list_structure_config($model_items, $list_setting);

			if (isset($list_setting) && is_countable($list_setting)) {
				foreach ($list_setting as $key=>$single) {
					$post->post_content['list_setting'][$key] = $single->get_for_saving_in_the_db();
				}
			}
			$post->post_content = apply_filters('dbp_list_structure_save', $post->post_content);

			if (isset($_REQUEST['list_general_setting']) && is_countable($_REQUEST['list_general_setting'])) {
				foreach ($_REQUEST['list_general_setting'] as $lgs_key => $list_general_setting) {
					$post->post_content['list_general_setting'][sanitize_text_field($lgs_key)] = sanitize_text_field($list_general_setting);
				}
			}
			ADFO_fn::save_list_config($id, $post->post_content);
		}
		return '';
	}
	/**
	 * I setting di una lista definiscono i parametri quali titolo, descrizione stato ecc.. 
	 */
	private function list_setting() {
		$file = plugin_dir_path( __FILE__  );
		wp_register_style( 'dbp_frontend_css',  plugins_url( 'frontend/admin-form.css',  $file), false, ADFO_VERSION);
		wp_enqueue_style( 'dbp_frontend_css' );
		wp_enqueue_script( 'af-list-setting-js', plugin_dir_url( __FILE__ ) . 'js/af-list-setting.js',[],ADFO_VERSION);
		
		$pages  = get_pages(['sort_column' => 'post_title']); 
		
		// $dbp = new ADFO_fn();
		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'string';
		$render_content = "/af-content-list-setting.php";
		$msg = $msg_error = "";
		if ($id > 0) {
			
			if ($action == 'list-setting-save') {
				if ($this->list_setting_save($id)) {
					$msg = __("Saved", 'admin_form');
				} else {
					$msg_error = __("There was a problem saving the data", 'admin_form');
				}
			}
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			$few = $post->post_content['frontend_view'];
			$errors_if_textarea  = "";
			if (@$few['checkif'] == 1) {
				$few['if_textarea'];
				$ris = PinaCode::math_and_logic($few['if_textarea']);
				$pc_errors = PcErrors::get('error');
				if (count($pc_errors) == 0) {
					if ( (is_numeric($ris) && $ris != 0 && $ris != 1) || (!is_numeric($ris)  && !is_bool($ris) &&  (is_string($ris) && !in_array(strtolower($ris), ["true",'t','false','f'])))) {
						$errors_if_textarea = __('The expression must return boolean, or a number or one of the following texts: true, t, false, f', 'admin_form');
					}
				} else {
					$errors_if_textarea = array_shift($pc_errors);
					if (is_array($errors_if_textarea)) {
						$errors_if_textarea = array_shift($errors_if_textarea);
					}
				}
			}
			$list_title = $post->post_title;
			$post_excerpt = $post->post_excerpt;

			$sql = @$post->post_content['sql'];
			
			if ($sql != "") {
				$table_model = new ADFO_model();
				$table_model->prepare($sql);
				if ($table_model->sql_type() != "select") {
					$msg_error = sprintf(__('Only a single select query is allowed in the lists %s', 'admin_form'), $sql);
				} else {
					$table_model->list_add_limit(0, 1);
					$items = $table_model->get_list();
					if ($table_model->last_error != "") {
						$msg_error = __('<h2>Query error:</h2>'.$table_model->last_error, 'admin_form');
					} else {
						$pinacode_fields = ['data'=>[], 'params'=>['example'], 'request'=>['example'], 'total_row'];
						$items =  ADFO_functions_list::get_list_structure_config($table_model->items, $post->post_content['list_setting']);
						if (isset($items) && is_countable($items)) {
							foreach ($items as $key=>$item) {
								$pinacode_fields['data'][] = $item->name;
							}
						}
						ADFO_fn::echo_pinacode_variables_script($pinacode_fields);
	
					}
				}
			}

		}
		require(dirname( __FILE__ ) . "/partials/af-page-base.php");
	}

	/**
	 * Salvo i setting
	 */
	private function list_setting_save($id) {
		if (!isset($_REQUEST['dbp_list_form_nonce']) || !wp_verify_nonce($_REQUEST['dbp_list_form_nonce'], 'dbp_list_form')) {
			return false;
		}
		$frontend_view = ADFO_fn::get_request('frontend_view', [], 'array');
		$frontend_view['content'] 				=  wp_unslash($_REQUEST['frontend_view']['content']);
		$frontend_view['no_result_custom_text'] =  wp_unslash($_REQUEST['frontend_view']['no_result_custom_text']);
		$frontend_view['detail_template'] 		=  wp_unslash($_REQUEST['frontend_view']['detail_template']);
		$frontend_view['content_header'] 		=  wp_unslash($_REQUEST['frontend_view']['content_header']);
		$frontend_view['content_footer'] 		=  wp_unslash($_REQUEST['frontend_view']['content_footer']);
		$frontend_view['detail_type'] 			=  wp_unslash($_REQUEST['frontend_view']['detail_type']);
		if (@$frontend_view['checkif'] == 1 && $frontend_view['if_textarea'] != "") {
			$frontend_view['content_else'] 		=  wp_unslash($_REQUEST['frontend_view']['content_else']);
			$frontend_view['if_textarea'] 		=  wp_unslash($_REQUEST['frontend_view']['if_textarea']);
		} else {
			$frontend_view['checkif'] = 0;
			$frontend_view['content_else'] = '';
		}
		if ($frontend_view['type'] == "EDITOR") {
			$frontend_view['table_update'] = ADFO_fn::get_request('editor_table_update');
			$frontend_view['table_pagination_style'] = ADFO_fn::get_request('editor_table_pagination_style');
		} 
		$post = ADFO_functions_list::get_post_dbp($id);
		ADFO_functions_list::no_post_dbp($post);
		$post->post_content['frontend_view'] = $frontend_view;
		ADFO_fn::save_list_config($id, $post->post_content);
		return true;
	
	}

	/**
	 * Gestisco la form 
	 */
	private function list_form() {
		global $wpdb;
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'af-list-form-js', plugin_dir_url( __FILE__ ) . 'js/af-list-form.js',[], ADFO_VERSION);
		wp_enqueue_script( 'af-form2-js', plugin_dir_url( __FILE__ ) . 'js/af-form2.js',[], ADFO_VERSION);
		
		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
		$action = ADFO_fn::get_request('action', '', 'string');
		$msg = "";
		$msg_error = "";
		if ($action == 'list-form-save') {
			//print "count: ".self::count_recursive($_REQUEST);
			if (self::count_recursive($_REQUEST) >= ADFO_fn::get_max_input_vars()) {
				$msg_error = __("The form is too large, please reduce the number of fields or change max_input_vars in your php.ini config", 'admin_form');
			} else {
				$ris = $this->list_form_save();
				if ($ris == false) {
					$msg_error = __("There was a problem saving the data", 'admin_form');
				} else {
					$msg = __("Saved", 'admin_form');
				}
			}
		
		}
		$tables = []; 
		if ($id > 0) {
			
			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			$total_row = ADFO::get_total($id);
			$select_array_test = [];
			for($xtr = 1; ($xtr <= $total_row && $xtr < 100); $xtr++) {
				$select_array_test[$xtr] =  $xtr;
			}

			$list_title = "FORM ".$post->post_title;
			if (array_key_exists('sql', $post->post_content)) {
				$form = new ADFO_class_form($id);
				list($settings, $table_options) = $form->get_form(false);	
				$table_options = array_shift($table_options);

				if (isset($settings) && is_countable($settings)) {
					foreach ($settings as $k=>$sett) {
						$temp_tables = ['table_name'=>$table_options[$k]->orgtable, 'fields'=>[], 'table_options' => $table_options[$k]];
						if (is_countable($sett)) {
							foreach ($sett as $field) {
								$temp_tables['fields'][] = $field;
							}
						}
						$tables[$table_options[$k]->table] = $temp_tables;
					}
				}
				
			} else {
				$link = admin_url("admin.php?page=admin_form&section=list-sql-edit&dbp_id=".$id);
				$msg_error = '<a href="' . $link . '">'.__('You have to config the query first!', 'admin_form')."</a>";
			}
			
			$post_types = get_post_types();
		
			// i campi template engine da inserire nella documentazione
			$pinacode_fields = [];
			if (isset($tables) && is_countable(($tables))) {
				foreach ($tables as $key=>$table) {
					if (isset($table['fields']) && is_countable(($table['fields']))) {
						foreach ($table['fields'] as $item) {
							if ($item->js_rif != "") {
								$pinacode_fields[] =  $item->js_rif;
							}
						}
					}
				}
			}

			ADFO_fn::echo_pinacode_variables_script($pinacode_fields);
			
			//
			$render_content = "/af-content-list-form.php";
		} else {
			$msg_error = __('You have not selected any list', 'admin_form');
		}
		if ($tables === false) {
			$msg_error =  __('Something is wrong, check the query', 'admin_form');
		}
		require(dirname( __FILE__ ) . "/partials/af-page-base.php");
	}

	/**
	 * Salva la list-form
	 *
	 * @return void
	 */
	private function list_form_save() {
		global $wpdb;
		// check wp_nonce_field('dbp_list_form', 'dbp_list_form_nonce');
		if (!isset($_REQUEST['dbp_list_form_nonce']) || !wp_verify_nonce($_REQUEST['dbp_list_form_nonce'], 'dbp_list_form')) {
			return false;
		}
		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
		if ($id == 0 || !current_user_can('administrator')) {
			return false;
		}
		$post = ADFO_functions_list::get_post_dbp($id);
		ADFO_functions_list::no_post_dbp($post);
		$post->post_content['form'] = [];
		$count = 0;
		$override_list_setting 	= [];
		$new_column_in_db 		= [];
		$meta_data_removed 		= [];
		// 
		$table_model = new ADFO_model();
		$table_model->prepare($post->post_content['sql']);
		$table_model->list_add_limit(0, 1);
		$model_items = $table_model->get_list();
		$setting_custom_list =  ADFO_functions_list::get_list_structure_config($model_items,  $post->post_content['list_setting']);
		// METDATADA
		// la configurazione delle tabelle
		
		$post->post_content['form_table'] = [];
		$post->post_content['primaries'] = $table_model->get_pirmaries();
		if (isset($_REQUEST['fields_info']) && is_countable($_REQUEST['fields_info'])) {
			foreach ($_REQUEST['fields_info'] as $key => $ft_info) {
				$ft_array = json_decode(wp_unslash($ft_info), true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					return false;
				}
				$field_table_alias_temp =  (isset($ft_array['fields_table'])) ? sanitize_text_field($ft_array['fields_table']) : '';
				$field_name = (isset($ft_array['fields_name'])) ? sanitize_text_field($ft_array['fields_name']) : '';
				$field_orgtable = (isset($ft_array['fields_orgtable'])) ? sanitize_text_field($ft_array['fields_orgtable']) : '';
				$field_type =  (isset($ft_array['show'])) ? sanitize_text_field($ft_array['type']) : '';
				$field_show = (isset($ft_array['show'])) ? sanitize_text_field($ft_array['show']) : '';
				$field_label = (isset($ft_array['label'])) ? sanitize_text_field( wp_unslash($ft_array['label'])) : '';
				$field_required = (isset($ft_array['required'])) ? sanitize_text_field($ft_array['required']) : '';
				$field_autocomplete = (isset($ft_array['autocomplete'])) ?  sanitize_text_field($ft_array['autocomplete']) : '';
				$key = sanitize_text_field($key);
				if (!isset($_REQUEST['table_options'][$field_table_alias_temp]) || $_REQUEST['table_options'][$field_table_alias_temp] == '') {
					continue;
				}
				// devo aggiungere il metadato prima di tutto alla query
				$options = maybe_unserialize(wp_unslash($_REQUEST['table_options'][$field_table_alias_temp]));
				if (!is_array($options) || !isset($options['orgtable'])) {
					continue;
				}
				if (!isset($_REQUEST['table_action'][$field_table_alias_temp]) || $_REQUEST['table_action'][$field_table_alias_temp] == 'REMOVE') {	
					ADFO_class_metadata::remove_sql_meta_data($table_model, $options['table'], $options['value_key'] );
					$post->post_content['sql'] = $table_model->get_current_query();
					$meta_data_removed[] = $options['table'];
					continue;
				}
			    // se non è ADD qui blocco tutto
				if (!isset($_REQUEST['table_action'][$field_table_alias_temp]) || $_REQUEST['table_action'][$field_table_alias_temp] != 'ADD_META') {
					continue;
				}
				//Ottenuti questi dati devo aggiungere il metadata alla query
				$table = $options['orgtable'];
				$field_key = $options['field_key'];
				$value_key = (isset($_REQUEST['table_module_field_name'][$field_table_alias_temp]) && $_REQUEST['table_module_field_name'][$field_table_alias_temp] != '') ? sanitize_text_field($_REQUEST['table_module_field_name'][$field_table_alias_temp]) : uniqid();
				$field_conn_id = $options['field_conn_id'];
				$value_conn_id_array = explode(".", $options['value_conn_id']);
				$parent_table = array_shift($value_conn_id_array);
				$parent_pri_id = implode(".",$value_conn_id_array);

				$post->post_content['sql'];
				$value_key_new = strtolower(ADFO_fn::clean_string($value_key));
				// se è solo numerica aggiungo un carattere iniziale
				if (is_numeric($value_key_new)) {
					$value_key_new = "f_".$value_key_new;
				}
				$count = 0;
				//Se esiste già cambio il nome, altrimenti modifico la query
				while (ADFO_class_metadata::is_inserted_meta($table_model, $options['orgtable'], $value_key_new) && $count < 99) {
					$count++;
					$value_key_new =  ADFO_fn::clean_string($value_key."_".str_pad($count,2,'0',STR_PAD_LEFT));
				}
				// Creo una funzione su af_metadata per aggiungere la parte di query del metadato (la prendo da admin_form_pro che ce l'ha già)
				list($alias, $field_alias, $field_alias_pri) = ADFO_class_metadata::add_sql_meta_data($table_model, $options['orgtable'], $value_key_new, $field_conn_id, $parent_table, $parent_pri_id);
				if ($field_label == "" || $field_label == "new field") {
					$field_label = $value_key;
				}
				$_REQUEST['fields_table'][$key] = $alias;

				$_REQUEST['fields_info'][$key] = json_encode([
					'fields_name' => wp_unslash($field_name),
					'fields_table' => wp_unslash($alias),
					'fields_orgtable' => wp_unslash($options['orgtable']),
					'type' => $field_type,
					'show' => $field_show,
					'label' => wp_unslash($field_label),
					'required' => $field_required,
					'autocomplete' => $field_autocomplete,
				]);
				$_REQUEST['table_module_order'][$alias] = $_REQUEST['table_module_order'][$field_table_alias_temp];
				unset($_REQUEST['table_module_order'][$field_table_alias_temp]);
				$options['value_key'] = $value_key_new;
				$_REQUEST['table_options'][$alias] = maybe_serialize($options);
				unset($_REQUEST['table_options'][$field_table_alias_temp]);
				$_REQUEST['table_module_type'][$alias] = 'EDIT';
				unset($_REQUEST['table_module_type'][$field_table_alias_temp]);
				$_REQUEST['table_allow_create'][$alias] = 'HIDE';
				unset($_REQUEST['table_allow_create'][$field_table_alias_temp]);
				$_REQUEST['table_show_title'][$alias] = 'HIDE';
				unset($_REQUEST['table_show_title'][$field_table_alias_temp]);
				$_REQUEST['table_frame_style'][$alias] = 'WHITE';
				unset($_REQUEST['table_frame_style'][$field_table_alias_temp]);
				$_REQUEST['table_title'][$alias] = '';
				unset($_REQUEST['table_title'][$field_table_alias_temp]);
				$_REQUEST['table_description'][$alias] = '';
				unset($_REQUEST['table_description'][$field_table_alias_temp]);
				$table_model->remove_limit();
				$post->post_content['sql'] = $table_model->get_current_query();
				$fields_options = isset($_REQUEST['fields_options'][$key]) ? $_REQUEST['fields_options'][$key]: '';
				$override_list_setting[$field_alias] = ['title' => $field_label, 'format_values' => wp_kses_post( wp_unslash($fields_options)), 'inherited' => 1, 'toggle' => 'SHOW'];
				$override_list_setting[$field_alias_pri] = ['title' => $field_label, 'format_values' => 'TEXT', 'inherited' => 1, 'toggle' => 'HIDE'];
				$new_column_in_db[] = $field_alias;
				$new_column_in_db[] = $field_alias_pri;

				$post->post_content['form'][] =  [ 
						'name' => ADFO_fn::get_primary_key($options['orgtable']), 
						'order' => 1, 
						'table' => $alias, 
						'orgtable' => $options['orgtable'],
						'edit_view' => 'HIDE',
						'label'=> $field_alias_pri,
						'form_type'=> 'TEXT'
					];
				
			}
		}
		
		// FINE METADATA
		// i campi
		if (isset($_REQUEST['fields_info']) && is_countable($_REQUEST['fields_info'])) {
			foreach ($_REQUEST['fields_info'] as $key => $ft_info) {
				$ft_array = json_decode(wp_unslash($ft_info), true);
				// se dà errore il json_decode
				if (json_last_error() !== JSON_ERROR_NONE) {
					return false;
				}
				$ft = isset($ft_array['fields_name']) ? sanitize_text_field($ft_array['fields_name']) : '';
				$field_table = isset($ft_array['fields_table']) ? sanitize_text_field($ft_array['fields_table']) : '';
				$field_orgtable = isset($ft_array['fields_orgtable']) ? sanitize_text_field($ft_array['fields_orgtable']) : '';
				$field_type = isset($ft_array['type']) ? sanitize_text_field($ft_array['type']) : '';
				$field_show = isset($ft_array['show']) ? sanitize_text_field($ft_array['show']) : '';
				$field_label = isset($ft_array['label']) ? sanitize_text_field(wp_unslash($ft_array['label'])) : '';
				$field_required = isset($ft_array['required']) ? sanitize_text_field($ft_array['required']) : '';
				$field_autocomplete = isset($ft_array['autocomplete']) ? sanitize_text_field($ft_array['autocomplete']) : '';
				$key = sanitize_text_field($key);
				if ($key === 0 ) continue;
				$count++;	
				if (isset($_REQUEST['fields_delete_column'][$key]) && $_REQUEST['fields_delete_column'][$key] == 1) {
					// elimino il campo
					$model_structure = new ADFO_model_structure($field_orgtable);
					$model_structure->delete_column($ft);
				} else if (isset($_REQUEST['fields_edit_new'][$key])) {
					if ($_REQUEST['fields_edit_new'][$key] == "") {
						$_REQUEST['fields_edit_new'][$key] = 'fl_'.$key;
					}
					// creo il campo
					$model_structure = new ADFO_model_structure($field_orgtable);

					$array_convert_type_to_field = [
						'VARCHAR'=>['VARCHAR',255],
						'TEXT'=>['TEXT',''],
						'DATE'=>['DATE',''],
						'DATETIME'=>['DATETIME',''],
						'NUMERIC'=>['BIGINT',''],
						'DECIMAL'=>['DECIMAL','9,2'],
						'SELECT'=>['VARCHAR',255],
						'RADIO'=>['VARCHAR',255],
						'CHECKBOX'=>['VARCHAR',255],
						'CHECKBOXES'=>['TINYTEXT',''],
						'READ_ONLY'=>['VARCHAR',255],
						'EDITOR_CODE'=>['TEXT',''],
						'EDITOR_TINYMCE'=>['TEXT',''],
						'CREATION_DATE'=>['DATE',''],
						'LAST_UPDATE_DATE'=>['DATE',''],
						'RECORD_OWNER'=>['BIGINT',''],
						'POST_STATUS'=>['VARCHAR','255'],
						'UPLOAD_FIELD'=>['VARCHAR',255],
						'EMAIL'=>['VARCHAR',255],
						'LINK'=>['VARCHAR',255],
						'POST'=>['BIGINT',''],
						'USER'=>['BIGINT',''],
						'LOOKUP'=>['BIGINT',''],
						'CALCULATED_FIELD'=>['VARCHAR',255],
						'COLOR_PICKER'=>['VARCHAR',255],
						'RANGE'=>['BIGINT',''],
						'ORDER'=>['INT',''],
						'MEDIA_GALLERY'=>['BIGINT',''],
						'TIME'=>['BIGINT',''],
						'YEAR'=>['BIGINT',''],
						'MONTH'=>['BIGINT',''],
						'DAY'=>['BIGINT',''],
						'HOUR'=>['BIGINT',''],
						'QUARTER-HOUR'=>['BIGINT',''],
						'MINUTES'=>['BIGINT','']
					];
					if (array_key_exists($field_type, $array_convert_type_to_field)) {
						$config_new_column = $array_convert_type_to_field[sanitize_text_field($field_type)];
					} else {
						$config_new_column = ['VARCHAR',255];
					}
					if (isset($_REQUEST['fields_is_multiple'][$key]) && $_REQUEST['fields_is_multiple'][$key] == 1) {
						$config_new_column = ['TEXT',''];
					}
					// fields_name
					$ft = $model_structure->insert_new_column(sanitize_text_field($_REQUEST['fields_edit_new'][$key]), $config_new_column[0], $config_new_column[1]);
					if ($ft == false) continue;	
					$new_column_in_db[] = $ft;
				}
				$override_key = $ft;
				foreach ($setting_custom_list as $sett_key => $setting) {
					if ($setting->orgname == $ft  && $setting->table == $field_table) {
						$override_key = $sett_key;
						break;
					}
				}
				$fields_options = isset($_REQUEST['fields_options'][$key]) ? $_REQUEST['fields_options'][$key]: '';
				// salvo i parametri per il setting
				$override_list_setting[$override_key] = ['title' => $field_label, 'format_values' => wp_kses_post( wp_unslash($fields_options)), 'inherited' => 1, 'toggle' => $field_show];
				if (in_array(sanitize_text_field($field_type), ['LINK','DATE','DATETIME', 'MEDIA_GALLERY','POST','USER', 'COLOR_PICKER', 'ORDER'])) {
					$override_list_setting[$override_key]['view'] = sanitize_text_field($field_type);
				} else {
					$override_list_setting[$override_key]['view'] = 'TEXT';
				}
				if (sanitize_text_field($field_type) == 'VARCHAR' && $ft == 'post_title' && $field_orgtable == $wpdb->posts) {
					$override_list_setting[$override_key]['view'] = 'PERMALINK';
					$override_list_setting[$override_key]['custom_value'] = 'ID';
				}
				if (sanitize_text_field($field_type) == 'CHECKBOXES') {
					$override_list_setting[$override_key]['view'] = 'JSON_LABEL';
				}
				if (sanitize_text_field($field_type) == 'CHECKBOX') {
					if (!isset($_REQUEST['fields_custom_value_checkbox'][$key])) {
						$_REQUEST['fields_custom_value_checkbox'][$key] = '1';
					}
					$override_list_setting[$override_key]['format_values'] = wp_kses_post( wp_unslash(@$_REQUEST['fields_custom_value_checkbox'][$key])).",".$field_label;
				}
				if (sanitize_text_field($field_type) == 'LOOKUP') {
					$override_list_setting[$override_key]['view'] = 'LOOKUP';
					// se è multiple
					if (isset($_REQUEST['fields_is_multiple'][$key]) && $_REQUEST['fields_is_multiple'][$key] == 1) {
						$override_list_setting[$override_key]['toggle'] = 'SHOW';
					} else {
						$override_list_setting[$override_key]['toggle'] = 'HIDE';
					}
					$override_list_setting[$override_key]['lookup_id'] = sanitize_text_field(@$_REQUEST['fields_lookup_id'][$key]);
					$override_list_setting[$override_key]['lookup_sel_val'] 	= sanitize_text_field($_REQUEST['fields_lookup_sel_val'][$key]); 
					$override_list_setting[$override_key]['lookup_sel_txt']= [sanitize_text_field($_REQUEST['fields_lookup_sel_txt'][$key])];
				} 
				if (sanitize_text_field($field_type) == 'MODIFYING_USER' || sanitize_text_field($field_type) == 'RECORD_OWNER') {
					$override_list_setting[$override_key]['view'] = 'USER';
				}
				//
				$custom_value = '';
				if (isset($field_type)) {
					if ($field_type == 'CHECKBOX') {
						$custom_value =  wp_unslash(@$_REQUEST['fields_custom_value_checkbox'][$key]);
					} else if ($field_type == 'CALCULATED_FIELD') {
						$custom_value =  wp_unslash($_REQUEST['fields_custom_value_calc'][$key]);
					}
				}
				if ($field_label == "" && isset($_REQUEST['fields_edit_new'][$key])) {
					$field_label = $_REQUEST['fields_edit_new'][$key];
				}
			
				$js = isset($_REQUEST['fields_js_script'][$key])  ?  wp_unslash($_REQUEST['fields_js_script'][$key]) : '';
				$fields_note = isset($_REQUEST['fields_note'][$key])  ? wp_kses_post( wp_unslash($_REQUEST['fields_note'][$key])) : '';
				$fields_custom_css_class = isset($_REQUEST['fields_custom_css_class'][$key])  ? wp_kses_post( wp_unslash($_REQUEST['fields_custom_css_class'][$key])) : '';
				$fields_default_value = isset($_REQUEST['fields_default_value'][$key])  ? wp_kses_post( wp_unslash($_REQUEST['fields_default_value'][$key])) : '';
				
				
				if ($field_table == '' || in_array($field_table, $meta_data_removed)) continue;

				$array_form =  [ 
					'name'=>$ft, 
					'order'=>sanitize_text_field(@$_REQUEST['fields_order'][$key]), 
					'table' => $field_table, 
					'orgtable' => $field_orgtable,
					'edit_view' => $field_show,
					'label'=> $field_label,
					'form_type'=> sanitize_text_field($field_type),
					'note'=>  $fields_note,
					'options'=>  wp_kses_post( wp_unslash($fields_options)), 
					'required'=> $field_required,
					'custom_css_class'=> $fields_custom_css_class,
					'default_value'=> $fields_default_value,
					'js_script'=> $js,
					'custom_value'=> $custom_value
				];

				if ( $field_type == 'VARCHAR' && $field_autocomplete == 1) {
					$array_form['autocomplete'] =  1;
				}
				if ($field_type == 'CALCULATED_FIELD' && isset($_REQUEST['where_precompiled'][$key]) && $_REQUEST['where_precompiled'][$key] == 1) {
					$array_form['where_precompiled'] =  1;
				}
				if ($field_type == 'CALCULATED_FIELD') {
					$array_form['custom_value_calc_when'] =   sanitize_text_field($_REQUEST['fields_custom_value_calc_when'][$key]) ;
				}
				if ($field_type == 'POST') {
					$array_form['post_types'] 			= isset($_REQUEST['fields_post_types'][$key]) ? sanitize_text_field(@$_REQUEST['fields_post_types'][$key]) : '';
					if (isset($_REQUEST['fields_post_cats'][$key]) && is_countable($_REQUEST['fields_post_cats'][$key])) {
						$array_form['post_cats'] = array_map('sanitize_text_field', $_REQUEST['fields_post_cats'][$key]); 
					}
				}
				if ($field_type == 'USER') {
					if (isset($_REQUEST['fields_user_roles'][$key]) && is_countable($_REQUEST['fields_user_roles'][$key])) {
						$array_form['user_roles'] = sanitize_text_field($_REQUEST['fields_user_roles'][$key]);
					}
				}
				if ($field_type == 'LOOKUP') {
					if (isset($_REQUEST['fields_lookup_id'][$key])) {
						$array_form['lookup_id'] 		= sanitize_text_field(@$_REQUEST['fields_lookup_id'][$key]);
						$array_form['lookup_sel_val'] 	= sanitize_text_field(@$_REQUEST['fields_lookup_sel_val'][$key]); 
						$array_form['lookup_sel_txt'] 	= sanitize_text_field(@$_REQUEST['fields_lookup_sel_txt'][$key]);
						if (isset($_REQUEST['fields_lookup_where'][$key])) {
							$array_form['lookup_where'] 	= sanitize_textarea_field( wp_unslash(@$_REQUEST['fields_lookup_where'][$key]));
						} else {
							$array_form['lookup_where'] = '';
						}
					}
				} else {
					if (isset($array_form['lookup_id'] )) unset($array_form['lookup_id']);
					if (isset($array_form['lookup_sel_val'] )) unset($array_form['lookup_sel_val']);
					if (isset($array_form['lookup_sel_txt'] )) unset($array_form['lookup_sel_txt']);
					if (isset($array_form['lookup_where'] )) unset($array_form['lookup_where']);
				}
				if (in_array($field_type, ['LOOKUP', 'USER', 'POST', 'MEDIA_GALLERY'])) {
					if (isset($_REQUEST['fields_is_multiple'][$key]) && $_REQUEST['fields_is_multiple'][$key] == 1) {
						$array_form['is_multiple'] 	= 1;
					}
				}
				

				if ($field_type == 'RANGE') {
					if (isset($_REQUEST['fields_lookup_id'][$key])) {
						$array_form['range_min'] 	= sanitize_text_field(@$_REQUEST['fields_range_min'][$key]);
						$array_form['range_max'] 	= sanitize_text_field($_REQUEST['fields_range_max'][$key]); 
						$array_form['range_step'] 	= sanitize_text_field($_REQUEST['fields_range_step'][$key]);
					}
				} else {
					if (isset($array_form['range_min'] )) unset($array_form['range_min']);
					if (isset($array_form['range_max'] )) unset($array_form['range_max']);
					if (isset($array_form['range_step'] )) unset($array_form['range_step']);
				}
			
				$post->post_content['form'][] = $array_form;
			}
		}
		
		// devo rieseguirlo perché potrebbero essere stati aggiunti/rimossi dei campi
		$table_model = new ADFO_model();
		$table_model->prepare($post->post_content['sql']);
		$table_model->list_add_limit(0, 1);
		$table_model->add_primary_ids();
		$model_items = $table_model->get_list();
		$from_query = $table_model->get_partial_query_from(true);
		$setting_custom_list =  ADFO_functions_list::get_list_structure_config($model_items,  $post->post_content['list_setting']);
		
		foreach ($setting_custom_list as $key_list=>$list) {
			$post->post_content['list_setting'][$key_list] = $list;
			if (isset($override_list_setting[$key_list]) && is_array($override_list_setting[$key_list]) && (isset($post->post_content['list_setting'][$key_list]->inherited) && $post->post_content['list_setting'][$key_list]->inherited == 1) || in_array($key_list, $new_column_in_db)) {
				// qui risetto i dati dei campi appena creati
				foreach ($override_list_setting[$key_list] as $ov_key=>$ov_val) {
					if (isset($post->post_content['list_setting'][$key_list])) {
						$post->post_content['list_setting'][$key_list]->$ov_key = $ov_val;
					}
				}
			}
		}
		
		ADFO_functions_list::add_lookups_column($table_model, $post);
		ADFO_functions_list::add_post_type_settings($table_model, $post);
		$model_items = $table_model->get_list();
		if ($table_model->last_error == "") {
			$setting_custom_list =  ADFO_functions_list::get_list_structure_config($model_items,  $post->post_content['list_setting']);
			foreach ($setting_custom_list as $key_list=>$list) {
				$post->post_content['list_setting'][$key_list] = $list->get_for_saving_in_the_db();
				if (isset($override_list_setting[$key_list]) && isset($post->post_content['list_setting'][$key_list]['inherited']) && $post->post_content['list_setting'][$key_list]['inherited'] == 1 ) {
					// qui resetto i dati dei campi appena creati
					foreach ($override_list_setting[$key_list] as $ov_key=>$ov_val) {
						$post->post_content['list_setting'][$key_list][$ov_key] = $ov_val;
					}
				}
			}
		}
		
		// salvo 
		$table_model->update_items_with_setting($post);
		if (is_array($table_model->items) && count($table_model->items) > 0) {
			$post->post_content['schema'] = reset($table_model->items);
		} else {
			$post->post_content['schema'] = $table_model->get_schema();
		}
		foreach ($post->post_content['list_setting'] as $key=>$value) {
			if (!isset($setting_custom_list[$key])) {
				unset($post->post_content['list_setting'][$key]);
			} else {
				if (is_a($value, 'admin_form\ADFO_list_setting')) {
					$post->post_content['list_setting'][$key] = $value->get_for_saving_in_the_db();
				}
			}
		}
		// la configurazione delle tabelle
		
		$post->post_content['form_table'] = [];
		$post->post_content['primaries'] = $table_model->get_pirmaries();	
		if (isset($_REQUEST['fields_info']) && is_countable($_REQUEST['fields_info'])) {
			
			foreach ($_REQUEST['fields_info'] as  $ft_info) {
				$ft_array = json_decode(wp_unslash($ft_info), true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					return false;
				}
				$ft = sanitize_text_field($ft_array['fields_name']);
				$field_table = sanitize_text_field($ft_array['fields_table']);
				if (in_array($field_table, $meta_data_removed)) continue;
				//print "<p>FIELD_TABLE: ".$field_table."</p>";
				if (isset($_REQUEST['table_options'][$field_table])) {
					$table_option = maybe_unserialize(wp_unslash($_REQUEST['table_options'][$field_table]));
					if (is_array($table_option) && $table_option['type'] == 'METADATA') {
						$_REQUEST['table_allow_create'][$field_table] = 'HIDE';
						$_REQUEST['table_show_title'][$field_table] = 'HIDE';
						$_REQUEST['table_frame_style'][$field_table] = 'WHIDE';
						$_REQUEST['table_title'][$field_table] = '';
						$_REQUEST['table_description'][$field_table] = '';
						$_REQUEST['table_module_type'][$field_table] = 'EDIT';
					}
				}
				$title = (isset($_REQUEST['table_title'][$field_table])) ? sanitize_text_field( wp_unslash($_REQUEST['table_title'][$field_table])) : '';
				$table_description = (isset($_REQUEST['table_description'][$field_table])) ? wp_kses_post( wp_unslash($_REQUEST['table_description'][$field_table])) : '';
				$table_options = (isset($_REQUEST['table_options'][$field_table])) ? sanitize_text_field(wp_unslash($_REQUEST['table_options'][$field_table])) : '';
				$post->post_content['form_table'][$field_table] = [
					'allow_create'  => sanitize_text_field($_REQUEST['table_allow_create'][$field_table]),
					'show_title' 	=> sanitize_text_field($_REQUEST['table_show_title'][$field_table]),
					'frame_style' 	=> sanitize_text_field($_REQUEST['table_frame_style'][$field_table]),
					'title' 		=> $title,
					'description' 	=> $table_description, 
					'module_type'   => sanitize_text_field($_REQUEST['table_module_type'][$field_table]),
					'table_options'  => $table_options,
					'order'   		=> absint($_REQUEST['table_module_order'][$field_table])
				];
			}
		}
		
		if (isset($_REQUEST['link_table']) && isset($_REQUEST['link_list_column']) && isset($_REQUEST['link_table_column'])) {	
			$alias = ADFO_fn::get_table_alias(sanitize_text_field($_REQUEST['link_table']), $post->post_content['sql'], sanitize_text_field($_REQUEST['link_table_column']));
			$new_list = ['table'=>sanitize_text_field($_REQUEST['link_table']), 'list'=>sanitize_text_field($_REQUEST['link_list_column']), 'column'=>sanitize_text_field($_REQUEST['link_table_column']), 'alias'=>sanitize_text_field($alias)];
			
			if (!isset($post->post_content['link_form_table'])) {
				$post->post_content['link_form_table'] = [];
			}
			$post->post_content['link_form_table'][] = $new_list;
		}
		// Rebuild di sql_from
		
		$from = [];
		foreach ($from_query as $f) {
			$from[$f[1]] = $f[0]; 
		}
		$post->post_content['sql_from'] = $from;
		// Rebuild dei delete_params (remove_tables_alias)
		if (is_a($post->post_content['delete_params'], 'admin_form\DbpDs_list_delete_params')) {
			$new_table_alias = [];
			foreach ($post->post_content['delete_params']->remove_tables_alias as $k=>$v) {
				if (array_key_exists($k, $from)) {
					$new_table_alias[$k] = $v;
				}
			}
			$post->post_content['delete_params']->remove_tables_alias = $new_table_alias;
		}
		ADFO_fn::save_list_config($id, $post->post_content);	
		return true;
	}

	/**
	 * Mostro la pagina list_example con gli esempi di codice della lista
	 * @since 1.8.0
	 */
	public function list_example() {
		$file = plugin_dir_path( __FILE__  );
		wp_register_style( 'dbp_frontend_css',  plugins_url( 'frontend/admin-form.css',  $file), false, ADFO_VERSION);
		wp_enqueue_style( 'dbp_frontend_css' );

		$id = (isset($_REQUEST['dbp_id'])) ? absint($_REQUEST['dbp_id']) : 0;
		if ($id == 0 || !current_user_can('administrator')) {
			return false;
		}
		$post = ADFO_functions_list::get_post_dbp($id);
		ADFO_functions_list::no_post_dbp($post);
		$render_content = "/af-content-list-example.php";
		$list_title = "Code examples";
		require(dirname( __FILE__ ) . "/partials/af-page-base.php");
	}


	/**
	 * Mostra una pagina che presenta il plugin pro
	 */
	public function database_presentation() {
		wp_enqueue_style( 'admin-form-css' , plugin_dir_url( __FILE__ ) . 'css/admin-form.css',[],ADFO_VERSION);
		
		require(dirname( __FILE__ ) . "/partials/af-page-database_presentation.php");
	}

	private function count_recursive($arr) {
		$count = 0;
		foreach ($arr as $item) {
			if (is_array($item)) {
				$count += self::count_recursive($item);
			} else {
				$count++;
			}
		}
		return $count;
	}
}
