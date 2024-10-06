<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RDFTransformerRIS.php,v 1.1 2024/08/01 08:48:35 dgoron Exp $

namespace Pmb\ImportExport\Models\RDFTransformers\RDFTransformerRIS;

use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class RDFTransformerRIS extends RDFTransformer
{

    public const ENTITY_NAME = "entity";
    
    public function toTriples($entity)
    {
        $subject = $this->getPrefix() . 'entity_' . $this->entityId;
        $this->addEntity(static::ENTITY_NAME, static::ENTITY_NAME);
        $this->addTriple($subject, 'rdf:type', $this->getPrefix() . static::ENTITY_NAME);
        
        $result = array();
        $fields=explode("\n",$entity);
        for($i=0;$i<count($fields);$i++){
            $matches = array();
            if(preg_match("/([A-Z0-9]{1,4}) *- (.*)/",$fields[$i],$matches)){
                $champ = $matches[1];
                if(isset($result[$champ]) && $result[$champ]) {
                    $result[$champ] = $result[$champ]."###".trim($matches[2]);
                } else {
                    $result[$champ] = trim($matches[2]);
                }
            } else {
                $result[$champ] = $result[$champ]." ".trim($fields[$i]);
            }
        }
        foreach ($result as $propertyName => $value) {
            $this->addProperty($propertyName, $propertyName, array($this->getPrefix() . static::ENTITY_NAME), array(static::LITERAL_TYPE));
            $property = $this->getPrefix() . $propertyName;
            if(strpos($value, "###") !== false) {
                $exploded_value = explode('###', $value);
                for($i=0;$i<count($exploded_value);$i++){
                    $this->addTriple($subject, $property, $exploded_value[$i]);
                }
            } else {
                $this->addTriple($subject, $property, $value);
            }
        }
        $this->storeTriples();
    }
}
