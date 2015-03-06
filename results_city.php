<?php # results_city.php
// This is the test results for the results_state.php page

// Include the configuration file, title, and header:
require ('includes/config.inc.php');
$page_title = 'Places to Dance';
include ('includes/header.php');

// Welcome the user (by name if they are logged in):
if (!isset($_GET['city'])) {
	echo 'Please go back and search again, it appears you accessed this page in error.';
} else {
	$city = $_GET['city'];
 
	echo '<h1>Places to dance in ' . $city . '!</h1>';
	
    require (MYSQL);

	// Query the databse on the events in that state and show all of the current events
	// Am having issues getting the dance_style_code to be able to show in the query too.
	$q = "SELECT event_name, evt.event_id
		FROM events AS evt
		INNER JOIN event_locations AS el ON evt.event_id = el.event_id
		INNER JOIN address_locations AS adl ON el.location_id = adl.location_id
		INNER JOIN addresses AS adr ON adl.address_id = adr.address_id
		INNER JOIN states AS st ON adr.state_id = st.state_id
		WHERE city = '$city'
		ORDER BY evt.event_name ASC;";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
	// Count the number of returned rows.
	$num = mysqli_num_rows($r);

	if ($num > 0) { // If it ran okay, display the records.

	//Print how many users there are.
	echo "<p>There are currently $num event(s) in $city.</p>\n<br />";
	
	// Table header.
	echo '<table cellspacing="0" cellpadding="5">';
	
	// Fetch and print all the records.
	$bg = '#eeeeee'; // Set the background color.
	while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
		$eid = $row['event_id'];
		$event = $row['event_name'];
		$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
		echo '<tr bgcolor="' . $bg . '">
			<td align="left"><a href="events.php?eid=' . $eid . '">' . $event . '</a></td>
		</tr>';
	}
	echo '</table></br />';
	
	echo "<b>Sunday</b><br />";
	echo "<b>Monday</b><br />";
	echo "<b>Tuesday</b><br />";
	echo "<b>Wednesday</b><br />";
	echo "<b>Thursday</b><br />";
	echo "<b>Friday</b><br />";
	echo "<b>Saturday</b><br />";

	mysqli_free_result ($r); // Free up the resources.
	
} else { // If no records were returned.
	
	echo '<p class="error">There are currently no registered events.</[p>';

} // End of if ($r) IF.

mysqli_close($dbc); // Close the database connection.
	
}

include ('includes/footer.php');
?>