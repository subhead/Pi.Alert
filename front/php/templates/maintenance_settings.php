        <div class="tab-pane <?=$pia_tab_setting;?>" id="tab_Settings">
            <table class="table_settings">
                <tr><td colspan="2"><h4 class="bottom-border-aqua"><?=$pia_lang['MT_Tools_Tab_Subheadline_d'];?></h4></td></tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell" colspan="2" style="padding-bottom: 20px;">
                        <div style="display: flex; justify-content: center; flex-wrap: wrap;">
<!-- Toggle Main Scan ----------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                	<?php $state = convert_state_action($_SESSION['Scan_MainScan'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button" id="btnEnableMainScanMon" onclick="askEnableMainScan()"><?=$pia_lang['MT_Tool_mainscan'] . '<br>' . $state;?></button>
                                </div>
                            </div>
<!-- Toggle Web Service Monitoring ---------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                	<?php $state = convert_state_action($_SESSION['Scan_WebServices'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button" id="btnEnableWebServiceMon" onclick="askEnableWebServiceMon()"><?=$pia_lang['MT_Tool_webservicemon'] . '<br>' . $state;?></button>
                                </div>
                            </div>
<!-- Toggle ICMP Monitoring ----------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                	<?php $state = convert_state_action($_SESSION['ICMPScan'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button" id="btnEnableICMPMon" onclick="askEnableICMPMon()"><?=$pia_lang['MT_Tool_icmpmon'] . '<br>' . $state;?></button>
                                </div>
                            </div>
<!-- Toggle Satellites ----------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <?php $state = convert_state_action($_SESSION['Scan_Satellite'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button" id="btnEnableSatellites" onclick="askEnableSatelliteScan()"><?=$pia_lang['MT_Tool_satellites'] . '<br>' . $state;?></button>
                                </div>
                            </div>
                        </div>
                    </td>
				</tr>
                <tr><td colspan="2"><h4 class="bottom-border-aqua"><?=$pia_lang['MT_Tools_Tab_Subheadline_g'];?></h4></td></tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell" colspan="2" style="padding-bottom: 20px;">
                        <div style="display: flex; justify-content: center; flex-wrap: wrap;">
<!-- Toggle Fritzbox ----------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <?php $state = convert_state_action($_SESSION['FRITZBOX_ACTIVE'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button-sm" id="btnToggleFB" onclick="askToggleImport('FB',<?=$_SESSION['FRITZBOX_ACTIVE'];?>)"><?=$pia_lang['MT_Tggl_Import_FB'] . '<br>' . $state;?></button>
                                </div>
                            </div>
<!-- Toggle Mikrotik ---------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <?php $state = convert_state_action($_SESSION['MIKROTIK_ACTIVE'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button-sm" id="btnToggleMT" onclick="askToggleImport('MT',<?=$_SESSION['MIKROTIK_ACTIVE'];?>)"><?=$pia_lang['MT_Tggl_Import_MT'] . '<br>' . $state;?></button>
                                </div>
                            </div>
<!-- Toggle Unifi ----------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <?php $state = convert_state_action($_SESSION['UNIFI_ACTIVE'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button-sm" id="btnToggleUF" onclick="askToggleImport('UF',<?=$_SESSION['UNIFI_ACTIVE'];?>)"><?=$pia_lang['MT_Tggl_Import_UF'] . '<br>' . $state;?></button>
                                </div>
                            </div>
<!-- Toggle Openwrt ----------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <?php $state = convert_state_action($_SESSION['OPENWRT_ACTIVE'], 1);?>
                                    <button type="button" class="btn btn-default dbtools-button-sm" id="btnToggleOW" onclick="askToggleImport('OW',<?=$_SESSION['OPENWRT_ACTIVE'];?>)"><?=$pia_lang['MT_Tggl_Import_OW'] . '<br>' . $state;?></button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr><td colspan="2"><h4 class="bottom-border-aqua"><?=$pia_lang['MT_Tools_Tab_Subheadline_c'];?></h4></td></tr>
<!-- API Key -------------------------------------------------------------- -->
                <tr class="table_settings_row">
                    <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn btn-default dbtools-button" id="btnSetAPIKey" onclick="askSetAPIKey()"><?=$pia_lang['MT_Tool_setapikey'];?></button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b"><?=$pia_lang['MT_Tool_setapikey_text'];?></td>
                </tr>
<!-- Test Notification ---------------------------------------------------- -->
                <tr class="table_settings_row">
                    <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn btn-default dbtools-button" id="btnTestNotific" onclick="askTestNotificationSystem()"><?=$pia_lang['MT_Tool_test_notification'];?></button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b"><?=$pia_lang['MT_Tool_test_notification_text'];?></td>
                </tr>
<!-- Pause Scan ----------------------------------------------------------- -->
                <tr class="table_settings_row">
                    <td class="db_info_table_cell db_tools_table_cell_a">
                        <div style="display: inline-block; text-align: center;">
                              <div class="form-group" style="width:160px; margin-bottom:5px;">
                                <!-- <div class="col-sm-7"> -->
                                  <div class="input-group">
                                    <input class="form-control" id="txtPiaArpTimer" type="text" value="<?=$pia_lang['MT_arpscantimer_empty'];?>" readonly >
                                    <div class="input-group-btn">
                                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" id="dropdownButtonPiaArpTimer">
                                        <span class="fa fa-caret-down"></span></button>
                                      <ul id="dropdownPiaArpTimer" class="dropdown-menu dropdown-menu-right">
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','15');">15min</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','30');">30min</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','60');">1h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','120');">2h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','720');">12h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','1440');">24h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','999999');">Very long</a></li>
                                      </ul>
                                    </div>
                                  </div>
                              </div>
                            </div>
                            <div style="display: block;">
                            <button type="button" class="btn btn-warning" style="margin-top:0px; width:160px; height:36px" id="btnSavePiaArpTimer" onclick="setPiAlertArpTimer()" ><div id="Timeralertspinner" class="loader disablespinner"></div>
                                <div id="TimeralertText" class=""><?=$pia_lang['MT_Tool_arpscansw'];?></div></button>
                            </div>
                        </div>
                    </td>
                    <td class="db_info_table_cell db_tools_table_cell_b text-danger"><?=$pia_lang['MT_Tool_arpscansw_text'];?></td>
                </tr>
                <tr class="table_settings_row">
<?php
if (strtolower($_SESSION['WebProtection']) != 'true') {
	echo '          <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn btn-default dbtools-button" id="btnPiAlertLoginEnable" onclick="askPiAlertLoginEnable()">' . $pia_lang['MT_Tool_loginenable'] . '</button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b">' . $pia_lang['MT_Tool_loginenable_text'] . '</td>';} else {
	echo '      <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn btn-danger dbtools-button" id="btnPiAlertLoginDisable" onclick="askPiAlertLoginDisable()">' . $pia_lang['MT_Tool_logindisable'] . '</button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b text-danger">' . $pia_lang['MT_Tool_logindisable_text'] . '</td>';}
?>
                </tr>
                <tr><td colspan="2"><h4 class="bottom-border-aqua">Advanced</h4></td></tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell" colspan="2" style="padding-bottom: 20px;">
                        <div style="display: flex; justify-content: center; flex-wrap: wrap;">
<!-- SelfCheck JSON ----------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <a href="./php/debugging/test_json_calls.php">
                                        <button type="button" class="btn btn-default dbtools-button">Test Main JSON Calls</button>
                                    </a>
                                </div>
                            </div>

<!-- Raw Devices table ----------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <a href="./php/debugging/test_main_tables_rawcontent.php">
                                        <button type="button" class="btn btn-default dbtools-button">Show raw Device Tables</button>
                                    </a>
                                </div>
                            </div>

<!-- Language Array ----------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <a href="./php/debugging/validate_languages.php">
                                        <button type="button" class="btn btn-default dbtools-button">Compare Language Arrays</button>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
            </table>
        </div>