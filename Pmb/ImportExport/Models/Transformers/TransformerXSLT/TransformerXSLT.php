<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: TransformerXSLT.php,v 1.8 2024/07/26 08:07:32 rtigero Exp $

namespace Pmb\ImportExport\Models\Transformers\TransformerXSLT;

use Pmb\ImportExport\Models\Transformers\Transformer;

class TransformerXSLT extends Transformer
{
    protected $settings = array();
    protected $XSLTProcessor = null;
    protected $xsl = null;

    public function __construct($settings = [])
    {
        parent::__construct($settings);
        $this->XSLTProcessor = new \XSLTProcessor();
        $this->XSLTProcessor->registerPHPFunctions();
    }

    public function transform($inEntity)
    {
        if (empty($this->settings["xslFilePath"]) || !is_readable($this->settings["xslFilePath"])) {
            return $inEntity;
        }
        if (is_null($this->xsl)) {
            $this->xsl = new \DOMDocument();
            $this->xsl->loadXML(file_get_contents($this->settings["xslFilePath"]), LIBXML_NOBLANKS | LIBXML_NOENT | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
            $this->XSLTProcessor->importStylesheet($this->xsl);
        }

        $xmlEntity = new \DOMDocument();
        $xmlEntity->loadXML($inEntity, LIBXML_NOERROR);
        $out = $this->XSLTProcessor->transformToXML($xmlEntity);
        return $out;
    }
}
