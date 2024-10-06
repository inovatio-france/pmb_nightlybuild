<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StepSynchro.php,v 1.9 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\ImportExport\Models\Steps\StepSynchro;

use Pmb\Common\Helper\GlobalContext;
use Pmb\ImportExport\Models\ImportExportParserDirectory;
use Pmb\ImportExport\Models\Steps\Step;

class StepSynchro extends Step
{

    private $tempFile = null;

    public function execute($sourcesData = array())
    {
        $source = $this->source->getSource();
        $this->setSourceContextParameters($sourcesData);
        if ($source->initSync()) {
            foreach ($source as $entity) {
                if (!is_null($entity)) {
                    switch ($this->baseParameters['storageMethod']) {
                        case "warehouse":
                            $source->toTriples($entity);
                            break;
                        case "temporaryFile":
                            if (is_null($this->tempFile)) {
                                $tempFilePath = GlobalContext::get("base_path") . "/temp/" . $this->source->sourceName . "_" . $this->source->id . "_" . $this->id . ".tmp";
                                if (file_exists($tempFilePath)) {
                                    @unlink($tempFilePath);
                                }
                                $this->tempFile = fopen($tempFilePath, "w+");
                            }
                            fwrite($this->tempFile, $entity);
                            break;
                        default:
                            break;
                    }
                }
            }
            $source->closeSync();
        }
    }

    public static function storageMethods()
    {
        $parser = ImportExportParserDirectory::getInstance();
        $manifests = $parser->getManifests("Pmb/ImportExport/Models/Steps/StepSynchro");
        if (!empty($manifests[0])) {
            $manifest = $manifests[0];
            $messages = $manifest->namespace::getMessages();

            return [
                ['value' => 'warehouse', 'label' => $messages['storageMethodWarehouse']],
                ['value' => 'temporaryFile', 'label' => $messages['storageMethodTemporaryFile']]
            ];
        }
        return [];
    }
}
