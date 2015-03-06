<!-- Start of Footer -->
</div><!-- Content -->

<div id="Menu">
	<a href="index.php" title="Home Page">Home</a><br />
    <?php # footer.html
	// This page completes the HTML template.
	
	// Display links based upom the login status:
	if (isset($_SESSION['people_id'])) {
	
		echo '<a href="logout.php" title="Logout">Logout</a><br />
        <a href="profile.php" title="Profile">View Your Profile</a><br />
		<a href="change_password.php" title="Change Your Password">Change Password</a><br />
    	<a href="add_event.php" title="Add an Event">Add an Event</a><br />';
		
	} else { // Not logged in.
		echo '<a href="login.php" title="Login">Login</a><br />
        <a href="register.php" title="Register for the Site">Register</a><br />
		<a href="forgot_password.php" title="Password Retrieval">Retrieve Password</a><br />';
	}
	?>
    <a href="search_state.php" title="Search">Search</a><br />
    <a href="contact.php" title="Contact Us">Contact Us</a><br />
</div><!-- Menu -->

</body>
</html>
<?php // Flush the buffered output.
ob_end_flush();
?>