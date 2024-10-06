<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: show_cart.inc.php,v 1.96 2023/12/07 15:02:48 pmallambic Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $msg, $base_path, $count, $opac_nb_max_tri, $raz_cart, $action, $notice, $opac_search_results_per_page, $page;
global $opac_notices_depliable, $begin_result_liste, $cart_aff_case_traitement, $nb_per_page_custom;
global $opac_rgaa_active;

// pour export panier
require_once("$base_path/admin/convert/start_export.class.php");

if (isset($_GET['sort'])) {
	$_SESSION['last_sortnotices'] = $_GET['sort'];
}
if (isset($count) && $count > $opac_nb_max_tri) {
	$_SESSION['last_sortnotices'] = '';
}

$cart_ = (isset($_SESSION['cart']) ? $_SESSION['cart'] : array());

if (!empty($raz_cart)) {
	$cart_ = array();
	$_SESSION['cart'] = $cart_;
}

//Traitement des actions
if (!isset($action)) {
    $action = '';
}
if (!empty($action)) {
    if ($action == 'del' && !empty($notice) && is_countable($notice)) {
		for ($i = 0; $i < count($notice); $i++) {
			$as = array_search($notice[$i], $cart_);
			if ($as !== null && $as !== false) {
				//Décalage
				for ($j = $as + 1; $j < count($cart_); $j++) {
					$cart_[$j - 1] = $cart_[$j];
				}
				unset($cart_[count($cart_) - 1]);
			}
		}
		$_SESSION['cart'] = $cart_;
		if (ceil(count($cart_) / $opac_search_results_per_page) < $page) {
		    $page = count($cart_) / $opac_search_results_per_page;
		}
	}
}

print "<script src='".$base_path."/includes/javascript/cart.js'></script>";

print '<div id="cart_action">';

if (!isset($page) || $page == '') {
    $page = 1;
}
if (!empty($cart_)) {
	//gestion des notices externes (sauvegarde)
	$cart_ext = array();
	for ($i = 0; $i < sizeof($cart_); $i++){
		if (strpos($cart_[$i], 'es') !== false) {
			$cart_ext[] = $cart_[$i];
		}
	}
	
	//Tri
	if (isset($_SESSION['last_sortnotices']) && $_SESSION['last_sortnotices'] !=='') {
		$sort = new sort('notices', 'session');
		$sql = "SELECT notice_id FROM notices WHERE notice_id IN (";
		for ($z = 0; $z < count($cart_); $z++) {
			$sql .= "'". $cart_[$z] ."',";
		}
		$sql = substr($sql, 0, strlen($sql) - 1) .")";
		$sql = $sort->appliquer_tri($_SESSION['last_sortnotices'], $sql, 'notice_id', 0, 0);
	} else {
		$sql = "select notice_id from notices where notice_id in ('".implode("','", $cart_)."') order by index_serie, tnvol, index_sew";
	}
	$res = pmb_mysql_query($sql);
	$cart_ = array();
	while ($r = pmb_mysql_fetch_object($res)) {
		$cart_[] = $r->notice_id;
	}
	if (!empty($cart_ext)) {
	    $cart_ = array_merge($cart_, $cart_ext);
	}
	$_SESSION['cart'] = $cart_;

	$instance_cart = new cart();
	print $instance_cart->get_display_actions($cart_);
}

if (!empty($cart_)) {
	print common::format_title($msg['show_cart_content'].' : <b>'.sprintf($msg['show_cart_n_notices'], count($cart_)).'</b>', true);

	print '<div class="search_result">';
	if (!empty($opac_notices_depliable)) {
	    print $begin_result_liste;
	}

	if (count($cart_) <= $opac_nb_max_tri) {
		$affich_tris_result_liste = sort::show_tris_selector();
		$affich_tris_result_liste = str_replace('!!page_en_cours!!', urlencode('lvl=show_cart'), $affich_tris_result_liste);
		$affich_tris_result_liste = str_replace('!!page_en_cours1!!', 'lvl=show_cart', $affich_tris_result_liste);
		print $affich_tris_result_liste;
	}
	
	if (isset($_SESSION['last_sortnotices']) && $_SESSION['last_sortnotices'] !== "") {
		print "<span class='sort'>".$msg['tri_par'].' '.$sort->descriptionTriParId($_SESSION['last_sortnotices']).'<span class="espaceCartAction">&nbsp;</span></span>';
	}
	
	print '<blockquote role="presentation">';

	// case à cocher de suppression transférée dans la classe notice_affichage
	$cart_aff_case_traitement = 1 ;
	print "<form action='./index.php?lvl=show_cart&action=del&page=$page' method='post' name='cart_form'>";
	for ($i = (($page - 1) * $opac_search_results_per_page); ($i < count($cart_) && ($i < ($page * $opac_search_results_per_page))); $i++) {
		if (substr($cart_[$i], 0, 2) != 'es') {
			print pmb_bidi(aff_notice($cart_[$i], 1));
		} else {
			print pmb_bidi(aff_notice_unimarc(substr($cart_[$i], 2), 1));
		}
	}
	print '</form></blockquote></div>';
	if(!isset($nb_per_page_custom)) {
	    $nb_per_page_custom = '';
	}
	print '<div id="cart_navbar"><hr /><div style="text-align:center">'.printnavbar($page, count($cart_), $opac_search_results_per_page, './index.php?lvl=show_cart&page=!!page!!&nbr_lignes='.count($cart_).($nb_per_page_custom ? "&nb_per_page_custom=".$nb_per_page_custom : '')).'</div></div>';
} else {
    if ($opac_rgaa_active) {
    	print '<h1 class="empty_cart"><span>'.$msg['show_cart_is_empty'].'</span></h1>';
    } else {
    	print '<h3 class="empty_cart"><span>'.$msg['show_cart_is_empty'].'</span></h3>';
    }
}
print "</div>";
