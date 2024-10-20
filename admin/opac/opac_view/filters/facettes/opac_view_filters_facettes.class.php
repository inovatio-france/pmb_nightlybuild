<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: opac_view_filters_facettes.class.php,v 1.1 2024/04/30 15:33:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path;
require_once($base_path."/admin/opac/opac_view/filters/opac_view_filters.class.php");

class opac_view_filters_facettes extends opac_view_filters {
    
    protected function _init_path() {
    	$this->path="facettes";
    }
    
    public function fetch_data() {
		parent::fetch_data();
		$myQuery = pmb_mysql_query("SELECT * FROM facettes order by facette_name ");
		$this->liste_item=array();
		$i=0;
		if(pmb_mysql_num_rows($myQuery)){
			while(($r=pmb_mysql_fetch_object($myQuery))) {
				$this->liste_item[$i]=new stdClass();
				$this->liste_item[$i]->id=$r->id_facette ;
				$this->liste_item[$i]->name=$r->facette_name ;
				if(in_array($r->id_facette ,$this->selected_list))	$this->liste_item[$i]->selected=1;
				else $this->liste_item[$i]->selected=0;				
				$i++;			
			}	
		}
		return true;
 	}
	
	public function save_form(){
		parent::save_form();
		
		$selected_list=array();
		for($i=0;$i<count($this->liste_item);$i++) {
			eval("global \$facettes_selected_".$this->liste_item[$i]->id.";
			\$selected= \$facettes_selected_".$this->liste_item[$i]->id.";");
			if($selected){
				$selected_list[]=$this->liste_item[$i]->id;
			}
		}
		
		//sauvegarde dans les facettes..
		$req = "select id_facette, facette_opac_views_num from facettes";
		$res = pmb_mysql_query($req);
		if ($res) {
			while($row = pmb_mysql_fetch_object($res)) {
				$views_num = array();
				//la facette est s�lectionn�e..
				if (in_array($row->id_facette,$selected_list)) {
					if ($row->facette_opac_views_num != "") {
						$views_num = explode(",", $row->facette_opac_views_num);
						if (count($views_num)) {
							if (!in_array($this->id_vue, $views_num)) {
								$views_num[] = $this->id_vue;
								$requete = "update facettes set facette_opac_views_num='".implode(",", $views_num)."' where id_facette=".$row->id_facette;
								pmb_mysql_query($requete);
							}
						}
					}
				} else {
					if ($row->facette_opac_views_num != "") {
						$views_num = explode(",", $row->facette_opac_views_num);
						if (count($views_num)) {
							$key_exists = array_search($this->id_vue, $views_num);
							if ($key_exists !== false) {
								//la facette ne doit plus �tre affich�e dans la vue
								array_splice($views_num,$key_exists,1);
								$requete = "update facettes set facette_opac_views_num='".implode(",", $views_num)."' where id_facette=".$row->id_facette;
								pmb_mysql_query($requete);
							}
						}
					} else {
						//la facette doit �tre affich�e dans les autres vues sauf celle-ci..
						$requete = "select opac_view_id from opac_views where opac_view_id <> ".$this->id_vue;
						$resultat = pmb_mysql_query($requete);
						$views_num[] = 0; // OPAC classique
						while ($view = pmb_mysql_fetch_object($resultat)) {
							$views_num[] = $view->opac_view_id;
						}
						$requete = "update facettes set facette_opac_views_num='".implode(",", $views_num)."' where id_facette=".$row->id_facette;
						pmb_mysql_query($requete);
					}
				}
			}
		}
	}	
	
}