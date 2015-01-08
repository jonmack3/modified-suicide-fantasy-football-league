<?php
	session_start();
	
	include_once("../../../php_scripts/msffl/msffl.php");
	include_once("../../../php_scripts/msffl/navbar.php");
	
	// set number of teams that must be initially excluded
	$reqd_exclude_num = 7;
	
	// send user back to login page if haven't logged in
	if (strcasecmp($_SESSION['user_type'], "player") != 0) header("Location: ../login.php");
	else
	{
		// header
		create_header("FF: Create pool",
			"modified suicide fantasy football league, create player pool",
			"Modified Suicide Fantasy Football League: Create player pool");
		
		echo "<body>\n";
		
		create_navbar($_SESSION['user_type'], "createpool.php");
		
		echo "<!-- Main Content -->\n";
		
		echo "<p>Hello, ".$_SESSION['first_name'].".</p>\n";
		
		// get username
		$username = $_SESSION['username'];
		
		// get cutoff time
		connect();
		$query = "SELECT time FROM cutoff_times WHERE week = '0'";
		$result = mysql_query($query);
		$cutoff_time = mysql_result($result, 0, "time");
	
		// determine whether current time is actual or testing value
		$fh = fopen("../../../football/config/timesetup.txt", "r");
		$value = file_get_contents("../../../football/config/timesetup.txt");
		fclose($fh);
		$current_time;
		if ($value == 0) $current_time = time();
		else $current_time = $value;
		
		// current date past cutoff date
		if ($current_time > $cutoff_time)
		{
			date_default_timezone_set('America/New_York');
			echo "<p>Alas, the pool creation cutoff 
				date of ".date("G:i:s T F d, Y", $cutoff_time)." has passed...</p>\n";
		}
		
		// current date still before cutoff date
		else
		{
			// get team_id, team_name, and team_city of all teams in player's pool
			$query = "SELECT teams.team_id, team_city, team_name FROM player_pools 
				INNER JOIN teams ON player_pools.team_id = teams.team_id 
				WHERE username = '$username' ORDER BY team_id";
			$result = mysql_query($query);
			$num_teams = mysql_num_rows($result);
			$teams = array();
			while (list($team_id, $team_city, $team_name) = mysql_fetch_row($result))
			{
				$teams[] = $team_id."|".$team_city."|".$team_name;
			}
		
			// get number in player's pool
			
			$query = "SELECT count(team_id) FROM player_pools WHERE 
				username = '$username' AND status ='initially_excluded'";
			$result = mysql_query($query);
			$num_excluded = mysql_result($result, 0, "count(team_id)");
			$num_not_excluded = $num_teams - $num_excluded;
			$num_to_exclude = $reqd_exclude_num - $num_excluded; 
		
			echo "<p>Currently, your pool has <span class=\"blue_font\">$num_not_excluded</span>
				teams. You must exclude <span class=\"red_font\">$num_to_exclude</span>
				by the deadline of <strong>".date("G:i:s T F d, Y", $cutoff_time)."</strong>.</p>\n";
			
			if ($num_to_exclude > 0) {
				echo "<p>Please uncheck <span class=\"red_font\">$reqd_exclude_num</span> teams ";
				echo "and hit the Submit button. (You may change your selections at any time ";
				echo "before the deadline by returning to this page.)</p>\n";
			}
			elseif ($num_to_exclude == 0) {
				echo "<p>Your pool is currently valid. If you wish to alter your pool, please ";
				echo "uncheck <span class=\"red_font\">7</span> teams and click the ";
				echo "<span class=\"red_font\">Create Pool</span> button.</p>\n";
			}
			
			// create teams form
			echo "<form action=\"createpool.php\" method=\"post\"><fieldset><legend>Uncheck
				<span class=\"red_font\">7</span> teams: </legend>\n";
			echo "<table><tr>\n";
			for ($key = 0; $key < count($teams); $key++)
			{
				$team_info = explode('|', $teams[$key]);
				$team_id = $team_info[0];
				$team_city = $team_info[1];
				$team_name = $team_info[2];
				
				echo "<td><label for=\"$team_id\">
					<input type=\"checkbox\" name=\"checked_teams[]\" 
						id=\"$team_id\"  value=\"$team_id\" checked=\"checked\"/>
					<img src=\"/images/teams/$team_id.gif\" width=\"48\" height=\"48\"
						alt=\"$team_city $team_name logo\" title=\"$team_city $team_name\" />
					</label>\n</td>\n";
				if (($key + 1) % 4 == 0) echo "</tr>\n<tr>\n";
			}
			echo "<td></td></tr></table>\n";
			echo "<p><input type=\"submit\" name=\"create_pool\" value=\"Create pool!\" /></p>\n";
			echo "</fieldset>\n</form>\n";
		
			// player submitting new pool
			if (isset($_POST['create_pool']))
			{	
				// too few teams unchecked
				if (count($_POST['checked_teams']) > 25)
				{
					echo "<p>You unchecked fewer than the $reqd_exclude_num required. (You unchecked ".
						(count($teams) - count($_POST['checked_teams']))." team(s).)</p>\n";
				}
				// too many teams selected
				elseif (count($_POST['checked_teams']) < 25)
				{
					echo "<p>You unchecked more than the $reqd_exclude_num required. (You unchecked ".
						(count($teams) - count($_POST['checked_teams']))." team(s).)</p>\n";
				}
				// correct amount selected
				else
				{	
					$reset_result;
					$set_result;
					
					// reset status of all teams associated with this player
					$query = "UPDATE player_pools SET status=NULL, week_removed=NULL WHERE username = '$username'";
					$reset_result = mysql_query($query);
					
					// set status of excluded teams
					if ($reset_result)
					{
						foreach ($teams as $team)
						{	
							$team_info = explode('|', $team);
							$team_id = $team_info[0];
							
							if (!in_array($team_id, $_POST['checked_teams']))
							{
								$query = "UPDATE player_pools SET status='initially_excluded', week_removed='0'
									WHERE username = '$username' AND team_id = '$team_id'";
								$set_result = mysql_query($query);
								if (!$set_result)
								{
									echo "<p>Database update of forfeited teams failed. Please try again</p>\n";
								}	
							}
						}
					}
					else echo "<p>Deletion of old pool failed at the database. Please try again.</p>\n";
					if ($reset_result && $set_result) echo "Pool successfully created!";
				}
			}
		}

		// show selected teams
		echo "<table><caption>Selected teams</caption><tr>
		<th>Abbreviation</th><th>City</th><th>Name</th></tr>\n";
		connect();
		$query = "SELECT teams.team_id, team_city, team_name 
			FROM player_pools INNER JOIN teams ON player_pools.team_id = teams.team_id 
			WHERE username = '$username' AND NOT status = 'initially_excluded' 
			ORDER BY team_id";
		$result = mysql_query($query);
		while (list($team_id, $team_city, $team_name) = mysql_fetch_row($result))
		{
			echo "<tr><td>$team_id</td><td>$team_city</td><td>$team_name</td></tr>\n";
		}
		echo "</table>\n";
			
		// show unselected teams
		echo "<table><caption>Teams to be Excluded from Pool:</caption><tr>
		<th>Abbreviation</th><th>City</th><th>Name</th></tr>\n";
		$query = "SELECT teams.team_id, team_city, team_name 
			FROM player_pools INNER JOIN teams ON player_pools.team_id = teams.team_id 
			WHERE username = '$username' AND status = 'initially_excluded' 
			ORDER BY team_id";
		$result = mysql_query($query);
		while (list($team_id, $team_city, $team_name) = mysql_fetch_row($result))
		{
			echo "<tr><td>$team_id</td><td>$team_city</td><td>$team_name</td></tr>\n";
		}
		echo "</table>\n";
	}
	echo "</body>\n</html>\n";
?>