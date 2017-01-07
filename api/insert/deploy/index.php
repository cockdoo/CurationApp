<?php 
include("../common.php");


$query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$latArray[$i]." ".$lngArray[$i].",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation";
$result = mysql_query($query) or die(mysql_error());

while ($row = mysql_fetch_assoc($result)) {    
    $lat = $row["lat"];
    $lng = $row["lng"];
    $title = $row["title"];
    $url = $row["url"];
    $imageUrl = $row["imageUrl"];
    $date = $row["date"];
    $media = $row["media"];
    $tag = $row["tag"];
    $prefecture = $row["prefecture"];
    $locality = $row["locality"];
    $sublocality = $row["sublocality"];

    $isAlready = isAlreadyInDatabase($url, $lat, $lng, true);

    if ($isAlready) {
        echo "この記事はすでにある！";
    }else {
        echo "この記事は新しい！";
        insertDB($title, $url, $imageUrl, $lat, $lng, $date, $media, $prefecture, $locality, $sublocality, true);
    }
}

 ?>


