<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_languages_datasource.class.php,v 1.3 2022/01/07 09:01:04 moble Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_languages_datasource extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_available_selectors(){
	    return array(
	        "cms_module_common_selector_lang"
	    );
	}
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
	    $selector = $this->get_selected_selector();
	    
	    $requete = "SELECT cadre_content_data FROM cms_cadre_content
                    WHERE cadre_content_type = 'view'
                    AND cadre_content_num_cadre = '$this->cadre_parent'";
	    $retour = pmb_mysql_query($requete);
	    while($obj = pmb_mysql_fetch_object($retour)){
	        $display_type = $obj->cadre_content_data;
	    }
	    $display_type = unserialize($display_type);
	    return [
	        'lang' => $selector->parameters,
	        'display_type' => $display_type
	    ];
	}
	
}