<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content" >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Doc</a><span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('PHP','admin_form'); ?></h2>

    <h1 style="border-bottom:1px solid #CCC">ADMIN FORM (ADFO) class</h1>
    <p>Per usare la classe ADFO scrivi <b>admin_form\ADFO::function_name();</b> oppure
    <pre>use admin_form\ADFO as ADFO;
ADFO::function_name();</pre>
    </p>
    <hr>
    <div class="dbp-help-p">
      
    <h2 class="dbp-h2">ADFO::get_list($dbp_id, $only_table = false, $params=[], $prefix = "")</h2>
    <p>Carica una lista da un id e ne ritorna l'html. </p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$only_table</b><br>
        (bool) Se stampare i filtri e la form che la raggruppa la tabella oppure no.</li>
        <li><b>$params</b><br>  
        (Array) Eventuali parametri aggiuntivi per filtrare ulteriormente la tabella [%params]
        <li><b>$prefix</b><br>  
        (String) un prefisso per i nomi dei campi da inviare nella form per evitare collisioni su più tabelle all'interno della stessa pagina
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>Html</p>

    <h2 class="dbp-h2">ADFO::get_single($dbp_id, $ids)</h2>
    <p>Ritorna l'html di un singolo record.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$ids</b><br>
        (integer) L'id del record.</li>
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>Html</p>


    <h2 class="dbp-h2">ADFO::get_total($dbp_id, $filter = false)</h2>
    <p>Carica una lista da un id e calcola il totale degli elementi.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$filter</b><br>
        (bool) Se applicare i filtri oppure no.</li>
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>Int,  -1 se non riesce a fare il conto</p>


    <h2 class="dbp-h2">ADFO::get_lists_names()</h2>
    <p>Carica tutte le liste dbp</p>
    <h4 class="dbp-h4">Return</h4>
    <p>Array</p>

    <h2 class="dbp-h2">get_list_columns($dbp_id, $searchable = true, $extend = false)</h2>
    <p>Estrae l'elenco delle colonne di una lista.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$searchable</b><br>
        (boolean) Estrae solo le colonne delle tabelle  escludendo le colonne calcolate.</li>
        <li><b>$extend</b><br>
        (boolean) Se true torna solo i nomi delle colonne, altrimenti tutte le informazioni disponibili sulla colonna.</li>
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>ADFO_list_setting[]|array</p>


    <h2 class="dbp-h2">ADFO::get_primaries_id($dbp_id)</h2>
    <p>Ritorna l'elenco delle chiavi primarie di una lista. I campi estratti sono gli alias!</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        
    </ul>
    <h4 class="dbp-h4">Return</h4>
    <p>@return array [table=>primary_name, ...]</p>


    <h2 class="dbp-h2">ADFO::get_data($dbp_id, $return = "items", $add_where = null, $limit = null, $order_field = null, $order="ASC", $raw_data = false)</h2>
    <p>Ritornano i dati o il model di una lista</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$return</b><br>
        (string) items|schema|model|schema+items|raw</li>
        <li><b>$add_where</b><br>
        (array)  [[op:'', column:'', value:'' ], ... ] es: [['op'=>'=', 'column'=>'dbp_id', value=>1]]</li>
        <li><b>$limit</b><br>
        (integer) il numero massimo di record estratti</li>
        <li><b>$order_field</b><br>
        (string) La colonna su cui ordinare i dati</li>
        <li><b>$orderorder_field</b><br>
        (string)  ASC|DESC</li>
        <li><b>$raw_data</b><br>
        (boolean)</li>
    </ul>
    <p>La differenza tra $return = "raw" e $raw_data = true è che con $return = $raw vengono estratti tutti i dati delle tabelle interessate, mentre con $raw_data = true vengono estratti sempre i dati grezzi, ma solo delle colonne estratte dalla query.</p>
    <h4 class="dbp-h4">Return</h4>
    <p>Mixed</p>
    

    <h2 class="dbp-h2">ADFO::get_detail($dbp_id, $dbp_ids, $raw_data = true)</h2>
    <p>Ritorna i dati di un singolo record non modificato dalle impostazioni della lista. Questi dati possono essere passati a save_data dopo essere stati modificati per essere salvati.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$dbp_ids</b><br>
        (array|int|string) $dbp_ids [pri_key=>val, ...] per un singolo ID perché una query può avere più primary Id a causa di left join per cui li accetto tutti. Se è un integer invece lo associo al primo pri_id che mi ritorna.<br>
        Accetta altresì la stringa 'uniq_chars_id'.</li>
        <li><b>$raw_data</b><br>
        (boolean) Il tipo di dato estratto</li>
    </ul>
    <h4 class="dbp-h4">Example</h4>
    <p>Apre un record, lo modifica e lo risalva</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$list_id = 'insert_the_id_of_your_list';
$record_id = 'insert_the_id_of_a_record';
$item = ADFO::get_detail($list_id, $record_id);
$item->my_column = "new_value";
$ris = ADFO::save_data($list_id, $item);</pre>
    <h4 class="dbp-h4">Return</h4>
    <p>\stdClass|false</p>

    <h2 class="dbp-h2">ADFO::get_clone_detail($dbp_id, $dbp_ids)</h2>
    <p>Ritorna i dati di un singolo record non modificato dalle impostazioni della lista. Questi dati possono essere passati a save_data dopo essere stati modificati per generare un nuovo record a partire dai dati di un altro record. A differenza di get_detail, clone_detail rimuove eventuali chiavi primarie e campi calcolati dai risultati estratti.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$dbp_ids</b><br>
        (array|int|string) $dbp_ids [pri_key=>val, ...] per un singolo ID perché una query può avere più primary Id a causa di left join per cui li accetto tutti. Se è un integer invece lo associo al primo pri_id che mi ritorna.<br>
        Accetta altresì la stringa 'uniq_chars_id'.</li>
    </ul>
    <h4 class="dbp-h4">Example</h4>
    <p>Clona un record</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$list_id = 'insert_the_id_of_your_list';
$record_id = 'insert_the_id_of_a_record';
$item = ADFO::get_clone_detail($list_id, $record_id);
$ris = ADFO::save_data($list_id, $item);</pre>
    <h4 class="dbp-h4">Return</h4>
    <p>\stdClass|false</p>
    
    <h2 class="dbp-h2">ADFO::save_data($dbp_id, $data)</h2>
    <p>Salva i dati di una o più righe in una lista.</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$data</b><br>
        (array) La stessa struttura importata estratta da get_data</li>
    </ul>
    <h4 class="dbp-h4">Example</h4>
    <p>Crea un record</p>
    <pre class="dbp-code">use admin_form\ADFO as ADFO;
$list_id = 'insert_the_id_of_your_list';
$item = (object)['my_column'=>'val'];
$ris = ADFO::save_data($list_id, $item);</pre>
    <p>Se tutti i campi sono vuoti (e sono tutti configurati) rimuove il record</p>
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
    <h4 class="dbp-h4">Return</h4>
    <p>array</p>

    
    

    <h2 class="dbp-h2">ADFO::render($dbp_id, $mode)</h2>
    <p>Restituisce la classe ADFO_render_list</p>
    <h4 class="dbp-h4">Parameters</h4>
    <ul>
        <li><b>$dbp_id</b><br>
        (integer) L'id della lista</li>
        <li><b>$mode</b><br>
        (string) Sceglie come gestire i dati se in get|post|ajax o link (gestione parziale)</li>
    </ul>

    <h4 class="dbp-h4">Return</h4>
    <p>ADFO_render_list</p>
    <h4 class="dbp-h4">Example</h4>
    <pre class="dbp-code">$list =  admin_form\ADFO::render(6, 'get'); // ($list_id:int, $mode:string); $mode is optional
$list->set_color('pink'); // Cambia il colore della lista rispetto a quello scelto 
$list->table("", false); // (custom_class:string, table_sort:bool)
$list->search(false); // optional (show_button:bool)
$list->single_field_search('column_name'); // aggiunge un campo di testo per ricercare in una singola colonna 
$list->submit(); // Aggiunge i bottoni per la ricerca 
$list->pagination('select');
$list->end(); // Required!</pre>
    </div>

    <h2 class="dbp-h2">ADFO::draw_export_btn($dbp_id, $title, $format, $btn_class)</h2>
    <p>Stampa un link per l'export dei dati</p>
    <h4 class="dbp-h4">Parameters</h4>
        <li><b>$dbp_id</b><br>
            (integer) L'id della lista</li>
        <li><b>$title</b><br>
            (string) il testo del bottone</li>
        <li><b>$format</b><br>
            (string) csv/sql</li>
        <li><b>$btn_class</b><br>
            (string) la classe per modificare la grafica</li>
    <h4 class="dbp-h4">Return</h4>
        <p>Void</p>
    <h4 class="dbp-h4">Example</h4>
    <pre class="dbp-code">
        // stampa il link per scaricare la lista $id.
        ADFO::draw_export_btn($dbp_id);
    </pre>

    <h2 class="dbp-h2">ADFO::get_ids_value_from_list($dbp_id, $row, $return = 'string'</h2>
    <p>Data una riga di un elenco di dati ($row) ritorna l'id o gli id per estrarre i dettagli del record.   La versione string è usata nei link.</p>
    <h4 class="dbp-h4">Parameters</h4>
        <li><b>$dbp_id</b><br>
            (integer) L'id della lista</li>
        <li><b>$row</b><br>
            (Object) un singolo record di una lista</li>
        <li><b>$return</b><br>
            (string) string|array|object</li>
    <h4 class="dbp-h4">Return</h4>
        <p>Mixed</p>
    <h4 class="dbp-h4">Example</h4>
    <pre class="dbp-code">  // Genera il link per un popup
    $id = ADFO::get_ids_value_from_list($dbp_id, $row);
    <?php echo htmlentities('<a href="{site}/admin-ajax.php?dbp_ids=\$id&#038;dbp_id=259&#038;action=dbp_get_detail" class="js-dbp-popup">popup link</a>'); ?></pre>

</div>
    <h1 style="border-bottom:1px solid #CCC">Template engine</h1>
    <div class="dbp-help-p">

    <h3>set get variable</h3>
    <pre class="code">
    &lt;?php 
        PinaCode::set_var(&quot;myvar&quot;,&quot;foobar&quot;); 
        echo PinaCode::get_var(&quot;myvar&quot;,&quot;default&quot;); 
    ?&gt;
    </pre>


    <h3>Execute shortcode</h3>
    
    <pre class="code">
    &lt;?php 
        PinaCode::execute_shortcode('...'); 
    ?&gt;
    </pre>

    <h3>Ritorna il risultato di un'espressione matematica o logica</h3>
    
    <pre class="code">
    &lt;?php 
        PinaCode::math_and_logic('3 > 6'); 
    ?&gt;
    </pre>


    <h3>Set a new function</h3>
    <pre class="code">
    function pinacode_fn_hello_world($short_code_name, $attributes) {
    PinaCode::set_var('global.search_container.status', 'open');
    return $string;	

    }
    pinacode_set_functions('hello_world', 'pinacode_fn_hello_world');
    </pre>

    <h3>Set a new attributes</h3>
    <pre class="code">
    function pinacode_attr_fn_new_upper($gvalue, $param, $shortcode_obj) {
		if (is_string($gvalue)) {
			$gvalue = strtoupper($gvalue);
		}
		return $gvalue;
	}
    pinacode_set_attribute(['new_upper'], 'pinacode_attr_fn_new_upper');
    </pre>

    </div>
</div>
   