<?php 
include("../common.php");

const mediaName = "RETRIP";

for ($num=52; $num < 60; $num++) { 
  echo $num."...";
  $url = "https://extraction.import.io/query/extractor/339fcb01-e918-4dda-9684-7f1e614d6e87?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Fretrip.jp%2Flocations%2FJapan%2F%3Fpage%3D".$num;

  $json = file_get_contents($url);
  // $json = file_get_contents("sample.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

  $arr = json_decode($json, true);
  $data = $arr["extractorData"]["data"][0]["group"];

  for ($i=0; $i < count($data); $i++) {
    $title = $data[$i]["title"][0]["text"];
    $url = $data[$i]["title"][0]["href"];
    $date = $data[$i]["date"][0]["text"];
    // echo "<br>";
    // echo "【タイトル】".$title." (".$url.")";
    // echo "<br>";

    // $searchUrl = str_replace("/", "\/", $url);
    // $searchUrl = str_replace(":", "\:", $searchUrl);

    //日付のフォーマットを変換
    $date = str_replace("年", ".", $date);
    $date = str_replace("月", ".", $date);
    $date = str_replace("日", "", $date);

    detail($title, $url, $date);
  }
  echo "終了！";
}

function detail($title, $url, $date){
  $html = file_get_contents($url);
  if($html != "" && $html != nil){
    preg_match( "/<img class=\"mainBg\" src=\"(.*?)\"/u", $html, $thumbnail);
    $imageUrl = $thumbnail[1];

    preg_match_all( "/<p class=\"address\"><span class=\"icon-location\"><\/span>(.*?)>/u", $html, $match);

    for ($i=0; $i < count($match); $i++) { 
      if ($match[1][$i] != nil && $match[1][$i] != "") {
        // if (substr($match[1][$i], 0, 1) != "0" && substr($match[1][$i], 0, 1) != " " ) {
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
        // }
      } 
    }   
  }else{
      echo "ファイルの取得に失敗しました";
  }
}

 ?>