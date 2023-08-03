CREATE TABLE `online_notification_status` ( `id` INT NOT NULL AUTO_INCREMENT , `status` VARCHAR(255) NOT NULL , `user` VARCHAR(255) NOT NULL , `issue_date` DATETIME NOT NULL , `order_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `online_notification_status` ADD INDEX(`order_id`);
ALTER TABLE `online_notification_status` ADD CONSTRAINT `fk_order_notifications` FOREIGN KEY (`order_id`) REFERENCES `online_order`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;


