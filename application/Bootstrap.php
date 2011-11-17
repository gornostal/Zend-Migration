<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    public function _initMigration()
    {
        $this->bootstrap('db');
        $config = new Zend_Config($this->getOptions(), true);
        $migration = new Application_Service_Migration($this->getResource('db'), $config);
        $migration->migrate();
        return $migration;
    }

}

