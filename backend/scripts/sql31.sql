CREATE TABLE `mms_unificado`.`cupons` ( `id` INT NOT NULL AUTO_INCREMENT , `value` DOUBLE(20,2) NULL DEFAULT '0.00' , `client_id` INT NOT NULL , `used` BOOLEAN NOT NULL , `data_expiracao` DATETIME NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `cupons` ADD INDEX(`client_id`);

ALTER TABLE `cupons` ADD CONSTRAINT `fk_cupons_client` FOREIGN KEY (`client_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `cupons` ADD `nome` VARCHAR(255) NOT NULL ;


