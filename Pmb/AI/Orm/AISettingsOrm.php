<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AISettingsOrm.php,v 1.9 2024/03/12 14:06:22 qvarin Exp $

namespace Pmb\AI\Orm;

use encoding_normalize;
use Pmb\Common\Orm\Orm;

class AISettingsOrm extends Orm
{
    /**
     * Table name
     *
     * @var string
     */
    public static $tableName = "ai_settings";

    /**
     * Primary Key
     *
     * @var string
     */
    public static $idTableName = "id_ai_setting";

    /**
     *
     * @var integer
     */
    public $id_ai_setting = 0;

    /**
     *
     * @var string|object
     */
    public $settings_ai_settings = "";

    /**
     *
     * @var integer
     */
    public $active_ai_settings = 0;


    /**
     * La fonction � getAiSettings � r�cup�re les param�tres de l'IA, les traite et les renvoie dans un
     * tableau.
     *
     * @return array tableau d'objets de param�tres IA avec des propri�t�s suppl�mentaires telles que
     * `settings_ai_settings`, `active_ai_settings` et `caddie_name`. La propri�t�
     * `settings_ai_settings` est d�cod�e � partir du format JSON, la propri�t� `active_ai_settings`
     * est d�finie directement et la propri�t� `caddie_name` est r�cup�r�e � l'aide d'une fonction
     * d'assistance `caddie::get_data_from_id()` bas�e sur
     */

    public function getAiSettings()
    {
        $return = [];
        $settings = $this->findAll();

        foreach($settings as $setting) {
            $setting->settings_ai_settings = json_decode($setting->settings_ai_settings);
            $setting->active_ai_settings = $setting->active_ai_settings;
            // Petit hack pour r�cupere le nom du caddie... Car dans la pratique on ne le sauvegarde pas
            $setting->settings_ai_settings->caddie_name = \caddie::get_data_from_id($setting->settings_ai_settings->caddie_id)["name"];
            $setting->unsetStructure();
            $return[] = $setting;
        }
        return $return;
    }


    /**
     * La fonction � getAiSettingsById � r�cup�re les param�tres AI par leur ID, convertit les
     * param�tres JSON en objet, supprime la structure et renvoie les param�tres.
     *
     * @param id Le param�tre "id" est l'identifiant des param�tres AI que vous souhaitez r�cup�rer. Il
     * est utilis� pour rechercher les param�tres AI dans la base de donn�es.
     *
     * @return array tableau d?objets de param�tres AI.
     */
    public function getAiSettingsById($id)
    {
        $return = [];
        $settings = $this->findById($id);

        foreach($settings as $setting) {
            $setting->settings_ai_settings = json_decode($setting->settings_ai_settings);
            $setting->unsetStructure();
            $return[] = $setting;
        }
        return $return;
    }


    /**
     * La fonction � getAiSettingsFindAll � r�cup�re tous les param�tres AI, convertit la cha�ne JSON
     * en objet, supprime la propri�t� de structure et renvoie les param�tres modifi�s.
     *
     * @return array tableau d?objets de param�tres IA.
     */
    public static function getAiSettingsFindAll()
    {
        $return = [];
        $settings = self::findAll();

        foreach($settings as $setting) {
            $setting->settings_ai_settings = json_decode($setting->settings_ai_settings);
            $setting->unsetStructure();
            $return[] = $setting;
        }
        return $return;
    }

    /**
     * La fonction � getAiSettingActive � renvoie l'objet des param�tres AI actuels.
     *
     * @return AiSettingsOrm|null
     */
    public static function getAiSettingActive()
    {
        $setting = self::find("active_ai_settings", "1");
        if (!empty($setting)) {
            $setting[0]->settings_ai_settings = json_decode($setting[0]->settings_ai_settings);
            $setting[0]->unsetStructure();
            return $setting[0];
        }
        return null;
    }


    /**
     * La fonction `formatSettingsAiSettings()` est une m�thode au sein de la classe `AISettingsOrm`.
     * Cette fonction est responsable du formatage de la propri�t� `settings_ai_settings` d'un objet.
     *
     * @return \stdClass
     */
    public function formatSettingsAiSettings()
    {
        $settings_ai_settings = is_string($this->settings_ai_settings) ? encoding_normalize::json_decode($this->settings_ai_settings) : $this->settings_ai_settings;
        if (!is_object($settings_ai_settings)) {
            $settings_ai_settings = new \stdClass();
        }
        return $settings_ai_settings;
    }

}
