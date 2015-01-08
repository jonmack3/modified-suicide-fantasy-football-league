<?php
	session_start();
	
	include_once("../../../php_scripts/msffl/msffl.php");
	include_once("../../../php_scripts/msffl/navbar.php");
	
	// send user back to login page if haven't logged in
	if (strcasecmp($_SESSION['user_type'], "admin") != 0) header("Location: ../login.php");
	else
	{
		create_header("FF Admin: Add new user",
		"modified suicide fantasy football league, administration, add new user",
		"Administrator page to add new user");
		echo "<body>\n";
		
		create_navbar($_SESSION['user_type'], "adduser.php");
		
		echo "<p>Add a new user, ".$_SESSION['first_name']."!</p>\n";
		
		// create "add new user" form
		echo "<form action=\"adduser.php\" method=\"post\">
		<fieldset><legend>Add new user</legend>\n";
		create_text_form_elements(array("Username", "First name", "Last name"));
		create_pw_form_elements(array("Password", "Confirm Password"));
		create_mv_boxes (array("Administrator", "Player"), "user_types");
		echo "<p><input type=\"submit\" name=\"add_user\" value=\"Create user\" /></p>\n";
		echo "</fieldset>\n</form>\n";

		// check entries
		if (isset($_POST['add_user']))
		{
			// fields blank or too long, or contain punctuation
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
			// no user type specified
			elseif (count($_POST['user_types']) == 0)
			{
				echo "<p>At least one of 'Adminstrator' or 'Player' must be selected.</p>\n";
			}
			// previous tests passed, confirm no duplicate username/type combinations in database
			else
			{
				// copy contents of $_POST variables to queryable versions
				$username = $_POST['username'];
				$first_name = $_POST['first_name'];
				$last_name = $_POST['last_name'];
				$password = md5($_POST['password']);
				$user_types = $_POST['user_types'];
	
				// determine if user name/type combination already in database
				connect();
				$query = "SELECT user_type FROM users WHERE username ='$username'";
				$result = mysql_query($query);
				$user_type_found = false;
				while ($row = mysql_fetch_assoc($result))
				{
					$db_user_type = $row['user_type'];
					// new user is an admin
					if (in_array("administrator", $_POST['user_types']) && strcasecmp($db_user_type, "A") == 0)
					{
						$user_type_found = true;
					}
					// new user is a player
					if (in_array("player", $_POST['user_types']) && strcasecmp($db_user_type, "P") == 0)
					{
						$user_type_found = true;
					}
				}
				// user name/type combination already exists; do not insert entry
				if ($user_type_found)
				{
					echo "<p>This user and type combination already exists.</p>\n";
				}
				// all requirements met: confirm add
				else
				{
					// create confirmation table
					unset($user_type_string);
					foreach ($user_types as $user_type)
					{
						$user_type_string .= $user_type." ";
					}
					echo "<table><caption>Add the following user?</caption>
						<tr><th>Username</th><th>First Name</th><th>Last Name</th>
						<th>Password</th><th>User Type</th></tr>
						<tr><td>$username</td><td>$first_name</td><td>$last_name</td>
						<td>$password</td><td>$user_type_string</td></tr>\n";
					
					// create confirmation form
					echo "<form action=\"adduser.php\" method=\"post\">
						<p><input type=\"submit\" name=\"confirm_add\" value=\"Confirm Add!\" /></p>
						<p><input type=\"submit\" name=\"do_not_add\" value=\"Well, maybe not...\" /></p>
						</form>\n";
				
					// set session variables to allow confirmation
					$_SESSION['username_to_add'] = $username;
					$_SESSION['first_name_to_add'] = $first_name;
					$_SESSION['last_name_to_add'] = $last_name;
					$_SESSION['password_to_add'] = $password;
					if (in_array("administrator", $user_types)) $_SESSION['admin'] = true;
					else $_SESSION['admin'] = false;
					if (in_array("player", $user_types)) $_SESSION['player'] = true;
					else $_SESSION['player'] = false;
				}
			}
		}	
		
		// add not confirmed, erase session variables
		if (isset($_POST['do_not_add']))
		{
			unset($_SESSION['username_to_add']);
			unset($_SESSION['first_name_to_add']);
			unset($_SESSION['last_name_to_add']);
			unset($_SESSION['password_to_add']);
			unset($_SESSION['admin']);
			unset($_SESSION['player']);
			echo "<p>Add user cancelled.</p>";
		}
		
		// add confirmed, add user
		if (isset($_POST['confirm_add']))
		{
			// get record info
			$username_to_add = $_SESSION['username_to_add'];
			$first_name_to_add = $_SESSION['first_name_to_add'];
			$last_name_to_add = $_SESSION['last_name_to_add'];
			$password_to_add = $_SESSION['password_to_add'];
			
			connect();
			$result;
			$team_result;

			// add new administrator
			if ($_SESSION['admin'])
			{
				$query = "INSERT INTO users SET username='$username_to_add',
				first_name='$first_name_to_add', last_name='$last_name_to_add',
				password='$password_to_add', user_type='A'";
				$result = mysql_query($query);
			}
			// add new player
			if ($_SESSION['player'])
			{
				$query = "INSERT INTO users SET username='$username_to_add',
				first_name='$first_name_to_add', last_name='$last_name_to_add',
				password='$password_to_add', user_type='P'";
				$result = mysql_query($query);
				
				// get all team IDs
				$query = "SELECT team_id FROM teams";
				$team_result = mysql_query($query);
				$team_ids = array();
				while (list($team_id) = mysql_fetch_row($team_result))
				{
					$team_ids[] = $team_id;
				}
				
				// populate player_pools table with combination of new user and all teams
				foreach ($team_ids as $team_id)
				{
					$query = "INSERT INTO player_pools SET username='$username_to_add',
						team_id = '$team_id'";
					$team_result = mysql_query($query);
					if (!team_result) $team_insert = false;
				}
			}
			
			// display message based on whether insert statements successfully executed
			if (!$result) echo "<p>Database record creation failed. Please try again.</p>\n";
			elseif (isset($_POST['player']) && !$team_result)
			{
				echo "<p>Unable to create user/team combination in database.
					Please delete this user, then attempt to add them again.</p>\n";
			}
			else echo "<p>User added!</p>\n";
			
			// erase session variables
			unset($_SESSION['username_to_add']);
			unset($_SESSION['first_name_to_add']);
			unset($_SESSION['last_name_to_add']);
			unset($_SESSION['password_to_add']);
			unset($_SESSION['admin']);
			unset($_SESSION['player']);
		}
	}
	echo "</body>\n</html>";
?>