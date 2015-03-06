<?php # Admin- logout.php
// This is the logout page for the admin site.

require ('includes/config.inc.php');
$page_title = 'Vagabond Guru: Logout';
include ('includes/header.php');

// IF no first_name session variable exists, redirect the user:
if (!isset($_SESSION['admin_first_name'])) {

	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
	
} else { // Log out the user.

	$_SESSION = array(); // Destroy the variables.
	session_destroy(); // Destroy the session itself.
	setcookie (session_name(), '', time()-3600); // Destroy the cookie.
	
}

// Print a customized message:
echo '<h3>You are now logged out.</h3>';

include ('includes/footer.php');
?>