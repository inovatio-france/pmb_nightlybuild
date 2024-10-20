<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DsiSource.php,v 1.1 2024/02/26 14:28:55 dbellamy Exp $

namespace Pmb\Dashboard\Models\Widget\Indicator\Sources\Dsi;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

use Pmb\Dashboard\Models\Widget\Common\AbstractSource;

class DsiSource extends AbstractSource
{

    protected static $configuration_filename = "DsiSource";

    public function __construct()
    {
        static::$configuration_filename = __DIR__ . DIRECTORY_SEPARATOR . static::$configuration_filename;
    }

}

