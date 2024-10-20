<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ret_todo.inc.php,v 1.6 2020/07/20 07:15:34 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $form_cb_expl, $action_piege, $piege_resa;
require_once("$class_path/expl_to_do.class.php");

$url="./circ.php?categ=ret_todo";

$expl=new expl_to_do($form_cb_expl,0,$url);
$expl->build_cb_tmpl($msg["alert_circ_retour"]." > ".$msg["alert_circ_retour_todo"], $msg[661], $msg["circ_tit_form_cb_expl"], $url);
if($form_cb_expl){
	$expl->do_form_retour($action_piege,$piege_resa);	
}
print $expl->cb_tmpl.$expl->expl_form.$expl->gen_liste(); 



