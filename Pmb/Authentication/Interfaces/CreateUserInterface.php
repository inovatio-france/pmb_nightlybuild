<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CreateUserInterface.php,v 1.4 2023/07/13 13:14:57 dbellamy Exp $

namespace Pmb\Authentication\Interfaces;

if (stristr($_SERVER['REQUEST_URI'], basename(__FILE__))) {
    die("no access");
}

interface CreateUserInterface
{
    /**
     * Creation utilisateur lors de l'authentification
     *
     * @param array $args
     *
     * @return int : Identifiant utilisateur si ok, 0 sinon
     */
    public function onAuthenticationCreate($caller = null, $user, $attributes);

    /**
     * Modification utilisateur lors de l'authentification
     *
     * @param array $args
     *
     * @return int : Identifiant utilisateur si ok, 0 sinon
     */
    public function onAuthenticationUpdate($caller = null, $userid, $user, $attributes);

    /**
     * Liste des arguments a fournir
     *
     * @return array
     */
    public function getArgs();
}
