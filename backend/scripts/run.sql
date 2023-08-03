CREATE TABLE `system_check` ( `id` INT NOT NULL AUTO_INCREMENT , `businesspartner_id` INT NOT NULL , `issue_date` DATETIME NOT NULL , `info_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `system_check` ADD CONSTRAINT `fk_business_id` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `system_check` ADD CONSTRAINT `fk_info_id` FOREIGN KEY (`info_id`) REFERENCES `info`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `system_check` ADD `check_info` BOOLEAN NULL AFTER `info_id`;
