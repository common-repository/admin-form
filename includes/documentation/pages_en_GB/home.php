<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-table dbp-docs-content  js-id-dbp-content" >
    <h2><?php _e('ADMIN FORM document','admin_form'); ?></h2>
    <div class="dbp-help-p">
         Admin Form allows you to create data collections by connecting directly to your wordpress database.

         When you create a new form you can choose whether to link it to a table or create a new one. <br>
         As soon as a new form is created, a menu with various tabs will appear.

         <h3>FORM</h3>
         <p>Shows how the table fields will be modified.<br>If the table was created with the plugin, you can press the "new field" button to create new database fields. <br>By clicking on the pencil you can change how the individual fields will be edited.</p>

         <h3>SETTINGS</h3>
         <p>Show module settings: What data should be extracted, who can edit it, how much data per page etc...</p>

         <h3>LIST VIEW FORMATTING</h3>
         <p>How the data is displayed in the data list and thus also in the frontend</p>

         <h3>FRONTEND</h3>
         <p>How data is displayed in the frontend.</br>
         A shortcode for displaying the frontend is generated from a list.</p>

     </div>
    
    <h3>Shortcodes</h3>
    <p>To print the graphics of a list you can use the shortcode:</p>
    <p><b>[adfo_list id=list_id]</b> where id is the id of the list. If you want to display more lists within the same page that derive from the same list you can set the prefix attribute with a unique short code like prefix = "abc".
    If in the tab setting you have set filters [%params.xxx] you can pass them in the list to further filter the results. Example:<br>
    [adfo_list id=list_id xxx=23]
    </p>
    <p><b>[adfo_tmpl]</b> To run the custom template engine</p>

    <p><b>[adfo_single dbp_id=xxx id=xxx]</b> Show the detail of a single record</p>

    <p><b>[adfo_total id=xxx]</b> Return the total of records a single list</p>

    <h3>Template Engine</h3>
        <div class="dbp-help-p">You can modify the data you see through an integrated template engine <br>
        The integrated template engine can be used both to modify table data such as calculated fields, and to generate custom templates in lists. <br> It is possible to use the functions of the template engine also outside the plugin by inserting the code between shortcodes<b>[adfo_tmpl] {my code} [/adfo_tmpl]</b>
        <br><br>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>">Learn more</a>
    </div>

    <h3>Form Javascript</h3>
        <div class="dbp-help-p">In the management of insertion forms you can use javascript to manage special actions in fields such as making a field appear or disappear or validate its content.<br><br>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=js-controller-form") ?>">Approfondisci</a>
    </div>

    <h3>Hooks & filters</h3>
    <div class="dbp-help-p">Change plugin behavior directly from code.<br><br>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=hooks") ?>">Learn more</a>
    </div>

    <h3>PHP</h3>
    <div class="dbp-help-p">Develop your site using the plugin functions directly. <br>
        The <b>admin_form\ADFO</b> class contains three main functions for data extraction:
            <ul>
                <li>admin_form\ADFO::get_list(list_id); Show front view</li>
                <li>$data = admin_form\ADFO::get_data(list_id); Returns the array of data from a list</li>
                <li>$row = admin_form\ADFO::get_detail(list_id, $record_id) Returns a given record in raw format. This format is needed because the result can be edited and saved using the function admin_form\ADFO::save_data(...)</li>
            </ul>
        <br>
        
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>">Learn more</a>
    </div>

    <h3>Tutorials</h3>
    <div class="dbp-help-p">
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_01") ?>">Related post</a><br>
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_02") ?>">Galleries</a>
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_03") ?>">New post type and metadata</a>
</div>
</div>