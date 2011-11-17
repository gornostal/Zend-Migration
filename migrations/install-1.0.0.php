<?php

$this->_execSql("
CREATE TABLE IF NOT EXISTS `table1` (
  `id` int(11) NOT NULL,
  `cname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

return '1.0.0';