<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Step.php,v 1.7 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\ImportExport\Models\Steps;

use Pmb\Common\Helper\Helper;
use Pmb\Common\Helper\ParserMessage;
use Pmb\ImportExport\Models\ImportExportParserDirectory;

class Step implements StepInterface
{
    use ParserMessage;

    protected static $stepsTypes = null;

    public $id = 0;
    public $stepName = '';
    public $stepComment = '';
    public $stepType = '';
    public $numScenario = 0;
    protected $source = null;
    protected $baseParameters = array();

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public function execute($sourcesData = array())
    {
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function setBaseParameters($parameters)
    {
        $this->baseParameters = $parameters;
    }

    /**
     * Retourne la liste des types d'etapes
     *
     * @return []
     */
    public static function getStepsTypes()
    {
        if (!is_null(static::$stepsTypes)) {
            return static::$stepsTypes;
        }
        static::$stepsTypes = [];
        $parser = ImportExportParserDirectory::getInstance();
        $manifests = $parser->getManifests("Pmb/ImportExport/Models/Steps");


        foreach ($manifests as $manifest) {
            if (!empty($manifest->type)) {
                $messages = $manifest->namespace::getMessages();
                static::$stepsTypes[] = array(
                    'value' => $manifest->type,
                    'type' => $manifest->type,
                    'namespace' => $manifest->namespace,
                    'settings' => $manifest->settings,
                    'msg' =>  $messages,
                );
            }
        }
        return static::$stepsTypes;
    }

    /**
     * Fournit les parametres de contexte à la source
     * @param array $sourcesData
     */
    protected function setSourceContextParameters($sourcesData)
    {
        $source = $this->source->getSource();
        foreach ($sourcesData as $sourceData) {
            if ($sourceData->id == $source->id && isset($sourceData->contextParameters)) {
                $source->setContextParameters(Helper::toArray($sourceData->contextParameters));
                return;
            }
        }
    }
}
