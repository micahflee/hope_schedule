<?php
// app header
require_once("head.inc.php");
require_once($root."include/phpass.class.php");
authenticate();

// process q
if(isset($_GET['q']))		   $q = $_GET['q'];
else if(isset($_POST['q']))	 $q = $_POST['q'];
else							$q = '';

// display users
function display_events() {
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
	$output .= '<th>Title</th>';
	$output .= '<th>Meta</th>';
    $output .= '<th>Speakers</th>';
    $output .= '<th>Description</th>';
	$output .= '<th>&nbsp;</th>';
	$output .= '</tr>';
	while($event_row = $db->fetch_array($query_id)) {
		$location_rows = $db->query("select location from ".$db->pre."location where id='".$db->escape($event_row['location_id'])."'");
		$location_row = $db->fetch_array($location_rows);
		
		$description = $event_row['description'];
		if(strlen($description) > 200)
			$description = substr($description, 0, 200)."...";

        $speakers = '';
        $speaker_rows = $db->query("select ".$db->pre."speaker.name from ".$db->pre."speaker left join ".$db->pre."event_speaker on ".$db->pre."event_speaker.speaker_id=".$db->pre."speaker.id where ".$db->pre."event_speaker.event_id='".$event_row['id']."' order by ".$db->pre."speaker.name");
        while($speaker_row = $db->fetch_array($speaker_rows)) {
            $speakers .= $speaker_row['name'].'<br/>';
        }

		$output .= '<tr>';
		$output .= '<td>'.html_sanitize($event_row['title']).'</td>';
		$output .= '<td>'.html_sanitize($location_row['location']).';<br/>'.date("M j Y, g:ia", html_sanitize($event_row['timestamp'])).'</td>';
		$output .= '<td>'.$speakers.'</td>';
		$output .= '<td>'.html_sanitize($description).'</td>';
		$output .= '<td>';
		$output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=edit&event_id='.$event_row['id'].'">edit</a> ';
		$output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=delete&event_id='.$event_row['id'].'" onclick="if(confirm(\'Are you sure?\')) return; else return false;">delete</a>';
		$output .= '</td>';
		$output .= '</tr>';
	}
	$output .= '</table>';

	return $output;
}

$title = "Manage Events";
switch($q) {
	default:
		break;

	case "add":
		$title = "Add Event";
        $speakers = array();
        break;

	case "edit":
		$title = "Edit Event";
		
		// validate input
		$event_id = (int)$_GET['event_id'];
		$db->query("select id,timestamp,location_id,title,description from ".$db->pre."event where id='".$event_id."'");
		if($db->num_rows() == 0) {
			$invalid_id = true;
			break;
		}
		
		// load the data
        $event_row = $db->fetch_array();
        $speakers = array();
        $es_rows = $db->query("select speaker_id from ".$db->pre."event_speaker where event_id='".$event_id."'");
        while($es_row = $db->fetch_array($es_rows))
            $speakers[$es_row['speaker_id']] = 'on';
		break;

	case "add_submit":
	case "edit_submit":
		if($q == "add_submit")	$title = "Add Event";
		if($q == "edit_submit")	$title = "Edit Event";
		
		// get input, and validate
		$error = array();
		
		// id
		if($q == "edit_submit") {
			$event_id = (int)$_POST['event_id'];
			$db->query("select id from ".$db->pre."event where id='".$db->escape($event_id)."'");
			if($db->num_rows() == 0)
				$invalid_id = true;
		}
		
		// timestamp
		$datetime = $_POST['datetime'];
		$event_row['timestamp'] = strtotime($datetime);
		if($event_row['timestamp'] == false)
			$error['datetime'] = 'Invalid date/time';
		
		// location_id
		$event_row['location_id'] = (int)$_POST['location_id'];
		$location_rows = $db->query("select id from ".$db->pre."location where id='".$db->escape($event_row['location_id'])."'");
		if($db->num_rows($location_rows) == 0)
			$error['location_id'] = 'Invalid location';

        // speakers
        $speakers = $_POST['speakers'];

		// title
		$event_row['title'] = $_POST['title'];
		if(empty($event_row['title']))
			$error['title'] = 'Title cannot be blank';
			
		// description
		$event_row['description'] = $_POST['description'];
		if(empty($event_row['description']))
			$error['description'] = 'Description cannot be blank';
		
		// should we end?
		if(sizeof($error) > 0)
			break;
		
		// update database
		if($q == "add_submit") {
			$db->query_insert("event", $event_row);
		}
		else if($q == "edit_submit") {
			$db->query_update("event", $event_row, "id='".$db->escape($event_id)."'");
        }
        $db->query("delete from ".$db->pre."event_speaker where event_id='".$db->escape($event_id)."'");
        foreach($speakers as $key => $val) {
            $speaker_rows = $db->query("select id from ".$db->pre."speaker where id='".$db->escape($key)."'");
            if($db->num_rows($speaker_rows) > 0) {
                $es_row = array();
                $es_row['event_id'] = $db->escape($event_id);
                $es_row['speaker_id'] = $db->escape($key);
                $db->query_insert("event_speaker", $es_row);
            }
        }
		break;

	case "delete":
		$title = "Delete Event";
		
		// validate input
		$event_id = (int)$_GET['event_id'];
		$event_rows = $db->query("select id from ".$db->pre."event where id='".$event_id."'");
		if($db->num_rows() == 0) {
			$invalid_id = true;
			break;
		}
		$event_row = $db->fetch_array($event_rows);
		
		// delete the event
		$db->query("delete from ".$db->pre."event where id='".$db->escape($event_id)."'");
		$db->query("delete from ".$db->pre."event_speaker where event_id='".$db->escape($event_id)."'");
		break;
}

// top of the page
$l = new layout_admin($title);
$l->top();

// page content
switch($q) {
	default:
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?q=add">Add event</a></p>';
		$db->query("select id,timestamp,location_id,title,description from ".$db->pre."event order by timestamp");
		echo display_events();
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
<p>Event added successfully</p>
				<?php
				break;
			} else if($q == "edit_submit") {
				?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>Event edited successfully</p>
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
<input type="hidden" name="event_id" value="<?=html_sanitize($event_id)?>">
<?php } ?>
<fieldset>
	<?php if($q == "add" || $q == "add_submit") { ?>
	<legend>Add new event</legend>
	<?php } else if($q == "edit" || $q == "edit_submit") { ?>
	<legend>Update event <?=html_sanitize($event_row['title'])?></legend>
	<?php } ?>
	<ul>
	<?php
	// location values
	$location_values = '';
	$location_rows = $db->query("select id,location from ".$db->pre."location order by location");
	while($location_row = $db->fetch_array($location_rows)) {
		$location_values .= '<option value="'.html_sanitize($location_row['id']).'"'.(($location_row['id']==$event_row['location_id'])?' selected':'').'>'.html_sanitize($location_row['location']).'</option>';
	}
	
	$datetime = '';
	if($q == "add")
		$datetime = date("M j Y, g:i a");
	else
		$datetime = date("M j Y, g:i a", $event_row['timestamp']);
	form_field('text', 'title', 'Title', $event_row['title'], $error['title']);
	form_field('text', 'datetime', 'Date/Time', $datetime, $error['datetime']);
    form_field('dropdown', 'location_id', 'Location', $location_values, $error['location_id']);
    ?>
        <li>
            <label>Speakers</label>
            <div style="float:left"><ul>
    <?php
    $speaker_rows = $db->query("select id,name from ".$db->pre."speaker order by name");
    while($speaker_row = $db->fetch_array($speaker_rows)) {
        $checked = isset($speakers[$speaker_row['id']]);
        form_field('checkbox', 'speakers['.$speaker_row['id'].']', $speaker_row['name'], 'on', '', $checked);
    }
    ?>
            </ul></div>
        </li>
    <?php
    form_field('textarea', 'description', 'Description', $event_row['description'], $error['description']);
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
<p>Event deleted successfully</p>
			<?php
		}
		break;
}

// bottom of page
$l->bottom();

// app footer
require_once($GLOBALS['root']."include/foot.inc.php");

?>
