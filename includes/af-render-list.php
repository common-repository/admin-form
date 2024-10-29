<?php
/**
 * dbp_html_obj genera tutti i pezzetti che possono servire per generare la tabella o gli shortcode nel frontend
 */

namespace admin_form;

class  ADFO_render_list {
	/**
	 * @var $table_model 
	 */
	var $table_model = false;
	/**
	 * @var String $table_name Il nome della tabella che si sta visualizzando
	 */
	var $table_name = "";
	/**
	 * @var String $table_class Una o più classi css da aggiungere al tag table 
	 */
	var $table_class = "";
	/**
	 * @var String $no_result l'html da stampare se non ci sono risultati
	 */
	var $no_result = "";
	/**
	 * @var String $prefix_request il prefisso da appore a tutte le richieste per paginazione, ordinamento e ricerca
	 */
	var $prefix_request = "";
	/**
	 * @var String $list_id il prefisso da appore a tutte le richieste per paginazione, ordinamento e ricerca
	 */
	var $list_id = "";
	/**
	 *  * @var Array $frontend_view_setting Le configurazioni lato amministrazione
	 */
	var $frontend_view_setting = [];
	/**
	 *  * @var Array $add_attributes [key=>value, ...] sono gli attributi aggiuntivi
	 */
	var $add_attributes = [];

	/**
	 * @var String $uniqid_div
	 */
	var $uniqid_div = "";
	
	/**
	 * @var String $mode ajax|get|post
	 */
	var $mode = "";

	/**
	 * @var Bool $block_opened se è stato aperto il blocco oppure no
	 */
	var $block_opened = false;

	/**
	 * @var Bool $search_opened se è stato aperto il blocco del search ?!
	 */
	var $search_opened = false;

	/**
	 * @var \admin_form\ADFO_search_form $search
	 */
	var $search = false;

	/**
	 * @var Bool $div_container se è stato disegnato il div che contiene tutto 
	 * (in ajax non deve essere ridisegnato)
	 */
	var $div_container = true;
	/**
	 * @var string $color  il colore del blocco
	 */
	var $color = 'blue';

	/**
	 * @var ADFO_list_setting[] $list_setting
	 */
	var $list_setting; 

	/**
	 * @param \admin_form\ADFO_model $table_model
	 */
	function __construct($post_id, $mode = null, $prefix = "") {
		$this->list_id = $post_id;
		if ($prefix == "") {
			$prefix = "dbp".$post_id;
		}
		$this->prefix_request = $prefix;
		$post        = ADFO_functions_list::get_post_dbp($post_id);
		if ($post == false) return '';
        $this->table_model = ADFO_functions_list::get_model_from_list_params($post->post_content);
		
		// i params settati nella lista
        $extra_params =  ADFO_functions_list::get_extra_params_from_list_params(@$post->post_content['sql_filter']);
	
		if ($this->table_model) {
			// global non è documentata!
			PinaCode::set_var('global.dbp_filter_path', $prefix);
            PinaCode::set_var('global.dbp_id', $post_id);
            ADFO_functions_list::add_lookups_column($this->table_model, $post);
		
			ADFO_functions_list::add_post_type_settings($this->table_model, $post, true);
		
			
			ADFO_functions_list::add_post_user_column($this->table_model, $post);
		
            ADFO_functions_list::add_frontend_request_filter_to_model($this->table_model, $post, $post_id, $prefix);
			$this->table_model->get_list();
			//print ($this->table_model->get_current_query());
			$total_row = $this->table_model->get_count();
			PinaCode::set_var('total_row', absint($total_row));
			$this->uniqid_div = 'dbp_' . ADFO_fn::get_uniqid();
			$this->table_model->update_items_with_setting($post, false);
			$this->table_model->check_for_filter();			
			$this->add_extra_params($extra_params);
			if (isset($post->post_content['frontend_view'])) {
				$this->frontend_view_setting = $post->post_content['frontend_view'];
				
				$this->no_result = (isset($post->post_content['frontend_view']['no_result_custom_text'])) ? $post->post_content["frontend_view"]['no_result_custom_text'] : '';
			}
			$this->list_setting = $post->post_content['list_setting'];
		}

		if (!is_null($mode)) {
			$this->mode = $mode;
		} else {
			$this->mode = $this->get_frontend_view('table_update','get');
		}
		$this->set_color();
	}
	
	/**
	 * uniqid_div serve come riferimento per eventuali chiamate ajax della tabella.
	 * TODO Potrebbe essere usato anche per differenti chiamate non ajax alla stessa lista
	 */
	public function set_uniqid($uniq_id = "") {
		if ($uniq_id != "") {
			$this->uniqid_div = $uniq_id;
		}
		return $this->uniqid_div;
	}

	/**
	 * Setta il colore generico
	 */
	public function set_color($color = '') {
		
		if ($color == "") {
			$this->color = $this->get_frontend_view('table_style_color', 'blue');
		} else {
			$this->color = $color;
		}
		return $this->color;
	}

	/**
	 * Se chiamata non stampa il div che contiene il codice
	 */
	public function hide_div_container() {
		
		$this->div_container =  false;
	}

	/**
	 * Verifica e ritorna un'impostazione di frontendview
	 */
	public function get_frontend_view($key, $default = false) {
		if (array_key_exists($key, $this->frontend_view_setting)) {
			return \wp_unslash($this->frontend_view_setting[$key]);
		} else {
			return $default;
		}
	}
	
	/**
	 * Stampa la tabella di una lista nel frontend
	 * @param Array $items Accetta un array di oggetti o un array di array.
	 * @return void  
	 */
	public function table( $custom_class = "", $table_sort = null) {
		$this->open_block();
		$this->update_table_class($custom_class);
		if (!isset($this->table_model->items)) return;
		$items = $this->table_model->items;
		if (!is_array($items) || count ($items) == 0) return;
		$array_thead = array_shift($items);
		?>
		<div class="dbp-table-overflow">
		<table class="dbp-table <?php echo esc_attr(@$this->table_class.' dbp-table-'.$this->color); ?>">
		<?php ob_start(); ?>
		<thead>
			<tr>
				<?php 
				foreach ($array_thead as $key => $value) {
					?>
					<th class="dbp-table-th dbp-th-dim-<?php echo strtolower($value->type). $value->width; ?>">
						<?php if (!$this->get_sort($table_sort, $value->original_table)) : ?>
							<div class="dbp-title-frontend"><?php echo $value->name; ?></div> 
						<?php else: ?>
						<?php $this->icons($value->name_request,  $value->name); ?>
						<?php endif; ?>
					</th>
					<?php 
				} 
				?>
			</tr>
		</thead>
		<?php 
		$content_table_thead = ob_get_clean();
		echo apply_filters('adfo_frontend_table_thead', $content_table_thead, $this->list_id, $array_thead); 
		?>
		<tbody>
		<?php foreach ($items as $item) : ?>
			<tr>
				<?php 
				foreach ($array_thead as $key=>$setting) { 
					$original_value = "____af__".$key;
					if (isset($item->$original_value)) {
						$formatting_class = ADFO_fn::column_formatting_convert($setting->format_styles, $item->$original_value, '');
					} else {
						$formatting_class = "";
					}
					?><td class="dbp-table-td<?php echo $setting->width.' '.$formatting_class; ?> dbp-td-<?php echo esc_attr(strtolower($setting->align)); ?>"><div class="btn-div-td "><?php 
					echo wp_kses(wp_unslash($item->$key), ADFO_fn::get_allowed_tag() );
					?></div></td> <?php
				} 
				?>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		</div>
		<?php
	}

	

	/**
	 * Chiude il container
	 */
	public function end() {
		if ($this->block_opened) {
			$this->block_opened = false;
			if ($this->mode != 'link' && $this->search_opened) {
				$this->search->close_form(false);
			}
			echo "</div>";
			if ($this->div_container) {
				echo "</div>";
			}
		}
	}

	/**
	 * Disegna la ricerca classica su tutti i campi
	 */
	public function search($btn = true) {
		$this->open_block();
		$field_use = "search";
		
		$field_use = apply_filters( 'adfo_frontend_search', $field_use, $this->prefix_request."_search", $this->list_id);
		if ($field_use == "" || is_bool($this->search)) return;
		
		if ($field_use != "search") {
			$this->single_field_search($field_use);
		} else if ($this->mode != 'link') {
			$this->search->classic_search_post($this->prefix_request, 'Search', $this->color, $btn);
		} else {
			$this->search->classic_search_link($this->prefix_request, false, $this->color);
		}	
		
	}
	/**
	 * Disegna il bottone per esportare i dati
	 */
	public function export_btn() {
		$this->open_block();
		$format = $this->get_frontend_view('table_export');
		$title = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.88 122.88" width="14px" height="14px"><path d="M61.44 0A61.46 61.46 0 1 1 18 18 61.21 61.21 0 0 1 61.44 0Zm10 50.74A3.31 3.31 0 0 1 76 55.47L63.44 67.91a3.31 3.31 0 0 1-4.65 0L46.38 55.65A3.32 3.32 0 0 1 51 50.92l6.83 6.77.06-23.84a3.32 3.32 0 0 1 6.64.06l-.07 23.65 6.9-6.82ZM35 81.19v-13a3.32 3.32 0 0 1 6.64.06v9.45h39.5v-9.51a3.32 3.32 0 1 1 6.64.06v12.91a3.32 3.32 0 0 1-3.29 3.17H38.34A3.32 3.32 0 0 1 35 81.19Zm64.44-57.75a53.74 53.74 0 1 0 15.74 38 53.58 53.58 0 0 0-15.74-38Z"></path></svg><span>Download</span>';
		$fl_right = ($this->get_frontend_view('table_search') == 'simple') ? 'dbp-download-button-fl-right' : '';
		?>
		<div class="dbp-download-button <?php echo $fl_right; ?> dbp-download-button-<?php echo $this->color; ?>" onclick="dbp_export(this)"><?php ADFO::draw_export_btn($this->list_id, $title, $format); ?></div>
		<?php
	}
	/**
	 * Disegna la ricerca classica su tutti i campi
	 */
	public function submit($label = '') {
		if ($label == "") {
			$label = "Search";
		}
		// vedo se deve apparire il bottone clear oppure no
		$req_search = false;
		if (isset($_REQUEST) && is_array($_REQUEST)) {
			foreach ($_REQUEST as $key => $_) {
				if (substr($key, 0, strlen($this->prefix_request)) == $this->prefix_request) {
					$req_search = true;
					break;
				}
			}
		}
		?>
		<div class="dbp-search-row">
			<div class="dbp-search-button dbp-search-button-<?php echo $this->color; ?>" onclick="dbp_submit_simple_search(this)"><?php _e($label, 'admin_form'); ?></div>
			<div class="dbp-search-button dbp-search-button-<?php echo $this->color; ?>" onclick="dbp_submit_clean_simple_search(this)"><?php _e('Clean', 'admin_form'); ?></div>
		</div>
		<?php
	}

	/**  
	 * Genera una form di ricerca di un solo campo.
     * @param String $field_name Il nome della colonna estratta
     * @param String $btn_text Il testo 
     * @param String $label 
     * @return Void
	 */
	public function single_field_search($field_name,  $label = '') {
		$this->open_block();
		$field_name_request = '';
		
		foreach ($this->list_setting as $list_setting) {
            if (strtolower($list_setting->name) == strtolower(trim($field_name))) {
				$field_name_request = $list_setting->name_request;
				if ($label == '') {
					$label = $list_setting->title;
				}
				
			}
		}
		$field_name = apply_filters( 'adfo_frontend_search', $field_name, $this->prefix_request."_".$field_name_request, $this->list_id);

		if ($field_name_request != "" && $field_name != "") {
			$this->search->field_one_input_form($this->prefix_request."_".$field_name_request,  $label ) ;
		}
	}


	/**
	 * Aggiunge parametri da passare nella paginazione, ordinamento o nei filtri in generale
	 * @param array $attributes [key=>value, ...]
	 * @return void
	 */
	public function add_extra_params($attributes) {
		if (is_countable($attributes)) {
			$this->add_attributes = array_merge($this->add_attributes, $attributes);
		}
	}

	/**
	 * Aggiunge una classe alla tabella se la classe è già impostata la sostituisce
	 */
	private function update_table_class($class) {
		if ($class != "") {
			$this->table_class = $class;
		}
	}
	


	/**
	 * Override delle impostazioni del sort
	 */
	private function get_sort($sort, $value_original_table) {
		if ($value_original_table == "") return false;
		if (is_null($sort) || !is_bool($sort)) {
			return !in_array($this->get_frontend_view('table_sort'),[false,'']);
		} else {
			return $sort;
		}
	}

	/**
	 * Tutta la costruzione del codice (a parte l'ajax) deve iniziare con open_block e finire con close block
	 */
	public function open_block($add_class = true) {
		if (!$this->block_opened) {
			$this->block_opened = true;
			// il div container non si stampa quando la chiamata è ajax
			if ($this->div_container) {
				echo '<a name="'.$this->prefix_request.'"></a>';
				echo '<div id="'. $this->uniqid_div.'" class="not-found dbp-block-table '. $this->prefix_request.'-block">';
			}
			if ($add_class) {
				$add_class_size = (!in_array($this->get_frontend_view('table_size'), [false,'']) ) ?
				" dbp-block-table-".$this->get_frontend_view('table_size') : '';
			} else {
				$add_class_size = '';
			}

			echo '<div class="dbp-max-large-table'. $add_class_size.'">';

			$this->search = new ADFO_search_form();
			if ($this->mode != 'link' ) {
				if (!$this->search_opened) {
					$this->search->open_form(get_permalink(), $this->mode);
					$this->search->add_params_list_per_post($this->list_id, ['page','search']);
					$this->search_opened = true;
				}
			}
			if ($this->mode != 'link' ) {
			    if ($this->mode == 'ajax' ) {
				 	?>
					<input type="hidden" name="dbp_list_id" value="<?php echo $this->list_id; ?>">
					<input type="hidden" name="dbp_div_id" value="<?php echo $this->uniqid_div; ?>" class="dbp-div-id">
					<input type="hidden" name="dbp_prefix" value="<?php echo $this->prefix_request; ?>" class="dbp-div-id">
					<?php 
					if (count($this->add_attributes) > 0) { 
						?>
						<textarea style="display:none;" name="dbp_extra_attr" id="dbp_extra_attr"><?php echo esc_textarea(base64_encode(json_encode($this->add_attributes))); ?></textarea>	
						<?php 
					}
			    }
				$sort = isset($_REQUEST[$this->prefix_request."_sort"]) ? sanitize_text_field($_REQUEST[$this->prefix_request."_sort"]) : '';
				?>
				<input type="hidden" class="js-dbp-sorting" name="<?php echo esc_attr($this->prefix_request); ?>_sort" value="<?php echo esc_attr($sort); ?>">
				<input type="hidden" name="<?php echo $this->prefix_request; ?>_page"  value="" class="js-dbp-page">
				<?php 
			}
		}
	}


	/**
	 * Disegna le icone accanto al titolo delle colonne della tabella
	 * 
	 * @param String $alias_column il nome o l'alias della colonna
	 * @return String  l'html dell'ordinamento delle colonne
	 */
	private function icons($alias_column, $title) {
		if ($alias_column == "") return ;
		$asc = '<span class="adfo-order-asc"> </span>';
		$desc = '<span class="adfo-order-desc"> </span>';
		$no_sort = '<span class="adfo-order-no"> </span>';
		//$asc = '&darr;';
		//$desc = '&uarr;';
		//$no_sort = '&udarr;';
		if ($this->mode != 'link') {	
			if (isset($_REQUEST[$this->prefix_request.'_sort']) && substr($_REQUEST[$this->prefix_request.'_sort'],0, strlen($alias_column)) == $alias_column) {
				if ($_REQUEST[$this->prefix_request.'_sort'] == $alias_column.".asc")  {
					?><span class="dbp-title-order-link" onclick="dbp_submit_sorting(this, '<?php echo $alias_column; ?>.desc')"><?php echo $title." ".$desc; ?></span><?php
				} else {
					?><span class="dbp-title-order-link" onclick="dbp_submit_sorting(this, '<?php echo $alias_column; ?>.asc')"><?php echo $title." ".$asc; ?></span><?php
				}
			} else {
				?><span class="dbp-title-order-link" onclick="dbp_submit_sorting(this, '<?php echo $alias_column; ?>.asc')"><?php echo $title." ".$no_sort; ?></span><?php
			}
		} else {
			$link = $this->filter_order_pagination_add_query_args(get_permalink(), $this->prefix_request, 'sort');
			$link_asc = add_query_arg([$this->prefix_request.'_sort'=> $alias_column.".asc"], $link );
			$link_desc = add_query_arg([$this->prefix_request.'_sort' =>$alias_column.".desc"], $link );
			if (isset($_REQUEST[$this->prefix_request.'_sort']) && substr($_REQUEST[$this->prefix_request.'_sort'],0, strlen($alias_column)) == $alias_column) {
				if ($_REQUEST[$this->prefix_request.'_sort'] == $alias_column.".asc")  {
					?><a class="dbp-title-order-link" href="<?php echo $link_desc; ?>"><?php echo $title." ".$desc; ?></a><?php
				} else {
					?><a class="dbp-title-order-link" href="<?php echo $link_asc; ?>"><?php echo $title." ".$asc; ?></a><?php
				}
			} else {
				?><a class="dbp-title-order-link" href="<?php echo $link_asc; ?>"><?php echo $title." ".$no_sort; ?></a><?php
			}
		}
	}

	/**
	 * Disegna la paginazione
	 * 
	 * @param String $style select | numeric
	 * @return Void
	 */
	function pagination($style = null) {
		$this->open_block();
		if (is_null($style) || !in_array($style, ['select','numeric'])) {
			$style = $this->get_frontend_view('table_pagination_style', '');
		} else {
			$style = "";
		}
		if (is_bool($this->search)) return;
		if ($this->mode != "link") {
			$this->search->pagination_form($this->prefix_request, $this->table_model->total_items, $this->table_model->limit, $this->list_id, $style, $this->color, $this->mode);
		} else {
			//@TODO manca il prefix
			$this->search->pagination_link(get_permalink(), $this->table_model->total_items, $this->table_model->limit, $this->list_id, $style, $this->color);
		}
	
	}

	/**
	 * Aggiunge i parametri dei filtraggi del frontend ai link
	 * Lo uso su pagination, order, filter
	 * le query possono essere passate in $path_parametro oppure $path[parametro]
	 * @param String $link 
	 * @param String $path Il prefisso dei parametri
	 * @param String $exclude Se c'è un parametro da non inserire
	 * @return String Il nuovo link
	 */
	private function filter_order_pagination_add_query_args($link, $path, $exclude = "") {
		$length = strlen($path);
		if (isset($_REQUEST) && is_array($_REQUEST)) {
			$request = ADFO_fn::sanitize_text_recursive($_REQUEST);
			foreach ($request as $key=>$value) {
                if (is_string($value)) {
                    if (substr($key,0, $length) == $path && substr($key, $length) != "_".$exclude) {
                        $link = add_query_arg($key, urlencode(wp_unslash($value)), $link);
                    }
                } else {
                    foreach ($value as $val) {
                        if (is_string($val)) {
                            if (substr($key,0, $length) == $path && substr($key, $length) != "_".$exclude) {
                                $link = add_query_arg($key."[]", urlencode(wp_unslash($val)), $link);
                            }
                        }
                    }
                   
                }
			}
		}
		return $link;
	}
}