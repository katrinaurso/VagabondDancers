<?php # Admin - header.php
// This page begins the HTML header for the admin side of the site.

// Start output buffering:
ob_start();

// Initilize a session:
session_start();

// Check for a $page_title value:
if (!isset($page_title)) {
	$page_title = 'Guru - Vagabond Dancers';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $page_title; ?></title>
    <style type="text/css" media="screen">@import "includes/layout.css";</style>
</head>
<body>
<div id="Header">Guru: Vagabond Dancers
<!-- Include the Search Bar -->
	<div id="testsearchbar">
		<form id="tfnewsearch" name="searchbar" method="post" action="search_results.php" target="_blank">
		<input name="search" type="text"  size="21" maxlength="120" class="tftextinput"/><input type="submit" name="Submit" value="Search" class="tfbutton" />
		</form>
    	<div class="tfclear"></div>
	</div>
</div>
<div id="Content">
<!-- End of Header -->
