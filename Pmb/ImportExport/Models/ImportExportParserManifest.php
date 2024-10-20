<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ImportExportParserManifest.php,v 1.7 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\ImportExport\Models;

use Pmb\Common\Helper\Helper;
use Pmb\Common\Library\Parser\ParserManifest;

class ImportExportParserManifest extends ParserManifest
{
    public $format = "";
    public $compatibility = array();
    public $settings = array();
    public $contextSettings = array();
    public $namespace = '';
    public $rdfTransformer = '';
    public $ontologyType = '';

    /**
     * @param \SimpleXMLElement $simplexml
     * @return array
     */
    protected function formatDataArray(\SimpleXMLElement $simplexml)
    {
        $result = [];

        foreach ($simplexml->children() as $child) {
            $key = Helper::camelize($child->getName());
            if (!$child->count()) {
                if (isset($result[$key])) {
                    if (is_string($result[$key])) {
                        $result[$key] = [$result[$key]];
                    }
                    $result[$key][] = $child->__toString();
                } else {
                    $result[$key] = $child->__toString();
                }
            } else {
                $result[$key] = $this->formatDataArray($child);
            }
        }

        return $result;
    }

    /**
     * formatage des donnees
     */
    protected function formatData()
    {
        foreach ($this->simplexml->children() as $prop => $value) {



            if (in_array($prop, ['author'])) {
                continue;
            }

            if ($prop == 'settings' || $prop == 'context_settings') {
                foreach ($value as $setting) {
                    $formattedSetting = array();
                    foreach ($setting->attributes() as $name => $attribute) {
                        $formattedSetting[$name] = $attribute->__toString();
                    }
                    if($prop == 'settings') {
                        $this->settings[] = $formattedSetting;
                    } else {
                        $this->contextSettings[] = $formattedSetting;
                    }
                }
                continue;
            }

            if (!$value->count()) {
                $this->{Helper::camelize($prop)} = $value->__toString();
            } else {
                $this->{Helper::camelize($prop)} = $this->formatDataArray($value);
            }
        }
    }
}
