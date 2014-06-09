<?php

namespace Devophp\Component\Monitor\Store;

use PDOException;

class DbalStore
{
    private $connection;
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    private function ensureTable($tablename)
    {
        try {
            $res = $this->connection->query(
                "CREATE TABLE IF NOT EXISTS " . $tablename . " (id int auto_increment, PRIMARY KEY (ID))"
            );
        } catch (\PDOException $e) {
            // ok
        }
        return true;
    }
    
    private function ensureColumn($tablename, $columnname, $type)
    {
        try {
            $res = $this->connection->query(
                "ALTER TABLE " . $tablename . " ADD column " . $columnname . " " . $type . ";"
            );
        } catch (\Doctrine\DBAL\DBALException $e) {
            // ok
        }
    }
    
    public function initSchema()
    {
        $this->ensureTable('monitor_agent');
        $this->ensureColumn('monitor_agent', 'hostname', 'varchar(64)');
        $this->ensureColumn('monitor_agent', 'os', 'varchar(64)');
        $this->ensureColumn('monitor_agent', 'first_register_stamp', 'int');
        $this->ensureColumn('monitor_agent', 'last_register_stamp', 'int');

        $this->ensureTable('monitor_check');
        $this->ensureColumn('monitor_check', 'name', 'varchar(64)');
        $this->ensureColumn('monitor_check', 'check', 'varchar(64)');
        $this->ensureColumn('monitor_check', 'attributes', 'varchar(64)');
        $this->ensureColumn('monitor_check', 'class', 'varchar(64)');
        $this->ensureColumn('monitor_check', 'frequency', 'int');
        

        $this->ensureTable('monitor_agent_check');
        $this->ensureColumn('monitor_agent_check', 'hostname', 'varchar(64)');
        $this->ensureColumn('monitor_agent_check', 'checkname', 'varchar(64)');
        $this->ensureColumn('monitor_agent_check', 'statuscode', 'varchar(64)');
        $this->ensureColumn('monitor_agent_check', 'output', 'varchar(128)');
        $this->ensureColumn('monitor_agent_check', 'perfdata', 'varchar(128)');
        $this->ensureColumn('monitor_agent_check', 'last_check_stamp', 'varchar(128)');
    }
    
    public function registerAgent($data)
    {
        // Ensure record
        $stmt = $this->connection->prepare(
            "SELECT id FROM monitor_agent WHERE hostname= :hostname"
        );
        $stmt->bindValue('hostname', $data['hostname']);
        $stmt->execute();
        $res = $stmt->fetchAll();
        if (count($res)==0) {
            $stmt = $this->connection->prepare(
                "INSERT INTO monitor_agent (hostname, first_register_stamp) VALUES(:hostname, :first_register_stamp)"
            );
            $stmt->bindValue('first_register_stamp', time());
            $stmt->bindValue('hostname', $data['hostname']);
            $res = $stmt->execute();
        }

        // Update monitor_agent details
        $stmt = $this->connection->prepare(
            "UPDATE monitor_agent
            SET os=:os,
            last_register_stamp=:last_register_stamp
            WHERE hostname=:hostname"
        );
        $stmt->bindValue('os', $data['os']);
        $stmt->bindValue('last_register_stamp', time());
        $stmt->bindValue('hostname', $data['hostname']);
        $res = $stmt->execute();
        
    }
}
