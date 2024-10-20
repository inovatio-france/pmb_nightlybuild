<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_authors.class.php,v 1.3 2021/01/21 09:42:08 jlaurent Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/vedette/vedette_element.class.php");
require_once($class_path."/author.class.php");

class vedette_authors extends vedette_element{
	
	/**
	 * Cl� de l'autorit� dans la table liens_opac
	 * @var string
	 */
	protected $key_lien_opac = "lien_rech_auteur";
	protected $type = TYPE_AUTHOR;
	
	public function set_vedette_element_from_database(){
		$author = new auteur($this->id);
		$this->isbd = $author->get_isbd();
	    if (empty($this->isbd)){
	        $this->isbd = onto_contribution_datatype_resource_selector::get_properties_from_uri($this->id)['isbd'];
	    }
	}
}
