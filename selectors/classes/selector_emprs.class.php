<?PHP
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: selector_emprs.class.php,v 1.2 2023/03/14 15:53:10 jparis Exp $
  
if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path, $class_path;
require_once($base_path."/selectors/classes/selector.class.php");

class selector_emprs extends selector {
	
	public function __construct($user_input=''){
		parent::__construct($user_input);
	}
	public function proceed() {
		global $search_data;
		$sc=new search(true,"search_fields_empr");
		
		// R�-affichage d'une recherche
		if (@unserialize($search_data) !== false) {
			$sc->unserialize_search(stripslashes($search_data));
		} elseif (!empty($search_data)) {
			$sc->json_decode_search(stripslashes($search_data));
		}
		
		print $sc->show_form("./circ.php?categ=search","./circ.php?categ=search&sub=launch", "", "./circ.php?categ=search_perso&sub=form");
		
	}
}