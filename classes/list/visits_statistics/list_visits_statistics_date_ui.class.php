<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_visits_statistics_date_ui.class.php,v 1.1 2024/10/16 13:31:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/visit_statistics.class.php');

class list_visits_statistics_date_ui extends list_visits_statistics_ui {
	
	protected function _get_query_base() {
		$query = 'select DATE(visits_statistics_date) as date, visits_statistics_location as location, visits_statistics_type as type, count(*) as visits_number from visits_statistics';
		return $query;
	}
	
	protected function _get_query_order() {
	    return ' GROUP BY date, location, type '.parent::_get_query_order();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('type');
		$this->add_column('location');
		$this->add_column('date');
		$this->add_column('visits_number');
		$this->add_column('actions');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('date', 'datatype', 'date');
	}
	
	protected function get_cell_content($object, $property) {
	    global $msg, $charset;
	    
	    $content = '';
	    switch($property) {
	        case 'actions':
	            //Voir le détails
	            $content .= "<input type='button' class='bouton_small' value='".htmlentities($msg["see"], ENT_QUOTES, $charset)."' onClick=\"document.location='".static::get_controller_url_base()."&visits_statistics_ui_date=".$object->date."'\" >";
	            break;
	        default :
	            $content .= parent::get_cell_content($object, $property);
	            break;
	    }
	    return $content;
	}
}