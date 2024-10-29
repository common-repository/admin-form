<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Galleries','admin_form'); ?></h2>
    <p>In this tutorial we will see how to create image gallery. The images will be managed through the wordpress Media Library. <br> The structure will consist of two tables. The first table will have the list of image galleries will have title and description. <br> The second table will have the list of images for each gallery and the columns will be: image, title and the reference to the gallery to which they belong.</p>

    <h2 class="dbp-h2">Let's start by creating a new list</h2>

    <p> Click on the ADMIN FORM plugin and then on the <b> "CREATE NEW FORM" </b> button. As a name we write <b> Gallery images </b> and leave "create a new Table". We then press the save button. </p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_01.png"  class="dbp-tutorial-img">

    <p> Once you have saved the list you will be redirected to the <b> TAB FORM </b>. <br>
     Click on the new field button to add a new column. <br>
     In the parameters we insert:
    <b>NEW FIELD NAME</b>: image_id<br>
    <b>Field Type</b>: Media gallery<br>
    <b>Required</b>: yes<br>
    </p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_02.png"  class="dbp-tutorial-img">

    <h2>Frontend</h2>
<p> Let's copy the shortcode for viewing the gallery. This can be found at the top right on the configuration pages or by returning to the main plugin page in the shortcode column.
     Let's copy the shortcode and associate it with an article. </p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_05.png"  class="dbp-tutorial-img">
 
    <p> The visualization is in table format, now let's turn it into an image gallery </p>
     <p> Let's go back to administration, inside the plugin in the <b> TAB FRONTEND </b>.<br>
     On <b> List type </b> we select <b> custom </b> instead of table </p>
     <p> On the <b> Header </b> section we write: </p>
    <pre class="dbp-code">&lt;div class=&quot;dbp-gallery-columns&quot;&gt;</pre>
    <p>On <b>Loop the data</b> section we write:</p>
    <div class="dbp-code">&lt;a href=&quot;[^LINK_DETAIL]&quot; class=&quot;js-dbp-popup&quot;&gt;[^IMAGE.image id=[%data.image_id val] image_size=medium]&lt;/a&gt;</div>
<p> Please note that we will use the built-in detail system to display the popup with the image. To call the popup, add the js-dbp-popup class to the link and as a link we just need to call the template engine function [^LINK_DETAIL]. </p>

<p> On the <b> Footer </b> we just need to close the div we opened to sort the images </p>
<div class ="dbp-code"> &lt;/div&gt; </div>
<p> On <b> Detail view </b> We can set various types of popups on popup_style, base, large or fit. Fit allows us to have a window that will adapt to the size of the content. We choose on <b> Popup type FIT </b> </p>
<p> On <b> View type </b> we choose <b> CUSTOM </b> and inside the editor we recall the large image asking for dimensions that fit the window (winfit). The documentation describes the various image_size options. Note that we cannot use the image column which natively would have contained the image id because in the list view setting we have set image as media gallery so the column returns the thumbnail in html and not the id of the image gallery. </p>
<div class = "dbp-code">[^IMAGE.image id=[%data.image_id] image_size=winfit] </div>

<p>Finally, inside the css of your template you need to add:
<pre class="dbp-code">.dbp-gallery-columns { display: grid; grid-template-columns: 1fr 1fr 1fr; align-items: center; justify-items: center; grid-gap: 2px; line-height: 0;}
.dbp-gallery-columns > a { border: 1px solid; line-height: 0; padding:.5rem; height: 100%; box-sizing: border-box; display: flex; align-items: center;}</pre>
</p>
<p> The final result will show a series of clickable images similar to the image below. </p>
<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_06.png"  class="dbp-tutorial-img">

</div>