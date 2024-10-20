<?php
// +-------------------------------------------------+
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: HarvestController.php,v 1.6 2024/01/03 14:21:36 qvarin Exp

namespace Pmb\ImportExport\Controller;

use Pmb\ImportExport\Models\Scenario;
use Pmb\ImportExport\Models\Sources\Source;
use Pmb\ImportExport\Models\Sources\SourceMaker;
use Pmb\ImportExport\Models\Steps\Step;
use Pmb\ImportExport\Models\Steps\StepMaker;
use Pmb\ImportExport\Orm\ScenarioOrm;

class ScenariosController extends ImportExportController
{

	protected const VUE_NAME = "importexport/scenarios";

	protected const MODEL_PATH = "Pmb/ImportExport/Models";

	public function proceed()
	{
		switch ($this->data->action) {
			case 'edit':
				$this->editAction();
				break;
			case 'execute':
				$this->executeAction();
				break;
			default:
				$this->defaultAction();
				break;
		}
	}

	protected function defaultAction()
	{
		$scenario = new Scenario();
		$this->render([
			"list" => $scenario->getList(),
		]);
	}

	protected function addAction()
	{
		$this->render($this->getFormData());
	}

	protected function editAction()
	{
		global $id;

		$id = intval($id);
		if (ScenarioOrm::exist($id)) {
			$this->render($this->getFormData($id));
		} else {
			$this->render($this->getFormData());
		}
	}

	protected function executeAction()
	{
		global $id;

		$id = intval($id);
		if (ScenarioOrm::exist($id)) {
			$this->render($this->getFormData($id));
		} else {
			$this->render($this->getFormData());
		}
	}

	/**
	 * Recuperation donnees formulaire ajout/edition
	 *
	 * @param number $id
	 * @return array[]
	 */
	protected function getFormData($id = 0)
	{
		$data = array();
		$data['scenario'] = new Scenario($id);
		$data['stepsTypes'] = Step::getStepsTypes();
		$data['sourcesTypes'] = Source::getSourcesTypes();
		return $data;
	}

	public function save()
	{
		$scenario = new Scenario($this->data->id);
		$scenario->setFromForm($this->data);
		$scenario->save();
		if ($scenario->id) {
			$this->saveSources($scenario->id);
			$this->saveSteps($scenario->id);
		}

		$this->ajaxJsonResponse($scenario);
	}

	public function saveSources(int $numScenario = 0)
	{
		if (!empty($this->data->sources)) {
			foreach ($this->data->sources as $sourceData) {
				if (isset($sourceData->id) && $sourceData->id) {
					$source = new SourceMaker($sourceData->id);
				} else {
					$source = new SourceMaker();
				}
				$source->numScenario = $numScenario;
				$source->setFromForm($sourceData);
				$source->save();
			}
		}
	}

	public function saveSteps(int $numScenario = 0)
	{
		if (!$numScenario || !scenarioOrm::exist($numScenario)) {
			$this->ajaxError('unknown scenario');
		}

		if (!empty($this->data->steps)) {
			foreach ($this->data->steps as $stepData) {
				if (isset($stepData->id) && $stepData->id) {
					$step = new StepMaker($stepData->id);
				} else {
					$step = new StepMaker();
				}
				$step->numScenario = $numScenario;
				$step->setFromForm($stepData);
				$step->save();
			}
		}
	}

	public function remove()
	{
		$id = ($this->data->id) ?? 0;
		if (!$id || !scenarioOrm::exist($id)) {
			$this->ajaxError('unknown scenario');
		}

		$scenario = new Scenario($id);
		$scenario->remove();
		$this->ajaxJsonResponse(['success' => true]);
	}

	public function saveStepsOrder(int $numScenario = 0)
	{
		if (!$numScenario || !scenarioOrm::exist($numScenario)) {
			$this->ajaxError('unknown scenario');
		}

		$this->saveSteps($numScenario);
		$this->ajaxJsonResponse(['success' => true]);
	}

	public function duplicate()
	{
		$id = ($this->data->id) ?? 0;
		if (!$id || !scenarioOrm::exist($id)) {
			$this->ajaxError('unknown scenario');
		}

		$scenario = new Scenario($this->data->id);
		$newScenario = $scenario->duplicate();
		$this->ajaxJsonResponse($newScenario);
	}

	public function execute()
	{
		if (!$this->data->id) {
			$this->ajaxJsonResponse(['success' => true]);
		}
		if (!scenarioOrm::exist($this->data->id)) {
			$this->ajaxError('unknown scenario');
		}

		$scenario = new Scenario($this->data->id, true);
		$scenario->execute($this->data);

		$this->ajaxJsonResponse(['success' => true]);
	}
}
