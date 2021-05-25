<?php
/**
 * Command Line Utility for Building DT Location Grid Folder
 *
 * @requires a mysql database with connection information in the connect_params.json
 *          The full location_grid table installed in the database.
 *           ability to create/overwrite a new table called 'dt_location_grid'
 *           ability to save to a subfolder called /location_grid
 *
 *
 *           php build_location_grid.php
 */

// Extend PHP limits for large processing
ini_set('memory_limit', '50000M');

$start_timestamp = date('H:i:s');

print '**************************************************************'. PHP_EOL;
print date('H:i:s') . ' | START SCRIPT'. PHP_EOL;
print '**************************************************************'. PHP_EOL;


/*************************************************************************************************************/
// define and create output directories
// define table names
$table = [
    'lg' => 'location_grid',
    'geo' => 'location_grid_geometry',
    'dt' => 'dt_location_grid',
];
$output = [
    'lg' => getcwd() . '/location_grid_v2/',
    'root' => getcwd() . '/',
];

foreach ( $output as $dirname ) {
    if ( ! is_dir( $dirname ) ) {
        mkdir($dirname, 0755, true);
    }
}

// clear folder
$current_files = scandir( $output['lg'] );
foreach ( $current_files as $file ) {
    if ( file_exists( $output['lg'] . $file ) ) {
        unlink($output['lg'] . $file );
    }
}


// define database connection
if ( ! file_exists( 'connect_params.json') ) {
    $content = '{"host": "","username": "","password": "","database": ""}';
    file_put_contents( 'connect_params.json', $content );
}
$params = json_decode( file_get_contents( "connect_params.json" ), true );
if ( empty( $params['host'] ) ) {
    print 'You have just created the connect_params.json file, but you still need to add database connection information.
Please, open the connect_params.json file and add host, username, password, and database information.' . PHP_EOL;
    exit();
}
$con = mysqli_connect( $params['host'], $params['username'], $params['password'],$params['database']);
if (!$con) {
    echo 'mysqli Connection FAILED. Check parameters inside connect_params.json file.' . PHP_EOL;
    exit();
}
$rebuild_db = true;
if ( isset( $argv[1] ) && 'skip_db' === $argv[1] ) {
    $rebuild_db = false;
}

if ( $rebuild_db ) :
// test expected location_grid table
$exists = mysqli_query( $con, "
        SELECT 1 FROM {$table['lg']} LIMIT 1;
    " );
if ( ! $exists ) {
    print 'Could not connect with location_grid source table.' . PHP_EOL;
    exit();
}
$exists = mysqli_query( $con, "
        SELECT 1 FROM {$table['dt']} LIMIT 1;
    " );
if ( $exists ) {
    print date('H:i:s') . ' | Drop previous dt_location_grid table.' . PHP_EOL;
    mysqli_query( $con, "
        DROP TABLE {$table['dt']};
    " );
    print date('H:i:s') . ' | End previous dt_location_grid table.' . PHP_EOL;
}
/*************************************************************************************************************/
// END SETUP
/*************************************************************************************************************/


/*************************************************************************************************************/
// CREATE DT_LOCATION_GRID DATABASE
/*************************************************************************************************************/

// create table
print date('H:i:s') . ' | Start create table.' . PHP_EOL;
$result = mysqli_query( $con, "
    CREATE TABLE {$table['dt']} (
  `grid_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `level` float DEFAULT NULL,
  `level_name` varchar(7) DEFAULT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `admin0_code` varchar(10) DEFAULT NULL,
  `admin1_code` varchar(20) DEFAULT NULL,
  `admin2_code` varchar(20) DEFAULT NULL,
  `admin3_code` varchar(20) DEFAULT NULL,
  `admin4_code` varchar(20) DEFAULT NULL,
  `admin5_code` varchar(20) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `admin0_grid_id` bigint(20) DEFAULT NULL,
  `admin1_grid_id` bigint(20) DEFAULT NULL,
  `admin2_grid_id` bigint(20) DEFAULT NULL,
  `admin3_grid_id` bigint(20) DEFAULT NULL,
  `admin4_grid_id` bigint(20) DEFAULT NULL,
  `admin5_grid_id` bigint(20) DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `north_latitude` float DEFAULT NULL,
  `south_latitude` float DEFAULT NULL,
  `east_longitude` float DEFAULT NULL,
  `west_longitude` float DEFAULT NULL,
  `population` bigint(20) NOT NULL DEFAULT '0',
  `modification_date` date DEFAULT NULL,
  `geonames_ref` bigint(20) DEFAULT NULL,
  `wikidata_ref` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`grid_id`),
  KEY `level` (`level`),
  KEY `latitude` (`latitude`),
  KEY `longitude` (`longitude`),
  KEY `admin0_code` (`admin0_code`),
  KEY `admin1_code` (`admin1_code`),
  KEY `admin2_code` (`admin2_code`),
  KEY `admin3_code` (`admin3_code`),
  KEY `admin4_code` (`admin4_code`),
  KEY `country_code` (`country_code`),
  KEY `parent_id` (`parent_id`),
  KEY `north_latitude` (`north_latitude`),
  KEY `south_latitude` (`south_latitude`),
  KEY `east_longitude` (`west_longitude`),
  KEY `west_longitude` (`east_longitude`),
  KEY `admin5_code` (`admin5_code`),
  KEY `admin0_grid_id` (`admin0_grid_id`),
  KEY `admin1_grid_id` (`admin1_grid_id`),
  KEY `admin2_grid_id` (`admin2_grid_id`),
  KEY `admin3_grid_id` (`admin3_grid_id`),
  KEY `admin4_grid_id` (`admin4_grid_id`),
  KEY `admin5_grid_id` (`admin5_grid_id`),
  KEY `level_name` (`level_name`),
  KEY `population` (`population`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | Failed to create table' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End create table.' . PHP_EOL;
}

// transfer data
print date('H:i:s') . ' | Start transfer data.' . PHP_EOL;
$result = mysqli_query( $con, "
INSERT INTO {$table['dt']} SELECT * FROM `location_grid`;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | Failed to transfer data.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End transfer data.' . PHP_EOL;
}


// drop indexes
print date('H:i:s') . ' | Start drop index admin1.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP INDEX admin1_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped indexes.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End drop index admin1.' . PHP_EOL;
}

// drop indexes
print date('H:i:s') . ' | Start drop index admin2.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP INDEX admin2_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped indexes.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End drop index admin2.' . PHP_EOL;
}

// drop indexes
print date('H:i:s') . ' | Start drop index admin3.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP INDEX admin3_code;

    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped indexes.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End drop index admin3.' . PHP_EOL;
}

// drop indexes
print date('H:i:s') . ' | Start drop index admin4.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP INDEX admin4_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped indexes.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End drop index admin4.' . PHP_EOL;
}

// drop indexes
print date('H:i:s') . ' | Start drop index admin5.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP INDEX admin5_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped indexes.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End drop index admin5.' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column admin1.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN admin1_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column admin1' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column admin2.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN admin2_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column admin2' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column admin3.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN admin3_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column admin3' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column admin4.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN admin4_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column admin4' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column admin5.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN admin5_code;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column admin5' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column geonames_ref.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN geonames_ref;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column geonames_ref' . PHP_EOL;
}

// drop columns
print date('H:i:s') . ' | Start drop column wikidata_ref.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} DROP COLUMN wikidata_ref;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Dropped columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Dropped column wikidata_ref' . PHP_EOL;
}

// add columns
print date('H:i:s') . ' | Start add column alt_name.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} ADD `alt_name` VARCHAR(200) NULL DEFAULT NULL  AFTER `modification_date`;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Added columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End add column alt_name.' . PHP_EOL;
}

// add columns
print date('H:i:s') . ' | Start add column alt_population.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} ADD `alt_population` BIGINT(20) NULL DEFAULT 0  AFTER `alt_name`;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Added columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End add column alt_population.' . PHP_EOL;
}
// add columns
print date('H:i:s') . ' | Start is_custom_location.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} ADD `is_custom_location` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `alt_population`;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Added columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End is_custom_location.' . PHP_EOL;
}

// add columns
print date('H:i:s') . ' | Start add column alt_name_changed.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} ADD `alt_name_changed` TINYINT(1)  NOT NULL  DEFAULT '0'  AFTER `is_custom_location`;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Added columns.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End alt_name_changed.' . PHP_EOL;
}


// add index
print date('H:i:s') . ' | Start alt_name_changed.' . PHP_EOL;
$result = mysqli_query( $con, "
ALTER TABLE {$table['dt']} ADD FULLTEXT INDEX (`alt_name`);
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Add index.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | Add index.' . PHP_EOL;
}

// copy names
print date('H:i:s') . ' | Start copy names.' . PHP_EOL;
$result = mysqli_query( $con, "
UPDATE {$table['dt']} SET alt_name=name;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Copy names.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End copy names.' . PHP_EOL;
}

// copy names
print date('H:i:s') . ' | Start populations.' . PHP_EOL;
$result = mysqli_query( $con, "
UPDATE {$table['dt']} SET alt_population=population;
    " );
if ( ! $result ) {
    print date('H:i:s') . ' | FAIL: Copy populations.' . PHP_EOL;
    exit();
} else {
    print date('H:i:s') . ' | End populations.' . PHP_EOL;
}


/*************************************************************************************************************/
// END CREATE DT_LOCATION_GRID DATABASE
/*************************************************************************************************************/

endif; // rebuild db


/*************************************************************************************************************/
// CLEAR FOLDER
/*************************************************************************************************************/


/*************************************************************************************************************/
// CREATE COUNTRY FILES
/*************************************************************************************************************/

// create country zip files
$json = [];
$results = mysqli_query( $con, "
SELECT admin0_code FROM {$table['lg']} GROUP BY admin0_code;
    " );
$raw_list = mysqli_fetch_all($results, MYSQLI_NUM);
$list = [];
foreach( $raw_list as $l ) {
    $list[] = $l[0];
}

foreach ( $list as $admin0 ) {
    if ( empty( $admin0 ) ) {
        continue;
    }
    if ( file_exists( $output['lg'] . $admin0 . '.tsv' ) ) {
        unlink($output['lg'] . $admin0 . '.tsv');
    }
    // #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
    if ( in_array($admin0, [100050711,100219347,100074576,100259978,100018514])) {
        $results = mysqli_query( $con, "
        SELECT * FROM {$table['dt']} WHERE admin0_code = '{$admin0}' AND level > 3 INTO OUTFILE '{$output['lg']}{$admin0}.tsv' 
        FIELDS TERMINATED BY '\t' 
        LINES TERMINATED BY '\n';
    " );
    }
    // #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
    else if ( in_array($admin0, [100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571]) ) {
        $results = mysqli_query( $con, "
        SELECT * FROM {$table['dt']} WHERE admin0_code = '{$admin0}' AND level > 1 INTO OUTFILE '{$output['lg']}{$admin0}.tsv' 
        FIELDS TERMINATED BY '\t' 
        LINES TERMINATED BY '\n';
    " );
    } else {
        $results = mysqli_query( $con, "
        SELECT * FROM {$table['dt']} WHERE admin0_code = '{$admin0}' AND level > 2 INTO OUTFILE '{$output['lg']}{$admin0}.tsv' 
        FIELDS TERMINATED BY '\t' 
        LINES TERMINATED BY '\n';
    " );
    }

    if ( filesize( $output['lg'] . $admin0 . '.tsv' ) < 1 ) {
        unlink( $output['lg'] . $admin0 . '.tsv' );
        print date('H:i:s') . ' | '.$admin0.' no value. Removed.'. PHP_EOL;
        continue;
    }

    // generate extension record
    $query = mysqli_query( $con, "
        SELECT count(*) as count FROM {$table['lg']} WHERE admin0_code = '{$admin0}' AND level > 2 AND level < 10;
    " );
    $count = mysqli_fetch_array( $query );

    $query = mysqli_query( $con, "
        SELECT name  FROM {$table['lg']} WHERE admin0_code = '{$admin0}' AND level= 0 LIMIT 1;
    " );
    $name = mysqli_fetch_array( $query );

    $json[$admin0] = $name['name'] . ' (' . $count['count'] . ')'; // add to countries_with_extended_levels.json list

    print date('H:i:s') . ' | ' . $admin0 . PHP_EOL;

}


// extended files
print date('H:i:s') . ' | Start create countries_with_extended_levels.json' . PHP_EOL;
$json = json_encode( $json );
if ( file_exists( $output['lg']  . 'countries_with_extended_levels.json' ) ) {
    unlink($output['lg']  . 'countries_with_extended_levels.json' );
}
file_put_contents( $output['lg']  . 'countries_with_extended_levels.json', $json );
if ( file_exists( $output['lg']  . 'countries_with_extended_levels.json' ) ) {
    print date('H:i:s') . ' | End create countries_with_extended_levels.json' . PHP_EOL;
}

/*************************************************************************************************************/
// END CREATE COUNTRY FILES
/*************************************************************************************************************/

/*************************************************************************************************************/
// BREAK FULL DATABASE INTO SMALL CHUNKS FOR FULL INSTALL
/*************************************************************************************************************/

// entire dt_locations_database
$loop_count = 15;
$offset = 0;
for ($i = 0; $i <= $loop_count; $i++) {
    if ( file_exists( $output['lg'] . 'dt_full_location_grid_'.$i.'.tsv' ) ) {
        unlink($output['lg'] . 'dt_full_location_grid_'.$i.'.tsv');
    }
    $results = mysqli_query( $con, "
        SELECT * FROM {$table['dt']} LIMIT 30000 OFFSET {$offset} INTO OUTFILE '{$output['lg']}dt_full_location_grid_{$i}.tsv' 
        FIELDS TERMINATED BY '\t' 
        LINES TERMINATED BY '\n';
    " );
    print 'offset '. $offset . PHP_EOL;
    if ( filesize( $output['lg'] . 'dt_full_location_grid_'.$i.'.tsv' ) === 0 ) {
        unlink( $output['lg'] . 'dt_full_location_grid_'.$i.'.tsv' );
        print date('H:i:s') . ' | ' . 'dt_full_location_grid_'.$i.'.tsv no value. Removed.'. PHP_EOL;
    }

    $offset = $offset + 30000;
}

/*************************************************************************************************************/
// END SMALL CHUNKS FOR FULL INSTALL
/*************************************************************************************************************/

/*************************************************************************************************************/
// BREAK FULL DATABASE INTO SMALL CHUNKS FOR FULL INSTALL
/*************************************************************************************************************/

// entire dt_locations_database
//$loop_count = 12;
//$offset = 0;
//for ($i = 0; $i <= $loop_count; $i++) {
//    if ( file_exists( $output['lg'] . 'dt_location_grid_50k_'.$i.'.tsv' ) ) {
//        unlink($output['lg'] . 'dt_location_grid_50k_'.$i.'.tsv');
//    }
//    $results = mysqli_query( $con, "
//        SELECT * FROM {$table['dt']} LIMIT 30000 OFFSET {$offset} INTO OUTFILE '{$output['lg']}dt_location_grid_50k_{$i}.tsv'
//        FIELDS TERMINATED BY '\t'
//        LINES TERMINATED BY '\n';
//    " );
//    if ( filesize( $output['lg'] . 'dt_full_location_grid_'.$i.'.tsv' ) === 0 ) {
//        unlink( $output['lg'] . 'dt_full_location_grid_'.$i.'.tsv' );
//        print date('H:i:s') . ' | ' . 'dt_full_location_grid_'.$i.'.tsv no value. Removed.'. PHP_EOL;
//    }
//
//    $offset = $offset + 30000;
//}

/*************************************************************************************************************/
// END SMALL CHUNKS FOR FULL INSTALL
/*************************************************************************************************************/

/*************************************************************************************************************/
// CREATE DATA TABLE FILE
/*************************************************************************************************************/


$result = mysqli_query( $con, "
SELECT * FROM (
    SELECT * 
FROM {$table['dt']}
WHERE 
level <= 3
	#'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
	AND admin0_grid_id IN (100050711,100219347,100074576,100259978,100018514) 
	#'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
	AND admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

UNION ALL

SELECT * 
FROM {$table['dt']}
WHERE 
level <= 1
	#'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
	AND admin0_grid_id NOT IN (100050711,100219347,100074576,100259978,100018514) 
	#'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
	AND admin0_grid_id IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

UNION ALL

SELECT * 
FROM {$table['dt']}
WHERE 
level <= 2
	#'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
	AND admin0_grid_id NOT IN (100050711,100219347,100074576,100259978,100018514) 
	#'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
	AND admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
) as tb
INTO OUTFILE '{$output['lg']}dt_location_grid.tsv' 
FIELDS TERMINATED BY '\t' 
LINES TERMINATED BY '\n';
    " );
if ( ! $result ) {

    print date('H:i:s') . ' | FAIL: dt_location_grid.tsv file creation.' . PHP_EOL;
    print_r($con);
    exit();
} else {
    print date('H:i:s') . ' | End dt_location_grid.tsv file creation' . PHP_EOL;
}



$current_files = scandir( $output['lg'] );
foreach ( $current_files as $file ) {
    if ( filesize( $output['lg'] . $file ) < 50 ) {
        unlink($output['lg'] . $file );
        print 'Removed empty file : ' . $file . PHP_EOL;
    }
}

print '*********************************************************************************'. PHP_EOL;
print '        END '. PHP_EOL;
print '*********************************************************************************'. PHP_EOL;