<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SharedListModel.php,v 1.5 2024/06/24 14:08:15 gneveu Exp $

namespace Pmb\AI\Models;

use Pmb\AI\Orm\AISettingsOrm;
use Pmb\AI\Orm\AiSharedListOrm;
use Pmb\Common\Models\Model;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class SharedListModel extends Model
{
    public const ID_CONFIG_SHARED_LIST = 1;

    public const TYPE_EXPLNUM_ID = "docnum";

    public const TYPE_OBJECT_ID = "summary";


    /**
     * R�cup�re les donn�es de param�trage.
     *
     * @return array
     */
    public static function getSharedListData()
    {
        if (empty(AiSharedListOrm::findAll())) {
            return [];
        }
        $aiSettingsOrm = new AiSharedListOrm(self::ID_CONFIG_SHARED_LIST);
        return $aiSettingsOrm->getAiSharedList();
    }

    /**
     * Marque le champ 'opac_liste_lecture_flag_ia' � 1 pour sp�cifier qu'un enregistrement est index�
     *
     * @param int $idList L'id de la liste.
     * @param int $limit Le nombre maximum d'enregistrements � mettre � jour
     * @param int $flag La valeur du flag � d�finir pour le champ 'opac_liste_lecture_flag_ia'
     * @return void
     */
    public static function setRecordFlagIAInList(int $idList, $limit = 0, int $flag = 0)
    {
        $records = self::getRecordsByListId($idList, $limit);
        if(empty($records)) {
            return;
        }

        $query = "
            UPDATE opac_liste_lecture_notices
            SET opac_liste_lecture_flag_ia = '" . $flag ."'
            WHERE opac_liste_lecture_notice_num IN (" . implode(", ", $records) . ")
            AND opac_liste_lecture_num = '" . $idList . "'
        ";

        pmb_mysql_query($query);
    }

    /**
     * Compte le nombre de enregistrements dans la table 'opac_liste_lecture_notices' qui ne sont pas index�s.
     *
     * @param int $idList L'id de la liste
     * @return int Le nombre d'enregistrements non index�s
     */
    public static function countNotIndexedRecords(int $idList)
    {
        $query = "
            SELECT COUNT(opac_liste_lecture_notice_num)
            FROM opac_liste_lecture_notices
            WHERE opac_liste_lecture_num = '" . $idList . "'
            AND opac_liste_lecture_flag_ia = 0
        ";
        $result = pmb_mysql_query($query);
        return intval(pmb_mysql_result($result, 0, 0));
    }

    /**
     * R�cup�re les identifiants de notices en fonction de l'id d'une liste.
     *
     * @param int $idList L'id de la liste
     * @param int $limit Le nombre maximum d'enregistrements � r�cup�rer
     * @return array
     */
    public static function getRecordsByListId(int $idList, $limit = 0)
    {
        $return = array();

        $query = "
            SELECT opac_liste_lecture_notice_num
            FROM opac_liste_lecture_notices
            WHERE opac_liste_lecture_num = '" . $idList . "'
            AND opac_liste_lecture_flag_ia = 0
            LIMIT " . $limit;

        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_assoc($result)) {
                $return[] = $row['opac_liste_lecture_notice_num'];
            }
        }

        return $return;
    }

    /**
     * La fonction `getEntityDataIa` r�cup�re les ID d'objet de la table `opac_liste_lecture_notices` en fonction de l'ID.
     *
     * @param int id Le param�tre "id" est un entier qui repr�sente l'ID d'une liste de lecture.
     * @param int limit Le nombre d'ID d'objet � s�lectionner.
     *
     * @return array tableau d'ID d'objet.
     */
    public static function getEntityDataAi(int $id, object $indexation_choice, $limit = 0)
    {
        $return = array();

        $records = self::getRecordsByListId($id, $limit);
        foreach ($records as $record) {
            $tab = [
                "entity_data" => [
                    "object_id" => $record,
                    "shared_list_id" => $id,
                    "metadata" => AiModel::getMetaDataRecord($id)
                ]
            ];

            $hasContent = false;

            if ($indexation_choice->docnum) {
                $content = AiModel::getDocnumsContent($record);
                if (!empty($content)) {
                    $tab["entity_data"]["explnum_id"] = $content["explnum_id"];
                    $tab["content"] = $content["content"];
                    $tab["type"] = self::TYPE_EXPLNUM_ID;

                    $hasContent = true;
                }
            }

            if ($indexation_choice->summary) {
                $content = AiModel::getSummariesContent($record);
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
        return $return;
    }

    /**
     * Retourne une structure de base pour supprimer l'indexation.
     *
     * @param string $key La cl� de l'indexation
     * @param mixed $value La valeur de l'indexation
     * @param string $operator L'op�rateur pour l'indexation
     * @param string|null $logicalOperator L'op�rateur logique pour l'indexation
     * @return array
     */
    private static function getBasicStructureToDeleteIndexation(string $key, $value, string $operator = '=', string $logicalOperator = null)
    {
        $structure = [
            'key' => $key,
            'value' => $value,
            'operator' => $operator,
        ];

        if ($logicalOperator) {
            $structure['logical_operator'] = $logicalOperator;
        }

        return $structure;
    }

    /**
     * Renvoie les structures en fonction du type pour la suppression de l'indexation.
     *
     * @param string $type
     * @param mixed $idList
     * @param mixed|null $ids Les IDs � supprimer
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function getStructureToDeleteIndexation(string $type, $idList, $ids = null)
    {
        $structures = [];

        switch ($type) {
            case 'deleteList':
                $structures[] = self::getBasicStructureToDeleteIndexation('shared_list_id', $idList);
                break;

            case 'deleteLists':
                $structures[] = self::getBasicStructureToDeleteIndexation('shared_list_id', $ids, 'in');
                break;

            case 'deleteRecordInList':
                $structures[] = self::getBasicStructureToDeleteIndexation('shared_list_id', $idList);
                $structures[] = self::getBasicStructureToDeleteIndexation('object_id', $ids, '=', 'AND');
                break;

            case 'deleteRecordsInList':
                $structures[] = self::getBasicStructureToDeleteIndexation('shared_list_id', $idList);
                $structures[] = self::getBasicStructureToDeleteIndexation('object_id', $ids, 'in', 'AND');
                break;

            case 'deleteDocnumInList':
                $structures[] = self::getBasicStructureToDeleteIndexation('shared_list_id', $idList);
                $structures[] = self::getBasicStructureToDeleteIndexation('docnum_id', $ids, '=', 'AND');
                break;

            default:
                throw new \InvalidArgumentException("Invalid type: $type");
        }

        return $structures;
    }
}
