<?php # Admin - edit_user.php
// This page is for editing a user record.
// This page is accessed through view_users.php.

;require ('includes/config.inc.php');
$page_title = 'Edit a User';
include ('includes/header.php');

echo '<h1>Edit a User</h1>';

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}
require (MYSQL); 

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$errors = array();
	
	// Check for a first name:
	if (empty($_POST['first_name'])) {
		$errors[] = 'You forgot to enter a first name.';
	} else {
		$fn = mysqli_real_escape_string($dbc, trim($_POST['first_name']));
	}
	
	// Check for a last name:
	if (empty($_POST['last_name'])) {
		$errors[] = 'You forgot to enter a last name.';
	} else {
		$ln = mysqli_real_escape_string($dbc, trim($_POST['last_name']));
	}

	// Check for an email address:
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter an email address.';
	} else {
		$e = mysqli_real_escape_string($dbc, trim($_POST['email']));
	}
	
	$id = ($_POST['id']); // Get the user id from the submission.
	
	if (empty($errors)) { // If everything's OK.
	
		//  Test for unique email address:
		$q = "SELECT people_id FROM peoples WHERE email='$e' AND people_id != $id";
		$r = @mysqli_query($dbc, $q);
		if (mysqli_num_rows($r) == 0) {

			// Make the query:
			$q = "UPDATE peoples SET first_name='$fn', last_name='$ln', email='$e' WHERE people_id=$id LIMIT 1";
			$r = @mysqli_query ($dbc, $q);
			if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.

				// Print a message:
				echo '<p>The user has been edited.</p>';	
				
			} else { // If it did not run OK.
				echo '<p class="error">The user could not be edited due to a system error. We apologize for any inconvenience.</p>'; // Public message.
				echo '<p>' . mysqli_error($dbc) . '<br />Query: ' . $q . '</p>'; // Debugging message.
			}
				
		} else { // Already registered.
			echo '<p class="error">The email address has already been registered.</p>';
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

// Retrieve the user's information:
$id = $_GET['id'];
$q = "SELECT first_name, last_name, email FROM peoples WHERE people_id=$id";		
$r = @mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) == 1) { // Valid user ID, show the form.

	// Get the user's information:
	$row = mysqli_fetch_array ($r, MYSQLI_NUM);
	
	// Create the form:
	echo '<form action="edit_user.php" method="post">
	<p>First Name: <input type="text" name="first_name" size="15" maxlength="25" value="' . $row[0] . '" /></p>
	<p>Last Name: <input type="text" name="last_name" size="15" maxlength="30" value="' . $row[1] . '" /></p>
	<p>Email Address: <input type="text" name="email" size="20" maxlength="40" value="' . $row[2] . '"  /> </p>
	<p><input type="submit" name="submit" value="Submit" /></p>
	<input type="hidden" name="id" value="' . $id . '" />
	</form>';

} else { // Not a valid user ID.
	echo '<p class="error">This page has been accessed in error.</p>';
}

mysqli_close($dbc);
include ('includes/footer.php');
?>