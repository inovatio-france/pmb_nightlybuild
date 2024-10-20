<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_acquisition_ui.class.php,v 1.1 2021/01/15 10:57:53 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_configuration_acquisition_ui extends list_configuration_ui {
		
	public function __construct($filters=array(), $pager=array(), $applied_sort=array()) {
		static::$module = 'admin';
		static::$categ = 'acquisition';
		static::$sub = str_replace(array('list_configuration_acquisition_', '_ui'), '', static::class);
		parent::__construct($filters, $pager, $applied_sort);
	}
}