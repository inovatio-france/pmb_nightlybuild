<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: common_includes.inc.php,v 1.18 2023/08/16 14:09:47 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $charset, $dbh;
global $cms_build_activate, $pmb_indexation_lang;
global $opac_opac_view_activate, $opac_view, $pmb_opac_view_class, $opac_default_style;

require_once($base_path."/includes/error_report.inc.php") ;
require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');

// r�cup�ration param�tres MySQL et connection � la base
if (file_exists($base_path.'/includes/opac_db_param.inc.php')) require_once($base_path.'/includes/opac_db_param.inc.php');
else die("Fichier opac_db_param.inc.php absent / Missing file Fichier opac_db_param.inc.php");

// On vient de charger, le db_param, on regarde s'il y a une page de maintenace avant de faire la connexion � la BDD
// On le fait dans le sens l� car on a besoin de la d�finition du charset pour pousser la page de maintenance dans le bon charset...
if (file_exists($base_path.'/temp/.'.DATA_BASE.'_maintenance')) {
    session_start();
    if (!($cms_build_activate || $_SESSION['cms_build_activate'])) {
        header("Content-Type: text/html; charset=$charset");
        print file_get_contents($base_path.'/temp/'.DATA_BASE.'_maintenance.html');
        exit;
    }
}



require_once($base_path.'/includes/opac_mysql_connect.inc.php');
if(!isset($dbh) || !$dbh){
	$dbh = connection_mysql();
}

//Sessions !! Attention, ce doit �tre imp�rativement le premier include (� cause des cookies)
require_once($base_path."/includes/session.inc.php");

require_once($base_path.'/includes/start.inc.php');

// r�cup�ration localisation
require_once($base_path.'/includes/localisation.inc.php');

require_once($base_path."/includes/marc_tables/".$pmb_indexation_lang."/empty_words");
require_once($base_path."/includes/misc.inc.php");
require_once($class_path."/pmb_error.class.php");

// version actuelle de l'opac
require_once ($base_path . '/includes/opac_version.inc.php');

// fonctions de gestion de formulaire
require_once($base_path.'/includes/javascript/form.inc.php');

require_once ($base_path . '/includes/divers.inc.php');

require_once($base_path."/includes/check_session_time.inc.php");

// si les vues sont activ�es (a laisser apr�s le calcul des mots vides)
// Il n'est pas possible de changer de vue � ce niveau
if($opac_opac_view_activate){
	$current_opac_view=(isset($_SESSION["opac_view"]) ? $_SESSION["opac_view"] : '');
	if ($opac_view == -1) {
		// On d�finit la vue opac classique
		$_SESSION["opac_view"] = "default_opac";
	} elseif (is_numeric($opac_view) && $opac_view != 0) {
		// On d�fini une vue opac
		$_SESSION["opac_view"] = intval($opac_view);
	} elseif (empty($opac_view) && empty($current_opac_view)) {
		// On demande la vue mis par d�faut en gestion
		// Ou l'opac_view n'a jamais �t� d�finis
		$_SESSION["opac_view"] = "default";
	}
	$_SESSION['opac_view_query']=0;
	if(!$pmb_opac_view_class) $pmb_opac_view_class= "opac_view";
	require_once($base_path."/classes/".$pmb_opac_view_class.".class.php");

	$opac_view_class= new $pmb_opac_view_class((isset($_SESSION["opac_view"]) ? $_SESSION["opac_view"] : ''),$_SESSION["id_empr_session"]);
	if($opac_view_class->id){
		$opac_view_class->set_parameters();
		$opac_view_filter_class=$opac_view_class->opac_filters;
		$_SESSION["opac_view"]=$opac_view_class->id;
		if(!$opac_view_class->opac_view_wo_query) {
			$_SESSION['opac_view_query']=1;
		}
	} else {
		$_SESSION["opac_view"]=0;
	}
	$css=$_SESSION["css"]=$opac_default_style;
}
