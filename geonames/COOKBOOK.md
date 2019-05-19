# Cookbook for Disciple Tools `dt_geonames` table

### 1. Get Source File from Saturation Grid Project
[Saturation Grid Project > data_source > saturation-grid-geonames.tsv](https://github.com/DiscipleTools/saturation-grid-project/tree/master/data_source)

### 2. Create MYSQL DB Table `dt_geonames`
```apacheconfig
CREATE TABLE `dt_geonames` (
  `geonameid` bigint(20) unsigned NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `asciiname` varchar(2000) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `alternatenames` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `feature_class` char(1) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `feature_code` varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `country_code` char(2) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cc2` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `admin1_code` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `admin2_code` varchar(80) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `admin3_code` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `admin4_code` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `population` bigint(20) NOT NULL DEFAULT '0',
  `elevation` int(20) DEFAULT NULL,
  `dem` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `timezone` varchar(40) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `modification_date` date DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `country_geonameid` bigint(20) DEFAULT NULL,
  `admin1_geonameid` bigint(20) DEFAULT NULL,
  `admin2_geonameid` bigint(20) DEFAULT NULL,
  `admin3_geonameid` bigint(20) DEFAULT NULL,
  `level` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `north_latitude` float DEFAULT NULL,
  `south_latitude` float DEFAULT NULL,
  `west_longitude` float DEFAULT NULL,
  `east_longitude` float DEFAULT NULL,
  PRIMARY KEY (`geonameid`),
  KEY `feature_code` (`feature_code`),
  KEY `country_code` (`country_code`),
  KEY `population` (`population`),
  KEY `parent_id` (`parent_id`),
  KEY `country_geonameid` (`country_geonameid`),
  KEY `admin1_geonameid` (`admin1_geonameid`),
  KEY `admin2_geonameid` (`admin2_geonameid`),
  KEY `admin3_geonameid` (`admin3_geonameid`),
  KEY `level` (`level`),
  KEY `north_latitude` (`north_latitude`),
  KEY `south_latitude` (`south_latitude`),
  KEY `west_longitude` (`west_longitude`),
  KEY `east_longitude` (`east_longitude`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

### 3. Upload to MYSQL DB
- Import `saturation-grid-geonames.tsv` to the `dt_geonames`
    - Note: The first row has column labels.
    - Note: The file is tab delimited with no enclosing quote marks (")
    - Note: utf8 character encoding.

### 4. Update and Add DT Specific Columns, Indexes, and Column Data
This query adds the `alt_name`, `alt_population`, `is_custom_location`, `alt_name_changed`
`has_polygon` columns to the Saturation Grid database.

```apacheconfig
ALTER TABLE `dt_geonames` ADD `alt_name` VARCHAR(200) NULL DEFAULT NULL  AFTER `east_longitude`;
ALTER TABLE `dt_geonames` ADD `alt_population` BIGINT(20) NULL DEFAULT 0  AFTER `alt_name`;
ALTER TABLE `dt_geonames` ADD `is_custom_location` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `alt_population`;
ALTER TABLE `dt_geonames` ADD `alt_name_changed` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `is_custom_location`;
ALTER TABLE `dt_geonames` ADD `has_polygon` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `alt_name_changed`;
ALTER TABLE `dt_geonames` ADD `has_polygon_collection` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `has_polygon`;
ALTER TABLE `dt_geonames` ADD FULLTEXT INDEX (`alt_name`);
ALTER TABLE `dt_geonames` ADD INDEX (`has_polygon`);
ALTER TABLE `dt_geonames` ADD INDEX (`has_polygon_collection`);
UPDATE `dt_geonames` SET alt_name=name;
```

| Column                | Type            | Purpose  |
| --------------------  |:---------------:| :-----|
| alt_name              | VARCHAR(200)    | Editable field in DT. Duplicate name. |
| alt_population        | BIGINT(20)      | Editable field in DT. Default 0. |
| is_custom_location    | TINYINT(1)      | True/False. Row is a custom location. |
| alt_name_changed      | TINYINT(1)      | True/False. Alt_Name field has been changed. |
| has_polygon           | TINYINT(1)      | True/False. Single Polygon Geojson is available. |
| has_polygon_collection| TINYINT(1)      | True/False. Polygon Collection Geojson is available. |


## 5. Update `has_polygon` and `has_polygon_collection` Columns
1. Download [Has Polygon CSV file (saturation-grid-project/polygon/available_polygons.csv)](https://github.com/DiscipleTools/saturation-grid-project/tree/master/polygon/available_polygons.csv)
1. Download [Has Polygon Collection CSV file (saturation-grid-project/polygon/available_polygons_collection.csv)](https://github.com/DiscipleTools/saturation-grid-project/tree/master/polygon_collection/available_polygons.csv)
1. Import has_polygon/available_polygons.csv file into new mysql table named 'has_polygon' 
1. Import has_polygon_collection/available_polygons.csv file into new mysql table named 'has_polygon_collection' 
1. Run update queries
```apacheconfig
UPDATE `dt_geonames` SET has_polygon=1 WHERE geonameid IN (SELECT geonameid FROM has_polygon );
UPDATE `dt_geonames` SET has_polygon_collection=1 WHERE geonameid IN (SELECT geonameid FROM has_polygon_collection );
```
Note: These are long running queries. Could be improved.

## 5. Export, Naming Convention, Zip
The last part of the data preparation is exporting. 
1. Export `dt_geonames` table to a (tsv) tab delimited file.
    1. DO NOT enclose files with `"`
    1. DO NOT add first row column names.
    1. Set NULL fields to `\N`
    1. Escape fields with \
1. Name the tab delimited file: `geonames.tsv`
1. Zip tab delimited file as `geonames.tsv.zip`
    1. Note: Original ZIP was created on a Mac OS with the Archive Utility.