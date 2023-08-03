ALTER TABLE `online_order` ADD `tax_payment` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `online_order` ADD `tax_approval` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `online_order` ADD `value_payment` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `online_order` ADD `value_approval` DOUBLE(20,2) NULL DEFAULT '0.00' ;

ALTER TABLE `sale` ADD `tax_online_payment` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `sale` ADD `tax_online_validation` DOUBLE(20,2) NULL DEFAULT '0.00' ;

ALTER TABLE `businesspartner` ADD `origin` VARCHAR(30) NULL AFTER `avoid_daily_reminder`;