<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_rameau_categ_integral.inc.php,v 1.17 2021/12/09 14:22:19 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path; //N�cessaire pour certaines inclusions
require_once ($class_path."/import/import_expl_bdp.class.php");
require_once($class_path."/thesaurus.class.php");
require_once($class_path."/categories.class.php");
global $thes, $thesaurus_defaut, $id_rech_theme;


// DEBUT param�trage propre � la base de donn�es d'importation :

// r�cup�ration du 606 : r�cup en cat�gories en essayant de classer :
// RECUP int�grale : 

// Attention, dans le multithesaurus, l'identifiant de recherche par terme est
// la racine du thesaurus par defaut

//	les sujets sous le terme "Recherche par terme" 
		$thes = new thesaurus($thesaurus_defaut);
		$id_rech_theme = $thes->num_noeud_racine;
		
//	les pr�cisions g�ographiques sous le terme "Recherche g�ographique" 
//		$id_rech_geo = 2 ;
//	les pr�cisions de p�riode sous le terme "Recherche chronologique" 
//		$id_rech_chrono = 3 ;
// FIN param�trage 

function recup_noticeunimarc_suite($notice) {
} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres au CNL
	
function import_new_notice_suite() {
	global $notice_id ;
	
	global $info_606_a, $info_606_j, $info_606_x, $info_606_y, $info_606_z ;
	global $id_rech_theme ; 
	global $thesaurus_defaut;
	global $thes;
	
	/* 
	echo "<pre>";
	print_r ($info_949);
	print_r ($info_997);
	echo "</pre>";
	*/
	
	// les champs $606 sont stock�s dans les cat�gories
	//	$a >> en sous cat�gories de $id_rech_theme
	// 		$j en compl�ment de $a
	//		$x en sous cat�gories de $a
	// $y >> en sous cat�gories de $id_rech_geo
	// $z >> en sous cat�gories de $id_rech_chrono
	// TRAITEMENT :
	// pour $a=0 � size_of $info_606_a
	//	pour $j=0 � size_of $info_606_j[$a]
	//		concat�ner $libelle_j .= $info_606_j[$a][$j]
	//	$libelle_final = $info_606_a[0]." ** ".$libelle_j
	//	Rechercher si l'enregistrement existe d�j� dans categories = 
	//	$categid = categories::searchLibelle(addslashes($libelle_final), $thesaurus_defaut, 'fr_FR', $id_rech_theme)

	//	Cr�er si besoin et r�cup�rer l'id $categid_a
	//	$categid_parent =  $categid_a
	//	pour $x=0 � size_of $info_606_x[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//	$categid = categories::searchLibelle(addslashes($info_606_x[$a][$x]), $thesaurus_defaut, 'fr_FR', $categ_parent)

	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	//	$categid_parent =  $id_rech_geo
	//	pour $y=0 � size_of $info_606_y[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//	$categid = categories::searchLibelle(addslashes($info_606_y[$a][$y]), $thesaurus_defaut, 'fr_FR', $categ_parent)

	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	//	$categid_parent =  $id_rech_chrono
	//	pour $y=0 � size_of $info_606_z[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//	$categid = categories::searchLibelle(addslashes($info_606_z[$a][$y]]), $thesaurus_defaut, 'fr_FR', $categ_parent)

	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	$libelle_j="";
	for ($a=0; $a<count($info_606_a); $a++) {
		for ($j=0; $j<count($info_606_j[$a]); $j++) {
			if (!$libelle_j) $libelle_j .= trim($info_606_j[$a][$j]) ;
				else $libelle_j .= " ** ".trim($info_606_j[$a][$j]) ;
		}
		if (!$libelle_j) $libelle_final = trim($info_606_a[$a][0]) ;
			else $libelle_final = trim($info_606_a[$a][0])." ** ".$libelle_j ;
		if (!$libelle_final) break ; 
		$res_a = categories::searchLibelle(addslashes($libelle_final), $thesaurus_defaut, 'fr_FR', $id_rech_theme);
		if ($res_a) {
			$categid_a = $res_a;
		} else {
			$categid_a = create_categ($id_rech_theme, $libelle_final, strip_empty_words($libelle_final, 'fr_FR'));
		}
		// r�cup des sous-categ en cascade sous $a
		$categ_parent =  $categid_a ;
		for ($x=0 ; $x < count($info_606_x[$a]) ; $x++) {
			$res_x = categories::searchLibelle(addslashes(trim($info_606_x[$a][$x])), $thesaurus_defaut, 'fr_FR', $categ_parent);
			if ($res_x) {
				$categ_parent = $res_x;
			} else {
				$categ_parent = create_categ($categ_parent, trim($info_606_x[$a][$x]), strip_empty_words($info_606_x[$a][$x], 'fr_FR'));
			}
		} // fin r�cup des $x en cascade sous l'id de la cat�gorie 606$a
		
		if ($categ_parent != $id_rech_theme) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', num_noeud='".$categ_parent."' " ;
			pmb_mysql_query($rqt_ajout);
		}
		
		// r�cup TOUT EN CASCADE
		$id_rech_geo = $categ_parent ;		
		// r�cup des categ g�o � loger sous la categ g�o principale
		$categ_parent =  $id_rech_geo ;
		for ($y=0 ; $y < count($info_606_y[$a]) ; $y++) {
			$res_y = categories::searchLibelle(addslashes(trim($info_606_y[$a][$y])), $thesaurus_defaut, 'fr_FR', $categ_parent);
			if($res_y) {
				$categ_parent = $res_y;
			} else {
				$categ_parent = create_categ($categ_parent, trim($info_606_y[$a][$y]), strip_empty_words($info_606_y[$a][$y], 'fr_FR'));
			}
		} // fin r�cup des $y en cascade sous l'id de la cat�gorie principale th�me g�o
		
		if ($categ_parent != $id_rech_geo) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', num_noeud='".$categ_parent."' " ;
			pmb_mysql_query($rqt_ajout);
		}

		// r�cup TOUT EN CASCADE
		$id_rech_chrono = $categ_parent ;		
		// r�cup des categ chrono � loger sous la categ chrono principale
		$categ_parent =  $id_rech_chrono ;
		for ($z=0 ; $z < count($info_606_z[$a]) ; $z++) {
			$res_z = categories::searchLibelle(addslashes(trim($info_606_z[$a][$z])), $thesaurus_defaut, 'fr_FR', $categ_parent);
			if ($res_z) {
				$categ_parent = $res_z;
			} else {
				$categ_parent = create_categ($categ_parent, trim($info_606_z[$a][$z]), strip_empty_words($info_606_z[$a][$z], 'fr_FR'));
			}
		} // fin r�cup des $z en cascade sous l'id de la cat�gorie principale th�me chrono
		
		if ($categ_parent != $id_rech_chrono) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', num_noeud='".$categ_parent."' " ;
			pmb_mysql_query($rqt_ajout);
		}
	}
	
} // fin import_new_notice_suite
			
			
function create_categ($num_parent, $libelle, $index) {
	
	global $thes;
	$n = new noeuds();
	$n->num_thesaurus = $thes->id_thesaurus;
	$n->num_parent = $num_parent;
	$n->save();
	
	$c = new categories($n->id_noeud, 'fr_FR');
	$c->libelle_categorie = $libelle;
	$c->index_categorie = $index;
	$c->save();
	
	return $n->id_noeud;
}			
			
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	import_expl_bdp::traite_exemplaires('rameau_categ_integral');
} // fin traite_exemplaires	TRAITEMENT DES EXEMPLAIRES JUSQU'ICI

// fonction sp�cifique d'export de la zone 995
function export_traite_exemplaires ($ex=array()) {
	return import_expl::export_traite_exemplaires($ex);
}