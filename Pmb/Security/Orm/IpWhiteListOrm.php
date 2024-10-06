<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: IpWhiteListOrm.php,v 1.1 2024/10/01 15:00:39 qvarin Exp $

namespace Pmb\Security\Orm;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

use Pmb\Common\Orm\Orm;

class IpWhiteListOrm extends Orm
{
    public static $instances = [];

    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = null;

    /**
     *
     * @var string
     */
    public static $tableName = "ip_whitelist";

    /**
     *
     * @var string
     */
    public static $idTableName = "id_ip_whitelist";

    /**
     * ID
     *
     * @var int
     */
    public $id_ip_whitelist = 0;

    /**
     * Address IP
     *
     * @var string
     */
    public $ip_whitelist_ip = "";
}
