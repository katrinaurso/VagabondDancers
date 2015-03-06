<?php # index.php
// This is the main page for the site.

// Include the configuration file:
require ('includes/config.inc.php');

// Set the page title and include the HTML header:
$page_title = 'Welcome | Vagabond Dancers!';
include ('includes/header.php');

// Welcome the user (by name if they are logged in):
echo '<h1>Welcome';
if (isset($_SESSION['first_name'])) {
	echo ", {$_SESSION['first_name']}";
}
echo '!</h1>';
?>
<p>Vagabond Dancers is a site dedicated to helping dancers find out where to dance.  Sounds like a simple idea, right?  With this site, it is our hope that dancers will be able to find out where to dance where ever they are!  Going to Seattle, WA for a trip and want to know where to Swing Dance the dates you are there?  Simply search to find out!</p></br />
<p>The use of the information is totally free!  So why not register and start plotting?  There are many benefits to registering, such as being able to see the reviews from other Vagabonds in their experiences and reviewing and rating things for others!  (That is just a start, head over to the Registration page to find out what other awesome things await on the other side of the <a href="login.php" title="Login">login</a> button...</p><br />
<p>Right now the site is just in its beginning phases.  We are tediously learning how to develop the site as we go, so please pardon the dust.  If you are here, you were most likely invited and we would love to hear any of your comments, suggestions and ideas!  Feel free to start browsing and if you encounter any errors in the workings of the site, do let us know either by filling out the contact form (<a href="contact.php" title="Contact Us">contact us</a> or emailing us <a href="mailto:contact@vagabonddancers.com">admin@vagabonddancers.com</a>) so we can fix that as soon as possible!</p></br />
<p>This website is for dancers to get information!  So let's start packing it with all we can so we can find where to go!</p><br />
<p>Let's get moving!</p>

<?php include ('includes/footer.php'); ?>