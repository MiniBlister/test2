<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of KoLight
 *
 * @author Administrator
 */
class KoLight {
  //put your code here
  
  protected $eibSchaltID;
  protected $actionRueck;
  protected $action;    //true or false
  
  function __construct($theEIBID) {
    $this->eibSchaltID = $theEIBID;
  }
  
  public function setLight ($action) {
    EIB_Switch($this->eibSchaltID, $action);
  }
  
  public function getLight () {
    if (IPS_HasChildren($this->eibSchaltID)) {
      $arr = IPS_GetChildrenIDs ($this->eibSchaltID);
      $this->actionRueck = GetValueBoolean($arr[0]);
      return $this->actionRueck;
    }
    else {
      return FALSE;
    }
  }
}


class KoLightDim extends KoLight {
  
  protected $dimValue;
  protected $eibWertID;
  protected $wertRueck;


  public function __construct($theEIBID, $theWertID) { 
    parent::__construct($theEIBID);
    $this->eibWertID = $theWertID;
  }
    
  public function setLightDimValue ($dimValue) {
    EIB_DimValue($this->eibWertID, $dimValue);
  }
  public function getLightDimValue () {
    if (IPS_HasChildren($this->eibWertID)) {
      $arr = IPS_GetChildrenIDs ($this->eibWertID);
      $this->wertRueck = GetValueInteger($arr[0]);
      return $this->wertRueck;
    
    }
    else {
      return FALSE;
    }
  }
}
  



