<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProfileImport.php,v 1.3 2024/08/01 14:34:42 dgoron Exp $

namespace Pmb\ImportExport\Models;

use Pmb\Common\Models\Model;
use Pmb\ImportExport\Orm\ProfileImportEntityOrm;
use Pmb\ImportExport\Models\ProfileImport\ProfileImportEntity;

class ProfileImport extends Model
{
    public $id = 0;
    public $profileName = "";
    public $profileComment = "";
    public $profileType = "all_entities";
    public $profileSettings = null;
    public $entities = array();
    
    protected $ormName = "Pmb\ImportExport\Orm\ProfileImportOrm";
    
    protected function fetchData()
    {
        parent::fetchData();
        $this->fetchEntities();
    }
    
    protected function fetchEntities()
    {
        $entitiesOrm = ProfileImportEntityOrm::finds(["num_profile" => $this->id]);
        $this->entities = array();
        foreach ($entitiesOrm as $entityOrm) {
            $this->entities[] = new ProfileImportEntity($entityOrm->id_entity);
        }
    }
    
    public function setFromForm(object $data)
    {
        $this->profileName = $data->profileName;
        $this->profileComment = $data->profileComment;
        $this->profileType = $data->profileType;
        
        $this->entities = [];
        if (!empty($data->entities)) {
            foreach ($data->entities as $data_entity) {
                $profileImportEntity = new ProfileImportEntity($data_entity->id);
                $profileImportEntity->numProfile = $data_entity->numProfile;
                $profileImportEntity->setFromForm($data_entity);
                $this->entities[] = $profileImportEntity;
            }
        }
    }
    
    public function save()
    {
        $orm = new $this->ormName($this->id);
        
        $orm->profile_name = $this->profileName;
        $orm->profile_comment = $this->profileComment;
        $orm->profile_type = $this->profileType;
        
        $orm->save();
        if(!$this->id) {
            $this->id = $orm->id_profile;
        }
        if (!empty($this->entities)) {
            foreach ($this->entities as $profileImportEntity) {
                $profileImportEntity->save();
            }
        }
        return $orm;
    }
    
    public function remove()
    {
        $orm = new $this->ormName($this->id);
        $orm->delete();
    }
}
