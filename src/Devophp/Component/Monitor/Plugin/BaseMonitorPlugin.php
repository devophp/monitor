<?php

namespace Devophp\Component\Monitor\Plugin;

use Monolog\Logger;
//use Devophp\Component\Monitor\Transport\TransportInterface;

abstract class BaseMonitorPlugin
{
    
    private $logger;
    private $transport;
    protected $store;
    
    public function init(Logger $logger, $transport, $store)
    {
        $this->logger = $logger;
        //$this->transport = $transport;
        $this->store = $store;
    }

    public function warning($message, $context = array())
    {
        $this->logger->warning($message, $context);
    }

    public function info($message, $context  = array())
    {
        $this->logger->info($message, $context);
    }
}
