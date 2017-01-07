<?php
    // Klassendefinition
    class KoVerkehr extends IPSModule {
 
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
            
            $this->RegisterPropertyString("Ziel1","München,Marienplatz+2");
            $this->RegisterPropertyString("Ziel2","München,Marienplatz+3");
            $this->RegisterPropertyString("Ziel3","München,Marienplatz+4");
            $this->RegisterPropertyString("Heimatort","München,Marienplatz+1");
            $this->RegisterPropertyString("Google","AIzaSyDwJzu3ieK0qM4qRVqMgwIsJGW3gzHK08Y");
            
            $this->RegisterVariableString("KoStauHTML", "Staumeldungen HTML", "~HTMLBox", 0);
            $this->RegisterTimer("Update", 30000, 'Verk_GetVerkehr($_IPS[\'TARGET\']);');
            
            
            
                    
        }
        
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            if (($this->ReadPropertyString("Google") != "") && ($this->ReadPropertyString("Heimatort") != "") && ($this->ReadPropertyString("Ziel1") != "" || $this->ReadPropertyString("Ziel2") != "" || $this->ReadPropertyString("Ziel3") != "")){
              
              //Instanz ist aktiv
              $this->SetStatus(102);
            } else {
              //Instanz ist inaktiv
              $this->SetStatus(104);
            }
            

                      
        }
        
        public function Destroy()
        {
            //Timer entfernen
            $this->UnregisterTimer("Update");
          

            //Never delete this line!!
            parent::Destroy();
         
        }
        
        
        
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function GetVerkehr() {
            // Selbsterstellter Code
          
          $ziel[0]  = $this->ReadPropertyString("Ziel1");
          $ziel[1]  = $this->ReadPropertyString("Ziel2");
          $ziel[2]  = $this->ReadPropertyString("Ziel3");
          $start[0] = $this->ReadPropertyString("Heimatort");
          $google   = $this->ReadPropertyString("Google");
          $duration = array();
          
          foreach ($ziel as $key => $value) {
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$start[0]."&destinations=".$value."&mode=driving&language=de-DE&departure_time=now&key=".$google;
            $key = file_get_contents($url);
            
            if ($key != FALSE ) {
              $result = json_decode($key, true);
              //Prüfen ob die Antwort OK, bei zu vielen Anfragen an den Server gibt es ein Fehler
              if ($result['status'] === 'OK') {
                if ($result["rows"]["0"]["elements"]["0"]["status"] === "OK") {
                  $duration[$key]['aktuell'] = $result["rows"]["0"]["elements"]["0"]["duration"]["value"];
                  $duration[$key]['traffic'] = $result["rows"]["0"]["elements"]["0"]["duration_in_traffic"]["value"];
                  $city = explode(",",$value);
                  $duration[$key]['startcity'] = $city[0];
                }
                else {
                  $duration[$key]['aktuell'] = 0;
                  $duration[$key]['traffic'] = 0;
                  $duration[$key]['startcity'] = $result["rows"]["0"]["elements"]["0"]["status"];
                }
              }
              else {
                $duration[0]['aktuell'] = 0;
                $duration[0]['traffic'] = 0;
                $duration[0]['startcity'] = $result["status"];
              }
            }
            else {
              $duration[$key]['aktuell'] = 0;
              $duration[$key]['traffic'] = 0;
              $duration[$key]['startcity'] = "Fehler";
            }
            
          }
          $this->WriteVerkehrHTML($duration);
          
        }
        
        protected function WriteVerkehrHTML($duration)
        {
          if ($this->GetIDForIdent("KoStauHTML")) {
            
            $html = '';
            $html .= '<table class="tab-HMTL-stat">';
            $html .= '  <thead><tr><th>Ziel</th><th>Normal</th><th>Aktuell</th></tr></thead><tbody>';
            foreach ($duration as $key => $value) {
              $html .= '  <tr><td>'.$value['startcity'].'</td><td>'.gmdate('H:i:s', $value['aktuell']).'</td><td>'.gmdate('H:i:s', $value['traffic']).'</td></tr>';
            }
            $html .= '<input type="button" value="Datum" onClick="alert(\''.date("d.m.Y",time()).'\');" /></tbody></table>';
            SetValue($this->GetIDForIdent("KoStauHTML"), $html);
          }
            
        }
        
        
        protected function GetParent()
        {
            $instance = IPS_GetInstance($this->InstanceID);
            return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
        }
    }
?>