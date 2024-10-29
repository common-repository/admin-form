<?php
/**
 * Gestisco gli sortcode
 * qui una guida chiara su come si scrivono: https://pagely.com/blog/creating-custom-shortcodes/

 */
namespace admin_form;


class  ADFO_init_shortcode {
    /**
     * Imposta gli shortcode
     */
    public function __construct() {
        add_shortcode('adfo_list', [$this, 'adfo_list']);
        add_shortcode('adfo_tmpl', [$this, 'adfo_tmpl']);
        add_shortcode('adfo_single', [$this, 'adfo_single']);
        add_shortcode('adfo_total', [$this, 'adfo_total']);
    }

    /**
     * Mostra una lista frontend id=
     */
    public function adfo_list($attrs = []) {
        if (is_admin()) return;
        ADFO_fn::require_init();
        $prefix = "";
        if (isset($attrs['prefix']) && $attrs['prefix'] != "") {
            $prefix = $attrs['prefix'];
        }
        if ($attrs['id'] > 0) {
            $post_id = $attrs['id'];
            unset($attrs['id']);
        	return ADFO::get_list($post_id, false, $attrs, $prefix);
        }
    }

    /**
     * Mostra una pagina singola in frontend dbp_id= id=
     * @since 1.8.0
     */
    public function adfo_single($attrs = []) {
        //if (is_admin()) return;
        ADFO_fn::require_init();
        if ($attrs['dbp_id'] > 0 && isset($attrs['id'])) {
            $post_id = $attrs['dbp_id'];
            $id = $attrs['id'];
        	return ADFO::get_single($post_id, $id);
        }
    }
    

    /**
     * Mostra il totale degli elementi di una lista
     * @since 1.8.0
     */
    public function adfo_total($attrs = []) {
        //if (is_admin()) return;
        ADFO_fn::require_init();
        if (isset($attrs['id']) && $attrs['id'] > 0) {
        	return ADFO::get_total($attrs['id']);
        }
    }
    
    
    /**
     * Esegue il codice di pinacode
     * attrs htmlentities è true di default e sostituisce gli htmlentities in testo, false non lo converte
     */
    public function adfo_tmpl($attrs = [], $content = "") {
        if (is_admin()) return;
        ADFO_fn::require_init();
        // TODO se stampa gli errori oppure no
        $attrs = shortcode_atts(array(
            'debug' => 0,
            'htmlentities' => 0
        ), $attrs);
        if ($attrs['htmlentities'] == 0) {
            $content = str_replace(['&ldquo;','&rdquo;', '&bdquo;','&lsquo;','&rsquo;','&sbquo;'], ['"','"','"',"'","'","'"], $content);
            $content = str_replace(['&#8221;','&#8220;', '&#8222;','&#8216;','&#8217;','&#8218;'], ['"','"','"',"'","'","'"], $content);
            $content = str_replace(['\u201C;','\u201D', '\u201E','\u201E','\u2019','\u201A'], ['"','"','"',"'","'","'"], $content);
            $content = str_replace(['&Prime;','&#8243;', '\u2033','&prime;','&#8242;','\u2032'], ['"','"','"',"'","'","'"], $content);
            $content = str_replace(["<br />",'<br>'],' ',$content);
           $content = html_entity_decode($content);
        }
        if ($content != "") {
            $result = PinaCode::execute_shortcode($content);
           // PcErrors::echo();
        }
        if ($attrs['debug'] > 0) {
     
            if ($attrs['debug'] == 2) {
                $show = "error warning notice info debug";
            }
            if ($attrs['debug'] == 1) {
                $show = "error warning ";
            }
            $result .= PcErrors::get_html($show);
        }
        return $result;
    }
}

new ADFO_init_shortcode();