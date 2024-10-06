<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: PMBEntity.php,v 1.4 2024/08/01 14:34:42 dgoron Exp $

namespace Pmb\ImportExport\Models\Entities;

use Pmb\Common\Helper\ParserMessage;

class PMBEntity
{
    use ParserMessage;
    
    protected const RDF_TYPE = "";
    protected const PREFIXES = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX pmb: <http://www.pmbservices.fr/ontology#>";
    protected const STORE_NAME = "pmb:sparql";
    protected $id = 0;
    protected $values = array();
    protected $store = null;

    public function __construct(\ARC2_Store $store, int $id = 0)
    {
        $this->id = $id;
        $this->store = $store;
        if ($this->id) {
            $this->fetchData();
        }
    }

    protected function fetchData()
    {
        $data = $this->store->query(static::PREFIXES . "
            SELECT ?p ?o WHERE {
                pmb:" . static::RDF_TYPE . "_{$this->id} ?p ?o
            }");
        if (empty($data["result"])) {
            throw new \Exception("Entity {$this->id} not found");
        }
        foreach ($data["result"]["rows"] as $row) {
            $prop = explode("#", $row["p"])[1];
            $this->values[$prop] = $row["o"];
        }
    }

    public function __get($prop)
    {
        if (isset($this->values[$prop])) {
            return $this->values[$prop];
        }
    }

    public function __set($prop, $value)
    {
        $this->values[$prop] = $value;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function save()
    {
        if ($this->exists()) {
            $this->delete();
        }

        $query = static::PREFIXES . "
            INSERT INTO " . static::STORE_NAME . " { \n";
        $query .= "pmb:record_{$this->id} rdf:type pmb:" . static::RDF_TYPE . " .\n";

        foreach ($this->values as $prop => $value) {
            $query .= "pmb:record_{$this->id} pmb:{$prop} '{$value}' .\n";
        }
        $query .= "}";

        $query = $this->store->query($query);

        if (isset($query["result"]) && ($query["result"]["t_count"] > 0)) {
            return true;
        }
        throw new \Exception("Error in store");
    }

    protected function exists()
    {
        $query = $this->store->query(static::PREFIXES . "
            SELECT ?type WHERE {
                pmb:" . static::RDF_TYPE . "_{$this->id} rdf:type ?type
                FILTER (?type = pmb:" . static::RDF_TYPE . ")
            }");
        if (isset($query["result"])) {
            return (count($query["result"]["rows"]) > 0);
        }
        throw new \Exception("Error in store");
    }

    protected function delete()
    {
        $query = $this->store->query(static::PREFIXES . "
            DELETE FROM " . static::STORE_NAME . " {
                pmb:record_{$this->id} ?p ?o
            }");
        if (isset($query["result"]) && ($query["result"]["t_count"] > 0)) {
            return true;
        }
        throw new \Exception("Error in store");
    }
}
