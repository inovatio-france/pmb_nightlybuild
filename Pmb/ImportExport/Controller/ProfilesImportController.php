<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProfilesImportController.php,v 1.5 2024/07/26 11:45:37 dgoron Exp $

namespace Pmb\ImportExport\Controller;

use Pmb\ImportExport\Models\ProfileImport;
use Pmb\ImportExport\Orm\ProfileImportOrm;
use Pmb\ImportExport\Models\ProfileImport\ProfileImportEntity;

class ProfilesImportController extends ImportExportController
{

    protected const VUE_NAME = "importexport/profilesImport";

    public function proceed()
    {
        switch($this->data->action) {
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
        $profileImport = new ProfileImport();
        $this->render([
            "list" => $profileImport->getList(),
        ]);
    }

    protected function editAction()
    {
        global $id;

        $id = intval($id);
        if (ProfileImportOrm::exist($id)) {
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

        $data['profile'] = new ProfileImport($id);
        $data['entitiesTypes'] = ProfileImportEntity::getEntitiesTypes();
        return $data;
    }

	public function save()
	{
	    $profileImport = new ProfileImport($this->data->id);
	    $profileImport->setFromForm($this->data);
	    $profileImport->save();
	    $this->ajaxJsonResponse($profileImport);
	}

	public function remove()
	{
	    
	    $profileImport = new ProfileImport($this->data->id);
	    $profileImport->remove();
	    $this->ajaxJsonResponse([ 'success' => true ]);
	}
}

