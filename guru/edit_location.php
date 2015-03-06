<?php # Admin - edit_location.php
// This page is for editing a location record.
// This page is accessed through edit_event.php and locations.php

require ('includes/config.inc.php');
$page_title = 'Edit a Location';
include ('includes/header.php');

echo '<h1>Edit a Location</h1>';

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
	if (empty($_POST['location_name'])) {
		$errors[] = 'You forgot to enter the venue name.';
	} else {
		$ln = mysqli_real_escape_string($dbc, trim($_POST['location_name']));
	}
	
	// Need to check to make sure that everything is selected properly.
	// Check for a master location:
	if (isset($_POST['master']) && filter_var($_POST['master'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {
		$ml = $_POST['master'];
	} else {
		$ml = 'NULL';
	}
	
	// Get the admin_id:
	$aid = ($_SESSION['admin_id']);
	$lid = $_GET['lid'];
	
	if ($ln && $ml && $aid && $lid) { // If everything's OK.
	
		//  Test for unique name:
		//$q = "SELECT location_name FROM locations WHERE location_name='$ln' AND event_id != '$id'";
		//$r = @mysqli_query($dbc, $q);
		//if (mysqli_num_rows($r) == 0) {

			// Make the query:
			$q = "UPDATE locations SET location_name='$ln', master_location_id='$ml', last_activity=NOW(), last_admin_id='$aid' WHERE location_id='$lid' LIMIT 1";
			$r = @mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.

				// Print a message:
				echo '<p>The event has been edited.</p>';	
				
			} else { // If it did not run OK.
				echo '<p class="error">The event could not be edited due to a system error. We apologize for any inconvenience.</p>'; // Public message.
				echo '<p>' . mysqli_error($dbc) . '<br />Query: ' . $q . '</p>'; // Debugging message.
			}
				
		//} else { // Already registered.
		//	echo '<p class="error">The event name has already been registered.</p>';
		//}
		
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

$lid = $_GET['id'];

$q = "SELECT location_name, master_location_id, address_one, city, state_name, st.state_id, postal_code 
FROM locations AS loc
LEFT JOIN address_locations AS al ON loc.location_id = al.location_id
LEFT JOIN addresses AS adr ON al.address_id = adr.address_id
LEFT JOIN states AS st ON adr.state_id = st.state_id
WHERE loc.location_id=$lid";		
$r = mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) !== -1) { // Valid event ID, show the form.

	// Get the user's information:
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$loc = $row[1];
	$adr1 = $row[2];
	$c = $row[3];
	$st = $row[4];
	$post = $row[6];
	
	echo '<p><b>' . $row[0] . '</b></p>';
	if ($row[1] !== NULL) {
		echo '<p><a href="edit_location.php?id=' . $loc . '">' . $row[1] . '</a></p>';
	}
	echo '<p>' . $adr1 . '</p>
	<p>' . $c .', ' . $st . ' ' . $post . '<br/><br />';

	// Create the form:
	echo '<form action="edit_location.php" method="post">
	<fieldset>
	<small>If you need to change the master location address, please click on the location link above and edit it from there.  Also, you can only edit either the master location selection or the address, not both from this screen.  If you select the master the address will not be modified.</small><br /><br />
	<p><b>Venue Name:</b> <input type="text" name="location_name" size="30" maxlength="100" value="' . $row[0] . '" /></p>
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Master Location:</b> <select name="master"><option>--(Optional) Select One--</option>';
    // Retrieve all the relevant locations and add to the pull-down menu.
		$q = "SELECT location_id, location_name FROM locations ORDER BY location_name ASC";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
				echo "<option value=\"$row[0]\"";
				// Check for stickiness:
				if (isset($_POST['master']) && ($_POST['master'] == $row[0]) ) echo 'selected="selected"';
				echo ">$row[1]</option>\n";
			}
		} else {
			echo '<option>Please add a new location first.</option>';
		}
	echo ' </select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_location.php" title="Add Location to Database" target="_blank">Add a location</a></p>
	<p><b>Address:</b> <input type="text" name="address_one" size="30" maxlength="100" value="' . $adr1 . '" /></p>
	<p><b>City:</b> <input type="text" name="city" size="20" maxlength="50" value="' . $c . '" /></p>
	<p><b>State:</b> <select name="state"><option>-- Select State --</option>';
		$q = "SELECT state_id, state_name FROM states ORDER BY state_name ASC";
		$r = mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) > 0) {
			while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
				echo "<option value=\"$row[0]\"";
				// Check for stickiness:
			if (isset($_POST['state']) && ($_POST['state'] == $row[0]) ) echo 'selected="selected"';
				echo ">$row[1]</option>\n";
			}
		} else {
			echo '<option>Please add a new state first.</option>';
		}
    	echo '</select></p>
	<p><b>Postal Code:</b> <input type="text" name="postal_code" size="5" maxlength="10" value="' . $post . '" /></p>
	<p><input type="submit" name="submit" value="Submit" /></p>
	<input type="hidden" name="lid" value="' . $lid . '" />
	</fieldset></form>';

} else { // Not a valid user ID.
	echo '<p class="error">This page has been accessed in error.</p>';
}
}

mysqli_close($dbc);
include ('includes/footer.php');
?>