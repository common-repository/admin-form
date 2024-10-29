<?php
/**
 * Il template della pagina amministrativa
 * 
 * @var String $render_content Il file dentro partial da caricare 
 */
namespace admin_form;
if (!defined('WPINC')) die;
?>
<div class="wrap">
   
    <div id="dbp_container" class="dbp-grid-container" style="display:none; position:fixed; width: inherit;  grid-template-columns: 1fr 320px;">
        <div class="dbp-column-content">
            <?php require ($render_content); ?>
        </div>
        <div class="dbp-column-tables-list" id="dbp_column_sidebar">
           

            <div >
                
                <div id="dbp_documentation_box" class="js-sidebar-block"  data-open="no_admin_form2">
                    <h3 class="js-sidebar-title dbp-sidebar-title">Documentation</h3>
                    
                    <div class="af-content-margin">
                        <ul>
                            <li>
                                <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>" class="js-simple-link">Home</a> 
                            </li>
                            <li>
                                <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=js-controller-form") ?>" class="js-simple-link">Form javascript</a> 
                            </li>
                            <li>
                                <a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=pinacode") ?>" class="js-simple-link">Template Engine</a>
                            </li>
                            <li><a class="js-simple-link" href="<?php echo admin_url("admin.php?page=admin_form_docs&section=hooks") ?>">Hooks & filters</a></li>
                            <li><a class="js-simple-link" href="<?php echo admin_url("admin.php?page=admin_form_docs&section=code-php") ?>">PHP</a></li>
                        </ul>
                        <h3>Tutorials</h3>
                        <ul>
                            <li><a class="js-simple-link" href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_01") ?>">Related post</a></li>
                            <li><a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_02") ?>">Galleries</a></li>
                            <li><a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_03") ?>">New post type and metadata</a></li>
                            <li><a href="<?php echo admin_url("admin.php?page=admin_form_docs&section=tutorial_04") ?>">Multiple fields</a></li>
                        </ul>
                        <h3>Testing</h3>
                        <ul>
                            <li><a class="js-simple-link" href="<?php echo admin_url("admin.php?page=admin_form_docs&section=test_admin_form") ?>">Testing admin form</a></li>
                        </ul>

                    </div>

                </div>
            </div>
        </div>
       
    </div>
   
</div>
<?php require (dirname(__FILE__)."/../js/admin-form-footer-script.php");