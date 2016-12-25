<?php 

header("Content-Type: text/html; charset=UTF-8");

require_once('./../config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);

$lat = $_GET["lat"];
$lng = $_GET["lng"];

$length = array("100", "500", "1000", "3000", "5000", "1000000000000", "34567876545678987");

$responseArray = array();


for ($h=0; $h < count($length); $h++) {
  echo "A";
    $query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$lat." ".$lng.",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation HAVING length <= ".$length[$h]."/112.12/1000 ORDER BY length";
    $result = mysql_query($query) or die(mysql_error());

    while ($row = mysql_fetch_assoc($result)) {
      echo "B";
      $responseRowArray = array(
        "id" => $row["id"],
        "lat" => $row["lat"],
        "lng" => $row["lng"],
        "title" => $row["title"],
        "url" => $row["url"],
        "imageUrl" => $row["imageUrl"],
        "date" => $row["date"],
        "media" => $row["media"],
        "tag" => $row["tag"],
        "prefecture" => $row["prefecture"],
        "locality" => $row["locality"],
        "sublocality" => $row["sublocality"]
      );
      array_push($responseArray, $responseRowArray);
      break;
    }
    if (count($responseArray) > 0) {
      break;
    }
}

$responseJSON = json_encode($responseArray);
echo $responseJSON;

 ?>


