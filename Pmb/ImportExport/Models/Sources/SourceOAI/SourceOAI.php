<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SourceOAI.php,v 1.1 2024/07/05 10:09:00 dgoron Exp $

namespace Pmb\ImportExport\Models\Sources\SourceOAI;

use Pmb\ImportExport\Models\Sources\Source;

class SourceOAI extends Source
{

    public function initSync()
    {
        
        return false;
    }
}
