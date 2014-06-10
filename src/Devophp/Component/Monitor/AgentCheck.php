<?php

namespace Devophp\Component\Monitor;

class AgentCheck
{
    private $checkdefinition;

    public function setCheckDefinition($checkdefinition)
    {
        $this->checkdefinition = $checkdefinition;
    }

    public function getCheckDefinition()
    {
        return $this->checkdefinition;
    }
    
    private $statuscode;
    public function getStatusCode()
    {
        return $this->statuscode;
    }

    public function setStatusCode($statuscode)
    {
        $this->statuscode = $statuscode;
    }

    public function getStatusText()
    {
        switch ($this->statuscode) {
            case 0:
                return 'OK';
                break;
            case 1:
                return 'Warning';
                break;
            case 2:
                return 'Critical';
                break;
            case 3:
                return 'Unknown';
                break;
            default:
                return 'Confused';
                break;
        }
    }

    private $serviceoutput;
    public function getServiceOutput()
    {
        return $this->serviceoutput;
    }

    public function setServiceOutput($serviceoutput)
    {
        $this->serviceoutput = $serviceoutput;
    }
    
    private $serviceperfdata;
    public function getServicePerfData()
    {
        return $this->serviceperfdata;
    }

    public function setServicePerfData($serviceperfdata)
    {
        $this->serviceperfdata = $serviceperfdata;
    }
    
    
    

    
    private $lastcheckstamp;
    public function getLastCheckStamp()
    {
        return $this->lastcheckstamp;
    }    

    public function setLastCheckStamp($lastcheckstamp)
    {
        $this->lastcheckstamp = $lastcheckstamp;
    }
    
    private $lastreportstamp;
    public function getLastReportStamp()
    {
        return $this->lastreportstamp;
    }    

    public function setLastReportStamp($lastreportstamp)
    {
        $this->lastreportstamp = $lastreportstamp;
    }
    

}
