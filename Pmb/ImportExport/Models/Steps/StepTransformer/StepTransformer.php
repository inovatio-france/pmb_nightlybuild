<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StepTransformer.php,v 1.3 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\ImportExport\Models\Steps\StepTransformer;

use Pmb\ImportExport\Models\Steps\Step;
use Pmb\ImportExport\Models\Transformers\TransformerInterface;

class StepTransformer extends Step
{
    private $transformer = null;

    public function execute($sourceData = array())
    {
        $this->source->setContextParameters([]);
        $this->source->initSync();
        foreach ($this->source as $entity) {
            if (!is_null($entity)) {
                $entity = $this->transformer->transform($entity);
            }
        }
    }

    public function setTransformer(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }
}
