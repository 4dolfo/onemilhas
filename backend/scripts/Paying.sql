ALTER TABLE `online_order` ADD `emissionMethodCompany` INT NULL AFTER `original_system`;

ALTER TABLE `online_order` ADD `emissionMethodMiles` INT NULL AFTER `emissionMethodCompany`;

ALTER TABLE `online_order` ADD `idCompany` INT NULL AFTER `emissionMethodMiles`;

ALTER TABLE `online_order` ADD `idMiles` INT NULL AFTER `idCompany`;