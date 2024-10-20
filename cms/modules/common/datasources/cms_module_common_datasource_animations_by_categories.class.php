<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_animations_by_categories.class.php,v 1.1 2021/03/26 13:47:46 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_animations_by_categories extends cms_module_common_datasource_animations_list {
	
	public function __construct($id = 0) {
		parent::__construct($id);
		$this->sortable = true;
		$this->limitable = true;
	}
	
	/*
	 * On d�finit les s�lecteurs utilisables pour cette source de donn�es
	 */
	public function get_available_selectors() {
		return array(
			'cms_module_common_selector_category_permalink',
		    'cms_module_common_selector_category',
			'cms_module_common_selector_env_var'
		);
	}
		
	/*
	 * R�cup�ration des donn�es de la source
	 */
	public function get_datas() {
		$selector = $this->get_selected_selector();
		if (!empty($selector) && $selector->get_value()) {
		    $num_noeud = (int) $selector->get_value();
			$query = "SELECT DISTINCT num_animation FROM anim_animation_categories WHERE num_noeud = '$num_noeud'";			
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
			    $return = [];
			    $animations = [];
				while ($row = pmb_mysql_fetch_object($result)) {
				    $animations[] = $row->num_animation;
				}
			
				$return['animations'] = $this->filter_datas('animations', $animations);
	
				if (!count($return['animations'])) return false;
				
				$return = $this->sort_animations($return['animations']);
				$return['title'] = '';
				
				return $return;
			}
		}
		
		return false;
	}
}