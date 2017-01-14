<?php 
include("../common.php");

const mediaName = "LeTRONC";

$category = $_GET["category"];
$page = $_GET["page"];

for ($num = 0; $num < $page; $num++) {
  // $category =  //gourmet, travel, (news)
  echo $category.$num."...";
  
  $url = "https://extraction.import.io/query/extractor/490555be-3a96-4730-8489-168147ce365e?_apikey=f6fd1ee2914748b58edd7fad25cadefc5c43628088ec417eb076d3973f9495866761514e26d104aa7e0d0e2994c82fa13d1ff705260e529ed1aa94509ad75409ef45ffd4f943eced28b570c15b3e9e24&url=https%3A%2F%2Fletronc-m.com%2F".$category."%3Fpage%3D".($num+1);

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
    $date = $data[$i]["date"][0]["text"];
    $date = str_replace("年", ".", $date);
    $date = str_replace("月", ".", $date);
    $date = str_replace("日", "", $date);
    $date = str_replace("更新", "", $date);
    echo "<br>";
    echo "【タイトル】".$title." (".$url.")";
    echo "<br>";
    // echo $date;
    prepareInsertDB($title, $url, $imageUrl, $date);
  }
  echo "終了！";
}

function prepareInsertDB($title, $url, $imageUrl, $date){
  $html = file_get_contents($url);
  if($html != ""){
    // preg_match_all( "|<th>住所</th>\n<td>[\s\S]*東京(.*?)[\s\S]*?<\/td>|iu", $html, $match);
    preg_match_all( "|<th>住所</th>[\s\S]*?<td>\n([\s\S]*?)<div id=\"div_map\">|iu", $html, $match);
    
    for ($i=0; $i < count($match[1]); $i++) { 
      $address = str_replace("\"", "", $match[1][$i]);

      echo "<br>".$address;
      $geo = get_gps_from_address($address);
      if (count($geo) == 5) {
        $isAlready = isAlreadyInDatabase($url, $geo["lat"], $geo["lng"], false);

        if ($isAlready == false) {
          echo "テストデータベースに追加！";
          insertDB($title, $url, $imageUrl, $geo['lat'], $geo['lng'], $date, mediaName, $geo["prefecture"], $geo["locality"], $geo["sublocality"], false);
        }else {
          echo "この記事はすでにある！";
        }
      }
    }
  }else{
    echo "ファイルの取得に失敗しました";
  }
}



 ?>