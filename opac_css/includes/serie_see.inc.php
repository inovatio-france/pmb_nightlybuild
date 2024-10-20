<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serie_see.inc.php,v 1.42 2024/03/22 15:31:02 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// affichage du detail pour une serie
require_once($class_path."/authorities/page/authority_page_serie.class.php");

$id = intval($id);
if($id) {
	$authority_page = new authority_page_serie($id);
	$authority_page->proceed('series');
}