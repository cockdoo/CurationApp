<?php 
include("../common.php");

const mediaName = "ことりっぷ";
$page = $_GET["page"];

//東京分
// $nums = array("303010300","303011000","303010200","303011700","303011900","303010800","303010700","303011500","303011200","303011400","");
// $nums = array("303012500","303011100","303011100","303012100","303012000","303020200","303020200","303020200","303012400","303020300");
// $nums = array("303110100","303021000","303110200","304060100","304060700","304060900","303020700","","","","");
// $nums = array("","","","","","","","","","","");

for ($num = 0; $num < 0; $num++) {
  echo $num."...";

  $prefectureNum = "13";
  $districtNum = $districtNums[$num];
  $url = "https://extraction.import.io/query/extractor/32d395b8-6ec7-484a-8c51-4e53ca5982eb?_apikey=f3dc56f9ff4b4d79b7e73e9c400731e0920d28a55d86b80a00fbe9c045ec1e510e219ecf45bbd1acf2e15f018ffd60b4fa5261faa4bd995b6ad2a1e60e1285dfdfe1935a16788ebb7e2a5b9da902077d&url=https%3A%2F%2Fco-trip.jp%2Fsearch%2Fpref%3A".$prefectureNum."%2Fdistrict%3A".$districtNum."%2Ftype%3A1%2F";


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
    // echo "<br>【タイトル】".$title." (".$url.")<br>";
    // echo $date."<br>";

    prepareInsertDB($title, $url, $imageUrl, $date);
  }
  echo "終了！";
}

function prepareInsertDB($title, $url, $imageUrl, $date){
  $html = file_get_contents($url);
  if($html != ""){
    $addressArray;
    preg_match_all( "|<p class=\"section_spot_address\">\n<a href=\".*\"title=\"(.*?)\" target|iu", $html, $match);
    
    if (count($match[1]) != 0) {
      $addressArray = $match[1];
    }else {
      preg_match_all( "|<p class=\"section_spot_address\">[\s\S]*?<a .*?>([\s\S]*?)<span class=\"section_spot_map_icon\">|iu", $html, $match);
      $addressArray = $match[1];
    }

    for ($i=0; $i < count($addressArray); $i++) {
      $address = $addressArray[$i];
      $address = str_replace("\"", "", $address);
      
      $address_explode = explode(" ", $address);
      $count = count($address_explode);
      for ($j=0; $j < $count; $j++) { 
        if ($address_explode[$j] == "" || $address_explode[$j] == "\n") {
          unset($address_explode[$j]);
        }
      }
      $address_explode = array_values($address_explode);

      if (count($address_explode) == 1) {
        $address = $address_explode[0];
      }
      else if (count($address_explode) == 3) {
        $address = $address_explode[1]; 
      }
      $geo = get_gps_from_address($address);
      
      if (count($geo) == 5) {
        $isAlready = isAlreadyInDatabase($url, $geo["lat"], $geo["lng"], false);

        if ($isAlready == false) {
          // echo "テストデータベースに追加！";
          echo "↑";
          insertDB($title, $url, $imageUrl, $geo['lat'], $geo['lng'], $date, mediaName, $geo["prefecture"], $geo["locality"], $geo["sublocality"], false);
        }else {
          echo "-";
          // echo "この記事はすでにある！";
        }
      }
    }
  }else{
    echo "ファイルの取得に失敗しました";
  }
}



 ?>