<?php

// make sure the user is authenticated
function authenticate() {
	global $root;
	if(!isset($_SESSION['user']['id'])) {
		redirect($root.'login.php');
	}
}

// check if email address is valid
function is_valid_email($email) {
    return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
}

// safely redirect to the given url
function redirect($url) {
    // if there's a $db object, close it
    global $db;
    if(!empty($db))
        $db->close();

    // do the redirect
    header("Location: ".$url);
    exit();
}

// display an error for a field
function error_display($error_field) {
    if(!empty($error_field)) {
        return '<span class="error">'.html_sanitize($error_field).'</span>';
    }
}

// detect if the user is using a mobile browser
function using_mobile() { 
	$mobile_browser = '0';
	
	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
		$mobile_browser++;
	}
	 
	if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
		$mobile_browser++;
	}    
	 
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
	$mobile_agents = array(
		'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
		'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
		'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
		'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
		'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
		'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
		'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
		'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
		'wapr','webc','winw','winw','xda','xda-');
	 
	if(in_array($mobile_ua,$mobile_agents)) {
		$mobile_browser++;
	}
	 
	if(strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
		$mobile_browser++;
	}
	 
	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
		$mobile_browser=0;
	}
	
	if($mobile_browser > 0)
		return true;
	else
		return false;
}

?>
