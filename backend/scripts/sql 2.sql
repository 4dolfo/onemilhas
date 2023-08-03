CREATE TABLE `milesbench_validation`.`midia` ( `id` INT NOT NULL AUTO_INCREMENT , `url` VARCHAR(255) NOT NULL , `businesspartner_id` INT NOT NULL , `keyname` VARCHAR(60) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `midia` ADD INDEX(`businesspartner_id`);
ALTER TABLE `midia` ADD CONSTRAINT `fk_midia_businesspartner` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

