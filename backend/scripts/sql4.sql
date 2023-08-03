CREATE TABLE `mms_gestao`.`online_billets` ( `id` INT NOT NULL AUTO_INCREMENT , `order_id` INT NOT NULL , `keyname` VARCHAR(250) NOT NULL , `url` VARCHAR(1024)NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `online_billets` ADD INDEX(`order_id`);
ALTER TABLE `online_billets` ADD CONSTRAINT `fk_online_baggage` FOREIGN KEY (`order_id`) REFERENCES `online_order`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
