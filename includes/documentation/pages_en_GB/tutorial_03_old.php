<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Post type and metadata V.1.6','admin_form'); ?></h2>
    <p><a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_03") ?>">Check out the new tutorial of version 1.7 +</a></p>

    <p>In this tutorial we will see how to create a new post type for a list of music albums. We're going to create three new fields for album author, release year, and poster. </p>

    <h2 class="dbp-h2">Let's start by creating a new list</h2>
    
    <p>Click on the ADMIN FORM plugin and then on the <b>"CREATE NEW FORM"</b> button. As name we write <b>Music</b> and click on "Choose an existing table". Select wp_post and click save.</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_01.png"  class="dbp-tutorial-img">
    <div style="float:left; width:48%; margin-right:1%">
        <p>Once the list has been saved you will be redirected to the <b>TAB FORM</b><br>
        <p>We order the fields by dragging them from the two triangles on the blue titles. We put ID, post_title, post_content, post_status and post_type in the first positions. We set all the other fields to HIDE.<br>Click on the pencil next to <b>post_title</b> and set the field type: <b>text (single line)</b>. We modify <b>post_content</b> and on the field type we choose <b>Classic text editor</b>.<br><br><u>On <b>post_status</b> we set the default value "<b >publish</b>" and select <b>HIDE</b> next to the title</u>.<br><u>On <b>post_type</b> set as default value "<b>music</b>" and select <b>HIDE</b> next to the title</u>.</p>
    </div>
    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_02.png"  class="dbp-tutorial-img">
    </div>
    <div style="clear:both;width:100%"><br></div>

    <h2 class="dbp-h2">Now let's add the metadata</h2>
     <div style="float:left; width:48%; margin-right:1%">
         <p>Go to the bottom of the form and click on the <b>ADD METADATA</b><br> button
         Let's configure two new fields:<br>
         fieldname: <b>artist</b><br>
         field Type: <b>Text (single line)</b><br>
         field label: <b>Artist</b><br>
         required<br>
         <br>
         fieldname: <b>cover</b><br>
         fieldType: <b>Mediagallery</b><br>
         field label: <b>Cover</b><br><br><br>
         <b>Save the form</b>
         </p>
    </div>

    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_03.png"  class="dbp-tutorial-img">
    </div>
    <div style="clear:both;width:100%"><br></div>

    <h2 class="dbp-h2">Tab setting</h2>
    <p>Now we have to tell our module that it should filter the data only for the post_type music.<br>Let's scroll down to the filters and choose pos.post_type as the field, = (Equals) as the operator and music as the value.</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_04.png"  class="dbp-tutorial-img">

    <p>Let's save.<br>
     <u>Warning the filters are not applied in the "Browse the list" tab!</u>. To test it, go to the "music" menu item that will appear under the Admin Form.</p>

    <p>This is the final result that should appear in the backend</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_05.png"  class="dbp-tutorial-img">

    <h2 class="dbp-h2">FRONTEND</h2>
    <br>
    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_06.png"  class="dbp-tutorial-img">
    </div>
    <div style="float:left; width:48%;">
        <p>Let's create a new wordpress page and insert the shortcode of the list that we find at the top right of each configuration page</p>
    </div>
    <div style="clear:both;width:100%"><br></div>
    <br>
    <div style="float:left; width:48%;">
        <p>Now let's go back to the plugin and on the <b>List view formatting</b> tab we modify the title to create the link to the single page of the post type. <br>
         Next to post_title click the pencil to edit the column and then deselect " Keeps settings aligned with information from the same field in tab 'Form'".<br>
         On <b>Column Type</b> I choose <b>Custom</b> and insert the following code:</p>
        <pre class="dbp-code">&lt;a href="[^LINK id=[%data.ID]]"&gt;[%data.post_title]&lt;/a&gt;</pre>
        <p>We save</p>
    </div>
    <div style="float:left; width:48%;">
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_07.png"  class="dbp-tutorial-img">
    </div>
    <div style="clear:both;width:100%"><br></div>

    <h2 class="dbp-h2">Let's modify the template</h2>
     <p>To make a new post type work, you must first register it, and then modify the content of the single page in order to view the added data. We can do everything inside the <b>function.php</b> file that we find inside the active template. At the bottom of the file we add </p>

    <pre class="dbp-code">// Register post_type music
function adfo_type_music() {
	$args = array(
	'public' => true,
	'query_var' => true,
	'rewrite' => array('slug' => 'music')
	);
	register_post_type('music', $args);
}
add_action('init', 'adfo_type_music');

// I add metadata to the content
function adfo_single_page_music ( $content ) {
    if ( is_single() && get_post_type() == "music") {
		$adfo_detail =  admin_form\ADFO::get_detail(8, get_the_ID(), false); 
        return $content . '&lt;p&gt;ARTIST: '.$adfo_detail->artist.'&lt;/p&gt;&lt;p&gt;Cover: '.$adfo_detail->cover.'&lt;/p&gt;';
    }
    return $content;
}
add_filter( 'the_content', 'adfo_single_page_music');</pre>

<p>Let's save the file. The last step that we must carry out in order to make the changes active we have to make wordpress regenerate the links. We can do it with a little trick. We go to administration in settings > permalinks and save the configuration without changing anything. This way wordpress will recalculate the site links and add our post type music.</p>

<p>We're done, now we can go to the music album list page. By clicking on the title you will go to the page with the detail of the article and at the bottom you will find the artist and the image of the album cover that you have entered.</p>

</div>