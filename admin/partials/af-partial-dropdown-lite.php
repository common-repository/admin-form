<?php
/**
 * Caricato da includes/af-html-table.php 
 * Disegno il dropdown con i filtri di ricerca e tutte le opzioni della colonna
 */
namespace admin_form;
if (!defined('WPINC')) die; ?>

<div class="dbp-dropdown-container-scroll">

<?php 
if ($original_field_name != "" && $filter !== false) : 
    // Il campo in cui salvo filtro si sta per fare ?>
    <input type="hidden"  name="filter[search][<?php echo esc_attr($name_column); ?>][op]" id="filter_<?php echo esc_attr($name_column); ?>_op"  class="js-table-filter-select-op" value="=">
    
<?php if ($sort !== false) :  ?>
    <div class="dbp-table-sort <?php echo esc_attr($sort_desc_class); ?>" data-dbp_sort_key="<?php echo esc_attr($original_field_name); ?>" data-dbp_sort_order="DESC"><?php _e('Sort Desending', 'admin_form'); ?></div>
    <div class="dbp-table-sort <?php echo esc_attr($sort_asc_class); ?>" data-dbp_sort_key="<?php echo esc_attr($original_field_name); ?>" data-dbp_sort_order="ASC"><?php _e('Sort Ascending', 'admin_form'); ?></div>
    <div class="dbp-table-sort <?php echo esc_attr($sort_remove_class); ?>" ><?php _e('Remove sort', 'admin_form'); ?></div>
<?php endif; ?>
  
    <?php // la textarea che tiene i valori della ricerca; 
    if (is_array($default_value)) {
        $default_value = '';
    }
    ?>
    <textarea name="filter[search][<?php echo esc_attr($name_column); ?>][value]" id="dbp_dropdown_search_value_<?php echo $name_column; ?>" class="dbp_hide"><?php echo esc_textarea(wp_unslash($default_value)); ?></textarea>
    <input type="hidden" id="filter_search_original_column<?php echo $name_column; ?>" name="filter[search][<?php echo esc_attr($name_column); ?>][column]" value="<?php echo esc_attr($original_field_name); ?>">
    <input type="hidden" id="filter_search_orgtable_<?php echo esc_attr($name_column); ?>" name="filter[search][<?php echo esc_attr($name_column); ?>][table]" value="<?php echo esc_attr($original_table); ?>">
    <input type="hidden" id="filter_search_filter_<?php echo esc_attr($name_column); ?>" value="<?php echo esc_attr(($default_value != "")); ?>">
    <input type="hidden" id="filter_search_type_<?php echo esc_attr($name_column); ?>" value="<?php echo esc_attr($symple_type); ?>">
    <?php // L'input che accetta i valori per le ricerche = > < ecc...; ?>
    <div class="dbp-dropdown-line-flex" id="dbp_input_value_box_<?php echo $name_column; ?>">
        <span class="dbp-filter-label"><?php _e('Value', 'admin_form'); ?></span>
       
        <input class="dbp-table-filter js-table-filter-input-value" data-rif="<?php echo $name_column; ?>"  id="dbp_input_value_<?php echo $name_column; ?>" type="text"  value="<?php echo esc_attr($def_input_value); ?>" >
      
    </div>
   
    
<?php 
endif;
// Rimuove i filtri ?>
 <div class="dbp-dropdown-hr"></div>
 <?php 
 if ($def_input_value != "" || @$def_input_value_2 != "" || $default_value != "") : 
    ?>
     <div class="js-remove-filter dbp-dropdown-line-click" data-rif="<?php echo esc_attr($name_column); ?>"><?php _e('Remove Filter', 'admin_form'); ?></div>
 <?php 
else: 
    ?>
     <div class="dbp-dropdown-line-disable" data-rif="<?php echo esc_attr($name_column); ?>"><?php _e('Remove Filter', 'admin_form'); ?></div>
<?php 
endif;
?>
</div>
