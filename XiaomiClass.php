<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XiaomiClass
 *
 * @author a0406232
 */
class XiaomiData extends stdClass {
    //put your code here
    static $MethodTyp = 0;
    static $ResultTyp = 1;
    
    /**
    * Typ der Daten
    * @access private
    * @var enum [ XiaomiData::ReportTyp, XiaomiData::ResultTyp]
    */
    private $Typ;
    
    /**
    * Name der Methode
    * @access private
    * @var string
    */
    private $Method;
    
    /**
    * Id des Xiaomi Objects
    * @access private
    * @var string
    */
    private $Id;
    
    /**
    * Parameter der Methode
    * @access private
    * @var object
    */
    private $Params;
   
    /**
     * Enthält Fehlermeldungen der Methode
     * @access private
     * @var object
     */
    private $Error;    

    /**
     * Antwort der Methode
     * @access private
     * @var object
     */
    private $Result;


    /**
     * Erstellt ein Xiaomi Objekt.
     * 
     * @access public
     * @param string $Method [optional] Xiaomi-Methode
     * @param object $Params [optional] Parameter der Methode
     * @param int $Id [optional] Id des Xiaomi Objektes
     * @return XiaomiData
     */

    public function __construct($Method = null, $Params = null, $Sid = null)
    {
        if (is_null($Method))
            $this->Typ = XiaomiData::$ResultTyp;
        else
        {
            $this->Method = $Method;
            $this->Typ = XiaomiData::$MethodTyp;
        }
        if (is_array($Params))
            $this->Params = (object) $Params;
        if (is_object($Params))
            $this->Params = (object) $Params;
        if (is_null($Id))
            $this->Id = round(explode(" ", microtime())[0] * 10000);
        else {
            if ($Id > 0)
                $this->Id = $Id;
        }
        
    }
    
        /**
     * Führt eine Xiaomi-Methode aus.
     * 
     * 
     * @access public
     * @param string $name Auszuführende Xiaomi-Methode
     * @param object|array $arguments Parameter der Xiaomi-Methode.
     */
    public function __call($name, $arguments)
    {
        $this->Method = $name;
        $this->Typ = self::$MethodTyp;
        if (count($arguments) == 0)
            $this->Params = new stdClass ();
        else
        {
            if (is_array($arguments[0]))
                $this->Params = (object) $arguments[0];
            if (is_object($arguments[0]))
                $this->Params = $arguments[0];
        }
        $this->Id = round(explode(" ", microtime())[0] * 10000);
    }
    
    /**
     * Schreibt die Daten aus $Data in das Xiaomi-Objekt.
     * 
     * @access public
     * @param object $Data Muss ein Objekt sein, welche vom Xiaomi-Splitter erzeugt wurde.
     */
    public function CreateFromGenericObject($Data)
    {
        if (property_exists($Data, 'Error'))
            $this->Error = $Data->Error;
        if (property_exists($Data, 'Result'))
            $this->Result = $this->DecodeUTF8($Data->Result);
        if (property_exists($Data, 'Method'))
        {
            $this->Method = $Data->Method;
            $this->Typ = self::$MethodTyp;
        }
        else
            $this->Typ = self::$ResultTyp;
        if (property_exists($Data, 'Params'))
            $this->Params = $this->DecodeUTF8($Data->Params);
        if (property_exists($Data, 'Id'))
            $this->Id = $Data->Id;
        if (property_exists($Data, 'Typ'))
            $this->Typ = $Data->Typ;
    }
    
        /**
     * Erzeugt einen, mit der GUDI versehenen, JSON-kodierten String.
     * 
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    public function ToJSONString($GUID)
    {
        $SendData = new stdClass();
        $SendData->DataID = $GUID;
        if (!is_null($this->Id))
            $SendData->Id = $this->Id;
        if (!is_null($this->Method))
            $SendData->Method = $this->Method;
        if (!is_null($this->Params))
            $SendData->Params = $this->EncodeUTF8($this->Params);
        if (!is_null($this->Error))
            $SendData->Error = $this->Error;
        if (!is_null($this->Result))
            $SendData->Result = $this->EncodeUTF8($this->Result);
        if (!is_null($this->Typ))
            $SendData->Typ = $this->Typ;
        return json_encode($SendData);
    }

     /**
     * Schreibt die Daten aus $Data in das Kodi_RPC_Data-Objekt.
     * 
     * @access public
     * @param string $Data Ein JSON-kodierter RPC-String vom RPC-Server.
     */
    public function CreateFromJSONString($Data)
    {
        $Json = json_decode($Data);
        if (property_exists($Json, 'error'))
            $this->Error = $Json->error;
        if (property_exists($Json, 'method'))
            $this->Method = $Json->method;
        if (property_exists($Json, 'params'))
            $this->Params = $this->DecodeUTF8($Json->params);
        if (property_exists($Json, 'result'))
        {
            $this->Result = $this->DecodeUTF8($Json->result);
            $this->Typ = Kodi_RPC_Data::$ResultTyp;
        }
        if (property_exists($Json, 'id'))
            $this->Id = $Json->id;

    }
    
    /**
     * Führt eine UTF8-Dekodierung für einen String oder ein Objekt durch (rekursiv)
     * 
     * @access private
     * @param string|object $item Zu dekodierene Daten.
     * @return string|object Dekodierte Daten.
     */
    private function DecodeUTF8($item)
    {
        if (is_string($item))
            $item = utf8_decode($item);
        else if (is_object($item))
        {
            foreach ($item as $property => $value)
            {
                $item->{$property} = $this->DecodeUTF8($value);
            }
        }
        else if (is_array($item))
        {
            foreach ($item as $property => $value)
            {
                $item[$property] = $this->DecodeUTF8($value);
            }
        }
        return $item;
    }
    
    /**
     * Führt eine UTF8-Enkodierung für einen String oder ein Objekt durch (rekursiv)
     * 
     * @access private
     * @param string|object $item Zu Enkodierene Daten.
     * @return string|object Enkodierte Daten.
     */
    private function EncodeUTF8($item)
    {
        if (is_string($item))
            $item = utf8_encode($item);
        else if (is_object($item))
        {
            foreach ($item as $property => $value)
            {
                $item->{$property} = $this->EncodeUTF8($value);
            }
        }
        else if (is_array($item))
        {
            foreach ($item as $property => $value)
            {
                $item[$property] = $this->EncodeUTF8($value);
            }
        }
        return $item;
    }    
    
    
}
