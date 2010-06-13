<?php
// app header
require_once("head.inc.php");
require_once($root."include/phpass.class.php");
$l = new layout_admin("Login");

$q = $_POST['q'];
switch($q) {
    case "login":
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // validate input
        $error = array();
        if(empty($username))
            $error['username'] = 'Username cannot be empty';
        if(empty($password))
            $error['password'] = 'Password cannot be empty';
        if(sizeof($error) > 0)
            break;
        
        // try selecting the user
        $hasher = new PasswordHash(8, false);
        $user_rows = $db->query("select id,username,password from ".$db->pre."user where username='".$db->escape($username)."'");
        if($db->num_rows() > 0) {
			// get the user row
			$user_row = $db->fetch_array($user_rows);
			
			// check the password
			if($hasher->CheckPassword($password, $user_row['password'])) {
				// we found a valid user
				$_SESSION['user']['id'] = $user_row['id'];
				$_SESSION['user']['username'] = $user_row['username'];
				redirect("speakers.php");
			}
        }
        break;
}

// top of the page
$l->top(false);

// page content
?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
<input type="hidden" name="q" value="login">
<fieldset>
    <?php
    if($q == "login") {
        // display error if we haven't redirected yet
        echo '<p class="error">Invalid username or password</p>';
    }
    ?>
    <legend>Login here if you're leet enough</legend>
    <ul>
        <?php
        form_field('text', 'username', 'Username', $username, $error['username']);
        form_field('password', 'password', 'Password', '', $error['password']);
        form_submit();
        ?>
    </ul>
</fieldset>
</form>
<?php

// bottom of page
$l->bottom();

// app footer
require_once($GLOBALS['root']."include/foot.inc.php");

?>
