<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facettes_root.class.php,v 1.89 2024/07/25 09:08:02 pmallambic Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) {
    die("no access");
}

use Pmb\Common\Opac\Views\VueJsView;

global $class_path;
require_once($class_path."/facette_search_compare.class.php");
require_once($class_path."/encoding_normalize.class.php");
require_once($class_path."/map/map_facette_controler.class.php");

abstract class facettes_root
{
    /**
     * Objets s�par�es par des virgules
     * @var string
     */
    public $objects_ids;

    /**
     * Liste des facettes
     * @var array
     */
    public $facettes;

    /**
     * Liste des facettes calcul�es
     * @var array
     */
    public $tab_facettes;

    /**
     * Flag pour indiquer qu'au moins une des facettes sera affich�e
     * @var boolean
     */
    public $exists_with_results = false;

    /**
     * Mode d'affichage (extended/external)
     * @var string
     */
    public $mode = 'extended';

    /**
     * Comparateur de notice activ� (oui/non)
     * @var string
     */
    protected static $compare_notice_active;

    /**
     * Instance
     * @var facette_search_compare
     */
    protected $facette_search_compare;

    /**
     * Liste des facettes s�lectionn�es
     * @var array
     */
    protected $clicked;

    /**
     * Liste des facettes non s�lectionn�es
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
    
    protected static $hidden_form_name = 'form_values';

    protected static $url_base;

    protected static $table_name;

    public function __construct($objects_ids = '')
    {
        $this->objects_ids = $objects_ids;
        $this->facette_existing();
        $this->nb_results_by_facette();
    }

    protected function get_query()
    {
        $query = "SELECT * FROM ".static::$table_name." WHERE facette_visible=1";
        if (!empty(static::$facet_type)) {
            $query .= " AND facette_type = '".addslashes(static::$facet_type)."'";
        }
        $query .= " AND num_facettes_set = 0";
        $query .= " ORDER BY facette_order, facette_name";

        return $query;
    }

    protected function facette_existing()
    {
        global $opac_view_filter_class;

        $this->facettes = [];
        $query = $this->get_query();
        $result = pmb_mysql_query($query);
        while ($row = pmb_mysql_fetch_object($result)) {
            if ($opac_view_filter_class) {
                if (!$opac_view_filter_class->is_selected(static::$table_name, $row->id_facette+0)) {
                    continue;
                }
            }
            $this->facettes[] = [
                'id'=> intval($row->id_facette),
                'type'=> $row->facette_type,
                'name'=> translation::get_text($row->id_facette, 'facettes', 'facette_name', $row->facette_name),
                'id_critere'=> intval($row->facette_critere),
				'id_ss_critere'=> intval($row->facette_ss_critere),
				'nb_result'=> intval($row->facette_nb_result),
				'limit_plus'=> intval($row->facette_limit_plus),
                'type_sort'=> intval($row->facette_type_sort),
				'order_sort'=> intval($row->facette_order_sort),
                'datatype_sort'=>$row->facette_datatype_sort,
            ];
        }
    }

    public function nb_results_by_facette()
    {
        global $msg;

        $this->tab_facettes = [];
        if ($this->objects_ids != "") {
            foreach ($this->facettes as $facette) {
                $query = $this->get_query_by_facette($facette['id_critere'], $facette['id_ss_critere'], $facette['type']);
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
                if ($facette['order_sort']==0) {
                    $query .= " asc";
                } else {
                    $query .= " desc";
                }
                if ($facette['nb_result']>0) {
                    $query .= " LIMIT"." ".$facette['nb_result'];
                }
                $result = pmb_mysql_query($query);
                $j=0;
                $array_tmp = [];
                $array_value = [];
                $array_nb_result = [];
                if ($result && pmb_mysql_num_rows($result)) {
                    while ($row = pmb_mysql_fetch_object($result)) {
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

    public static function see_more($json_facette_plus)
    {
        global $charset;

        $arrayRetour = [];
        for ($j=0; $j<count($json_facette_plus['facette']); $j++) {
            $facette_libelle = static::get_formatted_value($json_facette_plus['code_champ'], $json_facette_plus['code_ss_champ'], $json_facette_plus['value'][$j]);
            $facette_id = facette_search_compare::gen_compare_id($json_facette_plus['name'], $json_facette_plus['value'][$j], $json_facette_plus['code_champ'], $json_facette_plus['code_ss_champ'], $json_facette_plus['nb_result'][$j]);
            $facette_value = encoding_normalize::json_encode([$json_facette_plus['name'], $json_facette_plus['value'][$j], $json_facette_plus['code_champ'], $json_facette_plus['code_ss_champ'], $facette_id, $json_facette_plus['nb_result'][$j]]);
            if ($facette_libelle) {
                $arrayRetour[]= [
                    'facette_libelle' => htmlentities($facette_libelle, ENT_QUOTES, $charset),
                    'facette_number' => htmlentities($json_facette_plus['nb_result'][$j], ENT_QUOTES, $charset),
                    'facette_id' => $facette_id,
                    'facette_value' => htmlentities($facette_value, ENT_QUOTES, $charset),
                    'facette_link' => static::get_link_not_clicked($json_facette_plus['name'], $json_facette_plus['value'][$j], $json_facette_plus['code_champ'], $json_facette_plus['code_ss_champ'], $facette_id, $json_facette_plus['nb_result'][$j]),
                    'facette_code_champ' => htmlentities($json_facette_plus['code_champ'], ENT_QUOTES, $charset),
                ];
            }
        }
        return encoding_normalize::json_encode($arrayRetour);
    }

    public static function destroy_dom_node()
    {
        if ($_SESSION["cms_build_activate"]) {
            return "";
        } else {
            return "
				<script>
							require(['dojo/ready', 'dojo/dom-construct'], function(ready, domConstruct){
								ready(function(){
									domConstruct.destroy('facette');
								});
							});
				</script>";
        }
    }

    public static function get_nb_facettes()
    {
        $query = "SELECT count(id_facette) FROM ".static::$table_name." WHERE facette_visible=1";
        if (!empty(static::$facet_type)) {
            $query .= " AND facette_type = '".addslashes(static::$facet_type)."'";
        }
        $query .= " AND num_facettes_set = 0";
        $result = pmb_mysql_query($query);
        return pmb_mysql_result($result, 0);
    }

    protected static function get_ajax_base_url() 
    {
        global $base_path;
        
        return $base_path."/ajax.php?module=ajax&categ=".static::$table_name."&search_mode=".static::$search_mode;
    }
    
    protected static function get_ajax_url()
    {
        return static::get_ajax_base_url()."&sub=get_data&hidden_form_name=".static::$hidden_form_name."&facet_type=".static::$facet_type;
    }

    protected static function get_ajax_filtered_data_url() 
    {
        return static::get_ajax_base_url()."&sub=get_filtered_data&hidden_form_name=".static::$hidden_form_name."&facet_type=".static::$facet_type;
    }
    
    public static function call_ajax_facettes($additional_content = "")
    {
        $ajax_facettes = $additional_content;

        if (static::get_nb_facettes()) {
            $ajax_facettes .= static::get_facette_wrapper();
            $ajax_facettes .="
				<div id='facette_wrapper'>
					<img src='".get_url_icon('patience.gif')."'/>
					<script>
                        document.addEventListener('DOMContentLoaded', () => {
    						var req = new http_request();
    						req.request(\"".static::get_ajax_url()."\",false,null,true,function(data){
    							var response = JSON.parse(data);
    							document.getElementById('facette_wrapper').innerHTML=response.display;
    						    require(['dojo/query', 'dojo/dom-construct'], function(query, domConstruct){
        						    query('#facette_wrapper script').forEach(function(node) {
                    					domConstruct.create('script', {
                    						innerHTML: node.innerHTML
                    					}, node, 'replace');
                    				});
    						    });
    							if(!response.exists_with_results) {
    								require(['dojo/ready', 'dojo/dom-construct'], function(ready, domConstruct){
    									ready(function(){
    						                if (document.getElementById('segment_searches')) {
    										    domConstruct.destroy('facette_wrapper');
    						                } else {
    						                    domConstruct.destroy('facette');
    						                }

    									});
    								});
    							}
    							if(response.map_location) {
    								if(document.getElementById('map_location_search')) {
    									document.getElementById('map_location_search').innerHTML=response.map_location;
    									if(typeof(dojo) == 'object'){
    										dojo.require('dojo.parser');
    										dojo.parser.parse(document.getElementById('map_location_search'));
    									}
    								}
    							}
                                hide_element_by_its_hidden_children('bandeau');
    						}, '', '', true);
                        });
					</script>
				</div>";
        }
        return $ajax_facettes;
    }

    public static function make_facette($objects_ids)
    {
        global $opac_facettes_ajax, $opac_map_activate;

        $return = "";
        $class_name = static::class;
        $facettes = new $class_name($objects_ids);

        if (!$opac_facettes_ajax && ($opac_map_activate == 1 || $opac_map_activate == 3)) {
            $return .= "<div id='map_location_search'>" . $facettes->get_map_location() . "</div>";
        }
        if ($facettes->exists_with_results || count($facettes->get_clicked())) {
            $return .= static::get_facette_wrapper();
            $return .= $facettes->create_ajax_table_facettes();
        } else {
            $return .= self::destroy_dom_node();
        }
        return $return;
    }

    public function get_expls_location($notices_ids)
    {
        global $opac_show_exemplaires;

        if (!$notices_ids) {
            return [];
        }
        if ($opac_show_exemplaires) {
            $query = "SELECT DISTINCT id_location, id_notice from ( " . $this->get_query_expl($notices_ids) . " UNION " . $this->get_query_explnum($notices_ids) . " ) as sub";
        } else {
            $query = $this->get_query_explnum($notices_ids);
        }
        $expls_location = [
                'ids' => [],
                'notices_number' => [],
        ];
        $result = pmb_mysql_query($query);
        if ($result && pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                $expls_location['ids'][] = $row->id_location;
                if (!isset($expls_location['notices_number'][$row->id_location])) {
                    $expls_location['notices_number'][$row->id_location] = 0;
                }
                $expls_location['notices_number'][$row->id_location] ++;
            }
            $expls_location['ids'] = array_unique($expls_location['ids']);
        }
        return $expls_location;
    }

    public function get_map_location()
    {
        global $opac_map_activate;
        global $opac_sur_location_activate;
        global $msg;

        if (!$opac_map_activate || $opac_map_activate==2 || !strlen($_SESSION["tab_result"])) {
            return '';
        }
        $expls_location = $this->get_expls_location($_SESSION["tab_result"]);
        if (count($expls_location['ids']) > 1) {
            $surlocations_ids = [];
            $query = "SELECT DISTINCT idlocation, location_libelle, surloc_num, surloc_libelle
				FROM docs_location
				LEFT JOIN sur_location on surloc_id=surloc_num
				WHERE idlocation IN ( \"" . implode('","', $expls_location['ids']) . "\")";
            $result = pmb_mysql_query($query);
            if (pmb_mysql_num_rows($result)) {
                $tab_locations = [];
                $tab_surlocations = [];
                $location_ids = [];
                $i = 0;
                while ($row = pmb_mysql_fetch_object($result)) {
                    $location_ids[] = $row->idlocation;
                    $tab_locations[$i]["id"] = $row->idlocation;
					$tab_locations[$i]['libelle'] = translation::get_translated_text($row->idlocation, "docs_location", "location_libelle", $row->location_libelle);
                    $tab_locations[$i]['name'] = $msg['extexpl_location'];
                    $tab_locations[$i]['notices_number'] = $expls_location['notices_number'][$row->idlocation];
                    $tab_locations[$i]['surloc_num'] = $row->surloc_num;
                    $tab_locations[$i]['code_champ'] = 90;
                    $tab_locations[$i]['code_ss_champ'] = 4;
                    $tab_locations[$i]['url'] = static::format_url('lvl=more_results&mode=extended&facette_test=1');
                    $tab_locations[$i]['param'] = '&check_facette[]=["' . $tab_locations[$i]['name'] . '","' . $tab_locations[$i]['libelle'] . '",90,4,"",0]';
                    $i++;
                    if ($row->surloc_num && !array_key_exists($row->surloc_num, $tab_surlocations) && $opac_sur_location_activate) {
                        $surlocations_ids[] = $row->surloc_num;
                        $tab_surlocations[$row->surloc_num]["id"] = $row->surloc_num;
                        $tab_surlocations[$row->surloc_num]['libelle'] = $row->surloc_libelle;
                        $tab_surlocations[$row->surloc_num]['name'] = $msg['extexpl_surlocation'];
                        $tab_surlocations[$row->surloc_num]['code_champ'] = 90;
                        $tab_surlocations[$row->surloc_num]['code_ss_champ'] = 9;
                        $tab_surlocations[$row->surloc_num]['url'] = static::format_url('lvl=more_results&mode=extended&facette_test=1');
                        $tab_surlocations[$row->surloc_num]['param'] = '&check_facette[]=["' . $tab_surlocations[$row->surloc_num]['name'] . '","' . $tab_surlocations[$row->surloc_num]['libelle'] . '",90,9,"",0]';
                    }
                    if ($row->surloc_num && $opac_sur_location_activate) {
                        if (!isset($tab_surlocations[$row->surloc_num]['notices_number'])) {
                            $tab_surlocations[$row->surloc_num]['notices_number'] = 0;
                        }
                        $tab_surlocations[$row->surloc_num]['notices_number'] += $expls_location['notices_number'][$row->idlocation];
                    }
                }
                if (count($surlocations_ids) <= 1) {
                    return map_facette_controler::get_map_facette_location($location_ids, $tab_locations, [], []);
                } else {
                    $tab_locations_without_surloc = [];
                    $ids_loc_without_surloc = [];
                    foreach ($tab_locations as $location) {
                        if (!$location['surloc_num']) {
                            $ids_loc_without_surloc[] = $location["id"];
                            $tab_locations_without_surloc[] = $location;
                        }
                    }
                    return map_facette_controler::get_map_facette_location($ids_loc_without_surloc, $tab_locations_without_surloc, $surlocations_ids, $tab_surlocations);
                }
            }
        } else {
            return '';
        }
    }


    public static function make_ajax_facette($objects_ids)
    {
        $class_name = static::class;
        $facettes = new $class_name($objects_ids);

        $facettes_exists_with_or_without_results = false;
        if ($facettes->exists_with_results || count($facettes->get_clicked())) {
            $facettes_exists_with_or_without_results = true;
        }
        return [
            'exists_with_results' => ($_SESSION["cms_build_activate"] ? true : $facettes_exists_with_or_without_results),
            'display' => $facettes->create_ajax_table_facettes(),
            'map_location' =>  $facettes->get_map_location(),
        ];
    }

    protected static function get_ajax_see_more_url()
    {
        return static::get_ajax_base_url()."&sub=see_more";
    }
    
    protected static function get_ajax_filters_url($action='') 
    {
        return static::get_ajax_base_url()."&sub=filters&action=".$action."&facet_type=".static::$facet_type;
    }
    
    public static function get_modal_data()
    {
        $data = [];
        $data['form_name'] = "facettes_multi";
        return $data;
    }
    public static function get_facette_wrapper()
    {
        global $msg, $charset, $base_path, $opac_facettes_modal_activate, $opac_rgaa_active;

        $script = "";

        if (1 == $opac_facettes_modal_activate) {
            $vueJsView = new VueJsView("facettes/modal", static::get_modal_data());
            $script .= $vueJsView->render();
        }

        $script .= "
		<script src='".$base_path."/includes/javascript/select.js' ></script>
        <script type='text/javascript'> 
            var facettes_hidden_form_name = '".static::$hidden_form_name."';
            var facettes_ajax_see_more_url = '".static::get_ajax_see_more_url()."';
            var facettes_ajax_filtered_data_url = '".static::get_ajax_filtered_data_url()."';
            var facettes_ajax_filters_get_elements_url = '".static::get_ajax_filters_url('get_elements')."';
        </script>
        <script src='".$base_path."/includes/javascript/facettes.js' type='text/javascript'></script>
		<script>

			function facette_see_more(id, json_facette_plus) {

				const usingModal = '".$opac_facettes_modal_activate."' == 1;
				var myTable = document.getElementById('facette_list_'+id);

				if (json_facette_plus == null) {

                    if (usingModal) {
                        if (typeof openModal == 'function') {
                            return openModal(id);
                        } else {
                            console.error('[facettes_modal] : openModal is not a function !')
                            return false;
                        }
                    }

					var childs = myTable.querySelectorAll('tbody[id^=\'facette_body\'] .facette_tr');
					var nb_childs = childs.length;
					for(var i = 0; i < nb_childs; i++){
						if (childs[i].getAttribute('data-facette-ajax-loaded')!=null) {
							if (childs[i].getAttribute('style')=='display:block') {
								childs[i].setAttribute('style','display:none');
								childs[i].setAttribute('data-facette-expanded','false');
							} else {
								childs[i].setAttribute('style','display:block');
								childs[i].setAttribute('data-facette-expanded','true');
							}
						}
					}

					var see_more_less = document.getElementById('facette_see_more_less_'+id);
					see_more_less.innerHTML='';
					var span = document.createElement('span');
					if (see_more_less.getAttribute('data-etat')=='plus') {
						span.className='facette_moins_link';
						span.innerHTML='".htmlentities($msg['facette_moins_link'], ENT_QUOTES, $charset)."';
						see_more_less.setAttribute('data-etat','moins');
                        span.setAttribute('aria-label','".htmlentities($msg['facette_moins_label'], ENT_QUOTES, $charset)."');
					} else {
						span.className='facette_plus_link';
						span.innerHTML ='".htmlentities($msg['facette_plus_link'], ENT_QUOTES, $charset)."';
						see_more_less.setAttribute('data-etat','plus');
                        span.setAttribute('aria-label','".htmlentities($msg['facette_plus_label'], ENT_QUOTES, $charset)."');
					}
					see_more_less.appendChild(span);

				} else {
					var req = new http_request();
					var sended_datas = {'json_facette_plus': json_facette_plus };
					req.request(\"".static::get_ajax_see_more_url()."\", true, 'sended_datas='+encodeURIComponent(JSON.stringify(sended_datas)), true, function(response) {
                        if (usingModal) {
                            if (typeof callback_see_more_modal == 'function') {
                                callback_see_more_modal(id, myTable, response)
                            } else {
                                console.error('[facettes_modal] : callback_see_more_modal is not a function !')
                            }
                        } else {
                            callback_see_more(id, myTable, response);
                        }
                    });
				}
			}

            function callback_see_more(id, myTable, data) {
    			var jsonArray = JSON.parse(data);
    			//on supprime la ligne '+'
                var facetteListTbody = myTable.querySelector('tbody[id^=\'facette_body\']');
    			facetteListTbody.removeChild(myTable.rows[myTable.rows.length-1]);
    			//on ajoute les lignes au tableau
    			for(var i=0;i<jsonArray.length;i++) {

    				var tr = document.createElement('tr');
    				tr.setAttribute('style','display:block');
    				tr.setAttribute('class', 'facette_tr');
    				tr.setAttribute('data-facette-expanded','true');
    				tr.setAttribute('data-facette-ajax-loaded','1');
    	        	var td = tr.appendChild(document.createElement('td'));
    				td.setAttribute('class','facette_col_coche');

                    var uniqueIdInput = Math.random().toString(20).slice(2, 15);
                    var spanCheckbox = td.appendChild(document.createElement('span'));
                    spanCheckbox.setAttribute('class','facette_coche');
                    spanCheckbox.innerHTML = \"<input id='facette-\" + jsonArray[i]['facette_code_champ'] + \"-\" + uniqueIdInput + \"' type='checkbox' name='check_facette[]' value='\" + jsonArray[i]['facette_value'] + \"'></span>\";

                    var labelCheckbox = document.createElement('label');
                    spanCheckbox.prepend(labelCheckbox);
                    labelCheckbox.setAttribute('for','facette-' + jsonArray[i]['facette_code_champ'] + '-' + uniqueIdInput );
                    labelCheckbox.setAttribute('class', 'visually-hidden');
                    labelCheckbox.innerHTML = jsonArray[i]['facette_libelle'];

                    var td2 = tr.appendChild(document.createElement('td'));
    				td2.setAttribute('class','facette_col_info');
                    var aonclick = td2.appendChild(document.createElement('a'));
                    aonclick.setAttribute('style', 'cursor:pointer;');
                    aonclick.setAttribute('rel', 'nofollow');
                    aonclick.setAttribute('class', 'facet-link');
                    if (jsonArray[i]['facette_link']) {
                        aonclick.setAttribute('onclick', jsonArray[i]['facette_link']);
                    } else {
                        //Evt vers SearchSegmentController pour l'initialisation du clic
                        require(['dojo/topic'], function(topic){
    						topic.publish('FacettesRoot', 'FacettesRoot', 'initFacetLink', {elem: aonclick});
    					});
                    }
                    var span_facette_link = aonclick.appendChild(document.createElement('span'));
                    span_facette_link.setAttribute('class', 'facette_libelle');
    	        	span_facette_link.innerHTML = jsonArray[i]['facette_libelle'];
    				aonclick.appendChild(document.createTextNode(' '));
                    var span_facette_number = aonclick.appendChild(document.createElement('span'));
                    span_facette_number.setAttribute('class', 'facette_number');
                    span_facette_number.innerHTML = \"[\" + jsonArray[i]['facette_number'] + \"]\";
    	        	facetteListTbody.appendChild(tr);
    			}

                add_see_less(myTable, id);
    		}

            function add_see_less(myTable, id) {
    			//Ajout du see_less
    			var tr = document.createElement('tr');
    			tr.setAttribute('style','display:block');
    			tr.setAttribute('data-see-less','1');
    			tr.setAttribute('class','facette_tr_see_more');

    			var td = tr.appendChild(document.createElement('td'));
    			td.setAttribute('colspan','3');";


                if($opac_rgaa_active){
                    $script .= "
                        var node_see_less = document.createElement('button');
                        node_see_less.setAttribute('type','button');
                        node_see_less.setAttribute('class','button-unstylized');
                    ";
                }else{
                    $script .= "
                        var node_see_less = document.createElement('a');
                        node_see_less.setAttribute('role','button');
                        node_see_less.setAttribute('href','#');
                    ";
                };

                $script .= "
                    node_see_less.setAttribute('id','facette_see_more_less_'+id);
                    node_see_less.setAttribute('data-etat','moins');
                    node_see_less.setAttribute('onclick','javascript:facette_see_more(' + id + ',null); return false;');
                    node_see_less.setAttribute('style','cursor:pointer');
                    node_see_less.innerHTML='';

                    td.appendChild(node_see_less);
                    var span = document.createElement('span');
                    span.className='facette_moins_link';
                    span.setAttribute('aria-label','". ($opac_facettes_modal_activate ? htmlentities($msg['facette_plus_label'], ENT_QUOTES, $charset) : htmlentities($msg['facette_moins_label'], ENT_QUOTES, $charset)) ."');
                    span.innerHTML='". ($opac_facettes_modal_activate ? htmlentities($msg['facette_plus_link'], ENT_QUOTES, $charset) : htmlentities($msg['facette_moins_link'], ENT_QUOTES, $charset)) ."';

                    node_see_less.appendChild(span);

                    var facetteListTbody = myTable.querySelector('tbody[id^=\'facette_body\']');
                    facetteListTbody.appendChild(tr);
            }";
        if (static::get_compare_notice_active()) {
            $compare_class_name = static::$compare_class_name;
            $script .= $compare_class_name::get_compare_wrapper();
        }
        $script .= "</script>";
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
                //on d�cale
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

    public static function checked_facette_search()
    {
        global $param_delete_facette;
        global $opac_facettes_operator;

        $session_values = static::get_session_values();
        if (!is_array($session_values)) {
            $session_values = [];
        }
        //Suppression facette
        if ($param_delete_facette!="") {
            //On �vite le rafraichissement de la page
            static::delete_session_value($param_delete_facette);
        } else {
            $tmpArray = [];
            $check_facette = static::get_checked();
            foreach ($check_facette as $key => $facet_values) {
                $ajout=true;
                if (count($tmpArray)) {
                    foreach ($tmpArray as $prev_key => $prev_values) {
                        //On test le champ et le sous champ
                        if (($prev_values[2] == $facet_values[2]) && ($prev_values[3] == $facet_values[3])) {
                            $tmpArray[$prev_key][1][] = $facet_values[1];
                            $ajout=false;
                            break;
                        }
                    }
                }
                if ($ajout) {
                    $tmpItem = [];
                    $tmpItem[0] = $facet_values[0];
                    $tmpItem[1] = [$facet_values[1]];
                    $tmpItem[2] = $facet_values[2];
                    $tmpItem[3] = $facet_values[3];
                    $tmpArray[] = $tmpItem;
                }
            }
            //ajout facette : on v�rifie qu'elle n'est pas d�j� en session (rafraichissement page)
            $trouve = false;
            if (count($session_values)) {
                foreach ($session_values as $k=>$v) {
                    if ($tmpArray == $v) {
                        $trouve = true;
                        break;
                    } elseif ($opac_facettes_operator == 'or') {
                        if ($tmpArray[0][2] == $v[0][2]) {
                            $session_values[$k][0][1] = array_merge($tmpArray[0][1], $v[0][1]);
                            $trouve = true;
                            break;
                        }
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

    public static function get_nb_result_groupby($facettes)
    {
        $nb_result=0;
        foreach ($facettes as $facette) {
            $nb_result+=$facette['nb_result'];
        }
        return $nb_result;
    }

    public function get_clicked()
    {
        if (!isset($this->clicked)) {
            $session_values = static::get_session_values();
            if (is_array($session_values)) {
                $this->clicked = $session_values;
            } else {
                $this->clicked = [];
            }
        }
        return $this->clicked;
    }

    public function get_not_clicked()
    {
        $this->not_clicked = [];
        $this->facette_plus = [];
        foreach ($this->tab_facettes as $keyFacette=>$vTabFacette) {
            $affiche = true;
            foreach ($vTabFacette['value'] as $keyValue=>$vLibelle) {
// 				$clicked = false;
// 				foreach ($this->get_clicked() as $vSessionFacette) {
// 					foreach ($vSessionFacette as $vDetail) {
//						if (($vDetail[2]==$vTabFacette['code_champ']) && ($vDetail[3]==$vTabFacette['code_ss_champ']) && (in_array($vLibelle,$vDetail[1]))) {
// 							$clicked = true;
// 							break;
// 						}
// 					}
// 				}
// 				if (!$clicked) {
                $key = $vTabFacette['name']."_".$this->facettes[$keyFacette]['id'];
                if ($vTabFacette['size_to_display'] == '-1') {
                    $this->not_clicked[$key][]=['see_more' => true];
                    $affiche = false;
                } elseif ($vTabFacette['size_to_display']!='0') {
                    if (isset($this->not_clicked[$key]) && count($this->not_clicked[$key])>=$vTabFacette['size_to_display']) {
                        $this->not_clicked[$key][]=['see_more' => true];
                        $affiche = false;
                    }
                }
                if ($affiche) {
                    $this->not_clicked[$key][]=[
                            'libelle' => $vLibelle,
                            'code_champ' => $vTabFacette['code_champ'],
                            'code_ss_champ' => $vTabFacette['code_ss_champ'],
                            'nb_result' => $vTabFacette['nb_result'][$keyValue]
                    ];
                } else {
                    $this->facette_plus[$this->facettes[$keyFacette]['id']]['facette'][]=$vLibelle." "."(".$vTabFacette['nb_result'][$keyValue].")";
                    $this->facette_plus[$this->facettes[$keyFacette]['id']]['value'][]=$vLibelle;
                    $this->facette_plus[$this->facettes[$keyFacette]['id']]['nb_result'][]=$vTabFacette['nb_result'][$keyValue];
                    $this->facette_plus[$this->facettes[$keyFacette]['id']]['code_champ']=$vTabFacette['code_champ'];
                    $this->facette_plus[$this->facettes[$keyFacette]['id']]['code_ss_champ']=$vTabFacette['code_ss_champ'];
                    $this->facette_plus[$this->facettes[$keyFacette]['id']]['name']=$vTabFacette['name'];
                    if (static::get_compare_notice_active()) {
                        $id=facette_search_compare::gen_compare_id($vTabFacette['name'], $vLibelle, $vTabFacette['code_champ'], $vTabFacette['code_ss_champ'], $vTabFacette['nb_result'][$keyValue]);
                        $facette_compare=$this->get_facette_search_compare();
                        if (isset($facette_compare->facette_compare[$id]) && $facette_compare->facette_compare[$id]) {
                            $facette_compare->set_available_compare($id, true);
                        }
                    }
                }
            }
        }
        return $this->not_clicked;
    }

    public function get_facette_plus()
    {
        return $this->facette_plus;
    }

    protected function get_display_clicked()
    {
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
            foreach ($v as $vDetail) {
                foreach ($vDetail[1] as $vDetailLib) {
                    if ($tmp) {
                        $display_clicked .= "<br>";
                    }
                    $display_clicked .= htmlentities($vDetail[0]." : ".static::get_formatted_value($vDetail[2], $vDetail[3], $vDetailLib), ENT_QUOTES, $charset);
                    $tmp++;
                }
            }
            $display_clicked .= "
							</td>
							<td>
								<a class='reinitialize-facettes-link' href='#' onclick='".static::get_link_delete_clicked($k, count($this->clicked))."' title='".htmlentities($msg["facette_delete_one"], ENT_QUOTES, $charset)."'>
                                    <img src='".get_url_icon('cross.png')."' alt='".htmlentities($msg["facette_delete_one"], ENT_QUOTES, $charset)."'/>
								</a>
							</td>
						</tr>";
        }
        $display_clicked .= "
					</table>
					<div class='reinitialize-facettes'>
						<a href='#' onclick='".static::get_link_reinit_facettes()."' aria-label='".htmlentities($msg['facette_reset_all'], ENT_QUOTES, $charset)."' title='".htmlentities($msg['facette_reset_all'], ENT_QUOTES, $charset)."' class='reinitialize-facettes-link right'>
                            ".$msg['reset']."
                            <i class='fa fa-undo' aria-hidden='true'></i>
                        </a>
					</div>";
        return $display_clicked;
    }

    protected function get_display_not_clicked()
    {
        global $charset;
        global $msg;
        global $opac_rgaa_active;

        $display_not_clicked = '';
        if (is_array($this->not_clicked) && count($this->not_clicked)) {
            if (static::get_compare_notice_active()) {
                $facette_compare=$this->get_facette_search_compare();
            }
            $display_not_clicked .= "<input class='bouton bouton_filtrer_facette_haut filter_button' type='button' value='".htmlentities($msg["facette_filtre"], ENT_QUOTES, $charset)."' name='filtre2' ".$this->get_filter_button_action().">";
            foreach ($this->not_clicked as $tmpName=>$facette) {
                $flagSeeMore = false;
                $tmpArray = explode("_", $tmpName);
                $idfacette = array_pop($tmpArray);
                $name = get_msg_to_display(implode("_", $tmpArray));

                $currentFacette=current($facette);

                $idGroupBy=facette_search_compare::gen_groupby_id($name, $currentFacette['code_champ'], $currentFacette['code_ss_champ']);

                $groupBy=facette_search_compare::gen_groupby($name, $currentFacette['code_champ'], $currentFacette['code_ss_champ'], $idGroupBy);

                $display_not_clicked .= "<table id='facette_list_".$idfacette."' class='facette_expande' role='presentation'>";
//                 $display_not_clicked .= "<thead>";
                $display_not_clicked .= "<tr>";
                if (static::get_compare_notice_active() && count($facette_compare->facette_compare)) {
                    $display_not_clicked .= "
							<th style='width:90%' onclick='javascript:test(\"facette_list_".$idfacette."\");' colspan='2' aria-expanded='true' aria-controls='facette_body_".$idfacette."' role='button'>
								".htmlentities($name, ENT_QUOTES, $charset)."
							</th>";
                    $display_not_clicked.=facette_search_compare::get_groupby_row($facette_compare, $groupBy, $idGroupBy);
                    if (isset($facette_compare->facette_groupby[$idGroupBy]) && $facette_compare->facette_groupby[$idGroupBy]) {
                        $facette_compare->set_available_groupby($idGroupBy, true);
                    }
                } else {
                    if($opac_rgaa_active) {
                        $display_not_clicked .= "
							<td>
                                <span id='legend_facette_". $idfacette ."' class='visually-hidden'>". htmlentities($name, ENT_QUOTES, $charset). htmlentities($msg['1901'], ENT_QUOTES, $charset) . htmlentities($msg['rgaa_sort_ui_legend'], ENT_QUOTES, $charset) ."</span>
								<button type='button' class='facette_name button-unstylized' onclick='javascript:test(\"facette_list_".$idfacette."\");' aria-expanded='true' aria-controls='facette_body_".$idfacette."'>".htmlentities($name, ENT_QUOTES, $charset)."</button>
							</td>";
                    } else {
                        $display_not_clicked .= "
							<th onclick='javascript:test(\"facette_list_".$idfacette."\");' aria-expanded='true' aria-controls='facette_body_".$idfacette."' role='button'>
								".htmlentities($name, ENT_QUOTES, $charset)."
							</th>";
                    }
                }
                $display_not_clicked .= "</tr>";
//                 $display_not_clicked .= "</thead>";
                $display_not_clicked .= "<tbody id='facette_body_".$idfacette."' role='group' aria-labelledby='legend_facette_". $idfacette ."'>";

                $j=0;
                foreach ($facette as $detailFacette) {
                    if (!isset($detailFacette['see_more'])) {
                        $id=facette_search_compare::gen_compare_id($name, $detailFacette['libelle'], $detailFacette['code_champ'], $detailFacette['code_ss_champ'], $detailFacette['nb_result']);

                        $cacValue = encoding_normalize::json_encode([$name,$detailFacette['libelle'],$detailFacette['code_champ'],$detailFacette['code_ss_champ'],$id,$detailFacette['nb_result']]);
                        if (static::get_compare_notice_active()) {
                            if (!isset($facette_compare->facette_compare[$id]) || !sizeof($facette_compare->facette_compare[$id])) {
                                $onclick='select_compare_facette(\''.htmlentities($cacValue, ENT_QUOTES, $charset).'\')';
                                $img='double_section_arrow_16.png';
                            } else {
                                $facette_compare->set_available_compare($id, true);
                                $onclick='';
                                $img='vide.png';
                            }
                        }
                        $facette_libelle = static::get_formatted_value($detailFacette['code_champ'], $detailFacette['code_ss_champ'], $detailFacette['libelle']);
                        if ($facette_libelle) {
                            $link = static::get_link_not_clicked($name, $detailFacette['libelle'], $detailFacette['code_champ'], $detailFacette['code_ss_champ'], $id, $detailFacette['nb_result']);
                            // $idFacetteLabel = "facette-". $detailFacette['code_champ'] . $j . rand(0,999);
                            $idFacetteLabel = uniqid("facette-". $detailFacette['code_champ']."-", false);
                            $display_not_clicked .= "
									<tr style='display: block;' class='facette_tr' role='treeitem'>
										<td class='facette_col_coche' role='presentation'>
											<span class='facette_coche'>
                                                <label for='$idFacetteLabel' class='visually-hidden'>".htmlentities($facette_libelle, ENT_QUOTES, $charset)."</label>
                                                <input id='$idFacetteLabel' type='checkbox' name='check_facette[]' value='".htmlentities($cacValue, ENT_QUOTES, $charset)."' title='". htmlentities($msg['rgaa_checkbox_check'],ENT_QUOTES, $charset)."'>
                                            </span>
                                        </td>
                                        <td  class='facette_col_info' role='presentation'>
                                            <a href='#' ".$this->on_facet_click($link)." style='cursor:pointer' rel='nofollow' class='facet-link' aria-label='". htmlentities($msg['facettes_modal_trigger_filter_aria_label'],ENT_QUOTES, $charset)." : " . $facette_libelle. " [" . $detailFacette['nb_result'] . "]'>
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
                    } elseif (!$flagSeeMore) {
                        $display_not_clicked .= "
								<tr style='display: block;' class='facette_tr_see_more' role='treeitem'>
									<td colspan='3' role='presentation'>";
                        if($opac_rgaa_active) {
                            $display_not_clicked .= "<button type='button' class='button-unstylized' onclick='javascript:facette_see_more(".$idfacette.",".encoding_normalize::json_encode($this->facette_plus[$idfacette]).");'><span class='facette_plus_link' aria-label='".htmlentities($msg['facette_plus_label'], ENT_QUOTES, $charset)."'>".htmlentities($msg['facette_plus_link'], ENT_QUOTES, $charset)."</span></button>";
                        } else {
                            $display_not_clicked .= "<a role='button' href='javascript:facette_see_more(".$idfacette.",".encoding_normalize::json_encode($this->facette_plus[$idfacette]).");'><span class='facette_plus_link' aria-label='".htmlentities($msg['facette_plus_label'], ENT_QUOTES, $charset)."'>".htmlentities($msg['facette_plus_link'], ENT_QUOTES, $charset)."</span></a>";
                        }
                        $display_not_clicked .= "
									</td>
								</tr>";
                        $flagSeeMore = true;
                    }
                }
                $display_not_clicked .= "</tbody>";
                $display_not_clicked .="</table>";
            }
            $display_not_clicked .= "<input type='hidden' value='' id='filtre_compare_facette' name='filtre_compare'>";
            $display_not_clicked .= "<input class='bouton bouton_filtrer_facette_bas filter_button' type='button' value='".htmlentities($msg["facette_filtre"], ENT_QUOTES, $charset)."' name='filtre' ".$this->get_filter_button_action().">";
            if (static::get_compare_notice_active()) {
                $display_not_clicked .= "<input class='bouton' type='button' value='".htmlentities($msg["facette_compare"], ENT_QUOTES, $charset)."' name='compare' onClick='valid_facettes_compare()'>";
            }
        }
        return $display_not_clicked;
    }

    protected function get_filter_button_action()
    {
        return "onClick='valid_facettes_multi()'";
    }

    protected function on_facet_click($link = '')
    {
        return "onclick='".$link." return false;'";
    }

    public function create_ajax_table_facettes()
    {
        global $charset;
        global $mode;
        global $msg;
        global $opac_rgaa_active;

        if (static::get_compare_notice_active()) {
            $facette_compare=$this->get_facette_search_compare();
        }

        $table = "<form name='facettes_multi' class='facettes_multis' method='POST' action='".$this->get_action_form()."'>";
        if (count($this->get_clicked())) {

            $sectionTitle = $opac_rgaa_active ? "<h2 class='facette_title' role='presentation'>%s</h2>" : "<h3>%s</h3>";
            $sectionTitle = sprintf($sectionTitle, htmlentities($msg['facette_active'], ENT_QUOTES, $charset));
            $table .= $sectionTitle.$this->get_display_clicked()."<br/>";
        }

        if (static::get_compare_notice_active()) {
            //Le tableau des crit�res de comparaisons
            if (count($facette_compare->facette_compare)) {
                $table_compare=$facette_compare->gen_table_compare();

                $sectionTitle = "<h3 class='facette_compare_MainTitle'>%s</h3>";
                if ($opac_rgaa_active) {
                    $sectionTitle = "<h2 class='facette_compare_MainTitle' role='presentation'>%s</h2>";
                }

                $table .= sprintf(
                    $sectionTitle,
                    "<table role='presentation'>
				        <tr>
				            <td style='width:90%;'>".htmlentities($msg['facette_list_compare_crit'], ENT_QUOTES, $charset)."</td>
				            <td>
				                <a onclick='".static::get_link_back(true)."' class='facette_compare_raz'>
    				                <img width='18px' height='18px'
				                    alt='".htmlentities($msg['facette_compare_reinit'], ENT_QUOTES, $charset)."'
				                    title='".htmlentities($msg['facette_compare_reinit'], ENT_QUOTES, $charset)."'
				                    src='".get_url_icon('cross.png')."'/>
				                </a>
			                </td>
				        </tr>
				    </table>"
                );
                $table .= "<table id='facette_compare' role='presentation'>".$table_compare."</table><br/>";

                //Le tableau des crit�res de comparaisons
                $table_groupby = "";
                if (count($facette_compare->facette_groupby)) {
                    $table_groupby=$facette_compare->gen_table_groupby();
                }

                if ($opac_rgaa_active) {
                    $sectionTitle = "<h2 class='facette_compare_SubTitle' role='presentation'>%s</h2>";
                } else {
                    $sectionTitle = "<h3 class='facette_compare_SubTitle'>%s</h3>";
                }

                $table .= sprintf(
                    $sectionTitle,
                    "<img id='facette_compare_not_clickable' src='".get_url_icon('group_by.png')."'/> "
                     . htmlentities($msg['facette_list_groupby_crit'], ENT_QUOTES, $charset)
                );
                $table .= "<table id='facette_groupby' role='presentation'>".$table_groupby."</table><br/>";
            }

            //le bouton de retour
            if (isset($_SESSION['filtre_compare']) && $_SESSION['filtre_compare']=='compare') {
                $table .= "<input type='button' class='bouton backToResults' value='".htmlentities($msg['facette_compare_search_result'], ENT_QUOTES, $charset)."' onclick='".static::get_link_back()."'/><br /><br />";
            } elseif ((!isset($_SESSION['filtre_compare']) || $_SESSION['filtre_compare']!='compare') && count($facette_compare->facette_compare)) {
                $table .= "<input type='button' class='bouton' value='".htmlentities($msg['facette_compare_search_compare'], ENT_QUOTES, $charset)."' onclick='valid_compare();'/><br /><br />";
            }
        }

        if (count($this->get_not_clicked())) {
            if ($opac_rgaa_active) {
                $sectionTitle = "<h2 class='facette_compare_listTitle' role='presentation'>%s</h2>";
            } else {
                $sectionTitle = "<h3 class='facette_compare_listTitle'>%s</h3>";
            }

            if (static::get_compare_notice_active()) {
                $table .= "<div id='facettes_help' role='dialog' aria-labelledby='".htmlentities($msg['facette_compare_helper'], ENT_QUOTES, $charset)."' aria-modal='true'></div>";
                $table .= sprintf(
                        $sectionTitle,
                        htmlentities($msg['facette_list_compare'], ENT_QUOTES, $charset)."
                                &nbsp;
                                <button class='button-unstylized' aria-controls='facettes_help' type='button' onclick='open_popup(document.getElementById(\"facettes_help\"),\"".htmlentities($msg['facette_compare_helper_message'], ENT_QUOTES, $charset)."\");'>
                                    <img height='18px' width='18px' title='".htmlentities($msg['facette_compare_helper'], ENT_QUOTES, $charset)."' alt='".htmlentities($msg['facette_compare_helper'], ENT_QUOTES, $charset)."' src='".get_url_icon('quest.png')."'/>
                                </button>"
                        );
                $table .= $this->get_display_not_clicked()."<br/>";
            } else {
                $table .= sprintf(
                    $sectionTitle,
                    htmlentities($msg['facette_list'], ENT_QUOTES, $charset)
                );
                $table .= $this->get_display_not_clicked()."<br/>";
            }
        }
        $table .= "</form>";
        return $table;
    }

    public static function set_session_facettes_set($num_facettes_set)
    {
        $_SESSION['facettes_sets'][static::$facet_type] = $num_facettes_set;
    }
    
    protected function get_action_form()
    {
        return static::format_url("lvl=more_results&mode=".$this->mode."&facette_test=1");
    }

    public static function session_filtre_compare()
    {
        global $filtre_compare;

        $_SESSION['filtre_compare']=$filtre_compare;
    }

    public static function get_checked()
    {
        global $charset;
        global $name;
        global $value;
        global $champ;
        global $ss_champ;
        global $check_facette;
        //si rien en multi-s�lection, il n'y a qu'une seule facette de cliqu�e
        //on l'ajoute au tableau pour avoir un traitement unique apr�s
        if (!isset($check_facette) || !count($check_facette)) {
            $check_facette = [];
            if (!empty($name) && isset($value)) {
                $check_facette[] = [stripslashes($name),stripslashes($value),$champ,$ss_champ];
            }
        } else {
            //le tableau est addslash� automatiquement
            foreach ($check_facette as $k=>$v) {
                $check_facette[$k]=json_decode(stripslashes($v));
                //json_encode/decode ne fonctionne qu'avec des donn�es utf-8
                if ($charset!='utf-8') {
                    foreach ($check_facette[$k] as $key=>$value) {
                        $check_facette[$k][$key]=encoding_normalize::utf8_decode($check_facette[$k][$key]);
                    }
                }
            }
        }

        return $check_facette;
    }

    public function get_facette_search_compare()
    {
        if (!isset($this->facette_search_compare)) {
            $this->facette_search_compare = new facette_search_compare();
        }
        return $this->facette_search_compare;
    }

    public function get_json_datas()
    {
        $datas = [
                'clicked' => $this->get_clicked(),
                'not_clicked' => $this->get_not_clicked(),
                'facette_plus' => $this->get_facette_plus()
        ];
        return encoding_normalize::json_encode($datas);
    }

    public static function get_compare_notice_active()
    {
        if (!isset(static::$compare_notice_active)) {
            global $opac_compare_notice_active;
            static::$compare_notice_active = $opac_compare_notice_active*1;
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

    public static function set_url_base($url_base)
    {
        static::$url_base = $url_base;
    }

    protected static function format_url($url)
    {
        global $base_path;

        if (!isset(static::$url_base)) {
            static::$url_base = $base_path.'/index.php?';
        }
        if (strpos(static::$url_base, "lvl=search_segment")) {
            return static::$url_base.str_replace('lvl', '&action', $url);
        } else {
            return static::$url_base.$url;
        }
    }

    public static function set_facet_type($type)
    {
        static::$facet_type = $type;
    }
}// end class
