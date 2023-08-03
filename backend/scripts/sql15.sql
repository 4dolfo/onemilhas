CREATE TABLE `info` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `reminder` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `date` DATETIME NOT NULL , `status` VARCHAR(90) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `businesspartner_id` INT(11) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `info` ADD CONSTRAINT `fk_info` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

CREATE TABLE `businesspartner_update_registration` ( `id` INT NOT NULL AUTO_INCREMENT , `businesspartner_id` INT NOT NULL , `client_name` VARCHAR(40) NULL , `social_name` VARCHAR(40) NULL , `registration_code` VARCHAR(40) NULL , `adress` VARCHAR(40) NULL , `adress_number` VARCHAR(40) NULL , `adress_complement` VARCHAR(40) NULL , `adress_district` VARCHAR(40) NULL , `zip_code` VARCHAR(40) NULL , `email` VARCHAR(40) NULL , `phone_cel` VARCHAR(40) NULL , `phone_commercial` VARCHAR(40) NULL , `phone_residential` VARCHAR(40) NULL , `contact` VARCHAR(40) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `businesspartner_update_registration` ADD INDEX(`businesspartner_id`);
ALTER TABLE `businesspartner_update_registration` ADD CONSTRAINT `fk_businesspartner_registration` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

CREATE TABLE `plans_promotions` ( `id` INT NOT NULL AUTO_INCREMENT , `status` VARCHAR(5) NOT NULL DEFAULT 'false' , `start_date` DATETIME NOT NULL , `end_date` DATETIME NOT NULL , `url_image` VARCHAR(1024) NOT NULL , `plans` VARCHAR(1024) NOT NULL , `airlines` VARCHAR(1024) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `plans_promotions` CHANGE `plans` `plans` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `plans_promotions` CHANGE `airlines` `airlines` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

CREATE TABLE `clients_markups` ( `id` INT NOT NULL AUTO_INCREMENT , `businesspartner_id` INT NOT NULL , `json` VARCHAR(2048) NULL , `update_date` DATETIME NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `clients_markups` ADD INDEX(`businesspartner_id`);
ALTER TABLE `clients_markups` ADD CONSTRAINT `fk_businesspartner_markups` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

CREATE TABLE `plans_promos` ( `id` INT NOT NULL AUTO_INCREMENT , `start_date` DATETIME NOT NULL , `end_date` DATETIME NOT NULL , `status` VARCHAR(5) NOT NULL DEFAULT 'false' , `plan` INT NOT NULL , `clients` VARCHAR(2048) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `plans_promos` ADD INDEX(`plan`);
ALTER TABLE `plans_promos` ADD CONSTRAINT `fk_plans_promos` FOREIGN KEY (`plan`) REFERENCES `sale_plans`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `plans_control` ADD `discount_type` ENUM('P','D') NULL DEFAULT 'D' ;
ALTER TABLE `plans_promos` ADD `for_all_clients` VARCHAR(5) NOT NULL DEFAULT 'false' ;
ALTER TABLE `plans_promos` ADD `discount_type` ENUM('P','D') NOT NULL DEFAULT 'D' ;
ALTER TABLE `plans_promos` ADD `discount_markup` DOUBLE(20,2) NOT NULL DEFAULT '0.00' ;
ALTER TABLE `plans_promos` CHANGE `plan` `plan` INT(11) NULL;

ALTER TABLE `plans_promos` ADD `airlines` VARCHAR(2048) NULL ;
ALTER TABLE `plans_promos` ADD `airlines_types` VARCHAR(2048) NULL ;


CREATE TABLE `plans_promo_control_config` (
  `id` int(11) NOT NULL,
  `airline_id` int(11) NOT NULL,
  `plans_promos_id` int(11) NOT NULL,
  `cost` double(20,2) NOT NULL DEFAULT '0.00',
  `config` VARCHAR(2048) NOT NULL,
  `type` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `plans_promo_control_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `airline_id` (`airline_id`),
  ADD KEY `plans_promos_id` (`plans_promos_id`);

ALTER TABLE `plans_promo_control_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `plans_promo_control_config`
  ADD CONSTRAINT `fk_airline_promo__control_config` FOREIGN KEY (`airline_id`) REFERENCES `airline` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_promo__promos_promo__control_config` FOREIGN KEY (`plans_promos_id`) REFERENCES `plans_promos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `plans_promo_control_config` CHANGE `config` `config` VARCHAR(4194304) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE `systems_data` ( `id` INT NOT NULL AUTO_INCREMENT , `system_name` VARCHAR(240) NOT NULL , `description` VARCHAR(240) NULL , `logo_url` INT NULL , `label_name` VARCHAR(240) NULL , `label_description` VARCHAR(240) NULL , `label_adress` VARCHAR(240) NULL , `label_phone` VARCHAR(240) NULL , `label_email` VARCHAR(240) NULL , `logo_url_small` VARCHAR(240) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

ALTER TABLE `businesspartner` ADD `system_name` VARCHAR(240) NULL ;
ALTER TABLE `businesspartner` ADD `logo_url` VARCHAR(240) NULL ;
ALTER TABLE `businesspartner` ADD `label_name` VARCHAR(240) NULL ;
ALTER TABLE `businesspartner` ADD `label_description` VARCHAR(240) NULL ;
ALTER TABLE `businesspartner` ADD `label_adress` VARCHAR(240) NULL ;
ALTER TABLE `businesspartner` ADD `label_phone` VARCHAR(240) NULL ;
ALTER TABLE `businesspartner` ADD `label_email` VARCHAR(240) NULL ;

ALTER TABLE `businesspartner` ADD `logo_url_small` VARCHAR(240) NULL ;

ALTER TABLE `systems_data` ADD `emission_term` VARCHAR(10200) NULL ;
ALTER TABLE `systems_data` ADD `conclusion_term` VARCHAR(6200) NULL;

ALTER TABLE `businesspartner` ADD `suggestion_new_data` VARCHAR(1024) NULL ;

ALTER TABLE `online_order` ADD `emissionMethodCompany` INT NULL AFTER `original_system`;

ALTER TABLE `online_order` ADD `emissionMethodMiles` INT NULL AFTER `emissionMethodCompany`;

ALTER TABLE `online_order` ADD `idCompany` INT NULL AFTER `emissionMethodMiles`;

ALTER TABLE `online_order` ADD `idMiles` INT NULL AFTER `idCompany`;

ALTER TABLE `businesspartner` ADD `client_markup` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `businesspartner` ADD `client_markup_type` ENUM('P','D') NULL DEFAULT 'P' ;

ALTER TABLE `online_order` ADD `agencia_id` INT NULL ;

ALTER TABLE `businesspartner` CHANGE `suggestion_new_data` `suggestion_new_data` VARCHAR(10024) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;







CREATE TABLE `system_check` ( `id` INT NOT NULL AUTO_INCREMENT , `businesspartner_id` INT NOT NULL , `issue_date` DATETIME NOT NULL , `info_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `system_check` ADD CONSTRAINT `fk_business_id` FOREIGN KEY (`businesspartner_id`) REFERENCES `businesspartner`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `system_check` ADD CONSTRAINT `fk_info_id` FOREIGN KEY (`info_id`) REFERENCES `info`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `system_check` ADD `check_info` BOOLEAN NULL AFTER `info_id`;
ALTER TABLE `airline` ADD `max_per_pax` DOUBLE(20,2) NULL DEFAULT '0.00' ;
update airline set max_per_pax = 24 where id = 1;




ALTER TABLE `online_order` ADD `first_boarding_date` DATETIME NULL ;
ALTER TABLE `online_flight` ADD `du_tax` DOUBLE(20,2) NULL DEFAULT '0.00' ;
