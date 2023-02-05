<!-- ---------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector 
#
#  deviceDetails.php - Front module. Device management page
#-------------------------------------------------------------------------------
#  Puche 2021        pi.alert.application@gmail.com        GNU GPLv3
#--------------------------------------------------------------------------- -->

<?php
session_start();

if ($_SESSION["login"] != 1)
  {
      header('Location: /pialert/index.php');
      exit;
  }

require 'php/templates/header.php';
require 'php/server/db.php';

  $service_details_title = $_REQUEST['url'];

$db_file = '../db/pialert.db';

function get_service_details($service_URL) {
    global $db_file;
    $db = new SQLite3($db_file);
    $mon_res = $db->query('SELECT * FROM Services WHERE mon_URL="'.$service_URL.'"');
    $row = $mon_res->fetchArray();
    $db->close();
    return $row;
}

$servicedetails = get_service_details($service_details_title);

?>

<!-- Page ------------------------------------------------------------------ -->
  <div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
      <?php require 'php/templates/notification.php'; ?>

      <h1 id="pageTitle">
        <?php echo $service_details_title;?>
      </h1>
    </section>
    
<!-- Main content ---------------------------------------------------------- -->
    <section class="content">

<!-- top small box --------------------------------------------------------- -->
      <div class="row">

        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getEventsTotalsforService('all');">
            <div class="small-box bg-aqua">
              <div class="inner"> <h3 id="eventsAll"> -- </h3>
                <p class="infobox_label"><?php echo $pia_lang['WebServices_Events_Shortcut_All'];?></p>
              </div>
              <div class="icon"> <i class="fa fa-bolt text-aqua-40"></i> </div>
            </div>
          </a>
        </div>

<!-- top small box --------------------------------------------------------- -->
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getEventsTotalsforService('2');">
            <div class="small-box bg-green">
              <div class="inner"> <h3 id="events2xx"> -- </h3>
                <p class="infobox_label"><?php echo $pia_lang['WebServices_Events_Shortcut_HTTP2xx'];?></p>
              </div>
              <div class="icon"> <i class="bi bi-check2-square text-green-40"></i> </div>
            </div>
          </a>
        </div>

<!-- top small box --------------------------------------------------------- -->
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getEventsTotalsforService('3');">
            <div  class="small-box bg-yellow">
              <div class="inner"> <h3 id="events3xx"> -- </h3>
                <p class="infobox_label"><?php echo $pia_lang['WebServices_Events_Shortcut_HTTP3xx'];?></p>
              </div>
              <div class="icon"> <i class="bi bi-sign-turn-right text-yellow-40"></i> </div>
            </div>
          </a>
        </div>

<!-- top small box --------------------------------------------------------- -->
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getEventsTotalsforService('4');">
            <div  class="small-box bg-yellow">
              <div class="inner"> <h3 id="events4xx"> -- </h3>
                <p class="infobox_label"><?php echo $pia_lang['WebServices_Events_Shortcut_HTTP4xx'];?></p>
              </div>
              <div class="icon"> <i class="bi bi-exclamation-square text-yellow-40"></i> </div>
            </div>
          </a>
        </div>

<!-- top small box --------------------------------------------------------- -->
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getEventsTotalsforService('5');">
            <div  class="small-box bg-yellow">
              <div class="inner"> <h3 id="events5xx"> -- </h3>
                <p class="infobox_label"><?php echo $pia_lang['WebServices_Events_Shortcut_HTTP5xx'];?></p>
              </div>
              <div class="icon"> <i class="bi bi-database-x text-yellow-40"></i> </div>
            </div>
          </a>
        </div>

<!-- top small box --------------------------------------------------------- -->
        <div class="col-lg-2 col-sm-4 col-xs-6">
          <a href="#" onclick="javascript: getEventsTotalsforService('99999999');">
            <div  class="small-box bg-red">
              <div class="inner"> <h3 id="eventsDown"> -- </h3>
                <p class="infobox_label"><?php echo $pia_lang['WebServices_Events_Shortcut_Down'];?></p>
              </div>
              <div class="icon"> <i class="bi bi-exclamation-diamond-fill text-red-40"></i> </div>
            </div>
          </a>
        </div>

      </div>
      <!-- /.row -->

<!-- tab control------------------------------------------------------------ -->
      <div class="row">
        <div class="col-lg-12 col-sm-12 col-xs-12">
        <!-- <div class="box-transparent"> -->


          <div id="navDevice" class="nav-tabs-custom">
            <ul class="nav nav-tabs" style="fon t-size:16px;">
              <li> <a id="tabDetails"  href="#panDetails"  data-toggle="tab"> <?php echo $pia_lang['DevDetail_Tab_Details'];?>  </a></li>
              <li> <a id="tabEvents"   href="#panEvents"   data-toggle="tab"> <?php echo $pia_lang['DevDetail_Tab_Events'];?>   </a></li>


            </ul>



            <div class="tab-content" style="min-height: 430px;">

<!-- tab page 1 ------------------------------------------------------------ -->
<!--
              <div class="tab-pane fade in active" id="panDetails">
-->
              <div class="tab-pane fade" id="panDetails">

                <div class="row">
    <!-- column 1 -->
                  <div class="col-sm-6 col-xs-12">
                    <h4 class="bottom-border-aqua"><?php echo $pia_lang['DevDetail_MainInfo_Title'];?></h4>
                    <div class="box-body form-horizontal">

                      <!-- URL -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'URL';?></label>
                        <div class="col-sm-9">
                          <input class="form-control" id="txtURL" type="text" value="<?php echo $servicedetails['mon_URL']?>">
                        </div>
                      </div>
      
                      <!-- Tags -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'Tag';?></label>
                        <div class="col-sm-9">
                          <input class="form-control" id="txtTags" type="text" value="<?php echo $servicedetails['mon_Tags']?>">
                        </div>
                      </div>

                      <!-- MAC -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'Gerät';?></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="txtMAC">

<?php
if ($servicedetails['mon_MAC'] != "") {
    echo '<option value="'.$servicedetails['mon_MAC'].'">'.$servicedetails['mon_MAC'].'</option>';
}

echo '<option value=""> -----  </option>';

global $db_file;
$db = new SQLite3($db_file);
$dev_res = $db->query('SELECT dev_MAC, dev_Name FROM Devices');
$code_array = array();
while ($row = $dev_res->fetchArray()) {
    echo  '<option value="'.$row['dev_MAC'].'">'.$row['dev_Name'].'</option>';
}
?>
                            </select>
                        </div>
                      </div>

                    </div>         
                  </div>


    <!-- column 2 -->
                  <div class="col-sm-6 col-xs-12">
                    <h4 class="bottom-border-aqua"><?php echo $pia_lang['DevDetail_EveandAl_Title'];?></h4>
                    <div class="box-body form-horizontal">

                      <!-- Last IP -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'Last IP';?></label>
                        <div class="col-sm-9">
                          <input class="form-control" id="txtLast" type="text" readonly value="<?php echo $servicedetails['mon_TargetIP']?>">
                        </div>
                      </div>

                      <!-- Last Scan -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'Last Scan';?></label>
                        <div class="col-sm-9">
                          <input class="form-control" id="txtLastScan" type="text" readonly value="<?php echo $servicedetails['mon_LastScan']?>">
                        </div>
                      </div>

                      <!-- Last Latency -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'Last Response Time';?></label>
                        <div class="col-sm-9">
                          <input class="form-control" id="txtLastScan" type="text" readonly value="<?php echo $servicedetails['mon_LastLatency']?>">
                        </div>
                      </div>

                      <!-- Alert events -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'All Events';?></label>
                        <div class="col-sm-9" style="padding-top:6px;">
                          <input class="checkbox blue" id="chkAlertEvents" <?php if($servicedetails['mon_AlertEvents'] == 1) {echo 'checked';}?> type="checkbox">
                        </div>
                      </div>
      
                      <!-- Alert Down -->
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo 'Down';?></label>
                        <div class="col-sm-9" style="padding-top:6px;">
                          <input class="checkbox red" id="chkAlertDown" <?php if($servicedetails['mon_AlertDown'] == 1) {echo 'checked';}?> type="checkbox">
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- Buttons -->
                  <div class="col-xs-12">
                    <div class="pull-right">
                        <!-- <button type="button" class="btn btn-default pa-btn pa-btn-delete"  style="margin-left:0px;" -->
                        <button type="button" class="btn btn-danger"  style="margin-left:6px; margin-top:6px;"
                          id="btnDelete"   onclick="askDeleteDevice()">   <?php echo 'WebService löschen';?> </button>
                        <!-- <button type="button" class="btn btn-default pa-btn" style="margin-left:6px;"  -->
                        <button type="button" class="btn btn-default" style="margin-left:6px; margin-top:6px;" 
                          id="btnRestore"  onclick="getDeviceData(true)"> <?php echo $pia_lang['DevDetail_button_Reset'];?> </button>
                        <!-- <button type="button" disabled class="btn btn-primary pa-btn" style="margin-left:6px;"  -->
                        <button type="button" disabled class="btn btn-primary" style="margin-left:6px; margin-top:6px;" 
                          id="btnSave"     onclick="setDeviceData()" >     <?php echo $pia_lang['DevDetail_button_Save'];?> </button>
                    </div>
                  </div>

                </div>
              </div>                                                                         

<!-- tab page 4 ------------------------------------------------------------ -->
              <div class="tab-pane fade table-responsive" id="panEvents">
                
                <!-- Datatable Events -->

                <table id="tableEvents" class="table table-bordered table-hover table-striped ">
                  <thead>
                  <tr>
                    <th>Date</th>
                    <th>Event type</th>
                    <th>IP</th>
                    <th>Additional info</th>
                    <th>Additional info</th>
                  </tr>
                  </thead>
                  <tbody>
<?php

// Insert Table date here

?>
                </tbody>

                </table>
              </div>

            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->

          <!-- </div> -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->


<!-- ----------------------------------------------------------------------- -->
<?php
  require 'php/templates/footer.php';
?>


<!-- ----------------------------------------------------------------------- -->
<!-- iCkeck -->
  <link rel="stylesheet" href="lib/AdminLTE/plugins/iCheck/all.css">
  <script src="lib/AdminLTE/plugins/iCheck/icheck.min.js"></script>

<!-- Datatable -->
  <link rel="stylesheet" href="lib/AdminLTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <script src="lib/AdminLTE/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="lib/AdminLTE/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

<!-- fullCalendar -->
  <link rel="stylesheet" href="lib/AdminLTE/bower_components/fullcalendar/dist/fullcalendar.min.css">
  <link rel="stylesheet" href="lib/AdminLTE/bower_components/fullcalendar/dist/fullcalendar.print.min.css" media="print">
  <script src="lib/AdminLTE/bower_components/moment/moment.js"></script>
  <script src="lib/AdminLTE/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
  <script src="lib/AdminLTE/bower_components/fullcalendar/dist/locale-all.js"></script>

<!-- Dark-Mode Patch -->
<?php
if ($ENABLED_DARKMODE === True) {
   echo '<link rel="stylesheet" href="css/dark-patch-cal.css">';
}
?>

<!-- page script ----------------------------------------------------------- -->
<script>

  var mac                 = '';
  var devicesList         = [];
  var pos                 = -1;
  var parPeriod           = 'Front_ServiceDetails_Period';
  var parTab              = 'Front_ServiceDetails_Tab';
  var parEventsRows       = 'Front_ServiceDetails_Events_Rows';
  var period              = '1 month';
  var tab                 = 'tabDetails'
  var eventsRows          = 10;

  // Read parameters & Initialize components
  main();


// -----------------------------------------------------------------------------

function main () {
  initializeTabs();
  initializeiCheck();
  getEventsTotalsforService();
  initializeDatatable();
}

function initializeTabs () {
  // Activate panel
  $('.nav-tabs a[id='+ tab +']').tab('show');
}

function initializeiCheck () {
   // Blue
   $('input[type="checkbox"].blue').iCheck({
     checkboxClass: 'icheckbox_flat-blue',
     radioClass:    'iradio_flat-blue',
     increaseArea:  '20%'
   });

  // Orange
  $('input[type="checkbox"].orange').iCheck({
    checkboxClass: 'icheckbox_flat-orange',
    radioClass:    'iradio_flat-orange',
    increaseArea:  '20%'
  });

  // Red
  $('input[type="checkbox"].red').iCheck({
    checkboxClass: 'icheckbox_flat-red',
    radioClass:    'iradio_flat-red',
    increaseArea:  '20%'
  });

}

function getEventsTotalsforService() {
  // stop timer
  // stopTimerRefreshData();

  // get totals and put in boxes
  $.get('php/server/services.php?action=getEventsTotalsforService&url=<?php echo $servicedetails['mon_URL']?>', function(data) {
    var totalsEvents = JSON.parse(data);

    $('#eventsAll').html      (totalsEvents[0].toLocaleString());
    $('#events2xx').html      (totalsEvents[1].toLocaleString());
    $('#events3xx').html      (totalsEvents[2].toLocaleString());
    $('#events4xx').html      (totalsEvents[3].toLocaleString());
    $('#events5xx').html      (totalsEvents[4].toLocaleString());
    $('#eventsDown').html     (totalsEvents[5].toLocaleString());
  });
    // Timer for refresh data
    //newTimerRefreshData(getEventsTotals);
}

function initializeDatatable () {
  $('#tableEvents').DataTable({
    'paging'       : true,
    'lengthChange' : true,
    'lengthMenu'   : [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
    'searching'    : true,
    'ordering'     : true,
    'info'         : true,
    'autoWidth'    : false,
    'columns': [
        { "data": 0 },
        { "data": 4 },
        { "data": 1 },
        { "data": 2 },
        { "data": 3 }
      ],

    // Parameters
    'pageLength'   : 25,

    'columnDefs'  : [
      {className: 'text-center', targets: [2,3] },
      {className: 'text-right',  targets: [1] },
      {width:     '220px',       targets: [0] },
      {width:     '120px',       targets: [1] },
      {width:     '80px',        targets: [3] },
//      {orderData: [8],           targets: 7 },
//      {orderData: [10],          targets: 9 },

      //Device Name
      {targets: [0],
       "createdCell": function (td, cellData, rowData, row, col) {
         $(td).html ('<b><a href="'+ rowData[0] +'" class="" target="_blank">'+ cellData +'</a></b>');
      } },

      // Replace HTML codes
      // {targets: [0,1,2,3,4],
      //  "createdCell": function (td, cellData, rowData, row, col) {
      //    $(td).html (translateHTMLcodes (cellData));
      // } }
    ],

    // Processing
    'processing'  : true,
    'language'    : {
      processing: '<table><td width="130px" align="middle">Loading...</td><td><i class="ion ion-ios-loop-strong fa-spin fa-2x fa-fw"></td></table>',
      emptyTable: 'No data',
      "lengthMenu": "<?php echo $pia_lang['Events_Tablelenght'];?>",
      "search":     "<?php echo $pia_lang['Events_Searchbox'];?>: ",
      "paginate": {
          "next":       "<?php echo $pia_lang['Events_Table_nav_next'];?>",
          "previous":   "<?php echo $pia_lang['Events_Table_nav_prev'];?>"
      },
      "info":           "<?php echo $pia_lang['Events_Table_info'];?>",
    },
  });

  //Save Parameter rows when changed
  $('#tableEvents').on( 'length.dt', function ( e, settings, len ) {
    setParameter (parTableRows, len);
  } );
};

</script>
