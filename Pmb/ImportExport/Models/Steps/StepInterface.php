<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StepInterface.php,v 1.5 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\ImportExport\Models\Steps;

interface StepInterface
{
    public function setSource($source);
    public function execute($sourcesData);
}
