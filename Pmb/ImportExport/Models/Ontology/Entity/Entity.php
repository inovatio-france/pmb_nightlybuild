<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Entity.php,v 1.2 2024/07/23 07:41:54 rtigero Exp $

namespace Pmb\ImportExport\Models\Ontology\Entity;

use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class Entity
{
    protected $uri = "";
    protected $name = "";
    protected $displayLabel = "";
    protected $comment = "";
    protected $properties = array();

    public function toTriples()
    {
        $triples = array();
        $triples[] = "<" . $this->uri . "> rdf:type <" . RDFTransformer::ENTITY_TYPE . ">";
        //TODO voir pour la gestion des triplets et du formatage des objets
        if ($this->name != "") {
            $triples[] = "<" . $this->uri . '> pmb:name "' . addslashes($this->name) . '"';
        }
        if ($this->displayLabel != "") {
            $triples[] = "<" . $this->uri . '> rdfs:label "' . addslashes($this->displayLabel) . '"';
        }
        if ($this->comment != "") {
            $triples[] = "<" . $this->uri . '> rdfs:comment "' . addslashes($this->comment) . '"';
        }

        foreach ($this->properties as $property) {
            $triples[] = "<" . $property->uri . "> rdfs:domain <" . $this->uri . ">";
        }
        return $triples;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function addProperty($property)
    {
        if (!$this->propertyExists($property)) {
            $this->properties[$property->uri] = $property;
        }
    }

    public function propertyExists($property)
    {
        if (array_key_exists($property->uri, $this->properties)) {
            return true;
        }
        return false;
    }
}
