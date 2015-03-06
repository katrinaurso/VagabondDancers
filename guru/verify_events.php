<?php # Admin - verify_events.php
// This page is for a logged-in administrator to be able to view all unviewed entries into the unverified_events table...

require ('includes/config.inc.php');
$page_title = 'View and Verify the Unverified Events';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}

// Page header:
echo '<h1>Unverified Events</h1>';

require_once (MYSQL);

// Number of records to show per page:
$display = 20;

// Determine how many pages there are.
if (isset($_GET['np'])) { // Already been determined.
	$num_pages = $_GET['np'];
} else { // Need to determine.

	// Count the number of records
	$q = "SELECT COUNT(*) FROM unverified_events WHERE confirmed IS NULL";
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
			$order_by = 'state_id ASC';
			$link2 = "{$_SERVER['PHP_SELF']}?sort=ld";
			break;
		case 'ld':
			$order_by = 'state_id DESC';
			$link2 = "{$_SERVER['PHP_SELF']}?sort=la";
			break;
		case 'sa':
			$order_by = 'dance_style_id ASC';
			$link3 = "{$_SERVER['PHP_SELF']}?sort=sd";
			break;
		case 'sd':
			$order_by = 'dance_style_id DESC';
			$link3 = "{$_SERVER['PHP_SELF']}?sort=sa";
			break;
		case 'dra':
			$order_by = 'date_created ASC';
			$link4 = "{$_SERVER['PHP_SELF']}?sort=drd";
			break;
		case 'drd':
			$order_by = 'date_created DESC';
			$link4 = "{$_SERVER['PHP_SELF']}?sort=dra";
			break;
		default:
			$order_by = 'date_created ASC';
			break;
	}
	
	// $sort will be appended to the pagination links.
	$sort = $_GET['sort'];
	
} else { // Use the default sorting order.
	$order_by = 'date_created ASC';
	$sort = 'drd';
}


// Make the query:
$q = "SELECT uve.unverified_id, event_name, city, state_abbreviation, 
GROUP_CONCAT(ds.dance_style_code SEPARATOR ', ') AS 'dance_code', 
DATE_FORMAT(uve.date_created, '%m/%d/%y') AS dc 
FROM unverified_events AS uve 
INNER JOIN states AS st ON uve.state_id=st.state_id 
LEFT JOIN unverified_dance_styles AS uvds ON uve.unverified_id = uvds.unverified_id
LEFT JOIN dance_styles AS ds ON uvds.dance_style_id = ds.dance_style_id 
WHERE confirmed IS NULL 
group by uve.unverified_id ORDER BY uve.$order_by LIMIT $start, $display";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.

// Count the number of returned rows.
$num = mysqli_num_rows($r);

if ($num > 0) { // If it ran okay, display the records.

	//Print how many users there are.
	echo "<p>There are currently <b>$num</b> unverified events:</p><br />";
	
	// Table header.
echo '<table align="center" cellspacing="0" cellpadding="5">
<tr>
	<td align="left"><b><a href="' . $link1 . '">Event Name</a></b></td>
	<Td align="left"><b><a href="' . $link2 . '">Location</a></b></td>
	<td align="center"><b><a href="' . $link3 . '">Style</a></b></td>
	<td align="left"><b><a href="' . $link4 . '">Date Created</a></b></td>
</tr>';

$bg = '#eeeeee'; // Set the background color.
while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
	$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
	echo '<tr bgcolor="' . $bg . '">
		<td align="left">' . $row['event_name'] . '</td>
		<td align="left">' . $row['city'] . ', ' . $row['state_abbreviation'] . '</td>
		<td align="center"><small>' . $row['dance_code'] . '</small></td>
		<td align="left">' . $row['dc'] . '</td>
		<td align="left"><a href="verifying_event.php?uid=' . $row['unverified_id'] . '" target="_blank">Verify</a></td>
	</tr>';
}
echo '</table>';

	mysqli_free_result ($r); // Free up the resources.
	
} else { // If no records were returned.
	
	echo '<p class="error">There are currently no unverified events. Good job!</[p>';

} // End of if ($r) IF.

mysqli_close($dbc); // Close the database connection.

// Make the links to other pages, if necessary.
if ($num_pages > 1) {

	echo '<br /><p>';
	
	// Determine what page the script is on.
	$current_page = ($start/$display) + 1;
	
	//If it is not on the first page, make a Previous button.
	if ($current_page != 1) {
		echo '<a href="verify_events.php?s=' . ($start - $display) . '&np=' . $num_pages . '&sort=' . $sort . '">Previous</a> ';
	}
	
	// Make all the numbered pages.
	for ($i = 1; $i <= $num_pages; $i++) {
		if ($i != $current_page) {
			echo '<a href="verify_events.php?s=' . (($display * ($i - 1))) . '&np=' . $num_pages . '&sort=' . $sort .'">' . $i . '</a> ';
		} else {
			echo $i . ' ';
		}
	}
	
	// If it's not the last page, make a Next button.
	if ($current_page != $num_pages) {
	echo '<a href="verify_events.php?s=' . ($start + $display) . '&np=' . $num_pages . '&sort=' . $sort. '">Next</a>';
	}
	
	echo '</p>';
	
} // End of links section.

include ('includes/footer.php');
?>