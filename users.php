<?php
// app header
require_once("head.inc.php");
require_once($root."include/phpass.class.php");
authenticate();

// process q
if(isset($_GET['q']))           $q = $_GET['q'];
else if(isset($_POST['q']))     $q = $_POST['q'];
else                            $q = '';

$hasher = new PasswordHash(8, false);

// display users
function display_users() {
    global $db;
    $query_id = $db->query_id;

    // if it's empty, return no results
    if($db->num_rows($query_id) == 0) {
        return '<p>No results.</p>';
    }

    // generate the results
    $output = '';
    $output .= '<table>';
    $output .= '<tr>';
    $output .= '<th>Username</th>';
    $output .= '<th>&nbsp;</th>';
    $output .= '</tr>';
    while($user_row = $db->fetch_array($query_id)) {
        $output .= '<tr>';
        $output .= '<td>'.html_sanitize($user_row['username']).'</td>';
        $output .= '<td>';
        $output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=edit&user_id='.$user_row['id'].'">edit</a> ';
        $output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=delete&user_id='.$user_row['id'].'" onclick="if(confirm(\'Are you sure?\')) return; else return false;">delete</a>';
        $output .= '</td>';
        $output .= '</tr>';
    }
    $output .= '</table>';

    return $output;
}

$title = "Manage Users";
switch($q) {
    default:
        break;

    case "add":
        $title = "Add User";
        break;

    case "edit":
        $title = "Edit User";
        
        // validate input
        $user_id = (int)$_GET['user_id'];
        $db->query("select id,username from ".$db->pre."user where id='".$user_id."'");
        if($db->num_rows() == 0) {
            $invalid_id = true;
            break;
        }
        
        // load the data
        $user_row = $db->fetch_array();
        break;

    case "add_submit":
    case "edit_submit":
        if($q == "add_submit")	$title = "Add User";
        if($q == "edit_submit")	$title = "Edit User";
        
        // get input, and validate
        $error = array();
        
        // id
        if($q == "edit_submit") {
            $user_id = (int)$_POST['user_id'];
            $db->query("select id from ".$db->pre."user where id='".$db->escape($user_id)."'");
            if($db->num_rows() == 0)
                $invalid_id = true;
        }
        
        // username
        $user_row['username'] = $_POST['username'];
        if(empty($user_row['username']))
            $error['username'] = 'Username cannot be blank';
        
        // password
        if(!($q == "edit_submit" && $_POST['password'] == '')) {
            $user_row['password'] = $_POST['password'];
            if(empty($user_row['password'])) {
                $error['password'] = 'Password cannot be blank';
            } else {
                // hash the password
                $user_row['password'] = $hasher->HashPassword($user_row['password']);
            }
        }

        // should we end?
        if(sizeof($error) > 0)
            break;
        
        // update database
        if($q == "add_submit") {
            $db->query_insert("user", $user_row);
        }
        else if($q == "edit_submit") {
            $db->query_update("user", $user_row, "id='".$db->escape($user_id)."'");
        }
        break;

    case "delete":
        $title = "Delete User";
        
        // validate input
        $user_id = (int)$_GET['user_id'];
        $user_rows = $db->query("select id,username from ".$db->pre."user where id='".$user_id."'");
        if($db->num_rows() == 0 || ($user_id == $_SESSION['user']['id'])) {
            $invalid_id = true;
            break;
        }
        $user_row = $db->fetch_array($user_rows);
        
        // delete the user
        $db->query("delete from ".$db->pre."user where id='".$db->escape($user_id)."'");
        break;
}

// top of the page
$l = new layout_admin($title);
$l->top();

// page content
switch($q) {
    default:
        echo '<p><a href="'.$_SERVER['PHP_SELF'].'?q=add">Add user</a></p>';
        $db->query("select id,username from ".$db->pre."user order by username");
        echo display_users();
        break;

    case "add":
    case "add_submit":
    case "edit":
    case "edit_submit":
        // if invalid id
        if($invalid_id) {
            ?>
<p class="error">Invalid id</p>
<p><a href="<?=$_SERVER['PHP_SELF']?>">Go back</a></p>
            <?php
            break;
        }
        
        // if submitting a form without error
        if(sizeof($error) == 0) {
            if($q == "add_submit") {
                ?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>User added successfully</p>
                <?php
                break;
            } else if($q == "edit_submit") {
                ?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>User edited successfully</p>
                <?php
                break;
            }
        }
        
        // display the form
        ?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
<?php if($q == "add" || $q == "add_submit") { ?>
<input type="hidden" name="q" value="add_submit">
<?php } else if($q == "edit" || $q == "edit_submit") { ?>
<input type="hidden" name="q" value="edit_submit">
<input type="hidden" name="user_id" value="<?=html_sanitize($user_id)?>">
<?php } ?>
<fieldset>
    <?php if($q == "add" || $q == "add_submit") { ?>
    <legend>Add new user</legend>
    <?php } else if($q == "edit" || $q == "edit_submit") { ?>
    <legend>Update user <?=html_sanitize($user_row['username'])?></legend>
    <?php } ?>
    <ul>
        <?php
        form_field('text', 'username', 'Username', $user_row['username'], $error['username']);
        form_field('password', 'password', 'Password', '', $error['password']);
        form_submit();
        ?>
    </ul>
</fieldset>
</form>
<p><a href="<?=$_SERVER['HTTP_REFERER']?>">Go back</a></p>
        <?php
        break;

    case "delete":
        // if invalid id
        if($invalid_id) {
            ?>
<p class="error">Invalid id</p>
<p><a href="<?=$_SERVER['PHP_SELF']?>">Go back</a></p>
            <?php
        } else {
            ?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>User deleted successfully</p>
            <?php
        }
        break;
}

// bottom of page
$l->bottom();

// app footer
require_once($GLOBALS['root']."include/foot.inc.php");

?>
