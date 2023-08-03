ALTER TABLE `systems_data` ADD `color` VARCHAR(50) NULL ;
ALTER TABLE `systems_data` ADD `color_2` VARCHAR(50) NULL ;
ALTER TABLE `systems_data` ADD `url_math` VARCHAR(150) NULL ;

CREATE TABLE `commercial_documents` ( `id` INT NOT NULL AUTO_INCREMENT , `name_file` VARCHAR(100) NULL , `type_file` VARCHAR(100) NULL , `tag_bucket` VARCHAR(100) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

ALTER TABLE `commercial_documents` ADD `client_registration_code` VARCHAR(20) NULL AFTER `tag_bucket`;
INSERT INTO `plans_charging_methods` (`id`, `name`, `description`, `status`) VALUES (NULL, 'Cielo + Clear Sale', 'Cielo + Clear Sale', 'Ativo');
INSERT INTO `plans_charging_methods` (`id`, `name`, `description`, `status`) VALUES (NULL, 'Cielo Apenas', 'Cielo Apenas', 'Ativo');
INSERT INTO `plans_charging_methods` (`id`, `name`, `description`, `status`) VALUES (NULL, 'Rateio Apenas', 'Rateio Apenas', 'Ativo');

ALTER TABLE `sale_plans_charging_methods` ADD `interest_free_installment` INT NULL ;
