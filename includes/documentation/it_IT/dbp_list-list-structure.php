<?php
/**
 * header-type:doc
 * header-title: Modificare i dati estratti
* header-tags:
* header-description: Una volta salvata una query è possibile modificare la visualizzazione dei dati dal tab List view formatting
* header-lang:ITA
*/

namespace admin_form;
if (!defined('WPINC')) die;

?>
<div class="af-content-margin">
    <p>la lista presenta tutte le colonne che vengono estratte dalla tabella. Puoi cambiare l'ordine di visualizzazione,  scegliere di nascondere una colonna o modificare come i dati vengono visualizzati.</p>
    <p> Puoi aggiungere nuove colonne lavorando i dati estratti attraverso il template engine, ma se vuoi estrarre altri dati dovrai modificare la query di estrazione [ADMIN FORM PRO]</p>

    <div id="dbp_help_title" class="dbp_help_div">
        <h4>Table title</h4>
        <p>Il titolo che avrà la colonna, non infuenza i dati o i nomi dei dati estratti dal template engine</p>
    </div>
    <div id="dbp_help_searchable" class="dbp_help_div">
        <h4>Searchable</h4>
        <p>Quando usi il campo di ricerca  questo cercherà in tutte le colonne in chi è è stato scelto un tipo di ricerca. LIKE vuol dire che cerca all'interno del testo mentre = cercherà solo le colonne uguali al testo cercato.</p>
    </div>
    <div id="dbp_help_print" class="dbp_help_div">
        <h3>Column Type</h3>
        <p><b>Text</b>: Stampa il contenuto del campo mostrando l'html</p>
        <p><b>Html</b>: Stampa la colonna applicando l'html</p>
        <p><b>Date</b>: Stampa la data senza l'ora</p>
        <p><b>DateTime</b>: Stampa la data con l'ora</p>
        <p><b>Image</b>: Stampa la thubms dell'immagine</p>
        <p><b>External Link</b>: Rende un link cliccabile. Attributi: Link text: Mostra un testo alternativo. Puoi usare le variabili [%data.nome_campo] per mostrare il testo di un altro campo.</p>
        <p><b>Detail Link</b>: Rende il testo cliccabile. Apre un popup con i dettagli del record. Puoi configurare i dettagli in frontend Detail View.</p>
        <p><b>Serialize</b>: Mostra un campo con i dati serializzati</p>
        <p><b>Show Checkbox values (Json label)</b>: Mostra i valori dei checkboxes selezionati.</p>
        <p><b>Custom</b>:  permette di usare gli shortcode per visualizzare il contenuto della colonna. Clicca su 'show shortcode variables' per vedere la lista delle variabili.</p>
        <p id="dbp_help_user" class="dbp_help_div"><b>User</b>:  mostra il nome utente a partire dall'ID. Puoi anche aggiungere il link alla pagina dell'autore selezionando il checkbox link to author page. 
        <pre class="dbp-code">
            [%user.user_login], [%user.user_email]
        </pre>
        </p>
        <p id="dbp_help_post" class="dbp_help_div"><b>Post</b>: mostra il titolo di un post a partire dall'ID. Puoi anche scegliere se creare il titolo linkabile all'articolo selezionando il checkbox link to article page.</p>
        <p><b>Lookup [PRO]</b>: Se nel campo è salvato l'id di un'altra tabella, è possibile mostrare i dati della tabella correlata attraverso questo tipo di campo. Scegli la tabella, poi seleziona i campi che vuoi che vengano mostrati. L'attributo show ti permette di mostrare uno o più campi della tabella collegata (tiene premuto ctrl mentre clicchi i campi che vuoi visualizzare). Creato un campo lookup, salva le impostazioni. A questo punto appariranno le colonne selezionate come nuovi campi che potrai configurare a tuo piacere.</p> 

        <h3>Edit fields</h3>
        <p> I campi di modifica consentono di modificare il valore del campo direttamente dall'elenco.</p>
        
        <p><b>Order</b>:Consente di ordinare un campo transcinando. Per attivarla, la query deve essere ordinata in ordine crescente per la colonna che si desidera ordinare.</p>

        <p><b>Input</b>: Consente di modificare il valore del campo direttamente dall'elenco.</p>

        <p><b>Checkbox</b>: Quando si seleziona la casella di controllo, il campo del database viene modificato con il valore inserito nel campo Valore casella di controllo.</p>

        <p><b>Select</b>: Permette di modificare il campo con i valori inseriti nel campo Seleziona valori. </p>
    </div>

    <div id="dbp_help_format" class="dbp_help_div">
        <h3>Column formatting</h3>
        <h4>change values</h4>
        <p>Cambia il valore del contenuto secondo il csv inserito</p>
        <p>I valori del csv devono essere separati da virgola. Il primo valore è quello della colonna, il secondo è come deve essere trasformato</p>
        <p>È possibile usare le scritture speciali <b>< x, > x, o =x-y</b> per un intervallo, dove x e y sono numeri.</p>
        esempio: 
        <pre class="dbp-code">
    0, NO
    1, YES
    >1, MAYBE
        </pre>
    </div>
    <div id="dbp_help_styles" class="dbp_help_div">
        <h4>change styles</h4>
        <p>Aggiunge una classe condizionata a seconda del valore del csv inserito</p>
        <p>È possibile usare le scritture speciali <b>< x, > x, o =x-y</b> per un intervallo, dove x e y sono numeri.<br>
        ecco l'elenco delle classi già configurate:
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
        esempio: 
        <pre class="dbp-code">
    0, dbp-cell-red
    =1-10, dbp-cell-green
        </pre>
    </div>
</div>