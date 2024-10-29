<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> TEST <span class="dashicons dashicons-arrow-right-alt2"></span>ADMIN FORM</h2>
    <p>tA small example on the data structure of admin form. If you're not trying to do very complicated things you shouldn't need this page. I wrote this page mainly for myself while I program the plugin.est CODE form</p>
    <p>I take the last list created</p>
    <pre class="code">ADFO::get_lists_names(); 
$id = end($temp);
$last_name = array_pop($lists); </pre>
    <?php 
    $lists = ADFO::get_lists_names(); 
    if (empty($lists)) {
        echo "No list found!. Create a list first to see the structure of the data.";
        return;
    }
    // get last key
    ksort($lists);
    $temp = array_keys($lists);
    $id = end($temp);
    $last_name = array_pop($lists);
    $form = new ADFO_class_form($id);
    list($settings, $table_options) = $form->get_form();
    ?>
    <h1><?php echo $last_name; ?></h1>
    <p>I extract the form data</p>
    <pre class="code"> $form = new ADFO_class_form($id);
list($settings, $table_options) = $form->get_form();</pre>

<pre class="code"><?php var_dump ($form->data_structures_to_array($settings)); ?></pre>

</div>