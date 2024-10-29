<?php
/**
 * Admin form - Manages database tables in the Admin panel
 *
 * @package          Admin_form
 *
 * @wordpress-plugin
 * Plugin Name:       Admin form
 * Description:       ADFO - Admin form is a tool designed to manage administration form.
 * Version:           1.9.1
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Giulio Pandolfelli
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 	  admin_form
 * Domain Path: 	  /languages
 */
namespace admin_form;

if (!defined('WPINC')) die;
define('ADFO_VERSION', '1.9.1');
//define('ADFO_VERSION', rand());
define('ADFO_DIR',   __DIR__ ."/");


require_once(ADFO_DIR . "includes/af-loader.php");
require_once(ADFO_DIR . "includes/af-functions.php");
require_once(ADFO_DIR . "includes/af-list-functions.php");
require_once(ADFO_DIR . "includes/af-functions-items-setting.php");
require_once(ADFO_DIR . "includes/af-facade.php");
require_once(ADFO_DIR . "includes/af-shortcode.php");
require_once(ADFO_DIR . "includes/pinacode/pinacode-init.php");
require_once(ADFO_DIR . "includes/af-html-search-frontend.php");

/**
 * Activate the plugin.
 */
if (!function_exists('adfo_activate')) {
    function adfo_activate($h) { 
        update_option( '_af_activete_info', ['date'=>date('Y-m-d'), 'voted'=>'no'], false );
    }
    \register_activation_hook( __FILE__, '\admin_form\adfo_activate' );
}
