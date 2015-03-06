<?php # Admin - view_events.php
// This page is for a logged-in administrator to be able to view all of entries of the events table..

require ('includes/config.inc.php');
$page_title = 'View the Current Events';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}

// Page header:
echo '<h1>Dance Events</h1>';

require (MYSQL);

// Number of records to show per page:
$display = 15;

// Determine how many pages there are.
if (isset($_GET['np'])) { // Already been determined.
	$num_pages = $_GET['np'];
} else { // Need to determine.

	// Count the number of records
	$q = "SELECT COUNT(*) FROM events ORDER BY event_date_confirmed DESC";
	$r = mysqli_query ($dbc, $q);
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	$num_records = $row[0];
	
	// Calculate the number of pages.
	if ($num_records > $display) { // More than one page.
		$num_pages = ceil ($num_records/$display);
	} else {
		$num_pages = 1;
	}
	
} // End of np IF.

// Determine where in the database to start running results.
if (isset($_GET['s'])) {
	$start = $_GET['s'];
} else {
	$start = 0;
}

// Default column links.
$link1 = "{$_SERVER['PHP_SELF']}?sort=ena";
$link2 = "{$_SERVER['PHP_SELF']}?sort=la";
$link3 = "{$_SERVER['PHP_SELF']}?sort=sa";
$link4 = "{$_SERVER['PHP_SELF']}?sort=dra";

// Determine the sorting order.
if (isset($_GET['sort'])) {

	// Use existing sorting order.
	switch ($_GET['sort']) {
		case 'ena':
			$order_by = 'event_name ASC';
			$link1 = "{$_SERVER['PHP_SELF']}?sort=end";
			break;
		case 'end':
			$order_by = 'event_name DESC';
			$link1 = "{$_SERVER['PHP_SELF']}?sort=ena";
			break;
		case 'la':
			$order_by = 'st.state_id ASC';
			$link2 = "{$_SERVER['PHP_SELF']}?sort=ld";
			break;
		case 'ld':
			$order_by = 'st.state_id DESC';
			$link2 = "{$_SERVER['PHP_SELF']}?sort=la";
			break;
		case 'sa':
			$order_by = 'dance_code ASC';
			$link3 = "{$_SERVER['PHP_SELF']}?sort=sd";
			break;
		case 'sd':
			$order_by = 'dance_code DESC';
			$link3 = "{$_SERVER['PHP_SELF']}?sort=sa";
			break;
		case 'dra':
			$order_by = 'event_last_activity ASC';
			$link4 = "{$_SERVER['PHP_SELF']}?sort=drd";
			break;
		case 'drd':
			$order_by = 'event_last_activity DESC';
			$link4 = "{$_SERVER['PHP_SELF']}?sort=dra";
			break;
		default:
			$order_by = 'event_last_activity ASC';
			break;
	}
	
	// $sort will be appended to the pagination links.
	$sort = $_GET['sort'];
	
} else { // Use the default sorting order.
	$order_by = 'event_last_activity ASC';
	$sort = 'dra';
}


// Make the query:
$q = "SELECT evt.event_id, event_name, adr.city, st.state_abbreviation, GROUP_CONCAT(ds.dance_style_code SEPARATOR ', ') AS 'dance_code', 
DATE_FORMAT(event_last_activity, '%m/%d/%y') AS last_activity 
FROM events AS evt
INNER JOIN event_dance_styles AS eds ON evt.event_id = eds.event_id
INNER JOIN dance_styles AS ds ON eds.dance_style_id = ds.dance_style_id
INNER JOIN event_locations AS elo ON evt.event_id = elo.event_id 
INNER JOIN address_locations AS alo ON elo.location_id = alo.location_id
INNER JOIN addresses AS adr ON alo.address_id = adr.address_id 
INNER JOIN states AS st ON adr.state_id = st.state_id
GROUP BY evt.event_id
ORDER BY $order_by LIMIT $start, $display";

$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.

// Count the number of returned rows.
$num = mysqli_num_rows($r);

if ($num > 0) { // If it ran okay, display the records.

	//Print how many users there are.
	//echo "<p>There are currently $num_records registered events.</p>\n";
	
	 /* (Original)  // Table header.
	echo '<table align="center" cellspacing="3" cellpadding="3" width="75%">
	<tr><td align="left"><b>Name</b></td><td align="left"><b>Date Registered</b></td></tr>';
	
	// Fetch and print all the records.
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		echo '<tr><td align="left">' . $row['name'] . '</td><td align="left">' . $row['dr'] . '</td></tr>
		';
	}
	
	echo '</table>'; // Close the table. */
	
	
	// Table header.
echo '<table align="center" cellspacing="0" cellpadding="5">
<tr>
	<td align="left"><b><a href="' . $link1 . '">Event Name</a></b></td>
	<td align="left"><b><a href="' . $link2 . '">Location</a></b></td>
	<td align="center"><b><a href="' . $link3 . '">Dance Style</a></b></td>
	<td align="left"><b><a href="' . $link4 . '">Last Updated</a></b></td>
</tr>';
	
// Fetch and print all the records.
$bg = '#eeeeee'; // Set the background color.
while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
	$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
	echo '<tr bgcolor="' . $bg . '">
		<td align="left">' . $row['event_name'] . '</td>
		<td align="left">' . $row['city']. ', ' . $row['state_abbreviation'] . '</td>
		<td align="center"><small>' . $row['dance_code'] . '</small></td>
		<td align="left">' . $row['last_activity'] . '</td>
		<td align="left"><a href="edit_event.php?id=' . $row['event_id'] . '">Edit</a></td>
		<td align="left"><a href="delete_event.php?id=' . $row['event_id'] . '">Delete</a></td>
	</tr>';
}
echo '</table>';

	mysqli_free_result ($r); // Free up the resources.
	
} else { // If no records were returned.
	
	echo '<p class="error">There are currently no registered events.</[p>';

} // End of if ($r) IF.

mysqli_close($dbc); // Close the database connection.

// Make the links to other pages, if necessary.
if ($num_pages > 1) {

	echo '<br /><p>';
	
	// Determine what page the script is on.
	$current_page = ($start/$display) + 1;
	
	//If it is not on the first page, make a Previous button.
	if ($current_page != 1) {
		echo '<a href="view_events.php?s=' . ($start - $display) . '&np=' . $num_pages . '&sort=' . $sort . '">Previous</a> ';
	}
	
	// Make all the numbered pages.
	for ($i = 1; $i <= $num_pages; $i++) {
		if ($i != $current_page) {
			echo '<a href="view_events.php?s=' . (($display * ($i - 1))) . '&np=' . $num_pages . '&sort=' . $sort .'">' . $i . '</a> ';
		} else {
			echo $i . ' ';
		}
	}
	
	// If it's not the last page, make a Next button.
	if ($current_page != $num_pages) {
		echo '<a href="view_events.php?s=' . ($start + $display) . '&np=' . $num_pages . '&sort=' . $sort. '">Next</a>';
	}
	
	echo '</p>';
	
} // End of links section.

include ('includes/footer.php');
?>