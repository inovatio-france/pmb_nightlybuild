<?php

// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ListInterface.php,v 1.1 2024/10/01 15:00:39 qvarin Exp $

namespace Pmb\Security\Library;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

interface ListInterface
{
    public function add(string $ip): void;

    public function remove(string $ip): void;

    public function isInList(string $ip): bool;

    public function getAll(): array;
}
