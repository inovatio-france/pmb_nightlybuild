<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cart.class.php,v 1.4 2024/07/22 14:57:40 pmallambic Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cart {

    protected $actions = [];
    
	public function __construct() {
	}

	protected function get_type_action($name) {
	    global $opac_rgaa_active;
	    
	    if($opac_rgaa_active) {
	        if(!in_array($name, array('show_cart_checked_all', 'show_cart_more_actions', 'show_cart_reserve', 'show_cart_print'))) {
	            return 'link';
	        }
	    }
	    return 'button';
	}
	    
	protected function add_href_action($name, $label, $title, $link) {
	    $this->actions[$name] = array( 
	           'label' => $label,
	           'title' => $title,
	           'href' => $link,
	           'type' => $this->get_type_action($name)
	    );
	}
	
	protected function add_onclick_action($name, $label, $title, $event) {
	    $this->actions[$name] = array(
	        'label' => $label,
	        'title' => $title,
	        'onclick' => $event,
	        'type' => $this->get_type_action($name)
	    );
	}
	
	protected function _init_actions($cart_) {
	    global $msg;
	    global $opac_cart_more_actions_activate;
	    global $opac_allow_download_docnums;
	    global $opac_shared_lists, $allow_liste_lecture, $id_empr;
	    global $opac_show_suggest, $opac_allow_multiple_sugg, $allow_sugg;
	    global $opac_resa, $opac_resa_planning, $opac_resa_cart, $opac_resa_popup;
	    global $opac_scan_request_activate, $allow_scan_request;
	    global $opac_export_allow;
	    
	    $this->add_href_action('show_cart_empty', $msg['show_cart_empty'], $msg['show_cart_empty_title'], './index.php?lvl=show_cart&raz_cart=1');
	    $this->add_onclick_action('show_cart_del_checked', $msg['show_cart_del_checked'], $msg['show_cart_del_checked_title'], "document.cart_form.submit();");
	    $this->add_onclick_action('show_cart_print', $msg['show_cart_print'], $msg['show_cart_print_title'], "w=window.open('print.php?lvl=cart','print_window','width=500, height=750,scrollbars=yes,resizable=1'); w.focus();");
	    $this->add_onclick_action('show_cart_checked_all', $msg['show_cart_check_all'], $msg['show_cart_check_all'], "check_uncheck_all_cart();");
	    
	    if ($opac_cart_more_actions_activate) {
	        $this->add_onclick_action('show_cart_more_actions', $msg['show_cart_more_actions'], $msg['show_cart_more_actions'], "show_more_actions();");
	    }
	    if (!empty($opac_allow_download_docnums)) {
	        $this->add_onclick_action('docnum_download_caddie', $msg['docnum_download_caddie'], $msg['docnum_download_caddie'], "download_docnum();");
	        $this->add_onclick_action('docnum_download_checked', $msg['docnum_download_checked'], $msg['docnum_download_checked'], "download_docnum_notice_checked();");
	    }
	    if (!empty($opac_shared_lists) && !empty($allow_liste_lecture) && !empty($id_empr)) {
	        $this->add_href_action('list_lecture_transform_caddie', $msg['list_lecture_transform_caddie'], $msg['list_lecture_transform_caddie_title'], './index.php?lvl=show_list&sub=transform_caddie');
	        $this->add_onclick_action('list_lecture_transform_checked', $msg['list_lecture_transform_checked'], $msg['list_lecture_transform_checked_title'], "document.cart_form.action='./index.php?lvl=show_list&sub=transform_check';if(confirm_transform()) document.cart_form.submit(); else return false;");
	    }
	    if (!empty($opac_show_suggest) && !empty($opac_allow_multiple_sugg) && !empty($allow_sugg) && !empty($id_empr)) {
	        $this->add_onclick_action('transform_caddie_to_multisugg', $msg['transform_caddie_to_multisugg'], $msg['transform_caddie_to_multisugg_title'], "document.getElementById('div_src_sugg').style.display='';");
	        $this->add_onclick_action('transform_caddie_notice_to_multisugg', $msg['transform_caddie_notice_to_multisugg'], $msg['transform_caddie_to_multisugg_title'], "if(notice_checked()){ document.getElementById('div_src_sugg').style.display='';} else return false;");
	    }
	    //resas
	    if (!empty($opac_resa) && $opac_resa_planning != 1 && !empty($id_empr) && !empty($opac_resa_cart)) {
	        if (!empty($opac_resa_popup)) {
	            $this->add_onclick_action('show_cart_reserve', $msg['show_cart_reserve'], $msg['show_cart_reserve_title'], "w=window.open('./do_resa.php?lvl=resa_cart&sub=resa_cart','doresa','scrollbars=yes,width=900,height=300,menubar=0,resizable=yes'); w.focus(); return false;");
	            $this->add_onclick_action('show_cart_reserve_checked', $msg['show_cart_reserve_checked'], $msg['show_cart_reserve_checked_title'], "resa_cart_checked(true);");
	        } else {
	            $this->add_href_action('show_cart_reserve', $msg['show_cart_reserve'], $msg['show_cart_reserve_title'], "./do_resa.php?lvl=resa_cart&sub=resa_cart");
	            $this->add_onclick_action('show_cart_reserve_checked', $msg['show_cart_reserve_checked'], $msg['show_cart_reserve_checked_title'], "resa_cart_checked();");
            }
            //resas planifiees
	    } elseif(!empty($opac_resa) && $opac_resa_planning == '1' && !empty($id_empr) && !empty($opac_resa_cart)) {
	        if ($opac_resa_popup) {
	            $this->add_onclick_action('show_cart_reserve', $msg['show_cart_reserve'], $msg['show_cart_reserve_title'], "w=window.open('./do_resa.php?lvl=resa_cart&sub=resa_cart','doresa','scrollbars=yes,width=900,height=300,menubar=0,resizable=yes'); w.focus(); return false;");
	            $this->add_onclick_action('show_cart_reserve_checked', $msg['show_cart_reserve_checked'], $msg['show_cart_reserve_checked_title'], "resa_cart_checked(true, true);");
	        } else {
	            $this->add_href_action('show_cart_reserve', $msg['show_cart_reserve'], $msg['show_cart_reserve_title'], "./do_resa.php?lvl=resa_cart&sub=resa_planning_cart");
	            $this->add_onclick_action('show_cart_reserve_checked', $msg['show_cart_reserve_checked'], $msg['show_cart_reserve_checked_title'], "resa_cart_checked(false, true);");
	        }
	    }
	    // Demande de numérisation
	    if (!empty($opac_scan_request_activate) && !empty($allow_scan_request) && !empty($id_empr)) {
	        $this->add_href_action('scan_request_from_caddie', $msg['scan_request_from_caddie'], $msg['scan_request_from_caddie_title'], "./empr.php?tab=scan_requests&lvl=scan_request&sub=edit&from=caddie");
	        $this->add_onclick_action('scan_request_from_checked', $msg['scan_request_from_checked'], $msg['scan_request_from_checked_title'], "document.cart_form.action='./empr.php?tab=scan_requests&lvl=scan_request&sub=edit&from=checked';if(confirm_transform()) document.cart_form.submit(); else return false;");
	    }
	    if ($opac_export_allow == '1' || ($opac_export_allow == '2' && $_SESSION['user_code'])) {
	        $js_export_partiel = $this->get_js_export_partiel($cart_);
	        $this->add_onclick_action('show_cart_export', $msg['show_cart_export_ok'], $msg['show_cart_export_ok'], "$js_export_partiel if(getNoticeSelected()){ document.location='./export.php?action=export&typeexport='+document.export_form.typeexport.options[top.document.export_form.typeexport.selectedIndex].value+(typeof getNoticeSelected() != 'boolean' ? getNoticeSelected() : '');}}");
	    }
	}
	
	protected function get_display_multisugg_selector() {
	    global $msg, $charset;
	    
	    //Affichage du selecteur de source
	    $req = 'select * from suggestions_source order by libelle_source';
	    $res = pmb_mysql_query($req);
	    $option = '<option value="0" selected="selected">'.htmlentities($msg['empr_sugg_no_src'], ENT_QUOTES, $charset).'</option>';
	    while ($src = pmb_mysql_fetch_object($res)) {
	        $option .= "<option value='$src->id_source'>".htmlentities($src->libelle_source, ENT_QUOTES, $charset)."</option>";
	    }
	    return "<select id='sug_src' name='sug_src'>$option</select>";
	}
	
	protected function get_display_multisugg() {
	    global $msg;
	    
	    $display = '<div class="row" id="div_src_sugg" style="display:none" >';
	    $display .= '<label class="etiquette">'.$msg['empr_sugg_src'].': </label>';
	    $display .= $this->get_display_multisugg_selector();
	    $display .= "<input type='button' class='bouton' value=\"".$msg[11]."\" onClick=\"document.cart_form.action='./empr.php?lvl=transform_to_sugg&act=transform_caddie&sug_src='+document.getElementById('sug_src').value;document.cart_form.submit();\" />";
	    $display .= '</div>';
	    return $display;
	}
	
	public function has_display_breakline($name) {
	    switch ($name) {
	        case 'docnum_download_caddie':
	        case 'list_lecture_transform_caddie':
	        case 'transform_caddie_to_multisugg':
	        case 'show_cart_reserve':
	        case 'scan_request_from_caddie':
	            return true;
	        default:
	            return false;
        }
	}
	
	protected function get_html_action($name, $action) {
	    global $charset;
	    
	    if ($action['type'] == 'link') {
	        if(!empty($action['href'])) {
	            return "<a id='".$name."' class='cart_action_link' title='".htmlentities($action['title'], ENT_QUOTES, $charset)."' href='".$action['href']."'>".htmlentities($action['label'], ENT_QUOTES, $charset)."</a>";
	        } else {
	            return "<a id='".$name."' class='cart_action_link' title='".htmlentities($action['title'], ENT_QUOTES, $charset)."' href='#' onClick=\"".$action['onclick']."\">".htmlentities($action['label'], ENT_QUOTES, $charset)."</a>";
	        }
	    } else {
	        if(!empty($action['href'])) {
	            return "<input type='button' id='".$name."' class='bouton cart_action_input' value='".htmlentities($action['label'], ENT_QUOTES, $charset)."' title='".htmlentities($action['title'], ENT_QUOTES, $charset)."' onClick=\"document.location='".$action['href']."'\" />";
	        } else {
	            return "<input type='button' id='".$name."' class='bouton cart_action_input' value='".htmlentities($action['label'], ENT_QUOTES, $charset)."' title='".htmlentities($action['title'], ENT_QUOTES, $charset)."' onClick=\"".$action['onclick']."\" />";
	        }
	    }
	}
	
	protected function get_html_actions($cart_) {
	    global $msg, $charset;
	    global $opac_rgaa_active;
	    
	    $html_actions = [];
	    foreach ($this->actions as $name=>$action) {
	        $html_action = '';
			if(!$opac_rgaa_active){
				if ($this->has_display_breakline($name)) {
					$html_action .= "<br /><br />";
				} elseif (array_key_first($this->actions) != $name) {
					$html_action .= "<span class=\"espaceCartAction\">&nbsp;</span>";
				}
			}
	        switch ($name) {
	            case 'show_cart_more_actions':
					if($opac_rgaa_active){
						$html_action .= "<li id='list_action_".$name."' class='cart_action_item'>";
					}
	                $html_action .= $this->get_html_action($name, $action);
					if($opac_rgaa_active){
						$html_action .= "</li>";
						$html_action .= "<li id='show_more_actions' style='display: none;' class='cart_action_list_item'><ul id='cart_more_actions_list'>";
					}else{
						$html_action .= "<div id='show_more_actions' style='display: none;' class='cart_action_list_item'>";
					}
	                break;
	            case 'docnum_download_checked':
					if($opac_rgaa_active){
						$html_action .= "<li id='list_action_".$name."' class='cart_action_item'>";
					}
	                $html_action .= $this->get_html_action($name, $action);
	                $html_action .= "<div id='http_response'></div>";
					if($opac_rgaa_active){
						$html_action .= "</li>";
					}
	                break;
	            case 'transform_caddie_notice_to_multisugg':
					if($opac_rgaa_active){
						$html_action .= "<li id='list_action_".$name."' class='cart_action_item'>";
					}
	                $html_action .= $this->get_html_action($name, $action);
	                $html_action .= $this->get_display_multisugg();
					if($opac_rgaa_active){
						$html_action .= "</li>";
					}
	                break;
	            case 'show_cart_export':
					if($opac_rgaa_active){
						$html_action .= "<li id='list_action_".$name."' class='cart_action_item'>";
					}
	                $html_action .= "<form name='export_form'><br />";
	                
	                if($opac_rgaa_active) {
	                    $html_action .= "
                        <fieldset>
                            <legend class='visually-hidden'>".htmlentities(sprintf($msg['show_cart_export'], ''), ENT_QUOTES, $charset)."</legend>
	                           <div class='cart_selector_export'>
	                               <label for='typeexport_selector'>".sprintf($msg['show_cart_export'], '')."</label>
	                               ".$this->get_display_exports_selector()."
                               </div>";
	                } else {
	                    $html_action .= sprintf($msg['show_cart_export'], '');
	                    $html_action .= $this->get_display_exports_selector();
	                    $html_action .= "<br />";
	                }
	                $html_action .= $this->get_display_exports_radio($cart_);
	                $html_action .= "<span class=\"espaceCartAction\">&nbsp;</span>";
	                $html_action .= $this->get_html_action($name, $action);
	                if($opac_rgaa_active) {
	                    $html_action .= "
                        </fieldset>";
	                }
	                $html_action .= '</form>';
					if($opac_rgaa_active){
						$html_action .= "</li>";
					}
	                break;
	            default:
					if($opac_rgaa_active){
						$html_action .= "<li id='list_action_".$name."' class='cart_action_item'>";
					}
	                $html_action .= $this->get_html_action($name, $action);
					if($opac_rgaa_active){
						$html_action .= "</li>";
					}
	                break;
	        }
	        $html_actions[$name] = $html_action;
	    }
	    return $html_actions;
	}
	
	public function get_display_actions($cart_) {
	    global $opac_cart_more_actions_activate;
	    global $opac_rgaa_active;


		$display = "";
		if($opac_rgaa_active){
			// ouverture de la premiere liste
			$display .= "<ul id='cart_action_list'>";
		}

	    $this->_init_actions($cart_);
	    $html_actions = $this->get_html_actions($cart_);
	    if(!empty($html_actions)) {
// 	        $display .= implode("<span class=\"espaceCartAction\">&nbsp;</span>", $html_actions);
	        $display .= implode("", $html_actions);
	    }
		if($opac_rgaa_active){
			// fermeture de la premiere liste
			$display .= '</ul>';
		}
	    if ($opac_cart_more_actions_activate) {
			if($opac_rgaa_active){
				// fermeture de la seconde liste
	        	$display .= '</ul></li>';
			}else{
				$display .= '</div>';
			}
	    }
	    return $display;
	}
	
	protected function get_js_export_partiel($cart_) {
	    global $msg;
	    
	    $nb_fiche = 0;
	    $nb_fiche_total = count($cart_);
	    
	    for ($z = 0; $z < $nb_fiche_total; $z++) {
	        $sql = "";
	        if (substr($cart_[$z], 0, 2) != "es") {
	            // Exclure de l'export (opac, panier) les fiches interdites de diffusion dans administration, Notices > Origines des notices NG72
	            $sql = "select 1 from origine_notice,notices where notice_id = '$cart_[$z]' and origine_catalogage = orinot_id and orinot_diffusion='1'";
	        } else {
	            $requete = "SELECT source_id FROM external_count WHERE rid=".addslashes(substr($cart_[$z], 2));
	            $myQuery = pmb_mysql_query($requete);
	            if(pmb_mysql_num_rows($myQuery)) {
	                $source_id = pmb_mysql_result($myQuery, 0, 0);
	                $sql = "select 1 from entrepot_source_$source_id where recid='".addslashes(substr($cart_[$z], 2))."' group by ufield,usubfield,field_order,subfield_order,value";
	            }
	        }
	        if($sql) {
	            $res = pmb_mysql_query($sql);
	            if (!empty(pmb_mysql_fetch_array($res))) {
	                $nb_fiche++;
	            }
	        }
	    }
	    if ($nb_fiche != $nb_fiche_total) {
	        $msg_export_partiel = str_replace ('!!nb_export!!', $nb_fiche, $msg['export_partiel']);
	        $msg_export_partiel = str_replace ('!!nb_total!!', $nb_fiche_total, $msg_export_partiel);
	        return "if (confirm('".addslashes($msg_export_partiel)."')) {";
	    } else {
	        return "if (true) {";
	    }
	}
	
	protected function get_display_exports_radio($cart_) {
	    global $msg, $charset;
	    
	    return "
        <div class='cart_radio_export_all'>
		    <input type='radio' name='radio_exp' id='radio_exp_all' value='0' checked />
		    <label for='radio_exp_all'>".htmlentities($msg['export_cart_all'], ENT_QUOTES, $charset)."</label>
		</div>
		<div class='cart_radio_export_sel'>
    		<input type='radio' name='radio_exp' id='radio_exp_sel' value='1' />
    		<label for='radio_exp_sel'>".htmlentities($msg['export_cart_selected'], ENT_QUOTES, $charset)."</label>
		</div>";
	}
	
	protected function get_display_exports_selector() {
	    $exp = start_export::get_exports();
	    $selector = "<select id='typeexport_selector' name='typeexport'>" ;
	    for ($i = 0; $i < count($exp); $i++) {
	        $selector .= "<option value='".$exp[$i]['ID']."'>".$exp[$i]['NAME']."</option>";
	    }
	    $selector .= "</select>" ;
	    return $selector;
	}
}
