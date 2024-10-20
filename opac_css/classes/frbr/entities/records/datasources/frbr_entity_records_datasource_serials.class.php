<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frbr_entity_records_datasource_serials.class.php,v 1.2 2022/08/31 08:05:17 rtigero Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class frbr_entity_records_datasource_serials extends frbr_entity_common_datasource_records {
	
	public function __construct($id=0){
		$this->entity_type = 'records';
		parent::__construct($id);
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas($datas=array()){
		$query = "SELECT DISTINCT bulletin_notice as id, num_notice as parent FROM bulletins
			WHERE num_notice IN (".implode(',', $datas).")";
		$datas = $this->get_datas_from_query($query);
		$datas = parent::get_datas($datas);
		return $datas;
	}
}