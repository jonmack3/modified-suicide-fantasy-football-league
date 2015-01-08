<?php
	session_start();
	
	include_once("../../../php_scripts/msffl/msffl.php");
	include_once("../../../php_scripts/msffl/navbar.php");
	
	// send user back to login page if haven't logged in
	if (strcasecmp($_SESSION['user_type'], "admin") != 0) header("Location: ../login.php");
	else
	{
		create_header("FF Admin: Delete user",
		"modified suicide fantasy football league, administration, delete user",
		"Administrator page to delete user");
		echo "<body>\n";
	
		create_navbar($_SESSION['user_type'], "deleteuser.php");
	
		echo "<p>Welcome, ".$_SESSION['first_name'].", to the Delete User page.</p>\n";
	
		// create "delete user" form
		echo "<form action=\"deleteuser.php\" method=\"post\">\n<fieldset>\n
			<legend>Delete user</legend>\n";
		// create form elements
		create_text_form_elements(array("Username"));
		create_radio_form_elements(array("Administrator", "Player"), "user_type");
		echo "\n<p><input type=\"submit\" name=\"delete_user\" value=\"Delete user\" /></p>\n";
		echo "</fieldset>\n</form>\n";
		
		// check form values, and build confirm form if so
		if (isset($_POST['delete_user']))
		{
			// test for invalid username entered
			if (!validate($_POST['username']))
			{
				echo "<p>Fields must contain only alphanumeric characters (a-z, A-Z, 0-9), 
					and may not be blank or longer than 20 characters.</p>\n";
			}
			// test for no user type selected
			elseif (count($_POST['user_type']) == 0)
			{
				echo "<p>Must select a user type.</p>\n";
			}
			// valid user/type combination
			else
			{
				// get record
				connect();
				$username_to_delete = $_POST['username'];
				$user_type_to_delete = substr($_POST['user_type'], 0, 1);
				$query = "SELECT username FROM users WHERE username = 
				'$username_to_delete' and user_type = '$user_type_to_delete'";
				$result = mysql_query($query);
	
				// record not found
				if (mysql_num_rows($result) == 0)
				{
					echo "<p>User name/type combination not found.</p>\n";
				}
				
				// record found, confirm delete
				else
				{
					// set session variables to allow confirmation
					$_SESSION['username_to_delete'] = $username_to_delete;
					$_SESSION['user_type_to_delete'] = $user_type_to_delete;
					
					// create confirmation form
					echo "<p>Delete user/type combination \"".$_POST['username']."\" (".$_POST['user_type'].")?
						<form action=\"deleteuser.php\" method=\"post\">
						<p><input type=\"submit\" name=\"confirm_delete\" value=\"Confirm Delete\" /></p>
						<p><input type=\"submit\" name=\"do_not_delete\" value=\"Don't delete!\" /></p>
						</form>\n";
				}
			}
		}
		
		// delete not confirmed, erase session variables
		if (isset($_POST['do_not_delete']))
		{
			unset($_SESSION['username_to_delete']);
			unset($_SESSION['user_type_to_delete']);
			echo "<p>Deletion cancelled.</p>\n";
		}
		
		if (isset($_POST['confirm_delete']))
		{
			// get record
			connect();
			$username_to_delete = $_SESSION['username_to_delete'];
			$user_type_to_delete = $_SESSION['user_type_to_delete'];
					
			// delete from users table
			$query = "DELETE FROM users WHERE username = '$username_to_delete' 
				AND user_type = '$user_type_to_delete'";
			$result = mysql_query($query);
			$all_records_deleted = true;
			if (!$result)
			{
				echo "<p>Deletion of records failed in the table 'users'.</p>\n";
				$all_records_deleted = false;
			}
			
			// delete from player_pools if player
			if (strcasecmp($user_type_to_delete, "p") == 0)
			{
				// delete from player_picks table
				$query = "DELETE FROM player_picks WHERE username = '$username_to_delete'";
				$result = mysql_query($query);
				if (!$result)
				{
					echo "<p>Deletion of records failed in the table 'player_picks'.</p>\n";
					$all_records_deleted = false;
				}
					
				// delete from player_pools table
				$query = "DELETE FROM player_pools WHERE username = '$username_to_delete'";
				$result = mysql_query($query);
				if (!$result)
				{
					echo "<p>Deletion of records failed in the table 'player_pools'.</p>\n";
					$all_records_deleted = false;
				}
			}
			
			// display status message
			if ($all_records_deleted) echo "<p>User/type combination deleted.</p>\n";
			else echo "<p>Please try again.</p>\n";
			
			// unset session variables
			unset($_SESSION['username_to_delete']);
			unset($_SESSION['user_type_to_delete']);
		}
	}
	echo "</body>\n</html>";
?>