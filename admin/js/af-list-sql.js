jQuery(document).ready(function () {
    dbp_list_sql_add_row ();
});
/**
 * Il bottone per creare nuove righe "FILTER" delle tabelle mysql
 */
 function dbp_list_sql_add_row () {
    $div = jQuery('#dbp_clone_master').clone(true);
    jQuery('#dbp_container_filter').append($div);
    $div.css('display','block');
    $div.removeAttr('id');
 }

 /**
  * Cancello la riga
  */
 function dbp_remove_sql_row(el) {
     jQuery(el).parents('.dbp-form-row').remove();
 }

 /**
  * FILTER: Setto il required sul filtro dal checkbox all'hidden value
  */
 function dbp_required_field(el) {
    jQuery(el).parent().find('.js-filter-required').val( ((jQuery(el).is(':checked'))? 1 : 0) );
 }

function dbp_submit_list_sql(el) {
    checkboxes =0;
    jQuery('.js-add-role-cap').each(function() {
        if (jQuery(this).is(':checked')) {
            checkboxes++;
        }
    })
    if (checkboxes == 0 && jQuery('#cb_show_admin_menu').is(':checked')) {
        alert("You must select at least one role among the permissions");
        return ;
    }
    if ( document.getElementById('sql_query_edit') != null) {
        code = document.getElementById('sql_query_edit').dbp_editor_sql;
        if (typeof code != 'undefined' && code != null) {
            jQuery('#sql_query_edit').value = code.codemirror.getValue();
        }
        jQuery('#sql_query_edit').parents('form').submit();
    } else {
        jQuery(el).parents('form').submit();
    }
   
}

/**
 * 
 * @param DOM el 
 */
function cb_change_toggle_options(el) {
    if (jQuery(el).is(':checked')) {
        jQuery('#admin_menu_options_box').css('display','block');
        jQuery('.js-add-role-cap').first().prop('checked',true);
    } else {
        jQuery('#admin_menu_options_box').css('display','none');
    }
}


function dbp_edit_post_type(el, bool_confirm) {
    if (bool_confirm == true) {
        if (confirm('Are you sure you want to edit the post type?')) {
            jQuery(el).css('display', 'none');
            jQuery('#adfo_edit_post_type_fake').css('display', 'none');
            jQuery('#adfo_edit_post_type').prop('type', 'text');
        }
    } else {
        jQuery(el).css('display', 'none');
        jQuery('#adfo_edit_post_type_fake').css('display', 'none');
        jQuery('#adfo_edit_post_type').prop('type', 'text');
    }
}

function post_status_permission_change(el) {
    // è un select
    const list_roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber', '_all_others'];
    for (role in list_roles) {
        if (jQuery(el).val() == list_roles[role]) {
            jQuery('.js-post-status-permission').each(function() {
                if (el != this) {
                    if (jQuery(this).val() == list_roles[role]) {
                        jQuery(this).val('_no_body');
                    }
                }
            });
        }
    }
}