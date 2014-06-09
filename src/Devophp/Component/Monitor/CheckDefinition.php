<?php

namespace Devophp\Component\Monitor;

class CheckDefinition
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
    
    private $check;

    public function setCheck($check)
    {
        $this->check = $check;
    }

    public function getCheck()
    {
        return $this->check;
    }
    
    private $arguments;

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
        
    
    private $frequency;

    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    public function getFrequency()
    {
        return $this->frequency;
    }

}
