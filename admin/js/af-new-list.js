/**
 * Il bottone per salvare una query
 */
function dbp_create_list_show_form( from_sql ) {
    dbp_open_sidebar_popup('save_list');
    dbp_close_sidebar_loading();
    let $form = jQuery('<form class="dbp-form-save-query dbp-form-edit-row" id="dbp_form_save_new_query" action="'+dbp_admin_post+'"></form>');

    $form.append('<input type="hidden" name="page" value="admin_form"><input type="hidden" name="section" value="list-add"><input type="hidden" name="action" value="dbp_create_list">');
    
    if (from_sql) {
        $form.append('<p class="dbp-alert-gray">Save the query. Then you will have the shortcode to view the table on the website.</p>');
    } else {
        $form.append('<p class="dbp-alert-gray">Create a new form. This way you can extract data from a table and show it on your website.</p>'); 
    }

    $field_row = jQuery('<div class="dbp-form-row"></div>');
    $field_row.append('<label><span class="dbp-form-label">Name</span></label><input type="text" class="form-input-edit" name="new_title" id="dbp_name_create_list">');
    $form.append($field_row);
    new_title = "query_"+ dbp_uniqid();
    if (document.getElementById('sql_query_edit')) {
        code = document.getElementById('sql_query_edit').dbp_editor_sql;
        
        let get_first_row = jQuery('#sql_query_edit').val().toLowerCase();
        if (typeof(code) != "undefined") {
            get_first_row = code.codemirror.getValue().toLowerCase();
        }
    
        let temp_name = get_first_row.split("from");
       
        if (temp_name.length > 1) {
            let temp_name2 = temp_name[1].trim().split(" ");
            if (temp_name2.length > 0 && temp_name2[0].length > 2) {
                new_title = temp_name2[0].trim().replace(/ .*/,'').replaceAll('`','').substring(0,20);
            } else {
                new_title = temp_name[1].trim().replace(/ .*/,'').replaceAll('`','').substring(0,20);
            }
        }
    }
    $field_row.find('#dbp_name_create_list').val(new_title);

    $field_row  = jQuery('<div class="dbp-form-row"><label><span class="dbp-form-label">Description</span><textarea  class="form-textarea-edit" name="new_description"></textarea></label></div>');
    $form.append($field_row);

    if (from_sql) {
        $form.append('<div class="dbp-form-row"><span class="dbp-form-label">Choose with query use</span>');
        $form.append('<div class="dbp-dropdown-line-flex"><span style="margin-right:.5rem"><input name="choose_tables_query" type="radio" checked="checked" value="sql_query_executed"></span><div class="dbp-xmp">If you used filters to limit your data, save the query with the filtered results</div></div>');
        $form.append('<div class="dbp-dropdown-line-flex"><span style="margin-right:.5rem"><input name="choose_tables_query" type="radio" value="sql_query_edit"></span><div class="dbp-xmp">Show all data without filters</div></div>');
        $form.append('<textarea style="display:none" id="dbp_sql_new_list" name="new_sql"></textarea>');
    } else {
        $form.append('<div class="dbp-form-row"><span class="dbp-form-label">1. Choose what</span>');

        dbp_draw_grid_radio( $form);


        $select_tables = jQuery('<select name="mysql_table_name"></select>');
        dbp_tables.sort();
        for (x in dbp_tables ) {
            $select_tables.append('<option value="'+dbp_tables[x]+'">'+dbp_tables[x]+'</option>');
        }
        $select_tables.change(function() {
            if (jQuery(this).val() == dbp_post_table) {
                // Mostro il campo per il post type
                jQuery('#dbp_new_post_type').css('display','block');
                // faccio lo scroll fino in fondo del div id=dbp_dbp_content
                jQuery('#dbp_dbp_content').animate({scrollTop: jQuery('#dbp_dbp_content').prop("scrollHeight")}, 500);
                // metto il focus al campo per il post type
                jQuery('#dbp_new_post_type input').focus();
            } else {
                jQuery('#dbp_new_post_type').css('display','none');
            }
        });

        $form_row = jQuery('<div class="dbp-form-row" id="dbp_sql_select_tables" style="display:none"></div>');
        $form_row.append('<label><span class="dbp-form-label">2. Choose existing table</span></label>');
        $form_row.append($select_tables);

        // POST TYPE
        
        $form_row_post_type = jQuery('<div class="dbp-css-mt-2 dbp-form-row" id="dbp_new_post_type" style="display:none"></div>');
        $form_row_post_type.append('<label><span class="dbp-form-label">Post Type</span></label>');
        $form_row_post_type.append('<p class="dbp-css-mt-0">If you want to filter per post type enter the name here. This way you will be able to generate lists that link to new wordpress pages.</p>');
        $form_row_post_type.append('<input type="text" class="form-input-edit" name="new_post_type" value="">');
        $form.append($form_row);
        $form.append($form_row_post_type);
        $form.append('<div><br><br></div>');

    }

    jQuery('#dbp_dbp_content').append($form);
    $form.find('#dbp_name_create_list').select();
    $field_row.append('<input type="hidden" class="form-input-edit" name="new_sort_field" value="'+jQuery('#dbp_table_sort_field').val()+'">');
    $field_row.append('<input type="hidden" class="form-input-edit" name="new_sort_order" value="'+jQuery('#dbp_table_sort_order').val()+'">');

    jQuery('#dbp_dbp_title > .dbp-edit-btns').remove();
    jQuery('#dbp_dbp_title').append('<div class="dbp-edit-btns"><h3>New Form</h3><div id="dbp-bnt-edit-query" class="dbp-submit" onclick="dbp_save_sql_query()">Save</div></div>');

    if (!from_sql) {
        jQuery(".js-radio-table-choose:radio").change(function() {
            selected_value = jQuery(".js-radio-table-choose:checked").val();
            if (selected_value == 'create_new_table') {
                jQuery('#dbp_sql_select_tables').css('display','none');
                jQuery('#dbp_new_post_type').css('display','none');
            } else  if (selected_value == 'create_new_post_type') {
                jQuery('#dbp_sql_select_tables').css('display','none');
                jQuery('#dbp_new_post_type').css('display','none');
                jQuery('#dbp_dbp_content').animate({scrollTop: jQuery('#dbp_dbp_content').prop("scrollHeight")}, 500);
                jQuery('#dbp_new_post_type input').focus();
            } else {
                jQuery('#dbp_sql_select_tables').css('display','block');
                if (jQuery('#dbp_sql_select_tables select').val() == dbp_post_table) {
                    jQuery('#dbp_new_post_type').css('display','block');
                    jQuery('#dbp_new_post_type input').focus();
                } else {
                    jQuery('#dbp_new_post_type').css('display','none');
                }
                jQuery('#dbp_dbp_content').animate({scrollTop: jQuery('#dbp_dbp_content').prop("scrollHeight")}, 500);
            }
        });
    }
    
}
/**
 * Invio la form per la creazione di una nuova lista
 */
function dbp_save_sql_query() {
   let sql_id =  jQuery("input:radio[name=choose_tables_query]:checked").val();
   let sql = jQuery('#'+sql_id).val();
   jQuery('#dbp_sql_new_list').val(sql);
   if (jQuery('#dbp_name_create_list').val() == "") {
       alert("Name is required");
       jQuery('#dbp_name_create_list').focus();
       return false;
   }
   selected_value = jQuery(".js-radio-table-choose:checked").val();

   jQuery('#dbp_form_save_new_query').submit();
}


function dbp_draw_grid_radio($container) {
    $grid_container = jQuery('<div class="dbp-create-form-container"></div>');
    $grid_container.append('<div class="dbp-create-form-item js-new-item-grid-item dbp-create-form-item-checked"><label class="grid-item-label"><input type="radio" class="js-radio-table-choose" name="choose_tables_query" value="create_new_table" checked>Create a New Table</label></div>');
    $grid_container.append('<div class="dbp-create-form-item js-new-item-grid-item"><label class="grid-item-label"><input type="radio" class="js-radio-table-choose" name="choose_tables_query" value="choose_table_from_db">Choose an existing table</label></div>');
    $grid_container.append('<div class="dbp-create-form-item js-new-item-grid-item"><label class="grid-item-label"><input type="radio" class="js-radio-table-choose" name="choose_tables_query" value="create_new_post_type">Create a different types of content in your site (Post Type)</label></div>');
    
    $grid_container.find('.js-new-item-grid-item').each(function() {
        $this = jQuery(this);
       
        $this.append('<div class="grid-item-icon"></div>');
        // ogni elemento se cliccato deve selezionare un radio nascosto
        $this.click(function() {
            jQuery(this).find('input').prop('checked',true).change();
            jQuery('.js-new-item-grid-item').removeClass('dbp-create-form-item-checked');
            jQuery(this).addClass('dbp-create-form-item-checked');
        });
    });
    

    $container.append($grid_container);
}

