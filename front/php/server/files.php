<?php
//------------------------------------------------------------------------------
//  Pi.Alert
//  Open Source Network Guard / WIFI & LAN intrusion detector
//
//  file.php - Back module. Server side. FileSystem Operations
//------------------------------------------------------------------------------
//  leiweibau  2023        https://github.com/leiweibau     GNU GPLv3
//------------------------------------------------------------------------------

session_start();

if ($_SESSION["login"] != 1) {
	header('Location: ../../index.php');
	exit;
}
require 'timezone.php';
require 'db.php';
require 'util.php';
require 'journal.php';
require 'language_switch.php';
require '../templates/language/' . $pia_lang_selected . '.php';

// Action selector
// Set maximum execution time to 15 seconds
ini_set('max_execution_time', '30');

// Open DB
OpenDB();

// Action functions
if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	switch ($action) {
	case 'RestoreDBfromArchive':RestoreDBfromArchive();
		break;
	case 'PurgeDBBackups':PurgeDBBackups();
		break;
	case 'EnableDarkmode':EnableDarkmode();
		break;
	case 'EnableOnlineHistoryGraph':EnableOnlineHistoryGraph();
		break;
	case 'SetAPIKey':SetAPIKey();
		break;
	case 'LoginEnable':LoginEnable();
		break;
	case 'LoginDisable':LoginDisable();
		break;
	case 'deleteAllNotifications':deleteAllNotifications();
		break;
	case 'deleteAllNotificationsArchive':deleteAllNotificationsArchive();
		break;
	case 'setTheme':setTheme();
		break;
	case 'setLanguage':setLanguage();
		break;
	case 'setArpTimer':setArpTimer();
		break;
	case 'setDeviceListCol':setDeviceListCol();
		break;
	case 'RestoreConfigFile':RestoreConfigFile();
		break;
	case 'BackupConfigFile':BackupConfigFile();
		break;
	case 'BackupDBtoArchive':BackupDBtoArchive();
		break;
	case 'BackupDBtoCSV':BackupDBtoCSV();
		break;
	case 'SaveConfigFile':SaveConfigFile();
		break;
	case 'getReportTotals':getReportTotals();
		break;
	case 'setFavIconURL':setFavIconURL();
		break;
	case 'setPiholeURL':setPiholeURL();
		break;
	case 'GetLogfiles':GetLogfiles();
		break;
	case 'GetServerTime':GetServerTime();
		break;
	case 'GetUpdateStatus':GetUpdateStatus();
		break;
	case 'GetARPStatus':GetARPStatus();
		break;
	case 'GetAutoBackupStatus':GetAutoBackupStatus();
		break;
	case 'ToggleImport':ToggleImport();
		break;
	default:logServerConsole('Action: ' . $action);
		break;
	}
}

function GetAutoBackupStatus() {
	global $pia_lang;
	if (file_exists("../../../back/.backup")) {$result = array($pia_lang['BE_Files_autobkp_pending']);} else {$result = array($pia_lang['BE_Files_autobkp_pause']);}
	//Count db backups
	$backupdir = "../../../db";
	$backupfiles = glob($backupdir . "/pialertdb_20*.zip");
	$result[] = count($backupfiles);
	//Calc Backup size
	$backupfile_size = 0;
	foreach ($backupfiles as $file) {
		$backupfile_size = $backupfile_size + filesize($file);
	}
	//Count config backups
	$backupdir = "../../../config";
	$backupfiles = glob($backupdir . "/pialert-20*.bak");
	$result[] = count($backupfiles);

	$result[] = number_format(($backupfile_size / 1000000), 2, ",", ".") . ' MB';
	echo json_encode($result);
}

function GetARPStatus() {
	global $pia_lang;
	if (file_exists("../../../back/.scanning")) {$result = array('');} else {$result = array($pia_lang['MT_arpscancout_norun']);}
	echo json_encode($result);
}

function GetUpdateStatus() {
	$updatenotification = '../../auto_Update.info';
	if (file_exists($updatenotification)) {
		$answer[0] = "i";
		echo (json_encode($answer));
	} else {
		$answer[0] = "";
		echo (json_encode($answer));
	}
}

function GetServerTime() {
	echo date("Y,n,j,G,i,s");
}

// Read logfiles --------------------------------------------------------------
function GetLogfiles() {
	global $pia_lang;

	$logfiles = ["pialert.1.log", "pialert.IP.log", "pialert.vendors.log", "pialert.cleanup.log", "pialert.webservices.log"];
	$logmessage = [$pia_lang['MT_Tools_Logviewer_Scan_empty'], $pia_lang['MT_Tools_Logviewer_IPLog_empty'], '', $pia_lang['MT_Tools_Logviewer_Cleanup_empty'], $pia_lang['MT_Tools_Logviewer_WebServices_empty']];

	$i = 0;
	$logs = array();
	while($i < count($logfiles)) {
		$file = file_get_contents($logfiles[$i], true);
		if ($file == "") {
			array_push($logs, $logmessage[$i]);
		} else {
			if ($logfiles[$i] == "pialert.webservices.log") {
				$file = str_replace("Start Services Monitoring\n\n", "Start Services Monitoring\n\n<pre style=\"border: solid 1px #666; background-color: transparent;\">", $file);
				$file = str_replace("\nServices Monitoring Changes:", "\n</pre>Services Monitoring Changes:", $file);
			}
			// if ($logfiles[$i] == "pialert.1.log") {
			// 	$file = str_replace("\n        ...Skipped", "<span style=\"color:red;\"> Skipped</span>", $file);
			// }
			$templog = str_replace("\n", '<br>', str_replace("    ", '&nbsp;&nbsp;&nbsp;&nbsp;', str_replace("        ", '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $file)));
			array_push($logs, $templog);
		}
	    $i++;
	}
	echo (json_encode($logs));
}

function convert_bool($var) {
	if ($var == 1) {return "True";} else {return "False";}
}

//  Save Config
function SaveConfigFile() {
	global $pia_lang;

	$laststate = '../../../config/pialert-prev.bak';
	$configfile = '../../../config/pialert.conf';

	$configContent = preg_replace('/^\s*#.*$/m', '', $_REQUEST['configfile']);
	$configArray = parse_ini_string($configContent);

	$ignorlist_search = array("[ ", " ]", ", ", ",", "[", "]");
	$ignorlist_replace = array("[", "]", ",", "','", "['", "']");
	// Handle some special entries
	$Mail_Reort = str_replace(" ", "", $configArray['REPORT_FROM']);
	if (stristr($Mail_Reort, "<+SMTP_USER+>")) {
		$mail_parts = array();
		$mail_parts = explode("<", $configArray['REPORT_FROM']);
		$mail_parts[1] = '<' . $mail_parts[1];
		$mail_parts[1] = str_replace(" ", "", $mail_parts[1]);
		$mail_parts[1] = str_replace("<+SMTP_USER+>", "<' + SMTP_USER + '>", $mail_parts[1]);
		$configArray['REPORT_FROM'] = $mail_parts[0] . $mail_parts[1];
	}
	if ($configArray['MAC_IGNORE_LIST'] != "" && $configArray['MAC_IGNORE_LIST'] != "[]") {
		$configArray['MAC_IGNORE_LIST'] = str_replace($ignorlist_search, $ignorlist_replace, $configArray['MAC_IGNORE_LIST']);
	} else {
		$configArray['MAC_IGNORE_LIST'] = "[]";
	}
	if ($configArray['IP_IGNORE_LIST'] != "" && $configArray['IP_IGNORE_LIST'] != "[]") {
		$configArray['IP_IGNORE_LIST'] = str_replace($ignorlist_search, $ignorlist_replace, $configArray['IP_IGNORE_LIST']);
	} else {
		$configArray['IP_IGNORE_LIST'] = "[]";
	}

	if (substr($configArray['SCAN_SUBNETS'], 0, 2) == "--") {$configArray['SCAN_SUBNETS'] = "'" . $configArray['SCAN_SUBNETS'] . "'";} else {
		$configArray['SCAN_SUBNETS'] = str_replace($ignorlist_search, $ignorlist_replace, $configArray['SCAN_SUBNETS']);
	}
	if ($configArray['PUSHSAFER_PRIO'] == "") {$configArray['PUSHSAFER_PRIO'] = 0;}
	if ($configArray['PUSHOVER_PRIO'] == "") {$configArray['PUSHOVER_PRIO'] = 0;}
	
	$configArray['NETWORK_DNS_SERVER'] = str_replace(" ", "", $configArray['NETWORK_DNS_SERVER']);
	if ($configArray['NETWORK_DNS_SERVER'] == "") {
		$configArray['NETWORK_DNS_SERVER'] = "localhost";
	} else {
		if (filter_var(gethostbyname($configArray['NETWORK_DNS_SERVER']), FILTER_VALIDATE_IP)) {
		    $configArray['NETWORK_DNS_SERVER'] = $configArray['NETWORK_DNS_SERVER'];
		} else {$configArray['NETWORK_DNS_SERVER'] = "localhost";}
	}
	# Fix install script error 26.11.2024
	if (!is_numeric($configArray['REPORT_TO_ARCHIVE'])) {$configArray['REPORT_TO_ARCHIVE'] = 0;}


	$config_template = "# General Settings
# ----------------------
PIALERT_PATH               = '" . $configArray['PIALERT_PATH'] . "'
DB_PATH                    = " . str_replace("PIALERT_PATH + /", "PIALERT_PATH + '/", $configArray['DB_PATH']) . "'
LOG_PATH                   = " . str_replace("PIALERT_PATH + /", "PIALERT_PATH + '/", $configArray['LOG_PATH']) . "'
PRINT_LOG                  = " . convert_bool($configArray['PRINT_LOG']) . "
VENDORS_DB                 = '" . $configArray['VENDORS_DB'] . "'
PIALERT_APIKEY             = '" . $configArray['PIALERT_APIKEY'] . "'
PIALERT_WEB_PROTECTION     = " . convert_bool($configArray['PIALERT_WEB_PROTECTION']) . "
PIALERT_WEB_PASSWORD       = '" . $configArray['PIALERT_WEB_PASSWORD'] . "'
NETWORK_DNS_SERVER         = '" . $configArray['NETWORK_DNS_SERVER'] . "'
AUTO_UPDATE_CHECK          = " . convert_bool($configArray['AUTO_UPDATE_CHECK']) . "
AUTO_DB_BACKUP             = " . convert_bool($configArray['AUTO_DB_BACKUP']) . "
AUTO_DB_BACKUP_KEEP        = " . $configArray['AUTO_DB_BACKUP_KEEP'] . "
REPORT_NEW_CONTINUOUS      = " . convert_bool($configArray['REPORT_NEW_CONTINUOUS']) . "
NEW_DEVICE_PRESET_EVENTS   = " . convert_bool($configArray['NEW_DEVICE_PRESET_EVENTS']) . "
NEW_DEVICE_PRESET_DOWN     = " . convert_bool($configArray['NEW_DEVICE_PRESET_DOWN']) . "
SYSTEM_TIMEZONE            = '" . $configArray['SYSTEM_TIMEZONE'] . "'
OFFLINE_MODE               = " . convert_bool($configArray['OFFLINE_MODE']) . "

# Other Modules
# ----------------------
SCAN_WEBSERVICES           = " . convert_bool($configArray['SCAN_WEBSERVICES']) . "
ICMPSCAN_ACTIVE            = " . convert_bool($configArray['ICMPSCAN_ACTIVE']) . "
SATELLITES_ACTIVE          = " . convert_bool($configArray['SATELLITES_ACTIVE']) . "

# Special Protocol Scanning
# ----------------------
SCAN_ROGUE_DHCP            = " . convert_bool($configArray['SCAN_ROGUE_DHCP']) . "
DHCP_SERVER_ADDRESS        = '" . $configArray['DHCP_SERVER_ADDRESS'] . "'

# Custom Cronjobs
# ----------------------
# The shortest interval is 3 minutes. All larger intervals must be integer multiples of 3 minutes.
AUTO_UPDATE_CHECK_CRON     = '" . $configArray['AUTO_UPDATE_CHECK_CRON'] . "'
AUTO_DB_BACKUP_CRON        = '" . $configArray['AUTO_DB_BACKUP_CRON'] . "'
REPORT_NEW_CONTINUOUS_CRON = '" . $configArray['REPORT_NEW_CONTINUOUS_CRON'] . "'
SPEEDTEST_TASK_CRON        = '" . $configArray['SPEEDTEST_TASK_CRON'] . "'

# Mail-Account Settings
# ----------------------
SMTP_SERVER                = '" . $configArray['SMTP_SERVER'] . "'
SMTP_PORT                  = " . $configArray['SMTP_PORT'] . "
SMTP_USER                  = '" . $configArray['SMTP_USER'] . "'
SMTP_PASS                  = '" . $configArray['SMTP_PASS'] . "'
SMTP_SKIP_TLS	           = " . convert_bool($configArray['SMTP_SKIP_TLS']) . "
SMTP_SKIP_LOGIN	           = " . convert_bool($configArray['SMTP_SKIP_LOGIN']) . "

# WebGUI Reporting
# ----------------------
REPORT_WEBGUI              = " . convert_bool($configArray['REPORT_WEBGUI']) . "
REPORT_WEBGUI_WEBMON       = " . convert_bool($configArray['REPORT_WEBGUI_WEBMON']) . "
REPORT_TO_ARCHIVE          = " . $configArray['REPORT_TO_ARCHIVE'] . "
# Number of hours after which a report is moved to the archive. The value 0 disables the feature

# Mail Reporting
# ----------------------
REPORT_MAIL                = " . convert_bool($configArray['REPORT_MAIL']) . "
REPORT_MAIL_WEBMON         = " . convert_bool($configArray['REPORT_MAIL_WEBMON']) . "
REPORT_FROM                = '" . $configArray['REPORT_FROM'] . "'
REPORT_TO                  = '" . $configArray['REPORT_TO'] . "'
REPORT_DEVICE_URL          = '" . $configArray['REPORT_DEVICE_URL'] . "'
REPORT_DASHBOARD_URL       = '" . $configArray['REPORT_DASHBOARD_URL'] . "'

# Pushsafer
# ----------------------
REPORT_PUSHSAFER           = " . convert_bool($configArray['REPORT_PUSHSAFER']) . "
REPORT_PUSHSAFER_WEBMON    = " . convert_bool($configArray['REPORT_PUSHSAFER_WEBMON']) . "
PUSHSAFER_TOKEN            = '" . $configArray['PUSHSAFER_TOKEN'] . "'
PUSHSAFER_DEVICE           = '" . $configArray['PUSHSAFER_DEVICE'] . "'
PUSHSAFER_PRIO             = " . $configArray['PUSHSAFER_PRIO'] . "
PUSHSAFER_SOUND            = " . $configArray['PUSHSAFER_SOUND'] . "

# Pushover
# ----------------------
REPORT_PUSHOVER            = " . convert_bool($configArray['REPORT_PUSHOVER']) . "
REPORT_PUSHOVER_WEBMON     = " . convert_bool($configArray['REPORT_PUSHOVER_WEBMON']) . "
PUSHOVER_TOKEN             = '" . $configArray['PUSHOVER_TOKEN'] . "'
PUSHOVER_USER              = '" . $configArray['PUSHOVER_USER'] . "'
PUSHOVER_PRIO              = " . $configArray['PUSHOVER_PRIO'] . "
PUSHOVER_SOUND             = '" . $configArray['PUSHOVER_SOUND'] . "'

# NTFY
#---------------------------
REPORT_NTFY                = " . convert_bool($configArray['REPORT_NTFY']) . "
REPORT_NTFY_WEBMON         = " . convert_bool($configArray['REPORT_NTFY_WEBMON']) . "
NTFY_HOST                  = '" . $configArray['NTFY_HOST'] . "'
NTFY_TOPIC                 = '" . $configArray['NTFY_TOPIC'] . "'
NTFY_USER                  = '" . $configArray['NTFY_USER'] . "'
NTFY_PASSWORD	           = '" . $configArray['NTFY_PASSWORD'] . "'
NTFY_PRIORITY 	           = '" . $configArray['NTFY_PRIORITY'] . "'
NTFY_CLICKABLE 	           = " . convert_bool($configArray['NTFY_CLICKABLE']) . "

# Shoutrrr
# ----------------------
SHOUTRRR_BINARY            = '" . $configArray['SHOUTRRR_BINARY'] . "'
# SHOUTRRR_BINARY            = 'armhf'
# SHOUTRRR_BINARY            = 'arm64'
# SHOUTRRR_BINARY            = 'x86'

# Telegram via Shoutrrr
# ----------------------
REPORT_TELEGRAM            = " . convert_bool($configArray['REPORT_TELEGRAM']) . "
REPORT_TELEGRAM_WEBMON     = " . convert_bool($configArray['REPORT_TELEGRAM_WEBMON']) . "
TELEGRAM_BOT_TOKEN_URL     = '" . $configArray['TELEGRAM_BOT_TOKEN_URL'] . "'

# DynDNS and IP
# ----------------------
QUERY_MYIP_SERVER          = '" . $configArray['QUERY_MYIP_SERVER'] . "'
DDNS_ACTIVE                = " . convert_bool($configArray['DDNS_ACTIVE']) . "
DDNS_DOMAIN                = '" . $configArray['DDNS_DOMAIN'] . "'
DDNS_USER                  = '" . $configArray['DDNS_USER'] . "'
DDNS_PASSWORD              = '" . $configArray['DDNS_PASSWORD'] . "'
DDNS_UPDATE_URL            = '" . $configArray['DDNS_UPDATE_URL'] . "'

# Automatic Speedtest
# ----------------------
SPEEDTEST_TASK_ACTIVE      = " . convert_bool($configArray['SPEEDTEST_TASK_ACTIVE']) . "

# Arp-scan Options & Samples
# ----------------------
ARPSCAN_ACTIVE             = " . convert_bool($configArray['ARPSCAN_ACTIVE']) . "
MAC_IGNORE_LIST            = " . $configArray['MAC_IGNORE_LIST'] . "
IP_IGNORE_LIST             = " . $configArray['IP_IGNORE_LIST'] . "
SCAN_SUBNETS               = " . $configArray['SCAN_SUBNETS'] . "
# SCAN_SUBNETS               = '--localnet'
# SCAN_SUBNETS               = '--localnet --interface=eth0'
# SCAN_SUBNETS               = ['192.168.1.0/24 --interface=eth0','192.168.2.0/24 --interface=eth1']

# ICMP Monitoring Options
# ----------------------
ICMP_ONLINE_TEST           = " . $configArray['ICMP_ONLINE_TEST'] . "
ICMP_GET_AVG_RTT           = " . $configArray['ICMP_GET_AVG_RTT'] . "

# Pi-hole Configuration
# ----------------------
PIHOLE_ACTIVE              = " . convert_bool($configArray['PIHOLE_ACTIVE']) . "
PIHOLE_VERSION             = " . $configArray['PIHOLE_VERSION'] . "
PIHOLE_DB                  = '" . $configArray['PIHOLE_DB'] . "'
PIHOLE6_URL                = '" . $configArray['PIHOLE6_URL'] . "'
PIHOLE6_PASSWORD           = '" . $configArray['PIHOLE6_PASSWORD'] . "'
PIHOLE6_API_MAXCLIENTS     = " . $configArray['PIHOLE6_API_MAXCLIENTS'] . "
DHCP_ACTIVE                = " . convert_bool($configArray['DHCP_ACTIVE']) . "
DHCP_LEASES                = '" . $configArray['DHCP_LEASES'] . "'
DHCP_INCL_SELF_TO_LEASES   = " . convert_bool($configArray['DHCP_INCL_SELF_TO_LEASES']) . "

# Fritzbox Configuration
# ----------------------
FRITZBOX_ACTIVE            = " . convert_bool($configArray['FRITZBOX_ACTIVE']) . "
FRITZBOX_IP                = '" . $configArray['FRITZBOX_IP'] . "'
FRITZBOX_USER              = '" . $configArray['FRITZBOX_USER'] . "'
FRITZBOX_PASS              = '" . $configArray['FRITZBOX_PASS'] . "'

# Mikrotik Configuration
# ----------------------
MIKROTIK_ACTIVE            = " . convert_bool($configArray['MIKROTIK_ACTIVE']) . "
MIKROTIK_IP                = '" . $configArray['MIKROTIK_IP'] . "'
MIKROTIK_USER              = '" . $configArray['MIKROTIK_USER'] . "'
MIKROTIK_PASS              = '" . $configArray['MIKROTIK_PASS'] . "'

# UniFi Configuration
# -------------------
UNIFI_ACTIVE               = " . convert_bool($configArray['UNIFI_ACTIVE']) . "
UNIFI_IP                   = '" . $configArray['UNIFI_IP'] . "'
UNIFI_API                  = '" . $configArray['UNIFI_API'] . "'
UNIFI_USER                 = '" . $configArray['UNIFI_USER'] . "'
UNIFI_PASS                 = '" . $configArray['UNIFI_PASS'] . "'
# Possible UNIFI APIs are v4, v5, unifiOS, UDMP-unifiOS, default

# OpenWRT Configuration
# ----------------------
OPENWRT_ACTIVE            = " . convert_bool($configArray['OPENWRT_ACTIVE']) . "
OPENWRT_IP                = '" . $configArray['OPENWRT_IP'] . "'
OPENWRT_USER              = '" . $configArray['OPENWRT_USER'] . "'
OPENWRT_PASS              = '" . $configArray['OPENWRT_PASS'] . "'

# Satellite Configuration
# -----------------------
SATELLITE_PROXY_MODE       = " . convert_bool($configArray['SATELLITE_PROXY_MODE']) . "
SATELLITE_PROXY_URL        = '" . $configArray['SATELLITE_PROXY_URL'] . "'

# Maintenance Tasks Cron
# ----------------------
DAYS_TO_KEEP_ONLINEHISTORY = " . $configArray['DAYS_TO_KEEP_ONLINEHISTORY'] . "
DAYS_TO_KEEP_EVENTS        = " . $configArray['DAYS_TO_KEEP_EVENTS'] . "
";

	copy($configfile, $laststate);
	$newconfig = fopen($configfile, 'w');
	fwrite($newconfig, $config_template);
	fclose($newconfig);

	echo $pia_lang['BE_Dev_ConfEditor_CopOkay'];

	// Logging
	pialert_logging('a_000', $_SERVER['REMOTE_ADDR'], 'LogStr_9999', '1', '');
	echo "<meta http-equiv='refresh' content='2; URL=./index.php'>";
}

//  Backup DB to Archiv
function BackupDBtoArchive() {

	$db_file_path = '../../../db';
	$db_file_name_org = 'pialert.db';
	$db_file_path_temp = '../../../db/temp';
	$db_file_name_temp = 'temp_backup.db';

	$db_file_org_full = $db_file_path . '/' . $db_file_name_org; # ../../../db/pialert.db
	$db_file_temp_full = $db_file_path . '/' . $db_file_name_temp; # ../../../db/temp_backup.db
	$db_file_new_full = $db_file_path_temp . '/' . $db_file_name_org; # ../../../db/temp/pialert.db

	$Pia_Archive_Name = 'pialertdb_' . date("Ymd_His") . '.zip';
	$Pia_Archive_Path = '../../../db/';

	global $pia_lang;

	// Check if DB has open transactions
	if (filesize($db_file_org_full . '-wal') != "0") {
		//DEBUG
		//echo filesize($db_file_org_full.'-shm').'-'.filesize($db_file_org_full.'-wal').' - ';
		echo $pia_lang['BE_Dev_Backup_WALError'];exit;
	}

	// copy database
	exec('sqlite3 "' . $db_file_org_full . '" ".backup ' . $db_file_temp_full . '"', $output);

	if (file_exists($db_file_temp_full)) {
		// Integrity Check if file copy exists
		$sql1 = "PRAGMA integrity_check";
		$sql2 = "PRAGMA foreign_key_check";
		exec('sqlite3 ' . $db_file_temp_full . ' "' . $sql1 . '"', $output_a);
		exec('sqlite3 ' . $db_file_temp_full . ' "' . $sql2 . '"', $output_b);

		if (($output_a[0] == "ok") && (sizeof($output_b) == 0)) {
			// Integrity Check is okay
			// move file to temp dir
			rename($db_file_temp_full, $db_file_new_full);
			// Create archive with actual date
			exec('zip -j ' . $Pia_Archive_Path . $Pia_Archive_Name . ' ' . $db_file_new_full, $output);
			// check if archive exists
			if (file_exists($Pia_Archive_Path . $Pia_Archive_Name) && filesize($Pia_Archive_Path . $Pia_Archive_Name) > 0) {
				// if archive exists
				echo $pia_lang['BE_Dev_Backup_okay'] . ' / Integrity Checked: (' . $Pia_Archive_Name . ')';
				unlink($db_file_new_full);
				echo ("<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=3'>");
			} else {
				// if archive not exists
				echo $pia_lang['BE_Dev_Backup_Failed'] . ' / Integrity Checked (pialert-latestbackup.db)';
			}
		} else {
			// Integrity Check is okay
			echo $pia_lang['BE_Dev_Backup_IntegrityError'];exit;
		}
	} else {
		// File does not exists
		echo $pia_lang['BE_Dev_Backup_CopError'];exit;
	}
	// Logging
	pialert_logging('a_010', $_SERVER['REMOTE_ADDR'], 'LogStr_0011', '', '');
}

//  Backup DB to CSV
function BackupDBtoCSV() {
	global $pia_lang;

	$Pia_Archive_Name = 'pialertcsv.zip';
	$Pia_Archive_Path = '../../../db/';

	$db_file_path = '../../../db';
	$db_file_name_org = 'pialert.db';
	$db_file_path_temp = '../../../db/temp';

	$db_file_org_full = $db_file_path . '/' . $db_file_name_org; # ../../../db/pialert.db
	$csv_file_devices = $db_file_path_temp . '/devices.csv'; # ../../../db/temp/devices.csv
	$csv_file_services = $db_file_path_temp . '/services.csv'; # ../../../db/temp/services.csv
	$csv_file_icmphosts = $db_file_path_temp . '/icmphosts.csv'; # ../../../db/temp/icmphosts.csv

	// delete old archive
	unlink($Pia_Archive_Path . $Pia_Archive_Name);

	exec('sqlite3 -header -csv "' . $db_file_org_full . '" "select * from devices;" > ' . $csv_file_devices, $output);
	exec('sqlite3 -header -csv "' . $db_file_org_full . '" "select * from services;" > ' . $csv_file_services, $output);
	exec('sqlite3 -header -csv "' . $db_file_org_full . '" "select * from ICMP_Mon;" > ' . $csv_file_icmphosts, $output);

	if (!file_exists($csv_file_devices) || !file_exists($csv_file_services) || !file_exists($csv_file_icmphosts)) {
		echo $pia_lang['BE_Dev_BackupCSV_FailedExport'];
		// delete csv files
		unlink($csv_file_devices);
		unlink($csv_file_services);
		unlink($csv_file_icmphosts);
		exit;
	}

	// create new archive
	exec('zip -j ' . $Pia_Archive_Path . $Pia_Archive_Name . ' ' . $db_file_path_temp . '/*.csv', $output);
	// delete csv files
	unlink($csv_file_devices);
	unlink($csv_file_services);
	unlink($csv_file_icmphosts);

	if (!file_exists($Pia_Archive_Path . $Pia_Archive_Name)) {
		echo $pia_lang['BE_Dev_BackupCSV_FailedZip'];
		exit;
	}

	echo $pia_lang['BE_Dev_BackupCSV_okay'];
	// Logging
	pialert_logging('a_010', $_SERVER['REMOTE_ADDR'], 'LogStr_0036', '', '');
}

//  Restore DB from Archiv
function RestoreDBfromArchive() {
	// prepare fast Backup
	$file = '../../../db/pialert.db';
	
	global $pia_lang;

	$Pia_Archive_Path = '../../../db/';
	exec('/bin/ls -Art ' . $Pia_Archive_Path . '*.zip | /bin/tail -n 1 | /usr/bin/xargs -n1 /bin/unzip -o -d ../../../db/', $output);
	// check if the pialert.db exists
	if (file_exists($file)) {
		echo $pia_lang['BE_Dev_Restore_okay'];
		// unlink($oldfile);
		echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=3'>";
	} else {
		echo $pia_lang['BE_Dev_Restore_Failed'];
	}
	// }
}

//  Enable Login
function LoginEnable() {
	global $pia_lang;

	session_destroy();
	exec('../../../back/pialert-cli set_login', $output);
	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0050', '', '');
	echo $pia_lang['BE_Dev_Login_enabled'];
	echo "<meta http-equiv='refresh' content='1; ./index.php?action=logout'>";
}

//  Disable Login
function LoginDisable() {
	global $pia_lang;

	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0051', '', '');

	session_destroy();
	setcookie("PiAlert_SaveLogin", "", time() - 3600);
	exec('../../../back/pialert-cli unset_login', $output);
	echo $pia_lang['BE_Dev_Login_disabled'];
	echo "<meta http-equiv='refresh' content='1; ./index.php?action=logout'>";
}

//  Set Device List Columns
function setDeviceListCol() {
	global $pia_lang;

	if (($_REQUEST['connectiontype'] == 0) || ($_REQUEST['connectiontype'] == 1)) {$Set_ConnectionType = $_REQUEST['connectiontype'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['favorite'] == 0) || ($_REQUEST['favorite'] == 1)) {$Set_Favorites = $_REQUEST['favorite'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['group'] == 0) || ($_REQUEST['group'] == 1)) {$Set_Group = $_REQUEST['group'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['owner'] == 0) || ($_REQUEST['owner'] == 1)) {$Set_Owner = $_REQUEST['owner'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['type'] == 0) || ($_REQUEST['type'] == 1)) {$Set_Type = $_REQUEST['type'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['firstsess'] == 0) || ($_REQUEST['firstsess'] == 1)) {$Set_First_Session = $_REQUEST['firstsess'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['lastsess'] == 0) || ($_REQUEST['lastsess'] == 1)) {$Set_Last_Session = $_REQUEST['lastsess'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['lastip'] == 0) || ($_REQUEST['lastip'] == 1)) {$Set_LastIP = $_REQUEST['lastip'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['mactype'] == 0) || ($_REQUEST['mactype'] == 1)) {$Set_MACType = $_REQUEST['mactype'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['macaddress'] == 0) || ($_REQUEST['macaddress'] == 1)) {$Set_MACAddress = $_REQUEST['macaddress'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['macvendor'] == 0) || ($_REQUEST['macvendor'] == 1)) {$Set_MACVendor = $_REQUEST['macvendor'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['location'] == 0) || ($_REQUEST['location'] == 1)) {$Set_Location = $_REQUEST['location'];} else {echo "Error. Wrong variable value!";exit;}
	if (($_REQUEST['wakeonlan'] == 0) || ($_REQUEST['wakeonlan'] == 1)) {$Set_WakeOnLAN = $_REQUEST['wakeonlan'];} else {echo "Error. Wrong variable value!";exit;}
	echo $pia_lang['BE_Dev_DevListCol_noti_text'];
	$config_array = array('ConnectionType' => $Set_ConnectionType, 'Favorites' => $Set_Favorites, 'Group' => $Set_Group, 'Owner' => $Set_Owner, 'Type' => $Set_Type, 'FirstSession' => $Set_First_Session, 'LastSession' => $Set_Last_Session, 'LastIP' => $Set_LastIP, 'MACType' => $Set_MACType, 'MACAddress' => $Set_MACAddress, 'MACVendor' => $Set_MACVendor, 'Location' => $Set_Location, 'WakeOnLAN' => $Set_WakeOnLAN);
	$DevListCol_file = '../../../config/setting_devicelist';
	$DevListCol_new = fopen($DevListCol_file, 'w');
	fwrite($DevListCol_new, json_encode($config_array));
	fclose($DevListCol_new);
	echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php'>";
	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0052', '', '');
}

//  Purge Backups
function PurgeDBBackups() {
	global $pia_lang;

	// Clean DB Backups
	$Pia_Archive_Path = '../../../db';
	$Pia_Backupfiles = array();
	$files = array_diff(scandir($Pia_Archive_Path, SCANDIR_SORT_DESCENDING), array('.', '..', 'pialert.db', 'temp', 'GeoLite2-Country.mmdb', 'pialert.db-shm', 'pialert.db-wal', 'pialertcsv.zip', 'user_vendors.txt'));
	foreach ($files as &$item) {
		$item = $Pia_Archive_Path . '/' . $item;
		if (stristr($item, 'setting_') == '') {array_push($Pia_Backupfiles, $item);}
	}
	if (sizeof($Pia_Backupfiles) > 3) {
		rsort($Pia_Backupfiles);
		unset($Pia_Backupfiles[0], $Pia_Backupfiles[1], $Pia_Backupfiles[2]);
		$Pia_Backupfiles_Purge = array_values($Pia_Backupfiles);
		for ($i = 0; $i < sizeof($Pia_Backupfiles_Purge); $i++) {
			unlink($Pia_Backupfiles_Purge[$i]);
		}
	}
	// Clean Config Backups
	unset($Pia_Backupfiles);
	$Pia_Archive_Path = '../../../config';
	$Pia_Backupfiles = array();
	$files = array_diff(scandir($Pia_Archive_Path, SCANDIR_SORT_DESCENDING), array('.', '..', 'pialert.conf', 'version.conf', 'pialert-prev.bak', 'pialert.conf.back'));
	foreach ($files as &$item) {
		$item = $Pia_Archive_Path . '/' . $item;
		if (stristr($item, 'setting_') == '') {array_push($Pia_Backupfiles, $item);}
	}
	if (sizeof($Pia_Backupfiles) > 3) {
		rsort($Pia_Backupfiles);
		unset($Pia_Backupfiles[0], $Pia_Backupfiles[1], $Pia_Backupfiles[2]);
		$Pia_Backupfiles_Purge = array_values($Pia_Backupfiles);
		for ($i = 0; $i < sizeof($Pia_Backupfiles_Purge); $i++) {
			unlink($Pia_Backupfiles_Purge[$i]);
		}
	}
	// Logging
	pialert_logging('a_010', $_SERVER['REMOTE_ADDR'], 'LogStr_0013', '', '');

	echo $pia_lang['BE_Dev_DBTools_Purge'];
	echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=3'>";
}

//  Toggle Dark/Light Themes
function EnableDarkmode() {
	$file = '../../../config/setting_darkmode';
	global $pia_lang;

	if (file_exists($file)) {
		echo $pia_lang['BE_Dev_darkmode_disabled'];
		unlink($file);
		// Logging
		pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0055', '', '');

		echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
	} else {
		echo $pia_lang['BE_Dev_darkmode_enabled'];
		$darkmode = fopen($file, 'w');
		// Logging
		pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0056', '', '');
		echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
	}
}

//  Toggle History Graph Themes
function EnableOnlineHistoryGraph() {
	$file = '../../../config/setting_noonlinehistorygraph';
	global $pia_lang;

	if (file_exists($file)) {
		echo $pia_lang['BE_Dev_onlinehistorygraph_enabled'];
		unlink($file);
		// Logging
		pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0058', '', '');
		echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
	} else {
		echo $pia_lang['BE_Dev_onlinehistorygraph_disabled'];
		$history = fopen($file, 'w');
		fclose($history);
		// Logging
		pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0057', '', '');
		echo "<meta http-equiv='refresh'content='2; URL=./maintenance.php?tab=4'>";
	}
}

//  Set API-Key
function SetAPIKey() {
	//$file = '../../../db/setting_noonlinehistorygraph';
	global $pia_lang;

	exec('../../../back/pialert-cli set_apikey', $output);
	// Logging
	pialert_logging('a_070', $_SERVER['REMOTE_ADDR'], 'LogStr_0700', '', '');
	echo $pia_lang['BE_Dev_setapikey'];
	echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=1'>";
}

//  Set Theme
function setTheme() {
	global $pia_lang;

	$installed_skins = array('skin-black-light',
		'skin-black',
		'skin-blue-light',
		'skin-blue',
		'skin-green-light',
		'skin-green',
		'skin-purple-light',
		'skin-purple',
		'skin-red-light',
		'skin-red',
		'skin-yellow-light',
		'skin-yellow');

	$installed_themes = array('leiweibau_dark',
		'leiweibau_light');

	if (isset($_REQUEST['SkinSelection'])) {
		$skin_set_dir = '../../../config/';
		// echo "Enter Level 1";
		$skin_selector = htmlspecialchars($_REQUEST['SkinSelection']);
		if (in_array($skin_selector, $installed_skins)) {
			// lösche alle vorherigen skins
			foreach ($installed_skins as $file) {
				unlink($skin_set_dir . 'setting_' . $file);
			}
			// lösche alle vorherigen themes
			foreach ($installed_themes as $file) {
				unlink($skin_set_dir . 'setting_theme_' . $file);
			}
			foreach ($installed_skins as $file) {
				if (file_exists($skin_set_dir . 'setting_' . $file)) {
					$skin_error = True;
					break;
				} else {
					$skin_error = False;
				}
			}
			if ($skin_error == False) {
				$testskin = fopen($skin_set_dir . 'setting_' . $skin_selector, 'w');
				echo $pia_lang['BE_Dev_Theme_set'] . ': ' . $_REQUEST['SkinSelection'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			} else {
				echo $pia_lang['BE_Dev_Theme_notset'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			}
		} elseif (in_array($skin_selector, $installed_themes)) {
			// lösche alle vorherigen skins
			foreach ($installed_skins as $file) {
				unlink($skin_set_dir . 'setting_' . $file);
			}
			// lösche alle vorherigen themes
			foreach ($installed_themes as $file) {
				unlink($skin_set_dir . 'setting_theme_' . $file);
			}
			foreach ($installed_skins as $file) {
				if (file_exists($skin_set_dir . 'setting_theme_' . $file)) {
					$skin_error = True;
					break;
				} else {
					$skin_error = False;
				}
			}
			if ($skin_error == False) {
				$testskin = fopen($skin_set_dir . 'setting_theme_' . $skin_selector, 'w');
				echo $pia_lang['BE_Dev_Theme_set'] . ': ' . $_REQUEST['SkinSelection'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			} else {
				echo $pia_lang['BE_Dev_Theme_notset'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			}
		} else {echo $pia_lang['BE_Dev_Theme_invalid'];}
	}
	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0053', '', $skin_selector);
}

//  Set Language
function setLanguage() {
	global $pia_lang;

	$pia_installed_langs = array('en_us',
		'de_de',
		'es_es',
		'fr_fr',
		'it_it');

	if (isset($_REQUEST['LangSelection'])) {
		$pia_lang_set_dir = '../../../config/';
		$pia_lang_selector = htmlspecialchars($_REQUEST['LangSelection']);
		if (in_array($pia_lang_selector, $pia_installed_langs)) {
			foreach ($pia_installed_langs as $file) {
				unlink($pia_lang_set_dir . 'setting_language_' . $file);
			}
			foreach ($pia_installed_langs as $file) {
				if (file_exists($pia_lang_set_dir . 'setting_language_' . $file)) {
					$pia_lang_error = True;
					break;
				} else {
					$pia_lang_error = False;
				}
			}
			if ($pia_lang_error == False) {
				$testlang = fopen($pia_lang_set_dir . 'setting_language_' . $pia_lang_selector, 'w');
				echo $pia_lang['BE_Dev_Language_set'] . ': ' . $_REQUEST['LangSelection'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			} else {
				echo $pia_lang['BE_Dev_Language_notset'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			}
		} else {echo $pia_lang['BE_Dev_Language_invalid'];}
	}
	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0054', '', $pia_lang_selector);
}

//  Set Timer
function setArpTimer() {
	global $pia_lang;

	if (isset($_REQUEST['ArpTimer'])) {
		#$pia_lang_set_dir = '../../../config/';
		$file = '../../../config/setting_stoppialert';
		if (file_exists($file)) {
			echo $pia_lang['BE_Dev_Arpscan_enabled'];
			// Logging
			pialert_logging('a_002', $_SERVER['REMOTE_ADDR'], 'LogStr_0510', '', '');
			exec('../../../back/pialert-cli enable_scan', $output);
			echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php'>";
		} else {
			if (is_numeric($_REQUEST['ArpTimer'])) {
				// Logging
				pialert_logging('a_002', $_SERVER['REMOTE_ADDR'], 'LogStr_0511', '', $_REQUEST['ArpTimer'] . ' min');
				exec('../../../back/pialert-cli disable_scan ' . $_REQUEST['ArpTimer'], $output);
			} else {
				// Logging
				pialert_logging('a_002', $_SERVER['REMOTE_ADDR'], 'LogStr_0512', '', '');
				exec('../../../back/pialert-cli disable_scan', $output);
			}
			echo $pia_lang['BE_Dev_Arpscan_disabled'];
			echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php'>";
		}
	}
}

//  Restore Config File
function RestoreConfigFile() {
	global $pia_lang;

	$file = '../../../config/pialert.conf';
	$laststate = '../../../config/pialert-prev.bak';
	// Restore fast Backup
	if (!copy($laststate, $file)) {
		echo $pia_lang['BE_Dev_ConfEditor_RestoreError'];
	} else {
		echo $pia_lang['BE_Dev_ConfEditor_RestoreOkay'];
	}
	copy($file, $laststate);
	// Logging
	pialert_logging('a_000', $_SERVER['REMOTE_ADDR'], 'LogStr_0006', '1', '');
	echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php'>";
}

//  Backup Config File
function BackupConfigFile() {
	global $pia_lang;

	// prepare fast Backup
	$file = '../../../config/pialert.conf';
	$newfile = '../../../config/pialert-' . date("Ymd_His") . '.bak';
	$laststate = '../../../config/pialert-prev.bak';
	if (!copy($file, $newfile)) {
		echo $pia_lang['BE_Dev_ConfEditor_CopError'];
	} else {
		echo $pia_lang['BE_Dev_ConfEditor_CopOkay'];
	}
	// copy files as a fast Backup
	copy($file, $laststate);

	// Logging
	pialert_logging('a_000', $_SERVER['REMOTE_ADDR'], 'LogStr_0007', '1', '');
	if ($_REQUEST['reload'] == 'yes') {
		echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=3'>";
	}
}

//  Delete All Notification in WebGUI
function deleteAllNotifications() {
	global $pia_lang;

	$regex = '/[0-9]+-[0-9]+_.*\\.txt/i';
	$reports_path = '../../reports/';
	$files = array_diff(scandir($reports_path, SCANDIR_SORT_DESCENDING), array('.', '..', 'archived'));
	$count_all_reports = sizeof($files);
	foreach ($files as &$item) {
		if (preg_match($regex, $item) == True) {
			unlink($reports_path . $item);
		}
	}
	echo $count_all_reports . ' ' . $pia_lang['BE_Dev_Report_Delete'];
	echo "<meta http-equiv='refresh' content='2; URL=./reports.php'>";
	// Logging
	pialert_logging('a_050', $_SERVER['REMOTE_ADDR'], 'LogStr_0504', '', '');
}

//  Delete All Notification in WebGUI
function deleteAllNotificationsArchive() {
	global $pia_lang;

	$regex = '/[0-9]+-[0-9]+_.*\\.txt/i';
	$reports_path = '../../reports/archived/';
	$files = array_diff(scandir($reports_path, SCANDIR_SORT_DESCENDING), array('.', '..', 'archived'));
	$count_all_reports = sizeof($files);
	foreach ($files as &$item) {
		if (preg_match($regex, $item) == True) {
			unlink($reports_path . $item);
		}
	}
	echo $count_all_reports . ' ' . $pia_lang['BE_Dev_Report_Delete'];
	echo "<meta http-equiv='refresh' content='2; URL=./reports.php?report_source=archive'>";
	// Logging
	pialert_logging('a_050', $_SERVER['REMOTE_ADDR'], 'LogStr_0506', '', '');
}

// Get Report Counter
function getReportTotals() {
	$files = array_diff(scandir('../../reports'), array('..', '.', 'archived'));
	$report_counter = count($files);
	$totals = array($report_counter);
	echo (json_encode($totals));
}

//  Set FavIcon
function setFavIconURL() {
	global $pia_lang;

	if (isset($_REQUEST['FavIconURL'])) {
		$iconlist = array();
		$iconlist['redglass_w_local'] = 'img/favicons/glass_red_white.png';
		$iconlist['redflat_w_local'] = 'img/favicons/flat_red_white.png';
		$iconlist['redglass_b_local'] = 'img/favicons/glass_red_black.png';
		$iconlist['redflat_b_local'] = 'img/favicons/flat_red_black.png';
		$iconlist['blueglass_w_local'] = 'img/favicons/glass_blue_white.png';
		$iconlist['blueflat_w_local'] = 'img/favicons/flat_blue_white.png';
		$iconlist['blueglass_b_local'] = 'img/favicons/glass_blue_black.png';
		$iconlist['blueflat_b_local'] = 'img/favicons/flat_blue_black.png';
		$iconlist['greenglass_w_local'] = 'img/favicons/glass_green_white.png';
		$iconlist['greenflat_w_local'] = 'img/favicons/flat_green_white.png';
		$iconlist['greenglass_b_local'] = 'img/favicons/glass_green_black.png';
		$iconlist['greenflat_b_local'] = 'img/favicons/flat_green_black.png';
		$iconlist['yellowglass_w_local'] = 'img/favicons/glass_yellow_white.png';
		$iconlist['yellowflat_w_local'] = 'img/favicons/flat_yellow_white.png';
		$iconlist['yellowglass_b_local'] = 'img/favicons/glass_yellow_black.png';
		$iconlist['yellowflat_b_local'] = 'img/favicons/flat_yellow_black.png';
		$iconlist['purpleglass_w_local'] = 'img/favicons/glass_purple_white.png';
		$iconlist['purpleflat_w_local'] = 'img/favicons/flat_purple_white.png';
		$iconlist['purpleglass_b_local'] = 'img/favicons/glass_purple_black.png';
		$iconlist['purpleflat_b_local'] = 'img/favicons/flat_purple_black.png';
		$iconlist['blackglass_w_local'] = 'img/favicons/glass_black_white.png';
		$iconlist['blackflat_w_local'] = 'img/favicons/flat_black_white.png';
		$iconlist['whiteglass_b_local'] = 'img/favicons/glass_white_black.png';
		$iconlist['whiteflat_b_local'] = 'img/favicons/flat_white_black.png';
		$iconlist['redglass_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_red_white.png';
		$iconlist['redflat_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_red_white.png';
		$iconlist['redglass_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_red_black.png';
		$iconlist['redflat_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_red_black.png';
		$iconlist['blueglass_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_blue_white.png';
		$iconlist['blueflat_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_blue_white.png';
		$iconlist['blueglass_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_blue_black.png';
		$iconlist['blueflat_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_blue_black.png';
		$iconlist['greenglass_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_green_white.png';
		$iconlist['greenflat_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_green_white.png';
		$iconlist['greenglass_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_green_black.png';
		$iconlist['greenflat_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_green_black.png';
		$iconlist['yellowglass_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_yellow_white.png';
		$iconlist['yellowflat_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_yellow_white.png';
		$iconlist['yellowglass_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_yellow_black.png';
		$iconlist['yellowflat_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_yellow_black.png';
		$iconlist['purpleglass_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_purple_white.png';
		$iconlist['purpleflat_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_purple_white.png';
		$iconlist['purpleglass_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_purple_black.png';
		$iconlist['purpleflat_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_purple_black.png';
		$iconlist['blackglass_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_black_white.png';
		$iconlist['blackflat_w_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_black_white.png';
		$iconlist['whiteglass_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/glass_white_black.png';
		$iconlist['whiteflat_b_remote'] = 'https://raw.githubusercontent.com/leiweibau/Pi.Alert/main/front/img/favicons/flat_white_black.png';

		$url = $_REQUEST['FavIconURL'];

		if ($iconlist[$url] != "") {
			$newfavicon_url = $iconlist[$url];
		} else {
			$temp_favicon_url = filter_var($url, FILTER_SANITIZE_URL);
			if (filter_var($temp_favicon_url, FILTER_VALIDATE_URL) && strtolower(substr($temp_favicon_url, 0, 4)) == "http") {
				$newfavicon_url = $temp_favicon_url;
				$file_path = '../../../config/setting_favicon';
				file_put_contents($file_path, $newfavicon_url);
				echo $pia_lang['BE_Files_FavIcon_okay'];
				echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
			} else {
				echo $pia_lang['BE_Files_FavIcon_error'];
			}
		}
	}
	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0059', '', $_REQUEST['FavIconURL']);
}


//  Set Pihole URL
function setPiholeURL() {
	global $pia_lang;

	if (isset($_REQUEST['PiholeURL'])) {
				$url = $_REQUEST['PiholeURL'];
		$temp_favicon_url = filter_var($url, FILTER_SANITIZE_URL);
		if (filter_var($temp_favicon_url, FILTER_VALIDATE_URL) && strtolower(substr($temp_favicon_url, 0, 4)) == "http") {
			$newfavicon_url = $temp_favicon_url;
			$file_path = '../../../config/setting_piholebutton';
			file_put_contents($file_path, $newfavicon_url);
			echo $pia_lang['BE_Files_PiholeURL_okay'] ;
			echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
		} elseif ($url == "") {
			$newfavicon_url = $temp_favicon_url;
			$file_path = '../../../config/setting_piholebutton';
			file_put_contents($file_path, $newfavicon_url);
			echo $pia_lang['BE_Files_PiholeURL_remove'];
			echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=4'>";
		} else {
			echo $pia_lang['BE_Files_PiholeURL_error'];
		}
	}
	// Logging
	pialert_logging('a_005', $_SERVER['REMOTE_ADDR'], 'LogStr_0060', '', $_REQUEST['PiholeURL']);
}


function ToggleImport() {

    $file_path = '../../../config/pialert.conf';

    if (!isset($_REQUEST['deviceType']) || !isset($_REQUEST['toggleState'])) {
        echo "Missing Parameter";
        exit;
    }

    $deviceMap = [
        'FB' => 'FRITZBOX_ACTIVE',
        'MT' => 'MIKROTIK_ACTIVE',
        'UF' => 'UNIFI_ACTIVE',
        'OW' => 'OPENWRT_ACTIVE',
    ];

    $deviceType = $_REQUEST['deviceType'];
    $toggleState = filter_var($_REQUEST['toggleState'], FILTER_VALIDATE_BOOLEAN);

    if (!array_key_exists($deviceType, $deviceMap)) {
        echo 'Invalid device type';
        exit;
    }

    $configKey = $deviceMap[$deviceType];
    $newValue = $toggleState ? 'False' : 'True';

    if (!file_exists($file_path)) {
        echo 'Configuration file not found';
        exit;
    }

    $fileContents = file_get_contents($file_path);
    if (strpos($fileContents, $configKey) === false) {
        echo "Key '{$configKey}' not found in configuration";
        exit;
    }

    $pattern = '/^(' . preg_quote($configKey, '/') . '\s*=\s*)(True|False)$/m';
    $replacement = '${1}' . $newValue;

    $newContents = preg_replace($pattern, $replacement, $fileContents);

    if ($newContents !== null) {
        file_put_contents($file_path, $newContents);
        echo "{$configKey} set to {$newValue}";
    } else {
        echo 'Failed to update configuration';
    }
	// Logging
	pialert_logging('a_000', $_SERVER['REMOTE_ADDR'], 'LogStr_9999', '1', $configKey.' set to '.$newValue);
	echo "<meta http-equiv='refresh' content='2; URL=./maintenance.php?tab=1'>";
}
?>
