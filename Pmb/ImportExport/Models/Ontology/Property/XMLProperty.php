<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: XMLProperty.php,v 1.2 2024/07/23 07:41:54 rtigero Exp $

namespace Pmb\ImportExport\Models\Ontology\Property;

class XMLProperty extends Property
{
    protected $attribute = array();
    protected $parent = null;

    public function __set($name, $value)
    {
        if ($name == "attribute" && !in_array($value, $this->attribute)) {
            $this->attribute[] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function isAttribute()
    {
        return count($this->attribute) > 0;
    }

    public function toTriples()
    {
        $triples = parent::toTriples();
        foreach ($this->attribute as $attribute) {
            $triples[] = "<" . $this->uri . "> pmb:attribute <" . $attribute . ">";
        }
        if (isset($this->parent)) {
            $triples[] = "<" . $this->uri . "> pmb:parent <" . $this->parent . ">";
        }
        return $triples;
    }
}
