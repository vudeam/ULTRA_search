<?php
	echo "<code>Hello there</code>";
	require "setup.php";

	$o = new DBConnector($CFGHostAddr, $CFGUsername, $CFGPassword, $CFGDBName);
	$o->Connect() or die("Connection failed!");
	$o->SetFields(array(
		"EDT",
		"NUMBER",
		"ENG_TITLE",
		"QUANTITY"
	));
	echo $o->FetchSelect();
	
?>