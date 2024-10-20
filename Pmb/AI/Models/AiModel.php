<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AiModel.php,v 1.10 2024/06/25 09:34:29 qvarin Exp $

namespace Pmb\AI\Models;

use Pmb\Common\Models\Model;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class AiModel extends Model
{
    public const CADDIE_CONTENT_NOTICE = "NOTI";

    public const CADDIE_CONTENT_EXPL = "EXPL";

    public const CADDIE_CONTENT_BULL = "BULL";

    public const TYPE_EXPLNUM_ID = "docnum";

    public const TYPE_OBJECT_ID = "summary";

    /**
     * Recupere le contenu du document nu�mrique depuis la base de donnees.
     *
     * @param int $id Identifiant d'exemplaire numerique a partir duquel commencer le tableau
     * @return string Le contenu des docnums
     */
    public static function getDocnumsContent(int $noticeId = 0)
    {
        $content = array();

        $query = "SELECT explnum_id, explnum_notice, explnum_index_wew FROM explnum
            WHERE explnum_index_wew != ''
            AND explnum_notice = $noticeId";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            $content["explnum_id"] = pmb_mysql_result($result, 0, "explnum_id");
            $content["content"] = \encoding_normalize::utf8_normalize(pmb_mysql_result($result, 0, "explnum_index_wew"));
        }

        return $content;
    }

    /**
     * Recupere le contenu du resume et le titre d'une notice depuis la base de donnees.
     *
     * @param int $id Identifiant de notice
     * @return string Le contenu du resume
     */
    public static function getSummariesContent(int $id = 0)
    {
        $contents = "";

        $query = "SELECT tit1, notice_id, n_resume FROM notices WHERE n_resume != '' AND notice_id = $id";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            $content = pmb_mysql_result($result, 0, "tit1") . " " . pmb_mysql_result($result, 0, "n_resume");
            $contents = \encoding_normalize::utf8_normalize($content);
        }

        return $contents;
    }

    /**
     * La fonction `getEntityDataIa` r�cup�re les ID d'objet de la table `caddie_content` en
     * fonction de l'ID de caddie donn� et d'une valeur de flag NULL.
     *
     * @param int id Le param�tre "id" est un entier qui repr�sente l'ID d'un caddie.
     * @param int limit Le nombre d'ID d'objet � s�lectionner.
     * @param string type de caddie utilis�
     *
     * @return array tableau d?ID d?objet.
     */

    public static function getEntityDataAi(int $id, object $indexation_choice, $limit = 0, string $type = self::CADDIE_CONTENT_NOTICE)
    {
        $return = array();
        $query = "
            SELECT object_id
            FROM caddie_content
            WHERE caddie_id = " . intval($id) . "
            AND flag IS NULL
        ";

        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
        }
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_assoc($result)) {
                $tab = array();
                self::updateCaddieFlag($row["object_id"]);
                $tab = [
                    "entity_data" => [
                        "object_id" => $row["object_id"],
                        "metadata" => AiModel::getMetaDataRecord($row["object_id"])
                    ]
                ];
                $hasContent = false;

                if($indexation_choice->docnum) {
                    $content = self::getDocnumsContent($row['object_id']);
                    if (!empty($content)) {
                        $tab["entity_data"]["explnum_id"] = $content["explnum_id"];
                        $tab["content"] = $content["content"];
                        $tab["type"] = self::TYPE_EXPLNUM_ID;
                        $hasContent = true;
                    }
                }

                if($indexation_choice->summary) {
                    $content = self::getSummariesContent($row['object_id']);
                    if (!empty($content)) {
                        $tab["content"] = $content;
                        $tab["type"] = self::TYPE_OBJECT_ID;
                        $hasContent = true;
                    }
                }

                if ($hasContent) {
                    $return[] = $tab;
                }
            }
        }

        return $return;
    }

    /**
     * La fonction met � jour la valeur de l'indicateur � 1 dans la table caddie_content pour un
     * object_id sp�cifique.
     *
     * @param int id Le param�tre "id" est un entier qui repr�sente l'identifiant de l'objet du contenu
     * du caddy qui doit �tre mis � jour.
     */

    public static function updateCaddieFlag(int $id)
    {
        $query = "
            UPDATE caddie_content
            SET flag = 1
            WHERE object_id = " . intval($id) . "
        ";
        pmb_mysql_query($query);
    }

    /**
     * La fonction "GetNbEntriesCaddieContent" renvoie le nombre d'entr�es dans la table qui ne sont pas cocher
     * "caddie_content".
     *
     * @return le nombre d'entr�es dans la table "caddie_content".
     */

    public static function GetNbEntriesCaddieContent(int $id)
    {
        $query = "SELECT count(*) FROM caddie_content WHERE caddie_id = " . intval($id) . " AND flag IS NULL";
        $result = pmb_mysql_query($query);
        return pmb_mysql_result($result, 0, 0);
    }

    /**
     * La fonction "getCountEntriesInCaddie" renvoie le nombre d'entr�es dans la table
     *
     * @param integer $id
     * @return integer
     */
    public static function getCountEntriesInCaddie(int $id)
    {
        $query = "SELECT count(*) FROM caddie_content WHERE caddie_id = " . intval($id);
        $result = pmb_mysql_query($query);
        return pmb_mysql_result($result, 0, 0);
    }



    /**
     * La fonction unFlagAllElementInCaddie met � jour la colonne flag � NULL pour toutes les lignes
     * de la table caddie_content o� l'id_caddie correspond � l'identifiant donn�.
     *
     * @param int id Le param�tre "id" est un entier qui repr�sente l'ID du caddie.
     */
    public static function unFlagAllElementInCaddie(int $id)
    {
        $query = "
             UPDATE caddie_content
             SET flag = NULL
             WHERE caddie_id = " . intval($id) . "
         ";
        pmb_mysql_query($query);
    }

    /**
     * La fonction `setActiveSemanticSearch` met � jour le champ `active_ai_settings` dans la table
     * `ai_settings` � 0 pour tous les enregistrements, puis le d�finit � 1 pour un enregistrement
     * sp�cifique en fonction de l'ID fourni.
     *
     * @param int id La fonction `setActiveSemanticSearch` est utilis�e pour mettre � jour le champ
     * `active_ai_settings` dans la table `ai_settings`. Il d�finit d'abord tous les �
     * active_ai_settings � sur 0, puis d�finit les � active_ai_settings � sur 1 pour un �
     * id_ai_setting � sp�cifique.
     */
    public static function setActiveSemanticSearch(int $id)
    {
        $query = "
            UPDATE ai_settings
            SET active_ai_settings = 0
        ";
        pmb_mysql_query($query);

        $query = "
            UPDATE ai_settings
            SET active_ai_settings = 1
            WHERE id_ai_setting = " . intval($id) . "
        ";
        pmb_mysql_query($query);
    }

    /**
     * La fonction `getMetaDataRecord` dans la classe `AiModel` est charg�e de r�cup�rer les
     * informations de m�tadonn�es li�es � un enregistrement sp�cifique identifi� par le `` fourni.
     *
     * @param integer $id
     * @return string
     */
    public static function getMetaDataRecord(int $id)
    {
        global $msg;

        $recordData = new \record_datas($id);
        $metaData = $msg['isbd_editeur'] . " : " . self::getPublisherRecord($recordData->get_publishers()) . "\n";
        $metaData .= $msg['authors'] . " : " . self::getResponsabilitesRecord($recordData->get_responsabilites()["auteurs"]) . "\n";
        $metaData .= $msg['252'] . " : " . $recordData->get_year() . "\n";
        $metaData .= $msg['notice_tpl_import_error_typdoc'] . " : " . $recordData->get_tdoc() . "\n";

        return $metaData;
    }

    /**
     * La fonction `getPublisherRecord()` de la classe `AiModel` est charg�e de g�n�rer une
     * cha�ne format�e qui contient des informations sur les �diteurs associ�s � un enregistrement sp�cifique.
     *
     * @param \editeur[] $publishers
     * @return string
     */
    public static function getPublisherRecord($publishers)
    {
        $return = "";
        if(empty($publishers)) {
            return $return;
        }

        foreach ($publishers as $publisher) {
            $return .= $publisher->isbd_entry . " ";
        }
        return $return;
    }

    /**
     * La fonction `getResponsabilitesRecord()` de la classe `AiModel` est charg�e de g�n�rer
     * une cha�ne format�e qui contient des informations sur les responsabilit�s ou les auteurs
     * associ�s � un enregistrement sp�cifique.
     *
     * @param \auteur[] $authors
     * @return string
     */
    public static function getResponsabilitesRecord($authors)
    {
        $return = "";
        if(empty($authors)) {
            return $return;
        }

        foreach ($authors as $author) {
            $return .= $author["auteur_isbd"] . " ";
        }
        return $return;
    }
}
