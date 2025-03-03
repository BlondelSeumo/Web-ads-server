<?php
/*
	class to manage install process
*/

class install {

	var $gestore;


	function __construct () {
		global $root;
		$this->gestore = $_SERVER["PHP_SELF"];
	}

	/*
		mysql login data form
	*/
	function getDettaglioMysql() {

		global $root, $conn;

			/*
				modify
			*/
			$action = "modificaStep2";
			$html = loadTemplateAndParse("template/dettaglio.html");

			// build form
			$objform = new form();

			$host = new testo("host",WEBDOMAIN,40,30);
			$host->obbligatorio=1;
			$host->label="'{Host}'";
			$objform->addControllo($host);

			$dbname = new testo("dbname",DEFDBNAME,40,30);
			$dbname->obbligatorio=1;
			$dbname->label="'{Database}'";
			$objform->addControllo($dbname);

			$username = new testo("username",DEFUSERNAME,40,30);
			$username->obbligatorio=1;
			$username->label="'{Username}'";
			$objform->addControllo($username);

			$cr = new Cryptor();
			$password = new testo("password",DEFDBPWD,40,30);
			$password->obbligatorio=1;
			$password->label="'{Password}'";
			$objform->addControllo($password);

			$lang = new optionlist("lang","en", array(
					"en"=> "English",
					"it"=> "Italiano",
				));
			$lang->obbligatorio=0;
			$lang->label="'{Language}'";
			$objform->addControllo($lang);

			$theme = new optionlist("theme","basic_theme", array(
					"basic_theme"=> "Basic theme",
					"deepblue_theme"=> "Deep blue theme",
				));
			$theme->obbligatorio=0;
			$theme->label="'{Theme}'";
			$objform->addControllo($theme);

			$op = new hidden("op",$action);

			if( Connessione() ) {
				if(table_exists(DB_PREFIX."frw_vars")) {
					// UPDATE
					$html = str_replace("##ISUPDATE##", "", $html);
					$html = str_replace("##ISINSTALL##", " style='display:none' ", $html);

				} else {
					// INSTALL (ho i dati)
					$html = str_replace("##ISUPDATE##", "", $html);
					$html = str_replace("##ISINSTALL##", " style='display:none' ", $html);

				}
			
			} else {
				// INSTALL (missing mysql data)
				$html = str_replace("##ISUPDATE##", " style='display:none' ", $html);
				$html = str_replace("##ISINSTALL##", "", $html);

			}


			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##op##", $op->gettag(), $html);
			$html = str_replace("##username##", $username->gettag(), $html);
			$html = str_replace("##password##", $password->gettag(), $html);
			$html = str_replace("##dbname##", $dbname->gettag(), $html);
			$html = str_replace("##host##", $host->gettag(), $html);
			//$html = str_replace("##email##", $email->gettag(), $html);
			$html = str_replace("##lang##", $lang->gettag(), $html);
			$html = str_replace("##theme##", $theme->gettag(), $html);
			//$html = str_replace("##envato##", $envato->gettag(), $html);
			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);



		return $html;
	}

	function update($arDati) {
		global $root,$conn;

		$html = "";


		if( !Connessione()) {
			// INSTALL file settings
			$full_path_to_file = $root."pons-settings.php";


			//
			// save data in pons.settings.php
			if(file_exists($full_path_to_file) && isset($arDati['host'])) {

				if(Connessione($arDati['host'], $arDati['username'], $arDati['password'], $arDati['dbname'])) {

					if(!is_writable($full_path_to_file)) @chmod($full_path_to_file, 0755); 
					if(!is_writable($full_path_to_file)) die("<pre>" . $full_path_to_file. " not writeable.</pre>");

					$contents0 = file_get_contents( $full_path_to_file );
					$contents = preg_replace( "/define\(\"WEBDOMAIN\",\"([^\"]*)\"\);/", "define(\"WEBDOMAIN\",\"".$arDati['host']."\");", $contents0 );
					$contents = preg_replace( "/define\(\"DEFDBNAME\",\"([^\"]*)\"\);/", "define(\"DEFDBNAME\",\"".$arDati['dbname']."\");", $contents );
					$contents = preg_replace( "/define\(\"DEFUSERNAME\",\"([^\"]*)\"\);/", "define(\"DEFUSERNAME\",\"".$arDati['username']."\");", $contents );
					$contents = preg_replace( "/define\(\"DEFDBPWD\",\"([^\"]*)\"\);/", "define(\"DEFDBPWD\",\"".$arDati['password']."\");", $contents );
					$contents = preg_replace( "/define\(\"LANGUAGEFILE\",\"([^\"]*)\"\);/", "define(\"LANGUAGEFILE\",\"".$arDati['lang'].".lang.txt\");", $contents );

					$contents = preg_replace( "/define\(\"DOMINIODEFAULT\",\"([^\"]*)\"\);/", "define(\"DOMINIODEFAULT\",\"".$arDati['theme']."\");", $contents );

					file_put_contents( $full_path_to_file, $contents );

					if($contents0 == $contents) die("<pre>" ."Cant' find config strings in pons-settings.php"."</pre>");

					echo "<script>document.location.href='".$root."src/componenti/install/index.php?op=modificaStep2&rnd=".rand(1,1111)."';</script>";
					die; // refresh

				} else {
					return "1";
				}

			} else {
				if (isset($arDati['host'])) {
					die("file ".$full_path_to_file." not found.");
				} else {
					// go to db update
					echo "<pre>Reloading...</pre><script>setTimeout(function(){document.location.href='".$root."src/componenti/install/index.php?op=modificaStep2&rnd3=".rand(1,1111)."';},1000);</script>";
					die;
				}
			}
		} else {

			if(!table_exists(DB_PREFIX."frw_vars")) {
				// INSTALL (data ok)
				$this->sql1();
			} 
			
			if(table_exists(DB_PREFIX."frw_vars")) {
				// UPDATES
				$this->sql2();

				$this->sql3();

				$this->sql4();

				$this->sql5(); //392

				$this->sql6(); //393

				$this->sql7(); 

				$this->sql395(); 

				$this->sql396();
				
				$this->sql397();

				$this->sql398();

				$this->sql398b();
				$this->sql398d();
				$this->sql398i();
				$this->sql398j();
				$this->sql398k();
				$this->sql3991();
				$this->sql3993a();
				$this->sql3993c();
				$this->sql3994();
				$this->sql3995();
				$this->sql3996();
                $this->sql3997();
				$this->sql4000();
				$this->sql4004();
                $this->sql4100();
				$this->sql420();
				$this->sql421();
				$this->sql421c();
				$this->sql422();
				$this->sql423();
				$this->sql424();
				$this->sql426();
				$this->sql427();
				$this->sql428();
				$this->sql428b();

			}


			if(!file_exists($root."data/dbimg/media") && file_exists($root."data/dbimg/demofiles")) {
				// move folder demo contents
				renamebetter($root."data/dbimg/demofiles",$root."data/dbimg/media");
			}

			
			if(!file_exists($root."data/dbimg/media") && file_exists($root."data/dbimg/7banner")) {
				// fix old version
				renamebetter($root."data/dbimg/7banner",$root."data/dbimg/media");
			}

			// remove lock file
			$lockupdate = $root. str_replace(basename(LOGS_FILENAME),"lock.txt", LOGS_FILENAME);
			if(file_exists($lockupdate)) {
				unlinkbetter($lockupdate);
			}

			$pons_install = $root. "pons-settings-install.php";
			if(file_exists($pons_install)) {
				unlinkbetter($pons_install);
			}


			echo "<script>document.location.href='".$root."src/logout.php?rnd=".rand(1,111111)."';</script>";
			die;


		}


		return $html;
	}



	function sql1 () {
		global $conn;

		$a[] ="SET sql_mode = '';";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner` (
		  `id_banner` int(11) unsigned NOT NULL auto_increment,
		  `de_url` varchar(255) NOT NULL default '',
		  `dt_giorno1` date NOT NULL default '".ZERODATE."',
		  `dt_giorno2` date NOT NULL default '".ZERODATE."',
		  `nu_pageviews` int(11) unsigned NOT NULL default '0',
		  `fl_stato` char(1) NOT NULL default '',
		  `nu_clicks` int(11) unsigned NOT NULL default '0',
		  `de_nome` varchar(100) NOT NULL default '',
		  `de_codicescript` text NOT NULL,
		  `de_target` enum('_blank','_self') NOT NULL default '_blank',
		  `nu_maxtot` int(11) NOT NULL default '0' COMMENT 'massime impression totali',
		  `nu_maxday` int(11) NOT NULL default '0' COMMENT 'massime impression al giorno',
		  `dt_maxday_date` date NOT NULL default '".ZERODATE."',
		  `nu_maxday_count` int(11) NOT NULL default '0',
		  `cd_campagna` int(11) NOT NULL,
		  `cd_posizione` int(11) NOT NULL,
		  `nu_width` int(11) NOT NULL,
		  `nu_height` int(11) NOT NULL,
		  `nu_price` decimal(19,4) NOT NULL DEFAULT '0.0000',
		  PRIMARY KEY  (`id_banner`),
		  KEY `dt_giorno2` (`dt_giorno2`),
		  KEY `stato_giorno1_formato` (`fl_stato`,`dt_giorno1`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_campagne` (
		  `id_campagna` int(11) NOT NULL AUTO_INCREMENT,
		  `de_titolo` varchar(100) NOT NULL,
		  `cd_cliente` int(11) NOT NULL,
		  `fl_status` tinyint(1) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id_campagna`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."7banner_campagne` (`id_campagna`, `de_titolo`, `cd_cliente`, `fl_status`) VALUES
		(9, 'Mycampaign', 7, 1);";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_clienti` (
		  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
		  `de_nome` varchar(100) NOT NULL,
		  PRIMARY KEY (`id_cliente`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."7banner_clienti` (`id_cliente`, `de_nome`) VALUES
		(7, 'Myself');";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_clienti_tbc` (
		  `cd_utente` int(11) NOT NULL,
		  `cd_cliente` int(11) NOT NULL,
		  PRIMARY KEY (`cd_utente`,`cd_cliente`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Collega utenti a clienti';";

		$a[] = "INSERT INTO `".DB_PREFIX."7banner_clienti_tbc` (`cd_utente`, `cd_cliente`) VALUES
		(34, 7);";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_posizioni` (
		  `id_posizione` int(11) NOT NULL auto_increment,
		  `de_posizione` varchar(20) NOT NULL,
		  `nu_width` int(11) NOT NULL,
		  `nu_height` int(11) NOT NULL,
		  PRIMARY KEY  (`id_posizione`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."7banner_posizioni` (`id_posizione`, `de_posizione`,`nu_width`,`nu_height`) VALUES
		(9, '300x250',300,250);";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_stats` (
		  `id_day` date NOT NULL,
		  `nu_pageviews` int(10) unsigned NOT NULL,
		  `nu_click` int(10) unsigned NOT NULL,
		  `cd_banner` int(11) NOT NULL,
		  PRIMARY KEY (`id_day`,`cd_banner`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_componenti` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `nome` varchar(100) NOT NULL DEFAULT '',
		  `descrizione` varchar(255) DEFAULT NULL,
		  `urlcomponente` varchar(255) NOT NULL DEFAULT '',
		  `label` varchar(100) NOT NULL DEFAULT '',
		  `urliconamenu` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='dati dei componenti' AUTO_INCREMENT=190 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_componenti` (`id`, `nome`, `descrizione`, `urlcomponente`, `label`, `urliconamenu`) VALUES
		(1, 'DEBUGGER', 'Debug tool.', 'componenti/debugger/test.php', 'debugger', 'componenti/debugger/images/debugger_ico.gif'),
		(5, 'GESTIONEUTENTI', 'User manager', 'componenti/gestioneutenti/index.php', 'Users', 'componenti/gestioneutenti/images/gestioneutenti_ico.gif'),
		(12, 'FRWCOMPONENTI', 'per gestire l''installazione/rimozione di funzionalita e componenti', 'componenti/frwcomponenti/index.php', 'Componenti', 'componenti/frwcomponenti/images/ico.gif'),
		(14, 'FRWMODULI', 'Per la creazione di nuovi moduli', 'componenti/frwmoduli/index.php', 'Moduli', 'componenti/frwmoduli/images/ico.gif'),
		(15, 'FRWPROFILI', 'Gestione dei profili degli utenti del sistema', 'componenti/frwprofili/index.php', 'Profili utenti', 'componenti/frwprofili/images/ico.gif'),
		(58, 'MIOPROFILO', 'Gestione cambio password e altri miei dati', 'componenti/gestioneutenti/mioprofilo.php', 'Edit my profile', 'componenti/gestioneutenti/images/gestioneutenti_ico.gif'),
		(61, 'FRWVARS', 'Settaggi', 'componenti/frwvars/index.php', 'Settings more', 'icone/cog.png'),
		(72, 'BANNER', 'Banner', 'componenti/7banner/index.php', 'Banner', 'icone/shape_square.png'),
		(175, '7SETTINGS', 'Gestione impostazioni', 'componenti/7settings/index.php', 'Impostazioni', 'icone/cog.png'),
		(187, 'CAMPAGNE', 'Campaigns manager', 'componenti/7campagne', 'Campaigns', NULL),
		(188, 'CLIENTI', 'Clients manager', 'componenti/7clienti/index.php', 'Clients', NULL),
		(189, 'POSIZIONI', 'Positions manager', 'componenti/7posizioni/index.php', 'Positions', NULL),
		(194, 'CONSTANTSSETTINGS', 'Vars and settings', 'componenti/frwconstants/index.php', 'Settings', NULL);";



		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_com_mod` (
		  `idcomponente` int(11) NOT NULL DEFAULT '0',
		  `idmodulo` int(11) NOT NULL DEFAULT '0',
		  `posizione` tinyint(3) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY (`idcomponente`,`idmodulo`,`posizione`),
		  KEY `idcomponente` (`idcomponente`,`idmodulo`),
		  KEY `posizione` (`posizione`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_com_mod` (`idcomponente`, `idmodulo`, `posizione`) VALUES
		(14, 1, 0),
		(58, 1, 0),
		(61, 1, 0),
		(12, 1, 1),
		(72, 18, 1),
		(188, 18, 2),
		(187, 18, 3),
		(189, 18, 4),
		(5, 1, 6),
		(15, 1, 7),
		(194,1,0);";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_extrauserdata` (
		  `cd_user` int(10) unsigned NOT NULL DEFAULT '0',
		  `de_email` varchar(200) NOT NULL DEFAULT '',
		  `dt_datacreazione` date NOT NULL DEFAULT '".ZERODATE."',
		  `nu_costo` smallint(5) NOT NULL DEFAULT '0',
		  `de_temp` varchar(200) NOT NULL DEFAULT '',

		  PRIMARY KEY (`cd_user`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_extrauserdata` (`cd_user`, `de_email`, `dt_datacreazione`, `nu_costo`, `de_temp`) VALUES
		(36, '', '".date("Y-m-d")."', 0,'');";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_funzionalita` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idcomponente` int(11) NOT NULL DEFAULT '0',
		  `nome` varchar(100) NOT NULL DEFAULT '',
		  `descrizione` varchar(255) DEFAULT NULL,
		  `label` varchar(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`),
		  KEY `idcomponente` (`idcomponente`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='funzionalita dei componenti' AUTO_INCREMENT=214 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_funzionalita` (`id`, `idcomponente`, `nome`, `descrizione`, `label`) VALUES
		(1, 1, 'DEBUGGER', 'Debugger', 'Debugger tool for support'),
		(8, 5, 'READ', 'UTENTI possibilita di vedere gli utenti', 'lettura'),
		(9, 5, 'WRITE', 'UTENTI possibilita di modificare/aggiungere/togliere utenti', 'scrittura'),
		(20, 12, 'FRWCOMPONENTI', 'gestione componenti', 'gestione componenti'),
		(24, 14, 'FRWMODULI', 'Per abilitare la possibilita di gestire moduli', 'Gestione moduli'),
		(25, 15, 'FRWPROFILI', 'Per abilitare il componente che crea i profili', 'Per abilitare il componente che crea i profili'),
		(78, 58, 'MIOPROFILO', 'MIOPROFILO', 'MIOPROFILO'),
		(81, 61, 'FRWVARS', 'FRWVARS', 'FRWVARS'),
		(94, 72, 'BANNER', 'BANNER', 'BANNER'),
		(199, 175, '7SETTINGS', '7SETTINGS', '7SETTINGS'),
		(211, 187, 'CAMPAGNE', 'CAMPAGNE', 'CAMPAGNE'),
		(212, 188, 'CLIENTI', 'CLIENTI', 'CLIENTI'),
		(213, 189, 'POSIZIONI', 'POSIZIONI', 'POSIZIONI'),
		(218, 194, 'CONSTANTSSETTINGS', 'CONSTANTSSETTINGS', 'CONSTANTSSETTINGS');";





		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_moduli` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `nome` varchar(100) NOT NULL DEFAULT '',
		  `label` varchar(100) NOT NULL DEFAULT '',
		  `visibile` tinyint(3) unsigned NOT NULL DEFAULT '1',
		  `posizione` tinyint(3) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_moduli` (`id`, `nome`, `label`, `visibile`, `posizione`) VALUES
		(1, 'Handle config settings', 'Config', 1, 0),
		(18, 'Adv server menu', 'Ad Server', 1, 97);";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_profili` (
		  `id_profilo` int(3) unsigned NOT NULL DEFAULT '0',
		  `de_label` varchar(20) NOT NULL DEFAULT '0',
		  `de_descrizione` varchar(255) DEFAULT NULL,
		  `chiedita` varchar(100) DEFAULT NULL,
		  PRIMARY KEY (`id_profilo`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='profili degli utenti';";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_profili` (`id_profilo`, `de_label`, `de_descrizione`, `chiedita`) VALUES
		(20, 'administrator', 'administrator', ',20,5,15,'),
		(5, 'guest', 'guest', ''),
		(999999, 'superadmin', 'super', ',10,20,5,999999,15,16,4,');";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_profili_funzionalita` (
		  `cd_profilo` int(10) unsigned NOT NULL DEFAULT '999999',
		  `cd_modulo` int(10) unsigned NOT NULL DEFAULT '0',
		  `cd_funzionalita` int(10) unsigned NOT NULL DEFAULT '0',
		  KEY `cd_profilo` (`cd_profilo`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contiene le funzionalita da attivare per quel tipo di prof';";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES
		(999999, 1, 8),
		(999999, 1, 9),
		(20, 1, 1),
		(20, 1, 8),
		(20, 1, 9),
		(999999, 1, 20),
		(999999, 1, 24),
		(999999, 1, 25),
		(20, 1, 78),
		(5, 1, 78),
		(999999, 1, 78),
		(999999, 1, 81),
		(5, 18, 94),
		(20, 18, 94),
		(999999, 18, 212),
		(20, 18, 212),
		(999999, 18, 211),
		(20, 18, 211),
		(20, 18, 213),
		(999999, 18, 213),
		(999999, 18, 94),
		(999999, 1, 1),
		(20, 1, 1),
		(999999, 1, 1),
		(999999, 1, 218),
		(20, 1, 218);";


		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_utenti` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `username` varchar(20) NOT NULL DEFAULT '',
		  `password` varchar(60) NOT NULL DEFAULT '',
		  `nome` varchar(100) NOT NULL DEFAULT '',
		  `cognome` varchar(100) NOT NULL DEFAULT '',
		  `fl_attivo` int(1) unsigned NOT NULL DEFAULT '1',
		  `cd_profilo` int(10) unsigned NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='utenti dell''ambiente' AUTO_INCREMENT=37 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_utenti` (`id`, `username`, `password`, `nome`, `cognome`, `fl_attivo`, `cd_profilo`) VALUES
		(36, 'admin', 'BTIKMQEwXGIHYw==', 'Gengis', 'Kahn', 1, 20),
		(34, 'guest', 'BTQKIAE4XHgHeQ==', 'John', 'Snow', 1, 5);";

		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_ute_fun` (
		  `idutente` int(11) NOT NULL DEFAULT '0',
		  `idfunzionalita` int(11) NOT NULL DEFAULT '0',
		  `idmodulo` int(10) unsigned NOT NULL DEFAULT '0',
		  UNIQUE KEY `UNICO` (`idfunzionalita`,`idmodulo`,`idutente`),
		  KEY `idutente` (`idutente`,`idfunzionalita`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='collegamento utenti - funzionalita';";


		$a[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_vars` (
		  `id_var` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `de_nome` varchar(50) CHARACTER SET utf8 NOT NULL,
		  `de_value` text CHARACTER SET utf8 NOT NULL,
		  PRIMARY KEY (`id_var`),
		  UNIQUE KEY `label_unica` (`de_nome`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;";

		$a[] = "INSERT INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES
		(1, 'COLLATIONCONNECTIONQUERY', 'SET NAMES ''utf8'';'),
		(21, 'CREA_FUNZIONI_AUTOMATICAMENTE', '1'),
		(31, 'CONST_MONEY', '$'),
		(32, 'CONST_SERVER_EMAIL_ADDRESS', 'noreply@yourserver.com'),
		(33, 'CONST_DATEFORMAT', 'mm/dd/yyyy'),
		(34, 'CONST_LOGO', '/data/tema/thumb.jpg');";


		foreach ($a as $s) {

			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre> Remove all the tables and try again.<br><br>Error:<br><br><b>".$conn->error."</b>");

		}






			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_componenti (id ,nome ,descrizione ,urlcomponente ,label ,urliconamenu) VALUES ( '194','CONSTANTSSETTINGS','Vars and settings','componenti/frwconstants/index.php','Settings','')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_com_mod (idcomponente ,idmodulo,posizione) VALUES ( '194','1','0')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_funzionalita (id ,idcomponente ,nome ,descrizione ,label) VALUES ( '218','194','CONSTANTSSETTINGS','CONSTANTSSETTINGS','CONSTANTSSETTINGS')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_profili_funzionalita (cd_profilo ,cd_modulo ,cd_funzionalita) VALUES ( '999999','1','218')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_profili_funzionalita (cd_profilo ,cd_modulo ,cd_funzionalita) VALUES ( '20','1','218')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_vars (id_var,de_nome,de_value) VALUES ( 31,'CONST_MONEY','$')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_vars (id_var,de_nome,de_value) VALUES ( 32,'CONST_SERVER_EMAIL_ADDRESS','noreply@yourserver.com')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_vars (id_var,de_nome,de_value) VALUES ( 33,'CONST_DATEFORMAT','mm/dd/yyyy')");
			$conn->query("INSERT IGNORE INTO ".DB_PREFIX."frw_vars (id_var,de_nome,de_value) VALUES ( 34,'CONST_LOGO','/data/tema/thumb.jpg')");


			/* banner data creation */

			$a = array();
			$a[] = "INSERT INTO `".DB_PREFIX."7banner` (`id_banner`, `de_url`, `dt_giorno1`, `dt_giorno2`, `nu_pageviews`, `fl_stato`, `nu_clicks`, `de_nome`, `de_codicescript`, `de_target`, `nu_maxtot`, `nu_maxday`, `dt_maxday_date`, `nu_maxday_count`, `cd_campagna`, `cd_posizione`, `nu_width`, `nu_height`) VALUES
			(1, '', '2015-09-05', '2025-09-05', 29135, 'P', 0, 'Adsense', '<script type=\"text/javascript\"><!--\r\ngoogle_ad_client = \"ca-pub-8304104702162401\";\r\n/* banner 300 */\r\ngoogle_ad_slot = \"1198637355\";\r\ngoogle_ad_width = 300;\r\ngoogle_ad_height = 250;\r\n//-->\r\n</script>\r\n<script type=\"text/javascript\"\r\nsrc=\"//pagead2.googlesyndication.com/pagead/show_ads.js\">\r\n</script>', '_blank', 0, 0, '2020-11-24', 1, 15, 9, 300, 250),
			(44, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 115, 'A', 2, 'Test masthead responsive banner', '<a href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a>', '_blank', 0, 0, '2020-12-07', 6, 14, 27, -1, 250),
			(53, 'https://1.envato.market/adadmin', '2020-12-07', '2030-12-07', 1, 'L', 1, 'Test responsive HTML5 banner', '', '_blank', 0, 0, '2020-12-07', 1, 14, 27, -1, 250),
			(45, 'https://1.envato.market/adadmin', '2020-12-09', '2025-11-25', 190, 'A', 0, 'Test banner strip leaderboard', '<a href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a>', '_blank', 0, 0, '2020-12-01', 2, 14, 29, -1, 90),
			(46, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 189, 'A', 0, 'Test mpu fixed size', '', '_blank', 0, 0, '2020-12-07', 13, 14, 28, 300, 250),
			(47, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 192, 'A', 0, 'Test banner vertical', '', '_blank',  0, 0, '2020-12-07', 13, 14, 30, 240, 400),
			(48, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 93, 'A', 3, 'Test banner strip leaderboard into a masthead position', '<a href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a>', '_blank',  0, 0, '2020-12-07', 6, 14, 27, -1, 250),
			(49, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 180, 'L', 0, 'Test square banner', '', '_blank',  0, 0, '2020-12-07', 13, 14, 31, 250, 250),
			(50, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 80, 'L', 1, 'Leaderboard in a footer fixed way', '<style>#footerbanner[ID]A {position:fixed;bottom:0;transform: translate(-50%, 0%);left:50%;}\r\n.closemex[ID]A {position:absolute;top:-15px;right:-15px;text-decoration:none;font-size:15px;display:inline-block;width:30px;height:30px;line-height:30px;text-align:center;background-color:#000000;color:#ffffff;border-radius:50%}\r\n</style>\r\n<span id=\"footerbanner[ID]A\"><a href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a><a href=\"javascript:;\" class=''closemex[ID]A'' onclick=\"document.getElementById(''footerbanner[ID]A'').remove();\">X</a></span>', '_blank', 0, 0, '2020-12-07', 7, 14, 32, -1, 90),
			(51, 'https://1.envato.market/adadmin', '2020-11-25', '2025-11-25', 81, 'A', 2, 'Test overlay banner responsive', '<style>#overlaybanner[ID]A {position:fixed;top:50%;transform: translate(-50%, -50%);left:50%;}\r\n#overlaybanner[ID]A a.pic[ID]A {display:block;max-width:95vw;max-height:95vh;overflow:hidden}\r\n.closemex[ID]A {position:absolute;top:-15px;right:-15px;text-decoration:none;font-size:15px;display:inline-block;width:30px;height:30px;line-height:30px;text-align:center;background-color:#000000;color:#ffffff;border-radius:50%}\r\n</style>\r\n<span id=\"overlaybanner[ID]A\"><a class=''pic[ID]A'' href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a><a href=\"javascript:;\" class=''closemex[ID]A'' onclick=\"document.getElementById(''overlaybanner[ID]A'').remove();\">X</a></span>', '_blank', 0, 0, '2020-12-07', 6, 14, 32, -1, 90);";

			$a[] = "INSERT INTO `".DB_PREFIX."7banner_campagne` (`id_campagna`, `de_titolo`, `cd_cliente`, `fl_status`) VALUES
			(14, 'Self banners', 7, 1),
			(15, 'Adsense', 14, 1);";

			$a[] = "INSERT INTO `".DB_PREFIX."7banner_clienti` (`id_cliente`, `de_nome`) VALUES
			(14, 'Google');";

			$a[] = "INSERT INTO `".DB_PREFIX."7banner_posizioni` (`id_posizione`, `de_posizione`, `nu_width`, `nu_height`) VALUES
			(27, 'Masthead resp.', -1, 250),
			(28, 'MPU fixed size', 300, 250),
			(29, 'Leaderboard resp.', -1, 90),
			(30, 'Fat Skyscraper fixed', 240, 400),
			(31, 'Square box fixed', 250, 250),
			(32, 'Footer script', -1, 90);";



		foreach ($a as $s) {

			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre> Remove all the tables and try again.<br><br>Error:<br><br><b>".$conn->error."</b>");

		}



	}


	function sql2 () {
		global $conn;
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner' AND COLUMN_NAME = 'nu_maxclick'");
		if($q==0) {
			//echo "&gt; adding `nu_maxclick`.<br>";
			$sql = "ALTER TABLE  `".DB_PREFIX."7banner` ADD  `nu_maxclick` INT UNSIGNED NOT NULL default '0' COMMENT  'max click per day' AFTER  `nu_maxday` ;";
			$conn->query($sql) or die("Error while upgrading your DB for max click limit. ".$conn->error." sql='$sql'<br>");
		}
		
			
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner' AND COLUMN_NAME = 'nu_price'");
		if($q==0) {
			//echo "&gt; adding `nu_price`.<br>";
			$sql = "ALTER TABLE  `".DB_PREFIX."7banner` ADD  `nu_price` DECIMAL( 19, 4 ) NOT NULL default '0.0000';";
			$conn->query($sql) or die("Error while upgrading your DB for nu_price value. ".$conn->error." sql='$sql'<br>");
		}
		
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner' AND COLUMN_NAME = 'de_city'");
		if($q==0) {
			//echo "&gt; adding geo ip tables.<br>";
			$b = array();

			$b[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `de_country` VARCHAR(60) NOT NULL default '';";
			$b[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `de_region` VARCHAR(60) NOT NULL default '';";
			$b[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `de_city` VARCHAR(60) NOT NULL default '';";
			$b[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `nu_redux` TINYINT NOT NULL DEFAULT '0'";

			$b[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_geoip` (
				  `ipfrom` int(10) unsigned NOT NULL,
				  `ipto` int(10) unsigned NOT NULL,
				  `code` varchar(2) character set utf8 NOT NULL,
				  `country` varchar(64) character set utf8 NOT NULL,
				  `region` varchar(64) character set utf8 NOT NULL,
				  `city` varchar(128) character set utf8 NOT NULL,
				  PRIMARY KEY  (`ipfrom`,`ipto`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
				
			$b[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_GEOIP_CSV', 'DB1LITE');";
			$b[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_GEOIP_TOKEN', '');";



			foreach ($b as $s) {
				
				$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");

			}

		};

		if (!table_exists(DB_PREFIX."7banner_templates")) {

			//echo "&gt; adding banner templates.<br>";


			$conn->query("INSERT IGNORE INTO `".DB_PREFIX."frw_componenti` (`id`, `nome`, `descrizione`, `urlcomponente`, `label`, `urliconamenu`) VALUES
			(195, 'TEMPLATES', 'Banner templates', 'componenti/7templates/index.php', 'Templates', NULL);");

			$conn->query("INSERT INTO `".DB_PREFIX."frw_com_mod` (`idcomponente`, `idmodulo`, `posizione`) VALUES
			(195, 18, 40);");

			$conn->query("INSERT INTO `".DB_PREFIX."frw_funzionalita` (`id`, `idcomponente`, `nome`, `descrizione`, `label`) VALUES
			(219, 195, 'TEMPLATES', 'TEMPLATES', 'TEMPLATES');");

			$conn->query("INSERT INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES
			(999999, 1, 219),
			(20, 1, 219);");

			$conn->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_templates` (
			  `id_template` int(10) unsigned NOT NULL auto_increment,
			  `de_titolo` varchar(100) character set utf8 NOT NULL,
			  `de_info` text character set utf8 NOT NULL,
			  `de_code` text character set utf8 NOT NULL,
			  PRIMARY KEY  (`id_template`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;");
		
		}

	}


	function sql3() {
		global $conn;
		if (table_exists(DB_PREFIX."7banner_geoip")) {
			$c[] = "DROP TABLE ".DB_PREFIX."7banner_geoip";

			$c[] = "CREATE TABLE  IF NOT EXISTS `ip2location_db3`(
				`ip_from` INT(10) UNSIGNED,
				`ip_to` INT(10) UNSIGNED,
				`country_code` CHAR(2),
				`country_name` VARCHAR(64),
				`region_name` VARCHAR(128),
				`city_name` VARCHAR(128),
				INDEX `idx_ip_from` (`ip_from`),
				INDEX `idx_ip_to` (`ip_to`),
				INDEX `idx_ip_from_to` (`ip_from`, `ip_to`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

			$c[] = "CREATE TABLE  IF NOT EXISTS `ip2location_db3_ipv6`(
				`ip_from` DECIMAL(39,0) UNSIGNED NULL DEFAULT NULL,
				`ip_to` DECIMAL(39,0) UNSIGNED NOT NULL,
				`country_code` CHAR(2),
				`country_name` VARCHAR(64),
				`region_name` VARCHAR(128),
				`city_name` VARCHAR(128),
				INDEX `idx_ip_from` (`ip_from`),
				INDEX `idx_ip_to` (`ip_to`),
				INDEX `idx_ip_from_to` (`ip_from`, `ip_to`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";


			foreach ($c as $s) {
				
				$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");

			}

		}
	}


	function sql4() {
		global $conn;
			// mettere templates aggiornati, aggiungere che si aggiungono
			$q = execute_scalar("select count(1) from `".DB_PREFIX."7banner_templates`where `de_titolo`='Big overlay centered'");
			if($q==0) 
				$conn->query("INSERT IGNORE INTO `".DB_PREFIX."7banner_templates` (`de_titolo`, `de_info`, `de_code`) VALUES
					('Skin', 'First image 320x100px for mobile. The second image is the desktop skin background 1920x1080px.<br>\r\nPlace the script at the beginning of the page, before the main wrapper.<br>\r\nSkin works if your template allows it, if you need support please <a href=\"https://codecanyon.net/item/adadmin-easy-adv-server/12710605/support\" target=\"_blank\"><u>contact me</u></a>.\r\nChoose [OK] to insert code.', '<style>#skin[ID]banner {background:#ffffff url([IMG0]?1) center 0px no-repeat;margin:0 auto;width:100%;display: block;height: 100px;}\r\n@media only screen and (min-width: 1024px) {body { background:#ffffff url([IMG1]?1) center 0px no-repeat; background-attachment:fixed;background-size: 1532px!important;} #skin[ID]banner {background-image:none;background-color:transparent;width:100%}}\r\n</style>\r\n<a id=''skin[ID]banner'' href=''[CLICKTAG]'' rel=''nofollow'' target=''[TARGET]''></a>\r\n<script type=\"text/javascript\">\r\nfunction bae[ID]skin(e, v, f) {if (typeof addEventListener !== \"undefined\") e.addEventListener(v, f, false); else e.attachEvent(\"on\" + v, f);}\r\nbae[ID]skin(document.body, \"click\", function(e) {var tg = (window.event) ? e.srcElement : e.target;	var a = document.getElementById(''skinbanner'').getAttribute(\"href\");	if(tg.nodeName.toLowerCase()==''body'') window.open(a);});\r\nbae[ID]skin(document.body, \"mouseover\", function(e) {var tg = (window.event) ? e.srcElement : e.target;var d = document.body;if(tg.nodeName.toLowerCase()==''body'') d.style.cursor=''pointer''; else d.style.cursor=''default'';});\r\n</script>'),
					('Responsive banner', 'Just upload two images and check the order of the images: place first the mobile version. Press OK to insert the code. Use it in a responsive position.<br>\r\n<b>Code explained</b>: the first image [IMG0] of the banner details will be used on mobile devices; the second image [IMG1] will be used on desktops (screen size equal or grater than 1024px); <br>\r\n[CLICKTAG] is the link that tracks the url and [TARGET] is the target chosen in the banner details.', '<a href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a>'),
					('Overlay bottom fixed strip banner', 'This banner can handle leaderboard responsive banner attached at the bottom of the window, it comes with an X close button to remove the banner. Use it in a responsive position.<br>\r\n<strong>[IMG0]</strong> first image for mobile devices (size 320x50px);<br>\r\n<strong>[IMG1]</strong> second image for desktop devices (size 728x90);<br>\r\nClick OK to insert!<br>', '<style>#footerbanner[ID]A {position:fixed;bottom:0;transform: translate(-50%, 0%);left:50%;}\r\n.closemex[ID]A {position:absolute;top:-15px;right:-15px;text-decoration:none;font-size:15px;display:inline-block;width:30px;height:30px;line-height:30px;text-align:center;background-color:#000000;color:#ffffff;border-radius:50%}\r\n</style>\r\n<span id=\"footerbanner[ID]A\"><a href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a><a href=\"javascript:;\" class=\"closemex[ID]A\" onclick=\"document.getElementById(''footerbanner[ID]A'').remove();\">X</a></span>'),
					('Big overlay centered', 'This banner can handle big responsive banner in the center of the window, it comes with an X close button to remove the banner. Big image for mobile and for desktop. Use it in a responsive position.<br>\r\n<strong>[IMG0]</strong> first image for mobile devices (size 320x480px);<br>\r\n<strong>[IMG1]</strong> second image for desktop devices (size 500x350);<br>\r\nClick <b>OK</b> to insert!<br>', '<style>#overlaybanner[ID]A {position:fixed;top:50%;transform: translate(-50%, -50%);left:50%;}\r\n#overlaybanner[ID]A a.pic[ID]A {display:block;max-width:95vw;max-height:95vh;overflow:hidden}\r\n.closemex[ID]A {position:absolute;top:-15px;right:-15px;text-decoration:none;font-size:15px;display:inline-block;width:30px;height:30px;line-height:30px;text-align:center;background-color:#000000;color:#ffffff;border-radius:50%}\r\n</style>\r\n<span id=\"overlaybanner[ID]A\"><a class=\"pic[ID]A\" href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a><a href=\"javascript:;\" class=\"closemex[ID]A\" onclick=\"document.getElementById(''overlaybanner[ID]A'').remove();\">X</a></span>');");


			$conn->query("INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_GEOIP_LIMIT_COUNTRY', '');");
			$conn->query("INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_GEOIP_TOKEN', '');");

	
	}


	function sql5() { //392
		global $conn;
		$ar = array();

		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner' AND COLUMN_NAME = 'nu_cap'");
		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `nu_cap` SMALLINT NOT NULL DEFAULT '0' AFTER `nu_redux`;";
		}
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner' AND COLUMN_NAME = 'nu_mobileflag'");
		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `nu_mobileflag` TINYINT(1) NOT NULL DEFAULT '0' AFTER `nu_cap`;";
		}
		
		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql6() { // 393
		global $conn;
		$q = execute_scalar("select count(1) from `".DB_PREFIX."7banner_templates`where `de_titolo`='Intropage'");
		$ar = array();
		if($q==0) {
			$ar[] = "INSERT INTO `".DB_PREFIX."7banner_templates` (`de_titolo`, `de_info`, `de_code`) VALUES
				('Intropage', 'This banner can handle big responsive banner in the center of the window, with a black overlay. under that cover the site. It comes with an X close button to remove the banner. Big image for mobile and for desktop. Use it in a responsive position.<br>\r\n<strong>[IMG0]</strong> first image for mobile devices (size 320x480px);<br>\r\n<strong>[IMG1]</strong> second image for desktop devices (size 500x350);<br>\r\nClick <b>OK</b> to insert!<br>', '<style>#overlaybanner[ID]A img {width:100%;height:auto}#overlaybanner[ID]A {position:fixed;top:0;left:0;z-index: 99999999999;width:100vw;height:100vh;background-color:rgba(0,0,0,.9)}#overlaybanner[ID]A a.pic[ID]A {display:block;position:absolute;top: 50%;transform: translate(-50%, -50%);left: 50%;width: 80%;max-width:400px}.closemex[ID]A {position:absolute;top:15px;right:15px;text-decoration:none;font-size:15px;display:inline-block;width:30px;height:30px;line-height:30px;text-align:center;background-color:#000000;color:#ffffff;border-radius:50%}@media screen and (min-width: 768px) {#overlaybanner[ID]A a.pic[ID]A{max-width:500px;}}</style>\r\n<span id=\"overlaybanner[ID]A\"><a class=\"pic[ID]A\" href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture>\r\n<source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\">\r\n<source srcset=\"[IMG0]\">\r\n<img src=\"[IMG0]\" >\r\n</picture></a><a href=\"javascript:;\" class=\"closemex[ID]A\" onclick=\"document.getElementById(\'overlaybanner[ID]A\').remove();\">X</a></span>');";
		}
		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql7() { // 394
		global $conn;
		$ar = array();
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES ('CONST_SERVER_NAME', 'AdAdmin');";
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES (5, 18, 211);";

		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner_posizioni' AND COLUMN_NAME = 'vendita_online'");
		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` ADD `vendita_online` TINYINT(1) NOT NULL DEFAULT '0';";
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` ADD `modello_vendita` ENUM('cpm','cpc','cpd') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cpm';";
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` ADD `prezzo_vendita` DECIMAL(8,3) NOT NULL DEFAULT '0';";
		}
		$ar[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_ordini` (
				  `id_ordine` int(11) NOT NULL auto_increment,
				  `cd_utente` int(11) NOT NULL,
				  `cd_banner` int(11) NOT NULL default '0',
				  `prezzo` decimal(8,2) NOT NULL COMMENT 'è usato da adadmin per calcolare la data fine nei CPD',
				  `snapshot_id_posizione` int(11) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `snapshot_de_posizione` varchar(20) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `snapshot_nu_width` int(11) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `snapshot_nu_height` int(11) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `snapshot_vendita_online` tinyint(1) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `snapshot_modello_vendita` varchar(20) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `snapshot_prezzo_vendita` decimal(8,3) NOT NULL COMMENT 'snapshot tabella ".DB_PREFIX."7banner_posizioni',
				  `id_paypal` varchar(200) default NULL,
				  `en_stato_pagamento` enum('attesa','pagato') NOT NULL default 'attesa',
				  `dt_inizio_banner` date default NULL,
				  `data_pagamento` datetime NOT NULL default '".ZERODATE." 00:00:00',
				  `data_creazione` timestamp NOT NULL default CURRENT_TIMESTAMP,
				  `prezzo_finale` decimal(8,2) NOT NULL COMMENT 'è il prezzo finale pagato, può essere diverso da prezzo se è applicato codice sconto',
				  `codicesconto` varchar(30) NOT NULL,
				  `codicesconto_pagaora` varchar(30) default NULL,
				  PRIMARY KEY  (`id_ordine`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner_ordini' AND COLUMN_NAME = 'paypal_user'");
		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` CHANGE `nu_price` `nu_price` DECIMAL(8,2) NOT NULL;";
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` ADD `paypal_user` varchar(150) NOT NULL DEFAULT '';";
		}


		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_PAYMENTS', 'OFF');";
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_PAYPAL_CLIENTID', '');";
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_PAYPAL_SECRET', '');";
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_PAYPAL_SERVER', 'https://api.sandbox.paypal.com');";
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES (NULL, 'CONST_MIN_PRICE', '20');";

		$ar[] = "UPDATE `".DB_PREFIX."frw_profili` set `de_label`='advertiser', `de_descrizione` = 'advertiser' where `id_profilo`=5";

		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner_clienti' AND COLUMN_NAME = 'de_address'");

		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_clienti` ADD `de_address` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `de_nome`, ADD `cd_utente` INT NOT NULL AFTER `de_address`;";
		}
		if(table_exists("".DB_PREFIX."7banner_clienti_tbc")) {
			$ar[] = "UPDATE IGNORE `".DB_PREFIX."7banner_clienti` A set A.cd_utente = (select B.cd_utente from `".DB_PREFIX."7banner_clienti_tbc` B where B.cd_cliente = A.id_cliente) where cd_utente=0";
		}


		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}




	function sql395() { // 395
		global $conn;
		$ar = array();
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner_posizioni' AND COLUMN_NAME = 'fl_vignette'");
		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` ADD `fl_vignette` TINYINT NOT NULL DEFAULT '0' AFTER `prezzo_vendita`, ADD `de_trigger` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fl_vignette`, ADD `nu_timer` TINYINT UNSIGNED NOT NULL AFTER `de_trigger`;";
		}
		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}


	function sql396() {
		global $conn;
		$ar = array();

		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_componenti` (`id`, `nome`, `descrizione`, `urlcomponente`, `label`, `urliconamenu`) VALUES
		(196, 'SUPPORT', 'Support', 'http://1.envato.market/adadminsupport', 'Support', NULL);";

		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_com_mod` (`idcomponente`, `idmodulo`, `posizione`) VALUES
		(196, 18, 99);";

		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_funzionalita` (`id`, `idcomponente`, `nome`, `descrizione`, `label`) VALUES
		(220, 196, 'SUPPORT', 'SUPPORT', 'SUPPORT');";

		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES
		(999999, 18, 220),
		(20, 18, 220);";

		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`id_var`, `de_nome`, `de_value`) VALUES
				(46, 'CONST_FAVICON', '/data/tema/favicon.png');";
			
		$q = execute_scalar("SELECT `AUTO_INCREMENT`
		FROM  INFORMATION_SCHEMA.TABLES
		WHERE TABLE_SCHEMA = '".DEFDBNAME."'
		AND   TABLE_NAME   = '".DB_PREFIX."7banner_templates';");
		if($q<100) { $ar[] = "ALTER TABLE ".DB_PREFIX."7banner_templates AUTO_INCREMENT=101;"; }

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}


	function sql397() {
		global $conn;
		$ar = array();


		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_profili` (`id_profilo`, `de_label`, `de_descrizione`, `chiedita`) VALUES
			(10, 'webmaster', 'webmaster', '');";

		$ar[] ="UPDATE `".DB_PREFIX."frw_profili` SET `chiedita` = ',20,5,15,10,' WHERE `".DB_PREFIX."frw_profili`.`id_profilo` =20;";

		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_componenti` (`id`, `nome`, `descrizione`, `urlcomponente`, `label`, `urliconamenu`) VALUES
			(197, 'WEBSITES', 'Websites', 'componenti/websites', 'Websites', NULL),
			(198, 'DASHBOARD', 'Dashboard', 'componenti/7banner/index.php?op=dashboard', 'Dashboard', NULL),
			(199, 'PAYMENTS', 'Payments', 'componenti/payments/index.php', 'Payments', NULL);";

		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_com_mod` (`idcomponente`, `idmodulo`, `posizione`) VALUES
		(197, 18, 10),
		(198, 18, 0),
		(199, 18, 15);";

		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_funzionalita` (`id`, `idcomponente`, `nome`, `descrizione`, `label`) VALUES
		(221, 197, 'WEBSITES', 'WEBSITES', 'WEBSITES'),
		(222, 198, 'DASHBOARD', 'DASHBOARD', 'DASHBOARD'),
		(223, 199, 'PAYMENTS', 'PAYMENTS', 'PAYMENTS');";


		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES
		(999999, 18, 221),
		(10, 18, 221),
		(20, 18, 221),
		(5, 18, 221),
		(10, 1, 78),
		(10, 18, 213),
		(999999, 18, 222),
		(10, 18, 222),
		(5, 18, 222),
		(20, 18, 222),
		(999999, 18, 223),
		(10, 18, 223),
		(20, 18, 223);";


		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner_posizioni' AND COLUMN_NAME = 'cd_sito'");
		if($q==0) {
			$ar[] = "ALTER TABLE  `".DB_PREFIX."7banner_posizioni` ADD  `cd_sito` INT UNSIGNED NOT NULL default '0' COMMENT  '' AFTER  `nu_timer` ;";
		}

		$ar[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_sites` (
			  `id_sito` int(11) NOT NULL auto_increment,
			  `de_nomesito` varchar(100) character set utf8 NOT NULL,
			  `de_urlsito` varchar(100) character set utf8 NOT NULL,
			  `cd_webmaster` int(11) NOT NULL,
			  `fl_status` tinyint(1) NOT NULL,
			  `nu_share` tinyint(1) NOT NULL default '80',
			  `de_text` text character set utf8 NOT NULL,
			  PRIMARY KEY  (`id_sito`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;";


		$ar[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."frw_profili_funzionalita2` (
		  `cd_profilo` int(10) unsigned NOT NULL default '999999',
		  `cd_modulo` int(10) unsigned NOT NULL default '0',
		  `cd_funzionalita` int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`cd_profilo`,`cd_modulo`,`cd_funzionalita`),
		  KEY `cd_profilo` (`cd_profilo`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contiene le funzionalita da attivare';";

		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita2` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) SELECT DISTINCT * FROM `".DB_PREFIX."frw_profili_funzionalita`";

		$ar[] = "DROP TABLE `".DB_PREFIX."frw_profili_funzionalita`";

		$ar[] = "RENAME TABLE `".DB_PREFIX."frw_profili_funzionalita2` TO `".DB_PREFIX."frw_profili_funzionalita`";
		
		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."frw_extrauserdata' AND COLUMN_NAME = 'nu_costo'");
		if($q>0) $ar[] = "ALTER TABLE `".DB_PREFIX."frw_extrauserdata` DROP `nu_costo`";
				
		$ar[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."7banner_payments` (
		  `id_payment` int(10) unsigned NOT NULL auto_increment,
		  `nu_import_webmaster` decimal(19,2) NOT NULL default '0.00',
		  `nu_import_admin` decimal(19,2) NOT NULL default '0.00',
		  `cd_webmaster` int(11) NOT NULL,
		  `dt_quando` date NOT NULL default '".ZERODATE."',
		  `fl_stato` tinyint(1) NOT NULL default '0' COMMENT '0=non pagato, 1=pagato',
		  `de_log` text character set utf8 NOT NULL,
		  PRIMARY KEY  (`id_payment`),
		  KEY `cd_webmaster` (`cd_webmaster`,`dt_quando`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;";

		$ar[] = "update ".DB_PREFIX."frw_utenti set username='aaa',password='BTIKNAE8' where id='34' and username='guest' and cd_profilo=5";

		$ar[] = "insert ignore into ".DB_PREFIX."frw_utenti (id,username,password,nome,cognome,fl_attivo,cd_profilo) values (32,'www','BSQKIgEq','Boba','Fett',1,10)";

		$ar[] = "insert ignore into ".DB_PREFIX."7banner_sites (id_sito,de_nomesito,de_urlsito,cd_webmaster,fl_status,nu_share,de_text) values (
			1,'Barattalo.it','https://www.barattalo.it',32,1,80,'Website with scripts, code and other stuff for makers and developers.')";

		$ar[] = "update ".DB_PREFIX."7banner_posizioni set cd_sito=1 where id_posizione in (32,9)";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}






	function sql398() {
		global $conn;
		$ar = array();


		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` ( `de_nome`, `de_value`) VALUES
		('CONST_SMTP_SERVER', 'smtps.aruba.it'),
		('CONST_SMTP_AUTH', '1'),
		('CONST_SMTP_USERNAME', 'yourmail@server.com'),
		('CONST_SMTP_PASSWORD', 'yourmailpwd'),
		('CONST_SMTP_ENCRYPTION', 'SSL'),
		('CONST_SMTP_PORT', '465');";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql398b() {
		global $conn;
		$ar = array();

		$ar[] = "DELETE FROM ".DB_PREFIX."frw_com_mod where idcomponente=194 and idmodulo=1 and posizione=10";
		$ar[] = "INSERT IGNORE INTO ".DB_PREFIX."frw_com_mod (idcomponente ,idmodulo,posizione) VALUES ( '194','1','0')";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}
	
	function sql398d() {
		global $conn;
		$ar = array();
		//$ar[] = "SET GLOBAL sql_mode = '';"; // to force date 0000-00-00
		$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` CHANGE `dt_maxday_date` `dt_maxday_date` DATE NOT NULL DEFAULT '".ZERODATE."';";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql398i() {
		global $conn;
		$ar = array();
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` ( `de_nome`, `de_value`) VALUES
		('CONST_MAXSIZE_UPLOAD_BANNER', '1000')";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql398j() {
		global $conn;
		$ar = array();

		$CODE = execute_scalar("SELECT de_value FROM ".DB_PREFIX."frw_vars WHERE de_nome='CONST_MONEY_CODE'","");
		if($CODE==""){
			$SYMBOL = execute_scalar("SELECT de_value FROM ".DB_PREFIX."frw_vars WHERE de_nome='CONST_MONEY'","€");
			if($SYMBOL=="€") $CODE = "EUR";
			if($SYMBOL=="$") $CODE = "USD";
			if($SYMBOL=="R$") $CODE = "BRL";
			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` ( `de_nome`, `de_value`) VALUES ('CONST_MONEY_CODE', '".$CODE."')";
		}

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql398k() {
		global $conn;
		$ar = array();

		$q = execute_scalar( "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA='".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."7banner_posizioni' AND COLUMN_NAME = 'cd_fallback'");
		if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` ADD `cd_fallback` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'banner fallback filler' AFTER `cd_sito`;";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql3991() {
		global $conn;
		$ar = array();

		$ar[] = "INSERT IGNORE INTO ".DB_PREFIX."frw_vars (id_var,de_nome,de_value) VALUES ( 113,'GEO_IP_STEP','')";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql3993a() {

		global $conn;
		$ar = array();

		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '".DB_PREFIX."frw_funzionalita' and INDEX_NAME='nomeunico' AND TABLE_SCHEMA='".DEFDBNAME."'");
		if($q==0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."frw_funzionalita` ADD UNIQUE `nomeunico` (`nome`);";

			$ar[] = "ALTER TABLE `".DB_PREFIX."frw_ute_fun` ADD `fl_automatic` INT(1) NOT NULL DEFAULT '1' AFTER `idmodulo`;";

			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_funzionalita` (id,idcomponente, nome, descrizione, label) VALUES (300,72,'BANNER_LIMITTOBBASIC','Use only basic banners','BANNER_LIMITTOBBASIC');";
			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_funzionalita` (id,idcomponente, nome, descrizione, label) VALUES (301,72,'BANNER_AUTOAPPROVEPENDING','Auto approve uploaded banners','BANNER_AUTOAPPROVEPENDING');";
			
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` CHANGE `nu_price` `nu_price` DECIMAL(14,2) NOT NULL;";
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` CHANGE `prezzo_finale` `prezzo_finale` DECIMAL(14,2) NOT NULL COMMENT 'final price, it could be differen from nu_price if a discount code is applied';";
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` CHANGE `prezzo` `prezzo` DECIMAL(14,2) NOT NULL COMMENT 'it is used to calculate the final date in CPD banners';";

			$ar[] = "INSERT IGNORE INTO ".DB_PREFIX."frw_vars (id_var,de_nome,de_value) VALUES ( 107,'CONST_COINBASE_API_KEY','')";
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` ADD `id_coinbase` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Coinbase charge' AFTER `paypal_user`;";

		}
		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}

	}


	function sql3993c() {

		global $conn;
		$ar = array();
		$ar[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";

		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."7banner' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='dt_giorno1' AND COLUMN_DEFAULT='0000-00-00'");
		if($q==1) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` CHANGE `dt_giorno1` `dt_giorno1` DATE NOT NULL DEFAULT '".ZERODATE."', CHANGE `dt_giorno2` `dt_giorno2` DATE NOT NULL DEFAULT '".ZERODATE."', CHANGE `dt_maxday_date` `dt_maxday_date` DATE NOT NULL DEFAULT '".ZERODATE."';";

			//$ar[] = "ALTER TABLE `7banner` CHANGE `dt_giorno1` `dt_giorno1` DATE NOT NULL DEFAULT '".ZERODATE."';";
		}
		//$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '7banner' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='dt_giorno2' AND COLUMN_DEFAULT='0000-00-00'");
		//if($q==1) {
			//$ar[] = "ALTER TABLE `7banner` CHANGE `dt_giorno2` `dt_giorno2` DATE NOT NULL DEFAULT '".ZERODATE."';";
		//}
		//$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '7banner' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='dt_maxday_date' AND COLUMN_DEFAULT='0000-00-00'");
		//if($q==1) {
			//$ar[] = "ALTER TABLE `7banner` CHANGE `dt_maxday_date` `dt_maxday_date` DATE NOT NULL DEFAULT '".ZERODATE."';";
		//}
		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."frw_extrauserdata' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='dt_datacreazione' AND COLUMN_DEFAULT='0000-00-00'");
		if($q==1) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."frw_extrauserdata` CHANGE `dt_datacreazione` `dt_datacreazione` DATE NOT NULL DEFAULT '".ZERODATE."';";
		}
		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."7banner_ordini' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='data_pagamento' AND COLUMN_DEFAULT='0000-00-00 00:00:00'");
		if($q==1) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` CHANGE `data_pagamento` `data_pagamento` DATETIME NOT NULL DEFAULT '".ZERODATE."';";
		}
		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."7banner_payments' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='dt_quando' AND COLUMN_DEFAULT='0000-00-00'");
		if($q==1) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` CHANGE `data_pagamento` `data_pagamento` DATETIME NOT NULL DEFAULT '".ZERODATE."';";
		}
		$ar[] = "UPDATE `".DB_PREFIX."7banner` SET dt_giorno1='".ZERODATE."' where dt_giorno1='0000-00-00'";
		$ar[] = "UPDATE `".DB_PREFIX."7banner` SET dt_giorno2='".ZERODATE."' where dt_giorno2='0000-00-00'";
		$ar[] = "UPDATE `".DB_PREFIX."7banner` SET dt_maxday_date='".ZERODATE."' where dt_maxday_date='0000-00-00'";
		$ar[] = "UPDATE `".DB_PREFIX."frw_extrauserdata` SET dt_datacreazione='".ZERODATE."' where dt_datacreazione='0000-00-00'";
		$ar[] = "UPDATE `".DB_PREFIX."7banner_ordini` SET data_pagamento='".ZERODATE." 00:00:00' where data_pagamento='0000-00-00 00:00:00'";
		$ar[] = "UPDATE `".DB_PREFIX."7banner_payments` SET dt_quando='".ZERODATE."' where dt_quando='0000-00-00'";
		
		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}

	}

	function sql3994() {

		global $conn;
		$ar = array();


		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES ('CONST_ADADMIN_CHECK_VERSION', 'ON');";

		$ar[] = "DELETE FROM `".DB_PREFIX."frw_vars` WHERE de_nome= 'CONST_FAVICON';";
		$ar[] = "DELETE FROM `".DB_PREFIX."frw_vars` WHERE de_nome= 'CONST_LOGO';";

		foreach ($ar as $s) {
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}


	}

	function sql3995() {

		global $conn;
		$ar = array();

		$q = execute_scalar("SELECT count(1) FROM information_schema.statistics 
				  WHERE table_schema = '".DEFDBNAME."'
					AND table_name = '".DB_PREFIX."frw_utenti' AND column_name = 'username'");
		if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."frw_utenti` ADD UNIQUE(`username`)";


		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."7banner' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='se_os'");
		if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD `se_os` SET('iPhone','iPad','iPod','Windows','Android','BlackBerry','Ubuntu','Linux','CrOs','Mac OS X') NULL DEFAULT NULL AFTER `nu_mobileflag`;";

		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql3996() {

		global $conn;
		$ar = array();

		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."frw_extrauserdata' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='de_lang'");
		if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."frw_extrauserdata` ADD `de_lang` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'en' AFTER `de_temp`;";

		$q = execute_scalar( "SELECT count(1) FROM ".DB_PREFIX."7banner_templates WHERE de_titolo = 'Text and image banner responsive'");

		if($q==0) $ar[] = "INSERT INTO `".DB_PREFIX."7banner_templates` (`de_titolo`, `de_info`, `de_code`) VALUES
		('Text and image banner responsive', '<b>[CLICKTAG]</b> is the link of the banner (automatic). <b>[IMG0]</b> is the image (automatic). <b>[HEADING]</b> this is the title (manual). <b>[TEXT]</b> this is the text (manual). <b>[ACTION]</b> this is the call to action (manual).[HEADING]\r\nPlease fill in these fields and click OK to create the banner:<br>', '<div class=\'tb123\'><a href=\"[CLICKTAG]\"><img src=\"[IMG0]\"/><span class=\'tb123_text\'><span class=\'tb123_tit2\'>[HEADING]</span><span class=\'tb123_tit3\'>[TEXT]</span><span class=\'tb123_cta\'>[ACTION]</span></span></a></div><style>.tb123{display:flex;font-size:2em}.tb123 a{display:flex;text-decoration:none;border: 1px solid #ddd;color:#111;border-radius:5px;flex-wrap:wrap;overflow: hidden;}.tb123 img{display:flex;flex:0 0 100%;width:100%;height:auto;}.tb123_text{display:flex;flex:0 0 100%;width:100%;flex-direction:column;padding-left:4%;justify-content:center;box-sizing:border-box;}.tb123_tit2 {font-weight:bold;font-size:150%;}.tb123_cta{margin:10px 10% 0 0;background:green;color:#fff;flex: 0 0 2em;display:flex;border-radius:5px;height:2em;justify-content:center;align-items:center;width:90%;}@media screen and (min-width:600px){.tb123{display:flex;font-size:1.5em}.tb123 img{display:flex;flex:0 0 40%;width:40%}.tb123_text {display:flex;flex:0 0 0%;width:60%;}.tb123_cta{width: 50%;}}</style>');";

		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}


	}

    function sql3997() {

		global $conn;
		$ar = array();

		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '".DB_PREFIX."7banner_posizioni' and INDEX_NAME='sito' AND TABLE_SCHEMA='".DEFDBNAME."'");
		if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` ADD INDEX `sito` (`cd_sito`);";

        $q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '".DB_PREFIX."7banner' and INDEX_NAME='cd_posizione' AND TABLE_SCHEMA='".DEFDBNAME."'");
		if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."7banner` ADD INDEX `cd_posizione` (`cd_posizione`);";


        $q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."7banner_stats' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='cd_posizione'");
        if($q==0) $ar[] = "ALTER TABLE `".DB_PREFIX."7banner_stats` ADD `cd_posizione` INT NOT NULL DEFAULT '0' AFTER `cd_banner`;";


        $q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."7banner_stats' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='de_referrer'");
        if($q==0) {
            $ar[] = "ALTER TABLE `".DB_PREFIX."7banner_stats` ADD `de_referrer` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `cd_posizione`;";

            $ar[]= "ALTER TABLE `".DB_PREFIX."7banner_stats` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_day`, `cd_banner`, `cd_posizione`, `de_referrer`) USING BTREE;";
        }


		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}


	}

    function sql4000() {

		global $conn;
		$ar = array();
		
        $ar[] = "UPDATE `".DB_PREFIX."frw_componenti` SET `descrizione` = 'Banners', `label` = 'Banners' WHERE `".DB_PREFIX."frw_componenti`.`id` = 72;";

		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}


	}

	function sql4004() {

		global $conn;
		$ar = array();
		
        $ar[] = "UPDATE `".DB_PREFIX."frw_componenti` SET `descrizione` = 'Banners', `label` = 'Banners' WHERE `".DB_PREFIX."frw_componenti`.`id` = 72;";
		$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` CHANGE `id_coinbase` `id_coinbase` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Coinbase charge id';";
		
		$check = execute_scalar("select id_template from `".DB_PREFIX."7banner_templates`where `de_titolo`='Big overlay centered'");
		if($check==4) 
			$ar[] = "UPDATE `".DB_PREFIX."7banner_templates` SET `de_code`='<style>#overlaybanner[ID]A img {width:100%;height:auto;max-height: 100vh}#overlaybanner[ID]A {position:fixed;top:0;left:0;z-index:9999;width:100vw;height:100vh;background-color:rgba(0,0,0,.9)}#overlaybanner[ID]A a.pic[ID]A {display:block;position:absolute;top: 50%;transform: translate(-50%, -50%);left: 50%;width: 80%;max-width:500px}.closemex[ID]A {position:absolute;top:15px;right:15px;text-decoration:none;font-size:15px;display:inline-block;font-family:arial;width:30px;height:30px;line-height:30px;text-align:center;background-color:#000000;color:#ffffff;border-radius:50%}@media screen and (min-width: 768px) {#overlaybanner[ID]A a.pic[ID]A{max-width:700px;}}</style><span id=\"overlaybanner[ID]A\"><a class=\"pic[ID]A\" href=\"[CLICKTAG]\" target=\"[TARGET]\"><picture><source srcset=\"[IMG1]\" media=\"(min-width: 1024px)\"><source srcset=\"[IMG0]\"><img src=\"[IMG0]\" ></picture></a><a href=\"javascript:;\" class=\"closemex[ID]A\" onclick=\"document.getElementById(''overlaybanner[ID]A'').remove();\">X</a></span>' WHERE `id_template`=4;";

		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}	

	
    function sql4100() {        
		global $conn;
		$ar = array();
        $ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES ('CONST_MANUAL_PAYMENTS', 'ON')";
        $ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES ('CONST_MANUAL_PAYMENTS_INFO', '')";
		foreach ($ar as $s) { 
            $conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}	


	function sql420() {        
		global $conn;
		$ar = array();
        $ar[] = "UPDATE `".DB_PREFIX."frw_moduli` SET `nome` = 'CONFIG',posizione=0 WHERE `".DB_PREFIX."frw_moduli`.`id` = 1;";
        $ar[] = "UPDATE `".DB_PREFIX."frw_moduli` SET posizione=1 WHERE `".DB_PREFIX."frw_moduli`.`id` = 18;";
        $ar[] = "UPDATE `".DB_PREFIX."frw_com_mod` SET `posizione` = 10 WHERE `idcomponente` = 14 and idmodulo=1;";
		$ar[] = "UPDATE `".DB_PREFIX."frw_com_mod` SET `posizione` = 11 WHERE `idcomponente` = 12 and idmodulo=1;";

		$ar[] = "DELETE FROM `".DB_PREFIX."frw_com_mod` WHERE `idcomponente` = 194;";
		$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_com_mod` (`idcomponente`, `idmodulo`, `posizione`) VALUES
			(194, 1, 1);";

		//	$ar[] = "UPDATE `frw_com_mod` SET `posizione` = 1 WHERE `idcomponente` = 194 and idmodulo=1;";

		$ar[] = "UPDATE `".DB_PREFIX."frw_componenti` SET `label` = 'Menu editor' WHERE `".DB_PREFIX."frw_componenti`.`id` = 14;";
		$ar[] = "UPDATE `".DB_PREFIX."frw_componenti` SET `label` = 'Menu items' WHERE `".DB_PREFIX."frw_componenti`.`id` = 12;";
		$ai = execute_scalar("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".DEFDBNAME."' AND TABLE_NAME = '".DB_PREFIX."frw_moduli';");
		if($ai<1000) {
			$ar[] = "ALTER TABLE ".DB_PREFIX."frw_moduli AUTO_INCREMENT = 1000;";
			$ar[] = "ALTER TABLE ".DB_PREFIX."frw_componenti AUTO_INCREMENT = 1000;";
			$ar[] = "ALTER TABLE ".DB_PREFIX."frw_funzionalita AUTO_INCREMENT = 1000;";

			$ar[] = "DELETE FROM `".DB_PREFIX."frw_componenti` WHERE `id` IN( 175,196);";
			$ar[] = "DELETE FROM `".DB_PREFIX."frw_com_mod` WHERE `idcomponente` = 196;";
			$ar[] = "DELETE FROM `".DB_PREFIX."frw_funzionalita` WHERE `id` IN (199, 220);";
			$ar[] = "DELETE FROM `".DB_PREFIX."frw_profili_funzionalita` WHERE `cd_funzionalita` = 220;";

			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_moduli` (`id`, `nome`, `label`, `visibile`, `posizione`) VALUES
			(1001, 'MOREITEMS', 'More items', 1, 98);";

			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_componenti` (`id`, `nome`, `descrizione`, `urlcomponente`, `label`, `urliconamenu`) VALUES
			(1001, 'SUPPORT', 'Support', 'http://1.envato.market/adadminsupport', 'Support', NULL);";
	
			$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_com_mod` (`idcomponente`, `idmodulo`, `posizione`) VALUES
			(1001, 1001, 1);";
	
			$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_funzionalita` (`id`, `idcomponente`, `nome`, `descrizione`, `label`) VALUES
			(1001, 1001, 'SUPPORT', 'SUPPORT', 'SUPPORT');";
	
			$ar[] ="INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES
			(999999, 1001, 1001),
			(20, 1001, 1001);";


		}
		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."frw_componenti' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='target'");
		if($q == 0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."frw_componenti` ADD `target` VARCHAR(10) NOT NULL AFTER `urliconamenu`;";
            $ar[] = "ALTER TABLE `".DB_PREFIX."frw_componenti` ADD `fl_translate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `target`;";
            $ar[]="ALTER TABLE `".DB_PREFIX."frw_moduli` ADD `fl_translate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `posizione`;";
		}

		$ar[] = "UPDATE ".DB_PREFIX."frw_componenti SET urliconamenu='icon-comment-empty', fl_translate=1, target='_blank' WHERE id=1001;";

		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}

	}	

	function sql421() {        
		global $conn;
		$ar = array();
		$v = (integer)execute_scalar("SELECT de_value FROM ".DB_PREFIX."frw_vars where de_nome='CURRENT_VERSION'","");
		if($v<"421") {
			$ar[] = "UPDATE ".DB_PREFIX."7banner SET nu_redux=25 WHERE nu_redux=1;";
			$ar[] = "UPDATE ".DB_PREFIX."7banner SET nu_redux=50 WHERE nu_redux=2;";
			$ar[] = "UPDATE ".DB_PREFIX."7banner SET nu_redux=75 WHERE nu_redux=3;";
			if($v=="") {
				$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES ('CURRENT_VERSION', '421');";
			} else {
				$ar[] = "UPDATE `".DB_PREFIX."frw_vars` SET `de_value`='421' WHERE `de_nome`='CURRENT_VERSION';";
			}
			foreach ($ar as $s) { 
				$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
			}
		}

	}	

    


    function sql421c() {        
		global $conn;
		$ar = array();
			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES ('20', '1', '20');";
			$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_profili_funzionalita` (`cd_profilo`, `cd_modulo`, `cd_funzionalita`) VALUES ('20', '1', '24');";
			foreach ($ar as $s) { 
				$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
			}

	}

	function sql422() {        
		global $conn;
		$ar = array();
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES
		('CONST_NUMBERFORMAT', '1000.00');";
		$ar[] = "UPDATE ".DB_PREFIX."frw_moduli SET nome='BANNER' WHERE nome='Adv server menu'";

		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}

	}

	function sql423() {        
		global $conn;
		$ar = array();
		$v = (integer)execute_scalar("SELECT count(1) FROM ".DB_PREFIX."frw_vars where de_nome='CONST_CHECK_VERSION'","");
        if($v==1) {
            $ar[] = "DELETE FROM `".DB_PREFIX."frw_vars` WHERE `de_nome`='CONST_ADADMIN_CHECK_VERSION';";
        } else {
            $ar[] = "UPDATE `".DB_PREFIX."frw_vars` SET `de_nome` = 'CONST_CHECK_VERSION' WHERE de_nome='CONST_ADADMIN_CHECK_VERSION';";
        }


		$v = (integer)execute_scalar("SELECT count(1) FROM ".DB_PREFIX."frw_vars where de_nome='CONST_SERVER_NAME'","");
        if($v==1) {
            $ar[] = "DELETE FROM `".DB_PREFIX."frw_vars` WHERE `de_nome`='CONST_ADSERVERNAME';";
        } else {
            $ar[] = "UPDATE `".DB_PREFIX."frw_vars` SET `de_nome` = 'CONST_SERVER_NAME' WHERE de_nome='CONST_ADSERVERNAME';";
        }

		$v = (integer)execute_scalar("SELECT count(1) FROM ".DB_PREFIX."frw_vars where de_nome='CONST_MAXSIZE_UPLOAD'","");
        if($v==1) {
            $ar[] = "DELETE FROM `".DB_PREFIX."frw_vars` WHERE `de_nome`='CONST_MAXSIZE_UPLOAD_BANNER';";
        } else {
            $ar[] = "UPDATE `".DB_PREFIX."frw_vars` SET `de_nome` = 'CONST_MAXSIZE_UPLOAD' WHERE de_nome='CONST_MAXSIZE_UPLOAD_BANNER';";
        }


		foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}

	}
	function sql424() {        
		global $conn;
		$ar = array();
		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."frw_extrauserdata' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='cd_default_component'");
		if($q == 0) {
            $ar[] = "ALTER TABLE `".DB_PREFIX."frw_extrauserdata` ADD `cd_default_component` INT NOT NULL DEFAULT '0' AFTER `de_lang`;";
        }
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES  ('CONST_NOTIFY_NEW_USERS_TO_ADMIN', 'OFF');";
		// $ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_funzionalita` (id,idcomponente, nome, descrizione, label) VALUES (401,5,'USER_NOTIFY','Notify when new user register','USER_NOTIFY');";
        foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql426() {        
		global $conn;
		$ar = array();
		$ar[] = "UPDATE `".DB_PREFIX."frw_vars`set de_value='426' WHERE de_nome='CURRENT_VERSION';";
		$ar[] = "INSERT IGNORE INTO `".DB_PREFIX."frw_vars` (`de_nome`, `de_value`) VALUES  ('CONST_STRONG_PASSWORD', 'OFF');";
		$ar[] = "UPDATE `".DB_PREFIX."frw_componenti` SET `urlcomponente`= 'componenti/7gestioneutenti/index.php' WHERE `urlcomponente`= 'componenti/gestioneutenti/index.php'"; 

		$ar[] = "ALTER TABLE `".DB_PREFIX."7banner` CHANGE `nu_price` `nu_price` DECIMAL(19,4) NOT NULL DEFAULT '0.0000';";
		$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_payments` CHANGE `nu_import_webmaster` `nu_import_webmaster` DECIMAL(19,4) NOT NULL DEFAULT '0.0000', CHANGE `nu_import_admin` `nu_import_admin` DECIMAL(19,4) NOT NULL DEFAULT '0.0000';";
		$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_posizioni` CHANGE `prezzo_vendita` `prezzo_vendita` DECIMAL(19,4) NOT NULL DEFAULT '0.0000';";
		$ar[] = "ALTER TABLE `".DB_PREFIX."7banner_ordini` CHANGE `prezzo` `prezzo` DECIMAL(19,4) NOT NULL DEFAULT '0.0000' COMMENT 'Used to calculate final date in CPD', CHANGE `snapshot_prezzo_vendita` `snapshot_prezzo_vendita` DECIMAL(19,4) NOT NULL DEFAULT '0.0000' COMMENT 'snapshot of table 7banner_posizioni', CHANGE `prezzo_finale` `prezzo_finale` DECIMAL(19,4) NOT NULL DEFAULT '0.0000' COMMENT 'Final price, could be different if there is discount';";
		
        foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql427() {   
		global $conn;
		$ar = array();
		// the number ov the version could be used during updates in upgrade to determine the
		// installed version after the upload of the files.
		$ar[] = "UPDATE `".DB_PREFIX."frw_vars`set de_value='427' WHERE de_nome='CURRENT_VERSION';";
		$ar[] = "UPDATE `".DB_PREFIX."frw_componenti` SET `urlcomponente`= 'componenti/7gestioneutenti/mioprofilo.php' WHERE `urlcomponente`= 'componenti/gestioneutenti/mioprofilo.php'"; 
		$q = execute_scalar( "SELECT count(1) FROM INFORMATION_SCHEMA.columns WHERE TABLE_NAME = '".DB_PREFIX."frw_extrauserdata' AND TABLE_SCHEMA='".DEFDBNAME."' AND COLUMN_NAME='de_payment_details'");
		if($q == 0) {
			$ar[] = "ALTER TABLE `".DB_PREFIX."frw_extrauserdata` ADD `de_payment_details` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'payment details for webmasters' AFTER `cd_default_component`;";
		}

        foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}

	function sql428() {   
		global $conn;
		$ar = array();
		$ar[] = "UPDATE `".DB_PREFIX."frw_vars`set de_value='428' WHERE de_nome='CURRENT_VERSION';";
		$ar[] = "INSERT IGNORE INTO ".DB_PREFIX."frw_vars (de_nome,de_value) VALUES ('DBINTEGRITYDATA','')";
        foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}
	
	function sql428b() {   
		global $conn;
		$ar = array();
		$ar[] = "UPDATE `".DB_PREFIX."frw_vars`set de_value='428b' WHERE de_nome='CURRENT_VERSION';";
		$ar[] = "UPDATE `".DB_PREFIX."frw_componenti` set urlcomponente='componenti/7campagne/index.php' WHERE nome='CAMPAGNE';";
		$ar[] = "UPDATE `".DB_PREFIX."frw_componenti` set urlcomponente='componenti/websites/index.php' WHERE nome='WEBSITES';";
        foreach ($ar as $s) { 
			$conn->query($s) or die("Error executing query: <pre><code>$s</code></pre>.<br><br>Error:<br><br><b>".$conn->error."</b>");
		}
	}




}
