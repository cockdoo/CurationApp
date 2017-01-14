<?php 
include("../common.php");

const mediaName = "icotto";
$page = $_GET["page"];

// $nums = array("","","","","","","","","","","","","","","",);
// $nums = array("","","","","","","","","","","","","","","",);


for ($num = 0; $num < $page; $num++) {
  $areaNum = $nums[$num];
  // https://icotto.jp/areas
  // 東京神奈川はだいたいおけ あと0~60くらいもおけ

  echo $num."...";
  // $url = "https://extraction.import.io/query/extractor/cf6545d9-d193-4517-8737-54702b239b9f?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Ficotto.jp%2Fareas%2F".$areaNum;

  //トップページ（新着）
  $url = "https://extraction.import.io/query/extractor/c50f58c6-dba0-4cee-8feb-9e6e038efec3?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Ficotto.jp%2F%3Fpage%3D".($num+1);

  // $url = "https://extraction.import.io/query/extractor/522d6f8f-9906-4233-b6e7-6b6fa93997b8?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Ficotto.jp%2Fpresses%3Futf8%3D%25E2%259C%2593%26query%3D%25E6%2597%25A5%25E9%2587%258E%26commit%3D%25E6%25A4%259C%25E7%25B4%25A2";

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