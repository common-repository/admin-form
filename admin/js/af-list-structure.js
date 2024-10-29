jQuery(document).ready(function ($) {
    //aggiungo la possibilità di fare il sort sulla creazione del nuovo db
    jQuery('.js-dragable-table').sortable({
        items: '.js-dragable-tr',
        opacity: 0.5,
        cursor: 'move',
        axis: 'y',
        handle: ".js-dragable-handle"
    });

    jQuery('.js-toggle-row').change();
    jQuery('.js-type-fields').change();
    jQuery('.js-structure-toggle').click();
    jQuery('.js-toggle-inherited-label').each(function() {
        toggle_inherited(this);
    });

    jQuery('.js-checkbox-post-link').change(function() {
        if (jQuery(this).is(':checked')) {
            jQuery(this).parent().find('.js-input-parasm-custom').val('1');
        } else {
            jQuery(this).parent().find('.js-input-parasm-custom').val('0');
        }
    })
});

function dbp_list_structure_query_apply() {
    dbp_submit_list_structure();
}

/**
 * Il bottone per creare nuove righe della tabelle per la creazione delle tabelle mysql
 */
 function dbp_list_structure_add_row (el) {
    $original =  jQuery('#clone_master');
    $clone = $original.clone(true);
    jQuery('.js-dragable-table').append($clone);
    $clone.css('display','block');
    $clone.removeAttr('id').addClass('js-dragable-tr dbp-structure-card js-dbp-structure-card');
    jQuery('.js-dragable-table').sortable('refresh');
    $clone.find('.js-structure-toggle').click();
 }
/**
 * Invia la form, ma prima imposta i campi da riordinare
 */
 function dbp_submit_list_structure() {
    var count_list = 1;
    jQuery('#clone_master').remove();
    jQuery('.js-dragable-order').each(function() {
        jQuery(this).val(count_list);
        count_list++;
    });
    select_toggle_enabled($card.find('.js-toggle-row'));
    //jQuery('.js-toggle-row').prop('disabled',false);
    jQuery('#list_structure').submit();
 }

 /**
  * Elimina una riga
  * @param DOM el 
  */
function dbp_list_structure_delete_row(el) {
    jQuery(el).parents('.js-dbp-structure-card').remove();
}

// 
function dbp_change_toggle(el) {
    $card = jQuery(el).parents('.js-dbp-structure-card');
    $select =jQuery(el);
    $content = $card.find('.js-structure-content');
    if ($select.val() == 'SHOW') {
        $card.find('.js-structure-toggle').show();
        $card.removeClass('dbp-form-hide-field');
        $content.slideDown('fast');
    } else {
        if (!$card.find('.js-toggle-inherited-label input').is(':checked')) {
            $card.find('.js-structure-toggle').hide();
        }
        $card.addClass('dbp-form-hide-field');
        $content.slideUp('fast');
    }
}

/**
 * Vede se è un CUSTOM TYPE OPPURE NO
 * @param DOM el 
 */
function dbp_change_custom_type(el) {
   // console.log ('dbp_change_custom_type');
    let $tr = jQuery(el).parents('.js-dbp-structure-card');
    $tr.find('.js-checkbox-post-link').css('display', 'none');
    $tr.find('.js-input-parasm-custom').css('display', 'inline-block');
    $tr.find('.js-dbp-params-column').css('display','none');
    $tr.find('.js-dbp-params-column .dbp-form-label').css('display','none');
    $tr.find('.js-dbp-param-post-id').css('display','none');
    $tr.find('.js-dbp-params-checkbox').css('display','none');
    $tr.find('.js-dbp-params-select').css('display','none');
    $tr.find('.js-dbp-params-textarea-select').css('display','none');
    $tr.find('.js-dbp-params-textarea-select-div').css('display','none');
   // console.log ($tr.find('.js-dbp-param-post-id'));
   
    if (el._first_change === 'no') {
        $tr.find('.js-input-parasm-custom').val('');
    }
    el._first_change = 'no';
    $tr.find('.js-lookup-params').css('display','none');
    if (jQuery(el).val() == "CUSTOM") {
        jQuery(el).css('display', 'none');
        $tr.find('.js-type-custom-code').css('display', 'inline-block');
        $tr.find('.js-textarea-btn-cancel').css('display', 'inline-block');
        $tr.find('.js_show_variables_textarea').css('display', 'block');
    } else {
        jQuery(el).css('display', 'block');
        $tr.find('.js-type-custom-code').css('display', 'none');
        $tr.find('.js-textarea-btn-cancel').css('display', 'none');
        $tr.find('.js_show_variables_textarea').css('display', 'none');
        if (jQuery(el).val() == 'DATE' || jQuery(el).val() == 'DATETIME') {
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-input-parasm-custom').focus();
            $tr.find('.js-dbp-params-date').css('display','inline-block');
        }
        if ( jQuery(el).val() == 'LINK') {
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-input-parasm-custom').focus();
            $tr.find('.js-dbp-params-link').css('display','inline-block');
        }
        if ( jQuery(el).val() == 'COLUMN_CHECKBOX') {
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-input-parasm-custom').focus();
            $tr.find('.js-dbp-params-checkbox').css('display','inline-block');
        }
        if ( jQuery(el).val() == 'COLUMN_SELECT') {
            $tr.find('.js-dbp-params-textarea-select-div').css('display','block');
            $tr.find('.js-dbp-params-textarea-select').css('display','block');
            $tr.find('.js-input-parasm-custom').css('display', 'none');
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-dbp-params-textarea-select').focus();
            $tr.find('.js-dbp-params-select').css('display','inline-block');
        }
        if ( jQuery(el).val() == 'USER') {
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-input-parasm-custom').focus();
            $tr.find('.js-dbp-params-user').css('display','inline-block');
            $tr.find('.js-checkbox-post-link').css('display', 'inline-block');
            $tr.find('.js-input-parasm-custom').css('display', 'none');
            if ($tr.find('.js-input-parasm-custom').val() === '1') {
                $tr.find('.js-checkbox-post-link').prop('checked', true);
            } else {
                $tr.find('.js-checkbox-post-link').prop('checked', false);
            }
        }
        if ( jQuery(el).val() == 'POST') {
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-input-parasm-custom').focus();
            $tr.find('.js-dbp-params-post').css('display','inline-block');
            $tr.find('.js-checkbox-post-link').css('display', 'inline-block');
            $tr.find('.js-input-parasm-custom').css('display', 'none');
            if ($tr.find('.js-input-parasm-custom').val() === '1') {
                $tr.find('.js-checkbox-post-link').prop('checked', true);
            } else {
                $tr.find('.js-checkbox-post-link').prop('checked', false);
            }
        }
        if ( jQuery(el).val() == 'PERMALINK') {
            $tr.find('.js-dbp-params-column').css('display','none');
            $tr.find('.js-dbp-param-post-id').css('display','block');
            $tr.find('.js-input-parasm-custom').val($tr.find('.js-select-permalink-postid').val());
        }
        if (jQuery(el).val() == 'TEXT') {
            $tr.find('.js-dbp-params-column').css('display','block');
            $tr.find('.js-input-parasm-custom').focus();
            $tr.find('.js-dbp-params-text').css('display','inline-block');
        }
        if (jQuery(el).val() == 'LOOKUP') {
            $tr.find('.js-lookup-params').css('display','block');
          // $tr.find('.js-select-fields-lookup').change();
        }
    }
}

/**
 * Mostra/Nasconde gli attributi di una colonna
 */
function dbp_structure_toggle(el) {
    let $tr = jQuery(el).parents('.js-dbp-structure-card');
    let $box = $tr.find('.js-structure-content');
    if ($box.css('display') == "none") {
      $box.css('display','block');
    } else {
      $box.css('display','none');
    }
}

/**
 * DA custom type a un tipo predefinito
 * @param DOM el 
 */
function dbp_custom_cancel(el) {
    let $td = jQuery(el).parents('.js-form-row-custom-field');
    let $tr = jQuery(el).parents('.js-dbp-structure-card');
    let def = $tr.find('.js-type-td').data('type');
    if (def == 'CUSTOM') {
      def = "";
    }
    $td.find('.js-type-fields').css('display', 'block').val(def);
    $td.find('.js-type-custom-code').css('display', 'none');
    $td.find('.js-textarea-btn-cancel').css('display', 'none');
    $td.find('.js_show_variables_textarea').css('display', 'none');
}


/**
 * Lookup
 */
 function dbp_list_change_lookup_id(el) {
    $box = jQuery(el).parents('.js-lookup-params');
    table_rif = $box.find('.js-select-fields-lookup').val();
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : ajaxurl,
      data : {'page':'admin_form','action':'af_get_table_columns','table_rif':table_rif, 'rif':$box.prop('id'),'searchable':1},
      success: function(resp) {
         $box = jQuery('#'+resp.rif);
         $selv = $box.find('.js-lookup-select-value').first();
         $selt = $box.find('.js-lookup-select-text').first();
         $selt.parent().find('.js-lookup-warning').remove();
         if (resp.pri == "") {
            $selv.val('');
            $selt.empty().css('display','none');
            $selt.parent().append('<div class="dbp-alert-warning js-lookup-warning" style="margin:0">Questa tabella non ha una chiave primaria valida!</div>');
         } else {
         
            jQuery($selv).val(resp.pri);
            jQuery($selt).empty().css('display','block');
                
            for (x in resp.list) {
                jQuery($selt).append(jQuery('<option></option>').prop('value', resp.list[x]).text(resp.list[x]));
            }
            jQuery($selt).val(resp.list[Object.keys(resp.list)[0]]);
         }
      }
    });
 }



/**
 * Testa una formula rispetto alla tabella
 * copiate da admin\js\af-list-form.js
 * @param {DOM} el 
 */
 function click_af_test_formula(el) {
    if (jQuery(el).hasClass('disabled')) return;
    jQuery(el).addClass('disabled');
    var formula = jQuery(el).parents('.js-calculated-field-block').find('textarea').val();
    var test_row = jQuery(el).parents('.js-calculated-field-block').find('.js-choose-test-row').val();
    dbp_open_sidebar_popup('test_formula');
    jQuery('#dbp_dbp_title > .dbp-edit-btns').remove();
    
    jQuery('#dbp_dbp_title').append('<div class="dbp-edit-btns"><h3>Test Formula</h3></div>');
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : ajaxurl,
      data : {'page':'admin_form','action':'af_test_formula','dbp_id':jQuery('#dbp_id_list').val(),'formula':formula, 'row':test_row},
      success: function(response) {
         dbp_close_sidebar_loading();
         // jQuery('#dbp_dbp_title > .dbp-edit-btns').remove(); // Titolo e bottoni
         $block1 = jQuery('<div class="dbp-view-single-box dbp-form-box-white af-content-margin "></div>');
         $formula = jQuery('<div class="dbp-xmp"></div>').text(response.formula);
         $typeof = jQuery('<div class="dbp-xmp"></div>').text(response.typeof);
         $row_formula = jQuery('<div class="dbp-row-details"></div>');
         $row_response = jQuery('<div class="dbp-row-details"></div>');
         $row_typeof = jQuery('<div class="dbp-row-details"></div>');
         if (typeof(response.response) == "object") {
            $resBlock = jQuery('<div class="dbp-sidebar-test-formula-params"></div>');
            test_formula_show_data('', response.response, $resBlock);
            $row_response.append('<div class="dbp-label-detail">Response:</div>').append($resBlock);
            response.warning.push('The result must be a text string and not an object or array');
         } else {
            $response = jQuery('<div class="dbp-xmp"></div>').text(response.response);
            $row_response.append('<div class="dbp-label-detail">Response:</div>').append($response);
         }

         $row_formula.append('<div class="dbp-label-detail">Test Formula:</div>').append($formula);
         $row_typeof.append('<div class="dbp-label-detail">Type of:</div>').append($typeof);
         $block1.append($row_response).append($row_typeof).append($row_formula);

         jQuery('#dbp_dbp_content').append($block1 );
        
         if (response.error.length > 0) {
            $resBlockError = jQuery('<div class="af-content-margin dbp-alert-sql-error"></div>');
            for (x in response.error) {
                $resBlockError.append ('<p>'+response.error[x]+'</p>');
            }
            jQuery('#dbp_dbp_content').append($resBlockError);
         }
         if (response.warning.length > 0) {
            $resBlockWarning = jQuery('<div class="af-content-margin dbp-alert-warning"></div>');
            for (x in response.warning) {
                $resBlockWarning.append ('<p>'+response.warning[x]+'</p>');
            }
            jQuery('#dbp_dbp_content').append($resBlockWarning);
         }

         if (response.notice.length > 0) {
            $resBlockNotice = jQuery('<div class="af-content-margin dbp-alert-info"></div>');
            for (x in response.notice) {
                $resBlockNotice.append ('<p>'+response.notice[x]+'</p>');
            }
            jQuery('#dbp_dbp_content').append($resBlockNotice);
         }
  
         $block2 = jQuery('<div class="dbp-sidebar-test-formula-params af-content-margin"></div>');
         test_formula_show_data('', response.pinacode_data, $block2);
         jQuery('#dbp_dbp_content').append('<h3 class="af-content-margin">Template Engine Variables</h3>').append($block2);
        
         jQuery('.js-test-formula').removeClass('disabled');
      }, error : function(xhr, status, error) {
         dbp_close_sidebar_loading();
         $resBlockNotice = jQuery('<div class="af-content-margin dbp-alert-sql-error"></div>');
         $resBlockNotice.append ('<p>there was an unexpected error</p>');
         jQuery('#dbp_dbp_content').append($resBlockNotice);
         jQuery('.js-test-formula').removeClass('disabled');
      }
    });
}

function test_formula_show_data(left_name, data, $block) {
    for (x in data) {
      if (typeof(data[x]) == 'object' || typeof(data[x]) == 'array') {
         if (left_name == "") {
            new_left_name = x;
         } else {
            new_left_name = left_name+"."+x;
         }
         test_formula_show_data(new_left_name,  data[x], $block);
         
      } else if (typeof(data[x]) == 'string' || typeof(data[x]) == 'number' || typeof(data[x]) == 'boolean') {
         $var = jQuery('<div class="dbp-xmp"></div>').text(data[x]+" "+typeof(data[x]));
         $row = jQuery('<div class="dbp-row-details"></div>');
         if (left_name == "") {
            var_name = x;
         } else {
            var_name = left_name+"."+x;
         }
         $row.append('<div class="dbp-label-detail">[%'+var_name+']</div>').append($var);
         $block.append($row);
      }
    }
  
}

/**
 * 
 * @param {DOM} el il label che include il checkbox 
 */
function toggle_inherited(el) {
    $el = jQuery(el).find('input');
    $card = jQuery(el).parents('.js-dbp-structure-card');
    if ($el.is(':checked')) {
        $card.find('.js-structure-toggle').css('color','#d09034');
        $card.find('.js-structure-single-block').slideUp();
        select_toggle_disabled($card.find('.js-toggle-row'));
        //$card.find('.js-toggle-row').prop('disabled',true);
    } else {
        $card.find('.js-structure-toggle').css('color',null);
        $card.find('.js-structure-single-block').slideDown();
       // $card.find('.js-toggle-row').prop('disabled',false);
       select_toggle_enabled($card.find('.js-toggle-row'));
    }
}

function select_toggle_disabled(el) {
    jQuery(el).css('display', 'none');
    $card = jQuery(el).parents('.js-dbp-structure-card');
    $card.find('.js-toggle-disabled-text').remove();
    $dis_leg = jQuery('<div class="js-toggle-disabled-text" title="click the pencil and uncheck  Keeps settings aligned...">'+jQuery(el).val()+'</div>');
    jQuery(el).after($dis_leg);
}

function select_toggle_enabled(el) {
    jQuery(el).css('display', 'block');
    $card = jQuery(el).parents('.js-dbp-structure-card');
    $card.find('.js-toggle-disabled-text').remove();
}



function select_post_id_attr(el) {
    // prendo il valore dell'elemento e lo salvo in js-input-parasm-custom dbp-input
    $el = jQuery(el);
    $el.parents('.js-dbp-structure-card').find('.js-input-parasm-custom').val($el.val());
}

