<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SemanticSearchModel.php,v 1.4 2024/02/19 12:35:49 gneveu Exp $

namespace Pmb\AI\Models;

use Pmb\AI\Orm\AISettingsOrm;
use Pmb\Common\Models\Model;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

class SemanticSearchModel extends Model
{
    // On d�finit le type search_s�mantique a 1

     /**
     * La fonction � getSemanticSearchList � r�cup�re les param�tres AI d'un type sp�cifique.
     *
     * @return la liste des param�tres AI pour la recherche s�mantique.
     */
    public static function getSemanticSearchList()
    {
        $aiSettingsOrm = new AISettingsOrm();
        return $aiSettingsOrm->getAiSettings();
    }
}
