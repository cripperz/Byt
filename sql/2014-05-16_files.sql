ALTER TABLE `files` 
ADD COLUMN `dl_count_left` INT NOT NULL DEFAULT -1 AFTER `hash`,
ADD COLUMN `expire_date` INT NOT NULL DEFAULT -1 AFTER `dl_count_left`;

