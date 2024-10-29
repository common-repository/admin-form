<?php
/**
 * header-type:doc
 * header-title: Modulo di inserimento dati
* header-tags:
* header-description: Una volta salvata una query è possibile modificare la visualizzazione dei dati dal tab List view formatting
* header-lang:ITA
*/

namespace admin_form;
if (!defined('WPINC')) die;

?>
<div class="af-content-margin">
    <p>Gestisci i moduli per l'inserimento dei dati</p>
    <p>Qui puoi scegliere il tipo di campi delle tabelle interessate dalla query.</p>

    <div id="dbp_help_attrs" class="dbp_help_div">
        <h4>Table attributes</h4>
        <p>
            Ogni modulo di inserimento è composta da uno o più blocchi che identificano le tabelle dalle quali i dati sono estratti. Il titolo mostra la tabella e l'alias usato nella query. <br>
            Per ogni tabella cliccando su show attributes è possibile modificare alcuni parametri di visualizzazione
        </p>
    </div>

    <div id="dbp_help_toggle" class="dbp_help_div">
        <h4>Show/Hide</h4>
        <p>Scegli se il campo deve essere mostrato nel modulo di inserimento dei dati</p>
    </div>


    <div id="dbp_help_type" class="dbp_help_div">
        <h3>Field Type</h3>
        <p><b>Text (Single line)</b>: Una singola riga di testo. la spunta "Autocomplete" attiva i suggerimenti proponendo gli altri campi di testo già inseriti.</p>
        <p><b>Text (Multiline)</b>textarea. non formattata.</p>
        <p><b>Date</b> Data formattata secondo la configurazione di wordpress. <?php echo get_option( 'date_format' ); ?></p>
        <p><b>DateTime</b> Data più ora, formattata secondo la configurazione di wordpress. <?php echo get_option( 'time_format' ); ?></p>
        <p><b>Number</b>Un singolo numero intero (negativo o positivo). Per limitarlo vedi le funzioni javascript.</p>
        <p><b>Decimal</b> Numero con due numeri dopo la virgola.</p>
        <p><b>Multiply Choice - Drop-down list</b> Select a risposta singola. Le risposte vengono inserite nel campo choices. Una riga per ogni scelta. valore,label</p>
        <p><b>Multiply Choice </b> Radio buttons</p>
        <p><b>Checkbox (Single Answer)</b> Inserisci il valore che verrà salvato quando è spuntato il campo nel field checkbox value. </p>
        <p><b>Checkboxes</b> Checkboxes, Multiple Answers. I dati vengono salvati in un json. </p>
        <p><b>EMail</b> Il campo deve contenere il simbolo @. </p>
        <p><b>Link</b> Il campo deve essere un link valido.</p>

        <p><b>Primary value</b> Può essere assegnato solo a campi che cono chiave primaria. Di default è read only, ma può essere modificato.</p>

        <p><b>Read Only</b> Il campo è in sola lettura e non può essere modificato.</p>
        <p><b>Editor Code</b> Attiva l'editor del codice.</p>
        <p><b>Classic text editor</b> Attiva l'editor classico. Verifica l'impostazione dell'utente.</p>
       
        <p><b>Record Creation Date</b> Viene salvata la data di creazione del record. Il campo non viene sovrascritto durante le modifiche.</p>
        <p><b>Last update Date</b> Viene salvata la data di ultima modifica del campo.</p>
        <p><b>Author</b> Viene salvato l'id dell'autore del record. Il campo non viene sovrascritto durante le modifiche. In una form può esistere un solo campo autore. Il campo autore attiva la sezione permessi nel tab "Settings". Attraverso questa opzione puoi selezionare il ruolo adibito all'autore e il ruolo dell'editor. Il ruolo dell'autore può vedere e modificare solo i suoi record. Se nella stessa form si attiva il campo status è possibile gestire anche il ruolo del contributor, ovvero utenti che possono scrivere e vedere solo i loro record, ma non possono pubblicarli. </p>
        <p><b>Modifing user</b> Viene salvato l'id dell'autore dell'ultima modifica.</p>
        <p><b>ORDER</b> Una colonna per l'ordinamento dei record. Dopo aver impostato un campo order qesto viene prepopolato ogni volta che si crea un nuovo campo con il valore più alto dell'elenco. Se non vuoi che venga modificato puoi inserirlo come hide. Quindi è consigliabile modificare in Setting l'ordinamento impostando l'ordine della query per il campo appena creato. In "List view formatting" verifica infine che la colonna dell'ordinamento sia impostata in tipo order. In questo modo è possibile trascinare i campi e ordinarli direttamente dall'elenco dei record. </p>
        <p><b>Status (Publish, draft ...)</b> Viene salvato lo stato del post. In una form può esistere un solo campo autore. Puoi scegliere tra i seguenti stati: draft, publish, trash. Lo stato "pending" viene attivato solo se esiste anche un campo autore e usato solo per gli utenti con il ruolo di contributor. Solo i record publish vengono visualizzati nel frontend. Il campo post status si comporta in modo diverso nel campo browse the list o se viene selezionata la voce di menu del modulo.</p>
        <div id="dbp_help_lookup" class="dbp_help_div">
            <p><b>Lookup field</b>
             I campi di ricerca vengono utilizzati per collegare i dati con altre tabelle utilizzando la chiave primaria. <br>
            Il "campo della query WHERE" viene utilizzato per limitare i dati che verranno visualizzati. </p>
        </div>
        <p id="dbp_help_calc_field" class="dbp_help_div"><b>Calculated Field</b>
                The calculated fields are filled in upon saving with the formula you entered into the formula. You can copy a field using the template engine variables [%data.variable_name] or create new fields by requesting data from posts or users eg [^POST.title id = [%data.post_id]. To calculate the number of the newly created row you can use [%row], while if you want to create a new identifier you can use [^COUNTER]</p>

        <p><b>Post</b> Viene inserito l'id di un post. è possibile filtrare i tipi di post che possono essere inseriti</p>
        <p><b>User</b> Viene inserito l'id di un utente.</p>
        <p><b>Media Gallery</b> Viene inserito l'id riferito ad un file caricato nella media gallery di wordpress.</p>
    </div>

    <div id="dbp_help_js" class="dbp_help_div">
        <h4>JS Script</h4>
        <p>Inserisci i javascript per personalizzare l'esperienza di inserimento.</p>
        <p>Esempio: Valido il campo solo se è compreso tra 0 e 100</p>
        <pre class="dbp-code">
 field.valid_range(0,100);
        </pre>
        <p>Esempio: Mostro il campo se il valore di un'ipotetica checkbox è uguale a 1</p>
        <pre class="dbp-code">
 field.toggle(form.get('mycheckboxlabel').val() == 1);
        </pre>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=js-controller-form") ?>" target="blank" class="js-simple-link">Approfondisci</a>
    </div>
    
    <div id="dbp_help_default" class="dbp_help_div">
        <h4>Default Value</h4>
        <p>È il valore che viene presentato quando si inserisce un nuovo record. È possibile inserire shortcode del template engine.</p>
        <p> Example:</p>
        <pre class="dbp-code">[^user.id]
[^NOW]
[^request.xxx]</pre>
    </div>
    <div id="dbp_help_class" class="dbp_help_div">
        <h4>Custom css class</h4>
        <p>Aggiungi una o più classi css nel field.</p>
        <p>Puoi allineare uno accanto all'altro due campi aggiungendo ai due campi la classe <b>dbp-form-columns-2</b>.
        <p>Per i checkboxes e i radio è possibile impaginare le opzioni in più colonne aggiungendo uno dei seguenti custom css class:<br>
        dbp-form-cb-columns-2, dbp-form-cb-columns-3, dbp-form-cb-columns-4 </p>
    </div>

    <div id="dbp_help_delete" class="dbp_help_div">
        <h4>Delete field</h4>
        <p>Se la tabella è in DRAFT mode allora dal form è possibile modificarne la struttura.</p>
        <p>Delete field rimuove il campo e tutti i dati inseriti in quel campo</p>
        <p>Se non si vuole rimuovere la colonna o non si può modificare la tabella si può nascondere il campo dal select show/hide</p>
    </div>
    <div id="dbp_help_new_field" class="dbp_help_div">
        <h4>New field</h4>
        <p>Se la tabella è in DRAFT mode allora è possibile creare un nuovo campo nella tabella. Se si vuole avere più controllo nella creazione dei campi si può andare nella struttura della tabella e modificarla.</p>
    </div>
   
    <div class="dbp_help_div">
        <h4>PHP Filter</h4>
        <p><a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=hooks") ?>" target="_blank" class="js-simple-link">apply_filters('adfo_save_data', $query_to_execute, $dbp_id, $origin)</a></p>
    </div>
    <br><br>
</div>
