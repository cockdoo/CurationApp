<?php 

header("Content-Type: text/html; charset=UTF-8");

require_once('./../config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);

$lat = $_GET["lat"];
$lng = $_GET["lng"];

$length = array("100", "500", "1000", "3000", "5000", "999999999999999");


$response;


for ($h=0; $h < count($length); $h++) { 
    // $query = "SELECT imageUrl FROM Curation HAVING length <= ".$length[$h]."/112.12/1000 ORDER BY length";
    $query = "SELECT GLength(GeomFromText(CONCAT('LineString(".$lat." ".$lng.",', X(location), ' ', Y(location),')'))) AS length, imageUrl FROM Curation HAVING length <= ".$length[$h]."/112.12/1000 ORDER BY length";
    $result = mysql_query($query) or die(mysql_error());

    while ($row = mysql_fetch_assoc($result)) {
        $already = false;
        for ($j=0; $j < count($responseArray); $j++) { 
            if ($row["url"] == $responseArray[$j]["url"]) {
                $already = true;
            }
        }
        if ($already == false) {
            $response = $row["imageUrl"];
            break;
        }
    }
    if ($response != "") {
        break;
    }
}

echo $response;

 ?>


