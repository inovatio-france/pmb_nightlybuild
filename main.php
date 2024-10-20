<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.php,v 1.53 2023/09/01 07:58:31 dbellamy Exp $

// définition du minimum nécéssaire
$base_path=".";
$base_auth = "";
$base_title = "\$msg[308]";
$base_noheader=1;
$base_nocheck=1;

global $include_path, $msg, $charset;
global $password, $user, $action, $otp;
global $security_mfa_active, $pmb_indexation_must_be_initialized;

use Pmb\MFA\Controller\MFAMailController;
use Pmb\Common\Helper\MySQL;

require_once "$base_path/includes/init.inc.php";
//Est-on déjà authentifié ?
if (!checkUser('PhpMyBibli')) {

    $valid_user = 0;
    /************** Authentification externe  *******************/
    $ext_auth_hook = 1;
    $external_admin_auth_file_exists = file_exists( "$include_path/external_admin_auth.inc.php") ;
    if( $external_admin_auth_file_exists ) {
        require "$include_path/external_admin_auth.inc.php";
    }

    /************** Authentification classique *******************/
    if($valid_user !=1 ) {
        //Vérification que l'utilisateur existe dans PMB
        $query = "SELECT userid, username FROM users WHERE username='$user'";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            //Récupération du mot de passe
            $dbuser=pmb_mysql_fetch_object($result);

            /************** Authentification externe  (Vérification mot de passe hors admin uniquement) *******************/
            if ( $external_admin_auth_file_exists && ($ext_auth_hook !=0) && ($dbuser->userid !=1 ) ) {
                require "$include_path/external_admin_auth.inc.php";
            } else {

                // on checke si l'utilisateur existe et si le mot de passe est OK


                //$query = "SELECT count(1) FROM users WHERE username='$user' AND pwd=password('$password') ";
                $query = "SELECT count(1) FROM users WHERE username='$user' AND pwd='" . MySQL::password($password) . "'";
                $result = pmb_mysql_query($query);
                $valid_user = pmb_mysql_result($result, 0, 0);
            }
        }
    }
} else {
    $valid_user=2;
}

if(!$valid_user) {
    header("Location: index.php?login_error=1");
} else {
    if ($valid_user == 1) {

        /************** Double authentification *******************/
        if($security_mfa_active && !$external_admin_auth_file_exists) {
            $mfa_service = (new Pmb\MFA\Controller\MFAServicesController())->getData('GESTION');
            if($mfa_service->application) {
                // On regarde si l'utilisateur a initialisé sa double authentification
                $query = "SELECT mfa_secret_code, mfa_favorite, user_email FROM users WHERE username='$user'";
                $result = pmb_mysql_query($query);
                if (pmb_mysql_num_rows($result)) {
                    $row = pmb_mysql_fetch_object($result);
                    if(!empty($row->mfa_secret_code)) {
                        header("Content-Type: text/html; charset=$charset");
                        if(isset($otp) && !empty($otp)) {
                            $mfa_otp = (new Pmb\MFA\Controller\MFAOtpController())->getData('GESTION');

                            $reset_mfa = false;

                            $mfa_totp = new mfa_totp();
                            $mfa_totp->set_hash_method("sha1");
                            $mfa_totp->set_length_code(10);

                            if($mfa_totp->check_totp_reset_code(base32_upper_decode($row->mfa_secret_code), $otp)) {
                                $request = "UPDATE users SET mfa_secret_code = NULL, mfa_favorite = NULL WHERE userid = " . $dbuser->userid;
                                pmb_mysql_query($request);

                                $reset_mfa = true;
                            }

                            // On regarde si le code de sécurité est valide
                            if(!$reset_mfa) {
                                $mfa_totp->set_hash_method($mfa_otp->hashMethod);
                                $mfa_totp->set_life_time($mfa_otp->lifetime);
                                $mfa_totp->set_length_code($mfa_otp->lengthCode);

                                if(!$mfa_totp->check_totp(base32_upper_decode($row->mfa_secret_code), $otp)) {
                                    print json_encode(['error_otp' => true]);
                                    return;
                                }
                            }

                        } else if($action == "mail_otp_code") {
                            $mfa_mail = (new MFAMailController())->getData('GESTION');

                            $lang = user::get_param($dbuser->userid, "user_lang");

                            $messages = new XMLlist("$include_path/messages/$lang.xml", 0);
                            $messages->analyser();
                            $msg = $messages->table;

                            $mail_user = mail_user_mfa::get_instance();
                            $mail_user->set_mfa_mail($mfa_mail);
                            $mail_user->set_mail_to_id($dbuser->userid);

                            if($mail_user->send_mail()) {
                                print json_encode(['message' => $msg['mfa_login_notify_mail']]);
                            } else {
                                print json_encode(['message' => $msg['mfa_error_mail']]);
                            }
                            return;

                        } else {
                            print json_encode(['user' => $dbuser->userid, 'favorite' => $row->mfa_favorite, "user_email" => $row->user_email]);
                            return;
                        }
                    }
                }
            }
        }
        startSession('PhpMyBibli', $user, $database);

    }
}

if(defined('SESSlang') && SESSlang) {
    $lang=SESSlang;
    $helpdir = $lang;
}

// localisation (fichier XML)
$messages = new XMLlist("$include_path/messages/$lang.xml", 0);
$messages->analyser();
$msg = $messages->table;
require("$include_path/templates/common.tpl.php");
header ("Content-Type: text/html; charset=$charset");

$sphinx_message = check_sphinx_service();
if (!empty($sphinx_message)) {
    print "<script>alert('$sphinx_message')</script>";
}

if ((!$param_licence)||($pmb_bdd_version!=$pmb_version_database_as_it_should_be)||($pmb_subversion_database_as_it_shouldbe!=$pmb_bdd_subversion)) {
    require_once("$include_path/templates/main.tpl.php");
    print $std_header;
    print "<body class='$current_module claro' id='body_current_module' page_name='$current_module'>";
    print $menu_bar;

    print $extra;
    if($use_shortcuts) {
        include("$include_path/shortcuts/circ.sht");
    }
    print $main_layout;

    if ($pmb_bdd_version!=$pmb_version_database_as_it_should_be) {
        echo "<h1>".$msg["pmb_v_db_pas_a_jour"]."</h1>";
        echo "<h1>".$msg[1803]."<span style='color:red'>".$pmb_bdd_version."</span></h1>";
        echo "<h1>".$msg['pmb_v_db_as_it_should_be']."<span style='color:red'>".$pmb_version_database_as_it_should_be."</span></h1>";
        echo "<a href='./admin.php?categ=alter&sub='>".$msg["pmb_v_db_mettre_a_jour"]."</a>";
        echo "<SCRIPT>alert(\"".$msg["pmb_v_db_pas_a_jour"]."\\n".$pmb_version_database_as_it_should_be." <> ".$pmb_bdd_version."\");</SCRIPT>";
    } elseif ($pmb_subversion_database_as_it_shouldbe!=$pmb_bdd_subversion) {
        echo "<h1>Minor changes in database in progress...</h1>";
        include("./admin/misc/addon.inc.php");
        echo "<h1>Changes applied in database.</h1>";
    }

    //On est probablement sur une première connexion à PMB
    $pmb_indexation_must_be_initialized = empty($pmb_indexation_must_be_initialized) ? 0 : intval($pmb_indexation_must_be_initialized);
    if($pmb_indexation_must_be_initialized) {
        echo "<h1>Indexation in progress...</h1>";
        flush();
        ob_flush();
        include("./admin/misc/setup_initialization.inc.php");
        echo "<h1>Indexation applied in database.</h1>";
    }

    if (!$param_licence) {
        include("$base_path/resume_licence.inc.php");
    }

    print $main_layout_end;
    print $footer;

    pmb_mysql_close($dbh);
    exit ;
}
if ($ret_url) {
    if(strpos($ret_url, 'ajax.php') !== false) {
        print "<SCRIPT>document.location=\"".$_SERVER['HTTP_REFERER']."\";</SCRIPT>";
        exit;
    }
    //AR - on évite un redirection vers une url absolue...
    if((strpos($ret_url, 'http://') === false) && (strpos($ret_url, 'https://') === false)) {
        print "<SCRIPT>document.location=\"$ret_url\";</SCRIPT>";
        exit ;
    }
}

//chargement de la première page
require_once($include_path."/misc.inc.php");

go_first_tab();

pmb_mysql_close();
