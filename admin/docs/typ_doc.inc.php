<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: typ_doc.inc.php,v 1.30 2021/01/12 12:49:38 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $id;

require_once($class_path."/docs_type.class.php");
require_once($class_path."/configuration/configuration_controller.class.php");

configuration_controller::set_model_class_name('docs_type');
configuration_controller::set_list_ui_class_name('list_configuration_docs_typdoc_ui');
configuration_controller::proceed($id);