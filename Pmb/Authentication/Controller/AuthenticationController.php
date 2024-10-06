<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AuthenticationController.php,v 1.9 2023/06/30 13:05:45 gneveu Exp $
namespace Pmb\Authentication\Controller;

use Pmb\Common\Controller\Controller;
use Pmb\Common\Views\VueJsView;
use Pmb\Common\Helper\Helper;
use Pmb\Authentication\Models\AuthenticationParserDirectory;
use Pmb\Authentication\Models\AuthenticationModel;
use Pmb\Authentication\Models\AuthenticationConfig;
if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class AuthenticationController extends Controller
{

    protected $action;

    public function proceed()
    {
        $this->action = $this->data->action;
        switch ($this->data->action) {
            case 'services':
                $this->servicesAction();
                break;
            case 'models':
                $this->modelsAction();
                break;
            case 'config_opac':
                $this->configOpacAction();
                break;
            case 'config_gestion':
                $this->configGestionAction();
                break;
            default:
                $this->defaultAction();
                break;
        }
    }

    /**
     * Generation vue
     *
     * @param array $data
     */
    protected function render($vueName, array $data = [])
    {
        global $pmb_url_base, $opac_url_base;
        $vueJsView = new VueJsView($vueName, array_merge(Helper::toArray($this->data), [
            "url_webservice" => $pmb_url_base . "rest.php/auth/",
            "url_base" => $pmb_url_base,
            "opac_url_base" => $opac_url_base
        ], Helper::toArray($data)));
        print $vueJsView->render();
    }

    public function servicesAction()
    {
        $viewData = $this->getViewBaseData();
        $newVue = new VueJsView("authentication/sources", $viewData);
        print $newVue->render();
    }

    public function modelsAction()
    {
        $viewData = $this->getViewBaseData();
        $newVue = new VueJsView("authentication/models", $viewData);
        print $newVue->render();
    }

    public function configOpacAction()
    {
        $viewData = $this->getconfigOpacData();
        $newVue = new VueJsView("authentication/configOpac", $viewData);
        print $newVue->render();
    }

    public function configGestionAction()
    {
        $viewData = $this->getconfigGestionData();
        $newVue = new VueJsView("authentication/configGestion", $viewData);
        print $newVue->render();
    }

    protected function defaultAction()
    {
        global $include_path, $lang;
        include ("$include_path/messages/help/$lang/admin_security.txt");
    }

    private function getViewBaseData()
    {
        $viewData = AuthenticationModel::getViewData();

        $return = [
            "action" => $this->action
        ];

        return array_merge($viewData, $return);
    }

    private function getconfigOpacData()
    {
        global $security_allow_internal_opac_authentication;

        $viewData = AuthenticationModel::getViewData();

        $return = [
            "action" => $this->action,
            "configs_list" => AuthenticationConfig::getConfigs(AuthenticationModel::OPAC_MODEL),
            "allow_internal_opac_authentication" => intval($security_allow_internal_opac_authentication)
        ];

        return array_merge($viewData, $return);
    }

    private function getconfigGestionData()
    {
        global $security_allow_internal_gestion_authentication;

        $viewData = AuthenticationModel::getViewData();

        $return = [
            "action" => $this->action,
            "configs_list" => AuthenticationConfig::getConfigs(AuthenticationModel::GESTION_MODEL),
            "allow_internal_gestion_authentication" => intval($security_allow_internal_gestion_authentication)
        ];

        return array_merge($viewData, $return);
    }
}

