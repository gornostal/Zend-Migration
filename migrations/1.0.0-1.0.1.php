<?php

$this->_execSql("
ALTER TABLE  `table1` CHANGE  `cname`  `cname1` VARCHAR( 100 ) 
CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
");

return '1.0.1';