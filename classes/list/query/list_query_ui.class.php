<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_query_ui.class.php,v 1.4 2024/09/14 10:12:55 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_query_ui extends list_ui {
	
    protected static $parameters;
    
    protected static $SQL_query;
    
    protected static $formated_columns;
    
    public static function get_parameters() {
        return null;
    }
    
    public static function set_SQL_query($SQL_query) {
        static::$SQL_query = $SQL_query;
    }
    
    public function __construct($filters=array(), $pager=array(), $applied_sort=array()) {
        if(empty($this->objects_type)) {
            $this->objects_type = str_replace('list_', '', get_class($this)).'_'.md5(static::$SQL_query);
        }
        parent::__construct($filters, $pager, $applied_sort);
    }
    
    protected function _get_query_base() {
        $SQL_query = static::$SQL_query;
        //on d�finit les limites
        $last_limit_position = strripos($SQL_query, ' LIMIT ');
        if($last_limit_position !== false) {
            $last_parenthesis_position = strripos($SQL_query, ')');
            //le dernier limit ne doit pas �tre dans une sous-requ�te
            if($last_parenthesis_position === false || $last_parenthesis_position < $last_limit_position) {
                $SQL_query = substr($SQL_query, 0, $last_limit_position);
            }
        }
        $SQL_array = array();
        preg_match("/(.+)(order by.+)$/isU", $SQL_query, $SQL_array);
        if(count($SQL_array)) {
            $SQL_query = $SQL_array[1];
        }
        return $SQL_query;
    }
    
    /**
	 * Champ(s) du tri SQL
	 */
	protected function _get_query_field_order($sort_by) {
	    if(static::$formated_columns[$sort_by] != str_replace(' ', '', clean_string(static::$formated_columns[$sort_by]))) {
	        //On entourne le tri par des guillements
	        return '"'.static::$formated_columns[$sort_by].'"';
	    }
	    return static::$formated_columns[$sort_by];
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('default', 'align', 'left');
	}
	
	protected function init_default_selected_filters() {
	    if (!empty($this->available_filters['main_fields'])) {
	        foreach ($this->available_filters['main_fields'] as $property=>$label) {
	            $this->add_selected_filter($property, $label);
	        }
	    }
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $arraySql=array();
// 		preg_match('`^(.+)( order by .+)$`i',$sql,$arraySql);
	    preg_match("/(.+)(order by.+)$/isU", static::$SQL_query, $arraySql);
	    if(count($arraySql) && !empty($arraySql[2])) {
	        $order_by = trim(str_replace('order by', '', pmb_strtolower($arraySql[2])));
	        $sorts = explode(',', $order_by);
	        foreach ($sorts as $sort) {
	            $sort = trim($sort);
	            $exploded_sort = explode(' ', $sort);
	            $sort_by = trim($exploded_sort[0]);
	            $sort_asc_desc = (!empty($exploded_sort[1]) ? $exploded_sort[1] : 'asc');
	            if(is_numeric($sort_by)) {
	                $i=1;
	                foreach ($this->available_columns['main_fields'] as $property=>$label) {
	                    if($i == $sort_by) {
	                        $this->add_applied_sort($property, $sort_asc_desc);
	                    }
	                    $i++;
	                }
	            } else {
	                $this->add_applied_sort(static::format_query_property($sort_by));
	            }
	        }
	    } else {
	        if (strpos(pmb_strtoupper(trim(static::$SQL_query)), 'SELECT') === 0) {
                $this->add_applied_sort(array_key_first($this->available_columns['main_fields']));
	        } else {
	            $this->applied_sort = array();
	        }
	    }
	}
	
	/**
	 * Initialisation de la pagination par d�faut
	 */
	protected function init_default_pager() {
	    parent::init_default_pager();
	    $this->pager['nb_per_page'] = 25;
	}
	
	/**
	 * Affichage d'un filtre du formulaire de recherche
	 */
	public function get_search_filter_form($property, $label, $delete_is_allow=false) {
	    global $aff_list;
	    
	    $search_filter_form = "
				<div class='colonne3'>
					<div class='row'>";
	    
	    $hp = static::get_parameters();
	    //Affichage des champs
	    $champ_focus="";//nom du champ o� l'on va mettre le focus
	    if (!empty($hp->query_parameters)) {
	        for ($i=0; $i<count($hp->query_parameters); $i++) {
	            $name=$hp->query_parameters[$i];
	            if ($name == $property) {
	                $search_filter_form .= "
    						<label class='etiquette'>".$label."</label>
    					</div>
    					<div class='row'>";
	                $champ_type=$hp->get_field_type($hp->parameters_description[$name]);
	                if(!$champ_focus && ($champ_type == "text")) {
	                    $champ_focus=$name;//en priorit� le premier champ texte
	                }
	                eval("\$aff=".$aff_list[$champ_type]."(\$hp->parameters_description[\$name],\$check_scripts);");
	                $search_filter_form .= $aff."
                        </div>
    				</div>";
	            }
	        }
	    }
	    return $search_filter_form;
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
	    $hp = static::get_parameters();
	    if (!empty($hp->query_parameters)) {
	        for ($i=0; $i<count($hp->query_parameters); $i++) {
	            $name=$hp->query_parameters[$i];
	            global ${$name};
	            $val=${$name};
	            if (isset($val)) {
	                $this->filters[$name] = $val;
	            }
	        }
	    }
		parent::set_filters_from_form();
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
	    $this->available_filters['main_fields'] = array();
	    $this->available_filters['custom_fields'] = array();
	    
	    $hp = static::get_parameters();
	    if (!empty($hp->query_parameters)) {
	        for ($i=0; $i<count($hp->query_parameters); $i++) {
	            $name=$hp->query_parameters[$i];
	            $this->available_filters['main_fields'] [$name] = $hp->get_field_alias($hp->parameters_description[$name]);
	        }
	    }
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
	    $this->available_columns['main_fields'] = array();
	    $result = pmb_mysql_query($this->_get_query());
		$nbr_champs = pmb_mysql_num_fields($result);
		for($i=0; $i < $nbr_champs; $i++) {
		    $field_name = pmb_mysql_field_name($result, $i);
		    $this->available_columns['main_fields'][static::format_query_property($field_name)] = $field_name;
		}
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
	    if (!empty($this->available_columns['main_fields'])) {
	        foreach ($this->available_columns['main_fields'] as $property=>$label) {
	            $this->add_column($property, $label);
	        }
	    }
	}
	
	protected function _get_query_human_main_field($property, $label) {
	    if(!empty($this->filters[$property])) {
	        $hp = static::get_parameters();
	        if (!empty($hp->query_parameters)) {
	            for ($i=0; $i<count($hp->query_parameters); $i++) {
	                $name=$hp->query_parameters[$i];
	                if ($name == $property) {
	                    $field = $hp->parameters_description[$name];
	                    $field_type = $hp->get_field_type($field);
	                    switch ($field_type) {
	                        case 'query_list':
	                            $query = $field['OPTIONS'][0]['QUERY'][0]['value'];
	                            if (!empty($query)) {
	                                $values = array();
	                                $result = pmb_mysql_query($query);
	                                while ($row=pmb_mysql_fetch_row($result)) {
	                                    if((is_array($this->filters[$property]) && in_array($row[0], $this->filters[$property])) || $this->filters[$property] == $row[0]) {
	                                        $values[] = $row[1];
	                                    }
	                                }
	                                return $this->_get_label_query_human($label, $values);
	                            } else {
	                                return $this->_get_label_query_human($label, $this->filters[$property]);
	                            }
	                            break;
	                        default:
	                            return $this->_get_label_query_human($label, $this->filters[$property]);
	                    }
	                }
	            }
	        }
	    }
	}
	
	/**
	 * Contenu d'une colonne
	 * @param object $object
	 * @param string $property
	 */
	protected function get_cell_content($object, $property) {
	    global $charset;
	    
	    //html_entity_decode = Contournement de la mecanique pour conserver l'interpretation du HTML
	    return html_entity_decode(parent::get_cell_content($object, static::$formated_columns[$property]), ENT_QUOTES, $charset);
	}
	
	public static function format_query_property($property) {
	    $formated_column = str_replace(' ', '_', strip_empty_chars(convert_diacrit($property)));
	    if(!isset(static::$formated_columns[$formated_column])) {
	        static::$formated_columns[$formated_column] = $property;
	    }
	    return $formated_column;
	}
	
}