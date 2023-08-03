ALTER TABLE `businesspartner` ADD `last_credit_analysis` DATETIME NULL ;


ALTER TABLE `online_pax` ADD `passaporte` VARCHAR(150) NULL ;
ALTER TABLE `online_pax` ADD `data_passaporte` DATETIME NULL ;
ALTER TABLE `robot_emission_in8` CHANGE `flight_locator` `flight_locator` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `robot_emission_in8` CHANGE `sucesso` `sucesso` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `robot_emission_in8` CHANGE `erro` `erro` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `robot_emission_in8` CHANGE `flight_locator` `flight_locator` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `robot_emission_in8` ADD `online_pax_id` INT NULL ;
ALTER TABLE `robot_emission_in8` ADD `online_flight_id` INT NULL ;
ALTER TABLE `robot_emission_in8` ADD INDEX(`online_pax_id`);
ALTER TABLE `robot_emission_in8` ADD INDEX(`online_flight_id`);


ALTER TABLE `robot_emission_in8` CHANGE `online_pax_id` `online_pax_id` INT(11) NULL;
ALTER TABLE `robot_emission_in8` CHANGE `online_flight_id` `online_flight_id` INT(11) NULL;
ALTER TABLE `robot_emission_in8` ADD CONSTRAINT `fk_online_flight_robot` FOREIGN KEY (`online_flight_id`) REFERENCES `online_flight`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `robot_emission_in8` ADD CONSTRAINT `fk_online_pax_robot` FOREIGN KEY (`online_pax_id`) REFERENCES `online_pax`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;



ALTER TABLE `robot_emission_in8` ADD `post` longtext NULL ;
ALTER TABLE `robot_emission_in8` ADD `retorno` longtext NULL ;

ALTER TABLE `robot_emission_in8` ADD `ficha` VARCHAR(255) NULL ;
ALTER TABLE `robot_emission_in8` ADD `airline` VARCHAR(155) NULL ;