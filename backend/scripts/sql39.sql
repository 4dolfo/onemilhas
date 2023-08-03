ALTER TABLE `sale` ADD `cancellation_requested` VARCHAR(5) NULL DEFAULT 'false';
ALTER TABLE `sale` ADD `refund_requested` VARCHAR(5) NULL DEFAULT 'false';


ALTER TABLE robot_emission_in8 DROP FOREIGN KEY fk_online_flight_robot;
ALTER TABLE robot_emission_in8 DROP FOREIGN KEY fk_online_pax_robot;
ALTER TABLE `robot_emission_in8` DROP `online_pax_id`;
ALTER TABLE `robot_emission_in8` DROP `online_flight_id`;

ALTER TABLE cupons
  ADD `valid_voos` enum('N','I') DEFAULT NULL,
  ADD `aereas` varchar(255) DEFAULT NULL;

ALTER TABLE sale
  ADD is_diamond BOOLEAN DEFAULT false;

ALTER TABLE cards
  ADD max_diamond_pax INT(3) DEFAULT 0;

ALTER TABLE cupons
  ADD milhas BOOLEAN DEFAULT true,
  ADD pagante BOOLEAN DEFAULT false;
