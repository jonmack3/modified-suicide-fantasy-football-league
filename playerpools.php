<?php
	session_start();

	include_once("../../php_scripts/msffl/msffl.php");
	include_once("../../php_scripts/msffl/navbar.php");
	
	create_header("FF: View player pools",
		"modified suicide fantasy football league, view player pools",
		"Modified Suicide Fantasy Football League: View player pools");
	echo "<body>\n";
	
	create_navbar($_SESSION['user_type'], "playerpools.php");
	
	// get all player IDs and teams
	$players = array();
	$display_names = array();
	connect();
	
	$query = "SELECT time FROM cutoff_times WHERE week = '0'";
	$result = mysql_query($query);
	$cutoff_time = mysql_result($result, 0, "time");
	
	// determine whether current time is actual or testing value
	$fh = fopen("../../football/config/timesetup.txt", "r");
	$value = file_get_contents("../../football/config/timesetup.txt");
	fclose($fh);
	$current_time;
	if ($value == 0) $current_time = time();
	else $current_time = $value;
		
	// current date before cutoff date and you're not an admin? No peeking!
	if ($current_time < $cutoff_time && $_SESSION['user_type'] != "admin") {
		date_default_timezone_set('America/New_York');
		echo "<p>Sorry. You can't view the pools of other players until ";
		echo "after the deadline of <strong>".date("G:i:s T F d, Y", $cutoff_time)."</strong>.</p>\n";
	}	
		
	// past the cutoff date, everyone can see...
	else {	
		$query = "SELECT username FROM users WHERE user_type = 'P' ORDER BY first_name";
		$result = mysql_query($query);
		while (list($username) = mysql_fetch_row($result)) {
			array_push($players, $username);
		}

		// table definition
		echo "<table class=\"pool\">
			<caption>Player Pools</caption>
			<tr class=\"tight\"><th class=\"leftmost\">Player</th>
			<th>Score</th>\n";
	
		// display header row, with team icons and alt and title attributes
		$teams = array();
		$query = "SELECT team_id, team_city, team_name FROM teams ORDER BY team_id";
		$result = mysql_query($query);
		while (list($team_id, $team_city, $team_name) = mysql_fetch_row($result)) {
			array_push($teams, $team_id);
			echo "<th><img src=\"/images/teams/$team_id.gif\" width=\"32\" height=\"32\"
			alt=\"$team_city $team_name logo\" title=\"$team_city $team_name\" /></th>\n";
		}
		echo "</tr>\n";
	
		// create player-specific row of each table
		foreach ($players as $username) {
			// display player's actual name.
			$query_score = "SELECT first_name, last_name, SUM(score) FROM users, player_scores 
				WHERE users.username = '$username' 
				AND user_type='P' 
				AND users.username = player_scores.username";
			$result_score = mysql_query($query_score);
			$row = mysql_fetch_row($result_score);
			$first_name = $row[0];
			$last_name = $row[1];
			$weekly_score = $row[2];
			
			
			echo "<tr class=\"tight\">
				<td class=\"leftmost\">".$first_name." ".substr($last_name,0,1).".</td>
				<td>".$weekly_score."</td>\n";
		
			// get player-specific teams and statuses
			$team_status = array();
			$query = "SELECT team_id, status, week_removed FROM player_pools WHERE username = '$username'
				ORDER BY team_id";
			$result = mysql_query($query);

			while (list($team_id, $status, $week_removed) = mysql_fetch_row($result)) {

				// print "initially excluded" cell
				if (strcasecmp($status, "initially_excluded") == 0) {
					echo "<td class=\"out\"><img src=\"/images/symbols/init_exclude.gif\" width=\"32\" height=\"32\" title=\"Initially Excluded\" /></td>\n";
				}
							
				// print "forfeited" cell
				elseif (strcasecmp($status, "forfeited") == 0) {
					echo "<td class=\"out\"><img src=\"/images/symbols/forfeit.gif\" width=\"32\" height=\"32\" title=\"Forfeited in Week $week_removed\" /></td>\n";
				}

				// print "picked and won" cell
				elseif (strcasecmp($status, "picked_and_won") == 0) {
					echo "<td class=\"out\"><img src=\"/images/symbols/win.gif\" width=\"32\" height=\"32\" title=\"Picked in Week $week_removed and Won\" /></td>\n";
				}
							
				// print "picked and lost" cell
				elseif (strcasecmp($status, "picked_and_lost") == 0) {
					echo "<td class=\"out\"><img src=\"/images/symbols/lose.gif\" width=\"32\" height=\"32\" title=\"Picked in Week $week_removed and Lost\" /></td>\n";
				}
							
				// print "current pick" cell
				elseif (strcasecmp($status, "current_pick") == 0 && ( strcasecmp($username, $_SESSION['username']) == 0 || strcasecmp($_SESSION['user_type'],"admin") == 0)) {
					echo "<td class=\"out\"><img src=\"/images/symbols/current.gif\" width=\"32\" height=\"32\" title=\"Current Pick (Week $week_removed)\" /></td>\n";
				}
			
				// print cell with no status
				else {
					echo "<td class=\"avail\"><img src=\"/images/symbols/inpool.gif\" width=\"32\" height=\"32\" title=\"Available\" /></td>\n";
				}
						}
				echo "</tr>\n";
			}
	
			// table end
			echo "</table>\n";
	
		}
	
	echo "<p> A gray cell indicates a team that is no longer in a player's pool. The icon in the cell ";
	echo "indicates the reason the team has been removed, as follows: </p>\n";
	
	echo "<table class=\"pool\">\n";
	echo "<caption>Legend</caption>\n";
	echo "<tr><th>Symbol</th><th>Meaning</th></tr>\n";
	echo "<tr><td><img src=\"/images/symbols/init_exclude.gif\" width=\"64\" height=\"64\" alt=\"init_exclude.gif\" /></td><td> Initially Excluded</td></tr>\n";
	echo "<tr><td><img src=\"/images/symbols/forfeit.gif\" width=\"64\" height=\"64\" alt=\"forfeit.gif\" /></td><td> Forfeited</td></tr>\n";
	echo "<tr><td><img src=\"/images/symbols/inpool.gif\" width=\"64\" height=\"64\" alt=\"inpool.gif\" /></td><td> Still Available in Pool</td></tr>\n";
	echo "<tr><td><img src=\"/images/symbols/win.gif\" width=\"64\" height=\"64\" alt=\"win.gif\" /></td><td> Selected and Won</td></tr>\n";
	echo "<tr><td><img src=\"/images/symbols/lose.gif\" width=\"64\" height=\"64\" alt=\"lose.gif\" /></td><td> Selected and Lost</td></tr>\n";
	echo "<tr><td><img src=\"/images/symbols/current.gif\" width=\"64\" height=\"64\" alt=\"current.gif\" /></td><td> Current Pick </td></tr>\n";
	
	echo "</table>\n";
	
	echo "</body>\n</html>";
?>