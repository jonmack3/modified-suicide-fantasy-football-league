<?php
	session_start();
	
	include_once("../../../php_scripts/msffl/msffl.php");
	include_once("../../../php_scripts/msffl/navbar.php");
	
	// send user back to login page if haven't logged in
	if (strcasecmp($_SESSION['user_type'], "admin") != 0) header("Location: ../login.php");
	else
	{
		create_header("FF Admin: Enter scores",
		"modified suicide fantasy football league, administration, enter scores",
		"FF Admin page to enter game scores");
		echo "<body>\n";
		
		create_navbar($_SESSION['user_type'], "enterscores.php");
		
		echo "<p>Hello ".$_SESSION['first_name'].", for which week would you like to enter scores?</p>\n";
	
		// get weeks
		connect();
		$query = "SELECT MAX(week) FROM season";
		$result = mysql_query($query);
		$max_week = mysql_result($result, 0, "MAX(week)");
		
		// generate weeks form
		echo "<form action=\"enterscores.php\" method=\"post\">
			<div><select name=\"week\">\n";
		create_option_form_elements(range(1, $max_week));
		echo "</select>\n<input type=\"submit\" name=\"select_week\" value=\"Select week\" /></div></form>\n";
	
		if (isset($_POST['select_week']))
		{
			echo "<p>Week number: ".$_POST['week']."</p>";
			
			$home_teams = array();
			$away_teams = array();
			$_SESSION['week'] = $_POST['week'];
			$week = $_POST['week'];
		
			// get teams playing for selected week
			
			$query = "SELECT home_team_id, away_team_id FROM season 
				WHERE week='$week' ORDER BY home_team_id";
			$result = mysql_query($query);
			
			// create "enter scores" form
			echo "<form action=\"enterscores.php\" method=\"post\">\n";
			while (list($home_team_id, $away_team_id) = mysql_fetch_row($result))
			{
				$home_team_ids[] = $home_team_id;
				$away_team_ids[] = $away_team_id;
				
				echo "<div><label for=\"$home_team_id\">$home_team_id: </label>
					<input type=\"text\" id=\"$home_team_id\" name=\"$home_team_id\" size = \"3\"/>
					<label for=\"$away_team_id\">$away_team_id: </label>
					<input type=\"text\" id=\"$away_team_id\" name=\"$away_team_id\" size = \"3\"/><br /></div>\n";
			}
			echo "<p><input type=\"submit\" name=\"submit\" value=\"Submit scores\" /></p></form>\n";
			
			$_SESSION['home_team_ids'] = $home_team_ids;
			$_SESSION['away_team_ids'] = $away_team_ids;
		}
		
		if (isset($_POST['submit']))
		{
			$home_team_ids = $_SESSION['home_team_ids'];
			$away_team_ids = $_SESSION['away_team_ids'];
			$week = $_SESSION['week'];

			// check validity of all values
			$valid = true;

			foreach ($home_team_ids as $home_team_id)
			{
				if (!preg_match("/^[0-9]{1,3}$/", $_POST[$home_team_id])) $valid = false;
			}
			foreach ($away_team_ids as $away_team_id)
			{
				if (!preg_match("/^[0-9]{1,3}$/", $_POST[$away_team_id])) $valid = false;
			}
			
			// one or more values invalid
			if (!$valid)
			{
				echo "<p>All scores must contain only numeric characters, and be 
					within 1 and 3 characters in length. No values updated.</p>";
			}	
			// all values correct; insert them
			else
			{
				$inserted = true;
				// insert home team scores
				foreach ($home_team_ids as $home_team_id)
				{
					$home_score = $_POST[$home_team_id];
					$query = "UPDATE season SET home_score = '$home_score' WHERE 
						home_team_id = '$home_team_id' AND week = '$week'";
					$result = mysql_query($query);
					if (!result) $inserted = false;
				}
				
				// insert away team scores
				foreach ($away_team_ids as $away_team_id)
				{
					$away_score = $_POST[$away_team_id];
					$query = "UPDATE season SET away_score = '$away_score' WHERE 
						away_team_id = '$away_team_id' AND week = '$week'";
					$result = mysql_query($query);
					if (!result) $inserted = false;
				}
				
				if (!$inserted)
				{
					echo "<p>One or more scores were not successfully inserted into the season table.
						Please reenter scores and resubmit.</p>";
				}
				else echo "<p>Scores successfully updated!</p>";
			}
			
			unset($_SESSION['home_team_ids']);
			unset($_SESSION['away_team_ids']);
			unset($_SESSION['week']);
		}
	}
	echo "\n</body>\n</html>";
?>