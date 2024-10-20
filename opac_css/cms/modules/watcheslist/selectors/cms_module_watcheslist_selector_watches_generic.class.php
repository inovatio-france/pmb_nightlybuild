<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_watcheslist_selector_watches_generic.class.php,v 1.5 2022/01/18 20:34:17 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_watcheslist_selector_watches_generic extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->once_sub_selector = true;
	}
	
	public function get_sub_selectors(){
		return array(
			"cms_module_watcheslist_selector_watches",
			"cms_module_common_selector_env_var",
			"cms_module_common_selector_type_article_generic",
			"cms_module_common_selector_type_article",
			"cms_module_common_selector_type_section_generic",
			"cms_module_common_selector_type_section",
			"cms_module_watcheslist_selector_watches_by_categories"
		);
	}
	
	/*
	 * Retourne la valeur sélectionné
	*/
	public function get_value(){
		if(!$this->value){
			if($this->parameters['sub_selector']){
				$sub_selector = $this->get_selected_sub_selector();
				$this->value = $sub_selector->get_value();
			}else{
				$this->value = array();
			}
			if(!is_array($this->value)){
				$this->value = array($this->value);
			}
		}
		return $this->value;
	}
}