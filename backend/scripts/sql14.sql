CREATE TABLE `user_checklist` ( `id` INT NOT NULL AUTO_INCREMENT , `businesspartner_id` INT NOT NULL , `task` VARCHAR(255) NOT NULL , `done` VARCHAR(5) NULL DEFAULT 'false' , `issue_date` DATETIME NOT NULL , `check_date` DATETIME NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `user_checklist` ADD INDEX(`businesspartner_id`);

ALTER TABLE `user_checklist` ADD CONSTRAINT `fk_businesspartner_task` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

