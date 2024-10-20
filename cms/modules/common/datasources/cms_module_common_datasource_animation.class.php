<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_animation.class.php,v 1.1 2021/03/26 08:51:54 qvarin Exp $

use Pmb\Animations\Models\AnimationModel;

if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
    die("no access");

class cms_module_common_datasource_animation extends cms_module_common_datasource
{

    /**
     * On d�fini les s�lecteurs utilisable pour cette source de donn�e
     */
    public function get_available_selectors()
    {
        return array(
            "cms_module_common_selector_animation",
            "cms_module_common_selector_env_var",
            "cms_module_common_selector_global_var"
        );
    }

    /**
     * Sauvegarde du formulaire, revient � remplir la propri�t� parameters et appeler la m�thode parente...
     */
    public function save_form()
    {
        global $selector_choice;

        $this->parameters = array();
        $this->parameters['selector'] = $selector_choice;
        return parent::save_form();
    }

    /**
     * R�cup�ration des donn�es de la source...
     */
    public function get_datas()
    {
        // on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
        $selector = $this->get_selected_selector();
        if ($selector) {
            $animation_ids = $this->filter_datas("animation", array(
                $selector->get_value()
            ));
            if ($animation_ids[0]) {
                $animation = new AnimationModel($animation_ids[0]);
                $animation->getViewData();
                return [
                    "animation" => $animation
                ];
            }
        }
        return false;
    }

    public function get_format_data_structure()
    {
        $animation = new AnimationModel();
        return $animation->getCmsStructure("animation");
    }
}