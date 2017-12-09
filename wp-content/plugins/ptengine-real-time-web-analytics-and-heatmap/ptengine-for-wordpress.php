<?php
/*
 * Plugin Name: Ptengine - Real time web analytics and Heatmap
 * Version: 1.0.16
 * Plugin URI: http://www.ptengine.com/
 * Description: Ptengine is a a free Heatmap and Analytics Wordpress plugin that allows you to fix conversions problems. Get more enquiries, sales and engagement now. Ptengine shows you how.
 * Author: Ptengine
 * Author URI: http://www.ptengine.com/
 */

// define
define('ptengine_menu_position', '7', true);
define('wpp_api_report', admin_url(). 'admin.php?page=ptengine_report', true);
define('wpp_api_setting', admin_url(). 'admin.php?page=ptengine_setting', true);
define('ptengine_api_login', 'https://report.ptengine.com', true); // if failed to get area, let it be default area

define('ptengine_api_log', 'http://pwplog.ptmind.cn/wplc.php');
define('ptengine_log_level', 5); // all-log:5 sys-log:4 warning:3 error:2 critical:1
if (ptengine_log_level >= 1) {
	$ptengine_wpid = get_option('key_ptengine_wpid');
	if (empty($ptengine_wpid)) {
		add_option('key_ptengine_wpid', time().rand(100,999));
	}
}
define('user_code','ShFE0mkI',true);
// option init
add_option('key_ptengine_badge_visible', '0');
add_option('key_ptengine_tag_position', 'footer');

add_option('key_ptengine_account', '');
add_option('key_ptengine_pwd', '');
add_option('key_ptengine_uid', '');

add_option('key_ptengine_sid', '');
add_option('key_ptengine_site_id', '');
add_option('key_ptengine_pgid', '');
add_option('key_ptengine_site_name', '');
add_option('key_ptengine_timezone', '');
add_option('key_ptengine_code', '');

add_option('key_ptengine_area', '');
add_option('key_ptengine_dc_init', '1');

add_option('key_ptengine_nonce_id', '0');

/*******************common process start**********************************/
// log api
function ptengine_log($arg_type, $arg_log) {
	if ($arg_type == 'curl') {
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ptengine_api_log. '?log='. get_option('key_ptengine_wpid'). ','. $arg_log);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // for https request
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $r = curl_exec($ch);
        curl_close($ch);
	} elseif ($arg_type == 'img') {
		echo '<script type="text/javascript">(new Image()).src = "'. ptengine_api_log. '?log='. get_option('key_ptengine_wpid'). ','. $arg_log. '&'. time(). '"</script>';
	}
}
/*******************common process end**********************************/

/*******************param process start**********************************/

// call by current page
$from_cp = isset($_GET['from_cp']) ? $_GET['from_cp'] : "";
// after registed or login, set option
$account = isset($_GET['account']) ? $_GET['account'] : "";
$pwd = isset($_GET['pwd']) ? $_GET['pwd'] : "";
$uid = isset($_GET['uid']) ? $_GET['uid'] : "";
$set_first = isset($_GET['setFirst']) ? $_GET['setFirst'] : "";
if (isset($_GET["nonce_id"]) && ($_GET["nonce_id"] == get_option('key_ptengine_nonce_id')) && $account && $pwd && $uid) {
    update_option('key_ptengine_account', $account);
    update_option('key_ptengine_pwd', $pwd);
    update_option('key_ptengine_uid', $uid);
    update_option('key_ptengine_area', '');
    if ($set_first === '0'){
        update_option('key_ptengine_dc_init', $set_first);
    }
    if (ptengine_log_level >= 3) ptengine_log('curl', 'setuid,'. $account. '.'. empty($pwd). '.'. $uid. '.src='. $from_cp);
}

// after profile created, set option
$sid = isset($_GET['sid']) ? $_GET['sid'] : "";
$site_id = isset($_GET['siteId']) ? $_GET['siteId'] : "";
$pgid = isset($_GET['groupId']) ? $_GET['groupId'] : "";
$site_name = isset($_GET['siteName']) ? $_GET['siteName'] : "";
$timezone = isset($_GET['timezone']) ? $_GET['timezone'] : "";
if (isset($_GET["nonce_id"]) && ($_GET["nonce_id"] == get_option('key_ptengine_nonce_id')) && $sid && $site_id && $pgid && $site_name && $timezone) {
    update_option('key_ptengine_sid', $sid);
    update_option('key_ptengine_site_id', $site_id);
    update_option('key_ptengine_pgid', $pgid);
    update_option('key_ptengine_site_name', $site_name);
    update_option('key_ptengine_timezone', $timezone);
    if (ptengine_log_level >= 3) ptengine_log('curl', 'setsid,'. $sid. '.'. $site_id. '.'.$site_name. '.src='. $from_cp);
}
/*******************param process end**********************************/

/*******************plugin start/stop process start**********************************/
register_activation_hook( __FILE__, 'ptengine_plugin_install');   
register_deactivation_hook( __FILE__, 'ptengine_plugin_remove' );  

function ptengine_plugin_install(){
    $t_uid = get_option('key_ptengine_uid');
    if (!$t_uid) {
        $t_area = ptengine_getArea('a='. $_SERVER['HTTP_ACCEPT_LANGUAGE']. '&b='. getIP());
        if ($t_area) {
            update_option('key_ptengine_area', $t_area);
        } else {
        	update_option('key_ptengine_area', ptengine_api_login);
        	if (ptengine_log_level >= 2) ptengine_log('curl', 'active_!area,'. '.'. get_option('key_ptengine_site_id'));
        }
    }
    if (ptengine_log_level >= 2) ptengine_log('curl', 'active,'. $t_uid. '.'. get_option('key_ptengine_site_id'). '.'. $t_area);
}

function ptengine_plugin_remove(){
    ptengine_logout();
    if (ptengine_log_level >= 4) ptengine_log('curl', 'deactive,'. get_option('key_ptengine_uid'));
}
function getIP(){
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    if(!empty($_SERVER["REMOTE_ADDR"])){
        return $_SERVER["REMOTE_ADDR"];
    }
    return '';
}
/*******************plugin start/stop process end**********************************/

/*******************add tag process start**********************************/
// after profile created or profile found
if(get_option('key_ptengine_sid')){
    
    add_action('wp_head', 'add_ptengine_tag');

    // add ptengine tag
    function add_ptengine_tag() {
    	
        $t_site_id = get_option('key_ptengine_site_id');
?>
    <script type="text/javascript">
    (function(){
            var t = function(){
                window._pt_sp_2 = [];
                _pt_sp_2.push('setAccount,<?php echo $t_site_id; ?>');
                var _protocol = (("https:" == document.location.protocol) ? " https://" : " http://");
                (function() {
                    var atag = document.createElement('script'); atag.type = 'text/javascript'; atag.async = true;
                    atag.src = _protocol + 'js.ptengine.com/pta.js';
                    var stag = document.createElement('script'); stag.type = 'text/javascript'; stag.async = true;
                    stag.src = _protocol + 'js.ptengine.com/pts.js';
                    var s = document.getElementsByTagName('script')[0]; 
                    s.parentNode.insertBefore(atag, s);s.parentNode.insertBefore(stag, s);
                })();
            }
            if(window.attachEvent){
                window.attachEvent("onload",t);
            }else if(window.addEventListener){
                window.addEventListener("load",t,false);
            }else{
                t();
            }
        })();
    </script>
<?php
    }
}
/*******************add tag process end**********************************/

/*******************validate process start**********************************/
// when open DC or Setting page, login
function ptengine_login($arg_area){
    $t_account = get_option('key_ptengine_account');
    $t_pwd = get_option('key_ptengine_pwd');
    if ($t_account && $t_pwd) {
        // if account and pwd exist
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $arg_area. '/interface/login.pt?user.email='. $t_account. '&user.md5password='. $t_pwd);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // for https request
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $r = curl_exec($ch);
        curl_close($ch);
        if ($r != 'error') {
            return $r;
        }
    }
    return false;
}

// when remove plugin, logout
function ptengine_logout(){
    update_option('key_ptengine_pwd', '');
}

// get area api
function ptengine_getArea($arg_param) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ptengine_api_login . '/interface/getArea.pt?'. $arg_param);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // for https request
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $r = curl_exec($ch);
    curl_close($ch);
    if ($r != 'error') {
        return substr($r, 1);
    }
    return '';
}
/*******************validate process end**********************************/

/*******************page process start**********************************/
// add page
function ptengine_admin_menu() {
    add_menu_page(__('Ptengine Report'), __('Ptengine'), 'activate_plugins', 'ptengine_report', 'display_ptengine_report', plugins_url('menu-icon.png',__FILE__), ptengine_menu_position);
    add_submenu_page('ptengine_report', __('Ptengine Report'), __('Data Center'), 'activate_plugins', 'ptengine_report', 'display_ptengine_report');
    add_submenu_page('ptengine_report', __('Setting'), __('Setting'), 'activate_plugins', 'ptengine_setting', 'display_ptengine_setting');
}

// show dc page
function display_ptengine_report() {
    $t_flag = isset($_GET['flag']) ? $_GET['flag'] : "";
    if ($t_flag == 'api'){
        return;
    }
    
    $t_sid = get_option('key_ptengine_sid');
    $t_dc_init = get_option('key_ptengine_dc_init');
    $t_uid = get_option('key_ptengine_uid');
    $t_account = get_option('key_ptengine_account');
    $t_pwd = get_option('key_ptengine_pwd');
    $t_site_id = get_option('key_ptengine_site_id');
    $t_timezone = get_option('key_ptengine_timezone');
    $t_pgid = get_option('key_ptengine_pgid');

    if($t_uid && $t_sid){
        // if uid&sid exist, get area, try login, 
        $t_area = ptengine_getArea('uid='. $t_uid);
        if (!$t_area){
        	if (ptengine_log_level >= 2) ptengine_log('img', 'dc_sid_!area,'. $t_uid. '.'. $t_site_id);
            $t_area = ptengine_api_login;
        }
        $t_token = ptengine_login($t_area);
        $t_api_dc = $t_area. '/interface/wpPlugin.pt?page=dc';
        if (!$t_token) {
            // if login failed
            update_option('key_ptengine_pwd', '');
            $t_pwd = '';
            if (ptengine_log_level >= 2) ptengine_log('img', 'dc_sid_!token,'. $t_uid. '.'. $t_site_id. '.'. $t_account. '.'. empty($t_pwd));
        }
        // create nonce id
        $nonce_id = wp_create_nonce($t_account);
        update_option('key_ptengine_nonce_id', $nonce_id);
    
        $query_str = $t_api_dc
                . '&data={'
                    . 'isfirst:' . $t_dc_init . ','
                    . 'user:{'
                        . 'uid:"' . $t_uid . '",'
                        . 'email:"' . $t_account . '",'
                        . 'md5password:"' . $t_pwd . '"},'
                    . 'site:{'
                        . 'sid:"' . $t_sid . '",'
                        . 'siteId:"' . $t_site_id . '",'
                        . 'timezone:"' . $t_timezone . '",'
                        . 'groupId:"' . $t_pgid . '",'
                        . 'token:"' . $t_token . '"},'
                    . 'url:{'
                        . 'wppAPI:"' . wpp_api_report . '%26nonce_id='. $nonce_id. '%26flag=api"},';
        ?>
        <iframe id='ptengine_report_frame' frameborder='no' border='0'  allowtransparency='true'  style='border:none;' src='' width='100%' height='1460px'><p>Your browser does not support iframes.</p></iframe>
        <script type='text/javascript'>
            var vh = 600;
            vh = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
            document.getElementById("ptengine_report_frame").src = '<?php echo $query_str; ?>' + "vh:" + vh + "}";  
        </script>
        <?php
        echo "<script type='text/javascript'>
            (function(){
                if (document.readyState == 'complete') {
                    //ptFun_changeIframeSize();
                } else {
                    var oldWindowHandler = window.onload;
                    window.onload = function() {
                        if (!!oldWindowHandler) {
                            oldWindowHandler();
                        }
                        //ptFun_changeIframeSize();
                    };
                };
            })()

            //function ptFun_changeIframeSize(){
                //var minW = Math.max(document.getElementById('ptengine_report_frame').parentNode.offsetWidth, 840);
                //var minH = Math.max(document.getElementById('ptengine_report_frame').parentNode.offsetHeight, 1390);
                //document.getElementById('ptengine_report_frame').style.width = minW+'px';
                //document.getElementById('ptengine_report_frame').style.height = minH+'px';
            //}
        </script>";
        return;
    } else {
        // if profile do not exist, turn to Setting page
        if (ptengine_log_level >= 2) ptengine_log('img', 'dc_!sid,'. $t_uid. '.'. $t_site_id. '.'. $t_account. '.'. empty($t_pwd));
        echo '<script type="text/javascript">window.location.href ="'. wpp_api_setting. '";</script>';
    }
}
    
// show setting page
function display_ptengine_setting() {
	$t_flag = isset($_GET['flag']) ? $_GET['flag'] : "";
    if ($t_flag == 'api'){
        return;
    }
    
    $t_account = get_option('key_ptengine_account');
    $t_pwd = get_option('key_ptengine_pwd');
    $t_uid = get_option('key_ptengine_uid');
    $t_site_name = get_option('key_ptengine_site_name');
    $t_sid = get_option('key_ptengine_sid');
    $t_site_id = get_option('key_ptengine_site_id');
    $t_site_domain = get_option('home');
    
    if($t_uid){
        // if account created,but profile do not exist)
        $t_area = ptengine_getArea('uid='. $t_uid);
    } else {
        // if account do not exist
        if (ptengine_log_level >= 4) ptengine_log('img', 'st_!uid,');
        $t_area = get_option('key_ptengine_area');
        if (!$t_area) {
        	if (ptengine_log_level >= 2) ptengine_log('img', 'st_!uid_!area,'. $t_area);
            $t_area = ptengine_getArea('a='. $_SERVER['HTTP_ACCEPT_LANGUAGE']. '&b='. getIP());
        }
    }
    if (!$t_area){
    	if (ptengine_log_level >= 2) ptengine_log('img', 'st_uid_!area,'. $t_uid);
        $t_area = ptengine_api_login;
    }
    if($t_sid){
        // if sid exist, try login, get area and api
        $status = '1';
        $t_token = ptengine_login($t_area);
        $t_api_setting = $t_area. '/interface/wpPlugin.pt?page=setting';
        if (!$t_token){
            // validate
            if (ptengine_log_level >= 2) ptengine_log('img', 'st_sid_!token,'. $t_uid. '.'. $t_site_id. '.'. $t_account. '.'. empty($t_pwd));
            update_option('key_ptengine_pwd', '');
            $t_pwd = '';
        }
    } else {
        // if profile do not exist
        if (ptengine_log_level >= 4) ptengine_log('img', 'st_!sid,'. $t_uid);
        $status = '2';
        $t_api_setting = $t_area. '/interface/wpPlugin.pt?page=setting';
        update_option('key_ptengine_account', '');
        update_option('key_ptengine_pwd', '');
        update_option('key_ptengine_site_name', '');
        $t_account = '';
        $t_pwd = '';
        $t_site_name = '';
    }
    
    $nonce_id = wp_create_nonce($t_site_domain);
    update_option('key_ptengine_nonce_id', $nonce_id);
    
    $query_str = $t_api_setting
            . '&data={'
                . 'status:' . $status . ','
                . 'user:{'
                    . 'email:"' . $t_account . '",'
                    . 'md5password:"' . $t_pwd . '",'
                    . 'code:"'. user_code . '"'. '},'
                . 'site:{'
                    . 'sid:"' . $t_sid . '",'
                    . 'siteId:"' . $t_site_id . '",'
                    . 'siteName:"' . $t_site_name . '"},'
                . 'url:{'
                    . 'domain:"' . $t_site_domain . '",'
                . 'wppAPI:"'. wpp_api_report. '%26nonce_id='. $nonce_id. '%26flag=api"},';
    ?>
    <iframe id='ptengine_setting_frame' frameborder='no' border='0'  allowtransparency='true'  style='border:none;' src='' width='100%' height='740px'><p>Your browser does not support iframes.</p></iframe>
    <script type='text/javascript'>
		// set postMessage
		window.addEventListener("message", receiveMessage, false);
		function receiveMessage(event){
    		if (event.origin.indexOf("ptengine") > -1 ){
    			(new Image()).src = location.href + (location.href.indexOf("?") > -1 ? "&" : "?") + event.data + "&from_cp=1";
    		}
		}
        var vh = 600;
        vh = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
        document.getElementById("ptengine_setting_frame").src = '<?php echo $query_str; ?>' + "vh:" + vh + "}";
    </script>
    <?php
        echo "<script type='text/javascript'>
            (function(){
                if (document.readyState == 'complete') {
                    ptFun_changeIframeSize();
                } else {
                    var oldWindowHandler = window.onload;
                    window.onload = function() {
                        if (!!oldWindowHandler) {
                            oldWindowHandler();
                        }
                        ptFun_changeIframeSize();
                    };
                };
            })()

            function ptFun_changeIframeSize(){
                var minW = Math.max(document.getElementById('ptengine_setting_frame').parentNode.offsetWidth, 840);
                var minH = Math.max(document.getElementById('ptengine_setting_frame').parentNode.offsetHeight, 740);
                //document.getElementById('ptengine_setting_frame').style.width = minW+'px';
                document.getElementById('ptengine_setting_frame').style.height = minH+'px';
            }
        </script>";
}
// Create admin menu
add_action('admin_menu', 'ptengine_admin_menu');
/*******************page process end**********************************/

?>