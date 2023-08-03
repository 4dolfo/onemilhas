CREATE TABLE `sale_purchases` ( `id` INT NOT NULL AUTO_INCREMENT , `sale_id` INT NOT NULL , `purchase_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `sale_purchases` ADD INDEX(`sale_id`);
ALTER TABLE `sale_purchases` ADD INDEX(`purchase_id`);


ALTER TABLE `sale_purchases` ADD CONSTRAINT `fk_sale_purchase_sale` FOREIGN KEY (`sale_id`) REFERENCES `sale`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `sale_purchases` ADD CONSTRAINT `fk_sale_purchase_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchase`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `sale_purchases` ADD `miles_used` DOUBLE(20,2) NULL DEFAULT '0.00' ;

ALTER TABLE `sale_plans` ADD `documentos` VARCHAR(5) NULL DEFAULT 'true' ;

ALTER TABLE `cards` ADD `only_inter` VARCHAR(5) NULL ;



ALTER TABLE `purchase` CHANGE `miles_due_date` `miles_due_date` DATETIME NULL;
ALTER TABLE `businesspartner` ADD `bank_operation` VARCHAR(255) NULL ;
ALTER TABLE `businesspartner` ADD `bank_name_owner` VARCHAR(255) NULL ;
ALTER TABLE `businesspartner` ADD `cpf_name_owner` VARCHAR(255) NULL ;