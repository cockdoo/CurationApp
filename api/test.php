<?php 

header("Content-Type: text/html; charset=UTF-8");

require_once('./config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);

$lat = $_GET["lat"];
$lng = $_GET["lng"];
$min = $_GET["min"];
$max = $_GET["max"];
$escapeids = $_GET["escapeids"];

$length = array("100", "300", "600", "1000", "2000", "3000", "5000", "10000", "15000", "30000", "50000", "100000", "1000000", "10000000");

$latArray = explode(",", $lat);
$lngArray = explode(",", $lng);
$escapeIdArray = explode(",", $escapeids);

$responseArray = array();


for ($h=0; $h < count($latArray); $h++) {
  echoDebug("<br>■ ".$h."箇所目 <br>");
  $responseBlock = array();
  $isBreakBlock = false;

  for ($i=0; $i < 5; $i++) { //~2000m
    echoDebug($length[$i]."m内<br>");
    $query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$latArray[$h]." ".$lngArray[$h].",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation HAVING length <= ".$length[$i]."/112.12/1000 ORDER BY length";
    $result = mysql_query($query) or die(mysql_error());

    $articleCount = 0;
    while ($row = mysql_fetch_assoc($result)) {
      $already = false;
      for ($j=0; $j < count($responseBlock); $j++) { 
        if ($row["url"] == $responseBlock[$j]["url"]) {
          $already = true;
        }
      }
      for ($l=0; $l < count($responseArray); $l++) { 
        if ($row["url"] == $responseArray[$l]["url"]) {
          $already = true;
        }
      }

      $escape = false;
      for ($k=0; $k < count($escapeIdArray); $k++) { 
        if ($row["id"] == $escapeIdArray[$k]) {
          $escape = true;
        }
      }

      if ($already == false && $escape == false) {
        $articleCount++;
        if (count($latArray) <= 10) {
          if ($articleCount > ($min / count($latArray))) {
            echoDebug("これ以上はいらない！");
            $isBreakBlock = true;
            break;
          }
        }else{
          if ($articleCount > ($min / 10) + 10) {
            echoDebug("これ以上はいらない！");
            $isBreakBlock = true;
            break;
          }
        }
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
        array_push($responseBlock, $responseRowArray);
      }
    }// finish while
    echoDebug($articleCount."個の記事を見つけて<br>");
    echoDebug("ブロックの合計".count($responseBlock)."個になった<br>");
    if ($isBreakBlock == true) {
      break;
    }
  }
  $date = array();
  foreach ($responseBlock as $key => $value) {
    $date[$key] = $value["date"];
  }
  array_multisort($date, SORT_DESC, $responseBlock);

  echoDebug("<br>ここでブロックを追加<br>");
  $responseArray = array_merge($responseArray, $responseBlock);
  echoDebug("合計".count($responseArray)."個になった<br>");

  if (count($responseArray) >= $min) {
    echoDebug("いっぱいになったからブレイク！");
    break;
  }
}

if (count($responseArray) < $min) {
  echo echoDebug("<br><br>記事数が足りない！<br>");
  for ($h=0; $h < count($latArray); $h++) {
    echoDebug("<br>■ ".$h."箇所目 <br>");
    $responseBlock = array();
    $isBreakBlock = false;

    for ($i=5; $i < 7; $i++) { //~2000m
      echoDebug($length[$i]."m内<br>");

      $query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$latArray[$h]." ".$lngArray[$h].",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation HAVING length <= ".$length[$i]."/112.12/1000 ORDER BY length";
      $result = mysql_query($query) or die(mysql_error());

      $articleCount = 0;
      while ($row = mysql_fetch_assoc($result)) {
        $already = false;
        for ($j=0; $j < count($responseArray); $j++) { 
          if ($row["url"] == $responseArray[$j]["url"]) {
            $already = true;
          }
        }
        for ($j=0; $j < count($responseBlock); $j++) { 
          if ($row["url"] == $responseBlock[$j]["url"]) {
            $already = true;
          }
        }

        $escape = false;
        for ($k=0; $k < count($escapeIdArray); $k++) { 
          if ($row["id"] == $escapeIdArray[$k]) {
            $escape = true;
          }
        }

        if ($already == false && $escape == false) {
          $articleCount++;
          if ($articleCount > ($min - count($responseArray))) {
            echoDebug("これ以上はいらない！");
            $isBreakBlock = true;
            break;
          }
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
          array_push($responseBlock, $responseRowArray);
        }
      }// finish while
      echoDebug($articleCount."個の記事を見つけて<br>");
      echoDebug("ブロックの合計".count($responseBlock)."個になった<br>");
      if ($isBreakBlock == true) {
        break;
      }
    }
    $date = array();
    foreach ($responseBlock as $key => $value) {
      $date[$key] = $value["date"];
    }
    array_multisort($date, SORT_DESC, $responseBlock);

    echoDebug("<br>ここでブロックを追加<br>");
    $responseArray = array_merge($responseArray, $responseBlock);
    echoDebug("合計".count($responseArray)."個になった<br>");

    if (count($responseArray) >= $min) {
      echoDebug("いっぱいになったからブレイク！");
      break;
    }
  }
}

if (count($responseArray) < $min) {
  echo echoDebug("<br><br>記事数が足りない！<br>");
  for ($h=0; $h < count($latArray); $h++) {
    echoDebug("<br>■ ".$h."箇所目 <br>");
    $responseBlock = array();
    $isBreakBlock = false;

    for ($i=7; $i < 8; $i++) {
      echoDebug($length[$i]."m内<br>");
      $query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$latArray[$h]." ".$lngArray[$h].",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation HAVING length <= ".$length[$i]."/112.12/1000 ORDER BY length";
      $result = mysql_query($query) or die(mysql_error());

      $articleCount = 0;
      while ($row = mysql_fetch_assoc($result)) {
        $already = false;
        for ($j=0; $j < count($responseArray); $j++) { 
          if ($row["url"] == $responseArray[$j]["url"]) {
            $already = true;
          }
        }
        for ($j=0; $j < count($responseBlock); $j++) { 
          if ($row["url"] == $responseBlock[$j]["url"]) {
            $already = true;
          }
        }

        $escape = false;
        for ($k=0; $k < count($escapeIdArray); $k++) { 
          if ($row["id"] == $escapeIdArray[$k]) {
            $escape = true;
          }
        }

        if ($already == false && $escape == false) {
          $articleCount++;
          if ($articleCount > ($min - count($responseArray))) {
            echoDebug("これ以上はいらない！");
            $isBreakBlock = true;
            break;
          }
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
          array_push($responseBlock, $responseRowArray);
        }
      }// finish while
      echoDebug($articleCount."個の記事を見つけて<br>");
      echoDebug("ブロックの合計".count($responseBlock)."個になった<br>");
      if ($isBreakBlock == true) {
        break;
      }
    }
    $date = array();
    foreach ($responseBlock as $key => $value) {
      $date[$key] = $value["date"];
    }
    array_multisort($date, SORT_DESC, $responseBlock);

    echoDebug("<br>ここでブロックを追加<br>");
    $responseArray = array_merge($responseArray, $responseBlock);
    echoDebug("合計".count($responseArray)."個になった<br>");

    if (count($responseArray) >= $min) {
      echoDebug("いっぱいになったからブレイク！");
      break;
    }
  }
}


if (count($responseArray) < $min) {
  echo echoDebug("<br><br>記事数が足りない！<br>");
  for ($h=0; $h < count($latArray); $h++) {
    echoDebug("<br>■ ".$h."箇所目 <br>");
    $responseBlock = array();
    $isBreakBlock = false;

    for ($i=8; $i < 9; $i++) {
      echoDebug($length[$i]."m内<br>");
      $query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$latArray[$h]." ".$lngArray[$h].",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation HAVING length <= ".$length[$i]."/112.12/1000 ORDER BY length";
      $result = mysql_query($query) or die(mysql_error());

      $articleCount = 0;
      while ($row = mysql_fetch_assoc($result)) {
        $already = false;
        for ($j=0; $j < count($responseArray); $j++) { 
          if ($row["url"] == $responseArray[$j]["url"]) {
            $already = true;
          }
        }
        for ($j=0; $j < count($responseBlock); $j++) { 
          if ($row["url"] == $responseBlock[$j]["url"]) {
            $already = true;
          }
        }

        $escape = false;
        for ($k=0; $k < count($escapeIdArray); $k++) { 
          if ($row["id"] == $escapeIdArray[$k]) {
            $escape = true;
          }
        }

        if ($already == false && $escape == false) {
          $articleCount++;
          if ($articleCount > ($min - count($responseArray))) {
            echoDebug("これ以上はいらない！");
            $isBreakBlock = true;
            break;
          }
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
          array_push($responseBlock, $responseRowArray);
        }
      }// finish while
      echoDebug($articleCount."個の記事を見つけて<br>");
      echoDebug("ブロックの合計".count($responseBlock)."個になった<br>");
      if ($isBreakBlock == true) {
        break;
      }
    }
    $date = array();
    foreach ($responseBlock as $key => $value) {
      $date[$key] = $value["date"];
    }
    array_multisort($date, SORT_DESC, $responseBlock);

    echoDebug("<br>ここでブロックを追加<br>");
    $responseArray = array_merge($responseArray, $responseBlock);
    echoDebug("合計".count($responseArray)."個になった<br>");

    if (count($responseArray) >= $min) {
      echoDebug("いっぱいになったからブレイク！");
      break;
    }
  }
}



if (count($responseArray) < $min) {
  echo echoDebug("<br><br>まだ記事数が足りないの！？<br>");
  for ($h=0; $h < count($latArray); $h++) {
    echoDebug("<br>■ ".$h."箇所目 <br>");
    $responseBlock = array();
    $isBreakBlock = false;

    for ($i=9; $i < 14; $i++) {
      echoDebug($length[$i]."m内<br>");
      $query = "SELECT id, X(location) as lat, Y(location) as lng, GLength(GeomFromText(CONCAT('LineString(".$latArray[$h]." ".$lngArray[$h].",', X(location), ' ', Y(location),')'))) AS length, title, url, imageUrl, date, media, tag, prefecture, locality, sublocality FROM Curation HAVING length <= ".$length[$i]."/112.12/1000 ORDER BY length";
      $result = mysql_query($query) or die(mysql_error());

      $articleCount = 0;
      while ($row = mysql_fetch_assoc($result)) {
        $already = false;
        for ($j=0; $j < count($responseArray); $j++) { 
          if ($row["url"] == $responseArray[$j]["url"]) {
            $already = true;
          }
        }
        for ($j=0; $j < count($responseBlock); $j++) { 
          if ($row["url"] == $responseBlock[$j]["url"]) {
            $already = true;
          }
        }

        $escape = false;
        for ($k=0; $k < count($escapeIdArray); $k++) { 
          if ($row["id"] == $escapeIdArray[$k]) {
            $escape = true;
          }
        }

        if ($already == false && $escape == false) {
          $articleCount++;
          if ($articleCount > ($min - count($responseArray))) {
            echoDebug("これ以上はいらない！");
            $isBreakBlock = true;
            break;
          }
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
          array_push($responseBlock, $responseRowArray);
        }
      }// finish while
      echoDebug($articleCount."個の記事を見つけて<br>");
      echoDebug("ブロックの合計".count($responseBlock)."個になった<br>");
      if ($isBreakBlock == true) {
        break;
      }
    }
    $date = array();
    foreach ($responseBlock as $key => $value) {
      $date[$key] = $value["date"];
    }
    array_multisort($date, SORT_DESC, $responseBlock);

    echoDebug("<br>ここでブロックを追加<br>");
    $responseArray = array_merge($responseArray, $responseBlock);
    echoDebug("合計".count($responseArray)."個になった<br>");

    if (count($responseArray) >= $min) {
      echoDebug("いっぱいになったからブレイク！");
      break;
    }
  }
}

function echoDebug($str) {
  // echo $str;
}

if (count($responseArray) > $max) {
  $arrayCount = count($responseArray);

  for ($i=0; $i < $arrayCount; $i++) { 
    if ($i >= $max) {
      unset($responseArray[$i]);
    }
  }
  $responseArray = array_values($responseArray);
}

$responseJSON = json_encode($responseArray);
echo $responseJSON;

 ?>


