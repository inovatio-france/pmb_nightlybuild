<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ImportExportOntology.php,v 1.4 2024/07/23 07:41:54 rtigero Exp $

namespace Pmb\ImportExport\Models\Ontology;

use Pmb\ImportExport\Models\Ontology\Entity\Entity;
use Pmb\ImportExport\Models\Ontology\Property\Property;
use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class importExportOntology
{
    protected $entities = array();
    protected $properties = array();
    protected $store = null;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function addEntity($entity)
    {
        if (!$this->entityExists($entity->uri)) {
            $this->entities[$entity->uri] = $entity;
        }
    }

    public function addProperty($property)
    {
        if (!$this->propertyExists($property->uri)) {
            $this->properties[$property->uri] = $property;
        }
    }

    public function saveToStore()
    {
        foreach ($this->entities as $entity) {
            $this->store->storeTriples($entity->toTriples());
        }
        foreach ($this->properties as $property) {
            $this->store->storeTriples($property->toTriples());
        }
    }

    /**
     * @return $this
     */
    public function readFromStore()
    {
        $this->readEntities();
        $this->readProperties();
        //var_dump($this->entities, $this->properties);
        // foreach($this->entities as $entity) {
        //     var_dump($entity->name, "----------", array_keys($entity->properties));
        // }
        // foreach($this->properties as $entity) {
        //     var_dump($entity->name, "----------", array_keys($entity->domains), array_keys($entity->ranges));
        // }
        return $this;
    }

    public function propertyExists($uri)
    {
        if (array_key_exists($uri, $this->properties)) {
            return true;
        }
        return false;
    }

    public function entityExists($uri)
    {
        if (array_key_exists($uri, $this->entities)) {
            return true;
        }
        return false;
    }

    public function getPropertyByURI($uri)
    {
        if ($this->propertyExists($uri)) {
            return $this->properties[$uri];
        }
        $dynamicClass = 'Pmb\ImportExport\Models\Ontology\Property\\' . $this->store->getOntologyType() . 'Property';
        if (class_exists($dynamicClass)) {
            $property = new $dynamicClass();
        } else {
            $property = new Property();
        }
        $property->uri = $uri;
        return $property;
    }

    public function getEntityByURI($uri)
    {
        if ($this->entityExists($uri)) {
            return $this->entities[$uri];
        }
        $dynamicClass = 'Pmb\ImportExport\Models\Ontology\Entity\\' . $this->store->getOntologyType() . 'Entity';
        if (class_exists($dynamicClass)) {
            $entity = new $dynamicClass();
        } else {
            $entity = new Entity();
        }
        $entity->uri = $uri;
        return $entity;
    }

    protected function readEntities()
    {
        $query = "SELECT * WHERE {
            graph <" . $this->store->getGraph() . "> {
                ?uri rdf:type " . RDFTransformer::ENTITY_TYPE . " ;
                    ?predicate ?object
            }
        }";
        $result = $this->store->query($query);
        foreach ($result["result"]["rows"] as $row) {
            $entity = $this->getEntityByURI($row["uri"]);
            $predicate = explode("#", $row["predicate"])[1];
            if (!in_array($predicate, array('uri')) && property_exists($entity, $predicate)) {
                $entity->$predicate = $row["object"];
            }

            $this->addEntity($entity);
        }
    }

    protected function readProperties()
    {
        $query = "SELECT * WHERE {
            graph <" . $this->store->getGraph() . "> {
                ?uri rdf:type " . RDFTransformer::PROPERTY_TYPE . " ;
                    ?predicate ?object
            }
        }";
        $result = $this->store->query($query);
        foreach ($result["result"]["rows"] as $row) {
            $property = $this->getPropertyByURI($row["uri"]);
            $predicate = explode("#", $row["predicate"])[1];

            if (!in_array($predicate, array('domain', 'range')) && property_exists($property, $predicate)) {
                $property->$predicate = $row["object"];
                $this->addProperty($property);
                continue;
            }

            if ($predicate == "domain") {
                $domain = $this->getEntityByURI($row["object"]);
                $domain->addProperty($property);
                $property->addDomain($domain);
                $this->addEntity($domain);
            }

            if ($predicate == "range") {
                if ($this->entityExists($row["object"])) {
                    $range = $this->getEntityByURI($row["object"]);
                    $range->addProperty($property);
                } else {
                    $range = $this->getPropertyByURI($row["object"]);
                }
                $property->addRange($range);
                $this->addProperty($range);
            }

            $this->addProperty($property);
        }
    }
}
