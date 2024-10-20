<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: addon.inc.php,v 1.8 2023/08/28 14:01:12 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if( !function_exists('traite_rqt') ) {
	function traite_rqt($requete="", $message="") {

		global $charset;
		$retour="";
		if($charset == "utf-8"){
			$requete=encoding_normalize::utf8_normalize($requete);
		}
		pmb_mysql_query($requete) ; 
		$erreur_no = pmb_mysql_errno();
		if (!$erreur_no) {
			$retour = "Successful";
		} else {
			switch ($erreur_no) {
				case "1060":
					$retour = "Field already exists, no problem.";
					break;
				case "1061":
					$retour = "Key already exists, no problem.";
					break;
				case "1091":
					$retour = "Object already deleted, no problem.";
					break;
				default:
					$retour = "<font color=\"#FF0000\">Error may be fatal : <i>".pmb_mysql_error()."<i></font>";
					break;
			}
		}		
		return "<tr><td><font size='1'>".($charset == "utf-8" ? encoding_normalize::utf8_normalize($message) : $message)."</font></td><td><font size='1'>".$retour."</font></td></tr>";
	}
}
echo "<table>";

/******************** AJOUTER ICI LES MODIFICATIONS *******************************/


/******************** JUSQU'ICI **************************************************/
/* PENSER � faire +1 au param�tre $pmb_subversion_database_as_it_shouldbe dans includes/config.inc.php */
/* COMMITER les deux fichiers addon.inc.php ET config.inc.php en m�me temps */

echo traite_rqt("update parametres set valeur_param='".$pmb_subversion_database_as_it_shouldbe."' where type_param='pmb' and sstype_param='bdd_subversion'","Update to $pmb_subversion_database_as_it_shouldbe database subversion.");
echo "<table>";