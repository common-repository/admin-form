<?php
/**
 * Carico una singola tabella in list-form
 * Il salvataggio del form è dentro class-af-list-admin.php -> list_form_save();
 */
namespace admin_form;
if (!defined('WPINC')) die;

if (!isset($item->js_script)) {
    $item->js_script = '';
}
if (!isset($item->note)) {
    $item->note = '';
}

$count_fields++;

if (in_array($item->form_type, ["CREATION_DATE",'LAST_UPDATE_DATE','RECORD_OWNER','MODIFYING_USER'])) {
    $item->default_value = '';
}
$label = (@$item->label) ? @$item->label : $item->name;
$item_type_txt = ADFO_fn::h_type2txt($item->type, false);
$form_type_fields = ['Standard fields'=>['VARCHAR'=>'Text (single line)', 'TEXT'=>'Text (multi line)', 'DATE'=>'Date', 'DATETIME'=>'Date time', 'TIME'=>'Time', 'NUMERIC'=>'Number', 'DECIMAL'=>'Decimal (9,2)', 'SELECT'=>'Multiple Choice - Drop-down List (Single Answer)', 'RADIO'=>'Multiple Choice - Radio Buttons (Single Answer)', 'CHECKBOX'=>'Checkbox (Single Answer)','CHECKBOXES'=>'Checkboxes (Multiple Answers)', 'EMAIL'=>'E-Mail','LINK'=>'Link'], 'Special fields'=>['READ_ONLY'=>'Read only','EDITOR_CODE'=>'Editor Code','EDITOR_TINYMCE'=>'Classic text editor', 'CREATION_DATE'=>'Record creation date', 'LAST_UPDATE_DATE'=>'Last update date', 'RECORD_OWNER'=>'Author (who created the record)', 'MODIFYING_USER'=>'Modifying user', 'COLOR_PICKER' => 'Color Picker', 'ORDER' => 'Order'], 'Wordpress field' => ['POST'=>'Post','USER'=>'User', 'MEDIA_GALLERY' => 'Media Gallery']];
if ($item_type_txt == "DATE" || $item_type_txt == "DATETIME") {
    $form_type_fields = ['Standard fields'=>['VARCHAR'=>'Text (single line)', 'DATE'=>'Date', 'DATETIME'=>'Date time'], 'Special fields'=>['READ_ONLY'=>'Read only','CREATION_DATE'=>'Record creation date', 'LAST_UPDATE_DATE'=>'Last update date']];
}
if ($item_type_txt == "STRING") {
    $form_type_fields = ['Standard fields'=>['VARCHAR'=>'Text (single line)', 'DATE'=>'Date', 'DATETIME'=>'Date time', 'TIME'=>'Time', 'NUMERIC'=>'Number', 'DECIMAL'=>'Decimal (9,2)','SELECT'=>'Multiple Choice - Drop-down List (Single Answer)', 'RADIO'=>'Multiple Choice - Radio Buttons (Single Answer)', 'CHECKBOX'=>'Checkbox (Single Answer)','CHECKBOXES'=>'Checkboxes (Multiple Answers)', 'EMAIL'=>'E-Mail','LINK'=>'Link'], 'Special fields'=>['READ_ONLY'=>'Read only', 'CREATION_DATE'=>'Record creation date', 'LAST_UPDATE_DATE'=>'Last update date', 'RECORD_OWNER'=>'* Author (who created the record)', 'MODIFYING_USER'=>'Modifying user', 'POST_STATUS' => '* Status (Publish, draft ...)', 'COLOR_PICKER' => 'Color Picker'], 'Wordpress field' => ['POST'=>'Post','USER'=>'User', 'MEDIA_GALLERY' => 'Media Gallery']];
}
if ($item_type_txt == "NUMBER") {
    $form_type_fields = ['Standard fields'=>['VARCHAR'=>'Text (single line)',  'NUMERIC'=>'Number',  'DECIMAL'=>'Decimal (9,2)', 'TIME'=>'Time', 'SELECT'=>'Multiple Choice - Drop-down List (Single Answer)', 'RADIO'=>'Multiple Choice - Radio Buttons (Single Answer)', 'CHECKBOX'=>'Checkbox (Single Answer)'], 'Special fields'=>['READ_ONLY'=>'Read only', 'RECORD_OWNER'=>'Author (who created the record)', 'MODIFYING_USER'=>'Modifying user', 'RANGE' => 'Range', 'ORDER' => 'Order'], 'Wordpress field' => ['POST'=>'Post','USER'=>'User', 'MEDIA_GALLERY' => 'Media Gallery']];
}
if ($item_type_txt == "TINY") {
    $form_type_fields = ['Standard fields'=>['VARCHAR'=>'Text (single line)',  'NUMERIC'=>'Number', 'SELECT'=>'Multiple Choice - Drop-down List (Single Answer)', 'RADIO'=>'Multiple Choice - Radio Buttons (Single Answer)', 'CHECKBOX'=>'Checkbox (Single Answer)']];
}
if ($item->is_pri) {
    $form_type_fields = ['PRI' => 'Primary value', 'VARCHAR'=>'Text (single line)', 'NUMERIC'=>'Number', 'READ_ONLY'=>'Read only'];
}

/**
 * Permette di aggiungere nuovi campi al select di scelta nella creazione della form
 */
$form_type_fields = apply_filters( 'form_single_table_type_fields', $form_type_fields, $item_type_txt, $item->is_pri );
$bool_precompiled = ($item->where_precompiled == 1 && $item->custom_value != '' );
?>
<div class="js-dragable-fields dbp-lf-field_box js-dbp-lf-form-card<?php echo ($item->edit_view=="HIDE") ? ' dbp-form-hide-field' : ''; ?><?php echo $custom_field_class; ?>">
    
    <div class="dbp-lf-field-title" <?php echo ($draw_title) ? '' : 'style="display:none"'; ?>>
        <span class="dbp-lf-handle js-dragable-handle"><span class="dashicons dashicons-sort"></span></span>
        <input type="hidden" class="js-dragable-order js-name-with-count js-prevent-exceeded-1000" name="fields_order[<?php echo  absint($count_fields); ?>]" value="<?php echo esc_attr(@$item->order); ?>">
        <span class="dbp-lf-edit-icon js-lf-edit-icon">
        <span class="dashicons dashicons-edit dbp-edit-icon js-dashicon-edit" onclick="dbp_lf_form_toggle(this)"></span>
        </span> 
        <span class="js-title-field">
            <?php if ($item->is_pri) : ?>
                <span class="dashicons dashicons-admin-network" style="color:#e2c447; vertical-align: text-top;" title="Primary"></span>
            <?php endif;   ?>
            <?php echo '<b>'.wp_kses_post($item->name) . '</b> <span style="font-size:.9rem">('.$item_type_txt .')</span>'; ?>
            <?php echo (@$item->js_script != '') ? '<span class="dbp-jsicon">JS</span>' : ''; ?>
        </span>
        <?php 
        if ($bool_precompiled) {
            ?><span class="dbp-alert-warning"><?php _e('Automatically filled by the query','admin_form'); ?></span><?php
        }
        ?>
        <?php  $info = ['fields_name' => $item->name, 'fields_table' => $key, 'fields_orgtable' => $table['table_name']]; ?>
        <input type="hidden" class="js-name-with-count js-info-input" name="fields_info[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr(json_encode($info)); ?>">
        <div style="margin-left:1rem; display: inline-block;">
            <?php echo ADFO_fn::html_select(['SHOW'=>'Show', 'HIDE'=>'Hide'], true, 'name="fields_edit_view['. absint($count_fields) .']" class="js-show-hide-select js-name-with-count" onChange="dbp_lf_select_onchange_toggle_field(this)"', $item->edit_view); ?>
            <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','toggle'); ?>
        </div>
        <?php if ($table_options->table_status == "DRAFT") : ?>
            <div style="display:none;  vertical-align: middle; margin-left:1rem; cursor:pointer;" class="js-cancel-delete button" onclick="dbp_form_cancel_remove_field(this)"><?php _e('Restore the deleted field', 'admin_form'); ?></div>
        <?php endif; ?>
    </div>

    <div class="dbp-structure-field-example js-lf-form-field-example ">
        <div class="js-dbp-example dbp-form-example-field dbp-form-edit-row"> </div>
    </div>
    <div class="dbp-structure-content js-lf-form-content" style="display:none">
        <?php if ($bool_precompiled ) : ?>
            <div class="js-structure-content-before">
                <label class="dbp-alert-warning js-name-with-count " style="display:block; margin-left:1rem;"><input type="checkbox" name="where_precompiled[<?php echo absint($count_fields); ?>]" value="1" checked="checked" onchange="dbp_where_precompiled(this)" class="js-prevent-exceeded-1000"><?php printf(__('Automatically calculate the value from the query. This is the formula entered: %s.','admin_form'), @$item->custom_value); ?></label>
            </div>
        <?php endif; ?>
        <div class="js-structure-content-inside" <?php echo ($bool_precompiled) ? 'style="display:none"' : ''; ?>>
            <div class="dbp-structure-grid">
                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Field Type','admin_form'); ?>  <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','type'); ?></span>
                        <?php echo ADFO_fn::html_select($form_type_fields, true, 'name="fields_form_type['. absint($count_fields) . ']" onchange="dbp_lf_select_type_change(this)" class="js-fields-field-type js-name-with-count"', @$item->form_type); ?>
                    </label>
                </div>
                <div class="dbp-form-row-column" style="position: relative;">
                    <?php if ($table_options->table_status == "DRAFT" && $item->field_name != $primary_key) : ?>
                        <input type="hidden" class="js-delete-field js-name-with-count js-prevent-exceeded-1000" name="fields_delete_column[<?php echo absint($count_fields); ?>]" value="">
                        <span class="dbp-warning-link js-prevent-exceeded-1000" style="vertical-align:middle" onclick="dbp_form_remove_field(this)"><?php _e('DELETE FIELD', 'admin_form'); ?></span>
                        <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','delete'); ?>
                    <?php endif; ?>
                </div>
            </div>
        
            <div class="dbp-structure-grid">
                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Field Label','admin_form'); ?></span>
                        <input type="text" name="fields_label[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr( $label ); ?>" class="dbp-input js-fields-label js-name-with-count js-prevent-exceeded-1000">
                    </label>
                </div>

                <div class="dbp-form-row-column">
                    <label><span class="dbp-form-label">&nbsp;</span></label>
                    <label class="js-label-required" <?php echo (in_array(@$item->form_type, ['CREATION_DATE','LAST_UPDATE_DATE','RECORD_OWNER', 'MODIFYING_USER', 'CALCULATED_FIELD', 'READ_ONLY'])) ? 'style="display:none"' : '' ; ?>>
                        <input type="checkbox" name="fields_required[<?php echo absint($count_fields); ?>]" value="1" <?php echo ($item->required) ? 'checked="checked"' : ''; ?> class="dbp-input js-input-required js-name-with-count js-prevent-exceeded-1000">
                        <span class="dbp-form-label"><?php _e('Required?','admin_form'); ?></span>
                    </label>

                    <label class="js-label-autocomplete" <?php echo (!in_array(@$item->form_type, ['VARCHAR'])) ? 'style="display:none"' : '' ; ?>>
                        <input type="checkbox" name="fields_autocomplete[<?php echo absint($count_fields); ?>]" value="1" <?php echo ($item->autocomplete) ? 'checked="checked"' : ''; ?> class="dbp-input js-input-autocomplete js-name-with-count js-prevent-exceeded-1000">
                        <span class="dbp-form-label"><?php _e('Autocomplete','admin_form'); ?></span>
                        
                    </label>
                </div>
            </div>

            <div class="dbp-structure-grid">
                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Default Value','admin_form'); ?>
                    <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','default'); ?>
                    </span>
                        <input type="text" name="fields_default_value[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr($item->default_value); ?>" class="dbp-input js-lf-fields-default-value js-name-with-count js-prevent-exceeded-1000">
                    </label>
                </div>

                <div class="dbp-form-row-column">
                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Custom css class','admin_form'); ?>
                    <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','class'); ?>
                    </span>
                        <input type="text" name="fields_custom_css_class[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr($item->custom_css_class); ?>" class="dbp-input js-name-with-count js-prevent-exceeded-1000">
                    </label>
                </div>
            </div>

            <div class="js-lf-content-range-values" style="display:<?php echo ($item->form_type == "RANGE") ? 'grid' : 'none'; ?>">
                <h3>Range options   <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','range'); ?></h3>
                <div class="dbp-structure-grid">
                    <div class="dbp-form-row-column">
                        <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Min. Value','admin_form'); ?>
                        </span>
                            <input type="text" name="nfields_range_mi[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr($item->range_min); ?>" class="dbp-input js-fields-range-min js-name-with-count js-prevent-exceeded-1000">
                        </label>
                    </div>

                    <div class="dbp-form-row-column">
                        <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Max. value','admin_form'); ?>
                        </span>
                            <input type="text" name="fields_range_max[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr($item->range_max); ?>" class="dbp-input js-fields-range-max js-name-with-count js-prevent-exceeded-1000">
                        </label>
                    </div>

                    <div class="dbp-form-row-column">
                        <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Step','admin_form'); ?>
                        </span>
                            <input type="text" name="fields_range_step[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr($item->range_step); ?>" class="dbp-input js-fields-range-step js-name-with-count js-prevent-exceeded-1000">
                        </label>
                    </div>
                </div>
            </div>


            <div class="dbp-form-row dbp-label-grid">
                <label><span class="dbp-form-label"><?php _e('Field Note (optional)','admin_form'); ?></span></label>
                <textarea class="dbp-input js-lf-fields-note js-name-with-count js-prevent-exceeded-1000" style="width:100%" rows="1" name="fields_note[<?php echo absint($count_fields); ?>]"><?php echo esc_textarea($item->note); ?></textarea>
            </div>

            <div class="dbp-structure-grid js-lf-options-content" style="display:<?php echo (in_array($item->form_type, ['SELECT','CHECKBOXES','RADIO'])) ? 'grid' : 'none'; ?>">
                <div class="dbp-form-row-column">
                    <label><span class="dbp-form-label"><?php _e('Choices (one choice per line)','admin_form'); ?></span></label>
                    <textarea class="dbp-input js-fields-options js-name-with-count js-prevent-exceeded-1000" style="width:100%" rows="6" name="fields_options[<?php echo absint($count_fields); ?>]"><?php echo esc_textarea(ADFO_fn::stringify_csv_options($item->options)); ?></textarea>
                </div>
                <div class="dbp-form-row-column"> 
                <br>
                <p>You can manually define the encoded value for each choice by inserting the encoded number and a comma before the choice's label
                <pre>
    0, Not reported
    1, Male
    2, Female
    </pre> 
                </p>
                </div>
            </div>
            
            <div class="dbp-structure-grid js-lf-checkbox-value" style="display:<?php echo (in_array(@$item->form_type, ['CHECKBOX'])) ? 'grid' : 'none'; ?>">
                <div class="dbp-form-row-column">
                    <label><span class="dbp-form-label"><?php _e('Checkbox value','admin_form'); ?></span></label>
                    <input class="dbp-input js-name-with-count js-prevent-exceeded-1000" style="width:100%" rows="6" name="fields_custom_value_checkbox[<?php echo absint($count_fields); ?>]" value="<?php echo esc_attr(@$item->custom_value); ?>">
                </div>
            </div>

            <?php
            /**
             * Aggiunge eventuali nuovi campi di configurazione per le form
             */
                do_action( 'dbp_list_form_add_field_config', $count_fields, $item, $total_row, $select_array_test); 
                ?>
            <div class="dbp-structure-grid js-dbp-post-data"<?php echo (@$item->form_type != 'POST') ? ' style="display:none"' : ''; ?>>
                <div class="dbp-form-row-column">

                    <label class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Choose post types','admin_form'); ?></span>
                    <?php echo ADFO_fn::html_select($post_types, true, 'name="fields_post_types['. absint($count_fields) . ']" class="js-name-with-count" ', @$item->post_types); ?>
                    </label>
                </div>
                <div class="dbp-form-row-column">
                    Categories:<br>
                    <div class="dbp-form-box-cat">
                        <?php echo ADFO_functions_list::form_categ_tree(0, 0,absint($count_fields), @$item->post_cats); ?>
                    </div>
                </div>
            </div>

            <div class="dbp-structure-grid js-dbp-user-data"<?php echo (@$item->form_type != 'USER') ? ' style="display:none"' : ''; ?>>
                <div class="dbp-form-row-column">
                    <div class="dbp-label-grid dbp-css-mb-0"><span class="dbp-form-label"><?php _e('Roles','admin_form'); ?></span>
                    
                        <div class="dbp-form-box-cat">
                            <?php echo ADFO_functions_list::form_user_roles(absint($count_fields), @$item->user_roles); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php /** Abilita/disabilita Campi multipli */ ?>
            <div class="dbp-form-row dbp-label-grid js-dbp-is-multiple-data"<?php echo (!in_array($item->form_type, ['USER', 'LOOKUP', 'POST', 'MEDIA_GALLERY']) ) ? ' style="display:none"' : ''; ?>>
                <?php if (in_array($item_type_txt, ['TEXT']) || $item->is_pri) : ?>
                    <?php if (!isset($item->is_multiple)) $item->is_multiple = 0; ?>
                    <label class="js-label-required">
                        <input type="checkbox" name="fields_is_multiple[<?php echo absint($count_fields); ?>]" value="1" <?php echo ($item->is_multiple) ? 'checked="checked"' : ''; ?> class="dbp-input js-prevent-exceeded-1000">
                        <span class="dbp-form-label"><?php _e('Multiple values?','admin_form'); ?></span>
                    </label>
                    <span class="dbp-form-label">Allow you to insert multiple values in the same field. </span>
                <?php else : ?>
                    <span class="dbp-form-label">Multiple values are allowed only for text fields.</span>
                <?php endif; ?>
            </div>

            <div class="dbp-structure-grid js-javascript-script-block"  style="display:<?php echo (!in_array(@$item->form_type, ['CALCULATED_FIELD'])) ? 'grid' : 'none'; ?>">
                <div class="dbp-form-row-column">
                    <label>
                        <span class="dbp-form-label"><?php _e('JS Script','admin_form'); ?>
                            <?php ADFO_fn::echo_html_icon_help('dbp_list-list-form','js'); ?>
                        </span>
                    </label>
                    <textarea class="dbp-input js-field-js-script js-name-with-count js-prevent-exceeded-1000" style="width:100%" rows="3" name="fields_js_script[<?php echo absint($count_fields); ?>]"><?php echo esc_textarea($item->js_script); ?></textarea>
                </div>
                <div class="dbp-form-row-column"> 
                <br>
                <p>Add a javascript script to choose whether to show or hide the field or to validate its content.<a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=js-controller-form") ?>" target="_blank">Read the guide for more information</a></p>
                </div>
            </div>

        </div>
    </div>
</div>