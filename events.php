<?php # events.php

// This page shows the information of the event
// The information should change based on the type of event that it is
// Users need to be able to flag and event and add comments as to what is wrong.  Is it no longer in existance, is the time wrong, has the location changed?

require ('includes/config.inc.php');
$page_title = 'Event';
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

// Always show the form:


// Retrieve the event's information:
$row = FALSE;

if (isset($_GET['eid']) && filter_var($_GET['eid'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {

require (MYSQL);

$eid = $_GET['eid'];

$q = "SELECT event_name, location_name, address_one, city, state_name, postal_code, sq1.event_dance_styles, sq2.event_type_description, loc.location_id, ed.start_date, evt.start_time, ed.end_date, evt.end_time, frequency_description
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
LEFT JOIN event_dates AS ed ON evt.event_id = ed.event_id
LEFT JOIN frequencies AS fre ON evt.frequency_id = fre.frequency_id
WHERE evt.event_id ='$eid' AND ed.start_date >= CURDATE() LIMIT 1";

$r = mysqli_query ($dbc, $q);

	if (mysqli_num_rows($r) !== -1) { // Valid user ID, show the form.

		// Get the event's information:
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	
		echo '<h1>Events: ' . $row[0] . '</h1>';
		$loc = $row[8];	
		
		// Show the event's current information:
		echo '<b>' . $row[0] . '</b><br /><br />
		<a href="venues.php?id=' . $loc . '">' . $row[1] . '<br />
		' . $row[2] . '<br />
		' . $row[3] . ', ' . $row[4] . ' ' . $row[5] . '</a><br /><br />
		<p>' . $row[6] . '</p>
		<p>' . $row[7] . ' | ' . $row[13] . '</p><br />
		<p>Next Event:  ' . date("l, F d",strtotime($row['9'])) . '</p>
		<p>' . date("g:ia", strtotime($row[10])) . ' - ' . date("g:ia", strtotime($row[12])) . '</p><br /><br />';
		#echo '</td></tr>
		#<tr><td align="right">Cost:</td><td>(Cost to be added in)</td></tr>
		#<tr><td align="right">Lesson:</td><td>(Include if there is a lesson attached to a dance)</td></tr>
		#<tr><td align="right">Age Resitriction:</td><td>(Include what the age limitiation are if there is some)</td></tr>
		#</table>';
	}
}
include ('includes/footer.php');
?>