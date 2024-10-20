<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_animationslist_datasource_all_animations.class.php,v 1.2 2023/01/03 15:02:45 qvarin Exp $
use Pmb\Animations\Orm\AnimationOrm;

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) {
    die("no access");
}

class cms_module_animationslist_datasource_all_animations extends cms_module_common_datasource_animations_list
{

    public function __construct($id = 0)
    {
        parent::__construct($id);
        $this->sortable = true;
        $this->limitable = true;
        $this->paging = true;
    }

    /*
     * On d�fini les s�lecteurs utilisable pour cette source de donn�e
     */
    public function get_available_selectors()
    {
        return array();
    }

    /*
     * R�cup�ration des donn�es de la source...
     */
    public function get_datas()
    {
        $data = array(
            "title" => "",
            "animations" => array()
        );
        $animations = array();
        
        $animationsOrm = AnimationOrm::findAll();
        $index = count($animationsOrm);
        for ($i = 0; $i < $index; $i ++) {
            $animations[] = $animationsOrm[$i]->id_animation;
        }
        
        $data['animations'] = $this->filter_datas('animations', $animations);
        if (! count($data['animations'])) {
            return false;
        }
        
        $data = $this->sort_animations($data['animations']);
        $data['title'] = "";
        
        // Pagination
        if ($this->paging && isset($this->parameters['paging_activate']) && $this->parameters['paging_activate'] == "on") {
            $data['paging'] = $this->inject_paginator($data['animations']);
            $data['animations'] = $this->cut_paging_list($data['animations'], $data['paging']);
        } elseif ($this->parameters["nb_max_elements"] > 0) {
            $data['animations'] = array_slice($data['animations'], 0, $this->parameters["nb_max_elements"]);
        }
        
        return $data;
    }
}