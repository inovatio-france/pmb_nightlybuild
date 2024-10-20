<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProfilesImportEntitiesController.php,v 1.2 2024/08/01 14:34:42 dgoron Exp $

namespace Pmb\ImportExport\Controller;

use Pmb\ImportExport\Models\ProfileImport\ProfileImportEntity;
use Pmb\ImportExport\Orm\ProfileImportEntityOrm;

class ProfilesImportEntitiesController extends ImportExportController
{

    protected const VUE_NAME = "importexport/profilesImportEntities";

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
        $profileImportEntity = new ProfileImportEntity();
        $this->render([
            "list" => $profileImportEntity->getList(),
        ]);
    }

    protected function editAction()
    {
        global $id;

        $id = intval($id);
        if (ProfileImportEntityOrm::exist($id)) {
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

        $data['entity'] = new ProfileImportEntity($id);
        return $data;
    }

	public function save()
	{
	    if (!empty($this->data->numProfile)) {
	        $profileImportEntity = new ProfileImportEntity($this->data->id);
	        $profileImportEntity->numProfile = $this->data->numProfile;
	        $profileImportEntity->setFromForm($this->data);
	        $profileImportEntity->save();
	        $this->ajaxJsonResponse($profileImportEntity);
	    }
	}

	public function remove()
	{
	    
	    $profileImportEntity = new ProfileImportEntity($this->data->id);
	    $profileImportEntity->remove();
	    $this->ajaxJsonResponse([ 'success' => true ]);
	}
}

