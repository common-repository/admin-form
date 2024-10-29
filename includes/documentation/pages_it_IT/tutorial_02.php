<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Galleries','admin_form'); ?></h2>
    <p>In questo tutorial vedremo come creare galleria di immagini. Le immagini verranno gestite tramite la Media Library di wordpress.<br>La struttura sarà composta da due tabelle. La prima tabella avrà l'elenco delle gallerie di immagini avrà titolo e descrizione.<br>La seconda tabella avrà invece l'elenco delle immagini per ogni galleria e le colonne saranno: immagine, titolo e il riferimento alla galleria a cui appartengono.</p>

    <h2 class="dbp-h2">Iniziamo creando un nuovo elenco</h2>

    <p>Clicchiamo sul plugin ADMIN FORM e poi sul bottone <b>"CREATE NEW FORM"</b>. Come nome scriviamo <b>Gallery images</b> e lasciamo "create a new Table". Premiamo quindi il bottone salva.</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_01.png"  class="dbp-tutorial-img">

    <p>Una volta salvata la lista verrete rediretti nel <b>TAB FORM</b>.<br>
    Clicchiamo sul bottone new field così da aggiungere una nuova colonna.<br>
    Nei parametri inseriamo:
    <b>NEW FIELD NAME</b>: image_id<br>
    <b>Field Type</b>: Media gallery<br>
    <b>Required</b>: yes<br>
    </p>

    <h2>Frontend</h2>
    <p>Copiamo lo shortcode per la visualizzazione della galleria. Questo può essere trovato in alto a destra sulle pagine di configurazione oppure tornando alla pagina principale del plugin nella colonna shortcode.
    Copiamo lo shortcode e associamolo ad un articolo.</p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_05.png"  class="dbp-tutorial-img">
 
    <p>La visualizzazione è in formato tabella, ora trasformiamola in una galleria di immagini</p>
    <p>Torniamo in amministrazione, dentro il plugin nel <b>TAB FRONTEND</b>.
    Su <b>List type</b> selezioniamo <b>custom</b> invece che table</p>
    <p>Sulla sezione <b>Header</b> scrivamo:</p>
    <pre class="dbp-code">&lt;div class=&quot;dbp-gallery-columns&quot;&gt;</pre>
    <p>Su <b>Loop the data</b> scriviamo</p>
<div class="dbp-code">&lt;a href=&quot;[^LINK_DETAIL]&quot; class=&quot;js-dbp-popup&quot;&gt;[^IMAGE.image id=[%data.image_id val] image_size=medium]&lt;/a&gt;</div>
<p>Da notare che per visualizzare il popup con l'immagine utilizzeremo il sistema integrato dei dettagli. Per richiamare il popup il aggiungere al link la classe js-dbp-popup e come link ci basterà richiamare la funzione del template engine [^LINK_DETAIL].</p>

<p>Sul <b>Footer</b> ci basterà chiudere il div che abbiamo aperto per ordinare le immagini</p>
<div class="dbp-code">&lt;/div&gt;</div>
<p>Su <b>Detail view</b> Possiamo impostare vari tipi di popup su popup_style, base, large o fit. Fit ci permette di avere una finestra che si adatterà alle dimensioni del contenuto. Scegliamo su <b>Popup type</b> <b>FIT</b></p>
<p>Su <b>View type</b> scegliamo <b>CUSTOM</b> e all'interno dell'editor richiamiamo l'immagine grande richiedendo come dimensioni che si adatti alla finestra (winfit). Nella documentazione sono descritte le varie opzioni di image_size. Da notare che non possiamo usare la colonna image che nativamente avrebbe contenuto l'id dell'immagine perché dentro list view setting abbiamo impostato image come media gallery per cui la colonna restituisce la miniatura in html e non l'id della galleria di immagini.</p>
<div class="dbp-code">[^IMAGE.image id=[%data.image_id] image_size=winfit]</div>

<p>Infine all'interno del css del proprio template bisogna aggiungere:
<pre class="dbp-code">.dbp-gallery-columns { display: grid; grid-template-columns: 1fr 1fr 1fr; align-items: center; justify-items: center; grid-gap: 2px; line-height: 0;}
.dbp-gallery-columns > a { border: 1px solid; line-height: 0; padding:.5rem; height: 100%; box-sizing: border-box; display: flex; align-items: center;}</pre>
<p>Il risultato finale mostrerà una serie di immagini cliccabili simili all'immagine sottostante.</p>

<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_02_06.png"  class="dbp-tutorial-img">

</p>
</div>