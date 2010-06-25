<?php
// app header
require_once("head.inc.php");
authenticate();

// process q
if(isset($_GET['q']))		$q = $_GET['q'];
else if(isset($_POST['q'])) 	$q = $_POST['q'];
else				$q = '';

$title = "Update Notice";
if($q == "update") {
    // get input, and validate
    $error = array();
    
    // notice
    $notice = $_POST['notice'];
    
    // should we end?
    if(sizeof($error) > 0)
        break;
    
    // update database
    $db->query("update ".$db->pre."notice set notice='".$db->escape($notice)."'");
} else {
    $notice_rows = $db->query("select * from ".$db->pre."notice");
    $notice_row = mysql_fetch_array($notice_rows);
    $notice = $notice_row['notice'];
}

// top of the page
$l = new layout_admin($title);
$l->top();

// page content
switch($q) {
    default:
    case "update":
	// if submitting a form without error
	if(sizeof($error) == 0 && $q == "update") {
	    ?>
<script language="javascript">setTimeout('window.location="<?=$_SERVER['PHP_SELF']?>"', 1000)</script>
<p>Location added successfully</p>
	    <?php
            break;
        }
	
	// display the form
	?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
<input type="hidden" name="q" value="update">
<fieldset>
	<legend>Update Notice</legend>
	<ul>
		<?php
		form_field('textarea', 'notice', 'Notice', $notice, $error['notice']);
		form_submit();
		?>
	</ul>
</fieldset>
</form>
	<?php
	break;
}

// bottom of page
$l->bottom();

// app footer
require_once($GLOBALS['root']."include/foot.inc.php");

?>
