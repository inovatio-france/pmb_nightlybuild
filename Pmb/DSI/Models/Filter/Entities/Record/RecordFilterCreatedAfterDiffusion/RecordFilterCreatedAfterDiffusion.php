<?php
namespace Pmb\DSI\Models\Filter\Entities\Record\RecordFilterCreatedAfterDiffusion;

use Pmb\DSI\Models\Filter\Entities\Record\RecordFilter;

class RecordFilterCreatedAfterDiffusion extends RecordFilter
{
	public function __construct(array $data, int $entityId = 0)
	{
		parent::__construct($data, $entityId);
	}

    public function filter() : array {
        $filteredData = [];

        $lastDiffusionDate = $this->getLastDiffusionDate();
        //Si on n'a pas de derni�re diffusion on renvoie tout
        if(is_null($lastDiffusionDate)) {
            return $this->data;
        }

        foreach ($this->data as $id => $item) {
            $notice = new \notice($id);

            $date = \DateTime::createFromFormat("d/m/Y H:i:s", $notice->create_date);

            if($date->format("U") > $lastDiffusionDate->format("U")) {
                $filteredData[$id] = $item;
            }
        }

        return $filteredData;
    }
}

