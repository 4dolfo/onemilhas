
CREATE TABLE `final_marckup_plans` (
  `id` int(11) NOT NULL,
  `plans_control_config_id` int(11) NOT NULL,
  `value` decimal(20,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `final_marckup_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plans_control_config_id` (`plans_control_config_id`);

ALTER TABLE `final_marckup_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `final_marckup_plans` ADD CONSTRAINT `fk_final_markup_plans_control_config_id` FOREIGN KEY (`plans_control_config_id`) REFERENCES `plans_control_config`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;