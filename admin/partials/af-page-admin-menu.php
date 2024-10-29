<?php
/**
 * Il template della pagina amministrativa
 * 
 * @var String $render_content Il file dentro partial da caricare 
 */
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="wrap">
<div id="dbp_container" class="dbp-grid-container" style="display:none; position:fixed; width: inherit;">
        <?php
        $table_bulk_ok = (@$table_model->table_status() != 'CLOSE' && count($table_model->get_pirmaries()) > 0 && $post->post_content['delete_params']->allow);
        ?>
        <div class="dbp-column-content" style="border:none">
            <div class="af-content-table js-id-dbp-content" >
                <h1 class="wp-heading-inline"><?php echo wp_kses_post($list_title); ?></h1>
                <span class="page-title-action" onclick="af_edit_details_v2()"><?php  _e('Add New', 'admin_form') ;?></span>
                <?php if (current_user_can('manage_options')) : ?>
                    <a href="<?php echo admin_url("admin.php?page=admin_form&section=list-sql-edit&dbp_id=".$post->ID); ?>" class="page-title-action" target="blank"><span class="dashicons dashicons-admin-generic" style="vertical-align: sub;"></span></a>
                <?php endif; ?>
                <?php wp_enqueue_media(); ?>


                <?php if (@$msg != "") : ?>
                    <div class="dbp-alert-info dpb-alert-snackbar js-alert-snackbar"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (@$msg_error != ""): ?>
                    <div class="dbp-alert-sql-error dpb-alert-snackbar js-alert-snackbar"><?php echo $msg_error; ?></div>
                <?php endif ; ?>
                <div class="dbp-alert-info dpb-alert-snackbar js-alert-snackbar" id="dbp_cookie_msg" style="display:none"></div>
                <div class="dbp-alert-sql-error dpb-alert-snackbar js-alert-snackbar"  id="dbp_cookie_error" style="display:none"></div>

                <?php if ($description != "") : ?>
                    <div class="dbp-description" style="margin-top:.5rem;"><?php echo $description; ?></div>
                <?php endif; ?>

                <form id="table_filter" method="post" action="<?php echo admin_url("admin.php?page=".sanitize_text_field($_REQUEST['page'])); ?>">
                    <?php 
                    do_action('adfo_admin_page_list_after_title', $id, (int)$table_model->total_items);
                    ?>
                    <div class="dbp-wrap-bulk">
                        <?php
                        if ($table_model->last_error === false) {
                            ob_Start();
                            $max_input_vars = (int)ADFO_fn::get_max_input_vars();
                            do_action('dbp_page_admin_menu_after_title', $table_bulk_ok, $table_model);
                            $echo_after_title = ob_get_clean();
                            if ($echo_after_title != "") {
                                echo $echo_after_title;
                            } else {
                                ?>  <div class="dbp-table-footer" style="height:30px"> <a href="https://github.com/giuliopanda/admin-form-pro/releases" target="_blank"><?php _e('BULK ACTION only for PRO version', 'admin_form'); ?></a><br class="clear"></div>   <?php  
                            }
                        } 
                        if (ADFO_functions_list::has_post_status($post)) : ?>
                            <ul class="subsubsub" id="subssubsub">
                                <?php 
                                $all_count = 0;
                                if (!is_array($count_posts)) {
                                    $count_posts = [];
                                }
                                foreach ($count_posts as $cp_post_type => $cp_count) {
                                    if ( $cp_post_type != 'trash') {
                                        $all_count += $cp_count;
                                    }
                                }
                                
                                $post_status_request = (isset($_REQUEST['post_status'])) ? $_REQUEST['post_status'] : "";
                                ?>

                                <li class="all"><a href="<?php echo admin_url("admin.php?page=dbp_".$post->ID); ?>" <?php echo ($post_status_request == '') ? 'class="current"' : ""; ?> aria-current="page">All <span class="count" id="dbp_post_count_all"><?php echo $all_count;?></span></a> |</li>
                                <?php foreach ($count_posts as $cp_post_type => $cp_count) : ?>
                                    <?php if ($cp_count == 0 && $cp_post_type != 'trash') continue; ?>
                                    <li class="<?php echo $cp_post_type; ?>"<?php echo ($cp_count == 0) ? ' style="display:none"' : ''; ?>>
                                    <a href="<?php echo admin_url("admin.php?page=dbp_".$post->ID."&post_status=".$cp_post_type); ?>" <?php echo ($cp_post_type == $post_status_request) ? 'class="current"' : ""; ?>><?php echo ucfirst($cp_post_type); ?> <span class="count" id="dbp_post_count_<?php echo esc_attr($cp_post_type); ?>"><?php echo $cp_count; ?></span></a> |</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif;?>
                    </div>
                   
                    <p class="dbp-search-box">   
                        <input type="search" id="dbp_full_search" name="search" value="<?php echo esc_attr(wp_unslash($_REQUEST['search'])); ?>">
                        <span class="button" onclick="dbp_submit_table_filter('search');">Search</span>
                        &nbsp; 
                        <?php if (count($table_model->tables_primaries) > 0) : ?>
                        <div class="dbp-submit" onclick="af_edit_details_v2()"><?php _e('Add New record','admin_form'); ?></div>
                        <?php endif; ?>
                    </p>
                    
                 
                    <?php 
                    $filter = (isset($_REQUEST['filter']['sort']['field'])) ? esc_attr($_REQUEST['filter']['sort']['field']) : '';
                    $order = (isset($_REQUEST['filter']['sort']['order'])) ? esc_attr($_REQUEST['filter']['sort']['order']) : '';
                    ?>
                    <textarea style="display:none" id="sql_query_executed"><?php echo esc_textarea($table_model->get_current_query()); ?></textarea>
                    <textarea style="display:none" id="sql_query_edit"><?php echo esc_textarea($table_model->get_default_query()); ?></textarea>
                    <input type="hidden" name="page"  value="<?php echo esc_attr($_REQUEST['page']); ?>">
                    <input type="hidden" name="action_query" id="dbp_action_query"  value="">
                    <input type="hidden" id="dbp_table_sort_field" name="filter[sort][field]" value="<?php echo $filter; ?>">
                    <input type="hidden" id="dbp_table_sort_order"  name="filter[sort][order]" value="<?php echo $order; ?>">
                    <input type="hidden" id="dbp_table_filter_limit_start" name="filter[limit_start]" value="<?php echo absint($table_model->limit_start); ?>">
                    <?php if ($table_model->last_error == false && $table_model->total_items > 0) : ?>
                        <div class="tablenav top dbp-tablenav-top">
                            <span class="displaying-num">Show <?php echo count($table_items) -1; ?> of <?php echo wp_kses_post($table_model->total_items); ?> items</span>
                            <span class="" >Element per page: </span>
                            <input type="hidden" name="cache_count" id="cache_count"  value="<?php echo absint($table_model->total_items); ?>">
                            <input type="number" name="filter[limit]" id="Element_per_page" class="dbp-pagination-input" value="<?php echo absint($table_model->limit); ?>" style="width:3.4rem; padding-right:0;" min="1" max="500">
                            <div name="change_limit_start" class="button action dbp-pagination-input"  onclick="dbp_submit_table_filter('change_limit')" >Apply</div>
                            <?php ADFO_fn::get_pagination($table_model->total_items, $table_model->limit_start, $table_model->limit); ?>
                            <?php if (ADFO_fn::is_query_filtered())  : ?>
                                <div id="dbp-bnt-clear-filter-query" class="button"  onclick="dbp_clear_filter()"><?php _e('Clear Filter','admin_form'); ?></div>
                            <?php endif; ?>
                            <br class="clear">
                        </div>
                    <?php endif; ?>
                    
                    <?php echo $html_content; ?>
                </form>
                <div class="clear"></div>
                <br><br>
                <?php if ($table_model->last_error === false) : ?>
                
                        <div class="tablenav-pages">
                            <?php ADFO_fn::get_pagination($table_model->total_items, $table_model->limit_start, $table_model->limit); ?>
                        </div>
                        <br class="clear">
                
                <?php endif; ?>


                <?php 
                $list_of_tables_js = [];
                $list_of_tables = ADFO_fn::get_table_list();
                foreach ($list_of_tables['tables'] as $lot) {
                    $list_of_tables_js[] = $lot;
                }
                ?>
            </div>
        </div>

        <div id="dbp_sidebar_popup" class="dbp-sidebar-popup">
            <div id="dbp_dbp_title" class="dbp-dbp-title">
                <div id="dbp_dbp_close" class="dbp-dbp-close" onclick="dbp_close_sidebar_popup()">&times;</div>
            </div>
            <div id="dbp_dbp_loader" ><div class="dbp-sidebar-loading"><div  class="dbp-spin-loader"></div></div></div>
            <div id="dbp_dbp_content" class="dbp-dbp-content"></div>
        </div>
    </div>
</div>

<br><br>
<?php require (dirname(__FILE__)."/../js/admin-form-footer-script.php");