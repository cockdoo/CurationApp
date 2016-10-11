<?php 

header("Content-Type: text/html; charset=UTF-8");

require_once('../../config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);


for ($i=13; $i < 13; $i++) { 
  $url = "https://extraction.import.io/query/extractor/48bf96d6-27e3-4db4-abda-b62e8497c447?_apikey=191cc04eeaa3439b83e60fbdd2a4e502e5498cc8dfe8802423042475073cbb06c34bab9aeedcd6b42281ab57609fedbc3c39f037a44c2f214ec38b8963a39d00ee7e2b256cda855a61718d470286daa9&url=https%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2Fp13%2F";

  $json = file_get_contents($url);
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

  $arr = json_decode($json, true);

  $data = $arr["extractorData"]["data"][0]["group"];

  for ($i=0; $i < count($data); $i++) {
    $title = $data[$i]["thumbnail"][0]["alt"];
    if (strstr($title, '／', true)) {
      $title = strstr($title, '／', true); 
    }
    $title = htmlspecialchars($title, ENT_QUOTES);
    echo $title;
    echo "<br>";
    $url = $data[$i]["thumbnail"][0]["href"];
    $imageUrl = $data[$i]["thumbnail"][0]["src"];
    $date = $data[$i]["date"][0]["text"];
    // echo $title."<br>";
    // echo $url."<br>";
    detail($title, $url, $imageUrl, $date);
  }
  echo "終了！";
}

function detail($title, $url, $imageUrl, $date){
  $html = file_get_contents($url);
  if($html != ""){
      // あれやこれやと整形
      // $html = htmlspecialchars($html);
      // $html = mb_ereg_replace('\r\n', '<br />', $html);
      // $html = mb_ereg_replace('\n', '<br />', $html);
      // $html = mb_ereg_replace('\r', '<br />', $html);

      // $html = mb_convert_encoding($html,"SJIS-win", "ASCII,JIS,UTF-8,EUC-JP,SJIS"); 

      //  header("Content-type: text/html charset=Shift_JIS");
      // echo $html;

      /*preg_match( "|<a href=\"(.*?)\".*?>(.*?)</a>|mis", $html_tag, $matches);*/
      preg_match( "/<p class=\"article-info__text\">(\[住所\])?(.*?)<br \/>/u", $html, $address);

      // var_dump($address[1]);
      // echo $address[2];
      // echo "<br>";      

      if ($address[2] != nil) {
        $latlng = get_gps_from_address($address[2]);  
        // var_dump($latlng);
        if ($latlng != nil) {
          insertDB($title, $url, $imageUrl, $latlng['lat'], $latlng['lng'], $date, "ぐるたび"); 
          // echo "<br>";
        }        
      }
  }else{
      echo "ファイルの取得に失敗しました";
  }
}

function insertDB($title, $url, $imageUrl, $lat, $lng, $date, $media) {
  $query = "INSERT INTO Curation(
  title,
  url,
  imageUrl,
  location,
  date,
  media
  ) VALUES(
  '".$title."',
  '".$url."',
  '".$imageUrl."',
  GeomFromText('POINT(".$lat." ".$lng.")'),
  '".$date."',
  '".$media."'
  )";

  // echo $query;

  //DBに挿入
  mysql_query($query) or die(mysql_error());
}

function get_gps_from_address($address=''){
  $address_array = explode(" ", $address);
  // var_dump($address_array);

  $res = array();
  $req = 'http://maps.google.com/maps/api/geocode/xml';
  $req .= '?address='.urlencode($address);
  $req .= '&sensor=false';    
  $xml = simplexml_load_file($req) or die('XML parsing error');
  if ($xml->status == 'OK') {
      $location = $xml->result->geometry->location;
      $res['lat'] = (string)$location->lat[0];
      $res['lng'] = (string)$location->lng[0];
  }
  else {

    $req2 = 'http://maps.google.com/maps/api/geocode/xml';
    $req2 .= '?address='.urlencode($address_array[0]);
    $req2 .= '&sensor=false';    
    $xml2 = simplexml_load_file($req2) or die('XML parsing error');

    if ($xml2->status == 'OK') {
        $location = $xml2->result->geometry->location;
        $res['lat'] = (string)$location->lat[0];
        $res['lng'] = (string)$location->lng[0];
    }
  }
  return $res;
}


 ?>