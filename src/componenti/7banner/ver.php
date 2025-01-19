<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
	// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
	// you want to allow, and if so:
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		// may also be using PUT, PATCH, HEAD etc
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
	
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	exit(0);
}

$public = true;
$root="../../../";
include($root."src/_include/config.php");


echo $defaultReplace["##VER##"];

if(
    isset($_REQUEST['from'])) {

		// version check
		if(isset($_GET['ver'])) {

			if(isset($_GET['ver'])) $ver = strip_tags($_GET['ver']); else $ver = "";
			if(isset($_GET['stats'])) $stats = preg_replace("/[^0-9\|]/","",$_GET['stats']); else $stats = "";
			if($stats=="") $stats = "0|0";
			$x = explode("|",$stats);
			$stats = "";
			foreach($x as $v) {
				$stats = ( $stats != "" ? "|" : "" ) . (integer)$v;
			}
			if($stats[0]=="|") $stats="0".$stats;

			$url = str_replace(basename($_GET['from']),"",$_GET['from'] );
			$conn->query($sql = "update ".DB_PREFIX."installations set dt_saved=NOW(),MV=".explode("|",$stats)[0].",BA=".explode("|",$stats)[1].",de_ver='".addslashes($ver)."' WHERE de_referrer='".addslashes($url)."'");
			if($conn->affected_rows==0) {
				$conn->query("insert ignore into ".DB_PREFIX."installations (de_referrer,dt_saved,de_ver,MV,BA) values ('".addslashes($url)."',NOW(),'".addslashes($ver)."',".explode("|",$stats)[0].",".explode("|",$stats)[1].")");
			}

		}

		// integrity check
		if(isset($_POST['ver']) && isset($_POST['from']) && filter_var($_POST['from'], FILTER_VALIDATE_URL) !== false) {

			$url = $_POST['from'];

			if(isset($_POST['pc'])) {
				$pc = strip_tags($_POST['pc']);
				$de_referrer = execute_scalar( "SELECT de_referrer FROM ".DB_PREFIX."installations WHERE pc = '".addslashes($pc)."'","");
				if($de_referrer == $url || $de_referrer == "") {
					$conn->query("update ".DB_PREFIX."installations set pc='".addslashes($pc)."' WHERE de_referrer='".addslashes($url)."'");
					echo " O"."K^"."^";
				} else {
					echo " ERROR";
					die;
				}
			}

			if(isset($_POST['pu'])) {
				$pu = strip_tags($_POST['pu']);
				$conn->query("update ".DB_PREFIX."installations set pu='".addslashes($pu)."' WHERE de_referrer='".addslashes($url)."'");
			}
			if(isset($_POST['pd'])) {
				$pd = date("Y-m-d H:i:s", strtotime($_POST['pd']));
				$conn->query("update ".DB_PREFIX."installations set pd='".addslashes($pd)."' WHERE de_referrer='".addslashes($url)."'");
			}

		}

}