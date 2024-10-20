<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: import_export.php,v 1.1 2024/07/05 07:12:51 dgoron Exp $

use Pmb\ImportExport\Controller\ImportExportController;

// définition du minimum nécéssaire
$base_path=".";
$base_auth = "IMPORT_EXPORT_AUTH";
$base_title = "\$msg[imports_exports_title]";
$base_use_dojo = 1;
require_once ("$base_path/includes/init.inc.php");

// modules propres à autorites.php ou à ses sous-modules
require_once($class_path."/modules/module_import_export.class.php");

module_import_export::get_instance()->proceed_header();

$data = new stdClass();
$data->categ = $categ ?? "";
$data->sub = $sub ?? "";
$data->action = $action ?? "";

$controller = new ImportExportController($data);
$controller->proceed();

module_import_export::get_instance()->proceed_footer();

// deconnection MYSql
pmb_mysql_close();
