ALTER TABLE `plans_control` ADD `fixes_amount` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `plans_control` ADD `use_fixed_value` VARCHAR(5) NULL DEFAULT 'false' ;
ALTER TABLE `sale` ADD `special_seat` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `sale` ADD `baggage_price` DOUBLE(20,2) NULL DEFAULT '0.00' ;