<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RDFTransformerCSV.php,v 1.4 2024/07/26 12:49:56 rtigero Exp $

namespace Pmb\ImportExport\Models\RDFTransformers\RDFTransformerCSV;

use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class RDFTransformerCSV extends RDFTransformer
{
    public const ENTITY_NAME = "line";

    public function toTriples($entity)
    {
        if(is_string($entity)) {
            $entity = unserialize($entity);
        }
        $subject = $this->getPrefix() . 'line_' . $this->entityId;
        $this->addEntity(static::ENTITY_NAME, static::ENTITY_NAME);
        $this->addTriple($subject, 'rdf:type', $this->getPrefix() . static::ENTITY_NAME);

        foreach ($entity as $propertyName => $value) {
            $property = $propertyName;
            if (is_numeric($propertyName)) {
                $property = "col" . $propertyName;
            }
            $this->addProperty($property, $propertyName, array($this->getPrefix() . static::ENTITY_NAME), array(static::LITERAL_TYPE));
            $property = $this->getPrefix() . $property;
            $this->addTriple($subject, $property, $value);
        }
        $this->storeTriples();
    }
}
