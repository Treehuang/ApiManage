CREATE TABLE `anyclouds_cmdb`.`t_trial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `nickname` varchar(45) DEFAULT NULL,
  `sex` varchar(45) DEFAULT NULL,
  `tel` varchar(45) NOT NULL,
  `other_contact` varchar(45) DEFAULT NULL,
  `contact_type` varchar(45) DEFAULT NULL,
  `company_name` varchar(45) NOT NULL,
  `business_type` varchar(45) NOT NULL,
  `company_size` int(11) NOT NULL,
  `position` varchar(45) NOT NULL,
  `company_url` varchar(1024) DEFAULT NULL,
  `invite_code` varchar(45) NOT NULL,
  `ctime` varchar(45) NOT NULL DEFAULT 'CURRENT_TIMESTAMP()',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `invite_code_UNIQUE` (`invite_code`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;