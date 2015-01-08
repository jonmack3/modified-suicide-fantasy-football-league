<?php
	session_start();
	ob_start();

	unset($_SESSION['username']);
	unset($_SESSION['user_type']);
	unset($_SESSION['first_name']);
	unset($_SESSION['last_name']);
	unset($_SESSION['handle']);

	header("Location: index.php");
	
	ob_end_flush();
?>