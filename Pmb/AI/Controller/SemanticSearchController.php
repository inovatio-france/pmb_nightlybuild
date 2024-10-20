<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SemanticSearchController.php,v 1.9 2024/09/24 08:20:03 gneveu Exp $

namespace Pmb\AI\Controller;

use Pmb\AI\Models\AiModel;
use Pmb\AI\Models\SemanticSearchModel;
use Pmb\AI\Orm\AISettingsOrm;
use Pmb\AI\Views\SemanticSearchView;

use Pmb\Common\Controller\Controller;
use Pmb\Common\Orm\SearchPersoOrm;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class SemanticSearchController extends Controller
{
    /* La ligne `public  = "list";` d�clare une propri�t� publique appel�e `` et lui
    attribue la valeur "list". Cette propri�t� est utilis�e pour d�terminer l'action par d�faut �
    effectuer par la m�thode `proceed()` si aucun param�tre d'action n'est fourni. Dans ce cas,
    l'action par d�faut est d�finie sur "liste". */
    public $action = "list";


    /**
     * La fonction "proceed" d�termine l'action � entreprendre en fonction du param�tre fourni et
     * ex�cute la m�thode correspondante.
     *
     * @param action Le param�tre ��action�� est utilis� pour d�terminer l'action sp�cifique qui doit
     * �tre effectu�e. Il peut avoir trois valeurs possibles : � modifier �, � ajouter � ou �
     * enregistrer �.
     */

    public function proceed($action = "")
    {
        $this->action = $action;

        switch ($action) {
            case 'edit':
            case 'add':
                $this->getFormAction();
                break;
            case 'save':
                $this->saveAction();
                break;
            case 'delete':
                $this->deleteAction();
                break;
            case 'active_semantic_search':
                $this->activeSemanticSearchAction();
                break;
            default:
                $this->listAction();
        }
    }


    /**
     * La fonction setData d�finit la valeur de la propri�t� data dans une classe PHP.
     *
     * @param data Le param�tre "data" est une variable qui repr�sente les donn�es que vous souhaitez
     * d�finir pour l'objet. Il peut s'agir de n'importe quel type de donn�es, tel qu'une cha�ne, un
     * entier, un tableau ou un objet.
     */

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * La fonction � listAction � cr�e une nouvelle instance de la classe � SemanticSearchView �, en
     * transmettant certaines donn�es, puis restitue et imprime la vue.
     */

    public function listAction()
    {
        $semanticSearchView = new SemanticSearchView("AI/semanticSearch", [
            "action" => $this->action,
            "semanticsearchlist" => SemanticSearchModel::getSemanticSearchList(),
        ]);
        print $semanticSearchView->render();
    }

    /**
     * La fonction `getFormAction` r�cup�re l'action du formulaire pour la recherche s�mantique et
     * restitue la vue correspondante.
     */

    public function getFormAction()
    {
        global $pmb_url_base;

        $semanticSearch = [];
        if (isset($this->data) && is_int($this->data) && AISettingsOrm::exist($this->data)) {
            $aISettingsOrm = new AISettingsOrm($this->data);
            $aISettingsOrm->unsetStructure();
            $semanticSearch = $aISettingsOrm;
        }

        $semanticSearchView = new SemanticSearchView("AI/semanticSearch", [
            "action" => $this->action,
            "semanticsearch" => $semanticSearch,
            "caddieslist" => \caddie_root::get_caddies(["idcaddie", "name"]),
            "url_webservice" => $pmb_url_base . "rest.php/Ai/"
        ]);
        print $semanticSearchView->render();
    }

    /* La fonction `saveAction()` est responsable de la sauvegarde des donn�es re�ues lors de la
    soumission du formulaire. */

    public function saveAction()
    {
        try {
            if (isset($this->data->id) && !empty($this->data->id)) {
                $aiSettingsOrm = new AISettingsOrm($this->data->id);
            } else {
                $aiSettingsOrm = new AISettingsOrm();
            }

            $aiSettingsOrm->settings_ai_settings = json_encode($this->data->settings);
            $aiSettingsOrm->save();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /* La fonction `deleteAction()` est responsable de la suppression d'un enregistrement de la base de
    donn�es. Il cr�e une instance de la classe `AISettingsOrm` avec l'ID de l'enregistrement �
    supprimer, puis appelle la m�thode `delete()` sur cette instance pour supprimer
    l'enregistrement. Si la suppression r�ussit, elle renvoie ��true��. Si une exception se produit
    pendant le processus de suppression, il intercepte l'exception et renvoie le message d'erreur. */

    public function deleteAction()
    {
        $Id = intval($this->data);
        if (!AISettingsOrm::exist($id)) {
            http_response_code(404);
            return "Suppression Impossible";
        }

        try {
            $aISettingsOrm = new AISettingsOrm($id);
            $aISettingsOrm->delete();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /* La fonction `activeSemanticSearchAction()` est une m�thode de la classe
    `SemanticSearchController`. Il se charge d'activer une recherche s�mantique en appelant la
    m�thode `setActiveSemanticSearch()` depuis la classe `AiModel`. */

    public function activeSemanticSearchAction()
    {
        if (!isset($this->data) || !AISettingsOrm::exist(intval($this->data))) {
            http_response_code(404);
            return $this->listAction();
        }
        try {
            AiModel::setActiveSemanticSearch(intval($this->data));
            $this->listAction();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
