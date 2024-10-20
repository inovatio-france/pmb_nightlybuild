<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_itemslist_filter_by_interest.class.php,v 1.4 2022/05/30 14:25:00 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_itemslist_filter_by_interest extends cms_module_common_filter{
	
	public function get_filter_from_selectors(){
		return array(
				"cms_module_itemslist_selector_interesting_from"
		);
	}
	
	public function get_filter_by_selectors(){
		return array(
				"cms_module_itemslist_selector_interesting"
		);
	}
	
	public function filter($datas){
		$final_datas = array();
		if(count($datas)){
			array_walk($datas, 'static::int_caster');
			$query = "select id_item from docwatch_items where id_item in ('".implode("','",$datas)."')";
			$query .= " and item_interesting=1";
			$result = pmb_mysql_query($query);
			$filtered_datas = array();
			if($result && pmb_mysql_num_rows($result)){
				while($row = pmb_mysql_fetch_object($result)){
					$filtered_datas[] = $row->id_item;
				}
			}
			//les donn�es sont d�j� tri�es...dont on s'assure que ca ne bouge pas !
			$final_datas = array_merge(array_intersect($datas, $filtered_datas));
		}
		return $final_datas;
	}
}