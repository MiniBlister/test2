<?php
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
            $this->RegisterPropertyString("nameMuell1", "");
            $this->RegisterPropertyString("nameMuell2", "");
            $this->RegisterPropertyString("nameMuell3", "");
            $this->RegisterPropertyString("nameMuell4", "");
            $this->RegisterPropertyInteger("artMuell1", 0);
            $this->RegisterPropertyInteger("artMuell2", 0);
            $this->RegisterPropertyInteger("artMuell3", 0);
            $this->RegisterPropertyInteger("artMuell4", 0);
            $this->RegisterPropertyBoolean("activeMuell1", false);
            $this->RegisterPropertyBoolean("activeMuell2", false);
            $this->RegisterPropertyBoolean("activeMuell3", false);
            $this->RegisterPropertyBoolean("activeMuell4", false);
                    
        }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
           

            $this->ValidateConfiguration();
                      
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
        public function MeineErsteEigeneFunktion() {
            // Selbsterstellter Code
          
        }
        
        protected function GetParent()
        {
            $instance = IPS_GetInstance($this->InstanceID);
            return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
        }
        
        
        private function ValidateConfiguration() {
          if ($this->ReadPropertyBoolean('activeMuell1') == 1 AND $this->ReadPropertyString('nameMuell1') == "" ) {
            $this->SetStatus(201);
            return;
          }
          if ($this->ReadPropertyBoolean('activeMuell2') == 1 AND $this->ReadPropertyString('nameMuell2') == "" ) {
            $this->SetStatus(202);
            return;
          }
          if ($this->ReadPropertyBoolean('activeMuell3') == 1 AND $this->ReadPropertyString('nameMuell3') == "" ) {
            $this->SetStatus(203);
            return;
          }
          if ($this->ReadPropertyBoolean('activeMuell4') == 1 AND $this->ReadPropertyString('nameMuell4') == "" ) {
            $this->SetStatus(204);
            return;
          }
          $this->SetStatus(102);
          
        }          
        
        
    }
?>