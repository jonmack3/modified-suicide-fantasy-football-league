<?php
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