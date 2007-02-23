<?php


/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Update from 0.68.1 to 0.7
function update0681to07() {
	global $DB, $CFG_GLPI,$LANG,$LINK_ID_TABLE;
	// Improve user table :
	if (!isIndex("glpi_users", "firstname")) {
		$query = "ALTER TABLE `glpi_users` ADD INDEX ( `firstname` )";
		$DB->query($query) or die("0.7 alter users add indesx on firstname " . $LANG["update"][90] . $DB->error());
	}
	if (!isIndex("glpi_users", "realname")) {
		$query = "ALTER TABLE `glpi_users` ADD INDEX ( `realname` )";
		$DB->query($query) or die("0.7 alter users add indesx on realname " . $LANG["update"][90] . $DB->error());
	}
	// Decimal problem
	if (FieldExists("glpi_infocoms", "value")) {
		$query = "ALTER TABLE `glpi_infocoms` CHANGE `value` `value` DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter value in glpi_infocoms " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_infocoms", "warranty_value")) {
		$query = "ALTER TABLE `glpi_infocoms` CHANGE warranty_value warranty_value DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter warranty_value in glpi_infocoms " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_tracking", "cost_time")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE cost_time cost_time DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter cost_time in glpi_tracking " . $LANG["update"][90] . $DB->error());
	}	
	if (FieldExists("glpi_tracking", "cost_fixed")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE cost_fixed cost_fixed DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter cost_fixed in glpi_tracking " . $LANG["update"][90] . $DB->error());
	}	
	if (FieldExists("glpi_tracking", "cost_material")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE cost_material cost_material DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter cost_material in glpi_tracking " . $LANG["update"][90] . $DB->error());
	}	
	if (!FieldExists("glpi_config", "decimal_number")) {
		$query = "ALTER TABLE `glpi_config` ADD `decimal_number` INT DEFAULT '2';";
		$DB->query($query) or die("0.7 add decimal_number in glpi_config " . $LANG["update"][90] . $DB->error());
	}
	$CFG_GLPI["decimal_number"]=2;

	if (!FieldExists("glpi_config", "cas_logout")) {
		$query = "ALTER TABLE `glpi_config` ADD `cas_logout` VARCHAR( 255 ) NULL AFTER `cas_uri`;";
		$DB->query($query) or die("0.7 add cas_logout in glpi_config " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_computer_device", "specificity")) {
		$query = "ALTER TABLE `glpi_computer_device` ADD INDEX ( `specificity` )";
		$DB->query($query) or die("0.7 add index specificity in glpi_computer_device " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_docs", "comments")){
		$query = "ALTER TABLE `glpi_docs` CHANGE `comment` `comments` TEXT DEFAULT NULL ";
		$DB->query($query) or die("0.7 alter docs.comment to be comments" . $LANG["update"][90] . $DB->error());
		
	}
	// Update polish langage file
	$query = "UPDATE glpi_users SET language='pl_PL' WHERE language='po_PO'";
	$DB->query($query) or die("0.7 update polish lang file " . $LANG["update"][90] . $DB->error());

	// Add show_group_hardware
	if (!FieldExists("glpi_profiles", "show_group_hardware")){
		$query = "ALTER TABLE `glpi_profiles` ADD `show_group_hardware` CHAR( 1 ) NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter glpi_profiles add show_group_hardware" . $LANG["update"][90] . $DB->error());
		$query="UPDATE glpi_profiles SET `show_group_hardware`=`show_group_ticket`";
		$DB->query($query) or die("0.7 alter glpi_profiles add show_group_hardware" . $LANG["update"][90] . $DB->error());
	}
	

	// Clean doc association
	$doc_links = array (COMPUTER_TYPE, NETWORKING_TYPE, PRINTER_TYPE, MONITOR_TYPE , PERIPHERAL_TYPE, SOFTWARE_TYPE, PHONE_TYPE, ENTERPRISE_TYPE , CARTRIDGE_TYPE, CONSUMABLE_TYPE, CONTRACT_TYPE);

	foreach ($doc_links as $type) {
		$table=$LINK_ID_TABLE[$type];
		$query = "SELECT glpi_doc_device.ID as linkID, $table.*
									FROM glpi_doc_device 
									LEFT JOIN $table ON (glpi_doc_device.FK_device = $table.ID AND glpi_doc_device.device_type='$type') WHERE glpi_doc_device.is_template='1'";
		$result = $DB->query($query) or die("0.7 search wrong data link doc device $table " . $LANG["update"][90] . $DB->error());
		if ($DB->numrows($result)) {
			while ($data = $DB->fetch_array($result)) {
				if (!isset ($data['is_template']) || $data['is_template'] == 0) {
					$query2 = "UPDATE glpi_doc_device SET is_template='0' WHERE ID='" . $data['linkID'] . "'";
					$DB->query($query) or die("0.7 update link doc device for $table " . $LANG["update"][90] . $DB->error());
				}
			}
		}

	}

	//// ENTITY MANAGEMENT

	if (!TableExists("glpi_entities")) {
		$query = "CREATE TABLE `glpi_entities` (
								`ID` int(11) NOT NULL auto_increment,
								`name` varchar(255) NOT NULL,
								`parentID` int(11) NOT NULL default '0',
								`completename` text NOT NULL,
								`comments` text,
								`level` int(11) default NULL,
								PRIMARY KEY  (`ID`),
								UNIQUE KEY `name` (`name`,`parentID`),
								KEY `parentID` (`parentID`)
								) ENGINE=MyISAM;";
		$DB->query($query) or die("0.7 create glpi_entities " . $LANG["update"][90] . $DB->error());
		// TODO : ADD other fields
	}

	if (!TableExists("glpi_entities_data")) {
		$query = "CREATE TABLE `glpi_entities_data` (
				`ID` int(11) NOT NULL auto_increment,
				`FK_entities` int(11) NOT NULL default '0',
				`address` text,
				`postcode` varchar(255) default NULL,
				`town` varchar(255) default NULL,
				`state` varchar(255) default NULL,
				`country` varchar(255) default NULL,
				`website` varchar(200) default NULL,
				`phonenumber` varchar(200) default NULL,
				`fax` varchar(255) default NULL,
				`email` varchar(255) default NULL,
				`notes` longtext,
				PRIMARY KEY  (`ID`),
				UNIQUE KEY `FK_entities` (`FK_entities`)
				) ENGINE=MyISAM ;";

		$DB->query($query) or die("0.7 create glpi_entities_data " . $LANG["update"][90] . $DB->error());
	}	

	if (!FieldExists("glpi_users_profiles", "FK_entities")) {
		// Clean Datas
		$query="DELETE FROM glpi_users_profiles WHERE FK_users='0'";
		$DB->query($query) or die("0.7 clean datas of glpi_users_profiles " . $LANG["update"][90] . $DB->error());
		
		$query = " ALTER TABLE `glpi_users_profiles` ADD `FK_entities` INT NOT NULL DEFAULT '0',
											ADD `recursive` SMALLINT NOT NULL DEFAULT '1',
											ADD `active` SMALLINT NOT NULL DEFAULT '1',
											ADD `dynamic` SMALLINT NOT NULL DEFAULT '0' ";
		$DB->query($query) or die("0.7 alter glpi_users_profiles " . $LANG["update"][90] . $DB->error());

		// Manage inactive users
		$query = "SELECT ID FROM glpi_users WHERE active='0'";
		$result = $DB->query($query);
		if ($DB->numrows($result)) {
			while ($data = $DB->fetch_array($result)) {
				$query2 = "UPDATE glpi_users_profiles SET active = '0' WHERE FK_users='" . $data['ID'] . "'";
				$DB->query($query2);
			}
		}

		$query = "ALTER TABLE `glpi_users` DROP `active` ";
		$DB->query($query) or die("0.7 drop active from glpi_users " . $LANG["update"][90] . $DB->error());

		$query = "DELETE FROM glpi_display WHERE type='" . USER_TYPE . "' AND num='8';";
		$DB->query($query) or die("0.7 delete active field items for user search " . $LANG["update"][90] . $DB->error());
	}

	// Add entity tags to tables
	$tables = array (
		"glpi_cartridges_type",
		"glpi_computers",
		"glpi_consumables_type",
		"glpi_contacts",
		"glpi_contracts",
		"glpi_docs",
		"glpi_dropdown_locations",
		"glpi_dropdown_netpoint",
		"glpi_enterprises",
		"glpi_groups",
		"glpi_monitors",
		"glpi_networking",
		"glpi_peripherals",
		"glpi_phones",
		"glpi_printers",
		"glpi_reminder",
		"glpi_software",
		"glpi_tracking"
	);
	// "glpi_kbitems","glpi_dropdown_kbcategories", -> easier to manage
	// "glpi_followups" -> always link to tracking ?
	// "glpi_licenses" -> always link to software ? 
	// "glpi_infocoms" -> always link to item ? PB on reports stats ?
	// "glpi_links" -> global items easier to manage
	// "glpi_reservation_item", "glpi_state_item" -> always link to item ? but info maybe needed
	foreach ($tables as $tbl) {
		if (!FieldExists($tbl, "FK_entities")) {
			$query = "ALTER TABLE `" . $tbl . "` ADD `FK_entities` INT NOT NULL DEFAULT '0' AFTER `ID`";
			$DB->query($query) or die("0.7 add FK_entities in $tbl " . $LANG["update"][90] . $DB->error());
		}

		if (!isIndex($tbl, "FK_entities")) {
			$query = "ALTER TABLE `" . $tbl . "` ADD INDEX (`FK_entities`)";
			$DB->query($query) or die("0.7 add index FK_entities in $tbl " . $LANG["update"][90] . $DB->error());
		}
	}

	// Regenerate Indexes :
	$tables = array (
		"glpi_dropdown_locations"
	);
	foreach ($tables as $tbl) {
		if (isIndex($tbl, "name")) {
			$query = "ALTER TABLE `$tbl` DROP INDEX `name`;";
			$DB->query($query) or die("0.7 drop index name in $tbl " . $LANG["update"][90] . $DB->error());
		}
		if (isIndex($tbl, "parentID_2")) {
			$query = "ALTER TABLE `$tbl` DROP INDEX `parentID_2`;";
			$DB->query($query) or die("0.7 drop index name in $tbl " . $LANG["update"][90] . $DB->error());
		}
		$query = "ALTER TABLE `$tbl` ADD UNIQUE(`name`,`parentID`,`FK_entities`);";
		$DB->query($query) or die("0.7 add index name in $tbl " . $LANG["update"][90] . $DB->error());

	}

	if (isIndex("glpi_users_profiles", "FK_users_profiles")) {
		$query = "ALTER TABLE `glpi_users_profiles` DROP INDEX `FK_users_profiles`;";
		$DB->query($query) or die("0.7 drop index FK_users_profiles in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_users_profiles", "active")) {
		$query = "ALTER TABLE `glpi_users_profiles` ADD INDEX (`active`);";
		$DB->query($query) or die("0.7 add index active in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}
	if (!isIndex("glpi_users_profiles", "FK_entities")) {
		$query = "ALTER TABLE `glpi_users_profiles` ADD INDEX (`FK_entities`);";
		$DB->query($query) or die("0.7 add index FK_entities in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}

	//// MULTIAUTH MANAGEMENT

	if (!TableExists("glpi_auth_ldap")) {
		$query = "CREATE TABLE `glpi_auth_ldap` (
			 `ID` int(11) NOT NULL auto_increment,
			 `name` varchar(255) NOT NULL,
			 `ldap_host` varchar(255) default NULL,
			`ldap_basedn` varchar(255) default NULL,
			`ldap_rootdn` varchar(255) default NULL,
			`ldap_pass` varchar(255) default NULL,
			`ldap_port` varchar(255) NOT NULL default '389',
			`ldap_condition` varchar(255) default NULL,
			`ldap_login` varchar(255) NOT NULL default 'uid',	
			`ldap_use_tls` varchar(255) NOT NULL default '0',
			`ldap_field_group` varchar(255) default NULL,
			`ldap_group_condition` varchar(255) default NULL,
			`ldap_search_for_groups` int NOT NULL default '0',
			`ldap_field_group_member` varchar(255) default NULL,
			`ldap_field_email` varchar(255) default NULL,
			`ldap_field_location` varchar(255) default NULL,
			`ldap_field_realname` varchar(255) default NULL,
			`ldap_field_firstname` varchar(255) default NULL,
			`ldap_field_phone` varchar(255) default NULL,
			`ldap_field_phone2` varchar(255) default NULL,
			`ldap_field_mobile` varchar(255) default NULL,
			`ldap_field_comments` TEXT default NULL,		
			PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM;";
		$DB->query($query) or die("0.7 create glpi_auth_ldap " . $LANG["update"][90] . $DB->error());
		// TODO : ADD other fields

		$query = "select * from glpi_config WHERE ID=1";
		$result = $DB->query($query);
		$config = $DB->fetch_array($result);

		if (!empty ($config["ldap_host"])) {

			//Transfer ldap informations into the new table

			$query = "INSERT INTO `glpi_auth_ldap` VALUES 
			(NULL, '" . $config["ldap_host"] . "', '" . $config["ldap_host"] . "', '" . $config["ldap_basedn"] . "', '" . $config["ldap_rootdn"] . "', '" . $config["ldap_pass"] . "', " . $config["ldap_port"] . ", '" . $config["ldap_condition"] . "', '" . $config["ldap_login"] . "', '" . $config["ldap_use_tls"] . "', '" . $config["ldap_field_group"] . "',
			'" . $config["ldap_condition"] . "', " . $config["ldap_search_for_groups"] . ", '" . $config["ldap_field_group_member"] . "',
			'" . $config["ldap_field_email"] . "', '" . $config["ldap_field_location"] . "', '" . $config["ldap_field_realname"] . "', '" . $config["ldap_field_firstname"] . "',
			'" . $config["ldap_field_phone"] . "', '" . $config["ldap_field_phone2"] . "', '" . $config["ldap_field_mobile"] . "',NULL);";
			$DB->query($query) or die("0.7 transfert of ldap parameters into glpi_auth_ldap " . $LANG["update"][90] . $DB->error());
		}

		$query = "ALTER TABLE `glpi_config`
			DROP `ldap_field_email`,
			DROP `ldap_port`,
			DROP `ldap_host`,
			DROP `ldap_basedn`,
			DROP `ldap_rootdn`,
			DROP `ldap_pass`,
			DROP `ldap_field_location`,
			DROP `ldap_field_realname`,
			DROP `ldap_field_firstname`,
			DROP `ldap_field_phone`,
			DROP `ldap_field_phone2`,
			DROP `ldap_field_mobile`,
			DROP `ldap_condition`,
			DROP `ldap_login`,
			DROP `ldap_use_tls`,
			DROP `ldap_field_group`,
			DROP `ldap_group_condition`,
			DROP `ldap_search_for_groups`,
			DROP `ldap_field_group_member`;";
		$DB->query($query) or die("0.7 drop ldap fields from glpi_config " . $LANG["update"][90] . $DB->error());


	}
	if (!FieldExists("glpi_users", "id_auth")) {
		$query = "ALTER TABLE glpi_users ADD `id_auth` INT NOT NULL DEFAULT '-1',
				ADD `auth_method` INT NOT NULL DEFAULT '-1',
				ADD `last_login` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				ADD `date_mod` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
		$DB->query($query) or die("0.7 add auth_method & id_method in glpi_users " . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_auth_mail")) {
		$query = "CREATE TABLE `glpi_auth_mail` (
				`ID` int(11) NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`imap_auth_server` varchar(200) default NULL,
				`imap_host` varchar(200) default NULL,
				PRIMARY KEY  (`ID`)
				) ENGINE=MyISAM ;";

		$DB->query($query) or die("0.7 create glpi_auth_mail " . $LANG["update"][90] . $DB->error());
		// TODO : ADD other fields

		$query = "select * from glpi_config WHERE ID=1";
		$result = $DB->query($query);
		$config = $DB->fetch_array($result);

		if (!empty ($config["imap_host"])) {

			//Transfer ldap informations into the new table
			$query = "INSERT INTO `glpi_auth_mail` VALUES 
				(NULL, '" . $config["imap_host"] . "', '" . $config["imap_auth_server"] . "', '" . $config["imap_host"] . "');";
			$DB->query($query) or die("0.7 transfert of mail parameters into glpi_auth_mail " . $LANG["update"][90] . $DB->error());

		}

		$query = "ALTER TABLE `glpi_config`
		  		DROP `imap_auth_server`,
		  		DROP `imap_host`";
		$DB->query($query) or die("0.7 drop mail fields from glpi_config " . $LANG["update"][90] . $DB->error());

	}

	// Clean state_item -> add a field from tables
	if (TableExists("glpi_state_item")) {
		$state_type=array(SOFTWARE_TYPE,COMPUTER_TYPE,PRINTER_TYPE,MONITOR_TYPE,PERIPHERAL_TYPE,NETWORKING_TYPE,PHONE_TYPE);
		foreach ($state_type as $type){
			$table=$LINK_ID_TABLE[$type];
			if (!FieldExists($table, "state")) {
				$query ="ALTER TABLE `$table` ADD `state` INT NOT NULL DEFAULT '0';";
				$DB->query($query) or die("0.7 add state field to $table " . $LANG["update"][90] . $DB->error());
				$query2="SELECT * FROM glpi_state_item WHERE device_type='$type'";
				$result=$DB->query($query2);
				if ($DB->numrows($result)){
					while ($data=$DB->fetch_array($result)){
						$query3="UPDATE $table SET state='".$data["state"]."' WHERE ID ='".$data["id_device"]."'";
						$DB->query($query3) or die("0.7 update state field value to $table " . $LANG["update"][90] . $DB->error());
					}
				}
			}
		}
		$query="DROP TABLE `glpi_state_item` ";
		$DB->query($query) or die("0.7 drop table state_item " . $LANG["update"][90] . $DB->error());
		$query="INSERT INTO `glpi_display` (`type`, `num`, `rank`, `FK_users`) VALUES (22, 31, 1, 0);";
		$DB->query($query) or die("0.7 add default search for states " . $LANG["update"][90] . $DB->error());
		// Add for reservation
		$query="INSERT INTO `glpi_display` (`type`, `num`, `rank`, `FK_users`) VALUES ( 29, 4, 1, 0);";
		$DB->query($query) or die("0.7 add defaul search for reservation " . $LANG["update"][90] . $DB->error());
		$query="INSERT INTO `glpi_display` (`type`, `num`, `rank`, `FK_users`) VALUES ( 29, 3, 2, 0);";
		$DB->query($query) or die("0.7 add defaul search for reservation " . $LANG["update"][90] . $DB->error());
	}


	// Add ticket_tco for hardwares
	$tco_tbl=array(COMPUTER_TYPE, NETWORKING_TYPE, PRINTER_TYPE, MONITOR_TYPE, PERIPHERAL_TYPE, SOFTWARE_TYPE, PHONE_TYPE);
	include (GLPI_ROOT . "/inc/tracking.function.php");

	foreach ($tco_tbl as $type) {
		$table=$LINK_ID_TABLE[$type];
		if (!FieldExists($table, "ticket_tco")){
			$query = "ALTER TABLE `$table` ADD `ticket_tco` DECIMAL( 20, 4 ) DEFAULT '0.0000';";
			$DB->query($query) or die("0.7 alter $table add ticket_tco" . $LANG["update"][90] . $DB->error());
			// Update values
			$query="SELECT DISTINCT device_type, computer 
				FROM glpi_tracking 
				WHERE device_type = '$type' AND (cost_time>0 
					OR cost_fixed>0
					OR cost_material>0)";
			$result=$DB->query($query) or die("0.7 update ticket_tco" . $LANG["update"][90] . $DB->error());
			if ($DB->numrows($result)){
				while ($data=$DB->fetch_array($result)){
					$query2="UPDATE $table SET ticket_tco='".computeTicketTco($type,$data["computer"])."' 
						WHERE ID='".$data["computer"]."';";
					$DB->query($query2) or die("0.7 update ticket_tco" . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}	
	if (!FieldExists("glpi_software", "helpdesk_visible")) {
		$query = "ALTER TABLE glpi_software ADD `helpdesk_visible` INT NOT NULL default '1'";
		$DB->query($query) or die("0.7 add helpdesk_visible in glpi_software " . $LANG["update"][90] . $DB->error());
	}
	
	// TODO Enterprises -> dropdown manufacturer + update import OCS
	if (!TableExists("glpi_dropdown_manufacturer")) {
	
		$query="CREATE TABLE `glpi_dropdown_manufacturer` (
		`ID` int(11) NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		`comments` text,
		PRIMARY KEY  (`ID`),
		KEY `name` (`name`)
		) ENGINE=MyISAM ;";
		$DB->query($query) or die("0.7 add dropdown_manufacturer table " . $LANG["update"][90] . $DB->error());

		// Fill table
		$query="SELECT * FROM glpi_enterprises ORDER BY ID";
		if ($result=$DB->query($query)){
			if ($DB->numrows($result)){
				while ($data=$DB->fetch_assoc($result)){
					$data=addslashes_deep($data);
					$comments="";
					if (!empty($data['address'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$LANG["financial"][44].":\n";
						$comments.=$data['address'];
					}
					if (!empty($data['postcode'])||!empty($data['town'])){
						if (!empty($comments)) $comments.=$LANG["financial"][44].":\n";
						$comments.=$data['postcode']." ".$data['town'];
					}
					if (!empty($data['state'])||!empty($data['country'])){
						if (!empty($comments)) $comments.=$LANG["financial"][44].":\n";
						$comments.=$data['country']." ".$data['state'];
					}
					if (!empty($data['website'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$LANG["financial"][45].": ";
						$comments.=$data['website'];
					}
					if (!empty($data['phonenumber'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$LANG["financial"][29].": ";
						$comments.=$data['phonenumber'];
					}
					if (!empty($data['fax'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$LANG["financial"][30].": ";
						$comments.=$data['fax'];
					}
					if (!empty($data['email'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$LANG["setup"][14].": ";
						$comments.=$data['email'];
					}
					if (!empty($data['comments'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$data['comments'];
					}
					if (!empty($data['notes'])){
						if (!empty($comments)) $comments.="\n";
						$comments.=$data['notes'];
					}
					$query2="INSERT INTO `glpi_dropdown_manufacturer` (ID,name,comments) VALUES ('".$data['ID']."','".$data['name']."','$comments')";
					$DB->query($query2) or die("0.7 add manufacturer item " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}

	if (!FieldExists("glpi_ocs_link", "ocs_server_id")) {
		$query = "ALTER TABLE glpi_ocs_link ADD `ocs_server_id` int(11) NOT NULL";
		$DB->query($query) or die("0.7 add ocs_server_id in glpi_ocs_link " . $LANG["update"][90] . $DB->error());
		$query="update glpi_ocs_link set ocs_server_id=1";
		$DB->query($query) or die("0.7 update ocs_server_id=1 in glpi_ocs_link " . $LANG["update"][90] . $DB->error());
	}
	
	if (!FieldExists("glpi_ocs_config", "tplname")) {
		$query = "ALTER TABLE glpi_ocs_config ADD `name` varchar(200) default NULL AFTER `ID`, ADD `is_template` enum('0','1') NOT NULL default '0', ADD `tplname` varchar(200) default NULL, ADD `date_mod` datetime default NULL";
		$DB->query($query) or die("0.7 add name, is_template, tplname, date_mod in glpi_ocs_link " . $LANG["update"][90] . $DB->error());
		$query="update glpi_ocs_config set name=ocs_db_host";
		$DB->query($query) or die("0.7 add name in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}
	
	if(!FieldExists("glpi_ocs_config","import_registry")){		
		$query = "ALTER TABLE glpi_ocs_config ADD `import_registry` INT NOT NULL default '0' AFTER `import_device_modems`";
		$DB->query($query) or die("0.7 add import_registry in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}
	if (!TableExists("glpi_registry")) {
		$query="CREATE TABLE  `glpi_registry` (
 				`ID` int(10) unsigned NOT NULL auto_increment,
 				`computer_id` int(10) unsigned NOT NULL,
 				`registry_hive` varchar(45) NOT NULL,
 				`registry_path` varchar(255) NOT NULL,
 				`registry_value` varchar(255) NOT NULL,
 				PRIMARY KEY  (`ID`)
				) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		$DB->query($query) or die("0.7 add glpi_registry table " . $LANG["update"][90] . $DB->error());
		
	}

	//// Enum clean
	// Enum 0-1
	$enum01=array();
	$template_tables=array("glpi_computers","glpi_networking","glpi_printers","glpi_monitors","glpi_peripherals","glpi_software","glpi_phones","state_types","reservation_types","glpi_ocs_config");

	foreach ($template_tables as $table){
		if (!isset($enum01[$table])){
			$enum01[$table]=array();
		}
		$enum01[$table][]="is_template";
	}
	$enum01["glpi_config"][]="auto_assign";
	$enum01["glpi_config"][]="public_faq";
	$enum01["glpi_config"][]="url_in_mail";
	$enum01["glpi_profiles"][]="is_default";

	$enum01["glpi_monitors"][]="is_global";
	$enum01["glpi_peripherals"][]="is_global";
	$enum01["glpi_phones"][]="is_global";
	$enum01["glpi_printers"][]="is_global";
	$enum01["glpi_reminder"][]="rv";
	$enum01["glpi_contract_device"][]="is_template";
	$enum01["glpi_doc_device"][]="is_template";
	
	foreach ($enum01 as $table => $fields){
		foreach ($fields as $key => $field){
			if (FieldExists($table,$field)){
				
				$query="ALTER TABLE `$table` ADD `tmp_convert_enum` SMALLINT NOT NULL DEFAULT '0' AFTER `$field` ";
				$DB->query($query) or die("0.7 alter $table add new field tmp_convert_enum " . $LANG["update"][90] . $DB->error());

				$query="UPDATE `$table` SET tmp_convert_enum='1' WHERE $field='1';" ;
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());
				$query="UPDATE `$table` SET tmp_convert_enum='0' WHERE $field='0';" ;
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());

				$query="ALTER TABLE `$table` DROP `$field` ";
				$DB->query($query) or die("0.7 alter $table drop tmp enum field " . $LANG["update"][90] . $DB->error());

				$query="ALTER TABLE `$table` CHANGE `tmp_convert_enum` `$field` SMALLINT NOT NULL DEFAULT '0'";
				$DB->query($query) or die("0.7 alter $table move enum $field to tmp field " . $LANG["update"][90]. $DB->error()); 

				if ($table!="glpi_config"&&$table!="glpi_profiles"){
					$query ="ALTER TABLE `$table` ADD KEY (`$field`)";
					$DB->query($query) or die("0.7 alter $table add deleted key " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}
	
	$enumYN["N"]["glpi_contracts"][]="monday"; // N
	$enumYN["N"]["glpi_contracts"][]="saturday"; // N
	$enumYN["Y"]["glpi_device_drive"][]="is_writer"; // Y 
	$enumYN["N"]["glpi_device_control"][]="raid"; // Y -> N
	$enumYN["Y"]["glpi_device_power"][]="atx"; // Y
	$enumYN["N"]["glpi_licenses"][]="oem"; // N
	$enumYN["Y"]["glpi_licenses"][]="buy"; // Y
	$enumYN["N"]["glpi_software"][]="is_update"; // N
	$enumYN["Y"]["glpi_type_docs"][]="upload"; // Y

	$deleted_tables=array("glpi_computers","glpi_networking","glpi_printers","glpi_monitors","glpi_peripherals","glpi_software","glpi_cartridges_type","glpi_contracts","glpi_contacts","glpi_enterprises","glpi_docs","glpi_phones","glpi_consumables_type");

	foreach ($deleted_tables as $table){
		if (!isset($enum01[$table])){
			$enum01[$table]=array();
		}
		$enumYN["N"][$table][]="deleted";
	}


	foreach ($enumYN as $default => $tmptbl)
	foreach ($tmptbl as $table => $fields){
		foreach ($fields as $key => $field){
			if (FieldExists($table,$field)){

				$newdef=0;
				if ($default=="Y"){
					$newdef=1;
				}

				$query="ALTER TABLE `$table` ADD `tmp_convert_enum` SMALLINT NOT NULL DEFAULT '$newdef' AFTER `$field` ";
				$DB->query($query) or die("0.7 alter $table add new field tmp_convert_enum " . $LANG["update"][90] . $DB->error());

				$query="UPDATE `$table` SET tmp_convert_enum='1' WHERE $field='Y';" ;
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());
				$query="UPDATE `$table` SET tmp_convert_enum='0' WHERE $field='N';" ;
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());

				$query="ALTER TABLE `$table` DROP `$field` ";
				$DB->query($query) or die("0.7 alter $table drop tmp enum field " . $LANG["update"][90] . $DB->error());

				$query="ALTER TABLE `$table` CHANGE `tmp_convert_enum` `$field` SMALLINT NOT NULL DEFAULT '$newdef'";
				$DB->query($query) or die("0.7 alter $table move enum $field to tmp field " . $LANG["update"][90]. $DB->error()); 

				if ($field=="deleted"||$table=="glpi_licenses"||$table=="glpi_software"||$table=="glpi_type_docs"){
					$query ="ALTER TABLE `$table` ADD KEY (`$field`)";
					$DB->query($query) or die("0.7 alter $table add deleted key " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}

	if(FieldExists("glpi_tracking","is_group")){		
		$query = "ALTER TABLE glpi_tracking DROP `is_group`";
		$DB->query($query) or die("0.7 drop is_group from tracking " . $LANG["update"][90] . $DB->error());
	}
	

	$enumYesNo["glpi_kbitems"][]="faq"; 
	$enumYesNo["glpi_tracking"][]="emailupdates"; 
	$enumYesNo["glpi_users"][]="tracking_order"; 


	foreach ($enumYesNo as $table => $fields){
		foreach ($fields as $key => $field){
			if (FieldExists($table,$field)){


				$query="ALTER TABLE `$table` ADD `tmp_convert_enum` SMALLINT NOT NULL DEFAULT '0' AFTER `$field` ";
				$DB->query($query) or die("0.7 alter $table add new field tmp_convert_enum " . $LANG["update"][90] . $DB->error());

				$query="UPDATE `$table` SET tmp_convert_enum='1' WHERE $field='yes';" ;
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());
				$query="UPDATE `$table` SET tmp_convert_enum='0' WHERE $field='no';" ;
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());

				$query="ALTER TABLE `$table` DROP `$field` ";
				$DB->query($query) or die("0.7 alter $table drop tmp enum field " . $LANG["update"][90] . $DB->error());

				$query="ALTER TABLE `$table` CHANGE `tmp_convert_enum` `$field` SMALLINT NOT NULL DEFAULT '0'";
				$DB->query($query) or die("0.7 alter $table move enum $field to tmp field " . $LANG["update"][90]. $DB->error());

				if ($table=="glpi_kbitems"){
					$query ="ALTER TABLE `$table` ADD KEY (`$field`)";
					$DB->query($query) or die("0.7 alter $table add deleted key " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}
	// Reste enum : glpi_tracking.status et glpi_device_gfxcard.interface


	// TODO Split Config -> config general + config entity
	// TODO Auto assignment profile based on rules
	// TODO Add default profile to user + update data from preference

	// Alter INT fields to not null and default 0 :
	/* #819 -> clean CommonDBTM update(
	Need to update fields before.
	ALTER TABLE `glpi_computers` CHANGE `FK_users` `FK_users` INT( 11 ) NOT NULL DEFAULT '0',
	CHANGE `FK_groups` `FK_groups` INT( 11 ) NOT NULL DEFAULT '0'
	......
	*/
} // fin 0.7 #####################################################################################
?>
