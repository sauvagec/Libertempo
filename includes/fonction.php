<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

include_once INCLUDE_PATH .'fonction_config.php';
include_once INCLUDE_PATH .'lang_profile.php';
//better to include_once plugins at the end : see bottom function
//include_once INCLUDE_PATH .'plugins.php';

function schars( $htmlspec ) {
    return htmlspecialchars( $htmlspec );
}

function redirect($url , $auto_exit = true) {
    // $url = urlencode($url);
    if (headers_sent()) {
        echo '<html>';
            echo '<head>';
                echo '<meta HTTP-EQUIV="REFRESH" CONTENT="0; URL='.$url.'">';
                echo '<script language=javascript>
                        function redirection(page){
                            window.location=page;
                        }
                        setTimeout(\'redirection("'.$url.'")\',100);
                    </script>';
            echo '</head>';
        echo '</html>';
    }
    else {
        header('Location: '.$url);
    }
    if ($auto_exit)
        exit;
}


//Get the name of current php page
function curPage() {
 $local_scripts = array();
 $local_scripts[0] = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
 $local_scripts[1] = $_SERVER["REQUEST_URI"];
 return $local_scripts;
}


function header_popup($title = '' , $additional_head = '' ) {
    global $type_bottom;
    global $session;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'popup';

    if (empty($title))
        $title = 'Libertempo';

    include_once TEMPLATE_PATH . 'popup_header.php';
}

function header_error($title = '' , $additional_head = '' ) {
    global $type_bottom;
    global $session;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'error';

    if (empty($title))
        $title = 'Libertempo';

    include_once TEMPLATE_PATH . 'error_header.php';
}

function header_login($title = '' , $additional_head = '' ) {
    global $type_bottom;
    global $session;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'login';

    if (empty($title))
        $title = 'Libertempo';

    include_once TEMPLATE_PATH . 'login_header.php';
}

function header_menu( $info ,$title = '' , $additional_head = '' ) {
    global $type_bottom;
    global $session;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'menu';

    if (empty($title))
        $title = 'Libertempo';

    include_once TEMPLATE_PATH . 'menu_header.php';
}

function bouton($name, $icon ,$link, $active = false)
{
    $name = str_replace('"','\\"',$name);
    $icon = str_replace('"','\\"',$icon);
    $link = str_replace('"','\\"',$link);
    echo '<div class="button_div'.($active?' active':'').'">
            <a href="'. $link .'">
                <img src="'. IMG_PATH . $icon.'" title="'.$name.'" alt="'.$name.'">
                <span>'.$name.'</span>
            </a>
        </div>';
}

function bouton_popup($name, $icon ,$link, $popup_name, $size_x, $size_y, $active = false)
{
    $name = str_replace('"','\\"',$name);

    echo '<div class="button_div'.($active?' active':'').'">
            <a href="#" onClick="OpenPopUp(\''. $link .'\',\''.$popup_name.'\','.$size_x.','.$size_y.');">
                <img src="'. IMG_PATH . $icon.'" title="'.$name.'" alt="'.$name.'">
                <span>'.$name.'</span>
            </a>
        </div>';
}

function bottom() {
    global $type_bottom;


    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!!');

    include_once INCLUDE_PATH .'plugins.php';
    include_once TEMPLATE_PATH . $type_bottom .'_bottom.php';
}


//manage plugins
function install_plugin($plugin){
    include_once INCLUDE_PATH . "/plugins/".$plugin."/plugin_install.php";
}
function activate_plugin($plugin){
    include_once INCLUDE_PATH . "/plugins/".$plugin."/plugin_active.php";
}
function uninstall_plugin($plugin){
    include_once INCLUDE_PATH . "/plugins/".$plugin."/plugin_uninstall.php";
}
function disable_plugin($plugin){
    include_once INCLUDE_PATH . "/plugins/".$plugin."/plugin_inactive.php";
}





//
// indique (TRUE / FALSE) si une session est valide (par / au temps de connexion)
//
function session_is_valid($session)
{
   // ATTENTION:  on fixe l'id de session comme nom de session pour que , sur un meme pc, on puisse se loguer sous 2 users à la fois
   if (session_id() == "")
   {
      session_name($session);
      session_start();
   }

    if( (isset($_SESSION['timestamp_last'])) && (isset($_SESSION['config'])) )
    {
        $difference = time() - $_SESSION['timestamp_last'];
        if ( ($session==session_id()) && ($difference < $_SESSION['config']['duree_session']) )
            return true;
    }

    return false;
}

//
// cree la session et renvoie son identifiant
//
function session_create($username)
{
    if ($username != "")
    {
	if(isset($_SESSION)) unset($_SESSION);
        $session = "phpconges".md5(uniqid(rand()));
        session_name($session);
        session_id($session);

        session_start();
        $_SESSION['userlogin']=$username;
        $maintenant=time();
        $_SESSION['timestamp_start']=$maintenant;
        $_SESSION['timestamp_last']=$maintenant;
        if (function_exists('init_config_tab'))
            $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
        //$session=session_id();

        if (isset($_REQUEST['lang']))
            $_SESSION['lang'] = $_REQUEST['lang'];
    }
    else
    {
        $session="";
    }

    $comment_log = 'Connexion de '.$username;
    log_action(0, "", $username, $comment_log);

    return   $session;
}

//
// mise a jour d'une session
//
function session_update($session)
{
   if ($session != "")
   {
        $maintenant=time();
        $_SESSION['timestamp_last']=$maintenant;
   }
}

//
// destruction d'une session
//
function session_delete($session)
{
   if ($session != "")
   {
     unset($_SESSION['userlogin']);
     unset($_SESSION['timestamp_start']);
     unset($_SESSION['timestamp_last']);
     unset($_SESSION['tab_j_feries']);
     unset($_SESSION['config']);
     unset($_SESSION['lang']);
     session_destroy();
   }
}



//
// formulaire de saisie du user/password
//
function session_saisie_user_password($erreur, $session_username, $session_password)
{
   $PHP_SELF=$_SERVER['PHP_SELF'];

    $config_php_conges_version      = $_SESSION['config']['php_conges_version'];
    $config_url_site_web_php_conges = $_SESSION['config']['url_site_web_php_conges'];
//    $config_stylesheet_file         = $_SESSION['config']['stylesheet_file'];

    $return_url                     = getpost_variable('return_url', false);

    // verif si on est dans le repertoire install
    if(substr(dirname ($_SERVER["SCRIPT_FILENAME"]), -6, 6) == "config")   // verif si on est dans le repertoire install
        $config_dir=TRUE;
    else
        $config_dir=FALSE;

    $add = '<script language="JavaScript" type="text/javascript">
<!--
// Les cookies sont obligatoires
if (! navigator.cookieEnabled) {
    document.write("<font color=\'#FF0000\'><br><br><center>'. _('cookies_obligatoires') .'</center></font><br><br>");
}
//-->
</script>
<noscript>
        <font color="#FF0000"><br><br><center>'. _('javascript_obligatoires') .'</center></font><br><br>
</noscript>';

    header_login('', $add);
    include_once TEMPLATE_PATH . 'login_form.php';

    bottom();
    exit;
}



//
// autentifie un user dans le base mysql avec son login et son passwd conges :
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//
function autentification_passwd_conges($username,$password)
{
    $password_md5=md5($password);
//  $req_conges="SELECT u_passwd   FROM conges_users   WHERE u_login='$username' AND u_passwd='$password_md5' " ;
    // on conserve le double mode d'autentificatio (nouveau cryptage (md5) ou ancien cryptage (mysql))
    $req_conges='SELECT u_passwd   FROM conges_users   WHERE u_login="'. \includes\SQL::quote( $username ) .'" AND ( u_passwd=\''. md5($password) .'\' OR u_passwd=PASSWORD("'. \includes\SQL::quote( $password ).'") ) ' ;
    $res_conges = \includes\SQL::query($req_conges) ;
    $num_row_conges = $res_conges->num_rows;
    if ($num_row_conges !=0)
        return $username;
    return '';
}


// authentification du login/passwd sur un serveur LDAP
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//
function authentification_ldap_conges($username,$password)
{
    require_once ( LIBRARY_PATH .'authLDAP.php');

    $a = new authLDAP();
    //$a->DEBUG = 1;
    //$a->bind($username,$password);
    $a->bind($username,stripslashes($password));
    if ($a->is_authentificated())
        return $username;

    return '';
}


// Authentifie l'utilisateur auprès du serveur CAS, puis auprès de la base de donnée.
// Si le login qui a permis d'authentifier l'utilisateur auprès du serveur
//  CAS existe en tant que login d'une entrée de la table conges_user, alors
//  l'authentification est réussie et passwCAS renvoi le nom de l'utilisateur, "" sinon.
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
function authentification_passwd_conges_CAS()
{
    $config_CAS_host       =$_SESSION['config']['CAS_host'];
    $config_CAS_portNumber =$_SESSION['config']['CAS_portNumber'];
    $config_CAS_URI        =$_SESSION['config']['CAS_URI'];
    $config_CAS_CACERT     =$_SESSION['config']['CAS_CACERT'];

    global $connexionCAS;
    global $logoutCas;


    \phpCAS::setDebug();

    // initialisation phpCAS
    if($connexionCAS!="active")
    {
        $CASCnx = \phpCAS::client(CAS_VERSION_2_0,$config_CAS_host,$config_CAS_portNumber,$config_CAS_URI);
        $connexionCAS = "active";

    }

    if($logoutCas==1)
    {
        \phpCAS::logout();
    }


    // Vérification SSL
    if(!empty($config_CAS_CACERT))
        \phpCAS::setCasServerCACert ($config_CAS_CACERT);
    else
        \phpCAS::setNoCasServerValidation();

    // authentificationCAS (redirection vers la page d'authentification de CAS)
    \phpCAS::forceAuthentication();

    $usernameCAS = \phpCAS::getUser();

    //On nettoie la session créée par phpCAS
    session_destroy();
    // On créé la session gérée par Libertempo
    session_create($usernameCAS);

    //ON VERIFIE ICI QUE L'UTILISATEUR EST DEJA ENREGISTRE SOUS DBCONGES
    $req_conges = 'SELECT u_login FROM conges_users WHERE u_login=\''. \includes\SQL::quote($usernameCAS).'\'';
    $res_conges = \includes\SQL::query($req_conges) ;
    $num_row_conges = $res_conges->num_rows;
    if($num_row_conges !=0) {
        return $usernameCAS;
    } else {
        return '';
    }
}


function deconnexion_CAS($url="")
{
    // import des parametres du serveur CAS

    $config_CAS_host       =$_SESSION['config']['CAS_host'];
    $config_CAS_portNumber =$_SESSION['config']['CAS_portNumber'];
    $config_CAS_URI        =$_SESSION['config']['CAS_URI'];

    global $connexionCAS;

    // initialisation phpCAS
    if($connexionCAS!="active")
    {
        $CASCnx = \phpCAS::client(CAS_VERSION_2_0,$config_CAS_host,$config_CAS_portNumber,$config_CAS_URI);
        $connexionCAS = "active";

    }

    \phpCAS::logoutWithUrl($url);
}


function hash_user($user)
{
	$ics_salt = $_SESSION['config']['export_ical_salt'];
	$huser = hash('sha256', $user . $ics_salt);
	return $huser;
}

function unhash_user($huser_test)
{
	$user = "";
	$ics_salt = $_SESSION['config']['export_ical_salt'];
	$req_user = 'SELECT u_login FROM conges_users';
	$res_user = \includes\SQL::query($req_user) ;

	while ($resultat = $res_user->fetch_assoc())
	{
		$clear_user = $resultat['u_login'];
		$huser = hash('sha256', $clear_user . $ics_salt);
		if( $huser_test == $huser )
			$user = $clear_user;
	}
	return $user;
}

function authentification_AD_SSO()
{
	$cred = explode('@',$_SERVER['REMOTE_USER']);
	if(count($cred)==1)
		$userAD = $cred[0];
	else
		$userAD = $cred[1];

	//ON VERIFIE ICI QUE L'UTILISATEUR EST DEJA ENREGISTRE SOUS DBCONGES
	$req_conges = 'SELECT u_login FROM conges_users WHERE u_login=\''. $userAD.'\'';
	$res_conges = \includes\SQL::query($req_conges) ;
	$num_row_conges = $res_conges->num_rows;
	if($num_row_conges !=0)
		return $userAD;

	return '';
}
