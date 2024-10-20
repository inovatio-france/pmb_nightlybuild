<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_collections.class.php,v 1.9 2020/06/02 10:12:08 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/vedette/vedette_element.class.php");
require_once($class_path."/collection.class.php");

class vedette_collections extends vedette_element{
	
	protected $type = TYPE_COLLECTION;
	
	public function set_vedette_element_from_database(){
		$this->entity = new authority(0, $this->id, AUT_TABLE_COLLECTIONS);
		$this->isbd = $this->entity->get_isbd();
	}
	
	public function get_link_see(){
	    global $use_opac_url_base;
	    
	    if($use_opac_url_base) {
	        return str_replace("!!type!!", "coll",$this->get_generic_link());
	    } else {
	        return str_replace("!!type!!", "collection",$this->get_generic_link());
	    }
	}
}
