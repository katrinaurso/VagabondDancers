<?php # search.php
// This is the search page for the site!

// Include the configuration file, title, and header:
require ('includes/config.inc.php');
$page_title = 'Search for Places to Dance | Vagabond Dancers';
include ('includes/header.php');

// Welcome the user (by name if they are logged in):
echo '<h1>Search';
if (isset($_SESSION['first_name'])) {
	echo ", {$_SESSION['first_name']}";
}
echo '!</h1>';
?>

<!-- State search -->
<form enctype="multipart/form-data" action="results_state.php" method="post">
<?php // Retrieve all the states that have registered events and add them as a link:
echo '<p><b>Avaliable States:</b></p>';
require (MYSQL);
$q = "SELECT DISTINCT state_name, st.state_id
      FROM states as st
      INNER JOIN addresses AS adr ON st.state_id=adr.state_id
      INNER JOIN address_locations AS al ON adr.address_id = al.address_id
      INNER JOIN locations AS loc ON al.location_id = loc.Location_ID
      INNER JOIN event_locations AS el ON loc.location_id = el.Location_id
      INNER JOIN events AS evt ON el.event_id = evt.event_id
	  INNER JOIN event_dates AS ed ON evt.event_id = ed.event_id
	  WHERE ed.end_date >= CURDATE()
      ORDER BY state_name ";
$r = mysqli_query($dbc, $q);
if (mysqli_num_rows($r) > 0) {
	while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
		$state = $row[0];
		$sid = $row[1];
		$trimstate = trim($state);
		echo '<a href="results_state.php?state=' . $state . '"> ' . $state . '</a><br />';
	}	     
} // Close of the states IF.
?>
</form>

<?php include ('includes/footer.php'); ?>