<?php
/*

	controller for the users manager

*/


$root="../../../";

//
// require classes to send mail with SMTP
require $root.'src/componenti/PHPMailer/src/Exception.php';
require $root.'src/componenti/PHPMailer/src/PHPMailer.php';
require $root.'src/componenti/PHPMailer/src/SMTP.php';

//
include($root."src/_include/config.php");
include($root."src/_include/grid.class.php");
include($root."src/_include/formcampi.class.php");
include("../gestioneutenti/_include/user.class.php");
include("../gestioneutenti/_include/gestioneutenti.class.php");
include("../gestioneutenti/_include/grid_callbacks.php");
include("_include/BANNER.gestioneutenti.class.php");

print $ambiente->setPosizione( "{Users}" );

$gu = new BANNER_gestioneutenti("frw_utenti",40,"name","asc",0);

if (isset($ARRAY_EXTRA_USER_LABELS)) $gu->scegliDaInsiemeLabelProfili=$ARRAY_EXTRA_USER_LABELS;

$html="";

$command = getpost("op", null);
$parameter = getpost("id", null);
$combotipo = get("combotipo","");
$combotiporeset = get("combotiporeset","");
$keyword = get("keyword","");

switch ($command) {
	case "modifica":
		$risultato = $gu->getDettaglioNew( $parameter );
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} else $html = $risultato;
		break;
	case "modificaStep2" :
		$risultato = $gu->updateAndInsert($_POST);
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else {
			if ($command != "modificaStep2reload") $html = returnmsgok("{Done.}","reload");
				else $html = returnmsgok("{Done.}","load ".$_SERVER['SCRIPT_NAME']."?op=modifica&id={$parameter}");
		}
		break;
	case "eliminaSelezionati":
		$risultato = $gu->eliminaSelezionati($_POST);
		if ($risultato=="0" || $risultato == "2") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} else $html = returnmsgok("{Deleted.}","load ".$_SERVER['SCRIPT_NAME']."");
		break;
	case "ic":
		$risultato = $gu->getIntegrityCheckForm();
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} else $html = $risultato;
		break;
	case "icStep2":
		$risultato = $gu->setIntegrityCheckData($_POST);
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		}  elseif($risultato=="1") {
			$html = returnmsgok("{Code is valid, thank you for purchase!}","link ../7banner/index.php");
		}  elseif($risultato=="-1") {
			$msg = translateHtml("{Sorry, we cannot find your purchase code.<br>Please check your emails from CodeCanyon to find you purchase code and try again.<br>Or <a target='_blank' href='%s'>click here</a> to find your code on Codecanyon.}");
			$msg = str_replace("%s", "https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code", $msg);
			$html = returnmsg($msg,"jsback");
		}
		break;		
	case "aggiungi":
		$risultato = $gu->getDettaglioNew();
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} else $html = $risultato;
		break;
	case "aggiungiStep2":
		$risultato = $gu->updateAndInsert($_POST);
		if ($risultato=="0") {
			$html = returnmsg("{You're not authorized.}","jsback");
		} elseif(str_replace(strstr($risultato,"|"),"",$risultato)=="-1") {
			$html = returnmsg(str_replace("|","",strstr($risultato,"|")),"jsback");
		} else {
			$id = str_replace( "|","",stristr( $risultato, "|")) ; 
			if ($command != "aggiungiStep2reload") $html = returnmsgok("{Done.}","reload");
				else $html = returnmsgok("{Done.}","load ".$_SERVER['SCRIPT_NAME']."?op=modifica&id=".$id."");
		}
		break;
	case "personifica":
		$user = execute_row("SELECT username, password FROM frw_utenti where id='$parameter'");
		if($user && in_array( $session->get("idprofilo"), array(20,999999) )) {
			$cr = new Cryptor();
			$login->actionurl = $root."src/login.php";
			$out = $login->getLoginForm("Autologin");
			$out = str_replace('<input name="password" type="password"','<input name="password" type="password" value="'.$cr->decrypta($user['password']).'"',$out);
			$out = str_replace('<input name="utente"','<input name="utente" value="'.$user['username'].'"',$out);
			$out = str_replace('</form>','</form><script>document.getElementById("loginform").submit();</script>',$out);
			$logger->addlog( 2 , "{fine sessione utente ".$session->get("username").", id=".$session->get("idutente")."}" );
			$session->finish();
			echo $out;
		} 
		die;
}



if ($html=="") {
	$html = loadTemplateAndParse ("../gestioneutenti/template/elenco.html");
	$elenco = $gu->elencoUtenti($combotipo,$combotiporeset,$keyword);
	if($elenco!="0") {
		$html = str_replace("##corpo##", ($elenco), $html);
		$html = str_replace("##keyword##", $keyword, $html);
		$html = str_replace("##bottoni2##","<a href=\"$gu->linkeliminamarcate\" title=\"{Delete selected items}\" class='elimina'></a>", $html);
		$html = str_replace("##combotipo##", $gu->getHtmlcombotipo($combotipo), $html);
		if ($session->get("GESTIONEUTENTI_WRITE")=="true") {
			if( in_array( $session->get("idprofilo"), array(20,999999) )) {
				$html = str_replace("##aggiungi##","<a href=\"$gu->linkaggiungi\" class='aggiungi' title=\"{Add new item}\"></a>",$html);
			} else {
				$html = str_replace("##aggiungi##","",$html);
			}
		}
	} else {
		$url = getDefaultComponentAddress();
		$html = returnmsg ("{You're not authorized.}","link ../../".$url);
	}
}

print translateHtml($html);
