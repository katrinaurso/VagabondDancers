<?php # Admin - delete_user.php

// This page deletes a user.
// This page is accessed through view_users.php and can only be accessed by logged in users.

require ('includes/config.inc.php');
$page_title = 'Admin: Delete a User';
include ('includes/header.php');

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

	if ($_POST['sure'] == 'Yes') { // Delete the record.

		$id = ($_POST['id']); // Get the user id from the submission.

		// Make query.
		$q = "DELETE FROM peoples WHERE people_id=$id LIMIT 1";
		$r = mysqli_query ($dbc, $q); // Run the query.
		if (mysqli_affected_rows($dbc) == 1) { // If it ran okay.
		
			// Frint a message.
			echo '<h1>Delete a User</h1>
			<p>The user has been deleted.</p><p><br /><br /></p>';
			
		} else { // If the query did not run okay.
			echo '<h1>System Error</h1>
			<p class="error">The user could not be deleted due to a system error.</p>'; // Public message.
			echo '<p>' . mysqli_error() . '<br /><br />Query: ' . $query . '</p>'; // Debugging message.
		}
		
	} else { // Wasn't sure about deleting the user.
		echo '<h1>Delete a User</h1>
		<p>The user has NOT been deleted.</p><p><br /><br /></p>';
	}
		
} else { // Show the form.

	$id = $_GET['id'];
	
	// Retrieve the user's information.
	$q = "SELECT CONCAT(last_name, ', ', first_name) FROM peoples WHERE people_id=$id";
	$r = @mysqli_query ($dbc, $q); 
	
	if (mysqli_num_rows($r) == 1) { // Validate user ID, show the form.
	
		// Get the user's information.
		$row = mysqli_fetch_array ($r, MYSQLI_NUM);
		
		// Display the record being deleted:
		echo "<h1>Delete a User</h1>
		<h3>Name: $row[0]</h3>
		Are you sure you want to delete this user?";
		
		// Create the form:
		echo '<form action="delete_user.php" method="post">
		<input type="radio" name="sure" value="Yes" /> Yes 
		<input type="radio" name="sure" value="No" checked="checked" /> No
		<input type="submit" name="submit" value="Submit" />
		<input type="hidden" name="id" value="' . $id . '" />
		</form>';
		
	} else { // Not a valid user ID.
		echo '<h1>Page Error</h1>
		<p class="error">This page has been accessed in error.</p><p><br /><br /></p>';
	}
	
} // End of the main Submit conditional.

mysqli_close($dbc);
include ('includes/footer.php');
?>