<?PHP
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: selector_notes.class.php,v 1.3 2021/10/20 11:51:36 dgoron Exp $
  
if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path;
require_once($base_path."/selectors/classes/selector.class.php");
require($base_path."/selectors/templates/sel_notes.tpl.php");

class selector_notes extends selector {
	
	public function __construct($user_input=''){
		parent::__construct($user_input);
	}
}
?>