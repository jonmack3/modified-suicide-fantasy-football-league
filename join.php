<?php
	session_start();
	
	include_once("../../php_scripts/msffl/msffl.php");
	include_once("../../php_scripts/msffl/navbar.php");
	
	create_header("FF: Join the League!",
	"modified suicide fantasy football league, join league",
	"Join the league as a new user");
	echo "<body>\n";
	
	create_navbar($_SESSION['user_type'], "join.php");
	
	echo "<p>Thanks for registering to play. If you have any questions, go ahead
	and sign up here, then log in and click the <strong>Contact Us</strong>
	link.</p>\n";
	
	echo "<p>In the <em>Join the League</em> dialogue box below, enter your
	name, and then choose a username and password. You will need your username
	and password to enter the site and make picks, set forfeits, and view other
	players' pools.</p>\n";
	
	echo "<p><strong>Note</strong>: After clicking <em>Join the League</em>, you
	must also click <strong>Confirm Join!</strong> before you will be added.</p>\n";
		
	// create "join" form
	echo "<form action=\"join.php\" method=\"post\">\n<fieldset>
		<legend>Join the League!</legend>\n";
	create_text_form_elements(array("First Name", "Last Name","Username"));
	create_pw_form_elements(array("Password", "Confirm Password"));
	echo "<p><input type=\"submit\" name=\"join\" value=\"Join the League!\" /></p>
		</fieldset>\n</form>\n";
		
	// "Join the League!" button pressed
	if (isset($_POST['join']))
	{
		// fields blank, too long, or contain punctuation
		if (!validate($_POST['username']) || !validate($_POST['first_name']) || 
			!validate($_POST['last_name']) || !validate($_POST['password']) ||
			!validate($_POST['confirm_password']))
		{
			echo "<p>Fields must contain only alphanumeric characters (a-z, A-Z, 0-9), 
				and may not be blank or longer than 20 characters.</p>\n";
		}
		// check that password and confirm_password match
		elseif (strcmp($_POST['password'], $_POST['confirm_password']) != 0)
		{
			echo "<p>The password and its confirmation must match.</p>\n";
		}
		// previous tests passed, confirm no duplicate usernames in database
		else
		{
			// copy contents of $_POST variables to queryable versions
			$username = $_POST['username'];
			$first_name = $_POST['first_name'];
			$last_name = $_POST['last_name'];
			$password = md5($_POST['password']);
		
			// determine if user name already in database as a player
			connect();
			$query = "SELECT 1 FROM users WHERE username ='$username' AND user_type = 'P'";
			$result = mysql_query($query);
			if (mysql_num_rows($result) > 0)
			{
				echo "<p>Alas, this username already exists. Please enter another.</p>\n";
			}
			// all requirements met: confirm add
			else
			{
				echo "<table><caption>Is this information correct?</caption>
					<tr><th>Username</th><th>First Name</th><th>Last Name</th></tr>
					<tr><td>$username</td><td>$first_name</td><td>$last_name</td></tr>
					<table>\n";
					
				// create confirmation form
				echo "<form action=\"join.php\" method=\"post\">\n";
				echo "<p><input type=\"submit\" name=\"confirm_join\" value=\"Confirm Join!\" /></p>\n";
				echo "<p><input type=\"submit\" name=\"do_not_join\" value=\"Wait! Stop!\" /></p>\n";
				echo "</form>\n";
					
				// set session variables to allow confirmation
				$_SESSION['join_username'] = $username;
				$_SESSION['join_first_name'] = $first_name;
				$_SESSION['join_last_name'] = $last_name;
				$_SESSION['join_password'] = $password;
			}
		}	
	}
		
	// add not confirmed, erase session variables
	if (isset($_POST['do_not_join']))
	{
		unset($_SESSION['join_username']);
		unset($_SESSION['join_first_name']);
		unset($_SESSION['join_last_name']);
		unset($_SESSION['join_password']);
		echo "<p>Join cancelled. (*sniff*)</p>\n";
	}
		
	// add confirmed, add user
	if (isset($_POST['confirm_join']))
	{
		// get record
		connect();
		$join_username = $_SESSION['join_username'];
		$join_first_name = $_SESSION['join_first_name'];
		$join_last_name = $_SESSION['join_last_name'];
		$join_password = $_SESSION['join_password'];
		
		$query = "INSERT INTO users SET username='$join_username', first_name='$join_first_name', 
			last_name='$join_last_name', password='$join_password', user_type='P'";
		$result = mysql_query($query);
		
		// get all team IDs
		$query = "SELECT team_id FROM teams";
		$team_result = mysql_query($query);
		$team_ids = array();
		while (list($team_id) = mysql_fetch_row($team_result))
		{
			$team_ids[] = strtoupper($team_id);
		}
				
		// populate player_pools table with combination of new user and all teams
		foreach ($team_ids as $team_id)
		{
			$query = "INSERT INTO player_pools SET username='$join_username',
				team_id = upper('$team_id')";
			$team_result = mysql_query($query);
		}
		
		// display message based on whether insert statements successfully executed
		if (!$result)
		{
			echo "<p>Database record creation failed. Please try again.</p>\n";
		}
		elseif (!$team_result)
		{
			echo "<p>Unable to create user/team combination in database.
				Please notify the administrator of this issue; you will be
				unable to select your pool until it is resolved.<p>\n";
		}
		else echo "<p>Congratulations, $join_username, you've successfully joined the 
				league! Use the navigation menu at left to log in.</p>\n";
		
		// unset join session variables
		unset($_SESSION['join_username']);
		unset($_SESSION['join_first_name']);
		unset($_SESSION['join_last_name']);
		unset($_SESSION['join_password']);
	}
	echo "</body>\n</html>";
?>