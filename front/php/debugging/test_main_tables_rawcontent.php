<?php
session_start();

if ($_SESSION["login"] != 1) {
    header('Location: ../../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debugging</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 0px;
            margin: 0px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            display: none;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        tr:nth-child(even) {
            background-color: #f0f0f0;
        }
        .info_head {
            font-size: 1.2em;
            font-weight: bold;
        }
        .info_box {
            margin-top: 40px;
            margin-bottom: 40px;
            box-shadow: 0px 0px 15px #bbb;
            width: auto;
            margin-left: 20px;
            margin-right: 20px;
            padding: 10px;
        }
        .short {
            width: 300px;
        }
        .heading {
            font-size: 1.2em;
            margin: 0px;
            display: none;
        }
        #resultheading {
            font-size: 1.2em;
            margin: 0px;
        }
        td:hover::after {
            content: attr(data-column);
            position: absolute;
            background: #333;
            color: #fff;
            padding: 5px;
            border-radius: 3px;
            font-size: 12px;
        }
        #tableSelector {
            background-color: #fff;
            display: inline-block;
            border: solid 1px #999;
            padding: 5px 15px;
            font-size: 16px;
        }
        a {
            color: dodgerblue;
            text-decoration: none;
        }
        a:hover {
            color: deepskyblue; 
        }
        .topheader {
            width: 100%; background-color: #f0f0f0; position: relative; top: 0px; padding-top: 10px; padding-bottom: 10px; margin: 0px; text-align: center;
        }
        #pialert_url {
            margin-top: 10px;
        }
        .resultheader {
            width: 100%; background-color: #f0f0f0; position: relative; top: 0px; padding-top: 10px; padding-bottom: 10px; margin: 0px; text-align: center;
        }
    </style>
</head>
<body>
    <div class="topheader">
        <h2 style="margin: 0px">Show Main Table</h2>
    </div>

    <div class="info_box short" id="info_devices">
        <span class="info_head">Pi.Alert-URL:</span><br>
        <div id="pialert_url"></div>
    </div>

    <div class="info_box short">
        <span class="info_head">Select Table:</span>
        <select id="tableSelector" onchange="toggleTable()">
            <option value="devices">Devices Table</option>
            <option value="icmp">ICMP Table</option>
        </select>
    </div>

    <div class="resultheader">
        <h2 id="resultheading">Results</h2>
    </div>

<?php
$db = new SQLite3('../../../db/pialert.db');
$tables = [
    'devices' => 'Devices',
    'icmp' => 'ICMP_Mon'
];

foreach ($tables as $id => $table) {
    $query = "SELECT * FROM $table";
    $result = $db->query($query);
    $rowCount = 0;
    while ($result->fetchArray(SQLITE3_ASSOC)) {
        $rowCount++;
    }
    echo "<div class='info_box' id='summary_box_$id'><span class='info_head'>Table summary ($table):</span><div id='summary_$id'>$rowCount rows</div></div>";
    echo "<div class='info_box' id='table_box_$id' >
              <h2 class='heading' id='heading_$id'>$table Raw Data</h2>
              <div style='overflow: auto'>
              <table id='$id'>
                <tr>";
    $result = $db->query($query);
    $columns = [];
    for ($i = 0; $i < $result->numColumns(); $i++) {
        $colName = $result->columnName($i);
        $columns[] = $colName;
        echo "<th>" . htmlspecialchars($colName) . "</th>";
    }
    echo "</tr>";
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<td data-column='" . htmlspecialchars($col) . "'>" . htmlspecialchars($row[$col]) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>
          </div>
          </div>";
}
$db->close();
?>
<script type="text/javascript">
    function getBaseUrl() {
        const protocol = window.location.protocol;
        const host = window.location.host;
        const path = window.location.pathname;
        const scriptDir = path.substring(0, path.lastIndexOf('/') + 1).replace('php/debugging/', '');
        return `${protocol}//${host}${scriptDir}`;
    }
    const baseUrl = getBaseUrl();
    const pialertDiv = document.getElementById("pialert_url");
    if (pialertDiv) {
        const baseUrlLink = document.createElement("a");
        baseUrlLink.href = baseUrl + 'maintenance.php';
        baseUrlLink.textContent = baseUrl;
        pialertDiv.appendChild(baseUrlLink);
    }

    function toggleTable() {
        const selected = document.getElementById("tableSelector").value;
        document.getElementById("devices").style.display = selected === "devices" ? "table" : "none";
        document.getElementById("icmp").style.display = selected === "icmp" ? "table" : "none";
        document.getElementById("table_box_devices").style.display = selected === "devices" ? "block" : "none";
        document.getElementById("table_box_icmp").style.display = selected === "icmp" ? "block" : "none";
        document.getElementById("summary_box_devices").style.display = selected === "devices" ? "block" : "none";
        document.getElementById("summary_box_icmp").style.display = selected === "icmp" ? "block" : "none";
        document.getElementById("heading_devices").style.display = selected === "devices" ? "block" : "none";
        document.getElementById("heading_icmp").style.display = selected === "icmp" ? "block" : "none";
    }
    document.getElementById("tableSelector").value = "devices";
    toggleTable();
</script>
</body>
</html>
