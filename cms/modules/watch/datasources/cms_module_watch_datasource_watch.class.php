<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_watch_datasource_watch.class.php,v 1.5 2022/01/18 15:27:47 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/docwatch/docwatch_watch.class.php");

class cms_module_watch_datasource_watch extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	*/
	public function get_available_selectors(){
		return array(
				"cms_module_watch_selector_watch_generic"
		);
	}
		
	public function get_form(){
		global $msg;
		
		if(!isset($this->parameters['load_items_data'])) $this->parameters['load_items_data'] = 1;
		
		$form = parent::get_form();
		$form .= "
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_watch_datasource_watch_load_items_data'>".$this->format_text($this->msg['cms_module_watch_datasource_watch_load_items_data'])."</label>
			</div>
			<div class='colonne-suite'>
				".$msg[39]." <input type='radio' name='cms_module_watch_datasource_watch_load_items_data' value='0' ".(!$this->parameters['load_items_data'] ? "checked='checked'" : "")." />
				".$msg[40]." <input type='radio' name='cms_module_watch_datasource_watch_load_items_data' value='1' ".($this->parameters['load_items_data'] ? "checked='checked'" : "")." />
			</div>
		</div>";
		
		return $form;
	}
	
	public function save_form(){
		global $cms_module_watch_datasource_watch_load_items_data;
		
		$this->parameters['load_items_data'] = intval($cms_module_watch_datasource_watch_load_items_data);
		return parent::save_form();
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		//on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
		$selector = $this->get_selected_selector();
		if($selector){
			$watch_id = $selector->get_value();
			if ($watch_id) {
				$docwatch_watch = new docwatch_watch($watch_id);
				if(!isset($this->parameters['load_items_data'])) $this->parameters['load_items_data'] = 1;
				if($this->parameters['load_items_data']) {
					$docwatch_watch->fetch_items();
				}
				return $docwatch_watch->get_normalized_watch();
			}
		}
		return false;
	}
	
	public function get_format_data_structure(){
		return $this->prefix_var_tree(docwatch_watch::get_format_data_structure(),"watch");
	}
}