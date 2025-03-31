<!-- ---------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector
#
#  systeminfo.php - Front module. SystemInfo page
#-------------------------------------------------------------------------------
#  leiweibau 2024                                          GNU GPLv3
#--------------------------------------------------------------------------- -->

<?php
session_start();

// Turn off php errors
error_reporting(0);

if ($_SESSION["login"] != 1) {
	header('Location: ./index.php');
	exit;
}

require 'php/templates/header.php';


$prevVal = shell_exec("sudo ../back/pialert-cli show_usercron");
$prevArr = explode("\n", trim($prevVal));
function filterValues($value) {
    return (substr($value, 0, 1) !== '#');
}
$cleancron = array_filter($prevArr, 'filterValues');
$stat['usercron'] = implode("\n", $cleancron);
// https://stackoverflow.com/a/19209082
$os_version = '';
// Raspbian
if ($os_version == '') {$os_version = exec('cat /etc/os-release | grep PRETTY_NAME');}
// Dietpi
if ($os_version == '') {$os_version = exec('uname -o');}
//$os_version_arr = explode("\n", trim($os_version));
$stat['os_version'] = str_replace('"', '', str_replace('PRETTY_NAME=', '', $os_version));
$stat['uptime'] = str_replace('up ', '', shell_exec("uptime -p"));
//cpu stat
$prevVal = shell_exec("cat /proc/cpuinfo | grep processor");
$prevArr = explode("\n", trim($prevVal));
$stat['cpu'] = sizeof($prevArr);
$cpu_result = shell_exec("cat /proc/cpuinfo | grep Model");
$stat['cpu_model'] = strstr($cpu_result, "\n", true);
$stat['cpu_model'] = str_replace(":", "", trim(str_replace("Model", "", $stat['cpu_model'])));
if ($stat['cpu_model'] == '') {
	$cpu_result = shell_exec("cat /proc/cpuinfo | grep model\ name");
	$stat['cpu_model'] = strstr($cpu_result, "\n", true);
	$stat['cpu_model'] = str_replace(":", "", trim(str_replace("model name", "", $stat['cpu_model'])));
}
if (file_exists('/sys/devices/system/cpu/cpu0/cpufreq/scaling_max_freq')) {
	// RaspbianOS
	$stat['cpu_frequ'] = exec('cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_max_freq') / 1000;
} elseif (is_numeric(str_replace(',', '.', exec('lscpu | grep "MHz" | awk \'{print $3}\'')))) {
	// Ubuntu Server, DietPi event. others
	$stat['cpu_frequ'] = round(exec('lscpu | grep "MHz" | awk \'{print $3}\''), 0);
} elseif (is_numeric(str_replace(',', '.', exec('lscpu | grep "max MHz" | awk \'{print $4}\'')))) {
	// RaspbianOS and event. others
	$stat['cpu_frequ'] = round(str_replace(',', '.', exec('lscpu | grep "max MHz" | awk \'{print $4}\'')), 0);
} else {
	// Fallback
	$stat['cpu_frequ'] = "unknown";
}
$kernel_arch = exec('dpkg --print-architecture');
//memory stat
$mem_result = shell_exec("cat /proc/meminfo | grep MemTotal");
$stat['mem_total'] = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $mem_result) / 1024 / 1024, 3);
$stat['mem_used'] = round(getMemUsage() * 100, 2);
//network stat
$network_result = shell_exec("cat /proc/net/dev | tail -n +3 | awk '{print $1}'");
$net_interfaces = explode("\n", trim($network_result));
$network_result = shell_exec("cat /proc/net/dev | tail -n +3 | awk '{print $2}'");
$net_interfaces_rx = explode("\n", trim($network_result));
$network_result = shell_exec("cat /proc/net/dev | tail -n +3 | awk '{print $10}'");
$net_interfaces_tx = explode("\n", trim($network_result));
//hdd stat
$hdd_result = shell_exec("sudo df | awk '{print $1}'");
$hdd_devices = explode("\n", trim($hdd_result));
$hdd_result = shell_exec("sudo df | awk '{print $2}'");
$hdd_devices_total = explode("\n", trim($hdd_result));
$hdd_result = shell_exec("sudo df | awk '{print $3}'");
$hdd_devices_used = explode("\n", trim($hdd_result));
$hdd_result = shell_exec("sudo df | awk '{print $4}'");
$hdd_devices_free = explode("\n", trim($hdd_result));
$hdd_result = shell_exec("sudo df | awk '{print $5}'");
$hdd_devices_percent = explode("\n", trim($hdd_result));
$hdd_result = shell_exec("sudo df | awk '{print $6}'");
$hdd_devices_mount = explode("\n", trim($hdd_result));
//usb devices
$usb_result = shell_exec("lsusb");
$usb_devices_mount = explode("\n", trim($usb_result));
// users
$temp_usercount = shell_exec("w -h | awk '{print $1 \"##\" $2 \"##\" $3 \"//\"}'");
$search_usercount = array("##:0", "##-");
$str_usercount = str_replace($search_usercount, '', substr($temp_usercount, 0, -3));
$arr_usercount = explode("//", $str_usercount);
foreach ($arr_usercount as $user) {
	$arr_user = explode("##", $user);
	$stat['user_count'] = $stat['user_count'] . '<span class="text-aqua">' . $arr_user[0] . '</span> (' . $arr_user[1] . ') <span class="text-red">' . $arr_user[2] . '</span> /';
}
$stat['user_count'] = substr($stat['user_count'], 0, -2);
// count processes
$stat['process_count'] = shell_exec("ps -e --no-headers | wc -l");
?>

<!-- Page ------------------------------------------------------------------ -->
<div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
    <?php require 'php/templates/notification.php';?>
      <h1 id="pageTitle">
         System Infomation
      </h1>
    </section>

    <!-- Main content ---------------------------------------------------------- -->
    <section class="content">
<?php
// Reboot Shutdown ----------------------------------------------------------
echo '
		<div class="row">
		  <div class="col-sm-6" style="text-align: center; margin-bottom:20px;">
			  <a href="#" class="btn btn-danger"><i class="fa-solid fa-power-off custom-menu-button-icon" id="Menu_Report_Envelope_Icon"></i><div class="custom-menu-button-text" onclick="askPialertShutdown()">'.$pia_lang['SysInfo_Shutdown_noti_head'].'</div></a>
		  </div>
		  <div class="col-sm-6" style="text-align: center; margin-bottom:20px;">
		      <a href="#" class="btn btn-warning"><i class="fa-solid fa-rotate-right custom-menu-button-icon" id="Menu_Report_Envelope_Icon"></i><div class="custom-menu-button-text" onclick="askPialertReboot()">'.$pia_lang['SysInfo_Reboot_noti_head'].'</div></a>
		  </div>
		</div>';



// Client ----------------------------------------------------------
echo '<div class="box box-solid">
        <div class="box-header"><h3 class="box-title sysinfo_headline"><i class="bi bi-globe"></i> This Client</h3></div>
        <div class="box-body">
					<div class="row">
					  <div class="col-sm-3 sysinfo_gerneral_a">User Agent</div>
					  <div class="col-sm-9 sysinfo_gerneral_b">' . $_SERVER['HTTP_USER_AGENT'] . '</div>
					</div>
					<div class="row">
					  <div class="col-sm-3 sysinfo_gerneral_a">Browser Resolution:</div>
					  <div class="col-sm-9 sysinfo_gerneral_b" id="resolution"></div>
					</div>
        </div>
      </div>';

echo '<script>
	var ratio = window.devicePixelRatio || 1;
	var w = window.innerWidth;
	var h = window.innerHeight;
	var rw = window.innerWidth * ratio;
	var rh = window.innerHeight * ratio;

	var resolutionDiv = document.getElementById("resolution");
	resolutionDiv.innerHTML = "Width: " + w + "px / Height: " + h + "px<br> " + "Width: " + rw + "px / Height: " + rh + "px (native)";
</script>';

// General ----------------------------------------------------------
if (($_SESSION['Scan_Satellite'] == True)) {
		//$_SESSION['local'] = "local";

		$uptime_search  = array('w ', 'd ', 'h ', 'm ');
        $uptime_replace = array(' weeks, ', ' days, ', ' hours, ', ' minutes ');

		global $satellite_badges_list;
    	$database = '../db/pialert.db';
	    $db = new SQLite3($database);
	    $sql_select = 'SELECT * FROM Satellites ORDER BY sat_name ASC';
	    $result = $db->query($sql_select);
	    if ($result) {
	        if ($result->numColumns() > 0) {
	        	$tab_id = 0;
	            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
	            	$tab_id++;
	                $tabs .=  '<li class=""><a href="#tab_'.$tab_id.'" data-toggle="tab" aria-expanded="false">'.$row['sat_name'].'</a></li>';

	                $hostdata = json_decode($row['sat_host_data'], true);
	                $scan_time = explode(" ", $row['sat_lastupdate']);
	                $tab_content .= '<div class="tab-pane" id="tab_'.$tab_id.'">
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Uptime</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . str_replace($uptime_search, $uptime_replace, $hostdata['uptime']) . ' ('. $scan_time[0] . ' / '.substr($scan_time[1], 0, -3).')</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Operating System</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . $hostdata['os_version'] . '</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Kernel Architecture:</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . $hostdata['cpu_arch'] . '</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">CPU Name:</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . $hostdata['cpu_name'] . '</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">CPU Cores:</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . $hostdata['cpu_cores'] . ' @ ' . $hostdata['cpu_freq'] . '</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Memory:</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . round($hostdata['ram_total']/1048576, 2) . ' MB / ' . $hostdata['ram_used_percent'] . '% is used</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Running Processes:</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">' . $hostdata['proc_count'] . '</div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Satellite Host:</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">Name: ' . $hostdata['hostname'] . ' / IP: ' . $hostdata['satellite_ip'] . ' / MAC: <a href="./deviceDetails.php?mac=' . $hostdata['satellite_mac'] . '">' . $hostdata['satellite_mac'] . '</a></div>
											</div>
											<div class="row">
											  <div class="col-sm-3 sysinfo_gerneral_a">Timezone (System):</div>
											  <div class="col-sm-9 sysinfo_gerneral_b">"' . $hostdata['os_timezone'] . '"</div>
											</div>
							            </div>';
	            }
	        }
	    }
	    $db->close();
}
echo '<div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="pull-left header text-aqua" id="sys_info_gen_head"><i class="bi bi-info-circle"></i> General</li>
              <li class="active"><a href="#tab_0" data-toggle="tab" aria-expanded="true">Pi.Alert</a></li>
              '.$tabs.'
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_0">
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Uptime</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['uptime'] . '</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Operating System</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['os_version'] . '</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Kernel Architecture:</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $kernel_arch . '</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">CPU Name:</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['cpu_model'] . '</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">CPU Cores:</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['cpu'] . ' @ ' . $stat['cpu_frequ'] . ' MHz</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Memory:</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['mem_total'] . ' MB / ' . $stat['mem_used'] . '% is used</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Running Processes:</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['process_count'] . '</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Logged in Users:</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">' . $stat['user_count'] . '</div>
				</div>
				<div class="row">
				  <div class="col-sm-3 sysinfo_gerneral_a">Timezone (PHP / System):</div>
				  <div class="col-sm-9 sysinfo_gerneral_b">"'. date_default_timezone_get() .'" / "'. get_local_system_tz() .'"</div>
				</div>
              </div>
              '.$tab_content.'
            </div>
          </div>';


// DB Info ----------------------------------------------------------
echo '<div class="box box-solid">
        <div class="box-header"><h3 class="box-title sysinfo_headline"><i class="bi bi-database"></i> Pi.Alert Database Details</h3></div>
        <div class="box-body">
        	<div style="height: 300px; overflow-y: scroll; overflow-x: hidden;">';

$DB_SOURCE = str_replace('front', 'db', getcwd()) . '/pialert.db';
echo '<p>The directory of the Pi.Alert database is <b>' . $DB_SOURCE . '</b></p>';


$db = new SQLite3('../db/pialert.db');
$tablesQuery = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name ASC");
echo '<table class="table table-bordered table-hover table-striped dataTable no-footer" style="margin-bottom: 10px;">';
echo '<thead>
		<tr role="row">
			<th class="sysinfo_services col-sm-4 col-xs-8" style="padding: 8px;">Table Name</th>
			<th class="sysinfo_services" style="padding: 8px;">Table Entries</th>
		</tr>
	  </thead>';
while ($table = $tablesQuery->fetchArray()) {
    $tableName = $table['name'];
    
    $rowCountQuery = $db->query("SELECT COUNT(*) as count FROM $tableName");
    $rowCount = $rowCountQuery->fetchArray()['count'];

    echo '<tr>
    	<td style="padding: 3px; padding-left: 10px;">' . $tableName . '</td>
    	<td style="padding: 3px; padding-left: 10px;">' . $rowCount . '</td>
    	</tr>';
}
echo '</table>';

$db->close();

echo '		</div>
        </div>
      </div>';

// User Crontab -----------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title sysinfo_headline"><i class="bi bi-list-task"></i> User Crontab</h3>
            </div>
            <div class="box-body">
            <pre style="background-color: transparent; border: none;">'.$stat['usercron'].'</pre>
            </div>
      </div>';

// Pi.Alert Crontab -----------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title sysinfo_headline"><i class="bi bi-list-task"></i> Pi.Alert Crons</h3>
            </div>
            <div class="box-body">
            <table class="table table-bordered table-hover table-striped dataTable no-footer" style="margin-bottom: 10px;">
			<thead>
				<tr role="row">
					<th class="sysinfo_services col-xs-4" style="padding: 8px;">Cron Name</th>
					<th class="sysinfo_services col-xs-4" style="padding: 8px;">Cron</th>
					<th class="sysinfo_services col-xs-4" style="padding: 8px;">Status</th>
				</tr>
	  		</thead>';
function convert_bool_to_status($status) {
	if ($status == True) {return "enabled";} else {return "disabled";}
}
echo '<tr>
		<td style="padding: 3px; padding-left: 10px;">Update Check</td>
		<td style="padding: 3px; padding-left: 10px;">'.$_SESSION['AUTO_UPDATE_CHECK_CRON'].'</td>
		<td style="padding: 3px; padding-left: 10px;">'.convert_bool_to_status($_SESSION['Auto_Update_Check']).'</td>
	  </tr>';
echo '<tr>
		<td style="padding: 3px; padding-left: 10px;">Backup</td>
		<td style="padding: 3px; padding-left: 10px;">'.$_SESSION['AUTO_DB_BACKUP_CRON'].'</td>
		<td style="padding: 3px; padding-left: 10px;">'.convert_bool_to_status($_SESSION['AUTO_DB_BACKUP']).'</td>
	  </tr>';
echo '<tr>
		<td style="padding: 3px; padding-left: 10px;">Speedtest</td>
		<td style="padding: 3px; padding-left: 10px;">'.$_SESSION['SPEEDTEST_TASK_CRON'].'</td>
		<td style="padding: 3px; padding-left: 10px;">'.convert_bool_to_status($_SESSION['SPEEDTEST_TASK_ACTIVE']).'</td>
	  </tr>';
echo '<tr>
		<td style="padding: 3px; padding-left: 10px;">Continuous notifications</td>
		<td style="padding: 3px; padding-left: 10px;">'.$_SESSION['REPORT_NEW_CONTINUOUS_CRON'].'</td>
		<td style="padding: 3px; padding-left: 10px;">'.convert_bool_to_status($_SESSION['REPORT_NEW_CONTINUOUS']).'</td>
	  </tr>';
echo '      </table>
            </div>
      </div>';

// Storage ----------------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title sysinfo_headline"><i class="bi bi-hdd"></i> Storage</h3>
            </div>
            <div class="box-body">';

$storage_lsblk = shell_exec("lsblk -io NAME,SIZE,TYPE,MOUNTPOINT,MODEL --list | tail -n +2 | awk '{print $1\"#\"$2\"#\"$3\"#\"$4\"#\"$5}'");
$storage_lsblk_line = explode("\n", $storage_lsblk);
$storage_lsblk_line = array_filter($storage_lsblk_line);

for ($x = 0; $x < sizeof($storage_lsblk_line); $x++) {
	$temp = array();
	$temp = explode("#", $storage_lsblk_line[$x]);
	$storage_lsblk_line[$x] = $temp;
}

for ($x = 0; $x < sizeof($storage_lsblk_line); $x++) {
	if (strtolower($storage_lsblk_line[$x][2]) != "loop") {
		echo '<div class="row">';
		if (preg_match('~[0-9]+~', $storage_lsblk_line[$x][0])) {
			echo '<div class="col-sm-4 sysinfo_gerneral_a">Mount point "' . $storage_lsblk_line[$x][3] . '"</div>';
		} else {
			echo '<div class="col-sm-4 sysinfo_gerneral_a">"' . str_replace('_', ' ', $storage_lsblk_line[$x][3]) . '"</div>';
		}
		echo '<div class="col-sm-3 sysinfo_gerneral_b">Device: /dev/' . $storage_lsblk_line[$x][0] . '</div>';
		echo '<div class="col-sm-2 sysinfo_gerneral_b">Size: ' . $storage_lsblk_line[$x][1] . '</div>';
		echo '<div class="col-sm-2 sysinfo_gerneral_b">Type: ' . $storage_lsblk_line[$x][2] . '</div>';
		echo '</div>';
	}
}
echo '      </div>
      </div>';

// Storage usage ----------------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title sysinfo_headline"><i class="bi bi-hdd"></i> Storage usage</h3>
            </div>
            <div class="box-body">';
for ($x = 0; $x < sizeof($hdd_devices); $x++) {
	if (stristr($hdd_devices[$x], '/dev/')) {
		if (!stristr($hdd_devices[$x], '/loop')) {
			if ($hdd_devices_total[$x] == 0 || $hdd_devices_total[$x] == '') {$temp_total = 0;} else { $temp_total = number_format(round(($hdd_devices_total[$x] / 1024 / 1024), 2), 2, ',', '.');}
			if ($hdd_devices_used[$x] == 0 || $hdd_devices_used[$x] == '') {$temp_used = 0;} else { $temp_used = number_format(round(($hdd_devices_used[$x] / 1024 / 1024), 2), 2, ',', '.');}
			if ($hdd_devices_free[$x] == 0 || $hdd_devices_free[$x] == '') {$temp_free = 0;} else { $temp_free = number_format(round(($hdd_devices_free[$x] / 1024 / 1024), 2), 2, ',', '.');}
			echo '<div class="row">';
			echo '<div class="col-sm-4 sysinfo_gerneral_a">Mount point "' . $hdd_devices_mount[$x] . '"</div>';
			echo '<div class="col-sm-2 sysinfo_gerneral_b">Total: ' . $temp_total . ' GB</div>';
			echo '<div class="col-sm-3 sysinfo_gerneral_b">Used: ' . $temp_used . ' GB (' . $hdd_devices_percent[$x] . ')</div>';
			echo '<div class="col-sm-2 sysinfo_gerneral_b">Free: ' . $temp_free . ' GB</div>';
			echo '</div>';
		}
	}
}
//echo '<br>' . $pia_lang['SysInfo_storage_note'];
echo '      </div>
      </div>';

// Network ----------------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title sysinfo_headline"><i class="bi bi-hdd-network"></i> Network</h3>
            </div>
            <div class="box-body">';

for ($x = 0; $x < sizeof($net_interfaces); $x++) {
	$interface_name = str_replace(':', '', $net_interfaces[$x]);
	$interface_ip_temp = exec('ip addr show ' . $interface_name . ' | grep "inet "');
	$interface_ip_arr = explode(' ', trim($interface_ip_temp));

	if (!isset($interface_ip_arr[1])) {$interface_ip_arr[1] = '--';}

	if ($net_interfaces_rx[$x] == 0) {$temp_rx = 0;} else { $temp_rx = number_format(round(($net_interfaces_rx[$x] / 1024 / 1024), 2), 2, ',', '.');}
	if ($net_interfaces_tx[$x] == 0) {$temp_tx = 0;} else { $temp_tx = number_format(round(($net_interfaces_tx[$x] / 1024 / 1024), 2), 2, ',', '.');}
	echo '<div class="row">';
	echo '<div class="col-sm-2 sysinfo_network_a">' . $interface_name . '</div>';
	echo '<div class="col-sm-2 sysinfo_network_b">' . $interface_ip_arr[1] . '</div>';
	echo '<div class="col-sm-3 sysinfo_network_b">RX: <div class="sysinfo_network_value">' . $temp_rx . ' MB</div></div>';
	echo '<div class="col-sm-3 sysinfo_network_b">TX: <div class="sysinfo_network_value">' . $temp_tx . ' MB</div></div>';
	echo '</div>';

}
echo '      </div>
      </div>';

// Services ----------------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title sysinfo_headline"><i class="bi bi-database-gear"></i> Services (running)</h3>
            </div>
            <div class="box-body">';
echo '<div style="height: 300px; overflow: scroll;">';
exec('systemctl --type=service --state=running', $running_services);
echo '<table class="table table-bordered table-hover table-striped dataTable no-footer" style="margin-bottom: 10px;">';
echo '<thead>
		<tr role="row">
			<th class="sysinfo_services" style="padding: 8px;">Service Name</th>
			<th class="sysinfo_services" style="padding: 8px;">Service Description</th>
		</tr>
	  </thead>';
for ($x = 0; $x < sizeof($running_services); $x++) {
	if (stristr($running_services[$x], '.service')) {
		$temp_services_arr = array_values(array_filter(explode(' ', trim($running_services[$x]))));
		$servives_name = $temp_services_arr[0];
		unset($temp_services_arr[0], $temp_services_arr[1], $temp_services_arr[2], $temp_services_arr[3]);
		$servives_description = implode(" ", $temp_services_arr);
		echo '<tr><td style="padding: 3px; padding-left: 10px;">' . substr($servives_name, 0, -8) . '</td><td style="padding: 3px; padding-left: 10px;">' . $servives_description . '</td></tr>';
	}
}
echo '</table>';
echo '</div>';
echo '      </div>
      </div>';

// USB ----------------------------------------------------------
echo '<div class="box box-solid">
            <div class="box-header">
               <h3 class="box-title sysinfo_headline"><i class="bi bi-usb-symbol"></i> USB Devices</h3>
            </div>
            <div class="box-body">';
echo '         <table class="table table-bordered table-hover table-striped dataTable no-footer" style="margin-bottom: 10px;">';

sort($usb_devices_mount);
for ($x = 0; $x < sizeof($usb_devices_mount); $x++) {
	$cut_pos = strpos($usb_devices_mount[$x], ':');
	$usb_bus = substr($usb_devices_mount[$x], 0, $cut_pos);
	$usb_dev = substr($usb_devices_mount[$x], $cut_pos + 1);
	echo '<tr><td style="padding: 3px; padding-left: 10px; width: 150px;"><b>' . str_replace('Device', 'Dev.', $usb_bus) . '</b></td><td style="padding: 3px; padding-left: 10px;">' . $usb_dev . '</td></tr>';
}
echo '         </table>';
echo '      </div>
      </div>';
echo '<br>';

?>
    </section>

    <!-- /.content -->
</div>
  <!-- /.content-wrapper -->

<!-- ----------------------------------------------------------------------- -->
<?php
require 'php/templates/footer.php';
?>

<script type="text/javascript">

// Pialert Reboot
function askPialertReboot() {
  showModalWarning('<?=$pia_lang['SysInfo_Reboot_noti_head'];?>', '<?=$pia_lang['SysInfo_Reboot_noti_text'];?>',
    '<?=$pia_lang['Gen_Cancel'];?>', '<?=$pia_lang['Gen_Run'];?>', 'PialertReboot');
}
function PialertReboot() {
	$.get('php/server/commands.php?action=PialertReboot', function(msg) {showMessage (msg);});
}


// Pialert Shutdown
function askPialertShutdown() {
  showModalWarning('<?=$pia_lang['SysInfo_Shutdown_noti_head'];?>', '<?=$pia_lang['SysInfo_Shutdown_noti_text'];?>',
    '<?=$pia_lang['Gen_Cancel'];?>', '<?=$pia_lang['Gen_Run'];?>', 'PialertShutdown');
}
function PialertShutdown() {
	$.get('php/server/commands.php?action=PialertShutdown', function(msg) {showMessage (msg);});
}
</script>