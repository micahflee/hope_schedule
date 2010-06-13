<?php
require_once("helpers.inc.php");

// admin layout
class layout_admin {
    var $title = "";
    var $head = "";
    var $onload = "";

    // constructor
    function layout_admin($title, $head="", $onload="") {
        $this->title = $title;
        $this->head = $head;
    }

    // top of the page
    function top($sidebar=true) {
        global $root;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<html>
<head>
<title><?php if(!empty($this->title)) { echo $this->title.' | '; } ?>The Next HOPE Schedule Admin</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="<?=$root?>style/reset.css" />
<link rel="stylesheet" type="text/css" href="<?=$root?>style/admin.css" />
<?=$this->head?>
</head>

<body<?php if(!empty($this->onload)) { echo ' '.$this->onload; } ?>>

<div id="wrapper">
<div id="header">The Next HOPE Schedule Admin<?php if(!empty($this->title)) { echo '<br>'.$this->title; } ?></div>
<div id="sidebar"><?php if($sidebar) { ?>
	<ul>
		<li style="font-size:.8em;">Hello <?=html_sanitize($_SESSION['user']['username'])?>!</li>
		<li><a href="<?=$root?>speakers.php">Speakers</a></li>
		<li><a href="<?=$root?>locations.php">Locations</a></li>
		<li><a href="<?=$root?>events.php">Events</a></li>
		<li><a href="<?=$root?>users.php">Users</a></li>
		<li><a href="<?=$root?>logout.php">Logout</a></li>
	</ul>
<?php } ?>&nbsp;</div>
<div id="content">
<?php
    }

    // bottom of the page
    function bottom() {
?>
</div>
</div>
</body>
</html>
<?php
    }
}

// schedule display layout
class layout {
    var $title = "";
    var $head = "";
    var $onload = "";
    var $using_mobile = false;

    // constructor
    function layout($title, $head="", $onload="") {
        $this->title = $title;
        $this->head = $head;
        $using_mobile = using_mobile();
    }

    // top of the page
    function top() {
        global $root;
        if($this->using_mobile) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<html>
<head>
<title><?php if(!empty($this->title)) { echo $this->title.' | '; } ?>The Next HOPE Schedule</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="<?=$root?>style/reset.css" />
<link rel="stylesheet" type="text/css" href="<?=$root?>style/mobile.css" />
<?=$this->head?>
</head>

<body<?php if(!empty($this->onload)) { echo ' '.$this->onload; } ?>>
<p>mobile version</p>
<?php
		} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<html>
<head>
<title><?php if(!empty($this->title)) { echo $this->title.' | '; } ?>The Next HOPE Schedule</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="<?=$root?>style/reset.css" />
<link rel="stylesheet" type="text/css" href="<?=$root?>style/desktop.css" />
<?=$this->head?>
</head>

<body<?php if(!empty($this->onload)) { echo ' '.$this->onload; } ?>>
<p>desktop version</p>
<?php
		}
    }

    // bottom of the page
    function bottom() {
		if($this->using_mobile) {
?>
</body>
</html>
<?php
		} else {
?>
</body>
</html>
<?php
		}
    }
}
