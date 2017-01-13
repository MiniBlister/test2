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
            $this->RegisterPropertyString("nameMuell0", "");
            $this->RegisterPropertyString("nameMuell1", "");
            $this->RegisterPropertyString("nameMuell2", "");
            $this->RegisterPropertyString("nameMuell3", "");
            $this->RegisterPropertyInteger("artMuell0", 0);
            $this->RegisterPropertyInteger("artMuell0", 0);
            $this->RegisterPropertyInteger("artMuell0", 0);
            $this->RegisterPropertyInteger("artMuell0", 0);
            $this->RegisterPropertyBoolean("activeMuell0",  false);
            $this->RegisterPropertyBoolean("activeMuell1",  false);
            $this->RegisterPropertyBoolean("activeMuell2",  false);
            $this->RegisterPropertyBoolean("activeMuell3",  false);
            
            $this->RegisterPropertyBoolean("htmloutput",    false);
                    
        }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
          
            if ($this->ValidateConfiguration() == false){
              return;
            }
            
            // Varriable Muell 1 erstellen wenn noch nicht vorhanden; umbennen wenn die Varraible schon vorhanden
            for ($i = 1; $i <= KOAB_COUNT; $i++) {
              if ($this->ReadPropertyBoolean('activeMuell'.$i) == 1 AND @$this->GetIDForIdent('muell'.$i) !== false) {
                IPS_SetName($this->GetIDForIdent('muell'.$i), $this->ReadPropertyString ('nameMuell'.$i));
                $id
              }     
              elseif ($this->ReadPropertyBoolean('activeMuell'.$i) == 1 AND @$this->GetIDForIdent('muell'.$i) === false) {
                $this->RegisterVariableString('muell'.$i, $this->ReadPropertyString ('nameMuell'.$i));
              }
              elseif ($this->ReadPropertyBoolean('activeMuell'.$i) == 0 AND @$this->GetIDForIdent('muell'.$i) !== false) {
                IPS_DeleteVariable ($this->GetIDForIdent('muell'.$i));
              }
              else { 
              }
            }
            
            
            
            
   
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
          
          for ($i = 0; $i <= KOAB_COUNT; $i++) {
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