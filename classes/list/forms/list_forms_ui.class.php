<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_forms_ui.class.php,v 1.7 2023/03/30 08:21:20 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/forms/form.class.php");

class list_forms_ui extends list_ui {
	
	protected $tabs_instances = array();
	
	protected $subtabs_instances = array();
	
	protected function fetch_data() {
		$this->objects = array();
		$this->_init_forms();
		$this->pager['nb_results'] = count($this->objects);
		$this->messages = "";
	}
	
	/**
	 * Module Autorit�s
	 */
	protected function _init_autorites_forms() {
		$this->add_form('author', 'autorites', 'auteurs', 'author_form');
		$this->add_form('editor', 'autorites', 'editeurs', 'editeur_form');
		$this->add_form('collection', 'autorites', 'collections', 'collection_form');
		$this->add_form('subcollection', 'autorites', 'souscollections', 'collection_form');
		$this->add_form('serie', 'autorites', 'series', 'serie_form');
		$this->add_form('indexint', 'autorites', 'indexint', 'indexint_form');
	}
	
	/**
	 * Module Editions
	 */
	protected function _init_edit_forms() {
		//Mod�les de planche de codes-barres
		$this->add_form('barcodes_sheet', 'edit', 'barcodes_sheets', 'models');
		//Mod�les de planche d'�tiquettes
		$this->add_form('sticks_sheet', 'edit', 'sticks_sheet', 'models');
		
		//Templates
		$this->add_form('notice_tpl', 'edit', 'tpl', 'notice');
		$this->add_form('serialcirc_tpl', 'edit', 'tpl', 'serialcirc');
		$this->add_form('bannette_tpl', 'edit', 'tpl', 'bannette');
		$this->add_form('print_cart_tpl', 'edit', 'tpl', 'print_cart_tpl');
	}
	
	/**
	 * Module D.S.I.
	 */
	protected function _init_dsi_forms() {
		//Bannettes
		$this->add_form('bannette', 'dsi', 'bannettes', 'pro');
		$this->add_form('bannette', 'dsi', 'bannettes', 'abo');
		
		//Equations
		$this->add_form('equation', 'dsi', 'equations', 'gestion');
		//Classements
		$this->add_form('classements', 'dsi', 'options', 'classements');
		//Flux RSS
		$this->add_form('rss_flux', 'dsi', 'fluxrss', 'definition');
	}
	
	/**
	 * Module Administration
	 */
	protected function _init_admin_forms() {
		global $pmb_sur_location_activate, $pmb_map_activate, $pmb_nomenclature_activate;
		global $pmb_gestion_financiere, $pmb_gestion_abonnement, $pmb_gestion_financiere_caisses;
		global $pmb_scan_request_activate, $demandes_active, $faq_active;
		global $acquisition_gestion_tva, $acquisition_sugg_categ;
		
		//Exemplaires
		$this->add_form('docs_type', 'admin', 'docs', 'typdoc');
		$this->add_form('docs_location', 'admin', 'docs', 'location');
		if($pmb_sur_location_activate) {
			$this->add_form('sur_location', 'admin', 'docs', 'sur_location');
		}
		$this->add_form('docs_section', 'admin', 'docs', 'section');
		$this->add_form('docs_statut', 'admin', 'docs', 'statut');
		$this->add_form('docs_codestat', 'admin', 'docs', 'codstat');
		$this->add_form('lender', 'admin', 'docs', 'lenders');
		
		//Notices
		$this->add_form('origine_notice', 'admin', 'notices', 'orinot');
		$this->add_form('notice_statut', 'admin', 'notices', 'statut');
		if($pmb_map_activate) {
			$this->add_form('map_echelle', 'admin', 'notices', 'map_echelle');
			$this->add_form('map_projection', 'admin', 'notices', 'map_projection');
			$this->add_form('map_ref', 'admin', 'notices', 'map_ref');
		}
		$this->add_form('notice_onglet', 'admin', 'notices', 'onglet');
		$this->add_form('notice_usage', 'admin', 'notices', 'notice_usage');
		
		//Autorit�s
		$this->add_form('origin', 'admin', 'authorities', 'origins');
		$this->add_form('authorities_statut', 'admin', 'authorities', 'statuts');
		
		//Documents num�riques
		$this->add_form('explnum_statut', 'admin', 'docnum', 'statut');
		$this->add_form('explnum_licence', 'admin', 'docnum', 'licence');
		
		//Etats des collections
		$this->add_form('arch_emplacement', 'admin', 'collstate', 'emplacement');
		$this->add_form('arch_type', 'admin', 'collstate', 'support');
		$this->add_form('arch_statut', 'admin', 'collstate', 'statut');
		
		//Abonnements
		$this->add_form('abts_periodicite', 'admin', 'abonnements', 'periodicite');
		$this->add_form('abts_status', 'admin', 'abonnements', 'status');
		
		//Lecteurs
		$this->add_form('empr_categ', 'admin', 'empr', 'categ');
		$this->add_form('empr_statut', 'admin', 'empr', 'statut');
		$this->add_form('empr_codestat', 'admin', 'empr', 'codstat');
		
		//Utilisateurs
		$this->add_form('users_groups', 'admin', 'users', 'groups');
		
		//Contenu �ditorial
		$this->add_form('cms_editorial_type', 'admin', 'cms_editorial', 'type');
		$this->add_form('cms_editorial_publications_state', 'admin', 'cms_editorial', 'publication_state');
		
		//Infopages
		$this->add_form('infopage', 'admin', 'infopages');
		
		//Facettes
		$this->add_form('facette_search_compare', 'admin', 'opac', 'facettes_comparateur');
		
		//Statistiques
		$this->add_form('stat_view', 'admin', 'opac', 'stat');
		$this->add_form('stat_query', 'admin', 'opac', 'stat');
		
		//Vues OPAC
		$this->add_form('opac_view', 'admin', 'opac', 'opac_view');
		
		//Formulaire de contact
		$this->add_form('contact_form', 'admin', 'contact_forms');
		$this->add_form('contact_form_object', 'admin', 'contact_forms', 'objects');
		
		//Page de maintenance
		$this->add_form('maintenance_page', 'admin', 'opac', 'maintenance');
		
		//Cookies & traceurs
		$this->add_form('analytics_service', 'admin', 'opac', 'analytics_services');
		
		//Actions classements
		$this->add_form('procs_classement', 'admin', 'proc', 'clas');
		
		//Nomenclatures
		if($pmb_nomenclature_activate) {
			$this->add_form('nomenclature_family', 'admin', 'family', 'family');
			$this->add_form('nomenclature_musicstand', 'admin', 'family', 'family_musicstand');
			$this->add_form('nomenclature_formation', 'admin', 'formation', 'formation');
			$this->add_form('nomenclature_type', 'admin', 'formation', 'formation_type');
			$this->add_form('nomenclature_voice', 'admin', 'voice', 'voice');
			$this->add_form('nomenclature_instrument', 'admin', 'instrument', 'instrument');
		}
		
		//Gestion financi�re
		if (($pmb_gestion_financiere)&&($pmb_gestion_abonnement==2)) {
			$this->add_form('type_abt', 'admin', 'finance', 'abts');
		}
		if (($pmb_gestion_financiere)) {
			$this->add_form('transactype', 'admin', 'finance', 'transactype');
			$this->add_form('transaction_payment_method', 'admin', 'finance', 'transaction_payment_method');
		}
		if (($pmb_gestion_financiere)&&($pmb_gestion_financiere_caisses)) {
			$this->add_form('cashdesk', 'admin', 'finance', 'cashdesk');
		}
		
		//R�colteur
		$this->add_form('harvest', 'admin', 'harvest', 'profil');
		$this->add_form('harvest', 'admin', 'harvest', 'profil_import');
		
		//Z39.50
		$this->add_form('z_bib', 'admin', 'z3950', 'zbib');
		
		//Services externes
		$this->add_form('es_esuser', 'admin', 'external_services', 'esusers');
		$this->add_form('es_esgroup', 'admin', 'external_services', 'esusergroups');
		
		//Connecteurs
		$this->add_form('connectors_categ', 'admin', 'connecteurs', 'categ');
		$this->add_form('connector_out_setcateg', 'admin', 'connecteurs', 'categout_sets');
		$this->add_form('enrichment', 'admin', 'connecteurs', 'enrichment');
		
		//Acquisitions
		$this->add_form('entites', 'admin', 'acquisition', 'entite');
		$this->add_form('exercices', 'admin', 'acquisition', 'compta');
		if ($acquisition_gestion_tva) {
			$this->add_form('tva_achats', 'admin', 'acquisition', 'tva');
		}
		$this->add_form('types_produits', 'admin', 'acquisition', 'type');
		$this->add_form('frais', 'admin', 'acquisition', 'frais');
		$this->add_form('paiements', 'admin', 'acquisition', 'mode');
		$this->add_form('budgets', 'admin', 'acquisition', 'budget');
		if($acquisition_sugg_categ=='1') {
			$this->add_form('suggestions_categ', 'admin', 'acquisition', 'categ');
		}
		$this->add_form('suggestion_source', 'admin', 'acquisition', 'src');
		$this->add_form('lgstat', 'admin', 'acquisition', 'lgstat');
		$this->add_form('rent_pricing_system', 'admin', 'acquisition', 'pricing_systems');
		$this->add_form('threshold', 'admin', 'acquisition', 'thresholds');
		
		//Demandes
		if($demandes_active) {
			$this->add_form('demandes_theme', 'admin', 'demandes', 'theme');
			$this->add_form('demandes_type', 'admin', 'demandes', 'type');
		}
		
		//FAQ
		if($faq_active) {
			$this->add_form('faq_theme', 'admin', 'faq', 'theme');
			$this->add_form('faq_type', 'admin', 'faq', 'type');
		}
		
		//Template de mail
		$this->add_form('mailtpl', 'admin', 'mailtpl', 'build');
		
		//Num�risations
		if($pmb_scan_request_activate) {
			$this->add_form('scan_request_status', 'admin', 'scan_request', 'status');
			$this->add_form('scan_request_priority', 'admin', 'scan_request', 'priorities');
		}
	}
	
	/**
	 * Entit�s
	 */
	protected function _init_entity_forms() {
		//Autorit�s
		$this->add_form('auteur', 'autorites', 'auteurs', 'author_form');
		$this->add_form('editeur', 'autorites', 'editeurs', 'editeur_form');
		$this->add_form('collection', 'autorites', 'collections', 'collection_form');
		$this->add_form('subcollection', 'autorites', 'souscollections', 'collection_form');
		$this->add_form('serie', 'autorites', 'series', 'serie_form');
		$this->add_form('indexint', 'autorites', 'indexint', 'indexint_form');
		
		//Notices
		$this->add_form('notice', 'catalog', 'create_form');
		$this->add_form('serial', 'catalog', 'serials', 'serial_form');
		$this->add_form('bulletinage', 'catalog', 'serials', 'bulletinage');
		$this->add_form('analysis', 'catalog', 'serials', 'analysis');
		
	}
	
	protected function _init_forms() {
		$this->_init_admin_forms();
		$this->_init_autorites_forms();
		$this->_init_edit_forms();
		$this->_init_dsi_forms();
		$this->_init_entity_forms();
	}
	
	public function add_form($model_name, $module, $categ, $sub='') {
		if(empty($this->filters['modules']) || (!empty($this->filters['modules']) && in_array($module, $this->filters['modules']))) {
			$form = new form();
			$form->set_model_name($model_name);
			$form->set_module($module);
			$form->set_categ($categ);
			$form->set_sub($sub);
			$this->add_object($form);
		}
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters['main_fields'] = array(
				'modules' => 'Modules'
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'modules' => array(),
		);
		parent::init_filters($filters);
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'label' => '103',
						'tab' => 'tab',
						'subtab' => 'subtab',
						'section' => 'section',
						'initialization' => 'initialization'
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_applied_group() {
		$this->applied_group = array(0 => 'module', 1 => 'section');
// 		$this->applied_group = array(0 => 'module');
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'label', 'tab', 'subtab'
		);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('modules');
	}
	
	protected function init_default_pager() {
		parent::init_default_pager();
		$this->pager['all_on_page'] = true;
	}
	
	public function set_filters_from_form() {
		$this->set_filter_from_form('modules');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column('label');
		$this->add_column('tab');
		$this->add_column('subtab');
		$this->add_column('initialization');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('default', 'align', 'left');
		$this->settings['objects']['default']['display_mode'] = 'expandable_table';
// 		$this->settings['grouped_objects']['default']['display_mode'] = 'expandable_table';
		$this->settings['grouped_objects']['level_1']['display_mode'] = 'expandable_table';
	}
	
	protected function get_search_filter_modules() {
		global $msg;
		
		$options = array(
				'catalog' => $msg['6'],
				'autorites' => $msg['132'],
				'edit' => $msg['1100'],
				'dsi' => $msg['dsi_menu'],
				'admin' => $msg['7'],
		);
		sort($options);
		return $this->get_search_filter_multiple_selection('', 'modules', $msg["all"], $options);
	}
	
	protected function _get_object_property_module($object) {
		$list_modules_ui = list_modules_ui::get_instance();
		foreach ($list_modules_ui->get_objects() as $module) {
			if($module->get_name() == $object->get_module()) {
				return $module->get_label();
			}
		}
	}
	
	protected function _get_object_property_label($object) {
		$label = $this->_get_object_property_tab($object);
		$subtab = $this->_get_object_property_subtab($object);
		if($subtab) {
			$label .= " > ".$subtab;
		}
		return $label;
	}
	
	protected function _get_object_property_section($object) {
		global $msg;
		
		$tab = $this->_get_tab_from_object($object);
		if(is_object($tab)) {
			$section = $tab->get_section();
		} else {
			switch ($object->get_model_name()) {
				case 'facette_search_compare':
					$section = 'opac_admin_menu';
					break;
				case 'notice':
					$section = '4057';
					break;
				case 'bulletinage':
					$section = '771';
					break;
				case 'analysis':
					$section = '771';
					break;
					
			}
		}
		if(!empty($section)) {
			if(isset($msg[$section])) {
				return $msg[$section];
			} else {
				return $section;
			}
		}
		return '';
	}
	
	protected function _get_object_property_initialization($object) {
		if($object->is_substituted()) {
			return 1;
		}
		return 0;
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'initialization':
				if($object->is_substituted()) {
					$link = static::get_controller_url_base()."&action=delete&id=".$object->get_id();
					$content .= $this->get_img_cell_content('initialization.png', 'initialize', $link, 'initialization_confirm');
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function _get_tabs($module, $categ) {
		$tabs = array();
		$tabs_instance = $this->get_tabs_instance($module);
		$tabs_objects = $tabs_instance->get_objects();
		foreach ($tabs_objects as $tab_object) {
			if($tab_object->get_categ() == $categ) {
				$tabs[] = $tab_object;
			}
		}
		return $tabs;
	}
	
	protected function _get_tab_from_object($object) {
		$tabs = $this->_get_tabs($object->get_module(), $object->get_categ());
		if(!empty($tabs)) {
			if(count($tabs) == 1) {
				return $tabs[0];
			} else {
				foreach ($tabs as $tab) {
					if($tab->get_sub() == $object->get_sub()) {
						return $tab;
					}
				}
			}
		}
	}
	
	protected function _get_object_property_tab($object) {
		global $msg;
		
		$tab = $this->_get_tab_from_object($object);
		if(is_object($tab)) {
			return $tab->get_label();
		} else {
			switch ($object->get_model_name()) {
				case 'facette_search_compare':
					return $msg['opac_facette'];
				case 'notice':
					return $msg['type_mono'];
				case 'bulletinage':
					return $msg['type_bull'];
				case 'analysis':
					return $msg['type_art'];
					
			}
		}
		return '';
	}
	
	protected function _get_subtabs($module, $categ, $sub) {
		$subtabs = array();
		$subtabs_instance = $this->get_subtabs_instance($module, $categ);
		$subtabs_objects = $subtabs_instance->get_objects();
		foreach ($subtabs_objects as $subtab_object) {
			if($subtab_object->get_sub() == $sub) {
				$subtabs[] = $subtab_object;
			}
		}
		return $subtabs;
	}
	
	protected function _get_subtab_from_object($object) {
		$subtabs = $this->_get_subtabs($object->get_module(), $object->get_categ(), $object->get_sub());
		if(!empty($subtabs)) {
			if(count($subtabs) == 1) {
				return $subtabs[0];
			} else {
				foreach ($subtabs as $subtab) {
					if($subtab->get_sub() == $object->get_sub()) {
						return $subtab;
					}
				}
			}
		}
	}
	
	protected function _get_object_property_subtab($object) {
		global $msg;
		
		$subtab = $this->_get_subtab_from_object($object);
		if(is_object($subtab)) {
			return $subtab->get_label();
		} else {
			if($object->get_model_name() == 'facette_search_compare') {
				return $msg['facettes_admin_menu_compare'];
			}
		}
		return '';
	}
	
	protected function get_default_attributes_format_cell($object, $property) {
		$attributes = array();
		if($object->is_in_database()) {
			$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&id=".$object->get_id()."\"";
		} else {
			$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&form_model_name=".$object->get_model_name()."&form_module=".$object->get_module()."\"";
		}
		return $attributes;
	}
	
	public function get_tabs_instance($module) {
		$list_tabs_ui_name = "list_tabs_".$module."_ui";
		$list_tabs_ui_name::set_module_name($module);
		if(!isset($this->tabs_instances[$module])) {
			$this->tabs_instances[$module] = new $list_tabs_ui_name();
		}
		return $this->tabs_instances[$module];
	}
	
	public function get_subtabs_instance($module, $categ) {
		$list_subtabs_ui_name = "list_subtabs_".$module."_ui";
		$list_subtabs_ui_name::set_module_name($module);
		$list_subtabs_ui_name::set_categ($categ);
		if(!isset($this->subtabs_instances[$module][$categ])) {
			$this->subtabs_instances[$module][$categ] = new $list_subtabs_ui_name();
		}
		return $this->subtabs_instances[$module][$categ];
	}
	
	public function get_label_from_object($object) {
		return $this->_get_object_property_label($object);
	}
}