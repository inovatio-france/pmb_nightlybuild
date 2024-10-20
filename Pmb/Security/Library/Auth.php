<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Auth.php,v 1.4 2024/10/15 12:19:16 qvarin Exp $

namespace Pmb\Security\Library;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

use Pmb\Security\Models\GestionLoginAttemptModel;
use Pmb\Security\Models\IpBlackListModel;
use Pmb\Security\Models\IpWhiteListModel;
use Pmb\Security\Models\OpacLoginAttemptModel;
use Pmb\Common\Helper\IPTools;

class Auth
{
    /**
     * Tentatives de connexion
     *
     * @var LoginAttemptInterface
     */
    private $loginAttempt;

    /**
     * Liste blanche
     *
     * @var ListInterface
     */
    private $whiteList;

    /**
     * Liste noire
     *
     * @var ListInterface
     */
    private $blackList;

    /**
     * Adresse IP
     *
     * @var string
     */
    private $ip;

    /**
     * Login
     *
     * @var string
     */
    private $login;

    /**
     * Active le log des tentatives
     *
     * @var boolean
     */
    private $activeLogLoginAttempts = true;

    /**
     * Nombre de tentatives de tentatives en echec avant de notifier
     *
     * @var integer
     */
    private $notifyAfterFailures = 5;

    /**
     * Nombre de tentatives de tentatives en echec avant de bloquer
     *
     * @var integer
     */
    private $blockAfterFailures = 5;

    /**
     * Duree de blocage (en secondes)
     *
     * @var integer
     */
    private $blockDuration = 300;

    /**
     * Constructeur
     *
     * @param LoginAttemptInterface $loginAttempt
     * @param ListInterface $whiteList
     * @param ListInterface $blackList
     * @param string $login
     * @param string $ip
     */
    private function __construct(
        LoginAttemptInterface $loginAttempt,
        ListInterface $whiteList,
        ListInterface $blackList,
        string $login,
        string $ip
    ) {

        $this->loginAttempt = $loginAttempt;
        $this->whiteList = $whiteList;
        $this->blackList = $blackList;

        $this->login = $login;
        $this->ip = !empty($ip) ? $ip : IPTools::getIP();

        $this->loadParameters();
    }

    /**
     * Chargement des parametres
     *
     * @return void
     */
    private function loadParameters()
    {
        if (defined('GESTION')) {
            global $pmb_active_log_login_attempts, $pmb_block_after_failures;
            global $pmb_notify_after_failures, $pmb_block_duration;

            $this->activeLogLoginAttempts = boolval($pmb_active_log_login_attempts);
            $this->notifyAfterFailures = intval($pmb_notify_after_failures);
            $this->blockAfterFailures = intval($pmb_block_after_failures);
            $this->blockDuration = intval($pmb_block_duration);
        } else {
            global $opac_active_log_login_attempts, $opac_block_after_failures;
            global $opac_notify_after_failures, $opac_block_duration;

            $this->activeLogLoginAttempts = boolval($opac_active_log_login_attempts);
            $this->notifyAfterFailures = intval($opac_notify_after_failures);
            $this->blockAfterFailures = intval($opac_block_after_failures);
            $this->blockDuration = intval($opac_block_duration);
        }
    }

    /**
     * Constructeur
     *
     * @param string $login
     * @param string $ip
     * @return Auth
     */
    public static function getInstance(string $login = '', string $ip = '' ): Auth
    {
        if (defined('GESTION')) {
            $loginAttempt = new GestionLoginAttemptModel();
        } else {
            $loginAttempt = new OpacLoginAttemptModel();
        }

        $whiteList = new IpWhiteListModel();
        $blackList = new IpBlackListModel();
        return new self(
            $loginAttempt,
            $whiteList,
            $blackList,
            $login,
            $ip
        );
    }

    /**
     * Verifie si l'IP est en liste noire
     *
     * @return boolean
     */
    public function isInBlackList(): bool
    {
        if(!$this->activeLogLoginAttempts) {
            return false;
        }
        return $this->blackList->isInList($this->ip);
    }

    /**
     * Verifie si l'IP est en liste blanche
     *
     * @return boolean
     */
    public function isInWhiteList(): bool
    {
        if(!$this->activeLogLoginAttempts) {
            return true;
        }
        return $this->whiteList->isInList($this->ip);
    }

    /**
     * Verifie si l'utilisateur est autorise
     *
     * @return boolean
     */
    public function isAuthorized(): bool
    {
        // Verification non activee ou Liste blanche
        if(!$this->activeLogLoginAttempts || $this->isInWhiteList()) {
            return true;
        }

        // Liste noire ou trop d'essais
        if (
            $this->isInBlackList() ||
            $this->hasToManyFailedAttempts($this->ip, $this->login)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Indique si le nombre de tentatives en echec est superieur au parametre
     *
     * @return boolean
     */
    private function hasToManyFailedAttempts(): bool
    {
        if ($this->loginAttempt->countFailedForLogin($this->ip, $this->login, $this->blockDuration) >= $this->blockAfterFailures) {
            return true;
        }
        return false;
    }

    /**
     * Envoie une notification si le nombre de tentatives en echec est superieur au parametre
     *
     * @return void
     */
    private function notify()
    {
        if (!$this->activeLogLoginAttempts) {
            return;
        }

        $countFailedAttempts = $this->loginAttempt->countFailed($this->ip, $this->blockDuration);
        if ($countFailedAttempts >= $this->notifyAfterFailures) {
            // TODO: Notify
            // $result = pmb_mysql_query('SELECT * FROM users WHERE param_notify_login_failed = 1');
            // if (pmb_mysql_num_rows($result)) {
            //     while ($user = pmb_mysql_fetch_assoc($result)) {
            //         mailpmb();
            //     }
            // }
        }
    }

    /**
     * Enregistre une tentative de connexion.
     *
     * @param boolean $success
     * @return void
     */
    public function logAttempt(bool $success): void
    {
        if ($this->activeLogLoginAttempts) {
            $this->loginAttempt->log($this->ip, $this->login, $success);
        }
    }

    /**
     * Destructeur
     */
    public function __destruct()
    {
        $this->notify();
    }
}
