<?php # search_results.php
// This script will allow the signed in user to view the search results

require ('includes/config.inc.php');
$page_title = 'Search Bar Results | Vagabond Dancers';
include ('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form:

	// Need the databse connection:
	require (MYSQL);
}

if (!isset($_POST['search'])) {
	header ("Location:index.php");
}
$srch = $_POST['search'];
$q = "SELECT event_name, event_id FROM events WHERE event_name LIKE '%" . $srch . "%'";
$r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
?>

<h1>Search Results</h1>
<?php 
if (mysqli_num_rows($r) > 0) {
	 while ($row = mysqli_fetch_array($r)) {
		 $name = $row[0];
		 $eid = $row[1];
		 echo '<p><a href="events.php?eid=' . $eid . '">' . $name . '</a></p>';
	 }
mysqli_free_result ($r); // Free up the resources.

} else {
	echo "No results found";
	
mysqli_close($dbc); // Close the database connection.
}

include ('includes/footer.php');
?>