<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Store.php,v 1.3 2024/07/22 14:40:23 rtigero Exp $

namespace Pmb\ImportExport\Models\Ontology;

class Store extends \sparql
{
    public $importExportOntology = null;

    protected $graph = "";
    protected $ontologyType = "";

    public function __construct($storeName, $graph, $ontologyType = "")
    {
        parent::__construct($storeName);
        //$this->reset();
        $this->graph = $graph;
        $this->ontologyType = $ontologyType;
        $this->importExportOntology = new ImportExportOntology($this);
    }

    public function storeTriples(array $arrayTriples = array())
    {
        if (count($arrayTriples)) {
            $q = $this->get_prefix_text() . "INSERT INTO <" . $this->graph . "> {";
            $q .= implode(" .\n", $arrayTriples);
            $q .= "}";
            $r = $this->query($q);
            if (!empty($this->errors)) {
                var_dump($q, $r, $this->errors);
            }
        }
    }

    public function query($query)
    {
        $query = $this->get_prefix_text() . $query;
        return parent::query($query);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->importExportOntology, $name) && !method_exists($this, $name)) {
            return call_user_func_array(array($this->importExportOntology, $name), $arguments);
        }
    }

    public function getOntology()
    {
        return $this->importExportOntology->readFromStore();
    }

    public function getGraph()
    {
        return $this->graph;
    }

    public function getOntologyType()
    {
        return $this->ontologyType;
    }

    public function getUri($prefix, $name)
    {
        if (array_key_exists($prefix, $this->ns)) {
            return $this->ns[$prefix] . $name;
        }
        return "";
    }
}
