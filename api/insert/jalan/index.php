<?php 
include("../common.php");

const mediaName = "じゃらんニュース";


for ($num=0; $num < 1; $num++) {
  //北海道〜東京上までまだ

  // $cityName = $prefecture_and_famousCity_array[$num];
  // echo $num.$cityName."...";
  // $url = "https://extraction.import.io/query/extractor/77d0d2d1-fe3a-4b8c-8619-6ce17fb647c2?_apikey=8d657da6f43b44769e8e332e98242814f6032f1d0b30e50b1975a05183348131268c6f256a1f63f57f08800fc2360e8d861635f81c890058c6dcb789acca07f69146a9c5e4370a96107920e383d47bc8&url=http%3A%2F%2Fwww.jalan.net%2Fnews%2Fsearch%2F".$cityName;

  echo "...";
  $url = "https://extraction.import.io/query/extractor/6773c1af-11b1-457c-83fb-6fb9f158edd7?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=http%3A%2F%2Fwww.jalan.net%2Fnews%2F";
  $json = file_get_contents($url);
  // $json = file_get_contents("sample.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

  $arr = json_decode($json, true);
  $data = $arr["extractorData"]["data"][0]["group"];

  for ($i=0; $i < count($data); $i++) {
    if ($i == 6) {
      // echo "break!!";
      // break;
    }
    $title = $data[$i]["thumbnail"][0]["alt"];
    if (strstr($title, '／', true)) {
      $title = strstr($title, '／', true); 
    }
    $title = htmlspecialchars($title, ENT_QUOTES);
    $url = $data[$i]["thumbnail"][0]["href"];
    $imageUrl = $data[$i]["thumbnail"][0]["src"];
    $date = $data[$i]["date"][0]["text"];
    // echo "<br>";
    // echo "【タイトル】".$title." (".$url.")";
    // echo "<br>";
    $date = str_replace("NEW ", "", $date);

    prepareInsertDB($title, $url, $imageUrl, $date);
  }
  echo "終了！";
}

function prepareInsertDB($title, $url, $imageUrl, $date){
  $html = file_get_contents($url);
  if($html != ""){
    // preg_match_all( "|<div class=\"databox\">(.*?)<\/div>|iu", $html, $match);
    preg_match_all( "|住所／(.*?)\n|iu", $html, $match);
        
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