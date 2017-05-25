<?

// SendDataToParent (79827379-F36E-4ADA-8A95-5F8D1DC92FA9)
// SendDateToChild (B75DE28A-A29F-4B11-BF9D-5CC758281F38)
include_once(__DIR__ . "/../XiaomTraits.php");

/**
 * bla bla bla Erklärung Doku bla
 * 
 * @property integer $ParentID 
 * @property string $sid
 * @property array $SendQueue
 * @property string $Buffer
 */
class XiaomiGatewaySplitter extends ipsmodule
{

    // use fügt bestimmte Traits dieser Klasse hinzu.
    use BufferHelper, // Enthält die Magische Methoden __get und __set damit wir bequem auf die Instanz-Buffer zugreifen können.
        Semaphore, // Sorgt dafür dass nicht mehrere Threads gleichzeitig auf z.B. den Buffer zugreifen.
        DebugHelper, // Erweitert die SendDebug Methode von IPS um Arrays und Objekte.
        InstanceStatus // Diverse Methoden für die Verwendung im Splitter
    {
        InstanceStatus::MessageSink as IOMessageSink; // MessageSink gibt es sowohl hier in der Klasse, als auch im Trait InstanceStatus. Hier wird für die Methode im Trait ein Alias benannt.
    }

    // public $sidmode; // Das geht nicht, da alle Daten flüchtig sind!
    // Mit __set und  __get wird der SetBuffer und GetBuffer genutzt (siehe BufferHelper Trait)

    /**
     * Interne Funktion des SDK.
     * Wird immer ausgeführt wenn IPS startet und wenn eine Instanz neu erstellt wird.
     * @access public
     */
    public function Create()
    {
        // Diese Zeile nicht löschen.
        parent::Create();
        //Always create our own MultiCast I/O, when no parent is already available
        $this->RequireParent("{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}");

        $this->RegisterPropertyBoolean("Open", false);
        $this->RegisterPropertyString("Host", "");
        $this->RegisterTimer('KeepAlive', 0, 'XiSplitter_KeepAlive($_IPS[\'TARGET\']);');

        // Alle Instanz-Buffer initialisieren
        $this->sid = "";
        $this->SendQueue = array();

        // Gibt Fehler beim IPS-Neustart !
        /*
          $pid = $this->GetParent();
          if ($pid)
          {
          //Set SendHost Property of the MultiCast I/O
          IPS_SetProperty($pid, "Host", "192.168.178.47");
          //Set BindPort Property of the MultiCast I/O
          IPS_SetProperty($pid, "Port", "9898");
          //Set MulticastIP Property of the MultiCast I/O
          IPS_SetProperty($pid, "MulticastIP", "224.0.0.50");
          //Set BindPort Property of the MultiCast I/O
          IPS_SetProperty($pid, "BindPort", "9898");
          //Set Open Property of the MultiCast I/O
          IPS_SetProperty($pid, "Open", true);
          //Apply Changes
          IPS_ApplyChanges($pid);
          } */
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges()
    {
        // Wir wollen wissen wann IPS fertig ist mit dem starten, weil vorher funktioniert der Datenaustausch nicht.
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        // Wenn sich unserer IO ändert, wollen wir das auch wissen.
        $this->RegisterMessage($this->InstanceID, DM_CONNECT);
        $this->RegisterMessage($this->InstanceID, DM_DISCONNECT);

        // Wenn Kernel nicht bereit, dann warten... IPS_KERNELSTARTED/KR_READY kommt ja gleich
        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;

        // Kurzinfo dieser Instanz setzen wir später auf die sid
        //$this->SetSummary($this->ReadPropertyString('Host'));
        // SendQueue leeren
        $this->SendQueue = array();

        // Config prüfen
        $Open = $this->ReadPropertyBoolean('Open');

        // Erstmal gehen wir davon aus, das unsere Instanz 'aktiv' wird.
        $NewState = IS_ACTIVE;

        if (!$Open) // Kein Haken bei 'Open' gesetzt, also alles ignorieren.
            $NewState = IS_INACTIVE; // Und auf Inactive setzen.
        else
        {
            // Wenn kein Host eingetragen:
            if ($this->ReadPropertyString('Host') == '')
            {
                $NewState = IS_EBASE + 2; // Status auf Fehlerzustand 202
                $Open = false; // Und bitte nicht den Parent öffnen.
                echo 'Host is empty'; // Fehlermeldung für die Konsole, bzw. Log.
            }
        }
        // Unseren Parent merken und auf dessen Statusänderungen registrieren.
        $this->RegisterParent();

        parent::ApplyChanges();

        // Zwangskonfiguration des IO-ClientSocket
        if ($this->ParentID > 0) // ParentID wurde mit $this->RegisterParent() gesetzt !
        {
            // Übergeben wird die gewünschte Konfig an den Parent.
            IPS_SetConfiguration($this->ParentID, $this->GetConfigurationForParent());
            // Eine eventuelle Fehlermeldung beim übernehmen wollen wird vermeiden mit dem @.
            // Und wenn sich dadurch der Status des Parent ändert, so wird diese Nachricht im MessageSink verarbeitet.
            // Dort wird dann auch der Keep-Alive Timer und 
            @IPS_ApplyChanges($this->ParentID);
        }

        // Wenn Konfig Gültig, und Haken 'Open' gesetzt ist,
        // aber der Parent nicht in aktiv steht bzw fehlt,
        // dann setzen wir uns auf Inactive.
        if ($Open && !$this->HasActiveParent())
            $NewState = IS_INACTIVE;

        // Neuen Status setzen.
        $this->SetStatus($NewState);
    }

    /**
     * Interne Funktion des SDK.
     * Verarbeitet alle Nachrichten auf die wir uns registriert haben.
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        // Zuerst mal den Trait InstanceStatus die Nachtichten verarbeiten lassen:
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);
        // Und jetzt wir:
        switch ($Message)
        {
            case IPS_KERNELSTARTED: // Nach dem IPS-Start
                $this->KernelReady(); // Sagt alles.
                break;
        }
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     * @access protected
     */
    protected function KernelReady()
    {
        $this->ApplyChanges(); // Einfach noch mal und dann kommen wir auch über die RunLevel Abfrage hinaus.
        $this->RefreshAllDevices();
    }

    /**
     * Wird über den Trait InstanceStatus ausgeführt wenn sich der Parent ändert.
     * @access protected
     */
    protected function ForceIORefresh()
    {
        $this->ApplyChanges(); // Einfach noch mal, wir wollen ja den neuen Parent kennen und konfigurieren.
    }

    /**
     * Wird über den Trait InstanceStatus ausgeführt wenn sich der Status des Parent ändert.
     * @access protected
     * @param int $State Der neue Status des Parent.
     */
    protected function IOChangeState($State)
    {
        if ($State == IS_ACTIVE) // Parent ist Aktiv geworden
        {
            $this->SetStatus(IS_ACTIVE); // Na wir dann auch
            $this->SetTimerInterval('KeepAlive', 60000); // KeepAlive starten
            $this->RefreshAllDevices();
        }
        elseif ($State == IS_INACTIVE) // Oh, Parent ist inaktiv geworden
        {
            $this->SetStatus(IS_INACTIVE); // Na wir dann auch
            $this->SetTimerInterval('KeepAlive', 0); // Und kein Keep-Alive mehr.
        }
        elseif ($State >= IS_EBASE) // Parent in Fehler.
        {
            $this->SetTimerInterval('KeepAlive', 0); // Und kein Keep-Alive mehr.
        }
    }

    /**
     * Interne Funktion des SDK.
     * Wird von der Console aufgerufen, wenn 'unser' IO-Parent geöffnet wird.
     * Außerdem nutzen wir sie in Applychanges, da wir dort die Daten zum konfigurieren nutzen.
     * @access public
     */
    public function GetConfigurationForParent()
    {
        // Unsere Daten an den IO übergeben.
        $Config['Open'] = $this->ReadPropertyBoolean('Open');
        $Config['Host'] = $this->ReadPropertyString('Host');
        $Config['Port'] = 9898;
        $Config['MulticastIP'] = "224.0.0.50";
        $Config['BindPort'] = 9898;
        $Config['EnableBroadcast'] = false;
        $Config['EnableReuseAddress'] = true;
        $Config['EnableLoopback'] = false;

        // Wenn kein Host
        if ($Config['Host'] == '')
            $Config['Open'] = false; // Dann den IO nicht aktiv schalten.
// Konfig-Daten zurückgeben.
        return json_encode($Config);
    }

    /** Wird vom Timer aufgerufen, sollte NIE passieren, da wir den Timer bei jedem heartbeat zurücksetzen.
     *  Wenn der Timer also auslöst, ist das Gateway offline :(
     */
    public function KeepAlive()
    {
        $this->SetStatus(IS_EBASE + 3);
        $this->SetTimerInterval('KeepAlive', 0); // Und kein Keep-Alive mehr.
    }

    /** Einmal allen verbundenen Devices die Konfig übernehmen, damit sie ihre Stati holen können.
     * 
     */
    protected function RefreshAllDevices()
    {
        $InstanceIDList = IPS_GetInstanceListByModuleID("{B237D1DF-B9B0-4A8D-8EC5-B4F7A88E54FC}");
        foreach ($InstanceIDList as $InstanceID)
        {
            // Nur eigene Geräte
            if (IPS_GetInstance($InstanceID)['ConnectionID'] == $this->ParentID)
                IPS_ApplyChanges($InstanceID);
        }
    }

    /** Aktuell nur zum testen, später wird diese Funktion private und über ForwardData vom Konfigurator ausgeführt.
     * 
     * @return array
     */
    public function GetAllDevices()
    {
        return $this->Send("get_id_list");
    }

    // Von Device kommend, mit Send versenden und die Antwort zurückgeben.
    public function ForwardData($JSONString)
    {
        // Empfangene Daten von der Device Instanz
        $ForwardData = json_decode($JSONString);
        unset($ForwardData->DataID);
        $this->SendDebug('Forward', $ForwardData, 0);

        // Senden
        if (!property_exists($ForwardData, 'sid'))
            $ForwardData->sid = $this->sid;
        if (property_exists($ForwardData, 'model'))
            if (property_exists($ForwardData, 'data'))
                $result = $this->Send($ForwardData->cmd, $ForwardData->sid, $ForwardData->model, $ForwardData->data);
            else
                $result = $this->Send($ForwardData->cmd, $ForwardData->sid, $ForwardData->model);
        else
            $result = $this->Send($ForwardData->cmd, $ForwardData->sid);

        // Antwort (Array) serialisiert zurück an den Child mit return
        return serialize($result);
    }

    /** Versenden
     * 
     * @param type $cmd
     * @param type $sid
     * @param type $model
     * @param type $Data
     * @return boolean | array False im Fehlerfall. Das Array mit 'data' wenn OK
     */
    private function Send($cmd, $sid, $model = null, $Data = null)
    {
        if (IPS_GetInstance($this->InstanceID)['InstanceStatus'] != IS_ACTIVE)
        {
            trigger_error('Instance Xiaomi Gateway-Splitter (' . $this->InstanceID . ') inactiv. ', E_USER_NOTICE);
            return false;
        }
        if (!$this->HasActiveParent())
        {
            trigger_error('Instance Xiaomi Gateway-Splitter (' . $this->InstanceID . ') has no active parent. ', E_USER_NOTICE);
            return false;
        }

        // Einmal ein leeres Array in den Buffer schieben mit Index sid und cmd
        $SendQueue = $this->SendQueue;
        $SendQueue[$sid][$cmd] = array();
        $this->SendQueue = $SendQueue;

        // Daten aufbereiten
        $SendData = array(
            "cmd" => $cmd,
            "sid" => $sid);

        if ($model !== NULL)
            $SendData["model"] = $model;

        if ($Data !== NULL)
            $SendData["data"] = json_encode($Data);

        $this->SendDebug('Send', $SendData, 0);

        try     // versenden
        {
            $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($SendData))));
        }
        catch (Exception $exc)
        {
            // oh, Fehler.
            // Den Index wieder entfernene.
            $SendQueue = $this->SendQueue;
            unset($SendQueue[$sid][$cmd]);
            $this->SendQueue = $SendQueue;
            return false;
        }
        // Warten auf Änderung des Buffers durch ReceiveData
        $Result = false;
        for ($x = 0; $x < 500; $x++)
        {
            if (count($this->SendQueue[$sid][$cmd]) > 0)
            { //found 
                $SendQueue = $this->SendQueue;
                $Result = $SendQueue[$sid][$cmd];
                unset($SendQueue[$sid][$cmd]);
                $this->SendQueue = $SendQueue;
                break;
            }
            IPS_Sleep(10);
        }

        $this->SendDebug('Result', $Result, 0);

        return $Result;
    }

    private function UpdateQueue($cmd, $sid, $data, $model)
    {
        if (isset($this->SendQueue[$sid][$cmd]))
        {
            $SendQueue = $this->SendQueue;
            $SendQueue[$sid][$cmd] = json_decode($data, true);
            $SendQueue[$sid][$cmd]['model'] = $model;
            $this->SendQueue = $SendQueue;
        }
    }

    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);

        $gateway = json_decode($data->Buffer);
        // Das bei mehr als einem Gateway wir immer alle Nachrichten von allen Gateway bekommen,
        // stört uns nicht. Die Devices filtern über ihre SID und wenn die Anfrage in der SendQueue
        // fehlt war es wohl nicht 'unser' Gateway.
        $this->SendDebug("Receive", json_encode($gateway), 0);
        switch ($gateway->cmd)
        {
            case "heartbeat": //Event ? Dann hier verarbeiten
                // heartbeat vom Gateway mit unerer IP ?
                if (($gateway->model == "gateway") && (json_decode($gateway->data)->ip == $this->ReadPropertyString('Host')))
                {
                    // KeepAlive Timer neustarten
                    $this->SetTimerInterval('KeepAlive', 0);
                    $this->SetTimerInterval('KeepAlive', 60000);

                    //We need to check IP Address of our Gateway and Update sid accordingly
                    if ($this->sid != $gateway->sid)
                        $this->sid = $gateway->sid;
                }
                else // alle anderen heartbeats an die Childs senden, die finden den Weg über den ReceiveFilter
                    $this->SendDataToChildren(json_encode(Array("DataID" => "{B75DE28A-A29F-4B11-BF9D-5CC758281F38}", "Buffer" => $data->Buffer)));

                break;
            case "write_ack": // Antwort -> Abgleich mit der SendQueue
                $this->UpdateQueue("write", $gateway->sid, $gateway->data, $gateway->model);
                break;
            case "read_ack": // Antwort -> Abgleich mit der SendQueue
                $this->UpdateQueue("read", $gateway->sid, $gateway->data, $gateway->model);
                break;
            case "get_id_list_ack": // Antwort -> Abgleich mit der SendQueue
                $this->UpdateQueue("get_id_list", $gateway->sid, $gateway->data, $gateway->model);
                break;
            case 'report': // Event für Childs, weitersenden
                $this->SendDataToChildren(json_encode(Array("DataID" => "{B75DE28A-A29F-4B11-BF9D-5CC758281F38}", "Buffer" => $data->Buffer)));
                break;
            default:
                $this->SendDebug('Fehlendes cmd', $gateway->cmd, 0);
                break;
        }
    }

    /**
     * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
     *
     * ABC_MeineErsteEigeneFunktion($id);
     *
     */
    //Set the IP Address of Gateway in case this will be provided
    /*
      private function SetGatewayIP($gateway)
      {

      $gatewayip = json_decode($gateway->data);
      $pid = $this->GetParent();
      if ($pid)
      {
      if (IPS_GetProperty($pid, "Host") != $gatewayip->ip)
      {
      //Set the Host Address to the Address provided by the Gateway witht the Multicast Address
      IPS_SetProperty($pid, "Host", $gatewayip->ip);
      //Apply Changes
      IPS_ApplyChanges($pid);
      }
      }
      }

      //Get ID list and details for Sensors
      public function GetList($ids, $model, $sid)
      {
      foreach ($ids as $key => $value)
      {
      $payload = array("cmd" => "read", "sid" => $value);
      $return = @$this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($payload))));
      $x = 0;
      while (empty($this->GetBuffer($value)) AND $x < 10)
      {
      IPS_Sleep(1000);
      $this->SendDebug("test", $this->GetBuffer($value), 0);
      $this->SendDebug("test", $value, 0);
      $x++;
      }
      }
      $this->pushtochild($ids, $model, $sid);
      return $result;
      }

      public function pushtochild($ids, $model, $sid)
      {
      $sidmode["cmd"] = "get_modes";
      $sidmode["model"] = $model;
      $sidmode["sid"] = $sid;
      foreach ($ids as $key => $value)
      {
      $sidmode['data'][$key]['sid'] = $value;
      $sidmode['data'][$key]['model'] = $this->GetBuffer($value);
      }

      $this->SendDebug("Push Data SID:", json_encode($sidmode), 0);
      $this->SendDataToChildren(json_encode(Array("DataID" => "{B75DE28A-A29F-4B11-BF9D-5CC758281F38}", "Buffer" => $sidmode)));
      }
     */
}

?>