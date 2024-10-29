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
$append = '<span class="dbp-submit" onclick="dbp_submit_list_form()">' . __('Save', 'admin_form') . '</span>';
do_action( 'dbp_list_form_pre_form' ); 

?>
<div class="af-content-header">
    <?php require(dirname(__FILE__).'/af-partial-tabs.php'); ?>
</div>
<div class="af-content-table js-id-dbp-content">
    <div style="float:right; margin:1rem">
            <?php _e('Shortcode: ', 'admin_form'); ?>
            <b>[adfo_list id=<?php echo esc_html($post->ID); ?>]</b>
    </div>
    <?php ADFO_fn::echo_html_title_box($list_title, '', $msg, $msg_error, $append); ?>
    <div class="dbp-lf-container-table-pre">&nbsp;</div>
    <form id="list_form" method="POST" action="<?php echo admin_url("admin.php?page=admin_form&section=list-form&dbp_id=".$id); ?>" style="max-width:1450px">
        <?php wp_nonce_field('dbp_list_form', 'dbp_list_form_nonce'); ?> 
        <input type="hidden" name="action" value="list-form-save" />
        <input type="hidden" name="table" value="<?php echo (isset($import_table)) ? $import_table : ''; ?>" />
        <input type="hidden" name="dbp_id" value="<?php echo esc_html($id); ?>" id="dbp_id_list" />
        <div class="af-content-margin">
            
            <div class="js-dragable-table-container" id="container-dragable-table">
                <?php 
                $count_fields = 0;
                $count_tables_order = 0;
                foreach ($tables as $key=>$table) {
                    $table_options = $table['table_options']; //ADFO_fn::get_dbp_option_table($table['table_name']);
                    $primary_key = ADFO_fn::get_primary_key($table['table_name']);
                    if ($primary_key != "")  {       
                        $options = maybe_unserialize($table['table_options']->table_options);
                        $type = (is_array($options)) ? $options['type'] : 'CLASSIC'; 
                        if ($table['table_name'] == $wpdb->posts) {
                            $type = 'POST';
                        }
                        switch ($type) {
                            case 'METADATA':
                                require (__DIR__."/af-content-list-form-metadata-container.php");
                                break;
                            case 'POST':
                                require (__DIR__."/af-content-list-form-post-container.php");
                                break;
                            default:
                                require (__DIR__."/af-content-list-form-classic-container.php");
                                break;
                        }
                    } else { 
                        ?>
                        <div class="dbp-lf-container-table js-lf-container-table">
                            <div class="js-dbp-lf-box-table-info">
                                <div class="dbp-lf-table-title"> <?php echo (isset($table['table_name'])) ? $table['table_name']." (".$key.")" : $key; ?>
                                <div class="dbp-alert-warning"><?php _e('The table does not have a valid primary key', 'admin_form'); ?></div>
                                <p><?php _e('The primary key must be an autoincrement numeric column.', 'admin_form'); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    } 
                } 

                // ADD METADATA BUTTON
                if (isset($post->post_content['sql_metadata_table'])) {
                    $key = uniqid();
                    $info_metadata = explode("::", $post->post_content['sql_metadata_table']);
                    $metatable_info = (is_array($info_metadata) && count($info_metadata) > 1) ? ADFO_class_metadata::find_metadata_table_structure($info_metadata[1]) : [];
                    if (count($metatable_info) == 4) {
                        if (count($metatable_info) == 4) {
                            // TODO value_key MANCA lo inserisco in fase di visualizzazione della form
                            $options = ['type'=>'METADATA','orgtable'=>$info_metadata[1],'table'=>'', 'field_show'=>$metatable_info['meta_value'],'field_key'=>$metatable_info['meta_key'], 'value_key'=>'','field_conn_id'=>$metatable_info['parent_id'], 'value_conn_id' => $info_metadata[0]];
                            $table_options = new DbpDs_table_param();
                            $field = new ADFO_field_param();
                            // $metatable_info['meta_value']
                                $field->set_from_array([
                                'name'=>$metatable_info['meta_value'],
                                'orgtable' => $info_metadata[1],
                                'label' => 'new field'
                            ]);
                            
                            $table = ['fields' => [$field], 'table_name' => $info_metadata[1]];
                            $table_options ->table_options = maybe_serialize($options);
                            ?><div id="add_master_metadata" style="display:none"><?php
                                require (__DIR__."/af-content-list-form-metadata-container.php");
                            ?>
                            </div>
                            <?php 
                        }
                        ?>
                        <div class="adfo-new-field-box"  id="add_metadata_btn">
                            <div class="button " onclick="clone_metadata_fields('<?php echo $count_fields; ?>','<?php echo $key; ?>')"><span class="dashicons dashicons-plus-alt"></span> <?php _e('New Field (add metadata)', 'admin_form'); ?></div>
                        </div>
                        
                        <?php 
                    }
                }
                ?>
            </div>

            <?php if (is_countable($tables) && count($tables) > 0) : ?>
                <div class="dbp-submit" onclick="dbp_submit_list_form();"><?php _e('Save','admin_form'); ?></div>
            <?php else : ?>
                <div class="dbp-alert-sql-error"><?php _e('Something is wrong, check the query if it is correct', 'admin_form'); ?></div>
            <?php endif; ?>
        </div>
    </form><br>
</div>