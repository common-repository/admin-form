<?php
/**
 * La struttura dei dati per la gestione degli elenchi.
 * 
 * 
 */
namespace admin_form;

class ADFO_list_setting 
{
    /** @var string $name Il nome del campo nella query */
    public  $name;
    /** @var string $orgname Il nome del campo */
    public  $orgname; 
    /** @var string $table Il nome della tabella nella query */
    public  $table;
     /** @var string $orgtable Il nome della tabella originale */
    public  $orgtable; 
     /** @var string $def; */
    public  $def; 
    /** @var string $db Il database da cui estrarre i dati */
    public  $db; 
    /** @var string $catalog */
    public  $catalog;
    /** @var int $max_length */
    public  $max_length; 
    /** @var int $length */
    public  $length; 
    /** @var int $charsetnr */
    public  $charsetnr; 
    /** @var string $flags */
    public  $flags;
    /** @var string $type è il tipo di campo del database */
    public  $type;
    /** @var string $decimals */
    public  $decimals;
    /** @var string $name_request */
    public  $name_request;
    /** 
     *@var string $title Il nome della colonna nella tabella da spampare
     */
    public  $title;
    /** @var string $toggle */
    public  $toggle;
    /** @var string $view è il tipo di campo che verrà visualizzato e scelto quindi dal select columns type */
    public  $view;
    /** @var string $custom_code */
    public  $custom_code; 
    /** @var string $order */
    public  $order;
    /** @var string $origin FIELD|CUSTOM  */
    public  $origin;
    /** @var string $searchable; */
    public  $searchable;
    /** @var string $mysql_name; `table`.`field` */
    public  $mysql_name;
    /** @var string $mysql_table; table */
    public  $mysql_table;
    /** @var string $width  // la classe che definisce la larghezza della colonna small|regular|large|extra-large */
    public  $width = 'small'; 
    /** @var string $align  // L'allineamento delle celle */
    public  $align = 'top-left'; 
    /** @var string $custom_param */
    public  $custom_param; 
    /** @var string $format_values */
    public  $format_values; 
    /** 
     * @param string $format_styles 
     */
    public  $format_styles; 
    /** 
     * @param int $lookup_id Se il campo è di tipo lookup
     */
    public  $lookup_id; 
    /** 
     * @param string lookup_sel_val Se il campo è di tipo lookup
     */
    public  $lookup_sel_val; 
    /** 
     * @param string lookup_sel_txt Se il campo è di tipo lookup
     */
    public  $lookup_sel_txt; 
    /** 
     * @param string $inherited Se il campo è stato modificato o viene modificato dalla form
     */
    public  $inherited;
    /** 
     * @param string $is_multiple se il campo accetta più valori
     * @since 2.0
     */
    public  $is_multiple = 0;

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            trigger_error('ADFO_list_setting: GET '.$property. " NOT EXISTS ", E_USER_WARNING);
        }
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }else {
            /*
            trigger_error('ADFO_list_setting: GET '.$property. " NOT EXISTS ", E_USER_WARNING);
            */
        }
    return $this;
    }

    /**
     * la funzione isset del php nelle variabili delle classi non può essere usato.
     * @param String $property
     */
    public function isset($property) {
        return  (property_exists($this, $property) && $this->$property != null && $this->$property != '');
    }

    /**
     * Setta le variabili a partire da un array
     *```php
     * (new ADFO_list_setting())->set_from_array($vars);
     *```
     * @param array $array
     * @return \ADFO_list_setting
     */
    public function set_from_array($array) {
        foreach ($array as $key=>$value) {
            $this->$key = $value;
        }
        return $this;
    }
    /**
     * Ritorna l'array per il salvataggio nel db
     *
     * @return array
     */
    public function get_for_saving_in_the_db() {
        $vars = get_object_vars($this);
        unset($vars['def']);
        unset($vars['db']);
        unset($vars['catalog']);
        unset($vars['max_length']);
        unset($vars['length']);
        unset($vars['charsetnr']);
        unset($vars['flags']);
        unset($vars['decimals']);
        if ($vars['view'] != 'LOOKUP') {
            unset($vars['lookup_id']);
            unset($vars['lookup_sel_val']);
            unset($vars['lookup_sel_txt']);
        }
        return $vars;
    }

    public function get_array() {
        $vars = get_object_vars($this);
        if ($vars['view'] != 'LOOKUP') {
            unset($vars['lookup_id']);
            unset($vars['lookup_sel_val']);
            unset($vars['lookup_sel_txt']);
        }
        return $vars;
    }
}


/**
  * dbpDs_list_delete La struttura per la rimozione di un campo
  * Qui si può aprire un mondo sulle condizioni per poter cancellare un campo, 
  * eventuali tabelle collegate ecc...
  */
class  DbpDs_list_delete_params extends ADFO_data_structures
{
    /** @var string $allow Se da questa view è permesso cancellare un record */
      public $allow;

    /** @var array $remove_tables_alias Se l'elenco delle tabelle che è ammesso rimuovere */
     public $remove_tables_alias = [];
     
    /** @var string $field_title Il campo da mostrare quando si chiede la conferma di rimozione */
    //public $field_title;
    /** @var string $soft_delete Il campo che gestisce il soft delete */
    //public $soft_delete_field;
    /** @var string $soft_delete Il campo che gestisce il soft delete */
    //public $soft_delete_value;
    /** @var string %sql_allow La query di preparazione per verificare se un campo si può rimuovere ? */

    public function __construct($array = ""){
        if (is_array($array)) {
            $this->set_from_array($array);
        }
        if (is_object($array)) {
            $this->set_from_array((array)$array);
        }
        $this->allow = false;
        if (is_array($this->remove_tables_alias) && count($this->remove_tables_alias) > 0 ) {
            foreach ($this->remove_tables_alias as $value) {
                if ($value == 1) {
                    $this->allow = true;
                }
            }
        } else {
            // se non impostato 
            $this->allow = true;
        }
    }
}