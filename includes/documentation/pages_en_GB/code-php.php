<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content" >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Doc</a><span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('PHP','admin_form'); ?></h2>

    <h1 style="border-bottom:1px solid #CCC">ADMIN FORM (ADFO) CLASS</h1>
    <p>To use the ADFO class write <b>admin_form\ADFO::function_name();</b> or
    <pre>use admin_form\ADFO as ADFO;
ADFO::function_name();</pre>
    </p>
    <hr>
    <div class="dbp-help-p">
       
    
    <h2 class="dbp-h2">ADFO::get_list($dbp_id, $only_table = false, $params=[], $prefix = "")</h2>
    <p>Load a list from an id and return the html. Pretty much the same thing that the shortcode does!</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The id of the list</li>
        <li><b>$ajax</b><br>
        (bool) Whether to print the filters and the form that groups the table or not.</li>
        <li><b>$params</b><br>  
        (Array) Any additional parameters to further filter the table [%params]
        <li><b>$prefix</b><br>  
        (String) A prefix for the names of the fields to be sent in the form to avoid collisions on multiple tables within the same page
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>Html</p>

    <h2 class="dbp-h2">ADFO::get_single($dbp_id, $ids)</h2>
    <p>Returns the html of a single record.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The id of the list</li>
        <li><b>$ids</b><br>
        (integer) record id.</li>
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>Html</p>



    <h2 class="dbp-h2">ADFO::get_total($dbp_id, $filter = false)</h2>
    <p>Load a list from an id and return the html defined in the frontend view</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The id of the list</li>
        <li><b>$filter</b><br>
        (bool) Whether to apply filters or not.</li>
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>Int |  -1 if he fails to count</p>


    <h2 class="dbp-h2">ADFO::get_lists_names()</h2>
    <p>Load all dbp lists</p>
    <h4 class="dbp-h4">Return</h4>
    <p>Array</p>

    <h2 class="dbp-h2">get_list_columns($dbp_id, $searchable = true, $extend = false)</h2>
    <p>Estrae l'elenco delle colonne di una lista.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The list id</li>
        <li><b>$searchable</b><br>
        (boolean) Extracts columns from tables only, excluding calculated columns.</li>
        <li><b>$extend</b><br>
        (boolean) If true it returns only the names of the columns, otherwise all the information available on the column.</li>
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>ADFO_list_setting[]|array</p>


    <h2 class="dbp-h2">ADFO::get_primaries_id($dbp_id)</h2>
    <p>Returns the list of primary keys of a list. The extracted fields are the aliases!</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The list id</li>
        
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>@return array [table=>primary_name, ...]</p>


    <h2 class="dbp-h2">ADFO::get_data($dbp_id, $return = "items", $add_where = null, $limit = null, $order_field = null, $order="ASC", $raw_data = false)</h2>
    <p>Return the data or the model of a list</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The id of the list</li>
        <li><b>$return</b><br>
        (string) items|schema|model|schema+items|raw</li>
        <li><b>$add_where</b><br>
        (array)  [[op:'', column:'', value:'' ], ... ] es: [['op'=>'=', 'column'=>'dbp_id', value=>1]]</li>
        <li><b>$limit</b><br>
        (integer) The maximum number of records extracted</li>
        <li><b>$order_field</b><br>
        (string) The column on which to sort the data</li>
        <li><b>$orderorder_field</b><br>
        (string)  ASC|DESC</li>
        <li><b>$raw_data</b><br>
        (boolean)</li>
    </ul>
    <p>The difference between $return = "raw" and $raw_data = true is that with $return = $raw all the data from the tables concerned are extracted, while with $raw_data = true the raw data is always extracted, but only from columns extracted from the query.</p>
    <h4 class="dbp-h4">Return</h4>
    <p>Mixed</p>
    

    <h2 class="dbp-h2">ADFO::get_detail($dbp_id, $dbp_ids, $raw_data = true)</h2>
    <p>Returns the data of a single record unchanged from the list settings. This data can be passed to save_data after being modified to be saved.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The id of the list</li>
        <li><b>$dbp_ids</b><br>
        (array|int|string) $dbp_ids [pri_key=>val, ...] For a single ID because a query can have multiple primary Ids due to left joins so I accept them all. If an integer instead I associate it with the first pri_id it returns to me. It also accepts the string 'uniq_chars_id'.</li>
        <li><b>$raw_data</b><br>
        (boolean) The type of data extracted</li>
    </ul>
    <h4 class="dbp-h4">Example</h4>
    <p>Edit a record</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$list_id = 'insert_the_id_of_your_list';
$record_id = 'insert_the_id_of_a_record';
$item = ADFO::get_detail($list_id, $record_id);
$item->my_column = "new_value";
$ris = ADFO::save_data($list_id, $item);</pre>
    <h4 class="dbp-h4">Return</h4>
    <p>\stdClass|false</p>
    
    
    <h2 class="dbp-h2">ADFO::get_clone_detail($dbp_id, $dbp_ids)</h2>
    <p>Returns the data of a single record unchanged from the list settings. This data can be passed to save_data after being modified to generate a new record from data in another record. Unlike get_detail, clone_detail removes any primary keys and calculated fields from the extracted results.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The list id</li>
        <li><b>$dbp_ids</b><br>
        (array|int|string) $dbp_ids [pri_key=>val, ...] per un singolo ID perché una query può avere più primary Id a causa di left join per cui li accetto tutti. Se è un integer invece lo associo al primo pri_id che mi ritorna.<br>
        Accetta altresì la stringa 'uniq_chars_id'.</li>
    </ul>
    <h4 class="dbp-h4">Example</h4>
    <p>Clone a record</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$list_id = 'insert_the_id_of_your_list';
$record_id = 'insert_the_id_of_a_record';
$item = ADFO::get_clone_detail($list_id, $record_id);
$ris = ADFO::save_data($list_id, $item);</pre>
    <h4 class="dbp-h4">Return</h4>
    <p>\stdClass|false</p>

    
    <h2 class="dbp-h2">ADFO::save_data($dbp_id, $data)</h2>
    <p>Save the data of one or more lines in a list.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The id of the list</li>
        <li><b>$data</b><br>
        (array) The same imported structure extracted from get_data</li>
    </ul>
    <h4 class="dbp-h4">Example</h4>
    <p>Create a new record</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$list_id = 'insert_the_id_of_your_list';
$item = (object)['my_column'=>'val'];
$ris = ADFO::save_data($list_id, $item);</pre>
    <p>If all fields are empty (and they are all configured) remove the record</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$item = ADFO::get_detail('77', 5);
foreach ($item as $k=>&$i) {
    // if primary key == "dbp_id"
    if ($k == "dbp_id") continue;
    $i = '';
}
// remove record 5
$ris = ADFO::save_data('77', $item);</pre>
    <h4 class="dbp-h4">Return</h4>
    <p>array</p>


    <h2 class="dbp-h2">ADFO::render($dbp_id, $mode)</h2>
    <p>Restituisce la classe ADFO_render_list</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) The list id</li>
        <li><b>$mode</b><br>
        (string) Choose how to handle data if in get|post|ajax or link (partial handling)</li>
    </ul>

    <h4 class="dbp-h4">Return</h4>
    <p>ADFO_render_list</p>
    <h4 class="dbp-h4">Example</h4>
    <pre class="dbp-code">$list =  admin_form\ADFO::render(6, 'get'); // ($list_id:int, $mode:string); $mode is optional
$list->set_color('pink'); // Change the color of the list from the one you choose
$list->table("", false); // (custom_class:string, table_sort:bool)
$list->search(false); // optional (show_button:bool)
$list->single_field_search('column_name'); // Adds a text field to search in a single column 
$list->submit(); // Adds buttons for search
$list->pagination('select');
$list->end(); // Required!</pre>


    <h2 class="dbp-h2">ADFO::draw_export_btn($dbp_id, $title, $format, $btn_class)</h2>
    <p>Print a link to export data</p>
    <h4 class="dbp-h4">Parameters</h4>
        <li><b>$dbp_id</b><br>
            (integer) The list id</li>
        <li><b>$title</b><br>
            (string) The text of the button</li>
        <li><b>$format</b><br>
            (string) csv/sql</li>
        <li><b>$btn_class</b><br>
            (string) The class to edit the graphics</li>
    <h4 class="dbp-h4">Return</h4>
        <p>Void</p>
    <h4 class="dbp-h4">Example</h4>
    <pre class="dbp-code">  // Print the link to download the $id list.
    ADFO::draw_export_btn($dbp_id);</pre>

    <h2 class="dbp-h2">ADFO::get_ids_value_from_list($dbp_id, $row, $return = 'string'</h2>
    <p>Given a row in a list of data ($row) returns the id(s) to extract the details of the record. The string version is used in links.</p>
    <h4 class="dbp-h4">Parameters</h4>
        <li><b>$dbp_id</b><br>
            (integer) The list id</li>
        <li><b>$row</b><br>
            (Object) A single record in a list</li>
        <li><b>$return</b><br>
            (string) string|array|object</li>
    <h4 class="dbp-h4">Return</h4>
        <p>Mixed</p>
    <h4 class="dbp-h4">Example</h4>
    <pre class="dbp-code">  // Generate the link for a popup
    $id = ADFO::get_ids_value_from_list($dbp_id, $row);
    <?php echo htmlentities('<a href="{site}/admin-ajax.php?dbp_ids=\$id&#038;dbp_id=259&#038;action=dbp_get_detail" class="js-dbp-popup">popup link</a>'); ?></pre>


</div>

<h1 style="border-bottom:1px solid #CCC">Template engine</h1>
<div class="dbp-help-p">

    <h3>set get variable</h3>
    <pre class="code">&lt;?php 
    PinaCode::set_var(&quot;myvar&quot;,&quot;foobar&quot;); 
    echo PinaCode::get_var(&quot;myvar&quot;,&quot;default&quot;); 
?&gt;</pre>

    <h3>Execute shortcode</h3>

    <pre class="code">&lt;?php 
    PinaCode::execute_shortcode('...'); 
?&gt;</pre>
    <h3>Returns the result of a mathematical or logical expression</h3>
    
    <pre class="code">&lt;?php 
    PinaCode::math_and_logic('3 > 6'); 
?&gt;</pre>


    <h3>Set a new function</h3>
    <pre class="code">function pinacode_fn_hello_world($short_code_name, $attributes) {
    return 'HELLO WORLD';	
}
pinacode_set_functions('hello_world', 'pinacode_fn_hello_world');</pre>

    <h3>Set a new attributes</h3>
    <pre class="code">function pinacode_attr_fn_new_upper($gvalue, $param, $shortcode_obj) {
    if (is_string($gvalue)) {
    $gvalue = strtoupper($gvalue);
    }
    return $gvalue;
}
pinacode_set_attribute(['new_upper'], 'pinacode_attr_fn_new_upper');</pre>

    </div>
</div>
   