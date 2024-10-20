<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Scenario.php,v 1.20 2024/07/25 12:50:22 rtigero Exp $

namespace Pmb\ImportExport\Models;

use Pmb\Common\Models\Model;
use Pmb\ImportExport\Models\Sources\SourceMaker;
use Pmb\ImportExport\Models\Steps\StepMaker;
use Pmb\ImportExport\Orm\SourceOrm;
use Pmb\ImportExport\Orm\StepOrm;

class Scenario extends Model
{
    public $id = 0;
    public $idScenario = 0;
    public $scenarioName = "";
    public $scenarioComment = "";
    public $scenarioSettings = null;

    public $sources = array();
    public $steps = array();

    protected $runMode = false;

    protected $ormName = "Pmb\ImportExport\Orm\ScenarioOrm";

    public function __construct(int $id = 0, bool $runMode = false)
    {
        $this->runMode = $runMode;
        parent::__construct($id);
    }

    protected function fetchData()
    {
        parent::fetchData();
        $this->fetchSources();
        $this->fetchSteps();
    }

    protected function fetchSources()
    {
        $sourcesOrm = SourceOrm::finds(["num_scenario" => $this->id]);
        $this->sources = array();
        foreach ($sourcesOrm as $sourceOrm) {
            $this->sources[] = new SourceMaker($sourceOrm->id_source, $this, $this->runMode);
        }
    }

    /**
     * Recupere la source d'un scenario par son id
     *
     * @param integer $sourceId
     * @return void
     */
    public function getSourceById(int $sourceId = 0)
    {
        if (!$sourceId || empty($this->sources)) {
            return null;
        }

        $foundSource = null;
        foreach ($this->sources as $k => $source) {
            if ($source->id == $sourceId) {
                $foundSource = $this->sources[$k];
                break;
            }
        }
        return $foundSource;
    }

    protected function fetchSteps()
    {
        $stepsOrm = StepOrm::finds(["num_scenario" => $this->id]);
        $this->steps = array();
        foreach ($stepsOrm as $stepOrm) {
            $this->steps[] = new StepMaker($stepOrm->id_step, $this, $this->runMode);
        }
    }

    /**
     * Recupere l'etape d'un scenario par son id
     *
     * @param integer $stepId
     * @return void
     */
    public function getStepById(int $stepId = 0)
    {
        if (!$stepId || empty($this->steps)) {
            return null;
        }

        $foundStep = null;
        foreach ($this->steps as $k => $step) {
            if ($step->id == $stepId) {
                $foundStep = $this->steps[$k];
                break;
            }
        }
        return $foundStep;
    }

    public function setFromForm(object $data)
    {
        $this->scenarioName = $data->scenarioName ?? "";
        $this->scenarioComment = $data->scenarioComment ?? "";
    }

    public function save()
    {
        $orm = new $this->ormName($this->id);

        $orm->scenario_name = $this->scenarioName;
        $orm->scenario_comment = $this->scenarioComment;

        $orm->save();
        if (!$this->id) {
            $this->id = $orm->id_scenario;
        }
        return $orm;
    }

    public function remove()
    {
        $orm = new $this->ormName($this->id);

        foreach ($this->steps as $stepMaker) {
            $stepMaker->remove();
        }
        foreach ($this->sources as $sourceMaker) {
            $sourceMaker->remove();
        }
        $orm->delete();
    }

    public function addSource($source)
    {
        $this->sources[] = $source;
    }

    public function addStep($step)
    {
        $this->steps[] = $step;
    }

    public function execute($scenarioData = null)
    {
        foreach ($this->steps as $step) {
            $step->getStep()->execute($scenarioData->sources);
        }
    }

    public function duplicate()
    {
        $newScenario = clone $this;

        $newScenario->id = 0;
        $newScenario->scenarioName .= " - copy";
        $newScenario->save();
        $newSources = array();
        $newSteps = array();

        foreach ($this->sources as $source) {
            $source->numScenario = $newScenario->id;
            $newSources[] = $source->duplicate();
        }

        foreach ($this->steps as $step) {
            //Gestion des sources associees aux synchros
            if (!empty($step->stepSettings) && !empty($step->stepSettings->source)) {
                $sourceIndex = array_search($step->stepSettings->source, array_column($this->sources, 'id'));
                if ($sourceIndex !== false) {
                    $step->stepSettings->source = $newSources[$sourceIndex]->id;
                }
            }
            $step->numScenario = $newScenario->id;
            $newSteps[] = $step->duplicate();
        }
        $newScenario->sources = $newSources;
        $newScenario->steps = $newSteps;

        return $newScenario;
    }
}
