<?php
/**
 * Qui le classi per la gestione dei form. 
 * ADFO_field_param è Il table_params del form
 * dbpDs_table_param sono invece i parametri di un singola tabella di inserimento dei form
 */

 /**
  * ADFO_field_param è Il table_params del form
  */
namespace admin_form;

class ADFO_field_param extends ADFO_data_structures
{
    /** @var string $name Il nome del campo nella query */
    public $name;
    /** @var string $orgtable   */
    public $orgtable;
     /** @var string $table  */
     public $table;
    /** @var string $id  */
    public $id;
    /** @var string $label  */
    public $label;
    /** @var string $type Il type del campo nel db (int unsigned) */
    public $type;
    /** @var string $note  */
    public $note = "";
    /** @var string $field_name Il nome del campo nell'html es: edit_table[7][wpp_postmeta][meta_key][] */
    public $field_name;
    /** @var string $js_rif Il riferimento js  */
    public $js_rif;
    /** @var string $form_type Il tipo di campo  */
    public $form_type;
    /** @var object $options   */
    public $options;
    /** @var string $required   */
    public $required;
    /** @var string $custom_css_class   */
    public $custom_css_class = "";
    /** @var string $default_value   */
    public $default_value;
     /** @var string $js_script Javascript personalizzato  */
    public $js_script = "";
    /** @var string $custom_value Un campo per i valori aggiuntivi  */
    public $custom_value = "";
    /** @var string $edit_view HIDE|SHOW  */
    public $edit_view;
    /** @var string $post_types Solo per i form_type  post  */
    public $post_types;
    /** @var string $post_cats Solo per i form_type  post  */
    public $post_cats;
    /** @var string $user_roles Solo per i form_type  user  */
    public $user_roles;
    /** @var string $lookup_id Solo per i form_type lookup  */
    public $lookup_id;
    /** @var string $lookup_sel_val Solo per i form_type lookup  */
    public $lookup_sel_val;
    /** @var string $lookup_sel_txt Solo per i form_type lookup  */
    public $lookup_sel_txt;
    /** @var string $lookup_where Solo per i form_type lookup filtra le proposte  */
    public $lookup_where;
    /** @var string $range_min Solo per i form_type Range  */
    public $range_min;
    /** @var string $range_max Solo per i form_type Range  */
    public $range_max;
    /** @var string $range_step Solo per i form_type Range  */
    public $range_step;
    /** @var int $is_pri */
    public $is_pri;
    /** @var int $where_precompiled */
    public $where_precompiled = 0;
    /** @var int $order */
    public $order = 0;
    /** @var int $autocomplete Per i campi testo se far apparire i suggerimenti mentre si scrive */
    public $autocomplete = 0;
 /** @var string $autocomplete sql Viene utilizzato per filtrare i campi degli autocomplete tramite una query */
    public $ac_sql = "";
     /** @var string $custom_value_calc_when Quando deve essere rigenerato il campo calcolato EMPTY|EVERY_TIME */
    public $custom_value_calc_when = "EMPTY";
    /** @var int $is_multiple Se il campo accetta valori multipli oppure no (user, post lookup select?) */
    public $is_multiple = 0;
    public function __construct($array = "")
    {
        if (is_array($array)) {
            $this->set_from_array($array);
        }
        if (is_object($array)) {
            $this->set_from_array((array)$array);
        }
        if (empty($this->id)) {
            $this->id = 'id'.ADFO_fn::get_uniqid();
        }
    }
    
}

/**
 * sempre per dbp-form descrive i dati dei gruppi delle tabelle
 */
class  DbpDs_table_param extends ADFO_data_structures
{
     /** @var string $allow_create  SHOW|HIDE Mostra se è possibile non creare il record di una singola tabella */
    public $allow_create = "HIDE";
     /** @var string $show_title  SHOW|HIDE  */
    public $show_title = "SHOW";
     /** @var string $frame_style white|green|yellow|blue|red|purple|brown  */
    public $frame_style = "WHITE";
     /** @var string $title   */
    public $title = "";
     /** @var string $description   */
    public $description = "";
     /** @var string $module_type EDIT (creo la form con i campi modificabili) VIEW (mostro i dati)   */
    public $module_type = "EDIT";
     /** @var string $table_compiled   */
    public $table_compiled = "";
     /** @var string $table_status   */
    public $table_status = "";
    /** @var string $pri_name   */
    public $pri_name = "";
    /** @var string $pri_orgname   */
    public $pri_orgname = "";
    /** @var string $pri_value   */
    public $pri_value = "";
    /** @var string $count_form_block   */
    public $count_form_block = "";
    /** @var string $orgtable   */
    public $orgtable = "";
    /** @var string $table */
    public $table = "";
    /** @var string $precompiled_primary_id */
    public $precompiled_primary_id;
     /** @var numeric $order */
     public $order;
      /** @var string $table_options Aggiunge altre informazioni alla tabella. 
       * L'ho creato per i metadati. I parametri sono ['type'=>'METADATA','field_key'=>'meta_key', 'value_key'=>'ts_name','field_conn_id'=>'post_id', 'value_key'=>'[%wp_posts.ID]']
      */
      public $table_options;
    /**
     * Setta un frame_style casuale
     *
     * @return void
     */
    public function set_rand_frame_style() {
       $colors = array('WHITE','GREEN','YELLOW','BLUE','RED','PURPLE','BROWN');
       $this->frame_style = $colors[rand(0,count($colors)-1)];
    }
}