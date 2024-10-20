<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_handler.class.php,v 1.43 2024/05/27 13:02:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/onto/onto_ontology.class.php");
require_once($class_path."/onto/common/onto_common_item.class.php");
require_once($class_path."/onto/onto_store.class.php");


/**
 * class onto_handler
 * 
 */
class onto_handler {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/

	/**
	 * 
	 * @access protected
	 */
	protected $ontology;

	/**
	 * Store pour l'ontologie
	 * 
	 * @var onto_store
	 * 
	 * @access protected
	 */
	protected $onto_store;

	/**
	 * Store pour les donnn�es
	 * 
	 * @var onto_store
	 * 
	 * @access private
	 */
	protected $data_store;
	
	protected $default_display_label;
	
	private static $item_instances = array();
	
	private $nb_elements = array();
	
	private $errors = array();

	private static $pmb_names = [
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => 'type',
	];

	public $labels;
	
	/**
	 * 
	 *
	 * @param string ontology_filepath 
	 * @param string onto_store_type nom de la classe store � utiliser pour l'ontologie
	 * @param array() onto_store_config Configuration du store pour l'ontologie
	 * @param string data_store_type Nom de la classe � utiliser pour le store data
	 * @param Array() data_store_config Configuration du store data
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function __construct( $ontology_filepath,  $onto_store,  $onto_store_config,  $data_store_type,  $data_store_config,$tab_namespaces ,$default_display_label) {
			
		if (is_object($onto_store)) {
			$this->onto_store = $onto_store;
		} else {
			//on r�cup�re les stores...
			$onto_store_class = "onto_store_".$onto_store;
			$this->onto_store = new $onto_store_class($onto_store_config);
			$this->onto_store->set_namespaces($tab_namespaces);
			//chargement de l'ontologie dans son store
			if($ontology_filepath){
				$this->onto_store->load($ontology_filepath);
			}
		}
		$data_store_class = "onto_store_".$data_store_type;
		$this->data_store = new $data_store_class($data_store_config);
		$this->data_store->set_namespaces($tab_namespaces);
		
		$this->default_display_label=$default_display_label;
	} // end of member function __construct

	/**
	 * PARTIE DATASTORE
	 */
	
	/**
	 * revoie les assertion � inserer pour un item
	 *
	 * @param string $uri
	 *
	 * @return array
	 */
	public function get_assertions($uri){
		$assertions = array();
		
		if (empty($uri)) {
		    return $assertions;
		}
		
		$query = "select * where {
			<".$uri."> ?predicate ?object .
			optional {
				?object rdf:type ?type
			}.
			optional {
				?object pmb:has_assertions ?has_assertions.
			}
		}";
		$this->data_store->query($query);
		$results = $this->data_store->get_result();
		foreach($results as $assertion){
			$object_properties = array();
			foreach($assertion as $key=>$value){
				if(substr($key,0,strlen("object_")) == "object_"){
					$object_properties[substr($key,strlen("object_"))] = $value;
				}
			}
			if($object_properties['type'] == "literal"){
				$type = "http://www.w3.org/2000/01/rdf-schema#Literal";
			}else{
			    $type = (isset($assertion->type) ? $assertion->type : "");
				if(!$type){
					$type = $assertion->predicate;
				}else{
					$displayLabel=$this->get_display_label($assertion->type);
					$query="select ?display_label where {
						<".$assertion->object."> <".$displayLabel."> ?display_label
					}";
					$this->data_store->query($query);
					if($this->data_store->num_rows()){
						$result = $this->data_store->get_result();
						$object_properties['display_label'] = $result[0]->display_label;
					} else {//cas particulier pour les assertions qui auraient directement un displayLabel
						$query="select ?display_label where {
									<".$assertion->object."> pmb:displayLabel ?display_label
								}";
						$this->data_store->query($query);
						if($this->data_store->num_rows()){
							$result = $this->data_store->get_result();
							$object_properties['display_label'] = $result[0]->display_label;
						}
					}
				}
				if (!empty($assertion->has_assertions)) {
				    $object_properties["assertions"] = $this->get_assertions($assertion->object);
				}				
			}
			$assertions[] = new onto_assertion($uri, $assertion->predicate, $assertion->object, $type,$object_properties);
		}
		return $assertions;
	}
	
	/**
	 * Fonction d'acc�s aux requetes sparql dans le data store
	 *
	 * @param string $query
	 *
	 */
	public function data_query($query){
	    $this->data_store->query($query);
	    $errs = $this->data_store->get_errors();
		if (!empty($errs)) {
		    if ($this->get_onto_name() == "contribution") {
		        global $msg;
		        
		        // Erreur lors de l'enregistrement
		        $report = array();
		        $report["error"] = [
		            "type" => "sparql",
		            "message" => $msg['contribution_error_save'],
		            "errors" => $errs
		        ];
		        return $report;
		    } else {
		        print "<br>Erreurs: <br>";
		        print "<pre>";print_r($query);print "<br/>";print_r($errs);print "</pre><br>";
		    }
		}
		return true;
	}
	
	/**
	 * Fonction d'acc�s aux requetes sparql dans le data store
	 * renvoi le r�sultat
	 *
	 * @return array result
	 */
	public function data_result(){
		if($this->data_store->num_rows()){
			return $this->data_store->get_result();
		}elseif ($errs = $this->data_store->get_errors()) {
			print "<br>Erreurs: <br>";
			print "<pre>";print_r($errs);print "</pre><br>";
		}
		return false;
	}
	
	/**
	 * Fonction d'acc�s aux requetes sparql dans le data store
	 * renvoi le nombre de r�sultat
	 *
	 * @return integer num rows
	 */
	public function data_num_rows(){
		if($this->data_store->num_rows()){
			return $this->data_store->num_rows();
		}elseif ($errs = $this->data_store->get_errors()) {
			print "<br>Erreurs: <br>";
			print "<pre>";print_r($errs);print "</pre><br>";
		}
		return false;
	}
	
	public function get_data_label($uri){
		global $lang;
	
		$displayLabel=$this->get_display_label($uri);
	
		$query = "select * where {
			<".$uri.">  <".$displayLabel."> ?label
		}";
		$this->data_store->query($query);
	
		if($this->data_store->num_rows()){
			$results = $this->data_store->get_result();
			foreach($results as $result){
				if(isset($result->label_lang) && $result->label_lang==substr($lang,0,2)){
					return $result->label;
				}
			}
			//pas de langue de l'interface trouv�e
			foreach($results as $result){
				return $result->label;
			}
		}
	
	}

	
	public function get_nb_elements($class_uri,$more=""){
		
		if(!$this->nb_elements[$class_uri.$more]){
			$query="";
			$query.="select count(?elem) as ?nb_elem where {
				?elem rdf:type <".$class_uri."> .";
			$displayLabel=$this->get_display_label($class_uri);
			if ($displayLabel) {
			    $query .= "?elem <".$displayLabel."> ?label ";
			}
			if($more){
				if(substr(trim($more),0,1) == '.'){
					$query.=$more;
				}else{
					$query.=' . '.$more;
				}
				
			}
			$query.="}";
			$this->data_store->query($query);
			$results = $this->data_store->get_result();
			$this->nb_elements[$class_uri.$more] = $results[0]->nb_elem;
		}
		return $this->nb_elements[$class_uri.$more];
	}
	

	/**
	 * Supprime et recr�e les d�clarations de l'instance pass�e en param�tre
	 *
	 * @param onto_common_item $item Instance � sauvegarder
	 * 
	 * @return bool
	 * 
	 * @access public
	 */
	public function save( $item , $draft = false ) {
		global $area_id;		
		
		if ($item->check_values() || $draft) {	
			if(onto_common_uri::is_temp_uri($item->get_uri()) && !$draft){
			    $item_tmp_uri = $item->get_uri();
				$item->replace_temp_uri();
				$this->replace_tmp_uri_store($item_tmp_uri, $item->get_uri());
				$this->deleteDraft($item_tmp_uri);
			}
			$assertions = $item->get_assertions();
			
			// On peut y aller
			$query = "insert into <pmb> {";
			$query .= $this->build_triples($assertions, $item->get_uri());
			$query .= ".\n <".addslashes($assertions[0]->get_subject())."> pmb:area ".$area_id;
			
			//on ne rentre qu'une seule, afin de ne pas �craser le display label
			if($assertions[0]->get_object_properties()['type'] == "uri") {					
			    $display_label = $item->get_label($this->get_display_label($assertions[0]->get_object()));
				
				//si pas de display label, on va chercher celui du parent
				if (!$display_label) {
				    $sub_class_of = $this->ontology->get_sub_class_of($assertions[0]->get_object());
					foreach ($sub_class_of as $parent_uri) {							
						$display_label = $item->get_label($this->get_display_label($parent_uri));
						if ($display_label) {
							break;
						}
					}
				}					
				$query .= " .\n <".addslashes($assertions[0]->get_subject())."> pmb:displayLabel '".addslashes($display_label)."'";
			}
			
			$additionnal_data = $item->get_additionnal_data();
			if (!empty($additionnal_data)) {
			    $query .= ".\n <".addslashes($assertions[0]->get_subject())."> pmb:additionnal_data '".addslashes(json_encode($additionnal_data))."'";
			}
			
			$query.="}";
			
			$this->data_store->query($query);
			$errs = $this->data_store->get_errors();
			if (!empty($errs)) {
			    if ($this->get_onto_name() == "contribution") {
			        global $msg;
			        
			        // Erreur lors de l'enregistrement
			        $report = array();
			        $report[$item->get_uri()] = [
			            "type" => "sparql",
			            "message" => $msg['contribution_error_save'],
			            "errors" => $errs
			        ];
			        return $report;
			    } else {
    				print "<br/>Erreurs: <br/>";
    				print "<pre>";print_r($errs);print "</pre><br/>";
			    }
			} else {
			    $item->post_save();
			    //TODO: a reprendre plus tard si besoin (indexation des contribution par exemple...)
			    if ($this->get_onto_name() == "skos") {
    				$index = new onto_index();
    				$index->set_handler($this);
    				$index->maj(0,$item->get_uri());
			    }
			}
		} else {
			return $item->get_checking_errors();
		}
		$this->data_store->reset_after_save();
		
		return true;
	} // end of member function save

	
	
	/**
	 * D�truit une instance (l'ensemble de ses d�clarations)
	 *
	 * @param onto_common_item $item Instance � supprimer (l'ensemble de ses d�clarations)
	 * @param bool $force_delete Si false, renvoie un tableau des assertions o� l'item est objet. Si true, supprime toutes les occurences de l'item
	 * 
	 * @return bool
	 * @access public
	 */
	public function delete($item, $force_delete = false) {
	    global $dbh, $ajax;
		
		// On stockera dans un tableau tous les triplets desquels l'item est l'objet
		$is_object_of = array();
		
		$query = "select * where {
			?subject ?predicate <".$item->get_uri().">
		}";
		$this->data_store->query($query);
		$result = $this->data_store->get_result();
		
		foreach ($result as $assertion) {
			$is_object_of[] = new onto_assertion($assertion->subject, $assertion->predicate, $item->get_uri());
		}
		
		$length = count($is_object_of);
		if ($force_delete || !$length) {
		    
		    if ($this->get_onto_name() == "contribution") {
		        // On supprime toutes les contributions li�s
		        $contribution_linked_deleted = $this->delete_linked_contribution_from_uri($item->get_uri());
		        for ($i = 0; $i < $length; $i++) {
		            if (!empty($is_object_of[$i]) && !empty($is_object_of[$i]->get_subject()) && in_array($is_object_of[$i]->get_subject(), $contribution_linked_deleted)) {
		                array_splice($is_object_of, $i, 1);
		            }
		        }
		    }
		    
    		$success = $this->data_store->query("delete {
				<".$item->get_uri()."> ?prop ?obj
			}");
			
    		if (!$success) {
    		    if ($ajax) {
    		        $this->errors[] = $this->data_store->get_errors();
    		    } else {
    				print "<br>Erreurs: <br>";
    				print "<pre>";print_r($this->data_store->get_errors());print "</pre><br>";
    		    }
			} else {
			    $success = $this->data_store->query("delete {
					?subject ?predicate <".$item->get_uri().">
				}");
			    if (!$success) {
			        if ($ajax) {
			            $this->errors[] = $this->data_store->get_errors();
			        } else {
			            print "<br>Erreurs: <br>";
			            print "<pre>";print_r($this->data_store->get_errors());print "</pre><br>";
			        }
				} else {
				    
					// On met � jour l'index
					//TODO: a reprendre plus tard si besoin (indexation des contribution par exemple...)
					if ($this->get_onto_name() == "skos") {
					    $index = new onto_index();
					    $index->set_handler($this);
					    $index->maj(0,$item->get_uri());
					    if ($length) {
    						foreach ($is_object_of as $object) {
    						    $index->maj(0,$object->subject);
    						}
    					}
					}
					
					//on a tout vir� on supprime aussi l'URI dans la table
					$query = "delete from onto_uri where uri = '".$item->get_uri()."'";
					pmb_mysql_query($query, $dbh);
				}
			}
		}
		return $is_object_of;
	} // end of member function delete
	
	/**
	 * PARTIE DATASTORE
	 */
	
	
	/**
	 * Retourne l'item le plus appropri� pour d�finir l'URI pass�e en param�tre
	 *
	 * @param string class_uri URI de la classe de l'ontologie � instancier
	 * @param string uri URI de l'instance � cr�er
	 *
	 * @return onto_common_item $item
	 *
	 * @access public
	 */
	public function get_item($class_uri,$uri) {
		$item_class = "onto_".$this->ontology->name."_".$this->get_class_pmb_name($class_uri)."_item";
		if(!class_exists($item_class)){
			$item_class = "onto_".$this->ontology->name."_item";
		}
		if(!class_exists($item_class)){
			$item_class = "onto_common_item";
		}
		$item = new $item_class($this->ontology->get_class($class_uri),$uri);
		$item->set_assertions($this->get_assertions($uri));
		if(!$uri){
			//pas d'uri, on instancie les assertions par d�faut...
			$assertions = array();
			foreach($this->ontology->get_class_properties($class_uri) as $uri_property){
				$property=$this->ontology->get_property($class_uri,$uri_property);
				if(count($property->default_value)){
					global ${$property->default_value['value']};
					if(isset(${$property->default_value['value']})){
						$assertions[] = new onto_assertion($item->get_uri(),$uri_property,onto_common_uri::get_uri(${$property->default_value['value']}),$property->range[0], array('type' => "uri",'display_label' => $this->get_data_label(onto_common_uri::get_uri(${$property->default_value['value']}))));
					}
				}
			}
			if(count($assertions)){
				$item->set_assertions($assertions);
			}
		}
		
		$query = "select ?additionnal_data where {
			<{$item->get_uri()}> pmb:additionnal_data ?additionnal_data .
		}";
		
		$success = $this->data_store->query($query);
		if ($success) {		    
    		$results = $this->data_store->get_result();
    		if (!empty($results[0])) {
    		    $additionnal_data = json_decode($results[0]->additionnal_data, true);
    		    $item->set_additionnal_data($additionnal_data ?? []);
    		}
		}
		
		self::$item_instances[$item->get_uri()] = $item;
		
		return $item;
	} // end of member function get_item
	
	
	/**
	 * PARTIE ONTOLOGIE
	 */
	
	
	/**
	 * retourne les uri des classes de l'ontologie
	 * 
	 * @return array
	 */
	public function get_classes(){
	    if (!isset($this->ontology)) {
            $this->get_ontology();
        }
		return $this->ontology->get_classes_uri();
	}
	
	
	/**
	 * Retourne le nom de la classe ontologie en fonction de son uri
	 *
	 * @param string $uri_class
	 */
	public function get_class_label($uri_class){	
	    if (!isset($this->ontology)) {
            $this->get_ontology();
        }
		return $this->ontology->get_class_label($uri_class);
	}
	
	/**
	 * Renvoie le premier nom de classe de l'ontologie (choisi par d�faut)
	 * 
	 * @return string
	 */
	public function get_first_ontology_class_name(){
		$classes = $this->get_classes();
		reset($classes);
		return current($classes)->pmb_name;
	}
	
	/**
	 * Renvoie l'uri d'une classe en fonction de son nom pmb
	 * 
	 * @param string $class_name
	 */
	public function get_class_uri($class_name){
		$classes = $this->get_classes();
		$class_uri = "";
		foreach($classes as $class){
			if($class->pmb_name == $class_name){
				$class_uri = $class->uri;
				break;
			}
		}
		return $class_uri;
	}
	
	/**
	 * Renvoie le nom PMB d'une classe en fonction de son uri
	 * 
	 * @param string $class_uri
	 */
	public function get_class_pmb_name($class_uri){
		$classes = $this->get_classes();
		$class_pmb_name = "";
		foreach($classes as $class){
			if($class->uri == $class_uri){
				$class_pmb_name = $class->pmb_name;
				break;
			}
		}
		return $class_pmb_name;
	}
	
	/**
	 * Renvoi le titre de l'ontologie
	 * 
	 * @return string
	 */
	public function get_title(){
		if (!isset($this->ontology)) {
			$this->get_ontology();
		}
		return $this->ontology->title;
	}
	
	/**
	 * renvoie le nom de l'ontologie
	 * 
	 * @return string
	 */
	public function get_onto_name(){
		if (!isset($this->ontology)) {
			$this->get_ontology();
		}
		return $this->ontology->name;
	}
	
	/**
	 * Instancie et renvoie la valeur labels
	 * Contient les libell�s des mots pr�sents dans le data_store
	 * 
	 * @return array
	 */
	public function get_labels(){
		if(!isset($this->labels) || !$this->labels){		
			$this->labels = array();
			$query="select * where {
				?uri pmb:name ?name .
				?uri rdfs:label ?label .
				optional {
					?uri pmb:displayLabel ?displayLabel .
					?uri pmb:searchLabel ?searchLabel
				}
			}";
			
			$this->onto_store->query($query);
			$results = $this->onto_store->get_result();
			foreach($results as $result){
				$this->labels[$result->name]['uri'] = $result->uri;
				
				$this->labels[$result->name]['name'] = $result->name;
				
				if(isset($result->displayLabel) && $result->displayLabel){
					$this->labels[$result->name]['displayLabel'] = $result->displayLabel;
				}
				
				if(isset($result->searchLabel) && $result->searchLabel){
					$this->labels[$result->name]['searchLabel'] = $result->searchLabel;
				}
				
				if(!isset($labels[$result->name]['label']['default'])){
					$this->labels[$result->name]['label']['default'] = $result->label;
				}
				if (!empty($result->label_lang)) {
				    $this->labels[$result->name]['label'][$result->label_lang] = $result->label;
				}
			}
		}
		return $this->labels;
	}

	
	public function get_display_label($class_uri){
		$query = "select ?displayLabel where {
			<".$class_uri."> pmb:displayLabel ?displayLabel
		}";
		$this->onto_store->query($query);
		$displayLabel = $this->default_display_label;
		if($this->onto_store->num_rows()){
			$result = $this->onto_store->get_result();
			$displayLabel = $result[0]->displayLabel;
		}
		return $displayLabel;
	}
	
	/**
	 * Renvoie un libell� en fonction du nom ou de l'uri
	 * 
	 * @param string $name
	 */
	public function get_label($name){
		global $msg,$lang;
		$label= "";
		
		//@todo recherche SPARQL sur un libelle?
		if(!isset($this->labels) || !$this->labels){
			$this->get_labels();
		}
		
		foreach($this->labels as $key => $infos){
			if($name == $key || $name == $infos['uri']){
				if(isset($msg['onto_'.$this->get_onto_name().'_'.$infos['name']])){
					//le message PMB sp�cifique pour l'ontologie courante
					$label = $msg['onto_'.$this->get_onto_name().'_'.$infos['name']];
				}else if (isset($msg['onto_common_'.$infos['name']])){
					//le message PMB g�n�rique
					$label = $msg['onto_common_'.$infos['name']];
				}else if (isset($infos['label'][substr($lang,0,2)])){
					//le label de l'ontologie dans la langue de l'interface
					$label = $infos['label'][substr($lang,0,2)];
				}else{
					//le label g�n�rique de l'ontologie
					$label = $infos['label']['default'];
				}
				break;
			}
		}
	
		return $label;
	}
	
	/**
	 * Renvoie les propri�t�s en fonction d'un nom de classe pmb
	 * 
	 * @param string $pmb_name
	 * 
	 * @return array
	 */
	public function get_onto_property_from_pmb_name($pmb_name) {
		if (!isset($this->ontology)) {
			$this->get_ontology();
		}
		$properties_uri = $this->ontology->get_properties();
		foreach ($properties_uri as $uri => $info) {
			if ($info->pmb_name == $pmb_name) {
				return $this->ontology->get_property("", $uri);
			}
		}
	}
	
	
	/**
	 * Retourne une instance de l'ontologie charg�e � partir de onto_store
	 *
	 * @return onto_ontology
	 *
	 * @access public
	 */
	public function get_ontology() {
		if(!isset($this->ontology )){
			$this->ontology = new onto_ontology($this->onto_store);
		}
		$this->ontology->set_data_store($this->data_store);
		return $this->ontology;
	} // end of member function get_ontology
	
	
	/**
	 * Fonction d'acc�s aux requetes sparql dans l'onto store
	 *
	 * @param string $query
	 *
	 */
	public function onto_query($query){
		$this->onto_store->query($query);
		if($this->onto_store->num_rows()){
			return true;
		}elseif ($errs = $this->onto_store->get_errors()) {
			print "<br>Erreurs: <br>";
			print "<pre>";print_r($errs);print "</pre><br>";
		}
		return false;
	}
	
	/**
	 * Fonction d'acc�s aux requetes sparql dans l'onto store
	 * renvoi le r�sultat
	 *
	 * @return array result
	 */
	public function onto_result(){
		if($this->onto_store->num_rows()){
			return $this->onto_store->get_result();
		}elseif ($errs = $this->onto_store->get_errors()) {
			print "<br>Erreurs: <br>";
			print "<pre>";print_r($errs);print "</pre><br>";
		}
		return false;
	}
	
	/**
	 * Fonction d'acc�s aux requetes sparql dans l'onto store
	 * renvoi le nombre de r�sultat
	 *
	 * @return integer num rows
	 */
	public function onto_num_rows(){
		if($this->onto_store->num_rows()){
			return $this->onto_store->num_rows();
		}elseif ($errs = $this->onto_store->get_errors()) {
			print "<br>Erreurs: <br>";
			print "<pre>";print_r($errs);print "</pre><br>";
		}
		return false;
	}
	
	/**
	 * 
	 * @return onto_store
	 */
	public function get_data_store() {
		return $this->data_store;
	}
	
	/**
	 * recupere les uri des proprietes qui composent le display_label
	 * @param string $class_uri uri
	 * @return NULL[]
	 */
	public function get_display_labels($class_uri){
	    $query = "select ?displayLabel where {
			<".$class_uri."> pmb:displayLabel ?displayLabel
		}";
	    $this->onto_store->query($query);
	    $displayLabels = [$this->default_display_label];
	    if($this->onto_store->num_rows()){
	        $displayLabels = [];
	        $results = $this->onto_store->get_result();
	        foreach ($results as $result) {
	            $displayLabels[] = $result->displayLabel;
	        }
	    }
	    return $displayLabels;
	}
	/**
	 * PARTIE ONTOLOGIE
	 */
	
	private function build_triples($assertions, $main_uri) {
	    global $opac_url_base;
	    
	    $nb_assertions = count($assertions);
	    $i = 0;
	    
	    $subjects_deleted = array();
	    
	    // On peut y aller
	    $query = "";
	    foreach ($assertions as $assertion) {
	        if (!in_array($assertion->get_subject(), $subjects_deleted)) {
	            $pmb_id = 0;
	            
	            //on stocke l'id de l'entit� en base SQL s'il existe
	            $query_pmb_id = '	select ?pmb_id where {
						<'.$assertion->get_subject().'> pmb:identifier ?pmb_id
					}';
	            $this->data_store->query($query_pmb_id);
	            if ($this->data_store->num_rows()) {
	                $pmb_id = $this->data_store->get_result()[0]->pmb_id;
	            }
	            
	            // On supprime tous les triplets correspondant � cette uri pour les mettre � jour par la suite
	            if ($assertion->get_subject() == $main_uri) {
	                $query_delete = "delete {
    						<".$assertion->get_subject()."> ?prop ?obj
    						}";
	                $this->data_store->query($query_delete);
	                
	                $subjects_deleted[] = $assertion->get_subject();
	            } else {
	                $query_delete = "delete {
    						<".$assertion->get_subject()."> <".$assertion->get_predicate()."> <".$assertion->get_object().">
    						}";
	                $this->data_store->query($query_delete);
	            }
	            
	            //puis on commence par r�-ins�rer l'id de l'entit� en base SQL dans le store
	            if ($pmb_id) {
	                if (!$this->data_store->num_rows()) {
	                    $query_insert = 'insert into <pmb> {
									<'.$assertion->get_subject().'> pmb:identifier "'.$pmb_id.'" .
								}';
	                    $this->data_store->query($query_insert);
	                }
	            }
	        }
	        
	        if ($assertion->offset_get_object_property("type") == "literal"){
	            $object = "'".addslashes($assertion->get_object())."'";
	            $object_properties = $assertion->get_object_properties();
	            if (!empty($object_properties['lang'])) {
	                $object.="@".$object_properties['lang'];
	            }
	        }else{
	            
	            if (empty($assertion->get_object())) {
	                // Aucune uri
	                $object = "''";
	            } else {
    	            $object = "<".addslashes($assertion->get_object()).">";
    	            if ($assertion->offset_get_object_property("type") == "uri"){
    	                
    	                if ($assertion->get_object_type()) {
    	                    if (is_numeric($assertion->get_object())) {
    	                        $query_bis = "	select ?uri where {
    													?uri pmb:identifier '".addslashes($assertion->get_object())."' .
    													?uri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <".addslashes($assertion->get_object_type()).">
    												}";
    	                        $this->data_store->query($query_bis);
    	                        if (!$this->data_store->num_rows()) {
    	                            
    	                            $uri = "<".addslashes(onto_common_uri::get_new_uri($this->get_class_pmb_name($assertion->get_object_type()),$opac_url_base.$this->get_class_pmb_name($assertion->get_object_type())."#")).">";
    	                            $object = $uri;
    	                            
    	                            $object .= " .\n";
    	                            //sujet
    	                            $object .= $uri;
    	                            //pr�dicat
    	                            $object .= ' pmb:identifier ';
    	                            //objet
    	                            $object .= '"'.addslashes($assertion->get_object()).'"';
    	                            
    	                            $object .= " .\n";
    	                            //sujet
    	                            $object .= $uri;
    	                            //pr�dicat
    	                            $object .= ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ';
    	                            //objet
    	                            $object .= '<'.addslashes($assertion->get_object_type()).'>';
    	                            
    	                            if ($assertion->offset_get_object_property('display_label')) {
    	                                $object .= " .\n";
    	                                //sujet
    	                                $object .= $uri;
    	                                //pr�dicat
    	                                $object .= ' pmb:displayLabel ';
    	                                //objet
    	                                $object .= '"'.addslashes($assertion->offset_get_object_property('display_label')).'"';
    	                            }
    	                            $uri = "";
    	                        } else {
    	                            $uri = $this->data_store->get_result()[0]->uri;
    	                            $object = "<".$uri.">";
    	                        }
    	                    }
    	                    if ($assertion->offset_get_object_property('object_assertions')) {
    	                        
    	                        $object .= " .\n";
    	                        //sujet
    	                        $object .= '<'.addslashes($assertion->get_object()).'>';
    	                        //pr�dicat
    	                        $object .= ' pmb:has_assertions ';
    	                        //objet
    	                        $object .= '"1"';
    	                        
    	                        $object .= " .\n";
    	                        //sujet
    	                        $object .= '<'.addslashes($assertion->get_object()).'>';
    	                        //pr�dicat
    	                        $object .= ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ';
    	                        //objet
    	                        $object .= "<".addslashes($assertion->get_object_type())."> .\n";
    	                                                
    	                        $object .= $this->build_triples($assertion->offset_get_object_property('object_assertions'),$assertion->get_object());
    	                    } else {
    	                        
    	                        // On essaie de recuperer le display label supprime suite � une purge des stores
    	                        if (!is_numeric($assertion->get_object())) {
    	                            $uri = addslashes($assertion->get_object());
    	                        }
    	                        
    	                        if (!empty($uri)) {
                                    $query_bis = "select ?displayLabel where {
                    	                               <".$uri."> pmb:displayLabel ?displayLabel .
                                                    }";
                                    
                                    $this->data_store->query($query_bis);
                                    if (!$this->data_store->num_rows()) {
                                        if ($assertion->offset_get_object_property('display_label')) {
                                            $object .= " .\n";
                                            //sujet
                                            $object .= "<".$uri.">";
                                            //pr�dicat
                                            $object .= ' pmb:displayLabel ';
                                            //objet
                                            $object .= '"'.addslashes($assertion->offset_get_object_property('display_label')).'"';
                                        }
                                    }
    	                        }
    	                    }
    	                }
    	            }
	            }
	        }
	        $query.= " <".addslashes($assertion->get_subject())."> <".addslashes($assertion->get_predicate())."> ".$object;
	            	        
	        $i++;
	        if ($i < $nb_assertions) {
	            $query.=" .\n";
	        }
	        
	        //$query.=" \n";
	    }
	    return $query;
	}
	
	/**
	 * Remplacer dans le store l'uri temporaire par une nouvelle uri
	 * @param string $item_tmp_uri uri temporaire.
	 * @param string $item_new_uri nouvelle uri
	 */
	private function replace_tmp_uri_store($item_tmp_uri, $item_new_uri)
	{
	    // On r�cup�re les items qui ont l'uri Temporaire
	    $query_select = "select * where {
                ?s ?p <$item_tmp_uri> .
			}";
	    $this->data_store->query($query_select);
	    
	    $result = $this->data_store->get_result();
	    if (!empty($result)) {
	    
    	    // On supprime les items qui ont l'uri Temporaire
            $query_delete = "delete {
                    ?s ?p <$item_tmp_uri>
                }";
            $this->data_store->query($query_delete);
            
            
            // On cr�er les items avec la nouvelle uri
    	    $query_insert = "insert into <pmb> { ";
    	    foreach ($result as $item) {
    	        $query_insert .= "<$item->s> <$item->p> <$item_new_uri> .";
    	    }
    	    $query_insert.=" }";
    	    
    	    $this->data_store->query($query_insert);
	    }
	}	
	
	private function deleteDraft($item_tmp_uri)
	{
	    if(onto_common_uri::is_temp_uri($item_tmp_uri)){
            $query_delete = "delete {
                    <$item_tmp_uri> ?p ?o . 
                }";
            $this->data_store->query($query_delete);
	    }
	}
	
	private function delete_linked_contribution_from_uri($item_uri)
	{
	    $query = "select * where {
			<".$item_uri."> ?predicate ?object
		}";
	    
	    $contribution_linked_deleted = [];
	    
	    if ($this->data_store->query($query)) {
    	    $results = $this->data_store->get_result();
    	    foreach ($results as $result) {
    	        if ($result->object_type == "uri" && $result->predicate != "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" && !empty($result->object)) {
    	            
    	            $success = false;
    	            
    	            $query_success = $this->data_store->query('select * where {
            			<'.$result->object.'> pmb:sub_form ?object
                    }');
    	            if (!$query_success || ($query_success && $this->data_store->num_rows() == 0)) {
    	                // On supprime que les sous-formulaires
    	                continue;
    	            }
    	            switch (TRUE) {
    	                // Author - Responsability
    	                case (strpos($result->object, "responsability") !== FALSE) :
    	                    $success = $this->delete_linked_contribution_specific($result->object, "responsability");
    	                    break;
	                    // Work - Linked work
    	                case (strpos($result->object, "linked_work") !== FALSE) :
    	                    $success = $this->delete_linked_contribution_specific($result->object, "linked_work");
    	                    break;
	                    // Record - Linked record
    	                case (strpos($result->object, "linked_record") !== FALSE) :
    	                    $success = $this->delete_linked_contribution_specific($result->object, "linked_record");
    	                    break;
	                    // authority - Linked authority
    	                case (strpos($result->object, "linked_authority") !== FALSE) :
    	                    $success = $this->delete_linked_contribution_specific($result->object, "linked_authority");
    	                    break;
    	                default:
                            $success = $this->delete_linked_contribution($result->object, $item_uri);
    	                    break;
    	            }
    	            if ($success) {
    	                $contribution_linked_deleted[] = $result->object;
    	            }
    	        }
    	    }
	    }
	    return $contribution_linked_deleted;
	}
	
	private function delete_linked_contribution($linked_uri, $parent_uri)
	{
	    global $ajax;
	    
	    if (empty($linked_uri) || empty($parent_uri)) {
	        return FALSE;
	    }
	    
	    $success = $this->data_store->query('select * where {
			?subject ?predicate <'.$linked_uri.'> .
            filter ( ?subject != "'.$parent_uri.'")
        }');
	    
	    if ($success) {
	        $results = $this->data_store->get_result();
	        if (!count($results)) {
	            $success = $this->data_store->query('delete {
                    <'.$linked_uri.'> ?predicate ?object .
                }');
	            
	            if (!$success) {
	                if ($ajax) {
	                    $this->errors[] = $this->data_store->get_errors();
	                } else {
	                    print "<br>Erreurs: <br>";
	                    print "<pre>";print_r($this->data_store->get_errors());print "</pre><br>";
	                }
	            } else {
    	            $query = "delete from onto_uri where uri = '".$linked_uri."'";
    	            pmb_mysql_query($query);
    	            return TRUE;
	            }
	        }
	    }
        return FALSE;
	}
	
	private function delete_linked_contribution_specific(string $linked_uri, string $type)
	{
	    global $ajax;
	    
	    if (empty($linked_uri) || empty($type)) {
	        return FALSE;
	    }
	    
	    $predicate = "";
	    switch ($type) {
	        
	        case "responsability":
        	    $predicate = "pmb:has_author";
    	        break;
	        
	        case "linked_work":
        	    $predicate = "pmb:has_work";
    	        break;
    	        
	        case "linked_record":
        	    $predicate = "pmb:has_record";
    	        break;
	        case "linked_authority":
        	    $predicate = "pmb:has_authority";
    	        break;
	        
	    }
	    
	    if (!empty($predicate)) {
	        
    	    $success = $this->data_store->query('select ?uri where {
                <'.$linked_uri.'> '.$predicate.' ?uri .
            }');
	        
    	    if($success){
    	        $uri = '';
    	        if ($this->data_store->num_rows()) {
    	            $results = $this->data_store->get_result();
    	            $uri = $results[0]->uri;
    	        }
    	        
    	        if (!empty($uri)) {
    	            
    	            // On supprime le lien
    	            $success = $this->data_store->query('delete {
                        <'.$linked_uri.'> ?predicate ?object .
                    }');
    	            
    	            if (!$success) {
    	                if ($ajax) {
    	                    $this->errors[] = $this->data_store->get_errors();
    	                } else {
    	                    print "<br>Erreurs: <br>";
    	                    print "<pre>";print_r($this->data_store->get_errors());print "</pre><br>";
    	                }
    	            } else {
        	            $this->delete_linked_contribution($uri, $linked_uri);
        	            return TRUE;
    	            }
    	        }
    	    }
	    }
	    return FALSE;
	}
	
	/**
	 * 
	 * @param string $uri
	 * @return NULL|onto_common_item
	 */
	public static function get_item_instance($uri){

	    return self::$item_instances[$uri] ?? null;
	}
	
	public function get_errors() 
	{
	    return $this->errors;
	}
	
	public function get_pmb_name($class_uri)
	{
	    if(!empty(self::$pmb_names[$class_uri])){
	        return self::$pmb_names[$class_uri];
	    }
	    $query = 'select ?name where {
            <'.$class_uri.'> pmb:name ?name
        }';
	    $this->onto_query($query);
	    $result = $this->onto_result();
	    self::$pmb_names[$class_uri] = $result[0]->name;
	    return self::$pmb_names[$class_uri];
	    
	}
} // end of onto_handler