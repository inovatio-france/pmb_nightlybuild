<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StepsController.php,v 1.6 2024/07/23 10:29:43 rtigero Exp $

namespace Pmb\ImportExport\Controller;

use Pmb\ImportExport\Models\Steps\StepMaker;
use Pmb\Common\Helper\GlobalContext;
use Pmb\ImportExport\Orm\StepOrm;

class StepsController extends ImportExportController
{

    protected const VUE_NAME = "importexport/steps";

    protected const MODEL_PATH = "Pmb/ImportExport/Models/Steps";

    public function proceed()
    {
        switch ($this->data->action) {
            case 'edit':
                $this->editAction();
                break;
            default:
                $this->defaultAction();
                break;
        }
    }

    protected function defaultAction()
    {
    }

    protected function addAction()
    {
        $this->render($this->getFormData());
    }

    protected function editAction()
    {
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
        return $data;
    }

    public function save()
    {
        if (!empty($this->data->id)) {
            if(! StepOrm::exist($this->data->id)) {
                $this->ajaxError("Unknown step");
            }
            $step = new StepMaker($this->data->id);
        } else {
            $step = new StepMaker();
            $step->numScenario = $this->data->numScenario;
        }
        $step->setFromForm($this->data);
        $step->save();
        $this->ajaxJsonResponse($step);
    }

    public function duplicate()
    {
        if (!empty($this->data->id)) {
            if(! StepOrm::exist($this->data->id)) {
                $this->ajaxError("Unknown step");
            }
            $step = new StepMaker($this->data->id);
            $step->stepOrder = $this->data->stepOrder;
            $newStep = $step->duplicate();

            $this->ajaxJsonResponse($newStep);
        } else {
            $this->ajaxError(GlobalContext::msg('common_failed_save'));
        }
    }

    public function remove()
    {
        if(! StepOrm::exist($this->data->id)) {
            $this->ajaxError("Unknown step");
        }
        $step = new StepMaker($this->data->id);
        $step->remove();
        $this->ajaxJsonResponse(['success' => true]);
    }
}
