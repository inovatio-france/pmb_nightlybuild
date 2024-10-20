<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_section_by_cp_and_search_universe.class.php,v 1.3 2021/03/16 17:52:08 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
class cms_module_common_selector_section_by_cp_and_search_universe extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	
	public function get_sub_selectors(){
		return array(
			"cms_module_common_selector_section",
			"cms_module_common_selector_type_section_filter"
		);
	}
	
	/*
	 * Retourne la valeur s�lectionn�
	 */
	public function get_value(){
	    
	    global $id, $lvl;
	    
	    if ($lvl != "search_segment" && $lvl != "search_universe") {
	        $this->values = array();
	        return $this->value;
	    }
	    
	    if(!$this->value){
	        $parent = new cms_module_common_selector_section($this->get_sub_selector_id("cms_module_common_selector_section"));
	        $id_rubrique = $parent->get_value();
	        
	        $cp = new cms_module_common_selector_type_section_filter($this->get_sub_selector_id("cms_module_common_selector_type_section_filter"));
	        $field = $cp->get_value();
	        
	        $sections = [];
	        $query = "SELECT id_section FROM cms_sections WHERE section_num_parent = ". $id_rubrique . " AND section_num_type = " . $field["type"];
	        $result = pmb_mysql_query($query);
	        
	        while ($row = pmb_mysql_fetch_assoc($result)) {
	            $sections[] = $row['id_section'];
	        }
	        
	        $id_univers = $id;
	        if (!empty($lvl) && $lvl == "search_segment"){
	            $query = "SELECT search_segment_num_universe FROM search_segments WHERE id_search_segment = $id";
	            $result = pmb_mysql_query($query);
	            $id_univers = pmb_mysql_result($result, 0, 0);
	        }
	        
	        $fields = new cms_editorial_parametres_perso($field['type']);
	        foreach ($sections as $section){
	            $fields->get_values($section);
	            if(in_array($id_univers, $fields->values[$field['field']])){
	                $this->value = $section;
	                break;
	            }
	        }
	    }
	    return $this->value;
	}
}