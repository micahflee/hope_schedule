<?php
// include files
require_once($root."include/config.inc.php");
require_once($root."include/database.class.php");
require_once($root."include/sanitizer.class.php");
require_once($root."include/layout.class.php");
require_once($root."include/helpers.inc.php");
require_once($root."include/form.inc.php");

// connect to the database
$db = new database($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database'], $config['db']['prefix']);
$db->connect();

// start the session
session_start();
?>
