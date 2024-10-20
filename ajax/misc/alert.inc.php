<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: alert.inc.php,v 1.17 2024/05/02 11:38:15 dgoron Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $current_alert, $charset, $aff_alerte;

// d�finition du minimum n�c�ssaire                         
$base_auth = "CIRCULATION_AUTH|CATALOGAGE_AUTH|AUTORITES_AUTH|ADMINISTRATION_AUTH|EDIT_AUTH";  
$base_title = "\$msg[5]";
require_once ("$base_path/includes/init.inc.php");  

require_once ("$class_path/alerts/alerts.class.php");

$list_tabs_alerts_ui = new list_tabs_alerts_ui();

//On lib�re la session car il n'y a pas d'�criture ensuite et cela �vite les verrous.
session_write_close();

$aff_alerte = $list_tabs_alerts_ui->get_display();

//on reprend le format de la r�ponse. VIVE LE JSON !
if (trim($aff_alerte)) {
	if($charset!="utf-8"){
		$aff_alerte = encoding_normalize::utf8_normalize($aff_alerte);
	}
	$response = array(
		'state' => 1,
		'module' => $current_alert,
		'separator' => "<hr class='alert_separator'>",
		'html' => $aff_alerte
	);
} else {
	$response = array(
			'state' => 1,
			'module' => $current_alert,
			'separator' => "",
			'html' => ""
	);
}
ajax_http_send_response($response);


// // le '1' permet de savoir que la session est toujours active, pour �viter les transactions ajax ult�rieures
// if($aff_alerte)ajax_http_send_response("1<hr class='alert_separator'> $aff_alerte");
// else ajax_http_send_response("1");
?>