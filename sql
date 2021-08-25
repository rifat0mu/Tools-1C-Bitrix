CREATE TABLE `m_tasks_items` (
`ID` BIGINT NOT NULL AUTO_INCREMENT ,
`PROJECT_ID` BIGINT NOT NULL ,
`USER_ID` INT NOT NULL ,
`CREATE_DATE` DATETIME NOT NULL DEFAULT NOW() ,
`CODE` VARCHAR(255) NOT NULL ,
`SORT` BIGINT NOT NULL DEFAULT 0, 
`NAME` VARCHAR(255) NOT NULL ,
`TEXT` TEXT NOT NULL ,
`EXPIRATION_DATE` DATETIME ,
`RESPONSIBLE` TEXT NOT NULL ,
`AUTHOR` INT NOT NULL ,
`STATUS` VARCHAR(255) NOT NULL ,
`PRIORITY` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`ID`)) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci;
