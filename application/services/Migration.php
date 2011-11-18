<?php

/**
 * Extremely simple implementation of migrations on ZF1
 *
 * @author Aleksandr Gornostal <info@sanya.pp.ua>
 * @license http://sam.zoy.org/wtfpl/COPYING WTFPL V2
 * @link https://github.com/sanya-gornostal/Zend-Migration
 */
class Application_Service_Migration
{
    /**
     * @var Zend_Db_Adapter_Pdo_Mysql
     */
    private $_db;
    
    /**
     * @var Zend_Config
     */
    private $_config;
    
    /**
     * @var string
     */
    private $_currentVersion = false;
    
    /**
     * @var array
     */
    private $_migrationFiles;
    
    /**
     * @var string
     */
    private $_tableName = 'schema_version';
    
    
    /**
     * @param Zend_Db_Adapter_Pdo_Mysql $db 
     */
    public function __construct(Zend_Db_Adapter_Abstract $db, Zend_Config $config)
    {
        $this->_db = $db;
        $this->_config = $config->migration;
    }
    
    /**
     *
     * @return string
     */
    public function getRealVersion()
    {
        if (empty($this->_config->schema_version)) {
            throw new Exception('Schema version is not defined');
        }
        return $this->_config->schema_version;
    }
    
    /**
     *
     * @return string
     */
    private function _getDir()
    {
        if (empty($this->_config->dir)) {
            throw new Exception('Directory for migration files is not defined');
        }
        return $this->_config->dir;
    }
    
    /**
     * @return string
     */
    public function getVer()
    {
        if ($this->_currentVersion !== false) {
            return $this->_currentVersion;
        }
        
        try {
            $select = $this->_db->select()->from($this->_tableName)->limit(1);
            $rows = $this->_db->fetchAll($select);
            if (count($rows)) {
                foreach ($rows as $row) {
                    $this->_currentVersion = $row['version'];
                    return $this->_currentVersion;
                }
            }
        } catch (Exception $e) {
            // setup table
            $this->_db->query("DROP TABLE IF EXISTS `$this->_tableName`;
                CREATE TABLE IF NOT EXISTS `$this->_tableName` (
                  `version` varchar(20) NOT NULL DEFAULT ''
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
            return $this->getVer();
        }
    }
    
    /**
     *
     * @param string $ver 
     */
    public function setVer($ver)
    {
        if (!$ver) {
            throw new Exception('Version number is null');
        }
        
        if ($this->_currentVersion === false) {
            $this->_db->insert($this->_tableName, array(
                'version' => $ver
            ));
        } else {
            if (version_compare($this->getVer(), $ver, '==')) {
                throw new Exception('Version number could not be the same as current version');
            }
            
            $this->_currentVersion = $ver;
            $this->_db->update($this->_tableName, array(
                'version' => $ver
            ));
        }
    }
    
    /**
     * @param string $regexp Regular expression
     * @return string File name
     */
    private function _findMigrationFile($regexp)
    {
        $dir = $this->_getDir();
        if (!$this->_migrationFiles) {
            $this->_migrationFiles = scandir($dir);
        }
        foreach ($this->_migrationFiles as $file) {
            if (is_file($dir.DIRECTORY_SEPARATOR.$file) && preg_match($regexp, $file)) {
                return $file;
            }
        }
    }


    public function migrate()
    {
        $ver = $this->getRealVersion();
        
        if (version_compare($this->getVer(), $ver, '==')) {
            return;
        }
        
        try {
            $this->_db->beginTransaction();

            if (version_compare($this->getVer(), $ver, '>')) {
                throw new Exception('Version number in config file is less 
                    than actual version number');
            }
            if (!$this->getVer()) {
                // execute install script
                $this->setVer($this->_execMigrationFile('/^install/'));
            } else {
                // upgrade to new version
                $regexp = '/^'.str_replace('.', '\.', $this->getVer()).'[^.0-9]+/';
                $this->setVer($this->_execMigrationFile($regexp));
            }
            
            $this->_db->commit();
            if (version_compare($this->getVer(), $ver, '!=')) {
                $this->migrate();
            }
            
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw new Exception('Migration error: ' . $e->getMessage(), 
                    $e->getCode(), $e);
        }
    }
    
    /**
     * @param string $sql
     * @return mixed
     */
    protected function _execSql($sql)
    {
        return $this->_db->query($sql);
    }
    
    /**
     * @param string $regexp Regular expression
     * @return string New version
     */
    protected function _execMigrationFile($regexp)
    {
        $fileName = $this->_findMigrationFile($regexp);
        if (!$fileName) {
            throw new Exception("File $regexp not found");
        }
        
        return include $this->_getDir().DIRECTORY_SEPARATOR.$fileName;
    }
}