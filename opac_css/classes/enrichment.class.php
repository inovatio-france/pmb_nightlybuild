<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: enrichment.class.php,v 1.19 2024/09/23 11:52:58 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once $class_path."/connecteurs.class.php" ;
require_once $class_path."/marc_table.class.php" ;
require_once $include_path."/parser.inc.php" ;

class enrichment {
    
    public $enhancer = array();
    public $active = array();
    public $typnotice = "";
    public $typdoc = "";
    public $catalog;
    public $enrichmentsTabHeaders = array();
    public $type;
    
    protected $params = array();
    
    public function __construct($typnotice="",$typdoc="")
    {
        $this->typnotice = $typnotice;
        $this->typdoc = $typdoc;
        $this->fetch_sources();
        $this->fetch_data();
    }
    
    //On r�cup�re la liste des sources dispos pour enrichir
    public function fetch_sources()
    {
        $connectors = connecteurs::get_instance();
        $this->catalog = $connectors->catalog;
    }
    
    //R�cup�ration des donn�es existantes
    public function fetch_data()
    {
        $rqt = "select * from sources_enrichment";
        if($this->typnotice && $this->typdoc){
            $rqt.= " where (source_enrichment_typnotice like '".$this->typnotice."' and source_enrichment_typdoc like '') or (source_enrichment_typnotice like '".$this->typnotice."' and source_enrichment_typdoc like '".$this->typdoc."')";
        }
        $res = pmb_mysql_query($rqt);
        if(pmb_mysql_num_rows($res)){
            while($r= pmb_mysql_fetch_object($res)){
                $this->active[$r->source_enrichment_typnotice.$r->source_enrichment_typdoc][] = $r->source_enrichment_num;
                $this->params[$r->source_enrichment_typnotice.$r->source_enrichment_typdoc][$r->source_enrichment_num] = unserialize($r->source_enrichment_params);
            }
        }
    }
    
    //retourne les �l�ments � rajouter dans le head, les calculs aux besoins;
    public function getHeaders()
    {
        global $include_path;
        
        
        if(!$this->enrichmentsTabHeaders) {
            $this->generateHeaders();
        }
        //l'enrichissement se fait en ajax...
        $this->enrichmentsTabHeaders[]="
	<!-- Enrichissement de notice en Ajax-->
	<script src='$include_path/javascript/enrichment.js'></script>";
        return implode("\n",$this->enrichmentsTabHeaders);
    }
    
    //M�thode qui g�n�re les �l�ments � ins�rer dans le header pour le bon fonctionnement des enrichissements
    public function generateHeaders()
    {
        global $base_path;
        
        $this->enrichmentsTabHeaders =array();
        $alreadyIncluded = array();
        foreach($this->active as $sources){
            foreach($sources as $source_id){
                if(!in_array($source_id, $alreadyIncluded)){
                    //on r�cup�re les infos de la source n�cessaires pour l'instancier
                    $name = connecteurs::get_class_name($source_id);
                    foreach($this->catalog as $connector){
                        if($connector['NAME'] == $name){
                            if (is_file($base_path."/admin/connecteurs/in/".$connector['PATH']."/".$name.".class.php")){
                                require_once($base_path."/admin/connecteurs/in/".$connector['PATH']."/".$name.".class.php");
                                $conn = new $name($base_path."/admin/connecteurs/in/".$connector['PATH']);
                                $this->enrichmentsTabHeaders = array_merge($this->enrichmentsTabHeaders,$conn->getEnrichmentHeader($source_id));
                                $this->enrichmentsTabHeaders = array_unique($this->enrichmentsTabHeaders);
                            }
                        }
                    }
                    $alreadyIncluded[]=$source_id;
                }
            }
        }
    }
    
    
    /**
     * R�cup�re la liste des sources d'enrichissement possibles selon le type de notice (niveau_ biblio) et le type de document
     *
     * @param int $notice_id
     * @return mixed[][]
     */
    public function getTypeOfEnrichment($notice_id)
    {
        global $base_path;
        global $msg;
        
        $infos = array();
        $this->parseType();
        if(isset($this->active[$this->typnotice.$this->typdoc])) {
            $type = $this->typnotice.$this->typdoc;
        } else {
            $type = $this->typnotice;
        }
        if(isset($this->active[$type])){
            foreach($this->active[$type] as $source_id){
                //on r�cup�re les infos de la source n�cessaires pour l'instancier
                $name = connecteurs::get_class_name($source_id);
                foreach($this->catalog as $connector){
                    if($connector['NAME'] == $name){
                        if (is_file($base_path."/admin/connecteurs/in/".$connector['PATH']."/".$name.".class.php")){
                            require_once($base_path."/admin/connecteurs/in/".$connector['PATH']."/".$name.".class.php");
                            $conn = new $name($base_path."/admin/connecteurs/in/".$connector['PATH']);
                            $info = $conn->getTypeOfEnrichment($notice_id,$source_id);
                            $s=$conn->get_source_params($source_id);
                            $types = array(
                                'source_id' => $source_id
                            );
                            if(is_countable($info['type'])) {
                                for($i=0 ; $i<count($info['type']) ; $i++){
                                    if(!is_array($info['type'][$i])) {
                                        $info['type'][$i] = array(
                                            'code' => $info['type'][$i],
                                            'label' => $msg[substr($this->type[$info['type'][$i]],4)]
                                        );
                                    }elseif(!$info['type'][$i]['label']){
                                        $info['type'][$i]['label'] = $msg[substr($this->type[$info['type'][$i]],4)];
                                    }
                                    if(in_array($info['type'][$i]['code'],$s['TYPE_ENRICHMENT_ALLOWED'])){
                                        // R�cup�ration des infos d'affichage par d�faut et d'ordre
                                        if (isset($this->params[$type][$source_id][$info['type'][$i]['code']]['default_display'])) $info['type'][$i]['default_display'] = $this->params[$type][$source_id][$info['type'][$i]['code']]['default_display'];
                                        if (isset($this->params[$type][$source_id][$info['type'][$i]['code']]['order'])) $info['type'][$i]['order'] = $this->params[$type][$source_id][$info['type'][$i]['code']]['order'];
                                        
                                        $types['type'][]= $info['type'][$i];
                                    }
                                }
                            }
                            if(!empty($types) && is_countable($types['type']) && count($types['type'])>0){
                                $infos[] = $types;
                            }
                        }
                    }
                }
            }
        }
        return $infos;
    }
    
    public function getEnrichment($notice_id,$enrichmentType ="",$enrich_params=array(),$enrichPage=1)
    {
        global $base_path;
        $infos = array();
        if(isset($this->active[$this->typnotice.$this->typdoc])) {
            $type = $this->typnotice.$this->typdoc;
        } else {
            $type = $this->typnotice;
        }
        if(isset($this->active[$type])){
            foreach($this->active[$type] as $source_id){
                //on r�cup�re les infos de la source n�cessaires pour l'instancier
                $name = connecteurs::get_class_name($source_id);
                foreach($this->catalog as $connector){
                    if($connector['NAME'] == $name){
                        if (is_file($base_path."/admin/connecteurs/in/".$connector['PATH']."/".$name.".class.php")){
                            require_once($base_path."/admin/connecteurs/in/".$connector['PATH']."/".$name.".class.php");
                            $conn = new $name($base_path."/admin/connecteurs/in/".$connector['PATH']);
                            $eTypes = $conn->getTypeOfEnrichment($notice_id,$source_id);
                            if($enrichmentType){
                                $bool = false;
                                for($i=0 ; $i<count($eTypes['type']) ; $i++){
                                    if(is_array($eTypes['type'][$i])){
                                        if($enrichmentType == $eTypes['type'][$i]['code']) $bool =true;
                                    }else{
                                        if($enrichmentType == $eTypes['type'][$i]) $bool =true;
                                    }
                                }
                                if(!$enrichmentType || $bool)
                                    $infos[] = $conn->getEnrichment($notice_id,$source_id,$enrichmentType,$enrich_params,$enrichPage);
                            }
                        }
                    }
                }
            }
        }
        return $infos;
    }
    
    /**
     * R�cup�re les codes messages pour les enrichissements qui ne sont pas d�finis sous forme de tableau [code, label] !!!
     *
     */
    public function parseType()
    {
        global $include_path;
        
        $file = $include_path."/enrichment/categories.xml";
        $xml = file_get_contents($file);
        $types= _parser_text_no_function_($xml,"XMLLIST");
        foreach($types['ENTRY'] as $type){
            $this->type[$type['CODE']] = $type['value'];
        }
    }
}
?>