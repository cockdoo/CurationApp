<?php 
header("Content-Type: text/html; charset=UTF-8");

require_once('../../config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);

const myTestTable = "Curation_test";
const myTable = "Curation";

function test() {
  echo "string";
}

function get_gps_from_address($address=''){
  $address_array = explode(" ", $address);

  $res['prefecture'] = "";
  $res['locality'] = "";
  $res['sublocality'] = "";

  if (preg_match("/(jpg|png|JPG|PNG|gif|GIF|http|img)/i", $address)) {
    return nil;
  }

  $res = array();
  $req = 'http://maps.google.com/maps/api/geocode/xml';
  $req .= '?address='.urlencode($address);
  $req .= '&sensor=false';
  $req .= '&language=ja';
  
  $xml = simplexml_load_file($req) or die('XML parsing error');

  if ($xml->status == 'OK') {
    $location = $xml->result->geometry->location;
    $res['lat'] = (string)$location->lat[0];
    $res['lng'] = (string)$location->lng[0];
  
    $address_component = $xml->result->address_component;
    for ($i=0; $i < count($address_component); $i++) {
      $a = $address_component[$i];
      if ($a->type[0] == "administrative_area_level_1") {
        $res['prefecture'] = $a->long_name;
      }
      if ($a->type[0] == "locality" && $a->type[1] == "political") {
        $res['locality'] = $a->long_name;
      }
      if ($a->type[2] == "sublocality_level_1") {
        $res['sublocality'] = $a->long_name;
      }
    }
  }
  else {
    $req2 = 'http://maps.google.com/maps/api/geocode/xml';
    $req2 .= '?address='.urlencode($address_array[0]);
    $req2 .= '&sensor=false';   
    $req2 .= '&language=ja';
    $xml2 = simplexml_load_file($req2) or die('XML parsing error');

    if ($xml2->status == 'OK') {
      $location = $xml2->result->geometry->location;
      $res['lat'] = (string)$location->lat[0];
      $res['lng'] = (string)$location->lng[0];

      $address_component = $xml2->result->address_component;
      for ($i=0; $i < count($address_component); $i++) {
        $a = $address_component[$i];
        if ($a->type[0] == "administrative_area_level_1") {
          $res['prefecture'] = $a->long_name;
        }
        if ($a->type[0] == "locality" && $a->type[1] == "political") {
          $res['locality'] = $a->long_name;
        }
        if ($a->type[2] == "sublocality_level_1") {
          $res['sublocality'] = $a->long_name;
        }
      }
    }
  }
  return $res;
}

// function get_address_from_gps($lat, $lng) {
//   $req = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=35.794507,139.790788&sensor=false';
//   $xml = simplexml_load_file($req) or die('XML parsing error');
// }

function insertDB($title, $url, $imageUrl, $lat, $lng, $date, $media, $prefecture, $locality, $sublocality, $isProduction) {
  $table;
  if ($isProduction) {
    $table = myTable;
  }else {
    $table = myTestTable;
  }

  $escape = array("'");
  $title = str_replace($escape, "", $title);

  $query = "INSERT INTO ".$table."(
  title,
  url,
  imageUrl,
  location,
  date,
  media,
  prefecture,
  locality,
  sublocality
  ) VALUES(
  '".$title."',
  '".$url."',
  '".$imageUrl."',
  GeomFromText('POINT(".$lat." ".$lng.")'),
  '".$date."',
  '".$media."',
  '".$prefecture."',
  '".$locality."',
  '".$sublocality."'
  )";

  // echo $query;
  //DBに挿入
  mysql_query($query) or die(mysql_error());
}

function isAlreadyInDatabase($url, $lat, $lng, $isProduction) {
  $table;
  if ($isProduction) {
    $table = myTable;
  }else {
    $table = myTestTable;
  }
  $query = "SELECT X(location) as lat, Y(location) as lng, url FROM ".$table." where url = '".(string)$url."'";
  $result = mysql_query($query) or die(mysql_error());
  $isAlready = false;
  $responseArray = array();
  while ($row = mysql_fetch_assoc($result)) {
    if ($row["lat"] == $lat && $row["lng"] == $lng) {
      $isAlready = true;
    }
  }
  return $isAlready;
}


 ?>