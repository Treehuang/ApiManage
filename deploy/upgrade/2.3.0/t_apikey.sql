CREATE TABLE `anyclouds_cmdb`.`t_apikey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `org` int(11) NOT NULL,
  `user` varchar(45) NOT NULL DEFAULT 'openapi',
  `access_key` varchar(128) NOT NULL,
  `secret_key` varchar(128) NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mtime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `state` varchar(45) NOT NULL DEFAULT 'valid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_access_key` (`access_key`),
  UNIQUE KEY `UNIQUE_org_user` (`org`,`user`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;