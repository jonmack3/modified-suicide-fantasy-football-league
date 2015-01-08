<?php
	// connects to a mySQL database, returns link, prints error message if unable
	function link_connect()
	{
		$link = @mysql_pconnect("", "", "")
			or die("Could not connect to MySQL server");
		@mysql_select_db("") or die("Could not open database");
		return $link;
	}

	// connects to a mySQL database, printing error message if unable
	function connect()
	{
		@mysql_pconnect("", "", "")
			or die("Could not connect to MySQL server");
		@mysql_select_db("") or die("Could not open database");
	}

	// compares username and password, returns user type if they match, blank otherwise
	function compare($username, $password)
	{
		$query = "SELECT username, password, user_type from users WHERE username = 
			'$username' and password = '$password'";
		$result = mysql_query($query);
		if (mysql_num_rows($result) == 1) $user_type = mysql_result($result, 0, "user_type");
		else if (mysql_num_rows($result) == 2) $user_type = "B";
		else $user_type = "";
		return strtoupper($user_type);
	}
	
	// closes the database
	function close_db()
	{
		mysql_close();
	}
	
	// validates that no [:punct:] characters are present, and that string is not null or > 20 characters
	function validate($string)
	{
		$valid = true;
		if (preg_match("/[[:punct:]]/", $string) || strlen($string) == 0 ||
			strlen($string) > 20)
		{
			$valid = false;
		}
		return $valid;
	}
	
	// creates a header using a user-defined title, keywords, and description
	function create_header($title, $keywords, $description)
	{
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" 
			\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">
				<head profile=\"http://www.w3.org/2005/10/profile\">
					<link rel=\"icon\" type=\"image/gif\" href=\"/images/layout/favicon.gif\" />
					<title>$title</title>
					<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" />
					<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />
					<meta name=\"keywords\" content=\"$keywords\" />
					<meta name=\"description\" content=\"$description\" />
				</head>\n";
	}
	
	/* Creates a text form element. The parameter should be capitalized, as ': ' 
	is appended to it to form the title of the label. The ID of the label
	is formed by changing all uppercase to lowercase, and all ' ' to '_'. */
	function create_text_form_elements($labels)
	{
		foreach ($labels as $label)
		{
			$id = str_replace(" ", "_", strtolower($label));
			echo "<p><label for=\"$id\">$label: </label>
			<input type=\"text\" id=\"$id\" name=\"$id\" /></p>\n";
		}
	}
	
	/* Creates a password text form element. The parameter should be capitalized, as ': ' 
	is appended to it to form the title of the label. The ID of the label
	is formed by changing all uppercase to lowercase, and all ' ' to '_'. */
	function create_pw_form_elements($labels)
	{
		foreach ($labels as $label)
		{
			$id = str_replace(" ", "_", strtolower($label));
			echo "<p><label for=\"$id\">$label: </label>
			<input type=\"password\" id=\"$id\" name=\"$id\" />\n</p>\n";
		}
	}
	
	/* Creates checkbox form elements. $labels should be formatted to appear
	as they would in the html output; ' ' are changed to '_' and uppercase to 
	lowercase for IDs.*/
	function create_checkboxes_form_elements($labels)
	{
		foreach ($labels as $label)
		{
			$id = str_replace(" ", "_", strtolower($label));
			echo "<p><label for=\"$id\">
			<input type=\"checkbox\" id=\"$id\" name=\"$id\" />
			$label</label>\n</p>\n";
		}
	}
	
	/* Creates pre-checked checkbox form elements. $labels should be formatted to appear
	as they would in the html output; ' ' are changed to '_' and uppercase to 
	lowercase for IDs.*/
	function create_mv_boxes($labels, $name)
	{
		foreach ($labels as $label)
		{
			$id = str_replace(" ", "_", strtolower($label));
			echo "<p><label for=\"$id\">
			<input type=\"checkbox\" name=\"".$name."[]\" id=\"$id\"  value=\"$id\"/>
			$label</label>\n</p>\n";
		}
	}	
	
	/* Creates pre-checked checkbox form elements. $labels should be formatted to appear
	as they would in the html output; ' ' are changed to '_' and uppercase to 
	lowercase for IDs.*/
	function create_checked_mv_boxes($labels, $name)
	{
		foreach ($labels as $label)
		{
			$id = str_replace(" ", "_", strtolower($label));
			echo "<p><label for=\"$id\">
			<input type=\"checkbox\" name=\"".$name."[]\" id=\"$id\"  value=\"$id\" checked=\"checked\"/>
			$label</label>\n</p>\n";
		}
	}
	
	/* Creates radio button form elements. $labels should be formatted to
	appear as they would in the html output; ' ' are changed to '_' and uppercase
	to lowercase for IDs. */
	function create_radio_form_elements($labels, $name)
	{
		foreach ($labels as $label)
		{
			$id = str_replace(" ", "_", strtolower($label));
			echo "<p><label for=\"".$id."\">
			<input type=\"radio\" name=\"$name\" id=\"$id\" value=\"$id\" />
			$label</label>\n</p>\n";
		}
	}
	
	/* Creates option form elements. $labels should be formatted to
	appear as they would in the html output; ' ' are changed to '_' and uppercase
	to lowercase for IDs. */
	function create_option_form_elements($labels)
	{
		foreach ($labels as $label)
		{
			echo "<option>$label</option>\n";
		}
	}
?>