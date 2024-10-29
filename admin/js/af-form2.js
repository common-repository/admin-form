

/**
 * disegna la query 
 * $div_content Il div in cui inserire la form
 * response.edit_ids ?
 * I dati vengono generati da af-loader.php -> af_edit_details_v2
 */

function dbp_build_form($div_content, response) {
    $form = gp_form();
    count_color = 0;
    let class_box = (Object.keys(response.items).length > 1) ? "dbp-view-multi-box" : "dbp-view-single-box";
    // aggiungo gli id come nella riga nel form
    //TODO DA RIATTIVARE
    //$form.data('edit_ids', response.edit_ids);
    let color_list = ['white', 'green','yellow','blue','red','purple', 'brown'];
    for (key_item in response.items) {
        for (x in response.items[key_item]) {
            let dtbo = response.table_options[key_item][x];
            if (dtbo != null && dtbo.module_type == "HIDE") continue;
            let  print_color_list = color_list[count_color%(color_list.length)];
            if ( dtbo != null &&  typeof dtbo.frame_style != 'undefined') {
                print_color_list = dtbo.frame_style.toLowerCase();
            } 
            $box = jQuery('<div class="'+class_box+' dbp-form-box-'+print_color_list+' js-dbp-form-box" id="dbp_form_table_name_'+dtbo.table+'"></div>');
            count_color++;
          
            if (Object.keys(response.items[key_item]).length >= 1) {
                if (( dtbo != null &&  typeof dtbo.show_title != 'undefined' && dtbo.show_title == 'SHOW') || ( dtbo == null || typeof dtbo.show_title == 'undefined')) {
                    let text_title = '';
                    let add_class_title = '';
                    if ( dtbo != null &&  typeof dtbo.title != 'undefined' &&  dtbo.title != '') {
                        text_title = dtbo.title;
                        add_class_title = ' for-box-title-big';
                    } else {
                        add_text = '';
                        if (dtbo.table_status == 'CLOSE') {
                            add_text = 'status: CLOSE (No changes to the table are allowed)';
                        } else {
                          //  add_text = 'For subsequent forms, you can retrieve using shortcodes. Es: <b>[%'+dtbo.table+'.'+dtbo.pri_orgname+']</b> ';
                        }
                        text_title = '<h5>'+dtbo.table+'</h5><span>' + add_text + '</span>';
                    }
                    $box.append('<div class="for-box-title'+add_class_title+'">'+text_title+'</div>');
                }
                if (typeof dtbo != 'undefined' &&  typeof dtbo.description != 'undefined' && dtbo.description != "") {
                    $descr = jQuery('<div class="dbp-form-box-descr"></div>').html(dtbo.description );
                    $box.append($descr);  
                }
            }

            if  (response.items[key_item][x][dtbo.pri_name] > 0) {
                dtbo.allow_create = 'HIDE';
            }
            if (dtbo.pri_name == "") {
                $box.append('<div class="dbp-alert-gray" style="margin-top:0; margin-right:.4rem">I have not found the primary key for the following data </div>');
            } else if (dtbo.table_compiled != "") {
                if (typeof dtbo != 'undefined' &&  dtbo.allow_create == 'SHOW' && dtbo.count_form_block != 1) {
                    $box.append('<div class="dbp-alert-warning js-dbp-alert-compiled" ><input type="checkbox" name="'+dtbo.table_compiled+'" checked="checked" value="1" class="js-dbp-cb-compiled"> Do not create a new record for this form (This is to avoid records without data)</div>');
                    $box.on('dbp_change_something', function() {
                        jQuery(this).find('.js-dbp-cb-compiled').prop('checked', false).change();
                    });
                    $box.find('.js-dbp-cb-compiled').change(function() {
                        if (jQuery(this).is(':checked')){
                            jQuery(this).parent().removeClass('dbp-alert-gray').addClass('dbp-alert-warning');
                        } else {
                            jQuery(this).parent().removeClass('dbp-alert-warning').addClass('dbp-alert-gray');
                        }
                    });
                }
            }
            
            //
            for (y in response.items[key_item][x]) {
                val =  response.items[key_item][x][y];
                
                if (dtbo.pri_name && dtbo.table_status != 'CLOSE' && ((dtbo != null && dtbo.module_type == "EDIT") || dtbo == null || (dtbo != null && dtbo.module_type == null)) ) {
                    $box.append(gp_form_field_container( response.params[x][y]['label'], response.params[x][y]['field_name'], val,  response.params[x][y]['form_type'], response.params[x][y], dtbo.count_form_block ));
                } else if (response.params[x][y]['form_type'] != "HIDDEN" && response.params[x][y]['edit_view'] != "HIDE") {
                    if (response.items[key_item][x][y] == "")  continue;
                    $view_data = jQuery('<div class="dbp-row-details"><span class="dbp-label-detail">'+y+':</span><div class="dbp-xmp">'+'</div></div>');
                    if (typeof response.items[key_item][x][y] === 'object' || typeof response.items[key_item][x][y] === 'array') {
                        $view_data.find('.dbp-xmp').text(JSON.stringify(response.items[key_item][x][y]));
                    } else {
                        $view_data.find('.dbp-xmp').text(response.items[key_item][x][y]);
                    }
                    $box.append($view_data);
                    $field = jQuery('<input type="hidden" name="'+response.params[x][y]['field_name']+'">');
                    if (val == "" && response.params[x][y].default_value !== "") {
                        $field.val(response.params[x][y].default_value);
                    } else {
                        $field.val(val);
                    }
                    $box.append($field);
                }
            }
            if (response.params[x]['_dbp_alias_table_']) {
                $t_alias = response.params[x]['_dbp_alias_table_'];
                $box.append(gp_form_field_container($t_alias['name'], $t_alias['field_name'], '', $t_alias['form_type'], $t_alias, dtbo.count_form_block));
            }
            $form.append($box);
        }
      
    }
    $div_content.append($form);
    jQuery('.js-dbp-fn-set').each(function() {
        let __custom_fn = jQuery(this).data('ADFO_fn');
        dbp_exec_fn(__custom_fn, this, 'start');
    })
    gp_form_add_editor($form);
    return $form;
}

/**
 * Js build form
 */
function gp_form() {
    return jQuery('<form class="dbp-form-edit-row" id="dbp_edit_details" enctype="multipart/form-data"></form>');
}

function gp_form_field_container(label, field_name, val, type, param, count_block) {
   
    if ((type == "POST" || type == "USER" || type == "LOOKUP" || type == "MEDIA_GALLERY") && param.is_multiple == 1) {
        //l'input dei post è un autocomplete speciale!
        if (typeof(param) == 'object') {
            if (!param.autocomplete_id) {
                param.autocomplete_id = dbp_uniqid();
            }
        }
    }
    console.log ("LABEL "+label);
    console.log ("VAL :");
    console.log (val);
    if (typeof val == 'object' && param.is_multiple == 1) {
        // campi multipli
        let new_val = val;
        if (type == "POST" || type == "USER" || type == "LOOKUP") {
            new_val = {'id': val.id[0], 'label': val.label[0]};
        } else if (type == "MEDIA_GALLERY") {
            new_val = {'id': val.id[0], 'url': val.url[0], 'link': val.link[0], 'title': val.title[0]};
        }
        $el = gp_form_field(label, field_name, new_val, type, param, count_block);
    } else {
        $el = gp_form_field(label, field_name, val, type, param, count_block);
    }
    if ((type == "POST" || type == "USER" || type == "LOOKUP" || type == "MEDIA_GALLERY") && param.is_multiple == 1) {
        $el_container = jQuery('<div class="dbp-autocomplete-container js-container-multiple-fields" ></div>');
        $el_container_input = jQuery('<div class="dbp-autocomplete-container_input"></div>');
        $el_container_input.append($el);
        $el_container.append($el_container_input);
        // ADD BUTTON (ADD ROW)
        $add_btn = jQuery('<div class="dbp-btn-autocomplete-add"><span>ADD ROW</span></div>');
        $el_container.append($add_btn);
        // label, field_name, val, type, param, count_block
        $add_btn.data('dbp-autocomplete-field_name', field_name);
        $add_btn.data('dbp-autocomplete-val', val);
        $add_btn.data('dbp-autocomplete-type', type);
      
        // genera un nuovo id 
        $add_btn.data('dbp-autocomplete-param', original_param);
        $add_btn.data('dbp-autocomplete-count_block', count_block);
        $add_btn.data('dbp-autocomplete-target', $el_container_input);
        $add_btn.click(function(e) {
            current_target = jQuery(e.target);
            field_name = jQuery(this).data('dbp-autocomplete-field_name');
            val = '';
            type = jQuery(this).data('dbp-autocomplete-type');
            param = jQuery(this).data('dbp-autocomplete-param');
            $target = jQuery(this).data('dbp-autocomplete-target');
            count_block = jQuery(this).data('dbp-autocomplete-count_block');
            param['id'] = 'id' + dbp_uniqid(); 
            $new_field = gp_form_field('', field_name, val, type, param, count_block);
            // trovo il js-dbp-form-box contenitore
            $target.append($new_field);
        });
        // aggiungo i nuovi valori
        if (type == "MEDIA_GALLERY") {
            if (typeof val == 'object') {
                // TODO gestisco il media gallery
                // se esiste val.id && val.link && val.title && val.url
                if (typeof val.id != 'undefined' && typeof val.link != 'undefined' && typeof val.title != 'undefined' && typeof val.url != 'undefined') {
                    for (i=1; i<val.id.length; i++) {
                        param['id'] = 'id' + dbp_uniqid(); 
                        $new_field = gp_form_field('', field_name, {'id': val.id[i], 'url': val.url[i], 'link': val.link[i], 'title': val.title[i]}, type, param, count_block);
                        $el_container_input.append($new_field);
                    }
                }
            }
        } else  if (typeof val == 'object') {
            for (i=1; i<val.id.length; i++) {
                param['id'] = 'id' + dbp_uniqid(); 
                $new_field = gp_form_field('', field_name, {'id': val.id[i], 'label': val.label[i]}, type, param, count_block);
                $el_container_input.append($new_field);
            }
        }
        if (label != "" && param.is_multiple == 1) { 
             
            if (type == "POST") {
                $el_container_input.prepend('<label><span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-editor-quote"></span> '+label+'</span></label>');
            } else if (type == "USER") {
                $el_container_input.prepend('<label><span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-admin-users"></span> '+label+'</span></label>');
            } else if (type == "LOOKUP") {
                $el_container_input.prepend('<label><span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-search"></span> '+label+'</span></label>');
            } else if (type == "MEDIA_GALLERY") {
                $el_container_input.prepend('<label><span class="dashicons dbp-label-icon  dashicons-format-image" title="Media Gallery"></span> '+label+'</span></label>');
            }
        }

        // rendo ordinabili i campi js-dbp-form-row
        $el_container_input.sortable({
            items: ".js-dbp-form-row",
            handle: ".dbp-btn-handle-row",
            placeholder: "dbp-ui-state-sortable-highlight",
          
            update: function(event, ui) {
                jQuery(this).find('.js-dbp-form-row').each(function(index) {
                    jQuery(this).find('.js-input-pri').val(index);
                });
            }
        });


        return $el_container;
    } else {
        return $el;
    }

    
}


/**
 * Stampo un singolo campo del form (input/textarea/select(?) ecc..)
 * @param {String} label 
 * @param {String} field_name 
 * @param {String} val Il valore del campo 
 * @param {String} type 
 * @param {*} param 
 */ 
var ADFO_fn = [];
function gp_form_field(label, field_name, val, type, param, count_block) {
    $field_popup = false;
    if (typeof(param) == 'undefined') {
        param = [];
    }
    // clone param
    original_param = JSON.parse(JSON.stringify(param));
    if (type == "TEXT" || type == "EDITOR_CODE" || type == "EDITOR_TINYMCE") {
        $field_row = jQuery('<div class="dbp-form-row js-dbp-form-row"></div>');
        $field_label = jQuery('<label></label>');
        $field_label.append('<span class="dbp-form-label"><span class="dashicons  dbp-label-icon dashicons-text-page"></span> '+label+'</span>');
        addClass = '';
        if (type == "EDITOR_CODE") {
            addClass = ' js-add-codemirror-editor';
        } 
        if (type == "EDITOR_TINYMCE") {
            addClass = ' js-add-tinymce-editor';
        } 
        $field = jQuery('<textarea id="'+dbp_uniqid()+'" class="form-textarea-edit '+addClass+'" name="'+field_name+'"></textarea>');
        if (val == "" && param.default_value !== "") {
            $field.val(param.default_value);
        } else {
            $field.val(val);
        }
        if (typeof(param.note) != 'undefined' && param.note != "") {
            $note = jQuery('<div class="dbp-form-note"></div>');
            $note.html(param.note);
            $field_label.append($note);
        }
        $field.keyup(function() {
            jQuery(this).parents('.js-dbp-form-box').trigger("dbp_change_something");
        });

        $field_label.append($field);
        $field_row.append($field_label);
      
    } else if (type == "PRI") {
        $field_row = jQuery('<div class="dbp-form-row js-dbp-form-row"></div>');
        $field = jQuery('<input type="text" class="form-input-edit js-input-pri" name="'+field_name+'" style="display:none">');
        $field.val(val);
        $field_fake = jQuery('<div class="dbp-input-edit-fake js-input-fake">'+val+'</div>');
        $field_label = jQuery('<label class="js-input-box"></label>');
        $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-admin-network" style="color:#e2c447" title="Primary"></span> '+label+' <span class="dbp-link-click" onclick="dbp_toggle_pri(this)">Edit</span></span>');
        if (typeof(param.note) != 'undefined' && param.note != "") {
            $note = jQuery('<div class="dbp-form-note"></div>');
            $note.html(param.note);
            $field_label.append($note);
        }
        $field_label.append($field);
        $field_label.append($field_fake);
        $field_row.append($field_label);
    } else if (type == "HIDDEN") {
        $field_row = jQuery('<div class="dbp-form-row js-dbp-form-row" style="display:none"></div>');
        $field = jQuery('<input type="hidden" name="'+field_name+'">');
        if (val == "" && param.default_value !== "") {
            $field.val(param.default_value);
        } else {
            $field.val(val);
        }
        $field_row.append($field);
    } else {
        $field_row = jQuery('<div class="dbp-form-row js-dbp-form-row"></div>');
        $field_label = jQuery('<label></label>');
        
        if (type=="DATE" && dbp_checkInput_date('date')) {
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon  dashicons-calendar-alt" title="Date"></span> '+label+'</span>');
            $field = jQuery('<input type="date" pattern="\d{4}-\d{2}-\d{2}" class="form-input-edit js-dbp-validity" name="'+field_name+'">');
        } else if (type=="DATETIME") {
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon  dashicons-calendar-alt" title="Date Time"></span> '+label+'</span>');
            $field = jQuery('<input type="datetime-local" step="1" class="form-input-edit js-dbp-validity" name="'+field_name+'">');
        } else if (type=="NUMERIC") {
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-editor-ol" title="Number"></span> '+label+'</span>');
            $field = jQuery('<input class="form-input-edit" type="number" step="any" name="'+field_name+'">');
        }  else if (type=="TIME") {
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-editor-ol" title="Number"></span> '+label+'</span>');
            $field = jQuery('<input class="form-input-edit js-dbp-format-time js-dbp-validity" type="time" name="'+field_name+'" style="width:inherit">');
        }  else if (type=="DECIMAL") {
            //Decimal
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-editor-ol" title="Number"></span> '+label+'</span>');
            $field = jQuery('<input class="form-input-edit" type="number" step="0.01" name="'+field_name+'">');
        } else if (type=="SELECT") {
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-editor-ol" title="Number"></span> '+label+'</span>');
            $field = jQuery('<select class="form-input-edit" name="'+field_name+'"></select>');
            if (typeof(param.options) == 'object') {
                for (ob in param.options) {
                    $opt = jQuery('<option></option>');
                    if (!param.options[ob].hasOwnProperty('label')) {
                        param.options[ob].label = param.options[ob].value;
                    }
                    $opt.val(param.options[ob].value);
                    $opt.text(param.options[ob].label);
                    $field.append($opt);
                }
            }
        } else if (type=="CHECKBOXES") {
            $field_label = jQuery('<div class="dbp-form-label js-dbp-checkboxes"><span class="dashicons dbp-label-icon dashicons-editor-ol" title="Checkboxes"></span> '+label+'</div>');
            $field = jQuery('<textarea type="text" name="'+field_name+'" class="js-dbp-checboxes-value" style="display:none"></textarea>'); 
            $checkboxes_box = jQuery('<div class="dbp-checkboxes-box js-dbp-checkboxes-box"></div>'); 
            if (typeof(param.options) == 'object') {
                for (ob in param.options) {
                    $cb_label = jQuery('<label class="dbp-checkbox-label"></label>');
                    $cb_checkbox = jQuery('<input type="checkbox" />');
                    if (!param.options[ob].hasOwnProperty('label')) {
                        param.options[ob].label = param.options[ob].value;
                    }
                    if (typeof(param.options[ob].value) != "undefined") {
                        $cb_checkbox.val(param.options[ob].value);
                    } else {
                        $cb_checkbox.val(param.options[ob].label);
                    }
                    $cb_label.append($cb_checkbox);
                    $cb_label.append('<span>'+param.options[ob].label+'</span>');
                    $checkboxes_box.append($cb_label);
                }
            }
            $field_label.append($checkboxes_box);
        } else if (type=="RADIO") {
            $field_label = jQuery('<div class="dbp-form-label js-dbp-radio"><span class="dashicons dbp-label-icon dashicons-editor-ol" title="Radio"></span> '+label+'</div>');
            uid_radio = dbp_uniqid();
            $checkboxes_box = jQuery('<div class="dbp-radio-box js-dbp-radio-box"></div>'); 
            if (typeof(param.options) == 'object') {
                for (ob in param.options) {
                    $cb_label = jQuery('<label class="dbp-checkbox-label"></label>');
                    $cb_checkbox = jQuery('<input type="radio" name="r'+uid_radio+'" />');
                    if (!param.options[ob].hasOwnProperty('label')) {
                        param.options[ob].label = param.options[ob].value;
                    }
                    if (typeof(param.options[ob].value) != "undefined") {
                        $cb_checkbox.val(param.options[ob].value);
                    } else {
                        $cb_checkbox.val(param.options[ob].label);
                    }
                    $cb_label.append($cb_checkbox);
                    $cb_label.append('<span>'+param.options[ob].label+'</span>');
                    $checkboxes_box.append($cb_label);
                }
            }
            $field = jQuery('<input type="text" name="'+field_name+'" class="js-dbp-radio-value" style="display:none">'); 
            $field_label.append($checkboxes_box);
        } else if (type=="CHECKBOX") {
            $field_label = jQuery('<label class="dbp-checkbox-label"><span>'+label+'</span></label>');
            $field = jQuery('<input type="checkbox" name="'+field_name+'" class="dbp-checkbox-left">');
            $field.val(param.custom_value);
        } else if (type=="POST_STATUS") {
            $field_label = jQuery('<div></div>');
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-yes-alt"></span> '+label+'</span>');
            $field = jQuery('<input type="hidden" class="form-input-edit js-input-status" name="'+field_name+'" >');
            // aggiungo due bottoni uno per il publish e uno per il draft
            $field_box = jQuery('<div class="js-btns-post-status"></div>');
            $field_label.append($field_box);
            if (val == '') {
                val = param.default_value;
            }
            for (cp in param.options) {
                $field_box.append('<div class="dbp-btn-status dbp-btn-status-'+cp+' js-adfo-status js-dbp-'+cp + ((val==cp) ? ' dbp-btn-status-selected' : '')+'" onclick="choose_post_status(\''+cp+'\',this)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="presentation" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg> '+param.options[cp]+'</div>');
            }
            //$field.val(param.custom_value);
        } else if (type=="READ_ONLY") {
            $field_label = jQuery('<label class="js-input-box"><span class="dbp-form-label js-dbp-radio"><span class="dashicons dbp-label-icon dashicons-lock" title="Read only"></span> '+label+'</span></label>');
            $field = jQuery('<input type="hidden" name="'+field_name+'" style="display:none">');
            $field_readonly = jQuery('<div class="dbp-input-edit-fake js-input-fake"></div>');
            $field_readonly.text(val);
            $field_label.append( $field_readonly);
        } else if (type=="CREATION_DATE" || type=="LAST_UPDATE_DATE" || type=="RECORD_OWNER" || type=="MODIFYING_USER"  || type=="CALCULATED_FIELD") {
            dashicon = 'dashicons-clock';
            if (type=="RECORD_OWNER" || type=="MODIFYING_USER") {
                dashicon = 'dashicons-admin-users';
            } 
            if (type=="CALCULATED_FIELD") {
                dashicon = 'dashicons-calculator';
                if (val != "") {
                    param.custom_value = val;
                }
            }
           
            $field_label = jQuery('<label class="js-input-box"><span class="dbp-form-label js-dbp-radio"><span class="dashicons dbp-label-icon '+dashicon+'" title="Read only"></span> '+label+'</span></label>');
            $field = jQuery('<input type="hidden" name="'+field_name+'" style="display:none">');
            $field_readonly = jQuery('<div class="dbp-input-edit-fake js-input-fake"></div>');
            $field_readonly.text(param.custom_value);
            $field_label.append( $field_readonly);
        } else if( type == "POST" || type == "USER" || type == "LOOKUP")  {
            //l'input dei post è un autocomplete speciale!
            if (label != "" && param.is_multiple == 0) {
                if (type == "POST") {
                    $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-editor-quote"></span> '+label+'</span>');
                } else if (type == "USER") {
                    $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-admin-users"></span> '+label+'</span>');
                } else if (type == "LOOKUP") {
                    $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-search"></span> '+label+'</span>');
                }
            } 
           
            if (typeof(param) == 'object') {
                let addClass = (param.is_multiple == 1) ? ' js-dbp-autocoplete-multiple' : '';
                $field = jQuery('<input type="text" autocomplete="off" class="form-input-edit js-dbp-autocoplete js-dbp-autocoplete-id-title '+addClass+'">');
                let data_autocomplete = (param.is_multiple == 1) ? 'data-dbp-autocomplete-multiple="'+param.autocomplete_id+'"' : '';
                $field_id = jQuery('<input type="hidden" autocomplete="off" class="js-dbp-autocoplete-id" name="'+field_name+'" '+data_autocomplete+'>');
                if  (param.is_multiple == 1) {
                    $field_id.attr('data-dbp-autocomplete-multiple', param.autocomplete_id);
                    $field_id.data('dbp-autocomplete-multiple', param.autocomplete_id);
                }

                if (typeof(param.required) != 'undefined' && param.required == "1") {
                    $field_id.prop('required',true);
                    $field_id.addClass('js-dbp-validity');
                } 
                if (typeof val == 'object') {
                    $field_id.val(val.id);
                    param.custom_value = val.label;
                    val = val.id;
                } else {
                    $field_id.val(val);
                }
                $field.data('dbp-autocomplete-id', $field_id);
                $field_label.append($field_id);
                let addClass2 = (param.is_multiple == 1) ? 'dbp-input-autocomplete-fake-multirow' : 'dbp-input-autocomplete-fake';
        
                $field_readonly = jQuery('<div class="'+addClass2+' js-fake-autocomplete" title="click to edit"><div class="dbp-input-edit-fake js-input-fake"></div><div class="dbp-btn-autocomplete-x" >×</div></div>');

                $field_label.append($field_readonly);
                if (param.is_multiple == 1) {
                    $field_label.find('.js-fake-autocomplete').prepend('<div class="dbp-btn-handle-row" style="float: left;padding: 6px 0;"><span class="dashicons dashicons-sort"></span></div>');
                }
                $field_readonly.click(function(e) {
                    // e.target individua il target del click che può essere il div fake o il div x
                    if (jQuery(e.target).hasClass('dbp-btn-autocomplete-x')) {
                        jQuery(this).parent().find('.js-dbp-autocoplete').val('').change();
                        let countainer_multiple_row =  jQuery(this).parents('.js-container-multiple-fields');
                        // se esiste un container multiple row e ci sono più righe al suo interno
                        if (countainer_multiple_row.length == 1) {  
                            let row_inserted = countainer_multiple_row.find('.js-dbp-form-row');
                            if (row_inserted.length > 1) {
                                jQuery(this).parents('.js-dbp-form-row').remove();
                            }
                        }
                    }
                    jQuery(this).parent().find('.js-dbp-autocoplete').css('display','block').focus().select();
                    jQuery(this).css('display','none');
                })
                if (val > 0) {
                    $field.css('display','none');
                } else {
                    $field_readonly.css('display','none');
                }
                if (param.custom_value != "") {
                    $field_readonly.find('.js-input-fake').text(param.custom_value);
                    val = param.custom_value;
                    $field_id.data('dbp_complete_title', param.custom_value);
                } else {
                    val = '';
                }
                $field_popup = jQuery('<div class="js-dbp-popup-autocomplete dbp-autocomplete-popup-box"></div>'); 
            } else {
                 $field = jQuery('<input type="text" class="form-input-edit" name="'+field_name+'" >');
            } 
        }  else if (type=="UPLOAD_FIELD") {
            $field_label = jQuery('<div class="js-attachment-box"></div>');
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon dashicons-paperclip" title="Upload Field"></span> '+label+'</span>');
            if ( param.custom_value ) {
                $field_label.append(param.custom_value);
            }
            $field = jQuery('<input class="form-input-edit js-attachment-value custom-img-id" type="hidden" name="'+field_name+'">');
            $field_2 = jQuery('<input class="form-input-edit" type="file" name="'+field_name+'[upload]">');

            $field_label.append($field_2);

     
        }  else if ( type == "MEDIA_GALLERY" ) {
            $field_label = jQuery('<div class="js-attachment-box"></div>');
            if (param.is_multiple != 1 && label != "") {
                $field_label.append('<span class="dbp-form-label"><span class="dashicons dbp-label-icon  dashicons-format-image" title="Media Gallery"></span> '+label+'</span>');
            }
            if (param.is_multiple == 1) {
                $grid_field = jQuery('<div class="dbp-grid-media-gallery-multiple"></div>');
                $grid_field.append('<div class="dbp-btn-handle-row" style="float: left;padding: 6px 0;"><span class="dashicons dashicons-sort"></span></div>');
            } else {
                $grid_field = jQuery('<div class="dbp-grid-2-columns"></div>');
            }
            
            $grid_field_col1 = jQuery('<div class="dbp-media-gallery-img"></div>');
            $grid_field_col2 = jQuery('<div></div>');
            $grid_field.append($grid_field_col1).append($grid_field_col2);
            let addClass = '';
            if (typeof(param) == 'object') {
             //   addClass = (param.is_multiple == 1) ? ' js-dbp-autocoplete-multiple' : '';
            } 
            let data_autocomplete = (param.is_multiple == 1) ? 'data-dbp-autocomplete-multiple="'+param.autocomplete_id+'"' : '';
            
    
            $field = jQuery('<input class="form-input-edit js-attachment-value custom-img-id '+addClass+'" type="hidden" name="'+field_name+'" '+data_autocomplete+'>');
            if  (param.is_multiple == 1) {
                $field.attr('data-dbp-autocomplete-multiple', param.autocomplete_id);
                $field.data('dbp-autocomplete-multiple', param.autocomplete_id);
            }
            $field_3 = jQuery('<div class="button dbp-css-mb-1 " onclick="upload_image_button(this)">Media gallery</div>');
            $field_4 = jQuery('<div class="button delete-custom-img" onClick="dbp_del_img_link(this)">Remove this image</a>');
            $field_5 = jQuery('<div class="dbp-form-media-gallery-name js-media-gallery-name"></div>');
            $field.data('dbp-dom-validity-rif', $grid_field_col1);
           
            $grid_field_col2.append($field_3).append('<br>').append($field_4).append($field_5);
           

            $att_cust_img_cont = jQuery('<div class="js-custom-img-container dbp-custom-img-container"></div>'); 
            if (typeof val == 'object') {
                $att =  jQuery('<img src="'+val.url+'" alt="" style="max-width:100%;" />');
                $att_cust_img_cont.append($att);
                $field_5.html('<a href="'+val.link+'" target="blank">'+val.title+'</a>');
                val = val.id;
            } else {
                $field_4.addClass('hidden');
                $field_5.addClass('hidden');
            }
            if (val == 0) val = '';
            $grid_field_col1.append($att_cust_img_cont);
            $field_label.append($grid_field);
        } else if (type=="COLOR_PICKER") {
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dashicons-color-picker"></span> '+label +'</span>');
            $field = jQuery('<input type="color" autocomplete="off" class="form-input-edit dbp-form-input-color" name="'+field_name+'">');
        }  else if (type=="ORDER") {
            // campo ordinamento
            $field_label.append('<span class="dbp-form-label"><span class="dashicons dashicons-sort"></span> '+label +'</span>');
            $field = jQuery('<input type="number" min="0" size="6" class="form-input-edit dbp-form-input-order" name="'+field_name+'">');
        } else if (type=="RANGE") {
            let rand_id = dbp_uniqid();
            $field_label.append('<span class="dbp-form-label"><span class="dashicons  dashicons-performance"></span> '+label +'</span>');

            $grid_field_col1 = jQuery('<div style="display: inline-block; margin-right:5%; width:40%"></div>');
            range_val = param.default_value;
            if (val != '') {
                range_val = val;
            }
            $field2 = jQuery('<input type="range" autocomplete="off" class="form-input-edit dbp-form-input-color dbp-input-range" name="'+field_name+'_range" min="'+param.range_min+'" max="'+param.range_max+'" step="'+param.range_step+'" list="'+rand_id+'" value="'+range_val+'">');
            $data_list = jQuery('<datalist class="dbp-datalist" id="'+rand_id+'"><option value="'+param.range_min+'" label="'+param.range_min+'"></option><option value="'+param.range_max+'" label="'+param.range_max+'"></option></datalist>');
            $grid_field_col1.append($field2).append($data_list);
            $field = jQuery('<input type="number" autocomplete="off" class="form-input-edit dbp-input-range-value" name="'+field_name+'" min="'+param.range_min+'" max="'+param.range_max+'" step="'+param.range_step+'">');
            $field2.data('rangerif', $field);
            $field.data('rangerif', $field2);
            $field.change(function() {
                $newEl = jQuery(this).data('rangerif');
                $newEl.val(jQuery(this).val());
            })
            $field2.change(function() {
                $newEl = jQuery(this).data('rangerif');
                $newEl.val(jQuery(this).val());
            })
            $field_label.append($grid_field_col1);

        } else {
            //l'input classico
            let icon = 'dashicons-editor-quote';
            let input_type = 'text';
            if (type == "LINK") {
                icon = 'dashicons-admin-links';
                input_type = 'url';
            }
            if (type == "EMAIL") {
                icon = 'dashicons-email';
                input_type = 'email';
            }
            $field_label.append('<span class="dbp-form-label"><span class="dashicons '+icon+' "></span> '+label +'</span>');
            if (typeof(param) == 'object') {
                if (param.autocomplete == 1) {
                    $field = jQuery('<input type="'+input_type+'" autocomplete="off" class="form-input-edit js-dbp-autocoplete" name="'+field_name+'" >');
                    $field_popup = jQuery('<div class="js-dbp-popup-autocomplete dbp-autocomplete-popup-box"></div>');
                } else {
                    $field = jQuery('<input type="'+input_type+'" autocomplete="off" class="form-input-edit" name="'+field_name+'" >');
                }
              
            } else {
                $field = jQuery('<input type="text" class="form-input-edit" name="'+field_name+'" >');
            }
            $field_label.append($field_popup);
        }
        if (type=="CHECKBOXES") {
            if (val == "[]") {
                $field.val('');
            } else {
                $field.val(val);
            }
        } else if (type=="CHECKBOX") {
            if (val == param.custom_value) {
                $field.prop('checked', true); 
            }
        }  else {
            if (val == "" && param.default_value !== "") {
                $field.val(param.default_value);
            } else {
                $field.val(val);
            }
        }
        
      
        if (typeof(param.note) != 'undefined' && param.note != "") {
            $note = jQuery('<div class="dbp-form-note"></div>');
            $note.html(param.note);
            $field_label.append($note);
        } 


        $field_label.append($field);
        $field_label.append('<div class="dbp-form-field-footer" style="display:none"></div>');
        $field_row.append($field_label);
        if ($field_popup !== false) {
            if (typeof(param) == 'object') {
                if( type== "POST")  {
                    $field.data('dbp_type', 'post') ;
                    if (typeof(param.post_types) != 'undefined') {
                        cats = [];
                        if (typeof(param.post_cats) != 'undefined') {
                            cats = param.post_cats;
                        }
                        $field.data('autocomplete_params', {'post_types':param.post_types, 'cats':cats, 'type':'post'});
                    }
                  
                } else if( type== "USER")  {
                    $field.data('dbp_type', 'user') ;
                    roles = [];
                    if (typeof(param.user_roles) != 'undefined') {
                        roles = param.user_roles;
                    }
                    $field.data('autocomplete_params', {'roles':roles, 'type':'user'});
                } else if( type == "LOOKUP")  {
                    $field.data('dbp_type', 'lookup') ;
                    $field.data('autocomplete_params', {'lookup_id':param.lookup_id, 'lookup_sel_txt':param.lookup_sel_txt, 'lookup_sel_val':param.lookup_sel_val, 'type':'lookup'});
                } else {
                    $field.data('dbp_type', 'distinct');
                    if (typeof(param.orgtable) != 'undefined' && typeof(param.name) != 'undefined') {
                        if (param.hasOwnProperty('ac_sql')) {
                            $field.data('autocomplete_params', {'table':param.orgtable,'column':"`"+param.table+"`.`"+param.name+"`", 'type':'distinct', 'sql': param.ac_sql });
                        } else {
                            $field.data('autocomplete_params', {'table':param.orgtable,'column':param.name, 'type':'distinct', 'sql': '' });
                        }
                    }
                }
            

                $field_row.append($field_popup);

                // il popup dell'autocomplete
                $field.focus(function() {
                    let id = jQuery(this).prop('id'); 
                    jQuery('.js-dbp-popup-autocomplete').empty().css('display','none');
                    jQuery('.js-dbp-autocoplete').data('dbp_ajax_lock', 'f');
                    data = {};
                    data.params = jQuery(this).data('autocomplete_params');
                    data.filter_distinct = ''; 
                    data.rif =jQuery(this).prop('id'); 
                    data.sql = (typeof(data.params.sql) != 'undefined') ? data.params.sql : '';
                    if (typeof(data.params.sql) != 'undefined') delete data.params.sql;
                    data.action = 'af_autocomplete_values';
                    dbp_ajax_autocomplete(data);
                });
                $field.blur(function() {
                    setTimeout((function() {
                        dbp_close_autocomplete_popup(jQuery(this));
                        close_autocomplete_search(jQuery(this));
                        //jQuery(this).parents('.js-dbp-form-row').find('.js-dbp-popup-autocomplete').empty().css('display','none');
                    }).bind(this), 400);
                });
               
                $field.keyup(function(event) {
                    jQuery(this).parents('.js-dbp-form-box').trigger("dbp_change_something");
                    var char = event.which || event.keyCode;
                    if (jQuery(this).hasClass('js-dbp-autocoplete') && (char != 38 && char != 40 && char != 13) ) {
                        data = {};
                        data.params = jQuery(this).data('autocomplete_params');
                        data.filter_distinct =jQuery(this).val(); 
                        data.rif = jQuery(this).prop('id'); 
                        data.sql = (typeof(data.params.sql) != 'undefined') ? data.params.sql : '';
                        if (typeof(data.params.sql) != 'undefined') delete data.params.sql;
                        data.action = 'af_autocomplete_values';
                        memory_name = jQuery(this).data('dbp_memory_name');
                       
                        if (typeof(memory_name) != 'undefined' && ((memory_name != '' && jQuery(this).val().substring(0, memory_name.length) == memory_name) || memory_name == '_#ALL#_')) {
                            var filter = jQuery(this).val();
                            jQuery('#'+data.rif+'_popup ul li').each(function() {
                                if (jQuery(this).text().toLowerCase().indexOf(filter.toLowerCase()) > -1) {
                                    jQuery(this).removeClass('li-hide').addClass('li-show');
                                } else {
                                    jQuery(this).removeClass('li-show').addClass('li-hide');
                                }
                            });
                            if (jQuery('#'+data.rif+'_popup ul .li-show').length > 0) {
                                jQuery('#'+data.rif+'_popup').css('display', 'block');
                            } else {
                                dbp_close_autocomplete_popup(jQuery('#'+data.rif));
                                //jQuery('#'+data.rif+'_popup').css('display', 'none');
                            }
                        } else if (memory_name == '_#ALL#_' || (jQuery(this).val().length > 0 && jQuery(this).val().length < 250)) {
                            dbp_ajax_autocomplete(data);
                        } else {
                            dbp_close_autocomplete_popup(jQuery('#'+data.rif));
                            //jQuery('#'+data.rif+'_popup').css('display','none');
                        }
                        if (jQuery(this).val().length == 0) {
                            if (jQuery(this).data('dbp-autocomplete-id')) {
                                jQuery(this).data('dbp-autocomplete-id').val('');
                            }
                        }
                    } else {
                        jQuery(this).data('dbp_memory_name', '');
                    }

                    // SELEZIONE
                    if (jQuery(this).hasClass('js-dbp-autocoplete') ) { 
                        if (char == 13) {
                            popup_li = jQuery('#'+jQuery(this).prop('id')+"_popup .dbp-popup-selected");
                            if (typeof popup_li != 'undefined' && popup_li.length > 0) {
                                popup_li.click();
                            } else {
                                jQuery('#'+data.rif).blur(); 
                                //jQuery('#'+data.rif).val('');
                                dbp_close_autocomplete_popup(jQuery('#'+data.rif));
                                close_autocomplete_search(jQuery('#'+data.rif));
                            }
                            event.stopPropagation();
                            return false;
                        }
                        $popup_li = jQuery('#'+jQuery(this).prop('id')+"_popup .li-show");
                        jQuery('#'+jQuery(this).prop('id')+"_popup .li-hide").removeClass('dbp-popup-selected');
                        $popup_li_all = jQuery('#'+jQuery(this).prop('id')+"_popup li");
                        $popup_selected = jQuery('#'+jQuery(this).prop('id')+"_popup .dbp-popup-selected");

                        if ($popup_li.length == 1) {
                            $popup_li_all.removeClass('dbp-popup-selected');
                            $popup_li.addClass('dbp-popup-selected');
                        } else if (char == 38 || char == 40) {
                            
                            if (typeof $popup_li != 'undefined' && $popup_li.length > 0) {
                                $popup_li_all.removeClass('dbp-popup-selected');
                                if (char == 40) {
                                    $next_el = get_next_element($popup_selected, 'li-show');
                                    if (typeof $popup_selected != 'undefined' && $popup_selected.length > 0 &&  $next_el !== false) {
                                        $next_el.addClass('dbp-popup-selected');
                                    } else {
                                        $popup_li.first('.li-show').addClass('dbp-popup-selected');
                                    }
                                }
                                if (char == 38) {
                                    $prev_el = get_prev_element($popup_selected, 'li-show');
                                    if (typeof $popup_selected != 'undefined' && $popup_selected.length > 0 &&  $prev_el !== false) {
                                        $prev_el.addClass('dbp-popup-selected');
                                    } else {
                                        $popup_li.last('.li-show').addClass('dbp-popup-selected');
                                    }
                                    jQuery(this).val(jQuery(this).val());
                                }
                            }
                            event.stopPropagation();
                            return false;
                        }  else if (char == 27) {
                            jQuery('#'+data.rif).blur(); 
                            dbp_close_autocomplete_popup(jQuery('#'+data.rif));
                            close_autocomplete_search(jQuery('#'+data.rif));
                            event.stopPropagation();
                        } else if ($popup_selected.length == 0) {
                            $popup_li_all.removeClass('dbp-popup-selected');
                            $popup_li.first('.li-show').addClass('dbp-popup-selected');
                        }
                    }
                })
            }
        } // fine popup ajax

        if (type=="CHECKBOXES") {
            dbp_form_checkboxes($field_label);
        }
        if (type=="RADIO") {
            dbp_form_radio($field_label);
        }
    }

    if (typeof(param) == 'object') {
        if (typeof(param.id) != 'undefined') {
            $field.prop('id', param.id);
            if ($field_popup !== false) {
                $field_popup.prop('id', param.id+"_popup");
            }
        }
        
        if (typeof(param.custom_css_class) != 'undefined') {
            $field_row.addClass(param.custom_css_class);
        }

        if (typeof(param.required) != 'undefined' && param.required == "1") {
            $field.prop('required',true);
            $field.addClass('js-dbp-validity');
        } else if (type == "LINK" || type == "EMAIL") {
            $field.addClass('js-dbp-validity');
        }
        
        $field.attr('data-dbp_label', label);
        // TODO questo non va bene!
        $field.attr('data-dbp_name', param.name);
        $field.attr('data-dbp_js_rif', param.js_rif+"."+count_block);

        // questo permette di inserire una nuova funzione js che viene chiamata al change di una funzione (TODO e al submit?)
        if (typeof(param.js_script) != 'undefined') {
            try {
                $field.data('ADFO_fn', new Function('field','form','status', param.js_script));
                $field.addClass('js-dbp-fn-set');
                $field.on('keyup change', function() {
                    let __custom_fn = jQuery(this).data('ADFO_fn');
                    if (__custom_fn instanceof Function) {
                        dbp_exec_fn(__custom_fn, this, 'field_change');
                    }
                    var __current_change = this;
                    jQuery('.js-dbp-fn-set').each(function() {
                        if (this != __current_change) {
                            let __custom_fn = jQuery(this).data('ADFO_fn');
                            if (__custom_fn instanceof Function) {
                                dbp_exec_fn(__custom_fn, this, 'form_change');
                            }
                        }
                    })
                });
            } catch (error) {
                blabel = $field.data('dbp_label');
                show_err = $field.data('show_err');
                if (typeof(show_err) == 'undefined') { 
                    alert ('JS ERROR FOR '+blabel+"\n"+error);
                }
                console.warn('JS ERROR FOR '+blabel+" "+error+"\n"+param.js_script);
            }
           
        } else {
            // se cambio un qualsiasi campo del db seguo il form_change solo al change del campo.
            try { 
                $field.on('change', function() {
                    var __current_change = this;
                    jQuery('.js-dbp-fn-set').each(function() {
                        if (this != __current_change) {
                            let __custom_fn = jQuery(this).data('ADFO_fn');
                            if (__custom_fn instanceof Function) {
                                dbp_exec_fn(__custom_fn, this, 'form_change');
                            }
                        }
                    })
                });
            } catch (error) {
                blabel = $field.data('dbp_label');
                show_err = $field.data('show_err');
                if (typeof(show_err) == 'undefined') { 
                    alert ('JS ERROR FOR '+blabel+"\n"+error);
                }
                console.warn('JS ERROR FOR '+blabel+" "+error+"\n"+param.js_script);
            }
        }
    }

    
    $field.on('blur', function() {
        jQuery(this).addClass('js-dbp-add-show-error-border');
    });

    if (typeof(param.edit_view != 'undefined')) {
        if (param.edit_view == "HIDE") {
            $field_row.css('display','none').addClass('js-dbp-hide');
        }
    }
    
    
    return $field_row;
}

/**
 * quando chiudo i popup degli autocomplete, verifico se è un id-title che il titolo sia lo stesso inserito nell'id
 */
function dbp_close_autocomplete_popup($field) {
    $popup = jQuery('#'+$field.prop('id')+"_popup");
    if ($popup.length > 0) {
        if (typeof jQuery(':focus') != 'undefined' &&  jQuery(':focus').prop('id') == $field.prop('id')) {
            $popup.css('display', 'none');
        } else {
            $popup.empty().css('display', 'none');
            $field.data('dbp_memory_name', ''); 
            $field.data('dbp_ajax_lock', 'f');
        }
    }
    // verifico se è un id-title che i due combacino altrimenti ripulisco tutto
    if ($field.hasClass('js-dbp-autocoplete-id-title')) {
        $field_id = $field.data('dbp-autocomplete-id');

        if (typeof $field_id.data('dbp_complete_title') != 'undefined' && $field_id.length > 0 && $field.val().trim().toLowerCase() != $field_id.data('dbp_complete_title').trim().toLowerCase()) {
            if (typeof jQuery(':focus') == 'undefined' || jQuery(':focus').prop('id') != $field.prop('id') ) {
                $field.val('');
                $field_id.val('');
                $field_id.data('dbp_complete_title', '');
                $field.parent().find('.js-fake-autocomplete').css('display','none');
                $field.css('display','block');
                $field.parent().find('.js-input-fake').empty();
            }
        } else if (typeof jQuery(':focus') == 'undefined' || jQuery(':focus').prop('id') != $field.prop('id') ) {
            if ($field_id.val() == "" || $field_id.val() <= 0) {
                $field.val('');
                $field_id.val('');
            }
            $field.parent().find('.js-input-fake').text($field.val());
            $field.parent().find('.js-fake-autocomplete').css('display','grid');
            $field.css('display','none');
        }
    } 
}

function close_autocomplete_search($field) {
    if ($field.hasClass('js-dbp-autocoplete-id-title')) {
        $field_id = $field.data('dbp-autocomplete-id');
        if ($field_id.val() == "" || $field_id.val() <= 0) {
            $field.val('');
            $field_id.val('');
        }
        $field.parent().find('.js-input-fake').text($field.val());
        if ($field.val() != '' && $field.val != '0') {
            $field.parent().find('.js-fake-autocomplete').removeClass('dbp-input-error');
        }
        $field.parent().find('.js-fake-autocomplete').css('display','grid');
        $field.css('display','none');
    }
}



function get_next_element($el, hasClass) {
    var curr_el = false;
    var next_el = false;
    if ($el.length == 1) {
        el = $el.get(0);
        $el.parent().children().each(function() {
            if (curr_el) {
                if (next_el === false) {
                    if (jQuery(this).hasClass(hasClass)) {
                        next_el = jQuery(this);
                    }
                }
            } else if (this == jQuery($el).get(0)) {
                curr_el = true;
            }
        });
    }
    return next_el;
}

function get_prev_element($el, hasClass) {
    var curr_el = false;
    var next_el = false;
    if ($el.length == 1) {
        el = $el.get(0);
        jQuery($el.parent().children().get().reverse()).each(function() {
            if (curr_el) {
                if (next_el === false) {
                    if (jQuery(this).hasClass(hasClass)) {
                        next_el = jQuery(this);
                    }
                }
            } else if (this == jQuery($el).get(0)) {
                curr_el = true;
            }
        });
    }
    return next_el;

}

/**
 * Gestisce i checkboxes multipli
 * @param {Jquery} $field_label 
 */
function  dbp_form_checkboxes($field_label) {
    if ($field_label.data('dbp_form_checkboxes') != 'active') {
        $field_label.data('dbp_form_checkboxes','active');
        // carico i dati le segno i checkboxes collegati
      
        if ($field_label.find('.js-dbp-checboxes-value').val() != "") {
            try {
                json_txt_value = JSON.parse($field_label.find('.js-dbp-checboxes-value').val());
            }  catch (error) {}
            
            if (typeof(json_txt_value) == 'object') {       
                for (jtx in json_txt_value){

                    $field_label.find('[type=checkbox]').each(function(){
                        if (json_txt_value[jtx] == jQuery(this).val()) {
                            jQuery(this).prop('checked', true);
                        }
                    })
                }
                
            }
        }
        // imposto gli eventi al click dei checkbox
        $field_label.find('[type=checkbox]').change( function() {
            $box_parent = jQuery(this).parents('.js-dbp-checkboxes');
            $box_checkboxes = $box_parent.find('.js-dbp-checkboxes-box');
            var values = [];
            $box_checkboxes.find(":checked").each(function() {
                values.push(jQuery(this).val());
            });
            if (values.length > 0) {
                $box_parent.find('.js-dbp-checboxes-value').val(JSON.stringify(values)).change();
            } else {
                $box_parent.find('.js-dbp-checboxes-value').val('');
            }
        });
        //
        $field_label.find('.js-dbp-checboxes-value').data('field_label', $field_label);
    }
   
}


/**
 * Gestisce i radio 
 * @param {Jquery} $field_label 
 */
 function  dbp_form_radio($field_label) {
    if ($field_label.data('dbp_form_radio') != 'active') {
        $field_label.data('dbp_form_radio','active');
        // carico i dati e segno il radio collegato
        if ($field_label.find('.js-dbp-radio-value').val() != "") {
            $field_label.find('[type=radio]').each(function(){
                if ($field_label.find('.js-dbp-radio-value').val() == jQuery(this).val()) {
                    jQuery(this).prop('checked', true);
                }
            })
        }
        // imposto gli eventi al click dei checkbox
        $field_label.find('[type=radio]').change( function() {
            $box_parent = jQuery(this).parents('.js-dbp-radio');
            $box_parent.find('.js-dbp-radio-value').val(jQuery(this).val()).change();
        });
        //
        $field_label.find('.js-dbp-radio-value').data('field_label', $field_label);
    }
   
}

/**
 * L'autocomplete ajax
 * @param {} data 
 * @returns 
 */
function dbp_ajax_autocomplete(data) {
    console.log('dbp_ajax_autocomplete');
   // data.sql = "SELECT meta_value FROM wp_postmeta WHERE meta_key = 'test2'";
    console.log (data);
    if (jQuery('#'+data.rif).data('dbp_ajax_lock') == "t") {
        return false;
    }
    jQuery('#'+data.rif).data('dbp_ajax_lock','t');
    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : ajaxurl,
        data : data,
        success: function(response) {
            if (!jQuery('#'+response.rif).is(':focus')) {
                dbp_close_autocomplete_popup(jQuery('#'+response.rif));
                return;
            }
            $dbp_popup = jQuery('#'+response.rif+"_popup");
            $ul_box = jQuery('<ul class="dbp-autocomplete-list"></ul>');
            $dbp_popup.empty().append($ul_box);
            jQuery('#'+response.rif).data('dbp_ajax_lock', 'f');
            let  zero_result = false;
            if (typeof (response.result) != 'undefined' && typeof (response.result[0]) != 'undefined') {
                zero_result = (((response.count == 1 && jQuery('#'+response.rif).val() == response.result[0].c)) || response.count == 0);
            }
           
            if (parseInt(response.count) < 1000) {
                if (response.filter_distinct == "") {
                    jQuery('#'+response.rif).data('dbp_memory_name', '_#ALL#_');
                } else {
                    jQuery('#'+response.rif).data('dbp_memory_name', response.filter_distinct);
                }
            }

            if (response.count > 0 && !zero_result) {
                if (jQuery('#'+response.rif).data('dbp_ajax_lock') == "t") {
                    return;
                }
                $dbp_popup.css('display', 'block');
                count_show = 0;
                for (let i in response.result) {
                    // resposne.rif sul change lo prendo da ul.data.rif
                    if (i > 1000) break;
                    var value =  response.result[i].c;

                    if (response.result[i].c.length > 255) {
                        label_response = response.result[i].c.substring(0,255);
                    } else {
                        label_response = response.result[i].c;
                    }
                    if (value != '_##Empty values##_') {
                        label_response = String(label_response).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        count_show++;
                        $add = jQuery('<li class="dbp-popup-line-click li-show">' +  label_response + '</li>');
                        $add.data('dbp_input_id', response.rif);
                        $add.data('dbp_rif_p', response.result[i].p);
                        $add.mouseover(function() {
                            jQuery(this).parent().find('li').removeClass('dbp-popup-selected');
                            jQuery(this).addClass('dbp-popup-selected');
                        })
                        $add.mouseleave(function() {
                            jQuery(this).parent().find('li').removeClass('dbp-popup-selected');
                        })
                        $add.mouseout(function() {
                            jQuery(this).parent().find('li').removeClass('dbp-popup-selected');
                        })
                        $add.click(function() {
                            let id = jQuery(this).data('dbp_input_id');
                            // vuol dire che usa due campi uno di visualizzazione e uno per il valore effettivo da salvare
                            jQuery('#'+id).data('dbp_ajax_lock', 'f');
                            if (jQuery('#'+id).hasClass('js-dbp-autocoplete-id-title')) {
                                $hidden_field = jQuery('#'+id).data('dbp-autocomplete-id');
                                $hidden_field.val(jQuery(this).data('dbp_rif_p'));
                                $hidden_field.data('dbp_complete_title', jQuery(this).text());
                            }
                            jQuery('#'+id).val(jQuery(this).text());
                            jQuery('#'+id).change();
                            jQuery('#'+id).blur();
                            dbp_close_autocomplete_popup(jQuery('#'+id));
                            close_autocomplete_search(jQuery('#'+id));
                           // jQuery(this).parents('.js-dbp-popup-autocomplete').empty().css('display','none');
                        })
                        $ul_box.append($add);
                    }
                }
                if (count_show == 0) {
                    $dbp_popup.css('display', 'none');
                }
            } else {
                $dbp_popup.css('display', 'none');
            }
        },
       
    });
}



/**
 * Permette di modificare l'id del record
 * @param {DOM} el 
 */
function dbp_toggle_pri(el) {
    let $box = jQuery(el).parents('.js-input-box');
    jQuery(el).css('display','none');
    $box.find('.js-input-fake').css('display','none');
    $box.find('.js-input-pri').css('display','block');
    $box.find('.js-input-pri').first().focus();

}

function dbp_checkInput_date(type) {
    var input = document.createElement("input");
    input.setAttribute("type", type);
    return input.type == type;
}



/**
 * Js funzioni per le tabelle
 */

function dbp_exec_fn(custom_fn, el, status) {
    if (custom_fn instanceof Function) {
        var el_dbp_field = new Dbp_field(el, status);
        let el_dbp_form = new Dbp_form(el);
        try {
            custom_fn(el_dbp_field, el_dbp_form, status);
        } catch (error) {
            label = jQuery(el).data('dbp_label');
            show_err = jQuery(el).data('show_err');
            if (typeof(show_err) == 'undefined') { 
                alert ('JS ERROR FOR '+label+"\n"+error);
            }
            console.warn('JS ERROR FOR '+label+" "+error);
        }
        jQuery(el).data('show_err', '1');
    }
}

/**
 * Il javascript per gestire i campi della form
 * @param DOM el 
 * @param {*} status 
 */

function Dbp_field(el, status) {
    this.el = el;
    this.status = status;
    this.dom = function() {
        return el;
    };
    this.val = function(value) {
        if (this.el == false) return false;
        if (typeof(value) != 'undefined' )  {
            if (jQuery(this.el).hasClass('js-add-tinymce-editor') && jQuery(this.el).data('tny_editor') == "t") {
                tinyMCE.get(jQuery(this.el).prop('id')).setContent(value);
                jQuery(this.el).val(value);
            } else if (jQuery(this.el).hasClass('js-add-codemirror-editor') && jQuery(this.el).data('cm_editor')) {
                cm_editor = jQuery(this.el).data('cm_editor');
                cm_editor.codemirror.setValue(value);
                jQuery(this.el).val(value);
            } else if (jQuery(this.el).prop('type') == "checkbox"  ) {
                if (jQuery(this.el).val() == value) {
                    jQuery(this.el).prop('checked');
                }
            } else if (jQuery(this.el).prop('type') == "time"  ) {
                // converto i secondi in time
                if (value > 0) {
                    let hours = Math.floor(value / 3600);
                    let minutes = Math.floor(value / 60) % 60;
                    let seconds = value % 60;
                    let time = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
                    jQuery(this.el).val(time);
                } else {
                    jQuery(this.el).val(value);
                }
            } else if (jQuery(this.el).prop('type') == "date") {
                if (value > 0) {
                    let date = new Date(value * 1000);
                    let day = date.getDate();
                    let month = date.getMonth() + 1;
                    let year = date.getFullYear();
                    let time = year + '-' + month.toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0');
                    jQuery(this.el).val(time);
                } else {
                    jQuery(this.el).val(value);
                }
            } else if (jQuery(this.el).prop('type') == "datetime-local") {
                if (value > 0) {
                    let date = new Date(value * 1000);
                    let day = date.getDate();
                    let month = date.getMonth() + 1;
                    let year = date.getFullYear();
                    let hours = date.getHours();
                    let minutes = date.getMinutes();
                    let seconds = date.getSeconds();
                    let time = year + '-' + month.toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0') + 'T' + hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
                    jQuery(this.el).val(time);
                } else {
                    jQuery(this.el).val(value);
                }
            } else if (jQuery(this.el).hasClass('js-dbp-checboxes-value')) {
                if (typeof(value) == 'object') {
                    $field_label = jQuery(this.el).data('field_label');
                    $field_label.find('[type=checkbox]').prop('checked', false);
                    $field_label.find('[type=checkbox]').each(function() {
                        if (value.indexOf(jQuery(this).val()) > -1) {
                            jQuery(this).prop('checked', true);
                        }
                       
                    });
                }
            } else if (jQuery(this.el).hasClass('js-dbp-radio-value')) {
                $field_label = jQuery(this.el).data('field_label');
                $field_label.find('[type=radio]').prop('checked', false);
                $field_label.find('[type=radio]').each(function() {
                    if (value == jQuery(this).val()) {
                        jQuery(this).prop('checked', true);
                    }
                });
                jQuery(this.el).val(value);
            } else {
                jQuery(this.el).val(value);
            }
        }


        let val = jQuery(this.el).val();
        if (parseFloat(val) == val) {
            val = parseFloat(val);
        }
        if (jQuery(this.el).prop('type') == "checkbox" && !jQuery(this.el).is(':checked') ) {
            val = null;
        }
        if (jQuery(this.el).hasClass('js-dbp-checboxes-value')) {
            if (val == "") {
                val = "";
            } else {
                val = JSON.parse(val);
            }
        }
        if (jQuery(this.el).hasClass('js-add-tinymce-editor') && jQuery(this.el).data('tny_editor') == "t") {
            val =  tinyMCE.get(jQuery(this.el).prop('id')).getContent();
        }
        if (jQuery(this.el).hasClass('js-add-codemirror-editor') && jQuery(this.el).data('cm_editor')) {
            cm_editor = jQuery(this.el).data('cm_editor');
            val =  cm_editor.codemirror.getValue();
        }
        if (jQuery(this.el).prop('type') == "time"  ) {
            if (val != "") {
                val = val.split(":");
                val = parseInt(val[0])*3600 + parseInt(val[1])*60;
            }
        }
        if (jQuery(this.el).prop('type') == "date"  ) {
            if (val != "") {
                val = val.split("-");
                val = new Date(val[0], val[1]-1, val[2]);
                val = val.getTime()/1000;
            }
        }
        if (jQuery(this.el).prop('type') == "datetime-local"  ) {
            if (val != "") {
                val = val.split("T");
                let date = val[0].split("-");
                let time = val[1].split(":");
                val = new Date(date[0], date[1]-1, date[2], time[0], time[1], time[2]);
                val = val.getTime()/1000;
            }
        }

        if (jQuery(this.el).hasClass('js-dbp-autocoplete-id-title')) {
            $hidden_field = jQuery(this.el).data('dbp-autocomplete-id');
            val = $hidden_field.val();
        }


        return val;
    };
    this.isValid = function() {
        if (this.el == false) return false;
        return this.el.checkValidity();
    };
    this.valid  = function(condition, msg) {
        if (this.el == false) return false;
        jQuery(this).addClass('js-dbp-add-show-error-border');
        if (condition) {
            this.el.setCustomValidity("");
        } else {
            if (typeof(msg) == 'undefined' )  {
                msg = "Invalid field";
            }
            this.el.setCustomValidity(msg);
            if (msg != "" && jQuery(el).parents('.js-dbp-form-row').css('display') != 'none' && this.status == "field_change") {
                this.el.reportValidity();
            }
            jQuery(this.el).addClass('js-dbp-validity')
        }
        return this;
    };
    this.toggle = function(condition) {
        if (this.el == false) return false;
        if (condition) {
            jQuery(this.el).parents('.js-dbp-form-row').slideDown();
        } else {
            jQuery(this.el).parents('.js-dbp-form-row').slideUp();
        }
        return this;
    };
    this.addClass = function(class_name) {
        jQuery(this.el).parents('.js-dbp-form-row').addClass(class_name);
        return this;
    };
    this.removeClass = function(class_name) {
        jQuery(this.el).parents('.js-dbp-form-row').removeClass(class_name);
        return this;
    };

    this.msg = function(string) {
        let $row =  jQuery(el).parents('.js-dbp-form-row');
        if (string == "") {
            $row.find('.dbp-form-field-footer').slideUp();
        } else {
            $row.find('.dbp-form-field-footer').slideDown();
        }
        $row.find('.dbp-form-field-footer').html(string);
        return this;
    };

    this.valid_date = function() {
        let d = new Date(this.val());
        jQuery(this).addClass('js-dbp-add-show-error-border');
        let valid =  d instanceof Date && !isNaN(d);
        this.valid(valid, 'Invalid date');
        return valid;
    }

    this.valid_range = function(c_min, c_max) {
        $el = jQuery(this.el);
        jQuery(this).addClass('js-dbp-add-show-error-border');
        set_min = (typeof(c_min) !== 'undefined' && c_min !== false && c_min !== "");
        set_max = (typeof(c_max) !== 'undefined' && c_max !== false && c_max !== "");
        if (($el.prop('type') == 'number' || $el.prop('type') == 'date' ||  $el.prop('type') == 'datetime-local' ) && ( ($el.prop('min') != c_min && set_min) || ($el.prop('max') != c_max) && set_max)) {
            if (set_min)  {
                $el.prop('min',c_min);
            }
            if (set_max)  {
                $el.prop('max',c_max);
            }
        } else if ($el.prop('type') != 'number' && $el.prop('type') != 'date' && $el.prop('type') != 'datetime-local' ) {
            let d = parseFloat(this.val());
            if (set_min && set_max)  {
                let valid = (!isNaN(d) && d >= c_min && d <= c_max);
                this.valid(valid, 'Invalid range ('+c_min+', '+c_max+')');
            } else if (set_min)  {
                let valid = (!isNaN(d) && d >= c_min );
                this.valid(valid, 'The value must be greater or equal to '+c_min);
            }  else if (set_max)  {
                let valid = (!isNaN(d) && d <= c_max );
                this.valid(valid, 'The value must be less than or equal to '+c_max);
            }
           
        }
        return this.el.checkValidity();
    }
    this.choices = function(values) {
        if (jQuery(this.el).is("select")) {
            val = jQuery(this.el).val();
            jQuery(this.el).empty();
            if (Array.isArray(values) ) {
                for (x in values) {
                    option = jQuery('<option></option>').prop('value', values[x]).html(values[x]);
                    jQuery(this.el).append(option);
                }
            } else {
                for (x in values) {
                    option = jQuery('<option></option>').prop('value', x).html(values[x]);
                    jQuery(this.el).append(option);
                }
            }
            jQuery(this.el).val(val);
        }
        return this;
    }
    this.required = function(condition) {
        if (condition) {
            jQuery(this.el).addClass('js-dbp-validity').attr("required", "required");
        } else {
            jQuery(this.el).removeClass('js-dbp-validity').removeAttr("required");
        }
    }
    this.name = function() {
        return jQuery(this.el).data('dbp_js_rif');
    }
}


function Dbp_form(curr_el) {
    this.form = jQuery('#dbp_edit_details');
    this.curr_el = jQuery(curr_el);
    temp_curr = jQuery(curr_el).data('dbp_js_rif').split('.');
    this.current_count = temp_curr[2];

    this.get = function(val, count) {
        if (typeof(count) == 'undefined' )  {
            count = 0;
        }
        temp_val = val.split(".");
        if (temp_val.length == 2) {
            val_js_rif = val+"."+this.current_count;
        } else  if (temp_val.length == 3) {
            if (temp_val[2] == "next") {
                let next = parseInt(this.current_count)+1;
                val_js_rif = temp_val[0]+"."+temp_val[1]+"."+next;
            } else if (temp_val[2] == "prev") {
                let prev = parseInt(this.current_count)-1;
                val_js_rif = temp_val[0]+"."+temp_val[1]+"."+prev;
            } else {
                val_js_rif = val;
            }
        } else {
            val_js_rif = val;
        }
        els = this.form.find("[data-dbp_js_rif='" + val_js_rif + "']");
        if (els.length > count) {
            let form_field = new Dbp_field(els.get(count));
            return form_field;
        }
        els = this.form.find("[data-dbp_label='" + val + "']");
        if (els.length > count) {
            let form_field = new Dbp_field(els.get(count));
            return form_field;
        }
        els = this.form.find("[data-dbp_name='" + val + "']");
        if (els.length > count) {
            let form_field = new Dbp_field(els.get(count));
            return form_field;
        }
        
       // console.warn('NO ELEMENT by js_rif '+val_js_rif+' OR '+val+' FOUND!');

       return new Dbp_field(false);
    }
    //TODO ? permetto di aggiornare un campo
    this.update = function(form, id_value) {
        jQuery('#dbp_form_table_name_'+form).empty().append('<div class="dbp-sidebar-loading"><div  class="dbp-spin-loader"></div></div>');
    }
    
}

function dbp_remove_attachment(el) {
    jQuery(el).parents('.js-dbp-form-row').find('.js-attachment-value').val('');
    jQuery(el).parent().remove();
}

/**
 * IMMAGINI
 */
var frame;

function upload_image_button(el) {
    metaBox = jQuery(el).parents('.js-attachment-box');
    metaBox.find('.js-dbp-error-input-validate').removeClass('js-dbp-error-input-validate');
    jQuery('.js-attachment-box').removeClass('js-attachment-box-selected');
    metaBox.addClass('js-attachment-box-selected');
    //var  addImgLink = metaBox.find('.upload-custom-img'),
    //delImgLink = metaBox.find( '.delete-custom-img'),
    //imgContainer = metaBox.find( '.js-custom-img-container'),
    //imgIdInput = metaBox.find( '.custom-img-id' );
    // If the media frame already exists, reopen it.
    if ( frame ) {
        frame.open();
        return;
    }

    // Create a new media frame
    frame = wp.media({
        title: 'Select or Upload Media Of Your Chosen Persuasion',
        button: {
            text: 'Use this media'
        },
        multiple: false  // Set to true to allow multiple files to be selected
    });

    // When an image is selected in the media frame...
    frame.on( 'select', function() {
        metaBox = jQuery('.js-attachment-box-selected');
        // Get media attachment details from the frame state
        var attachment = frame.state().get('selection').first().toJSON();
        // Send the attachment URL to our custom image input field.
        metaBox.find( '.js-custom-img-container').empty().append( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );

        metaBox.find( '.js-media-gallery-name').empty().append( '<a href="'+attachment.url+'" target="_blank">'+attachment.title+'</a>' ).removeClass( 'hidden' );
        // Send the attachment id to our hidden input
        metaBox.find( '.custom-img-id' ).val( attachment.id );
        // Hide the add image link
        metaBox.find('.upload-custom-img').addClass( 'hidden' );
        // Unhide the remove image link
        metaBox.find( '.delete-custom-img').removeClass( 'hidden' );
    });
    // Finally, open the modal on click
    frame.open();
    
}


function dbp_del_img_link(el) {


    let countainer_multiple_row =  jQuery(el).parents('.js-container-multiple-fields');
    // se esiste un container multiple row e ci sono più righe al suo interno
    let multi_row = false;
    if (countainer_multiple_row.length == 1) {  
        let row_inserted = countainer_multiple_row.find('.js-dbp-form-row');
        if (row_inserted.length > 1) {
            jQuery(el).parents('.js-dbp-form-row').remove();
            multi_row = true;
        }
    } 
    
    if (!multi_row) {
        metaBox = jQuery(el).parents('.js-attachment-box');
        var  addImgLink = metaBox.find('.upload-custom-img'),
        delImgLink = metaBox.find( '.delete-custom-img'),
        imgContainer = metaBox.find( '.js-custom-img-container'),
        imgIdInput = metaBox.find( '.custom-img-id' );
        imgContainer.empty();
        // Un-hide the add image link
        addImgLink.removeClass( 'hidden' );
        // Hide the delete image link
        delImgLink.addClass( 'hidden' );
        metaBox.find( '.js-media-gallery-name').empty().addClass( 'hidden' );
        // Delete the image id from the hidden input
        imgIdInput.val( '' );
    }
}

/**
 * SETTO GLI EDITOR 
 * @param {jQuery} $form 
 */

function gp_form_add_editor($form) {
    $form.find('.js-add-tinymce-editor').each(function() {
        id = jQuery(this).prop('id');
        window.wp.editor.initialize(id, window.wp.editor.getDefaultSettings());
        jQuery(this).data('tny_editor', 't');
    });
    
    $form.find('.js-add-codemirror-editor').each(function() {
        if (typeof(jQuery(this).data('cm_editor')) == 'undefined') {
            var codeMirror_ext = wp.codeEditor.initialize(this, {
                'codemirror':{
                    mode: "htmlmixed",
                    lineNumbers: true
                }
            });
            codeMirror_ext.codemirror.setSize('100%', '300px');
            jQuery(this).data('cm_editor', codeMirror_ext);
        }
    });
    
}

function choose_post_status(status, el) {
    const $form_row = jQuery(el).parents('.js-dbp-form-row');
    $form_row.find('.js-input-status').val(status);
    $form_row.find('.js-adfo-status').removeClass('dbp-btn-status-selected');
    jQuery(el).addClass('dbp-btn-status-selected');
}
