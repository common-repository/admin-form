<?php 
namespace admin_form;
if (!defined('WPINC')) die;

function dbp_partial_tabs() {
    $current_page = 'browse';
    $page = ADFO_fn::get_request('page', '');
    $section = ADFO_fn::get_request('section', 'home');
    $base_link = admin_url("admin.php?page=".$page); 

    $var_name = 'dbp_id';
    $dbp_id  = ADFO_fn::get_request('dbp_id', '', 'int');

    // table diventa l'id del post della lista
    if ($dbp_id == "" || $section == 'list-all') {
        $array_tabs = ['list-all' => 'ADMIN FORM'];
        $array_icons = ['list-all' => '<span class="dashicons dashicons-admin-site-alt3"></span>'];
    } else {
        $array_tabs = ['list-browse' => 'Browse the list',  'list-form' => 'Form', 'list-sql-edit' => 'Settings', 'list-structure' => 'List view formatting', 'list-setting'=>'Frontend' , 'list-example'=>'code'];
        $array_icons = ['list-browse' => '<span class="dashicons dashicons-visibility"></span>', 'list-structure' => '<span class="dashicons dashicons-editor-table"></span>','list-sql-edit' => '<span class="dashicons dashicons-edit-page"></span>', 'list-setting'=> 
        '<span class="dashicons dashicons-admin-settings"></span>'
        ,'list-form' => '<span class="dashicons dashicons-welcome-write-blog"></span>', 'list-example' => '<span class="dashicons dashicons-editor-code"></span>' ];
    }

    ?>
    <div class="dbp-tabs-container">
        <?php foreach ($array_tabs as $key=>$value) : ?>
            <?php
            $action  = "";
            if (strpos($key, "|") != "") {
                $temp = explode("|", $key);
                $key = array_shift($temp);
                $action =  array_shift($temp);
            }
            if ( $$var_name != "" ) {
                $link = add_query_arg(['section' => $key, $var_name => $$var_name ], $base_link);
            } else {
                if ($key == "table-browse") continue;
                $link = add_query_arg(['section'=>$key ], $base_link);
            }
            if ($action != "") {
                $link = add_query_arg(['action' => $action], $link);
            } 
            if ($section == $key) : ?>
                <a href="<?php echo esc_url($link); ?>" class="dbp-tab dbp-tab-active">
                    <?php echo $array_icons[$key]; ?>
                    <?php _e($value, 'admin_form'); ?>
                </a>
            <?php else :?>
                <a href="<?php echo esc_url($link); ?>" class="dbp-tab">
                    <?php echo $array_icons[$key]; ?>
                    <?php _e($value, 'admin_form'); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php do_action('adfo_partial_list_add_tabs'); ?>
        <?php if (!defined('ADFO_PRO_VERSION')) : ?>
            <div style="margin-left: auto; padding:.3rem .5rem;">
                <a href="https://github.com/giuliopanda/admin-form-pro/releases" target="_blank">Download version PRO, It's FREE!</a>
            </div>
        <?php endif; ?>
    </div>
<?php 
}

dbp_partial_tabs();