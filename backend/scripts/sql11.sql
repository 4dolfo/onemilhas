ALTER TABLE `businesspartner` ADD `conta_azul_token` VARCHAR(255) NULL ;
ALTER TABLE `businesspartner` ADD `conta_azul_refresh_token` VARCHAR(255) NULL ;
ALTER TABLE `businesspartner` ADD `conta_azul_last_update` DATETIME NULL ;

ALTER TABLE `sale` ADD `processing_start_date` DATETIME NULL ;
ALTER TABLE `online_order` ADD `original_system` VARCHAR(20) NULL ;
ALTER TABLE `cards` ADD `notes` VARCHAR(512) NULL ;
