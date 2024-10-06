<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RDFTransformer.php,v 1.3 2024/07/23 15:14:02 rtigero Exp $

namespace Pmb\ImportExport\Models\RDFTransformers;

use encoding_normalize;
use Pmb\ImportExport\Models\ImportExportParserDirectory;
use Pmb\ImportExport\Models\Ontology\Store;

abstract class RDFTransformer implements RDFTransformerInterface
{
    public const NAMESPACE = "pmb";
    public const STORE_NAME = "importexport";
    public const ENTITY_TYPE = "pmb:EntityImportExport";
    public const PROPERTY_TYPE = "pmb:property";
    public const ATTRIBUTE_TYPE = "pmb:attribute";
    public const LITERAL_TYPE = "rdfs:Literal";

    protected $store = null;
    protected $entityId = 1;
    protected $sourceId = 0;
    protected $triples = array();
    protected $triplesDescriptions = array(
        "entities" => array(),
        "properties" => array()
    );

    public function __construct($sourceId)
    {
        $this->sourceId = $sourceId;
        $parser = ImportExportParserDirectory::getInstance();
        $manifest = $parser->getManifestByNamespace(static::class);
        $this->store = new Store(static::STORE_NAME, static::NAMESPACE . $this->sourceId, $manifest->ontologyType);
        $this->store->ns[$this->getPrefix()] = "http://www.pmbservices.fr/ontology/source/" . $this->sourceId . "#";
    }

    abstract public function toTriples($entity);

    /**
     * Ajoute un triplet a la propriete $this->triples et remplit $this->triplesDescriptions si
     * la propriete est nouvelle
     * @param string $subject
     * @param string $predicate
     * @param string $object
     *
     * @return void
     */
    protected function addTriple($subject, $predicate, $object, $parent = "")
    {
        if (stripos($object, "_:") === false) {
            $object = encoding_normalize::charset_normalize($object, encoding_normalize::detect_encoding($object, ["UTF-8", "ISO-8859-1", "ISO-8859-15", "cp1252"]));
            $object = '"' . addslashes($object) . '"';
        }
        $this->triples[] = $subject . ' ' . $predicate . ' ' . $object . '';
    }

    /**
     * Retourne le prefix sparql utilise dans l'ontologie
     * Depend de la source
     * @return string
     */
    public function getPrefix()
    {
        return static::NAMESPACE . $this->sourceId . ":";
    }

    /**
     * Retourne la chaine passee en parametre depourvue de son prefix sparql
     * @param string $subject
     * @return string
     */
    public function stripPrefix($subject)
    {
        return str_replace(static::NAMESPACE . $this->sourceId . ":", "", $subject);
    }

    /**
     * Retourne le store
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    protected function storeTriples()
    {
        $this->store->storeTriples($this->triples, $this->getPrefix());
        $this->triples = array();
        $this->entityId++;
    }

    /**
     * Ajoute une entite dans l'ontologie
     * @param string $name
     * @param string $label
     *
     * @return void
     */
    protected function addEntity($name, $label)
    {
        if (!isset($this->triplesDescriptions['entities'][$name])) {
            $prefix = $this->getPrefix();

            $this->triplesDescriptions['entities'][$name][] = $prefix . $name . ' rdf:type ' . static::ENTITY_TYPE;
            $this->triplesDescriptions['entities'][$name][] = $prefix . $name . ' pmb:name "' . $name . '"';
            $this->triplesDescriptions['entities'][$name][] = $prefix . $name . ' pmb:displayLabel "' . $label . '"';
            $this->triplesDescriptions['entities'][$name][] = $prefix . $name . ' rdfs:isDefinedBy <http://www.pmbservices.fr/ontology/source/' . $this->sourceId . '#>';
        }
    }

    /**
     * Ajoute une propriete dans l'ontologie
     * @param string $name nom PMB
     * @param string $label label PMB
     * @param array $ranges
     * @param array $domains
     * @param array $additionalTriples tableau de type [ predicat => objet ]
     *
     * @return void
     */
    protected function addProperty($name, $label, $domains = array(), $ranges = array(), $additionalTriples = array())
    {
        $prefix = $this->getPrefix();

        if (!isset($this->triplesDescriptions['properties'][$name])) {
            $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' rdf:type ' . static::PROPERTY_TYPE;
            $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' pmb:name "' . $name . '"';
            $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' pmb:displayLabel "' . $label . '"';
            $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' rdfs:isDefinedBy <http://www.pmbservices.fr/ontology/source/' . $this->sourceId . '#>';
            //Si on a qu'une entite c'est forcement le domain
            if (empty($domains) && count($this->triplesDescriptions['entities']) == 1) {
                $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' rdfs:domain ' . $prefix . array_keys($this->triplesDescriptions['entities'])[0];
            }
            //Sinon on ajoute les domains passes en parametre
        }

        foreach ($domains as $domain) {
            if (!in_array($prefix . $name . ' rdfs:domain ' . $domain, $this->triplesDescriptions['properties'][$name])) {
                $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' rdfs:domain ' . $domain;
            }
        }
        foreach ($ranges as $range) {
            if (!in_array($prefix . $name . ' rdfs:range ' . $range, $this->triplesDescriptions['properties'][$name])) {
                $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' rdfs:range ' . $range;
            }
        }
        foreach ($additionalTriples as $predicate => $object) {
            if (!in_array($prefix . $name . ' ' . $predicate . ' ' . $object, $this->triplesDescriptions['properties'][$name])) {
                $this->triplesDescriptions['properties'][$name][] = $prefix . $name . ' ' . $predicate . ' ' . $object;
            }
        }
    }

    public function generateTriplesDescriptions()
    {
        foreach ($this->triplesDescriptions["entities"] as $triples) {
            foreach ($triples as $triplesDescription) {
                $this->triples[] = $triplesDescription;
            }
        }
        foreach ($this->triplesDescriptions["properties"] as $triples) {
            foreach ($triples as $triplesDescription) {
                $this->triples[] = $triplesDescription;
            }
        }
        $this->store->storeTriples($this->triples);
        $this->triples = array();
    }
}
