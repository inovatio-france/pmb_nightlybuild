<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DSIMigration.php,v 1.25 2024/10/02 14:23:32 jparis Exp $

namespace Pmb\DSI\Models;

use Pmb\Common\Helper\Helper;
use Pmb\DSI\Models\Channel\Cart\CartChannel;
use Pmb\DSI\Models\Channel\Mail\MailChannel;
use Pmb\DSI\Models\Event\Periodical\PeriodicalEvent;
use Pmb\DSI\Models\Item\SimpleItem;
use Pmb\DSI\Models\SubscriberList\RootSubscriberList;
use Pmb\DSI\Models\SubscriberList\Subscribers\Subscriber;
use Pmb\DSI\Models\View\CartSimpleView\CartSimpleView;
use Pmb\DSI\Models\View\ExportView\ExportView;
use Pmb\DSI\Models\View\GroupView\GroupView;
use Pmb\DSI\Models\View\PreviousDSIView\PreviousDSIView;
use Pmb\DSI\Orm\TagOrm;
use search;
use stdClass;
use bannette;
use classement;
use equation;

class DSIMigration
{

    protected $bannette = null;
    protected $diffusion = null;
    protected $lastDiffusion = null;
    protected $isDiffusionCart = false;
    protected $tags = array();
    protected $product = null;
    protected $nbMigrateBannette = 0;
    protected $nbBannette = 0;

    /**
     * Migration de l'ancien module vers le nouveau module DSI.
     *
     * @return void
     */
    public function migrate(): void
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        $query = "SELECT id_bannette FROM bannettes";
        $result = pmb_mysql_query($query);
        $this->nbBannette = pmb_mysql_num_rows($result);
        if ($this->nbBannette) {
            while ($row = pmb_mysql_fetch_assoc($result)) {
                $this->bannette = bannette::get_instance($row['id_bannette']);

                // Cr�e la diffusion de type mail et ces �l�ments.
                $this->migrateAll();

                // Si la bannette diffuse dans un panier
                if (!empty($this->bannette->num_panier)) {
                    $this->isDiffusionCart = true;
                    $this->lastDiffusion = $this->diffusion;

                    // On cr�e une deuxi�me diffusion
                    $this->migrateAll();

                    // On ajoute les deux diffusions � un produit
                    $this->createProduct();
                }

                $this->isDiffusionCart = false;
                $this->nbMigrateBannette++;
            }
        }

        self::showFinishForm($this->nbBannette, $this->nbMigrateBannette);
    }

    /**
     * Cr�e un produit li� aux diffusions (mail et panier)
     *
     * @return void
     */
    protected function createProduct(): void
    {
        $this->product = new Product();
        $this->product->name = $this->bannette->nom_bannette;
        $this->product->status = 1;

        $this->product->create();
        $this->migrateSubscribers("product", "products");
        $this->migrateTrigger("product");

        $this->createDiffusionProduct($this->lastDiffusion->id, $this->product->id);
        $this->createDiffusionProduct($this->diffusion->id, $this->product->id);
    }

    /**
     * Cr�e le lien entre une diffusion et un produit
     *
     * @param int $diffusionId Identifiant de la diffusion
     * @param int $productId Identifiant du produit
     * @return void
     */
    protected function createDiffusionProduct($diffusionId, $productId): void
    {
        $diffusionProduct = new DiffusionProduct($diffusionId, $productId);
        $diffusionProduct->active = true;
        $diffusionProduct->create();
    }

    /**
     * Migration de tous les �l�ments li�s � une bannette vers une diffusion
     *
     * @return void
     */
    protected function migrateAll(): void
    {
        $this->diffusion = new Diffusion();
        $this->migrateBannette();
        $this->migrateEquations();
        $this->migrateClassements();
        $this->migrateDescriptors();
        $this->migrateSubscribers("diffusion", "diffusions");
        $this->migrateChannel();
        $this->migrateView();
        $this->migrateAttachments();
        $this->migrateTrigger("diffusion");
        $this->migrateHistory();
    }

    /**
     * Affiche le formulaire permettant de lancer la migration
     */
    public static function showStartForm(): void
    {
        global $msg, $url_base;

        $info = strip_tags(str_replace('\n', '<br>', $msg['dsi_migrate_explanation']), ['<br>', '<b>']);

        $form = "<h1>" . $msg["dsi_menu_migration_definition"] . "</h1>";
        $form .= "<p>" . $info . "</p>";
        $form .= static::getForm();
        print $form;
    }

    /**
     * Affiche le formulaire de fin de la migration
     */
    public static function showFinishForm($nbBannette, $nbMigrateBannette): void
    {
        global $msg, $url_base;

        $info = strip_tags(str_replace('\n', '<br>', $msg['dsi_migrate_finish']), ['<br>', '<b>']);
        $info = str_replace('!!nb_bannette!!', $nbBannette, $info);
        $info = str_replace('!!nb_migrate_bannette!!', $nbMigrateBannette, $info);

        $form = "<h1>" . $msg["dsi_migrate_finish_title"] . "</h1>";
        $form .= "<i class='fa fa-exclamation-triangle' style='font-size: 3rem;' aria-hidden='true'></i>";
        $form .= "<p>" . $info . "</p>";
        $form .= "<button class='bouton' onclick='goDsi()'>" . $msg['dsi_migrate_finish_button'] . "</button>";
        $form .= "
        <script>
            function goDsi() {
                document.location = './dsi.php';
            }
        </script>";

        print $form;
    }

    /**
     * Migration des informations de base d'une bannette
     *
     * @return void
     */
    protected function migrateBannette(): void
    {
        global $msg;

        $name = $this->bannette->nom_bannette;

        if ($this->isDiffusionCart) {
            $name = $this->bannette->nom_bannette . ' - ' . $msg["dsi_migrate_label_cart"];
        }

        $this->diffusion->name = $name;
        $this->diffusion->settings->opacName = $this->bannette->comment_public;
        $this->diffusion->settings->nb_history_saved = 0;
        if ($this->bannette->diffusions_history) {
            $this->diffusion->settings->nb_history_saved = intval($this->bannette->archive_number);
        }

        // On force la diffusion en non automatique pour eviter les envois de bannettes "mortes"
        $this->diffusion->automatic = 0;
        //$this->diffusion->automatic = intval($this->bannette->bannette_auto);

        $this->diffusion->settings->opacVisibility = !empty($this->bannette->bannette_opac_accueil) ? true : false;
        $this->diffusion->settings->opacVisibilityCateg = $this->bannette->categorie_lecteurs;
        $this->diffusion->settings->opacVisibilityGroups = $this->bannette->groupe_lecteurs;
        $this->diffusion->numStatus = 1;

        //Gestion des bannettes priv�es
        if ($this->bannette->proprio_bannette) {
            $this->diffusion->settings->isPrivate = true;
            $this->diffusion->settings->idEmpr = intval($this->bannette->proprio_bannette);
        }
        $this->diffusion->create();
    }

    /**
     * Migration des informations d'une bannette vers un canal
     *
     * @return void
     */
    protected function migrateChannel(): void
    {
        if (!$this->isDiffusionCart) {
            // Canal de type mail
            $channel = new MailChannel();
            $channel->type = 1;
            $channel->settings->mail_choice = "mail_simple";

            // Mail avec export ou/et un PDF
            if (!empty($this->bannette->typeexport) || !empty($this->bannette->document_generate)) {
                $channel->settings->mail_choice = "mail_attachments";
            }

            // Objet du mail
            $channel->settings->mail_object = !empty($this->bannette->comment_public) ? $this->bannette->comment_public : $this->bannette->nom_bannette;

            // Si choix est par d�faut, on prend le mail de l'utilisateur courant, sinon on va le chercher dans le nouveau module de mail
            if ($this->bannette->num_sender == 0) {
                $channel->settings->mail_selected = 0;
            } else {
                $user = new \user($this->bannette->num_sender);
                $mailList = MailChannel::getMailList();

                $channel->settings->mail_selected = intval(array_search($user->get_user_email(), $mailList));
            }

            //Petit flag pour ne pas parser 2x le html � l'envoi,
            //vu que la view ajoute d�j� les infos de base du html, cela �vite de casser l'ancien style
            $channel->settings->noHtmlFormat = 1;
        } else {
            // Canal de type panier
            $channel = new CartChannel();
            $channel->type = 7;

            $channel->settings->emptyCart = true;
        }

        // Creation du canal
        $channel->create();

        // Mise en place du lien entre la diffusion et le canal
        $this->diffusion->numChannel = $channel->id;
        $this->diffusion->channel = $channel;
        $this->diffusion->update();
    }

    /**
     * Migration des informations d'une bannette vers un d�clencheur
     *
     * @return void
     */
    protected function migrateTrigger($propertyName): void
    {
        global $msg;

        // Si la bannette diffuse dans un panier et par mail on est sur un produit pas besoin de d�clencheur pour une diffusion
        if (!empty($this->bannette->num_panier) && $propertyName == "diffusion") {
            return;
        }

        $trigger = new PeriodicalEvent();
        $trigger->name = $this->bannette->nom_bannette . " - " . $msg["dsi_migrate_label_trigger"];
        $trigger->type = 1;
        $trigger->settings->periodical = "daily";
        $trigger->settings->periodical_data = [
            "nbDays" => $this->bannette->periodicite,
            "custom_dates" => [
                "added_dates" => [],
                "removed_dates" => []
            ]
        ];

        if (empty($this->bannette->date_last_envoi)) {
            // Si la date est vide, utiliser la date du jour
            $dateTime = new \DateTime();

            // On fixe l'heure � 09:00 par d�faut
            $dateTime->setTime(9, 0); // Fixer l'heure � 09:00
        } else {
            // Sinon, on utilise la date de derni�re envoi
            $dateTime = new \DateTime($this->bannette->date_last_envoi);
        }

        // R�cup�rer la date au format "Y-m-d"
        $dateFormatted = $dateTime->format('Y-m-d');

        // R�cup�rer l'heure au format "H:i"
        $timeFormatted = $dateTime->format('H:i');

        $trigger->settings->periodical_start = $dateFormatted;
        $trigger->settings->periodical_time = $timeFormatted;
        $trigger->settings->periodical_end = "";

        $trigger->settings->conditions = [
            "emptyAssociatedItem" => [
                "views" => [$this->diffusion->numView]
            ]
        ];

        $trigger->create();

        // Cr�ation du lien entre la diffusion / produit et le d�clencheur
        $className = "Pmb\\DSI\\Models\\Event" . ucfirst($propertyName);
        $eventLink = new $className($trigger->id, $this->$propertyName->id);
        $eventLink->create();

        $this->$propertyName->events[] = $trigger;
    }

    /**
     * Migration des informations d'une bannette vers une liste d'abonn�s
     *
     * @param string $propertyName Nom de la propri�t� concern�e
     * @param string $entityType Type de l'entit�
     * @return void
     */
    protected function migrateSubscribers($propertyName, $entityType): void
    {
        global $dsi_insc_categ;

        $unsubscribers = array();
        $bannetteAbon = array();

        $this->$propertyName->fetchSubscriberList();

        //R�cup�ration des emprunteurs des cat�gories / groupes si le param�tre est actif
        if ($dsi_insc_categ) {
            $rmc = $this->getEmprRMC();
            $this->generateSubscriberListSettings($propertyName, $rmc);
        }

        //Recuperation des abonn�s de la bannette
        $query = "SELECT id_empr, empr_nom, empr_prenom, empr_cb, empr_mail FROM bannette_abon JOIN empr ON id_empr = num_empr WHERE num_bannette=" . $this->bannette->id_bannette;
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_assoc($result)) {
                $bannetteAbon[] = $row['id_empr'];
                //Si on l'a deja dans la RMC pas besoin de l'ajouter
                if (isset($rmc) && in_array($row['id_empr'], $rmc['results'])) {
                    continue;
                }
                $subscriber = $this->addSubscriber($propertyName, $entityType, $row, Subscriber::UPDATE_TYPE_SUBSCRIBER);
                $this->$propertyName->subscriberList->lists->subscribers[] = $subscriber;
            }

            //On d�sinscrit les emprunteurs qui sont dans la RMC mais pas dans la liste de la bannette
            if ($dsi_insc_categ) {
                $unsubscribers = array_diff($rmc['results'], $bannetteAbon);
                if (empty($unsubscribers)) {
                    return;
                }

                //On recupere les donnes des emprunteurs a desinscrire
                $query = "SELECT id_empr, empr_nom, empr_prenom, empr_cb, empr_mail FROM empr WHERE id_empr IN (" . implode(",", $unsubscribers) . ")";
                $result = pmb_mysql_query($query);
                while ($row = pmb_mysql_fetch_assoc($result)) {
                    $this->addSubscriber($propertyName, $entityType, $row, Subscriber::UPDATE_TYPE_UNSUBSCRIBER);
                }
            }
        }

        //On s'assure que tout est bien cr�� et reli�
        if ($this->$propertyName->numSubscriberList == 0) {
            if ($this->$propertyName->subscriberList->source->id == 0) {
                $this->$propertyName->subscriberList->source->create();
            }
            $this->$propertyName->numSubscriberList = $this->$propertyName->subscriberList->source->id;
            $this->$propertyName->update();
        }
    }

    /**
     * Convertit une ligne d'emprunteur en base en subscriber
     * @param string $propertyName Nom de la propri�t� concern�e
     * @param string $entityType Type de l'entit�
     * @param array $data Ligne en base repr�sentant l'emprunteur
     * @param int $updateType Abonnement ou d�sabonnement
     *
     * @return Subscriber
     */
    protected function addSubscriber($propertyName, $entityType, $data, $updateType)
    {
        $subscriber = new stdClass();
        $subscriber->settings = new stdClass();
        $subscriber->name = $data['empr_prenom'] . " " . $data['empr_nom'];
        $subscriber->settings->idEmpr = $data['id_empr'];
        $subscriber->settings->cb = $data['empr_cb'];
        $subscriber->settings->email = $data['empr_mail'];
        $subscriber->updateType = $updateType;
        $subscriber->type = RootSubscriberList::SUBSCRIBER_TYPE_MANUAL;

        $subscriberModel = Subscriber::getInstance($entityType);
        $subscriberModel->setFromForm($subscriber);
        $subscriberModel->setEntity($this->$propertyName->id);
        $subscriberModel->create();

        return $subscriberModel;
    }

    /**
     * Convertit la liste des categories / groupes de la bannette en RMC d'emprunteur
     *
     * @return array
     */
    protected function getEmprRMC(): array
    {
        global $msg;
        $result = array(
            "results" => array(),
            "serializedSearch" => "",
            "humanQuery" => "",
            "search" => ""
        );
        $searchInstance = new search(false, "search_fields_empr");
        $searchInstance->destroy_global_env();
        $i = 0;

        //On transforme la s�lection des cat�gories / groupes en RMC dans la nouvelle DSI
        if (!empty($this->bannette->categorie_lecteurs)) {
            $fieldIndex = array_search($msg["empr_categ"], array_column($searchInstance->fixedfields, "TITLE", "ID"));
            if ($fieldIndex !== false) {
                $field = $searchInstance->fixedfields[$fieldIndex];
                $this->setRMCField($field, $i, $this->bannette->categorie_lecteurs);
                $i++;
            }
        }
        if (!empty($this->bannette->groupe_lecteurs)) {
            $fieldIndex = array_search($msg["groupe_empr"], array_column($searchInstance->fixedfields, "TITLE", "ID"));
            if ($fieldIndex !== false) {
                $field = $searchInstance->fixedfields[$fieldIndex];
                $this->setRMCField($field, $i, $this->bannette->groupe_lecteurs);
            }
        }

        //On lance la recherche et on r�cup�re les donn�es
        $query = "SELECT id_empr FROM " . $searchInstance->make_search();
        $res = pmb_mysql_query($query);
        while ($row = pmb_mysql_fetch_assoc($res)) {
            $result["results"][] = intval($row["id_empr"]);
        }
        $result["serializedSearch"] = $searchInstance->serialize_search();
        $result["humanQuery"] = $searchInstance->make_human_query();
        $result["search"] = $searchInstance->json_encode_search();

        return $result;
    }

    /**
     * Ajoute les globales n�cessaires � la recherche
     * @param array $field Tableau contenant les param�tres du champ de recherche
     * @param int $i Indice du champ dans la recherche
     * @param array $values Valeurs du champ
     */
    protected function setRMCField($field, $i, $values)
    {
        global $search;

        if (is_null($search)) {
            $search = array();
        }

        $fieldName = "f_" . $field["ID"];
        $search[] = $fieldName;
        $op = "op_" . $i . "_" . $fieldName;
        $field_ = "field_" . $i . "_" . $fieldName;
        global ${$op}, ${$field_};
        ${$op} = "EQ";
        ${$field_} = $values;

        //Si on a les 2 champs on rajoute l'inter
        if (count($search) == 2) {
            $inter = "inter_1_" . $search[1];
            global ${$inter};
            ${$inter} = "or";
        }
    }

    /**
     * MAJ de la source d'abonn�s en fonction d'une RMC d'emprunteurs
     * @param string $propertyName Nom de la propri�t� concern�e
     * @param array $rmc Tableau contenant les informations de la RMC
     *
     * @return void
     */
    protected function generateSubscriberListSettings($propertyName, $rmc): void
    {
        //On set les parametres de la subscriberlist
        $manifestSource = DSIParserDirectory::getInstance()->getManifestByNamespace("Pmb\\DSI\\Models\\Source\\Subscriber\\Entities\\Empr\\EmprList");
        $manifestSelector = DSIParserDirectory::getInstance()->getManifestByNamespace("Pmb\\DSI\\Models\\Selector\\Subscriber\\Empr\\RMC\\EmprRMCSelector");

        //Param�trage de la source
        $settings = new stdClass();
        $settings->subscriberListSource = new stdClass();
        $settings->subscriberListSource->id = $manifestSource->id;
        $settings->subscriberListSource->name = $manifestSource->name;
        $settings->subscriberListSource->namespace = $manifestSource->namespace;

        //Param�trage du s�lecteur
        $settings->subscriberListSource->subscriberListSelector = new stdClass();
        $settings->subscriberListSource->subscriberListSelector->name = $manifestSelector->name;
        $settings->subscriberListSource->subscriberListSelector->namespace = $manifestSelector->namespace;

        //Param�trage de la RMC dans le s�lecteur
        $settings->subscriberListSource->subscriberListSelector->data = new stdClass();
        $settings->subscriberListSource->subscriberListSelector->data->human_query = $rmc['humanQuery'];
        $settings->subscriberListSource->subscriberListSelector->data->search = $rmc['search'];
        $settings->subscriberListSource->subscriberListSelector->data->search_serialize = $rmc['serializedSearch'];

        //MAJ de la liste
        $this->$propertyName->subscriberList->source->settings = $settings;
        $this->$propertyName->subscriberList->source->update();
    }

    /**
     * Cr�ation de l'item de la diffusion
     *
     * @return void
     */
    protected function migrateEquations()
    {
        $item = $this->createItem();
        if (!$item) {
            return;
        }

        // Mise en place du lien entre la diffusion et l'item
        $this->diffusion->numItem = $item->id;
        $this->diffusion->item = $item;
        $this->diffusion->update();
    }

    /**
     * Conversion des �quations de la bannette en items de la nouvelle DSI
     *
     * @return SimpleItem|false
     */
    protected function createItem()
    {
        $equations = $this->bannette->get_bannette_equations()->get_equations();
        if (empty($equations)) {
            return false;
        }
        $item = $this->convertEquationToItem($equations);

        return $item;
    }

    /**
     * Conversion d'une �quation en item de la nouvelle DSI
     * @param int $equationId Identifiant de l'�quation
     *
     * @return SimpleItem
     */
    protected function convertEquationToItem($equations): SimpleItem
    {
        $item = new SimpleItem();
        $item->type = TYPE_NOTICE;

        //Pr�paration des settings
        if (is_string($item->settings)) {
            $item->settings = new stdClass();
        }
        $item->settings->namespace = "Pmb\\DSI\\Models\\Source\\Item\\Entities\\Record\\RecordList\\RecordList";

        //Param�trage du s�lecteur
        $item->settings->selector = new stdClass();
        $item->settings->selector->namespace = "Pmb\\DSI\\Models\\Selector\\Item\\Entities\\Record\\RMC\\RecordRMCSelector";

        //On parcourt toutes les �quations et on les merge dans une seule recherche
        $mergedSearch = array("SEARCH" => array());
        foreach ($equations as $equationId) {
            $bannetteEquation = new equation($equationId);
            //On d�s�rialise et on merge les crit�res avec notre recherche
            $currentSearch = unserialize($bannetteEquation->requete);
            foreach ($currentSearch as $key => $value) {
                if (!is_array($value)) {
                    continue;
                }
                $i = count($mergedSearch) - 1;
                if ($key === "SEARCH") {
                    $mergedSearch["SEARCH"] = array_merge($mergedSearch["SEARCH"], $value);
                } else {
                    $mergedSearch[$i] = $value;
                }
            }
            if (count($equations) > 1) {
                //On met un OR entre les �quations
                $mergedSearch[$i]["INTER"] = "or";
            }

            //Le nom est la concat�nation des noms des �quations
            if ($item->name == "") {
                $item->name = $bannetteEquation->nom_equation;
            } else {
                $item->name .= " - " . $bannetteEquation->nom_equation;
            }
        }
        //Param�trage de la RMC

        //On instancie la search et on lui passe le r�sultat du merge des �quations
        $searchInstance = new search();
        $searchInstance->destroy_global_env();
        $mergedSearch = serialize($mergedSearch);
        $searchInstance->unserialize_search($mergedSearch);


        //Ensuite on remplit les param�tres de l'item
        $item->settings->selector->data = new stdClass();
        $item->settings->selector->data->human_query = $searchInstance->make_human_query();
        $item->settings->selector->data->search_serialize = $mergedSearch;
        $item->settings->selector->data->search = $searchInstance->json_encode_search();

        //Cr�ation de l'item
        $item->create();

        return $item;
    }

    /**
     * Migration des informations du classement d'une bannette vers un tag
     *
     * @return void
     */
    protected function migrateClassements()
    {
        global $classements;

        if (($this->bannette->nom_classement == "") || (!array_key_exists($this->bannette->num_classement, $classements))) {
            return;
        }

        $this->bannette->num_classement = intval($this->bannette->num_classement);

        switch (intval($classements[$this->bannette->num_classement][0])) {
            case 1:
                //Reprise du nom gestion
                $tagName = $this->bannette->nom_classement;
                break;
            case 2:
                //Reprise du nom OPAC
                $classement = classement::get_instance($this->bannette->num_classement);
                $tagName = $classement->nom_classement_opac;
                break;
            case 3:
                //Nouveau nom
                if (empty($classements[$this->bannette->num_classement][1])) {
                    return;
                }
                $tagName = $classements[$this->bannette->num_classement][1];
                break;
            case 0:
            default:
                //Ne pas reprendre
                return;
        }

        $tag = TagOrm::find("name", $tagName);
        if (empty($tag)) {
            $tag = new Tag();
            $tag->name = $tagName;
            $tag->create();
            $id = $tag->id;
        } else {
            $id = $tag[0]->id_tag;
        }
        $this->diffusion->linkTag($id, $this->diffusion->id);
    }

    /**
     * Migration des informations des descripteurs d'une bannette
     *
     * @return void
     */
    protected function migrateDescriptors()
    {
        $descriptors = $this->bannette->get_bannette_descriptors()->descriptors;

        foreach ($descriptors as $order => $id) {
            $diffusionDescriptors = new DiffusionDescriptors();
            $diffusionDescriptors->setNumNoeud(intval($id));
            $diffusionDescriptors->setNumDiffusion($this->diffusion->id);
            $diffusionDescriptors->setOrder(intval($order));
            $diffusionDescriptors->create();
        }
    }
    /**
     * Migrer les templates de bannette vers une nouvelle vue (Ancienne D.S.I.)
     *
     * @return void
     */
    protected function migrateView(): void
    {
        // On ne cr�ait pas du vue si on est en cr�ation d'une diffusion par panier
        if (!$this->isDiffusionCart) {
            $view = $this->createView();
        } else {
            $view = $this->createCartView();
        }

        // Met � jour l'objet diffusion l'instance de la vue
        $this->diffusion->numView = $view->id;
        $this->diffusion->view = $view;

        $this->diffusion->update();
    }

    /**
     * Cr�e une vue Ancienne D.S.I. a partir d'une bannette
     *
     * @return PreviousDSIView
     */
    protected function createView(): PreviousDSIView
    {
        // Cr�e une nouvelle instance de la vue Ancienne D.S.I.
        $view = new PreviousDSIView();
        $view->type = 15;

        $view->settings = new stdClass();

        // D�finit une limite de notice pour la vue
        $view->settings->limit = intval($this->bannette->nb_notices_diff);

        // D�finit un filtre sur les notices
        if (!empty($this->bannette->update_type)) {
            $namespace = "Pmb\\DSI\\Models\\Filter\\Entities\\Record\\";

            // Si le filtre est de type C (Date de cr�ation > Date de derni�re diffusion) sinon type U (Date de modification > Date de derni�re diffusion)
            if ($this->bannette->update_type == "C") {
                $namespace .= "RecordFilterCreatedAfterDiffusion\\RecordFilterCreatedAfterDiffusion";
            } else {
                $namespace .= "RecordFilterModifiedAfterDiffusion\\RecordFilterModifiedAfterDiffusion";
            }

            // Ajout du filtre
            $view->settings->filters = [
                [
                    "fields" => new stdClass(),
                    "namespace" => $namespace
                ]
            ];
        }

        // Assigne si on affiche le nombre de notice
        $view->settings->displayNbNotice = $this->bannette->bannette_aff_notice_number == 1;

        // Assigne les templates de bannette et de notice � la vue
        $view->settings->bannetteTemplate = intval($this->bannette->bannette_tpl_num);
        $view->settings->noticeTemplate = intval($this->bannette->notice_tpl);

        // Assigne les templates d'en-t�te et de pied de page pour les mails
        $view->settings->headerTemplate = $this->bannette->entete_mail;
        $view->settings->footerTemplate = $this->bannette->piedpage_mail;

        // Cr�e une vue de groupement li�e
        $view->settings->linkedView = $this->createGroupView();

        $view->create();

        return $view;
    }

    /**
     * Cr�e une nouvelle instance de la vue panier li�e � la bannette.
     *
     * @return CartSimpleView La vue panier li�e � la bannette.
     */
    protected function createCartView(): CartSimpleView
    {
        // Cr�e une nouvelle instance de la vue panier.
        $view = new CartSimpleView();
        $view->type = 17;

        $view->settings = new stdClass();

        // Pas de limite de notice pour la diffusion dans un panier
        $view->settings->limit = 0;

        // D�finit un filtre sur les notices
        if (!empty($this->bannette->update_type)) {
            $namespace = "Pmb\\DSI\\Models\\Filter\\Entities\\Record\\";

            // Si le filtre est de type C (Date de cr�ation > Date de dernieure diffusion) sinon type U (Date de modification > Date de dernieure diffusion)
            if ($this->bannette->update_type == "C") {
                $namespace .= "RecordFilterCreatedAfterDiffusion\\RecordFilterCreatedAfterDiffusion";
            } else {
                $namespace .= "RecordFilterModifiedAfterDiffusion\\RecordFilterModifiedAfterDiffusion";
            }

            // Ajout du filtre
            $view->settings->filters = [
                [
                    "fields" => new stdClass(),
                    "namespace" => $namespace
                ]
            ];
        }

        // Assigne le panier a la vue
        $view->settings->cart = [
            "NOTI" => $this->bannette->num_panier
        ];

        $view->create();

        return $view;
    }

    /**
     * Cr�e une vue de groupement en fonction des param�tres de la bannette
     *
     * @return int L'identifiant de la vue cr��e
     */
    protected function createGroupView(): int
    {
        global $msg;

        // Cr�ation de la vue de groupement
        $view = new GroupView();
        $view->type = 11;

        // Configuration par d�faut des param�tres de la vue
        $view->name = $this->bannette->nom_bannette . " - " . $msg["dsi_migrate_label_grouped"];

        $view->settings = new stdClass();
        $view->settings->entityType = 1;
        $view->settings->groups = [];

        // Si le type de groupe est pour les champs personnalis�s sinon pour les facettes
        if ($this->bannette->group_type == 0) {
            $settings = new stdClass();
            $settings->criteria = intval($this->bannette->group_pperso);

            $view->settings->groups[] = [
                "component" => "RecordCustomFields",
                "id" => 2,
                "name" => $msg["dsi_migrate_group_view_cp_name"],
                "settings" => $settings
            ];
        } else {
            $bannetteFacette = $this->bannette->get_instance_bannette_facette();

            // Si aucune facette n'est d�finie, on ne cr�e aucune vue
            if (empty($bannetteFacette->facettes)) {
                return 0;
            }

            // D�finir les types de tri
            $datatypeSort = ["alpha" => 1, "num" => 2, "date" => 3];

            // Boucle sur chaque facette et configuration du groupe
            foreach ($bannetteFacette->facettes as $facette) {
                $settings = new stdClass();
                $settings->criteria = [$facette->critere];

                // Ajouter un sous-crit�re s'il existe
                if (!empty($facette->ss_critere)) {
                    $settings->criteria[] = $facette->ss_critere;
                }

                // Configurer l'ordre et le type de tri
                $settings->order = $facette->order_sort == 0 ? "asc" : "desc";
                $settings->sort = $datatypeSort[$facette->datatype_sort];

                // Assigner les param�tres de la facette au groupement dans la vue
                $view->settings->groups[$facette->order] = [
                    "component" => "RecordFacets",
                    "id" => 1,
                    "name" => $msg["dsi_migrate_group_view_facette_name"],
                    "settings" => $settings
                ];
            }
        }

        $view->create();
        return $view->id;
    }

    /**
     * Migration des pi�ces jointes d'une bannette vers une diffusion
     *
     * @return void
     */
    protected function migrateAttachments(): void
    {
        global $msg;

        // On ne cr�ait pas les pi�ces jointes si on est en cr�ation d'une diffusion par panier
        if (!$this->isDiffusionCart) {
            $this->diffusion->settings->attachments = [];

            // Mail avec export
            if (!empty($this->bannette->typeexport)) {
                $item = $this->createItem();
                $view = $this->createExportView();

                $this->diffusion->settings->attachments[] = [
                    "item" => $item ? $item->id : 0,
                    "view" => $view ? $view->id : 0,
                    "name" => $msg['dsi_migrate_label_attachment_export']
                ];
            }

            if (!empty($this->diffusion->settings->attachments)) {
                $this->diffusion->update();
            }
        }
    }

    /**
     * Cr�e une vue d'export
     *
     * @return ExportView
     */
    protected function createExportView(): ExportView
    {
        // Cr�e une nouvelle instance de la vue d'export
        $view = new ExportView();
        $view->type = 10;

        $view->settings = new stdClass();

        // Type de l'entit� d�fini comme "Notice"
        $view->settings->entityType = 1;

        // D�finit un filtre sur les notices
        if (!empty($this->bannette->update_type)) {
            $namespace = "Pmb\\DSI\\Models\\Filter\\Entities\\Record\\";

            // Si le filtre est de type C (Date de cr�ation > Date de dernieure diffusion) sinon type U (Date de modification > Date de dernieure diffusion)
            if ($this->bannette->update_type == "C") {
                $namespace .= "RecordFilterCreatedAfterDiffusion\\RecordFilterCreatedAfterDiffusion";
            } else {
                $namespace .= "RecordFilterModifiedAfterDiffusion\\RecordFilterModifiedAfterDiffusion";
            }

            // Ajout du filtre
            $view->settings->filters = [
                [
                    "fields" => new stdClass(),
                    "namespace" => $namespace
                ]
            ];
        }

        // Initialisation des param�tres d'exportation sp�cifiques
        $view->settings->exportExplStatuts = [];
        $view->settings->exportExplTypeDocs = [];

        // R�cup�ration du format d'exportation � partir des param�tres de la bannette
        $exportParam = $view->getParameterExportByPath($this->bannette->typeexport);
        $view->settings->exportFormat = intval(is_array($exportParam) ? $exportParam["index"] : 0);

        // D�termine si un lien doit �tre g�n�r� lors de l'exportation
        $view->settings->exportGenerateLink = !empty($this->bannette->param_export["genere_lien"]);

        // Initialisation du param�tre de pr�teur (fix� � 0)
        $view->settings->exportLender = 0;

        // D�finition des liens d'exportation
        $view->settings->exportLinks = [
            "fille" => !empty($this->bannette->param_export["fille"]),
            "horizontale" => false,
            "mere" => !empty($this->bannette->param_export["mere"]),
            "notice_fille" => !empty($this->bannette->param_export["notice_fille"]),
            "notice_horizontale" => false,
            "notice_mere" => !empty($this->bannette->param_export["notice_mere"]),
        ];

        // D�finition des liens d'exportation de s�ries
        $view->settings->exportLinksSeries = [
            "art_link" => !empty($this->bannette->param_export["art_link"]),
            "bull_link" => !empty($this->bannette->param_export["bull_link"]),
            "bulletinage" => !empty($this->bannette->param_export["bulletinage"]),
            "notice_art" => !empty($this->bannette->param_export["notice_art"]),
            "notice_perio" => !empty($this->bannette->param_export["notice_perio"]),
            "perio_link" => !empty($this->bannette->param_export["perio_link"]),
        ];

        // Indicateurs pour enregistrer l'exemplaire et le num�ro d'exemplaire (fix�s � false)
        $view->settings->exportSaveExpl = false;
        $view->settings->exportSaveExplNum = false;

        // Pas de limite de notice pour l'exportation
        $view->settings->limit = 0;

        $view->create();

        return $view;
    }

    public static function getForm()
    {

        global $url_base, $msg;

        $form = "<form class='form-contenu' action='" . $url_base . "?categ=migrate&action=start' method='post' name='dsi_migration'>";

        //Formulaire de mapping entre classements et tags
        $form .= "<h1>" . $msg["dsi_form_classement_title"] . "</h1>
            <p>" . $msg["dsi_form_classements_migration_explain"] . "</p>";

        //On r�cup�re tous les classements utilis�s dans des bannettes
        $query = "SELECT id_classement, nom_classement, classement_opac_name FROM classements WHERE id_classement IN (SELECT DISTINCT num_classement FROM bannettes)";
        $result = pmb_mysql_query($query);
        while ($row = pmb_mysql_fetch_object($result)) {
            $form .= "<fieldset style='padding:15px;'>
                <legend>" . $msg["dsi_form_classement"] . " <strong>" . $row->nom_classement . "</strong></legend>
                <input type='radio' id='skip_label_" . $row->id_classement . "' name='classements[" . $row->id_classement . "][]' value='0'/>
                <label for='skip_label_" . $row->id_classement . "'>" . $msg["dsi_form_classement_skip"] . "</label><br/>
                <input type='radio' id='keep_current_" . $row->id_classement . "' name='classements[" . $row->id_classement . "][]' value='1' checked />
                <label for='keep_current_" . $row->id_classement . "'>" . $msg["dsi_form_classement_keep_current"] . "</label><br/>";
            if ($row->classement_opac_name != "") {
                $form .= "<input type='radio' id='keep_opac_" . $row->id_classement . "' name='classements[" . $row->id_classement . "][]' value='2'/>
                    <label for='keep_opac_" . $row->id_classement . "'>" . $msg["dsi_form_classement_keep_opac"] . " (" . $row->classement_opac_name . ")</label><br/>";
            }
            $form .= "
                    <div class='row'>
                        <div class='colonne3'>
                            <input type='radio' id='new_label_" . $row->id_classement . "' name='classements[" . $row->id_classement . "][]' value='3'/>
                            <label for='new_label_" . $row->id_classement . "'>" . $msg["dsi_form_classement_new_label"] . "</label>
                        </div>
                        <div class='colonne-suite'>
                            <input type='text' name='classements[" . $row->id_classement . "][]' />
                        </div>
                    </div>
                </fieldset>";
        }

        $form .= "<input type='submit' value='" . $msg['dsi_migrate_button'] . "'/>";
        $form .= "</form>";

        return $form;
    }

    /**
     * Migration des historiques de diffusion d'une bannette.
     *
     * @return void
     */
    protected function migrateHistory(): void
    {
        // Si on est sur une diffusion dans un panier
        // if ($this->isDiffusionCart) {
        //     return;
        // }

        $histories = $this->getBannetteHistories();

        // Si aucun historique n'existe
        if (empty($histories) && !empty($this->bannette->date_last_envoi)) {
            $diffusionHistory = new DiffusionHistory();
            $diffusionHistory->diffusion = $this->diffusion;
            $diffusionHistory->numDiffusion = intval($this->diffusion->id);

            $diffusionHistory->state = DiffusionHistory::NODATA;

            $diffusionHistory->totalRecipients = 0;

            $diffusionHistory->date = $this->bannette->date_last_envoi;
            $diffusionHistory->formatedDate = $diffusionHistory->getFormatedDate();

            $diffusionHistory->create();
            return;
        }

        foreach ($histories as $history) {
            $diffusionHistory = new DiffusionHistory();
            $diffusionHistory->diffusion = $this->diffusion;
            $diffusionHistory->numDiffusion = intval($this->diffusion->id);

            // On ajoute le contenu de l'historique
            $diffusionHistory->addContentRenderView($history->diffusion_mail_content);

            // On ajoute le channel
            $diffusionHistory->addContentChannel($this->diffusion->channel);

            // On ajoute les subscribers
            $subscriberList = $this->diffusion->getSubscribers();

            $emprIds = str_replace(['"', '[', ']'], "", $history->diffusion_recipients);
            $emprIds = explode(",", $emprIds);
            $emprIds = array_map('intval', $emprIds);

            $subscribers = [];
            foreach ($subscriberList->lists->subscribers as $subscriber) {
                if (in_array($subscriber->settings->idEmpr, $emprIds)) {
                    $subscribers[] = $subscriber;
                }
            }

            $diffusionHistory->totalRecipients = count($subscribers) ?? 0;

            $contentBufferSubscriber = new ContentBuffer();
            $contentBufferSubscriber->setContent(Helper::toArray($subscribers));
            $contentBufferSubscriber->type = ContentBuffer::CONTENT_TYPES_SUBSCRIBER;
            $diffusionHistory->contentBuffer[ContentBuffer::CONTENT_TYPES_SUBSCRIBER][] = $contentBufferSubscriber;

            $diffusionHistory->state = DiffusionHistory::SENT;

            $diffusionHistory->date = $history->diffusion_date;
            $diffusionHistory->formatedDate = $diffusionHistory->getFormatedDate();

            $diffusionHistory->create();

            $diffusionHistory->saveContentHistory();
        }
    }

    /**
     * Retourne les historiques de diffusion de la bannette en cours de migration
     * @return array Les historiques de diffusion de la bannette
     */
    protected function getBannetteHistories(): array
    {
        $histories = [];
        $query = "SELECT * FROM bannettes_diffusions WHERE diffusion_num_bannette = " . $this->bannette->id_bannette;
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                $histories[] = $row;
            }
        }
        return $histories;
    }
}
