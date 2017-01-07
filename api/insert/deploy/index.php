<?php 
include("../common.php");


$query = "SELECT id, X(location) as lat, Y(location) as lng, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation_test";
$result = mysql_query($query) or die(mysql_error());

$num = 0;
while ($row = mysql_fetch_assoc($result)) {
    $num ++;
    echo "ID:".$row["id"]." ";
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
        echo "本番のデータベースに追加！";
        insertDB($title, $url, $imageUrl, $lat, $lng, $date, $media, $prefecture, $locality, $sublocality, true);
    }
}

 ?>


