<?php
// app header
require_once("head.inc.php");
authenticate();

// kill the session
session_start();
session_unset();
session_destroy();

// redirect to the login page
redirect($root."login.php");
?>
