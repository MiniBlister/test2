<?php

//disable html errors in modules
ini_set("html_errors", "0");
/**
 * @class T2DModule
 *
 * IPS Module Helper Class
 * combines often used functions and constants
 *
 */
class KoHelpDModule extends IPSModule
{
    protected function GetParent($id = 0)
    {
        $parent = 0;
        if ($id == 0) $id = $this->InstanceID;
        if (IPS_InstanceExists($id)) {
            $instance = IPS_GetInstance($id);
            $parent = $instance['ConnectionID'];
        } else {
            $this->debug(__FUNCTION__, "Instance #$id doesn't exists");
        }
        return $parent;
    }
}
