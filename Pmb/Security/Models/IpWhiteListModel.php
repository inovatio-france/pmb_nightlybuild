<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: IpWhiteListModel.php,v 1.1 2024/10/01 15:00:39 qvarin Exp $

namespace Pmb\Security\Models;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

use Pmb\Common\Models\Model;
use Pmb\Security\Library\ListInterface;
use Pmb\Security\Orm\IpWhiteListOrm;

class IpWhiteListModel extends Model implements ListInterface
{
    /**
     * Correspond au namespace de l'ORM
     *
     * @var string
     */
    protected $ormName = IpWhiteListOrm::class;

    /**
     * ID
     *
     * @var int
     */
    public $idIpWhitelist = 0;

    /**
     * Address IP
     *
     * @var string
     */
    public $ipWhitelistIp = "";

    /**
     * Ajoute une IP dans la liste noire
     *
     * @param string $ip
     * @return void
     */
    public function add(string $ip): void
    {
        $orm = new IpWhiteListOrm();

        $orm->ip_blacklist_ip = $ip;

        $orm->save();
    }

    /**
     * Supprime une IP
     *
     * @param string $ip
     * @return void
     */
    public function remove(string $ip): void
    {
        $orms = IpWhiteListOrm::find('ip_blacklist_ip', $ip);
        if (!empty($orms)) {
            $orms[0]->delete();
        }
    }

    /**
     * Verifie si une IP est dans la liste noire
     *
     * @param string $ip
     * @return boolean
     */
    public function isInList(string $ip): bool
    {
        return !empty(IpWhiteListOrm::find('ip_blacklist_ip', $ip));
    }

    /**
     * Retourne toutes les IP
     *
     * @return array
     */
    public function getAll(): array
    {
        $orms = IpWhiteListOrm::findAll();

        return array_map(function ($orm) {
            return $orm->ip_blacklist_ip;
        }, $orms);
    }
}
