<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_opac_resa_planning_ui.class.php,v 1.1 2023/08/04 12:25:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/emprunteur.class.php");
require_once($class_path."/resa_planning.class.php");

class list_opac_resa_planning_ui extends list_opac_ui {
	
	protected $locations_number;
	
	protected function _get_query_base() {
		$query = "SELECT resa_planning.id_resa
            FROM resa_planning 
            JOIN empr ON resa_planning.resa_idempr = empr.id_empr";
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new resa_planning($row->id_resa);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'montrerquoi' => 'empr_etat_resa_planning_query',
						'empr_location' => 'resa_planning_loc_empr',
						'resa_loc_retrait' => 'resa_planning_loc_retrait'
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		global $pmb_location_resa_planning;
		
		$this->filters = array(
                'id_notice' => 0,
                'id_bulletin' => 0,
                'id_empr' => 0,
                'montrerquoi' => 'all',
                'empr_location' => 0,
		);
		if($pmb_location_resa_planning) {
		    $this->filters['resa_loc_retrait'] = 0;
		}
		parent::init_filters($filters);
	}
	
	protected function init_no_sortable_columns() {
	    $this->no_sortable_columns = array(
			'resa_delete'
	    );
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
	    $this->available_columns =
		array('main_fields' =>
				array(
						'record' => 'resa_planning_title',
						'empr' => 'empr_nom_prenom',
						'empr_location' => 'resa_planning_loc_empr',
						'resa_date' => '374',
				        'resa_date_debut' => 'resa_planning_date_debut',
				        'resa_date_fin' => 'resa_planning_date_fin',
						'resa_dates' => 'resa_planning_dates',
				        'resa_qty' => 'resa_planning_qty',
						'resa_validee' => 'resa_validee',
						'resa_confirmee' => 'resa_confirmee',
				)
		);
		if ($this->get_locations_number() > 1) {
			$this->available_columns['main_fields']['resa_loc_retrait'] = 'resa_planning_loc_retrait';
		}
	}

	/**
	 * Initialisation des settings par d�faut
	 */
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('record', 'align', 'left');
// 		$this->set_setting_column('record', 'text', array('bold' => true));
		$this->set_setting_column('resa_date', 'datatype', 'date');
		$this->set_setting_column('resa_date_debut', 'datatype', 'date');
		$this->set_setting_column('resa_date_fin', 'datatype', 'date');
// 		$this->set_setting_column('resa_validee', 'text', array('strong' => true));
// 		$this->set_setting_column('resa_confirmee', 'text', array('strong' => true));
		$this->set_setting_column('resa_validee', 'datatype', 'boolean');
		$this->set_setting_column('resa_confirmee', 'datatype', 'boolean');
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
	    $montrerquoi = $this->objects_type.'_montrerquoi';
	    global ${$montrerquoi};
	    if(isset(${$montrerquoi}) && ${$montrerquoi} != '') {
	        $this->filters['montrerquoi'] = stripslashes(${$montrerquoi});
	    }
	    $empr_location = $this->objects_type.'_empr_location';
		global ${$empr_location};
		if(isset(${$empr_location}) && ${$empr_location} != '') {
		    $this->filters['empr_location'] = ${$empr_location}*1;
		}
		$resa_loc_retrait = $this->objects_type.'_resa_loc_retrait';
		global ${$resa_loc_retrait};
		if(isset(${$resa_loc_retrait}) && ${$resa_loc_retrait} != '') {
		    $this->filters['resa_loc_retrait'] = ${$resa_loc_retrait}*1;
		}
		parent::set_filters_from_form();
	}
		
	protected function get_search_filter_montrerquoi() {
	    global $msg, $charset;
	    
	    //Selecteur previsions validees/confirmees
	    $search_filter = "
            <span class='list_ui_montrerquoi_checkbox ".$this->objects_type."_montrerquoi_checkbox'>
                <input type='radio' name='".$this->objects_type."_montrerquoi' value='all' id='all' ".($this->filters['montrerquoi'] == 'all' ? "checked='checked'" : "")." />
                <label for='all'>".htmlentities($msg['resa_planning_show_all'], ENT_QUOTES, $charset)."</label>
            </span>&nbsp;
            <span class='list_ui_montrerquoi_checkbox ".$this->objects_type."_montrerquoi_checkbox'>
                <input type='radio' name='".$this->objects_type."_montrerquoi' value='validees' id='validees' ".($this->filters['montrerquoi'] == 'validees' ? "checked='checked'" : "")." />
                <label for='validees'>".htmlentities($msg['resa_planning_show_validees'], ENT_QUOTES, $charset)."</label>
            </span>&nbsp;
            <span class='list_ui_montrerquoi_checkbox ".$this->objects_type."_montrerquoi_checkbox'>
                <input type='radio' name='".$this->objects_type."_montrerquoi' value='invalidees' id='invalidees' ".($this->filters['montrerquoi'] == 'invalidees' ? "checked='checked'" : "")." />
                <label for='invalidees'>".htmlentities($msg['resa_planning_show_invalidees'], ENT_QUOTES, $charset)."</label>
            </span>&nbsp;
            <span class='list_ui_montrerquoi_checkbox ".$this->objects_type."_montrerquoi_checkbox'>
                <input type='radio' name='".$this->objects_type."_montrerquoi' value='valid_noconf' id='valid_noconf' ".($this->filters['montrerquoi'] == 'valid_noconf' ? "checked='checked'" : "")." />
                <label for='valid_noconf'>".htmlentities($msg['resa_planning_show_non_confirmees'], ENT_QUOTES, $charset)."</label>
            </span>&nbsp;
            <span class='list_ui_montrerquoi_checkbox ".$this->objects_type."_montrerquoi_checkbox'>
                <input type='radio' name='".$this->objects_type."_montrerquoi' value='toresa' id='toresa' ".($this->filters['montrerquoi'] == 'toresa' ? "checked='checked'" : "")." />
                <label for='toresa'>".htmlentities($msg['resa_planning_show_toresa'], ENT_QUOTES, $charset)."</label>
            </span>";
	    return $search_filter;
	}
	
	protected function get_search_filter_empr_location() {
	    global $msg, $charset;
	    
	    $query = 'select idlocation, location_libelle FROM docs_location order by location_libelle';
	    $result = pmb_mysql_query($query);
	    $search_filter = '<select name="'.$this->objects_type.'_empr_location">';
	    $search_filter.='<option value="0"'.((!$this->filters['empr_location'])?' selected="selected"':'').'>'.$msg['all_location'].'</option>';
	    if(pmb_mysql_num_rows($result)) {
	        while($o=pmb_mysql_fetch_object($result)) {
	            $search_filter.= '<option value="'.$o->idlocation.'"'.(($this->filters['empr_location'] == $o->idlocation)?' selected="selected"':'').'>'.htmlentities($o->location_libelle,ENT_QUOTES,$charset).'</option>';
	        }
	    }
	    $search_filter.= '</select>';
	    return $search_filter;
	}
	
	protected function get_search_filter_resa_loc_retrait() {
	    global $msg, $charset;
	    
	    $query = 'select idlocation, location_libelle FROM docs_location order by location_libelle';
	    $result = pmb_mysql_query($query);
	    $search_filter = '<select name="'.$this->objects_type.'_resa_loc_retrait">';
	    $search_filter.='<option value="0"'.((!$this->filters['resa_loc_retrait'])?' selected="selected"':'').'>'.$msg['all_location'].'</option>';
	    if(pmb_mysql_num_rows($result)) {
	        while($o=pmb_mysql_fetch_object($result)) {
	            $search_filter.= '<option value="'.$o->idlocation.'"'.(($this->filters['resa_loc_retrait'] == $o->idlocation)?' selected="selected"':'').'>'.htmlentities($o->location_libelle,ENT_QUOTES,$charset).'</option>';
	        }
	    }
	    $search_filter.= '</select>';
	    return $search_filter;
	}
	
	protected function _add_query_filters() {
		global $pmb_lecteurs_localises;
		global $pmb_location_resa_planning;
		
		$this->_add_query_filter_simple_restriction('id_notice', 'resa_planning.resa_idnotice', 'integer');
		$this->_add_query_filter_simple_restriction('id_bulletin', 'resa_planning.resa_idbulletin', 'integer');
		$this->_add_query_filter_simple_restriction('id_empr', 'resa_planning.resa_idempr', 'integer');
		if($this->filters['montrerquoi']) {
			switch ($this->filters['montrerquoi']) {
				case 'validees':
					$this->query_filters [] = 'resa_planning.resa_validee="1"';
					$this->query_filters [] = 'resa_planning.resa_remaining_qty!=0';
					break;
				case 'invalidees':
					$this->query_filters [] = 'resa_planning.resa_validee="0"';
					$this->query_filters [] = 'resa_planning.resa_remaining_qty!=0';
					break;
				case 'valid_noconf':
					$this->query_filters [] = 'resa_planning.resa_validee="1"';
					$this->query_filters [] = 'resa_planning.resa_confirmee="0"';
					$this->query_filters [] = 'resa_planning.resa_remaining_qty!=0';
					break;
				case 'toresa':
					$this->query_filters [] = 'resa_planning.resa_remaining_qty=0';
					break;
				case 'all':
				default:
					$this->query_filters [] = 'resa_planning.resa_remaining_qty!=0';
					break;
			}
		} else {
			$this->query_filters [] = 'resa_planning.resa_remaining_qty!=0';
		}
		if($pmb_lecteurs_localises && $this->filters['empr_location']) {
			$this->query_filters [] = 'empr_location = "'.$this->filters['empr_location'].'"';
		}
		if($pmb_location_resa_planning && $this->filters['resa_loc_retrait']) {
			$this->query_filters [] = 'resa_planning.resa_loc_retrait = "'.$this->filters['resa_loc_retrait'].'"';
		}
	}
	
	protected function _get_object_property_record($object) {
		global $msg;
		
		if ($object->resa_idnotice) {
			// affiche la notice correspondant � la r�servation
			return notice::get_notice_title($object->resa_idnotice);
		}  else {
			// c'est un bulletin donc j'affiche le nom de p�riodique et le nom du bulletin (date ou n�)
			$requete = "SELECT bulletin_id, bulletin_numero, bulletin_notice, mention_date, date_date, date_format(date_date, '".$msg['format_date_sql']."') as aff_date_date FROM bulletins WHERE bulletin_id='".$object->resa_idbulletin."'";
			$res = pmb_mysql_query($requete);
			$obj = pmb_mysql_fetch_object($res);
			return $obj->bulletin_titre;
		}
	}
	
	protected function _get_object_property_empr($object) {
		return emprunteur::get_name($object->resa_idempr);
	}
	
	protected function _get_object_property_empr_location($object) {
		return emprunteur::get_location($object->resa_idempr)->libelle;
	}
	
	protected function _get_object_property_resa_loc_retrait($object) {
		$docs_location = new docs_location($object->resa_loc_retrait);
		return $docs_location->libelle;
	}
	
	protected function _get_object_property_resa_date($object) {
		return $object->aff_resa_date;
	}
	
	protected function _get_object_property_resa_date_debut($object) {
		if($object->resa_date_debut != '0000-00-00') {
			return $object->aff_resa_date_debut;
		}
		return '';
	}
	
	protected function _get_object_property_resa_date_fin($object) {
		if($object->resa_date_fin != '0000-00-00') {
			return $object->aff_resa_date_fin;
		}
		return '';
	}
		
	protected function _get_object_property_resa_dates($object) {
		return $this->_get_object_property_resa_date_debut($object).' - '.$this->_get_object_property_resa_date_fin($object);
	}
	
	protected function get_cell_content($object, $property) {
		global $msg, $css;
		
		$content = '';
		switch($property) {
			case 'record':
				if ($object->resa_idnotice) {
					// affiche la notice correspondant � la r�servation
					$notice = new notice($object->resa_idnotice);
					$content .= pmb_bidi($notice->print_resume(1,$css));
				}  else {
					// c'est un bulletin donc j'affiche le nom de p�riodique et le nom du bulletin (date ou n�)
					$requete = "SELECT bulletin_id, bulletin_numero, bulletin_notice, mention_date, date_date, date_format(date_date, '".$msg['format_date_sql']."') as aff_date_date FROM bulletins WHERE bulletin_id='".$object->resa_idbulletin."'";
					$res = pmb_mysql_query($requete);
					$obj = pmb_mysql_fetch_object($res) ;
					$notice3 = new notice($obj->bulletin_notice);
					$content .= pmb_bidi($notice3->print_resume(1,$css));
					
					// affichage de la mention de date utile : mention_date si existe, sinon date_date
					if ($obj->mention_date) {
						$content .= pmb_bidi("(".$obj->mention_date.")");
					} elseif ($obj->date_date) {
						$content.= pmb_bidi("(".$obj->aff_date_date.")");
					}
				}
				break;
			case 'resa_dates':
				$content .= $msg['resa_planning_date_debut'].$this->_get_object_property_resa_date_debut($object).'<br />';
				$content .= $msg['resa_planning_date_fin'].$this->_get_object_property_resa_date_fin($object).'<br />';
				if($object->resa_date_fin < date('Y-m-d') || $object->resa_date_fin == '0000-00-00') {
					$content .= $msg['resa_planning_overtime'];
				} else {
					$content .= $msg['resa_planning_attente_validation'];
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_class_objects_list() {
		return "tab_resa_planning ".parent::get_class_objects_list();
	}
	
	protected function _get_query_human_montrerquoi() {
		global $msg;
		
		switch ($this->filters['montrerquoi']) {
			case 'validees':
				return $msg['resa_planning_show_validees'];
			case 'invalidees':
				return $msg['resa_planning_show_invalidees'];
			case 'valid_noconf':
				return $msg['resa_planning_show_non_confirmees'];
			case 'toresa':
				return $msg['resa_planning_show_toresa'];
		}
	}
	
	protected function _get_query_human_empr_location() {
	    if(!empty($this->filters['empr_location'])) {
    		$docs_location = new docs_location($this->filters['empr_location']);
    		return $docs_location->libelle;
	    }
	}
	
	protected function _get_query_human_resa_loc_retrait() {
	    if(!empty($this->filters['resa_loc_retrait'])) {
    		$docs_location = new docs_location($this->filters['resa_loc_retrait']);
    		return $docs_location->libelle;
	    }
	    return '';
	}
	
	public function get_locations_number() {
		if(empty($this->locations_number)) {
			$this->locations_number = pmb_mysql_result(pmb_mysql_query("SELECT count(*) FROM docs_location"), 0);
		}
		return $this->locations_number;
	}
	
	public function has_rights() {
	    global $opac_resa_planning;
		
		if(!$opac_resa_planning) {
			return false;
		}
		return parent::has_rights();
	}
}