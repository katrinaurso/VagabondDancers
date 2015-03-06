<?php # Admin - add_dancestyle.php
// This script will allow the signed in admin to create an entry to the dance_styles table.  It also tracks who added it, and when it was added.

require ('includes/config.inc.php');
$page_title = 'Add Dance Style';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {

	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.

} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form.

	require (MYSQL);

	// Trim all the incmoing data:
	$trimmed = array_map('trim', $_POST);
	
	// Asume invalid values:
	$style = $code = FALSE;
	
	// Check for a dance style name/description:
	if (preg_match ('/^[A-Z\d \'-]{3,20}$/i', $trimmed['style'])) { // Need to make a validater for this specifically
		$style = mysqli_real_escape_string ($dbc, $trimmed['style']);
	} else {
		echo '<p class="error">Please enter the dance style!</p>';
	}
	
	// Check for a style code:
	if (preg_match ('/^[A-Z0-9]{4}$/i', $trimmed['code'])) {
		$code = mysqli_real_escape_string ($dbc, $trimmed['code']);
	} else {
		echo '<p class="error">Please enter a valid style code!</p>';
	}
	
	// Select the master dance style if appicable
	
		
	if ($style && $code) { // If everything is okay...
		
		// Make sure the description name is available:
		$q = "SELECT dance_style_description FROM dance_styles WHERE dance_style_description='$style'";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (mysqli_num_rows($r) == 0) { // Available.
			
			// Set the queries to run.
			$aid = ($_SESSION['admin_id']);
			$q = "INSERT INTO dance_styles (dance_style_description, dance_style_code, admin_id, date_created) VALUE ('$style', '$code', '$aid', NOW() )";
			$r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
					
			if (mysqli_affected_rows($dbc) == 1) { // If it ran okay.
				
				// Finish the page:
				echo '<h3>Thank you for adding this dance style! If you are ready to head back to finish confimring the event, just close the window and the style should now be able to be selected.  If not please refresh the page.<br /><br /><div align="center"><a href="add_dancestyle.php" title="Add Another">Add Another Dance Style</a></div></h3>';
				include ('includes/footer.php'); // Include the HTML footer.
				exit(); // Stop the page.
			
			} else { // If it did not run okay.	
				echo '<p class="error">The style could not be added due to a system error.  We apologize for any inconvenience.</p>';
			}
		
		} else { // The description is not available.
			echo '<p class="error">That style has already been created.  Please use the search to check what styles are currently listed and try again.</p>';
		}
		
	} else { // If one of the data tests failed.
		echo '<p class="error">Please try again.</p>';
	}
	
	mysqli_close($dbc);
	
} // End of the main Submit conditional.
?>

<h1>Add a Dance Style</h1>

<p>This page is an additional window that opened when the link was clicked.  When you are ready to return to the page you were just on, simply close this window.  Don't forget to click submit for the information you have entered though :).</p><br />
<form enctype="multipart/form-data" action="add_dancestyle.php" method="post">
	<fieldset>
    <p><b>Dance Style:</b> <input type="text" name="style" size="20" maxlength="20" value="<?php if (isset($trimmed['style'])) echo $trimmed['style']; ?>" /> <small>(Example: East Coast Swing)</small></p>
    <p><b>Style Code:</b> <input type="text" name="code" size="4" maxlength="4" value="<?php if (isset($trimmed['code'])) echo $trimmed['code']; ?>" /> <small>Please enter a three digit code to identify this dance style (Example: ECS)</small></p>
    </fieldset><br />
    <div align="center"><input type="submit" name="submit" value="Add to Dance Styles!" /></div><br />
</form>

<p><b>Currently Listed Dance Styles:</b><p><br />

<?php // Need the databse connection:
	require (MYSQL);
	
	// Default query for this page:
	$q = "SELECT dance_style_description, dance_style_code FROM dance_styles ORDER BY dance_style_description ASC";

	// Create the table head:
	echo '<table border="0" width="90%" cellspacing="3" cellpadding="3" align="center">
		<tr>
			<td align="left" width="30%"><b>Description</b></td>
			<td align="left" width="30%"><b>Style Code</b></td>
		</tr>';
	
	// Display all the prints, linked to URLs
	$r = mysqli_query ($dbc, $q);
	while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {

		// Display each record:
		echo "\t<tr>
			<td align=\"left\">{$row['dance_style_description']}</td>
			<td align=\"left\">{$row['dance_style_code']}</td>
		</tr>\n";
	
	} // End of while loop.
	mysqli_close($dbc);
echo '</table>'; ?>

<?php include ('includes/footer.php'); ?>