<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_opac_scan_requests_ui.class.php,v 1.3 2023/12/07 13:14:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/scan_request/scan_requests.class.php');
require_once($class_path.'/scan_request/scan_request.class.php');

class list_opac_scan_requests_ui extends list_opac_ui {
	
    protected $scan_requests; // Utilis� pour le Django
	
	protected function _get_query_base() {
		$query = 'select id_scan_request from scan_requests';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new scan_request($row->id_scan_request);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
	    global $pmb_scan_request_location_activate;
	    
		$this->available_filters =
		array('main_fields' =>
				array(
						'status' => 'scan_request_form_status',
						'priority' => 'scan_request_form_priority',
						'user_input' => 'global_search',
						'date' => 'scan_request_form_date',
			            'wish_date' => 'scan_request_form_wish_date',
				        'deadline_date' => 'scan_request_form_deadline_date'
				)
		);
		if($pmb_scan_request_location_activate) {
		    $this->available_filters['main_fields']['location'] = 'scan_request_location_search';
		}
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
	    global $pmb_scan_request_location_activate;
	    
		$this->filters = array(
				'status' => '',
				'priority' => '',
                'user_input' => '', 
				'date_start' => '',
				'date_end' => '',
                'wish_date_start' => '',
                'wish_date_end' => '',
                'deadline_date_start' => '',
                'deadline_date_end' => '',
				'empr' => array()
		);
		if($pmb_scan_request_location_activate) {
		    $this->filters['location'] = '';
		}
		parent::init_filters($filters);
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'title' => 'scan_request_title',
					'empr' => 'empr_nom_prenom',
					'date' => 'scan_request_date',
					'wish_date' => 'scan_request_wish_date',
                    'deadline_date' => 'scan_request_deadline_date',
                    'priority' => 'scan_request_priority',
                    'status' => 'scan_request_status'
			)
		);
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date', 'desc');
	}
	
	/**
	 * Champ(s) du tri SQL
	 */
	protected function _get_query_field_order($sort_by) {
	    switch($sort_by) {
	        case 'id':
	            return 'id_scan_request';
	        case 'title' :
	        case 'desc' :
	        case 'date':
	        case 'wish_date':
	        case 'deadline_date':
	            return 'scan_request_'.$sort_by;
	        default :
	            return parent::_get_query_field_order($sort_by);
	    }
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {

		$this->set_filter_from_form('status', 'integer');
		$this->set_filter_from_form('priority', 'integer');
	    $this->set_filter_from_form('user_input');
	    $this->set_filter_from_form('date_start');
	    $this->set_filter_from_form('date_end');
	    $this->set_filter_from_form('wish_date_start');
	    $this->set_filter_from_form('wish_date_end');
	    $this->set_filter_from_form('deadline_date_start');
	    $this->set_filter_from_form('deadline_date_end');
	    $this->set_filter_from_form('location', 'integer');
	    $this->set_filter_from_form('empr', 'integer');
		parent::set_filters_from_form();
	}
	
	protected function add_column_expand() {
	    $this->columns[] = array(
	        'property' => '',
	        'label' => "<div class='center'>
							<i class='fa fa-plus-square' onclick='".$this->objects_type."_expand_all(document.".$this->get_form_name().");' style='cursor:pointer;'></i>
							&nbsp;
							<i class='fa fa-minus-square' onclick='".$this->objects_type."_collapse_all(document.".$this->get_form_name().");' style='cursor:pointer;'></i>
						</div>",
	        'html' => "<div class='center'><img style='border:0px; margin:3px 3px' onclick='expand_scan_request(!!id!!); return false;' id='scan_request_!!id!!_img' name='imEx' class='img_plus' src='./images/plus.gif'></div>",
	        'exportable' => false
	    );
	}
	
	protected function add_column_actions() {
	    global $msg, $charset;
	    
	    $this->columns[] = array(
	        'property' => 'edit',
	        'label' => $msg['scan_request_actions'],
	        'html' => "<a href='!!edit_link!!'>
                        <img class='icon' width='16' height='16' src='".get_url_icon('b_edit.png')."' title='".htmlentities($msg["scan_request_edit"], ENT_QUOTES, $charset)."' alt='".htmlentities($msg["scan_request_edit"], ENT_QUOTES, $charset)."' />
                       </a>",
	        'exportable' => false
	    );
	}
	
	protected function init_default_columns() {
	
		$this->add_column_expand();
		$this->add_column('title');
		$this->add_column('date');
		$this->add_column('wish_date');
		$this->add_column('deadline_date');
		$this->add_column('priority');
		$this->add_column('status');
	}
	
	protected function init_default_pager() {
	    parent::init_default_pager();
	    $this->pager['all_on_page'] = true;
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('date', 'datatype', 'date');
		$this->set_setting_column('wish_date', 'datatype', 'date');
		$this->set_setting_column('deadline_date', 'datatype', 'date');
		$this->set_setting_display('pager', 'visible', false);
	}
	
	protected function get_search_filter_status() {
		global $msg, $charset;
	
		$selector = "<select name='".$this->objects_type."_status'>";
		$selector .= "<option value='-1' ".(($this->filters['status'] == -1) ? 'selected="selected"' : '').">".htmlentities($msg['scan_request_list_statuses_selector_open'], ENT_QUOTES, $charset)."</option>";
		$selector .= "<option value='0' ".((!$this->filters['status']) ? 'selected="selected"' : '').">".htmlentities($msg['scan_request_list_statuses_selector_all'], ENT_QUOTES, $charset)."</option>";
        $selector .= scan_request_statuses::get_options($this->filters['status']);
		$selector .= "</select>";
		return $selector;
	}
	
	protected function get_search_filter_priority() {
	    global $msg, $charset;
	    
	    $selector = "<select name='".$this->objects_type."_priority'>";
	    $selector .= "<option value='0'>".htmlentities($msg['scan_request_list_priorities_selector_all'], ENT_QUOTES, $charset)."</option>";
        $selector .= scan_request_priorities::get_options($this->filters['priority']);
	    $selector .= "</select>";
	    return $selector;
	}
	
	protected function get_search_filter_user_input() {
		return $this->get_search_filter_simple_text('user_input', 50);
	}
	
	protected function get_search_filter_date() {
		return $this->get_search_filter_interval_date('date');
	}
	
	protected function get_search_filter_wish_date() {
	    return $this->get_search_filter_interval_date('wish_date');
	}
	
	protected function get_search_filter_deadline_date() {
	    return $this->get_search_filter_interval_date('deadline_date');
	}
	
	protected function get_search_filter_location() {
	    global $msg;
	    return gen_liste("select idlocation, location_libelle from docs_location order by location_libelle ", "idlocation", "location_libelle", $this->objects_type.'_location', "", $this->filters['location'], "", "", "0", $msg['all_location'],0);
	}
	
	/**
	 * Jointure externes SQL pour les besoins des filtres
	 */
	protected function _get_query_join_filters() {
		$filter_join_query = '';
		if($this->filters['status'] == -1) {
			$filter_join_query .= " LEFT JOIN scan_request_status ON scan_request_status.id_scan_request_status=scan_requests.scan_request_num_status";
		}
		return $filter_join_query;
	}
	
	protected function _add_query_filters() {
		if($this->filters['status'] == -1) {
			$this->query_filters [] = 'scan_request_status_is_closed = 0';
		} elseif($this->filters['status']) {
			$this->query_filters [] = 'scan_request_num_status = "'.$this->filters['status'].'"';
		}
		$this->_add_query_filter_simple_restriction('priority', 'scan_request_num_priority', 'integer');
		if($this->filters['user_input']) {
			$this->query_filters [] = 'scan_request_title like "%'.$this->filters['user_input'].'%"';
		}
		$this->_add_query_filter_interval_restriction('date', 'scan_request_date', 'datetime');
		$this->_add_query_filter_interval_restriction('wish_date', 'scan_request_wish_date', 'datetime');
		$this->_add_query_filter_interval_restriction('deadline_date', 'scan_request_deadline_date', 'datetime');
		$this->_add_query_filter_simple_restriction('location', 'scan_request_num_location', 'integer');
		$this->_add_query_filter_multiple_restriction('empr', 'scan_request_num_dest_empr', 'integer');
	}
	
	protected function _get_query_human_status() {
		global $msg;
		
		if($this->filters['status'] == -1) {
			return $msg['scan_request_list_statuses_selector_open'];
		} elseif(!empty($this->filters['status'])) {
			$query = "select scan_request_status_label from scan_request_status where id_scan_request_status = ".$this->filters['status'];
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)){
				return pmb_mysql_result($result, 0, 'scan_request_status_label');
			}
		}
		return '';
	}
	
	protected function _get_query_human_priority() {
		if(!empty($this->filters['priority'])) {
			$query = "select scan_request_priority_label from scan_request_priorities where id_scan_request_priority = ".$this->filters['priority'];
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)){
				return pmb_mysql_result($result, 0, 'scan_request_priority_label');
			}
		}
		return '';
	}
	
	protected function _get_query_human_location() {
		if(!empty($this->filters['location'])) {
			$docs_location = new docs_location($this->filters['location']);
			return $docs_location->libelle;
		}
		return '';
	}
	
	protected function _get_object_property_priority($object) {
		return $object->get_priority()->get_label();
	}
	
	protected function _get_object_property_status($object) {
		return $object->get_status()->get_label();
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'status':
			    $content .= "<span><img id='scan_request_img_statut_part_".$object->get_id()."' class='".$object->get_status()->get_class_html()."' style='width:7px; height:7px; vertical-align:middle; margin-left:-3px;' src='./images/spacer.gif'></span>";
			    $content .= parent::get_cell_content($object, $property);
			    break;
			case 'edit':
			    break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
// 	protected function get_default_attributes_format_cell($object, $property) {
// 		return array(
// 				'onclick' => "scan_request_show_form(".$object->get_id().")"
// 		);
// 	}
	
	/**
	 * Header de la liste
	 */
	public function get_display_header_list() {
	    global $include_path;
	    global $scan_request_order_by, $scan_request_order_by_sens;
	    
	    $scan_request_order_by = $this->applied_sort[0]['by'];
	    $scan_request_order_by_sens = ($this->applied_sort[0]['asc_desc'] ? $this->applied_sort[0]['asc_desc'] : 'asc');
	    $display = '';
	    $tpl = $include_path.'/templates/scan_request/scan_requests_header_list.tpl.html';
	    if (file_exists($include_path.'/templates/scan_request/scan_requests_header_list_subst.tpl.html')) {
	        $tpl = $include_path.'/templates/scan_request/scan_requests_header_list_subst.tpl.html';
	    }
	    if(file_exists($tpl)) {
	        $h2o = H2o_collection::get_instance($tpl);
	        $this->scan_requests = $this->objects;
	        $display .= $h2o->render(array('scan_requests' => $this));
	    } else {
	        $display .= '<tr>';
	        foreach ($this->columns as $column) {
	            $display .= $this->_get_cell_header($column['property'], $column['label']);
	        }
	        $display .= '</tr>';
	    }
	    return $display;
	}
	
	/**
	 * Objet de la liste
	 */
	protected function get_display_content_object_list($object, $indice) {
	    global $include_path;
	    
	    $display = '';
	    $tpl = $include_path.'/templates/scan_request/scan_request_in_list.tpl.html';
	    if (file_exists($include_path.'/templates/scan_request/scan_request_in_list_subst.tpl.html')) {
	        $tpl = $include_path.'/templates/scan_request/scan_request_in_list_subst.tpl.html';
	    }
	    if(file_exists($tpl)) {
	       $h2o = H2o_collection::get_instance($tpl);
	       $empr = $object->get_empr();
	       $display .= $h2o->render(array('scan_request' => $object, 'empr' => $empr));
	    } else {
            $display .= "
					<tr class='".($indice % 2 ? 'odd' : 'even')."' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".($indice % 2 ? 'odd' : 'even')."'\">";
    	    foreach ($this->columns as $column) {
    	        if($column['html']) {
    	            $display .= $this->get_display_cell_html_value($object, $column['html']);
    	        } else {
    	            $display .= $this->get_display_cell($object, $column['property']);
    	        }
    	    }
    	    $display .= "</tr>";
	    }
	    return $display;
	}
	
	public function get_scan_requests() {
	    return $this->scan_requests;
	}
}