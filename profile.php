<?php # profile.php
// This is the page for a users profile information to be seen and edited.  Must of course be logged in to view, and also only the user logged in can see and edit this information.

// Include the configuration file, title, and header:
require ('includes/config.inc.php');
$page_title = 'My Profile | Vagabond Dancers';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

// Check if the form has been submitted:
} else {
	
	// Get the people_id:
	$pid = ($_SESSION['people_id']);

} // End of submit conditional.

// Always show the form...

// Retrieve the person's information:
$row = FALSE;

//if (isset($_GET['pid']) && filter_var($_GET['pid'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {
if ($pid) {

require (MYSQL);

$q = "SELECT first_name, last_name, email, DATE_FORMAT( registration_date, '%m/%d/%Y' ), DATE_FORMAT( last_active, '%m/%d/%Y' ), event_name, event_id
FROM peoples AS ppl
LEFT JOIN events AS evt ON ppl.people_id = evt.people_id
WHERE ppl.people_id='$pid'";

$r = mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) !== -1) { // Valid user ID, show the form.

	// Get the persons's information:
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$pass = $row[3];
				
	echo '<h1>My Information: </h1>';
	
	// Show persons's current information:
	echo '<table>
		<tr>';
	echo '<td align="right">Name:</td><td><b>' . $row[0] . '&nbsp;' . $row[1] .'</b></td></tr>
	<tr><td align="right">Email:</td><td>' . $row[2] . '<br /></td>
	<tr><td align="right">Registered:</td><td>' . $row[3] . '<br /></td>
	<tr><td align="right">Last Active:</td><td>' . $row[4] . '<br /></td>
	</tr></table><br />';
	echo '<a href="edit_profile.php">I want to change my info</a>';
	echo '<br /><br />';
	
	// Search and display events created by the person
	$q2 = "SELECT event_name, event_id
	FROM events WHERE people_id='$pid'";
	$r2 = mysqli_query ($dbc, $q2);
	echo '<h1>My Confirmed Events I Submitted:</h1>';
	// Table header.
	echo '<table cellspacing="0" cellpadding="5">';
	// Fetch and print all the records.
	$bg = '#eeeeee'; // Set the background color.
	while ($row2 = mysqli_fetch_array ($r2, MYSQLI_ASSOC)) {
		$eid = $row2['event_id'];
		$event = $row2['event_name'];
		$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
		echo '<tr bgcolor="' . $bg . '">
			<td align="left"><a href="events.php?eid=' . $eid . '">' . $event . '</a></td>
		</tr>';
	}
	echo '</table></br />';
	
	echo '<h1>Events I Have Made Approved Edits To:</h1>
	(Coming Soon)<br />';
}
}
include ('includes/footer.php');
?>