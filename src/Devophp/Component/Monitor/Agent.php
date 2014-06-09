<?php

namespace Devophp\Component\Monitor;

class Agent
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
    
    private $hostname;

    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    public function getHostname()
    {
        return $this->hostname;
    }
    
    private $os;

    public function setOs($os)
    {
        $this->os = $os;
    }

    public function getOs()
    {
        return $this->os;
    }
        
    
    private $agentchecks = array();

    public function addAgentCheck($agentcheck)
    {
        $this->agentchecks[] = $agentcheck;
    }

    public function getAgentChecks()
    {
        return $this->agentchecks;
    }
}
