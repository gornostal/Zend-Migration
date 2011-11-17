<?php

$this->_execSql("
ALTER TABLE  `table1` ADD COLUMN  `text` VARCHAR( 255 ) NOT NULL
");

return '1.0.2';