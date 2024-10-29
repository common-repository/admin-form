<?php
/**
 * La sezione documentale
 * @internal
 */
namespace admin_form;
if (!defined('WPINC')) die;
class  ADFO_docs_admin 
{
	/**
	 * Viene caricato alla visualizzazione della pagina
     */
    function controller() {
        wp_enqueue_style( 'admin-form-css' , plugin_dir_url( __FILE__ ) . 'css/admin-form.css',[],ADFO_VERSION);
        wp_register_script( 'admin-form-all', plugin_dir_url( __FILE__ ) . 'js/admin-form-all.js',[],ADFO_VERSION);
		wp_add_inline_script( 'admin-form-all', 'dbp_cookiepath = "'.esc_url(COOKIEPATH).'";'."\n  var dbp_cookiedomain =\"".esc_url(COOKIE_DOMAIN).'";', 'before' );
		wp_enqueue_script( 'admin-form-all' );
          // $dbp = new ADFO_fn();
		ADFO_fn::require_init();
        $section =  ADFO_fn::get_request('section', 'home');
        $base_dir = dirname( __FILE__ )."/../includes/documentation/pages_".get_user_locale();
		if (!is_dir($base_dir)) {
			$base_dir = dirname( __FILE__ )."/../includes/documentation/pages_en_GB";
		}
        switch ($section) {
            case 'hooks' :
                $render_content = $base_dir."/hooks.php";
				break;
            case 'code-php' :
                $render_content = $base_dir."/code-php.php";
				break;
			case 'pinacode' :
                $render_content = $base_dir."/pinacode.php";
				break;
			case 'js-controller-form' :
                $render_content = $base_dir."/js-controller-form.php";
				break;
			case 'tutorial_01' :
                $render_content = $base_dir."/tutorial_01.php";
				break;
			case 'tutorial_02' :
                $render_content = $base_dir."/tutorial_02.php";
				break;
			case 'tutorial_03' :
                $render_content = $base_dir."/tutorial_03.php";
				break;
			case 'tutorial_03_old' :
                $render_content = $base_dir."/tutorial_03_old.php";
				break;
            case 'tutorial_04' :
                $render_content = $base_dir."/tutorial_04.php";
                break;
            case 'test_admin_form' :
                $render_content = $base_dir."/test_admin_form.php";
                break;
            default :
            $render_content = $base_dir."/home.php";
            break;
        }
        require(dirname( __FILE__ ) . "/partials/af-page-base-docs.php");
    }
}