<?php 
/**
 * L'elenco dei dati di una lista 
 * 
 * @var Class $dbp function
 * @var Array $table_items
 * @var Array $list_of_tables

 * @var ADFO_model $table_model  
 */
namespace admin_form;
if (!defined('WPINC')) die;
$table_bulk_ok = ($table_model->table_status() != 'CLOSE' && count($table_model->get_pirmaries()) > 0 && $post->post_content['delete_params']->allow);
?>
<div class="af-content-header">
    <?php require(dirname(__FILE__).'/af-partial-tabs.php'); ?>
</div>
 <div class="af-content-table js-id-dbp-content" >
    <div style="float:right; margin:1rem">
        <?php _e('Shortcode: ', 'admin_form'); ?>
        <b>[adfo_list id=<?php echo wp_kses_post($post->ID); ?>]</b>
    </div>
    <?php 
    if ($table_bulk_ok) {
        $append = '<span class="page-title-action" onclick="af_edit_details_v2()">' . __('Add new content', 'admin_form') . '</span>';
    } else {
        $append = '';
    }
    if (ADFO_fn::echo_html_title_box($list_title, '', $msg,  $msg_error, $append)) :
        wp_enqueue_media();
        if (isset($msg_warning) && $msg_warning != "") :
            ?>
            <div class="dbp-alert-warning"><?php echo $msg_warning; ?></div>
            <?php 
        endif; 
        ?>
        <div class="dbp-alert-info dpb-alert-snackbar js-alert-snackbar" id="dbp_cookie_msg" style="display:none"></div>
        <div class="dbp-alert-sql-error dpb-alert-snackbar js-alert-snackbar"  id="dbp_cookie_error" style="display:none"></div>

        <form id="table_filter" method="post" action="<?php echo admin_url("admin.php?page=admin_form&section=list-browse&dbp_id=".$id); ?>">
            <textarea style="display:none" id="sql_query_executed"><?php echo esc_textarea($table_model->get_current_query()); ?></textarea>
            <textarea style="display:none" id="sql_query_edit"><?php echo esc_textarea($table_model->get_default_query()); ?></textarea>
            <input type="hidden" name="page"  value="admin_form">
            <input type="hidden" name="action_query" id="dbp_action_query"  value="">
            <input type="hidden" id="dbp_table_sort_field" name="filter[sort][field]" value="<?php echo esc_attr(ADFO_fn::esc_request('filter.sort.field')); ?>">
            <input type="hidden" id="dbp_table_sort_order"  name="filter[sort][order]" value="<?php echo esc_attr(ADFO_fn::esc_request('filter.sort.order')); ?>">
            <input type="hidden" id="dbp_table_filter_limit_start" name="filter[limit_start]" value="<?php echo esc_attr(ADFO_fn::esc_request($table_model->limit_start)); ?>">
           
           
            <div class="tablenav top dbp-tablenav-top">
                <p class="search-box">   
                    <input type="search" id="dbp_full_search" name="search" value="<?php echo esc_attr(wp_unslash(ADFO_fn::get_request('search'))); ?>">
                    <span class="button" onclick="dbp_submit_table_filter('search');">Search</span>
                    &nbsp; 
                </p>
                <?php if ($table_model->last_error == false && $table_model->total_items > 0) : ?>
                    <span class="displaying-num"><b><?php echo esc_html($table_model->total_items); ?></b> items</span>
                    <span class="" >Element per page: </span>
                    <input type="hidden" name="cache_count" id="cache_count"  value="<?php echo absint($table_model->total_items); ?>">
                    <input type="number" name="filter[limit]" id="Element_per_page" class="dbp-pagination-input" value="<?php echo absint($table_model->limit); ?>" style="width:3.4rem; padding-right:0;" min="1" max="500">
                    <div name="change_limit_start" class="button action dbp-pagination-input"  onclick="dbp_submit_table_filter('change_limit')" >Apply</div>
                    <?php ADFO_fn::get_pagination($table_model->total_items, $table_model->limit_start, $table_model->limit); ?>
                    <?php if (ADFO_fn::is_query_filtered())  : ?>
                        <div id="dbp-bnt-clear-filter-query" class="button"  onclick="dbp_clear_filter()"><?php _e('Clear Filter','admin_form'); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                <br class="clear">
            </div>
            <?php 
            echo $html_content;
            do_action('dbp_list_browse_after_content', $table_bulk_ok, $table_model);
            ?>
        </form>
    <?php endif; ?>
    <?php 
    $list_of_tables_js = [];
    $list_of_tables = ADFO_fn::get_table_list();
    foreach ($list_of_tables['tables'] as $lot) {
        $list_of_tables_js[] = $lot;
    }
    ?>

</div>
