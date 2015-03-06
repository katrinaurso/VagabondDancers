<?php # Admin - verifying_event.php
// This page is for verifying/confirming an unverified_event and adding an event to the events table.
// This page is accessed through verify_events.php.

require ('includes/config.inc.php');
$page_title = 'Confirm and Edit an Event';
include ('includes/header.php');

echo '<h1>Confirm an Unconfirmed Event</h1>';

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

// Check if the form has been submitted:
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	require (MYSQL); 
	
	// Trim all the incmoing data:
	//$trimmed = array_map('trim', $_POST);
	
	// Asume invalid values:
	$en = $lid = $sty = $etype = $fid = $sdate = $stime = $edate = $etime = FALSE;
	
	// Check for an event name:
	if (preg_match ('/^[A-Z\d \'\!.,-]{2,40}$/i', trim($_POST['event_name']))) {
		$en = mysqli_real_escape_string ($dbc, trim($_POST['event_name']));
	} else {
		echo '<p class="error">Please enter a valid event name!</p>';
	}
	
	// Check for a location:
	if (isset($_POST['location']) && filter_var($_POST['location'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {
		$lid = $_POST['location'];
	} else {
		echo 'You forgot to select a location from the drop-down menu, or add a new location!';
	}
	
	// Check for selected dance styles:
	if ($_POST['dance_style']) {
		$sty = $_POST['dance_style'];
	} else {
		echo '<p class="error">Please select dance style(s)!</p>';
	}
	
	// Check for selected event types:
		if ($_POST['event_type']) {
		$etype = $_POST['event_type'];
	} else {
		echo '<p class="error">Please select event type(s)!</p>';
	}
	
	// Check for frequency
	if (isset($_POST['frequency']) && filter_var($_POST['frequency'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {
		$fid = $_POST['frequency'];
	} else {
		echo 'You forgot to select a frequency from the drop-down menu, or add a new location!';
	}
	
	// Check for start date (need to specify what exactally it needs to look for here)
	if (preg_match ('/^[A-Z\d \'\!.,-]{2,40}$/i', trim($_POST['start_date']))) {
		$sdate = mysqli_real_escape_string ($dbc, trim($_POST['start_date']));
	} else {
		echo '<p class="error">Please enter a valid start date!</p>';
	}
	
	// Check for start time
	if  (preg_match ('/^[A-Z\d \:-]{1,7}$/i', trim($_POST['start_time']))) {
		$stime = $_POST['start_time'];
	} else {
		echo '<p class="error">Please enter a start time.</p>';
	}
	
	// Check for end date (need to specify what exactally it needs to look for here)
	if (preg_match ('/^[A-Z\d \'\!.,-]{2,40}$/i', trim($_POST['end_date']))) {
		$edate = mysqli_real_escape_string ($dbc, trim($_POST['end_date']));
	} else {
		echo '<p class="error">Please enter a valid end date!</p>';
	}
	
	// Check for end time
	if  (preg_match ('/^[A-Z\d \:-]{1,7}$/i', trim($_POST['end_time']))) {
		$etime = $_POST['end_time'];
	} else {
		echo '<p class="error">Please enter an end time.</p>';
	}
		
	// Check for stop date
	
	
	// Get people_id
	if (isset($_POST['people_id']) && filter_var($_POST['people_id'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {
		$pid = $_POST['people_id'];
	}
	
	// Get unverified_id
	if (isset($_POST['unverified_id'])) {
		$unv = $_POST['unverified_id'];
	}
	
	// Get the date originally created
	if (isset($_POST['date_created'])) {
		$date_c = $_POST['date_created'];
	}
	
	// Get the admin_id:
	$aid = ($_SESSION['admin_id']);
	
	if ($en && $lid && $sty && $etype && $fid && $sdate && $stime && $edate && $etime) { // If everything's OK.

		// Add the event into the database:			
		mysqli_autocommit($dbc, FALSE);  // Turn off auto-commit.

		// Set and run $query1:
		$query1 = "INSERT INTO events (event_name, event_date_confirmed, confirmed_admin_id, people_id, unverified_id, event_date_created, last_admin_id, event_last_activity, frequency_id, start_date, start_time, end_date, end_time) VALUES ('$en', NOW(), $aid, $pid, $unv, '$date_c', $aid, NOW(), '$fid', '$sdate', '$stime', '$edate', '$etime')";
		$r = mysqli_query($dbc, $query1) or trigger_error("Query: $query1\n<br />MySQL Error: " . mysqli_error($dbc));
		if ($r !== TRUE) {
			mysqli_rollback($dbc);
		} else {
			 $eid = mysqli_insert_id($dbc);
		}

		// Set and run $query2:
		$query2 = "INSERT INTO event_locations (event_id, location_id) VALUE ($eid, $lid)";
		$r = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br />MySQL Error: " . mysqli_error($dbc));
		if ($r !== TRUE) {
			mysqli_rollback($dbc);  // if error, roll back transaction
		} 
			
		// Define and run $query3 to tie them together into the unverfied_dance_styles table:
		if ($sty !== -1) {
			for ($i=0; $i<sizeof($sty);$i++) {
				$query3 = "INSERT INTO event_dance_styles (event_id, dance_style_id) VALUES ('$eid', '" . $sty[$i] . "')";
				$r = mysqli_query($dbc, $query3) or trigger_error("Query: $query3\n<br />MySQL Error: " . mysqli_error($dbc));
			}
			if ($r !== TRUE) {
			mysqli_rollback($dbc);
			}
		}
		
		// Define and run $query4 to tie them together into the event_event_type table:
		if ($etype !== -1) {
			for ($i=0; $i<sizeof($etype);$i++) {
				$query4 = "INSERT INTO event_event_types (event_id, event_type_id) VALUES ('$eid', '" . $etype[$i] . "')";
				$r = mysqli_query($dbc, $query4) or trigger_error("Query: $query4\n<br />MySQL Error: " . mysqli_error($dbc));
			}
			if ($r !== TRUE) {
			mysqli_rollback($dbc);
			}
		}
			
		// Define and run $query5 to update the unverified event to Confirmed.
		$query5 = "UPDATE unverified_events SET confirmed='Y' WHERE unverified_id=$unv";
		if ($dbc) {
			$r = mysqli_query($dbc, $query5) or trigger_error("Query: $query5\n<br />MySQL Error: " . mysqli_error($dbc));
			if ($r !== TRUE) {
				mysqli_rollback($dbc);
			}
		}
			
		// Assuming no errors, commit transaction.
		mysqli_commit($dbc);
					
		if (mysqli_affected_rows($dbc) !== -1) { // If it ran OK.
			// Print a message:
			echo '<p>The event has been verified!  Thank you for all that you did in adding this event :).</p>';		
		} else { // If it did not run OK.
			echo '<p class="error">The event could not be confirmed due to a system error. We apologize for any inconvenience.</p>'; // Public message.
			echo '<p>' . mysqli_error($dbc) . ''; // Debugging message.
		}
	} // End of the if everything is is ok IF.
				
} // End of submit conditional.

// Always show the form...

// Retrieve the event's information:
$row = FALSE;

if (isset($_GET['uid']) && filter_var($_GET['uid'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {

require (MYSQL);

$uid = $_GET['uid'];
$q = "SELECT event_name, venue_name, city, st.state_name, GROUP_CONCAT(ds.dance_style_description SEPARATOR ', ') AS styles, evt.event_type_description, frq.frequency_description, uve.date, uve.time, DATE_FORMAT( uve.date_created,  '%M %d, %Y - %h:%m:%s' ) AS date_created, ppl.first_name, ppl.last_name, st.state_id, uve.unverified_id, uve.people_id, uve.date_created, photo_id, photo_name, comments
FROM unverified_events AS uve
LEFT JOIN states AS st ON uve.state_id = st.state_id
LEFT JOIN unverified_dance_styles as uvds on uve.unverified_id = uvds.unverified_id
LEFT JOIN dance_styles AS ds ON uvds.dance_style_id = ds.dance_style_id
LEFT JOIN event_types AS evt ON uve.event_type_id = evt.event_type_id
LEFT JOIN frequencies AS frq ON uve.frequency_id = frq.frequency_id
LEFT JOIN peoples AS ppl ON uve.people_id = ppl.people_id
LEFT JOIN unverified_photos AS up ON uve.unverified_id = up.unverified_id
WHERE uve.unverified_id=$uid";

$r = mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) !== -1) { // Valid user ID, show the form.

	// Get the unverified event's information:
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$pid = $row[16];
	$state = $row[12];
	
	// Links to Flag, Mark for Deletion, Mark as Duplicate:
    echo '<a href="flag_unverifiedevent.php?uid=' . $uid . '" class="button red">Flag This Event</a>
	<a href="duplicate_unverifiedevent.php?uid=' . $uid . '" class="button red">Mark as Duplicate</a>
	<a href="delete_unverifiedevent.php?uid=' . $uid . '" class="button red">Mark to Delete</a><br /><br />';
	
	// Show the unverified event's current information:
	echo '<p>Name: <b>' . $row[0] . '</b></p>
	<p>Venue: ' . $row[1] . '</p>
	<p>Location: ' . $row[2] . ', ' . $row[3] . '</p>
	<p>Dance Style(s): ' . $row[4] . '</p>
	<p>Event Type: ' . $row[5] . '</p>
	<p>Frequency: ' . $row[6] . '</p>
	<p>Date: ' . $row[7] . '</p>
	<p>Time: ' . $row[8] . '</p>
	<p>Comments: ' . $row[18] . '</p>
	<p>Date Created: ' . $row[9] . '</p>
	<p>Created By: ' . $row[10] . ' ' . $row[11] . '</p><br />';
	
	// Get the image information and display the image:
	// Check for image:
	if ($pid != NULL) {
		if ($image = @getimagesize ("/home1/ksanford/uploads/unverified/$pid")) {
			echo "<div style=\"max-width:200px;\"><img src=\"show_image.php?image=$pid&name=" . urlencode($row[17]) . "\" $image[3] alt=\"{$row[0]}\" /></div>";
		} 
	} else {
			echo 'No image was uploaded.'; 
	}
	echo '<br /><br />';

	
	// Create the form:
	echo '<form enctype="multipart/form-data" action="verifying_event.php" method="post">
	<fieldset>
	<input type="hidden" name="people_id" value="' . $row[14] . '" />
	<input type="hidden" name="date_created" value="' . $row[15] . '" />
	<input type="hidden" name="unverified_id" value="' . $row[13] . '" />
	<p><b>Event Name:</b> <input type="text" name="event_name" size="20" maxlength="100" value="' . $row[0] . '" /></p>
	<p><b>Location:</b> <select name="location"><option> -- If not listed please add below -- </option>';
		// Retrieve all the locations in the state of the event and add to the pull-down menu.
		$q = "SELECT loc.location_id, CONCAT(location_name, ': ', adr.address_one,', ', adr.city) as location_name
		FROM locations AS loc
		INNER JOIN address_locations AS adl ON loc.location_id = adl.location_id
		INNER JOIN addresses AS adr ON adl.address_id = adr.address_id
		WHERE adr.state_id = $state
		ORDER BY location_name, adr.city, adr.address_one ASC";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			while ($loc = mysqli_fetch_array ($r, MYSQLI_BOTH)) {
				echo "<option value=\"$loc[0]\"";
				// Check for stickiness:
			if (isset($_POST['existing']) && ($_POST['existing'] == $loc[0]) ) echo 'selected="selected"';
				echo ">$loc[1]</option>\n";
			}
		} else {
			echo '<option>Please add a new location first.</option>';
		}
		?>
    </select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_location.php?uid=' . $uid . '" title="Add Location to Database" target="_blank">Add a location</a></p>
    
    <?php
	echo '<p><b>Dance Style(s):</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_dancestyle.php" title="Add Dance Style to Database" target="_blank">Add a Dance Style</a></p>';
    $q = "SELECT dance_style_id, dance_style_description, master_dance_style_id FROM dance_styles";
 	$r = mysqli_query($dbc, $q);
	while($row = mysqli_fetch_array($r,  MYSQLI_NUM)){	
		//if ($row[2] !== NULL) {
		if ($row[0] > 1) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><input value=\"".$row[0]."\" name=\"dance_style[]\" type=\"checkbox\">".$row[1]."<br /></small>";
		}
	} 
	?><br />
    
     <?php
	echo '<p><b>Event Type(s):</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_eventtype.php" title="Add Event Type to Database" target="_blank">Add an Event Type</a></p>';
    $q = "SELECT event_type_id, event_type_description FROM event_types";
 	$r = mysqli_query($dbc, $q);
	while($row = mysqli_fetch_array($r,  MYSQLI_NUM)){
		// I need to do something about how to word the below line	
		if (($row[1] !== NULL) && ($row[0] > 1)) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><input value=\"".$row[0]."\" name=\"event_type[]\" type=\"checkbox\">".$row[1]."<br /></small>";
		}
	} 
	?><br />
   <!-- <?php
	echo '<p><b>Event Type:</b> <select name="event_types"><option> -- If not listed please add -- </option>';
		// Retrieve all the event types and add to the pull-down menu.
		$q = "SELECT event_type_id, event_type_description FROM event_types ORDER BY event_type_id ASC";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			while ($et = mysqli_fetch_array ($r, MYSQLI_NUM)) {
				echo "<option value=\"$et[0]\"";
			// Check for stickiness:
			if (isset($_POST['event_type']) && ($_POST['event_type'] == $et[0]) ) echo 'selected="selected"';
				echo ">$et[1]</option>\n";
			}
			}
		} else {
			echo '<option>Please add a new event type first.</option>';
		}
	?> 
    </select>
    </select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_eventtype.php" title="Add Event Type to Database" target="_blank">Add an Event Type</a></p> -->
    
	<p><b>Frequency:</b> <select name="frequency"><option>-- Select Frequency --</option>
    <?php // Retrieve all the dance types and add it to the drop down menu.
	$q = "SELECT frequency_id, frequency_description, frequency_code FROM frequencies ORDER BY frequency_description ASC";
	$r = mysqli_query($dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
			if ($row[0] > 1){
				echo "<option value=\"$row[0]\"";
			}
			// Check for stickiness:
		if (isset($_POST['frequency']) && ($_POST['frequency'] == $row[0]) ) echo 'selected="selected"';
			echo ">$row[1]</option>\n";
		}
	} else {
		echo '<option>Please add a new frequency first.</option>';
	}
	mysqli_close ($dbc);
	?>
    </select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_frequency.php" title="Add Frequency to Database" target="_blank">Add a Frequency</a></p>
    <p><label for="sdate"><b>Start Date: </b></label><input id="sdate" name="start_date" type="date" value="<?php echo date('Y-m-d'); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <b>Start Time:</b> <input type="text" name="start_time" size=8 maxlength=8 /></p>
    <p><label for="edate"><b>End Date: </b></label><input id="edate" name="end_date" type="date" value="<?php echo date('Y-m-d'); ?>" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
    <b>End Time:</b> <input type="text" name="end_time" size=8 maxlength=8 /></p>
    
    <!-- <b>Start Time:</b> <input type="text" name="start_time" size=8 maxlength=8 /> </p>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>End Time:</b> <input type="text" name="end_time" size=8 maxlength=8 /><small>(Please enter in the format of YYYY-MM-DD please)</small></p>
    <fieldset>
    <p><b>Contact Name:</b> <input type="text" name="contact_name" size=20 maxlength=50 /></p>
    <p><b>Contact Type:</b> <input type="text" name="contact_type" size=8 maxlength=8 /></p>
    <p><b>Contact Phone:</b> <input type="text" name="contact_phone" size=8 maxlength=8 /></p> -->
	</fieldset><br />
	<div align="center"><input type="submit" name="submit" value="Confirm It!" /></div>
	<input type="hidden" name="id" value="' . $uid . '" />
	</form>
	
<?php
}
include ('includes/footer.php');
?>