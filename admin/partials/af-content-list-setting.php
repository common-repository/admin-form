<?php

/**
 * Descrizione della pagina
 * @var $vars descrizione delle variabili ereditate
 */
namespace admin_form;
if (!defined('WPINC')) die;
$append = '<span class="dbp-submit" onclick="dbp_submit_list_setting()">' . __('Save', 'admin_form') . '</span>';

?>
<div class="af-content-header">
    <?php require(dirname(__FILE__) . '/af-partial-tabs.php'); ?>
</div>

<div class="af-content-table js-id-dbp-content">
    <div style="float:right; margin:1rem">
            <?php _e('Shortcode: ', 'admin_form'); ?>
            <b>[adfo_list id=<?php echo wp_kses_post($post->ID); ?>]</b>
    </div>
    <?php ADFO_fn::echo_html_title_box($list_title, '', $msg,  $msg_error, $append); ?>
  

    <div class="af-content-margin">
        <form id="list_setting_form" method="POST" action="<?php echo admin_url("admin.php?page=admin_form&section=list-setting&dbp_id=" . $id); ?>" id="dbp_create_table">
            <?php wp_nonce_field('dbp_list_form', 'dbp_list_form_nonce'); ?> 
            <input type="hidden" name="action" value="list-setting-save" />
            <h3 class="dbp-h3 dbp-margin-top">
                <?php _e('List of records', 'admin_form'); ?>
                <?php  ADFO_fn::echo_html_icon_help('dbp_list-list-setting','list_of_records');  ?>
            </h3>
    
            <div id="block_if">
                <div class="dbp-form-row dbp-show-if">
                    <label style="vertical-align: top;">
                        <span class="dbp-form-label"><input type="checkbox" name="frontend_view[checkif]" value="1" id="checkbox_show_if" onchange="dbp_checkif()" <?php echo (@$few['checkif'] == 1) ? 'checked="checked"' : ''; ?>)> <?php _e('Show IF', 'admin_form'); ?>  <?php  ADFO_fn::echo_html_icon_help('dbp_list-list-setting','show_if');  ?></span>
                    </label>
                        <div id="dbp_textarea_if"> 
                            <?php  $few['if_textarea'] = (isset($few['if_textarea'])) ? $few['if_textarea'] : ''; ?>
                            <textarea class="dbp-form-textarea" rows="2" name="frontend_view[if_textarea]"><?php echo esc_textarea(wp_unslash(@$few['if_textarea'])); ?></textarea>
                            <div ><span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span></div>
                        </div>
                
                    <?php if (@$few['checkif'] == 1) : ?>
                        <?php if ($errors_if_textarea == "") : ?>
                        <?php else : ?>
                        <span class="dashicons dashicons-warning dbp-dashicons-red" title="<?php echo esc_attr($errors_if_textarea); ?>"></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dbp-form-row dbp-list-setting-color-margin-left-first">
                <label>
                    <span class="dbp-form-label"><?php _e('List type', 'admin_form'); ?></span>
                    <?php echo ADFO_fn::html_select(['TABLE_BASE' => 'Table', 'EDITOR' => 'Custom'], true, 'name="frontend_view[type]" onchange="dbp_list_setting(this)" id="dbp_choose_type_frontend_view"', @$few['type']); ?>
                </label>
                <hr>
            </div>
            <div id="frontend_view_table">
                <div class="dbp-grid-3-columns dbp-list-setting-color-margin-left">
                    <div class="dbp-column">
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Style color', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['blue' => 'BLue', 'green' => 'Green', 'red' => 'Red', 'pink' => 'Pink', 'yellow' => 'Yellow', 'orange' => 'Orange',  'purple' => 'Purple',  'gray' => 'Gray', 'white'=>'White', 'light'=>'Light', 'light-blue'=>'Light-blue'], true, 'name="frontend_view[table_style_color]" onchange="dbp_update_css_table()" id="dbp_css_color"', @$few['table_style_color']); ?>
                            </label>
                        </div>
                        
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Pagination position', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['' => 'None', 'down' => 'Down', 'up' => 'Up', 'both' => 'Both'], true, 'name="frontend_view[table_pagination_position]" onchange="dbp_update_position_pagination()" id="dbp_position_pagination"', @$few['table_pagination_position']); ?>
                            </label>
                        </div>
                        <div class="dbp-form-row" id="dbp_pagination_style_row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Pagination style', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['select' => 'Select', 'numeric' => 'Numeric'], true, 'name="frontend_view[table_pagination_style]" onchange="dbp_update_position_pagination()" id="dbp_pagination_style"', @$few['table_pagination_style']); ?>
                            </label>
                        </div>
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Column sort', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['' => 'No', 'icon1' => 'Yes'], true, 'name="frontend_view[table_sort]" onchange="dbp_update_column_sort()" id="dbp_table_sort"', @$few['table_sort']); ?>
                            </label>
                        </div>
                    </div>
                    <div class="dbp-column">
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Search', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['' => 'No', 'simple' => 'Yes'], true, 'name="frontend_view[table_search]" onchange="dbp_update_search()" id="dbp_table_search"', @$few['table_search']); ?>
                            </label>
                        </div>
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Table Dimension', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['xsmall' => 'X Small', 'small' => 'Small', '' => 'Normal' , 'big' => 'Big'], true, 'name="frontend_view[table_size]" onchange="dbp_update_css_table()" id="dbp_table_size"', @$few['table_size']); ?>
                            </label>
                        </div>
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Table update', 'admin_form');  ADFO_fn::echo_html_icon_help('dbp_list-list-setting','update'); ?></span>
                                <?php echo ADFO_fn::html_select(['get' => 'Get', 'post' => 'Post', 'ajax' => 'Ajax'], true, 'name="frontend_view[table_update]"', @$few['table_update']); ?>
                            </label>
                        </div>
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Export button', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['' => 'No', 'csv' => 'CSV', 'sql' => 'SQL'], true, 'name="frontend_view[table_export]" onchange="dbp_update_download()" id="dbp_table_export"', @$few['table_export']); ?>
                            </label>
                        </div>
                    </div>
                    <div class="dbp-column">
                        <h4><?php _e('Table example', 'admin_form'); ?></h4>
                        <div class="af-content-table-example dbp-admin-content-table" style="margin:0">
                            
                            <div id="dbp_content_table">
                                <div class="dbt-download-box"><a href="#" id="dbp_preview_table_download" class="dbp-download-button" style=" line-height: 15px; padding: 0.1rem 0.5rem; "><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.88 122.88" width="14px" height="14px"><path d="M61.44 0A61.46 61.46 0 1 1 18 18 61.21 61.21 0 0 1 61.44 0Zm10 50.74A3.31 3.31 0 0 1 76 55.47L63.44 67.91a3.31 3.31 0 0 1-4.65 0L46.38 55.65A3.32 3.32 0 0 1 51 50.92l6.83 6.77.06-23.84a3.32 3.32 0 0 1 6.64.06l-.07 23.65 6.9-6.82ZM35 81.19v-13a3.32 3.32 0 0 1 6.64.06v9.45h39.5v-9.51a3.32 3.32 0 1 1 6.64.06v12.91a3.32 3.32 0 0 1-3.29 3.17H38.34A3.32 3.32 0 0 1 35 81.19Zm64.44-57.75a53.74 53.74 0 1 0 15.74 38 53.58 53.58 0 0 0-15.74-38Z"/></svg><span style="vertical-align: top; margin-left: 6px;">Download</span></a></div>
                                <div class="dbp-search-row" id="dbp_preview_table_search">
                                    <input type="text" class="dbp-search-input">
                                    <div class="dbp-search-button">Search</div>
                                </div>
                               
                                <div class="dbp-pagination" id="dbp_pag_up">
                                    <div class="dbp-pagination-total">total: xx</div>
                                    <div class="dbp-pagination-btns">
                                        <a href="#">&laquo;</a>
                                    </div>
                                    <select style="font-size:1em; padding:.2em 2.4em .2em .8em"><option value="1">1</option><option value="2">2</option></select>
                                    <div class="dbp-pagination-btns">
                                        <a href="#">&raquo;</a>
                                    </div>
                                </div>
                                <div class="dbp-pagination" id="dbp_pag2_up">
                                    <div class="dbp-pagination-total">total: xx</div>
                                    <div class="dbp-pagination-btns">
                                        <a href="#">&laquo;</a>
                                        <a href="#">1</a>
                                        <a href="#" class="active">2</a>

                                    </div>
                                </div>
                                <table class="dbp-table-view-list" id="dbp_test_table">
                                    <thead>
                                        <tr>
                                            <th style="min-width:15em"><span class="js-no-order" >Column A</span><span class="js-order-link dbp-title-order-link" href="#">Column A &udarr;</span></th>
                                            <th style="min-width:15em"><span class="js-no-order">Column B</span><span class="js-order-link dbp-title-order-link" href="#">Column B &udarr;</span></th>
                                          
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Lorem Ipsum</td>
                                            <td>Lorem Ipsum</td>
                                        </tr>
                                        <tr>
                                            <td>Lorem Ipsum</td>
                                            <td>Lorem Ipsum</td>
                                        </tr>
                                        <tr>
                                            <td>Lorem Ipsum</td>
                                            <td>Lorem Ipsum</td>
                                        </tr>
                                        <tr>
                                            <td>Lorem Ipsum</td>
                                            <td>Lorem Ipsum</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="dbp-pagination" id="dbp_pag_down">
                                    <div class="dbp-pagination-total">total: xx</div>
                                    <div class="dbp-pagination-btns">
                                        <a href="#">&laquo;</a>
                                    </div>
                                    <select style="font-size:1em; padding:.2em 2.4em .2em .8em"><option value="1">1</option><option value="2">2</option></select>
                                    <div class="dbp-pagination-btns">
                                        <a href="#">&raquo;</a>
                                    </div>
                                </div>
                                <div class="dbp-pagination" id="dbp_pag2_down">
                                    <div class="dbp-pagination-total">total: xx</div>
                                    <div class="dbp-pagination-btns">
                                        <a href="#">&laquo;</a>
                                        <a href="#">1</a>
                                        <a href="#" class="active">2</a>
                                        <a href="#">3</a>
                                        <a href="#">&raquo;</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="frontend_view_editor">
                <div class="dbp-list-setting-color-margin-left">
                    <div class="dbp-grid-3-columns">
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label">
                                    <?php _e('Table update', 'admin_form'); ?>
                                    <?php  ADFO_fn::echo_html_icon_help('dbp_list-list-setting','table_update');  ?>
                                </span>
                                <?php echo ADFO_fn::html_select(['none' => 'None', 'get' => 'Get', 'post' => 'Post', 'ajax' => 'Ajax', 'link'=>'Link'], true, 'name="editor_table_update" onchange="select_editor_table_update()" id="select_editor_table_upldate"', @$few['table_update']); ?>
                            </label>
                        </div>
                        <div class="dbp-form-row" id="dbp_pagination_style_row_2">
                            <label>
                                <span class="dbp-form-label"><?php _e('Pagination style', 'admin_form'); ?></span>
                                <?php echo ADFO_fn::html_select(['select' => 'Select', 'numeric' => 'Numeric'], true, 'name="editor_table_pagination_style"', @$few['table_pagination_style']); ?>
                            </label>
                        </div>
                    </div>
                    <div class="dbp-form-row">
                        <label>
                            <span class="dbp-form-label-long"><b><?php _e('Header (first special row)', 'admin_form'); ?></b></span>
                            <p class="dbp-alert-gray" style="margin-top:-.5rem">
                            Special variables: [%html.pagination], [%html.search], [%total_row], [%key], [%data]
                            </p>
                            <?php $few['content_header'] = (isset($few['content_header'])) ? $few['content_header'] : ''; ?>
                            <textarea id="editor_content_header" name="frontend_view[content_header]"><?php echo esc_textarea($few['content_header']); ?></textarea>
                            <span class="dbp-link-click" onclick="show_pinacode_vars(['html.pagination', 'html.search', 'total_row', 'key'])">show shortcode variables</span>
                        </label>
                    </div>
                  
                    <div class="dbp-form-row">
                        <label>
                            <span class="dbp-form-label-long"><b><?php _e('Loop the data', 'admin_form'); ?></b>
                                <?php  ADFO_fn::echo_html_icon_help('dbp_list-list-setting','loop_data');  ?>
                            </span>
                            <p class="dbp-alert-gray" style="margin-top:-.5rem">
                            If 'Detailed view' is active, you can create a link that opens the popup to show the details box. Example <?php echo htmlentities('<a href="[^LINK_DETAIL]" class="js-dbp-popup">detail</a>'); ?> <br> Other special variables: [%key], [%data]</p>
                            <?php $few['content'] = (isset($few['content'])) ? $few['content'] : ''; ?>
                            <textarea id="editor_content" name="frontend_view[content]"><?php echo esc_textarea($few['content']); ?></textarea>
                            <span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span>
                        </label>
                    </div>

                    <div class="dbp-form-row">
                        <label>
                            <span class="dbp-form-label-long"><?php _e('Footer', 'admin_form'); ?></span>
                            <?php $few['content_footer'] = (isset($few['content_footer'])) ? $few['content_footer'] : ''; ?>
                            <textarea id="editor_content_footer" name="frontend_view[content_footer]"><?php echo esc_textarea($few['content_footer']); ?></textarea>
                            <span class="dbp-link-click" onclick="show_pinacode_vars(['html.pagination', 'html.search', 'total_row', 'key'])">show shortcode variables</span>
                        </label>
                    </div>

                </div>
            </div>

            <div id="block_else" class="dbp_block_else">
                <div class="dbp-form-row dbp-show-if">
                    <label>
                        <span class="dbp-form-label"><?php _e('ELSE :', 'admin_form'); ?></span>
                      
                    </label>
                </div>
                <div class="dbp-form-row dbp-list-setting-color-margin-left">
                <p class="dbp-alert-gray" >
                        In addition to the normal template engine tags here you can use: <b>[%total_row]</b> to know how many rows have been extracted. <b>[%html.search]</b> to print the search form. <b>[%html.pagination]</b> for pagination. <b>[%html.table]</b> prints the table. <b>[%html.no_result]</b> prints the result of the "no result" field. <b>[%data]</b> the list of rows extracted from the database.</p>
                    <textarea id="editor_else" name="frontend_view[content_else]" style="height:300px"><?php echo esc_textarea(@$few['content_else']); ?></textarea>
                    <span class="dbp-link-click" onclick="show_pinacode_vars(['total_row','html.search','html.pagination','html.table','html.no_result'])">show shortcode variables</span>
                </div>
            </div>
            <div id="no_result">
                <h3 class="dbp-h3 dbp-margin-top"><?php _e('No result', 'admin_form'); ?></h3>
                <p class="dbp-alert-gray" style="margin-top:-1rem">
                <?php _e('What appears if there are no results. It is used only when compiled. Unlike "show if [%total_row]> 0", no result keeps showing the search field.','admin_form');  ?>
                </p>
                <div>
                    <div class="dbp-form-row">
                        <?php
                        $few['no_result_custom_text'] = (isset($few['no_result_custom_text'])) ? $few['no_result_custom_text'] : ''; ?>
                        <textarea class="dbp-form-textarea" id="editor_no_result" rows="2" name="frontend_view[no_result_custom_text]"><?php echo esc_textarea(wp_unslash(@$few['no_result_custom_text'])); ?></textarea>
                        <span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span>
                    </div>
                </div>
            </div>

            <h3 class="dbp-h3 dbp-margin-top"><?php _e('Detailed view', 'admin_form'); ?></h3>
            <p class="dbp-alert-gray" style="margin-top:-1rem">
                <?php _e('You can choose to view the content detail on a page.','admin_form'); 
                ADFO_fn::echo_html_icon_help('dbp_list-list-setting','detail');
                ?>
            </p>
            <div>
                <div class="dbp-grid-2-columns">
                    <div class="dbp-form-row">
                        <label><span class="dbp-form-label"><?php _e('View type', 'admin_form'); ?></span><?php  ADFO_fn::html_select(['TABLE'=>'Table',  'CUSTOM'=>'Custom','PHP'=>'PHP'], true, 'name="frontend_view[detail_type]" id="select_detail_toggle" onchange="detail_toggle()"', @$few['detail_type']); ?>
                        </label>
                    </div>
                    <div class="dbp-form-row">
                        <label><span class="dbp-form-label"><?php _e('Popup type', 'admin_form'); ?></span><?php  ADFO_fn::html_select([''=>'Base','large'=>'large',  'fit'=>'Fit'], true, 'name="frontend_view[popup_type]" id="select_popup_type"', @$few['popup_type']); ?>
                        </label>
                    </div>
                </div>
                <div class="dbp-form-row" id="detail_info_table">
                    <?php _e('Mostra i dati in una tabella','admin_form'); ?>
                </div>
                <div class="dbp-form-row" id="detail_info_php">
                    <?php printf(__('The action is now active: <b>do_action("dbp_detail_%s", $data)</b> ','admin_form'),$id); ?>
                    <h4>Example</h4>
                    <div class="dbp-docs-content" style="padding:0">
                        <pre class="dbp-code">add_action(\'dbp_detail_%s\', function($data) {
    var_dump ($data);
});</pre>
                    </div>
                </div>
                <div class="dbp-form-row" id="detail_text">
                    <span class="dbp-form-label"><?php _e('Detail Template', 'admin_form'); ?></span>
                    <textarea class="dbp-form-textarea" id="editor_detail_template" rows="2" name="frontend_view[detail_template]"><?php echo esc_textarea(wp_unslash(@$few['detail_template'])); ?></textarea>
                    <span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span>
                </div>
            </div>

            <br class="clear">
            <div class="dbp-submit" onclick="dbp_submit_list_setting()"><?php _e('Save', 'admin_form'); ?></div>
        </form>
    </div>
</div>