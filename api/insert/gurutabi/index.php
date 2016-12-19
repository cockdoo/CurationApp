<?php 
include("../common.php");

const mediaName = "ぐるたび";

for ($num=14; $num < 15; $num++) { 
	$url = "https://extraction.import.io/query/extractor/48bf96d6-27e3-4db4-abda-b62e8497c447?_apikey=191cc04eeaa3439b83e60fbdd2a4e502e5498cc8dfe8802423042475073cbb06c34bab9aeedcd6b42281ab57609fedbc3c39f037a44c2f214ec38b8963a39d00ee7e2b256cda855a61718d470286daa9&url=https%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2Fp".$num."%2F";

	// $json = file_get_contents($url);
  $json = file_get_contents("sample.json");
	$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

	$arr = json_decode($json, true);
	$data = $arr["extractorData"]["data"][0]["group"];

	for ($i=0; $i < count($data); $i++) {
    $title = $data[$i]["thumbnail"][0]["alt"];
    if (strstr($title, '／', true)) {
      $title = strstr($title, '／', true); 
    }
    $title = htmlspecialchars($title, ENT_QUOTES);
    // echo "<br>";
    echo "<br>";
    echo "【タイトル】".$title;
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
      // preg_match( "|<p class=\"article-info__text\">(\[住所\])?(.*?)<br \/>|u", $html, $address);
      preg_match_all( "|<p class=\"article-info__text\">(\[住所\])?(.*?)( )?<br \/>|iu", $html, $match);

      // var_dump($match[2]);
      for ($i=0; $i < count($match); $i++) { 
        if ($match[2][$i] != nil) {
          if (substr($match[2][$i], 0, 1) != "0" && substr($match[2][$i], 0, 1) != " " ) {

            $geo = get_gps_from_address($match[2][$i]);

            if (count($geo) == 5) {
              //すでにDBにあるデータかどうか調べる
              $isAlready = isAlreadyInDatabase($url, $geo["lat"], $geo["lng"]);

              if ($isAlready == false) {
                echo "データを追加する！";
                insertDB($title, $url, $imageUrl, $geo['lat'], $geo['lng'], $date, mediaName, $geo["prefecture"], $geo["locality"], $geo["sublocality"]);
              }else {
                echo "すでにあるよ！";
              }
            }         
          }
        }   
      }
  }else{
      echo "ファイルの取得に失敗しました";
  }
}



 ?>