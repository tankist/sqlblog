# SQL Manager 2010 for MySQL 4.5.0.9
# ---------------------------------------
# Host     : localhost
# Port     : 3306
# Database : sqlblog

SET FOREIGN_KEY_CHECKS=0;

DROP DATABASE IF EXISTS `sqlblog`;

CREATE DATABASE `sqlblog`
    CHARACTER SET 'utf8'
    COLLATE 'utf8_general_ci';

USE `sqlblog`;

#
# Structure for the `sb_posts` table : 
#

DROP TABLE IF EXISTS `sb_posts`;

CREATE TABLE `sb_posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `text` text,
  `date` datetime DEFAULT NULL,
  `commentsCount` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `sb_comments` table : 
#

DROP TABLE IF EXISTS `sb_comments`;

CREATE TABLE `sb_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) unsigned NOT NULL,
  `user` varchar(50) NOT NULL,
  `text` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `sb_comments_fk` FOREIGN KEY (`post_id`) REFERENCES `sb_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER $$

CREATE DEFINER = 'sqlblog'@'localhost' TRIGGER `sb_comments_after_ins_tr` AFTER INSERT ON `sb_comments`
  FOR EACH ROW
BEGIN
    UPDATE `sb_posts` AS p
    SET p.`commentsCount` = p.`commentsCount` + 1
    WHERE p.`id` = NEW . `post_id`;
END$$

CREATE DEFINER = 'sqlblog'@'localhost' TRIGGER `sb_comments_after_del_tr` AFTER DELETE ON `sb_comments`
  FOR EACH ROW
BEGIN
	UPDATE `sb_posts` AS p
    SET p.`commentsCount` = p.`commentsCount` - 1
    WHERE p.`id` = OLD . `post_id`;
END$$

DELIMITER ;

#
# Structure for the `sb_tags` table : 
#

DROP TABLE IF EXISTS `sb_tags`;

CREATE TABLE `sb_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) DEFAULT NULL,
  `weight` int(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Structure for the `sb_post_tags` table : 
#

DROP TABLE IF EXISTS `sb_post_tags`;

CREATE TABLE `sb_post_tags` (
  `tag_id` int(11) unsigned NOT NULL,
  `post_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`post_id`),
  KEY `tag_id` (`tag_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `sb_post_tags_fk` FOREIGN KEY (`tag_id`) REFERENCES `sb_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sb_post_tags_fk1` FOREIGN KEY (`post_id`) REFERENCES `sb_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER $$

CREATE DEFINER = 'sqlblog'@'localhost' TRIGGER `sb_post_tags_after_ins_tr` AFTER INSERT ON `sb_post_tags`
  FOR EACH ROW
BEGIN
	UPDATE `sb_tags` AS t
    	SET t.`weight` = `Tag_calculateWeight`(`Tag_getById`(NEW . tag_id))
	    WHERE t.`id` = NEW.tag_id;
END$$

CREATE DEFINER = 'sqlblog'@'localhost' TRIGGER `sb_post_tags_after_del_tr` AFTER DELETE ON `sb_post_tags`
  FOR EACH ROW
BEGIN
	UPDATE `sb_tags` AS t
    	SET t.`weight` = `Tag_calculateWeight`(`Tag_getById`(OLD.`tag_id`))
		WHERE t.`id` = OLD.`tag_id`;
END$$

DELIMITER ;

#
# Definition for the `Posts_getCount` function : 
#

DELIMITER $$

DROP FUNCTION IF EXISTS `Posts_getCount`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Posts_getCount`()
    RETURNS int(11)
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE postsCount INTEGER(11);
    SELECT COUNT(*) INTO postsCount FROM `sb_posts`;
	RETURN postsCount;
END$$

#
# Definition for the `Post_getCommentsCount` function : 
#

DROP FUNCTION IF EXISTS `Post_getCommentsCount`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Post_getCommentsCount`(
        post_id INTEGER(11)
    )
    RETURNS int(11)
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `commentsCount` INT(11);
    SELECT COUNT(*) INTO `commentsCount` FROM `sb_comments` AS c WHERE c.`post_id` = `post_id`;
	RETURN `commentsCount`;
END$$

#
# Definition for the `Post_getTagsCount` function : 
#

DROP FUNCTION IF EXISTS `Post_getTagsCount`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Post_getTagsCount`(
        post_id INTEGER(11)
    )
    RETURNS tinyint(4)
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `tagsCount` INT(11);
    SELECT COUNT(*) INTO `tagsCount` FROM `sb_post_tags` AS t WHERE t.`post_id` = `post_id`;
	RETURN `tagsCount`;
END$$

#
# Definition for the `Tag_getPostsCount` function : 
#

DROP FUNCTION IF EXISTS `Tag_getPostsCount`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Tag_getPostsCount`(
        tag VARCHAR(50)
    )
    RETURNS int(11)
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `postsCount` INTEGER(11);
    SELECT COUNT(*) INTO `postsCount` FROM `sb_post_tags` AS pt
    	INNER JOIN `sb_tags` AS t ON t.`id` = pt.`tag_id`
        WHERE t.`tag` = `tag`;
	RETURN `postsCount`;
END$$

#
# Definition for the `Tag_calculateWeight` function : 
#

DROP FUNCTION IF EXISTS `Tag_calculateWeight`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Tag_calculateWeight`(
        tag VARCHAR(50)
    )
    RETURNS int(3)
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `tagWeight` INTEGER(3);
	SET `tagWeight` = ROUND(100 * `Tag_getPostsCount`(`tag`) / `Posts_getCount`());
	RETURN `tagWeight`;
END$$

#
# Definition for the `Tag_getById` function : 
#

DROP FUNCTION IF EXISTS `Tag_getById`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Tag_getById`(
        tag_id INTEGER(11)
    )
    RETURNS varchar(50) CHARSET utf8
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `tag` VARCHAR(50);
	SELECT t.`tag` INTO `tag` FROM `sb_tags` AS t WHERE t.`id` = `tag_id` LIMIT 1;
	RETURN `tag`;
END$$

#
# Definition for the `Tag_getIdByTag` function : 
#

DROP FUNCTION IF EXISTS `Tag_getIdByTag`$$

CREATE DEFINER = 'sqlblog'@'localhost' FUNCTION `Tag_getIdByTag`(
        tag VARCHAR(50)
    )
    RETURNS int(11)
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `tag_id` INTEGER(11);
    SELECT t.`id` INTO `tag_id` FROM `sb_tags` AS t WHERE t.`tag` = `tag` LIMIT 1;
	RETURN `tag_id`;
END$$

#
# Definition for the `Comments_getLast` procedure : 
#

DROP PROCEDURE IF EXISTS `Comments_getLast`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Comments_getLast`()
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	SELECT * FROM `sb_comments` AS c ORDER BY c.`date` DESC LIMIT 10;
END$$

#
# Definition for the `Comment_remove` procedure : 
#

DROP PROCEDURE IF EXISTS `Comment_remove`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Comment_remove`(
        IN comment_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DELETE FROM `sb_comments` WHERE `id` = `comment_id` LIMIT 1;
END$$

#
# Definition for the `Posts_getById` procedure : 
#

DROP PROCEDURE IF EXISTS `Posts_getById`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Posts_getById`(
        IN post_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	SELECT * FROM `sb_posts` AS p WHERE p.`id` = `post_id` LIMIT 1;
END$$

#
# Definition for the `Posts_save` procedure : 
#

DROP PROCEDURE IF EXISTS `Posts_save`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Posts_save`(
        IN title VARCHAR(100),
        IN text TEXT,
        INOUT post_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	IF ISNULL(`post_id`) OR `post_id` = 0 THEN
    	INSERT INTO `sb_posts` (`title`, `text`, `date`) 
    		VALUES (`title`, `text`, NOW());
        SET `post_id` = LAST_INSERT_ID();
    ELSE
    	UPDATE `sb_posts` AS p 
    		SET p.`title` = `title`, p.`text` = `text` 
        	WHERE p.`id` = `post_id` LIMIT 1;
    END IF;
END$$

#
# Definition for the `Post_addComment` procedure : 
#

DROP PROCEDURE IF EXISTS `Post_addComment`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Post_addComment`(
        IN post_id INTEGER(11),
        IN user VARCHAR(50),
        IN text TEXT,
        OUT comment_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
    INSERT INTO `sb_comments` (`post_id`, `user`, `text`, `date`) 
    	VALUES (`post_id`, `user`, `text`, NOW());
    SET `comment_id` = LAST_INSERT_ID();
END$$

#
# Definition for the `Tags_save` procedure : 
#

DROP PROCEDURE IF EXISTS `Tags_save`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Tags_save`(
        IN tag VARCHAR(50),
        INOUT tag_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	IF ISNULL(`tag_id`) OR `tag_id` = 0 THEN
    	INSERT INTO `sb_tags` (`tag`) 
    		VALUES (`tag`);
        SET `tag_id` = LAST_INSERT_ID();
    ELSE
    	UPDATE `sb_tags` AS t 
    		SET t.`tag` = `tag` 
        	WHERE t.`id` = `tag_id` LIMIT 1;
    END IF;
END$$

#
# Definition for the `Post_addTag` procedure : 
#

DROP PROCEDURE IF EXISTS `Post_addTag`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Post_addTag`(
        IN post_id INTEGER(11),
        IN tag VARCHAR(100)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DECLARE `tag_id` INT(11);
	SELECT `t`.`id` INTO `tag_id` FROM `sb_tags` AS t WHERE t.`tag` = `tag`;
    IF ISNULL(`tag_id`) OR `tag_id` = 0 THEN
    	CALL `Tags_save`(`tag`, `tag_id`);
    END IF;
    INSERT IGNORE INTO `sb_post_tags` (`post_id`, `tag_id`)
    	VALUES(`post_id`, `tag_id`);
END$$

#
# Definition for the `Post_clearTags` procedure : 
#

DROP PROCEDURE IF EXISTS `Post_clearTags`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Post_clearTags`(
        IN post_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DELETE FROM `sb_post_tags` WHERE `sb_post_tags`.`post_id` = `post_id`;
END$$

#
# Definition for the `Post_getComments` procedure : 
#

DROP PROCEDURE IF EXISTS `Post_getComments`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Post_getComments`(
        IN post_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	SELECT c.* FROM `sb_comments` AS c WHERE c.`post_id` = `post_id`;
END$$

#
# Definition for the `Post_getTags` procedure : 
#

DROP PROCEDURE IF EXISTS `Post_getTags`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Post_getTags`(
        IN post_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	SELECT t.* FROM `sb_tags` AS t INNER JOIN `sb_post_tags` as pt ON pt.`tag_id` = t.`id` WHERE pt.`post_id` = `post_id`;
END$$

#
# Definition for the `Post_remove` procedure : 
#

DROP PROCEDURE IF EXISTS `Post_remove`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Post_remove`(
        IN post_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DELETE FROM `sb_posts` WHERE `id` = `post_id` LIMIT 1;
END$$

#
# Definition for the `Tag_getPosts` procedure : 
#

DROP PROCEDURE IF EXISTS `Tag_getPosts`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Tag_getPosts`(
        IN tag VARCHAR(50)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	SELECT p.* FROM `sb_posts` AS p 
    	INNER JOIN `sb_post_tags` AS pt ON pt.`post_id` = p.`id`
        INNER JOIN `sb_tags` AS t ON t.`id` = pt.`tag_id`
        WHERE t.`tag` = `tag`;
END$$

#
# Definition for the `Tag_remove` procedure : 
#

DROP PROCEDURE IF EXISTS `Tag_remove`$$

CREATE DEFINER = 'sqlblog'@'localhost' PROCEDURE `Tag_remove`(
        IN tag_id INTEGER(11)
    )
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
	DELETE FROM `sb_tags` WHERE `id` = `tag_id` LIMIT 1;
END$$

DELIMITER ;