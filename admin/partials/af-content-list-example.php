<?php

/**
 * La pagina con gli esempi di codice per renderizzare la lista
 */
namespace admin_form;
if (!defined('WPINC')) die;

$data = ADFO::get_data($post->ID);
if (count($data) == 0) {
    echo '<div class="af-content-header">';
    require(dirname(__FILE__) . '/af-partial-tabs.php');
    echo '</div>';
    echo '<div class="af-content-table js-id-dbp-content">';
    echo '<div class="af-content-margin">';
    echo '<p class="info-page-example">' . __('Please insert some data first', 'admin_form') . '</p>';
    echo '</div>';
    echo '</div>';
    return;
}
$row = array_shift($data);
$id_values = ADFO::get_ids_value_from_list($post->ID, $row);
$array_id_values = ADFO::get_ids_value_from_list($post->ID, $row, 'array');
// creo un array con tutte le proprietà dell'oggetto $row
$first_column = key(get_object_vars($row));

$detail = ADFO::get_detail($post->ID, $id_values);

//var_dump($detail);
?>
<div class="af-content-header">
    <?php require(dirname(__FILE__) . '/af-partial-tabs.php'); ?>
</div>

<div class="af-content-table js-id-dbp-content">
    <div style="float:right; margin:1rem">
            <?php _e('Shortcode: ', 'admin_form'); ?>
            <b>[adfo_list id=<?php echo wp_kses_post($post->ID); ?>]</b>
    </div>
    <?php ADFO_fn::echo_html_title_box($list_title, ''); ?>


    <div class="af-content-margin">

        <h3 class="dbp-h3 dbp-css-mb-0"><?php _e('Render the list', 'admin_form'); ?></h3>
        <div class="dbp-grid-3-columns">
            <div>
                <p class="info-page-example"><?php _e('Shortcode (Wordpress)', 'admin_form'); ?></p>
                <code class="dbp-code-page-example">[adfo_list id=<?php echo wp_kses_post($post->ID); ?>]</code>
            </div>
            <div>
                <p class="info-page-example"><?php _e('PHP', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span> </a></p>
                <code class="dbp-code-page-example"><?php echo htmlentities("<?php echo ADFO::get_list(". wp_kses_post($post->ID)."); ?>"); ?></code> 
            </div>
            <div>
                <p class="info-page-example"><?php _e('Template engine', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span></a></p>
                <code class="dbp-code-page-example">[^GET_LIST id=<?php echo wp_kses_post($post->ID); ?>]</code>
            </div>
        </div>
        <p class="info-page-example"><?php _e('Result: (some iterations or codes may not work in the example)', 'admin_form'); ?></p>
        <div class="dbp-page-example-result">
            <?php echo ADFO::get_list($post->ID); ?>
        </div>
        <?php /*
        <div class="dbp-page-example-result">
            <?php echo PinaCode::execute_shortcode('[^GET_LIST id='.$post->ID.']');?>
        </div>
        */ ?>


        <h3 class="dbp-h3 dbp-css-mb-0"><?php _e('Render Single Page', 'admin_form'); ?></h3>
        <p>You can change the view from the frontend in the Detailed View section.</p>
        <div class="dbp-grid-3-columns">
            <div>
                <p class="info-page-example"><?php _e('Shortcode (Wordpress)', 'admin_form'); ?></p>
                <code class="dbp-code-page-example">[adfo_single dbp_id=<?php echo wp_kses_post($post->ID); ?> id={id}]</code>
            </div>
            <div>
            <p class="info-page-example"><?php _e('PHP', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span> </a></p>
                <code class="dbp-code-page-example"><?php echo htmlentities("<?php echo ADFO::get_single(". wp_kses_post($post->ID)."); ?>"); ?></code> 
            </div>
            <div>
                <p class="info-page-example"><?php _e('Template engine', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span></a></p>
                <code class="dbp-code-page-example">[^GET_SINGLE dbp_id=<?php echo wp_kses_post($post->ID); ?> id={ID}]</code>
            </div>
        </div>
        <p class="info-page-example"><?php _e('Result: (some iterations or codes may not work in the example)', 'admin_form'); ?></p>
        <div class="dbp-page-example-result">
            <?php echo ADFO::get_single($post->ID, $id_values); ?>
        </div>
        <?php /*
        <div class="dbp-page-example-result">
        <pre> <?php echo (PinaCode::execute_shortcode('[^GET_SINGLE dpb_id='.$post->ID.' id='.$id_values.']'));?></pre>
        </div>
        <div class="dbp-page-example-result">
        <pre><?php  echo (do_shortcode('[adfo_single dbp_id='.$post->ID.' id='.$id_values.']'));?></pre>
        </div>
        */ ?>

        <h3 class="dbp-h3 dbp-css-mb-0"><?php _e('Get Total', 'admin_form'); ?></h3>
        <div class="dbp-grid-3-columns">
            <div>
                <p class="info-page-example"><?php _e('Shortcode (Wordpress)', 'admin_form'); ?></p>
                <code class="dbp-code-page-example">[adfo_total id=<?php echo wp_kses_post($post->ID); ?>]</code>
            </div>
            <div>
            <p class="info-page-example"><?php _e('PHP', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span> </a></p>
                <code class="dbp-code-page-example"><?php echo htmlentities("<?php echo ADFO::get_total((". wp_kses_post($post->ID)."); ?>"); ?></code> 
            </div>
            <div>
                <p class="info-page-example"><?php _e('Template engine', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span></a></p>
                <code class="dbp-code-page-example">[^get_total id=<?php echo wp_kses_post($post->ID); ?>]</code>
            </div>
        </div>
        <p>Result</p>
        <div class="dbp-page-example-result">
            <?php echo ADFO::get_total($post->ID, true); ?>
        </div>
        <?php /*
        <div class="dbp-page-example-result">
        <pre><?php var_dump(do_shortcode('[adfo_total id='.$post->ID.']')); ?></pre>
        </div>
        <div class="dbp-page-example-result">
            <?php echo PinaCode::execute_shortcode('[^GET_LIST id='.$post->ID.']');?>
        </div>
        */ ?>

        <h3 class="dbp-h3 dbp-css-mb-0"><?php _e('Returns the array with the list data', 'admin_form'); ?></h3>
        <div class="dbp-grid-2-columns">
            <div>
            <p class="info-page-example"><?php _e('PHP', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span> </a></p>
                <code class="dbp-code-page-example"><?php echo htmlentities("<?php echo ADFO::get_data(". wp_kses_post($post->ID)."); ?>"); ?></code> 
            </div>
            <div>
                <p class="info-page-example"><?php _e('Template engine', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span></a></p>
                <code class="dbp-code-page-example">[^GET_LIST_DATA  id=<?php echo wp_kses_post($post->ID); ?>]</code>
            </div>
        </div>
        <p>Result</p>
        <div class="dbp-page-example-result">
            <pre><?php var_dump(ADFO::get_data($post->ID)); ?></pre>
        </div>
        <?php /*
        <div class="dbp-page-example-result">
        <pre><?php var_dump(PinaCode::execute_shortcode('[^GET_LIST_DATA id='.$post->ID.']')); ?></pre>
        </div>
        */ ?>

        <h3 class="dbp-h3 dbp-css-mb-0"><?php _e('Returns the detail of a single record', 'admin_form'); ?></h3>
        <div class="dbp-grid-2-columns">
            <div>
            <p class="info-page-example"><?php _e('PHP', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span> </a></p>
                <code class="dbp-code-page-example"><?php echo htmlentities("<?php var_dump( ADFO::get_detail($post->ID, {id})); ?>"); ?></code> 
            </div>
            <div>
                <p class="info-page-example"><?php _e('Template engine', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span></a></p>
                <code class="dbp-code-page-example">[^GET_DETAIL  dbp_id=<?php echo wp_kses_post($post->ID); ?> id={id}]</code>
            </div>
        </div>
        <p>Result</p>
        <div class="dbp-page-example-result">
            <?php $result_detail = ADFO::get_detail($post->ID,  $id_values); ?>
            <pre><?php var_dump($result_detail); ?></pre>
        </div>
        <?php /* 
        <div class="dbp-page-example-result">
        <pre> <?php var_dump(PinaCode::execute_shortcode('[^GET_DETAIL dpb_id='.$post->ID.' id='.$id_values.']'));?></pre>
        </div>
        */ ?>
        <?php if ($result_detail == false) : ?>
            <div class="dbp-alert-warning"><?php _e("Detail does not return elements, probably the query has problems with table ids.", 'admin_form'); ?> </div>
        <?php else: ?>
            <h3 class="dbp-h3 dbp-css-mb-0"><?php _e('Returns a field of a single record', 'admin_form'); ?></h3>
            <div class="dbp-grid-2-columns">
                <div>
                    <p class="info-page-example"><?php _e('PHP', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span> </a></p>
                    <code class="dbp-code-page-example"><?php echo htmlentities("<?php ADFO::get_detail($post->ID, {id})->$first_column; ?>"); ?></code> 
                </div>
                <div>
                    <p class="info-page-example"><?php _e('Template engine', 'admin_form'); ?> <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" target="_blank"><span class="dashicons dashicons-external adfo-dashicons-page-example "></span></a></p>
                    <code class="dbp-code-page-example">[^GET_DETAIL.<?php echo $first_column; ?> dbp_id=<?php echo wp_kses_post($post->ID); ?> id={id}]</code>
                </div>
            </div>
            <p>Result</p>
            <div class="dbp-page-example-result">
                <pre><?php echo ADFO::get_detail($post->ID,  $id_values)->$first_column; ?></pre>
            </div>
            <?php /*
            <div class="dbp-page-example-result">
            <pre> <?php var_dump(PinaCode::execute_shortcode('[^GET_DETAIL.'.$first_column.' dpb_id='.$post->ID.' id='.$id_values.']'));?></pre>
            </div>
            */ ?>
        <?php endif; ?>

    </div>
   
</div>