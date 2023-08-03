ALTER TABLE `online_order` ADD `cupom` VARCHAR(144) NULL ;
ALTER TABLE `online_order` ADD `tipoCupom` VARCHAR(144) NULL ;
ALTER TABLE `online_order` ADD `indicacao` VARCHAR(144) NULL ;

ALTER TABLE `online_order` ADD `credito_usado` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `online_order` ADD `valorCupom` DOUBLE(20,2) NULL DEFAULT '0.00' ;

ALTER TABLE `online_order` ADD `data_transferencia` DATETIME NULL ;

ALTER TABLE `businesspartner` ADD `cpf_bank_account` VARCHAR(255) NULL ;
ALTER TABLE `businesspartner` ADD `name_bank_account` VARCHAR(255) NULL ;