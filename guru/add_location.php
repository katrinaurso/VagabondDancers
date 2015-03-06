<?php # Admin - add_location.php

// This script will allow the signed in admin to create an entry to the locations table.
// This script will also track who added it, and when it was added.

require ('includes/config.inc.php');
$page_title = 'Add Location';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {

	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form.

	// Need the databse connection:
	require (MYSQL);
	
	// Trim all the incmoing data:
	$trimmed = array_map('trim', $_POST);
	
	// Asume invalid values:
	$n = $master = $a = $c = $st = $z = $ct = FALSE;
	
	// Check for a location name:
	if (preg_match ('/^[A-Z\d \'.-]{2,100}$/i', $trimmed['name'])) { // Need to make a validater for this specifically
		$n = mysqli_real_escape_string ($dbc, $trimmed['name']);
	} else {
		echo '<p class="error">Please enter the location/venue\'s name!</p>';
	}
	
	// Check for a master location:
	if (isset($_POST['master']) && filter_var($_POST['master'], FILTER_VALIDATE_INT, array('master' => 1)) ) {
		$master = $_POST['master'];
	} else {
	
		// Check for an address:
		if (preg_match ('/^[A-Z0-9 \'.-]{6,40}$/i', $trimmed['address'])) {
			$a = mysqli_real_escape_string ($dbc, $trimmed['address']);
		} else {
			echo '<p class="error">Please enter a valid address!</p>';
		}
			
		// Check for a city:
		if (preg_match ('/^[A-Z \'.-]{2,40}$/i', $trimmed['city'])) {
			$c = mysqli_real_escape_string ($dbc, $trimmed['city']);
		} else {
			echo '<p class="error">Please enter a valid city!</p>';
		}
		
		// Check for a state selection
		if (isset($_POST['state']) && filter_var($_POST['state'], FILTER_VALIDATE_INT, array('state' => 1)) ) {
			$st = $_POST['state'];
		} else {
			echo '<p class="error">Please select a state from the drop-down menu!</p>';
		}
		
		// Check for a zip code
		if (preg_match ('/^[0-9]{5}$/i', $trimmed['zip'])) {
			$z = mysqli_real_escape_string ($dbc, $trimmed['zip']);
		} else {
			echo '<p class="error">Please enter a valid zip-code!</p>';
		}
		
	} // Close look for master if
	
	//*************************** This section works! ****************************
	
	// Get the admin_id:
	$aid = ($_SESSION['admin_id']);
	
	if ($n && $master) { // If adding location to a master location:
	
		require_once (MYSQL);
		
		$q = "INSERT INTO locations (location_name, master_location_id) VALUE ('$n', '$master')";
		$r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		if (mysqli_affected_rows($dbc) == 1) { // If it ran ok.
			echo '<h3>Thank you for adding this location! If you have any more that you would like to add, please feel free to fill out the form again!  Otherwise simply close this window and refresh your previous window.<br /><br /><div align="center"><a href="add_location.php" title="Add Another">Add Another Location</a></div></h3>';
			include ('includes/footer.php'); // Include the HTML footer.
			exit(); // Stop the page.
			
		} else { // If it did not run okay.	
			echo '<p class="error">The event could not be added due to a system error.  We apologize for any inconvenience.</p>';
		}
		//mysqli_close($dbc);
		
	} else if ($n && $a && $c && $st && $z) { // If admin is entering in a new address:
			
		// Add the event into the database:			
		mysqli_autocommit($dbc, FALSE);  // Turn off auto-commit.

		// Set the queries to run.
		$query1 = "INSERT INTO addresses (address_one, city, state_id, postal_code, date_created, created_admin_id, last_admin_id) VALUES ('$a', '$c', '$st', '$z', NOW(), $aid, $aid)";
		$query2 = "INSERT INTO locations (location_name, date_created, created_admin_id, last_admin_id) VALUE ('$n', NOW(), $aid, $aid)";

		// Run $query1 and $query2.
		$r = mysqli_query($dbc, $query1) or trigger_error("Query: $query1\n<br />MySQL Error: " . mysqli_error($dbc));
		if ($r !== TRUE) {
			mysqli_rollback($dbc);
		} else {
			 $aid = mysqli_insert_id($dbc);
		}

		$r = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br />MySQL Error: " . mysqli_error($dbc));
		if ($r !== TRUE) {
			mysqli_rollback($dbc);  // if error, roll back transaction
		} else {
			$lid = mysqli_insert_id($dbc);
		}
			
		// Define and run $query3 to tie them together into the event_location table
		$query3 = "INSERT INTO address_locations (address_id, location_id) VALUE ('$aid', '$lid')";
			
		if ($dbc) {
			$r = mysqli_query($dbc, $query3) or trigger_error("Query: $query3\n<br />MySQL Error: " . mysqli_error($dbc));
			if ($r !== TRUE) {
				mysqli_rollback($dbc);
			}
		}
			
		// Assuming no errors, commit transaction.
		mysqli_commit($dbc);
					
		if (mysqli_affected_rows($dbc) !== -1) { // If it ran okay.
			echo '<h3>Thank you for adding this location! If you have any more that you would like to add, please feel free to fill out the form again!<br /><br /><div align="center"><a href="add_location.php" title="Add Another">Add Another Location</a></div></h3>';
			include ('includes/footer.php'); // Include the HTML footer.
			exit(); // Stop the page.
			
		} else { // If it did not run okay.	
			echo '<p class="error">The location could not be added due to a system error.  We apologize for any inconvenience.</p>';
		}
	
	} else { // If one of the data tests failed.
		echo '<p class="error">Please try again.</p>';
	}
	mysqli_close($dbc);
	
} // End of the main Submit conditional.
?>

<h1>Add a Venue/Location</h1>

<p>This page is an additional window that opened when the link was clicked.  When you are ready to return to the page you were just on, simply close this window.  Don't forget to click submit for the information you have entered though :).</p><br />
<form enctype="multipart/form-data" action="add_location.php" method="post">
	<fieldset>
    <p><b>Venue/Location Name:</b> <input type="text" name="name" size="30" maxlength="100" value="<?php if (isset($trimmed['name'])) echo $trimmed['name']; ?>" /> <small>(Example: Aruba Hotel)</small></p>
    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Is this venue inside another location? If yes, no need to fill out the address section below, just select: </p>
    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Location:</b>
    <select name="master"><option>Select One</option>
    <?php // Retrieve all the artists and add to the pull-down menu.
	require (MYSQL);
	$q = "SELECT location_id, location_name FROM locations ORDER BY location_name ASC";
	$r = mysqli_query($dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
			echo "<option value=\"$row[0]\"";
			// Check for stickiness:
			if (isset($_POST['master']) && ($_POST['master'] == $row[0]) ) echo 'selected="selected"';
			echo ">$row[1]</option>\n";
		}
	} else {
		echo '<option>Please add a new location first.</option>';
	}
	//mysqli_close($dbc); // Close the database connection.
	?>
    </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="add_location.php" title="Add Location to Database" target="_blank">Add a location</a></p>
    <p><b>Address:</b> <input type="text" name="address" size="20" maxlength="60" value="<?php if (isset($trimmed['address'])) echo $trimmed['address']; ?>" /></p>
    <p><b>City:</b> <input type="text" name="city" size="20" maxlength="60" value="<?php if (isset($trimmed['city'])) echo $trimmed['city']; ?>" /></p>
    
    <p><b>State:</b> <select name="state"><option>-- Select State --</option>
    <?php // Retrieve all the dance types and add it to the drop down menu.
	$q = "SELECT state_id, state_name FROM states ORDER BY state_name ASC";
	$r = mysqli_query($dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
			echo "<option value=\"$row[0]\"";
			// Check for stickiness:
		if (isset($_POST['state']) && ($_POST['state'] == $row[0]) ) echo 'selected="selected"';
			echo ">$row[1]</option>\n";
		}
	} else {
		echo '<option>Please add a new state first.</option>';
	}
	mysqli_close($dbc); // Close the database connection.
	?>
    </select></p>
    
    <p><b>Zip code:</b> <input type="text" name="zip" size="5" maxlength="10" value="<?php if (isset($trimmed['zip'])) echo $trimmed['zip']; ?>" /></p>
    <!--<p><b>Country:</b> <input type="text" name="country" size="15" maxlength="30" value="<?php if (isset($trimmed['country'])) echo $trimmed['country']; ?>" /></p>-->
   
    </fieldset><br />
    <div align="center"><input type="submit" name="submit" value="Add It To The Map!" /></div>
   
</form>

<?php include ('includes/footer.php'); ?>