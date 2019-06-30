# Cookbook for Disciple Tools `dt_location_grid` table

### 1. Get Source File from Location Grid Project
[Location Grid Project > data_source > location_grid.tsv](https://github.com/DiscipleTools/location-grid-project/tree/master/data_source)

### 2. Install Full Location Grid Table

### 3. Rename table to 'dt_location_grid'

### 4. Reduce and add columns 
```apacheconfig
CREATE TABLE `dt_location_grid` (   `grid_id` bigint(20) NOT NULL AUTO_INCREMENT,   `name` varchar(200) NOT NULL DEFAULT '',   `level` int(1) DEFAULT NULL,   `level_name` varchar(6) DEFAULT NULL,   `country_code` varchar(10) DEFAULT NULL,   `admin0_code` varchar(10) DEFAULT NULL,   `admin1_code` varchar(20) DEFAULT NULL,   `admin2_code` varchar(20) DEFAULT NULL,   `admin3_code` varchar(20) DEFAULT NULL,   `admin4_code` varchar(20) DEFAULT NULL,   `admin5_code` varchar(20) DEFAULT NULL,   `parent_id` bigint(20) DEFAULT NULL,   `admin0_grid_id` bigint(20) DEFAULT NULL,   `admin1_grid_id` bigint(20) DEFAULT NULL,   `admin2_grid_id` bigint(20) DEFAULT NULL,   `admin3_grid_id` bigint(20) DEFAULT NULL,   `admin4_grid_id` bigint(20) DEFAULT NULL,   `admin5_grid_id` bigint(20) DEFAULT NULL,   `longitude` float DEFAULT NULL,   `latitude` float DEFAULT NULL,   `north_latitude` float DEFAULT NULL,   `south_latitude` float DEFAULT NULL,   `west_longitude` float DEFAULT NULL,   `east_longitude` float DEFAULT NULL,   `population` bigint(20) NOT NULL DEFAULT '0',   `modification_date` date DEFAULT NULL,   `geonames_ref` bigint(20) DEFAULT NULL,   `wikidata_ref` varchar(20) DEFAULT NULL,   PRIMARY KEY (`grid_id`),   KEY `level` (`level`),   KEY `latitude` (`latitude`),   KEY `longitude` (`longitude`),   KEY `admin0_code` (`admin0_code`),   KEY `admin1_code` (`admin1_code`),   KEY `admin2_code` (`admin2_code`),   KEY `admin3_code` (`admin3_code`),   KEY `admin4_code` (`admin4_code`),   KEY `country_code` (`country_code`),   KEY `north_latitude` (`north_latitude`),   KEY `south_latitude` (`south_latitude`),   KEY `parent_id` (`parent_id`),   KEY `west_longitude` (`west_longitude`),   KEY `east_longitude` (`east_longitude`),   KEY `admin5_code` (`admin5_code`),   KEY `admin0_grid_id` (`admin0_grid_id`),   KEY `admin1_grid_id` (`admin1_grid_id`),   KEY `admin2_grid_id` (`admin2_grid_id`),   KEY `admin3_grid_id` (`admin3_grid_id`),   KEY `admin4_grid_id` (`admin4_grid_id`),   KEY `admin5_grid_id` (`admin5_grid_id`),   KEY `level_name` (`level_name`),   FULLTEXT KEY `name` (`name`) ) ENGINE=InnoDB AUTO_INCREMENT=100386738 DEFAULT CHARSET=utf8;

INSERT INTO `dt_location_grid` SELECT * FROM `location_grid`;

DELETE FROM `dt_location_grid` WHERE level > 2;

ALTER TABLE `dt_location_grid` DROP INDEX admin1_code;
ALTER TABLE `dt_location_grid` DROP INDEX admin2_code;
ALTER TABLE `dt_location_grid` DROP INDEX admin3_code;
ALTER TABLE `dt_location_grid` DROP INDEX admin4_code;
ALTER TABLE `dt_location_grid` DROP INDEX admin5_code;
ALTER TABLE `dt_location_grid` DROP COLUMN admin1_code;
ALTER TABLE `dt_location_grid` DROP COLUMN admin2_code;
ALTER TABLE `dt_location_grid` DROP COLUMN admin3_code;
ALTER TABLE `dt_location_grid` DROP COLUMN admin4_code;
ALTER TABLE `dt_location_grid` DROP COLUMN admin5_code;
ALTER TABLE `dt_location_grid` DROP COLUMN geonames_ref;
ALTER TABLE `dt_location_grid` DROP COLUMN wikidata_ref;
ALTER TABLE `dt_location_grid` ADD `alt_name` VARCHAR(200) NULL DEFAULT NULL  AFTER `modification_date`;
ALTER TABLE `dt_location_grid` ADD `alt_population` BIGINT(20) NULL DEFAULT 0  AFTER `alt_name`;
ALTER TABLE `dt_location_grid` ADD `is_custom_location` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `alt_population`;
ALTER TABLE `dt_location_grid` ADD `alt_name_changed` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `is_custom_location`;
ALTER TABLE `dt_location_grid` ADD FULLTEXT INDEX (`alt_name`);
UPDATE `dt_location_grid` SET alt_name=name;
```