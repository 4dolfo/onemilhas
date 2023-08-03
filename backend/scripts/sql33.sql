CREATE TABLE `cupons_b2c` ( `id` INT NOT NULL AUTO_INCREMENT , `nome` VARCHAR(255) NOT NULL , `valor` DOUBLE(20,2) NOT NULL DEFAULT '0.00' , `porcentagem` VARCHAR(5) NOT NULL DEFAULT 'false' , `status` VARCHAR(16) NOT NULL DEFAULT 'Criado' , `criado_b2c` VARCHAR(5) NOT NULL DEFAULT 'false' , `data_inicio` DATETIME NULL , `data_fim` DATETIME NULL , `dealer_id` INT NOT NULL , `user_aprovacao` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

ALTER TABLE `cupons_b2c` ADD INDEX(`dealer_id`);
ALTER TABLE `cupons_b2c` ADD INDEX(`user_aprovacao`);

ALTER TABLE `cupons_b2c` ADD CONSTRAINT `fk_dealer_cupons` FOREIGN KEY (`dealer_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION; ALTER TABLE `cupons_b2c` ADD CONSTRAINT `fk_user_cupons` FOREIGN KEY (`user_aprovacao`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;


CREATE TABLE `campanhas_b2c` ( `id` INT NOT NULL AUTO_INCREMENT , `nome` VARCHAR(255) NOT NULL , `dealer_id` INT NOT NULL , `codigo` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `campanhas_b2c` ADD INDEX(`dealer_id`);

ALTER TABLE `campanhas_b2c` ADD CONSTRAINT `fk_dealer_campanha` FOREIGN KEY (`dealer_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `cupons_b2c` CHANGE `user_aprovacao` `user_aprovacao` INT(11) NULL;
ALTER TABLE `campanhas_b2c` ADD `url` VARCHAR(255) NULL ;
