<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SourcesController.php,v 1.10 2024/07/23 10:29:43 rtigero Exp $

namespace Pmb\ImportExport\Controller;

use Pmb\Common\Helper\GlobalContext;
use Pmb\ImportExport\Models\Sources\SourceMaker;
use Pmb\ImportExport\Orm\SourceOrm;

class SourcesController extends ImportExportController
{

    protected const VUE_NAME = "importexport/sources";

    protected const MODEL_PATH = "Pmb/ImportExport/Models/Sources";

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
            if (!SourceOrm::exist($this->data->id)) {
                $this->ajaxError("Unknown source");
            }
            $source = new SourceMaker($this->data->id);
        } else {
            $source = new SourceMaker();
            $source->numScenario = $this->data->numScenario;
        }
        $source->setFromForm($this->data);
        $source->save();
        $this->ajaxJsonResponse($source);
    }

    public function duplicate()
    {
        if (!empty($this->data->id)) {
            if (!SourceOrm::exist($this->data->id)) {
                $this->ajaxError("Unknown source");
            }
            $source = new SourceMaker($this->data->id);
            $newSource = $source->duplicate();
            $this->ajaxJsonResponse($newSource);
        } else {
            $this->ajaxError(GlobalContext::msg('common_failed_save'));
        }
    }

    public function remove()
    {
        if (!SourceOrm::exist($this->data->id)) {
            $this->ajaxError("Unknown source");
        }
        $source = new SourceMaker($this->data->id);
        try {
            $source->remove();
        } catch (\Exception $e) {
            $this->ajaxError($e->getMessage());
        }
        $this->ajaxJsonResponse(['success' => true]);
    }
}
