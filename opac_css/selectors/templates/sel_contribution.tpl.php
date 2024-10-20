<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sel_contribution.tpl.php,v 1.3 2023/08/17 09:47:53 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// templates communs
global $jscript_common_selector_simple;
global $jscript_common_selector, $infield, $param1, $param2, $p1, $p2;

$jscript_common_selector_simple = "
	<script>
	function set_parent(f_caller, id_value, libelle_value, callback){
        //function vide pour eviter les erreurs js
	}
	</script>";

$pp1 = (($param1) ? $param1 : (($p1) ? $p1 : ''));
$pp2 = (($param2) ? $param2 : (($p2) ? $p2 : ''));
$jscript_common_selector = "
	<script>
	function set_parent(f_caller, id_value, libelle_value, callback){
        if (!f_caller){
            return;
        }
		var w = window;
		var p1 = '$pp1';
		var p2 = '$pp2';
		//on enl�ve le dernier _X
		var tmp_p1 = p1.split('_');
		var tmp_p1_length = tmp_p1.length;
		tmp_p1.pop();
		var p1bis = tmp_p1.join('_');
		
		var tmp_p2 = p2.split('_');
		var tmp_p2_length = tmp_p2.length;
		tmp_p2.pop();
		var p2bis = tmp_p2.join('_');
		
		var max_aut = w.parent.document.getElementById(p1bis.replace('id','max_aut'));
		if(max_aut && (p1bis.replace('id','max_aut').substr(-7)=='max_aut')){
			var trouve=false;
			var trouve_id=false;
			for(i_aut=0;i_aut<=max_aut.value;i_aut++){
				if(w.parent.document.getElementById(p1bis+'_'+i_aut).value==0){
					w.parent.document.getElementById(p1bis+'_'+i_aut).value=id_value;
					w.parent.document.getElementById(p2bis+'_'+i_aut).value=reverse_html_entities(libelle_value);
					trouve=true;
					break;
				}else if(w.parent.document.getElementById(p1bis+'_'+i_aut).value==id_value){
					trouve_id=true;
				}
			}
			if(!trouve && !trouve_id){
				w.parent.add_line(p1bis.replace('_id',''));
				w.parent.document.getElementById(p1bis+'_'+i_aut).value=id_value;
				w.parent.document.getElementById(p2bis+'_'+i_aut).value=reverse_html_entities(libelle_value);
			}
			if(callback)
				w.parent[callback](p1bis.replace('_id','')+'_'+i_aut);
		}else{
			set_parent_value(f_caller,'$pp1', id_value);
			set_parent_value(f_caller,'$pp2', reverse_html_entities(libelle_value));
			if(callback)
				w.parent[callback]('$infield');
			closeCurrentEnv();
		}
	}
	</script>
";
