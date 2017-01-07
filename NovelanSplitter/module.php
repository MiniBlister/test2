<?php
    // Klassendefinition
    class NovSplitter extends IPSModule {
 
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
            
            //Always create our own Client Server, when no parent is already available
            $this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");
            $this->RegisterPropertyString("Name", "Novelan Client");
            $this->RegisterPropertyString("Host", "192.168.178.13");
            $this->RegisterPropertyBoolean("Open", TRUE);
            $this->RegisterPropertyInteger("Port", 8888);
            
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            $change = false;
            // Zwangskonfiguration des ClientSocket
            $ParentID = $this->GetParent();
            
            if (!($ParentID === false))
            {
                if (IPS_GetProperty($ParentID, 'Host') <> $this->ReadPropertyString('Host'))
                {
                    IPS_SetProperty($ParentID, 'Host', $this->ReadPropertyString('Host'));
                    $change = true;
                }
                if (IPS_GetProperty($ParentID, 'Port') <> $this->ReadPropertyInteger('Port'))
                {
                    IPS_SetProperty($ParentID, 'Port', $this->ReadPropertyInteger('Port'));
                    $change = true;
                }
                $ParentOpen = $this->ReadPropertyBoolean('Open');
                // Keine Verbindung erzwingen wenn Host leer ist, sonst folgt später Exception.

                if (IPS_GetProperty($ParentID, 'Open') <> $ParentOpen)
                {
                    IPS_SetProperty($ParentID, 'Open', $ParentOpen);
                    $change = true;
                }
                if ($change)
                {
                    @IPS_ApplyChanges($ParentID);                    
                }
            }
        }
        
        // Beispiel innerhalb einer Gateway/Splitter Instanz
        public function ReceiveData($JSONString) {

            // Empfangene Daten vom I/O
            $data = json_decode($JSONString);
            IPS_LogMessage("ReceiveData", utf8_decode($data->Buffer));

            // Hier werden die Daten verarbeitet
            $unpacked_data = unpack('N*',utf8_decode($data->Buffer));
            //print_r ($unpacked_data);
            $NOV_data = json_encode(array_slice($unpacked_data,13,148));
           
            // Weiterleitung zu allen Gerät-/Device-Instanzen
            $this->SendDataToChildren(json_encode(Array("DataID" => "{7553773d-45e4-4334-9d37-dbf9c7ca1778}", "Buffer" => $NOV_data)));
        }
        // Beispiel innerhalb einer Gateway/Splitter Instanz
        public function ForwardData($JSONString) {

            // Empfangene Daten von der Device Instanz
            $data = json_decode($JSONString);
            IPS_LogMessage("ForwardData", utf8_decode($data->Buffer));

            // Hier würde man den Buffer im Normalfall verarbeiten
            // z.B. CRC prüfen, in Einzelteile zerlegen
             
            // Weiterleiten zur I/O Instanz
            $resultat = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $data->Buffer)));

            // Weiterverarbeiten und durchreichen
            return $resultat;

        }
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function MeineErsteEigeneFunktion() {
            // Selbsterstellter Code
        }
        
        protected function GetParent()
        {
            $instance = IPS_GetInstance($this->InstanceID);
            return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
        }
    }
?>