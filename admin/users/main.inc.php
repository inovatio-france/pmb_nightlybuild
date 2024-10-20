<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.13 2023/08/29 09:04:56 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $sub, $admin_user_javascript, $id;

switch ($sub) {
	case 'groups':
		require_once("./admin/users/users_groups.inc.php");
		break;
	case 'users' :
	default:
		require_once($class_path.'/users/users_controller.class.php');
		print $admin_user_javascript;
		users_controller::proceed($id);
		break;
}
