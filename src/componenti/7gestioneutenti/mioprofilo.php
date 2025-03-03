<?php
/*

	controller to manage my own user data

*/

$root="../../../";
include($root."src/_include/config.php");
include($root."src/_include/formcampi.class.php");
include("../gestioneutenti/_include/user.class.php");
include("../gestioneutenti/_include/gestioneutenti.class.php");
include("../gestioneutenti/_include/mioprofilo.class.php");
include("_include/BANNER.gestioneutenti.class.php");
include("_include/BANNER.mioprofilo.class.php");

print $ambiente->setPosizione( "{My profile}" );

$io = new BANNER_MioProfilo();

$html="";

if (isset($_GET["op"])) {
	$command = $_GET["op"];
	if (isset($_GET["id"])) $parameter = $_GET["id"]; else $parameter="";
} else if (isset($_POST["op"])) {
	$command = $_POST["op"];
	if (isset($_POST["id"]))	$parameter = $_POST["id"]; else $parameter="";
}

if(!isset($command) || $command=="") {$command = "modifica"; }
if(!isset($parameter) || $parameter=="") {$parameter = $session->get("idutente"); }

if (isset($command)) {
	switch ($command) {
	case "modifica":
		$risultato = $io->getDettaglio();
		
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} else $html = $risultato;
		break;
	case "modificaStep2":
		if($_SERVER['HTTP_HOST']!="www.barattalo.it" && PONSDIR=="/ambdemo") {
			$html = returnmsg("{This is a demo version, you can't do that.}","jsback");
		} else {
			$risultato = $io->update($_POST,$_FILES);
			if ($risultato=="0") {
				$html = returnmsg("{You're not authorized.}","jsback");
			} elseif($risultato=="2") {
				$html = returnmsg("{Not a valid email.}","jsback");
			} elseif($risultato=="1") {
				$html = returnmsg("{Email already used.}","jsback");
			} else $html = returnmsgok("{Done.}","reload");
			break;
		}
	}

}

print translateHtml($html);
