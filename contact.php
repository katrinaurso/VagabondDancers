<?php # contact.php
// This script scrubs dangerous strings from the submitted input.

// Include the configuration file:
require ('includes/config.inc.php');

// Set the page title and include the HTML header:
$page_title = 'Contact Us | Vagabond Dancers';
include ('includes/header.php');

// Check for form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	/* The function takes one argument: a string.
	* The function returns a clean version of the string.
	* The clean version may be either an empty string or
	* just the removal of all newline characters.
	*/
	function spam_scrubber($value) {
	
		// List of very bad values:
		$very_bad = array('to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:');
		
		// If any of the very bad strings are in
		// the submitted value, return as empty string:
		foreach ($very_bad as $v) {
			if (stripos($value, $v) !== false) return '';
		}
		
		// Replace any newline characters with spaces:
		$value = str_replace(array( "\r", "\n", "%0d"), ' ', $value);
		
		// Return the value:
		return trim($value);
		
	} // End of spam_scrubber() function.
	
	// Clean the form data:
	$scrubbed = array_map('spam_scrubber', $_POST);

	// Minimal form validation:
	if (!empty($scrubbed['name']) && !empty($scrubbed['email']) && !empty($scrubbed['comments']) ) {
	
		// Create the body:
		$body = "Name: {$scrubbed['name']}\n\nComments: {$scrubbed['comments']}";
		
		// Make it no longer than 70 characters long:
		$body = wordwrap($body, 70);
		
		// Send the email:
		mail('admin@vagabonddancers.com', 'Contact Form Submission', $body, "From: {$scrubbed['email']}");
		
		// Print a message:
		echo '<h3>Thank you for contacting us.  We will reply as soon as we can.</h3>
		<p>In the meantime, why not browse around and find a new place to dance?  Or rate a few of your most recent adventures?';
		
		// Clear $scrubbed (so that the form's not sticky):
		$scrubbed = array();
		include ('includes/footer.php'); // Include the HTML footer.
		exit(); // Stop the page.
		
	} else {
		echo '<p class="error">Please fill out the form completely.</p>';
	}
	
} // End of main isset() IF.

// Create the HTML form:
?>
<p>Please fill out this form to contact us.</p><br />
<form action="contact.php" method="post">
	<fieldset>
	<p><b>Name:</b> <input type="text" name="name" size="30" maxlength="60" value="<?php if (isset($scrubbed['name'])) echo $scrubbed['name']; ?>" /></p>
    <p><b>Email Address:</b> <input type="text" name="email" size="30" maxlength="80" value="<?php if (isset($scrubbed['email'])) echo $scrubbed['email']; ?>" /></p>
    <p><b>Comments:</b> <textarea name="comments" rows="5" cols="30"><?php if (isset($scrubbed['comments'])) echo $scrubbed['comments']; ?></textarea></p>
    </fieldset>
    <div align="center"><input type="submit" name="submit" value="Send!" /></div>
</form>

<?php include ('includes/footer.php'); ?>