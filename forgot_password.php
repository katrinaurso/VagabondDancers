<?php # forgot_password.php

// This page was last checked on 07-02-2014
// This page allows a person to reset their password, if forgotten.

require ('includes/config.inc.php');
$page_title = 'Forgot Password | Vagabond Dancers';
include ('includes/header.php');
ini_set("display_errors", "off");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require (MYSQL);
	
	// Assume nothing:
	$uid = FALSE;
	
	// Validate the email address:
	if (!empty($_POST['email'])) {
		
		$e = ($_POST['email']);
	
		// Check for the existance of that email address:
		$q = 'SELECT people_id FROM peoples WHERE email="'. mysqli_real_escape_string($dbc, $_POST['email']) . '"';
		$r = mysqli_query ($dbc, $q) or trigger_error("There was an error with teh database, please try again later.");
		
		// Retrieve the user ID:
		if (mysqli_num_rows($r) == 1) {
			list($uid) = mysqli_fetch_array($r, MYSQLI_NUM);
		} else { // No database match made.
			echo '<p class="error">The submitted email address is not currently registered.</p>';
		}
		
	} else { // No email:
		echo '<p class="error">You forgot to enter your email address!</p>';
	} // End of empty($_POST['email']) IF.
	
	if ($uid) { // If everything is okay:
	
		// Create a new, random password:
		$p = substr ( md5(uniqid(rand(), true)), 3, 10);
		
		// Update the database:
		$q = "UPDATE peoples SET password=SHA1('$p') WHERE people_id=$uid LIMIT 1";
		$r = mysqli_query ($dbc, $q) or trigger_error("There was an error with the database, please try again later.");
		
		if (mysqli_affected_rows($dbc) == 1) { // If it ran okay:
		
			// Send the email:
			require("includes/mail/class.phpmailer.php");
			$mail = new PHPMailer();
			$mail->IsSMTP();  // telling the class to use SMTP
			$mail->Host     = "mail.vagabonddancers.com"; // SMTP server
			$mail->From     = "admin@vagabonddancers.com";
			$mail->FromName = "Vagabond Dancers";
			$mail->AddAddress($e);
			$mail->Subject  = "Password Has Been Reset";
			$mail->Body     = "Your password to log into VagabondDancers.com has been temporarily changed to: " . $p . " .  \n\n 
			Please log in using this temporary new password and this email address.  \n\n 
			Once logged in, you may change your password to something more familiar:\n\n";
			$mail->Body    .= BASE_URL . 'login.php';
			$mail->WordWrap = 72;
			if(!$mail->Send()) {
				echo 'Message was not sent due to a system error, please try again later.';
				#echo 'Mailer error: ' . $mail->ErrorInfo;
				include ('includes/footer.php');
			} else {
				// Finish the page:
				echo '<h1>Your password has been changed</h1>
				<p>An email has been sent to ' . $e . ' with a new password.</p>
				<p>Once you receive it, just log in and you can change it to something else by clicking on the "Change Password" link.
				<p>It should arrive momentarily, but please allow up to 30 minutes.</p>';
				mysqli_close($dbc);
				include ('includes/footer.php'); // Include the HTML footer.
			}
			
			exit(); // Stop the page.
			
		} else { // If it did not run okay:
			echo '<p class="error">Your password could not be changed due to a system error.  We apologize for any inconvenience.</p>';
		}
		
	} else { // Failed the validation test:
		echo '<p class="error">Please try again.</p>';
	}
	
	mysqli_close($dbc);
	
} // End of the main Submit conditional.
?>

<h1>Reset Your Password</h1>
<p>Already registered, but forgot your password?  No problem, just enter your email address below and your password will be reset, the new password will be emailed to you.</p><br />
<form action="forgot_password.php" method="post">
	<fieldset>
    <p><b>Email Address:</b> <input type="text" name="email" size="20" maxlength="60" value="<?php if (isset($_POST['email'])); ?>" /></p>
    </fieldset>
    <div align="center"><input type="submit" name="submit" value="Reset My Password" /></div>
</form>

<?php include ('includes/footer.php'); ?>	