<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ldap_auth.inc.php,v 1.12 2023/08/28 14:01:13 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// param�tres d'acc�s � le serveur LDAP

define ('LDAP_SERVER',$ldap_server);  //url server ldap
define ('LDAP_BASEDN',$ldap_basedn);  //search base
define ('LDAP_PORT'  ,$ldap_port);    //port
define ('LDAP_PROTO'  ,$ldap_proto);    //protocollo
define ('LDAP_BINDDN',$ldap_binddn);

function auth_ldap($uid,$pwd){
	global $ldap_accessible,$charset,$ldap_encoding_utf8;
	if (!$ldap_accessible) return 0 ;
	$ret = 0;
	if ($pwd){
		//Gestion encodage
		if(($ldap_encoding_utf8) && ($charset != "utf-8")){
			$uid=encoding_normalize::utf8_normalize($uid);
			$pwd=encoding_normalize::utf8_normalize($pwd);
		}elseif((!$ldap_encoding_utf8) && ($charset == "utf-8")){
			$uid=encoding_normalize::utf8_decode($uid);
			$pwd=encoding_normalize::utf8_decode($pwd);
		}
		$dn=str_replace('UID',$uid,LDAP_BINDDN);
		$conn=@ldap_connect(LDAP_SERVER,LDAP_PORT);  // must be a valid LDAP server!
		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTO);
		if ($conn) {
			$ret = @ldap_bind($conn, $dn, $pwd);
			ldap_close($conn);
		}
	}
	return $ret;
}
