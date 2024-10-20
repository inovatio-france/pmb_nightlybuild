<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SharedListsController.php,v 1.2 2024/06/17 12:06:20 jparis Exp $

namespace Pmb\AI\Controller;

use encoding_normalize;
use Pmb\AI\Models\AiModel;
use Pmb\AI\Models\SemanticSearchModel;
use Pmb\AI\Models\SharedListModel;
use Pmb\AI\Orm\AISettingsOrm;
use Pmb\AI\Orm\AiSharedListOrm;
use Pmb\AI\Views\SemanticSearchView;
use Pmb\AI\Views\SharedListView;
use Pmb\Common\Controller\Controller;
use Pmb\Common\Orm\EmprCategOrm;
use Pmb\Common\Orm\SearchPersoOrm;
use Pmb\Common\Orm\UploadFolderOrm;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class SharedListsController extends Controller
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
            case "save":
                $this->saveAction();
            default:
                $this->formAction();
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

    public function formAction()
    {
        global $pmb_url_base;

        $emprCategOrm = new EmprCategOrm();
        $uploadFolder = new UploadFolderOrm();

        $sharedListView = new SharedListView("AI/sharedList", [
            "sharedlistsdata" => SharedListModel::getSharedListData(),
            "emprcategory" => $emprCategOrm->getEmprCategList(),
            "uploadfolder" => $uploadFolder->getUploadForlderList(),
            "url_webservice" => $pmb_url_base . "rest.php/Ai/"
        ]);
        print $sharedListView->render();
    }


    /* La fonction `saveAction()` est responsable de la sauvegarde des donn�es re�ues lors de la
    soumission du formulaire. */

    public function saveAction()
    {
        try {
            $aiSharedListOrm = new AiSharedListOrm();
            $aiSharedListOrm->id_ai_shared_list = SharedListModel::ID_CONFIG_SHARED_LIST;
            $aiSharedListOrm->settings_ai_shared_list = encoding_normalize::json_encode($this->data);
            $aiSharedListOrm->save();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
