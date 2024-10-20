<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_loans_archives_ui.class.php,v 1.9 2023/09/29 07:22:57 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/pret_archive.class.php");

class list_loans_archives_ui extends list_loans_ui {
	
	protected function _get_query_base() {
		$query = 'select pret_archive.*
			FROM (((pret_archive LEFT JOIN notices AS notices_m ON arc_expl_notice = notices_m.notice_id )
		 		LEFT JOIN bulletins ON arc_expl_bulletin = bulletins.bulletin_id)
				LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id)
				JOIN empr ON empr.id_empr = pret_archive.arc_id_empr
				JOIN docs_type ON arc_expl_typdoc = idtyp_doc
				';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new pret_archive($row->arc_id);
	}
		
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		
		parent::init_available_filters();
		//Il y en aura s�rement des sp�cifiques aux archives de pr�ts
	}
		
	protected function init_default_applied_sort() {
		$this->add_applied_sort('arc_fin');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('arc_debut', 'datatype', 'date');
		$this->set_setting_column('arc_fin', 'datatype', 'date');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		global $msg;
		parent::init_available_columns();
		$main_fields =
		array(
				'arc_debut' => 'circ_date_emprunt',
				'arc_fin' => 'circ_date_retour',
				'arc_empr_cp' => 'acquisition_cp',
				'arc_empr_ville' => 'ville_empr',
				'arc_empr_prof' => '74',
				'arc_empr_year' => 'year_empr',
				'arc_empr_sexe' => '125',
				'arc_expl_cote' => '4016',
				'arc_empr_categ' => 'categ_empr',
				'arc_empr_codestat' => 'codestat_empr',
				'arc_empr_statut' => 'statut_empr',
				'arc_empr_location' => 'localisation_sort',
				'arc_expl_typdoc' => '294',
				'arc_expl_statut' => 'editions_datasource_expl_statut',
				'arc_expl_location' => 'editions_datasource_expl_location'
		);
		foreach ($main_fields as $key=>$main_field) {
			$main_fields[$key] = $msg[$main_field]." <sup>(arc)</sup>";
		}
		$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $main_fields);
		//Il y en aura s�rement des sp�cifiques aux archives de pr�ts
	}
	
	/**
	 * Champ(s) du tri SQL
	 */
	protected function _get_query_field_order($sort_by) {
	    switch($sort_by) {
	        case 'pret_retour_empr' :
	            return 'arc_fin, empr_nom, empr_prenom';
	        case 'arc_expl_cote':
	        case 'arc_debut':
	        case 'arc_fin':
	            return $sort_by;
	        default :
	            return parent::_get_query_field_order($sort_by);
	    }
	}
		
	protected function _add_query_filters() {
		$this->_add_query_filter_simple_restriction('empr_location_id', 'arc_empr_location', 'integer');
		$this->_add_query_filter_simple_restriction('docs_location_id', 'arc_expl_location', 'integer');
		$this->_add_query_filter_simple_restriction('empr_categ_filter', 'arc_empr_categ', 'integer');
		$this->_add_query_filter_simple_restriction('empr_codestat_filter', 'arc_empr_codestat', 'integer');
		$this->_add_query_filter_simple_restriction('empr_location_id', 'arc_empr_location', 'integer');
		
		$this->_add_query_filter_interval_restriction('pret_date', 'arc_debut');
		$this->_add_query_filter_interval_restriction('pret_retour', 'arc_fin');
		
		$this->_add_query_filter_simple_restriction('short_loan_flag', 'arc_short_loan_flag', 'integer');
		$this->_add_query_filter_simple_restriction('pnb_flag', 'arc_pnb_flag', 'integer');
	}
	
	protected function _get_object_property_arc_empr_location($object) {
		$docs_location = new docs_location($object->get_arc_empr_location());
		return $docs_location->libelle;
	}
	
	protected function _get_object_property_arc_expl_typdoc($object) {
		$docs_type = new docs_type($object->get_arc_expl_typdoc());
		return $docs_type->libelle;
	}
	
	protected function _get_object_property_arc_expl_statut($object) {
		$docs_statut = new docs_statut($object->get_arc_expl_statut());
		return $docs_statut->libelle;
	}
	
	protected function _get_object_property_arc_expl_location($object) {
		$docs_location = new docs_location($object->get_arc_expl_location());
		return $docs_location->libelle;
	}
}