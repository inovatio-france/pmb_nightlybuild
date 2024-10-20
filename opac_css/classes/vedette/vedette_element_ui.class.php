<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_element_ui.class.php,v 1.2 2020/12/22 16:55:42 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/vedette/vedette_authors.tpl.php");


abstract class vedette_element_ui {

	
	protected static $created_boxes = array();
	
	/**
	 * Boite de s�lection de l'�l�ment
	 *
	 * @return string
	 * @access public
	 */
	public static function get_form($params = array(), $suffix = "") {
	    
	}

	
	/**
	 * Renvoie le code javascript pour la cr�ation du s�l�cteur
	 * 
	 * @return string
	 */
	public static function get_create_box_js($params=array(), $suffix = ""){
		
	}

	/**
	 * Renvoie les donn�es (id objet, type)
	 *
	 * @return void
	 * @access public
	 */
	public static function get_from_form(){
		
	}

}
