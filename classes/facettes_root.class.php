<?php 
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facettes_root.class.php,v 1.42 2024/03/21 15:24:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/facette_search_compare.class.php");
require_once($class_path."/encoding_normalize.class.php");

abstract class facettes_root {
	/**
	 * Objets séparées par des virgules
	 * @var string
	 */
	public $objects_ids;
	
	/**
	 * Liste des facettes
	 * @var array
	 */
	public $facettes;
	
	/**
	 * Liste des facettes calculées
	 * @var array
	 */
	public $tab_facettes;
	
	/**
	 * Flag pour indiquer qu'au moins une des facettes sera affichée
	 * @var boolean
	 */
	public $exists_with_results = false;
	
	/**
	 * Mode d'affichage (extended/external)
	 * @var string
	 */
	public $mode = 'extended';
	
	/**
	 * Comparateur de notice activé (oui/non)
	 * @var string
	 */
	protected static $compare_notice_active;
	
	/**
	 * Instance
	 * @var facette_search_compare
	 */
	protected $facette_search_compare;
	
	/**
	 * Liste des facettes sélectionnées
	 * @var array
	 */
	protected $clicked;
	
	/**
	 * Liste des facettes non sélectionnées
	 * @var array
	 */
	protected $not_clicked;
	
	/**
	 * Liste des valeurs par facette hors limite
	 */
	protected $facette_plus;
	
	protected static $facet_type;
	
	/**
	 * Mode de recherche (simple / multi-criteres)
	 * @var string
	 */
	protected static $search_mode = 'extended_search';
	
	protected static $hidden_form_name = 'search_form';
	
	protected static $url_base;
	
	public function __construct($objects_ids = ''){
		$this->objects_ids = $objects_ids;
		$this->facette_existing();
		$this->nb_results_by_facette();
	}
	
	protected static function get_authorities_types() 
	{
	    return [
	        'authors', 'categories', 'publishers', 'collections', 'subcollections',
	        'series', 'titres_uniformes', 'indexint'
	    ];
	}
	
	protected static function get_facettes_sets() 
	{
	    global $PMBuserid;
	    
	    $facettes_sets = new facettes_sets(static::$facet_type);
	    return $facettes_sets->get_filtered_list($PMBuserid);
	}
	
	protected static function get_selected_num_facettes_set() {
	    if (!empty($_SESSION['facettes_sets'][static::$facet_type])) {
	        $facettes_set = new facettes_set($_SESSION['facettes_sets'][static::$facet_type]);
	        if($facettes_set->get_id()) {
	            if(static::$facet_type == $facettes_set->get_type() || (static::$facet_type == 'authorities' && in_array($facettes_set->get_type(), facettes_sets::get_authorities_types()))) {
	                return $_SESSION['facettes_sets'][static::$facet_type];
	            }
	        }
	    } 
        $objects = static::get_facettes_sets();
        if (!empty($objects)) {
            return $objects[0]->get_id();
        }
	}
	
	protected static function get_query_restricts($selected = 0) {
	    $selected = intval($selected);
	    $where = "WHERE facette_visible=1";
	    if (!empty(static::$facet_type)) {
	        if(static::$facet_type == 'authorities') {
	            $authorities_types = static::get_authorities_types();
	            $where .= " AND (facette_type IN ('".implode("','", $authorities_types)."') OR facette_type LIKE 'authperso%')";
	        } else {
	            $where .= " AND facette_type = '".addslashes(static::$facet_type)."'";
	        }
	        if($selected) {
	           $where .= " AND num_facettes_set = ".$selected;
	        }
	    }
	    return $where;
	}
	
	protected function get_query()
	{
	    $selected = static::get_selected_num_facettes_set();
	    $query = "SELECT * FROM ".static::$table_name." JOIN facettes_sets ON id_set = num_facettes_set";
	    $query .= " ".static::get_query_restricts($selected);
	    $query .= "  ORDER BY facette_order, facette_name";
	    return $query;
	}
	
	protected function facette_existing(){
		global $opac_view_filter_class;
		
        $this->facettes = [];
		$query = $this->get_query();
		$result = pmb_mysql_query($query);
		while($row = pmb_mysql_fetch_object($result)){
			if($opac_view_filter_class) {
                if (!$opac_view_filter_class->is_selected(static::$table_name, $row->id_facette+0)) {
                    continue;
                }
			}
			$this->facettes[] = [
				'id'=> intval($row->id_facette),
				'name'=> translation::get_translated_text($row->id_facette, 'facettes', 'facette_name',  $row->facette_name),
				'id_critere'=> intval($row->facette_critere),
			    'id_ss_critere'=> intval($row->facette_ss_critere),
		        'nb_result'=> intval($row->facette_nb_result),
	            'limit_plus'=> intval($row->facette_limit_plus),
                'type_sort'=> intval($row->facette_type_sort),
                'order_sort'=> intval($row->facette_order_sort),
				'datatype_sort'=>$row->facette_datatype_sort
			];
		}
	}
	
	public function nb_results_by_facette(){
		global $msg;
		
        $this->tab_facettes = [];
		if($this->objects_ids != "") {
			foreach ($this->facettes as $facette) {
			    $query = $this->get_query_by_facette($facette['id_critere'], $facette['id_ss_critere']);
				if ($facette['type_sort']==0) {
					$query .= " nb_result";
				} else {
					if ($facette['datatype_sort']== 'date') {
						$query .= " STR_TO_DATE(value,'".$msg['format_date']."')";
					} elseif ($facette['datatype_sort']== 'num') {
						$query .= " value*1";
					} else {
						$query .= " value";
					}
				}
				if($facette['order_sort']==0) {
					$query .= " asc";
				} else {
					$query .= " desc";
				}
				if($facette['nb_result']>0) {
					$query .= " LIMIT"." ".$facette['nb_result'];
				}
				$result = pmb_mysql_query($query);
				$j=0;
                $array_tmp = [];
                $array_value = [];
                $array_nb_result = [];
				if($result && pmb_mysql_num_rows($result)) {
					while($row = pmb_mysql_fetch_object($result)) {
						$array_tmp[$j] =  $row->value." "."(".($row->nb_result+0).")";
						$array_value[$j] = $row->value;
						$array_nb_result[$j] = ($row->nb_result+0);
						$j++;
					}
					$this->exists_with_results = true;
				}
                $this->tab_facettes[] = [
						'name' => $facette['name'],
						'facette' => $array_tmp,
						'code_champ' => $facette['id_critere'],
						'code_ss_champ' => $facette['id_ss_critere'],
						'value' => $array_value,
						'nb_result' => $array_nb_result,
                        'size_to_display' => $facette['limit_plus'],
                ];
			}
		}
	}
	
	protected static function get_link_not_clicked($name, $label, $code_champ, $code_ss_champ, $id, $nb_result) {
	    $datas = array($name, $label, $code_champ, $code_ss_champ, $id, $nb_result);
	    $link = "facettes_valid_facette(".encoding_normalize::json_encode($datas).");";
	    return $link;
	}
	
	public static function see_more($json_facette_plus){
		global $charset;
		
        $arrayRetour = [];
		for($j=0; $j<count($json_facette_plus['facette']); $j++) {
		    $facette_libelle = static::get_formatted_value($json_facette_plus['code_champ'],$json_facette_plus['code_ss_champ'], $json_facette_plus['value'][$j]);
			$facette_id = facette_search_compare::gen_compare_id($json_facette_plus['name'],$json_facette_plus['value'][$j],$json_facette_plus['code_champ'],$json_facette_plus['code_ss_champ'],$json_facette_plus['nb_result'][$j]);
            $facette_value = encoding_normalize::json_encode([$json_facette_plus['name'], $json_facette_plus['value'][$j], $json_facette_plus['code_champ'], $json_facette_plus['code_ss_champ'], $facette_id, $json_facette_plus['nb_result'][$j]]);
			if($facette_libelle) {
                $arrayRetour[]= [
    					'facette_libelle' => htmlentities($facette_libelle,ENT_QUOTES,$charset),
    					'facette_number' => htmlentities($json_facette_plus['nb_result'][$j],ENT_QUOTES,$charset),
    					'facette_id' => $facette_id,
    					'facette_value' => htmlentities($facette_value,ENT_QUOTES,$charset),
                    	'facette_link' => static::get_link_not_clicked($json_facette_plus['name'], $json_facette_plus['value'][$j], $json_facette_plus['code_champ'], $json_facette_plus['code_ss_champ'], $facette_id, $json_facette_plus['nb_result'][$j]),
                    	'facette_code_champ' => htmlentities($json_facette_plus['code_champ'], ENT_QUOTES, $charset),
                ];
			}
		}
		return encoding_normalize::json_encode($arrayRetour);
	}
	
	public static function destroy_dom_node() {
		if($_SESSION["cms_build_activate"]) {
			return "";
		} else {
			return "
				<script type='text/javascript'>
							require(['dojo/ready', 'dojo/dom-construct'], function(ready, domConstruct){
								ready(function(){
									domConstruct.destroy('facette');
								});
							});
				</script>";
		}
	}
	
	public static function get_nb_facettes() {
	    $query = "SELECT count(id_facette) FROM ".static::$table_name;
	    if(static::$table_name == 'facettes_external') {
	        $query .= " WHERE facette_visible_gestion=1";
	        if (!empty(static::$facet_type)) {
	            $query .= " AND facette_type = '".addslashes(static::$facet_type)."'";
	        }
	    } else {
	        $query .= " ".static::get_query_restricts();
	    }
		$result = pmb_mysql_query($query);
		return pmb_mysql_result($result, 0);
	}
	
	protected static function get_ajax_base_url() {
	    global $base_path;
	    
	    return $base_path."/ajax.php?module=ajax&categ=".static::$table_name."&search_mode=".static::$search_mode;
	}
	
	protected static function get_ajax_url() {
	    return static::get_ajax_base_url()."&sub=get_data&hidden_form_name=".static::$hidden_form_name."&facet_type=".static::$facet_type;
	}
	
	protected static function get_ajax_filtered_data_url() {
	    return static::get_ajax_base_url()."&sub=get_filtered_data&hidden_form_name=".static::$hidden_form_name."&facet_type=".static::$facet_type;
	}
	
	public static function call_ajax_facettes() {
		$ajax_facettes = "";
		if(static::get_nb_facettes()) {
			$ajax_facettes .= static::get_facette_wrapper();
			$ajax_facettes .="
				<div id='facette_wrapper'>
					<img src='".get_url_icon('patience.gif')."'/>
					<script type='text/javascript'>
						var req = new http_request();
						req.request(\"".static::get_ajax_url()."\",false,null,true,function(data){
							var response = JSON.parse(data);
							document.getElementById('facette_wrapper').innerHTML=response.display;
							if(!response.exists_with_results) {
                                if(facettes_get_mode() == 'filter') {

                                } else {
    								require(['dojo/ready', 'dojo/dom-construct'], function(ready, domConstruct){
    									ready(function(){
    										domConstruct.destroy('facettes_list');
    									});
    								});
                                }
							} else {
								document.getElementById('results_list').classList.add('has_facettes');
							}
						}, '', '', true);
					</script>
				</div>";
		}
		return $ajax_facettes;
	}
	
	public static function make_facette($objects_ids){
		$return = "";
		$class_name = static::class;
		$facettes = new $class_name($objects_ids);
		if($facettes->exists_with_results) {
			$return .= static::get_facette_wrapper();
			$return .= $facettes->create_ajax_table_facettes();
		} else {
			$return .= self::destroy_dom_node();
		}
		return $return;
	}

	public static function make_ajax_facette($objects_ids){
	    $class_name = static::class;
		$facettes = new $class_name($objects_ids);
		return array(
			'exists_with_results' => (isset($_SESSION["cms_build_activate"]) && $_SESSION["cms_build_activate"] ? true : $facettes->exists_with_results),
			'display' => $facettes->create_ajax_table_facettes()
		);
	}

	protected static function get_ajax_see_more_url() {
	    return static::get_ajax_base_url()."&sub=see_more";
	}
	
	protected static function get_ajax_filters_url($action='') {
	    return static::get_ajax_base_url()."&sub=filters&action=".$action."&facet_type=".static::$facet_type;
	}
	
	public static function get_facette_wrapper(){
	    global $base_path;
		$script ="
		<script src='".$base_path."/javascript/select.js' type='text/javascript'></script>
        <script type='text/javascript'> 
            var facettes_hidden_form_name = '".static::$hidden_form_name."';
            var facettes_ajax_see_more_url = '".static::get_ajax_see_more_url()."';
            var facettes_ajax_filtered_data_url = '".static::get_ajax_filtered_data_url()."';
            var facettes_ajax_filters_get_elements_url = '".static::get_ajax_filters_url('get_elements')."';
        </script>
        <script src='".$base_path."/javascript/facettes.js' type='text/javascript'></script>
		<script type='text/javascript'> 		
            ";
			if(static::get_compare_notice_active()){
				$compare_class_name = static::$compare_class_name;
				$script .= $compare_class_name::get_compare_wrapper();
			}
		$script.="</script>";
		return $script;	
	}
	
	public static function destroy_global_search_element($indice) {
	    global $search;
	    
	    $nb_search = count($search);
	    for($i=$indice; $i<=$nb_search; $i++) {
	        $op="op_".$i."_".$search[$i];
	        $field_="field_".$i."_".$search[$i];
	        $inter="inter_".$i."_".$search[$i];
	        $fieldvar="fieldvar_".$i."_".$search[$i];
	        global ${$op};
	        global ${$field_};
	        global ${$inter};
	        global ${$fieldvar};
	        if($i == $nb_search) {
	            unset($GLOBALS[$op]);
	            unset($GLOBALS[$field_]);
	            unset($GLOBALS[$inter]);
	            unset($GLOBALS[$fieldvar]);
	            unset($search[$i]);
	            array_pop($search);
	        } else {
	            //on décale
	            $n = $i+1;
	            $search[$i]=$search[$n];
	            $op="op_".$n."_".$search[$n];
	            $field_="field_".$n."_".$search[$n];
	            $inter="inter_".$n."_".$search[$n];
	            $fieldvar="fieldvar_".$n."_".$search[$n];
	            
	            global ${$op_next};
	            global ${$field_next};
	            global ${$inter_next};
	            global ${$fieldvar_next};
	            
	            ${$op}=${$op_next};
	            ${$field_}=${$field_next};
	            ${$inter}=${$inter_next};
	            ${$fieldvar}=${$fieldvar_next};
	        }
	    }
	}
	
	public static function checked_facette_search(){
		global $param_delete_facette;
	
		$session_values = static::get_session_values();
		if (!is_array($session_values)){
            $session_values = [];
		}
	
		//Suppression facette
		if($param_delete_facette!=""){
			//On évite le rafraichissement de la page
			static::delete_session_value($param_delete_facette);
		} else {
            $tmpArray = [];
			$check_facette = static::get_checked();
			foreach ($check_facette as $v) {
				$ajout=true;
				if (count($tmpArray)) {
					foreach ($tmpArray as $k2=>$v2) {
						if (($v2[2]==$v[2]) && ($v2[3]==$v[3])) {
							$tmpArray[$k2][1][] = $v[1];
							$ajout=false;
							break;
						}
					}
				}
				if ($ajout) {
					$tmpItem = array();
					$tmpItem[0] = $v[0];
					$tmpItem[1] = array($v[1]);
					$tmpItem[2] = $v[2];
					$tmpItem[3] = $v[3];
					$tmpArray[] = $tmpItem;
				}
			}
			//ajout facette : on vérifie qu'elle n'est pas déjà en session (rafraichissement page)
			$trouve = false;
			if (count($session_values)) {
				foreach ($session_values as $v) {
					if ($tmpArray == $v) {
						$trouve = true;
						break;
					}
				}
			}
			if (!$trouve && count($tmpArray)) {
				$session_values[] = $tmpArray;
			}
			static::set_session_values($session_values);
		}
		static::make_facette_search_env();
	}
	
	public static function get_nb_result_groupby($facettes){
		$nb_result=0;
		foreach($facettes as $facette){
			$nb_result+=$facette['nb_result'];
		}
		return $nb_result;
	}
	
	public function get_clicked() {
		if(!isset($this->clicked)) {
			$session_values = static::get_session_values();
			if(is_array($session_values)) {
				$this->clicked = $session_values;
			} else {
                $this->clicked = [];
			}
		}
		return $this->clicked;
	}
	
	public function get_not_clicked() {
	    $this->not_clicked = [];
	    $this->facette_plus = [];
		
		foreach ($this->tab_facettes as $keyFacette=>$vTabFacette) {
			$affiche = true;
			foreach ($vTabFacette['value'] as $keyValue=>$vLibelle) {
				$clicked = false;
				foreach ($this->get_clicked() as $vSessionFacette) {
					foreach ($vSessionFacette as $vDetail) {
						if (($vDetail[2]==$vTabFacette['code_champ']) && ($vDetail[3]==$vTabFacette['code_ss_champ']) && (in_array($vLibelle,$vDetail[1]))) {
							$clicked = true;
							break;
						}
					}
				}
				if (!$clicked) {
					$key = $vTabFacette['name']."_".$this->facettes[$keyFacette]['id'];
					if ($vTabFacette['size_to_display'] == '-1') {
						$this->not_clicked[$key][]=array('see_more' => true);
						$affiche = false;
					} elseif ($vTabFacette['size_to_display']!='0') {
						if (isset($this->not_clicked[$key]) && count($this->not_clicked[$key])>=$vTabFacette['size_to_display']) {
							$this->not_clicked[$key][]=array('see_more' => true);
							$affiche = false;
						}
					}
					if ($affiche) {
						$this->not_clicked[$key][]=array(
								'libelle' => $vLibelle,
								'code_champ' => $vTabFacette['code_champ'],
								'code_ss_champ' => $vTabFacette['code_ss_champ'],
								'nb_result' => $vTabFacette['nb_result'][$keyValue]
						);
					} else {
						$this->facette_plus[$this->facettes[$keyFacette]['id']]['facette'][]=$vLibelle." "."(".$vTabFacette['nb_result'][$keyValue].")";
						$this->facette_plus[$this->facettes[$keyFacette]['id']]['value'][]=$vLibelle;
						$this->facette_plus[$this->facettes[$keyFacette]['id']]['nb_result'][]=$vTabFacette['nb_result'][$keyValue];
						$this->facette_plus[$this->facettes[$keyFacette]['id']]['code_champ']=$vTabFacette['code_champ'];
						$this->facette_plus[$this->facettes[$keyFacette]['id']]['code_ss_champ']=$vTabFacette['code_ss_champ'];
						$this->facette_plus[$this->facettes[$keyFacette]['id']]['name']=$vTabFacette['name'];
						if(static::get_compare_notice_active()){
							$id=facette_search_compare::gen_compare_id($vTabFacette['name'],$vLibelle,$vTabFacette['code_champ'],$vTabFacette['code_ss_champ'],$vTabFacette['nb_result'][$keyValue]);
							$facette_compare=$this->get_facette_search_compare();
							if(isset($facette_compare->facette_compare[$id]) && $facette_compare->facette_compare[$id]){
								$facette_compare->set_available_compare($id,true);
							}
						}
					}
				}
			}
		}
		return $this->not_clicked;
	}
	
	public function get_facette_plus() {
		return $this->facette_plus;
	}
	
	protected function get_display_clicked() {
	    global $msg, $charset;
	    
		$display_clicked = "<table id='active_facette' role='presentation'>";
		$n = 0;
		foreach ($this->clicked as $k=>$v) {
			($n % 2) ? $pair_impair="odd" : $pair_impair="even";
			$n++;
			$display_clicked .= "
						<tr class='".$pair_impair."' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\">
							<td>";
			$tmp=0;
			foreach($v as $vDetail) {
				foreach($vDetail[1] as $vDetailLib) {
					if($tmp) {
						$display_clicked .= "<br>";
					}
					$display_clicked .= $vDetail[0]." : ".static::get_formatted_value($vDetail[2], $vDetail[3], $vDetailLib);
					$tmp++;
				}
			}
			$display_clicked .= "
							</td>
							<td>
								<a onclick='".static::get_link_delete_clicked($k, count($this->clicked))."' style='cursor:pointer'>
									<img src='".get_url_icon('cross.png')."' alt='".htmlentities($msg["facette_delete_one"], ENT_QUOTES, $charset)."'/>
								</a>
							</td>
						</tr>";
		}
		$display_clicked .= "
					</table>
					<div class='reinitialize-facettes'>
						<a onclick='".static::get_link_reinit_facettes()."' style='cursor:pointer' title='".htmlentities($msg['facette_reset_all'], ENT_QUOTES, $charset)."' class='reinitialize-facettes-link right'>".$msg['reset']." <i alt=\"\" class='fa fa-undo' aria-hidden='true'></i></a>
					</div>";
		return $display_clicked;
	}
	
	protected function get_display_not_clicked() {
		global $charset;
		global $msg;
		
		$display_not_clicked = '';
		if(is_array($this->not_clicked) && count($this->not_clicked)) {
			if(static::get_compare_notice_active()){
				$facette_compare=$this->get_facette_search_compare();
			}
			foreach ($this->not_clicked as $tmpName=>$facette) {
				$flagSeeMore = false;
				$tmpArray = explode("_", $tmpName);
				$idfacette = array_pop($tmpArray);
				$name = get_msg_to_display(implode("_", $tmpArray));
			
				$currentFacette=current($facette);
			
				$idGroupBy=facette_search_compare::gen_groupby_id($name,$currentFacette['code_champ'],$currentFacette['code_ss_champ']);
			
				$groupBy=facette_search_compare::gen_groupby($name,$currentFacette['code_champ'],$currentFacette['code_ss_champ'],$idGroupBy);
			
				$display_not_clicked .= "<table id='facette_list_".$idfacette."' class='facette_expande'>";
				$display_not_clicked .= "<thead>";
				$display_not_clicked .= "<tr>";
				if(static::get_compare_notice_active() && !empty($facette_compare->facette_compare) && count($facette_compare->facette_compare)){
					$display_not_clicked .= "
							<th style='width:90%' onclick='javascript:test(\"facette_list_".$idfacette."\");' colspan='2'>
								".htmlentities($name,ENT_QUOTES,$charset)."
							</th>";
					$display_not_clicked.=facette_search_compare::get_groupby_row($facette_compare,$groupBy,$idGroupBy);
					if (!empty($facette_compare->facette_groupby[$idGroupBy])){
						$facette_compare->set_available_groupby($idGroupBy,true);
					}
				}else{
					$display_not_clicked .= "
							<th onclick='javascript:test(\"facette_list_".$idfacette."\");'>
								".htmlentities($name,ENT_QUOTES,$charset)."
							</th>";
				}
				$display_not_clicked .= "</tr>";
				$display_not_clicked .= "</thead>";
				$display_not_clicked .= "<tbody id='facette_body_".$idfacette."'>";
			
				$j=0;
				foreach ($facette as $detailFacette) {
					if (!isset($detailFacette['see_more'])) {
						$id=facette_search_compare::gen_compare_id($name,$detailFacette['libelle'],$detailFacette['code_champ'],$detailFacette['code_ss_champ'],$detailFacette['nb_result']);
							
						$cacValue = encoding_normalize::json_encode(array($name,$detailFacette['libelle'],$detailFacette['code_champ'],$detailFacette['code_ss_champ'],$id,$detailFacette['nb_result']));
						if(static::get_compare_notice_active()){
							if (!isset($facette_compare->facette_compare[$id]) || empty($facette_compare->facette_compare[$id])) {
								$onclick='select_compare_facette(\''.htmlentities($cacValue,ENT_QUOTES,$charset).'\')';
								$img='double_section_arrow_16.png';
							}else{
								$facette_compare->set_available_compare($id,true);
								$onclick='';
								$img='vide.png';
							}
						}
						$facette_libelle = static::get_formatted_value($detailFacette['code_champ'], $detailFacette['code_ss_champ'], $detailFacette['libelle']);
						if($facette_libelle) {
    						$link = static::get_link_not_clicked($name, $detailFacette['libelle'], $detailFacette['code_champ'], $detailFacette['code_ss_champ'], $id, $detailFacette['nb_result']);
    						$display_not_clicked .= "
    								<tr style='display: block;' class='facette_tr'>
    									<td class='facette_col_coche'>
    										<span class='facette_coche'>
    											<input type='checkbox' name='check_facette[]' value='".htmlentities($cacValue,ENT_QUOTES,$charset)."'>
    										</span>
    									</td>
    									<td  class='facette_col_info'>
    										<a onclick='".$link."' style='cursor:pointer' rel='nofollow'>
    											<span class='facette_libelle'>
    												".htmlentities($facette_libelle, ENT_QUOTES, $charset)."
    											</span>
    											<span class='facette_number'>
    												[".htmlentities($detailFacette['nb_result'], ENT_QUOTES, $charset)."]
    											</span>
    										</a>
    									</td>
    								</tr>";
    						$j++;
						}
					} elseif(!$flagSeeMore) {
						$display_not_clicked .= "
								<tr style='display: block;' class='facette_tr_see_more'>
									<td colspan='3'>
										<button type='button' class='button-unstylized' onclick='javascript:facette_see_more(".$idfacette.",".json_encode(encoding_normalize::utf8_normalize($this->facette_plus[$idfacette]),JSON_HEX_APOS | JSON_HEX_QUOT).");'>".$msg["facette_plus_link"]."</button>
									</td>
								</tr>";
						$flagSeeMore = true;
					}
				}
				$display_not_clicked .= "</tbody>";
				$display_not_clicked .="</table>";
			}
			$display_not_clicked .= "<input type='hidden' value='' id='filtre_compare_facette' name='filtre_compare'>";
			$display_not_clicked .= "<input class='bouton bouton_filtrer_facette_bas' type='button' value='".htmlentities($msg["facette_filtre"],ENT_QUOTES,$charset)."' name='filtre' onClick='valid_facettes_multi()'>";
			if(static::get_compare_notice_active()) {
				$display_not_clicked .= "<input class='bouton' type='button' value='".htmlentities($msg["facette_compare"],ENT_QUOTES,$charset)."' name='compare' onClick='valid_facettes_compare()'>";
			}
		}
		return $display_not_clicked;
	}
	
	public function create_ajax_table_facettes(){
		global $charset;
		global $mode;
		global $msg;
		
		if(static::get_compare_notice_active()) {
			$facette_compare=$this->get_facette_search_compare();
		}
		
		$table = "<form name='facettes_multi' class='facettes_multis' method='POST' action='".static::format_url("lvl=more_results&mode=".$this->mode."&facette_test=1")."'>";
		if(count($this->get_clicked())) {
			$table .= "<h3>".htmlentities($msg['facette_active'],ENT_QUOTES,$charset)."</h3>".$this->get_display_clicked()."<br/>";
		}
		
		if(static::get_compare_notice_active()) {
			//Le tableau des critères de comparaisons
		    if(!empty($facette_compare->facette_compare) && count($facette_compare->facette_compare)){
				$table_compare=$facette_compare->gen_table_compare();
				
				$table .= "<h3 class='facette_compare_MainTitle'><table><tr><td style='width:90%;'>".htmlentities($msg['facette_list_compare_crit'],ENT_QUOTES,$charset)."</td>
				<td><a onclick='".static::get_link_back(true)."' class='facette_compare_raz'><img alt='".htmlentities($msg['facette_compare_reinit'],ENT_QUOTES,$charset)."' title='".htmlentities($msg['facette_compare_reinit'],ENT_QUOTES,$charset)."' width='18px' height='18px' src='".get_url_icon('cross.png')."'/></a></td></tr></table>
				</h3><table id='facette_compare'>".$table_compare."</table><br/>";
				
				//Le tableau des critères de comparaisons
				if(!empty($facette_compare->facette_groupby) && count($facette_compare->facette_groupby)){
					$table_groupby=$facette_compare->gen_table_groupby();
				} else {
				    $table_groupby='';
				}
				$table .= "<h3 class='facette_compare_SubTitle'><img id='facette_compare_not_clickable' src='".get_url_icon('group_by.png')."'/> ".htmlentities($msg['facette_list_groupby_crit'],ENT_QUOTES,$charset)."</h3><table id='facette_groupby'>".$table_groupby."</table><br/>";
			}
			
			//le bouton de retour
			if(isset($_SESSION['filtre_compare']) && $_SESSION['filtre_compare']=='compare'){
				$table .= "<input type='button' class='bouton backToResults' value='".htmlentities($msg['facette_compare_search_result'],ENT_QUOTES,$charset)."' onclick='".static::get_link_back()."'/><br /><br />";
			}elseif((!isset($_SESSION['filtre_compare']) || $_SESSION['filtre_compare']!='compare') && !empty($facette_compare->facette_compare) && count($facette_compare->facette_compare)){
				$table .= "<input type='button' class='bouton' value='".htmlentities($msg['facette_compare_search_compare'],ENT_QUOTES,$charset)."' onclick='valid_compare();'/><br /><br />";
			}
		}
		if (!empty($_SESSION['facettes_sets'][static::$facet_type])) {
		    $selected = $_SESSION['facettes_sets'][static::$facet_type];
		} else {
		    $selected = 0;
		}
		$facettes_sets = new facettes_sets(static::$facet_type);
		$facettes_sets_selector = $facettes_sets->get_display_selector($selected);
		
		if(count($this->get_not_clicked())){
			if(static::get_compare_notice_active()){
				$table .= "<div id='facettes_help'></div>";
				$table .= "<h3 class='facette_compare_listTitle'>".htmlentities($msg['facette_list_compare'],ENT_QUOTES,$charset)." &nbsp;<img onclick='open_popup(document.getElementById(\"facettes_help\"),\"".htmlentities($msg['facette_compare_helper_message'],ENT_QUOTES,$charset)."\");' height='18px' width='18px' title='".htmlentities($msg['facette_compare_helper'],ENT_QUOTES,$charset)."' alt='".htmlentities($msg['facette_compare_helper'],ENT_QUOTES,$charset)."' src='".get_url_icon('quest.png')."'/></h3>";
			}else{
				$table .= "<h3 class='facette_compare_listTitle'>".htmlentities($msg['facette_list'],ENT_QUOTES,$charset)."</h3>";
			}
			$table .= $facettes_sets_selector;
			$table .= $this->get_display_not_clicked()."<br/>";
		} else {
		    $table .= $facettes_sets_selector;
		}
		$table .= "</form>";
		return $table;
	}
	
	public static function set_session_facettes_set($num_facettes_set) {
	    $_SESSION['facettes_sets'][static::$facet_type] = $num_facettes_set;
	}
	
	public static function session_filtre_compare(){
		global $filtre_compare;
		
		$_SESSION['filtre_compare']=$filtre_compare;
	}
	
	public static function get_checked() {
		global $charset;
		global $name;
		global $value;
		global $champ;
		global $ss_champ;
		global $check_facette;
		
		//si rien en multi-sélection, il n'y a qu'une seule facette de cliquée
		//on l'ajoute au tableau pour avoir un traitement unique après
		if(!isset($check_facette) || !count($check_facette)){
		    $check_facette = [];
		    if (!empty($name) && isset($value)) {
		        $check_facette[] = [stripslashes($name),stripslashes($value),$champ,$ss_champ];
		    }
		}else{
			//le tableau est addslashé automatiquement
			foreach($check_facette as $k=>$v){
				$check_facette[$k]=json_decode(stripslashes($v));
				//json_encode/decode ne fonctionne qu'avec des données utf-8
				if ($charset!='utf-8') {
					foreach($check_facette[$k] as $key=>$value){
						$check_facette[$k][$key]=encoding_normalize::utf8_decode($check_facette[$k][$key]);
					}
				}
			}
		}
		return $check_facette;
	}
	
	public function get_facette_search_compare() {
		if(!isset($this->facette_search_compare)) {
			$this->facette_search_compare = new facette_search_compare();
		}
		return $this->facette_search_compare;
	}
	
	public function get_json_datas() {
		$datas = [
				'clicked' => $this->get_clicked(),
				'not_clicked' => $this->get_not_clicked(),
				'facette_plus' => $this->get_facette_plus()
		];
		return encoding_normalize::json_encode($datas);
	}
	
	public static function get_compare_notice_active() {
		if(!isset(static::$compare_notice_active)) {
		    if(static::$table_name == 'facettes') {
		        //Désactivé pour les facettes non externes
		        static::$compare_notice_active = 0;
		    } else {
		        global $pmb_compare_notice_active;
		        static::$compare_notice_active = intval($pmb_compare_notice_active);
		    }
		}
		return static::$compare_notice_active;
	}
	
	public static function set_search_mode($search_mode)
	{
	    static::$search_mode = $search_mode;
	}
	
	public static function set_hidden_form_name($hidden_form_name)
	{
	    static::$hidden_form_name = $hidden_form_name;
	}
	
	public static function set_url_base($url_base) {
		static::$url_base = $url_base;
	}
	
	protected static function format_url($url) {
		global $base_path;
	
		if(!isset(static::$url_base)) {
			static::$url_base = $base_path.'/index.php?';
		}
		if(strpos(static::$url_base, "lvl=search_segment")) {
			return static::$url_base.str_replace('lvl', '&action', $url);
		} else {
			return static::$url_base.$url;
		}
	}
	
	public static function set_facet_type($type)
	{
	    static::$facet_type = $type;
	}
	
	/**
	 * Liste des facettes (tableau ordonné)
	 * 
	 * @return array
	 */
	public static function get_list($type='notices') {
		
		$q = "select * from ".static::$table_name." where facette_type='".addslashes($type)."' order by facette_order";
		
		$r = pmb_mysql_query($q);
		if(0 === pmb_mysql_num_rows($r)) {
			return [];
		}

		$list = [];
		while( $row=pmb_mysql_fetch_assoc($r) ) {
			$list[$row['id_facette']] = $row;
		}
		return $list;
	}
}// end class
