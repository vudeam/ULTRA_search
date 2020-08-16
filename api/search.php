<?php
	require "setup.php";
	header("Content-type: application/json");

	$CardsDB = new DBConnector($CFGHostAddr, $CFGUsername, $CFGPassword, $CFGDBName);
	$Response = array();
	
	$CardsDB->Connect();
	$Fields = array("EDT", "NUMBER", "ENG_TITLE", "COLOR", "FOIL");
	$Dictionary = array(
		"EDT"       => "set",
		"NUMBER"    => "num",
		"ENG_TITLE" => "name",
		"COLOR"     => "color",
		"FOIL"      => "isFoil"
	);
	$CardsDB->SetFields($Fields, $Dictionary);
	if(isset($_GET["name"])) {
		$n = $CardsDB->FetchSelect("%".$_GET["name"]."%", 2);
		if ($CardsDB->error_code != DBC_CONN_SUCCESS) die("Connecction error ".$CardsDB->error_code);
		$Response["object"] = "cards_list";
		$Response["success"] = $CardsDB->error_code === DBC_CONN_SUCCESS;
		$Response["data"] = $CardsDB->rows;
		echo json_encode($Response, JSON_PRETTY_PRINT);
	}
	else {
		$Response["object"] = "error";
		$Response["description"] = "Search parameter is not set.";
		echo json_encode($Response, JSON_PRETTY_PRINT);
	}
	
?>
