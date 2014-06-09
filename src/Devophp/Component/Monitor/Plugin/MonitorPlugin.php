<?php

namespace Devophp\Component\Monitor\Plugin;

class MonitorPlugin extends BaseMonitorPlugin
{
    public function register($data)
    {
        $this->info("REGISTERING: " . $data['hostname']);
        if ($this->store) {
            $this->store->registerAgent($data);
        }
    }

    public function checkReport($data, $monitor)
    {
        foreach ($monitor->getAgents() as $agent) {
            if ($agent->getHostname() == $data['hostname']) {
                foreach($agent->getAgentChecks() as $agentcheck) {
                    if ($agentcheck->getCheckDefinition()->getName()==$data['checkdefinitionname']) {
                        $agentcheck->setLastReportStamp(time());
                        $agentcheck->setStatusCode($data['statuscode']);
                        $agentcheck->setServiceOutput($data['serviceoutput']);
                        $agentcheck->setServicePerfData($data['serviceperfdata']);
                        
                        //$agentcheck->setStatusCode($data['statuscode']);
                        $this->info("Updated agentcheck for " . $data['hostname'] . "/" . $data['checkdefinitionname'] .  " to " . $data['statuscode']);
                    }
                }
            }
        }
        
    }
}
