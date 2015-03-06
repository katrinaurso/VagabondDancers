<?php # add_event.php
// This script will allow the signed-in user to create an entry to the unverified events.  It will also track who added it, when it was added, and send it to the administrative side for verification.

require ('includes/config.inc.php');
$page_title = 'Add an Event | Vagabond Dancers';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['first_name'])) {

	$url = BASE_URL . 'index.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form:

	// Need the databse connection:
	require (MYSQL);
	
	// Trim all the incmoing data:
	//$trimmed = array_map('trim', $_POST);
	
	// Asume invalid values:
	$en = $sty = $et = $f = $d = $t = $vn = $sta = $c = FALSE;
	
	// Check for an event name:
	if (preg_match ('/^[A-Z\d \'\!.,-]{2,40}$/i', trim($_POST['event_name'])) ) {
		$en = mysqli_real_escape_string ($dbc, trim($_POST['event_name']));
	} else {
		echo '<p class="error">Please enter a valid event name!</p>';
	}
	
	// Check for a dance style:
	if ($_POST['dance_style']) {
		$sty = $_POST['dance_style'];
	} else {
		echo '<p class="error">Please select dance style(s)!</p>';
	}
	
	// Check for selected event types:
	if ($_POST['event_type']) {
		$et = $_POST['event_type'];
	} else {
		echo '<p class="error">Please select event type(s)!</p>';
	}
	
	// Check for frequency:
	if (isset($_POST['frequency']) && filter_var($_POST['frequency'], FILTER_VALIDATE_INT, array('min_range' => 1)) ) {
		$f = $_POST['frequency'];
	} else {
		echo '<p class="error">Please select a frequency from the pull-down menu!</p>';
	}
	
	// Check for a date: ***************** Need to do further validations!
	if (preg_match ('/^[A-Z\d \/-]{2,20}$/i', trim($_POST['date']))) {
		$d = $_POST['date'];
	} else {
		echo '<p class="error">Please enter a date.</p>';
	}
	
	// Check for a time:
	if  (preg_match ('/^[A-Z\d \:-]{1,15}$/i', trim($_POST['time']))) {
		$t = $_POST['time'];
	} else {
		echo '<p class="error">Please enter a time.</p>';
	}
	
	// Check for a venue name:
	if (preg_match ('/^[A-Z\d \'!.-]{2,30}$/i', trim($_POST['venue_name']))) {
		$vn = mysqli_real_escape_string ($dbc, trim($_POST['venue_name']));
	} else {
		echo '<p class="error">Please enter a valid location name!</p>';
	}
	
	// ******* Check for a state selection: ************* Need to validate that it is selected!
	if (isset($_POST['state']) ) {
		$sta = $_POST['state'];
	} else {
		echo '<p class="error">Please select a state from the pull-down menu!</p>';
	}
	
	// Check for a city:
	if (preg_match ('/^[A-Z \'.-]{2,20}$/i', trim($_POST['city']))) {
		$c = mysqli_real_escape_string ($dbc, trim($_POST['city']));
	} else {
		echo '<p class="error">Please enter a valid city!</p>';
	}
	
	// I need to write a conditional statement just for this one!!!!  This was just a quicky to make sure that it is working
	// Check for comments: (these are optional)
	if (preg_match ('/^[A-Z\d \'\!.,-]{2,100}$/i', trim($_POST['comments']))) {
		$com = mysqli_real_escape_string ($dbc, trim($_POST['comments']));
	} else {
		echo '<p class="error">Please enter a valid comment, do not use awful characters!</p>';
	}
	
	#Need to make photo optional.  So am commenting it out for now.
	// Check for an image
	#if (is_uploaded_file ($_FILES['image']['tmp_name'])) {

		// Create a temporary file name:
		#$temp = '../uploads/unverified/' . md5($_FILES['image']['name']);
	
		// Move the file over:
		#if (move_uploaded_file($_FILES['image']['tmp_name'], $temp)) {
	
			// Set the $i variable to the image's name:
			#$i = $_FILES['image']['name'];
	
		#} else { // Couldn't move the file over.
			#$errors[] = 'The file could not be moved.';
			#$temp = $_FILES['image']['tmp_name'];
		#}

	#} else { // No uploaded file.
		//$errors[] = 'No file was uploaded.';
		#$temp = NULL;
	#}
	
	// Get the people_id
	$pid = ($_SESSION['people_id']);
	
	if ($en && $sty && $et && $f && $d && $t && $vn && $sta && $c) { // If everything is okay...	
			
		// Add the event into the database:			
		mysqli_autocommit($dbc, FALSE);  // Turn off auto-commit.
		
		// Set and run $query1:
		$query1 = "INSERT INTO unverified_events (event_name, event_type_id, frequency_id, date, time, venue_name, state_id, city, comments, people_id, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
		$stmt1 = mysqli_prepare($dbc, $query1);
		mysqli_stmt_bind_param($stmt1, 'siisssissi', $en, $et, $f, $d, $t, $vn, $sta, $c, $com, $pid);
		mysqli_stmt_execute($stmt1);
		if (mysqli_stmt_affected_rows($stmt1) !== 1 ) {
			mysqli_rollback($dbc);
		} else {
			 $uid = mysqli_insert_id($dbc);
		}
		
		#Need to make photo optional, so am commenting it out for testing purposes
		// Set and run $query2
		#$query2 = "INSERT INTO unverified_photos (photo_name, unverified_id) VALUES (?, ?)";	
		#$stmt2 = mysqli_prepare($dbc, $query2);
		#mysqli_stmt_bind_param($stmt2, 'si', $i, $uid);
		#mysqli_stmt_execute($stmt2);
		#if (mysqli_stmt_affected_rows($stmt2) !== 1) {
		#	mysqli_rollback($dbc);
		#} else {
			// Rename the image:
		#	$id = mysqli_stmt_insert_id($stmt2); // Get the print ID.
		#	rename ($temp, '../uploads/unverified/' . $id);
			
			// Clear $_POST:
		#	$_POST = array();
		#}
		// Delete the uploaded file if it still exists:
		#if (isset($temp) && file_exists ($temp) && is_file($temp) ) {
		#	unlink ($temp);
		#}
		#mysqli_stmt_close($stmt2);
		
		// Define and run $query3 to tie all together:
		if ($sty !== -1) {
			for ($i=0; $i<sizeof($sty);$i++) {
				$query3 = "INSERT INTO unverified_dance_styles (unverified_id, dance_style_id) VALUES ('$uid', '" . $sty[$i] . "')";
				$r3 = mysqli_query($dbc, $query3) or trigger_error("Query: $query2\n<br />MySQL Error: " . mysqli_error($dbc));
			}
			if ($r3 !== TRUE) {
				mysqli_rollback($dbc);
			}
		}
				
		// Assuming no errors, commit transaction.
		mysqli_commit($dbc);
					
		if (mysqli_affected_rows($dbc) !== -1) { // If it ran okay.
				
			// Finish the page:
			echo '<h3>Thank you for adding this event! If you have any more that you would like to add, please feel free to fill out the form again!  It may take a day or two before the entry is officially added, as we verfiy each and every entry to make sure that we have the correct information, and that it truely does exist.  If verified, you will get credit for creating this event then.<br /><br /><div align="center"><a href="add_event.php" title="Add Another">Add Another Event</a></div></h3>';
			include ('includes/footer.php');
			exit(); // Stop the page.
			
		} else { // If it did not run okay.	
			echo '<p class="error">The event could not be added due to a system error.  We apologize for any inconvenience.</p>';
		}
		
	} else { // If one of the data tests failed.
		echo '<p class="error">Please try again.</p>';
	}
	
	mysqli_close($dbc);
	
} // End of the main Submit conditional.
?>

<h1>Add an Event</h1>
<p>Thank you for taking the time to add an event that you have been to for other dancers to be able to find out about!  Please fill out as much of the information as you know, it is ok if you do not know everything but there are a few key ones you must know in order for this event to be created.  Also, don't forget that you need to rate and review as well (you will be the very first person to have a say about this event).  If it is something to happen in the future, not an ongoing regular event, then a review is not needed, since no one has been able to attend it yet.</p><br />
<form enctype="multipart/form-data" action="add_event.php" method="post">
	<fieldset>
    <p><small>The fields below are required  The next section is optional.</small></p>
    <p><b>Event Name:</b> <input type="text" name="event_name" size="30" maxlength="100" value="<?php if (isset($trimmed['event_name'])) echo $trimmed['event_name']; ?>" /></p>
    <p><b>Venue:</b> <input type="text" name="venue_name" size="20" maxlength="60" value="<?php if (isset($trimmed['venue_name'])) echo $trimmed['venue_name']; ?>" /> <small>(Example: Aruba Hotel)</small></p>
    <p><b>City:</b> <input type="text" name="city" size="30" maxlength="30" value="<?php if (isset($trimmed['city'])) echo $trimmed['city']; ?>" />, <select name="state"><option>-- Select State --</option>
	<?php // Retrieve all the dance types and add it to the drop down menu.
	require (MYSQL);
	$q = "SELECT state_id, state_name FROM states ORDER BY state_id ASC";
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
	?>
    </select></p>
    
    <p><b>Frequency:</b> <select name="frequency"><option>-- Select Frequency --</option>
    <?php // Retrieve all the dance types and add it to the drop down menu.
	$q = "SELECT frequency_id, frequency_description, frequency_code FROM frequencies ORDER BY frequency_id ASC";
	$r = mysqli_query($dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
			echo "<option value=\"$row[0]\"";
			// Check for stickiness:
		if (isset($_POST['frequency']) && ($_POST['frequency'] == $row[0]) ) echo 'selected="selected"';
			echo ">$row[1]</option>\n";
		}
	} else {
		echo '<option>Please add a new frequency first.</option>';
	}
	?>
    </select> <small>How often does this event occur?</small></p>
    
    <p><b><label for="date">Date:</b></label><input id="date" name="date" type="date" value="<?php echo date('Y-m-d'); ?>" /><small>If event is repeating, please enter in only the next event date.</small></p>
    
     <p><b>Time:</b> <input type="text" name="time" size="8" maxlength="20" value="<?php if (isset($trimmed['time'])) echo $trimmed['time']; ?>" /> <small>What time is the event? Example 8pm-1am</small></p>
    
    <?php
	echo '<p><b>Dance Style(s):</b></p>';
    $q = "SELECT dance_style_id, dance_style_description, master_dance_style_id FROM dance_styles";
 	$r = mysqli_query($dbc, $q);
	while($row = mysqli_fetch_array($r,  MYSQLI_NUM)){	
		//if ($row[2] !== NULL) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><input value=\"".$row[0]."\" name=\"dance_style[]\" type=\"checkbox\">".$row[1]."<br /></small>";
		//}
	}
	?><br />
    
    <p><b>Event Type:</b> <select name="event_type"><option>-- Select Type --</option>
    <?php // Retrieve all the dance types and add it to the drop down menu.
	$q = "SELECT event_type_id, event_type_description, event_type_code FROM event_types ORDER BY event_type_id ASC";
	$r = mysqli_query($dbc, $q);
	if (mysqli_num_rows($r) > 0) {
		while ($row = mysqli_fetch_array ($r, MYSQLI_NUM)) {
			echo "<option value=\"$row[0]\"";
			// Check for stickiness:
		if (isset($_POST['event_type']) && ($_POST['event_type'] == $row[0]) ) echo 'selected="selected"';
			echo ">$row[1]</option>\n";
		}
	} else {
		echo '<option>Please add a new event type first.</option>';
	}
	?>
    </select></p>
    
    <!--Need to make photo optional, so am commenting it out for the moment -->
    <!--<input type="hidden" name="MAX_FILE_SIZE" value="524288" />
    <p><b>Photo:</b> <input type="file" name="image"/></p> -->
    
    <p><b>Comments:</b> <textarea name="comments" rows="4" value="<?php if (isset($trimmed['comments'])) echo $trimmed['comments']; ?>"></textarea></p>

    </fieldset><br />
   
   <div align="center"><input type="submit" name="submit" value="Add It!" /></div>
   
</form>

<?php include ('includes/footer.php'); ?>