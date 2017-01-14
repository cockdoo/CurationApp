<?php 
header("Content-Type: text/html; charset=UTF-8");
ini_set('memory_limit', '-1');

require_once('../../config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);

const myTestTable = "Curation_test";
const myTable = "Curation";


function get_gps_from_address($address=''){
  $address_array = explode(" ", $address);
  $address_array2 = explode("　", $address);

  $res['prefecture'] = "";
  $res['locality'] = "";
  $res['sublocality'] = "";

  if (preg_match("/(jpg|png|JPG|PNG|gif|GIF|http|img)/i", $address)) {
    return nil;
  }

  $req = 'https://maps.google.com/maps/api/geocode/xml';
  $req .= '?address='.urlencode($address);
  $req .= '&sensor=false';
  $req .= '&key=AIzaSyDmGkF_u5Ub_jbKTX4wantijQWbLdXZwKM';
  $req .= '&language=ja';
  
  // echo "<br>".$req;
  // echo "<br>".$address."<br>";
  $xml = simplexml_load_file($req) or die('XML parsing error');

  if ($xml->status == 'OVER_QUERY_LIMIT') {
    echo "OVER_QUERY_LIMIT";
  }
  if ($xml->status == 'OK') {
    $location = $xml->result->geometry->location;
    $res['lat'] = (string)$location->lat[0];
    $res['lng'] = (string)$location->lng[0];
  
    $address_component = $xml->result->address_component;
    for ($i=0; $i < count($address_component); $i++) {
      $a = $address_component[$i];
      if ($a->type[0] == "administrative_area_level_1") {
        $res['prefecture'] = $a->long_name;
      }
      if ($a->type[0] == "locality" && $a->type[1] == "political") {
        $res['locality'] = $a->long_name;
      }
      if ($a->type[2] == "sublocality_level_1") {
        $res['sublocality'] = $a->long_name;
      }
    }
  }
  else {
    if ($address_array[0] == nil || $address_array[0] == "") {
      return $res;
    }
    $req2 = 'https://maps.google.com/maps/api/geocode/xml';
    $req2 .= '?address='.urlencode($address_array[0]);
    $req2 .= '&sensor=false';   
    $req2 .= '&key=AIzaSyDmGkF_u5Ub_jbKTX4wantijQWbLdXZwKM';
    $req2 .= '&language=ja';
    $xml2 = simplexml_load_file($req2) or die('XML parsing error');

    if ($xml2->status == 'OK') {
      $location = $xml2->result->geometry->location;
      $res['lat'] = (string)$location->lat[0];
      $res['lng'] = (string)$location->lng[0];

      $address_component = $xml2->result->address_component;
      for ($i=0; $i < count($address_component); $i++) {
        $a = $address_component[$i];
        if ($a->type[0] == "administrative_area_level_1") {
          $res['prefecture'] = $a->long_name;
        }
        if ($a->type[0] == "locality" && $a->type[1] == "political") {
          $res['locality'] = $a->long_name;
        }
        if ($a->type[2] == "sublocality_level_1") {
          $res['sublocality'] = $a->long_name;
        }
      }
    } else {
      if ($address_array2[0] == nil || $address_array2[0] == "") {
        return $res;
      }
      $req3 = 'https://maps.google.com/maps/api/geocode/xml';
      $req3 .= '?address='.urlencode($address_array2[0]);
      $req3 .= '&sensor=false';   
      $req3 .= '&key=AIzaSyDmGkF_u5Ub_jbKTX4wantijQWbLdXZwKM';
      $req3 .= '&language=ja';
      $xml3 = simplexml_load_file($req3) or die('XML parsing error');

      if ($xml3->status == 'OK') {
        $location = $xml3->result->geometry->location;
        $res['lat'] = (string)$location->lat[0];
        $res['lng'] = (string)$location->lng[0];

        $address_component = $xml3->result->address_component;
        for ($i=0; $i < count($address_component); $i++) {
          $a = $address_component[$i];
          if ($a->type[0] == "administrative_area_level_1") {
            $res['prefecture'] = $a->long_name;
          }
          if ($a->type[0] == "locality" && $a->type[1] == "political") {
            $res['locality'] = $a->long_name;
          }
          if ($a->type[2] == "sublocality_level_1") {
            $res['sublocality'] = $a->long_name;
          }
        }
      }
    }
  }
  return $res;
}

// function get_address_from_gps($lat, $lng) {
//   $req = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=35.794507,139.790788&sensor=false';
//   $xml = simplexml_load_file($req) or die('XML parsing error');
// }

function insertDB($title, $url, $imageUrl, $lat, $lng, $date, $media, $prefecture, $locality, $sublocality, $isProduction) {
  $table;
  if ($isProduction) {
    $table = myTable;
  }else {
    $table = myTestTable;
  }

  $escape = array("'");
  $title = str_replace($escape, "", $title);

  $encode = array("&amp;, &quot;");
  $decode = array("\&, \"");
  $title = str_replace($encode, $decode, $title);  

  $query = "INSERT INTO ".$table."(
  title,
  url,
  imageUrl,
  location,
  date,
  media,
  prefecture,
  locality,
  sublocality
  ) VALUES(
  '".$title."',
  '".$url."',
  '".$imageUrl."',
  GeomFromText('POINT(".$lat." ".$lng.")'),
  '".$date."',
  '".$media."',
  '".$prefecture."',
  '".$locality."',
  '".$sublocality."'
  )";

  // echo $query;
  //DBに挿入
  mysql_query($query) or die(mysql_error());
}

function isAlreadyInDatabase($url, $lat, $lng, $isProduction) {
  $table;
  if ($isProduction) {
    $table = myTable;
  }else {
    $table = myTestTable;
  }
  $query = "SELECT X(location) as lat, Y(location) as lng, url FROM ".$table." where url = '".(string)$url."'";
  $result = mysql_query($query) or die(mysql_error());
  $isAlready = false;
  $responseArray = array();
  while ($row = mysql_fetch_assoc($result)) {
    if ($row["lat"] == $lat && $row["lng"] == $lng) {
      $isAlready = true;
    }
  }
  return $isAlready;
}

// $cityArray = array("江別","千歳","札幌","岩見沢","滝川","深川","小樽","倶知安","函館","長万部","江差","苫小牧","室蘭","浦河","旭川","士別","名寄","留萌","稚内","網走","北見","紋別","帯広","釧路","根室","日高","富良野","枝幸","弟子屈","青森","八戸","弘前","十和田","むつ","五所川原","盛岡","一関","釜石","北上","宮古","久慈","二戸","大船渡","花巻","奥州","仙台","石巻","大崎","気仙沼","白石","仙台駅","県庁市役所","秋田","大館","能代","由利本荘","大仙","横手","湯沢","山形","米沢","新庄","酒田","鶴岡","福島","郡山","白河","会津若松","いわき","南相馬","相馬"," 鹿嶋","古河","筑西","土浦","日立","水戸","足利","宇都宮","小山","日光","那須塩原","桐生","渋川","高崎","沼田","前橋","草津","さいたま","春日部","川越","熊谷","秩父","草加","所沢","東松山","柏","木更津","千葉","成田","浅草橋","池袋","上野","五反田","新宿","渋谷","品川","巣鴨","日本橋","赤羽","青戸","荻窪","赤羽橋","蒲田","板橋","飯田橋","大森","大原","王子","羽田","日比谷","東中野","本郷","馬込","丸子橋","三宅坂","目白","四谷","目黒","谷原","六本木","信濃町","砂町","千住","瀬田","高井戸","辰巳","高田馬場","戸田橋","等々力","成増","半蔵門","初台","晴海","亀戸","上馬","葛西","亀有","銀座","言問橋","高円寺","桜田門","大崎","三軒茶屋","新橋","四ツ木","西新井","三ノ輪","南砂","芝公園","市川橋","祝田橋","永代橋","恵比寿","大久保","大手町","御徒町","駒形橋","駒沢","笹目橋","水道橋","溜池","豊洲","八王子","秋川","五日市","あきる野","青梅","奥多摩","数馬","清瀬","狛江","小平","立川","高尾","西東京","多摩ニュータウン","調布","拝島橋","東村山","檜原","府中","町田","瑞穂","三鷹","福生","厚木","小田原","相模原","横須賀","伊勢原","江の島","鎌倉","茅ヶ崎","津久井","秦野","箱根","藤沢","松田","三崎","大和","湯河原","平塚","相模湖","横浜","磯子","市ヶ尾","新横浜","金沢","桜木町","綱島","鶴ヶ峰","鶴見","戸塚","長津田","東神奈川","保土ケ谷","関内","高島町","川崎","小杉","登戸","溝口","村上","新潟","長岡","上越","糸魚川","南魚沼","三条","十日町","魚津","富山","高岡","砺波","小松","金沢","七尾","輪島","福井","敦賀","大月","甲府","韮崎","富士吉田","飯田","上田","小諸","塩尻","諏訪","長野","松本","岐阜","高山","大垣","美濃加茂","多治見","静岡","浜松","沼津","名古屋","豊橋","豊田","津","四日市","伊勢","尾鷲","伊賀","松阪","大津","福知山","舞鶴","京都","堀川五条","大阪","大阪駅","梅田新道","難波","天王寺駅","大阪港","南港","深江橋","神戸","姫路","豊岡","洲本","三宮","奈良","大和郡山","天理","橿原","大和高田","五條","和歌山","田辺","新宮","米子","倉吉","鳥取","出雲","益田","大田","松江","浜田","津山","新見","岡山","広島","福山","三次","山口","宇部","周南","岩国","萩","下関","徳島","三好市","つるぎ","高松","松山","今治","宇和島","西条","大洲","高知","室戸","四万十市","須崎","足摺岬","福岡","久留米","大牟田","飯塚","北九州"," 唐津","鳥栖","武雄","佐賀","佐世保","諫早","島原","大村","長崎","八代","人吉","水俣","天草","宇土","熊本","荒尾","中津","日田","佐伯","大分","宇佐","別府","都城","延岡","日南","小林","宮崎","薩摩川内","鹿屋","枕崎","霧島","鹿児島","辺戸岬","沖縄","那覇","名護","海洋博公園");

$prefecture_and_famousCity_array = array("札幌","小樽","函館","旭川","稚内","釧路","富良野","青森","弘前","盛岡","仙台","気仙沼","秋田","山形","福島","水戸","宇都宮","日光","前橋","草津","川越","熊谷","所沢","千葉","成田","池袋","上野","五反田","新宿","渋谷","品川","日本橋","荻窪","蒲田","板橋","飯田橋","大森","日比谷","四谷","目黒","六本木","信濃町","高井戸","半蔵門","晴海","葛西","銀座","桜田門","大崎","三軒茶屋","新橋","恵比寿","大手町","御徒町","駒沢","水道橋","豊洲","八王子","秋川","五日市","あきる野","青梅","奥多摩","立川","高尾","調布","府中","町田","三鷹","厚木","小田原","相模原","横須賀","江の島","鎌倉","茅ヶ崎","箱根","藤沢","大和","平塚","相模湖","横浜","新横浜","金沢","桜木町","長津田","川崎","登戸","溝口","新潟","長岡","魚沼","富山","金沢","福井","甲府","塩尻","長野","松本","岐阜","静岡","名古屋","津","伊勢","大津","福知山","京都","大阪","難波","神戸","姫路","奈良","和歌山","鳥取","益田","松江","岡山","広島","山口","徳島","松山","高知","福岡","佐賀","長崎","熊本","大分","別府","宮崎","鹿児島","沖縄","那覇");


 ?>