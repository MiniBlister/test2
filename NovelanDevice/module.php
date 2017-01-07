<?php
    // Klassendefinition
    class NovDevice extends IPSModule {
 
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
            
            $this->RegisterPropertyInteger("Interval", 20);
            $this->RegisterTimer("Update", 0, 'NovDev_Test($_IPS["TARGET"]);');
            
                 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();

            //Always create our own Splitter, when no parent is already available
            $this->RequireParent("{C5A51178-2760-49DA-9175-1ED71975753C}");
            $this->RegisterVariableString("Test", "Test", "", 0);

            $this->SetTimerInterval("Update", $this->ReadPropertyInteger("Interval")*1000);
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
        
        public function Destroy()
        {
            //Timer entfernen
            $this->UnregisterTimer("Update");

            //Never delete this line!!
            parent::Destroy();
        }
        
        public function Test()
        {
                $test = utf8_encode(pack('N*',3004));      
                $this->SendDataToParent(json_encode(Array("DataID" => "{B87AC955-F258-468B-92FE-F4E0866A9E18}", "Buffer" => $test)));
                $test = utf8_encode(pack('N*',0));
                $this->SendDataToParent(json_encode(Array("DataID" => "{B87AC955-F258-468B-92FE-F4E0866A9E18}", "Buffer" => $test)));

        }
        // Beispiel innerhalb einer Geräte/Device Instanz
        public function ReceiveData($JSONString) {

            // Empfangene Daten vom Gateway/Splitter
            $data = json_decode($JSONString);
            IPS_LogMessage("ReceiveData", utf8_decode($data->Buffer));
            
            // Datenverarbeitung und schreiben der Werte in die Statusvariablen
            SetValue($this->GetIDForIdent("Test"), $data->Buffer);

        }
        
        protected function CreateVarArray () {
            
            $NOV_array = array();
            $NOV_array[] = array ('Name' => "Temperatur_TVL_Vorlauf", 'Dataset' => 0);
            $NOV_array[] = array ('Name' => "Temperatur_TRL_Ruecklauf", 'Dataset' => 1);
            $NOV_array[] = array ('Name' => "Sollwert_TRL_HZ_Ruecklauf_Soll", 'Dataset' => 2);
            $NOV_array[] = array ('Name' => "Temperatur_TRL_ext_Ruecklauf_Extern", 'Dataset' => 3);
            $NOV_array[] = array ('Name' => "Temperatur_THG_Heissgas", 'Dataset' => 4);
            $NOV_array[] = array ('Name' => "Temperatur_TA_Aussenfuehler", 'Dataset' => 5);
            $NOV_array[] = array ('Name' => "Mitteltemperatur", 'Dataset' => 6);
            $NOV_array[] = array ('Name' => "Temperatur_TBW", 'Dataset' => 7);
            $NOV_array[] = array ('Name' => "Temperatur_TWE_Waermequelle_Ein", 'Dataset' => 9);
            $NOV_array[] = array ('Name' => "Temperatur_TWA_Waermequelle_Aus", 'Dataset' => 10);
            $NOV_array[] = array ('Name' => "Temperatur_TFB1", 'Dataset' => 11);
            $NOV_array[] = array ('Name' => "Temperatur_RFV", 'Dataset' => 13);
            $NOV_array[] = array ('Name' => "Temperatur_TFB2", 'Dataset' => 14);
            $NOV_array[] = array ('Name' => "Temperatur_TSK", 'Dataset' => 16);
            $NOV_array[] = array ('Name' => "Temperatur_TSS", 'Dataset' => 17);
            $NOV_array[] = array ('Name' => "Temperatur_TEE", 'Dataset' => 18);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitVD1", 'Dataset' => 46);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitImpVD1", 'Dataset' => 47);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitVD2", 'Dataset' => 48);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitImpVD2", 'Dataset' => 49);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitZWE1", 'Dataset' => 50);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitZWE2", 'Dataset' => 51);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitZWE3", 'Dataset' => 52);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitWP", 'Dataset' => 53);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitHz", 'Dataset' => 54);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitBW", 'Dataset' => 55);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitKue", 'Dataset' => 56);
            $NOV_array[] = array ('Name' => "Time_WPein_akt", 'Dataset' => 57);
            $NOV_array[] = array ('Name' => "Time_ZWE1_akt", 'Dataset' => 58);
            $NOV_array[] = array ('Name' => "Time_ZWE2_akt", 'Dataset' => 59);
            $NOV_array[] = array ('Name' => "Time_SSPAUS_akt", 'Dataset' => 61);
            $NOV_array[] = array ('Name' => "Time_SSPEIN_akt", 'Dataset' => 62);
            $NOV_array[] = array ('Name' => "Time_VDStd_akt", 'Dataset' => 63);
            $NOV_array[] = array ('Name' => "Time_HRM_akt", 'Dataset' => 64);
            $NOV_array[] = array ('Name' => "Time_HRW_akt", 'Dataset' => 65);
            $NOV_array[] = array ('Name' => "Time_LGS_akt", 'Dataset' => 66);
            $NOV_array[] = array ('Name' => "Time_SBW_akt", 'Dataset' => 67);
            $NOV_array[] = array ('Name' => "Code_WP_akt", 'Dataset' => 68);
            $NOV_array[] = array ('Name' => "BIV_Stufe_akt", 'Dataset' => 69);
            $NOV_array[] = array ('Name' => "WP_BZ_akt", 'Dataset' => 70);
            $NOV_array[] = array ('Name' => "AnzahlFehlerInSpeicher", 'Dataset' => 95);
            $NOV_array[] = array ('Name' => "AktuelleTimeStamp", 'Dataset' => 124);
            $NOV_array[] = array ('Name' => "Temperatur_TFB3", 'Dataset' => 127);
            $NOV_array[] = array ('Name' => "Temperatur_RFV2", 'Dataset' => 132);
            $NOV_array[] = array ('Name' => "Temperatur_RFV3", 'Dataset' => 133);
            $NOV_array[] = array ('Name' => "Zaehler_BetrZeitSW", 'Dataset' => 135);
            $NOV_array[] = array ('Name' => "WMZ_Heizung", 'Dataset' => 141);
            $NOV_array[] = array ('Name' => "WMZ_Brauchwasser", 'Dataset' => 142);
            $NOV_array[] = array ('Name' => "WMZ_Schwimmbad", 'Dataset' => 143);
            $NOV_array[] = array ('Name' => "WMZ_Seit", 'Dataset' => 144);
            $NOV_array[] = array ('Name' => "WMZ_Durchfluss", 'Dataset' => 145);
            $NOV_array[] = array ('Name' => "Time_Heissgas", 'Dataset' => 148);
       
            
        }
    }
?>