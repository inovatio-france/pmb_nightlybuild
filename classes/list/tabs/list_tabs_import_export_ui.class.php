<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_tabs_import_export_ui.class.php,v 1.1 2024/07/05 07:12:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_tabs_import_export_ui extends list_tabs_ui {
	
	protected function _init_tabs() {

		//Section Scénario
		$this->add_tab('', 'scenarios', 'imports_exports_scenarios');
		
		//Section Profils
		$this->add_tab('', 'profiles_import', 'imports_exports_profiles_import');
	}
}