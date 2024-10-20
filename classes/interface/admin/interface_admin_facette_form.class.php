<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_admin_facette_form.class.php,v 1.1 2024/01/31 07:35:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class interface_admin_facette_form extends interface_admin_form {
	
	protected $object_type;
	
	protected function get_action_cancel_label() {
		global $msg;
		return $msg['submitStopFacette'];
	}
	
	protected function get_action_save_label() {
		global $msg;
		if($this->object_id) {
		    return $msg['submitMajFacette'];
		} else {
		    return $msg['submitSendFacette'];
		}
	}
	
	protected function get_action_delete_label() {
		global $msg;
		return $msg['submitSupprFacette'];
	}
	
	protected function get_submit_action() {
	    return $this->get_url_base()."&action=save&id=".$this->object_id;
	}
	
	protected function get_delete_action() {
	    return $this->get_url_base()."&action=delete&id=".$this->object_id;
	}
	
	protected function get_js_script_error_label() {
		global $msg;
		return $msg['label_alert_form_facette'];
	}
	
	public function set_object_type($object_type) {
		$this->object_type = $object_type;
		return $this;
	}
	
	public function get_url_base() {
	    return parent::get_url_base().(!empty($this->object_type) ? "&type=".$this->object_type : "");
	}
}