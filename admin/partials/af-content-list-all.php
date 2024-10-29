<?php
/**
 * Il template della pagina amministrativa
 * Lo spazio dei grafici è impostato qui, e poi verrà disegnato in javascript
 * l'html del setup e del resize bulk invece è caricato sui due html a parte
 */
namespace admin_form;
if (!defined('WPINC')) die;
$dbp_admin_show = $wpdb->get_results("SELECT * FROM ".$wpdb->postmeta ." WHERE meta_key = '_dbp_admin_show'");
// trasformo $dbp_admin_show in un array dove la key è il post_id e il valore è il valore del meta
$dbp_admin_show = array_column($dbp_admin_show, 'meta_value', 'post_id');
?>
<div class="af-content-header">
    <?php require(dirname(__FILE__).'/af-partial-tabs.php'); ?>
</div>

<div class="af-content-table js-id-dbp-content" >
    <div class="af-content-margin">
        <h2 class="dbp-h2-inline af-content-margin"><?php _e('Create a new form', 'admin_form'); ?></h2>
        <span class="dbp-submit" onclick="dbp_create_list_show_form(false)"><?php _e('CREATE NEW FORM', 'admin_form'); ?></span>
        
        <?php if ($msg != "") : ?>
            <div class="dbp-alert-info"><?php echo wp_kses_post($msg); ?></div>
        <?php endif; ?>
        <?php if (@$msg_error != ""): ?>
            <div class="dbp-alert-sql-error"><?php echo wp_kses_post($msg_error); ?></div>
        <?php endif ; ?>
        <hr>
        <?php if ( $post_count['trash'] > 0 || (isset($_REQUEST['action']) && in_array($_REQUEST['action'],['show-trashed','remove-list']))) : ?>
        <ul class="dbp-submenu" style="margin-bottom:0">
                <?php if ($action == "show-trashed" ) : ?>
                    <li><a href="<?php echo admin_url('admin.php?page=admin_form'); ?>">All (<?php echo wp_kses_post($post_count['publish']); ?>)</a></li>
                    <li><b>Trash (<?php echo wp_kses_post($post_count['trash']); ?>)</b></li>
                <?php else: ?>
                    <li><b>All (<?php echo wp_kses_post($post_count['publish']); ?>)</b></li>
                    <li><a href="<?php echo admin_url('admin.php?page=admin_form&action=show-trashed'); ?>">Trash (<?php echo wp_kses_post($post_count['trash']); ?>)</a></li>
                <?php endif; ?>
        </ul>
        <?php endif; ?>
        
        <table class="wp-list-table widefat striped dbp-table-view-list">
            <thead>
                <tr>
                    <td>id</td>
                    <td>Name</td>
                    <td style="width:20%">Description</td>
                    <td>Shortcode</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_page as $post): ?>
                    <?php 
                        $link_show_data = $link = admin_url("admin.php?page=admin_form&section=list-browse&dbp_id=".$post->ID); 
                        if (isset($dbp_admin_show[$post->ID])) {
                            $page_data = maybe_unserialize($dbp_admin_show[$post->ID]);
				            if (is_countable($page_data) && isset($page_data['show']) && $page_data['show'] == 1 && isset($page_data['status']) && $page_data['status'] != 'trash') {
                            $link_show_data = admin_url("admin.php?page=admin_form&page=".$page_data['slug']);
                            }
                        }
                        ?>
                    <tr>
                        <td><?php echo wp_kses_post($post->ID); ?></td>
                        <td>
                            <?php if ($action != "show-trashed" ) : ?>
                            <a href="<?php echo esc_attr($link); ?>"><?php echo wp_kses_post($post->post_title); ?></a>
                            <?php else: ?>
                                <?php echo wp_kses_post($post->post_title); ?>
                            <?php endif; ?>
                            <div class="row-actions visible">
                              
                                <?php if ($post->post_status == "publish") : ?>
                                    <a href="<?php echo esc_attr($link_show_data); ?>">Show data</a> |
                                    <a class="" href="<?php echo admin_url('admin.php?page=admin_form&section=list-all&action=clone-list&dbp_id='.$post->ID); ?>">Clone</a> |  
                                    <a class="dbp-warning-link" href="<?php echo admin_url('admin.php?page=admin_form&section=list-all&action=trash-list&dbp_id='.$post->ID); ?>" onclick="return confirm('Are you sure to remove this list?');">Trash the list</a>
                                <?php elseif ($post->post_status == "trash") :  ?> 
                                    <a class="" href="<?php echo admin_url('admin.php?page=admin_form&section=list-all&action=publish-list&dbp_id='.$post->ID); ?>">Publish</a>
                                    <a class="dbp-warning-link" href="<?php echo admin_url('admin.php?page=admin_form&section=list-all&action=remove-list&dbp_id='.$post->ID); ?>" onclick="return confirm('Are you sure to remove this list?');">Remove permanently</a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="width:20%">
                            <div style=" max-height:150px; overflow-y:auto; width:100%;">
                            <?php echo wp_kses_post($post->post_excerpt); ?></a>
                            </div>
                        </td>
                        <td>
                            <b>[adfo_list id=<?php echo $post->ID; ?>]</b> <?php echo wp_kses_post(($post->shortcode_param!= "") ? __('Attributes', 'admin_form').":<b>".$post->shortcode_param.'</b>' : ''); ?>
                        </td>
                    </tr>
                <?php endforeach ;?>
            </tbody>
        </table>
    </div>
</div>
<?php 
// $dbp = new ADFO_fn();
$list_of_tables = ADFO_fn::get_table_list();
$list_of_tables_js = [];
foreach ($list_of_tables['tables'] as $lot) {
    $list_of_tables_js[] = $lot;
}
?>
<script>
    var dbp_tables = <?php echo json_encode($list_of_tables_js); ?>;
    var dbp_post_table = '<?php echo esc_attr($wpdb->posts); ?>';
</script>