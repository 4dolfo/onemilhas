ALTER TABLE `cards` ADD `max_per_pax` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `purchase` ADD `payment_method` ENUM('prepaid','after_payment') NULL DEFAULT 'prepaid' ;
ALTER TABLE `purchase` ADD `payment_by` ENUM('boarding_date','issue_date') NULL ;
ALTER TABLE `purchase` ADD `payment_days` INT NULL DEFAULT '0' ;
ALTER TABLE `sale` ADD `is_extra` VARCHAR(5) NULL DEFAULT 'false' ;
ALTER TABLE `online_order` ADD `order_post` VARCHAR(10000) NULL ;

ALTER TABLE `online_order` ADD `priority` BOOLEAN NULL AFTER `order_post`;

ALTER TABLE `user_permission` ADD `wizar_sale_event` VARCHAR(5) NULL DEFAULT 'false' AFTER `sale_revert_refund`;

ALTER TABLE `user_permission` CHANGE `wizar_sale_event` `wizar_sale_event` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'false';

CREATE TABLE `customers_link` ( `id` INT NULL AUTO_INCREMENT , `clientDealer` INT NOT NULL , `user_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

ALTER TABLE `customers_link` ADD CONSTRAINT `dealer_fk` FOREIGN KEY (`clientDealer`) REFERENCES `businesspartner`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `customers_link` ADD CONSTRAINT `userId_fk` FOREIGN KEY (`user_id`) REFERENCES `businesspartner`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `online_order` ADD `order_post` VARCHAR(10000) NULL ;
