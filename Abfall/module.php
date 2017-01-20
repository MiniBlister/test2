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
            $this->RegisterPropertyString("nameMuell" . $i, "");
            $this->RegisterPropertyInteger("artMuell" . $i, 0);
            $this->RegisterPropertyBoolean("activeMuell" . $i, false);
        }
        $this->RegisterPropertyBoolean("htmloutput", false);
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $id = array();

        // Standardmasssig wird das Event geloescht
        if (IPS_EventExists(@IPS_GetEventIDByName("Update", $this->InstanceID))) {
            IPS_DeleteEvent(@IPS_GetEventIDByName("Update", $this->InstanceID));
        }
        if ($this->ValidateConfiguration() == false) {
            // Loesche das Event wenn die Validierung der Meull Eingaben nicht korrekt ist
            return;
        }

        // Varriable Muell 1 erstellen wenn noch nicht vorhanden; umbennen wenn die Varraible schon vorhanden
        for ($i = 0; $i < KOAB_COUNT; $i++) {
            if ($this->ReadPropertyBoolean('activeMuell' . $i) == 1 AND @$this->GetIDForIdent('muell' . $i) !== false) {
                IPS_SetName($this->GetIDForIdent('muell' . $i), $this->ReadPropertyString('nameMuell' . $i));
            } elseif ($this->ReadPropertyBoolean('activeMuell' . $i) == 1 AND @$this->GetIDForIdent('muell' . $i) === false) {
                $this->RegisterVariableString('muell' . $i, $this->ReadPropertyString('nameMuell' . $i), "", $i);
                $this->EnableAction('muell' . $i);
            } elseif ($this->ReadPropertyBoolean('activeMuell' . $i) == 0 AND @$this->GetIDForIdent('muell' . $i) !== false) {
                IPS_DeleteVariable($this->GetIDForIdent('muell' . $i));
            } else {
                
            }
        }
        if ($this->ReadPropertyBoolean('htmloutput') == true) {
            $this->RegisterVariableString('htmloutput' , "HTMLBox", "~HTMLBox", 99);
        }
        
        $eid = IPS_CreateEvent(1);
        //IPS_SetEventCyclicTimeFrom($eid, 0, 0, 0);
        IPS_SetEventCyclicTimeFrom($eid, date('G'), date('i'), date('s')+1);
        IPS_SetParent($eid, $this->InstanceID);
        IPS_SetName($eid, "Update");
        IPS_SetEventActive ($eid, true);
        IPS_SetEventScript($eid, 'KoAbfall_Update($_IPS[\'TARGET\']);');
    }

    public function Destroy() {
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
        for ($i = 0; $i < KOAB_COUNT; $i++) {
            // Checken ob die Function activ ist und ob es die varriable gibt
            if ($this->ReadPropertyBoolean('activeMuell' . $i) == 1 && @$this->GetIDForIdent('muell' . $i) !== false) {
                $datearray = $this->GetDateArray($this->GetIDForIdent('muell' . $i));
                $datearray['type'] = $this->ReadPropertyInteger('muell' . $i);
            }
        }
        if (@$this->GetIDForIdent('htmloutput') !== false) {
          print_r ($datearray);  
        }
    }

    protected function GetParent() {
        $instance = IPS_GetInstance($this->InstanceID);
        return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
    }

    private function ValidateConfiguration() {

        for ($i = 0; $i < KOAB_COUNT; $i++) {
            if ($this->ReadPropertyBoolean('activeMuell' . $i) == 1 AND $this->ReadPropertyString('nameMuell' . $i) == "") {
                $this->SetStatus(201);
                return false;
            }
        }
        $this->SetStatus(102);
        return true;
    }
     /**
     *  
     *  GetDateString($id);
     *  Liest den String ein und gibt diesen zurück
     */
    private function GetDateArray($id) {
        
        $s = GetValueString($id);
        if ($s != "") {
            return $this->GetNextDate($s);
        }
        else {
            throw new Exception("No valid dates in varriable");
        }
    }
    
     /**
     *  
     *  GetDateString($id);
     *  Liest den String ein und gibt diesen zurück
     */
    private function GetNextDate($datestr) {
        //Date of Today
        $today = strtotime(date('d.m.Y'));
        //Den String aufteilen in ein Array
        $dates_array = explode (';',$datestr);
        $i = -1;
        foreach($dates_array as $key => $val) {
            if ($i == -1) {
                $kodate[0] = ((strtotime($val)-$today) / 86400);
		$kodate[1] = round($kodate[0],0);
	        $kodate[2] = strtotime($val);
		if ($kodate[0] >= 0) {
                    $i = $key;
		}
            }
	}
        return $kodate;       
    }
    
    


    public function RequestAction($Ident, $Value) {

        switch ($Ident) {
            case ( preg_match('/muell.*/', $Ident) ? true : false ):
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            case 'SATURATION':
            case 'BRIGHTNESS':
                $Value = $Value;
                break;
            default:
                throw new Exception("Invalid ident");
        }
    }

}

?>