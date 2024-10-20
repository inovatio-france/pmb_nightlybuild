<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_indexation_fields_ui.class.php,v 1.2 2024/10/04 12:29:37 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_indexation_fields_ui extends list_indexation_ui {
    
    protected function _init_fields() {
        $fields = $this->get_fields();
        foreach ($fields as $field) {
            $this->add_object((object) $field);
        }
    }
    
    protected function fetch_data() {
        $this->set_filters_from_form();
        $this->objects = array();
        $this->_init_fields();
        $this->pager['nb_results'] = count($this->objects);
        $this->messages = "";
    }
    
	protected function init_default_selected_filters() {
	    $this->add_selected_filter('entity_type');
		$this->add_selected_filter('field');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
    			    'id' => 'indexation_code',
    			    'field' => 'indexation_label',
    			    'pond' => 'indexation_pond',
                    'state' => 'indexation_state',
                    'actions' => 'indexation_actions'
			)
		);
	}
	
	protected function init_default_columns() {
	    $this->add_column('id');
		$this->add_column('field');
		$this->add_column('pond');
		$this->add_column('state');
		$this->add_column('actions');
	}	
	
	protected function _get_object_property_state($object) {
	    if(!isset($object->state)) {
	        $object->state = 0;
	        $this->_init_table_fields();
	        $object->state = (!empty($this->table_fields[$object->id]) ? $this->table_fields[$object->id] : 0);
	    }
	    return $object->state;
	}
}