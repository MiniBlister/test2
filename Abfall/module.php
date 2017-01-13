<?php
    define("KOAB_COUNT", 4);
    
    // Klassendefinition
    class KoAbfall extends IPSModule {
 
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
            for ($i = 0; $i < KOAB_COUNT; $i++) {
              $this->RegisterPropertyString("nameMuell".$i, "");
              $this->RegisterPropertyInteger("artMuell".$i, 0);
              $this->RegisterPropertyBoolean("activeMuell".$i,  false);     
            }            
            $this->RegisterPropertyBoolean("htmloutput",    false);
                    
        }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            $id = array();
          
            if ($this->ValidateConfiguration() == false){
                // Loesche das Event wenn die Validierung der Meull Eingaben nicht korrekt ist
              if (IPS_EventExists (@IPS_GetEventIDByName("Update", $this->InstanceID))) {
                IPS_DeleteEvent (@IPS_GetEventIDByName("Update", $this->InstanceID) );
              }
              return;
            }
            
            // Varriable Muell 1 erstellen wenn noch nicht vorhanden; umbennen wenn die Varraible schon vorhanden
            for ($i = 0; $i < KOAB_COUNT; $i++) {
              if ($this->ReadPropertyBoolean('activeMuell'.$i) == 1 AND @$this->GetIDForIdent('muell'.$i) !== false) {
                IPS_SetName($this->GetIDForIdent('muell'.$i), $this->ReadPropertyString ('nameMuell'.$i));
                $id[$i] = $this->GetIDForIdent('muell'.$i);
              }     
              elseif ($this->ReadPropertyBoolean('activeMuell'.$i) == 1 AND @$this->GetIDForIdent('muell'.$i) === false) {
                $this->RegisterVariableString('muell'.$i, $this->ReadPropertyString ('nameMuell'.$i));
                $id[$i] = $this->GetIDForIdent('muell'.$i);
              }
              elseif ($this->ReadPropertyBoolean('activeMuell'.$i) == 0 AND @$this->GetIDForIdent('muell'.$i) !== false) {
                IPS_DeleteVariable ($this->GetIDForIdent('muell'.$i));
                $id[$i] = 0;
              }
              else {
                $id[$i] = 0;
              }
            }

            $eid = IPS_CreateEvent (1);
            IPS_SetEventCyclicTimeFrom($eid, 0, 0, 0);
            IPS_SetParent($eid, $this->InstanceID);
            IPS_SetEventScript($eid, 'KoAbfall_Update($_IPS[\'TARGET\']);');

        }
        
        public function Destroy()
        {
            //Timer entfernen
            //$this->UnregisterTimer("Update");

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
        public function Update() {
            // Selbsterstellter Code
          
        }
        
        protected function GetParent()
        {
            $instance = IPS_GetInstance($this->InstanceID);
            return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
        }
        
       
        
        private function ValidateConfiguration() {
          
          for ($i = 0; $i < KOAB_COUNT; $i++) {
            if ($this->ReadPropertyBoolean('activeMuell'.$i) == 1 AND $this->ReadPropertyString('nameMuell'.$i) == "") {
              $this->SetStatus(201);
              return false;
            }
          }
          $this->SetStatus(102);
          return true;
        }          
        
        
    }
?>