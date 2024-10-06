<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: TransformerInterface.php,v 1.4 2024/07/19 14:50:31 dbellamy Exp $

namespace Pmb\ImportExport\Models\Transformers;

interface TransformerInterface
{
    public function transform($inEntity);

    public function setParameters($settings);
}
