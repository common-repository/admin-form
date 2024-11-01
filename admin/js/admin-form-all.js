
var doc_pina_history = [];
var dbp_cm_variables = ['ADD', 'ALL', 'ALTER', 'ANALYZE', 'EXPLAIN', 'AND', 'AS', 'ASC', 'BEGIN', 'BETWEEN', 'BOTH', 'BY', 'CALL', 'CASE', 'COLLATE', 'COMMIT','CONCAT', 'COUNT', 'CREATE', 'CURSOR', 'DATABASE', 'DEFAULT', 'DELETE', 'DESC', 'DISTINCT', 'DROP', 'EACH', 'ELSE', 'ELSEIF', 'END', 'FIELD', 'FOR', 'FROM', 'GLOBAL', 'GROUP BY', 'GROUP', 'HAVING', 'IF', 'IN', 'INDEX', 'INNER', 'INSERT', 'INTO', 'IS', 'JOIN', 'LIKE', 'NOT', 'ON', 'ORDER', 'OR', 'OUTER', 'SHOW', 'PROCESSLIST' ,'LEFT', 'RIGHT', 'SELECT', 'SET', 'TABLE', 'TABLES', 'UNION', 'UPDATE', 'VALUES', 'WHERE', 'LIMIT', 'TEMPORARY', 'FLUSH', 'PRIVILEGES'];
/**
 * Tutte le pagine di amministrazione
 */
jQuery(document).ready(function () {
    /**
     * Tabs
     */
    jQuery('#dbp_container').fadeIn('fast');
    var sidebar_section = jQuery('#sidebar-tabs').data('section');
    jQuery('#sidebar-tabs .js-sidebar-block').each(function() {
        if (sidebar_section == jQuery(this).data('open')) {
            jQuery(this).addClass('dbp-open-sidebar');
        }
    });
    jQuery('#sidebar-tabs .js-sidebar-title').click(function() {
        jQuery('#sidebar-tabs .js-sidebar-block').removeClass('dbp-open-sidebar');
        jQuery(this).parents('.js-sidebar-block').addClass('dbp-open-sidebar');
		set_cookie('_dbp_sidebar_open', jQuery(this).data('dbpname'));
    });

    /**
     * documentazione
     */
    pina_doc_ajax_free = false;
	jQuery.ajax({
		type : "post",
		dataType : "json",
		url : ajaxurl,
		data : {action: "dbp_get_documentation", get_page: jQuery('#dbp_documentation_box').data('homepage')},
		success: function(response) {
			pina_first_page = pina_last_page = response.doc;
			dbp_sidebar_documentation_menu();
			$dhc = jQuery('<div id="dbp_help_content" class="dbp-animate-bg"></div>');
			$dhc.data('curr_page', response.page);
			$dhc.append(response.doc);
			jQuery('#dbp_help_content').append($dhc);

			pina_convert_link_doc();
		},
		complete: function(response) {
			pina_doc_ajax_free = true;
		}
	});

	// quando la sidebar è aperta i link devono essere controllati con un confirm
	jQuery('a').click(function() {
		sidebar_status = jQuery('#dbp_dbp_content').data('dbpstatus');
		if (sidebar_status != "" && typeof sidebar_status != 'undefined') {
			return confirm( "Do you want to leave the page? any changes will be lost" );
		}
	})

	 /**
     * COLORO Le QUERY ESEGUITA
     */
	jQuery('.js-dbp-mysql-query-text').each(function() {
        jQuery(this).html(query_color(jQuery(this).text()));
    });

	/**
	 * Gestisco i link di tipo detail view
	 */
	 setup_dbp_popup_frontend();
});


/**
 * Coloro un testo passato con le istruzioni delle query 
 */
 function query_color (query_text) {
    let new_text = [];
    query_text.split(" ").forEach(function (item) {
        var item = item.replace(/[\u00A0-\u9999<>\&]/g, function(i) {
            return '&#'+i.charCodeAt(0)+';';
         });
        if (dbp_cm_variables.indexOf(item.toUpperCase()) != -1) {
            new_text.push('<span class="dbp-cm-keyword">' + item.toUpperCase() + '</span>');
        } else {
            new_text.push(item);
        }
    });
    return new_text.join(" ");
}

/**
 * Converte i link della documentazione
 */
function pina_convert_link_doc() {
	if ( document.getElementById('dbp_dbp_content') != null) {
		jQuery('#dbp_help_content').find('a').click(function(e) {
			if (jQuery(this).hasClass('js-simple-link')) {
				return true;
			}
			e.stopPropagation();
			page_url = jQuery(this).prop('href');
			doc_pina_history.push(page_url);
			pina_doc_ajax_free = false;
			dbp_doc_load_link(page_url);
			return false;
		});
		document.getElementById('dbp_dbp_content').scrollTop = 0;
	}
}

/**
 * Scorre nella documentazione ad un elemento preciso della pagina
 */
function anchor_help(file, anchor) {
	// apri il tab help
	// verifica se sei già nella pagina corretta
	// se no apri la pagina
	// scrolla fino al punto richiesto
	dbp_open_sidebar_popup('help');
	jQuery('#dbp_dbp_title > .dbp-edit-btns').remove();
	jQuery('#dbp_dbp_title').append('<div class="dbp-edit-btns"><h3>Help</h3><div id="dbp-bnt-edit-query" class="dbp-btn-cancel" onclick="dbp_close_sidebar_popup()">CANCEL</div></div>');

	let curr_status = jQuery('#dbp_dbp_content').data('dbpstatus');
	if (curr_status =='help' ) {
		if (file  ==  jQuery('#dbp_help_content').data('curr_page')) {
			anchor_help_scroll(anchor);
		} else {
			dbp_doc_load_link(ajaxurl+'?action=dbp_get_documentation&get_page='+file, anchor);
		}
	} else {
		dbp_doc_load_link(ajaxurl+'?action=dbp_get_documentation&get_page='+file, anchor);
	}
	
}

function anchor_help_scroll(anchor) {
	if (jQuery('#dbp_help_'+anchor).length == 1) {
		jQuery('#dbp_help_content').css('background','#CCC');
		jQuery('.dbp_help_div').css('background','#CCC');
		jQuery('#dbp_dbp_content').animate({
			scrollTop: jQuery('#dbp_help_'+anchor).position().top - 50
		}, 500);
		jQuery('#dbp_help_'+anchor).css('background','#FFF');
		jQuery('#dbp_help_'+anchor+" .dbp_help_div").css('background','#FFF');
		setTimeout(function() {
			jQuery('#dbp_help_content').css('background','#FFF');
			jQuery('.dbp_help_div').css('background','#FFF');
		}, 5000);
	} else {
		jQuery('#dbp_dbp_content').animate({scrollTop: 0}, 100);
	}
}


/**
 * genera un id univoco
 */
 var __unid = 0;
 var __last_unid = 0;
 var __used = [];
 function dbp_uniqid() {
	__unid = __unid + 1;
	let new_last = Math.floor(((Date.now() - Math.floor(Date.now() / 100000000)*100000000)) / 10000) * 100000;
	if (new_last != __last_unid) {
	 __last_unid = new_last;
	 __unid = 0;
	}
   let num = __last_unid + __unid;
   let r = "u_"+num.toString(36);
   if (__used.indexOf(r) != -1) {
	   return dbp_uniqid();
   } else {
	   __used.push(r);
	   return r;
   }
 }
 
 
/**
 * Chiude il popup della sidebar
 */
function dbp_close_sidebar_popup() {
	jQuery('#dbp_container').css('overflow','hidden');
 	jQuery('#dbp_sidebar_popup').animate({'right':'-200px', 'opacity':0}, 200, function() {jQuery(this).css('display','none');  jQuery('#dbp_container').css('overflow','');});
	jQuery('#dbp_dbp_content').data('dbpstatus','');
	jQuery('#dbp_dbp_loader').css('display','none');
	jQuery('#dbp_dbp_title .dbp-edit-btns .js-sidebar-btn').removeClass('dbp-btn-disabled js-btn-disabled');
}

/**
 * Apro il popup della sidebar
 * @param String status dice cosa ha aperto per vedere se devo riaprirlo o fare altro. 
 */
function dbp_open_sidebar_popup(status) {
	let curr_status = jQuery('#dbp_dbp_content').data('dbpstatus');
	if (curr_status == status && (curr_status == 'delete' || curr_status == 'help') ) {
		return 'already_open';
	} else {
		jQuery('#dbp_dbp_title > .dbp-edit-btns').remove();
		jQuery('#dbp_container').css('overflow','hidden');
		jQuery('#dbp_dbp_content').empty();
		jQuery('#dbp_sidebar_popup').css({'opacity':0,'right':'-200px','display':'flex'});
		jQuery('#dbp_sidebar_popup').animate({'right':0, 'opacity':1}, 200, function() {jQuery('#dbp_container').css('overflow','');});
		jQuery('#dbp_dbp_content').data('dbpstatus', status);
		dbp_open_sidebar_loading(true);
		return 'new';
	} 
}



/**
 * Gestisco il loading del popup
 */
function dbp_close_sidebar_loading() {
	jQuery('#dbp_dbp_content').css('display','block');
	jQuery('#dbp_dbp_loader').css('display','none');
	jQuery('#dbp_dbp_title > .dbp-edit-btns').children().each(function() {
		if (this.tagName.toLowerCase() != "h3") {
			jQuery(this).css('display','inline-block');
		} 
	});
	jQuery('#dbp_dbp_title .dbp-edit-btns .js-sidebar-btn').removeClass('dbp-btn-disabled js-btn-disabled');
	jQuery('#dbp_dbp_close').css('display','block');
}
/**
 * 
 * @param boolean show_title se true lascia il titolo dellla sidebar ma disabilita i bottoni che hanno la classe js-sidebar-btn
 */
function dbp_open_sidebar_loading(show_title = false) {
	jQuery('#dbp_dbp_content').css('display','none');
	jQuery('#dbp_dbp_loader').css('display','block');
	if (!show_title) {
		jQuery('#dbp_dbp_title > .dbp-edit-btns').children().each(function() {
			if (this.tagName.toLowerCase() != "h3") {
				jQuery(this).css('display','none');
			} 
		});
	} else {
		jQuery('#dbp_dbp_title .dbp-edit-btns .js-sidebar-btn').addClass('dbp-btn-disabled js-btn-disabled');
	}

	jQuery('#dbp_dbp_close').css('display','none');
}

/**
 * Gestione dei messaggi in cookie
 */
jQuery(document).ready(function () {
	let ck_msg = get_cookie('dbp_msg');
	if (! (ck_msg === null) && ck_msg != '') {
		//console.log ("TODO SISTEMARE LA GESTIONE DEI MESSAGGI CON COOKIE ck_msg: "+ck_msg);
		if (jQuery('#dbp_cookie_msg').length == 1) {
			jQuery('.js-alert-snackbar').fadeOut();
			jQuery('#dbp_cookie_msg').html(ck_msg).fadeIn();
			delete_cookie('dbp_msg');
		}
	}
	setTimeout(()=>{ jQuery('.js-alert-snackbar').fadeOut();}, 5000);
});

/**
 * Setta un cookie
 * @param {String} name 
 * @returns {String}
 */

function set_cookie(cname, cvalue) {
	var d = new Date();
	d.setTime(d.getTime() + (60*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
/**
 * Ritorna un cookie
 * @param {String} name 
 * @returns {String}
 */
function get_cookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
		c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
		return c.substring(name.length, c.length);
		}
	}
	return "";
}


function delete_cookie( name ) {
	if( get_cookie( name ) ) {
	  document.cookie = name + "=" +
		((dbp_cookiepath) ? ";path="+dbp_cookiepath:"")+
		((dbp_cookiedomain)?";domain="+dbp_cookiedomain:"") +
		";expires=Thu, 01 Jan 1970 00:00:01 GMT";
	}
  }

 /**
  * help filtro tabelle/campi sql
  */


 function dbp_help_filter(el) {
	
	let $ul_rif = jQuery('#'+jQuery(el).data('idfilter'));
	let val = jQuery(el).val().toLowerCase();
	$ul_rif.children().each(function() {
		let text = jQuery(this).find('.js-dbp-table-text').text();
		let found_field = false;
		if (text.toLowerCase().indexOf(val) == -1 ) {
			jQuery(this).find('ul').children().each(function() {
				let text = jQuery(this).find('.js-dbp-field-text').text();
				if (val == '' || text.toLowerCase().indexOf(val) > -1 ) {
					jQuery(this).css('display','block');
					found_field = true;
				} else {
					jQuery(this).css('display','none');
				}
			});
		} else {
			jQuery(this).find('ul').children().css('display','block');
		}
		if (val == '' || text.toLowerCase().indexOf(val) > -1 || found_field) {
			jQuery(this).css('display','block');
		} else {
			jQuery(this).css('display','none');
		}
	
	});
 }

 /**
  * help filtro tabelle
  */
  function dbp_help_filter2(el) {
	
	let $ul_rif = jQuery('.'+jQuery(el).data('classfilter'));
	let val = jQuery(el).val().toLowerCase();
	$ul_rif.each(function() {

		jQuery(this).children().each(function() {
			let text = jQuery(this).find('.js-dbp-table-text').text();
			if (val == '' || text.toLowerCase().indexOf(val) > -1) {
				jQuery(this).css('display','block');
			} else {
				jQuery(this).css('display','none');
			}
		
		});


	});
 }


 /**
  * help filtro del search
  */

 function dbp_help_search(el) {
	
	let $ul_rif = jQuery('#'+jQuery(el).data('idfilter'));
	let val = jQuery(el).val().toLowerCase();
	$ul_rif.children().each(function() {
		let text = jQuery(this).find('.js-dbp-table-text').text();
		let found_field = false;
		if (val == '' || text.toLowerCase().indexOf(val) > -1 || found_field) {
			jQuery(this).css('display','block');
		} else {
			jQuery(this).css('display','none');
		}
	
	});
 }


/**
 * Monstra/nasconde i link per le variabili shortcode ogni volta che si cambia il textarea di default bisogna richiamare di nuovo questa funzione.
 * @param jQuery $el 
 */
 function dbp_show_pinacode_link($el, display) {
	if (!$el.parent().hasClass('js-dbp-wrap')) {
		$wrap = jQuery('<div class="dbp-wrap js-dbp-wrap"></div>');
	} else {
		$wrap = $el.parent();
	}
	if (typeof display != 'undefined') {
		$wrap.css('display', display);
	} else {
		$wrap.css('display', $el.css('display'));
	}
	if (!$el.parent().hasClass('js-dbp-wrap')) {
		$el.wrap( $wrap );
		if (typeof dbp_pinacode_vars != 'undefined') {
			$el.parent().append('<div ><span class="dbp-link-click" onclick="show_pinacode_vars()">show shortcode variables</span></div>');
		}
	}
 }

 /**
  * Disegna gli shortcode presenti nella pagina
  */
 function show_pinacode_vars(add_var) {

	dbp_open_sidebar_popup('help');
	if (typeof dbp_pinacode_vars != 'undefined') {
		dbp_close_sidebar_loading();
		jQuery('#dbp_dbp_content').empty();
		jQuery('#dbp_dbp_title').append('<div class="dbp-edit-btns"><h3>VARIABLES</h3><div id="dbp-bnt-edit-query" class="dbp-btn-cancel" onclick="dbp_close_sidebar_popup()">CANCEL</div></div>');
		$container = jQuery('<div style="padding:1rem"></div>');
		for (x in dbp_pinacode_vars) {
			$container.append('<div class="dbp-sidebar-doc-pinavars">'+dbp_pinacode_vars[x]+'</div>');
		}
		jQuery('#dbp_dbp_content').append($container);
		if (Array.isArray(add_var)) {
			$container.append('<hr>');
			for (x in add_var) {
				if (add_var[x].substring(0,1) == "[") {
					$container.append('<div class="dbp-sidebar-doc-pinavars">'+add_var[x]+'</div>');
				} else {
					$container.append('<div class="dbp-sidebar-doc-pinavars">[%'+add_var[x]+']</div>');
				}
			}
		}
		pina_convert_link_doc();
		$container.append('<br><hr><br><a href="admin.php?page=admin_form_docs&section=pinacode" target="_blank">Guide to the use of variables</a>');

	}
 }

 /**
  * Disegna il menu a tab della documentazione.
  */
 function dbp_sidebar_documentation_menu() {
	jQuery('#dbp_help_content').empty();
	$tabs = jQuery('<div class="pina-doc-tabs"></div>');
	
	if (typeof doc_pina_history != 'undefined' && doc_pina_history.length > 1) {
		$tabs.append('<div onclick="dbp_doc_go_back()" class="pina-doc-tab" title="Go back"><span class="dashicons dashicons-arrow-left-alt2"></span></div>');
	}
	$tabs.append('<a href="'+ajaxurl+'?action=dbp_get_documentation&amp;get_page=index-doc.php" class="pina-doc-tab" title="home"><span class="dashicons dashicons-admin-home"></span></a>');
	$tabs.append('<a href="'+ajaxurl+'?action=dbp_get_documentation&amp;get_page=doc-search.php" class="pina-doc-tab" title="Search in documentation"><span class="dashicons dashicons-search"></span> SEARCH</a>');
	if (typeof dbp_pinacode_vars != 'undefined') {
		$tabs.append('<div onclick="show_pinacode_vars()" class="pina-doc-tab" title="shortcode variables"><span class="dashicons dashicons-shortcode"></span> VARS</div>');
	}
	jQuery('#dbp_help_content').append($tabs);
 }

function dbp_doc_go_back() {
	if (doc_pina_history.length > 0) {
		doc_pina_history.pop();
		page_url = doc_pina_history.pop();
		doc_pina_history.push(page_url);
		dbp_doc_load_link(page_url);

	}
}

function dbp_doc_load_link(page_url, anchor) {
	jQuery.ajax({
		type : "get",
		dataType : "json",
		url : page_url, 
		success: function(response) {
			dbp_sidebar_documentation_menu();
			$dhc = jQuery('<div id="dbp_help_content" class="dbp-animate-bg"></div>');
			$dhc.append(response.doc);
			$dhc.data('curr_page', response.page);
			jQuery('#dbp_dbp_content').empty().append($dhc);
			dbp_close_sidebar_loading();
			pina_convert_link_doc();
			pina_last_page = response.doc;
			if (typeof(anchor) != 'undefined') {
				anchor_help_scroll(anchor);
			} else {
				anchor_help_scroll();
			}
		},
		complete: function(response) {
			pina_doc_ajax_free = true;
		}
	});
}

/**
 * Gestisco in amministrazione I link del frontend 
 */
 function setup_dbp_popup_frontend() {
	let table_filter = document.getElementById('table_filter');
	if (!table_filter) return;
    table_filter.querySelectorAll('.js-dbp-popup').forEach(function(el) {
        if (el.__dbp_data_popup_href == undefined) {
            el.__dbp_data_popup_href = el.getAttribute('href');
            el.removeAttribute('target');
            el.setAttribute('href', "javascript: void(0)");

            el.addEventListener('click', (e) => { 
				dbp_open_sidebar_popup('info');
				jQuery('#dbp_dbp_title > .dbp-edit-btns').remove();
				jQuery('#dbp_dbp_title').append('<div class="dbp-edit-btns"><h3>Frontend link</h3><div id="dbp-bnt-edit-query" class="dbp-btn-cancel" onclick="dbp_close_sidebar_popup()">CANCEL</div></div>');
				dbp_close_sidebar_loading();
				jQuery('#dbp_dbp_content').append('<div class="dbp-alert-gray" style="margin:.4rem">This link is used in the frontend to show a popup with the content details.<br><br>Go to the "Frontend" tab in the "Detailed View" section for settings.</div>');
				return false;
            });
        }
    });
}

function dbp_vote_plugin(msg) {
	jQuery.ajax({
		type : "post",
		dataType : "json",
		url : ajaxurl,
		data : {action: "af_record_preference_vote", msg: msg},
		success: function(response) {
			// chiudo il popup
			jQuery('#dbp_vote_popup').remove();
		}
	});
}