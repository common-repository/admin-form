<?php
/**
 * Scrive una query
 * 
 * Per il rendering delle tabelle chiama: dirname(__FILE__)."/af-content-table-without-filter" 
 * 
 * @var Boolean $ajax_continue 
 * @var Array $info
 * @var $queries

 */
namespace admin_form;
if (!defined('WPINC')) die;

$append = '<span class="dbp-submit" onclick="dbp_submit_list_sql(this)">' . __('Save', 'admin_form') . '</span>';

?>
<div class="af-content-header">
    <?php require(dirname(__FILE__).'/af-partial-tabs.php'); ?>
</div>
<div class="af-content-table js-id-dbp-content" >
    <div style="float:right; margin:1rem">
        <?php _e('Shortcode: ', 'admin_form'); ?>
        <b>[adfo_list id=<?php echo wp_kses_post($post->ID); ?>]</b>
    </div>
    <form id="table_filter" method="post" action="<?php echo admin_url("admin.php?page=admin_form&section=list-sql-edit&dbp_id=".esc_attr($id)); ?>">  
        <?php ADFO_fn::echo_html_title_box($list_title, '', $msg,  $msg_error, $append); ?>
        <div class="af-content-margin">
            <?php wp_nonce_field('dbp_list_form', 'dbp_list_form_nonce'); ?> 
            <input type="hidden" name="section" value="list-sql-edit">
            <input type="hidden" name="action" value="list-sql-save">
            <input type="hidden" name="dbp_id" value="<?php echo  esc_attr($id); ?>">

            <h3 class="dbp-h3 dbp-margin-top"><?php _e('List settings', 'admin_form'); ?></h3>
            <div class="dbp-form-row">
                <label>
                    <span class="dbp-form-label"><?php _e('Title', 'admin_form'); ?></span>
                    <input name="post_title" class="dbp-input-long" value="<?php echo esc_attr($list_title); ?>">
                </label>
            </div>
            <div class="dbp-form-row">
                <label>
                    <span class="dbp-form-label-top"><?php _e('Descriprion', 'admin_form'); ?></span>
                    <span style="max-width: 400px; display: inline-block;">
                        <textarea name="post_excerpt" class="dbp-form-textarea"><?php echo esc_textarea($post_excerpt); ?></textarea>
                        <span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span>
                    </span>
                </label>
                <label style="margin-left:1rem;vertical-align: top;">
                    <input type="checkbox" class="js-add-role-cap" name="show_desc" value="1" <?php echo (isset($post->post_content['show_desc']) && $post->post_content['show_desc'] == 1) ? 'checked="checked"' : ''; ?> style="vertical-align: middle;">
                    <span ><?php _e('Show description','admin_form'); ?></span>
                </label>

            </div>


            <h3 class="dbp-h3 dbp-margin-top"><?php _e('Admin sidebar menu', 'admin_form'); ?></h3>
            <p class="dbp-alert-gray" style="margin-top:-1rem">
                <?php _e('Add the list in the sidebar menu.','admin_form'); 
                ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_sidebar_menu');
                ?>
            </p>
            <div class="dbp-form-row">
                <label>
                    <input type="checkbox" name="show_admin_menu" id="cb_show_admin_menu" value="1"  <?php echo (@$dbp_admin_show['show'] == 1) ? 'checked="checked"' : ''; ?> onchange="cb_change_toggle_options(this)">
                    <span class="dbp-form-label"><?php _e('Show in admin menu', 'admin_form'); ?></span>
                </label>
            </div>
            <div id="admin_menu_options_box"  style="display:<?php echo (@$dbp_admin_show['show'] == 1) ? 'block' : 'none'; ?>">
                <div class="dbp-structure-grid">
                    <div class="dbp-form-row-column">
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Title', 'admin_form'); ?></span>
                                <?php $menu_title = (isset($dbp_admin_show['menu_title']) && $dbp_admin_show['menu_title'] != "") ? $dbp_admin_show['menu_title'] : $list_title; ?>
                                <input name="menu_title" class="dbp-input-long" value="<?php echo esc_attr($menu_title); ?>">
                            </label>
                        </div>
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Add custom icon', 'admin_form');    ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_sidebar_menu_icon'); ?></span>
                                <input name="menu_icon" class="dbp-input-long" value="<?php echo esc_attr(@$dbp_admin_show['menu_icon']); ?>">
                            </label>
                        </div>
                        <div class="dbp-form-row">
                            <label>
                                <span class="dbp-form-label"><?php _e('Position (number)', 'admin_form'); ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_sidebar_menu_position'); ?></span>
                                <input name="menu_position" class="dbp-input-long" value="<?php echo esc_attr(@$dbp_admin_show['menu_position']); ?>">
                            </label>
                        </div>
                    </div>
                    <div class="dbp-form-row-column" style="padding-left:2rem">
                        
                        <label><?php 
                        _e('Permissions', 'admin_form'); 
                        ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_sidebar_menu_permissions'); 
                        ?></label>
                        <?php foreach($wp_roles->get_names() as $role_key=>$role_name): ?>
                            <?php $role = get_role( $role_key ); ?>
                        
                                <div class="dbp-form-row">
                                    <label>
                                        <input type="checkbox" class="js-add-role-cap" name="add_role_cap[]" value="<?php echo $role_key; ?>" <?php echo ($role->has_cap('dbp_manage_'.$id)) ? 'checked="checked"' : ''; ?>>
                                        <span class="dbp-form-label-top"><?php echo $role_name;  ?></span>
                                    </label>
                                </div>
                        
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php if (isset($post->post_content['sql_from']) && is_array($post->post_content['sql_from']) && is_array($post->post_content['sql_from'])) : ?>    
                <div class="af-content-margin dbp-structure-grid">
                    <div class="dbp-form-row-column">
                        <h3 class="dbp-h3"><?php _e('Table selected', 'admin_form'); ?></h3>
                        <p class="dbp-alert-gray" style="margin-top:-1rem"><?php _e('List of tables used in data extraction', 'admin_form'); ?></p>
                        <?php 
                        $inserted = [];
                        foreach ($post->post_content['sql_from'] as $form) {
                            if (!in_array($form, $inserted)) {
                                echo '<div style="font-size:1.1rem; margin-left:2rem;">'.$form.'</div>';
                                $inserted[] = $form;
                            }
                        }
                        ?>
                    </div>
                    <div class="dbp-form-row-column">
                        <h3 class="dbp-h3"><?php _e('Metadata Table', 'admin_form'); ?></h3>
                        <p class="dbp-alert-gray" style="margin-top:-1rem"><?php _e('Link a table to manage linked metadata. To wp_post you can attach wp_postmeta', 'admin_form'); ?></p>
                        <?php 
                        if (isset($metadata_tables) && is_countable($metadata_tables) && count($metadata_tables) > 0)  {
                            echo ADFO_fn::html_select(array_merge([''=>'No Metadata selected'], $metadata_tables), true, 'name="sql_metadata_table"', $sql_metadata_table);
                        } else {
                            _e("I didn't find any metadata tables", 'admin_form');
                            ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_sidebar_metadata');
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php do_action( 'dbp_list_sql_edit_html_after_table', $table_model, $show_query ); ?>
            <div class="af-content-margin">
                <h3 class="dbp-h3"><?php _e('Options', 'admin_form'); ?></h3>
                <div class="dbp-form-row">
                    <label>
                        <span class="dbp-form-label "><?php _e('Elements per page', 'admin_form'); ?></span>
                        <input name="sql_limit" class="dbp-input" value="<?php echo absint($sql_limit); ?>">
                    </label>
                </div>
                <div class="dbp-form-row">
                    <label>
                        <span class="dbp-form-label "><?php _e('Default Order', 'admin_form'); ?></span>
                        <?php echo ADFO_fn::html_select(array_merge(['' => '-', 'RANDOM' => 'Random'],$info_rows), true, 'name="sql_order[field]"', $sql_order['field']); ?>
                        <?php echo ADFO_fn::html_select(['ASC','DESC'], false, 'name="sql_order[sort]"', $sql_order['sort']); ?>
                    </label>
                </div>

                <?php 
                /**
                 * POST TYPE
                 * Verifico che esista solo una tabella posts
                 * @since 1.7.0
                 */
                $show_post_type_box = 0;
                if (isset($post->post_content['sql_from']) && is_array($post->post_content['sql_from'])) {
                    foreach ($post->post_content['sql_from'] as $from) {
                        if ($from == $wpdb->posts) {
                            $show_post_type_box++;
                        }
                    }
                }
                if ($show_post_type_box == 1) :
                    ?>
                    <div class="af-content-margin">
                        <h3 class="dbp-h3 dbp-margin-top"><?php _e('Post type', 'admin_form'); ?></h3>
                        <p class="dbp-alert-gray" style="margin-top:-1rem">
                            <?php _e('The section for managing post types','admin_form'); 
                            ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_sidebar_menu');
                            ?>
                        </p> 
                        <div id="admin_menu_options_box">
                          
                            <div class="dbp-form-row-column">
                                <div class="dbp-form-row">
                                    <label>
                                        <span class="dbp-form-label"><?php _e('Post type', 'admin_form'); ?></span>
                                        <input type="hidden" id="adfo_edit_post_type" name="post_type_name" class="dbp-input-long" value="<?php echo esc_attr($post->post_content['post_type']['name']); ?>" >
                                        <?php if ($post->post_content['post_type']['name'] != '') : ?>
                                        
                                            <span id="adfo_edit_post_type_fake" class="dbp-backend-input-edit-fake"><?php echo esc_attr($post->post_content['post_type']['name']); ?></span>
                                            <div class="button" onclick="dbp_edit_post_type(this, true)">EDIT</div>
                                        <?php else : ?>
                                            <div class="button" onclick="dbp_edit_post_type(this, false)">FILTER BY POST TYPE</div>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                <div class="dbp-form-row">
                                    <label>
                                        <span class="dbp-form-label "><?php _e('link', 'admin_form'); ?></span>
                                        <span><span class="dbp-adfo-post-type-slug-pre"><?php echo get_site_url(); ?>/</span>
                                            <input name="post_type_slug" class="dbp-input" value="<?php echo esc_attr(isset($post->post_content['post_type']['slug']) ? $post->post_content['post_type']['slug'] : ''); ?>">
                                        </span>
                                    </label>
                                </div>
                            </div>   
                        </div>
                    </div>
                <?php endif; ?>
                <?php /** Mostro i permessi se c'è l'autore */ ?>
                <?php if (ADFO_functions_list::has_post_author($post)) : ?>
                    <div class="af-content-margin">
                        <h3 class="dbp-h3 dbp-margin-top"><?php _e('Permissions', 'admin_form'); ?></h3>
                        <p class="dbp-alert-gray" style="margin-top:-1rem">
                            <?php _e('In the form tab you have inserted an author column. For this reason you can assign author or editor permissions to a role. The author can edit only his own articles. The editor can edit all articles.','admin_form'); 
                            ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_permission');
                            ?>
                        </p> 
                        <div id="admin_menu_options_box">
                            <div class="dbp-structure-grid-3">
                                <?php 
                                $list_of_roles = $wp_roles->get_names();
                                $all_list_of_roles = $list_of_roles = array_merge(['_all_others'=>'all others'], $list_of_roles, ['_no_body'=>'none']);
                                unset($list_of_roles['administrator']);
                                ?>
                                <?php if (ADFO_functions_list::has_post_status($post)) : ?>
                                    <div class="dbp-form-row-column">
                                        <div class="dbp-form-row">
                                            <label>
                                                <span class="dbp-form-label" style="min-width:inherit"><?php _e('Contributor'); ?></span>
                                            </label>
                                            <?php echo ADFO_fn::html_select($list_of_roles, true, 'name="post_status_permission[contributor]" style="max-width:150px" id="status_contributor" class="js-post-status-permission" onchange="post_status_permission_change(this)"', isset($post->post_content['post_status']['permission']['contributor']) ? $post->post_content['post_status']['permission']['contributor'] : 'contributor'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="dbp-form-row-column">
                                    <div class="dbp-form-row">
                                        <label>
                                            <span class="dbp-form-label" style="min-width:inherit"><?php _e('Author'); ?></span>
                                        </label>
                                        <?php echo ADFO_fn::html_select($list_of_roles, true, 'name="post_status_permission[author]" style="max-width:150px" id="status_author" class="js-post-status-permission" onchange="post_status_permission_change(this)"', isset($post->post_content['post_status']['permission']['author']) ? $post->post_content['post_status']['permission']['author'] : 'author'); ?>
                                    </div>
                                </div>
                                <div class="dbp-form-row-column">
                                    <div class="dbp-form-row">
                                        <label>
                                            <span class="dbp-form-label" style="min-width:inherit"><?php _e('Editor'); ?></span> 
                                        </label>
                                        <?php echo ADFO_fn::html_select($all_list_of_roles, true, 'name="post_status_permission[editor]" style="max-width:150px" id="status_editor" class="js-post-status-permission" onchange="post_status_permission_change(this)"', isset($post->post_content['post_status']['permission']['editor']) ? $post->post_content['post_status']['permission']['editor'] : 'editor'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                endif;
                /** 
                 * end post type
                 */
                ?>
                <h3 class="dbp-h3"><?php _e('Filter (in frontend And admin plugin)', 'admin_form'); ?></h3>
                <p class="dbp-alert-gray" style="margin-top:-1rem">
                    <?php _e('Add filters from external data', 'admin_form'); 
                ADFO_fn::echo_html_icon_help('dbp_list-list-sql-edit','admin_filter');?>
                    <span class="dbp-link-click" onclick="show_pinacode_vars(['[^current_post.id]'])">show shortcode variables</span>
                </p>
                <?php if (count($info_rows) > 0) : ?>
                    <div class="dbp-form-row" id="dbp_clone_master" style="display:none">
                        <label>
                            <?php echo ADFO_fn::html_select($info_rows, true, 'name="sql_filter_field[]" style="max-width:25%"'); ?>
                            <?php echo ADFO_fn::html_select($info_ops, true, 'name="sql_filter_op[]" style="max-width:15%"'); ?>
                            <textarea class="dbp-input" name="sql_filter_val[]" rows="1" style="min-width:350px"></textarea>
                            <span> required <input type="checkbox" onchange="dbp_required_field(this)"><input type="hidden" name="sql_filter_required[]" class="js-filter-required"> </span>
                        
                            <div class="button" onclick="dbp_remove_sql_row(this)"><?php _e('DELETE','admin_form'); ?></div>
                        </label>
                    </div>
                    <div id="dbp_container_filter">
                        <?php foreach ($sql_filter as $filter) : ?>
                           <?php  $filter['required'] = (!isset($filter['required'])) ? '' : $filter['required']; ?>
                            <div class="dbp-form-row">
                                <label>
                                    <?php echo ADFO_fn::html_select($info_rows, true, 'name="sql_filter_field[]"', $filter['column']); ?>
                                    <?php echo ADFO_fn::html_select($info_ops, true, 'name="sql_filter_op[]"', $filter['op']); ?>
                                    <textarea class="dbp-input" name="sql_filter_val[]" rows="1" style="min-width:350px; vertical-align:top"><?php echo esc_textarea(wp_unslash($filter['value'])); ?></textarea>
                                    <span>  required <input type="checkbox" onchange="dbp_required_field(this)"<?php echo esc_attr($filter['required']) ? ' checked="checked"' : ''; ?>><input type="hidden" name="sql_filter_required[]" class="js-filter-required" value="<?php echo esc_attr($filter['required']); ?>"> </span>
                                    <div class="button" onclick="dbp_remove_sql_row(this)"><?php _e('DELETE','admin_form'); ?></div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="dbp-form-row">
                        <label>
                            <div onclick="dbp_list_sql_add_row()" class="button"><?php _e('Add row', 'admin_form'); ?></div>
                        </label>
                    </div>
                <?php else : ?>
                <?php _e('Salva la query prima di impostare i filtri','admin_form'); ?>
                <?php endif; ?>
        
                <?php do_action( 'dbp_list_sql_edit_html_bottom', $table_model, $post_allow_delete ); ?>
 
                <br><br><hr>
                <div class="dbp-submit" onclick="dbp_submit_list_sql(this)">Save</div>
                <br><br>
            </div>
        </div>
    </form>
</div>