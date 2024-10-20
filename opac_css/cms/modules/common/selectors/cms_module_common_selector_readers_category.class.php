<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_readers_category.class.php,v 1.1 2021/03/16 13:04:26 jlaurent Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector_readers_category extends cms_module_common_selector{
	
	/*
	 * Retourne la valeur s�lectionn�
	 */
	public function get_value(){
	    global $id_empr;
	    
	    if (!$id_empr){
	        return null;
	    }
	    
		$query = "SELECT empr_categ FROM empr WHERE id_empr = ".$id_empr;
		$result = pmb_mysql_query($query);
		$empr_categ = 0;
		if (pmb_mysql_num_rows($result)){
		    $empr_categ = pmb_mysql_fetch_assoc($result, 0, 0);
		}
		return intval($empr_categ["empr_categ"]);
	}
}