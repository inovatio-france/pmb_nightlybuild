<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_items_ui.class.php,v 1.14 2023/12/18 15:58:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_items_ui extends list_ui {
		
	protected $cp;
	
	protected $displayed_cp;
	
	protected static $link_expl;
	
	protected static $print_mode;
	
	protected static $mono_display_instances;
	
	protected static $bulletinage_display_instances;
	
	protected function _get_query_base() {
		$query = "SELECT * FROM exemplaires
			JOIN docs_location ON docs_location.idlocation = exemplaires.expl_location
			JOIN docs_section ON docs_section.idsection = exemplaires.expl_section
			JOIN docs_statut ON docs_statut.idstatut = exemplaires.expl_statut
			JOIN docs_type ON docs_type.idtyp_doc = exemplaires.expl_typdoc
			JOIN docs_codestat ON docs_codestat.idcode = exemplaires.expl_codestat";
		return $query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'expl_codestat' => 'editions_datasource_expl_codestat',
						'expl_codestats' => 'editions_datasource_expl_codestats',
						'expl_section' => 'editions_datasource_expl_section',
						'expl_sections' => 'editions_datasource_expl_sections',
						'expl_statut' => 'editions_datasource_expl_statut',
						'expl_statuts' => 'editions_datasource_expl_statuts',
						'expl_type' => 'editions_datasource_expl_type',
						'expl_types' => 'editions_datasource_expl_types',
						'expl_cote' => '296',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
				'expl_id' => 0,
				'expl_notice' => 0,
				'expl_bulletin' => 0,
				'expl_codestat' => '',
				'expl_codestats' => array(),
				'expl_section' => '',
				'expl_sections' => array(),
				'expl_statut' => '',
				'expl_statuts' => array(),
				'expl_type' => '',
				'expl_types' => array(),
				'expl_cote' => '',
				'expl_location' => '',
				'expl_locations' => array(),
				'expl_group' => 0,
				'expl_groups' => array(),
				'loaned' => 0 //$loaned FALSE = tous les exemplaires, TRUE les exemplaires en cours de pret
		);
		parent::init_filters($filters);
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('expl_id');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		global $pmb_sur_location_activate;
		
		$this->available_columns =
		array('main_fields' =>
				array(
						'expl_cb' => '293',
						'record_header' => '',
						'location_libelle' => '298',
						'section_libelle' => '295',
						'expl_cote' => '296',
						'statut_libelle' => '297',
						'tdoc_libelle' => '294',
						'lender_libelle' => '651',
						'actions' => 'items_actions'
				)
		);
		if($pmb_sur_location_activate){
			$this->available_columns['main_fields']['surloc_libelle'] = 'sur_location_expl';
		}
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('expl_codestat', 'integer');
		$this->set_filter_from_form('expl_codestats');
		$this->set_filter_from_form('expl_section', 'integer');
		$this->set_filter_from_form('expl_sections');
		$this->set_filter_from_form('expl_statut', 'integer');
		$this->set_filter_from_form('expl_statuts');
		$this->set_filter_from_form('expl_type', 'integer');
		$this->set_filter_from_form('expl_types');
		$this->set_filter_from_form('expl_cote');
		$this->set_filter_from_form('expl_location', 'integer');
		$this->set_filter_from_form('expl_locations');
		$this->set_filter_from_form('expl_group', 'integer');
		$this->set_filter_from_form('expl_groups');
		parent::set_filters_from_form();
	}
	
	protected function get_selection_query($type) {
		$query = '';
		switch ($type) {
			case 'docs_codestat':
				$query = 'select idcode as id, codestat_libelle as label from docs_codestat order by label';
				break;
			case 'docs_section':
				$query = 'select idsection as id, section_libelle as label from docs_section order by label';
				break;
			case 'docs_statut':
				$query = 'select idstatut as id, statut_libelle as label from docs_statut order by label';
				break;
			case 'docs_type':
				$query = 'select idtyp_doc as id, tdoc_libelle as label from docs_type order by label';
				break;
			case 'docs_location':
				$query = 'select idlocation as id, location_libelle as label from docs_location order by label';
				break;
			case 'docs_groups':
				$query = 'select id_groupexpl as id, groupexpl_name as label from groupexpl order by label';
				break;
		}
		return $query;
	}
	
	protected function get_search_filter_expl_cote() {
		global $charset;
		return "<input type='text' class='saisie-20em' name='".$this->objects_type."_expl_cote' value='".htmlentities($this->filters['expl_cote'], ENT_QUOTES, $charset)."' />";
	}
	
	protected function get_search_filter_expl_codestat() {
		global $msg;
		
		return $this->get_search_filter_simple_selection($this->get_selection_query('docs_codestat'), 'expl_codestat', $msg['all']);
	}
	
	protected function get_search_filter_expl_codestats() {
		global $msg;
		
		return $this->get_search_filter_multiple_selection($this->get_selection_query('docs_codestat'), 'expl_codestats', $msg['all']);
	}
	
	protected function get_search_filter_expl_section() {
		global $msg;
		
		return $this->get_search_filter_simple_selection($this->get_selection_query('docs_section'), 'expl_section', $msg['all']);
	}
	
	protected function get_search_filter_expl_sections() {
		global $msg;
		
		return $this->get_search_filter_multiple_selection($this->get_selection_query('docs_section'), 'expl_sections', $msg['all']);
	}
	
	protected function get_search_filter_expl_statut() {
		global $msg;
		
		return $this->get_search_filter_simple_selection($this->get_selection_query('docs_statut'), 'expl_statut', $msg['all']);
	}
	
	protected function get_search_filter_expl_statuts() {
		global $msg;
		
		return $this->get_search_filter_multiple_selection($this->get_selection_query('docs_statut'), 'expl_statuts', $msg['all']);
	}
	
	protected function get_search_filter_expl_type() {
		global $msg;
		
		return $this->get_search_filter_simple_selection($this->get_selection_query('docs_type'), 'expl_type', $msg['all']);
	}
	
	protected function get_search_filter_expl_types() {
		global $msg;
		
		return $this->get_search_filter_multiple_selection($this->get_selection_query('docs_type'), 'expl_types', $msg['all']);
	}
	
	protected function get_search_filter_expl_location() {
		global $msg;
		
		return $this->get_search_filter_simple_selection($this->get_selection_query('docs_location'), 'expl_location', $msg['all']);
	}
	
	protected function get_search_filter_expl_locations() {
		global $msg;
		
		return $this->get_search_filter_multiple_selection($this->get_selection_query('docs_location'), 'expl_locations', $msg['all']);
	}
	
	protected function get_search_filter_expl_groups() {
		global $msg;
		
		return $this->get_search_filter_multiple_selection($this->get_selection_query('docs_groups'), 'expl_groups', $msg['all']);
	}
	
	protected function _add_query_filters() {
		$this->_add_query_filter_simple_restriction('expl_id', 'expl_id', 'integer');
		$this->_add_query_filter_simple_restriction('expl_notice', 'expl_notice', 'integer');
		$this->_add_query_filter_simple_restriction('expl_bulletin', 'expl_bulletin', 'integer');
		$this->_add_query_filter_simple_restriction('expl_group', 'groupexpl_num', 'integer');
		
		/*if ($pmb_droits_explr_localises && $explr_invisible) {
			$this->query_filters [] = 'expl_location not in ('.$explr_invisible.')';
		}
		if($this->filters['loaned']) {
			$this->query_filters [] = 'pret.pret_idexpl is not null';
		}*/
	}
	
	protected function _compare_objects($a, $b, $index=0) {
	    $sort_by = $this->applied_sort[$index]['by'];
		switch($sort_by) {
			case 'record_header' :
				if($a->expl_notice) {
					$notice = static::get_instance_mono_display($a->expl_notice);
					$strcmp_a = $notice->header;
				} elseif ($a->expl_bulletin) {
					$bl = static::get_instance_bulletinage_display($a->expl_bulletin);
					$strcmp_a = $bl->header;
				}
				if($b->expl_notice) {
					$notice = static::get_instance_mono_display($b->expl_notice);
					$strcmp_b = $notice->header;
				} elseif ($b->expl_bulletin) {
					$bl = static::get_instance_bulletinage_display($b->expl_bulletin);
					$strcmp_b = $bl->header;
				}
				return $this->strcmp($strcmp_a, $strcmp_b);
				break;
			default :
			    return parent::_compare_objects($a, $b, $index);
				break;
		}
	}
	
	protected function _get_object_property_surloc_libelle($object) {
		$sur_location = sur_location::get_info_surloc_from_location($object->expl_location);
		return $sur_location->libelle;
	}
	
	protected function _get_object_property_nb_prets($object) {
		return exemplaire::get_nb_prets_from_id($object->expl_id);
	}
	
	protected function _get_object_property_groupexpl_name($object) {
		return groupexpls::get_group_name_expl($object->expl_cb);
	}
	
	protected function get_cell_content($object, $property) {
		global $msg, $charset, $cart_link_non;
	
		$content = '';
		switch($property) {
			case 'expl_cb':
				$content .= "<a href='./circ.php?categ=visu_ex&form_cb_expl=".rawurlencode($object->expl_cb)."'>".$object->expl_cb."</a>";
				break;
		    case 'record_header':
		    	if($object->expl_notice) {
		    		if (SESSrights & CATALOGAGE_AUTH) {
		    			$notice = new mono_display($object->expl_notice, 1, notice::get_permalink($object->expl_notice), 0);
		    		} else {
		    			$notice = static::get_instance_mono_display($object->expl_notice);
		    		}
		    		$content .= $notice->header;
		    	} elseif ($object->expl_bulletin) {
		    		$bl = static::get_instance_bulletinage_display($object->expl_bulletin);
		    		if ($cart_link_non) {
		    			$content .= $bl->header;
		    		} else {
		    			$content .= "<a href='".bulletinage::get_permalink($object->expl_bulletin)."'>".$bl->header."</a>";
		    		}
		    	}
		    	break;
		    case 'add_cart':
		    	$content .= "<img src='".get_url_icon('basket_small_20x20.gif')."' alt='".htmlentities($msg['400'], ENT_QUOTES, $charset)."' title='".htmlentities($msg['400'], ENT_QUOTES, $charset)."' onclick=\"openPopUp('./cart.php?object_type=EXPL&item=".$object->expl_id."', 'cart')\" class='align_middle' />";
		    	break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function _get_query_property_filter($property) {
		switch ($property) {
			case 'expl_codestat':
				return "select codestat_libelle from docs_codestat where idcode = ".$this->filters[$property];
			case 'expl_codestats':
				return "select codestat_libelle from docs_codestat where idcode IN (".implode(',', $this->filters[$property]).")";
			case 'expl_section':
				return "select section_libelle from docs_section where idsection = ".$this->filters[$property];
			case 'expl_sections':
				return "select section_libelle from docs_section where idsection IN (".implode(',', $this->filters[$property]).")";
			case 'expl_statut':
				return "select statut_libelle from docs_statut where idstatut = ".$this->filters[$property];
			case 'expl_statuts':
				return "select statut_libelle from docs_statut where idstatut IN (".implode(',', $this->filters[$property]).")";
			case 'expl_type':
				return "select tdoc_libelle from docs_type where idtyp_doc = ".$this->filters[$property];
			case 'expl_types':
				return "select tdoc_libelle from docs_type where idtyp_doc IN (".implode(',', $this->filters[$property]).")";
			case 'expl_group':
				return "select groupexpl_name from groupexpl where id_groupexpl = ".$this->filters[$property];
			case 'expl_groups':
				return "select groupexpl_name from groupexpl where id_groupexpl IN (".implode(',', $this->filters[$property]).")";
		}
		return '';
	}
	
	public static function set_link_expl($link_expl) {
		static::$link_expl = $link_expl;
	}
	
	public static function set_print_mode($print_mode) {
		static::$print_mode = $print_mode;
	}
	
	public static function get_instance_mono_display($notice_id) {
		if(!isset(static::$mono_display_instances[$notice_id])) {
			static::$mono_display_instances[$notice_id] = new mono_display($notice_id, 1, "", 0);
		}
		return static::$mono_display_instances[$notice_id];
	}
	
	public static function get_instance_bulletinage_display($bulletin_id) {
		if(!isset(static::$bulletinage_display_instances[$bulletin_id])) {
			static::$bulletinage_display_instances[$bulletin_id] = new bulletinage_display($bulletin_id);
		}
		return static::$bulletinage_display_instances[$bulletin_id];
	}
}