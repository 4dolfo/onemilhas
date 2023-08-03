ALTER TABLE `cards` ADD `people_used_by_the_card` INT NULL DEFAULT '0' ;

ALTER TABLE `airline` ADD `max_pax_field` VARCHAR(40) NULL ;
update airline set max_pax_field = 'registration_code' where id = 1;
update airline set max_per_pax = 18 where id = 1;