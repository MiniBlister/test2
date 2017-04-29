<?php

    include_once(__DIR__ . "/../module_helper.php");
    // Klassendefinition
    class XiaomiGatewaySplitter extends KoHelpDModule {
 
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
        }
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();
            //Always create our own MultiCast I/O, when no parent is already available
            $this->RequireParent("{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}");

            $pid = $this->GetParent();
            if ($pid) {
                $name = IPS_GetName($pid);
                if ($name == "Multicast Socket") {
                    IPS_SetName($pid, __CLASS__ . " Socket");
                }
                //Set SendHost Property of the MultiCast I/O
                IPS_SetProperty ( $pid, "Host", "192.168.178.47");
                //Set BindPort Property of the MultiCast I/O
                IPS_SetProperty ( $pid, "Port", "9898");
                //Set MulticastIP Property of the MultiCast I/O
                IPS_SetProperty ( $pid, "MulticastIP", "224.0.0.50");
                //Set BindPort Property of the MultiCast I/O
                IPS_SetProperty ( $pid, "BindPort", "9898");
                //Set Open Property of the MultiCast I/O
                IPS_SetProperty ( $pid, "Open", true);
                //Apply Changes
                IPS_ApplyChanges($pid);
            }            
            
             
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
        }
        public function ForwardData($JSONString)
	{
          
            //$debug = $this->ReadPropertyBoolean('Debug');
            // Empfangene Daten von der Device Instanz
            
            $data = json_decode($JSONString);
            print_r($data);
            $datasend = $data->Buffer;
            $datasend = json_encode($datasend);
            $this->SendDebug("test Data:",$datasend,0);

            // Hier würde man den Buffer im Normalfall verarbeiten
            // z.B. CRC prüfen, in Einzelteile zerlegen
            try
            {
                    //
            }
            catch (Exception $ex)
            {
                    echo $ex->getMessage();
                    echo ' in '.$ex->getFile().' line: '.$ex->getLine().'.';
            }

              
            //We would package our payload here before sending it further...
            $result = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $data->Buffer)));
			
            //Normally we would wait here for ReceiveData getting called asynchronically and buffer some data
            //Then we should extract the relevant feedback/data and return it to the caller
            return $result;
	}
		
	public function ReceiveData($JSONString)
	{
            $data = json_decode($JSONString);
            IPS_LogMessage("XiaomiGateway RECV", utf8_decode($data->Buffer));
            
            //We need to check IP Address of the Gateway and Update Parent Property accordingly
            $gateway =  json_decode($data->Buffer);
            if ($gateway->cmd == "heartbeat" && $gateway->model == "gateway") {
                $gatewayip= json_decode($gateway->data);
                $pid = $this->GetParent();
                if ($pid) {
                    if (IPS_GetProperty($pid, "Host") != $gatewayip->ip) {
                        //Set the Host Address to the Address provided by the Gateway witht the Multicast Address
                        IPS_SetProperty ( $pid, "Host", $gatewayip->ip);
                        //Apply Changes
                        IPS_ApplyChanges($pid);
                    }
                }
            }
            
            //We would parse our payload here before sending it further...
            //Lets just forward to our children
            $this->SendDataToChildren(json_encode(Array("DataID" => "{B75DE28A-A29F-4B11-BF9D-5CC758281F38}", "Buffer" => $data->Buffer)));
	}
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */

    }
?>