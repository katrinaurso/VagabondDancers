<?php # register.php

// This page was last checked on 06-30-2014
// This is the registration page for the site.

require ('includes/config.inc.php');
$page_title = 'Register | Vagabond Dancers';
include ('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle the form.

	// Need the databse connection:
	require (MYSQL); 
	
	// Trim all the incoming data:
	$trimmed = array_map('trim', $_POST);  
	
	// Asume invalid values:
	$fn = $ln = $e = $p = FALSE;
	
	// Check for a first name:
	if (preg_match ('/^[A-Z \'.-]{2,20}$/i', $trimmed['first_name'])) {
		$fn = mysqli_real_escape_string ($dbc, $trimmed['first_name']);
	} else {
		echo '<p class="error">Please enter your first name!</p>';
	}
	
	// Check for a last name:
	if (preg_match ('/^[A-Z \'.-]{2,40}$/i', $trimmed['last_name'])) {
		$ln = mysqli_real_escape_string ($dbc, $trimmed['last_name']);
	} else {
		echo '<p class="error">Please enter your last name!</p>';
	}
	
	// Check for an email address:
	if (filter_var($trimmed['email'], FILTER_VALIDATE_EMAIL)) {
		$e = mysqli_real_escape_string ($dbc, $trimmed['email']);
	} else {
		echo '<p class="error">Please enter a valid email address!</p>';
	}
	
	// Check for a password and match against the confirmed password:
	if (preg_match ('/^\w{4,20}$/', $trimmed['password1']) ) {
		if ($trimmed['password1'] == $trimmed['password2']) {
			$p = mysqli_real_escape_string ($dbc, $trimmed['password1']);
		} else {
			echo '<p class="error">Your password did not match the confirmed password!</p>';
		}
	} else {
		echo '<p class="error">Please enter a valid password!</p>';
	}
	
	if ($fn && $ln && $e && $p) { // If everything is okay...
	
		// Make sure the email address is available:
		$q = "SELECT people_id FROM peoples WHERE email='$e'";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (mysqli_num_rows($r) == 0) { // Available.
		
			// Create the activation code:
			$a = md5(uniqid(rand(), true));
			
			// Add the user to the database:
			$q = "INSERT INTO peoples (email, password, first_name, last_name, activation, registration_date) VALUES ('$e', SHA1('$p'), '$fn', '$ln', '$a', NOW() )";
			$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
			
			if (mysqli_affected_rows($dbc) == 1) { // If it ran okay.
			
				// Send the email:
				require("includes/mail/class.phpmailer.php");
				$mail = new PHPMailer();
				$mail->IsSMTP();  // telling the class to use SMTP
				$mail->Host     = "mail.vagabonddancers.com"; // SMTP server
				$mail->From     = "admin@vagabonddancers.com";
				$mail->FromName = "Vagabond Dancers";
				$mail->AddAddress($e);
				$mail->Subject  = "Activate Your Vagabond Dancers Account";
				$mail->Body     = "Hello " . $fn . "! \n\n Thank you for registering to become a Vagabond Dancer. Please click the link below to activate your account:\n\n";
				$mail->Body    .= BASE_URL . 'activate.php?x=' . urlencode($e) . "&y=$a";
				$mail->WordWrap = 72;
				if(!$mail->Send()) {
					echo 'Message was not sent due to a system error, please try again later.';
					#echo 'Mailer error: ' . $mail->ErrorInfo;
					include ('includes/footer.php');
				} else {
					// Finish the page:
					echo '<h3>' . $fn . ', thank you for registering! </h3>
					<p>A confirmation email has been sent to ' . $e . '.</p>
					<p>Just click on the link in that email in order to activate your account. 
					<p>It should arrive momentarily, but please allow up to 30 minutes.</p>';
					include ('includes/footer.php'); // Include the HTML footer.
				}
				exit(); // Stop the page.
			
			} else { // If it did not run okay:	
				echo '<p class="error">You could not be registered due to a system error.  We apologize for any inconvenience.</p>';
			}
		
		} else { // The email address is not available:
			echo '<p class="error">That email address has already been registered.  If you have forgotten your password, use the link at the right to have your password sent to you.</p>';
		}
		
	} else { // If one of the data tests failed:
		echo '<p class="error">Please try again.</p>';
	}
	
	mysqli_close($dbc);
	
} // End of the main Submit conditional.
?>

<h1>Register</h1>
<p>So you are thinking about registering?  Well, we think that is a wonderful idea.  When you sign-in you will be able to do much more!  First you will be able to add any dances, practicas, lesssons, workshops, and events you would like!  Get the word out for your locals spots or places your have visited.  Plus we are in the process of adding even more features to make seaching for and promoting anything related to dance around the US (and hopefully world) easy and fun.</p><br /><br />
<form action="register.php" method="post">
	<fieldset>
    <p><small>Please fill out all fields to register.</small></p>
    <p><b>First Name:</b> <input type="text" name="first_name" size="20" maxlength="20" value="<?php if (isset($trimmed['first_name'])) echo $trimmed['first_name']; ?>" /></p>
    <p><b>Last Name:</b> <input type="text" name="last_name" size="20" maxlength="40" value="<?php if (isset($trimmed['last_name'])) echo $trimmed['last_name']; ?>" /></p>
    <p><b>Email Address:</b> <input type="text" name="email" size="30" maxlength="60" value="<?php if (isset($trimmed['email'])) echo $trimmed['email']; ?>" /></p>
    <p><b>Password:</b> <input type="password" name="password1" size="20" maxlength="20" value="<?php if (isset($trimmed['password1'])) echo $trimmed['password1']; ?>" /> <small>Use only letters, number, and the underscore.  Must be between 4 and 20 characters long.</small></p>
    <p><b>Confirm Password:</b> <input type="password" name="password2" size="20" maxlength="20" value="<?php if (isset($trimmed['password2'])) echo $trimmed['password2']; ?>" /></p>
    </fieldset>
<div align="center"><input type="submit" name="submit" value="Register" /></div>
</form>

<?php include ('includes/footer.php'); ?>