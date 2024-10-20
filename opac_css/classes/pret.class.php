<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pret.class.php,v 1.5 2024/01/23 13:31:26 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class pret {

/*---------------------------------------------------------------
		propri�t�s de la classe
  ---------------------------------------------------------------

	public $id_empr;				id emprunteur
	public $id_expl;				id exemplaire
	public $pret_date;				timestamp du d�but du pret
	public $pret_retour;			timestamp du retour pr�vu
	public $cb_expl;				code barre exemplaire
	public $type_doc;				type de doc de l'exemplaire
	public $owner				propri�taire de l'exemplaire
	public $date_pret_display;			date d�but du pr�t en format affichable
	public $date_retour_display;		date retour pr�vu du pr�t en format affichable
	public $resultat_action;			bool�en de r�sultat de l'action
	public $display;				reste dispo pour l'instant

  ---------------------------------------------------------------
		pret($id_empr, $id_expl, $cb_expl, $pret_date, $pret_retour) : constructeur
			id_empr = id de l'emprunteur
			id_expl = id de l'exemplaire
			cb_expl = code barre de l'exemplaire, au choix avec l'id
			pret_date = date du d�but du pret
			pret_retour = date du retour pr�vu
  --------------------------------------------------------------*/
	public $id_empr;
	public $id_expl;
	public $pret_date;
	public $pret_retour;
	public $cb_expl;
	public $type_doc;
	public $statut_doc;
	public $date_pret_display;
	public $date_retour_display;
	public $retard;
	public $etat;
	public $display;
	protected $exemplaire;
	protected $emprunteur;
	public $owner;

	protected $is_extendable;
	public $no_prolong_explanation = '';
	public $nb_prolongation = 0;
	public $pret_nombre_prolongation = 0;
	public $date_prolongation = '';
	public $aff_date_prolongation = '';


	/**
	 *
	 * @param integer $id_empr
	 * @param integer $id_expl
	 * @param string $cb_expl
	 * @param string $pret_date
	 * @param string $pret_retour
	 */
	public function __construct( $id_empr, $id_expl, $cb_expl='', $pret_date='', $pret_retour='') {
	    $this->id_empr= intval($id_empr);
		$this->id_expl = intval($id_expl);
		$this->cb_expl = $cb_expl;
		$this->getData();
	}


	//	r�cup�ration infos du pr�t
	public function getData() {
		global $msg;
		if(($this->id_expl==0) && ($this->cb_expl=="")) {
			// aucun identifiant. on retourne un tableau vide
			$this->id_empr = 0;
			$this->id_expl = 0;
			$this->pret_date = "";
			$this->pret_retour = "";
			$this->cb_expl = "";
			$this->type_doc="";
			$this->statut_doc="";
			$this->owner="";
			$this->date_pret_display="";
			$this->date_retour_display="";
			$this->etat=0;
			$this->display = $msg[4052];
		} else {
			$sql_dates = " date_format(pret_date, '".$msg["format_date"]."') as aff_pret_date, ";
			$sql_dates .= " date_format(pret_retour, '".$msg["format_date"]."') as aff_pret_retour, ";
			$sql_dates .= " IF(pret_retour>sysdate(),0,1) as retard " ;
			if ($this->id_expl!=0) $requete = "SELECT pret_idempr, pret_idexpl, pret_date, pret_retour, expl_cb, expl_typdoc, expl_statut, expl_owner, $sql_dates FROM pret, exemplaires WHERE pret_idexpl='".$this->id_expl."' and pret_idexpl=expl_id LIMIT 1 ";
				else $requete = "SELECT pret_idempr, pret_idexpl, pret_date, pret_retour, expl_cb, expl_typdoc, expl_statut, expl_owner, $sql_dates FROM pret, exemplaires WHERE expl_cb='".$this->cb_expl."' and pret_idexpl=expl_id LIMIT 1 ";
			$result = pmb_mysql_query($requete);
			if(pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);
				pmb_mysql_free_result($result);
				$this->id_empr = $temp->pret_idempr;
				$this->id_expl = $temp->pret_idexpl;
				$this->pret_date = $temp->pret_date;
				$this->pret_retour = $temp->pret_retour;
				$this->cb_expl = $temp->expl_cb;

				$requete = "select tdoc_libelle from docs_type where idtyp_doc='".$temp->expl_typdoc."' ";
				$result = pmb_mysql_query($requete);
				$typdoc = pmb_mysql_fetch_object($result);
				pmb_mysql_free_result($result);
				$this->type_doc = $typdoc->tdoc_libelle;

				$requete = "select statut_libelle from docs_statut where idstatut='".$temp->expl_statut."' ";
				$result = pmb_mysql_query($requete);
				$statdoc = pmb_mysql_fetch_object($result);
				pmb_mysql_free_result($result);
				$this->statut_doc = $statdoc->statut_libelle;

				$requete = "select lender_libelle from lenders where idlender='".$temp->expl_owner."' ";
				$result = pmb_mysql_query($requete);
				$lender = pmb_mysql_fetch_object($result);
				pmb_mysql_free_result($result);
				$this->owner = $lender->lender_libelle;

				$this->date_pret_display=$temp->aff_pret_date;
				$this->date_retour_display=$temp->aff_pret_retour;
				$this->retard=$temp->retard;
				$this->etat=1;
				$this->display = "Pr�t existant";
			} else {
				// pas de pr�t avec cette cl� : on va aller chercher le expl_cb avec l'id ou l'inverse
				$long_maxi_cb_expl = pmb_mysql_field_len(pmb_mysql_query("SELECT expl_cb FROM exemplaires limit 1"),0);
				$this->cb_expl = rtrim(substr(pmb_preg_replace('/\[|\]/', '', rtrim(ltrim($this->cb_expl))),0,$long_maxi_cb_expl));

				if ($this->id_expl==0) {
					/* ici la recherche de l'id_expl */
					$query = "SELECT expl_id, expl_cb FROM exemplaires WHERE expl_cb='${key_cb_expl}' LIMIT 1 ";
				} else {
					/* ici la recherche du cb � partir de l'id */
					$query = "SELECT expl_id, expl_cb FROM exemplaires WHERE expl_id='".$this->id_expl."' LIMIT 1 ";
				}
				$result = pmb_mysql_query($query) or die("can't SELECT exemplaires ".$query);
				if (pmb_mysql_num_rows($result)==0) { /* on n'a trouv� aucun exemplaire */
					$this->id_empr = 0;
					$this->id_expl = 0;
					$this->pret_date = "";
					$this->pret_retour = "";
					$this->cb_expl = "";
					$this->type_doc="";
					$this->statut_doc="";
					$this->owner = "";
					$this->date_pret_display="";
					$this->date_retour_display="";
					$this->etat=3;
					$this->display = "Exemplaire introuvable";
				} else {
					$expl  = pmb_mysql_fetch_object($result);
					$this->id_expl = $expl->expl_id;
					$this->cb_expl = $expl->expl_cb;
					$this->pret_retour = "";
					$this->type_doc="";
					$this->statut_doc="";
					$this->owner = "";
					$this->date_pret_display="";
					$this->date_retour_display="";
					$this->etat=2;
					$this->display = "Pr�t possible, inexistant avec cette cl�";
				}
			}
		}
	}

	// retour pr�t
	public function retour($retour_effectif) {
		// check sur le type de  la variable pass�e en param�tre
		if ($retour_effectif=="") $retour_effectif=time();

		/* on a tout ce qu'il faut, on peut supprimer le pr�t */

		/* on va d'abord transf�rer tout ce que l'on connait dans la table des archives pour les stats */
		$query = "SELECT pret_date as debut, cpt_prolongation, empr_cp, empr_ville, empr_prof, empr_year, empr_categ, empr_codestat, empr_sexe, empr_statut, empr_location, type_abt, ";
		$query.= "expl_typdoc, expl_cote, expl_statut, expl_location, expl_codestat, expl_section, expl_owner FROM pret, empr, exemplaires WHERE pret_idexpl='".$this->id_expl."' and id_empr=pret_idempr and expl_id=pret_idexpl ";
		$res_stat = pmb_mysql_query($query) or die(pmb_mysql_error()."<br />can't SELECT pret & co for stats <br />".$query."<br />");
		$temp = pmb_mysql_fetch_object($res_stat);
		$query = "insert into pret_archive set ";
		$query.="arc_debut          ='".$temp->debut         ."', ";
		$query.="arc_fin            ='".date("Y-m-d",$retour_effectif) ."', ";
		$query.="arc_empr_cp        ='".addslashes($temp->empr_cp       )."', ";
		$query.="arc_empr_ville     ='".addslashes($temp->empr_ville    )."', ";
		$query.="arc_empr_prof      ='".addslashes($temp->empr_prof     )."', ";
		$query.="arc_empr_year      ='".$temp->empr_year     			."', ";
		$query.="arc_empr_categ     ='".$temp->empr_categ    			."', ";
		$query.="arc_empr_codestat  ='".$temp->empr_codestat 			."', ";
		$query.="arc_empr_sexe      ='".$temp->empr_sexe     			."', ";
		$query.= "arc_empr_statut	='".$temp->empr_statut   			."', ";
		$query.= "arc_empr_location	='".$temp->empr_location     		."', ";
		$query.= "arc_type_abt		='".$temp->type_abt     			."', ";
		$query.="arc_expl_typdoc    ='".$temp->expl_typdoc   			."', ";
		$query.="arc_expl_cote      ='".addslashes($temp->expl_cote     )."', ";
		$query.="arc_expl_statut    ='".$temp->expl_statut   			."', ";
		$query.="arc_expl_location  ='".$temp->expl_location 			."', ";
		$query.="arc_expl_section  ='".$temp->expl_section 				."', ";
		$query.="arc_expl_codestat  ='".$temp->expl_codestat 			."', ";
		$query.="arc_expl_owner     ='".$temp->expl_owner    			."', ";
		$query.="arc_niveau_relance='".	$temp->niveau_relance 			."', ";
		$query.="arc_date_relance='".	$temp->date_relance				."', ";
		$query.="arc_printed='".		$temp->printed    				."', ";
		$query.="arc_cpt_prolongation='".$temp->cpt_prolongation		."' ";
		pmb_mysql_query($query) or die(pmb_mysql_error()."<br />can't insert in pret_archive <br />".$query."<br />");

		$query = "delete from pret where pret_idexpl = '".$this->id_expl."' ";
		pmb_mysql_query($query) or die("can't delete from pret ".$query."<br />".pmb_mysql_error());
		return 0;
	}

	public function is_extendable() {
		global $msg;
		global $opac_pret_prolongation, $allow_prol;
		global $opac_pret_duree_prolongation, $pmb_pret_restriction_prolongation, $pmb_pret_nombre_prolongation;
		global $pmb_pret_date_retour_adhesion_depassee, $pmb_location_reservation, $opac_pret_prolongation_blocage;

		if(!isset($this->is_extendable)) {
			if ($opac_pret_prolongation==1 && $allow_prol) {
				$prolongation=TRUE;
				$this->no_prolong_explanation = '';
				$query = "select cpt_prolongation, pret_date,pret_retour, expl_location, niveau_relance, short_loan_flag from pret, exemplaires where expl_id=pret_idexpl and pret_idexpl='".$this->id_expl."'";
				$result = pmb_mysql_query($query);
				$data_expl = pmb_mysql_fetch_array($result);
				$this->nb_prolongation = $cpt_prolongation = $data_expl['cpt_prolongation'];
				$pret_date =  $data_expl['pret_date'];
				$date_retour= $data_expl['pret_retour'];
				if ($data_expl['short_loan_flag']) {
					$prolongation = FALSE;
					$this->no_prolong_explanation = $msg['empr_no_prolongation_short_loan_flag'];
				}
				$cpt_prolongation++;

				$duree_prolongation=$opac_pret_duree_prolongation;
				$today=pmb_sql_value("SELECT CURRENT_DATE()");
				if ($pmb_pret_restriction_prolongation==0) {
					// Aucune limitation des prolongations
					$prolongation=true;
					$duree_prolongation=$opac_pret_duree_prolongation;
				} else if ($pmb_pret_restriction_prolongation>0) {
					$this->pret_nombre_prolongation=$pmb_pret_nombre_prolongation;
					if(($pmb_pret_restriction_prolongation==1) && ($cpt_prolongation>$this->pret_nombre_prolongation)) {
						// Limitation simple de la prolongation
						$prolongation=FALSE;
						$this->no_prolong_explanation = $msg['empr_no_prolongation_limit'];
					} else if($pmb_pret_restriction_prolongation==2) {
						// Limitation du pret par les quotas
						//Initialisation des quotas pour nombre de prolongations
						$qt = new quota("PROLONG_NMBR_QUOTA");
						//Tableau de passage des param�tres
						$struct=array();
						$struct["READER"] = $this->id_empr;
						$struct["EXPL"] = $this->id_expl;
						$struct["NOTI"] = exemplaire::get_expl_notice_from_id($this->id_expl);
						$struct["BULL"] = exemplaire::get_expl_bulletin_from_id($this->id_expl);
						$this->pret_nombre_prolongation=$qt -> get_quota_value($struct);

						if($cpt_prolongation>$this->pret_nombre_prolongation){
							$prolongation=FALSE;
							$this->no_prolong_explanation = $msg['empr_no_prolongation_limit'];
						}

						//Initialisation des quotas la dur�e de prolongations
						$qt = new quota("PROLONG_TIME_QUOTA");
						$struct=array();
						$struct["READER"] = $this->id_empr;
						$struct["EXPL"] = $this->id_expl;
						$struct["NOTI"] = exemplaire::get_expl_notice_from_id($this->id_expl);
						$struct["BULL"] = exemplaire::get_expl_bulletin_from_id($this->id_expl);
						$duree_prolongation=$qt -> get_quota_value($struct);
					} // fin if gestion par quotas
				} // fin else if pmb_pret_restriction_prolongation>0

				$empr_date_expiration=pmb_sql_value("SELECT empr_date_expiration FROM empr WHERE id_empr=".$this->id_empr);

				if($pmb_pret_date_retour_adhesion_depassee) {
					$this->date_prolongation=pmb_sql_value("SELECT DATE_ADD('$date_retour', INTERVAL $duree_prolongation DAY)");
				} else {
					if ($empr_date_expiration < $today) {
						$prolongation=FALSE;
						$this->no_prolong_explanation = $msg['empr_no_prolongation_adhesion_depassee'];
					}
					$this->date_prolongation=pmb_sql_value("SELECT if('".$empr_date_expiration."'>DATE_ADD('".$date_retour."', INTERVAL '$duree_prolongation' DAY),DATE_ADD('".$date_retour."', INTERVAL '$duree_prolongation' DAY),'".$empr_date_expiration."')");
				}
				if ((!$pmb_pret_date_retour_adhesion_depassee) && ($prolongation==TRUE)) {
					if ($this->date_prolongation<$date_retour) {
						$prolongation=FALSE;
						$this->no_prolong_explanation = $msg['empr_no_prolongation_retour_ahesion_depassee'];
					}
				}
				if ($prolongation==TRUE) {
					$diff=pmb_sql_value("SELECT DATEDIFF('$date_retour','$today')");
					if($diff<-$duree_prolongation || $diff>$duree_prolongation) {
						$prolongation=FALSE;
						$date_deb_prolongation = pmb_sql_value("SELECT DATE_ADD('$date_retour', INTERVAL -$duree_prolongation DAY)");
						$date_fin_prolongation = pmb_sql_value("SELECT DATE_ADD('$date_retour', INTERVAL $duree_prolongation DAY)");
						$this->no_prolong_explanation = sprintf($msg['empr_prolongation_not_yet_dispo'],formatdate($date_deb_prolongation),formatdate($date_fin_prolongation));
					}
				}

				$req_date_calendrier = "select date_ouverture from ouvertures where ouvert=1 and num_location='".$data_expl['expl_location']."' and DATEDIFF(date_ouverture,'".$this->date_prolongation."')>=0 order by date_ouverture asc limit 1";
				$res_date_calendrier = pmb_mysql_query($req_date_calendrier);

				if (pmb_mysql_num_rows($res_date_calendrier)) {
					$this->date_prolongation=pmb_mysql_result($res_date_calendrier,0,0);
				}

				// Verif s'il y a des r�sa et plus d'exemplaire dispo
				if ($prolongation) {
					if ($this->get_exemplaire()->id_notice) {
						$this->get_exemplaire()->id_bulletin = 0;
					} else {
						$this->get_exemplaire()->id_notice = 0;
					}

					// chercher le premier (par ordre de rang, donc de date de d�but de r�sa, non valid�
					$rqt = 	"SELECT count(1) FROM resa
						WHERE resa_idnotice='".$this->get_exemplaire()->id_notice."' AND resa_idbulletin='".$this->get_exemplaire()->id_bulletin."'
						AND resa_cb='' AND resa_date_fin='0000-00-00' ";

					$res= pmb_mysql_query($rqt);
					$nbresa = pmb_mysql_result($res, 0, 0);
					if ($nbresa) {
						$rqt="SELECT count(1) FROM exemplaires, docs_statut WHERE expl_statut=idstatut and pret_flag=1 and statut_visible_opac=1 AND expl_notice=".$this->get_exemplaire()->id_notice." AND expl_bulletin=".$this->get_exemplaire()->id_bulletin." ";
						if ($pmb_location_reservation) {
							$rqt.=" and expl_location in (select resa_loc from resa_loc where resa_emprloc=".$this->get_emprunteur()->location.") ";
						}
						$res= pmb_mysql_query($rqt);
						$nbexpl = pmb_mysql_result($res, 0, 0);
						$rqt="SELECT count(1) FROM pret,exemplaires WHERE pret_idexpl=expl_id AND expl_notice=".$this->get_exemplaire()->id_notice." AND expl_bulletin=".$this->get_exemplaire()->id_bulletin." ";
						if ($pmb_location_reservation) {
							$rqt.=" and expl_location in (select resa_loc from resa_loc where resa_emprloc=".$this->get_emprunteur()->location.") ";
						}
						$res= pmb_mysql_query($rqt);
						$nbexpl_en_pret = pmb_mysql_result($res, 0, 0);
						if (($nbexpl-$nbexpl_en_pret) < $nbresa) {
							$prolongation=false;
							$this->no_prolong_explanation = $msg['empr_no_prolongation_resa'];
						}
					}
				}


				//Blocage des prolongations si relance sur pret, selon param�tre
				if ($opac_pret_prolongation_blocage) {
					if ($data_expl['niveau_relance']!='0') {
						$prolongation=false;
						$this->no_prolong_explanation = $msg['empr_no_prolongation_relance'];
					}
				}

				// Proposer le bouton prolongation
				if ($prolongation==TRUE) {
					// Mettre au format affichable
					$rqt_date = "select date_format('".$this->date_prolongation."', '".$msg["format_date_sql"]."') as aff_date_prolongation ";
					$resultatdate = pmb_mysql_query($rqt_date);
					$res = pmb_mysql_fetch_object($resultatdate) ;
					$this->aff_date_prolongation= $res->aff_date_prolongation;
					// Bouton de prolongation
					if (pmb_sql_value("SELECT DATEDIFF('$date_retour','".$this->date_prolongation."')") == 0) {
						$prolongation=false;
						$this->no_prolong_explanation = $msg['empr_no_prolongation_date_prolongation'];
					}
				}
				$this->is_extendable = $prolongation;
			} else { // fin if prolongeable
				$this->is_extendable = false;
			}
		}
		return $this->is_extendable;
	}

	// ---------------------------------------------------------------
	//		annulation() : annulation violente d'un pr�t
	// ---------------------------------------------------------------
	public function annulation() {
		$query = "delete from pret where ";
		$query .= "pret_idexpl = '".$this->id_expl."' ";
		pmb_mysql_query($query) or die("can't delete from pret ".$query."<br />".pmb_mysql_error());
		return 0;
	}

	// ---------------------------------------------------------------
	//		prolongation() : prolongation d'un pr�t
	// ---------------------------------------------------------------
	public function prolongation($nouvelle_date) {
	    global $msg;
	    global $opac_pret_duree_prolongation, $pmb_pret_restriction_prolongation, $pmb_pret_nombre_prolongation;
	    global $pmb_pret_date_retour_adhesion_depassee;
	    global $pmb_utiliser_calendrier;

	    $prolongation = TRUE;

	    //on recupere les informations du pret
	    $query = "select cpt_prolongation, pret_date, pret_retour, expl_location from pret, exemplaires";
	    $query .= " where pret_idexpl=expl_id";
	    $query .= " and pret_idexpl=".$this->id_expl." limit 1";
	    $result = pmb_mysql_query($query);
	    $data = pmb_mysql_fetch_array($result);
	    $cpt_prolongation = $data['cpt_prolongation'];
	    $pret_date =  $data['pret_date'];
	    $date_retour = $data['pret_retour'];
	    $expl_location = $data['expl_location'];

	    $duree_prolongation = $opac_pret_duree_prolongation;

	    // Limitation simple du pret
	    if ($pmb_pret_restriction_prolongation==1) {

	        $this->pret_nombre_prolongation = $pmb_pret_nombre_prolongation;

	    } elseif($pmb_pret_restriction_prolongation==2) {

	        // Limitation du pret par les quotas
	        //Initialisation des quotas pour nombre de prolongations
	        $qt = new quota("PROLONG_NMBR_QUOTA");
	        //Tableau de passage des param�tres
	        $struct=array();
	        $struct["READER"] = $this->id_empr;
	        $struct["EXPL"] = $this->id_expl;
	        $struct["NOTI"] = exemplaire::get_expl_notice_from_id($this->id_expl);
	        $struct["BULL"] = exemplaire::get_expl_bulletin_from_id($this->id_expl);
	        $this->pret_nombre_prolongation = $qt -> get_quota_value($struct);

	        //Initialisation des quotas la dur�e de prolongations
	        $qt = new quota("PROLONG_TIME_QUOTA");
	        $struct=array();
	        $struct["READER"] = $this->id_empr;
	        $struct["EXPL"] = $this->id_expl;
	        $struct["NOTI"] = exemplaire::get_expl_notice_from_id($this->id_expl);
	        $struct["BULL"] = exemplaire::get_expl_bulletin_from_id($this->id_expl);
	        $duree_prolongation = $qt -> get_quota_value($struct);

	    }

	    $today = pmb_sql_value("SELECT CURRENT_DATE()");
	    $diff = pmb_sql_value("SELECT DATEDIFF('$date_retour', '$today')");

	    if ($diff < -$duree_prolongation || $diff > $duree_prolongation) {
	        $prolongation = FALSE;
	        echo $msg["loan_extend_false"] . "<br />";
	    }
	    $empr_date_expiration = pmb_sql_value("SELECT empr_date_expiration FROM empr WHERE id_empr=".$this->id_empr);

	    if ($pmb_pret_date_retour_adhesion_depassee) {
	        $this->date_prolongation = pmb_sql_value("SELECT DATE_ADD('$date_retour', INTERVAL $duree_prolongation DAY)");
	    } else {
	        if ($empr_date_expiration < $today) {
	            $prolongation = FALSE;
	            echo $msg['empr_no_prolongation_adhesion_depassee'] . "<br />";
	        }
	        $this->date_prolongation = pmb_sql_value("SELECT if('" . $empr_date_expiration."'>DATE_ADD('" . $date_retour . "', INTERVAL '$duree_prolongation' DAY),DATE_ADD('" . $date_retour . "', INTERVAL '$duree_prolongation' DAY),'" . $empr_date_expiration . "')");
	    }
	    if ((!$pmb_pret_date_retour_adhesion_depassee) && ($prolongation==TRUE)) {
	        if ($this->date_prolongation < $date_retour) {
	            $prolongation = FALSE;
	            echo $msg['empr_no_prolongation_retour_ahesion_depassee'] . "<br />";
	        }
	    }

	    if ($prolongation == TRUE) {
	        $cpt_prolongation++;

	        if ($pmb_utiliser_calendrier) {
	            $req_date_calendrier = "select date_ouverture from ouvertures where ouvert=1 and num_location='".$expl_location."' and DATEDIFF(date_ouverture,'".$this->date_prolongation."')>=0 order by date_ouverture asc limit 1";
	            $res_date_calendrier = pmb_mysql_query($req_date_calendrier);

	            if (pmb_mysql_num_rows($res_date_calendrier) > 0) {
	                $this->date_prolongation = pmb_mysql_result($res_date_calendrier,0,0);
	            }
	        }
	        // Memorisation de la nouvelle date de prolongation
	        $query = "update pret set cpt_prolongation='" . $cpt_prolongation . "', pret_retour='" . $this->date_prolongation . "', niveau_relance = 0, date_relance = '0000-00-00', printed=0 where pret_idexpl=" . $this->id_expl;
	        $result = pmb_mysql_query($query);

	        // Memorisation de la nouvelle date de prolongation dans la table d'archive
	        $res_arc=pmb_mysql_query("select pret_arc_id from pret where pret_idexpl=".$this->id_expl."");
	        if($res_arc && pmb_mysql_num_rows($res_arc)){
	            $query = "update pret_archive set arc_cpt_prolongation='".$cpt_prolongation."', arc_fin='".$this->date_prolongation."' where arc_id = ".pmb_mysql_result($res_arc,0,0);
	            pmb_mysql_query($query);
	        }
	    }
	    return $prolongation;
	}

	public function get_exemplaire() {
		if(!isset($this->exemplaire)) {
			$this->exemplaire = new exemplaire($this->cb_expl, $this->id_expl);
		}
		return $this->exemplaire;
	}

	public function get_emprunteur() {
	    if(!isset($this->emprunteur)) {
	        $this->emprunteur = new emprunteur($this->id_empr);
	    }
	    return $this->emprunteur;
	}

	public static function get_display_info($title='', $content='') {
		static::add_alert_sound_list('information');
		return "
			<hr />
			<div class='row'>
				<div class='colonne10'>
					<img src='".get_url_icon('info.png')."' />
				</div>
				<div class='colonne-suite'>
					".($title ? $title." :" : "")."
					<span class='erreur'>".$content."</span>
				</div>
			</div><br />";
	}

	public static function get_display_error($title='', $content='', $show_cancel=0, $show_loan=0, $suffix_link_loan='') {
		global $msg, $charset;
		global $id_empr;

		if(!$title && !$content) {
		    return "<hr />
			<div class='row'>
			<div class='colonne10'></div>
			<div class='colonne-suite'><span class='erreur'></span></div>
			</div><br />";
		}
		static::add_alert_sound_list('critique');
		$display = "
			<hr />
			<div class='row'>
				<div class='colonne10'>
					<img src='".get_url_icon('error.png')."' />
				</div>
				<div class='colonne-suite'>
					".($title ? $title." :" : "")."
					<span class='erreur'>".$content."</span>";
		if($show_cancel) {
			$display .= "<input type='button' class='bouton' value='".htmlentities($msg[76], ENT_QUOTES, $charset)."' onClick=\"document.location='./circ.php?categ=pret&id_empr=$id_empr'\" />";
		}
		if($show_loan) {
			$display .= "&nbsp;<input type='button' class='bouton' value='".htmlentities($msg[389], ENT_QUOTES, $charset)."' onClick=\"document.location='./circ.php?categ=pret&id_empr=".$id_empr.$suffix_link_loan."'\" />";
		}
		$display .= "</div>
			</div><br />";
		return $display;
	}

	public static function get_display_custom_fields($id_empr,$id_expl) {
		$p_perso=new pret_parametres_perso("pret");
		if(!$p_perso->no_special_fields) {
			$query_custom = "select pret_arc_id from pret
				where pret_idempr='".$id_empr."' and pret_idexpl='".$id_expl."'";
			$result_custom = pmb_mysql_query($query_custom);
			if ($result_custom && pmb_mysql_result($result_custom,0,0)) {
				$pret_arc_id = pmb_mysql_result($result_custom,0,0);
				$perso_ = $p_perso->show_fields($pret_arc_id);
				$perso="";
				if (count($perso_["FIELDS"])) {
					for ($i=0; $i<count($perso_["FIELDS"]); $i++) {
						$perso .= "<div class='row'>".$perso_["FIELDS"][$i]["TITRE"];
						$perso .= $perso_["FIELDS"][$i]["AFF"]."</div>";
					}
					$perso."<br />";
				}
				return $perso;
			}
		}
	}

	public static function get_display_antivol($id_expl) {
		global $pmb_antivol;

		$script_magnetique="
		<script language='javascript' >
		var requete = null;
		function creerRequette(){
			if(window.XMLHttpRequest) // Firefox
				requete = new XMLHttpRequest();
			else if(window.ActiveXObject) // Internet Explorer
		  		requete = new ActiveXObject('Microsoft.XMLHTTP');
			else { // XMLHttpRequest non support� par le navigateur
		   		alert('Votre navigateur ne supporte pas les objets XMLHTTPRequest...');
		    	return;
			}
		}
		function magnetise(commande){
			creerRequette();
			if(typeof netscape !== 'undefined') {
				if(netscape.security.PrivilegeManager)netscape.security.PrivilegeManager.enablePrivilege('UniversalBrowserRead');
			}
			requete.open('GET', 'http://localhost:30000/?send_value='+commande+'&command=Send', false);
			requete.send(null);
			if(requete.readyState != 4) alert('Requ�te antivol non effectu�e !');
		}";

		if($pmb_antivol>0) {
			$rqt = "SELECT type_antivol FROM exemplaires WHERE expl_id='".$id_expl."' ";
			$result = pmb_mysql_query($rqt);
			$expl = pmb_mysql_fetch_object($result);
			$type_antivol =$expl->type_antivol;
			if($type_antivol ==1)// c'est un support non magn�tique (livre, revue...)
				return "$script_magnetique"."magnetise('DDD');</script>";
			if($type_antivol ==2)//c'est un support magn�tique (cassette)
				return "$script_magnetique"."magnetise('SSS');</script>";
		}
		return "";
	}

	public static function add_alert_sound_list($sound) {
		global $pmb_play_pret_sound, $alert_sound_list;

		if($pmb_play_pret_sound) {
			$alert_sound_list[] = $sound;
		}
	}

	public static function extendLoan($id_empr, $expl_id, $date_retour){
        global $msg;

	    $query = "SELECT cpt_prolongation FROM pret WHERE pret_idempr = $id_empr AND pret_idexpl = $expl_id";
	    $r = pmb_mysql_query($query);
	    if (!pmb_mysql_num_rows($r)){
	        return ["status"=>false, "message"=>$msg["pnb_extend_loan_fail"], "infos"=>"infos"];
	    }

	    $result = pmb_mysql_fetch_array($r);
	    $new_cpt_prolongation = intval($result['cpt_prolongation']) + 1;

	    $query = "UPDATE pret SET pret_retour = '$date_retour', cpt_prolongation = $new_cpt_prolongation WHERE pret_idempr = $id_empr AND pret_idexpl = $expl_id";
	    pmb_mysql_query($query);

	    return true;
	}

	public static function is_last_late($id_empr, $expl_id) {
		$id_empr = intval($id_empr);
		$is_last_loan_late = false;
		$loans_late = emprunteur::get_loans_late($id_empr);
		if(!empty($loans_late[$expl_id])) {
			unset($loans_late[$expl_id]);
		}
		if(empty($loans_late)) {
			$is_last_loan_late = true;
		}
		return $is_last_loan_late;
	}

	public static function update_blocage($id_empr, $id_expl, $ndays, $loc_calendar = 0) {
		global $msg;
		global $pmb_blocage_max, $selfservice_retour_blocage_msg;

		$id_empr = intval($id_empr);
		$id_expl = intval($id_expl);
		$ndays = intval($ndays);

		$informations = array();
		//Le lecteur est-il d�j� bloqu� ?
		$date_fin_blocage_empr = pmb_mysql_result(pmb_mysql_query("select date_fin_blocage from empr where id_empr='".$id_empr."'"),0,0);
		//Calcul de la date de fin
		if ($pmb_blocage_max!=-1) {
			$date_fin=calendar::add_days(date("d"),date("m"),date("Y"),$ndays,$loc_calendar);
		} else {
			$date_fin=calendar::add_days(date("d"),date("m"),date("Y"),0,$loc_calendar);
		}
		if ($pmb_blocage_max==-1 && static::is_last_late($id_empr, $id_expl)) {
			if($date_fin_blocage_empr != '0000-00-00') {
				//on l�ve le blocage
				//Mise � jour
				pmb_mysql_query("update empr set date_fin_blocage='0000-00-00' where id_empr='".$id_empr."'");
				$informations['message'] = $msg["blocage_retard_pret_is_up"];
				static::add_alert_sound_list('information');
			} else {
				//on ne bloque pas car il s'agit du dernier retard
				$informations['message'] = $msg["blocage_retard_pret_last_late"];
				static::add_alert_sound_list('information');
			}
		} else {
			if ($date_fin > $date_fin_blocage_empr) {
				//Mise � jour
				pmb_mysql_query("update empr set date_fin_blocage='".$date_fin."' where id_empr='".$id_empr."'");
				$informations['message'] = sprintf($msg["blocage_retard_pret"],formatdate($date_fin));
				$informations['custom_message'] = sprintf($selfservice_retour_blocage_msg,formatdate($date_fin));
				static::add_alert_sound_list('critique');
			} else {
				$informations['message'] = sprintf($msg["blocage_already_retard_pret"],formatdate($date_fin_blocage_empr));
				$informations['custom_message'] = sprintf($selfservice_retour_blocage_msg,formatdate($date_fin_blocage_empr));
				static::add_alert_sound_list('critique');
			}
		}
		return $informations;
	}

	public function get_id() {
	    return $this->id_expl;
	}
} # fin de d�finition de la classe pret
