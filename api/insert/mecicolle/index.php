<?php 
include("../common.php");

const mediaName = "メシコレ";

$nums = array("359","1165","173","441","246","263","350","646","426","209","476","524","783","544","420","654","648","371","877","860");

for ($num = 0; $num < count($nums); $num++) {
  $areaNum = $nums[$num];
  // 東京　渋谷~三越前 高尾~江戸川橋 + 60くらいまで済み

  echo $areaNum."...";

  $url = "https://extraction.import.io/query/extractor/cf6545d9-d193-4517-8737-54702b239b9f?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Ficotto.jp%2Fareas%2F".$areaNum;


  $json = file_get_contents($url);
  // $json = file_get_contents("sample.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

  $arr = json_decode($json, true);
  $data = $arr["extractorData"]["data"][0]["group"];

  for ($i=0; $i < count($data); $i++) {
    if ($i==10) {
      echo "break!";
      break;
    }
    $title = $data[$i]["thumbnail"][0]["alt"];
    if (strstr($title, '／', true)) {
      $title = strstr($title, '／', true); 
    }
    $title = htmlspecialchars($title, ENT_QUOTES);
    $url = $data[$i]["thumbnail"][0]["href"];
    $imageUrl = $data[$i]["thumbnail"][0]["src"];
    // echo "<br>";
    // echo "【タイトル】".$title." (".$url.")";
    // echo "<br>";
    prepareInsertDB($title, $url, $imageUrl);
  }
  echo "終了！";
}

function prepareInsertDB($title, $url, $imageUrl){
  $html = file_get_contents($url);
  if($html != ""){
    preg_match_all( "|<span class=\"PressUpdate\">\（(.*)&nbsp;最終更新\）<\/span>|iu", $html, $match_date);
    $date = $match_date[1][0];
    $date = str_replace("年", ".", $date);
    $date = str_replace("月", ".", $date);
    $date = str_replace("日", "", $date);

    preg_match_all( "|<dl class=\"PressTabelogAddress\">.*?<dd>(.*?)<\/dd>|iu", $html, $match);
    

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