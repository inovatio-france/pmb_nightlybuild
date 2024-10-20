<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.9 2021/03/17 13:32:14 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $no_rec_history, $class_path;
global $browser_url, $rec_history, $page, $msg, $tu;

if(!isset($no_rec_history)) $no_rec_history = '';

// page de switch recherche titres uniformes

require_once($class_path."/searcher.class.php");
require_once($class_path."/notice.class.php");

notice::init_globals_patterns_links();

$browser_url = "./catalog/notices/search/titres_uniformes/titre_uniforme_browser.php";

$rec_history=true;

if(!isset($page))  $page = '';

if (($no_rec_history)&&((string)$page=="")) {
	$_SESSION["CURRENT"]=count($_SESSION["session_history"]);
	$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["NOLINK"]=true;
	$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["HUMAN_QUERY"]=$msg["histo_free_browse"];
	$_SESSION["session_history"][$_SESSION["CURRENT"]]["QUERY"]["HUMAN_TITLE"]=$msg["titre_uniforme_search"];
	$_POST["page"]=0;
	$page=0;
}

$tu=new searcher_titre_uniforme("./catalog.php?categ=search&mode=9",$rec_history);

