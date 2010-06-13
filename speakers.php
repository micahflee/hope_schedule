<?php
// app header
require_once("head.inc.php");
require_once($root."include/phpass.class.php");
authenticate();

// process q
if(isset($_GET['q']))           $q = $_GET['q'];
else if(isset($_POST['q']))     $q = $_POST['q'];
else                            $q = '';

// display speakers
function display_speakers() {
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
    $output .= '<th>Name</th>';
    $output .= '<th>Bio</th>';
    $output .= '<th>&nbsp;</th>';
    $output .= '</tr>';
    while($speaker_row = $db->fetch_array($query_id)) {
		$bio = $speaker_row['bio'];
		if(strlen($bio) > 200)
			$bio = substr($bio, 0, 200).' <span class="elipses">...</span>';
		
        $output .= '<tr>';
        $output .= '<td>'.html_sanitize($speaker_row['name']).'</td>';
        $output .= '<td>'.html_sanitize($bio).'</td>';
        $output .= '<td>';
        $output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=edit&speaker_id='.$speaker_row['id'].'">edit</a> ';
        $output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=delete&speaker_id='.$speaker_row['id'].'" onclick="if(confirm(\'Are you sure?\')) return; else return false;">delete</a>';
        $output .= '</td>';
        $output .= '</tr>';
    }
    $output .= '</table>';

    return $output;
}

$title = "Manage Speakers";
switch($q) {
    default:
        break;

    case "add":
        $title = "Add Speaker";
        break;

    case "edit":
        $title = "Edit Speaker";
        
        // validate input
        $speaker_id = (int)$_GET['speaker_id'];
        $db->query("select id,name,bio from ".$db->pre."speaker where id='".$speaker_id."'");
        if($db->num_rows() == 0) {
            $invalid_id = true;
            break;
        }
        
        // load the data
        $speaker_row = $db->fetch_array();
        break;

    case "add_submit":
    case "edit_submit":
        if($q == "add_submit")	$title = "Add Speaker";
        if($q == "edit_submit")	$title = "Edit Speaker";
        
        // get input, and validate
        $error = array();
        
        // id
        if($q == "edit_submit") {
            $speaker_id = (int)$_POST['speaker_id'];
            $db->query("select id from ".$db->pre."speaker where id='".$db->escape($speaker_id)."'");
            if($db->num_rows() == 0)
                $invalid_id = true;
        }
        
        // name
        $speaker_row['name'] = $_POST['name'];
        if(empty($speaker_row['name']))
            $error['name'] = 'Name cannot be blank';
        
        // bio
        $speaker_row['bio'] = $_POST['bio'];
        if(empty($speaker_row['bio']))
            $error['bio'] = 'Bio cannot be blank';

        // should we end?
        if(sizeof($error) > 0)
            break;
        
        // update database
        if($q == "add_submit") {
            $db->query_insert("speaker", $speaker_row);
        }
        else if($q == "edit_submit") {
            $db->query_update("speaker", $speaker_row, "id='".$db->escape($speaker_id)."'");
        }
        break;

    case "delete":
        $title = "Delete Speaker";
        
        // validate input
        $speaker_id = (int)$_GET['speaker_id'];
        $speaker_rows = $db->query("select id from ".$db->pre."speaker where id='".$speaker_id."'");
        if($db->num_rows() == 0) {
            $invalid_id = true;
            break;
        }
        $speaker_row = $db->fetch_array($speaker_rows);
        
        // delete the speaker
        $db->query("delete from ".$db->pre."speaker where id='".$db->escape($speaker_id)."'");
        $db->query("delete from ".$db->pre."event_speaker where speaker_id='".$db->escape($speaker_id)."'");
        break;
}

// top of the page
$l = new layout_admin($title);
$l->top();

// page content
switch($q) {
    default:
        echo '<p><a href="'.$_SERVER['PHP_SELF'].'?q=add">Add speaker</a></p>';
        $db->query("select id,name,bio from ".$db->pre."speaker order by name");
        echo display_speakers();
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
<p>Speaker added successfully</p>
                <?php
                break;
            } else if($q == "edit_submit") {
                ?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>Speaker edited successfully</p>
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
<input type="hidden" name="speaker_id" value="<?=html_sanitize($speaker_id)?>">
<?php } ?>
<fieldset>
    <?php if($q == "add" || $q == "add_submit") { ?>
    <legend>Add new speaker</legend>
    <?php } else if($q == "edit" || $q == "edit_submit") { ?>
    <legend>Update speaker <?=html_sanitize($speaker_row['name'])?></legend>
    <?php } ?>
    <ul>
        <?php
        form_field('text', 'name', 'Name', $speaker_row['name'], $error['name']);
        form_field('textarea', 'bio', 'Bio', $speaker_row['bio'], $error['bio']);
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
<p>Speaker deleted successfully</p>
            <?php
        }
        break;
}

// bottom of page
$l->bottom();

// app footer
require_once($GLOBALS['root']."include/foot.inc.php");

?>
