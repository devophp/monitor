<?php 

namespace Devophp\Component\Monitor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use FuseSource\Stomp\Stomp;
use FuseSource\Stomp\Frame;
use FuseSource\Stomp\Exception\StompException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Devophp\Component\Monitor\Transport\StompTransport;

use Devophp\Component\Monitor\CheckDefinition;
use Devophp\Component\Monitor\Agent;
use Devophp\Component\Monitor\AgentCheck;

class DaemonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('monitor:daemon')
            ->setDescription(
                'Run daemon'
            );
    }
    
    private $plugin = array();
    private $stomp;
    
    private $server;
    private $port;
    private $logger;
    
    private $agents = array();
    private $checkdefinitions = array();
    
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        
        $output->write("Starting Devophp Monitor");
        
        $this->logger = new Logger('devophp-monitor');
        $this->logger->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));
        
        $checkdefinition1 = new CheckDefinition();
        $checkdefinition1->setName("Check all the users");
        $checkdefinition1->setCheck("users");
        $checkdefinition1->setArguments("-w 2 -c 3");
        $checkdefinition1->setFrequency(5);
        $this->checkdefinitions[] = $checkdefinition1;
        
        $checkdefinition2 = new CheckDefinition();
        $checkdefinition2->setName("Check all the root disks");
        $checkdefinition2->setCheck("disk");
        $checkdefinition2->setArguments("-w 5% -c 1% -p /");
        $checkdefinition2->setFrequency(10);
        $this->checkdefinitions[] = $checkdefinition2;
        
        
        
        $agent = new Agent();
        $agent->setHostname('joosts-macbook-pro.fritz.box');
        $agent->setOs('Darwin');
        $this->agents[] = $agent;

        $agentcheck = new AgentCheck();
        $agentcheck->setCheckDefinition($checkdefinition1);
        $agent->addAgentCheck($agentcheck);

        $agentcheck = new AgentCheck();
        $agentcheck->setCheckDefinition($checkdefinition2);
        $agent->addAgentCheck($agentcheck);
        
        
        /*
        $dbalconfig = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'dbname'   => 'devophp',
            'user'     => $dbusername,
            'password' => $dbpassword,
            'host'     => '127.0.0.1',
            'driver'   => 'pdo_mysql',
            'charset'  => 'utf8'
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $dbalconfig);
        
        // initialise
        $t = $conn->prepare(
            "SET
            character_set_results = 'utf8', 
            character_set_client = 'utf8', 
            character_set_connection = 'utf8', 
            character_set_database = 'utf8', 
            character_set_server = 'utf8'"
        );
        $t->execute();
        
        $store = new \Devophp\Component\Monitor\Store\DbalStore($conn);
        $store->initSchema();
        */
        




        
        // setup plugins
        $monitorplugin = new \Devophp\Component\Monitor\Plugin\MonitorPlugin();
        $transport = null;
        $monitorplugin->init($this->logger, $transport, $store);
        $this->plugin['monitor'] = $monitorplugin;
        
        $this->server = 'localhost';
        $this->port = 61613;
        
        try {
            $this->stomp = new Stomp('tcp://' . $this->server . ':' . $this->port);
            $this->stomp->clientId = "testing.1.2.3.monitor";
            $this->stomp->connect();
            $this->logger->info('Connected to "' . $this->server . ':' . $this->port . '"');
        } catch (StompException $e) {
            $this->logger->warn('Connection failed: ' . $e->getMessage());
            throw new RuntimeException("Connection failed");
        }
        
        $this->stomp->setReadTimeout(1, 0);
        
        //$properties = array('selector' => "username='" . $username . "'");
        $properties = null;

        $destination = '/queue/devophp/monitor';
        $this->stomp->subscribe($destination, $properties);
        
        
        while (true) {
            $this->logger->debug("-----");
            $this->scheduleChecks();
            $jsonstatus = json_encode($this->getStatusData(), JSON_PRETTY_PRINT);
            echo $jsonstatus ."\n";
            file_put_contents("/tmp/devophp_monitor_status.json", $jsonstatus);
            $this->logger->debug("Listening for messages");
            $msg = $this->stomp->readFrame();
            if ($msg!=null) {
                $this->logger->debug('Received message: ' . $msg->body . '(' . $msg->headers['message-id'] . '/' . $msg->headers['destination'] . ')');
                $this->stomp->ack($msg);
                $this->handleMessage($msg->body);
            } else {
                $this->logger->debug('Empty message queue');
            }
            //sleep(2);
            
        }
        
        exit('doei');
    }
    
    public function getStatusData()
    {
        $data = array();
        $data['agents'] = array();
        foreach($this->getAgents() as $agent) {
            $agentdata = array();
            $agentdata['name'] = $agent->getName();
            $agentdata['hostname'] = $agent->getHostName();
            $agentdata['os'] = $agent->getOs();
            $agentdata['agentchecks'] = array();
            foreach($agent->getAgentChecks() as $agentcheck) {
                $agentcheckdata=array();

                $checkdefinition = $agentcheck->getCheckDefinition();
                
                $checkdefinitiondata = array();
                $checkdefinitiondata['name'] = $checkdefinition->getName();
                $checkdefinitiondata['check'] = $checkdefinition->getCheck();
                $checkdefinitiondata['arguments'] = $checkdefinition->getArguments();
                $checkdefinitiondata['frequency'] = $checkdefinition->getFrequency();
                
                $agentcheckdata['checkdefinition'] = $checkdefinitiondata;
                
                $agentcheckdata['statuscode'] = $agentcheck->getStatusCode();
                $agentcheckdata['statustext'] = $agentcheck->getStatusText();
                $agentcheckdata['serviceoutput'] = $agentcheck->getServiceOutput();
                $agentcheckdata['serviceperfdata'] = $agentcheck->getServicePerfData();
                $agentcheckdata['lastcheckstamp'] = $agentcheck->getLastCheckStamp();
                $agentcheckdata['lastreportstamp'] = $agentcheck->getLastReportStamp();
                $agentdata['agentchecks'][] = $agentcheckdata;
            }
            $data['agents'][] = $agentdata;
        }
        return $data;
        
    }
    public function getAgents()
    {
        return $this->agents;
    }
    public function getCheckDefinitions()
    {
        return $this->checkdefinitions;
    }
    
    private function scheduleChecks()
    {
        foreach($this->agents as $agent) {
            // $this->logger->debug("Scanning for agentchecks on " . $agent->getHostName());
            foreach ($agent->getAgentChecks() as $agentcheck) {
                $cd = $agentcheck->getCheckDefinition();
                //$this->logger->debug("Found checkable agentchecks: " . $cd->getName());
                $lastcheckstamp = $agentcheck->getLastCheckStamp();
                if ($lastcheckstamp<time()-$cd->getFrequency()) {
                    $this->logger->info("RUNNING '" . $cd->getName() . "' on agent '" . $agent->getHostName() . "'");
                    $agentcheck->setLastCheckStamp(time());
                    
                    $message = array();
                    $message['command'] = 'nagios:check';
                    $message['sendstamp'] = time();
                    
                    $parameters = array(
                        "check"=>$cd->getCheck(),
                        "arguments"=>$cd->getArguments(),
                        "checkdefinitionname"=>$cd->getName()
                    );
                    $message['parameters'] = $parameters;
                    $messagejson = json_encode($message);
                    $this->logger->info("Sending message: " . $messagejson);
                    $destination = '/queue/devophp/agents';
                    $this->stomp->send($destination, $messagejson);
                    
                    
                } else {
                    $this->logger->debug("SNOOZE '" . $cd->getName() . "' on agent '" . $agent->getHostName() . "'");
                }
            }
        }
    }
    
    private function handleMessage($message)
    {
        $data = json_decode($message, true);
        $command=$data['command'];
        $parameters=$data['parameters'];
        switch($command) {
            case "monitor:register":
                $res = $this->plugin['monitor']->register($parameters);
                break;
            case "monitor:checkreport":
                $res = $this->plugin['monitor']->checkReport($parameters, $this);
                
                break;
            default:
                exit('Unsupported command: ' . $command);
                break;
        }
    }
}
