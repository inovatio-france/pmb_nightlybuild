<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: map_ref.class.php,v 1.3 2023/06/23 07:21:23 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class map_ref {
	
	/* ---------------------------------------------------------------
	 propri�t�s de la classe
	 --------------------------------------------------------------- */
	
	public $id=0;
	public $name='';
	
	public function __construct($id=0) {
		$this->id = intval($id);
		$this->getData();
	}
	
	/* ---------------------------------------------------------------
	 getData() : r�cup�ration des propri�t�s
	 --------------------------------------------------------------- */
	public function getData() {
		if(!$this->id) return;
		
		$requete = 'SELECT * FROM map_refs WHERE map_ref_id='.$this->id;
		$result = @pmb_mysql_query($requete);
		if(!pmb_mysql_num_rows($result)) {
			pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			return;
		}
		$data = pmb_mysql_fetch_object($result);
		$this->name = $data->map_ref_name ;
	}
	
	public function get_content_form() {
		$interface_content_form = new interface_content_form(static::class);
		$interface_content_form->add_element('form_nom', 'admin_noti_map_ref_name')
		->add_input_node('text', $this->name);
		return $interface_content_form->get_display();
	}
	
	public function get_form() {
		global $msg;
		
		$interface_form = new interface_admin_form('map_refform');
		if(!$this->id){
			$interface_form->set_label($msg['admin_noti_map_ref_ajout']);
		}else{
			$interface_form->set_label($msg['admin_noti_map_ref_modification']);
		}
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->name." ?")
		->set_content_form($this->get_content_form())
		->set_table_name('map_refs')
		->set_field_focus('form_nom');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $form_nom;
		
		$this->name = stripslashes($form_nom);
	}
	
	public function save() {
		if($this->id) {
			$requete = "UPDATE map_refs SET map_ref_name='".addslashes($this->name)."' WHERE map_ref_id='".$this->id."' ";
			$res = pmb_mysql_query($requete);
		} else {
			$requete = "SELECT count(1) FROM map_refs WHERE map_ref_name='".addslashes($this->name)."' LIMIT 1 ";
			$res = pmb_mysql_query($requete);
			$nbr = pmb_mysql_result($res, 0, 0);
			if($nbr == 0){
				$requete = "INSERT INTO map_refs (map_ref_name) VALUES ('".addslashes($this->name)."') ";
				$res = pmb_mysql_query($requete);
			}
		}
	}
	
	public static function check_data_from_form() {
		global $form_nom;
		
		if(empty($form_nom)) {
			return false;
		}
		return true;
	}
	
	public static function delete($id) {
		$id = intval($id);
		if ($id) {
			$requete = "DELETE FROM map_refs WHERE map_ref_id='$id' ";
			pmb_mysql_query($requete);
			$requete = "OPTIMIZE TABLE map_refs ";
			pmb_mysql_query($requete);
			return true;
		}
		return true;
	}
} /* fin de d�finition de la classe */