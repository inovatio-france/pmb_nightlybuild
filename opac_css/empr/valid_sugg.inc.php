<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: valid_sugg.inc.php,v 1.29 2023/12/04 13:07:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $include_path, $msg, $charset;
global $id_sug, $tit, $edi, $aut, $code, $nb, $mail;
global $opac_sugg_categ, $num_categ, $opac_sugg_categ_default, $sugg_location_id;

// classes de gestion des suggestions
require_once($base_path.'/classes/suggestions.class.php');
require_once($base_path.'/classes/suggestions_origine.class.php');
require_once($base_path.'/classes/suggestions_categ.class.php');
require_once($include_path.'/explnum.inc.php');
require_once($base_path.'/classes/explnum_doc.class.php');
require_once($base_path.'/classes/suggestion_source.class.php');

$sug_form = common::format_title($msg["empr_make_sugg"]);

// Contr�le des donn�es saisies 
if (($tit != "") && ($aut != "" || $edi != "" || $code != "" || $_FILES['piece_jointe_sug']['name'] != "") ) {		//Les donn�es minimun ont �t� saisies	

	$userid = $_SESSION["id_empr_session"];
	if (!$userid) {
		$type = '2';	//Visiteur non authentifi�
		$userid= $mail;	
	} else {
		$type = '1';	//Abonn�
	}

	//On �vite de saisir 2 fois la m�me suggestion
	if ($id_sug || !suggestions::exists($userid, $tit, $aut, $edi, $code)) {
		$su = new suggestions($id_sug);
		$su->set_properties_from_form();

		// chargement de la PJ
		if($_FILES['piece_jointe_sug']['name']){			
			$explnum_doc = new explnum_doc();
			$explnum_doc->load_file($_FILES['piece_jointe_sug']);
			$explnum_doc->analyse_file();
		} 
		
		if ($opac_sugg_categ == '1' ) {
			
			if (!suggestions_categ::exists($num_categ) ){
				$num_categ = $opac_sugg_categ_default;
			}
			 if (!suggestions_categ::exists($num_categ) ) {
				$num_categ = '1';
			}
			$su->num_categ = $num_categ;	
		}
		$su->sugg_location=$sugg_location_id;
		$su->save($explnum_doc ?? "");
		
		$orig = new suggestions_origine($userid, $su->id_suggestion);
		$orig->type_origine = $type;
		$orig->save();
		
		//R�-affichage de la suggestion
		$sug_form.= $su->get_table();
		$sug_form.= "<br />";
		$sug_form.= "<b>".htmlentities($msg["empr_sugg_ok"], ENT_QUOTES, $charset)."</b><br /><br />";
		
		//Envoi mail
		suggestions::alert_mail_sugg_users_pmb($type, $userid, $su->get_table(), $sugg_location_id) ;
		
	} else {
		//Mise en forme des donn�es pour r�-affichage
		$tit = stripslashes($tit);
		$edi = stripslashes($edi);
		$aut = stripslashes($aut);
		$code = stripslashes($code);
		//R�-affichage de la suggestion
		$sug_form.= "
		<table style='width:60%; padding:5px' role='presentation'>
			<tr>
				<td >".htmlentities($msg["empr_sugg_tit"], ENT_QUOTES, $charset)."</td>
				<td>".htmlentities($tit, ENT_QUOTES, $charset)."</td>
			</tr>
			<tr>
				<td >".htmlentities($msg["empr_sugg_aut"], ENT_QUOTES, $charset)."</td>
				<td>".htmlentities($aut, ENT_QUOTES, $charset)."</td>
			</tr>
			<tr>
				<td >".htmlentities($msg["empr_sugg_edi"], ENT_QUOTES, $charset)."</td>
				<td>".htmlentities($edi, ENT_QUOTES, $charset)."</td>
			</tr>
			<tr>
				<td >".htmlentities($msg["empr_sugg_code"], ENT_QUOTES, $charset)."</td>
				<td>".htmlentities($code, ENT_QUOTES, $charset)."</td>
			</tr>
			<tr>
				<td >".htmlentities($msg["empr_sugg_qte"], ENT_QUOTES, $charset)."</td>
				<td>".htmlentities($nb, ENT_QUOTES, $charset)."</td>
			</tr>";
		$sug_form.= "</table><br />";
		$sug_form.= "<b>".htmlentities($msg["empr_sugg_already_exist"], ENT_QUOTES, $charset)."</b><br /><br />";
	}
} else {	// Les donn�es minimun n'ont pas �t� saisies
	$sug_form.= str_replace('\n','<br />',$msg["empr_sugg_ko"])."<br /><br />";
	$sug_form.= "<input type='button' class='bouton' name='ok' value='&nbsp;".addslashes($msg['acquisition_sugg_retour'])."&nbsp;' onClick='history.go(-1)'/>";
}

print $sug_form;
 
?>