<?php # Admin - delete_unverifiedevent.php

// This page markes an unverified event as deleted.
// This page is accessed through verifying_event.php and can only be accessed by logged in admin.

require ('includes/config.inc.php');
$page_title = 'Admin: Delete an Unverified Event';
include ('includes/header.php');

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

	if ($_POST['sure'] == 'Yes') { // Delete the record.

		$uid = ($_POST['uid']);

		// Make query.
		$q = "UPDATE unverified_events SET confirmed='X' WHERE unverified_id=$uid LIMIT 1";
		$r = mysqli_query ($dbc, $q); // Run the query.
		if ($r == TRUE) { // If it ran okay.
		
			// Frint a message.
			echo '<h1>Delete an Event</h1>
			<p>The event has been removed.</p><p><br /><br /></p>';
			
		} else { // If the query did not run okay.
			echo '<h1>System Error</h1>
			<p class="error">The event could not be updated due to a system error.</p>'; // Public message.
			echo '<p>' . mysqli_error() . '<br /><br />Query: ' . $query . '</p>'; // Debugging message.
		}
		
	} else { // Wasn't sure about deleting the user.
		echo '<h1>Delete an Event</h1>
		<p>The event has NOT been deleted.</p><p><br /><br /></p>';
	}
		
} else { // Show the form.

	// Retrieve the user's information.
	$uid = ($_GET['uid']);
	$q = "SELECT event_name FROM unverified_events WHERE unverified_id=$uid";
	$r = @mysqli_query ($dbc, $q); 
	
	if (mysqli_num_rows($r) == 1) { // Validate user ID, show the form.
	
		// Get the user's information.
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		
		// Display the record being deleted:
		echo "<h1>Delete an Event</h1>
		<h3>Name: $row[0]</h3>
		Are you sure you want to delete this event?";
		
		// Create the form:
		echo '<form action="delete_unverifiedevent.php" method="post">
		<input type="radio" name="sure" value="Yes" /> Yes 
		<input type="radio" name="sure" value="No" checked="checked" /> No
		<input type="submit" name="submit" value="Submit" />
		<input type="hidden" name="uid" value="' . $uid . '" />
		</form>';
		
	} else { // Not a valid user ID.
		echo '<h1>Page Error</h1>
		<p class="error">This page has been accessed in error.</p><p><br /><br /></p>';
	}
	
} // End of the main Submit conditional.

mysqli_close($dbc);
include ('includes/footer.php');
?>