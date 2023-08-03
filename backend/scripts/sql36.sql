ALTER TABLE `purchase` CHANGE `payment_method` `payment_method` ENUM('prepaid','after_payment','after_use') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'prepaid';
ALTER TABLE `billspay` CHANGE `due_date` `due_date` DATETIME NULL;


CREATE TRIGGER `milesbench_balance` BEFORE INSERT ON `milesbench`
FOR EACH ROW INSERT INTO `system_log` (`id`, `businesspartner_id`, `issue_date`, `description`, `log_type`)
VALUES (NULL, NULL, NOW(), CONCAT('Alteração de valor cartao ->',NEW.cards_id,' para ',NEW.leftover), 'TRIGGER');

CREATE TRIGGER `milesbench_balance_update` BEFORE UPDATE ON `milesbench`
FOR EACH ROW INSERT INTO `system_log` (`id`, `businesspartner_id`, `issue_date`, `description`, `log_type`)
VALUES (NULL, NULL, NOW(), CONCAT('Alteração de valor cartao ->',NEW.cards_id,' para ',NEW.leftover), 'TRIGGER');
