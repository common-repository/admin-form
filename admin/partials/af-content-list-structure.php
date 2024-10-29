<?php
/**
 * La grafica del tab list view formatting
 * /admin.php?page=admin_form&section=list-structure&dbp_id=xxx
 * Tutte le configurazioni di una lista
 * 
 * @var $items Lo schema della tabella
 */
namespace admin_form;
if (!defined('WPINC')) die;
$append = '<span class="dbp-submit" onclick="dbp_submit_list_structure()">' . __('Save', 'admin_form') . '</span>';

$select_type_fields = ['Standard field'=>[ 'TEXT'=>'Text', 'HTML'=>'Html', 'DATE'=>'Date', 'DATETIME'=>'Date Time', 'TIME'=>'Time', 'IMAGE'=>'Image','LINK'=>'External link', 'DETAIL_LINK' => 'Detail Link','PERMALINK'=>'Post link (Permalink)','SERIALIZE'=>'Serialiaze', 'JSON_LABEL'=>'Show Checkbox values (Json label)'],
'Special Fields' => ['CUSTOM'=>'Custom', 'USER' => 'User', 'POST' => 'Post', 'MEDIA_GALLERY' => 'Media Gallery', 'COLOR_PICKER' => 'Color Picker'], 'Edit fields' => [ 'ORDER' => 'Order', 'INPUT' => 'Input', 'COLUMN_CHECKBOX' => 'Checkbox' , 'COLUMN_SELECT' => 'Select' ]];

/**
 * Permette di aggiungere nuovi tipi di campi nella lista
 */
$select_type_fields = apply_filters( 'dbp_list_structure_fields_type', $select_type_fields );
?>
<div class="af-content-header">
    <?php require(dirname(__FILE__).'/af-partial-tabs.php'); ?>
</div>
<div class="af-content-table js-id-dbp-content">
    <div style="float:right; margin:1rem">
            <?php _e('Shortcode: ', 'admin_form'); ?>
            <b>[adfo_list id=<?php echo wp_kses_post($post->ID); ?>]</b>
    </div>
    <?php if (ADFO_fn::echo_html_title_box($list_title, '', $msg, $msg_error, $append)) : ?>
        <form id="list_structure" method="POST" action="<?php echo admin_url("admin.php?page=admin_form&section=list-structure&dbp_id=".$id); ?>">
            <?php wp_nonce_field('dbp_list_form', 'dbp_list_form_nonce'); ?> 
            <input type="hidden" name="action" value="list-structure-save" />
            <input type="hidden" name="table" value="<?php echo (isset($import_table)) ? $import_table : ''; ?>" />
            
            <div class="af-content-margin">
                <div class="js-clore-master" id="clone_master">
                    <div class="dbp-structure-title" >
                        <span class="dbp-form-label js-dragable-handle"><span class="dashicons dashicons-sort" title="sort"></span></span>
                        <input class="js-dragable-order" name="fields_order[]" value=""></label>
                        <span class="dbp-lf-edit-icon">
                            <span class="dashicons dashicons-edit js-structure-toggle" onclick="dbp_structure_toggle(this)"></span>
                        </span>
                        <b onclick="dbp_structure_toggle(this)"><?php _e('CUSTOM COLUMN', 'admin_form'); ?></b>

                        <span class="button"  onClick="dbp_list_structure_delete_row(this);"><?php _e('DELETE', 'admin_form'); ?></span>
                   
                        
                        <?php echo ADFO_fn::html_select(['SHOW'=>'Show', 'HIDE'=>'Hide'], true, 'name="fields_toggle[]" onchange="dbp_change_toggle(this)" class="js-toggle-row"'); ?>
                        
                    </div>
                    <div class="dbp-structure-content js-structure-content" style="display:none" >
                        <div class="dbp-structure-grid">
                            <div class="dbp-form-row-column">
                                <label><span class="dbp-form-label"><?php _e('Table title','admin_form'); 
                                ADFO_fn::echo_html_icon_help('dbp_list-list-structure','title');
                                ?>
                                </span>
                                    <input type="text" name="fields_title[]" value="" class="js-title dbp-input">
                                </label>
                                <input type="hidden" name="fields_origin[]" value="CUSTOM">
                            </div>
                            
                            <div class="dbp-form-row-column">
                                <label><span class="dbp-form-label"><?php _e('Name in url (for request)','admin_form'); ?></span>
                                    <input type="text" disabled value="" class="dbp-input">
                                    <input type="hidden" name="fields_name_request[]" value="" class=" dbp-input">
                                    </label>
                                </label>
                                <input type="hidden" name="fields_mysql_name[]" value="">
                            </div>

                            <div class="dbp-form-row-column">
                                <label><span class="dbp-form-label "><?php _e('Column dimension','admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['extra-small'=>'Extra small', 'small'=>'Small','regular'=>'Regular','large' => 'Large', 'extra-large'=>'Extra large'], true, 'name="fields_width[]" class="js-width-fields"'); ?>
                                </label>
                            </div>

                            <div class="dbp-form-row-column">
                                <label><span class="dbp-form-label "><?php _e('Searchable','admin_form'); 
                                ADFO_fn::echo_html_icon_help('dbp_list-list-structure','searchable');
                                    ?></span>
                                    <input type="text" disabled value="No" class="dbp-input">
                                    <input type="hidden" name="fields_searchable[]" value="no" class="dbp-input">
                                </label>
                            </div>
                        </div>
                        <div class="dbp-form-row js-form-row-custom-field">
                            <label><span class="dbp-form-label "><?php _e('Custom Code','admin_form'); 
                                ADFO_fn::echo_html_icon_help('dbp_list-list-structure','print');
                                ?></span>
                                <div style="display:inline-block; min-width:80%; vertical-align: text-top;">
                                    <input type="hidden" name="fields_custom_view[]" class="js-type-fields" onchange="dbp_change_custom_type(this)" value="CUSTOM">
                                    <textarea name="fields_custom_code[]" class="js-type-custom-code dbp-input" rows="2" style="display:inline-block; width:80%"></textarea>
                                    <div><span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span></div>
                                </div>
                            </label>
                        </div>

                        <h3>column formatting</h3>
                        <div class="dbp-structure-grid">
                            <div class="dbp-form-row-column js-form-row-custom-field">
                                <label><span class="dbp-form-label" style="vertical-align:top"><?php _e('change values','admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-structure','format'); ?></span>
                                </label>
                                <div style="display:inline-block; min-width:50%">
                                    <textarea name="fields_format_values[<?php echo esc_attr($item->name); ?>]" class="dbp-input" rows="4" style=" width:80%; min-width:250px"></textarea>
                                </div>
                            </div>
                            <div class="dbp-form-row-column js-form-row-custom-field">
                                <label><span class="dbp-form-label" style="vertical-align:top"><?php _e('change styles','admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-structure','styles'); ?></span>
                                </label>
                                <div style="display:inline-block; min-width:50%">
                                    <textarea name="fields_format_styles[<?php echo esc_attr($item->name); ?>]" class="dbp-input" rows="4" style="width:80%; min-width:250px"></textarea>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="js-dragable-table">
                        
                    <?php 
                    $names = [];
                    $selected_post_id_attr = '';
                    $post_link_select = [];
                    foreach ($items as $key=>$item) {
                        if ($item->orgtable == $wpdb->posts && $item->orgname == "ID" && $item->custom_param == '') {
                            $selected_post_id_attr = $item->name;
                        } else if ($item->custom_param != '') {
                            $selected_post_id_attr = $item->custom_param;
                        }
                        $post_link_select[$item->name] = $item->name;
                    }

                    foreach ($items as $key=>$item) : 
                        $pri = ($item->table != "" && isset($primaries[$item->orgtable]) && strtolower($primaries[$item->orgtable]) == strtolower($item->orgname));
                        ?>
                        <div class="js-dragable-tr dbp-structure-card js-dbp-structure-card">
                            <div class="dbp-structure-title" >
                                <span class="dbp-form-label js-dragable-handle"><span class="dashicons dashicons-sort"></span></span>
                                <input class="js-dragable-order" name="fields_order[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->order); ?>"></label>
                              
                                <span class="dbp-lf-edit-icon">
                                <span class="dashicons dashicons-edit js-structure-toggle" onclick="dbp_structure_toggle(this)"></span>
                                </span>
                                <?php if ($pri) : ?><span class="dashicons dashicons-admin-network" title="Primary"></span><?php endif; ?>
                                <span onclick="dbp_structure_toggle(this)" style="cursor:pointer"><?php echo ($item->mysql_name) ? '<b>'.$item->title.'</b> - <span title="mysql column: '.esc_attr($item->mysql_name).'" >'. substr($item->name,0,70 - strlen($item->title)).'</span>' : '<b>'.$item->title.'</b>'; ?></span>  
                                <span class="dbp-structure-type" onclick="dbp_structure_toggle(this)">(<?php echo $item->type; ?>)</span>
                                <?php if ($item->origin == "CUSTOM") : ?>
                                    <span class="button" onClick="dbp_list_structure_delete_row(this);"><?php _e('DELETE', 'admin_form'); ?></span>
                                <?php endif; ?>
                                <span class="dbp-structure-title-label">
                                <?php echo ADFO_fn::html_select(['SHOW'=>'Show', 'HIDE'=>'Hide'], true, 'name="fields_toggle['. esc_attr($item->name) . ']" onchange="dbp_change_toggle(this)" class="js-toggle-row"', $item->toggle); ?>
                               
                            </div>
                            <div class="dbp-structure-content js-structure-content">
                                <div class="js-structure-single-block">
                                    <div class="dbp-structure-grid">
                                        <div class="dbp-form-row-column">
                                            <label><span class="dbp-form-label"><?php _e('Table title','admin_form'); 
                                            ADFO_fn::echo_html_icon_help('dbp_list-list-structure','title');
                                            ?></span>
                                                <input type="text" name="fields_title[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->title); ?>" class="js-title dbp-input">
                                            </label>
                                            <input type="hidden" name="fields_origin[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->origin); ?>">
                                        </div>

                                        <div class="dbp-form-row-column">
                                            <label><span class="dbp-form-label"><?php _e('Name in url (for request)','admin_form'); ?></span>
                                                <input type="text" disabled value="<?php echo esc_attr(@$item->name_request); ?>" class="dbp-input">
                                                <input type="hidden" name="fields_name_request[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->name_request); ?>" class=" dbp-input">
                                                </label>
                                            </label>
                                            <input type="hidden" name="fields_mysql_name[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->mysql_name); ?>">
                                            <input type="hidden" name="fields_mysql_table[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->mysql_table); ?>">
                                        </div>

                                        <div class="dbp-form-row-column">
                                            <label><span class="dbp-form-label "><?php _e('Column dimension','admin_form'); ?></span>
                                            <?php echo ADFO_fn::html_select(['extra-small'=>'Extra small', 'small'=>'Small','regular'=>'Regular','large' => 'Large', 'extra-large'=>'Extra large'], true, 'name="fields_width['. esc_attr($item->name).']" class="js-width-fields"', $item->width); ?>
                                            </label>
                                        </div>

                                        <div class="dbp-form-row-column">
                                            <label><span class="dbp-form-label "><?php _e('Searchable','admin_form'); 
                                            ADFO_fn::echo_html_icon_help('dbp_list-list-structure','searchable');
                                            ?></span>
                                            <?php if ($item->mysql_name != "") : ?>
                                            <?php echo ADFO_fn::html_select(['no'=>'No', 'LIKE' => 'the exact phrase as substring (%LIKE%)','='=>'the exact phrase as whole field (=)' ], true, 'name="fields_searchable['. esc_attr($item->name).']" class="js-width-fields"', $item->searchable); ?>
                                            <?php else : ?>
                                                <input type="text" disabled value="No" class="dbp-input">
                                                <input type="hidden" name="fields_searchable[<?php echo esc_attr($item->name); ?>]" value="no" class="dbp-input">
                                            <?php endif; ?>
                                            </label>
                                        </div>
                                            
                                            
                                        <div class="dbp-form-row-column js-form-row-custom-field">
                                            
                                            <label>
                                            <span class="dbp-form-label" style="vertical-align:top">
                                                <?php  
                                                if ($item->origin == "FIELD" || $pri) {
                                                     _e('Column type','admin_form'); 
                                                } else {
                                                    _e('Custom code','admin_form'); 
                                                }
                                                ADFO_fn::echo_html_icon_help('dbp_list-list-structure','print');
                                            ?></span>
                                            </label>
                                            <div style="display:inline-block; min-width:50%">
                                                <?php
                                                if ($item->origin == "FIELD" && !$pri) {
                                                echo ADFO_fn::html_select($select_type_fields, true, 'name="fields_custom_view['. esc_attr($item->name).']" class="js-type-fields" onchange="dbp_change_custom_type(this)" style="display:'. (($item->view =='CUSTOM') ? 'none' :'inline-block').'"', $item->view); 
                                                } else if ($pri) {
                                                    ?><input type="hidden" name="fields_custom_view[<?php echo esc_attr($item->name); ?>]" class="js-type-fields" onchange="dbp_change_custom_type(this)"  value="TEXT"><span style="line-height: 1.7rem;">Text</span><?php
                                                } else {
                                                    ?><input type="hidden" name="fields_custom_view[<?php echo esc_attr($item->name); ?>]" class="js-type-fields" onchange="dbp_change_custom_type(this)"  value="CUSTOM"><?php
                                                }
                                                ?> 
                                                <textarea name="fields_custom_code[<?php echo esc_attr($item->name); ?>]" class="js-type-custom-code dbp-input" rows="2" style="display:<?php echo ($item->view =='CUSTOM') ? 'inline-block' :'none'; ?>; width:80%; min-width:250px"><?php echo esc_textarea($item->custom_code); ?></textarea>
                                            
                                                <?php if ($item->origin == "FIELD") : ?>
                                                <div class="dashicons dashicons-list-view dbp-input-button js-textarea-btn-cancel" style="display:<?php echo ($item->view =='CUSTOM') ? 'inline-block' :'none'; ?>" onclick="dbp_custom_cancel(this)"></div>
                                                <?php endif; ?>
                                                <div class="dbp-link-click js_show_variables_textarea" onclick="show_pinacode_vars()" style="display:<?php echo ($item->view =='CUSTOM') ? 'inline-block' :'none'; ?>">show shortcode variables</div>
                                            </div>
                                        </div>

                                        <div class="dbp-form-row-column">
                                            <label><span class="dbp-form-label "><?php _e('Align','admin_form'); ?></span>
                                            <?php echo ADFO_fn::html_select(['top-left'=>'Top Left', 'top-center'=>'Top Center','top-right'=>'Top Right', 'center-left'=>'Center Left', 'center-center'=>'Center Center','center-right'=>'Center Right', 'bottom-left'=>'Bottom Left', 'bottom-center'=>'Bottom Center','bottom-right'=>'Bottom Right'], true, 'name="fields_align['. esc_attr($item->name).']" class="js-width-fields"', $item->align); ?>
                                            </label>
                                        </div>
                                                
                                        <div class="dbp-form-row-column js-dbp-params-column">
                                            <label<?php echo ($pri) ? ' style="display:none"' : ''; ?>>
                                                <span class="dbp-form-label js-dbp-params-date"><?php _e('Date format','admin_form'); ?></span>
                                                <span class="dbp-form-label js-dbp-params-link"><?php _e('Link text','admin_form'); ?></span>
                                                <span class="dbp-form-label js-dbp-params-checkbox"><?php _e('Checkbox value','admin_form'); ?></span>
                                                <span class="dbp-form-label js-dbp-params-select"><?php _e('Mulitple Choice - Drop down list (Single answer)','admin_form'); ?></span>
                                                <span class="dbp-form-label js-dbp-params-user"><?php _e('Link to author page','admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-structure','user'); ?></span>
                                                <span class="dbp-form-label js-dbp-params-post"><?php _e('Link to article page','admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-structure','post'); ?></span>
                                                <span class="dbp-form-label js-dbp-params-text"><?php _e('Max text length','admin_form'); ?></span>
                                                <input type="text" name="fields_custom_param[<?php echo esc_attr($item->name); ?>]" value="<?php echo esc_attr($item->custom_param); ?>" class="js-input-parasm-custom dbp-input">
                                                <div class="js-dbp-params-textarea-select-div" style="display: block;">
                                                    <textarea class="js-dbp-params-textarea-select" name="fields_custom_param_select[<?php echo esc_attr($item->name); ?>]"  rows="4" style="display: block; width: 50%; float:left;"><?php echo esc_textarea($item->custom_param); ?></textarea>
                                                    <div style="float: left; width: 48%; margin-left: 2%;" class="js-dbp-params-textarea-select-info"> 
                                                    You can manually define the encoded value for each choice by inserting the encoded number and a comma before the choice's label
                                                        <pre>    0, No
    1, Yes</pre> 
                                                    </div>
                                                </div>
                                                <input type="checkbox" value="1" class="js-checkbox-post-link dbp-input">
                                            </label>
                                        </div>
                                        <div class="dbp-form-row-column js-dbp-param-post-id">
                                            <label>
                                                <span class="dbp-form-label js-dbp-params-date"><?php _e('Post_ID','admin_form'); ?></span>
                                                <?php echo ADFO_fn::html_select($post_link_select,true, 'onChange="select_post_id_attr(this)" class="js-select-permalink-postid"', $selected_post_id_attr); ?>
                                            </label>
                                        </div>
                                    </div>
                                                
                                    <div class="js-lookup-params" id="id<?php echo ADFO_fn::get_uniqid(); ?>">
                                        <h3 class="dbp-css-mb-1">Looup params</h3>
                                        <div class="dbp-form-row-column dbp-css-mb-1">
                                            <?php _e('Choose the list and column on which to compare the data','admin_form'); ?>
                                        </div>
                                        <?php
                                        if ( $item->lookup_id != '') {
                                            $lookup_col_list = ADFO_fn::get_table_structure($item->lookup_id, true);
                                            $primary = ADFO_fn::get_primary_key($item->lookup_id);
                                            $pos = array_search($primary, $lookup_col_list);
                                            if ($pos !== false) {
                                                unset($lookup_col_list[$pos]);
                                            }

                                        } else {
                                            $lookup_col_list = [];
                                        }
                                        $list_of_tables = ADFO_fn::get_table_list();
                                        ?>
                                        <div class="dbp-structure-grid" <?php echo ($pri) ? ' style="display:none"' : ''; ?>>
                                            <div class="dbp-form-row-column">
                                                <div class="dbp-form-row-column dbp-css-mb-2">
                                                    <label><span class="dbp-form-label" style="vertical-align:top"><?php _e('Table:','admin_form'); ?></span>
                                                    </label>
                                                    <div style="display:inline-block; min-width:50%">
                                                    <?php echo ADFO_fn::html_select($list_of_tables['tables'], true, 'name="fields_lookup_id[' . esc_attr($item->name) . ']" onchange="dbp_list_change_lookup_id(this)" class="js-select-fields-lookup"', $item->lookup_id); ?>
                                                    </div>

                                                    <input type="hidden"  name="fields_lookup_sel_val[<?php echo esc_attr($item->name); ?>]" value="<?php echo $item->lookup_sel_val; ?>" class="dbp-input js-lookup-select-value">
                                                </div>
                                            </div>
                                            <div class="dbp-form-row-column">
                                                <label><span class="dbp-form-label" style="vertical-align:top"><?php _e('Show','admin_form'); ?></span>
                                                </label>
                                                <div style="display:inline-block; min-width:50%">
                                                    <?php echo ADFO_fn::html_select($lookup_col_list, false, 'name="fields_lookup_sel_txt[' . esc_attr($item->name) . '][]" class="dbp-input js-lookup-select-text" multiple', $item->lookup_sel_txt); ?>
                                                </div>
                                            </div>
                                    
                                        </div>
                                    </div>

                                    <h3<?php echo ($pri) ? ' style="display:none"' : ''; ?>>column formatting</h3>
                                    
                                    <div class="dbp-structure-grid" <?php echo ($pri) ? ' style="display:none"' : ''; ?>>
                                        <div class="dbp-form-row-column js-form-row-custom-field">
                                            <label><span class="dbp-form-label" style="vertical-align:top"><?php _e('change values','admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-structure','format');?></span>
                                            </label>
                                            <div style="display:inline-block; min-width:50%">
                                                <textarea name="fields_format_values[<?php echo esc_attr($item->name); ?>]" class="dbp-input" rows="4" style=" width:80%; min-width:250px"><?php echo esc_textarea(isset($item->format_values) ? $item->format_values : ''); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="dbp-form-row-column js-form-row-custom-field">
                                            <label><span class="dbp-form-label" style="vertical-align:top"><?php _e('change styles','admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-structure','styles') ?></span>
                                            </label>
                                            <div style="display:inline-block; min-width:50%">
                                                <textarea name="fields_format_styles[<?php echo esc_attr($item->name); ?>]" class="dbp-input" rows="4" style="width:80%; min-width:250px"><?php echo esc_textarea(isset($item->format_styles) ? $item->format_styles : ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <label onclick="toggle_inherited(this)" class="js-toggle-inherited-label"><input type="checkbox" name="inherited[<?php echo esc_attr($item->name); ?>]" value="1"<?php echo ($item->inherited == '1') ? ' checked="checked"' : ''; ?>> Keeps settings aligned with information from the same field in tab 'Form'.</label>
                            </div>
                        </div>
                       
                    <?php endforeach ;?>
                    
                </div>

                <div style="margin:1rem; border-top:1px solid #dcdcde; padding-top:.5rem">
                <div onclick="dbp_list_structure_add_row(this)" class="button"><?php _e('Add Custom column', 'admin_form'); ?></div>
               
                <?php do_action('dbp_list_structure_after_btns', $table_model); ?>
             
                </div>
                    
                <hr>
                <h3 class="dbp-h3">General Setting</h3>
                <div class="dbp-form-row">
                    <label><span class="dbp-form-label "><?php echo _e('Max text length', 'admin_form'); ?></span>
                    <input type="number" name="list_general_setting[text_length]" class="dbp-input" value="<?php echo esc_attr($post->post_content['list_general_setting']['text_length']); ?>"></label>
                </div>
                <div class="dbp-submit" onclick="dbp_submit_list_structure();"><?php _e('Save','admin_form'); ?></div>
            </div>
        </form>
    <?php endif; ?>
</div>
