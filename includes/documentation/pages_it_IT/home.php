<?php 
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="af-content-table dbp-docs-content  js-id-dbp-content" >
    <h2><?php _e('ADMIN FORM document','admin_form'); ?></h2>
    <div class="dbp-help-p">
        Admin Form ti permette di creare delle raccolte di dati collegandoti direttamente al tuo database wordpress.

        Quando crei una nuova form puoi scegliere se collegarla ad una tabella o crearne una nuova. <br>
        Appena creata una nuova form apparirà un menù con vari tabs.

        <h3>FORM</h3>
        <p>Mostra come verranno modificati i campi della tabella.<br>Se la tabella è stata creata con il plugin, puoi premere il bottone "new field" per creare nuovi campi del database. <br>Cliccando sulla matita puoi modificare come verranno editati i singoli campi.</p>

        <h3>SETTINGS</h3>
        <p>Mostra le impostazioni del modulo: Quali dati devono essere estratti, chi può modificarli quanti dati per pagina ecc...</p>

        <h3>LIST VIEW FORMATTING</h3>
        <p>Come si visualizzano i dati nell'elenco dei dati e quindi anche nel frontend</p>

        <h3>FRONTEND</h3>
        <p>Come si visualizzano i dati nel frontend.</p>
        Da una lista viene generato uno shortcode per la visualizzazione del frontend.

    </div>

    <hr>

    <h3>Shortcodes</h3>
    <p>Per stampare la grafica di una lista puoi usare lo shortcode:</p>
    <p><b>[adfo_list id=list_id]</b> dove id è l'id della lista. Se vuoi visualizzare più liste all'interno della stessa pagina che derivano dalla stessa lista puoi impostare l'attributo prefix con un codice breve univoco tipo prefix="abc". 
    Se nel tab setting hai impostato dei filtri [%params.xxx] puoi passarli all'interno della lista per filtrare ulteriormente i risultati. Esempio:<br>
    [adfo_list id=list_id xxx=23]
    </p>
    <p><b>[adfo_tmpl]</b> Per eseguire il template engine personalizzato</p>

    <p><b>[adfo_single dbp_id=xxx id=xxx]</b> Mostra il dettaglio di un singolo record</p>
    
    <p><b>[adfo_total id=xxx]</b> Ritorna il totale dei record di una lista</p>


    <h3>Template Engine</h3>
        <div class="dbp-help-p">Puoi modificare i dati che visualizzi attraverso un template engine integrato<br>
        Il template engine integrato può essere usato sia per modificare i dati delle tabelle come ad esempio nei campi calcolati, sia per generare template personalizzati nelle liste.<br>È possibile usare le funzioni del template engine anche all'esterno del plugin inserendo il codice tra gli shortcode <b>[adfo_tmpl] {my code} [/adfo_tmpl]</b>
        <br><br>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>">Approfondisci</a>
    </div>

    <h3>Form Javascript</h3>
        <div class="dbp-help-p">Nella gestione dei moduli di inserimento puoi usare il javascript per gestire azioni speciali nei campi come far apparire o sparire un campo o validarne il contenuto.
        <br><br>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=js-controller-form") ?>">Approfondisci</a>
    </div>

    <h3>Hooks & filters</h3>
    <div class="dbp-help-p">Modifica il comportamento del plugin direttamente da codice.<br><br>
        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=hooks") ?>">Approfondisci</a>
    </div>

    <h3>PHP</h3>
    <div class="dbp-help-p">Sviluppa usando direttamente le funzioni del programma. <br>
    La classe <b>admin_form\ADFO</b> contiene tre funzioni principali per l'estrazione dei dati:
        <ul>
            <li>admin_form\ADFO::get_list(list_id); Mostra la visualizzazione del frontend</li>
            <li>$data = admin_form\ADFO::get_data(list_id); Restituisce l'array con i dati di una lista</li>
            <li>$row = admin_form\ADFO::get_detail(list_id, $record_id) Restituisce un determinato record in formato raw. Questo formato serve perché il risultato può essere modificato e risalvato attraverso la funzione admin_form\ADFO::save_data(...)</li>
        </ul> 
    <br>

        <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>">Approfondisci</a>
    </div>

    <h3>Tutorials</h3>
    <div class="dbp-help-p">
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_01") ?>">Related post</a><br>
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_02") ?>">Galleries</a>
    <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_03") ?>">New post type and metadata</a>
    </div>
</div>