<?php 
// session_login.php  
// This script checks to see if an admin is logged into the system.  If not, they are redirected to the login screen

if (!isset($_SESSION['admin_first_name'])) {
	$url = BASE_URL . 'login.php'; // Define the URL.
	ob_end_clean(); // Delete the buffer.
	header("Location: $url");
	exit(); // Quit the script.
}
?>