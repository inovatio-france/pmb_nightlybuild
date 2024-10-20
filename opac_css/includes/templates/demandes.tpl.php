<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes.tpl.php,v 1.33 2023/12/28 09:50:14 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $form_liste_demande;
global $opac_demandes_affichage_simplifie;
global $form_modif_demande;
global $form_consult_dmde;
global $form_linked_record;
global $form_consult_linked_record;
global $msg, $javascript_path, $current_module;
global $opac_rgaa_active;

$form_liste_demande ="
<script src='".$javascript_path."/demandes_form.js' type='text/javascript'></script>
<script>

var base_path = '.';
var imgOpened = new Image();
imgOpened.src = '".get_url_icon("minus.gif")."';
var imgClosed = new Image();
imgClosed.src = '".get_url_icon("plus.gif")."';
var imgPatience =new Image();
imgPatience.src = '".get_url_icon("patience.gif")."';
var expandedDb = '';

function alert_progressiondemande(){
	alert(\"".$msg['demandes_progres_ko']."\");
}

</script>";

$form_liste_demande .= "
    <form class='form-".$current_module."' id='liste' name='liste' method='post' action=\"./empr.php?tab=request&lvl=list_dmde\">
    	<input type='hidden' name='act' id='act' />
    	<input type='hidden' name='state' id='state' />";
if ($opac_rgaa_active) {
    $form_liste_demande .= "<h1>".$msg['demandes_liste']."</h1>";
} else {
    $form_liste_demande .= "<h3>".$msg['demandes_liste']."</h3>";
}
$form_liste_demande.="<div class='row'>
    		!!select_etat!!
    	</div>
    	<div class='form-contenu'>
            !!liste_dmde!!
        </div>
    	<div class='row'></div>
    </form>
";

if(!$opac_demandes_affichage_simplifie){
	$date_prevue_label_tpl="<label class='etiquette'>".$msg['demandes_date_prevue']."</label>";
	$date_prevue_tpl="<input type='date' name='date_prevue' id='date_prevue' value='!!date_prevue!!' required/>";

	$date_echeance_label_tpl="<label class='etiquette'>".$msg['demandes_date_butoir']."</label>";
	$date_echeance_tpl="<input type='date' name='date_fin' id='date_fin' value='!!date_fin!!' required />";
} else {
	$date_prevue_label_tpl="";
	$date_prevue_tpl="";
	$date_echeance_label_tpl="";
	$date_echeance_tpl="";
}

$form_modif_demande = "
<form class='form-".$current_module."' id='modif_dmde' name='modif_dmde' method='post' action=\"!!form_action!!\">";

if ($opac_rgaa_active) {
    $form_modif_demande .= "<h1>!!form_title!!</h1>";
} else {
    $form_modif_demande .= "<h3>!!form_title!!</h3>";
}

$form_modif_demande .= "<input type='hidden' id='act' name='act' />
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='idempr' name='idempr' value='!!idempr!!' />
	<input type='hidden' id='idetat' name='idetat' value='!!idetat!!' />
	<input type='hidden' id='iduser' name='iduser' value='!!iduser!!' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_etat']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				!!select_theme!!
			</div>
			<div class='colonne3'>
				!!select_type!!
			</div>
			<div class='colonne3'>
				!!value_etat!!
			</div>
		</div>

		<div class='row'>
			<label class='etiquette'>".$msg['demandes_titre']."</label>
		</div>
		<div class='row'>
			<input class='saisie-50em' type='text' id='titre' name='titre' value='!!titre!!' />
		</div>
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_sujet']."</label>
		</div>
		<div class='row'>
			<textarea id='sujet' name='sujet' cols='55' rows='4' wrap='virtual'>!!sujet!!</textarea>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_dmde']."</label>
			</div>
			<div class='colonne3'>
				$date_prevue_label_tpl
			</div>
			<div class='colonne3'>
				$date_echeance_label_tpl
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<input type='date' name='date_debut' id='date_debut' required value='!!date_debut!!' />
			</div>
			<div class='colonne3'>
				$date_prevue_tpl
			</div>
			<div class='colonne3'>
				$date_echeance_tpl
			</div>
		</div>
		!!form_linked_record!!
		<div class='row'>&nbsp;</div>
	</div>
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='$msg[76]' onClick=\"!!cancel_action!!\" />
			<input type='submit' class='bouton' value='$msg[77]' onClick='this.form.act.value=\"save\";return test_form_demand(this.form); ' />
		</div>
    	<div class='row'>&nbsp;</div>
	</div>
	<div class='row'></div>
</form>

<script>
	function test_form_demand(form) {
		if(form.titre.value.length == 0){
			alert(\"$msg[demandes_create_ko]\");
			return false;
	    }
		var deb = dijit.byId('date_debut').get('value');
		var end = dijit.byId('date_fin').get('value');
		if(!deb || !end){
			alert(\"$msg[demandes_create_no_date]\");
			return false;
	    }
 		var date_debut = dojo.date.stamp.toISOString(deb, {selector: 'date'});
 		var date_fin = dojo.date.stamp.toISOString(end, {selector: 'date'});

	    if(date_debut > date_fin){
	    	alert(\"$msg[demandes_date_ko]\");
	    	return false;
	    }
		return true;

	}
</script>
";
if(!$opac_demandes_affichage_simplifie)
$form_consult_dmde = "
<script src='./includes/javascript/demandes.js' ></script>
<script src='./includes/javascript/tablist.js' ></script>
<script src='./includes/javascript/select.js' ></script>

<form class='form-".$current_module."' id='see_dmde' name='see_dmde' method='post' action=\"./demandes.php?categ=gestion\">
	<h3>!!icone!!!!form_title!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='state' name='state' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3 empr_demande_theme'>
				<label class='etiquette'>".$msg['demandes_theme']." : </label>
				!!theme_dmde!!
			</div>
			<div class='colonne3 empr_demande_etat'>
				<label class='etiquette'>".$msg['demandes_etat']." : </label>
				!!etat_dmde!!
			</div>
			<div class='colonne3 empr_demande_date_dmde'>
				<label class='etiquette'>".$msg['demandes_date_dmde']." : </label>
				!!date_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3 empr_demande_sujet'>
				<label class='etiquette'>".$msg['demandes_sujet']." : </label>
				!!sujet_dmde!!
			</div>
			<div class='colonne3 empr_demande_date_prevue'>
				<label class='etiquette'>".$msg['demandes_date_prevue']." : </label>
				!!date_prevue_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3 empr_demande_type'>
				<label class='etiquette'>".$msg['demandes_type']." : </label>
				!!type_dmde!!
			</div>
			<div class='colonne3 empr_demande_attribution'>
				<label class='etiquette'>".$msg['demandes_attribution']." : </label>
				!!attribution!!
			</div>
			<div class='colonne3 empr_demande_date_butoir'>
				<label class='etiquette'>".$msg['demandes_date_butoir']." : </label>
				!!date_butoir_dmde!!
			</div>
		</div>

		<div class='row'>
			<div class='colonne3'>
				&nbsp;
			</div>
			<div class='colonne3'>
				&nbsp;
			</div>
			<div class='colonne3 empr_demande_progression'>
				<label class='etiquette' >".$msg['demandes_progression']." : </label>
				<span id='progressiondemande_!!iddemande!!' name='progressiondemande_!!iddemande!!' dynamics='demandes,progressiondemande' dynamics_params='text'>!!progression_dmde!!</span>
			</div>
		</div>
		!!form_linked_record!!
		<div class='row'></div>
		<div class='row'>
			!!champs_perso!!
		</div>
		<div class='row'>&nbsp;</div>
	</div>

	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"document.location='./empr.php?tab=request&lvl=list_dmde&view=all!!params_retour!!'\" />
			!!demande_modify!!
		</div>
	</div>
	<div class='row'>
		<div class='left'>
			!!add_actions_list!!
		</div>
	</div>
	<div class='row'></div>
</form>
";
else
$form_consult_dmde = "
<script src='./includes/javascript/demandes.js' ></script>
<script src='./includes/javascript/tablist.js' ></script>
<script src='./includes/javascript/select.js' ></script>

<form class='form-".$current_module."' id='see_dmde' name='see_dmde' method='post' action=\"./demandes.php?categ=gestion\">
	<h3>!!icone!!!!form_title!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='state' name='state' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme']." : </label>
				!!theme_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_etat']." : </label>
				!!etat_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_dmde']." : </label>
				!!date_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_sujet']." : </label>
				!!sujet_dmde!!
			</div>
			<div class='colonne3'>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']." : </label>
				!!type_dmde!!
			</div>
		</div>
		!!form_linked_record!!
		<div class='row'></div>
		<div class='row'>
			!!champs_perso!!
		</div>
		<div class='row'>&nbsp;</div>
	</div>

	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"document.location='./empr.php?tab=request&lvl=list_dmde&view=all!!params_retour!!'\" />
			!!demande_modify!!
		</div>
	</div>
	<div class='row'>
		<div class='left'>
			!!add_actions_list!!
		</div>
	</div>
	<div class='row'></div>
</form>
";

$form_linked_record = "
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_linked_record']."</label>
		</div>
		<div class='row'>
			<a href='!!linked_record_link!!' title='!!linked_record!!' id='demandes_linked_record'>!!linked_record!!</a>
		</div>
		<input type='hidden' name='linked_record_id' value='!!linked_record_id!!'/>";

$form_consult_linked_record = "
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_linked_record']." : </label>
			<a href='!!linked_record_link!!' title='!!linked_record!!' id='demandes_linked_record'>!!linked_record!!</a>
		</div>";
?>