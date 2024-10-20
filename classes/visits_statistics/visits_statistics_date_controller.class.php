<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: visits_statistics_date_controller.class.php,v 1.1 2024/10/16 13:31:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class visits_statistics_date_controller extends visits_statistics_controller {
	
	protected static $model_class_name = '';
	
	protected static $list_ui_class_name = 'list_visits_statistics_date_ui';
	
}