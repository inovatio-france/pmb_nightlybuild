<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frbr_entity_works_datasource_works_other_links.class.php,v 1.9 2021/02/25 16:27:00 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class frbr_entity_works_datasource_works_other_links extends frbr_entity_works_datasource_works_links {
	
	protected static $type = "other_link";
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas($datas=array()){
		$query = "SELECT DISTINCT oeuvre_link_to AS id, oeuvre_link_from AS parent, oeuvre_link_type AS group_key FROM tu_oeuvres_links
			WHERE oeuvre_link_other_link = 1 AND oeuvre_link_from IN (".implode(',', $datas).")";
		if (!empty($this->work_link_type)) {
			if (is_array($this->work_link_type)) {
				$query .= " AND oeuvre_link_type IN ('".implode("','", $this->work_link_type)."')";
			} else {
				$query .= " AND oeuvre_link_type = '".$this->work_link_type."'";
			}
		}
		$query .= " ORDER BY oeuvre_link_order ASC";
		$datas = $this->get_datas_from_query($query);
		$datas = parent::get_datas($datas);		
		return $datas;
	}
}