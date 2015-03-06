<?php # Admin - edit_event.php
// This page is for editing an event record.

require ('includes/config.inc.php');
$page_title = 'Edit an Event';
include ('includes/header.php');

#echo '<h1>Edit an Event</h1>';

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}
require (MYSQL); 

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$errors = array();
	
	// Check for an event name:
	if (empty($_POST['event_name'])) {
		$errors[] = 'You forgot to enter an event name.';
	} else {
		$en = mysqli_real_escape_string($dbc, trim($_POST['event_name']));
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
		$et = $_POST['event_type'];
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
	if (!empty($_POST['start_date'])) {
		if(preg_match ('/^[A-Z\d \'\!.,-]{2,40}$/i', trim($_POST['start_date']))) {
		$sdate = mysqli_real_escape_string ($dbc, trim($_POST['start_date']));
		} else {
		echo '<p class="error">Please enter a valid start date!</p>';
		}
	}
	
	// Check for stop date
	
	
	if (empty($errors)) { // If everything's OK.
	
		// Get the admin_id:
		$aid = ($_SESSION['admin_id']);
		$eid = ($_POST['eid']);
	
		//  Test for unique name at a location:
		$q = "SELECT event_name, evt.event_id, location_id
			FROM events AS evt
			LEFT JOIN event_locations AS eloc ON evt.event_id = eloc.event_id
			WHERE event_name='$en' AND evt.event_id != $eid AND location_id = $lid";
		#$q = "SELECT event_name FROM events WHERE event_name='$en' AND event_id != $eid";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) == 0) {

			// Make the query:
			$q = "UPDATE events SET event_name='$en', last_admin_id='$aid', frequency_id='$fid', event_last_activity=NOW(), start_date='$sdate' WHERE event_id=$eid LIMIT 1";
			$r = @mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.
			}
			
			// Query 2:
			$q = "UPDATE event_locations SET location_id='$lid' WHERE event_id=$eid LIMIT 1";
			$r = @mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.
			}
			
			// Define and run $query3 to tie them together into the dance_styles table:
			if ($sty !== -1) {
				for ($i=0; $i<sizeof($sty);$i++) {
					$query3 = "UPDATE event_dance_styles SET dance_style_id='$sty[$i]' WHERE event_id='$eid'";
					$r = mysqli_query($dbc, $query3) or trigger_error("Query: $query3\n<br />MySQL Error: " . mysqli_error($dbc));
				}
				if ($r !== TRUE) {
				mysqli_rollback($dbc);
				}
			}
			
			// Define and run $query4 to tie them together into the event_event_type table:
			if ($et !== -1) {
				for ($i=0; $i<sizeof($et);$i++) {
					$query4 = "UPDATE event_event_type SET event_type_id='$et[$i]' WHERE event_id='$eid'";
					$r = mysqli_query($dbc, $query4) or trigger_error("Query: $query4\n<br />MySQL Error: " . mysqli_error($dbc));
				}
				if ($r !== TRUE) {
				mysqli_rollback($dbc);
				}
			

				// Print a message:
				echo '<p>The event has been edited.  Thank you so much!</p>';	
				
			} else { // If it did not run OK.
				echo '<p class="error">The event could not be edited due to a system error. We apologize for any inconvenience.</p>'; // Public message.
				echo '<p>' . mysqli_error($dbc) . '<br />Query: ' . $q . '</p>'; // Debugging message.
			}
				
		} else { // Already registered.
			echo '<p class="error">The event name has already been registered.</p>';
		}
		
	} else { // Report the errors.

		echo '<p class="error">The following error(s) occurred:<br />';
		foreach ($errors as $msg) { // Print each error.
			echo " - $msg<br />\n";
		}
		echo '</p><p>Please try again.</p>';
	
	} // End of if (empty($errors)) IF.

} // End of submit conditional.

// Always show the form...

// Retrieve the event's information:
$row = FALSE;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {

$eid = $_GET['id'];

$q = "SELECT event_name, location_name, address_one, city, state_name, st.state_id, postal_code, evt.event_id, loc.location_id, sq1.event_dance_styles, sq2.event_type_description, frequency_description, start_date, start_time, event_date_created, first_name, last_name
FROM events AS evt
LEFT JOIN event_locations AS el ON evt.event_id = el.event_id
LEFT JOIN locations AS loc ON el.location_id = loc.location_id
LEFT JOIN address_locations AS al ON el.location_id = al.location_id
LEFT JOIN addresses AS adr ON al.address_id = adr.address_id
LEFT JOIN states AS st ON adr.state_id = st.state_id
LEFT JOIN (
   	SELECT GROUP_CONCAT(DISTINCT ds1.dance_style_description SEPARATOR ', ')
    AS event_dance_styles, eds1.event_id
    FROM event_dance_styles AS eds1
    LEFT JOIN dance_styles AS ds1 ON eds1.dance_style_id = ds1.dance_style_id
    WHERE eds1.event_id = event_id
    GROUP BY eds1.event_id
    ) AS sq1 ON evt.event_id = sq1.event_id
	LEFT JOIN ( 
	SELECT GROUP_CONCAT(DISTINCT et.event_type_description SEPARATOR ', ')
	AS event_type_description, eet.event_id
    FROM event_event_types AS eet
    INNER JOIN event_types AS et ON eet.event_type_id = et.event_type_id
    WHERE eet.event_id = event_id
    GROUP BY eet.event_id
    ) AS sq2 ON evt.event_id = sq2.event_id
LEFT JOIN frequencies AS freq ON evt.frequency_id = freq.frequency_id
LEFT JOIN peoples AS ppl ON evt.people_id = ppl.people_id
WHERE evt.event_id='$eid'";		
$r = @mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) == 1) { // Valid event ID, show the form.

	// Get the user's information:
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	
	// Defince $loc
	$loc = $row[8];
	
	echo '<h1>Edit Event: ' . $row[0] . '</h1>';
	
	// Show the unverified event's current information:
	echo '<p>Name: <b>' . $row[0] . '</b></p>
	<p>Venue: <a href="edit_location.php?id=' . $loc . '">' . $row[1] . '</a> <small>(Click to change venue information)</small></p>
	<p>Location: ' . $row[2] . '</p>
	<p>Location: ' . $row[3] . ', ' , $row[4] . ' ' . $row[6] . '</p>
	<p>Dance Style(s): ' . $row[9] . '</p>
	<p>Event Type: ' . $row[10] . '</p>
	<p>Frequency: ' . $row[11] . '</p>
	<p>Start Date: ' . $row[12] . '</p>
	<p>Time: ' . date("g:ia", strtotime($row[13])) . ' - (coming soon)</p>
	<p>Date Created: ' . $row[14] . '</p>
	<p>Created By: ' . $row[15] . ' ' . $row[16] . '<br /><br />';
	
	$state = $row[5];
	
	// Create the form to edit the event:
	echo '<form enctype="multipart/form-data" action="edit_event.php" method="post"><fieldset>';
	echo '<p>If you need to change details about the venue, please click the above link and edit it from there.  If you need to change the selected venue please change using the drop down menu.</p><br />
	<p><b>Event Name: </b><input type="text" name="event_name" size="30" maxlength="100" value="' . $row[0] . '" /></p>
	<p><b>Location: </b><select name="location"><option> -- If not listed please add below -- </option>';
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
				// Need to figure out how to preselect current selection already
				echo "<option value=\"$loc[0]\"";
				// Check for stickiness:
			if (isset($_POST['existing']) && ($_POST['existing'] == $loc[0]) ) echo 'selected="selected"';
				echo ">$loc[1]</option>\n";
			}
		} else {
			echo '<option>Please add a new location first.</option>';
		}
    echo '</select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_location.php" title="Add Location to Database" target="_blank">Add a location</a></p>
	<p><b>Dance Style(s):</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_dancestyle.php" title="Add Dance Style to Database" target="_blank">Add a Dance Style</a></p>';
    $q = "SELECT dance_style_id, dance_style_description, master_dance_style_id FROM dance_styles";
 	$r = mysqli_query($dbc, $q);
	while($row = mysqli_fetch_array($r,  MYSQLI_NUM)){	
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
		if ($row[0] > 1) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><input value=\"".$row[0]."\" name=\"event_type[]\" type=\"checkbox\">".$row[1]."<br /></small>";
		}
	} 
	?><br />
    
    <?php // Retrieve all the dance types and add it to the drop down menu.
	echo '<p><b>Frequency:</b> <select name="frequency"><option>-- Select Frequency --</option>';
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
	echo '</select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_frequency.php" title="Add Frequency to Database" target="_blank">Add a Frequency</a></p>';
	?>
	
    <p><b>Start Date:</b> <input type="text" name="start_date" size=10 maxlength=10 /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>End Date:</b> <input type="text" name="end_date" size=10 maxlength=10 />  <small>(Please enter in the format of YYYY-MM-DD please, leave "End Date" blank if this is a reoccuring event)</small></p>
	<?php echo '<p align="center"><input type="submit" name="submit" value="Make Changes!" /></p>
	<input type="hidden" name="eid" value="' . $eid . '" />
	</fieldset></form>';

} else { // Not a valid user ID.
	echo '<p class="error">This page has been accessed in error.</p>';
}
}

mysqli_close($dbc);
include ('includes/footer.php');
?>