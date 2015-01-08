<?php
	session_start();
	
	include_once("../../php_scripts/msffl/msffl.php");
	include_once("../../php_scripts/msffl/navbar.php");
	
	ob_start();
	
	create_header("FF: Log In!",
		"modified suicide fantasy football league, login",
		"Modified Suicide Fantasy Football League: Login");
	echo "<body>\n";
	
	create_navbar($_SESSION['user_type'], "login.php");
	
	// create login form
	echo "<form action=\"login.php\" method=\"post\">\n";
	create_text_form_elements(array("Username"));
	create_pw_form_elements(array("Password"));
	echo "<p><input type=\"submit\" name=\"login\" value=\"Sign in!\" /></p>\n</form>\n";

	if (isset($_POST['login']))
	{
		// username/password either blank, too long, or contain punctuation
		if (!validate($_POST['username']) || !validate($_POST['password']))
		{
			echo "<p>Fields must contain only alphanumeric characters (a-z, A-Z, 0-9), 
				and may not be blank or longer than 20 characters.</p>\n";
		}
		
		// username/password valid, check combination
		else
		{
			// compare username/password/user type
			connect();
			$user_type = compare($_POST['username'], md5($_POST['password']));

			// username/password correct
			if (strcasecmp($user_type, "A") == 0 || strcasecmp($user_type, "P") == 0 || 
				strcasecmp($user_type, "B") == 0)
			{
				// get and set player username, first name, last name, and handle
				$username = $_POST['username'];
				$query = "SELECT first_name, last_name, handle FROM users WHERE username ='$username'";
				$result = mysql_query($query);
				$_SESSION['username'] = $username;
				$_SESSION['first_name'] = mysql_result($result, 0, "first_name");
				$_SESSION['last_name'] = mysql_result($result, 0, "last_name");
				$_SESSION['handle'] = mysql_result($result, 0, "handle");

				switch ($user_type)
				{
					// administrator signing in, load admin page
					case "A":
						$_SESSION['user_type'] = "admin";
						header("Location: admin/admin.php");
						break;
	
					// player signing in, load player page
					case "P":
						$_SESSION['user_type'] = "player";
						header("Location: players/player.php");
						break;
				
					// user is both player and admin; determine user_type
					case "B": 
						$_SESSION['username'] = $_POST['username'];
						echo "<p>Would you like to log in as a player or administrator?</p>\n";
			
						// create form
						echo "<form action=\"login.php\" method=\"post\">\n";
						create_radio_form_elements(array("Administrator", "Player"), "decide");
						echo "<p><input type=\"submit\" name=\"choose_type\" value=\"Choose type\" /></p></form>\n";
						break;
				}
			}
			// username/password combination incorrect
			else
			{
				echo "<p>Username/password combination incorrect; please try again.</p>";
			}
		}
	}

	// user is both player and admin: determine which type to load
	if (isset($_POST['choose_type']))
	{
		// load admin
		if (strcasecmp($_POST['decide'], "administrator") == 0)
		{
			$_SESSION['user_type'] = "admin";
			header("Location: admin/admin.php");
		}
		// load user
		elseif (strcasecmp($_POST['decide'], "player") == 0)
		{
			$_SESSION['user_type'] = "player";
			header("Location: players/player.php");
		}
		else
		{
			echo "Please select either user or administrator.";
		}
	}
	
	ob_end_flush();
	echo "</body>\n</html>";
?>