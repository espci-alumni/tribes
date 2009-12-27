ALTER TABLE `ece_phpbb`.`user_group` DROP INDEX `group_id` ,
ADD UNIQUE `group_id` ( `group_id` , `user_id` ) ;
