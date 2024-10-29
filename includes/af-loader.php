<?php
/**
 * Gestisco il filtri e hook (prevalentemente le chiamate ajax amministrative)
 *
 * @package    DATABASE TABLE
 * @subpackage DATABASE TABLE/INCLUDES
 * @internal
 */
namespace admin_form;

if (!defined('WPINC')) die;
class  ADFO_admin_loader {
	/**
	 * @var Object $saved_queries le ultime query salvate per tipo
	 */
	public static $saved_queries;

	public function __construct() {
		self::$saved_queries = (object)[];
		add_action( 'admin_menu', [$this, 'add_menu_page'] );
		// aggiungo eventuali pagine di menu
		add_action('admin_menu',  [$this, 'init_add_menu_page'] );
		
		add_action('admin_enqueue_scripts', [$this, 'codemirror_enqueue_scripts']);

		// L'ajax per la richiesta dell'elenco dei valori unici per mostrarli nei filtri di ricerca
		add_action( 'wp_ajax_af_distinct_values', [$this, 'af_distinct_values']);
		add_action( 'wp_ajax_af_autocomplete_values', [$this, 'af_autocomplete_values']);
		// l'ajax per vedere il dettaglio di una sola riga estratta da una query
		add_action( 'wp_ajax_af_view_details', [$this, 'af_view_details']);
		
		/**
		 * l'ajax per l'edit record
		 */ 
		add_action( 'wp_ajax_af_edit_details_v2', [$this, 'af_edit_details_v2']);
		// l'ajax per il salvataggio di un record
		add_action( 'wp_ajax_af_save_details', [$this, 'af_save_details']);

		// attivo i post_type se presenti
		add_action('init', [$this, 'init_post_type']);

		// Questa una chiamata che deve rispondere un csv
		if (is_admin() && isset($_REQUEST['page'])  && 
		in_array($_REQUEST['page'], ['admin_form']) ) { 
			add_action('init',  [$this, 'init_get_msg_cookie'] );
		}

		// Aggiungo css e js nel frontend
		add_action( 'wp_enqueue_scripts', [$this, 'frontend_enqueue_scripts'] );

		// carico una lista frontend in ajax
		add_action ('wp_ajax_nopriv_dbp_get_list', [$this,'get_list']); 
        add_action ('wp_ajax_dbp_get_list', [$this,'get_list']);

		// carico i dati del dettaglio di un record in ajax
		add_action ('wp_ajax_nopriv_dbp_get_detail', [$this,'get_detail']); 
        add_action ('wp_ajax_dbp_get_detail', [$this,'get_detail']);

		/**
		 * @since v1.8.0
		 * Chiamata per esportare i dati di una lista
		 */		
		add_action( 'wp_ajax_adfo_frontend_export_data', [$this, 'adfo_frontend_export_data'] );
        add_action( 'wp_ajax_nopriv_adfo_frontend_export_data', [$this,'adfo_frontend_export_data'] );


		//registro il voto.
		add_action ('wp_ajax_af_record_preference_vote', [$this,'record_preference_vote']);

		// l'ajax per confermare l'eliminazione di uno o più record 
		add_action( 'wp_ajax_af_delete_confirm', [$this, 'af_delete_confirm']);

		// l'ajax per settare in trash uno o più record 
		add_action( 'wp_ajax_af_trash', [$this, 'af_trash']);
		add_action( 'wp_ajax_af_untrash', [$this, 'af_untrash']);

		// l'ajax per salvare un campo input della lista
		add_action( 'wp_ajax_af_update_table_list_input_value', [$this, 'update_table_list_input_value']);

		// l'ajax per salvare un ordine della lista
		add_action( 'wp_ajax_af_update_table_list_order_value', [$this, 'update_table_list_order_value']);


		add_action('in_admin_header', function () {	
			if (is_admin() && isset($_REQUEST['page'])  && 
				in_array($_REQUEST['page'],['admin_form', 'admin_form_docs']) ) { 
				remove_all_actions('admin_notices');
				remove_all_actions('all_admin_notices');
			}
		}, 1000);

		if (is_admin())  {
			require_once(ADFO_DIR . "includes/af-loader-documentation.php");
			$ADFO_loader_documentation = new ADFO_loader_documentation();
		}
		// Carico eventuali altri loader
		
		require_once(ADFO_DIR . "includes/af-list-loader.php");
		
		

		add_filter('dbp_table_status', [$this, 'publish_wp_tables'], 2, 2);
	}

	/**
	 * Aggiunge la voce di menu e carica la classe che gestisce la pagina amministrativa
	 */
	public function add_menu_page() {
		require_once(ADFO_DIR . "admin/class-af-list-admin.php");
		require_once(ADFO_DIR . "admin/class-af-docs-admin.php");
		$db_admin = new ADFO_list_admin();
		add_menu_page( 'Admin Form', 'ADFO', 'manage_options', 'admin_form', [$db_admin, 'controller'], 'dashicons-database-view');
		$db_docs = new ADFO_docs_admin();
		add_submenu_page(  'admin_form', 'Documentation', 'Documentation', 'manage_options', 'admin_form_docs', [$db_docs, 'controller']);
		if (!defined('ADFO_PRO_VERSION')) {
			add_submenu_page(  'admin_form', 'Database', 'Database', 'manage_options', 'database_presentation', [$db_admin, 'database_presentation']);
		}
	

	}
	/**
	 * Gli script per far funzionare l'editor
	 */
	public function codemirror_enqueue_scripts() {
		if ((isset($_REQUEST['page']) && (substr($_REQUEST['page'],0,3) == 'dbp' || $_REQUEST['page'] == "admin_form"))) {
			if ( ! class_exists( '_WP_Editors', false ) ) {
				require( ABSPATH . WPINC . '/class-wp-editor.php' );
			}
			wp_enqueue_editor();

			$settings = wp_get_code_editor_settings([]);
			// copio wp_enqueue_code_editor per escludere 'false' === wp_get_current_user()->syntax_highlighting
			
			if ( empty( $settings ) || empty( $settings['codemirror'] ) ) {
				return false;
			}

			wp_enqueue_script( 'code-editor' );
			wp_enqueue_style( 'code-editor' );

			wp_enqueue_script( 'csslint' );
			wp_enqueue_script( 'htmlhint' );
			wp_enqueue_script( 'jshint' );
			wp_add_inline_script( 'code-editor', sprintf( 'jQuery.extend( wp.codeEditor.defaultSettings, %s );', wp_json_encode( $settings ) ) );
			
		}
	}

	/**
	 * L'ajax per la richiesta dell'elenco dei valori unici per mostrarli nei filtri di ricerca 
	 *  $_REQUEST = array(5) {
	 *	     ["sql"]=> string(26) "SELECT * FROM  `wpx_posts`"["rif"]=> string(21) "wpx_posts_post_author", ["column"]=> string(25) "`wpx_posts`.`post_author`", ["action"]=> string(19) "af_distinct_values", ["table"]=> string(9) "wpx_posts"
	 *  }
	 *  [{c=>il testo del campo distinct, p=>l'id se serve di filtrare per id oppure -1 n il numero di volte che compare},{}] | false if is not a select query 
	 *
	 */
	public function af_distinct_values() {
		global $wpdb;
		ADFO_fn::require_init();
		if (!isset($_REQUEST['column'])) {
			wp_send_json(['error' => 'no_column_selected']);
			die();
		}
		$table = (isset($_REQUEST['table'])) ? sanitize_text_field($_REQUEST['table']) : '';
		$model = new ADFO_model(	$table ); // è la tabella a cui appartiene il singolo campo!
		
		if (isset($_REQUEST['sql'])) {
			$req_sql =html_entity_decode( wp_kses_post( wp_unslash($_REQUEST['sql'])));
			$model->prepare($req_sql);
			if (!isset($_REQUEST['dbp_id'])) {
				$model->removes_column_from_where_sql(sanitize_text_field($_REQUEST['column']));
			}
		}
		if (isset($_REQUEST['filter_distinct'])) {
			$result = $model->distinct(sanitize_text_field($_REQUEST['column']), sanitize_text_field($_REQUEST['filter_distinct']));
		} else {
			$result = $model->distinct(sanitize_text_field($_REQUEST['column']));
		}
		
		$error = "";
		$count = 0;
		if ($model->last_error != "" || !is_countable($result)) {
			$error = __('Option not available for this query '.$model->last_error." ".$model->get_current_query(),'admin_form');
			$result = [];
		} else if ( count($result) >= 1000) {
			//$error = __('The column has too many values to show.<br><i style="font-size:.8em">You can use the field above to filter the results</i>','admin_form');
			$count = count ($result);
			if (count($result) >= 5000) {
				$count = "5000+";
			} 
			$result = [];
			
		} else {
			$count = count ($result);
		}
		
		wp_send_json(['error' => $error, 'result' => $result, 'rif' => sanitize_text_field($_REQUEST['rif']), 'count'=>$count, 'filter_distinct'=>sanitize_text_field(@$_REQUEST['filter_distinct'] )]);
		die();
	}
	
	/**
	 * L'ajax per la richiesta dell'elenco dei valori unici per mostrarli nei filtri di ricerca 
	 *  $_REQUEST = array(5) {
	 *	     ["sql"]=> string(26) "SELECT * FROM  `wpx_posts`"["rif"]=> string(21) "wpx_posts_post_author", ["column"]=> string(25) "`wpx_posts`.`post_author`", ["action"]=> string(19) "af_distinct_values", ["table"]=> string(9) "wpx_posts"
	 *  }
	 *  [{c=>il testo del campo distinct, p=>l'id se serve di filtrare per id oppure -1 n il numero di volte che compare},{}] | false if is not a select query 
	 */
	public function af_autocomplete_values() {
		global $wpdb;
		ADFO_fn::require_init();
		if (!isset($_REQUEST['params'])) {
			wp_send_json(['error' => 'no_params_selected']);
			die();
		}
		$params = ADFO_fn::sanitize_text_recursive($_REQUEST['params']);
		$result = [];
		$error = "";
		// TODO REQUEST TABLE E COLUMN devono trasformarsi in un generico attributes... perché esistono più tipi di distinct values (e in alcuni casi potrebbero non essere distinct (?!))
		if (isset($params['type']) && $params['type'] == "post") {
			$count = 2000;
			$array_params = array(
				'post_limits'      => 100,
				'orderby'          => 'post_title',
				'order'            => 'ASC',
				'post_type'        => $params['post_types'],
				'post_status'      => 'publish'
			);
			if (isset($params['cats']) && is_countable(($params['cats']))) {
				$array_params['category__in'] = $params['cats'];
			}
			if (isset($_REQUEST['filter_distinct'])) {
				$array_params['search'] = sanitize_text_field($_REQUEST['filter_distinct'])."*";
			}
			$posts = query_posts($array_params);
			foreach($posts as $rl) {
				$result[] = ['c' => wp_trim_words(strip_tags($rl->post_title), 10), 'p'=>$rl->ID];
			}
			if (count($result) < 100) {
				$count = count($result);
			}
	

		} else if (isset($params['type']) && $params['type'] == "user") {
			$count = 2000;
			$array_params = array('number'=>100);
			if (isset($params['roles']) && is_countable(($params['roles']))) {
				$array_params['role__in'] = $params['roles'];
			}
			if (isset($_REQUEST['filter_distinct'])) {
				$array_params['search'] = sanitize_text_field($_REQUEST['filter_distinct'])."*";
			}
			$users = get_users( $array_params );
			foreach($users as $rl) {
				$result[] = ['c' => wp_trim_words(strip_tags($rl->user_login), 10), 'p'=>$rl->ID];
			}
			if (count($result) < 100) {
				$count = count($result);
			}
		} else if (isset($params['type']) && $params['type'] == "lookup") {

			/*
			 *  NON VA BENE! devo passare id della lista e field del lookup
			params[lookup_id]: dbt_id
			params[lookup_sel_val]: field_key
			*/
			$count = -1;
			$post       = ADFO_functions_list::get_post_dbp(absint($params['lookup_id']));
			if ($post == false) {
				wp_send_json(['error' => 'no_post_found']);
			}
 			$lookup_table = $lookup_sel_val = $lookup_sel_txt = $lookup_where = '';
			foreach ($post->post_content['form'] as $form) {
				if ($form['name'] == $params['lookup_sel_val']) {
					$lookup_table = $form['lookup_id'];
					$lookup_sel_val = $form['lookup_sel_val'];
					$lookup_sel_txt = $form['lookup_sel_txt'];
					$lookup_where = trim($form['lookup_where']);
				}
			}
			if ($lookup_table != "") {
				$table_model = new ADFO_model($lookup_table); // è la tabella a cui appartiene il singolo campo!
				//TODO questo lo prendo da dbp_id se è presente altrimenti dalla query
				if ($lookup_where != "") {
					$sql = $table_model->get_current_query()." WHERE ".$lookup_where;
					$table_model->prepare($sql);
				}
				$table_model->list_change_select('`'.esc_sql($lookup_sel_val).'` AS val, `'.esc_sql($lookup_sel_txt).'` AS txt');
				if (isset($_REQUEST['filter_distinct']) && $_REQUEST['filter_distinct'] != "") {
					$table_model->list_add_where([['op'=>'LIKE%','column'=>$lookup_sel_txt, 'value'=>sanitize_text_field($_REQUEST['filter_distinct'])],
					['op'=>'NOT NULL','column'=>$lookup_sel_txt, 'value'=>'']]);
				} else {
					$table_model->list_add_where([['op'=>'NOT NULL','column'=>$lookup_sel_txt, 'value'=>'']]);
				}
				$table_model->list_add_limit(0, 100);
				$table_model->list_add_order('`'.$lookup_sel_txt.'`', 'ASC');
				//$error = "sql: | ".$table_model->get_current_query();
				$items = $table_model->get_list();
				$count = $table_model->get_count();
				if ($table_model->last_error != "" || !is_countable($result)) {
					$error = __('Query Error '.$table_model->last_error, 'admin_form');
				} else {
					array_shift($items);
					foreach($items as $rl) {
						$result[] = ['c' => wp_trim_words(strip_tags($rl->txt), 10), 'p'=>$rl->val];
					}
					$count = $table_model->get_count();
				}
			}

		} else {
			$model = new ADFO_model($params['table']); // è la tabella a cui appartiene il singolo campo!
			//TODO questo lo prendo da dbp_id se è presente altrimenti dalla query
			if (isset($_REQUEST['sql'])) {
				$model->prepare(wp_unslash($_REQUEST['sql']));
			}
			$result = $model->distinct($params['column'], sanitize_text_field($_REQUEST['filter_distinct']));
			
			$error = "";
			$count = 0;
			if ($model->last_error != "" || !is_countable($result)) {
				$error = __('Option not available for this query','admin_form');
				$result = [];
			} else if ( count($result) >= 1000) {
				//$error = __('The column has too many values to show.<br><i style="font-size:.8em">You can use the field above to filter the results</i>','admin_form');
				$count = count ($result);
				if (count($result) >= 5000) {
					$count = "5000+";
				} 
				$result = [];
				
			} else {
				$count = count ($result);
			}
		}
		wp_send_json([
			'error' => $error, 
			'result' => $result, 
			'rif' => sanitize_text_field($_REQUEST['rif']), 
			'count'=>$count, 
			'filter_distinct' => sanitize_text_field(@$_REQUEST['filter_distinct']) ]);
		die();
	}

	/**
	 * Aggiungo le voci di menu delle liste
	 * postmeta _dbp_admin_show 
	 * ```json
	 * {"page_title":"dbp_174","menu_title":"connection","capability":"manage_options","slug":"dbp_174"}
	 * ```
	 */
	public function init_add_menu_page() {
		global $wpdb;
		$pages = $wpdb->get_results("SELECT * FROM ".$wpdb->postmeta ." WHERE meta_key = '_dbp_admin_show'");
		if (is_countable($pages)) {
			require_once(ADFO_DIR . "admin/class-af-admin-menu.php");
			$db_admin = new ADFO_admin_list_menu();
			foreach ($pages as $page) {
				$page_data = maybe_unserialize(@$page->meta_value);
				if (is_countable($page_data) && isset($page_data['show']) && $page_data['show'] == 1 && isset($page_data['status']) && $page_data['status'] != 'trash') {
					add_menu_page($page_data['page_title'], $page_data['menu_title'], $page_data['capability'], $page_data['slug'], [$db_admin, 'controller'], $page_data['menu_icon'], $page_data['menu_position']);
				}
			}
			
		}
	}

	/**
	 * Aggiungo gli script frontend
	 */
	public function frontend_enqueue_scripts() {
		$file = plugin_dir_path( __FILE__  );
		$dbp_css_ver = date("ymdGi", filemtime( plugin_dir_path($file) . 'frontend/admin-form.css' ));
		$dbp_js_ver = date("ymdGi", filemtime( plugin_dir_path($file) . 'frontend/admin-form.js' ));
		wp_register_style( 'dbp_frontend_css',  plugins_url( 'frontend/admin-form.css',  $file), false,   $dbp_css_ver );
		wp_enqueue_style( 'dbp_frontend_css' );
		wp_register_script( 'dbp_frontend_js',  plugins_url( 'frontend/admin-form.js',  $file), false,   $dbp_js_ver, true);
		//	wp_add_inline_script( 'mytheme-typekit', 'try{Typekit.load({ async: true });}catch(e){}' );
		wp_add_inline_script( 'dbp_frontend_js', 'dbp_post = "'.esc_url( admin_url('admin-ajax.php')).'";', 'before' );
		wp_enqueue_script( 'dbp_frontend_js' );
	}

    /**
     * Restituisce una lista chiamata in ajax (per il frontend - da verificare che sia nel singolo giorno)
     */
    public function get_list() {
	    ADFO_fn::require_init();
	    $result['div'] = sanitize_text_field($_REQUEST['dbp_div_id']);
		if (isset($_REQUEST['dbp_prefix'])) {
			$prefix = sanitize_text_field($_REQUEST['dbp_prefix']);
		} else {
			$prefix = "";
		}
	    if (isset($_REQUEST['dbp_extra_attr'])) {
		    $dbp_extra_attr = ADFO_fn::sanitize_text_recursive($_REQUEST['dbp_extra_attr']);
		    $extra_attr = json_decode(base64_decode($dbp_extra_attr), true);
		    if (json_last_error() == JSON_ERROR_NONE) {
			    if (isset($extra_attr['request'])) {
				    foreach ($extra_attr['request'] as $key=>$val) {
					   $_REQUEST[$key] = $val;
				    }
					pinacode::set_var('request', $extra_attr['request']);
			    }
			    //if (isset($extra_attr['params'])) {
				//	pinacode::set_var('params', $extra_attr['params']);
				//}
				if (isset($extra_attr['data'])) {
					pinacode::set_var('data', $extra_attr['data']);
				}
				$result['html'] = ADFO::get_list(absint($_REQUEST['dbp_list_id']), true, $extra_attr['params'], $prefix);
			} else {
				$result['html'] = 'OPS an error occurred';
			}
		} else {
			$result['html'] = ADFO::get_list(absint($_REQUEST['dbp_list_id']), true, [], $prefix);
		}
		wp_send_json($result);
	    die();
    }

	/**
	 * Carico i dettagli di una lista in detail
	 *
	 * @return void
	 */
	public function get_detail() {	
        ADFO_fn::require_init();
		$dbp_ids = sanitize_text_field($_REQUEST['dbp_ids']);
		$ids = ADFO_fn::ids_url_decode($dbp_ids);
		$dbp_id = absint($_REQUEST['dbp_id']);
		if ($dbp_id == 0 && !(is_array($ids) || is_numeric($ids))) return;
		
		print ADFO::get_single($dbp_id, $ids);
		
		
		die();
	}

	/**
	 * Restituisce il risultato di una query per una riga
	 */
	public function af_view_details() {
		ADFO_fn::require_init();
		$json_send = ['error' => '', 'items' => ''];
		
		if (!isset($_REQUEST['ids']) || !is_countable($_REQUEST['ids'])) {
			$json_send['error'] = __('I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement.', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		$table_model = $this->get_table_model_for_sidebar();
		$table_model->remove_limit();
		$table_items = $table_model->get_list();

		if (is_countable($table_items) && count($table_items) == 2) {
			$item = array_pop($table_items);
			foreach ($item as &$val) {
				$val = ADFO_fn::format_single_detail_value($val);
			}
			$json_send['items'] = [$item]; 	
		} else if (is_countable($table_items) && count($table_items) > 2 && count($table_items) < 200) {
			// Sono più risultati quindi raggruppo i risultati per tabella e mostro solo i gruppi differenti. 
			$items = ADFO_fn::convert_table_items_to_group($table_items);
			if (count($items) > 1) {
				$json_send['error'] = __('The query responded with multiple lines. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement', 'admin_form');
			}
		
			$json_send['items'] = $items;
		} else if (is_countable($table_items) && count($table_items) > 2 && count($table_items) >= 200) {
			$json_send['error'] = __('I am sorry but I cannot show the requested details because I have found more than 200 results!. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement. Check that the tables have a unique auto increment primary key.', 'admin_form');
		}else {
			$json_send['error'] = __('Strange, I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement. Check that the tables have a unique auto increment primary key.', 'admin_form');
		}
		wp_send_json($json_send);
		die();
	}

	/**
	 * Genera i parametri per la creazione della form (add o edit) nella sidebar
	 */
	public function af_edit_details_v2() {
		ADFO_fn::require_init();
		$json_send = ['error' => '', 'items' => ''];
		if (isset($_REQUEST['div_id'])) {
			$json_send['div_id'] = sanitize_text_field($_REQUEST['div_id']);
		}
		
		if (!isset($_REQUEST['dbp_id']) && !isset($_REQUEST['sql'])) {
			$json_send['error'] = __('There was an unexpected problem, please try reloading the page.', 'admin_form');
			wp_send_json($json_send);
			die();
		}

		// aggiungo eventuali dbp_extra_attr (deve essere una funzione!)
		if (isset($_REQUEST['dbp_extra_attr'])) {
			$dbp_extra_attr = ADFO_fn::sanitize_text_recursive($_REQUEST['dbp_extra_attr']);
			$extra_attr = json_decode(base64_decode($dbp_extra_attr), true);
			if (json_last_error() == JSON_ERROR_NONE) {
				if (isset($extra_attr['request'])) {
					foreach ($extra_attr['request'] as $key=>$val) {
						if (!isset($_REQUEST[$key])) {
							$_REQUEST[$key] = $val;
						}
					}
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

		$json_send['edit_ids'] = ADFO_fn::get_request('ids', 0);
		
        $form = new ADFO_class_form($_REQUEST['dbp_id']);
		$json_send['dbp_id'] = sanitize_text_field($_REQUEST['dbp_id']);
        
		if (isset($_REQUEST['ids']) && is_countable($_REQUEST['ids'])) {
			$ids = ADFO_fn::sanitize_absint_recursive($_REQUEST['ids']);
			$items = $form->get_data($ids);
		} else {
			$items = [];
		}

        list($settings, $table_options) = $form->get_form();
        $json_send['items'] = $form->convert_items_to_groups($items, $settings, $table_options);
		
		$json_send['params'] = $form->data_structures_to_array($settings);
		$json_send['table_options'] = $form->data_structures_to_array($table_options);	
		// DEFAULT RICALCOLATI
		if (isset($_REQUEST['dbp_id'])) {
			foreach ($json_send['params'] as $group_key => &$group_param) {
				foreach ($group_param as $field_key => &$field_param) {
					if (isset($field_param['default_value']) && $field_param['default_value'] != "") {
						
						$field_param['default_value'] = PinaCode::execute_shortcode($field_param['default_value']);
						
						if ($field_param['form_type'] == 'DATE') {
							if (strtotime($field_param['default_value']) !== false) {
								$field_param['default_value'] = date('Y-m-d', strtotime($field_param['default_value']));
							} else {
								$field_param['default_value'] = '';
							}
						}
						if ($field_param['form_type'] == 'DATETIME') {
							if (strtotime($field_param['default_value']) !== false) {
								$field_param['default_value'] = date('Y-m-d H:i:s', strtotime($field_param['default_value']));
							} else {
								$field_param['default_value'] = '';
							}
						}
						if ($field_param['form_type'] == 'TIME') {
							if (strtotime($field_param['default_value']) !== false) {
								$field_param['default_value'] = date('H:i', strtotime($field_param['default_value']));
							} else {
								$field_param['default_value'] = '';
							}
						}

					}
					// TODO: se sono lookup, post user o media gallery con default_value
					// devo trovare gli altri dati per compilare il campo.
					if ($field_param['form_type'] == 'LOOKUP') {
						if (isset($field_param['default_value']) && isset($field_param['default_value']) && $field_param['default_value'] != "") {
							foreach ($json_send['items'] as $key=>&$item) {
								$is_new =  (!isset($json_send['table_options'][$key][$group_key]['pri_value']) || absint($json_send['table_options'][$key][$group_key]['pri_value']) == 0);
								if ($item[$group_key]->$field_key == "" && $is_new) {
									
									$field_param['custom_value'] = $field_param['default_value'];
									$item[$group_key]->$field_key = $field_param['default_value'];
									
									$table_model2 = new ADFO_model($field_param['lookup_id']);
								
									$table_model2->list_change_select('`'.esc_sql($field_param['lookup_sel_val']).'` AS val, `'.esc_sql($field_param['lookup_sel_txt']).'` AS txt');
									
									$table_model2->list_add_where([['op'=>'=','column'=>$field_param['lookup_sel_val'], 'value'=>$field_param['default_value']]]);
									$table_model2->list_add_limit(0,1);
									$res = $table_model2->get_list();
									if (is_array($res) && count($res) == 2) {
										$row = array_pop($res);
										$field_param['custom_value'] = $row->txt;
										$item[$group_key]->$field_key = $row->val;
									}

								}
							}
						}
						// risetto i parametri da passare al javascript che disegna la form passandogli l'id della lista e il nome del campo così quando invia l'ajax ricalcolo tabella field e where sono nel backend .
						$field_param['lookup_id'] = absint($_REQUEST['dbp_id']);
						$field_param['lookup_sel_val'] = $field_param['name'];
						unset($field_param['lookup_sel_txt']);
						unset($field_param['lookup_where']);
						
					}
					if ($field_param['form_type'] == 'USER' && isset($field_param['default_value']) && $field_param['default_value'] > 0) {
						$user = get_user_by('id',absint($field_param['default_value']));
						if (is_object($user) && isset($user->user_login)) {
							foreach ($json_send['items'] as $key=>&$item) {
								$is_new =  (!isset($json_send['table_options'][$key][$group_key]['pri_value']) || absint($json_send['table_options'][$key][$group_key]['pri_value']) == 0);
								if ($item[$group_key]->$field_key == "" && $is_new) {
									$field_param['custom_value'] = $user->user_login;
									$item[$group_key]->$field_key = absint($field_param['default_value']);
								}
							}
						}
					}

					if ($field_param['form_type'] == 'TIME' ) {
						foreach ($json_send['items'] as $key=>&$item) {
							if (isset($item[$group_key]->$field_key) && $item[$group_key]->$field_key != '') {
								$hour = floor($item[$group_key]->$field_key / 3600);
								$min = floor(($item[$group_key]->$field_key - ($hour * 3600)) / 60) ;
								$item[$group_key]->$field_key = str_pad($hour,2,'0', STR_PAD_LEFT).':'.str_pad($min,2,'0', STR_PAD_LEFT);
							
							}
						}
					}

					if ($field_param['form_type'] == 'POST' && isset($field_param['default_value']) && $field_param['default_value'] > 0) {
						$post = get_post(absint($field_param['default_value']));
						if (is_object($post) && isset($post->post_title)) {
							foreach ($json_send['items'] as $key=>&$item) {
								$is_new =  (!isset($json_send['table_options'][$key][$group_key]['pri_value']) || absint($json_send['table_options'][$key][$group_key]['pri_value']) == 0);
								if ($item[$group_key]->$field_key == ""  && $is_new) {
									$field_param['custom_value'] = $post->post_title;
									$item[$group_key]->$field_key = absint($field_param['default_value']);
								}
							}
						}
					}
					
					if (isset($_REQUEST['clone_record']) && $_REQUEST['clone_record'] == 'clone') {
						foreach ($json_send['items'] as $key=>&$item) {
							$remove = (isset($field_param['is_pri']) && $field_param['is_pri'] === 1);
							$remove =  ($remove || (isset($field_param['form_type']) && $field_param['form_type'] == 'CALCULATED_FIELD'));
							if (!$remove) continue;
							$field_param['default_value'] = '';
							$item[$group_key]->$field_key = '';
						}
					}
				}
			}
		}
		// FINE DEFAULT RICALCOLATI
		$json_send['buttons'] =  $form->get_btns_allow(); //['save'=>false,'delete'=>true];
		wp_send_json($json_send);
		die();
	}

	/**
	 * Salva un record
	 */
	public function af_save_details() {
		global $wpdb;
		ADFO_fn::require_init();
		$json_result = ['reload'=>0,'msg'=>'','error'=>''];
		if (isset($_REQUEST['div_id'])) {
			$json_result['div_id'] = sanitize_text_field($_REQUEST['div_id']);
		}
		$queries_executed = [];
		$query_to_execute = [];
		$dbp_id = 0;
		$form_dbp_id = false;
		$request_edit_table = $_REQUEST['edit_table'];

		// se è una lista ok, altrimenti solo gli amministratori possono salvare dati
		if (isset($_REQUEST['dbp_global_list_id']) && absint($_REQUEST['dbp_global_list_id']) > 0) {
			$dbp_id = absint($_REQUEST['dbp_global_list_id']);
			$form_dbp_id = new ADFO_class_form($dbp_id);
			
		} else {
			if( !current_user_can('administrator') ) {
				$json_result['result'] = 'nook';
				$json_result['error'] = __('You do not have permission to access this content!', 'admin_form');
				wp_send_json($json_result);
				die();
			}
		}
		
		foreach ($request_edit_table as $form_value) {
			$alias_table = "";
			foreach ($form_value as $table=>$rows) {
				//print $table;
				$primary_key = ADFO_fn::get_primary_key(sanitize_text_field($table));
				$primary_field = $fields_names = [];
				
				foreach ($rows as $key=>$row) {
					if ($key == $primary_key) {
						$primary_field = $row;
					} else {
						$fields_names[$key] = $row;
					}
				}
				// ciclo per più query.
				$exists = 0;
				$primary_value = "";
				// ciclo quante volte si ripete la chiave primaria per la tabella (ogni volta è una nuova riga)
				foreach ($primary_field as $key => $pri) {
					$sql = [];
					$exists = 0;
					$primary_value = $pri;
					// l'alias della tabella sta in un campo nascosto e serve per definire i pinacode
					if (isset($fields_names["_dbp_alias_table_"][$key]))  {
						$alias_table = $fields_names["_dbp_alias_table_"][$key];
					}
					if ($alias_table == "") {
						$alias_table = $table;
					}
					//print "ALIAS TABLE:" . $alias_table;
					$pri_name = ADFO_fn::clean_string($alias_table).'.'.$primary_key;
					PinaCode::set_var($pri_name, $primary_value);
			    	//	print "<p>PRI NAME".$pri_name." </p>";

					// preparo i campi da salvare 
					// Setto le variabili per i campi calcolati // DA TESTARE
					foreach ($fields_names as $kn=>$fn) {
						if ($kn != "_dbp_alias_table_") {
							// ?
							if (is_countable($fn[$key])) {
								$fn[$key] = maybe_serialize($fn[$key]);
							}
							$fn[$key] = wp_kses_post(wp_unslash( $fn[$key] ));
							//$sql[$kn] = $fn[$key];
							
							PinaCode::set_var(ADFO_fn::clean_string($alias_table).".".$kn, $fn[$key]);
							PinaCode::set_var("data.".$kn, $fn[$key]);
						}
					}

					foreach ($fields_names as $kn=>$fn) {
						if ($kn != "_dbp_alias_table_") {
							if ( $fn[$key] != "") {
								$sql[$kn] = wp_kses_post(wp_unslash( $fn[$key] ));
							} else {
								$sql[$kn] = '';
							}
						}
					}
					
					// se primary key è un valore 
					if ($primary_value != "") {
						$exists = $wpdb->get_var($wpdb->prepare('SELECT count(*) as tot FROM `'.ADFO_fn::sanitize_key($table).'` WHERE `'.ADFO_fn::sanitize_key($primary_key).'` = %s', absint($primary_value)));
						if ($exists == 0) {
							$sql[$primary_key] = $primary_value;
						}
					} else {
						$sql[$primary_key] = $primary_value;
					}
					$setting = false;
					$option = false;
					
					if (is_a($form_dbp_id, 'admin_form\ADFO_class_form')) {
						$setting = $form_dbp_id->find_setting_from_table_field($alias_table);
						$option =  $form_dbp_id->find_option_from_table_field($alias_table);
						if ($option->module_type != 'EDIT' || $option->table_status == "CLOSE") continue;
						// METADATA DELETE vedo se c'è una tabella assegnata come metadata in quel caso gestisco l'eliminazione del record se vuoto
						if (isset($form_dbp_id->post->post_content['sql_metadata_table']) ) {
							$metadata_table_info = explode("::", $form_dbp_id->post->post_content['sql_metadata_table']);
							if (count($metadata_table_info) == 2 && $table == $metadata_table_info[1] && isset($sql['meta_value'])) {
								if ($sql['meta_value'] == "") { 
									// rimuovo il campo
									if ($exists == 1) {
									    $query_to_execute[] = ['action'=>'delete', 'table'=>$table, 'sql_to_save'=>$sql, 'id'=> [$primary_key => $primary_value], 'table_alias'=>$alias_table, 'pri_val'=>$primary_value, 'pri_name'=>$primary_key, 'setting' => $setting];
									}
									$exists = 2;
								} else {
									// se non c'è la versione pro non posso gestire i calculated 
									// quindi parent_id e meta_key non vengono compilati correttamente!
									$metatable_info = ADFO_class_metadata::find_metadata_table_structure($metadata_table_info[1]);
									if (count($metatable_info) == 4) {
										// trovo il parent_id
										$sql[$metatable_info['parent_id']] =  "[%".$metadata_table_info[0]."]";
										// trovo il meta_key ?!?!
										$table_options = maybe_unserialize($option->table_options);
										$sql['meta_key'] = $table_options['value_key'] ;
									}
								}
							}
						}
						/**
						 * POST TYPE 
						 * @since 1.7.0
						 */ 
						if (isset($form_dbp_id->post->post_content['post_type']['name']) && $form_dbp_id->post->post_content['post_type']['name'] != "") {
							if ($table == $wpdb->posts) {
								$sql['post_type'] = $form_dbp_id->post->post_content['post_type']['name'];
							}
							$user_role = ADFO_functions_list::get_user_role($form_dbp_id->post);
							if ($user_role != 'administrator') {
								$sql['post_author'] = get_current_user_id();
							}
							if ($exists == 1) {
								if ($user_role == 'contributor' && ($sql['post_status'] == 'publish' || $sql['post_status'] == 'pending')) {
									// non posso più modificare il post
									$exists = 2;
								}
							} else {
								if ($user_role == 'contributor' && $sql['post_status'] == 'publish') {
									$sql['post_status'] = 'pending';
								}
							}
						}
					}

				
					if ($exists == 1) {
						if (count($sql) > 0) {
							$query_to_execute[] = ['action'=>'update', 'table'=>$table, 'sql_to_save'=>$sql, 'id'=> [$primary_key=>$primary_value], 'table_alias'=>$alias_table, 'pri_val'=>$primary_value, 'pri_name'=>$primary_key, 'setting' => $setting];
						}
					} else if ($exists == 0) {
						$json_result['reload'] = 1;
						if (isset($sql[$primary_key])) {
							unset($sql[$primary_key]);
						}
						if (count($sql) > 0 &&  !(isset($sql['_dbp_leave_empty_']) && $sql['_dbp_leave_empty_'] == 1 )) {
							$query_to_execute[] = ['action'=>'insert', 'table'=>$table, 'sql_to_save'=>$sql, 'table_alias'=>$alias_table, 'pri_val'=>$primary_value, 'pri_name'=>$primary_key, 'setting' => $setting];
						}
					} 
				}
				//die($pri);
			}
		}

		$ris =  ADFO_functions_list::execute_query_savedata($query_to_execute, $dbp_id, 'admin-form');
		foreach ($ris as $r) {
			if (!($r['result'] == true || ($r['result'] == false && $r['error'] == "" && $r['action']=="update"))) {	
				$json_result['error'] = ($r['error'] != "") ? $r['error'] : 'the data could not be saved';
				ADFO_fn::set_cookie('error', $json_result['error']);
				if (is_countable($queries_executed) && count($queries_executed) > 0) {
					$json_result['msg'] = sprintf( __('%s record update','admin_form'), count($queries_executed));
					ADFO_fn::set_cookie('msg', $json_result['msg']);
				}
				wp_send_json($json_result);
				die();
			} else {
				$queries_executed[] =  $r['query'];
			} 
		}

		if (is_countable($queries_executed) && count($queries_executed) > 0) {
			$json_result['msg'] = __('Saved','admin_form');
			ADFO_fn::set_cookie('msg', $json_result['msg']);
		}
		// preparo i dati da inviare per aggiornare la tabella nel frontend!
		$dbp_global_list_id = ADFO_fn::get_request('dbp_global_list_id', 0, 'absint');
		$table_model = $this->get_table_model_for_sidebar($dbp_global_list_id);
		if ($table_model != false) {

	    	// preparo i dati da inviare per aggiornare la tabella nel frontend!
			$dbp_global_list_id = ADFO_fn::get_request('dbp_global_list_id', 0, 'absint');
			if ($dbp_global_list_id > 0) {
				$post = ADFO_functions_list::get_post_dbp($dbp_global_list_id);
				if ($post == false) {
					$json_result['error'] = __('The list does not exist','admin_form');
					wp_send_json($json_result);
					die();
				}
				ADFO_functions_list::add_lookups_column($table_model, $post);
				ADFO_functions_list::add_post_type_settings($table_model, $post);
				ADFO_functions_list::add_post_user_column($table_model, $post);
			}
			$table_model->get_list();
			if ($dbp_global_list_id > 0) {
				$table_model->update_items_with_setting($post);
			}
			ADFO_fn::remove_hide_columns($table_model);
			$table_items = $table_model->items;
			if (count($table_model->items) == 2) {
				$json_result['table_item_row'] = array_pop($table_items);
			} else {
				$json_result['reload'] = 1;
			}
		}
		//$json_result['error'] = 'OPS ERRORE!!!';
		wp_send_json($json_result);
		die();
	}
	

	/**
     *  Imposta i cookie in una variabile statica e li rimuove dai cookie
     */
	function init_get_msg_cookie() {
        ADFO_fn::init_get_msg_cookie();
    }

	/**
	 * Raggruppo questo pezzettino di codice solo perché usato di continuo per preparare la query sull'edit, view, ecc..
	 */
	private function get_table_model_for_sidebar($dbp_id = 0) {
		if ($dbp_id > 0) {
			$post       = ADFO_functions_list::get_post_dbp($dbp_id);
			if ($post == false) {
				return false;
			}
			$table_model 				= new ADFO_model();
			$table_model->prepare($post->post_content['sql']);
		} 
		
		if (isset($_REQUEST['ids']) && is_countable($_REQUEST['ids'])) {
			$filter = [];
			$ids = ADFO_fn::sanitize_absint_recursive($_REQUEST['ids']);
			foreach ($ids as $column => $id) {
				$column = str_replace("`", "", $column );
				$column = "`".str_replace(".", "`.`", $column )."`";
				$filter[] = ['op' => "=", 'column' => $column, 'value' => $id];
			}
			$table_model->list_add_where($filter);
		}
		return $table_model;
	}

	/**
	 * Mette tutte le tabelle di wordpress in stato pubblicato
	 */
	function publish_wp_tables($status, $table) {
		global $wpdb; 
		if (in_array($table, [$wpdb->posts, $wpdb->users, $wpdb->prefix.'usermeta', $wpdb->prefix.'terms' , $wpdb->prefix.'termmeta', $wpdb->prefix.'term_taxonomy', $wpdb->prefix.'term_relationships', $wpdb->postmeta, $wpdb->prefix.'options', $wpdb->prefix.'links', $wpdb->prefix.'comments', $wpdb->prefix.'commentmeta'])) {
			$status = 'PUBLISH';
		}
		return $status;
	}

	/**
	 * Quando cliccano il popup per votare il plugin registro la scelta (già votato o non mi piace)
	 */
	function record_preference_vote() {
		$vote = sanitize_text_field($_REQUEST['msg']);
		$info = get_option('_af_activete_info');
		$info['voted'] = $vote;
		update_option('_af_activete_info', $info, false);
		wp_send_json(['done']);
		die();
	}

	/**
	 * Calcola quali record sta per eliminare a seconda della query e delle primary ID
	 */
	public function af_delete_confirm() {
		ADFO_fn::require_init();
		$json_send = [];
		//$json_send = ['error' => '', 'items' => '', 'checkboxes'];
		if (!isset($_REQUEST['ids']) || !is_countable($_REQUEST['ids'])) {
			$json_send['error'] = __('I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement.', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		if (isset($_REQUEST['dbp_id']) && $_REQUEST['dbp_id']  > 0) {
			$ids =  ADFO_fn::sanitize_intjson_recursive($_REQUEST['ids']);
			$json_send = ADFO_fn::prepare_delete_rows($ids,'', absint($_REQUEST['dbp_id']));
        } else if ($_REQUEST['sql'] != "") {
			$ids = ADFO_fn::sanitize_absint_recursive($_REQUEST['ids']);
			//TODO security nessun sql deve passare su request!
			$json_send = ADFO_fn::prepare_delete_rows($ids, wp_kses_post( wp_unslash($_REQUEST['sql'])) );
        } else {
			$json_send['error'] = __('Something wrong', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		unset($json_send['sql']);
		wp_send_json($json_send);
		die();	
	}

	/**
	 * setto i record nel post_status a trash
	 */
	public function af_trash() {
		ADFO_fn::require_init();
		$json_send = [];
		//$json_send = ['error' => '', 'items' => '', 'checkboxes'];
		if (!isset($_REQUEST['ids']) || !is_countable($_REQUEST['ids'])) {
			$json_send['error'] = __('I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement.', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		if (isset($_REQUEST['dbp_id']) && $_REQUEST['dbp_id']  > 0) {
			$ids =  ADFO_fn::sanitize_intjson_recursive($_REQUEST['ids']);
			$json_send = ADFO_fn::set_post_status(absint($_REQUEST['dbp_id']),$ids,'trash');
        } else {
			$json_send['error'] = __('Something wrong', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		$json_send['el_id'] = sanitize_text_field($_REQUEST['el_id']);
		unset($json_send['sql']);
		wp_send_json($json_send);
		die();	
	}

	/**
	 * setto i record nel post_status a trash
	 */
	public function af_untrash() {
		ADFO_fn::require_init();
		$json_send = [];
		//$json_send = ['error' => '', 'items' => '', 'checkboxes'];
		if (!isset($_REQUEST['ids']) || !is_countable($_REQUEST['ids'])) {
			$json_send['error'] = __('I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement.', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		if (isset($_REQUEST['dbp_id']) && $_REQUEST['dbp_id']  > 0) {
			$ids =  ADFO_fn::sanitize_intjson_recursive($_REQUEST['ids']);
			$json_send = ADFO_fn::set_post_status(absint($_REQUEST['dbp_id']), $ids, 'draft');
        } else {
			$json_send['error'] = __('Something wrong', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		$json_send['el_id'] = sanitize_text_field($_REQUEST['el_id']);
		unset($json_send['sql']);
		wp_send_json($json_send);
		die();	
	}

	/**
	 * @since 1.8.0
	 * Aggiorna i campi di un input lista
	 */
	public function update_table_list_input_value() {
		ADFO_fn::require_init();
		$json_send = ['divid' => $_REQUEST['divid'], 'error'=>''];
		if (!isset($_REQUEST['ids']) || !is_countable($_REQUEST['ids'])) {
			$json_send['error'] = __('I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement.', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		if (isset($_REQUEST['dbp_id']) && $_REQUEST['dbp_id']  > 0) {
			$item_ids =  ADFO_fn::sanitize_absint_recursive($_REQUEST['ids']);
			//$detail = ADFO::get_detail($_REQUEST['dbp_id'], $_REQUEST['ids'] );
			$list_id = absint($_REQUEST['dbp_id']);
		
			$mysql_alias_name = sanitize_text_field($_REQUEST['mysql_alias_name']);
			$item[$mysql_alias_name] = sanitize_text_field(wp_unslash($_REQUEST['value']));
			$item = ADFO::update_list_to_form($list_id , $item_ids, (object)$item);
			$ris = ADFO::save_data($list_id, (object)$item);
		} else {
			$json_send['error'] = __('Something wrong', 'admin_form');
		}
		wp_send_json($json_send);
		die();	
	}

	/**
	 * @since 1.8.0
	 * Aggiorna l'ordinamento dei campi di una lista
	 */
	public function update_table_list_order_value() {
		ADFO_fn::require_init();
	
		$json_send = [ 'error'=>''];
		if (!isset($_REQUEST['update_data']) || !is_countable($_REQUEST['update_data'])) {
			$json_send['error'] = __('I have not found any results. Verify that the primary key of each selected table is always displayed in the MySQL SELECT statement.', 'admin_form');
			wp_send_json($json_send);
			die();
		}
		
		$list_id = absint($_REQUEST['dbp_id']);
		$post = ADFO_functions_list::get_post_dbp($_REQUEST['dbp_id']);
		if ($post == false) {
			$json_send['error'] = __('The list does not exist','admin_form');
			wp_send_json($json_send);
			die();
		}
		$mysql_alias_name = ADFO_functions_list::get_order_field($post, 'field');
		if (!is_array($mysql_alias_name)) {
			$json_send['error'] = __("ERROR: There is no field in the order type form! First go to the form tab and transform this field into ORDER type");
			wp_send_json($json_send);
			die();
		}
		$mysql_alias_name = $mysql_alias_name['label'];
		if (isset($_REQUEST['update_data']) && is_array($_REQUEST['update_data'])) {
			foreach ($_REQUEST['update_data'] as $key => $value) {
			
				$item = [$mysql_alias_name => sanitize_text_field($value['value'])];
				$item = ADFO::update_list_to_form($list_id , $value['ids'], (object)$item);
				ADFO::save_data($list_id, (object)$item);

			}

		} else {
			$json_send['error'] = __('Something wrong', 'admin_form');
		}
		wp_send_json($json_send);
		die();	
	}

	public function init_post_type() {
		// carico i post type dagli options (update_option('adfo_post_types', $adfo_post_types, true);)
		$adfo_post_types = get_option('adfo_post_types');
		if (!is_array($adfo_post_types)) {
			$adfo_post_types = [];
		}
	
		foreach ($adfo_post_types as $post_type_key => $post_type) {
			if ($post_type_key == 'post' || $post_type_key == 'page' || $post_type_key == 'attachment' || $post_type_key == 'revision' || $post_type_key == 'nav_menu_item' || $post_type_key == 'custom_css' || $post_type_key == 'customize_changeset' || $post_type_key == 'oembed_cache' || $post_type_key == 'user_request' || $post_type_key == 'wp_block' || $post_type_key == 'adfo_list' || $post_type_key == '' || $post_type_key == ' ' ) continue;
			if (!post_type_exists($post_type_key)) {
				$args = array(
					'public' => true,
					'query_var' => true,
					'rewrite' => array('slug' => $post_type['slug']),
					'show_in_menu' => false
					);
			
				register_post_type($post_type_key, $args);
				//die("--------- :".$post_type_key);
				// chiamo l'hook per il contenuto del post type
				do_action('adfo_post_type_'.$post_type_key);

			}
		}
		if (count($adfo_post_types ) > 0) {
			// aggiungo il filtro per il contenuto del post type
			add_filter('the_content', [$this, 'custom_post_content']);
		}
	}
	
	public function custom_post_content($content) {
		global $post;
		ADFO_fn::require_init();
		$adfo_post_types = get_option('adfo_post_types');
		if (!is_array($adfo_post_types)) {
			$adfo_post_types = [];
			$slug_post_type = [];
		} else {
			$slug_post_type = array_map(function($item, $key ) { return (isset($item['slug']) ? $item['slug'] : $key); }, $adfo_post_types, array_keys($adfo_post_types));
			if (in_array($post->post_type, $slug_post_type) && is_single()) {
				// trovo il primo adfo_id che corrisponde al post type
				foreach ($adfo_post_types as $adfo_id => $post_type) {
					if ($post_type['slug'] == $post->post_type) {
						$adfo_id = $post_type['adfo_ids'][0];
						break;
					}
				}
				if ($adfo_id > 0) {
					$content = ADFO::get_single($adfo_id, $post->ID);
				} 
			}
		}
		return $content;
	}

	
	/**
	 * @since v.1.8.0
	 * link per esportare i dati in csv o sql
	 */
	public function adfo_frontend_export_data() {
		global $wpdb;
		ADFO_fn::require_init();
		
		check_ajax_referer( 'adfo_frontend_export_data', 'nonce' );
		
		$adfo_id =  absint(base64_decode($_REQUEST['adfo_id']));
		$post = ADFO_functions_list::get_post_dbp($adfo_id);
		if ($post == false) {
			wp_die(__('The list does not exist','admin_form'));
		}
	    if (empty($post)) return '';
		//$adfo_id =  absint($_REQUEST['adfo_id']); 
		/* @var array $list_of_data */
		$list_of_data = ADFO::get_data($adfo_id , "items", null, 100000);
		$filename = sanitize_title( $post->post_title)."-".date('Y-m-d');
		if (strtolower($_REQUEST['format']) == "csv") {
			$delimiter = ';'; 
			$enclosure = '"';
			
			// Tells to the browser that a file is returned, with its name : $filename.csv
			header("Content-disposition: attachment; filename=$filename.csv");
			// Tells to the browser that the content is a csv file
			header("Content-Type: text/csv");
			// I open PHP memory as a file
			$fp = fopen("php://output", 'w');
			// Insert the UTF-8 BOM in the file
			//fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
			// I add the array keys as CSV headers
			
			$first = reset($list_of_data);
			if (is_object($first)) {
				$first = (array)$first;
			}
			fputcsv($fp,array_keys($first),$delimiter,$enclosure);
			
			// Add all the data in the file
			foreach ($list_of_data as $fields) {
				if (is_object($fields)) {
					$fields = (array)$fields;
				}
				fputcsv($fp, $fields,$delimiter,$enclosure);
			}
			// Close the file
			fclose($fp);
			die();
		} else if (strtolower($_REQUEST['format']) == "sql") {
			$insert_queries = [];
			$post   = ADFO_functions_list::get_post_dbp($adfo_id );
			if ($post == false) {
				wp_die(__('The list does not exist','admin_form'));
			}
			if ($post != false && isset($post->post_content["sql_from"]) && is_array($post->post_content["sql_from"])) {
				if (count($post->post_content["sql_from"]) == 1) {
					$table_name = array_shift($post->post_content["sql_from"]);
					$create = $wpdb->get_row('SHOW CREATE TABLE `' . ADFO_fn::sanitize_key($table_name) . '`', 'ARRAY_A');
					$insert_queries[] = str_replace('CREATE TABLE ', 'CREATE TABLE IF NOT EXISTS ', $create["Create Table"]).";";
				} else {
					$table_name = array_shift($post->post_content["sql_from"]);
				}
			} else {
				$table_name = "{table_name}";
			}
			
			foreach ($list_of_data as $row) {
				$keys = array();
				$values = array();
				$row = (array)($row);
				foreach ($row as $key => $value) {
					$keys[] = "`" . $key . "`";
					$value = str_replace("%", "{quote_escape_admin_form_crazy_wpdb_system}", $value);
					$value = '"' . esc_sql($value) . '"';
					$value = str_replace("{quote_escape_admin_form_crazy_wpdb_system}", "%", $value);
					$values[] =  $value;
				}
				$insert_queries[] = "INSERT INTO `" . ADFO_fn::sanitize_key($table_name) . "` (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ");";
			}
			header("Content-disposition: attachment; filename=$filename.sql");
			header("Content-Type: text/sql");

			echo implode("\n", $insert_queries);
			die();
			
		}

	}

}

$ADFO_admin_loader = new ADFO_admin_loader();