-- --------------------------------------------------------
-- Host:                         172.104.188.7
-- Server version:               10.3.35-MariaDB-1:10.3.35+maria~stretch-log - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             12.21.0.7344
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for meter_reading
CREATE DATABASE IF NOT EXISTS `meter_reading` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `meter_reading`;

-- Dumping structure for table meter_reading.ac_status
CREATE TABLE IF NOT EXISTS `ac_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(100) NOT NULL,
  `date_time` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.ci_sessions
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.live_meter_data
CREATE TABLE IF NOT EXISTS `live_meter_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(30) NOT NULL DEFAULT 'Home',
  `meter_id` varchar(30) NOT NULL,
  `datetime` datetime NOT NULL,
  `vrms_a` double NOT NULL DEFAULT 0,
  `vrms_b` double NOT NULL DEFAULT 0,
  `vrms_c` double NOT NULL,
  `irms_a` double NOT NULL,
  `irms_b` double NOT NULL,
  `irms_c` double NOT NULL,
  `freq` double NOT NULL,
  `pf` double NOT NULL,
  `watt` double NOT NULL,
  `va` double NOT NULL,
  `var` double NOT NULL,
  `wh_del` double NOT NULL,
  `wh_rec` double NOT NULL,
  `wh_net` double NOT NULL,
  `wh_total` double NOT NULL,
  `varh_neg` double NOT NULL,
  `varh_pos` double NOT NULL,
  `varh_net` double NOT NULL,
  `varh_total` double NOT NULL,
  `vah_total` double NOT NULL,
  `max_rec_kw_dmd` double NOT NULL,
  `max_rec_kw_dmd_time` datetime DEFAULT NULL,
  `max_del_kw_dmd` double NOT NULL,
  `max_del_kw_dmd_time` datetime DEFAULT NULL,
  `max_pos_kvar_dmd` double NOT NULL,
  `max_pos_kvar_dmd_time` datetime DEFAULT NULL,
  `max_neg_kvar_dmd` double NOT NULL,
  `max_neg_kvar_dmd_time` datetime DEFAULT NULL,
  `v_ph_angle_a` double NOT NULL COMMENT 'additional for sfelapco inc (JULY 23, 2018)',
  `v_ph_angle_b` double NOT NULL COMMENT 'additional for sfelapco inc (JULY 23, 2018)',
  `v_ph_angle_c` double NOT NULL COMMENT 'additional for sfelapco inc (JULY 23, 2018)',
  `i_ph_angle_a` double NOT NULL COMMENT 'additional for sfelapco inc (JULY 23, 2018)',
  `i_ph_angle_b` double NOT NULL COMMENT 'additional for sfelapco inc (JULY 23, 2018)',
  `i_ph_angle_c` double NOT NULL COMMENT 'additional for sfelapco inc (JULY 23, 2018)',
  `mac_addr` text NOT NULL,
  `soft_rev` text NOT NULL,
  `relay_status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40035 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.load_profile
CREATE TABLE IF NOT EXISTS `load_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` text NOT NULL DEFAULT 'Not Set',
  `meter_id` text NOT NULL,
  `meter_details_id` int(11) DEFAULT NULL,
  `datetime` text DEFAULT NULL,
  `event_id` text NOT NULL,
  `ch_1` double NOT NULL COMMENT '1.5.0_kW',
  `ch_2` double NOT NULL COMMENT '1-1:1.30.2_kWh',
  `ch_3` double NOT NULL COMMENT '1-1:3.30.2_kvarh',
  `ch_4` double NOT NULL COMMENT '2.5.0_kW',
  `ch_5` double NOT NULL COMMENT '1-1:2.30.2_kWh',
  `ch_6` double NOT NULL COMMENT '1-1:4.30.2_kvarh',
  `ch_7` double NOT NULL DEFAULT 0,
  `ch_8` double NOT NULL DEFAULT 0,
  `ch_9` double NOT NULL DEFAULT 0,
  `ch_10` double NOT NULL DEFAULT 0,
  `ch_11` double NOT NULL DEFAULT 0,
  `ch_12` double NOT NULL DEFAULT 0,
  `ch_13` double NOT NULL DEFAULT 0,
  `ch_14` double NOT NULL DEFAULT 0,
  `ch_15` double NOT NULL DEFAULT 0,
  `ch_16` double NOT NULL DEFAULT 0,
  `ch_17` double NOT NULL DEFAULT 0,
  `ch_18` double NOT NULL DEFAULT 0,
  `ch_19` double NOT NULL DEFAULT 0,
  `ch_20` double NOT NULL DEFAULT 0,
  `time_import` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88090 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.load_profile_imported_files
CREATE TABLE IF NOT EXISTS `load_profile_imported_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` text NOT NULL,
  `site_code` text NOT NULL,
  `meter_name` text NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=37484 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.load_profile_new_uploaded_files
CREATE TABLE IF NOT EXISTS `load_profile_new_uploaded_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_id` text NOT NULL,
  `file_directory` text NOT NULL,
  `file_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.load_profile_uploaded_files
CREATE TABLE IF NOT EXISTS `load_profile_uploaded_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_id` text NOT NULL,
  `file_directory` text NOT NULL,
  `file_name` text NOT NULL,
  `file_date` date DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `overwrite_count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_data
CREATE TABLE IF NOT EXISTS `meter_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(30) NOT NULL DEFAULT 'Home',
  `meter_id` varchar(30) NOT NULL,
  `datetime` datetime NOT NULL,
  `vrms_a` double NOT NULL DEFAULT 0,
  `vrms_b` double NOT NULL DEFAULT 0,
  `vrms_c` double NOT NULL,
  `irms_a` double NOT NULL,
  `irms_b` double NOT NULL,
  `irms_c` double NOT NULL,
  `freq` double NOT NULL,
  `pf` double NOT NULL,
  `watt` double NOT NULL,
  `va` double NOT NULL,
  `var` double NOT NULL,
  `wh_del` double NOT NULL,
  `wh_rec` double NOT NULL,
  `wh_net` double NOT NULL,
  `wh_total` double NOT NULL,
  `varh_neg` double NOT NULL,
  `varh_pos` double NOT NULL,
  `varh_net` double NOT NULL,
  `varh_total` double NOT NULL,
  `vah_total` double NOT NULL,
  `max_rec_kw_dmd` double NOT NULL,
  `max_rec_kw_dmd_time` datetime DEFAULT NULL,
  `max_del_kw_dmd` double NOT NULL,
  `max_del_kw_dmd_time` datetime DEFAULT NULL,
  `max_pos_kvar_dmd` double NOT NULL,
  `max_pos_kvar_dmd_time` datetime DEFAULT NULL,
  `max_neg_kvar_dmd` double NOT NULL,
  `max_neg_kvar_dmd_time` datetime DEFAULT NULL,
  `v_ph_angle_a` double NOT NULL,
  `v_ph_angle_b` double NOT NULL,
  `v_ph_angle_c` double NOT NULL,
  `i_ph_angle_a` double NOT NULL,
  `i_ph_angle_b` double NOT NULL,
  `i_ph_angle_c` double NOT NULL,
  `mac_addr` text NOT NULL,
  `soft_rev` text NOT NULL,
  `relay_status` int(11) NOT NULL DEFAULT 0,
  `dt` timestamp NULL DEFAULT current_timestamp(),
  `genset_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`meter_id`,`datetime`,`location`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27924096 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_data_copy
CREATE TABLE IF NOT EXISTS `meter_data_copy` (
  `id` int(11) NOT NULL,
  `location` varchar(30) NOT NULL DEFAULT 'Home',
  `meter_id` varchar(30) NOT NULL,
  `date` date DEFAULT '0000-00-00',
  `datetime` datetime NOT NULL,
  `vrms_a` double DEFAULT 0,
  `vrms_b` double DEFAULT 0,
  `vrms_c` double DEFAULT NULL,
  `irms_a` double DEFAULT NULL,
  `irms_b` double DEFAULT NULL,
  `irms_c` double DEFAULT NULL,
  `freq` double DEFAULT NULL,
  `pf` double DEFAULT NULL,
  `watt` double DEFAULT NULL,
  `va` double DEFAULT NULL,
  `var` double DEFAULT NULL,
  `wh_del` double DEFAULT NULL,
  `wh_rec` double DEFAULT NULL,
  `wh_net` double DEFAULT NULL,
  `wh_total` double DEFAULT NULL,
  `varh_neg` double DEFAULT NULL,
  `varh_pos` double DEFAULT NULL,
  `varh_net` double DEFAULT NULL,
  `varh_total` double DEFAULT NULL,
  `vah_total` double DEFAULT NULL,
  `max_rec_kw_dmd` double DEFAULT NULL,
  `max_rec_kw_dmd_time` datetime DEFAULT NULL,
  `max_del_kw_dmd` double DEFAULT NULL,
  `max_del_kw_dmd_time` datetime DEFAULT NULL,
  `max_pos_kvar_dmd` double DEFAULT NULL,
  `max_pos_kvar_dmd_time` datetime DEFAULT NULL,
  `max_neg_kvar_dmd` double DEFAULT NULL,
  `max_neg_kvar_dmd_time` datetime DEFAULT NULL,
  `v_ph_angle_a` double DEFAULT NULL,
  `v_ph_angle_b` double DEFAULT NULL,
  `v_ph_angle_c` double DEFAULT NULL,
  `i_ph_angle_a` double DEFAULT NULL,
  `i_ph_angle_b` double DEFAULT NULL,
  `i_ph_angle_c` double DEFAULT NULL,
  `mac_addr` varchar(255) DEFAULT NULL,
  `soft_rev` varchar(255) DEFAULT NULL,
  `relay_status` int(11) DEFAULT 0,
  `dt` timestamp NULL DEFAULT current_timestamp(),
  `genset_status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_data_daily
CREATE TABLE IF NOT EXISTS `meter_data_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_id` text NOT NULL,
  `location` text NOT NULL,
  `yearmonth` text NOT NULL,
  `01` double NOT NULL,
  `02` double NOT NULL,
  `03` double NOT NULL,
  `04` double NOT NULL,
  `05` double NOT NULL,
  `06` double NOT NULL,
  `07` double NOT NULL,
  `08` double NOT NULL,
  `09` double NOT NULL,
  `10` double NOT NULL,
  `11` double NOT NULL,
  `12` double NOT NULL,
  `13` double NOT NULL,
  `14` double NOT NULL,
  `15` double NOT NULL,
  `16` double NOT NULL,
  `17` double NOT NULL,
  `18` double NOT NULL,
  `19` double NOT NULL,
  `20` double NOT NULL,
  `21` double NOT NULL,
  `22` double NOT NULL,
  `23` double NOT NULL,
  `24` double NOT NULL,
  `25` double NOT NULL,
  `26` double NOT NULL,
  `27` double NOT NULL,
  `28` double NOT NULL,
  `29` double NOT NULL,
  `30` double NOT NULL,
  `31` double NOT NULL,
  `last_column_used` text NOT NULL,
  `cut_off` double NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_log_update` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1394 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_data_hourly
CREATE TABLE IF NOT EXISTS `meter_data_hourly` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_id` text NOT NULL,
  `location` text NOT NULL,
  `year_month_day` text NOT NULL,
  `00` double NOT NULL COMMENT '12am',
  `01` double NOT NULL COMMENT '1am',
  `02` double NOT NULL COMMENT '2am',
  `03` double NOT NULL COMMENT '3am',
  `04` double NOT NULL COMMENT '4am',
  `05` double NOT NULL COMMENT '5am',
  `06` double NOT NULL COMMENT '6am',
  `07` double NOT NULL COMMENT '7am',
  `08` double NOT NULL COMMENT '8am',
  `09` double NOT NULL COMMENT '9am',
  `10` double NOT NULL COMMENT '10am',
  `11` double NOT NULL COMMENT '11am',
  `12` double NOT NULL COMMENT '12pm',
  `13` double NOT NULL COMMENT '1pm',
  `14` double NOT NULL COMMENT '2pm',
  `15` double NOT NULL COMMENT '3pm',
  `16` double NOT NULL COMMENT '4pm',
  `17` double NOT NULL COMMENT '5pm',
  `18` double NOT NULL COMMENT '6pm',
  `19` double NOT NULL COMMENT '7pm',
  `20` double NOT NULL COMMENT '8pm',
  `21` double NOT NULL COMMENT '9pm',
  `22` double NOT NULL COMMENT '10pm',
  `23` double NOT NULL COMMENT '11pm',
  `last_column_used` text NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31110 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_data_interval_report
CREATE TABLE IF NOT EXISTS `meter_data_interval_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(30) NOT NULL DEFAULT 'Home',
  `meter_id` varchar(30) NOT NULL,
  `datetime_last_log` datetime NOT NULL,
  `datetime_next_log` datetime NOT NULL,
  `log_duration` varchar(200) NOT NULL,
  `log_duration_minute` double NOT NULL,
  `interval_count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_data_sap
CREATE TABLE IF NOT EXISTS `meter_data_sap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_id` text NOT NULL,
  `location` text NOT NULL,
  `yearmonth` text NOT NULL,
  `01` double NOT NULL,
  `02` double NOT NULL,
  `03` double NOT NULL,
  `04` double NOT NULL,
  `05` double NOT NULL,
  `06` double NOT NULL,
  `07` double NOT NULL,
  `08` double NOT NULL,
  `09` double NOT NULL,
  `10` double NOT NULL,
  `11` double NOT NULL,
  `12` double NOT NULL,
  `13` double NOT NULL,
  `14` double NOT NULL,
  `15` double NOT NULL,
  `16` double NOT NULL,
  `17` double NOT NULL,
  `18` double NOT NULL,
  `19` double NOT NULL,
  `20` double NOT NULL,
  `21` double NOT NULL,
  `22` double NOT NULL,
  `23` double NOT NULL,
  `24` double NOT NULL,
  `25` double NOT NULL,
  `26` double NOT NULL,
  `27` double NOT NULL,
  `28` double NOT NULL,
  `29` double NOT NULL,
  `30` double NOT NULL,
  `31` double NOT NULL,
  `last_column_used` text NOT NULL,
  `cut_off` double NOT NULL,
  `cut_off_datetime` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_log_update` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='FOR SAP REPORT';

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_details
CREATE TABLE IF NOT EXISTS `meter_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_name` varchar(70) NOT NULL DEFAULT '',
  `meter_seil` varchar(255) NOT NULL DEFAULT '',
  `meter_name_addressable` int(11) NOT NULL DEFAULT 1,
  `meter_load_profile` varchar(50) NOT NULL DEFAULT 'NO',
  `meter_default_name` text NOT NULL,
  `physical_location` text NOT NULL,
  `meter_site_id` int(11) NOT NULL,
  `rtu_sn_number_id` int(11) NOT NULL,
  `rtu_sn_number` text NOT NULL,
  `meter_model` text NOT NULL,
  `meter_model_id` int(11) NOT NULL,
  `meter_config_file` text NOT NULL,
  `meter_type` text NOT NULL,
  `meter_role` varchar(100) NOT NULL DEFAULT 'Client Meter',
  `created_by` text NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `modified_by` text NOT NULL,
  `date_modified` datetime NOT NULL,
  `added_by` int(11) NOT NULL,
  `soft_rev` text NOT NULL,
  `meter_list_src` text NOT NULL,
  `last_log_update` datetime NOT NULL,
  `last_wh_total` double NOT NULL,
  `company_no` text NOT NULL COMMENT 'COMPANY_CODE',
  `meter_site_name` varchar(200) NOT NULL COMMENT 'Site Code to use on gateway or grouping of Mall or CPG',
  `business_entity` varchar(200) NOT NULL COMMENT 'use to group building pweding sa isang entity iba ibang stablishment like 3ecom 2ecom nasa isang entity sila',
  `building` text NOT NULL COMMENT 'BUILDING use to group meter per building',
  `building_description` text NOT NULL,
  `land` text NOT NULL COMMENT 'LAND',
  `land_description` text NOT NULL,
  `rental_object` text NOT NULL COMMENT 'RENTAL_OBJECT_NO',
  `rental_object_name` text NOT NULL COMMENT 'RENTAL_OBJECT_NAME',
  `usage_type` text NOT NULL COMMENT 'USAGE_TYPE',
  `ro_valid_from` text NOT NULL COMMENT 'RO_VALID_FROM',
  `ro_valid_to` text NOT NULL COMMENT 'RO_VALID_TO',
  `contract_number` text NOT NULL COMMENT 'CONTRACT_NUMBER',
  `customer_name` text NOT NULL COMMENT 'CONTRACT_NAME/Tenant Name',
  `meter_characteristic` varchar(100) NOT NULL DEFAULT 'NORMALPOWER' COMMENT 'METER_CHARACTERISTIC',
  `measuring_point` varchar(100) NOT NULL COMMENT 'MEASPONT',
  `measuring_point_description` text NOT NULL COMMENT 'METER_DESCRIPTION/Meter Serial Number',
  `measurement_sequence` text NOT NULL COMMENT 'MEASUREMENT_SEQUENCE',
  `measurement_separator` text NOT NULL COMMENT 'MEASUREMENT_SEPARATOR',
  `meter_multiplier` double NOT NULL DEFAULT 1 COMMENT 'MEASUREMENT_MULTIPLIER',
  `meter_reading_date` text DEFAULT NULL COMMENT 'METER_READING_DATE',
  `meter_reading` double DEFAULT NULL COMMENT 'METER_READING',
  `meter_status` text NOT NULL COMMENT 'METER_STATUS',
  `participation_group` text NOT NULL COMMENT 'PARTICIPATION GROUP',
  `creation_date` text NOT NULL COMMENT 'CREATION DATE',
  `creation_by` text NOT NULL COMMENT 'CREATION_TIME',
  `last_change_on` text NOT NULL COMMENT 'LAST_CHANGE_ON',
  `last_change_by` text NOT NULL COMMENT 'LAST_CHANGE_BY',
  PRIMARY KEY (`id`),
  KEY `meter_name_characteristic_site` (`id`,`meter_name`,`meter_characteristic`,`meter_site_name`),
  KEY `meter_name_characteristic_site_measuring_point` (`meter_name`,`meter_characteristic`,`meter_site_name`,`measuring_point`)
) ENGINE=InnoDB AUTO_INCREMENT=198381 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_import_logs
CREATE TABLE IF NOT EXISTS `meter_import_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime_import` datetime DEFAULT NULL COMMENT 'date time',
  `file_name` text DEFAULT NULL COMMENT 'file name',
  `import_path` text DEFAULT NULL COMMENT 'Path Directory',
  `moved_path` text DEFAULT NULL COMMENT 'Moved to Directory',
  `moved_file_name` text DEFAULT NULL,
  `backup_file` blob DEFAULT NULL COMMENT 'FILE',
  `datetime` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1268 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for view meter_reading.meter_info_view
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `meter_info_view` (
	`location` VARCHAR(1) NOT NULL COMMENT 'Site Code to use on gateway or grouping of Mall or CPG' COLLATE 'latin1_swedish_ci',
	`meter_id` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`meter_load_profile` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`customer_name` TEXT NOT NULL COMMENT 'CONTRACT_NAME/Tenant Name' COLLATE 'latin1_swedish_ci'
);

-- Dumping structure for table meter_reading.meter_model_config
CREATE TABLE IF NOT EXISTS `meter_model_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_model` text NOT NULL,
  `config_file` text NOT NULL,
  `created_by` varchar(300) NOT NULL,
  `date_created` varchar(200) NOT NULL,
  `modified_by` varchar(200) NOT NULL,
  `date_modified` timestamp NULL DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_rtu
CREATE TABLE IF NOT EXISTS `meter_rtu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rtu_sn_number` text NOT NULL,
  `rtu_physical_location` text NOT NULL,
  `connection_type` text NOT NULL,
  `phone_no_or_ip_address` text NOT NULL,
  `ip_netmask` text NOT NULL,
  `ip_gateway` text NOT NULL,
  `mac_addr` text NOT NULL,
  `rtu_server_ip` text NOT NULL,
  `gateway_description` text NOT NULL,
  `update_rtu` int(11) NOT NULL DEFAULT 0,
  `update_rtu_location` int(11) NOT NULL,
  `update_rtu_ssh` int(11) NOT NULL,
  `update_rtu_force_lp` int(11) NOT NULL,
  `rtu_site_id` int(11) NOT NULL,
  `idf_number` text DEFAULT NULL,
  `switch_name` text DEFAULT NULL,
  `idf_port` text DEFAULT NULL,
  `rtu_site_name` varchar(200) NOT NULL,
  `created_by` varchar(300) NOT NULL,
  `date_created` varchar(200) NOT NULL,
  `modified_by` varchar(200) NOT NULL,
  `date_modified` timestamp NULL DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `last_log_update` datetime NOT NULL,
  `soft_rev` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.meter_site
CREATE TABLE IF NOT EXISTS `meter_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_code` text NOT NULL COMMENT 'SITE CODE TO Used on Gateway',
  `company_no` int(11) NOT NULL COMMENT 'COMPANY_CODE',
  `site_name` text NOT NULL COMMENT 'BUSINESS_ENTITY/Location/SITE ed SM SA LAZARO',
  `site_cut_off` text NOT NULL COMMENT 'METER_READING_CUTOFF',
  `device_ip_range` text DEFAULT NULL,
  `ip_netmask` text DEFAULT NULL,
  `ip_gateway` text DEFAULT NULL,
  `access_list_src` text NOT NULL,
  `created_by` varchar(200) NOT NULL,
  `date_created` varchar(100) NOT NULL,
  `modified_by` varchar(200) NOT NULL,
  `modified_by_id` int(11) NOT NULL,
  `date_modified` varchar(100) NOT NULL,
  `last_log_update` text NOT NULL,
  `added_by` int(11) NOT NULL,
  `service_charge_key` text DEFAULT NULL COMMENT 'SERVICE_CHARGE_KEY',
  `participation_group` text DEFAULT NULL COMMENT 'PARTICIPATION GROUP',
  `settlement_unit` text DEFAULT NULL COMMENT 'SETTLEMENT_UNIT',
  `settlement_variant_text` text DEFAULT NULL COMMENT 'SETTLEMENT VARIANT TEXT',
  `settlement_valid_from` text DEFAULT NULL COMMENT 'SETTLEMENT VALID FROM',
  `settlement_valid_to` text DEFAULT NULL COMMENT 'SETTLEMENT VALID TO',
  `created_on` text DEFAULT NULL COMMENT 'CREATED_ON',
  `created_at` text DEFAULT NULL COMMENT 'CREATED_AT',
  `last_edited_on` text DEFAULT NULL COMMENT 'LAST_EDITED_ON',
  `last_edited_at` text DEFAULT NULL COMMENT 'LAST_EDITED_AT',
  `business_entity` text NOT NULL COMMENT 'BUSINESS_ENTITY GROUP kng wala same lang ng SITE CODE. Gagamitin sa tulad ng SMPH business entity na madaming sakop like 3ECOM,2ecom etc.',
  `building_type` text NOT NULL COMMENT 'CPG/MALL',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for view meter_reading.meter_site_user
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `meter_site_user` (
	`id` INT(11) NULL,
	`site_code` TEXT NOT NULL COMMENT 'SITE CODE TO Used on Gateway' COLLATE 'latin1_swedish_ci',
	`company_no` INT(11) NOT NULL COMMENT 'COMPANY_CODE',
	`settlement_valid_from` TEXT NULL COMMENT 'SETTLEMENT VALID FROM' COLLATE 'latin1_swedish_ci',
	`settlement_valid_to` TEXT NULL COMMENT 'SETTLEMENT VALID TO' COLLATE 'latin1_swedish_ci',
	`site_name` TEXT NOT NULL COMMENT 'BUSINESS_ENTITY/Location/SITE ed SM SA LAZARO' COLLATE 'latin1_swedish_ci',
	`site_cut_off` TEXT NOT NULL COMMENT 'METER_READING_CUTOFF' COLLATE 'latin1_swedish_ci',
	`created_by` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`date_created` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`modified_by` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`date_modified` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`last_log_update` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`added_by` INT(11) NOT NULL,
	`user_id` TEXT NULL COMMENT 'USER_ID from SAP' COLLATE 'latin1_swedish_ci',
	`business_entity` TEXT NOT NULL COMMENT 'BUSINESS_ENTITY GROUP kng wala same lang ng SITE CODE. Gagamitin sa tulad ng SMPH business entity na madaming sakop like 3ECOM,2ecom etc.' COLLATE 'latin1_swedish_ci',
	`access_list_src` TEXT NULL COLLATE 'latin1_swedish_ci',
	`user_site_access_expiration` DATE NULL COMMENT 'BUSINESS_ENTITY_VALID_TO',
	`meter_site_id` INT(11) NOT NULL,
	`building_type` TEXT NOT NULL COMMENT 'CPG/MALL' COLLATE 'latin1_swedish_ci'
);

-- Dumping structure for table meter_reading.power_ledger_access_token
CREATE TABLE IF NOT EXISTS `power_ledger_access_token` (
  `token_type` varchar(255) DEFAULT NULL,
  `issued_at` bigint(20) DEFAULT NULL,
  `issued_at_datetime` varchar(50) DEFAULT NULL COMMENT 'Based on Server Date and Time',
  `expires_in` int(11) DEFAULT NULL,
  `access_token` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.power_ledger_logs
CREATE TABLE IF NOT EXISTS `power_ledger_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` timestamp NULL DEFAULT current_timestamp() COMMENT 'Current Server Date and Time',
  `meterId` varchar(100) DEFAULT NULL,
  `wh_rec` double DEFAULT NULL,
  `wh_del` double DEFAULT NULL,
  `multiplier` double DEFAULT NULL,
  `import` double DEFAULT NULL COMMENT 'kwh_total as import',
  `export` double DEFAULT NULL COMMENT 'kvarh_total as export',
  `timeStamp` varchar(100) DEFAULT NULL COMMENT 'Time Stamp to Post is baed from Meter reading logs',
  `isEstimate` varchar(50) DEFAULT NULL,
  `energyUnit` varchar(50) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `result` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=54069 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.power_ledger_logs_copy
CREATE TABLE IF NOT EXISTS `power_ledger_logs_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` timestamp NULL DEFAULT current_timestamp() COMMENT 'Current Server Date and Time',
  `meterId` varchar(100) DEFAULT NULL,
  `import` double DEFAULT NULL COMMENT 'kwh_total as import',
  `export` double DEFAULT NULL COMMENT 'kvarh_total as export',
  `timeStamp` varchar(100) DEFAULT NULL COMMENT 'Time Stamp to Post is baed from Meter reading logs',
  `isEstimate` varchar(50) DEFAULT NULL,
  `energyUnit` varchar(50) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=24304 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.power_ledger_logs_copy_12-06-2023
CREATE TABLE IF NOT EXISTS `power_ledger_logs_copy_12-06-2023` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` timestamp NULL DEFAULT current_timestamp() COMMENT 'Current Server Date and Time',
  `meterId` varchar(100) DEFAULT NULL,
  `wh_rec` double DEFAULT NULL,
  `wh_del` double DEFAULT NULL,
  `multiplier` double DEFAULT NULL,
  `import` double DEFAULT NULL COMMENT 'kwh_total as import',
  `export` double DEFAULT NULL COMMENT 'kvarh_total as export',
  `timeStamp` varchar(100) DEFAULT NULL COMMENT 'Time Stamp to Post is baed from Meter reading logs',
  `isEstimate` varchar(50) DEFAULT NULL,
  `energyUnit` varchar(50) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `result` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13190 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.power_ledger_logs_copy_2
CREATE TABLE IF NOT EXISTS `power_ledger_logs_copy_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` timestamp NULL DEFAULT current_timestamp() COMMENT 'Current Server Date and Time',
  `meterId` varchar(100) DEFAULT NULL,
  `wh_rec` double DEFAULT NULL,
  `wh_del` double DEFAULT NULL,
  `multiplier` double DEFAULT NULL,
  `import` double DEFAULT NULL COMMENT 'kwh_total as import',
  `export` double DEFAULT NULL COMMENT 'kvarh_total as export',
  `timeStamp` varchar(100) DEFAULT NULL COMMENT 'Time Stamp to Post is baed from Meter reading logs',
  `isEstimate` varchar(50) DEFAULT NULL,
  `energyUnit` varchar(50) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `result` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=487 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.power_ledger_logs_re_upload
CREATE TABLE IF NOT EXISTS `power_ledger_logs_re_upload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` timestamp NULL DEFAULT current_timestamp() COMMENT 'Current Server Date and Time',
  `meterId` varchar(100) DEFAULT NULL,
  `import` double DEFAULT NULL COMMENT 'kwh_total as import',
  `export` double DEFAULT NULL COMMENT 'kvarh_total as export',
  `timeStamp` varchar(100) DEFAULT NULL COMMENT 'Time Stamp to Post is baed from Meter reading logs',
  `isEstimate` varchar(50) DEFAULT NULL,
  `energyUnit` varchar(50) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31719 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.user_access_group
CREATE TABLE IF NOT EXISTS `user_access_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` text NOT NULL COMMENT 'USER_ID from SAP',
  `user_name` text NOT NULL COMMENT 'USER_NAME',
  `user_expiration` date NOT NULL COMMENT 'USER_ID_VALID_TO',
  `meter_site_id` int(11) NOT NULL,
  `company_no` int(11) NOT NULL COMMENT 'COMPANY_NO/COMPANY',
  `business_entity` text NOT NULL COMMENT 'BUSINESS_ENTITY',
  `user_site_access_expiration` date NOT NULL COMMENT 'BUSINESS_ENTITY_VALID_TO',
  `user_type` text NOT NULL COMMENT 'FUNCTION',
  `position_expiration` date NOT NULL COMMENT 'FUNCTION_VALID_TO',
  `created_by` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_by` int(11) NOT NULL,
  `date_modified` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `added_by` int(11) NOT NULL,
  `access_list_src` text NOT NULL,
  `site_code` text NOT NULL COMMENT 'Location Access',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1793 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table meter_reading.user_client_meter_access
CREATE TABLE IF NOT EXISTS `user_client_meter_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `meter_name` varchar(200) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for view meter_reading.user_meter_group_access
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `user_meter_group_access` (
	`location` VARCHAR(1) NOT NULL COMMENT 'Site Code to use on gateway or grouping of Mall or CPG' COLLATE 'latin1_swedish_ci',
	`meter_id` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`meter_load_profile` VARCHAR(1) NOT NULL COLLATE 'latin1_swedish_ci',
	`customer_name` TEXT NOT NULL COMMENT 'CONTRACT_NAME/Tenant Name' COLLATE 'latin1_swedish_ci',
	`user_id` TEXT NOT NULL COMMENT 'USER_ID from SAP' COLLATE 'latin1_swedish_ci',
	`group_to_access` TEXT NOT NULL COMMENT 'BUSINESS_ENTITY' COLLATE 'latin1_swedish_ci'
);

-- Dumping structure for table meter_reading.user_tb
CREATE TABLE IF NOT EXISTS `user_tb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id_sap` text NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_real_name` text NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_type` varchar(100) NOT NULL,
  `user_expiration` date NOT NULL,
  `default_location` text NOT NULL COMMENT 'Use for Load Profile Login',
  `created_by` varchar(200) NOT NULL,
  `date_created` varchar(200) NOT NULL,
  `modified_by` varchar(200) NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_list_src` varchar(100) NOT NULL DEFAULT 'AMR',
  `user_access` varchar(100) NOT NULL DEFAULT 'Selected' COMMENT 'All/Selected if all user will have an access to all site, if Selected only assigned site to user',
  `active_session_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `active_session_activity` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=967 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for trigger meter_reading.after_update_meter_model_config
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `after_update_meter_model_config` AFTER UPDATE ON `meter_model_config`
 FOR EACH ROW update meter_details
       set meter_model = NEW.meter_model, meter_config_file = NEW.config_file
       where meter_model_id = NEW.id//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `meter_info_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `meter_info_view` AS select distinct `meter_details`.`meter_site_name` AS `location`,`meter_details`.`meter_name` AS `meter_id`,`meter_details`.`meter_load_profile` AS `meter_load_profile`,`meter_details`.`customer_name` AS `customer_name` from `meter_details`
;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `meter_site_user`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `meter_site_user` AS select `user_access_group`.`id` AS `id`,`meter_site`.`site_code` AS `site_code`,`meter_site`.`company_no` AS `company_no`,`meter_site`.`settlement_valid_from` AS `settlement_valid_from`,`meter_site`.`settlement_valid_to` AS `settlement_valid_to`,`meter_site`.`site_name` AS `site_name`,`meter_site`.`site_cut_off` AS `site_cut_off`,`meter_site`.`created_by` AS `created_by`,`meter_site`.`date_created` AS `date_created`,`meter_site`.`modified_by` AS `modified_by`,`meter_site`.`date_modified` AS `date_modified`,`meter_site`.`last_log_update` AS `last_log_update`,`meter_site`.`added_by` AS `added_by`,`user_access_group`.`user_id` AS `user_id`,`meter_site`.`business_entity` AS `business_entity`,`user_access_group`.`access_list_src` AS `access_list_src`,`user_access_group`.`user_site_access_expiration` AS `user_site_access_expiration`,`meter_site`.`id` AS `meter_site_id`,`meter_site`.`building_type` AS `building_type` from (`meter_site` left join `user_access_group` on(`meter_site`.`site_code` = `user_access_group`.`site_code`))
;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `user_meter_group_access`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `user_meter_group_access` AS select `meter_details`.`meter_site_name` AS `location`,`meter_details`.`meter_name` AS `meter_id`,`meter_details`.`meter_load_profile` AS `meter_load_profile`,`meter_details`.`customer_name` AS `customer_name`,`user_access_group`.`user_id` AS `user_id`,`user_access_group`.`business_entity` AS `group_to_access` from (`user_access_group` join `meter_details` on(convert(`user_access_group`.`business_entity` using utf8) = convert(`meter_details`.`meter_site_name` using utf8)))
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
