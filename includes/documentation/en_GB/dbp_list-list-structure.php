<?php
/**
 * header-type:doc
 * header-title: Edit the extracted data
 * header-tags: edit columns, column
 * header-description: Once you have saved a query, you can change the view of the data from the List view formatting tab
*/
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-margin">
    <p> the list presents all the columns that are extracted from the table. You can change the display order, choose to hide a column or change how the data is displayed. </p>
     <p> You can add new columns by working the extracted data through the template engine, but if you want to extract more data you will need to modify the extraction query </p>

    <div id="dbp_help_title" class="dbp_help_div">
        <h4>Table title</h4>
        <p> The title that the column will have does not affect the data or the names of the data extracted from the template engine </p>
    </div>
    <div id="dbp_help_searchable" class="dbp_help_div">
        <h4>Searchable</h4>
        <p> When you use the search field it will search all columns for who a search type has been chosen. LIKE means that it searches within the text while = will only search for columns that match the searched text. </p>
    </div>
    <div id="dbp_help_print" class="dbp_help_div">
        <h3>Column type</h3>
        <p><b>Text</b>: Print the contents of the field. It doesn't render the html.</p>
        <p><b>Html</b>: Print the column and it render the html</p>
        <p><b>Date</b>: Print the date without the time</p>
        <p><b>DateTime</b>: Print date with time</p>
        <p><b>Image</b>: Print the thumbs of the image</p>
        <p><b>External link</b>: makes a link clickable. Attributes: Link text: Show alternative text. You can use the [%data.field_name] variables to show the text of another field.</p>
        <p><b>Detail Link</b>: Makes the text clickable. Opens a popup with the details of the record. You can configure the details in the detailed view of the frontend.</p>
        <p><b>Serialize</b>: show a field with serialized data</p>
        <p><b>Show checkbox values ​​(Json label)</b>: Show the values ​​of selected checkboxes.</p>
        <p><b>Custom</b>: Use shortcodes to display column content. Click "show shortcode variables" to view the list of variables.</p>
        <p id="dbp_help_user" class="dbp_help_div"><b>User</b>: show the username starting from the ID.  You can also choose whether to create the user linkable to the author page by selecting the checkbox link to author page.
        </p>
        <p id="dbp_help_post" class="dbp_help_div"><b>Post</b>: show the title of a post starting from the ID. You can also choose whether to create the title linkable to the article by selecting the checkbox link to article page.
</p>
        <p><b>Lookup [PRO]</b>: If the id of another table is saved in the field, you can show the data of the related table through this type of field. Choose the table, then select the fields you want to show. The show attribute allows you to show one or more fields of the linked table (hold down ctrl while clicking on the fields you want to show). Created a search field, save the settings. At this point, the selected columns will appear as new fields that you can configure to your liking.</p>

        <h3>Edit fields</h3>
        <p>The edit fields allow you to modify the value of the field directly from the list.</p>

        <p><b>Order</b>: Allows you to sort a field by transcending. To activate it, the query must be sorted in ascending order by the column you want to sort.. </p>

        <p><b>Input</b>: Allows you to modify the value of the field directly from the list. </p>

        <p><b>Checkbox</b>: When the checkbox is selected, the database field is modified with the value entered in the Checkbox value field.</p>

        <p><b>Select</b>: Allows you to modify the field with the values ​​entered in the Select values field. </p>

    </div>
 
    <div id="dbp_help_format" class="dbp_help_div">
        <h3>column formatting</h3>
        <h4>change values</h4>
        <p> Change the content value according to the entered csv </p>
         <p> The csv values must be separated by commas. The first value is that of the column, the second is how it should be transformed </p>
         <p> You can use the special scripts <b> &lt;x, &gt;x, OR =x-y </b> for a range, where x and y are numbers. </p>
         example:
        <pre class="dbp-code">
    0, NO
    1, YES
    >1, MAYBE
        </pre>
    </div>
    <div id="dbp_help_styles" class="dbp_help_div">
        <h4>change styles</h4>
        <p> Adds a conditional class depending on the value of the csv inserted </p>
         <p> You can use the special writes <b> &lt;x, &gt;x, OR =x-y </b> for a range, where x and y are numbers. <br>
         here is the list of classes already configured:
            <ul>
                <li>dbp-cell-red</li>
                <li>dbp-cell-yellow </li>
                <li>dbp-cell-green</li>
                <li>dbp-cell-blue</li>
                <li>dbp-cell-dark-red</li>
                <li>dbp-cell-dark-yellow </li>
                <li>dbp-cell-dark-green </li>
                <li>dbp-cell-dark-blue</li>
                <li>dbp-cell-text-red </li>
                <li>dbp-cell-text-yellow </li>
                <li>dbp-cell-text-green</li>
                <li>dbp-cell-text-blue</li> 
            </ul>
        </p>
        example: 
        <pre class="dbp-code">
    0, dbp-cell-red
    =1-10, dbp-cell-green
        </pre>
    </div>
</div>