<?php 
namespace DatabasePress;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Related post','admin_form'); ?></h2>
    <p>In this tutorial I will show you how to create a table that displays articles that have at least one tag in common with the article you are viewing. <br> First we need to create some articles that have the same tags. For example, you can create a series of articles composed as follows:</p>
    <table class="dbp-tutorial-table">
        <tr><td>Post title</td><td>Tag</td></tr>
        <tr><td>Venice</td><td>Italy</td></tr>
        <tr><td>Rome</td><td>Italy, Capital</td></tr>
        <tr><td>Milan</td><td>Italy</td></tr>
        <tr><td>Paris</td><td>France, Capital</td></tr>
        <tr><td>Marseille</td><td>France</td></tr>
    </table>

    <h2 class="dbp-h2">Creation of the list</h2>
    <p> Click on the ADMIN FORM plugin and then on the <b> "CREATE NEW FORM" button </b> In the name we write <b> Related Articles </b>. We choose 'Choose an existing table' and select wp_term_relationships </p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_01.png"  class="dbp-tutorial-img">
    <p> Once saved, a message will appear in the FORM tab informing us that there is no valid primary key for modifying the data. In this case we only want to view articles with similar tags and we are not working on a list that changes the data. </p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_02.png"  class="dbp-tutorial-img">
    <h2 class="dbp-h2">TAB SETTING</h2>
    <p> Let's go to the tab setting and in the <b>Admin sidebar menu section deselect Show in admin menu</b>. </p>
    <p> To allow the script to display only the articles that interest us we need to create two filters. We want all articles that have at least one tag equal to that of the article being viewed to be extracted. To do this we want tr.term_taxonomy_id to be equal to one of the ids of the tags (term) of the article being viewed. To get the id of the article you are viewing you can use the shortcode [^current_post.id] <br>
    To get the list of the tag ids of an article we can use the function [^get_post_tags]. So writing in the <b>first filter</b>: </p>
    <p> Select Column: <b>ter.term_taxonomy_id</b>, The filter type is <b>IN (Match in array)</b>, the value is:
    <pre class = "dbp-code">[^get_post_tags.term_id post_id=[^current_post.id]] </pre>.</p>
    <p> The second filter instead says that the id of the extracted articles must be different from the id of the article you are viewing. This is to avoid showing the same article you are viewing in related articles. </p>
    <p> Select  Column: <b>ter.object_id</b>, The filter type is <b>!= (Does NOT Equal)</b>, the value is:
    <pre class = "dbp-code">[^current_post.id]</pre>.</p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_03.png"  class="dbp-tutorial-img">
    <p>Save the settings</p>

    <h2 class="dbp-h2">PUBLISH</h2>
    <p>The shortcode to publish the list can be found at the top right.<br>
    You can publish the shortcode in the template so that it can be applied to all pages or to individual articles</p>
    <p>Otherwise you can use php to upload the list and publish it as you like. Here is an example of loading the list at the bottom of posts. Insert the code into the template inside function.php and <b>replace {list_id} with the id of the list you just created</b>.</p>
    <pre class="dbp-code">function add_dbp_list_to_content($content) {
	$append = '';
	if (is_single()) {
		$append = "&lt;h3&gt;TEST RELEATED POST&lt;/h3&gt;" . admin_form\ADFO::get_list( {list_id} );
	}
	return $content.$append;
}
add_filter('the_content', 'add_dbp_list_to_content');</pre>

    <p>If we try to publish the table within the articles something like this will appear:
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_04.png" class="dbp-tutorial-img">
    Object_id shows the post id, now we need to display the title.</p>

    <h2 class="dbp-h2">LIST view formatting</h2>
    <p>Let's open the edit of the first column (object_id).<br>
    Deselect "Keeps settings aligned with information from the same field in tab 'Form'.". This option allows you to automatically change the column settings depending on how you change the field in the Form tab.<br>
    As title we choose "Articles" while as type we choose <b>CUSTOM</b>.<br>
    At this point a textarea will open in which we will insert
    <pre class="dbp-code">&lt;a href="[^LINK id=[%data.object_id]]"&gt;[^post.title id=[%data.object_id]]&lt;/a&gt;</pre>
    In the following columns we select Hide so as not to make them appear. Always uncheck "keeps settings ..." first to be able to edit columns. <br>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_05.png" class="dbp-tutorial-img">
    </p>
    <p>Here's what the final result should look like

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_06.png" class="dbp-tutorial-img">
    </p>

    <p style="color:red">When writing in the ADMIN FORM built-in template engine Always remember that between attributes and value there must never be spaces! So [^get_post_tags.html post_id = [%data.object_id]] for example won't work because before and after the = symbol there are two spaces.</p>

</div>