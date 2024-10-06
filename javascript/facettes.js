// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facettes.js,v 1.1 2024/03/21 11:06:01 dgoron Exp $

function test(elmt_id){
	var elmt_list=document.getElementById(elmt_id);

	if(elmt_list.className.includes('facette_expande')){
        elmt_list.setAttribute('class', 'facette_collapsed');
    } else {
        elmt_list.setAttribute('class', 'facette_expande');
    }

	for(i in elmt_list.rows){
		if(elmt_list.rows[i].firstElementChild && elmt_list.rows[i].firstElementChild.nodeName!='TH'){
			if(elmt_list.rows[i].style.display == 'none'){
				elmt_list.rows[i].style.display = 'block';
                elmt_list.rows[i].setAttribute('class', 'facette_tr'); 
			}else{
				elmt_list.rows[i].style.display = 'none';
                elmt_list.rows[i].setAttribute('class', 'facette_tr_hidden uk-hidden'); 
			}
		}
	}
}

function facettes_add_searchform(datas) {
	var input_form_values = document.createElement('input');
	input_form_values.setAttribute('type', 'hidden');
	input_form_values.setAttribute('name', 'check_facette[]');
	input_form_values.setAttribute('value', datas);
	document.forms[facettes_hidden_form_name].appendChild(input_form_values);
}

function valid_facettes_multi(){
    var facettes_checked = new Array();
	var flag = false;
    var params = '';
    //on bloque si aucune case cochée
	var form = document.facettes_multi;
	for (i=0, n=form.elements.length; i<n; i++){
		if ((form.elements[i].checked == true)) {
			if(facettes_get_mode() == 'filter') {
                params += '&check_facette[]='+form.elements[i].value;
            } else {
                //copie le noeud vers search_form
				facettes_add_searchform(form.elements[i].value);
            }
            flag = true;
		}
	}
    if(flag) {
		if(document.getElementById('filtre_compare_facette')) {
			document.getElementById('filtre_compare_facette').value='filter';
		}
		if(document.getElementById('filtre_compare_form_values')) {
			document.getElementById('filtre_compare_form_values').value='filter';
		}
        if(facettes_get_mode() == 'filter') {
            var req = new http_request();
        	req.request(facettes_ajax_filters_get_elements_url, true, params, true, function(data){
        		document.getElementById('results_list').innerHTML=data;
                facettes_refresh();
        	});
        } else {
        	document.forms[facettes_hidden_form_name].page.value = 0;
        	document.forms[facettes_hidden_form_name].submit();
        }
		return true;
	} else {
		return false;
	}
}

function facette_see_more(id,json_facette_plus){
	var req = new http_request();
	var sended_datas={'json_facette_plus':json_facette_plus};
	req.request(facettes_ajax_see_more_url,true,'sended_datas='+encodeURIComponent(JSON.stringify(sended_datas)),true,function(data){
		
		var jsonArray = JSON.parse(data);
		var myTable = document.getElementById('facette_list_'+id);
		//on supprime la ligne '+'
		myTable.tBodies[0].removeChild(myTable.rows[myTable.rows.length-1]);
		//on ajoute les lignes au tableau
		for(var i=0;i<jsonArray.length;i++) {
			var tr = document.createElement('tr');
			tr.setAttribute('style','display:block');
			tr.setAttribute('class', 'facette_tr');
			tr.setAttribute('expanded','true');
			tr.setAttribute('facette_ajax_loaded','1');
        	var td = tr.appendChild(document.createElement('td'));
			td.setAttribute('class','facette_col_coche');
        	td.innerHTML = "<span class='facette_coche'><input type='checkbox' name='check_facette[]' value='" + jsonArray[i]['facette_value'] + "'></span>";
        	var td2 = tr.appendChild(document.createElement('td'));
			td2.setAttribute('class','facette_col_info');
        	var aonclick = td2.appendChild(document.createElement('a'));
			aonclick.setAttribute('style', 'cursor:pointer;');
			aonclick.setAttribute('rel', 'nofollow');
			aonclick.setAttribute('class', 'facet-link');
			aonclick.setAttribute('onclick', jsonArray[i]['facette_link']);
			var span_facette_link = aonclick.appendChild(document.createElement('span'));
			span_facette_link.setAttribute('class', 'facette_libelle');
        	span_facette_link.innerHTML = jsonArray[i]['facette_libelle'];
			aonclick.appendChild(document.createTextNode(' '));
			var span_facette_number = aonclick.appendChild(document.createElement('span'));
			span_facette_number.setAttribute('class', 'facette_number');
			span_facette_number.innerHTML = "[" + jsonArray[i]['facette_number'] + "]";
        	myTable.appendChild(tr);

		}
	});
}

function facettes_set_selection(num_facettes_set) {
    facettes_refresh(num_facettes_set);
}

function facettes_valid_facette(datas){
    if(facettes_get_mode() == 'filter') {
        var params = '&check_facette[]='+JSON.stringify(datas);
    	var req = new http_request();
    	req.request(facettes_ajax_filters_get_elements_url, true, params, true, function(data){
    		document.getElementById('results_list').innerHTML=data;
            facettes_refresh();
    	});
    } else {
		facettes_add_searchform(JSON.stringify(datas));
		document.forms[facettes_hidden_form_name].page.value = 0;
		document.forms[facettes_hidden_form_name].submit();
    }
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
