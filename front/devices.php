<!-- ---------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector
#
#  devices.php - Front module. Devices list page
#-------------------------------------------------------------------------------
#  Puche 2021        pi.alert.application@gmail.com        GNU GPLv3
#  leiweibau 2024+                                         GNU GPLv3
#--------------------------------------------------------------------------- -->

<?php
session_start();

if ($_SESSION["login"] != 1) {
	header('Location: ./index.php');
	exit;
}
require 'php/server/db.php';
require 'php/templates/header.php';
require 'php/server/graph.php';
require 'php/server/journal.php';

$DBFILE = '../db/pialert.db';
OpenDB();

function print_box_top_element($title) {
	echo '<div class="row">
        <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">' . $title . '</h3>
            </div>
            <div class="box-body">
              <div>';
}

function print_box_bottom_element() {
	echo '          </div>
                </div>
                <!-- /.box-body -->
              </div>
            </div>
        </div>';
}

// Get Online Graph Arrays
$graph_arrays = array();
$graph_arrays = prepare_graph_arrays_history($SCANSOURCE);
$Graph_Device_Time = $graph_arrays[0];
$Graph_Device_Down = $graph_arrays[1];
$Graph_Device_All = $graph_arrays[2];
$Graph_Device_Online = $graph_arrays[3];
$Graph_Device_Arch = $graph_arrays[4];

?>

<!-- Page ------------------------------------------------------------------ -->
  <div class="content-wrapper">
    <section class="content-header">
    	<?php require 'php/templates/notification.php';?>

<?php
// ################### Start Bulk-Editor #######################################
if ($_REQUEST['mod'] == 'bulkedit') {

	echo '
					<h1 id="pageTitle">' . $pia_lang['Device_Title'] . ' / ' . $_SESSION[$SCANSOURCE] . ' - ' . $pia_lang['Device_bulkEditor_mode'] . '</h1>
          <a href="./devices.php" class="btn btn-success pull-right bulk_editor_quit" role="button">' . $pia_lang['Device_bulkEditor_mode_quit'] . '</a>
        </section>';

	echo '<section class="content">
        <script src="lib/AdminLTE/bower_components/jquery/dist/jquery.min.js"></script>
        <link rel="stylesheet" href="lib/AdminLTE/plugins/iCheck/all.css">';

	if ($_REQUEST['savedata'] == 'yes') {

		$sql_queue = array();

		if ($_REQUEST['en_bulk_owner'] == 'on') {
			$set_bulk_owner = htmlspecialchars($_REQUEST['bulk_owner'], ENT_QUOTES);
			array_push($sql_queue, 'dev_Owner="' . $set_bulk_owner . '"');}
		if ($_REQUEST['en_bulk_type'] == 'on') {
			$set_bulk_type = htmlspecialchars($_REQUEST['bulk_type'], ENT_QUOTES);
			array_push($sql_queue, 'dev_DeviceType="' . $set_bulk_type . '"');}
		if ($_REQUEST['en_bulk_group'] == 'on') {
			$set_bulk_group = htmlspecialchars($_REQUEST['bulk_group'], ENT_QUOTES);
			array_push($sql_queue, 'dev_Group="' . $set_bulk_group . '"');}
		if ($_REQUEST['en_bulk_location'] == 'on') {
			$set_bulk_location = htmlspecialchars($_REQUEST['bulk_location'], ENT_QUOTES);
			array_push($sql_queue, 'dev_Location="' . $set_bulk_location . '"');}
		if ($_REQUEST['en_bulk_comments'] == 'on') {
			$set_bulk_comments = htmlspecialchars($_REQUEST['bulk_comments'], ENT_QUOTES);
			array_push($sql_queue, 'dev_Comments="' . $set_bulk_comments . '"');}
		if ($_REQUEST['en_bulk_connectiontype'] == 'on') {
			$set_bulk_connectiontype = htmlspecialchars($_REQUEST['bulk_connectiontype'], ENT_QUOTES);
			array_push($sql_queue, 'dev_ConnectionType="' . $set_bulk_connectiontype . '"');}
		if ($_REQUEST['en_bulk_linkspeed'] == 'on') {
			$set_bulk_linkspeed = htmlspecialchars($_REQUEST['bulk_linkspeed'], ENT_QUOTES);
			array_push($sql_queue, 'dev_LinkSpeed="' . $set_bulk_linkspeed . '"');}
		if ($_REQUEST['en_bulk_AlertAllEvents'] == 'on') {
			if ($_REQUEST['bulk_AlertAllEvents'] == 'on') {$set_bulk_AlertAllEvents = 1;} else { $set_bulk_AlertAllEvents = 0;}
			array_push($sql_queue, 'dev_AlertEvents="' . $set_bulk_AlertAllEvents . '"');}
		if ($_REQUEST['en_bulk_AlertDown'] == 'on') {
			if ($_REQUEST['bulk_AlertDown'] == 'on') {$set_bulk_AlertDown = 1;} else { $set_bulk_AlertDown = 0;}
			array_push($sql_queue, 'dev_AlertDeviceDown="' . $set_bulk_AlertDown . '"');}
		if ($_REQUEST['en_bulk_NewDevice'] == 'on') {
			if ($_REQUEST['bulk_NewDevice'] == 'on') {$set_bulk_NewDevice = 1;} else { $set_bulk_NewDevice = 0;}
			array_push($sql_queue, 'dev_NewDevice="' . $set_bulk_NewDevice . '"');}
		if ($_REQUEST['en_bulk_Archived'] == 'on') {
			if ($_REQUEST['bulk_Archived'] == 'on') {$set_bulk_Archived = 1;} else { $set_bulk_Archived = 0;}
			array_push($sql_queue, 'dev_Archived="' . $set_bulk_Archived . '"');}
		if ($_REQUEST['en_bulk_PresencePage'] == 'on') {
			if ($_REQUEST['bulk_PresencePage'] == 'on') {$set_bulk_PresencePage = 1;} else { $set_bulk_PresencePage = 0;}
			array_push($sql_queue, 'dev_PresencePage="' . $set_bulk_PresencePage . '"');}

		print_box_top_element($pia_lang['Device_bulkEditor_savebox_title']);
		// Count changed fields
		if (sizeof($sql_queue) < 1) {
			// No fields were selected for modification
			echo '<br>' . $pia_lang['Device_bulkEditor_savebox_noselection'] . '<br>&nbsp;';
		} else {
			// Fields were selected for modification
			echo '<h4>' . $pia_lang['Device_bulkEditor_savebox_mod_devices'] . ':</h4>';
			// Update Segment start
			$sql = 'SELECT dev_Name, dev_MAC FROM Devices ORDER BY dev_Name COLLATE NOCASE ASC';
			$results = $db->query($sql);
			while ($row = $results->fetchArray()) {
				if (isset($_REQUEST[$row['dev_MAC']])) {
					// List modified devices (name)
					$modified_hosts = $modified_hosts . $row['dev_Name'] . '; ';
					// Build sql query and update
					$sql_queue_str = implode(', ', $sql_queue);
					$sql_update = 'UPDATE Devices SET ' . $sql_queue_str . ' WHERE dev_MAC="' . $row['dev_MAC'] . '"';
					$results_update = $db->query($sql_update);
				}
			}
			// output modified hosts
			echo $modified_hosts;
			// List modifications
			echo '<h4>' . $pia_lang['Device_bulkEditor_savebox_mod_fields'] . ':</h4>';
			if (isset($set_bulk_owner)) {echo $pia_lang['DevDetail_MainInfo_Owner'] . ': ' . $set_bulk_owner . '<br>';}
			if (isset($set_bulk_type)) {echo $pia_lang['DevDetail_MainInfo_Type'] . ': ' . $set_bulk_type . '<br>';}
			if (isset($set_bulk_group)) {echo $pia_lang['DevDetail_MainInfo_Group'] . ': ' . $set_bulk_group . '<br>';}
			if (isset($set_bulk_location)) {echo $pia_lang['DevDetail_MainInfo_Location'] . ': ' . $set_bulk_location . '<br>';}
			if (isset($set_bulk_comments)) {echo $pia_lang['DevDetail_MainInfo_Comments'] . ': ' . $set_bulk_comments . '<br>';}
			if (isset($set_bulk_connectiontype)) {echo $pia_lang['DevDetail_MainInfo_Network_ConnectType'] . ': ' . $set_bulk_connectiontype . '<br>';}
			if (isset($set_bulk_linkspeed)) {echo $pia_lang['DevDetail_MainInfo_Network_LinkSpeed'] . ': ' . $set_bulk_linkspeed . '<br>';}
			if (isset($set_bulk_AlertAllEvents)) {echo $pia_lang['DevDetail_EveandAl_AlertAllEvents'] . ': ' . $set_bulk_AlertAllEvents . '<br>';}
			if (isset($set_bulk_AlertDown)) {echo $pia_lang['DevDetail_EveandAl_AlertDown'] . ': ' . $set_bulk_AlertDown . '<br>';}
			if (isset($set_bulk_NewDevice)) {echo $pia_lang['DevDetail_EveandAl_NewDevice'] . ': ' . $set_bulk_NewDevice . '<br>';}
			if (isset($set_bulk_Archived)) {echo $pia_lang['DevDetail_EveandAl_Archived'] . ': ' . $set_bulk_Archived . '<br>';}
			if (isset($set_bulk_PresencePage)) {echo $pia_lang['DevDetail_MainInfo_ShowPresence'] . ': ' . $set_bulk_PresencePage . '<br>';}
			// Update Segment stop
			// Logging
			pialert_logging('a_021', $_SERVER['REMOTE_ADDR'], 'LogStr_0002', '', $modified_hosts);
		}
		echo '<a href="./devices.php?mod=bulkedit&scansource='.$SCANSOURCE.'" class="btn btn-default pull-right" role="button" style="margin-bottom: 10px;">' . $pia_lang['Gen_Close'] . '</a>';
		print_box_bottom_element();
	}
	echo '<form method="post" action="./devices.php?scansource='.$SCANSOURCE.'">
          <input type="hidden" id="mod" name="mod" value="bulkedit">
          <input type="hidden" id="savedata" name="savedata" value="yes">';

	print_box_top_element($pia_lang['Device_bulkEditor_hostbox_title']);
	$sql = 'SELECT dev_Name, dev_MAC, dev_PresentLastScan, dev_Archived, dev_NewDevice, dev_AlertEvents, dev_AlertDeviceDown, dev_PresencePage FROM Devices WHERE dev_ScanSource="'.$SCANSOURCE.'" ORDER BY dev_Name COLLATE NOCASE ASC';
	$results = $db->query($sql);
	while ($row = $results->fetchArray()) {
		if ($row[2] == 1) {$status_border = 'bulked_online_border';} else { $status_border = 'bulked_offline_border';}
		if ($row[3] == 1) {$status_box = 'background-color: #aaa;';} elseif ($row[4] == 1) {$status_box = 'background-color: #b1720c;';} else { $status_box = 'background-color: transparent;';}
		if ($row[5] == 1 && $row[6] == 1) {$status_text_color = 'bulked_checkbox_label_alldown';} elseif ($row[5] == 1) {$status_text_color = 'bulked_checkbox_label_all';} elseif ($row[6] == 1) {$status_text_color = 'bulked_checkbox_label_down';} else { $status_text_color = '';}
		if ($row[7] == 0) {$underline = 'presence-underlined';} else { $underline = '';}
		echo '<div class="bulked_dev_box ' . $status_border . '">
             <div class="bulked_dev_chk_cont" style="' . $status_box . '">
             		<input class="icheckbox_flat-blue hostselection bulked_dev_chkbox" id="' . $row[1] . '" name="' . $row[1] . '" type="checkbox">
             </div>
             <label class="control-label ' . $status_text_color . ' ' . $underline . '" for="' . $row[1] . '">' . $row[0] . '</label>
          </div>';
	}
	// Check/Uncheck All Button
	echo '<button type="button" class="btn btn-warning pull-right" id="bulked_checkall">' . $pia_lang['Device_bulkEditor_selectall'] . '</button>';
	echo '<script>
            var clicked = false;
            $("#bulked_checkall").on("click", function() {
              $(".hostselection").prop("checked", !clicked);
              clicked = !clicked;
              this.innerHTML = clicked ? \'' . $pia_lang['Device_bulkEditor_selectnone'] . '\' : \'' . $pia_lang['Device_bulkEditor_selectall'] . '\';
            });
        </script>';
	print_box_bottom_element();
	print_box_top_element($pia_lang['Device_bulkEditor_inputbox_title']);
	// Inputs
	echo '<table style="margin-bottom:30px; width: 100%">
          <tr>
            <td class="bulked_table_cell_a" style="width: 80px;"><input class="icheckbox_flat-blue" id="en_bulk_owner" name="en_bulk_owner" type="checkbox"></td>
            <td>
                <label for="bulk_owner">' . $pia_lang['DevDetail_MainInfo_Owner'] . ':</label><br>
                <input type="text" class="form-control" id="bulk_owner" name="bulk_owner" style="max-width: 400px;" disabled></td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_type" name="en_bulk_type" type="checkbox"></td>
            <td>
                <label for="bulk_type">' . $pia_lang['DevDetail_MainInfo_Type'] . ':</label><br>
                <div class="input-group" style="max-width: 400px;">
                  <input class="form-control" id="bulk_type" name="bulk_type" type="text" disabled>
                  <div class="input-group-btn">
                    <button type="button" id="bulk_type_selector" name="bulk_type_selector" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="fa fa-caret-down"></span></button>
                    <ul id="dropdownDeviceType" class="dropdown-menu dropdown-menu-right">
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Smartphone\')">   Smartphone   </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Laptop\')">       Laptop       </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'PC\')">           PC           </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Tablet\')">       Tablet       </a></li>
                      <li class="divider"></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Router\')">       Router       </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Switch\')">       Switch       </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Access Point\')"> Access Point </a></li>
                      <li class="divider"></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_type\',\'Others\')">       Others       </a></li>
                    </ul>
                  </div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_group" name="en_bulk_group" type="checkbox"></td>
            <td>
                <label for="bulk_group">' . $pia_lang['DevDetail_MainInfo_Group'] . ':</label><br>
                <div class="input-group" style="max-width: 400px;">
                  <input class="form-control" id="bulk_group" name="bulk_group" type="text" disabled>
                  <div class="input-group-btn">
                    <button type="button" id="bulk_group_selector" name="bulk_group_selector" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="fa fa-caret-down"></span></button>
                    <ul id="dropdownGroup" class="dropdown-menu dropdown-menu-right">
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_group\',\'Always On\')"> Always On </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_group\',\'Friends\')">   Friends   </a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_group\',\'Personal\')">  Personal  </a></li>
                      <li class="divider"></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_group\',\'Others\')">    Others    </a></li>
                    </ul>
                  </div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_location" name="en_bulk_location" type="checkbox"></td>
            <td>
                <label for="bulk_location">' . $pia_lang['DevDetail_MainInfo_Location'] . ':</label><br>
                <div class="input-group" style="max-width: 400px;">
                  <input class="form-control" id="bulk_location" name="bulk_location" type="text" disabled>
                  <div class="input-group-btn">
                    <button type="button" id="bulk_location_selector" name="bulk_location_selector" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="fa fa-caret-down"></span></button>
                    <ul id="dropdownLocation" class="dropdown-menu dropdown-menu-right">
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_location\',\'Bathroom\')">    Bathroom</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_location\',\'Bedroom\')">     Bedroom</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_location\',\'Hall\')">        Hall</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_location\',\'Kitchen\')">     Kitchen</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_location\',\'Living room\')"> Living room</a></li>
                      <li class="divider"></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_location\',\'Others\')">      Others</a></li>
                    </ul>
                  </div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_comments" name="en_bulk_comments" type="checkbox"></td>
            <td>
                <label for="bulk_comments">' . $pia_lang['DevDetail_MainInfo_Comments'] . ':</label><br>
                <textarea class="form-control" rows="3" id="bulk_comments" name="bulk_comments" spellcheck="false" data-gramm="false" style="max-width: 400px;" disabled></textarea></td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_connectiontype" name="en_bulk_connectiontype" type="checkbox"></td>
            <td>
                <label for="bulk_connectiontype">' . $pia_lang['DevDetail_MainInfo_Network_ConnectType'] . ':</label><br>
                <div class="input-group" style="max-width: 400px;">
                  <input class="form-control" id="bulk_connectiontype" name="bulk_connectiontype" type="text" disabled>
                  <div class="input-group-btn">
                    <button type="button" id="bulk_connectiontype_selector" name="bulk_connectiontype_selector" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="fa fa-caret-down"></span></button>
                    <ul id="dropdownLocation" class="dropdown-menu dropdown-menu-right">
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_connectiontype\',\'Ethernet\')">        Ethernet</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_connectiontype\',\'Fibre\')">           Fibre</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_connectiontype\',\'WiFi\')">            WiFi</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_connectiontype\',\'Bluetooth\')">       Bluetooth</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_connectiontype\',\'Virtual Machine\')"> Virtual Machine</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_connectiontype\',\'Container\')">       Container</a></li>
                    </ul>
                  </div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_linkspeed" name="en_bulk_linkspeed" type="checkbox"></td>
            <td>
                <label for="bulk_linkspeed">' . $pia_lang['DevDetail_MainInfo_Network_LinkSpeed'] . ':</label><br>
                <div class="input-group" style="max-width: 400px;">
                  <input class="form-control" id="bulk_linkspeed" name="bulk_linkspeed" type="text" disabled>
                  <div class="input-group-btn">
                    <button type="button" id="bulk_linkspeed_selector" name="bulk_linkspeed_selector" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="fa fa-caret-down"></span></button>
                    <ul id="dropdownLocation" class="dropdown-menu dropdown-menu-right">
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'10 Mbps\')">   10 Mbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'100 Mbps\')"> 100 Mbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'1.0 Gbps\')"> 1.0 Gbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'2.5 Gbps\')"> 2.5 Gbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'10 Gbps\')">   10 Gbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'20 Gbps\')">   20 Gbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'25 Gbps\')">   25 Gbps</a></li>
                      <li><a href="javascript:void(0)" onclick="setTextValue(\'bulk_linkspeed\',\'40 Gbps\')">   40 Gbps</a></li>
                    </ul>
                  </div>
                </div>
            </td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_AlertAllEvents" name="en_bulk_AlertAllEvents" type="checkbox"></td>
            <td>
                <label for="bulk_AlertAllEvents" style="width: 200px;">' . $pia_lang['DevDetail_EveandAl_AlertAllEvents'] . ':</label>
                <input class="icheckbox_flat-blue" id="bulk_AlertAllEvents" name="bulk_AlertAllEvents" type="checkbox" disabled></td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_AlertDown" name="en_bulk_AlertDown" type="checkbox"></td>
            <td>
                <label for="bulk_AlertDown" style="width: 200px;">' . $pia_lang['DevDetail_EveandAl_AlertDown'] . ':</label>
                <input class="icheckbox_flat-blue" id="bulk_AlertDown" name="bulk_AlertDown" type="checkbox" disabled></td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_NewDevice" name="en_bulk_NewDevice" type="checkbox"></td>
            <td>
                <label for="bulk_NewDevice" style="width: 200px;">' . $pia_lang['DevDetail_EveandAl_NewDevice'] . ':</label>
                <input class="icheckbox_flat-blue" id="bulk_NewDevice" name="bulk_NewDevice" type="checkbox" disabled></td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_Archived" name="en_bulk_Archived" type="checkbox"></td>
            <td>
                <label for="bulk_Archived" style="width: 200px;">' . $pia_lang['DevDetail_EveandAl_Archived'] . ':</label>
                <input class="icheckbox_flat-blue" id="bulk_Archived" name="bulk_Archived" type="checkbox" disabled></td>
          </tr>
          <tr>
            <td class="bulked_table_cell_a"><input class="icheckbox_flat-blue" id="en_bulk_PresencePage" name="en_bulk_PresencePage" type="checkbox"></td>
            <td>
                <label for="bulk_PresencePage" style="width: 200px;">' . $pia_lang['DevDetail_MainInfo_ShowPresence'] . ':</label>
                <input class="icheckbox_flat-blue" id="bulk_PresencePage" name="bulk_PresencePage" type="checkbox" disabled></td>
          </tr>
        </table>
        <button type="button" class="btn btn-danger" id="btnBulkDeletion" onclick="askBulkDeletion()" style="min-width: 180px;">' . $pia_lang['Device_bulkDel_button'] . '</button>
        <input class="btn btn-warning pull-right" type="submit" value="' . $pia_lang['Gen_Save'] . '" style="margin-bottom: 10px; min-width: 180px;">';

	// JS to enable/disable inputs. Inputs are delete, when disabled
	echo '<script>
            var bulk_owner = true;
            $("#en_bulk_owner").on("click", function() {
              $("#bulk_owner").val(\'\');
              $("#bulk_owner").prop("disabled", !bulk_owner);
              bulk_owner = !bulk_owner;
            });
            var bulk_type = true;
            $("#en_bulk_type").on("click", function() {
              $("#bulk_type").val(\'\');
              $("#bulk_type").prop("disabled", !bulk_type);
              $("#bulk_type_selector").prop("disabled", !bulk_type);
              bulk_type = !bulk_type;
            });
            var bulk_group = true;
            $("#en_bulk_group").on("click", function() {
              $("#bulk_group").val(\'\');
              $("#bulk_group").prop("disabled", !bulk_group);
              $("#bulk_group_selector").prop("disabled", !bulk_group);
              bulk_group = !bulk_group;
            });
            var bulk_location = true;
            $("#en_bulk_location").on("click", function() {
              $("#bulk_location").val(\'\');
              $("#bulk_location").prop("disabled", !bulk_location);
              $("#bulk_location_selector").prop("disabled", !bulk_location);
              bulk_location = !bulk_location;
            });
            var bulk_comments = true;
            $("#en_bulk_comments").on("click", function() {
              $("#bulk_comments").val(\'\');
              $("#bulk_comments").prop("disabled", !bulk_comments);
              bulk_comments = !bulk_comments;
            });
            var bulk_connectiontype = true;
            $("#en_bulk_connectiontype").on("click", function() {
              $("#bulk_connectiontype").val(\'\');
              $("#bulk_connectiontype").prop("disabled", !bulk_connectiontype);
              $("#bulk_connectiontype_selector").prop("disabled", !bulk_connectiontype);
              bulk_connectiontype = !bulk_connectiontype;
            });
            var bulk_linkspeed = true;
            $("#en_bulk_linkspeed").on("click", function() {
              $("#bulk_linkspeed").val(\'\');
              $("#bulk_linkspeed").prop("disabled", !bulk_linkspeed);
              $("#bulk_linkspeed_selector").prop("disabled", !bulk_linkspeed);
              bulk_linkspeed = !bulk_linkspeed;
            });
            var bulk_AlertAllEvents = true;
            $("#en_bulk_AlertAllEvents").on("click", function() {
              $("#bulk_AlertAllEvents").prop("checked", false);
              $("#bulk_AlertAllEvents").prop("disabled", !bulk_AlertAllEvents);
              bulk_AlertAllEvents = !bulk_AlertAllEvents;
            });
            var bulk_AlertDown = true;
            $("#en_bulk_AlertDown").on("click", function() {
              $("#bulk_AlertDown").prop("checked", false);
              $("#bulk_AlertDown").prop("disabled", !bulk_AlertDown);
              bulk_AlertDown = !bulk_AlertDown;
            });
            var bulk_NewDevice = true;
            $("#en_bulk_NewDevice").on("click", function() {
              $("#bulk_NewDevice").prop("checked", false);
              $("#bulk_NewDevice").prop("disabled", !bulk_NewDevice);
              bulk_NewDevice = !bulk_NewDevice;
            });
            var bulk_Archived = true;
            $("#en_bulk_Archived").on("click", function() {
              $("#bulk_Archived").prop("checked", false);
              $("#bulk_Archived").prop("disabled", !bulk_Archived);
              bulk_Archived = !bulk_Archived;
            });
            var bulk_PresencePage = true;
            $("#en_bulk_PresencePage").on("click", function() {
              $("#bulk_PresencePage").prop("checked", false);
              $("#bulk_PresencePage").prop("disabled", !bulk_PresencePage);
              bulk_PresencePage = !bulk_PresencePage;
            });
            function setTextValue (textElement, textValue) {
              $("#"+textElement).val (textValue);
            }
						function askBulkDeletion() {
						  // Ask
						  showModalWarning(\'' . $pia_lang['Device_bulkDel_info_head'] . '\', \'' . $pia_lang['Device_bulkDel_info_text'] . '\',
						    \'' . $pia_lang['Gen_Cancel'] . '\', \'' . $pia_lang['Gen_Delete'] . '\', \'BulkDeletion\');
						}
						function BulkDeletion()
						{
							const checkboxes = document.querySelectorAll(\'.icheckbox_flat-blue.hostselection:checked\');
							const checkedIds = Array.from(checkboxes).map((checkbox) => checkbox.id);
							const queryParams = new URLSearchParams();
							checkedIds.forEach((id) => queryParams.append(\'hosts[]\', id));
						  // Execute
						  $.get(\'php/server/devices.php?action=BulkDeletion&\' + queryParams.toString(), function(msg) {
						    showMessage (msg);
						  });
						}
        </script>';
	print_box_bottom_element();
	echo '</form>';
	echo '</section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->';

	require 'php/templates/footer.php';
// ################### End Bulk-Editor #########################################
} else {
// ################### Start Device List #######################################
	?>
<!-- Content header--------------------------------------------------------- -->
      <h1 id="pageTitle">
           <?php
           echo $pia_lang['Device_Title'] . ' / ' . $_SESSION[$SCANSOURCE];
           if ($_REQUEST['predefined_filter']) {
           	echo ' ('.$_REQUEST['predefined_filter'].')';
           }
           ?>
      </h1>
    </section>
<!-- Main content ---------------------------------------------------------- -->
    <section class="content">
<!-- top small boxes ------------------------------------------------------- -->
      <div class="row">
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getDevicesList('all');">
          <div class="small-box bg-aqua">
            <div class="inner"><h3 id="devicesAll"> -- </h3><p class="infobox_label"><?=$pia_lang['Device_Shortcut_AllDevices'];?></p></div>
            <div class="icon"><i class="fa fa-laptop text-aqua-40"></i></div>
          </div>
          </a>
        </div>
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getDevicesList('connected');">
          <div class="small-box bg-green">
            <div class="inner"><h3 id="devicesConnected"> -- </h3><p class="infobox_label"><?=$pia_lang['Device_Shortcut_Connected'];?></p></div>
            <div class="icon"><i class="mdi mdi-lan-connect text-green-40"></i></div>
          </div>
          </a>
        </div>
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getDevicesList('favorites');">
          <div class="small-box bg-yellow">
            <div class="inner"><h3 id="devicesFavorites"> -- </h3><p class="infobox_label"><?=$pia_lang['Device_Shortcut_Favorites'];?></p></div>
            <div class="icon"><i class="fa fa-star text-yellow-40"></i></div>
          </div>
          </a>
        </div>
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getDevicesList('new');">
          <div class="small-box bg-yellow">
            <div class="inner"><h3 id="devicesNew"> -- </h3><p class="infobox_label"><?=$pia_lang['Device_Shortcut_NewDevices'];?></p></div>
            <div class="icon"><i class="fa fa-plus text-yellow-40"></i></div>
          </div>
          </a>
        </div>
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getDevicesList('down');">
          <div class="small-box bg-red">
            <div class="inner"><h3 id="devicesDown"> -- </h3><p class="infobox_label"><?=$pia_lang['Device_Shortcut_DownAlerts'];?></p></div>
            <div class="icon"><i class="mdi mdi-lan-disconnect text-red-40"></i></div>
          </div>
          </a>
        </div>
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getDevicesList('archived');">
          <div class="small-box bg-gray top_small_box_gray_text">
            <div class="inner"><h3 id="devicesArchived"> -- </h3><p class="infobox_label"><?=$pia_lang['Device_Shortcut_Archived'];?></p></div>
            <div class="icon"><i class="fa fa-eye-slash text-gray-40"></i></div>
          </div>
          </a>
        </div>
      </div>
<!-- Activity Chart ------------------------------------------------------- -->
<?php
If ($ENABLED_HISTOY_GRAPH !== False) {
		?>
      <div class="row">
          <div class="col-md-12">
          <div class="box" id="clients">
              <div class="box-header with-border">
                <h3 class="box-title"><?=$pia_lang['Device_Shortcut_OnlineChart_a'];?><span class="maxlogage-interval">12</span> <?=$pia_lang['Device_Shortcut_OnlineChart_b'];?></h3>
              </div>
              <div class="box-body">
                <div class="chart">
                  <script src="lib/AdminLTE/bower_components/chart.js/Chart.js"></script>
                  <canvas id="OnlineChart" style="width:100%; height: 150px;  margin-bottom: 15px;"></canvas>
                </div>
              </div>
              <!-- /.box-body -->
            </div>
          </div>
      </div>
      <script src="js/graph_online_history.js"></script>
      <script>
        var online_history_time = [<?php pia_graph_devices_data($Graph_Device_Time);?>];
        var online_history_ondev = [<?php pia_graph_devices_data($Graph_Device_Online);?>];
        var online_history_dodev = [<?php pia_graph_devices_data($Graph_Device_Down);?>];
        var online_history_ardev = [<?php pia_graph_devices_data($Graph_Device_Arch);?>];
        graph_online_history_main(online_history_time, online_history_ondev, online_history_dodev, online_history_ardev);
      </script>

<?php
}
?>
<!-- datatable ------------------------------------------------------------- -->
      <div class="row">
        <div class="col-xs-12">
          <div id="tableDevicesBox" class="box">

            <!-- box-header -->
            <div class="box-header">
              <h3 id="tableDevicesTitle" class="box-title text-gray"><?=$pia_lang['NAV_Devices']?></h3>
              <?php
              # Create or remove custom filters
              if (!$_REQUEST['predefined_filter']) {
              	# no active filter
              	echo '<a href="./devices.php?mod=bulkedit&scansource='.$SCANSOURCE.'" class="btn btn-xs btn-link" role="button" style="display: inline-block; margin-top: -5px; margin-left: 15px;"><i class="fa fa-pencil text-yellow" style="font-size:1.5rem"></i></a>';
              	echo '<a href="#" class="btn btn-xs btn-link" role="button" data-toggle="modal" data-target="#modal-set-predefined-filter" style="display: inline-block; margin-top: -5px; margin-left: 15px;"><i class="fa-solid fa-filter text-green" style="font-size:1.5rem"></i></a>';
              } else {
              	# active filter
              	echo '<a href="#" class="btn btn-xs btn-link" role="button" onclick="askDeleteDeviceFilter()" style="display: inline-block; margin-top: -5px; margin-left: 15px;"><i class="fa-solid fa-filter-circle-xmark text-red" style="font-size:1.5rem"></i></a>';
              	echo '
              	<style>
									.dataTables_wrapper .dataTables_filter {
									float: right;
									text-align: right;
									visibility: hidden;
									}
              	</style>';
              }
							echo '<div class="modal fade" id="modal-set-predefined-filter">
							        <div class="modal-dialog modal-dialog-centered">
							            <div class="modal-content">
							                <div class="modal-header">
							                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
							                    <h4 class="modal-title">'.$pia_lang['Device_predef_table_filter'].'</h4>
							                </div>
							                <div class="modal-body main_logviwer_text_layout">
							                    <div class="main_logviwer_log" style="max-height: 70vh;" id="modal-set-filter-content">
								                    <div style="height: 150px;">
								                      <div class="form-group col-xs-12">
								                        <label class="col-xs-3 control-label">'.$pia_lang['Device_del_table_filtername'].'</label>
								                        <div class="col-xs-9">
								                          <input type="text" class="form-control" id="txtFilterName" placeholder="'.$pia_lang['Device_del_table_filtername_help'].'">
								                        </div>
								                      </div>
								                      <div class="form-group col-xs-12">
								                        <label class="col-xs-3 control-label">'.$pia_lang['Device_del_table_filterstring'].'</label>
								                        <div class="col-xs-9">
								                          <input type="text" class="form-control" id="txtFilterString" placeholder="'.$pia_lang['Device_del_table_filterstring_help'].'">
								                        </div>
								                      </div>
								                      <div class="form-group col-xs-12">
								                        <label class="col-xs-3 control-label">'.$pia_lang['Device_del_table_filtergroup'].'</label>
								                        <div class="col-xs-9">
								                          <input type="text" class="form-control" id="txtFilterGroup" placeholder="'.$pia_lang['Device_del_table_filtergroup_help'].'">
								                        </div>
								                      </div>
								                      <div class="form-group col-xs-12">
								                        <label class="col-xs-12 control-label">'.$pia_lang['Device_del_table_columns'].'</label>
								                        <div class="col-xs-12" style="display: flex;flex-wrap: wrap;">
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterName" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_Name'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterOwner" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_Owner'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterGroup" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_Group'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterLocation" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_Location'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterType" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_Type'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterIP" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_LastIP'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterMac" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_MACaddress'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterVendor" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['DevDetail_MainInfo_Vendor'] .'</label>
							                            </div>
							                            <div class="table_settings_col_box" style="width:180px;">
							                              <input class="checkbox blue" id="chkFilterConnectionType" type="checkbox">
							                              <label class="control-label" style="margin-left: 5px">' . $pia_lang['Device_TableHead_ConnectionType'] .'</label>
							                            </div>
								                        </div>
								                      </div>
								                    </div>
								                    <br>
							                    </div>
							                </div>
					                    <div class="modal-footer">
					                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">'.$pia_lang['Gen_Close'].'</button>
					                        <button type="button" class="btn btn-primary" id="btnFilterSave" onclick="SetDeviceFilter()">'.$pia_lang['Gen_Save'].'</button>
					                    </div>
									        </div>
									    </div>
									 </div>';
              ?>
            </div>
            <div class="box-body table-responsive">
              <table id="tableDevices" class="table table-bordered table-hover table-striped">
                <thead>
                <tr>
<?php
									$file = '../config/setting_devicelist';
										if (file_exists($file)) {
											$get = file_get_contents($file, true);
											$table_config = json_decode($get, true);
										} else {
											$table_config = array('Favorites' => 1, 'Group' => 1, 'Owner' => 1, 'Type' => 1, 'FirstSession' => 1, 'LastSession' => 1, 'LastIP' => 1, 'MACType' => 1, 'MACAddress' => 0, 'Location' => 0, 'ConnectionType' => 0, 'WakeOnLAN' => 0);
										}

									$devlistcol_hide = '';
									if ($table_config['ConnectionType'] == 0) {$devlistcol_hide .= '1, ';}
									if ($table_config['Owner'] == 0) {$devlistcol_hide .= '2, ';}
									if ($table_config['Type'] == 0) {$devlistcol_hide .= '3, ';}
									if ($table_config['Favorites'] == 0) {$devlistcol_hide .= '4, ';}
									if ($table_config['Group'] == 0) {$devlistcol_hide .= '5, ';}
									if ($table_config['Location'] == 0) {$devlistcol_hide .= '6, ';}
									if ($table_config['FirstSession'] == 0) {$devlistcol_hide .= '7, ';}
									if ($table_config['LastSession'] == 0) {$devlistcol_hide .= '8, ';}
									if ($table_config['LastIP'] == 0) {$devlistcol_hide .= '9, ';}
									if ($table_config['MACType'] == 0) {$devlistcol_hide .= '10, ';}
									if ($table_config['MACAddress'] == 0) {$devlistcol_hide .= '11, ';}
									if ($table_config['MACVendor'] == 0) {$devlistcol_hide .= '12, ';}
									if ($table_config['WakeOnLAN'] == 0) {$devlistcol_hide .= '17, ';}
?>
                  <th><?=$pia_lang['Device_TableHead_Name'];?></th> 
                  <th><?=$pia_lang['Device_TableHead_ConnectionType'];?></th>
                  <th><?=$pia_lang['Device_TableHead_Owner'];?></th>
                  <th><?=$pia_lang['Device_TableHead_Type'];?></th>
                  <th><?=$pia_lang['Device_TableHead_Favorite'];?></th>
                  <th><?=$pia_lang['Device_TableHead_Group'];?></th>
                  <th><?=$pia_lang['Device_TableHead_Location'];?></th>
                  <th style="white-space: nowrap;"><?=$pia_lang['Device_TableHead_FirstSession'];?></th>
                  <th style="white-space: nowrap;"><?=$pia_lang['Device_TableHead_LastSession'];?></th>
                  <th style="white-space: nowrap;"><?=$pia_lang['Device_TableHead_LastIP'];?></th>
                  <th><?=$pia_lang['Device_TableHead_MAC'];?></th>
                  <th style="white-space: nowrap;"><?=$pia_lang['Device_TableHead_MACaddress'];?></th>
                  <th><?=$pia_lang['DevDetail_MainInfo_Vendor'];?></th>
                  <th><?=$pia_lang['Device_TableHead_Status'];?></th>
                  <th><?=$pia_lang['Device_TableHead_LastIPOrder'];?></th>
                  <th>ScanSource</th>
                  <th><?=$pia_lang['Device_TableHead_Rowid'];?></th>
                  <th><?=$pia_lang['Device_TableHead_WakeOnLAN'];?></th>
                </tr>
                </thead>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

<!-- ----------------------------------------------------------------------- -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<?php
require 'php/templates/footer.php';
?>

<!-- Datatable -->
<link rel="stylesheet" href="lib/AdminLTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
<script src="lib/AdminLTE/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="lib/AdminLTE/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

<!-- iCheck -->
<link rel="stylesheet" href="lib/AdminLTE/plugins/iCheck/all.css">
<script src="lib/AdminLTE/plugins/iCheck/icheck.min.js"></script>

<!-- page script ----------------------------------------------------------- -->
<script>
  var deviceStatus    = 'all';
  var parTableRows    = 'Front_Devices_Rows';
  var parTableOrder   = 'Front_Devices_Order';
  var tableRows       = 10;
  var tableOrder      = [[3,'desc'], [0,'asc']];

  // Read parameters & Initialize components
  main();

// -----------------------------------------------------------------------------
function main () {
  // get parameter value
  $.get('php/server/parameters.php?action=get&parameter='+ parTableRows, function(data) {
    var result = JSON.parse(data);
    if (Number.isInteger (result) ) {
        tableRows = result;
    }

    // get parameter value
    $.get('php/server/parameters.php?action=get&parameter='+ parTableOrder, function(data) {
      var result = JSON.parse(data);
      result = JSON.parse(result);
      if (Array.isArray (result) ) {
        tableOrder = result;
      }
      // Initialize components with parameters
      initializeDatatable();
      // query data
      getDevicesTotals();
      getDevicesList (deviceStatus);
     });
   });
  initializeiCheck();
}

// -----------------------------------------------------------------------------
function initializeDatatable () {
  var table=
  $('#tableDevices').DataTable({
    'paging'       : true,
    'lengthChange' : true,
    'lengthMenu'   : [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, '<?=$pia_lang['Device_Tablelenght_all'];?>']],
    'searching'    : true,
    'oSearch'      : {'sSearch': '<?=$_REQUEST['predefined_filter'];?>'},

    'ordering'     : true,
    'info'         : true,
    'autoWidth'    : false,

    // Parameters
    'pageLength'   : tableRows,
    'order'        : tableOrder,
    // 'order'       : [[3,'desc'], [0,'asc']],

    'columnDefs'   : [
      {visible:   false,         targets: [<?=$devlistcol_hide;?>14, 15, 16] },
      {className: 'text-center', targets: [4, 9, 10, 11, 13, 17] },
      {width:     '100px',       targets: [7, 8] },
      {width:     '30px',        targets: [10] },
      {width:     '0px',         targets: [13] },
      {width:     '20px',         targets: [17] },
      {orderData: [14],          targets: [9] },
      { "targets": [<?=$_REQUEST['filter_fields'];?>], "searchable": false },

      // Device Name
      {targets: [0],
        'createdCell': function (td, cellData, rowData, row, col) {
          switch (rowData[13]) {
            case 'Down':      color='red';                 break;
            case 'NewON':     color='orange';              break;
            case 'NewOFF':    color='orange';              break;
            case 'OnlineV':   color='#00A000';             break;
            case 'On-line':   color='#00A000';             break;
            case 'Off-line':  color='transparent';         break;
            default:          color='transparent';         break;
          };
        	if (rowData[11] == "Internet") {
        		$(td).html ('<b><a href="deviceDetails.php?mac='+ rowData[11] +'" class="text-danger">'+ cellData +'</a></b>');
        	} else {
            $(td).html ('<b><a href="deviceDetails.php?mac='+ rowData[11] +'" class="">'+ cellData +'</a></b>');
        	}

          let tableWidth = $("#tableDevices").outerWidth();
          let viewportWidth = $(window).width() - 50;

          if (tableWidth > viewportWidth) {
              $(td).css({
                  "border-left": `2px solid ${color}`,
                  "padding-left": "8px"
              });
          } else {
          	  $(td).css({
          	  	  "border-left": "",
          	  	  "padding-left": ""
          	  });
          }
      } },
      // Favorite
      {targets: [4],
        'createdCell': function (td, cellData, rowData, row, col) {
          if (cellData == 1){
            $(td).html ('<i class="fa fa-star text-yellow" style="font-size:16px"></i>');
          } else {
            $(td).html ('');
          }
      } },
      // Dates
      {targets: [7, 8],
        'createdCell': function (td, cellData, rowData, row, col) {
          $(td).html (translateHTMLcodes (cellData));
      } },
      // Random MAC
      {targets: [10],
        'createdCell': function (td, cellData, rowData, row, col) {
          if (cellData == 1){
            $(td).html ('<i data-toggle="tooltip" data-placement="right" title="Random MAC" style="font-size: 16px;" class="text-yellow glyphicon glyphicon-random"></i>');
          } else {
            $(td).html ('');
          }
      } },
      //MAC-Address
      {targets: [11],
        'createdCell': function (td, cellData, rowData, row, col) {
            $(td).html (rowData[11]);
      } },
      // Status color
      {targets: [13],
        'createdCell': function (td, cellData, rowData, row, col) {
          switch (rowData[13]) {
            case 'Down':      color='red';                 statusname='Down';                          break;
            case 'NewON':     color='grad-green-yellow';   statusname='&nbsp;&nbsp;New&nbsp;&nbsp;';   break;
            case 'NewOFF':    color='grad-gray-yellow';    statusname='&nbsp;&nbsp;New&nbsp;&nbsp;';   break;
            case 'OnlineV':   color='green';               statusname='Online*';                       break;
            case 'On-line':   color='green';               statusname='Online';                        break;
            case 'Off-line':  color='gray text-white';     statusname='Offline';                       break;
            case 'Archived':  color='gray text-white';     statusname='Archived';                      break;
            default:          color='aqua';                statusname=''; 					                   break;
          };
          $(td).html ('<a href="deviceDetails.php?mac='+ rowData[11] +'" class="badge bg-'+ color +'">'+ statusname +'</a>');
      } },
      // WakeonLAN
      {targets: -1, // last column
         data : null,
         orderable: false,
         "render": function (data, type, row, meta) {
         	 // Deactivation of WoL buttons for devices where it probably makes no sense
         	 var includeValues = ["Mini PC", "Server", "Laptop", "NAS", "PC"];

         	 if (includeValues.indexOf(row[3]) !== -1 && row[11] !== "Internet") {
              return '<a href="#" onclick="askwakeonlan(\'' + row[11] + '\',\'' + row[9] + '\', \'' + row[0] + '\')"><i class="fa-solid fa-power-off text-red"></i></a>';
           } else {
           	return '';
           }
         }
      },
    ],

    // Processing
    'processing'  : true,
    'language'    : {
      processing: '<table> <td width="130px" align="middle">Loading...</td><td><i class="ion ion-ios-sync fa-spin fa-2x fa-fw"></td> </table>',
      emptyTable: 'No data',
      "lengthMenu": "<?=$pia_lang['Device_Tablelenght'];?>",
      "search":     "<?=$pia_lang['Device_Searchbox'];?>: ",
      "paginate": {
          "next":       "<?=$pia_lang['Device_Table_nav_next'];?>",
          "previous":   "<?=$pia_lang['Device_Table_nav_prev'];?>"
      },
      "info":           "<?=$pia_lang['Device_Table_info'];?>",
    }
  });

  // Save cookie Rows displayed, and Parameters rows & order
  $('#tableDevices').on( 'length.dt', function ( e, settings, len ) {
    setParameter (parTableRows, len);
  } );

  $('#tableDevices').on( 'order.dt', function () {
    setParameter (parTableOrder, JSON.stringify (table.order()) );
    setCookie ('devicesList',JSON.stringify (table.column(16, { 'search': 'applied' }).data().toArray()) );
  } );

  $('#tableDevices').on( 'search.dt', function () {
    setCookie ('devicesList', JSON.stringify (table.column(16, { 'search': 'applied' }).data().toArray()) );
  } );

};

// -----------------------------------------------------------------------------
function getDevicesTotals() {
  // stop timer
  stopTimerRefreshData();

  // get totals and put in boxes
  $.get('php/server/devices.php?action=getDevicesTotals&scansource=<?=$SCANSOURCE?>', function(data) {
    var totalsDevices = JSON.parse(data);

    $('#devicesAll').html        (totalsDevices[0].toLocaleString());
    $('#devicesConnected').html  (totalsDevices[1].toLocaleString());
    $('#devicesFavorites').html  (totalsDevices[2].toLocaleString());
    $('#devicesNew').html        (totalsDevices[3].toLocaleString());
    $('#devicesDown').html       (totalsDevices[4].toLocaleString());
    $('#devicesArchived').html   (totalsDevices[5].toLocaleString());

    // Timer for refresh data
    newTimerRefreshData (getDevicesTotals);
  } );
}

// -----------------------------------------------------------------------------
function getDevicesList (status) {
  // Save status selected
  deviceStatus = status;

  // Define color & title for the status selected
  switch (deviceStatus) {
    case 'all':        tableTitle = '<?=$pia_lang['Device_Shortcut_AllDevices']?>';  color = 'aqua';    break;
    case 'connected':  tableTitle = '<?=$pia_lang['Device_Shortcut_Connected']?>';   color = 'green';   break;
    case 'favorites':  tableTitle = '<?=$pia_lang['Device_Shortcut_Favorites']?>';   color = 'yellow';  break;
    case 'new':        tableTitle = '<?=$pia_lang['Device_Shortcut_NewDevices']?>';  color = 'yellow';  break;
    case 'down':       tableTitle = '<?=$pia_lang['Device_Shortcut_DownAlerts']?>';  color = 'red';     break;
    case 'archived':   tableTitle = '<?=$pia_lang['Device_Shortcut_Archived']?>';    color = 'gray';    break;
    default:           tableTitle = '<?=$pia_lang['Device_Shortcut_Devices']?>';     color = 'gray';    break;
  }

  // Set title and color
  $('#tableDevicesTitle')[0].className = 'box-title text-'+ color;
  $('#tableDevicesBox')[0].className = 'box box-'+ color;
  $('#tableDevicesTitle').html (tableTitle);

  // Define new datasource URL and reload
  $('#tableDevices').DataTable().ajax.url(
    'php/server/devices.php?action=getDevicesList&scansource=<?=$SCANSOURCE?>&status=' + deviceStatus).load();
};

// WakeonLAN
function askwakeonlan(fmac,fip,fname) {
  window.global_fmac = fmac;
  window.global_fip = fip;
  showModalWarning('<?=$pia_lang['DevDetail_Tools_WOL_noti'];?> (<span class="text-red">' + fname + '</span>)', '<?=$pia_lang['DevDetail_Tools_WOL_noti_text'];?>',
    '<?=$pia_lang['Gen_Cancel'];?>', '<?=$pia_lang['Gen_Run'];?>', 'wakeonlan');
}
function wakeonlan() {
  var fmac = window.global_fmac;
  var fip = window.global_fip;
  $.get('php/server/devices.php?action=wakeonlan&'
    + '&mac='         + fmac
    + '&ip='          + fip
    , function(msg) {
    showMessage (msg);
  });
}

// Remove Device List Column
function askDeleteDeviceFilter() {
  showModalWarning('<?=$pia_lang['Device_del_table_filter_noti'];?>', '<?=$pia_lang['Device_del_table_filter_noti_text'];?>',
    '<?=$pia_lang['Gen_Cancel'];?>', '<?=$pia_lang['Gen_Delete'];?>', 'DeleteDeviceFilter');
}
function DeleteDeviceFilter() {
    $.get('php/server/devices.php?action=DeleteDeviceFilter&filterstring=<?=$_REQUEST['predefined_filter'];?>'
    , function(msg) {
    showMessage (msg);
  });
}

// Set Device Filter
function SetDeviceFilter() {
    $.get('php/server/devices.php?action=SetDeviceFilter&'
    + '&filtername='    + $('#txtFilterName').val()
    + '&filterstring='  + $('#txtFilterString').val()
    + '&filtergroup='   + $('#txtFilterGroup').val()
    + '&fname='         + ($('#chkFilterName')[0].checked * 1)
    + '&fowner='        + ($('#chkFilterOwner')[0].checked * 1)
    + '&fgroup='        + ($('#chkFilterGroup')[0].checked * 1)
    + '&flocation='     + ($('#chkFilterLocation')[0].checked * 1)
    + '&ftype='         + ($('#chkFilterType')[0].checked * 1)
    + '&fip='           + ($('#chkFilterIP')[0].checked * 1)
    + '&fmac='          + ($('#chkFilterMac')[0].checked * 1)
    + '&fvendor='       + ($('#chkFilterVendor')[0].checked * 1)
    + '&fconnectiont='  + ($('#chkFilterConnectionType')[0].checked * 1)
    , function(msg) {
    showMessage (msg);
  });
}
// Copy Filter from Searchbox
$('#modal-set-predefined-filter').on('shown.bs.modal', function () {
 		var tableDevicesFilter = $("#tableDevices_filter .form-control.input-sm").val();
    if (tableDevicesFilter.length > 0) {
        $("#txtFilterString").val(tableDevicesFilter);
    }
});

</script>

<?php
}
// ################### End Device List #########################################
?>
