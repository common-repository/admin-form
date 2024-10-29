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
?>
<div class="dbp-lf-container-table js-lf-container-table js-dragable-table-box">
    <div class="js-dbp-lf-box-table-info">
    
        <div class="dbp-lf-table-title"> 
            <span class="dbp-lf-handle js-dragable-table-handle" title="order"><span class="dashicons dashicons-sort"></span></span>
            <?php echo (isset($table['table_name'])) ? $table['table_name']." (".$key.")" : $key; ?>
            
            <label class="adfo-checkbox" style="margin-left: 5rem;">
                <input type="checkbox" class="" onchange="adfo_post_show_all_fileds(this)" value="1">
                <div class="adfo-checbox-box-bg"></div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="presentation" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg>
                <span><?php _e('Mostra tutti i campi', 'admin_form'); ?></span>
            </label>

           

            <span class="dbp-structure-toggle js-structure-toggle">
                <span class="js-lf-dbp-hide" onClick="dbp_lf_toggle_attr(this,0)" style="display: none;">Hide attributes</span>
                <span class="js-lf-dbp-show" onClick="dbp_lf_toggle_attr(this,1)" style="display: inline-block;">Show attributes</span>
                <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','attrs'); ?>
            </span>
            <?php if ($table_options->table_status == "CLOSE") : ?>
                <div class="dbp-alert-warning">
                    <?php _e('The table can no longer be modified because it is in a closed state.', 'admin_form'); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="js-dbp-lf-box-attributes" style="display:none">
            <div class="dbp-structure-grid">
                <input type="hidden" class="js-dragable-table-order" name="table_module_order[<?php echo esc_attr($key); ?>]" value="<?php echo $count_tables_order++; ?>"> 
                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Module Type','admin_form'); ?></span>
                        <?php
                        $add_style = "";
                        if ($table_options->table_status == "CLOSE") {
                            $module_type = "READONLY";
                            $add_style=' disabled="disabled';
                        } else if (!$table_options->isset('module_type')) {
                        
                        $module_type =  ($table_options->isset($precompiled_primary_id)) ? 'HIDE' : 'EDIT';
                        } else {
                            $module_type = $table_options->module_type;
                        }
                        $select_module_type_array = ['EDIT'=>'Editable' ,'READONLY'=>'Read only'];
                        if (count($tables) > 1) {
                            $select_module_type_array['HIDE'] = 'Hide';
                        }
                        
                        echo ADFO_fn::html_select($select_module_type_array, true, 'name="table_module_type['. esc_attr($key) .']" class="js-module-type" onchange="change_select_module_type(this)" '.$add_style, $module_type); ?>
                    </label>
                </div>

                <div class="dbp-form-row-column js-row-allow-create-record">
                    <input type="hidden" value="<?php echo esc_attr($table_options->table_options); ?>" name="table_options[<?php echo esc_attr($key); ?>]">
                </div>

                <div class="dbp-form-row-column js-row-allow-create-record">
                    <?php if ($key > 0) : ?> 
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('It allows you NOT to create the record','admin_form'); ?></span>
                        <?php echo ADFO_fn::html_select(['SHOW'=>'Yes', 'HIDE'=>'No'], true, 'name="table_allow_create['. esc_attr($key) .']"', $table_options->allow_create); ?>
                    </label>
                    <?php else: ?>
                        <input type="hidden" value="HIDE" name="table_allow_create[<?php echo esc_attr($key); ?>]">
                    <?php endif; ?>
                </div>
            </div>

            <div class="dbp-structure-grid">
                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Show Title','admin_form'); ?></span>
                            <?php echo ADFO_fn::html_select(['SHOW'=>'Show', 'HIDE'=>'Hide'], true, 'name="table_show_title['. esc_attr($key) .']" onchange="dbp_select_change_toggle_form_title(this)"',$table_options->show_title); ?>
                    </label>
                </div>
                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Frame Style','admin_form'); ?></span>
                        <?php echo ADFO_fn::html_select(['WHITE'=>'White', 'BLUE'=>'Blue', 'RED'=>'red', 'GREEN'=>'green', 'YELLOW'=>'yellow', 'PURPLE'=>'purple',  'BROWN'=>'brown', 'HIDDEN'=>'hidden'], true, 'name="table_frame_style['. esc_attr($key) .']"', $table_options->frame_style); ?>
                    </label>
                </div>
            </div>

            <div class="dbp-form-row dbp-label-grid js-form-row-title">
                <label><span class="dbp-form-label"><?php _e('Custom title','admin_form'); ?></span></label>
                <input class="dbp-input" style="width:100%"  name="table_title[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($table_options->title); ?>">
            </div>

            <div class="dbp-form-row dbp-label-grid">
                <label><span class="dbp-form-label"><?php _e('Description','admin_form'); ?></span></label>
                <textarea class="dbp-input" style="width:100%" rows="2" name="table_description[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($table_options->description); ?></textarea>
            </div>
        </div>
    </div>
    <div class="js-dragable-table">      
        <?php 
        $draw_title = true;
        $custom_field_class = '';
        foreach ($table['fields'] as $item) {
            if ($item->name == "_dbp_alias_table_") continue;
            if ($item->name == "post_type" && $post->post_content['post_type']['name'] != '') {
                ?><div style="display:none"><?php
            }
            if (in_array($item->name, ['post_date_gmt',  'post_modified_gmt', 'post_parent', 'post_name', 'post_password', 'post_mime_type', 'comment_count', 'menu_order', 'ping_status', 'comment_status', 'to_ping', 'pinged', 'post_content_filtered','guid']) && $item->edit_view == 'HIDE') {
                $custom_field_class = ' js-adfo-row-name-advanced adfo-row-name-advanced';
            } else {
                $custom_field_class = '';
            }
            require(__DIR__.'/af-content-list-form-single-table.php');
            if ($item->name == "post_type" && $post->post_content['post_type']['name'] != '') {
                ?></div><?php
            } 
        }
        ?>
    </div>
</div>
                    