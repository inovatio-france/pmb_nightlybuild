<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: GestionLoginAttemptModel.php,v 1.2 2024/10/04 08:10:44 dbellamy Exp $

namespace Pmb\Security\Models;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

use DateTime;
use Pmb\Security\Library\LoginAttemptInterface;
use Pmb\Security\Orm\GestionLoginAttemptOrm;

class GestionLoginAttemptModel implements LoginAttemptInterface
{
    /**
     * Id de l'utilisateur
     *
     * @var int
     */
    private $id;

    /**
     * ORM
     *
     * @var GestionLoginAttemptOrm
     */
    private $orm;

    /**
     * Constructeur
     *
     * @param integer $id
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
        $this->orm = new GestionLoginAttemptOrm($this->id);
    }

    /**
     * Enregistre le login
     *
     * @param string $ip
     * @param string $login
     * @param boolean $success
     * @return void
     */
    public function log(string $ip, string $login, bool $success): void
    {
        $orm = new GestionLoginAttemptOrm();

        $orm->gestion_login_attempt_ip = $ip;
        $orm->gestion_login_attempt_login = $login;
        $orm->gestion_login_attempt_success = $success ? 1 : 0;
        $orm->gestion_login_attempt_time = date('Y-m-d H:i:s');

        $orm->save();
    }

    /**
     * Compte le nombre de tentatives
     *
     * @param string $ip
     * @param integer $seconds
     * @return integer
     */
    public function countFailed(string $ip): int
    {
        return $this->orm->countFailed($ip);
    }

    /**
     * Compte le nombre de tentatives
     *
     * @param string $ip
     * @param string $login
     * @param integer $seconds
     * @return integer
     */
    public function countFailedForLogin(string $ip, string $login): int
    {
        return $this->orm->countFailedForLogin($ip, $login);
    }

    /**
     * Retourne le dernier login
     *
     * @param string $ip
     * @return LoginAttemptInterface
     */
    public function getLastFailed(string $ip): LoginAttemptInterface
    {
        $orm = $this->orm->fetchLastFailed($ip);
        return new GestionLoginAttemptModel($orm->gestion_login_attempt_id);
    }

    /**
     * Retourne le timestamp du login
     *
     * @return integer
     */
    public function getTimestamp(): int
    {
        $datetime = new DateTime($this->orm->gestion_login_attempt_time);
        return $datetime->getTimestamp();
    }

    /**
     * Nettoie les logs
     *
     * @param integer $month
     * @return void
     */
    public function cleanLogs(int $month): void
    {
        $datetime = new DateTime();
        $datetime->sub(new \DateInterval('P'.$month.'M'));

        $this->orm->cleanLogs($datetime);
    }

    public function __destruct()
    {
        global $pmb_log_retention;

        $pmb_log_retention = intval($pmb_log_retention);
        $this->cleanLogs($pmb_log_retention);
    }
}
