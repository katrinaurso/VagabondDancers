<?php # results_state.php
// This is the test results for the search_state.php page

// Include the configuration file, title, and header:
require ('includes/config.inc.php');
$page_title = 'Places to Dance';
include ('includes/header.php');

// Welcome the user (by name if they are logged in):
if (!isset($_GET['state'])) {
	echo 'Please go back and search again, it appears you accessed this page in error.';
} else {
	$st = $_GET['state'];
 
	echo '<h1>Places to dance in ' . $st .'';
	if (isset($_SESSION['first_name'])) {
		echo ", {$_SESSION['first_name']}";
	}
	echo '!</h1>';
	
	
	echo '<table>
	<tr>';
    require (MYSQL);
    $q = "SELECT DISTINCT city 
	      FROM addresses AS adr 
	      LEFT JOIN states AS st ON adr.state_id = st.state_id
		  WHERE state_name='$st'";
    $r = mysqli_query($dbc, $q);
     
	echo '<form enctype="multipart/form-data" action="results_city.php" method="post">';                  
    if (mysqli_num_rows($r) > 0) {
        echo '<td><fieldset><p><b>Avilable Cities:</b></p>';
        while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
        	$city = $row[0];
            // $trimcity = str_replace(' ', '', $city);
			echo '<a href="results_city.php?city=' . $city . '"> ' . $city . '</a><br />';
        } echo'</form></fieldset></td>';
    }

	// Query the databse on the events in that state and show all of the current events
	// Am having issues getting the dance_style_code to be able to show in the query too.
	$q = "SELECT event_name, city, evt.event_id
		FROM events AS evt
		INNER JOIN event_locations AS el ON evt.event_id = el.event_id
		INNER JOIN address_locations AS adl ON el.location_id = adl.location_id
		INNER JOIN addresses AS adr ON adl.address_id = adr.address_id
		INNER JOIN states AS st ON adr.state_id = st.state_id
		WHERE state_name = '$st'
		ORDER BY evt.event_name ASC;";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc)); // Run the query.
	// Count the number of returned rows.
	$num = mysqli_num_rows($r);

	if ($num > 0) { // If it ran okay, display the records.

	//Print how many users there are.
	echo "<p>There are currently $num events in $st.</p>\n";
	
	// Table header.
	echo '<td><table align="center" cellspacing="0" cellpadding="5">';
	
	// Fetch and print all the records.
	$bg = '#eeeeee'; // Set the background color.
	while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
		$eid = $row['event_id'];
		$event = $row['event_name'];
		$bg = ($bg=='#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the background color.
		echo '<tr bgcolor="' . $bg . '">
			<td align="left"><a href="events.php?eid=' . $eid . '">' . $event . '</a></td>
			<td align="center">' . $row['city'] . '</td>
		</tr>';
	}
	echo '</table></td></tr><table>';

	mysqli_free_result ($r); // Free up the resources.
	
} else { // If no records were returned.
	
	echo '<p class="error">There are currently no registered events.</[p>';

} // End of if ($r) IF.

mysqli_close($dbc); // Close the database connection.
	
}

include ('includes/footer.php');
?>