<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Simple list with multiple post','admin_form'); ?></h2>
    <p>In this tutorial we will see how to create a simple list with a field listing a number of items.</p>

    <h2 class="dbp-h2">We start by creating a new list that we will call "article list"</h2>
    
    <p>We click on the ADMIN FORM plugin and then on the <b>"CREATE NEW FORM"</b> button. We write <b>Article list</b> as the name and click on "create a new Table".</p>

    <p>
        Once the list has been saved we will be redirected to the <b>FORM TAB</b> where we can add 2 fields:</b><br><br>
        field name: <b>title</b><br>
        field Type: <b>Text (single line)</b><br>
        field label: <b>Title</b><br>
        required<br>
        <br>
        field name: <b>Articles</b><br>
        field Type: <b>Post</b><br>
        is multiple: <b>Selezioniamo il checkbox "is multiple"</b><br>
        field label: <b>Articles</b><br><br>

        <b>Let's save the form</b>
    </p>

    <p>Now it is already possible to load a series of articles for each record.</p>

    <h2 class="dbp-h2">FRONTEND</h2>
    <p>Now Let's create a different view for the frontend where we add links to the articles</p>
    <p>Let's go to the FRONTEND tab and click on "list type" "<b>custom</b>".</p>
    <p>In the "In Loop" textarea we add the code:</p>
    
    <pre class="dbp-code">
&lt;h1&gt;[%data.title]&lt;/h1&gt;

[%data.Articles json tmpl=[:
    &lt;div class="dbp-item-post"&gt;
    [^POST.title_link id=[%item]]
    &lt;/div&gt;
:]]
    </pre>

    <br>
    <p>%data.Articles is the name of the field into which we insert multiple posts. These are saved in a text field in json format. [%data.Articles json] converts the json to an array. to cycle the array we use tmpl=[::] Inside the square brackets of tmpl we can use $item to find the individual cycled elements. So $item is the post id. At this point we load the post and print the link with the title.</p> 

    <h2 class="dbp-h2">PUBLICATION</h2>
    <p>At the top right or from the "code" tab you can copy the shortcode to view the list. After entering some test data, create a new page and insert the shortcode [adfo_list id=xxx].</p> 

</div>