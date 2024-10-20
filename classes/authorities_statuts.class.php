<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authorities_statuts.class.php,v 1.17 2021/01/21 07:42:27 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class authorities_statuts{
	protected static $statuts = array();
	private static $statuts_fetched = false;
	
	public static function get_list(){
		if(!static::$statuts_fetched){
			static::$statuts = array();
			$query = "select id_authorities_statut, authorities_statut_label, authorities_statut_class_html, authorities_statut_available_for from authorities_statuts order by authorities_statut_label";
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)){
				while($row = pmb_mysql_fetch_object($result)){
					static::$statuts[$row->id_authorities_statut] = array(
						'label' => $row->authorities_statut_label,
						'class_html' => $row->authorities_statut_class_html,
						'available_for' => unserialize($row->authorities_statut_available_for)							
					);
					if(!is_array(static::$statuts[$row->id_authorities_statut]['available_for'])){
						static::$statuts[$row->id_authorities_statut]['available_for'] = array();
					}
				}
			}
			static::$statuts_fetched = true;
		}
	}	
	
	/**
	 * Fonction permettant de g�n�rer le selecteur des statut d�finis pour un type d'autorit�
	 * @param integer $auth_type Constante type d'autorit� (ou 1000+id authperso)
	 * @param integer $auth_statut_id Identifiant du statut enregistr� pour l'autorit� courante 
	 * @param boolean $selector_search S�l�cteur affich� dans la page de recherche
	 * @return string
	 */
	public static function get_form_for($auth_type, $auth_statut_id, $search=false){
	    global $msg;
	    $auth_statut_id=intval($auth_statut_id);
        $statuts_defined = static::get_statuts_for($auth_type);
        $on_change='';
        $selector = '<select name="authority_statut" '.$on_change.' >';
        if($search){
            $selector.='<option value="0">'.$msg['authorities_statut_selector_all'].'</option>';
        }
        foreach($statuts_defined as $id_statut => $statut){
            $selector.='<option '.(($id_statut == $auth_statut_id)?'selected="selected"':'').' value="'.$id_statut.'">'.$statut['label'].'</option>';
        }
        $selector.= '</select>';
        return $selector;
	}
	
	/**
	 * Fonction retournant un tableau des statut d�fini pour le type d'autorit� pass� en parametre
	 * @param integer $auth_type Type d'autorit�
	 * @return array $statuts_found Tableau des statuts disponible pour le type d'autorit� pass� en parametre
	 */
	private static function get_statuts_for($auth_type){
	    /**
	     * TODO test sur auth_type pour les authorit�s perso
	     */
	    static::get_list();
	    $statuts_found = array();
	    foreach(static::$statuts as $id_statut => $statut){
	        if(in_array($auth_type,$statut['available_for']) || ($id_statut==1)){
	            $statuts_found[$id_statut] = $statut;
	        }
	        //TODO: array merge authority perso
	    }
	    return $statuts_found;
	} 
}