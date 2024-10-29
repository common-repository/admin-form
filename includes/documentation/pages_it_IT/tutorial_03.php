<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>

<div class="af-content-table dbp-docs-content  js-id-dbp-content dbp-tutorial"  >
    <h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Docs </a><span class="dashicons dashicons-arrow-right-alt2"></span> Tutorials <span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('New post type and metadata','admin_form'); ?></h2>
    <p>In questo tutorial vedremo come creare un nuovo post type per un elenco di album musicali. Creeremo due nuovi campi: uno per l'autore dell'album, e uno per la locandina. </p>

    <h2 class="dbp-h2">Iniziamo creando un nuovo elenco</h2>
    
    <p>Clicchiamo sul plugin ADMIN FORM e poi sul bottone <b>"CREATE NEW FORM"</b>. Come nome scriviamo <b>my Music</b> e clicchiamo su  "Create a different types of content in your site (Post Type)".</p>
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_01_new.png"  class="dbp-tutorial-img">
    <div style="float:left; width:48%; margin-right:1%">
        <p>Una volta salvata la lista verrete rediretti nel <b>TAB FORM</b> e aggiungiamo i metadati.<br>
        <p>Andiamo in fondo alla form e clicchiamo sul bottone <b>New field</b><br>
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
    <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_03_new.png"  class="dbp-tutorial-img">
    <h2 class="dbp-h2">FRONTEND</h2>
    <br>
    <div style="float:left; width:48%;">
        <img src="<?php echo plugin_dir_url( __DIR__ ); ?>/assets/tutorial_03_06.png"  class="dbp-tutorial-img">
    </div>
    <div style="float:left; width:48%;">
        <p>Creiamo una nuova pagina di wordpress e inseriamo lo shortcode della lista che troviamo in alto a destra su ogni pagina di configurazione. </p>
    </div>

<p>Possiamo infine modificare il template andando sul Detailed view del tab frontend.<br>
<a href="">Guarda il vecchio tutorial della versione 1.6</a></p>

</div>