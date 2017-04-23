<?php

    include_once(__DIR__ . "/../module_helper.php");
    // Klassendefinition
    class XiaomiGatewayDevice extends KoHelpDModule {
 
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
            
            //Always create our own MultiCast I/O, when no parent is already available
            $this->RequireParent("{7553773d-45e4-4334-9d37-dbf9c7ca1778}");

            $pid = $this->GetParent();
            
            if ($pid) {
                $name = IPS_GetName($pid);
                if ($name == "Multicast Socket") {
                    IPS_SetName($pid, __CLASS__ . " Splitter");
                }
            }            
            
            parent::Create(); 
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
            $this->SendDataToParent(json_encode(Array("DataID" => "{66C1E46E-20B6-42FE-8477-2671A0512DD6}", "Buffer" => $data->Buffer)));
			
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
            //$this->SendDataToChildren(json_encode(Array("DataID" => "{C5A51178-2760-49DA-9175-1ED71975753C}", "Buffer" => $data->Buffer)));
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