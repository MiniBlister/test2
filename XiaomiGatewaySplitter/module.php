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
            }            
            
             
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
        }
        public function ForwardData($JSONString)
	{
            $data = json_decode($JSONString);
            IPS_LogMessage("XiaomiGateway FRWD", utf8_decode($data->Buffer));
            //We would package our payload here before sending it further...
            $this->SendDataToParent(json_encode(Array("DataID" => "{B237D1DF-B9B0-4A8D-8EC5-B4F7A88E54FC}", "Buffer" => $data->Buffer)));
			
            //Normally we would wait here for ReceiveData getting called asynchronically and buffer some data
            //Then we should extract the relevant feedback/data and return it to the caller
            return "String data for the device instance!";
	}
		
	public function ReceiveData($JSONString)
	{
            $data = json_decode($JSONString);
            IPS_LogMessage("XiaomiGateway RECV", utf8_decode($data->Buffer));
            //We would parse our payload here before sending it further...
            //Lets just forward to our children
            $this->SendDataToChildren(json_encode(Array("DataID" => "{66164EB8-3439-4599-B937-A365D7A68567}", "Buffer" => $data->Buffer)));
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