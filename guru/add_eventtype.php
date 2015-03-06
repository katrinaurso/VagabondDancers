<?php # Admin - add_eventtype.php
// This script will allow the signed in admin to create an entry to the event_type table.
// This script will also track who added it, and when it was added.

require ('includes/config.inc.php');
$page_title = 'Guru | Add Event Type';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {

	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form.

	require (MYSQL);

	// Trim all the incmoing data:
	$trimmed = array_map('trim', $_POST);
	
	// Asume invalid values:
	$et = $code = FALSE;
	
	// Check for a frequency name/description:
	if (preg_match ('/^[A-Z\d \'-]{3,20}$/i', $trimmed['event_type'])) { // Need to make a validater for this specifically
		$et = mysqli_real_escape_string ($dbc, $trimmed['event_type']);
	} else {
		echo '<p class="error">Please enter the frequency!</p>';
	}
	
	// Check for a style code:
	if (preg_match ('/^[A-Z0-9]{4}$/i', $trimmed['code'])) {
		$code = mysqli_real_escape_string ($dbc, $trimmed['code']);
	} else {
		echo '<p class="error">Please enter a valid frequency code!</p>';
	}
	
	// Select the master dance style if appicable
	
		
	if ($et && $code) { // If everything is okay...
		
		// Make sure the description name is available:
		$q = "SELECT event_type_description FROM event_types WHERE event_type_description='$et'";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (mysqli_num_rows($r) == 0) { // Available.
			
			// Set the queries to run.
			$aid = ($_SESSION['admin_id']);
			$q = "INSERT INTO event_types (event_type_description, event_type_code, admin_id, date_created) VALUE ('$et', '$code', '$aid', NOW() )";
			$r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
					
			if (mysqli_affected_rows($dbc) == 1) { // If it ran okay.
				
				// Finish the page:
				// I would really like to add the users name in there so that I can tell it is working for right now :).
				// Would like to have the page loop back to show the form so that users can immediatly add another if they would like.  The form can not be sticky from the previously submitted data!
				echo '<h3>Thank you for adding this event type! If you are ready to head back to finish confirming the event, just close the window and the event type should now be able to be selected.  If not please refresh the page.<br /><br /><div align="center"><a href="add_eventtype.php" title="Add Another">Add Another Event Type</a></div></h3>';
				include ('includes/footer.php'); // Include the HTML footer.
				exit(); // Stop the page.
			
			} else { // If it did not run okay.	
				echo '<p class="error">The event type could not be added due to a system error.  We apologize for any inconvenience.</p>';
			}
		
		} else { // The description is not available.
			echo '<p class="error">That event type has already been created.  Please use the search to check what event types are currently listed and try again.</p>';
		}
		
	} else { // If one of the data tests failed.
		echo '<p class="error">Please try again.</p>';
	}
	
	mysqli_close($dbc);
	
} // End of the main Submit conditional.
?>

<h1>Add an Event Type</h1>

<p>This page is an additional window that opened when the link was clicked.  When you are ready to return to the page you were just on, simply close this window.  Don't forget to click submit for the information you have entered though :).</p><br />
<form enctype="multipart/form-data" action="add_eventtype.php" method="post">
	<fieldset>
    <p><b>Event Type:</b> <input type="text" name="event_type" size="20" maxlength="20" value="<?php if (isset($trimmed['event_type'])) echo $trimmed['event_type']; ?>" /> <small>(Example: Weekly)</small></p>
    <p><b>Event Type Code:</b> <input type="text" name="code" size="4" maxlength="4" value="<?php if (isset($trimmed['code'])) echo $trimmed['code']; ?>" /> <small>Please enter a four digit code to identify this frequency (Example: WKLY)</small></p>
    </fieldset><br />
    <div align="center"><input type="submit" name="submit" value="Add to Event Types!" /></div><br />
</form>

<p><b>Currently Listed Event Types:</b><p><br />

<?php // Need the databse connection:
	require (MYSQL);
	
	// Default query for this page:
	$q = "SELECT event_type_description, event_type_code FROM event_types ORDER BY event_type_description ASC";

	// Create the table head:
	echo '<table border="0" width="90%" cellspacing="3" cellpadding="3" align="center">
		<tr>
			<td align="left" width="30%"><b>Description</b></td>
			<td align="left" width="30%"><b>Event Type Code</b></td>
		</tr>';
	
	// Display all the prints, linked to URLs
	$r = mysqli_query ($dbc, $q);
	while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {

		// Display each record:
		echo "\t<tr>
			<td align=\"left\">{$row['event_type_description']}</td>
			<td align=\"left\">{$row['event_type_code']}</td>
		</tr>\n";
	
	} // End of while loop.
	mysqli_close($dbc);
echo '</table>'; ?>

<?php include ('includes/footer.php'); ?>