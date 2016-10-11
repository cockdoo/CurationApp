<?php 

header("Content-Type: text/html; charset=UTF-8");

require_once('./config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);

$lat = $_GET["lat"];
$lng = $_GET["lng"];
$length = $_GET["length"];

$query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$lat." ".$lng.",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag FROM Curation HAVING length <= ".$length."/112.12/1000 ORDER BY length";

$result = mysql_query($query) or die(mysql_error());

$responseArray = array();

while ($row = mysql_fetch_assoc($result)) {

    $responseRowArray = array(
        "id" => $row["id"],
        "lat" => $row["lat"],
        "lng" => $row["lng"],
        "title" => $row["title"],
        "url" => $row["url"],
        "imageUrl" => $row["imageUrl"],
        "date" => $row["date"],
        "media" => $row["media"],
        "tag" => $row["tag"]
        );
    array_push($responseArray, $responseRowArray);
}

$responseJSON = json_encode($responseArray);
echo $responseJSON;

 ?>


