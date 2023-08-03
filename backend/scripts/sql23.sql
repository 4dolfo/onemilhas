ALTER TABLE `sale_plans_charging_methods` ADD `interest_free` INT NULL ;
ALTER TABLE `sale_plans_charging_methods` CHANGE `interest_free_installment` `interest_free_installment` DOUBLE(20,6) NULL DEFAULT NULL;


ALTER TABLE `sale_plans_charging_methods` ADD `extra_value` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `sale_plans_charging_methods` ADD `extra_type` VARCHAR(1) NULL DEFAULT 'D' ;
