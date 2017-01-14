<?php 
include("../common.php");

const mediaName = "macaroni";
$page = $_GET["page"];

for ($num = 0; $num < $page; $num++) {
  // $tagNum = $nums[$num];
  echo $num."...";

  $url = "https://extraction.import.io/query/extractor/7f4ba4eb-e2fb-492b-a7b7-8c292a4a0f2e?_apikey=f6fd1ee2914748b58edd7fad25cadefc5c43628088ec417eb076d3973f9495866761514e26d104aa7e0d0e2994c82fa13d1ff705260e529ed1aa94509ad75409ef45ffd4f943eced28b570c15b3e9e24&url=http%3A%2F%2Fmacaro-ni.jp%2F%3Fpage%3D".($num+1);


  $json = file_get_contents($url);
  // $json = file_get_contents("sample.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

  $arr = json_decode($json, true);
  $data = $arr["extractorData"]["data"][0]["group"];

  for ($i=0; $i < count($data); $i++) {
    if ($i==10) {
      // echo "break!";
      // break;
    }
    $title = $data[$i]["title"][0]["text"];
    if (strstr($title, '／', true)) {
      $title = strstr($title, '／', true); 
    }
    $title = htmlspecialchars($title, ENT_QUOTES);
    $url = $data[$i]["title"][0]["href"];

    // echo "<br>";
    // echo "【タイトル】".$title." (".$url.")";
    // echo "<br>";

    prepareInsertDB($title, $url);
  }
  echo "終了！";
}

function prepareInsertDB($title, $url){
  $html = file_get_contents($url);
  if($html != ""){

    preg_match_all( "|<div id=\"summary-icon\" style=\"background-image\:url\('(.*?)'\)\">|iu", $html, $imageUrl_match);
    $imageUrl = $imageUrl_match[1][0];

    preg_match_all( "|<time datetime=\"(.*?)\" class=\"summary-update-date\">|iu", $html, $date_match);
    $date = $date_match[1][0];

    preg_match_all( "|<p class=\"geo_title\">(.*?)<\/p>|iu", $html, $match);

    for ($i=0; $i < count($match[1]); $i++) { 
      $geo = get_gps_from_address($match[1][$i]);
      if (count($geo) == 5) {
        $isAlready = isAlreadyInDatabase($url, $geo["lat"], $geo["lng"], false);

        if ($isAlready == false) {
          // echo "テストデータベースに追加！";
          insertDB($title, $url, $imageUrl, $geo['lat'], $geo['lng'], $date, mediaName, $geo["prefecture"], $geo["locality"], $geo["sublocality"], false);
        }else {
          // echo "この記事はすでにある！";
        }
      }
    }
  }else{
    echo "ファイルの取得に失敗しました";
  }
}



 ?>