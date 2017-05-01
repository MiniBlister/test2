<?php
    //SendDateToParent(E496ED12-5963-4494-87F3-E537175E7418)
    include_once(__DIR__ . "/../module_helper.php");
    // Klassendefinition
    class XiaomiSmartDoorWindowSensor extends KoHelpDModule {
 
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
            
            //Always create our own Splitter, when no parent is already available
            $this->ConnectParent ("{66C1E46E-20B6-42FE-8477-2671A0512DD6}");
                
            //$this->SetParentName("Multicast Socket", "Splitter");
                        
            //Create Varriable for Status and Voltage
            $this->RegisterVariableBoolean("state", "state", "~Window",0);
            $this->RegisterVariableFloat("voltage", "battery voltage", "~Volt",1);
            
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
            
            $datasend = $data->Buffer;
            $datasend = json_decode($datasend);

            //$this->SendDebug("test Data:",$datasend,0);

            // Hier würde man den Buffer im Normalfall verarbeiten
            // z.B. CRC prüfen, in Einzelteile zerlegen
            try
            {
                $payload = array("cmd" => $datasend->cmd);
                
            }
            catch (Exception $ex)
            {
                    echo $ex->getMessage();
                    echo ' in '.$ex->getFile().' line: '.$ex->getLine().'.';
            }

              
            //We would package our payload here before sending it further...
            $result = $this->SendDataToParent(json_encode(Array("DataID" => "{E496ED12-5963-4494-87F3-E537175E7418}", "Buffer" => json_encode($payload))));
			
            //Normally we would wait here for ReceiveData getting called asynchronically and buffer some data
            //Then we should extract the relevant feedback/data and return it to the caller
            return $result;
	}
        
		
	public function ReceiveData($JSONString)
	{
            $data = json_decode($JSONString);
            IPS_LogMessage("Xiaomi Door RECV", utf8_decode($data->Buffer));
            $xidata = json_decode($data->Buffer);
            if ($xidata->cmd == "get_id_list_ack") {
                $idlist = $this->GetList(json_decode($xidata->data));
                //print_r ($idlist);
            }
            
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
        
        //Get ID list and details for Sensors
        public function GetList ($ids){
            foreach ($ids as $key=>$value) {
                $payload = array ("cmd" => "read", "sid" => $value);
                $result[$key] = @$this->SendDataToParent(json_encode(Array("DataID" => "{E496ED12-5963-4494-87F3-E537175E7418}", "Buffer" => json_encode($payload)))); 
                
                
            }
            print_r ($result);
            return $result;
        }
        
        public function ShowIDs() {
            
            $payload = array("cmd" => "get_id_list");
            IPS_LogMessage("Send from Device to Splitter ShowIDs():",json_encode($payload));
            $result = $this->SendDataToParent(json_encode(Array("DataID" => "{E496ED12-5963-4494-87F3-E537175E7418}", "Buffer" => json_encode($payload))));
            
        }
        

    }
?>