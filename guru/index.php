
text/x-generic index.php 
PHP script text
<?php # Admin - index.php
// This is the main page for the admin site.

// Include the configuration file:
require ('includes/config.inc.php');
$page_title = 'Vagabond Guru: Welcome';
include ('includes/header.php');
include ('includes/session_login.php');

// Welcome the user (by name if they are logged in):
echo '<h1>Welcome';
if (isset($_SESSION['admin_first_name'])) {
	echo ", {$_SESSION['admin_first_name']}";
}
echo '!</h1>';
?>

<p>Welcome back to the back end of the site!  You hold a certain power here at Vagabond and we trust that you will do good with those powers.  Now lets get to work, here on this page is a quick link to the most important things to be done.</p><br/>

<?php
require_once (MYSQL);

	// Count the number of records
	$q = "SELECT COUNT(*) FROM unverified_events WHERE confirmed IS NULL ORDER BY date_created DESC";
	$r = mysqli_query ($dbc, $q);
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$num_records = $row[0];
	
	
	// Determine where in the database to start running results.
	if (isset($_GET['s'])) {
		$start = $_GET['s'];
	} else {
		$start = 0;
	}
echo '<ul>';
// Make the query for unverified_events:
$q = "SELECT unverified_id FROM unverified_events WHERE confirmed IS NULL";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
// Count the number of returned rows.
$num = mysqli_num_rows($r);
if ($num > 1) { // If it ran okay, display the records.
	//Print how many unverified events there are.
	echo '<li><a href="verify_events.php" title="Unverified Events">There are currently <b>' . $num_records . '</b> unverified events.</a></li><br />';
} else if ($num == 1) { // If only one record was returned.
	echo '<li><a href="verify_events.php" title="Unverified Events">There is <b>1</b> unverified event.</a></li><br />';
} else {// If no records were returned.
	echo '<li>There are currently no unverified events. Great job!</li><br />';
} // End of if ($r) IF.

// Make the query to find number of events that have expired and need to be reconfirmed:
$q = "SELECT event_id, event_last_activity FROM events WHERE event_last_activity <= DATE_SUB(CURDATE(), INTERVAL 10 DAY)";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
// Count the number of returned rows.
$num2 = mysqli_num_rows($r);
if ($num2 > 1) { // If it ran okay, display the records.
	//Print how many expired events there are.
	echo '<li>There are currently <b>' . $num2 . '</b> expired events. <a href="expired_events.php" title="Expired Events">Let\'s start re-verifying these guys!</a></li><br />';
} else if ($num2 == 1) { // If there was only one result.
	echo '<li>There is <b>1</b> <a href="expired_events.php" title="Expired Events">expired event</a>.</li><br />';
} else { // If no records were returned.
	echo '<li>There are currently no expiring events. Great job!</li><br />';
} // End of if ($r) IF.

// Make the query:
$q = "SELECT event_id FROM events";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
// Count the number of returned rows.
$num = mysqli_num_rows($r);
if ($num > 0) { // If it ran okay, dispay the records.
	// Print how many events there are.
	echo '<li>There are now <b>' . $num . '</b> events in the databse!  Whoo hoo :D</li><br />';
} else { // If no records were returned.
	echo '<p class="error">There are currently no events listed.  We better add some!</p><br />';
} // End of display events if.

// Make the query:
$q = "SELECT DISTINCT state_id FROM addresses WHERE state_id!=0";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
// Count the number of returned rows.
$num = mysqli_num_rows($r);
if ($num > 0) { // If it ran okay, display the records.
	//Print how many unverified events there are.
	echo '<li>There are currently <b>' . $num . '</b>/50 states represented, so far.</li><br />';
} else { // If no records were returned.
	echo '<p class="error">There are currently zero states with events :(.</p><br />';
} // End of if ($r) IF.

echo '</ul>';

mysqli_close($dbc); // Close the database connection.

?>

<?php include ('includes/footer.php'); ?>