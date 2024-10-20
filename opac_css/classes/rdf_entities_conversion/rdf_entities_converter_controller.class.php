<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: rdf_entities_converter_controller.class.php,v 1.6 2021/08/03 13:38:04 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/rdf_entities_conversion/rdf_entities_converter.class.php");

class rdf_entities_converter_controller {
    
	public function __construct($entity_id, $entity_type, $depth = 1) {
	    $this->entity_id = $entity_id * 1;
	    $this->entity_type = $entity_type;
	}
	
	/**
	 * Retourne la classe d'int�gration associ� au type d'entit�
	 * @param string $type type d'entit�
	 */
	public static function get_entity_converter_name_from_type($type) {
	    global $class_path;
		switch ($type) {
			default :
				$converter_class = 'rdf_entities_converter_'.$type;
				if (strpos($type, 'article') !== false) {
				    $converter_class = 'rdf_entities_converter_article';
				}
				if (strpos($type, 'section') !== false) {
				    $converter_class = 'rdf_entities_converter_section';
				}
				if (file_exists($class_path."/rdf_entities_conversion/".$converter_class.".class.php")) {
    				require_once($class_path."/rdf_entities_conversion/".$converter_class.".class.php");
    				if (class_exists($converter_class)) {
    				    return $converter_class;
    				}
				}
				return null;
		}
	}
	
	public static function convert($id, $type, $uri = "", $depth = 1) {
	    if (strpos($type, "authperso_") !== false) {
	        $type = "authperso";
	    }
	    $converter_class_name = rdf_entities_converter_controller::get_entity_converter_name_from_type($type);
	    if ($converter_class_name != null && class_exists($converter_class_name)) {
    	    $rdf_converter = new $converter_class_name($id, $type, $uri, $depth);
    	    return $rdf_converter->get_assertions();
	    }
        return false;
	}
}