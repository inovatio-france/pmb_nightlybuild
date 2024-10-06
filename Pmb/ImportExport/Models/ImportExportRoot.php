<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ImportExportRoot.php,v 1.2 2024/07/10 15:02:52 rtigero Exp $

namespace Pmb\ImportExport\Models;

use encoding_normalize;
use Pmb\Common\Helper\ParserMessage;
use Pmb\Common\Models\Model;

abstract class ImportExportRoot extends Model
{
    use ParserMessage;

    protected function fetchData()
    {
        parent::fetchData();
        if(property_exists($this, $this->ormName::PREFIX . "Settings")) {
            $this->{$this->ormName::PREFIX . "Settings"} = encoding_normalize::json_decode($this->{$this->ormName::PREFIX . "Settings"});
        }
    }
}