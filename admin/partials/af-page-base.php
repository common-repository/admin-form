<?php
/**
 * Il template della pagina amministrativa
 * @var String $render_content Il file dentro partial da caricare 
 */
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="wrap">
   
    <div id="dbp_container" class="dbp-grid-container" style="display:none; position:fixed; width: inherit;">
        <div class="dbp-column-content">
            <?php require (dirname(__FILE__).$render_content); ?>
        </div>
        <div id="dbp_sidebar_popup" class="dbp-sidebar-popup">
            <div id="dbp_dbp_title" class="dbp-dbp-title">
                <div id="dbp_dbp_close" class="dbp-dbp-close" onclick="dbp_close_sidebar_popup()">&times;</div>
            </div>
            <div id="dbp_dbp_loader" ><div class="dbp-sidebar-loading"><div  class="dbp-spin-loader"></div></div></div>
            <div id="dbp_dbp_content" class="dbp-dbp-content"></div>
        </div>
    </div>
   
</div>
<?php require (dirname(__FILE__)."/../js/admin-form-footer-script.php");