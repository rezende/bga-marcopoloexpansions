
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- MarcoPoloExpansions implementation : © Hershey Sakhrani <hersh16@yahoo.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):
CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `die` (
   `die_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
   `die_type` varchar(16) NOT NULL,
   `die_value` int(10) UNSIGNED DEFAULT '0',
   `die_location` varchar(16),
   `die_location_arg` varchar(16),   
   `die_location_height` int(10) unsigned NOT NULL DEFAULT '0',
   `die_player_id` varchar(16),
   PRIMARY KEY (`die_id`)   
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `piece` (
    `piece_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `piece_type` varchar(32) NOT NULL,
    `piece_type_arg` int(10),
    `piece_player_id` varchar(16),
    `piece_location` varchar(32) NOT NULL,
    `piece_location_arg` varchar(32) NOT NULL,
    `piece_location_position` int,
    PRIMARY KEY (`piece_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `pending_action` (
    `pending_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `pending_type` varchar(64) NOT NULL,
    `pending_type_arg` varchar(32),
    `pending_type_arg1` varchar(32),
    `pending_remaining_count` int,
    `pending_location` varchar(32),    
    `pending_order` INT DEFAULT '0', 
    `pending_triggered_by` varchar(32),
    `pending_player_id` varchar(32),
    PRIMARY KEY (`pending_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- add a custom field to the standard "player" table
ALTER TABLE `player` ADD `camel` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `pepper` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `silk` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `gold` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `coin` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `vp` INT NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `character_type` INT UNSIGNED NOT NULL DEFAULT '99';
ALTER TABLE `player` ADD `character_ability_used` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `hourglass` INT UNSIGNED NOT NULL DEFAULT '0';