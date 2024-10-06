<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Property.php,v 1.2 2024/07/23 07:41:54 rtigero Exp $

namespace Pmb\ImportExport\Models\Ontology\Property;

use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class Property
{
    protected $uri = "";
    protected $name = "";
    protected $displayLabel = "";
    protected $comment = "";
    protected $domains = array();
    protected $ranges = array();
    protected $pmbType = null;

    public function addDomain($domain)
    {
        if (!$this->domainExists($domain)) {
            $this->domains[$domain->uri] = $domain;
        }
    }

    public function addRange($range)
    {
        if (!$this->rangeExists($range)) {
            $this->ranges[$range->uri] = $range;
        }
    }

    public function  domainExists($domain)
    {
        if (array_key_exists($domain->uri, $this->domains)) {
            return true;
        }
        return false;
    }

    public function  rangeExists($range)
    {
        if (array_key_exists($range->uri, $this->ranges)) {
            return true;
        }
        return false;
    }

    public function toTriples()
    {
        $triples = array();
        $triples[] = "<" . $this->uri . "> rdf:type <" . RDFTransformer::PROPERTY_TYPE . ">";
        //TODO voir pour la gestion des triplets et du formatage des objets
        if ($this->name != "") {
            $triples[] = "<" . $this->uri . '> pmb:name "' . addslashes($this->name) . '"';
        }
        if ($this->displayLabel != "") {
            $triples[] = "<" . $this->uri . '> pmb:displayLabel "' . addslashes($this->displayLabel) . '"';
        }
        if ($this->comment != "") {
            $triples[] = "<" . $this->uri . '> rdfs:comment "' . addslashes($this->comment) . '"';
        }

        foreach ($this->domains as $domain) {
            $triples[] = "<" . $this->uri . "> rdfs:domain <" . $domain->uri . ">";
        }

        foreach ($this->ranges as $range) {
            $triples[] = "<" . $this->uri . "> rdfs:range <" . $range->uri . ">";
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
}
