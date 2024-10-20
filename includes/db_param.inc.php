<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: db_param.inc.php,v 1.7 2023/04/07 14:25:37 dbellamy Exp $
// param�tres d'acc�s � la base MySQL

// prevents direct script access
if(preg_match('/db_param\.inc\.php/', $_SERVER['REQUEST_URI'])) {
    include ('./forbidden.inc.php');
    forbidden();
}

// inclure ici les tableaux des bases de donn�es accessibles
$_tableau_databases[0]="bibli" ;
$_libelle_databases[0]="bibli" ;

// pour multi-bases
if ( empty($database) ){
    if( !empty($_COOKIE["PhpMyBibli-DATABASE"]) ) {
        $database = $_COOKIE["PhpMyBibli-DATABASE"];
	} else {
        $database = $_tableau_databases[0];
    }
}
if ( !in_array($database, $_tableau_databases) ) {
    $database = $_tableau_databases[0];
		}
define('LOCATION', $database);

// define pour les param�tres de connection. A adapter.
switch (LOCATION) {

    default :
    case 'bibli':
        define('SQL_SERVER', 'localhost');  // nom du serveur
        define('USER_NAME', 'bibli');       // nom utilisateur
        define('USER_PASS', 'bibli');       // mot de passe
        define('DATA_BASE', 'bibli');       // nom base de donn�es
        define('SQL_TYPE',  'mysql');       // Type de serveur de base de donn�es
        // $charset = 'utf-8'; || $charset = 'iso-8859-1';
        // $time_zone = 'Europe/Paris'; //Pour modifier l'heure PHP
        // $time_zone_mysql =  "'-00:00'"; //Pour modifier l'heure MySQL
        // $SQL_VARIABLES = 'sql_mode="NO_AUTOCREATE_USER",join_buffer_size=1000000';

        break;

}

$dsn_pear = SQL_TYPE."://".USER_NAME.":".USER_PASS."@".SQL_SERVER."/".DATA_BASE ;
