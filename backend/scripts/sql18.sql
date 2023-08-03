ALTER TABLE `cards` ADD `minimum_miles` DOUBLE(20,2) NULL DEFAULT '0.00' ;
ALTER TABLE `cost_center` ADD `id_children` INT NULL ;
ALTER TABLE `cost_center` ADD INDEX(`id_children`);

ALTER TABLE `cost_center` ADD CONSTRAINT `fk_cost_center_cost_center` FOREIGN KEY (`id_children`) REFERENCES `cost_center`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
