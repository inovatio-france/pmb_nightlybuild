<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_node_authority.class.php,v 1.2 2024/07/09 13:34:33 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class interface_node_authority extends interface_node {
	
    protected $class = 'saisie-20emr';
	
    protected $hidden_name;
    
    protected $hidden_value;
    
    protected $completion;
    
    protected $openPopUpUrl;
    
    protected $param1;
    
    public function get_display() {
        global $msg, $charset;
        
//         templates::init_selection_attributes(array(
//             array('name' => 'dyn', 'value' => '2'),
//         ));
//         $selector .= templates::get_display_elements_completion_field($elements, $this->get_form_name(), $this->objects_type.'_applicants', $this->objects_type.'_applicants_id', 'emprunteur');
        
        $display = "
        <input type='text' data-form-name='".$this->name."' id='".$this->id."' autfield='".$this->hidden_name."' completion='".$this->completion."' class='".$this->class."' value='".htmlentities($this->value, ENT_QUOTES, $charset)."' autocomplete='off' param1='".(!empty($this->param1) ? $this->param1 : '')."' />
		<input type='button' class='bouton_small' value='".htmlentities($msg['parcourir'], ENT_QUOTES, $charset)."' onclick=\"openPopUp('".$this->openPopUpUrl."&deb_rech='+this.form.".$this->name.".value, 'selector')\"/>
		<input type='button' class='bouton_small' value='".htmlentities($msg['raz'], ENT_QUOTES, $charset)."'  onclick=\"this.form.".$this->name.".value=''; this.form.".$this->hidden_name.".value='0'; \" />
		<input type='hidden' data-form-name='".$this->hidden_name."' id='".$this->hidden_name."' name='".$this->hidden_name."' value='".htmlentities($this->hidden_value, ENT_QUOTES, $charset)."' />
        ";

        return $display;
    }
    
// 	public function get_type() {
// 		return $this->type;
// 	}
	
    public function get_openPopUpUrl() {
        if (empty($this->openPopUpUrl)) {
            $this->openPopUpUrl = "./select.php?what=";
        }
        return $this->openPopUpUrl;
    }
    
// 	public function is_required() {
// 		return $this->required;
// 	}
	
	public function set_hidden_name($hidden_name) {
	    $this->hidden_name = $hidden_name;
		return $this;
	}
	
	public function set_hidden_value($hidden_value) {
	    $this->hidden_value = $hidden_value;
	    return $this;
	}
	
	public function set_completion($completion) {
	    $this->completion = $completion;
	    return $this;
	}
	
	public function set_openPopUpUrl($openPopUpUrl) {
	    $this->openPopUpUrl = $openPopUpUrl;
	    return $this;
	}
	
	public function set_param1($param1) {
	    $this->param1 = $param1;
	    return $this;
	}
	
// 	public function set_required($required) {
// 		$this->required = $required;
// 		return $this;
// 	}
}