// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facettes.js,v 1.2 2024/04/04 07:57:37 pmallambic Exp $

function test(elmt_id){
	var elmt_list=document.getElementById(elmt_id);

    if(elmt_list.className.includes('facette_expande')){
        elmt_list.setAttribute('class', 'facette_collapsed');
        elmt_list.querySelector('*[aria-expanded]').setAttribute('aria-expanded', 'false');
    } else {
        elmt_list.setAttribute('class', 'facette_expande');
        elmt_list.querySelector('*[aria-expanded]').setAttribute('aria-expanded', 'true');
    }


    var elmt_list_rows = elmt_list.querySelectorAll('tbody[id^=\'facette_body\'] tr');

	for(i in elmt_list_rows){
		if(elmt_list_rows[i].firstElementChild && elmt_list_rows[i].firstElementChild.nodeName!='TH'){
			if(elmt_list_rows[i].style.display == 'none'){
				elmt_list_rows[i].style.display = 'block';
                elmt_list_rows[i].setAttribute('class', 'facette_tr');
			}else{
				elmt_list_rows[i].style.display = 'none';
                elmt_list_rows[i].setAttribute('class', 'facette_tr_hidden uk-hidden');
			}
		}
	}
}

function valid_facettes_multi(){
	//on bloque si aucune case cochée
	var form = document.facettes_multi;
	for (i=0, n=form.elements.length; i<n; i++){
		if ((form.elements[i].checked == true)) {
			if(document.getElementById('filtre_compare_facette')) {
				document.getElementById('filtre_compare_facette').value='filter';
			}
			if(document.getElementById('filtre_compare_form_values')) {
				document.getElementById('filtre_compare_form_values').value='filter';
			}
			form.submit();
			return true;
		}
	}
	return false;
}

function facettes_add_searchform(datas) {
	var input_form_values = document.createElement('input');
	input_form_values.setAttribute('type', 'hidden');
	input_form_values.setAttribute('name', 'check_facette[]');
	input_form_values.setAttribute('value', datas);
	document.forms[facettes_hidden_form_name].appendChild(input_form_values);
}

function facettes_valid_facette(datas){
	facettes_add_searchform(JSON.stringify(datas));
    document.forms[facettes_hidden_form_name].page.value = 1;
	document.forms[facettes_hidden_form_name].submit();
	return true;
}

function facettes_reinit(is_external=false) {
	if(facettes_get_mode() == 'filter') {
		if(is_external) {
			var params = '&reinit_facettes_external=1';
		} else {
			var params = '&reinit_facettes=1';
		}
    	var req = new http_request();
    	req.request(facettes_ajax_filters_get_elements_url, true, params, true, function(data){
    		document.getElementById('results_list').innerHTML=data;
            facettes_refresh();
    	});
    } else {
        var input_form_values = document.createElement('input');
		input_form_values.setAttribute('type', 'hidden');
		if(is_external) {
			input_form_values.setAttribute('name', 'reinit_facettes_external');
		} else {
			input_form_values.setAttribute('name', 'reinit_facettes');
		}
		input_form_values.setAttribute('value', '1');
		document.forms[facettes_hidden_form_name].appendChild(input_form_values);
		document.forms[facettes_hidden_form_name].page.value = 0;
		document.forms[facettes_hidden_form_name].submit();
    }
	return true;
}

function facettes_external_reinit() {
	return facettes_reinit(1);
}

function facettes_delete_facette(indice) {
	if(facettes_get_mode() == 'filter') {
        var params = '&param_delete_facette='+indice;
    	var req = new http_request();
    	req.request(facettes_ajax_filters_get_elements_url, true, params, true, function(data){
    		document.getElementById('results_list').innerHTML=data;
            facettes_refresh();
    	});
    } else {
        var input_form_values = document.createElement('input');
		input_form_values.setAttribute('type', 'hidden');
		input_form_values.setAttribute('name', 'param_delete_facette');
		input_form_values.setAttribute('value', indice);
		document.forms[facettes_hidden_form_name].appendChild(input_form_values);
		document.forms[facettes_hidden_form_name].submit();
    }
	return true;
}

function facettes_reinit_compare() {
	var input_form_values = document.createElement('input');
	input_form_values.setAttribute('type', 'hidden');
	input_form_values.setAttribute('name', 'reinit_compare');
	input_form_values.setAttribute('value', '1');
	document.forms[facettes_hidden_form_name].appendChild(input_form_values);
	document.forms[facettes_hidden_form_name].submit();
	return true;
}

function facettes_refresh(num_facettes_set) {
    var req = new http_request();
    var url = facettes_ajax_filtered_data_url;
    if(typeof(num_facettes_set) != 'undefined') {
        url += '&num_facettes_set='+num_facettes_set;
    }
	req.request(url,true,null,true,function(data){
		var response = JSON.parse(data);
		document.getElementById('facette_wrapper').innerHTML=response.display;
		document.getElementById('results_list').classList.add('has_facettes');
	});
}