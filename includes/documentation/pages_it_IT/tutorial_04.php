<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Simple list with multiple post','admin_form'); ?></h2>
    <p>In questo tutorial vedremo come creare un semplice elenco con un campo in cui vengono elencati una serie di articoli. </p>

    <h2 class="dbp-h2">Iniziamo creando un nuovo elenco che chiameremo article list</h2>
    
    <p>Clicchiamo sul plugin ADMIN FORM e poi sul bottone <b>"CREATE NEW FORM"</b>. Come nome scriviamo <b>Article list</b> e clicchiamo su  "create a new Table".</p>

    <p>
        Una volta salvata la lista verremo rediretti nel <b>TAB FORM</b> dove potremo aggiungere 2 campi:</b><br><br>
        field name: <b>title</b><br>
        field Type: <b>Text (single line)</b><br>
        field label: <b>Title</b><br>
        required<br>
        <br>
        field name: <b>Articles</b><br>
        field Type: <b>Post</b><br>
        is multiple: <b>Selezioniamo il checkbox "is multiple"</b><br>
        field label: <b>Articles</b><br><br>

        <b>Salviamo la form</b>
    </p>

    <p>Ora è già possibile caricare per ogni record una serie di articoli. </p>

    <h2 class="dbp-h2">FRONTEND</h2>
    <p>Ora Creiamo una visualizzazione diversa per il frontend in cui aggiungiamo i link agli articoli</p>
    <p>Andiamo sul tab FRONTEND e clicchiamo su "list type" "<b>custom</b>".</p>
    <p>In Loop the data aggiungiamo:</p>
    
    <pre class="dbp-code">
&lt;h1&gt;[%data.title]&lt;/h1&gt;

[%data.Articles json tmpl=[:
    &lt;div class="dbp-item-post"&gt;
    [^POST.title_link id=[%item]]
    &lt;/div&gt;
:]]
    </pre>

    <br>
    <p>%data.Articles è il nome del campo nel quale inseriamo i post multipli. Questi vengono salvati in un campo di testo in formato json. [%data.Articles json] converte il json in un array. per ciclare l'array usiamo tmpl=[::] All'interno delle parentesi quadre di tmpl possiamo usare $item per reperire i singoli elementi ciclati. Quindi $item è l'id dei post. A questo punto quindi carichiamo il post e stampiamo il link con il titolo.</p> 

    <h2 class="dbp-h2">PUBBLICAZIONE</h2>
    <p>In alto a destra oppure dalla tab "code" puoi copiare lo shortcode per visualizzare la lista. Dopo aver inserito qualche dato di test crea una nuova pagina ed inserisci lo shortcode [adfo_list id=xxx].</p> 

</div>