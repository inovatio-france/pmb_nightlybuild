<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: crontab_pmb.php,v 1.4 2021/09/22 14:47:18 dbellamy Exp $

// PARAMETRAGE CLIENT

// Identifiant de la source du connecteur sortant
$source_id = 3;
// Base de donn�es (A pr�ciser si +sieurs bases de donn�es sont accessibles)
$database = '';

// Adresse wsdl du web service
$wsdl_url = "http://SERVER/PATH_PMB/ws/connector_out.php?" . (($database) ? "&database=$database" : "") . "&source_id=" . $source_id . "&wsdl";

// Identification
$ws_user = 'external_user';
$ws_pwd = 'PassW0rD';
$options = [];
if ($ws_user && $ws_pwd) {
    $options = [
        'login' => $ws_user,
        'password' => $ws_pwd
    ];
}

try {
    $ws = new SoapClient($wsdl_url, $options);
    // ces 3 fonctions doivent �tre autoris�e dans le groupe anonyme
    // //T�ches dont le timeout serait d�pass�...
    $ws->pmbesTasks_timeoutTasks();
    // T�ches interrompues involontairement..
    $ws->pmbesTasks_checkTasks();
    // T�ches � ex�cuter
    $ws->pmbesTasks_runTasks($source_id);
} catch (Exception $e) {
    error_log($e->getMessage());
}
