<!-- Start of Footer -->
</div><!-- Content -->

<div id="Menu">
    <?php # Admin - footer.html
	// This page completes the template.
	
	// Display links based upom the login status:
	if (isset($_SESSION['admin_id'])) {
	
		echo '<a href="logout.php" title="Logout">Logout</a><br />
        <a href="index.php" title="Home">Home</a><br />
        <a href="change_password.php" title="Change Your Password">Change Password</a><br />
		<a href="view_users.php" title="View All Users">View Users</a><br />
        <a href="view_events.php" title="View Events">View Events</a><br />
		<a href="search_state.php" title="Search by State">Search by State</a></br>
    	<a href="add_event.php" title="Add an Event">Add an Event</a><br />
        <a href="verify_events.php" title="Verify Events Added">Verify Events</a><br />
        <a href="expired_events.php" title="Update Expired Events">Expired Events</a><br />
        ';
		
	} else { // Not logged in.
		echo '<a href="login.php" title="Login">Login</a><br />
        <a href="forgot_password.php" title="Password Retrieval">Retrieve Password</a><br />
		<a href="../test/index.php" title="VagabondDancers.com">Main Site</a><br />
		';
	}
	?>
    
</div><!-- Menu -->

</body>
</html>
<?php // Flush the buffered output.
ob_end_flush();
?>