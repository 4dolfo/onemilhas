ALTER TABLE `cupons` ADD `valor_minimo` DOUBLE NULL DEFAULT '0.00' ;
ALTER TABLE `cupons` CHANGE `client_id` `client_id` INT(11) NULL;

ALTER TABLE `cupons` ADD `data_inicio` DOUBLE NULL DEFAULT '0.00' ;
ALTER TABLE `cupons` ADD `tipo_cupom` ENUM('P','D') NOT NULL DEFAULT 'D' ;
ALTER TABLE `cupons` CHANGE `data_inicio` `data_inicio` DATETIME NULL;
ALTER TABLE `cupons` ADD `quant_usos` INT NULL DEFAULT '0' ;