<?php 
include("../common.php");

const mediaName = "ぐるたび";


for ($num = 4001; $num < 4005; $num++) {
  echo $num."...";

  //東京
  // $url = "https://extraction.import.io/query/extractor/1f56fa7f-c3d7-4656-977d-a22c6003346f?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2Fp13%2Fn1304%2F"

  //各都道府県 1~47
	// $url = "https://extraction.import.io/query/extractor/48bf96d6-27e3-4db4-abda-b62e8497c447?_apikey=191cc04eeaa3439b83e60fbdd2a4e502e5498cc8dfe8802423042475073cbb06c34bab9aeedcd6b42281ab57609fedbc3c39f037a44c2f214ec38b8963a39d00ee7e2b256cda855a61718d470286daa9&url=https%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2Fp".$num."%2F";

  $prefecture_num = "40";
  $url = "https://extraction.import.io/query/extractor/48bf96d6-27e3-4db4-abda-b62e8497c447?_apikey=191cc04eeaa3439b83e60fbdd2a4e502e5498cc8dfe8802423042475073cbb06c34bab9aeedcd6b42281ab57609fedbc3c39f037a44c2f214ec38b8963a39d00ee7e2b256cda855a61718d470286daa9&url=https%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2Fp".$prefecture_num."%2Fn".$num."%2F";

  //新着
  // $url = "https://extraction.import.io/query/extractor/d7547d4d-a75e-4cbc-badf-fd6754856e40?_apikey=a48eeb417f384d79b5b8586bb7bd1e8dbd8814d1d74d0da1efde4a5b0ee27a3f9683cabd5d58c1a8b37b7aa100047dac88fed19fa63e664107ff4a376d280c2ba8239f23fcf8158a0e09ac17b8e22486&url=https%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2F";

	$json = file_get_contents($url);
  // $json = file_get_contents("sample.json");
	$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

	$arr = json_decode($json, true);
	$data = $arr["extractorData"]["data"][0]["group"];

	for ($i=0; $i < count($data); $i++) {
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
    prepareInsertDB($title, $url, $imageUrl, $date);
	}
	echo "終了！";
}

function prepareInsertDB($title, $url, $imageUrl, $date){
	$html = file_get_contents($url);
  if($html != ""){
      preg_match_all( "|<p class=\"article-info__text\">(\[住所\])?(.*?)( )?<br \/>|iu", $html, $match);

      for ($i=0; $i < count($match); $i++) { 
        if ($match[2][$i] != nil) {
          if (substr($match[2][$i], 0, 1) != "0" && substr($match[2][$i], 0, 1) != " " ) {
            $geo = get_gps_from_address($match[2][$i]);
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
        }   
      }
  }else{
      echo "ファイルの取得に失敗しました";
  }
}



 ?>