<?php

/**
 * Il controller amministrativo specifico per le liste
 * @internal
 */
namespace admin_form;
if (!defined('WPINC')) die;
class  ADFO_admin_list_menu
{
	/**
	 * @var Int $max_show_items Numero massimo di elementi da caricare per un select
	 */
	var $max_show_items = 500; 
	/**
	 * @var string $last_error
	 */
	var $last_error = "";

	/**
	 * Viene caricato alla visualizzazione della pagina
     */
    function controller() {
		global $wpdb;
		wp_enqueue_style( 'admin-form-css' , plugin_dir_url( __FILE__ ) . 'css/admin-form.css',[],ADFO_VERSION);
		
		wp_register_script( 'admin-form-all', plugin_dir_url( __FILE__ ) . 'js/admin-form-all.js',[],ADFO_VERSION);
		wp_add_inline_script( 'admin-form-all', 'dbp_cookiepath = "'.esc_url(COOKIEPATH).'";'."\n  var dbp_cookiedomain =\"".esc_url(COOKIE_DOMAIN).'";', 'before' );
		wp_enqueue_script( 'admin-form-all' );

		wp_enqueue_script( 'database-form2-js', plugin_dir_url( __FILE__ ) . 'js/af-form2.js',[],ADFO_VERSION);
		$id = absint(str_replace("dbp_", "" , $_REQUEST['page']));

		wp_register_script( 'database-press-js', plugin_dir_url( __FILE__ ) . 'js/admin-form.js',[],ADFO_VERSION);
		wp_add_inline_script( 'database-press-js', 'dbp_admin_post = "'.esc_url( admin_url("admin-post.php")).'";'."\n  var dbp_global_list_id =".$id , 'before' );
		wp_enqueue_script( 'database-press-js' );
		//TODO da verificare!
		//wp_enqueue_script( 'database-sql-editor-js', plugin_dir_url( __FILE__ ) . 'js/af-sql-editor.js',[],ADFO_VERSION);

		$file = plugin_dir_path( __FILE__  );
		$dbp_css_ver = date("ymdGi", filemtime( plugin_dir_path($file) . 'frontend/admin-form.css' ));
		wp_enqueue_script( 'dbp_frontend_js' );
		$action = ADFO_fn::get_request('action_query', '', 'string');
		ADFO_fn::require_init();
		$html_content = "";
		$msg_error = "";
		if ($id > 0) {

			$post = ADFO_functions_list::get_post_dbp($id);
			ADFO_functions_list::no_post_dbp($post);
			if ($post == false) {
				 _e('Something is wrong, call the site administrator', 'admin_form');
				 return;
			} else {
				$list_title = $post->post_title;
				$description =  PinaCode::execute_shortcode($post->post_excerpt);
				$sql = @$post->post_content['sql'];
				if ($sql == "") {
					$link = admin_url("admin.php?page=admin_form&section=list-sql-edit&dbp_id=".$id);
					$msg_error = '<a href="' . $link . '">'.__('Something is wrong, call the site administrator', 'admin_form')."</a>";
				}
				// questo aggiunge i filtri del setting
				$table_model = ADFO_functions_list::get_model_from_list_params($post->post_content);
				$list_of_columns 				= ADFO_fn::get_all_columns();

				if ($table_model->sql_type() == "multiqueries") {
					//  NON GESTISCO MULTIQUERY NELLE LISTE
					$msg_error = __('No Multiquery permitted in list', 'admin_form');
				} else if ($table_model->sql_type() == "select") {

					ADFO_functions_list::add_lookups_column($table_model, $post);
					ADFO_functions_list::add_post_user_column($table_model, $post);
					ADFO_functions_list::add_post_type_settings($table_model, $post);

					// SEARCH in all columns
					//print "action: ".$action ;
					$search = ADFO_fn::get_request('search', false, 'remove_slashes'); 
					if ($search && $search != "" &&  in_array($action, ['search','order','limit_start','change_limit','filter'])) {
						// TODO se è search deve rimuovere prima tutti i where!!!!
						
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
					
					} else {
						$_REQUEST['search'] = '';
					}

					ADFO_fn::set_open_form(); 
					// cancello le righe selezionate!
					if ($action == "delete_rows" && isset($_REQUEST["remove_ids"]) && is_array($_REQUEST["remove_ids"])) {
						$ids = ADFO_fn::sanitize_absint_recursive($_REQUEST["remove_ids"]);
						$result_delete = ADFO_fn::delete_rows($ids, '', $id);
						if ($result_delete['error'] != "") {
							$msg_error = $result_delete;
						} else {
							$msg = __('The data has been removed.', 'admin_form');
						}
					}

					$extra_params = ADFO_functions_list::get_extra_params_from_list_params(@$post->post_content['sql_filter']);
					$exclude = ['page','action_query','filter','search'];
					foreach ($_REQUEST as $req_key => $req) {
						if (!in_array($req_key, $exclude)) {
							$extra_params['request'][$req_key] = sanitize_text_field($_REQUEST[$req_key]);
						}
					}
					
					if ( ADFO_fn::get_request('filter.limit', 0) == 0) {
						if (isset($post->post_content['sql_limit']) &&  (int)$post->post_content['sql_limit'] > 0) {
							$sql_limit  = (int)$post->post_content['sql_limit'];
						} else {
							$sql_limit  = 100;
						}
						$_REQUEST['filter']['limit'] = absint($sql_limit);
						$table_model->list_add_limit(0, $sql_limit);
					}
					if ( ADFO_fn::get_request('filter.sort.field', '') == '') {
						if (isset($post->post_content['sql_order']['sort']) &&  isset($post->post_content['sql_order']['field'])) {
							$_REQUEST['sort']['field'] = $post->post_content['sql_order']['field'];
							$table_model->list_add_order($post->post_content['sql_order']['field'], $post->post_content['sql_order']['sort']);
						}
					}

				
					$original_post = clone $post;
					// TODO update_items_with_setting deve essere fatto qui e poi correggere tutti i warning e notice. per ora lo eseguo due volte, ma è sbagliato
					$table_model->update_items_with_setting($post);
				
					$post_status = isset($_REQUEST['post_status']) ? $_REQUEST['post_status'] : '';
					ADFO_functions_list::add_post_type_status($table_model, $post, $post_status);
					$count_sql_query = ADFO_fn::add_request_filter_to_model($table_model, $this->max_show_items, $post);
					$post = $original_post;
				
					$count_posts = [];
					// TODO faccio il conteggio degli articoli per stato... ora dev essere fatto rispetto ad un model, quale non so...
					if (ADFO_functions_list::has_post_status($post)) {
						$count_posts = ADFO_functions_list::count_table_by_status($post);					
					}

					//print "<p>".$table_model->get_current_query()."</p>";

					$table_items = $table_model->get_list();
					$table_model->update_items_with_setting($post);

					if ($table_model->limit - 1 <= count($table_items)) {
						if ($count_sql_query != '') {
							$table_model->total_items = $wpdb->get_var($count_sql_query);
						}
					} else {
						$table_model->total_items = count($table_items) - 1;
					}
					
					

					ADFO_fn::items_add_action($table_model, $id);
				
					$table_model->check_for_filter();
					ADFO_fn::remove_hide_columns($table_model);
					$html_table   = new ADFO_html_table();
					$html_table->add_table_class('dbp-table-admin-menu');
					$html_table->add_extra_params($extra_params);
					
					$html_content = $html_table->template_render($table_model); // lo uso nel template
					ADFO_fn::set_close_form(); 
				
				} else {
					$msg_error = __('Something is wrong, call the site administrator', 'admin_form');
				}
			}
		}  else {
			$msg_error = __('Something is wrong, call the site administrator', 'admin_form');
		}
		require(dirname( __FILE__ ) . "/partials/af-page-admin-menu.php");
	}

}
