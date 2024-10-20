<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cashdesk.inc.php,v 1.3 2021/01/15 10:14:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $id;
//Gestion des caisses

require_once($class_path."/cashdesk/cashdesk.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

configuration_controller::set_model_class_name('cashdesk');
configuration_controller::set_list_ui_class_name('list_configuration_finance_cashdesk_ui');
configuration_controller::proceed($id);