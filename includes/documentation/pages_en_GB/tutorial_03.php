<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('New post type and metadata','admin_form'); ?></h2>
    <p>In this tutorial we will see how to create a new post type for a list of music albums. We will create two new fields: one for the album author, and one for the poster. </p>

    <h2 class="dbp-h2">We begin by creating a new list</h2>
    
    <p>Clicchiamo sul plugin ADMIN FORM e poi sul bottone <b>"CREATE NEW FORM"</b>. Come nome scriviamo <b>my Music</b> e clicchiamo su "Create a different types of content in your site (Post Type)".</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_01_new.png"  class="dbp-tutorial-img">
    <div style="float:left; width:48%; margin-right:1%">
        <p>Once the list is saved you will be redirected to the <b>TAB FORM</b> and add the metadata.<br>
        <p>We go to the bottom of the form and click on the button  <b>New field</b><br>
        We configure two new fields:<br>
        field name: <b>artist</b><br>
        field Type: <b>Text (single line)</b><br>
        field label: <b>Artist</b><br>
        required<br>
        <br>
        field name: <b>cover</b><br>
        field Type: <b>Media gallery</b><br>
        field label: <b>Cover</b><br><br><br>
        <b>Salviamo la form</b>
        </p>
    </div>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_03_new.png"  class="dbp-tutorial-img">
    <h2 class="dbp-h2">FRONTEND</h2>
    <br>
    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_06.png"  class="dbp-tutorial-img">
    </div>
    <div style="float:left; width:48%;">
        <p>Let's create a new wordpress page and insert the list shortcode that we find on the top right corner of each configuration page. </p>
    </div>

<p>Finally, we can edit the template by going to the Detailed view on the frontend tab.<br>
<a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_03_old") ?>">Look at the old tutorial of version 1.6</a></p>

</div>