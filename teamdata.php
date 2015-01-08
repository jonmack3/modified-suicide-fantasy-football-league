<?php

	/* This function takes in a team_id and returns a three element array
	of wins, losses, and ties -- in that order. */
	function get_record($team_id) {
		$query_wins = "SELECT 1 FROM season 
			WHERE (home_team_id='$team_id' AND home_score > away_score)
			OR (away_team_id='$team_id' AND away_score > home_score)";
		$result_wins = mysql_query($query_wins);
		$team_wins = mysql_num_rows($result_wins);
		mysql_free_result($result_wins);
		
		$query_losses = "SELECT 1 FROM season
			WHERE (home_team_id='$team_id' AND home_score < away_score)
			OR (away_team_id='$team_id' AND away_score < home_score)";
		$result_losses = mysql_query($query_losses);
		$team_losses = mysql_num_rows($result_losses);
		mysql_free_result($result_losses);
		
		$query_ties = "SELECT 1 FROM season
			WHERE (home_team_id='$team_id' OR away_team_id='$team_id')
			AND (away_score = home_score) AND (away_score IS NOT NULL)"; 
		$result_ties = mysql_query($query_ties);
		$team_ties = mysql_num_rows($result_ties);
		mysql_free_result($result_ties);
		
		$rec = array();
		$rec[] = $team_wins;
		$rec[] = $team_losses;
		$rec[] = $team_ties;
		
		return $rec;
	}
	
	// This function takes in a team_id and returns an image source string:
	// <img src=.... />
	// which will display in a square of px pixels. (default is 32 pixels)
	function team_img($team_id, $px = 32) {
		$img_str = "<img src=\"images/teams/".$team_id.".gif\"";
		$img_str = $img_str." height=\"".$px."\"";
		$img_str = $img_str." height=\"".$px."\"";
		
		$query = "SELECT team_name, team_city FROM teams WHERE team_id = '$team_id'";
		$result = mysql_query($query);
		$name = mysql_result($result, 0, team_name);
		$city = mysql_result($result, 0, team_city);
		mysql_free_result($result);
		
		$team_record = get_record($team_id);
		
		$img_str = $img_str." title=\"".$city." ".$name;
		$img_str = $img_str." (".$team_record[0]."-".$team_record[1]."-".$team_record[2].")";
		$img_str = $img_str."\" />";
		
		return $img_str;
	}
?>