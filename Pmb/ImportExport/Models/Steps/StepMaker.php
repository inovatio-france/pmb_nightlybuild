<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StepMaker.php,v 1.6 2024/07/23 13:47:23 rtigero Exp $

namespace Pmb\ImportExport\Models\Steps;

use Pmb\Common\Helper\Helper;
use Pmb\ImportExport\Models\ImportExportRoot;
use Pmb\ImportExport\Models\Steps\Step;

class StepMaker extends ImportExportRoot
{

    protected static $stepsTypes = null;

    public $id = 0;
    public $stepName = '';
    public $stepComment = '';
    public $stepType = '';
    public $stepSettings = null;
    public $stepOrder = 0;
    public $numScenario = 0;

    protected $ormName = "Pmb\ImportExport\Orm\StepOrm";

    protected $step = null;
    protected $caller = null;

    /**
     * Constructeur
     *
     * @param integer $id : id step
     * @param [type] $caller : object appellant
     * @param boolean $runMode : mode execution
     */
    public function __construct(int $id = 0, $caller = null, bool $runMode = false)
    {
        parent::__construct($id);
        $this->caller = $caller;

        if ($id && $runMode) {
            $this->instantiateStep();
        }
    }

    /**
     * Instanciation etape
     *
     * @return void
     */
    protected function instantiateStep()
    {
        $currentStepType = $this->stepType ?? '';
        if (!$currentStepType) {
            return;
        }
        $stepClass = '';
        $stepsTypes = Step::getStepsTypes();

        foreach ($stepsTypes as $stepType) {
            if ($stepType['type'] == $currentStepType) {
                $stepClass = $stepType['namespace'];
                break;
            }
        }
        if ($stepClass && class_exists($stepClass)) {

            $stepObject = new $stepClass($this->id);

            $sourceId = $this->stepSettings->source ?? 0;
            $source = null;
            if ($sourceId && !is_null($this->caller) && method_exists($this->caller, 'getSourceById')) {
                $source = $this->caller->getSourceById($sourceId);
                $stepObject->setSource($source);
            }
            $stepSettings = Helper::toArray($this->stepSettings);
            $stepObject->setBaseParameters($stepSettings);

            $this->step = $stepObject;
        }
    }

    public function getStep()
    {
        return $this->step;
    }

    public function setFromForm(object $data)
    {
        $this->stepName = $data->stepName ?? '';
        $this->stepComment = $data->stepComment ?? '';
        $this->stepType = $data->stepType ?? '';
        $this->stepOrder = $data->stepOrder ?? 0;
        $this->stepSettings = $data->stepSettings ?? null;
    }

    public function save()
    {
        $orm = new $this->ormName($this->id);

        $orm->step_name = $this->stepName;
        $orm->step_comment = $this->stepComment;
        $orm->step_type = $this->stepType;
        $orm->step_settings = \encoding_normalize::json_encode($this->stepSettings);
        $orm->step_order = $this->stepOrder;
        $orm->num_scenario = $this->numScenario;
        $orm->save();
        if (!$this->id) {
            $this->id = $orm->id_step;
        }
        return $orm;
    }


    public function remove()
    {
        $orm = new $this->ormName($this->id);
        $orm->delete();
    }

    public function duplicate()
    {
        $newStep = clone $this;

        $newStep->id = 0;
        $newStep->stepName .= " - copy";
        $newStep->save();

        return $newStep;
    }
}
