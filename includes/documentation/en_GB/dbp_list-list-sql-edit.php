<?php
/**
 * header-type:doc
 * header-title: List Tab Settings
* header-tags:
* header-description: Define the query to be executed, who can modify the list, etc.
* header-package-title: Manage List
* header-package-link: manage-list.php
*/
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-margin">
    <div id="dbp_help_admin_sidebar_menu" class="dbp_help_div">
        <h3>Admin sidebar menu</h3>
        Add a menu item in your administration and decide who can access. <br> If you don't see the settings change immediately, reload the page.
    </div>

    <div id="dbp_help_admin_sidebar_menu_icon" class="dbp_help_div">
        <h4>Add Icon</h4> 
        <p>You can choose an icon from those on this page: <a href="https://developer.wordpress.org/resource/dashicons" target="_blank" class="js-simple-link">developer.wordpress.org/resource/dashicons</a> Clicca sull'icona che vuoi inserire e premi copia HTML. Nell'alert che apparirà prendi la seconda classe. es: <i>dashicons-image-rotate-right</i>. Copia la classe nel campo. </p>
    </div>

    <div id="dbp_help_admin_sidebar_menu_position" class="dbp_help_div">
        <h4>Position (number)</h4> 
        <p>Choose at what height to show the menu item.</p>
    </div>

    <div id="dbp_help_admin_sidebar_menu_permissions" class="dbp_help_div">
        <h4>Permissions</h4> 
        <p>Choose who can edit the list. Among the permissions there is also the administrator. You will always be able to view and edit the list within the plugin, but you can choose not to show it in the menu. </p>
    </div>

    <div id="dbp_help_admin_sidebar_metadata" class="dbp_help_div">
        <h4>Metadata</h4> 
        <p>Metadata is additional data linked through tables with the 'meta' suffix. ADMIN FORM is able to manage these tables if they respect the following structure:
        <ul>
            <li>- Table name must be main table name + 'meta' or '_meta'</li>
            <li>- The table must contain 4 fields: the primary key, a connection field with the main table, a field called 'meta_key' and the last field called 'meta_value'</li>
        </ul>
        </p>
        <p>Example: wp_postmeta: meta_id,post_id,meta_key,meta_value</p>
    </div>                  
                 
    <div id="dbp_help_admin_query" class="dbp_help_div">
        <h3>Query</h3>
        <p> You can choose to extract all data from a table, or filter it by adding WHERE clauses. You can extract only some fields or add calculated fields. Some more complex queries may not work correctly. If you want to link other tables use the LEFT JOIN ... ON clause. </p>
    </div>

    <div id="dbp_help_admin_filter" class="dbp_help_div">
        <h3>Filter</h3>
        <p> Adds a filter when the list receives a certain parameter. </p>
        <p> Parameters are written as shortcodes and are: </p>
        
        <ul>
            <li><b>[%params.xxx]</b> are the parameters added in the shortcodes</li>
            <li><b>[%request.xxx]</b> for the data received in the url</li>
        </ul> 
        <p>You can also use all the functions of the template engine such as [^ current_post] to get the data of the post you are viewing ([^ current_post.id] for the id).</p>
        <p>If required is selected but the value is not passed, the query returns no results. If required is not selected and no parameters are passed, the query returns the unfiltered results</p>
    </div>

    <div id="dbp_help_admin_permission" class="dbp_help_div">
        <h3>Permission</h3>
        <p>The permissions section appears only when a column is set in the form tab as the author of the record. Once this column is activated, it will be possible to allow you to select which roles can only see and edit your own items. If you also activate the status (publish, draft) column also in the FORM tab, then you will also be able to manage a role for contributors. These can only see and edit their own content, but cannot publish it.</p>
    </div>

    <div id="dbp_help_delete_options" class="dbp_help_div">
        <h3>Delete options (PRO)</h3>
        <p>If there is only one table, choose whether or not records can be deleted from the list. <br> If the query extracts multiple tables, choose which ones should be removed when deleting a file. <br> If you select no to all tables, then the records cannot be removed in the list.</p>
    </div>
    
</div>