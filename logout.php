<?php # - logout.php

// This page was last checked on 07-02-2014
// This is the logout page for the site.

require ('includes/config.inc.php');
$page_title = 'Logout | Vagabond Dancers';
include ('includes/header.php');

// If no first_name session variable exists, redirect the user:
if (!isset($_SESSION['first_name'])) {

	$url = BASE_URL . 'index.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
	
} else { // Log out the user.

	$_SESSION = array(); // Destroy the variables.
	session_destroy(); // Destroy the session itself.
	setcookie (session_name(), '', time()-3600); // Destroy the cookie.
	
}

// Print a customized message:
echo '<h1>You are now logged out.</h1> 
<p>Thank you for visiting, please come back again soon!  Remember that the world is our dance floor!</p>';

include ('includes/footer.php');
?>