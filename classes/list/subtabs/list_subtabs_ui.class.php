<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_subtabs_ui.class.php,v 1.9 2023/12/18 15:17:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/subtabs/subtab.class.php");

class list_subtabs_ui extends list_ui {
	
	protected static $module_name;
	
	protected static $categ;
	
	protected $selected_subtab;
	
	protected function fetch_data() {
		$this->objects = array();
		$this->_init_subtabs();
	}
	
	public function add_subtab($sub, $label_code, $title_code='', $url_extra='') {
		global $msg;
		global $base_path;
		
		if(!$title_code) $title_code = $label_code;
		$subtab = new subtab();
		$subtab->set_sub($sub)
			->set_label_code($label_code)
			->set_label(isset($msg[$label_code]) ? $msg[$label_code] : $label_code)
			->set_title_code($title_code)
			->set_title(isset($msg[$title_code]) ? $msg[$title_code] : $title_code)
			->set_url_extra($url_extra)
			->set_destination_link($base_path."/".static::$module_name.".php?categ=".static::$categ.($sub ? "&sub=".$sub : '').$url_extra);
		$this->add_object($subtab);
	}
	
	protected function is_selected_tab($object) {
		return ongletSelect("categ=".static::$categ."&sub=".$object->get_sub().(!empty($object->get_url_extra()) ? $object->get_url_extra() : ''));
	}
	
	public function get_display_subtab($object) {
	    global $charset;
	    $title = $object->get_title();
	    if (strpos($title, $object->get_label()) === false) {
	        $title = $object->get_label()." : ".$title;
	    }
		return "<span".$this->is_selected_tab($object).">
			<a title='".htmlentities($title, ENT_QUOTES, $charset)."' href='".$object->get_destination_link()."'>
				".$object->get_label()."
			</a>
		</span>";
	}
	
	public function get_display_breadcrumb() {
		$display = "";
		$title = $this->get_title();
		$sub_title = $this->get_sub_title();
		if($title || $sub_title) {
			$display .= "<h1>";
			$display .= $title;
			if($sub_title) {
				$display .= " <span>> ".$sub_title."</span>";
			}
			$display .= "</h1>";
		}
		return $display;
	}
	
	public function get_sub_title() {
		$sub_title = "";
		$selected_subtab = $this->get_selected_subtab();
		if(!empty($selected_subtab)) {
			$sub_title .= $selected_subtab->get_label();
		}
		return $sub_title;
	}
	
	public function get_display() {
		$display = $this->get_display_breadcrumb();
		$display .= "<div class='hmenu'>";
		foreach ($this->objects as $object) {
			$display .= $this->get_display_subtab($object);
		}
		$display .= "</div>";
		return $display;
	}
	
	public function get_selected_subtab() {
		if(!isset($this->selected_subtab)) {
			foreach ($this->objects as $object) {
				if($this->is_selected_tab($object)) {
					$this->selected_subtab = $object;
				}
			}
		}
		return $this->selected_subtab;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters['main_fields'] = array();
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'label' => '103',
						'title' => '233',
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'label', 'title',
		);
	}
	
	protected function init_default_pager() {
		parent::init_default_pager();
		$this->pager['all_on_page'] = true;
	}
	
	protected function init_default_columns() {
		$this->add_column('label');
		$this->add_column('title');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('default', 'align', 'left');
	}
		
	public static function set_module_name($module_name) {
		static::$module_name = $module_name;
	}
	
	public static function set_categ($categ) {
		static::$categ = $categ;
	}
}