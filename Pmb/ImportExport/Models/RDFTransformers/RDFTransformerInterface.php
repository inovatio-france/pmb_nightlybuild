<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RDFTransformerInterface.php,v 1.1 2024/07/10 15:02:52 rtigero Exp $

namespace Pmb\ImportExport\Models\RDFTransformers;

interface RDFTransformerInterface
{
    public function toTriples($entity);
}