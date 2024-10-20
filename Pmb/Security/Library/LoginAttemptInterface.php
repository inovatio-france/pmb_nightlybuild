<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: LoginAttemptInterface.php,v 1.2 2024/10/15 12:19:16 qvarin Exp $

namespace Pmb\Security\Library;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

interface LoginAttemptInterface
{
    public function log(string $ip, string $login, bool $success): void;

    public function countFailed(string $ip, int $second): int;

    public function countFailedForLogin(string $ip, string $login, int $second): int;

    public function getLastFailed(string $ip): LoginAttemptInterface;

    public function getTimestamp(): int;

    public function cleanLogs(int $month): void;
}
