<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_enrichment.inc.php,v 1.9 2023/08/28 14:01:13 tsamson Exp $

require_once($class_path."/enrichment.class.php");
require_once($class_path."/parametres_perso.class.php");

if (!isset($enrichPage)) $enrichPage = 1;
if (!isset($enrich_params)) $enrich_params = array();
if (!isset($action)) $action = '';
if (!isset($type)) $type = '';
$id = intval($id);

$return = array(
	'state' => 0,
	'notice_id' => $id,
	'result' => array(),
	'error' => ""
	
);

if(!$id){
	$return['error'] = "no input";
}else{
	$rqt= "select niveau_biblio, typdoc from notices where notice_id='".$id."'";
	$res = pmb_mysql_query($rqt);
	if(pmb_mysql_num_rows($res)){
		$r = pmb_mysql_fetch_object($res);
		$enrichment = new enrichment($r->niveau_biblio, $r->typdoc);
		switch($action){
			case "gettype":
				$typeofenrichment = $enrichment->getTypeOfEnrichment($id);
				$return["result"] = $typeofenrichment;
				break;
			default :
				if($enrichPage)	$enhance = $enrichment->getEnrichment($id,$type,$enrich_params,$enrichPage);
				else $enhance = $enrichment->getEnrichment($id,$type,$enrich_params);
				$return["result"] = $enhance;
				break;
		}
		$return["state"] = 1;
	}
}

//On renvoie du JSON dans le charset de PMB...
if( empty($debug) ) {
	header("Content-Type:application/json; charset=$charset");
	$return = charset_pmb_normalize($return);
	print json_encode($return);
}

function charset_pmb_normalize($mixed){
	global $charset;
	$is_array = is_array($mixed);
	$is_object = is_object($mixed);
	if($is_array || $is_object){
		foreach($mixed as $key => $value){
			 if($is_array) $mixed[$key]=charset_pmb_normalize($value);
			 else $mixed->$key=charset_pmb_normalize($value);
		}
	}elseif ($charset!="utf-8") {
		$mixed =encoding_normalize::utf8_normalize($mixed);	
	} 
	return $mixed;
}
