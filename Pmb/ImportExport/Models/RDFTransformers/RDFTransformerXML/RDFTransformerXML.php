<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RDFTransformerXML.php,v 1.3 2024/07/23 13:47:23 rtigero Exp $

namespace Pmb\ImportExport\Models\RDFTransformers\RDFTransformerXML;

use Pmb\Common\Library\Parser\ParserXML;
use Pmb\ImportExport\Models\RDFTransformers\RDFTransformer;

class RDFTransformerXML extends RDFTransformer
{

    protected $parserXML = null;
    protected $bnodesIndex = 1;


    public function __construct($sourceId)
    {
        parent::__construct($sourceId);
        $this->parserXML = new ParserXML();
    }

    public function toTriples($entity)
    {
        $this->parserXML->loadFromXmlString($entity);
        $result = $this->parserXML->getResult();
        $k = array_key_first($result);
        if (!is_numeric($k)) {
            $subject = $this->getPrefix() . $k . '_' . $this->entityId;
            $predicate = 'rdf:type';
            $object = $this->getPrefix() . $k;
            $this->triples[] = $subject . " " . $predicate . " " . $object;
            $this->addEntity($k, $k);
            $this->recurseTriples($subject, $result[$k], $this->getPrefix() . $k);
        }
        $this->storeTriples();
        $this->bnodesIndex = 1;
    }

    /**
     * Parcourt un tableau associatif et ajoute les triplets correspondants
     */
    protected function recurseTriples($subject, $arr, $parent = "")
    {
        foreach ($arr as $k => $v) {
            $domains = array();
            //On regarde si le parent est une entite
            if (array_key_exists($this->stripPrefix($parent), $this->triplesDescriptions['entities'])) {
                $domains = array($parent);
            }
            switch (true) {
                    //Indice textuel + valeur scalaire >> creation triple >> Pas de recursion
                case (!is_numeric($k) && is_scalar($v)):
                    if ($k === "value") {
                        $predicate = 'rdf:value';
                    } else {
                        //Gestion des attributs
                        $predicate = 'pmb' . $this->sourceId . ':' . $k;
                        $bnode = '_:' . $this->entityId . '-' . $this->bnodesIndex;
                        $this->bnodesIndex++;
                        $this->addProperty($k, $k, array(), array(static::LITERAL_TYPE), array(
                            static::ATTRIBUTE_TYPE =>  $parent
                        ));
                        $this->addTriple($subject, $predicate, $bnode, $parent);
                        $this->addTriple($bnode, static::ATTRIBUTE_TYPE, $v, $parent);
                        break;
                    }
                    $this->addTriple($subject, $predicate, $v, $this->getPrefix() . $k);
                    break;

                    //Tableau d'une seule valeur = 'value' >> creation triple >> Pas de recursion
                case (is_array($v) && isset($v[0]) && (count($v[0]) == 1) && isset($v[0]['value'])):
                    $this->addTriple($subject, $this->getPrefix() . $k, $v[0]['value'], $parent);
                    $this->addProperty($k, $k, $domains, array(static::LITERAL_TYPE), array("pmb:parent" => $parent));
                    break;

                    //Indice textuel + tableau de valeurs >> creation noeud blanc >> Recursion
                case (!is_numeric($k) && is_array($v)):
                    $bnode = '_:' . $this->entityId . '-' . $this->bnodesIndex;
                    $this->bnodesIndex++;
                    $parent = $parent != "" ? $parent : $this->getPrefix() . $k;
                    $this->addTriple($subject, $this->getPrefix() . $k, $bnode, $parent);
                    $this->addProperty($k, $k, $domains, array(), array("pmb:parent" => $parent));
                    $this->recurseTriples($bnode, $v, $this->getPrefix() . $k);
                    break;

                    //Indice numerique >> Recursion
                case (is_numeric($k) && is_array($v)):
                    $this->recurseTriples($subject, $v, $parent);
                    break;
            }
        }
    }
}
