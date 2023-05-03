<!-- ---------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector
#
#  help_faq.php - Front module. Help and FAQ
#-------------------------------------------------------------------------------
#  leiweibau 2023                                          GNU GPLv3
#--------------------------------------------------------------------------- -->

<?php
session_start();

if ($_SESSION["login"] != 1) {
	header('Location: /pialert/index.php');
	exit;
}

require 'php/templates/header.php';
?>
<!-- Page ------------------------------------------------------------------ -->
<div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
    <?php require 'php/templates/notification.php';?>
      <h1 id="pageTitle">
         <?php echo $pia_lang['HelpFAQ_Title']; ?>
      </h1>
    </section>

    <!-- Main content ---------------------------------------------------------- -->
    <section class="content">
      <h4><?php echo $pia_lang['HelpFAQ_Cat_General']; ?></h4>
       <div class="panel-group" id="accordion_gen">
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse100">
              <?php echo $pia_lang['HelpFAQ_Cat_General_100_head']; ?></a>
            </h4>
          </div>
          <div id="collapse100" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body"><?php echo $pia_lang['HelpFAQ_Cat_General_100_text_a']; ?>
              <span class="text-danger help_faq_code"><?php echo date_default_timezone_get(); ?></span><br>
              <?php echo $pia_lang['HelpFAQ_Cat_General_100_text_b']; ?>
              <span class="text-danger help_faq_code"><?php echo php_ini_loaded_file(); ?></span><br>
              <?php echo $pia_lang['HelpFAQ_Cat_General_100_text_c']; ?></div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse101">
              <?php echo $pia_lang['HelpFAQ_Cat_General_101_head']; ?></a>
            </h4>
          </div>
          <div id="collapse101" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body">
              <?php echo $pia_lang['HelpFAQ_Cat_General_101_text']; ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse102">
              <?php echo $pia_lang['HelpFAQ_Cat_General_102_head']; ?></a>
            </h4>
          </div>
          <div id="collapse102" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body">
              <?php echo $pia_lang['HelpFAQ_Cat_General_102_text']; ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse103">
              <?php echo $pia_lang['HelpFAQ_Cat_General_103_head']; ?></a>
            </h4>
          </div>
          <div id="collapse103" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body">
              <?php echo $pia_lang['HelpFAQ_Cat_General_103_text']; ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse104">
              <?php echo $pia_lang['HelpFAQ_Cat_General_104_head']; ?></a>
            </h4>
          </div>
          <div id="collapse104" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body">
              <?php echo $pia_lang['HelpFAQ_Cat_General_104_text']; ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse105">
              <?php echo $pia_lang['HelpFAQ_Cat_General_105_head']; ?></a>
            </h4>
          </div>
          <div id="collapse105" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body">
              <?php echo $pia_lang['HelpFAQ_Cat_General_105_text']; ?>
            </div>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading faqhelp_head_color">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion_gen" href="#collapse106">
              <?php echo $pia_lang['HelpFAQ_Cat_General_106_head']; ?></a>
            </h4>
          </div>
          <div id="collapse106" class="panel-collapse collapse" style="font-size: 16px;">
            <div class="panel-body">
              <?php echo $pia_lang['HelpFAQ_Cat_General_106_text']; ?>
            </div>
          </div>
        </div>
      </div>

<!-- Devices -->

<h4><?php echo $pia_lang['Navigation_Devices']; ?></h4>
 <div class="panel-group" id="accordion_dev">
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_dev" href="#collapse200">
        <?php echo $pia_lang['HelpFAQ_Cat_Device_200_head']; ?></a>
      </h4>
    </div>
    <div id="collapse200" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
         <?php echo $pia_lang['HelpFAQ_Cat_Device_200_text']; ?>
      </div>
    </div>
  </div>
</div>

<!-- Devices Details -->

<h4><?php echo $pia_lang['Navigation_Devices']; ?> - <?php echo $pia_lang['HelpFAQ_Cat_Detail']; ?></h4>
 <div class="panel-group" id="accordion_det">
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_det" href="#collapse300">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_300_head']; ?> "<?php echo $pia_lang['DevDetail_MainInfo_Network']; ?>" / "<?php echo $pia_lang['DevDetail_MainInfo_Network_Port']; ?>"?</a>
      </h4>
    </div>
    <div id="collapse300" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        "<?php echo $pia_lang['DevDetail_MainInfo_Network']; ?>" <?php echo $pia_lang['HelpFAQ_Cat_Detail_300_text_a']; ?><br>
        "<?php echo $pia_lang['DevDetail_MainInfo_Network_Port']; ?>" <?php echo $pia_lang['HelpFAQ_Cat_Detail_300_text_b']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_det" href="#collapse301">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_301_head_a']; ?> "<?php echo $pia_lang['DevDetail_EveandAl_ScanCycle']; ?>" <?php echo $pia_lang['HelpFAQ_Cat_Detail_301_head_b']; ?></a>
      </h4>
    </div>
    <div id="collapse301" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_301_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_det" href="#collapse302">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_302_head_a']; ?> "<?php echo $pia_lang['DevDetail_EveandAl_RandomMAC']; ?>" <?php echo $pia_lang['HelpFAQ_Cat_Detail_302_head_b']; ?></a>
      </h4>
    </div>
    <div id="collapse302" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_302_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_det" href="#collapse303">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_303_head']; ?></a>
      </h4>
    </div>
    <div id="collapse303" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Detail_303_text']; ?>
      </div>
    </div>
  </div>
</div>

<!-- Network -->

<h4><?php echo $pia_lang['Navigation_Network']; ?></h4>
 <div class="panel-group" id="accordion_net">
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_net" href="#collapse600">
        <?php echo $pia_lang['HelpFAQ_Cat_Network_600_head']; ?></a>
      </h4>
    </div>
    <div id="collapse600" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Network_600_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_net" href="#collapse601">
        <?php echo $pia_lang['HelpFAQ_Cat_Network_601_head']; ?></a>
      </h4>
    </div>
    <div id="collapse601" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Network_601_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_net" href="#collapse602">
        <?php echo $pia_lang['HelpFAQ_Cat_Network_602_head']; ?></a>
      </h4>
    </div>
    <div id="collapse602" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Network_602_text']; ?>
      </div>
    </div>
  </div>
</div>

<!-- Web Services -->

<h4><?php echo $pia_lang['Navigation_Services']; ?></h4>
 <div class="panel-group" id="accordion_pre">
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse700">
        <?php echo $pia_lang['HelpFAQ_Cat_Service_700_head']; ?></a>
      </h4>
    </div>
    <div id="collapse700" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Service_700_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse701">
        <?php echo $pia_lang['HelpFAQ_Cat_Service_701_head']; ?></a>
      </h4>
    </div>
    <div id="collapse701" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <table class="table table-bordered table-hover table-striped"><tr><th class="col-xs-1" style="text-align: center">Code</th><th class="col-xs-11">Description</th></tr>
        <?php

$jsonfile = file_get_contents("./lib/http-status-code/index.json");
$code_array = json_decode($jsonfile, true);
asort($code_array);
foreach ($code_array as $key => $jsons) {
	echo '<tr><td class="text-danger httpstatus_table_code">' . $jsons['code'] . '</td><td class="httpstatus_table_text">' . $jsons['description'] . '</td></tr>';
}

;?>
      </table>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse702">
        <?php echo $pia_lang['HelpFAQ_Cat_Service_702_head']; ?></a>
      </h4>
    </div>
    <div id="collapse702" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Service_702_text']; ?>
      </div>
    </div>
  </div>
</div>

<!-- Web Services Details -->

<h4><?php echo $pia_lang['Navigation_Services']; ?> - <?php echo $pia_lang['HelpFAQ_Cat_Detail']; ?></h4>
 <div class="panel-group" id="accordion_pre">
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse750">
        <?php echo $pia_lang['HelpFAQ_Cat_ServiceDetails_750_head']; ?></a>
      </h4>
    </div>
    <div id="collapse750" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_ServiceDetails_750_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse751">
        <?php echo $pia_lang['HelpFAQ_Cat_ServiceDetails_751_head']; ?></a>
      </h4>
    </div>
    <div id="collapse751" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_ServiceDetails_751_text']; ?>
      </div>
    </div>
  </div>
</div>

<!-- Presence -->

<h4><?php echo $pia_lang['Navigation_Presence']; ?></h4>
 <div class="panel-group" id="accordion_pre">
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse400">
        <?php echo $pia_lang['HelpFAQ_Cat_Presence_400_head']; ?></a>
      </h4>
    </div>
    <div id="collapse400" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Presence_400_text']; ?>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading faqhelp_head_color">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion_pre" href="#collapse401">
        <?php echo $pia_lang['HelpFAQ_Cat_Presence_401_head']; ?></a>
      </h4>
    </div>
    <div id="collapse401" class="panel-collapse collapse" style="font-size: 16px;">
      <div class="panel-body">
        <?php echo $pia_lang['HelpFAQ_Cat_Presence_401_text']; ?>
      </div>
    </div>
  </div>
</div>

  <div style="width: 100%; height: 20px;"></div>
    </section>
    <!-- /.content -->
</div>
  <!-- /.content-wrapper -->

<!-- ----------------------------------------------------------------------- -->
<?php
require 'php/templates/footer.php';
?>
