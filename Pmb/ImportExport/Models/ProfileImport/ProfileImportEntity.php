<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProfileImportEntity.php,v 1.2 2024/08/01 14:34:42 dgoron Exp $

namespace Pmb\ImportExport\Models\ProfileImport;

use Pmb\ImportExport\Models\ImportExportParserDirectory;
use Pmb\ImportExport\Models\ImportExportRoot;

class ProfileImportEntity extends ImportExportRoot
{
    public $id = 0;
    public $entityType = "";
    public $numProfile = 0;
    public $entitySettings = null;
    
    protected $ormName = "Pmb\ImportExport\Orm\ProfileImportEntityOrm";
    
    public function setFromForm(object $data)
    {
        $this->entityType = $data->entityType;
        $this->entitySettings = $data->entitySettings ?? null;
    }
    
    public function save()
    {
        $orm = new $this->ormName($this->id);
        
        $orm->entity_type = $this->entityType;
        $orm->entity_settings = \encoding_normalize::json_encode($this->entitySettings);
        $orm->num_profile = $this->numProfile;
        $orm->save();
        if(!$this->id) {
            $this->id = $orm->id_entity;
        }
        return $orm;
    }
    
    public static function getRdfFieldsEntityIntegrator($type)
    {
        $fields = array(); 
        $className = "rdf_entities_integrator_".$type;
        $rdf_entities_integrator = new $className(null);
        $map_fields = $rdf_entities_integrator->get_map_fields();
        foreach ($map_fields as $uri=>$property) {
            $fields[] = array('value' => $uri, 'label' => $property);
        }
        return $fields;
    }
    
    public static function getEntitiesTypes()
    {
        $entitiesTypes = array();
        $parser = ImportExportParserDirectory::getInstance();
        $manifests = $parser->getManifests("Pmb/ImportExport/Models/Entities");
        foreach ($manifests as $manifest) {
            if (!empty($manifest->type)) {
                $messages = $manifest->namespace::getMessages();
                $entitiesTypes[] = array(
                    'value' => $manifest->type,
                    'settings' => $manifest->settings,
                    'msg' =>  $messages,
                    'fields' => static::getRdfFieldsEntityIntegrator($manifest->type)
                );
            }
        }
        return $entitiesTypes;
    }
    
    public function remove()
    {
        $orm = new $this->ormName($this->id);
        $orm->delete();
    }
}
