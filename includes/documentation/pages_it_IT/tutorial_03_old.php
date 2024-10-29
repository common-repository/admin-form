<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Post type and metadata V1.6','admin_form'); ?></h2>
    <p>In questo tutorial vedremo come creare un nuovo post type per un elenco di album musicali. Creeremo tre nuovi campi per l'autore dell'album, l'anno di uscita e la locandina. </p>
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_03") ?>">Guarda il nuovo tutorial della versione 1.7 +</a></p>
    <h2 class="dbp-h2">Iniziamo creando un nuovo elenco</h2>
    
    <p>Clicchiamo sul plugin ADMIN FORM e poi sul bottone <b>"CREATE NEW FORM"</b>. Come nome scriviamo <b>Music</b> e clicchiamo su  "Choose an existing table". Selezioniamo wp_post e clicchiamo salva.</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_01.png"  class="dbp-tutorial-img">
    <div style="float:left; width:48%; margin-right:1%">
        <p>Una volta salvata la lista verrete rediretti nel <b>TAB FORM</b>.<br>
        <p>Ordiniamo i campi trascinandoli dai due triangoli sui titoli azzurri. Mettiamo alle prime posizioni ID, post_title, post_content, post_status e post_type. Tutti gli altri campi li impostiamo su HIDE.<br>Clicchiamo sulla matita accanto a <b>post_title</b> e impostiamo field type: <b>text (single line)</b>. Modifichiamo <b>post_content</b> e su field type scegliamo <b>Classic text editor</b>.<br><br><u>Su <b>post_status</b> impostiamo come default value "<b>publish</b>" e selezioniamo <b>HIDE</b> accanto al titolo</u>.<br><u>Su <b>post_type</b> impostiamo come default value "<b>music</b>"  e selezioniamo <b>HIDE</b> accanto al titolo</u>.</p>
    </div>
    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_02.png"  class="dbp-tutorial-img">
    </div>
    <div style="clear:both;width:100%"><br></div>

    <h2 class="dbp-h2">Ora aggiungiamo i metadata</h2>
    <div style="float:left; width:48%; margin-right:1%">
        <p>Andiamo in fondo alla form e clicchiamo sul bottone <b>ADD METADATA</b><br>
        Configuriamo due nuovi campi:<br>
        field name: <b>artist</b><br>
        field Type: <b>Text (single line)</b><br>
        field label: <b>Artist</b><br>
        required<br>
        <br>
        field name: <b>cover</b><br>
        field Type: <b>Media gallery</b><br>
        field label: <b>Cover</b><br><br><br>
        <b>Salviamo la form</b>
        </p>
    </div>

    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_03.png"  class="dbp-tutorial-img">
    </div>
    <div style="clear:both;width:100%"><br></div>

     <h2 class="dbp-h2">Tab setting</h2>
    <p>Ora dobbiamo dire al nostro modulo che deve filtrare i dati solo per il post_type music.<br>Scorriamo fino ai filtri e scegliamo come campo pos.post_type, come operatore = (Equals) e come valore music.</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_04.png"  class="dbp-tutorial-img">

    <p>Salviamo.<br>
    <u>Attenzione i filtri non vengono applicati nel tab "Browse the list"!</u>. Per provare il funzionamento andate sulla voce di menu "music" che sarà apparsa sotto ad Admin Form.</p>

    <p>Questo il risultato finale che dovrebbe apparire nel backend</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_05.png"  class="dbp-tutorial-img">

    <h2 class="dbp-h2">FRONTEND</h2>
    <br>
    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_06.png"  class="dbp-tutorial-img">
    </div>
    <div style="float:left; width:48%;">
        <p>Creiamo una nuova pagina di wordpress e inseriamo lo shortcode della lista che troviamo in alto a destra su ogni pagina di configurazione</p>
    </div>
    <div style="clear:both;width:100%"><br></div>
    <br>
    <div style="float:left; width:48%;">
        <p>Ora torniamo al plugin e sul tab <b>List view formatting</b> modifichiamo il titolo per creare il link alla pagina singola del post type. <br> 
        Accanto a post_title clicchiamo la matita per modificare la colonna e poi deselezioniamo " Keeps settings aligned with information from the same field in tab 'Form'".<br>
        Su <b>Column Type</b> scelgo <b>Custom</b> e inserisco il seguente codice:
        <pre class="dbp-code">&lt;a href="[^LINK id=[%data.ID]]"&gt;[%data.post_title]&lt;/a&gt;</pre>
        <br>Salviamo
    
        </p>
    </div>
    <div style="float:left; width:48%;">
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_07.png"  class="dbp-tutorial-img">
    </div>
    <div style="clear:both;width:100%"><br></div>

    <h2 class="dbp-h2">Modifichiamo il template</h2>
    <p>Per far funzionare un nuovo post type bisogna prima di tutto registrarlo, e poi modificare il contenuto della pagina singola per poter visualizzare i dati aggiunti. Possiamo fare tutto dentro il file <b>function.php</b> che troviamo dentro il template attivo. In fondo al file aggiungiamo</p>

    <pre class="dbp-code">// Register post_type music
function adfo_type_music() {
	$args = array(
	'public' => true,
	'query_var' => true,
	'rewrite' => array('slug' => 'music')
	);
	register_post_type('music', $args);
}
add_action('init', 'adfo_type_music');

// I add metadata to the content
function adfo_single_page_music ( $content ) {
    if ( is_single() && get_post_type() == "music") {
		$adfo_detail =  admin_form\ADFO::get_detail(8, get_the_ID(), false); 
        return $content . '&lt;p&gt;ARTIST: '.$adfo_detail->artist.'&lt;/p&gt;&lt;p&gt;Cover: '.$adfo_detail->cover.'&lt;/p&gt;';
    }
    return $content;
}
add_filter( 'the_content', 'adfo_single_page_music');</pre>

<p>Salviamo il file. L'ultimo passaggio che dobbiamo effettuare per poter rendere attive le modifiche dobbiamo far rigenerare a wordpress i link. Possiamo farlo con un piccolo trucco. Andiamo in amministrazione dentro impostazioni > permalink e salviamo la configurazione senza modificare nulla. In questo modo wordpress ricalcolerà i link del sito e aggiungerà il nostro post type music.</p>

<p>Abbiamo finito, ora possiamo andare sulla pagina con l'elenco degli album musicali. Cliccando sul titolo si andrà sulla pagina con il dettaglio dell'articolo e in fondo troverete l'artista e l'immagine della cover dell'album che avrete inserito.</p>

</div>