<?PHP
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: selector_external_notice.class.php,v 1.2 2020/04/03 07:31:13 dgoron Exp $
  
if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class selector_external_notice extends selector_notice {
	
	public function __construct($user_input = '') {
		parent::__construct($user_input);
		$this->objects_type = 'external_records';
	}
	
	protected function get_search_instance() {
		$search = new search(false, 'search_fields_unimarc');
		$search->add_context_parameter('in_selector', true);
		return $search;
	}
}
?>