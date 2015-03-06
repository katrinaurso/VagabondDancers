<?php # edit_profile.php
// This is the page to edit a users profile information.  Must of course be logged in to view, and also only the user logged in can see and edit this information.

// Include the configuration file, title, and header:
require ('includes/config.inc.php');
$page_title = 'Edit My Profile';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	require (MYSQL);
	
	$errors = array();
	
	// Check for a first name:
	if (empty($_POST['first_name'])) {
		$errors[] = 'You forgot to enter your first name.';
	} else {
		$fn = mysqli_real_escape_string($dbc, trim($_POST['first_name']));
	}
	
	// Check for a last name:
	if (empty($_POST['last_name'])) {
		$errors[] = 'You forgot to enter your last name.';
	} else {
		$ln = mysqli_real_escape_string($dbc, trim($_POST['last_name']));
	}
	
	// Check for an email address:
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter your email.';
	} else {
		$e = mysqli_real_escape_string($dbc, trim($_POST['email']));
	}
	
	if (empty($errors)) { // If everything's OK.
	
		//  Test for unique name:
		$q = "SELECT email FROM peoples WHERE email='$e' AND people_id != $pid";
		$r = @mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) == 0) {

			// Make the query:
			$q = "UPDATE peoples SET first_name='$fn', last_name='$ln', email='$e' WHERE people_id=$pid LIMIT 1";
			$r = @mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.

				// Print a message:
				echo '<p>Your information has been edited.</p>';	
				
			} else { // If it did not run OK.
				echo '<p class="error">Your information could not be edited due to a system error. We apologize for any inconvenience.</p>'; // Public message.
				echo '<p>' . mysqli_error($dbc) . '<br />Query: ' . $q . '</p>'; // Debugging message.
			}
				
		} else { // Already registered.
			echo '<p class="error">The email has already been registered.</p>';
		}
		
	} else { // Report the errors.

		echo '<p class="error">The following error(s) occurred:<br />';
		foreach ($errors as $msg) { // Print each error.
			echo " - $msg<br />\n";
		}
		echo '</p><p>Please try again.</p>';
	
	} // End of if (empty($errors)) IF.

} // End of submit conditional.

// Always show the form...
	
// Get the people_id:
$pid = ($_SESSION['people_id']);

// Retrieve the person's information:
$row = FALSE;

if ($pid) {

	require (MYSQL);

	$q = "SELECT first_name, last_name, email
	FROM peoples
	WHERE people_id='$pid'";

	$r = mysqli_query ($dbc, $q);

	if (mysqli_num_rows($r) !== -1) { // Valid user ID, show the form.

		// Get the persons's information:
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	
		// Show persons's current information:
		echo '<h1>My Information: </h1>';
	
		// Create the form:
		echo '<form action="edit_profile.php" method="post">
		<p><b>First Name:</b> <input type="text" name="first_name" size="20" maxlength="20" value="' . $row[0] . '" /></p>
		<p><b>Last Name:</b> <input type="text" name="last_name" size="30" maxlength="30" value="' . $row[1] . '" /></p>
		<p><b>Email:</b> <input type="text" name="email" size="30" maxlength="40" value="' . $row[2] . '" /></p>
		<p><input type="submit" name="submit" value="Submit" /></p>
		<input type="hidden" name="pid" value="' . $pid . '" />
		</form>';

	} else { // Not a valid user ID.
		echo '<p class="error">This page has been accessed in error.</p>';
	} // Close else statement
} // Close if ($pid)

mysqli_close($dbc);
include ('includes/footer.php');
?>