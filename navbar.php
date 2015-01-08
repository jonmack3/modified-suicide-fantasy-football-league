<?php
	// Creates the navigation bar, based on $user_type and a variable called $current_location, for any page.
	function create_navbar($user_type, $current_location)
	{
		// start of navigation menu
		echo "<!-- Site navigation menu -->\n";
		echo "<ul class=\"navbar\">\n";
		
		// Link/file names for pages viewable by all
		$all_pages = array(
			"Home" => "index.php", 
			"Rules" => "rules.php", 
			"View Pools" => "playerpools.php");
		
		// Link/file names for pages viewable by who haven't logged in
		$not_logged_pages = array(
			"Sign in!" => "login.php",
			"Join the League" => "join.php");
		
		// Link/file names for pages viewable only by players
		$player_pages = array(
			"Player Home" => "player.php",
			"Create Pool" => "createpool.php",
			"Picks/Forfeits" => "picks.php",
			"Contact Us" => "contact.php");
		
		// Link/file names for pages viewable only by admins
		$admin_pages = array(
			"Admin Home" => "admin.php", 
			"Add User" => "adduser.php", 
			"Delete User" => "deleteuser.php",
			"Set Cutoffs" => "setpoolcutoff.php", 
			"View Users" => "viewusers.php", 
			"Current Time Mode" => "testtime.php", 
			"Enter Scores" => "enterscores.php",
			"Compute Scores" => "computescores.php",
			"Process forfeits" => "processforfeits.php");

		$logout_pages = array("Logout" => "logout.php");

		// Useful links that anyone can use
		$handy_links = array(
			"2007 Records" => "http://www.nfl.com/standings?category=league&amp;season=2007-REG&amp;split=Overall",
			"2008 Schedule" => "http://www.nfl.com/schedules?seasonType=REG&amp;season=2008#Week");
			
		// links viewable by all
		create_links ($all_pages, "/", $current_location);
		
		// links viewable only by admins
		if (strcasecmp($user_type, 'admin') == 0) {
			echo "<li class=\"separator\">&nbsp; </li>\n";
			create_links ($admin_pages, "/admin/", $current_location);
			create_links ($logout_pages, "/", $current_location);
		}
		
		// links viewable only by players
		elseif (strcasecmp($user_type, 'player') == 0) {
			echo "<li class=\"separator\">&nbsp; </li>\n";
			create_links ($player_pages, "/players/", $current_location);
			create_links ($logout_pages, "/", $current_location);
		}
		
		// links viewable by those who haven't logged in
		else {
			echo "<li class=\"separator\">&nbsp; </li>\n";
			create_links ($not_logged_pages, null, $current_location);
		}

		// handy links viewable for everyone
		echo "<li class=\"separator\">&nbsp; </li>\n";
		create_links ($handy_links, null, $current_location);
		
		// end of navigation menu
		echo "<li class=\"separator\">&nbsp; </li>
			<li><a href=\"http://validator.w3.org/check?uri=referer\">
        		<img src=\"http://www.w3.org/Icons/valid-xhtml10\"
        		alt=\"Valid XHTML 1.0 Transitional\" height=\"31\" width=\"88\" /></a>\n
        		</li>\n";
		echo "</ul>\n";
		
		//
		// echo "<!-- Main content -->\n";
		//       (This should be added to other files individually...)
	}

	// creates list of links for all except current page
	function create_links ($links_array, $dir, $current_location)		
	{
		reset($links_array);
		while ($name = key($links_array))
		{
			if (strcasecmp(current($links_array), $current_location) == 0) {
				echo "<li class=\"current\">".$name."</li>\n";
			}
			else {
				echo "<li><a href=\"".$dir.current($links_array)."\">".
					key($links_array)."</a></li>\n";
			}
			next($links_array);
		}
	}
?>