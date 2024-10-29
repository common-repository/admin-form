<?php
/**
 * header-type:doc
 * header-title: Data entry form 
* header-tags: form, field, lookup, text, textarea, select, checkbox, checkboxes, table attributes
* header-description: Data Entry Form is a form that helps to enter the data. Here you can manage how the data should be entered into the tables
* header-lang:ENG
*/
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-margin">
    <p>Manage data entry forms</p>
    <div id="dbp_help_attrs" class="dbp_help_div">
        <h4>Table attributes</h4>
        <p>
            Each insertion module is composed of one or more blocks that identify the tables from which the data is extracted. The title shows the table and the alias used in the query. <br>
             For each table, by clicking on show attributes, it is possible to modify some display parameters
        </p>
    </div>

    <div id="dbp_help_toggle" class="dbp_help_div">
        <h4>Show/Hide</h4>
        <p>Choose whether the field should be used in the data entry form</p>
    </div>


    <div id="dbp_help_type" class="dbp_help_div">
        <h3>Field Type</h3>
        <p><b>Text (Single line)</b>:A single line of text. the "Autocomplete" check activates the suggestions by proposing the other text fields already inserted.</p>
        <p><b>Text (Multiline)</b>textarea. unformatted.</p>
        <p><b>Date</b> Date formatted according to the wordpress configuration. <?php echo get_option( 'date_format' ); ?></p>
        <p><b>DateTime</b> Date with time, formatted according to your wordpress configuration. <?php echo get_option( 'time_format' ); ?></p>
        <p><b>Number</b>A single integer (negative or positive). To limit it see javascript functions.</p>
        <p><b>Decimal</b> Number with two numbers after the decimal point.</p>
        <p><b>Multiply Choice - Drop-down list</b> Single response select. The answers are entered in the choices field. One row for each choice. value,label<</p>
        <p><b>Multiply Choice </b> Radio buttons</p>
        <p><b>Checkbox (Single Answer)</b> Attention, remember to enter the value of the checkbox you want to save. </p>
        <p><b>Checkboxes</b> Checkboxes, Multiple Answers. The data is saved in a json.</p>
        <p><b>EMail</b> The field must contain the @ symbol.</p>
        <p><b>Link</b> The field must be a valid link.</p>

        <p><b>Primary value</b> It can only be assigned to fields that have a primary key. By default it is read only, but it can be changed.</p>

        <p><b>ReadOnly</b> The field is read-only and cannot be changed.</p>
        <p><b>Editor Code</b> Activate the code editor.</p>
        <p><b>Classic text editor</b> Activate the classic editor. Check the user setting.</p>
       
        <p><b>Record Creation Date</b> The creation date of the record is saved. The field is not overwritten during editing.</p>
        <p><b>Last update Date</b> The date the field was last modified is saved.</p>
        <p><b>Author</b> The id of the author of the record is saved. The field is not overwritten during editing. Only one author field can exist in a form. The author field activates the permissions section in the "Settings" tab. Through this option you can select the author role and the editor role. The author role can only see and edit its own records. If you activate the status field in the same form you can also manage the contributor role, i.e. users who can write and see only their records, but cannot publish them. </p>
        <p><b>Modifing user</b> The ID of the author of the last modification is saved.</p>
        <p><b>ORDER</b> The order of the record is saved. After setting a field order, this is pre-populated every time a new field is created with the highest value in the list. If you don't want it to be modified you can enter it as hide. Therefore it is advisable to change the sort order in Setting by setting the query order for the newly created field. Finally, in "List view formatting", check that the sorting column is set to order type. This way you can drag fields and sort them directly from the record list. </p>
        <p><b>Status (Publish, draft ...)</b> The status of the post is saved. Only one author field can exist in a form. You can choose from the following statuses: draft, publish, trash. The status "pending" is activated only if an author field also exists and used only for users with the contributor role. Only publish records are displayed in the frontend. The post status field behaves differently in the browse the list field or if the form menu item is selected.</p>
        <p id="dbp_help_lookup" class="dbp_help_div">
            <b>Lookup field [PRO]</b>
            Lookup fields are used to link data with other tables using primary key. <br>
             The "WHERE query field" is used to limit the data that will be displayed.
        </p>
        <p id="dbp_help_calc_field" class="dbp_help_div"><b>Calculated Field [PRO]</b>
                The calculated fields are filled in upon saving with the formula you entered into the formula. You can copy a field using the template engine variables [%data.variable_name] or create new fields by requesting data from posts or users eg [^POST.title id = [%data.post_id]. To calculate the number of the newly created row you can use [%row], while if you want to create a new identifier you can use [^COUNTER]</p>

        <p><b>Post</b> A post id is entered. you can filter the types of posts that can be inserted</p>
        <p><b>User</b> A user ID is entered.</p>
        <p><b>Media Gallery</b> The id referring to a file uploaded to the WordPress media gallery is entered.</p>
    </div>


    <div id="dbp_help_js" class="dbp_help_div">
        <h4>JS Script</h4>
        <p> Insert javascript to customize the insertion experience. </p>
         <p> Example: The field is valid only if it is between 0 and 100 </p>
        <pre class="dbp-code">field.valid_range(0,100);</pre>
        <p>Example: I show the field if the value of a hypothetical checkbox is equal to 1</p>
        <pre class="dbp-code">
 field.toggle(form.get('mycheckboxlabel').val() == 1);
        </pre>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=js-controller-form") ?>" target="blank" class="js-simple-link">Approfondisci</a>
    </div>
    <div id="dbp_help_default" class="dbp_help_div">
        <h4>Default Value</h4>
        <p>It is the value that is presented when a new record is inserted. You can insert template engine shortcodes.</p>
        <p> Example:</p>
        <pre class="dbp-code">[^user.id]
[^NOW]
[^request.xxx]</pre>
    </div>

    <div id="dbp_help_class" class="dbp_help_div">
        <h4>Custom css class</h4>
        <p> Add one or more css classes to the field. </p>
        <p> You can align two fields next to each other by adding the <b> dbp-form-columns-2 </b> class to the two fields.
        <p> For checkboxes and radios it is possible to paginate the options in multiple columns by adding one of the following custom css class: <br>
        dbp-form-cb-columns-2, dbp-form-cb-columns-3, dbp-form-cb-columns-4 </p>
    </div>

    <div id="dbp_help_lookup" class="dbp_help_div">
        <h4> Lookup field </h4>
        <p> Lookup fields are used to link data with other tables via primary key. it is important that the linked table has a single primary key. </p>
    </div>

    <div id="dbp_help_delete" class="dbp_help_div">
        <h4>Delete field</h4>
        <p> If the table is in DRAFT mode then from the form it is possible to modify its structure. </p>
         <p> Delete field removes the field and all data entered in that field </p>
         <p> If you don't want to remove the column or can't edit the table you can hide the field from the select show / hide </p>
    </div>
    <div id="dbp_help_new_field" class="dbp_help_div">
        <h4>New field</h4>
        <p> When you create a table it is put by default in DRAFT state. you can change the states of the tables with the PRO version.</p>
        <p> If the table is in DRAFT mode then it is possible to create a new field in the table. If you want to have more control in the creation of fields, you can go to the table structure and modify it. </p>
    </div>

    <div class="dbp_help_div">
        <h4>PHP Filter</h4>
        <p><a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=hooks") ?>" target="_blank" class="js-simple-link">apply_filters('dbp_save_data', $query_to_execute, $dbp_id, $origin)</a></p>
    </div>
    <br><br>
</div>
