<?php
    // SendDataToParent (79827379-F36E-4ADA-8A95-5F8D1DC92FA9)
    // SendDateToChhild (B75DE28A-A29F-4B11-BF9D-5CC758281F38)
    include_once(__DIR__ . "/../module_helper.php");
    // Klassendefinition
    class XiaomiGatewaySplitter extends KoHelpDModule {
        
        public $sidmode;
        
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
            $this->RequireParent ("{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}");

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
        
          
                 
            //We would parse our payload here before sending it further...
            //Lets just forward to our children
            
	}
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        //Set the IP Address of Gateway in case this will be provided
        private function SetGatewayIP ($gateway) {
        /* @var $gateway object */
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
        


    }
?>