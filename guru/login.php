<?php # Admin - login.php

// This is the login page for the administrators.

require ('includes/config.inc.php');
$page_title = 'Vagabond Guru: Login';
include ('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require (MYSQL);
	
	// Trim all the incmoing data:
	$trimmed = array_map('trim', $_POST);
	
	// Validate the email address:
	if (!empty($_POST['email'])) {
		$e = mysqli_real_escape_string($dbc, $_POST['email']);
	} else {
		$e = FALSE;
		echo '<p class="error">You forgot to enter your email address!</p>';
	}
	
	// Validate the password:
	if (!empty($_POST['password'])) {
		$p = mysqli_real_escape_string($dbc, $_POST['password']);
	} else {
		$p = FALSE;
		echo '<p class="error">You forgot to enter your password!</p>';
	}
	
	if ($e && $p) { // If everything is okay.
		
		 // Query the database:
		$q = "SELECT admin_id, admin_first_name, admin_level FROM admin WHERE (admin_email='$e' AND admin_password=SHA1('$p'))";
		// $q = "UPDATE last_login VALUES NOW()";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (@mysqli_num_rows($r) == 1) { // A match was made.
		
			// Register the values.
			$_SESSION = mysqli_fetch_array($r, MYSQLI_ASSOC);
			mysqli_free_result($r);
			mysqli_close($dbc);
			
			// Redirect the user:
			$url = BASE_URL . 'index.php'; // Define the URL.
			ob_end_clean(); // Delete the buffer.
			header("Location: $url");
			exit(); // Quit the script.
			
		} else { // No match was made.
			echo '<p class="error">Either the email address and password entered do not match those on file or you have not yet activated your account.</p>';
		}
		
	} else { // If everything wasn't okay.
		echo '<p class="error">Please try again.</p>';
	}
		
	mysqli_close($dbc);
		
} // End of SUMBIT conditional.
?>

<h1>Login</h1>
<form action="login.php" method="post">
	<fieldset>
    <p><small>Your browser must allow cookies in order to log in.</small></p>
    <p><b>Email Address:</b> <input type="text" name="email" size="20" maxlength="60" value="<?php if (isset($trimmed['email'])) echo $trimmed['email']; ?>" /></p>
    <p><b>Password:</b> <input type="password" name="password" size="20" maxlength="20" /></p>
    </fieldset>
    <div align="center"><input type="submit" name="submit" value="Login" /></div>
</form>

<?php include ('includes/footer.php'); ?>