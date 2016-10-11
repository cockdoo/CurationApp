<?php 

echo "string";

header("Content-Type: text/html; charset=UTF-8");

for ($i=13; $i < 14; $i++) { 
	$url = "https://extraction.import.io/query/extractor/dd1417f1-b350-4777-b0fc-39470d4097a5?_apikey=ed627ce6434946358b90840b902ba996363da95364eaf24962630112cb9b03bc0ba8896d64070ec119934e43225f21c23a6fd70518fcc96841aa4a352e202290b42cc07270cce5ae6715166e0909f18e&url=http%3A%2F%2Fgurutabi.gnavi.co.jp%2Fa%2Fp".$i."%2F";

	$json = file_get_contents($url);
	$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

	$arr = json_decode($json, true);

	$data = $arr["extractorData"]["data"][0]["group"];

	for ($i=0; $i < count($data); $i++) { 
		$title = $data[$i]["thumbnail"][0]["alt"];
		$mediaUrl = $data[$i]["thumbnail"][0]["href"];
		// echo $title."<br>";
		// echo $mediaUrl."<br>";

		// if ($i==0) {
			test($mediaUrl);
		// }
	}

	echo "終了！";
}



function test($url){
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
      preg_match( "/<p class=\"article-info__text\">(\[住所\])?(.*?)<br \/>/u", $html, $title);

      // var_dump($title[1]);
      echo $title[2];
			echo "<br>";      

			if ($title[2] != nil) {
				$latlng = get_gps_from_address($title[2]);	
				var_dump($latlng);
				echo "<br>";
	      echo "<br>";
			}


  }else{
      echo "ファイルの取得に失敗しました";
  }
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