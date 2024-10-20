<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: entities_records_expl_controller.class.php,v 1.6 2023/04/07 09:12:31 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once ($class_path."/entities/entities_records_controller.class.php");
require_once($class_path."/expl.class.php");

class entities_records_expl_controller extends entities_records_controller {
		
    protected $cb;
    
	protected $record_id;
	
	/**
	 * 8 = droits de modification
	 */
	protected function get_acces_m() {
		$acces_m = 1;
		$exemplaire = new exemplaire($this->cb, $this->id,$this->record_id);
		if ($exemplaire->explr_acces_autorise == "INVIS") {
		    $acces_m = 0;
		}
		if($acces_m == 0) {
			$this->error_message = 'mod_expl_error';
		}
		return $acces_m;
	}
	
	protected function is_expl_deletable() {
		global $msg;
		
		$query = "select 1 from pret where pret_idexpl='".$this->id."' ";
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			// gestion erreur pr�t en cours
			error_message($msg[416], $msg['impossible_expl_del_pret'], 1, $this->get_permalink());
			return false;
		} else {
			return true;
		}
	}
	
	public function proceed_expl_delete() {
		global $msg;
	
		$query = "select 1 from pret where pret_idexpl='".$this->id."' ";
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			// gestion erreur pr�t en cours
			error_message($msg[416], $msg['impossible_expl_del_pret'], 1, $this->get_permalink());
		} else {
			exemplaire::del_expl($this->id);
			
			print "<div class='row'><div class='msg-perio'>".$msg['maj_encours']."</div></div>";
			print $this->get_redirection_form();
		}
	}
	
	protected function get_permalink($id=0) {
		if(!$id) $id = $this->record_id;
		return notice::get_permalink($id);
	}
	
	public function set_cb($cb) {
		$this->cb = $cb;
	}
	
	public function set_record_id($record_id=0) {
	    $this->record_id = (int) $record_id;
	}
}
