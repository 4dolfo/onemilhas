ALTER TABLE `online_order` ADD `total_parcelas` VARCHAR(20) NULL;
ALTER TABLE `online_order` ADD `acrescimo` DOUBLE(20,2) NULL DEFAULT '0.00';

ALTER TABLE `online_order` ADD `comprovanteTransferencia` VARCHAR(3024) NULL;
ALTER TABLE `online_order` ADD `totalReal` DOUBLE(20,2) NULL DEFAULT '0.00';