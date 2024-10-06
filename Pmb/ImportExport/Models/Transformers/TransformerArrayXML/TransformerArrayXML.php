<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: TransformerArrayXML.php,v 1.6 2024/07/26 08:07:32 rtigero Exp $

namespace Pmb\ImportExport\Models\Transformers\TransformerArrayXML;

use Pmb\ImportExport\Models\Transformers\Transformer;

class TransformerArrayXML extends Transformer
{
    protected $settings = [];

    protected $xml = null;

    public function transform($inEntity)
    {
        if (is_string($inEntity)) {
            $inEntity = unserialize($inEntity);
        }
        $this->xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"" . $this->settings["XMLEncoding"] . "\"?><" . $this->settings["rootElement"] . "/>");
        foreach ($inEntity as $key => $value) {
            $this->xml->addChild($key, $value);
        }
        return $this->xml->asXML();
    }
}
