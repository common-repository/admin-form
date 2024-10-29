<?php 
namespace DatabasePress;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Related post','admin_form'); ?></h2>
    <p>In questo tutorial ti mostrerò come creare una tabella che visualizzi gli  articoli che abbiano almeno un tag in comune con l'articolo che si sta visualizzando. <br>Per prima cosa dobbiamo creare alcuni articoli che abbiano gli stessi tag. Ad esempio puoi creare una serie di articoli così composti:</p>
    <table class="dbp-tutorial-table">
        <tr><td>Post title</td><td>Tag</td></tr>
        <tr><td>Venice</td><td>Italy</td></tr>
        <tr><td>Rome</td><td>Italy, Capital</td></tr>
        <tr><td>Milan</td><td>Italy</td></tr>
        <tr><td>Paris</td><td>France, Capital</td></tr>
        <tr><td>Marseille</td><td>France</td></tr>
    </table>

    <h2 class="dbp-h2">Creazione della lista</h2>
    <p>Clicchiamo sul plugin ADMIN FORM e poi sul bottone <b>"CREATE NEW FORM"</b> Nel nome scriviamo <b>Articoli correlati</b>. Scegliamo 'Choose an existing table' e selezioniamo wp_term_relationships</p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_01.png"  class="dbp-tutorial-img">
    <p>Una volta salvato apparirà nel tab FORM iun messaggio che ci avverte che non è presenta una chiave primaria valida per la modifica dei dati. In questo caso vogliamo infatti solo visualizzare gli articoli con tag simili e non stiamo lavorando su una lista che modifichi i dati.</p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_02.png"  class="dbp-tutorial-img">
    <h2 class="dbp-h2">TAB SETTING</h2>
    <p>Andiamo sul tab setting e nella sezioni Admin sidebar menu deselezioniamo Show in admin menu.</p>


    <p>Per permettere allo script di visualizzare solo gli articoli che ci interessano dobbiamo creare due filtri. Noi vogliamo che vengano estratti tutti gli articoli che hanno almeno un tag uguale a quello dell'articolo che si sta visualizzando. Per fare questo vogliamo che tr.term_taxonomy_id sia uguale ad uno degli id dei tag (term) dell'articolo che si sta visualizzando. Per avere l'id dell'articolo che si sta visualizzando si può usare lo shortcode [^current_post.id]<br>
    Per avere l'elenco degli id dei tag di un articolo possiamo usare la funzione [^get_post_tags]. Quindi scrivendo nel primo filtro:</p>
    <p>Select Column: <b>ter.term_taxonomy_id</b>, il tipo di filtro è <b>IN (Match in array)</b>, il valore è: 
    <pre class="dbp-code">[^get_post_tags.term_id post_id=[^current_post.id]]</pre>.</p>
    <p>Il secondo filtro invece dice che l'id degli articoli estratti devono essere diversi dall'id dell'articolo che si sta visualizzando. Questo per evitare di mostrare lo stesso articolo che si sta visualizzando tra gli articoli correlati.</p>
    <p>Select Column: <b>ter.object_id</b>, il tipo di filtro è <b>!= (Does NOT Equal)</b>, il valore è:
    <pre class="dbp-code">[^current_post.id]</pre>.</p>

    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_03.png"  class="dbp-tutorial-img">
    <p>Salvo le impostazioni</p>

    <h2 class="dbp-h2">Pubblicazione</h2>
    <p>Lo shortcode per pubblicare la lista lo trovi in alto a destra.<br>
    Puoi pubblicare lo shortcode nel template così da applicarlo in tutte le pagine oppure nei singoli articoli</p>
    <p>Altrimenti puoi usare il php per caricare la lista e pubblicarla a tuo piacere. Ecco un esempio per caricare la lista in fondo ai post. Inserisci il codice nel template dentro function.php e <b>sostituisci {list_id} con l'id della lista appena creata</b>.</p>
    <pre class="dbp-code">function add_dbp_list_to_content($content) {
	$append = '';
	if (is_single()) {
		$append = "&lt;h3&gt;TEST RELEATED POST&lt;/h3&gt;" .  admin_form\ADFO::get_list('{list_id}');
	}
	return $content.$append;
}
add_filter('the_content', 'add_dbp_list_to_content');</pre>

<p>Se proviamo a pubblicare la tabella all'interno degli articoli apparirà qualche cosa del genere:
<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_04.png" class="dbp-tutorial-img">
Object_id mostra l'id del post, ora dobbiamo visualizzare il titolo.</p>

<h2 class="dbp-h2">LIST view formatting</h2>
<p>Apriamo la modifica della prima colonna (object_id).<br>
Deseleziona "Keeps settings aligned with information from the same field in tab 'Form'". Questa opzione consente di modificare automaticamente le impostazioni della colonna in base a come si modifica il campo nella scheda Modulo.
Come titolo scegliamo "Articles" mentre come tipo scegliamo <b>CUSTOM</b>.<br>
A questo punto si aprirà una textarea in cui inseriremo 
<pre class="dbp-code">&lt;a href="[^LINK id=[%data.object_id]]"&gt;[^post.title id=[%data.object_id]]&lt;/a&gt;</pre>
Nelle colonne successive selezioniamo Hide per non farle apparire.<br>
<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_05.png" class="dbp-tutorial-img">
</p>
<p>Ecco come dovrebbe apparire il risultato finale

<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_01_06.png" class="dbp-tutorial-img">
</p>



<p style="color:red">Quando scrivi nel template engine integrato di ADMIN FORM Ricordati sempre che tra gli attributi e il valore non ci devono mai essere spazi! Quindi [^get_post_tags.html post_id = [%data.object_id]] ad esempio non funzionerà perché prima e dopo il simbolo = ci sono due spazi</p>
