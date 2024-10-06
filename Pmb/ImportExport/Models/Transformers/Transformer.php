<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Transformer.php,v 1.5 2024/08/01 08:48:35 dgoron Exp $

namespace Pmb\ImportExport\Models\Transformers;

use Pmb\Common\Helper\ParserMessage;

abstract class Transformer implements TransformerInterface
{
    use ParserMessage;

    protected $settings = [];

    public function __construct($settings = [])
    {
        $this->setParameters($settings);
    }

    public function getSettings()
    {
        return $this->settings;
    }
    
    public function setParameters($settings)
    {
        $this->settings = $settings;
    }
}
