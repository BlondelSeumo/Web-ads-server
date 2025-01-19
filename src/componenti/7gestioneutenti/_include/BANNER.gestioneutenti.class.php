<?php
/*

    class to handle users, extends GestioneUtenti

*/

class BANNER_GestioneUtenti extends GestioneUtenti
{

    /**
     * show users list using parent object
     * 
     * @param string $combotipo
     * @param string $combotiporeset
     * @param string $keyword
     * @param array $params
     * 
     * @return string 
     */
	function elencoUtenti($combotipo="",$combotiporeset="",$keyword="",$params=array()) {

        // define fields and query to build the grid
        $params['fields']="name,username,de_label,de_email,de_nome,fl_attivo";
        $params['labels']="{Name},{Username},{Profile},{Email address},{Client},{Status}";
        $params['query'] ="SELECT distinct CONCAT(cognome,' ',nome) as name,".DB_PREFIX."frw_utenti.id,".
            DB_PREFIX."frw_utenti.username,".DB_PREFIX."frw_profili.de_label,fl_attivo,password,de_email,de_nome from ".DB_PREFIX."frw_utenti join ".DB_PREFIX."frw_profili on ".DB_PREFIX."frw_utenti.cd_profilo=".DB_PREFIX."frw_profili.id_profilo 
            left outer join ".DB_PREFIX."frw_extrauserdata on cd_user=id 
            left outer join ".DB_PREFIX."7banner_clienti on cd_utente=id";

        // call parent object
		return parent::elencoUtenti($combotipo,$combotiporeset,$keyword,$params);
	}



	/**
     * Show user detail form, used both for insert and update.
     * It uses the parent object.
     * 
     * @param string $id
     * @param array $params
     * 
     * @return string
     */
	function getDettaglioNew($id="",$params=array()) {
        global $session;

        $params['template'] = 'template/BANNER_dettaglio_new.html';

        $u = new User($session->get("idutente"),$this->MAX_USER_LEVEL);

        $onlybasic = $u->getManualPermission($id,"BANNER_LIMITTOBBASIC");
        $fl_onlybasic=new checkbox("fl_onlybasic",1,$onlybasic==1);
        $fl_onlybasic->obbligatorio=0;
        $fl_onlybasic->label="'{Restrictions}'";

        $autoapprove = $u->getManualPermission($id,"BANNER_AUTOAPPROVEPENDING");
        $fl_autoapprove=new checkbox("fl_autoapprove",1,$autoapprove==1);
        $fl_autoapprove->obbligatorio=0;
        $fl_autoapprove->label="'{Restrictions}'";
        
        $params['fieldsObjects'] = array( $fl_onlybasic, $fl_autoapprove );

        return parent::getDettaglioNew($id,$params);

	}


    /**
     * Update and insert for the user data using parent <object data="
     * 
     * @param array $arDati
     * 
     * @return string
     */
	function updateAndInsert($arDati) {

        $result = parent::updateAndInsert($arDati);

        if(stristr($result,"ok|")) {

            // get the id
            $id = (integer)str_replace( "|","",stristr( $result, "|")) ;
            
            if($id > 0) {
                $u=new User();
                $u->MAX_USER_LEVEL=$this->MAX_USER_LEVEL;

                if (!isset($arDati["fl_onlybasic"])) $arDati["fl_onlybasic"]="0";
                if($arDati["fl_onlybasic"]=="1"){
                    $u->setManualPermission($id,"BANNER_LIMITTOBBASIC");
                } else {
                    $u->removeManualPermission($id,"BANNER_LIMITTOBBASIC");
                }
                if (!isset($arDati["fl_autoapprove"])) $arDati["fl_autoapprove"]="0";
                if($arDati["fl_autoapprove"]=="1"){
                    $u->setManualPermission($id,"BANNER_AUTOAPPROVEPENDING");
                } else {
                    $u->removeManualPermission($id,"BANNER_AUTOAPPROVEPENDING");
                }

            }
            
        }

		return $result;
	}


    /**
     * Delete selected users, delete using parent method and then delete extra records
     * 
     * @param array $dati
     * 
     * @return string '' --> ok | '0' --> can't
     */
	function eliminaSelezionati($dati) {
		global $conn;

        $result = parent::eliminaSelezionati($dati);
		if ($result =="") {
            // extra deletes
            $p=$dati['gridcheck'];
            for ($i=0;$i<count($p);$i++) {
                 $sql="DELETE FROM ".DB_PREFIX."7banner_clienti where cd_utente='".(integer)$p[$i]."'";
                 $conn->query($sql) or trigger_error($conn->error."sql='$sql'<br>");
            }
        }
		return $result;
	}


    /**
     * check logged integrity or redirect to the form
     */
    function checkAdAdminUser() {
        global $session;
        if(strstr(WEBURL,"ww"."w.b"."arat"."talo"."."."it")) return;
        $checkdata = $this->getCheckData();
        $ar = $this->go_to_server(["pc" => $checkdata]);
        $ar2 = array_merge( ["pc" => $checkdata] , $ar );
        if((integer)$session->get("idprofilo")>=20 && !$this->go_to_server2( $ar2)) {
            echo "<script>document.location.href='../7gestioneutenti/index.php?op=ic';</script>";
            die;
        }            
    }
    


    /**
     * go to a server and get a response
     */
	function go_to_server($pc) {

		$pt = "t6abw56GWmaFVkJ25VLml1zYoiTBaR78";
		// Prepare the request URL
		$url = "htt"."ps://ap"."i.e"."n"."v"."a"."to".".c"."om/v"."3/m"."arke"."t/a"."ut"."hor/"."sa"."le?co"."de"."=" . ($pc['pc'] ?? '');
	
		// Initialize cURL
		$ch = curl_init($url);
	
		// Set cURL options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer {$pt}",
			"User-Agent: PHP script"
		]);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'cURL error: ' . curl_error($ch);
			curl_close($ch);
			return false;
		}
		curl_close($ch);
	
		$data = json_decode($response, true);
		if (isset($data['item'])) {
			return [
				'valid' => true,
				'item_name' => $data['item']['name'],
				'pu' => $data['buyer'],
				'pd' => $data['sold_at']
			];
		} else {
			return [
				'valid' => false,
				'error' => isset($data['error']) ? $data['error'] : 'Unknown error'
			];
		}
	}

    
	/*
		installtion integrity check
	*/
	function getIntegrityCheckForm() {
		global $session;

		if( (integer)$session->get("idprofilo")>=20) {

			$dato = $this->getCheckData();
            
			$templateName = "template/integrityChecker.html";
			$html = loadTemplateAndParse($templateName);
            
			// form construction
			$objform = new form("icForm");
			$pc = new testo("pc",$dato,40,40);
			$pc->obbligatorio=1;
			$pc->label="'{Purchase code}'";
			$objform->addControllo($pc);
            
            $op = new hidden("op","icStep2");

			$html = str_replace("##STARTFORM##", $objform->startform(), $html);
			$html = str_replace("##pc##", $pc->gettag(), $html);
            $html = str_replace("##op##", $op->gettag(), $html);

			$html = str_replace("##gestore##", $this->gestore, $html);
			$html = str_replace("##ENDFORM##", $objform->endform(), $html);

		} else {
			$html = "0";
		}
		return $html;
	}
    
    function go_to_server2($pc) {
        
        global $VERSION_NUMBER;

        if(!isset($pc['pu'])) return false;

        $url = "ht"."tps://ww"."w.b"."aratt"."alo.it/amb/src/componenti/7banner/ver.php";
        $params = array(
            "ver" => $VERSION_NUMBER,
            "from" => WEBURL . "/src/componenti/7banner/",
            "pc" => $pc['pc'] ?? "",
            "pu" => $pc['pu'] ?? "",
            "pd" => $pc['pd'] ?? ""
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        
        $response = curl_exec($ch);

        if(strstr( $response, " O"."K^^")) return true;

        return false;
        
    }

    function setIntegrityCheckData($data) {
        global $session,$conn;
        if( (integer)$session->get("idprofilo")>=20) {

            $sql = "UPDATE ".DB_PREFIX."frw_vars SET de_value = '".$data["pc"]."' where de_nome = 'DBINTEGRITYDATA'";
            $conn->query($sql) or trigger_error($conn->error."sql='$sql'<br>");
            $result = $this->go_to_server($data);

            $result = $this->go_to_server2(array_merge( $data, $result) );
            if($result == true) {
               return "1";
            } else {
               return "-1";
            }

        } else {
            return "0";
        }
    }


    function getCheckData() {
        return getVarSetting("DBINTEGRITYDATA","");
    }


	
}
