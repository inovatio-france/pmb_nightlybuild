<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reindex_sphinx_authorities.inc.php,v 1.2 2024/09/27 14:23:26 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $include_path, $msg;
global $start, $v_state, $spec, $count, $current_module, $index_quoi, $step_position;

require_once($class_path."/indexation_authority.class.php");
require_once($class_path."/indexation_authperso.class.php");

// la taille d'un paquet de notices
$lot = REINDEX_PAQUET_SIZE; // defini dans ./params.inc.php

// initialisation de la borne de d�part
if (!isset($start)) {
    $start=0;
    //remise a zero de la table au d�but
	
}
$v_state=urldecode($v_state);

// on commence par :
if (empty($index_quoi)) $index_quoi='AUTHORS';

switch ($index_quoi) {
    case 'AUTHORS':
        netbase_authorities::set_object_type(AUT_TABLE_AUTHORS);
        netbase_authorities::proceed();
        break ;
        
    case 'PUBLISHERS':
        netbase_authorities::set_object_type(AUT_TABLE_PUBLISHERS);
        netbase_authorities::proceed();
        break ;
        
    case 'CATEGORIES':
        netbase_authorities::set_object_type(AUT_TABLE_CATEG);
        netbase_authorities::proceed();
        break ;
        
    case 'COLLECTIONS':
        netbase_authorities::set_object_type(AUT_TABLE_COLLECTIONS);
        netbase_authorities::proceed();
        break ;
        
    case 'SUBCOLLECTIONS':
        netbase_authorities::set_object_type(AUT_TABLE_SUB_COLLECTIONS);
        netbase_authorities::proceed();
        break ;
        
    case 'SERIES':
        netbase_authorities::set_object_type(AUT_TABLE_SERIES);
        netbase_authorities::proceed();
        break ;
        
    case 'DEWEY':
        netbase_authorities::set_object_type(AUT_TABLE_INDEXINT);
        netbase_authorities::proceed();
        break ;
        
    case 'TITRES_UNIFORMES':
        netbase_authorities::set_object_type(AUT_TABLE_TITRES_UNIFORMES);
        netbase_authorities::proceed();
        break ;
        
    case 'AUTHPERSO':
        netbase_authperso::proceed();
        break ;
        
    case 'FINI':
        $spec = $spec - INDEX_SPHINX_AUTHORITIES;
        $v_state .= netbase::get_display_progress_v_state($msg["nettoyage_reindex_fini"]);
        print "
			<form class='form-$current_module' name='process_state' action='./clean.php?spec=$spec&start=0' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			</form>
			<script type=\"text/javascript\"><!--
				setTimeout(\"document.forms['process_state'].submit()\",1000);
				-->
			</script>";
        break ;
}
