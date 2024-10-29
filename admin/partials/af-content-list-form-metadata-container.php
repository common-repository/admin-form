<?php
/**
 * I campi della form
 * 
 * /admin.php?page=admin_form&section=list-form&dbp_id=xxx
 *  (come deve essere gestito un campo? Ad esempio: se lo voglio lavorare come numero e quindi fare il cast se è un testo, oppure come un link, o come un serializzato, o ancora come un'immagine.)
 * @var $items Lo schema della tabella
 */
namespace admin_form;
if (!defined('WPINC')) die;

foreach ($table['fields'] as $item) {
    if ($item->name == "_dbp_alias_table_") continue;
    if ($options['field_key'] == $item->name) {    
        $table_options->table_options = maybe_serialize($options);
    }
} 

?>
<div class="dbp-lf-container-table js-lf-container-table js-dragable-table-box">
    <div class="js-dbp-lf-box-table-info">

        <div class="dbp-lf-table-title dbp-lf-field-title"> 
          
            <span class="dbp-lf-handle js-dragable-table-handle" title="order"><span class="dashicons dashicons-sort"></span></span>
            <span class="dbp-lf-edit-icon">
                <span class="dashicons dashicons-edit dbp-edit-icon js-dashicon-edit2" onclick="dbp_lf_form_toggle_metadata(this)"></span>
            </span>
            <span style="float: right; color:#F00; cursor:pointer" onclick="dbp_lf_form_delete_metadata(this)">DELETE</span>
            <?php if ($options['value_key'] == "") { 
                ?>
                Field Name: <input class="js-name-with-key js-module-field-name js-prevent-exceeded-1000" name="table_module_field_name[<?php echo esc_attr($key); ?>]" required> <?php echo $table['table_name']; 
            } else { 
                echo $options['value_key'] ." (METADATA ".$table['table_name'] ." AS ".$key.")"; ?>
                <?php 
            }
            ?>
        </div>
        <div class="js-dbp-lf-box-attributes">
            <input type="hidden" class="dbp-input js-name-with-key js-prevent-exceeded-1000" style="width:100%"  value="<?php echo esc_attr($table_options->table_options); ?>" name="table_options[<?php echo esc_attr($key); ?>]">

            <input type="hidden" class="js-name-with-key js-dragable-table-order" name="table_module_order[<?php echo esc_attr($key); ?>]" value="<?php echo $count_tables_order++; ?>"> 
            <?php // non devo assolutamente mettere js-prevent-exceeded-1000 su table_action altrimenti rimuove sempre il metadato ?>
            <input type="hidden" class="js-name-with-key js-table-action" name="table_action[<?php echo esc_attr($key); ?>]" value=""> 
        </div>
    </div>
   
    <?php  
    $draw_title = false;
    foreach ($table['fields'] as $item) {
        $custom_field_class = ($options['field_show'] == $item->name) ? ' js-field-to-show' : '';
        if ($item->name == "_dbp_alias_table_") continue;
        $item->edit_view = ($options['field_show'] == $item->name) ? "SHOW" : "HIDE";
        require(__DIR__.'/af-content-list-form-single-table.php'); 
    } 
    ?>
  
</div>
                    