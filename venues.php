<?php # venues.php

require ('includes/config.inc.php');
$page_title = 'Venues';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

// Check if the form has been submitted:
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	// Get the people_id:
	$pid = ($_SESSION['people_id']);

} // End of submit conditional.

// Always show the form...

// Retrieve the event's information:
$row = FALSE;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {

require (MYSQL);

$vid = $_GET['id'];

$q = "SELECT location_name, address_one, city, state_name, postal_code, geocode, loc.location_id, master_location_id, event_name, evt.event_id
FROM locations AS loc
LEFT JOIN address_locations AS al ON loc.location_id = al.location_id
LEFT JOIN addresses AS adr ON al.address_id = adr.address_id
LEFT JOIN states AS st ON adr.state_id = st.state_id
LEFT JOIN event_locations AS el ON loc.location_id = el.location_id
LEFT JOIN events AS evt ON el.event_id = evt.event_id
WHERE loc.location_id=$vid";

$r = mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) !== -1) { // Valid user ID, show the form.

	// Get the venues's information:
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	
	echo '<h1>Venue: ' . $row[0] . '</h1>';
	
	// Show venues's current information:
	echo '<table><tr>';
	echo '<td align="right">Name:</td><td><b>' . $row[0] . '</b></td></tr>
	<tr><td align="right">Location:</td><td>';
	if (!empty($row[7])) {
		echo '' . $row[7] . '<br />';
	} else {
	echo '' . $row[1] . '<br />' . $row[2] . ', ' . $row[3] . ' ' . $row[4] . '</td></tr>';
	#include($row[5]);
	echo '</tr></table><br /><br />';
	}
	
	// Search for events that are currently held at this venue
	$q2 = "SELECT evt.event_id, event_name 
	FROM locations AS loc
	LEFT JOIN event_locations AS el ON loc.location_id = el.location_id
	LEFT JOIN events AS evt ON evt.event_id = el.event_id
	WHERE loc.location_id = '$vid'";
	$r2 = mysqli_query ($dbc, $q2);
	
	echo '<h1>Events currently held at this venue:</h1>';
	// Table header.
	echo '<table cellspacing="0" cellpadding="5">';
	// Fetch and print all the records.
	$bg = '#eeeeee'; // Set the background color.
	while ($events = mysqli_fetch_array ($r2, MYSQLI_ASSOC)) {
		$eid = $events['event_id'];
		$event = $events['event_name'];
		$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
		echo '<tr bgcolor="' . $bg . '">
			<td align="left"><a href="events.php?eid=' . $eid . '">' . $event . '</a></td>
		</tr>';
	}
	echo '</table></br />';
	
}
}
include ('includes/footer.php');
?>