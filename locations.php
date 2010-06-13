<?php
// app header
require_once("head.inc.php");
require_once($root."include/phpass.class.php");
authenticate();

// process q
if(isset($_GET['q']))		$q = $_GET['q'];
else if(isset($_POST['q'])) 	$q = $_POST['q'];
else				$q = '';

// display locations
function display_locations() {
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
	$output .= '<th>Location</th>';
	$output .= '<th>&nbsp;</th>';
	$output .= '</tr>';
	while($location_row = $db->fetch_array($query_id)) {
		$output .= '<tr>';
		$output .= '<td>'.html_sanitize($location_row['location']).'</td>';
		$output .= '<td>';
		$output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=edit&location_id='.$location_row['id'].'">edit</a> ';
		$output .= '<a href="'.$_SERVER['PHP_SELF'].'?q=delete&location_id='.$location_row['id'].'" onclick="if(confirm(\'Are you sure?\')) return; else return false;">delete</a>';
		$output .= '</td>';
		$output .= '</tr>';
	}
	$output .= '</table>';

	return $output;
}

$title = "Manage Locations";
switch($q) {
	default:
		break;

	case "add":
		$title = "Add Location";
		break;

	case "edit":
		$title = "Edit Location";
		
		// validate input
		$location_id = (int)$_GET['location_id'];
		$db->query("select id,location from ".$db->pre."location where id='".$location_id."'");
		if($db->num_rows() == 0) {
			$invalid_id = true;
			break;
		}
		
		// load the data
		$location_row = $db->fetch_array();
		break;

	case "add_submit":
	case "edit_submit":
		if($q == "add_submit")	$title = "Add Location";
		if($q == "edit_submit")	$title = "Edit Location";
		
		// get input, and validate
		$error = array();
		
		// id
		if($q == "edit_submit") {
			$location_id = (int)$_POST['location_id'];
			$db->query("select id from ".$db->pre."location where id='".$db->escape($location_id)."'");
			if($db->num_rows() == 0)
				$invalid_id = true;
		}
		
		// location
		$location_row['location'] = $_POST['location'];
		if(empty($location_row['location']))
			$error['location'] = 'Location cannot be blank';

		// should we end?
		if(sizeof($error) > 0)
			break;
		
		// update database
		if($q == "add_submit") {
			$db->query_insert("location", $location_row);
		}
		else if($q == "edit_submit") {
			$db->query_update("location", $location_row, "id='".$db->escape($location_id)."'");
		}
		break;

	case "delete":
		$title = "Delete Location";
		
		// validate input
		$location_id = (int)$_GET['location_id'];
		$location_rows = $db->query("select id from ".$db->pre."location where id='".$location_id."'");
		if($db->num_rows() == 0) {
			$invalid_id = true;
			break;
		}
		$location_row = $db->fetch_array($location_rows);
		
		// delete the speaker
		$db->query("delete from ".$db->pre."location where id='".$db->escape($location_id)."'");
		$db->query("update ".$db->pre."event set location_id='0' where location_id='".$db->escape($location_id)."'");
		break;
}

// top of the page
$l = new layout_admin($title);
$l->top();

// page content
switch($q) {
	default:
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?q=add">Add location</a></p>';
		$db->query("select id,location from ".$db->pre."location order by location");
		echo display_locations();
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
<p>Location added successfully</p>
				<?php
				break;
			} else if($q == "edit_submit") {
				?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>Location edited successfully</p>
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
<input type="hidden" name="location_id" value="<?=html_sanitize($location_id)?>">
<?php } ?>
<fieldset>
	<?php if($q == "add" || $q == "add_submit") { ?>
	<legend>Add new location</legend>
	<?php } else if($q == "edit" || $q == "edit_submit") { ?>
	<legend>Update location <?=html_sanitize($location_row['location'])?></legend>
	<?php } ?>
	<ul>
		<?php
		form_field('text', 'location', 'Location', $location_row['location'], $error['location']);
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
<p>Location deleted successfully</p>
			<?php
		}
		break;
}

// bottom of page
$l->bottom();

// app footer
require_once($GLOBALS['root']."include/foot.inc.php");

?>
