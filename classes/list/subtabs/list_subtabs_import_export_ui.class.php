<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_subtabs_import_export_ui.class.php,v 1.1 2024/07/05 07:12:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_subtabs_import_export_ui extends list_subtabs_ui {
	
    public function get_title() {
        global $msg;
        
        $title = "";
        switch (static::$categ) {
            case 'scenarios':
                $title .= $msg['imports_exports_scenarios'];
                break;
            case 'conversions':
                $title .= $msg['imports_exports_conversions'];
                break;
            case 'profiles':
                $title .= $msg['imports_exports_profiles'];
                break;
        }
        return $title;
    }
	
    public function get_sub_title() {
        global $msg, $sub;
        
        $sub_title = "";
        switch (static::$categ) {
            case 'profiles':
                switch($sub) {
                    case 'import':
                        $sub_title .= $msg['imports_exports_profiles_import'];
                        break;
                }
                break;
            default:
                $sub_title .= parent::get_sub_title();
                break;
        }
        return $sub_title;
    }
    
	protected function _init_subtabs() {
		
	}
}