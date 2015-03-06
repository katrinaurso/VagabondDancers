<?php # Admin - view_users.php
// This page is for a logged-in administrator to be able to view registered users of the site.

require ('includes/config.inc.php');
$page_title = 'View the Current Users';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}

// Page header:
echo '<h1>Registered Users</h1>';

require_once (MYSQL);

// Number of records to show per page:
$display = 10;

// Determine how many pages there are.
if (isset($_GET['np'])) { // Already been determined.
	$num_pages = $_GET['np'];
} else { // Need to determine.

	// Count the number of records
	$q = "SELECT COUNT(*) FROM peoples ORDER BY registration_date ASC";
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
$link1 = "{$_SERVER['PHP_SELF']}?sort=lna";
$link2 = "{$_SERVER['PHP_SELF']}?sort=fna";
$link3 = "{$_SERVER['PHP_SELF']}?sort=drd";

// Determine the sorting order.
if (isset($_GET['sort'])) {

	// Use existing sorting order.
	switch ($_GET['sort']) {
		case 'lna':
			$order_by = 'last_name ASC';
			$link1 = "{$_SERVER['PHP_SELF']}?sort=lnd";
			break;
		case 'lnd':
			$order_by = 'last_name DESC';
			$link1 = "{$_SERVER['PHP_SELF']}?sort=lna";
			break;
		case 'fna':
			$order_by = 'first_name ASC';
			$link2 = "{$_SERVER['PHP_SELF']}?sort=fnd";
			break;
		case 'fnd':
			$order_by = 'first_name DESC';
			$link2 = "{$_SERVER['PHP_SELF']}?sort=fna";
			break;
		case 'dra':
			$order_by = 'registration_date ASC';
			$link3 = "{$_SERVER['PHP_SELF']}?sort=drd";
			break;
		case 'drd':
			$order_by = 'registration_date DESC';
			$link3 = "{$_SERVER['PHP_SELF']}?sort=dra";
			break;
		default:
			$order_by = 'registration_date DESC';
			break;
	}
	
	// $sort will be appended to the pagination links.
	$sort = $_GET['sort'];
	
} else { // Use the default sorting order.
	$order_by = 'registration_date DESC';
	$sort = 'dra';
}


// Make the query:
$q = "SELECT last_name, first_name, people_id, DATE_FORMAT(registration_date, '%M %d, %Y') AS dr FROM peoples ORDER BY $order_by LIMIT $start, $display";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.

// Count the number of returned rows.
$num = mysqli_num_rows($r);

if ($num > 0) { // If it ran okay, display the records.

	if ($num != 1) {
		
		//Print how many users there are.
		echo "<p>There are currently $num registered people.</p>\n";
		
	} else {
		
		echo "<p>There is 1 registered person.</p>\n";
	}
	
	// Table header.
echo '<table align="center" cellspacing="0" cellpadding="5">
<tr>
	<td align="left"><b><a href="' . $link1 . '">Last Name</a></b></td>
	<td align="left"><b><a href="' . $link2 . '">First Name</a></b></td>
	<td align="left"><b><a href="' . $link3 . '">Date Registered</a></b></td>
</tr>';
	
// Fetch and print all the records.
$bg = '#eeeeee'; // Set the background color.
while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
	$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
	echo '<tr bgcolor="' . $bg . '">
		<td align="left">' . $row['last_name'] . '</td>
		<td align="left">' . $row['first_name'] . '</td>
		<td align="left">' . $row['dr'] . '</td>
		<td align="left"><a href="edit_user.php?id=' . $row['people_id'] . '">Edit</a></td>
		<td align="left"><a href="delete_user.php?id=' . $row['people_id'] . '">Delete</a></td>
	</tr>';
}
echo '</table>';

	mysqli_free_result ($r); // Free up the resources.
	
} else { // If no records were returned.
	
	echo '<p class="error">There are currently no registered users.</[p>';

} // End of if ($r) IF.

mysqli_close($dbc); // Close the database connection.

// Make the links to other pages, if necessary.
if ($num_pages > 1) {

	echo '<br /><p>';
	
	// Determine what page the script is on.
	$current_page = ($start/$display) + 1;
	
	//If it is not on the first page, make a Previous button.
	if ($current_page != 1) {
		echo '<a href="view_users.php?s=' . ($start - $display) . '&np=' . $num_pages . '&sort=' . $sort . '">Previous</a> ';
	}
	
	// Make all the numbered pages.
	for ($i = 1; $i <= $num_pages; $i++) {
		if ($i != $current_page) {
			echo '<a href="view_users.php?s=' . (($display * ($i - 1))) . '&np=' . $num_pages . '&sort=' . $sort .'">' . $i . '</a> ';
		} else {
			echo $i . ' ';
		}
	}
	
	// If it's not the last page, make a Next button.
	if ($current_page != $num_pages) {
		echo '<a href="view_users.php?s=' . ($start + $display) . '&np=' . $num_pages . '&sort=' . $sort. '">Next</a>';
	}
	
	echo '</p>';
	
} // End of links section.

include ('includes/footer.php');
?>