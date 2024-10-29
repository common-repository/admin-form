<?php
/**
 * Queste sono funzioni specifiche per database table
 * 
 * TODO Questa parte deve essere ripensata anche in php
 * [^mialista id=5 params... tmpl=[:
 *  [item.html.start]
 *  [item.html.search]
 * 	[item.html.table]
 * 	[item.html.pagination]
 *  [item.html.order]
 *  item.html.end]
 * :]]
 * 
 */
namespace admin_form;
if (!defined('WPINC')) die;

/**
 * [^GET_LIST
 * ID per identificare la lista
 * table_id per identificare univocamente la tabella e i parametri da passare!
 */
if (!function_exists('pinacode_fn_dbp_list')) {
	function pinacode_fn_dbp_list($short_code_name, $attributes) {
		$ori_attributes = $attributes;
		$prefix = "";
		if (isset($attributes['prefix'])) {
			$prefix = $attributes['prefix'];
			unset($attributes['prefix']);
		}
		
		/**
		 * @var $id l'id della lista
		 */
		if (isset($attributes['id'])) {
			$id =  absint($attributes['id']);
			unset($attributes['id']);
		} else {
			$id =  absint(PinaCode::get_var('global.dbp_id'));
		}
		$params = [];
		if (count($attributes) > 0) {	
			foreach ($attributes as $key=>$value) {
				$params[$key] = PinaCode::get_registry()->short_code($value);
			}
			if (isset($attributes['table_id']) && $attributes['table_id'] != "") {
			//	PinaCode::set_var('params', [$attributes['table_id']=>$new_values]);
			}
		}
		$a = ADFO::get_list($id, false, $params, $prefix);
		return $a;	  
	}
}
pinacode_set_functions('get_list', 'pinacode_fn_dbp_list');


/**
 * [^GET_LIST_DATA id=
 * id per identificare la lista
 * 
 */
if (!function_exists('pinacode_fn_get_list_data')) {
	function pinacode_fn_get_list_data($short_code_name, $attributes) {
		$ori_attributes = $attributes;
		$prefix = "";
		if (isset($attributes['prefix'])) {
			$prefix = $attributes['prefix'];
			unset($attributes['prefix']);
		}
		
		/**
		 * @var $id l'id della lista
		 */
		if (isset($attributes['id'])) {
			$id = absint($attributes['id']);
			unset($attributes['id']);
		} else {
			$id =  absint(PinaCode::get_var('global.dbp_id'));
		}
		if ($id == 0) return [];
		$params = [];
		if (count($attributes) > 0) {	
			foreach ($attributes as $key=>$value) {
				$params[$key] = PinaCode::get_registry()->short_code($value);
			}
		}
		$ori_params =  PinaCode::get_var('params');
        $ori_globals = PinaCode::get_var('global');
        PinaCode::set_var('params', $params);
        $list =  new ADFO_render_list($id, null);
		PinaCode::set_var('global',  $ori_globals);
        PinaCode::set_var('params', $ori_params);
		$items = [];
		if (isset($list->table_model->items) && is_array($list->table_model->items)) {
			$items = $list->table_model->items;
			if(count($items) > 1) {
				array_shift($items);
			}
		}
		return $items;	  
	}
}
pinacode_set_functions('get_list_data', 'pinacode_fn_get_list_data');

/**
 * [^LINK_DETAIL item={}, dbp_id=xx, action="" ]
 */

if (!function_exists('pinacode_fn_link_detail')) {
	function pinacode_fn_link_detail($short_code_name, $attributes) {
		$primary_values = [];

		if (@array_key_exists('dbp_id', $attributes)) {
			$dbp_id = PinaCode::get_registry()->short_code($attributes['dbp_id']);
		} else {
			$dbp_id = PinaCode::get_var('global.dbp_id');
		}
		if (absint($dbp_id) == 0) return '';
		

		if (@array_key_exists('item', $attributes)) {
			if (is_array($attributes['item']) || is_object($attributes['item'])) {
				$item = $attributes['item'];
			} else {
				$item = PinaCode::get_registry()->short_code($attributes['item']);
			}
		} else {
			$item = PinaCode::get_var('data');
		}
		if (!is_array($item) && !is_object($item)) {
			return '';
		}
		if (is_array($item)) {
			$item = (object) $item;
		}

		$primary_values['dbp_ids'] = ADFO::get_ids_value_from_list($dbp_id, $item);
		$primary_values['dbp_id'] = $dbp_id;

		if (@array_key_exists('action', $attributes)) {
			$primary_values['action'] = $attributes['action'];
		} else {
			$primary_values['action'] = 'dbp_get_detail';
		}

		$link = esc_url(add_query_arg($primary_values, admin_url('admin-ajax.php')));
		
		return $link;
	}
}
pinacode_set_functions('link_detail', 'pinacode_fn_link_detail');


/**
 * [^UNIQ_CHARS_IDS item={}, dbp_id=xx ]
 */

if (!function_exists('pinacode_fn_uniq_chars_id')) {
	function pinacode_uniq_chars_id($short_code_name, $attributes) {
		
		if (@array_key_exists('dbp_id', $attributes)) {
			$dbp_id = PinaCode::get_registry()->short_code($attributes['dbp_id']);
		} else {
			$dbp_id = PinaCode::get_var('global.dbp_id');
		}
		if (absint($dbp_id) == 0) return '';
		$post = ADFO_functions_list::get_post_dbp($dbp_id);
		if ($post == false) return '';
		
		if (@array_key_exists('item', $attributes)) {
			if (is_array($attributes['item']) || is_object($attributes['item'])) {
				$item = $attributes['item'];
			} else {
				$item = PinaCode::get_registry()->short_code($attributes['item']);
			}
		} else {
			$item = PinaCode::get_var('data');
		}
		if (!is_array($item) && !is_object($item)) {
			return '';
		}
		if (is_array($item)) {
			$item = (object) $item;
		}
		return ADFO::get_ids_value_from_list($dbp_id, $item);
	}
}
pinacode_set_functions('uniq_chars_id', 'pinacode_uniq_chars_id');


/**
 * [^ADMIN_URL id=dbp_id]
 */
if (!function_exists('pinacode_fn_admin_url')) {
	function pinacode_fn_admin_url($short_code_name, $attributes) { 
		$link = '';
		if (@array_key_exists('id', $attributes)) {
			$id = PinaCode::get_registry()->short_code($attributes['id']);
			unset($attributes['id']);
			$link = admin_url("admin.php?page=dbp_".$id);
		
			if (count ($attributes) > 0) {
				foreach ($attributes as $key=>$attr) {
					$attributes[$key] = PinaCode::get_registry()->short_code($attr);
				}
				$link = add_query_arg($attributes, $link);
			}
		}
		return $link;
	}
}
pinacode_set_functions('admin_url', 'pinacode_fn_admin_url');


/**
 * [^BTN_EXPORT id=dbp_id format=csv|sql title= class=]
 * @since 1.8.0
 */
if (!function_exists('pinacode_fn_btn_export')) {
	function pinacode_fn_btn_export($short_code_name, $attributes) { 
		$link = '';
		if (@array_key_exists('id', $attributes)) {
			$id = PinaCode::get_registry()->short_code($attributes['id']);
			unset($attributes['id']);
		} else {
			$id = PinaCode::get_var('global.dbp_id');
		}
		if (count ($attributes) > 0) {
			foreach ($attributes as $key=>$attr) {
				$attributes[$key] = PinaCode::get_registry()->short_code($attr);
			}
		}
		$format = (isset($attributes['format'])) ? $attributes['format'] : 'csv';
		if (!in_array($format, array('csv', 'sql'))) {
			$format = 'csv';
		}
		$title = (isset($attributes['title'])) ? $attributes['title'] : __('Download '.strtoupper($format), 'admin_form');
		$btn_class = (isset($attributes['class'])) ? $attributes['class'] : '';
	
		ob_start();
		ADFO::draw_export_btn($id, $title, $format, $btn_class);
		$btn = ob_get_clean();
		
		return $btn;
	}
}
pinacode_set_functions('btn_export', 'pinacode_fn_btn_export');



/**
 * [^GET_DETAIL dpb_id=xx id=]
 * ID per identificare la lista
 * table_id per identificare univocamente la tabella e i parametri da passare!
 * @since 1.8.0
 */
if (!function_exists('pinacode_fn_dbp_get_single_data')) {
	function pinacode_fn_dbp_get_single_data($short_code_name, $attributes) {
		if (strpos($short_code_name, ".") !== false) {
			$shortcode_command = explode(".", $short_code_name);
			$shortcode_command = array_shift($shortcode_command);
		} else {
			$shortcode_command = $short_code_name;
		}
		
		if (isset($attributes['id'])) {
			$id =  $attributes['id'];
			unset($attributes['id']);
		} else {
			PcErrors::set('In <b>'.$short_code_name.'</b> ths <b>id</b> attrs is required', '', -1, 'error');
			return [];
		}
		
		/**
		 * @var $id l'id della lista
		 */
		if (isset($attributes['dpb_id'])) {
			$dpb_id =  absint($attributes['dpb_id']);
			unset($attributes['dpb_id']);
		} else {
			$dpb_id =  absint(PinaCode::get_var('global.dbp_id'));
		}
		if ($dpb_id == 0) {
			PcErrors::set('In <b>'.$short_code_name.'</b> ths <b>dpb_id</b> attrs is required', '', -1, 'error');
			return [];
		}
		$get_var = ADFO::get_detail($dpb_id, $id);
	
		if (substr($short_code_name, 0, strlen($shortcode_command)+1) == $shortcode_command.".") {	
			PinaCode::set_var($shortcode_command, (array)$get_var);
			$get_var = PinaCode::get_var($short_code_name);
			//PinaCode::set_var("post", $get_var);
		}
		return $get_var;	  
	}
}
pinacode_set_functions('get_detail', 'pinacode_fn_dbp_get_single_data');



/**
 * [^GET_SINGLE dpb_id=xx id=]
 * ID per identificare la lista
 * table_id per identificare univocamente la tabella e i parametri da passare!
 * @since 1.8.0
 */
if (!function_exists('pinacode_fn_dbp_get_single')) {
	function pinacode_fn_dbp_get_single($short_code_name, $attributes) {
		if (strpos($short_code_name, ".") !== false) {
			$shortcode_command = explode(".", $short_code_name);
			$shortcode_command = array_shift($shortcode_command);
		} else {
			$shortcode_command = $short_code_name;
		}
		
		if (isset($attributes['id'])) {
			$id =  $attributes['id'];
			unset($attributes['id']);
		} else {
			PcErrors::set('In <b>'.$short_code_name.'</b> ths <b>id</b> attrs is required', '', -1, 'error');
			return [];
		}
		
		/**
		 * @var $id l'id della lista
		 */
		if (isset($attributes['dpb_id'])) {
			$dpb_id =  absint($attributes['dpb_id']);
			unset($attributes['dpb_id']);
		} else {
			$dpb_id =  absint(PinaCode::get_var('global.dbp_id'));
		}
		if ($dpb_id == 0) {
			PcErrors::set('In <b>'.$short_code_name.'</b> ths <b>dpb_id</b> attrs is required', '', -1, 'error');
			return [];
		}
		return ADFO::get_single($dpb_id, $id); 
	}
}
pinacode_set_functions('get_single', 'pinacode_fn_dbp_get_single');


/**
 * [^GET_SINGLE dpb_id=xx id=]
 * ID per identificare la lista
 * table_id per identificare univocamente la tabella e i parametri da passare!
 * @since 1.8.0
 */
if (!function_exists('pinacode_fn_dbp_get_total')) {
	function pinacode_fn_dbp_get_total($short_code_name, $attributes) {
		/**
		 * @var $id l'id della lista
		 */
		if (isset($attributes['id'])) {
			$dpb_id =  absint($attributes['id']);
		} else {
			$dpb_id =  absint(PinaCode::get_var('global.dbp_id'));
		}
		if ($dpb_id == 0) {
			PcErrors::set('In <b>'.$short_code_name.'</b> the <b>id</b> attrs is required', '', -1, 'error');
			return [];
		}
		return ADFO::get_total($dpb_id); 
	}
}
pinacode_set_functions('get_total', 'pinacode_fn_dbp_get_total');