<?php 

header("Content-Type: text/html; charset=UTF-8");


require_once('../../config.php');
$con = mysql_connect(server, user, pass) or die(mysql_error());
mysql_select_db(myDatabase, $con) or die(mysql_error());
mysql_query('set names utf8',$con);


for ($num=0; $num < 1; $num++) { 
	$title = "東京発の新鮮野菜！若きファーマーたちが仕掛ける、新しい農業";
  $url = "https://gurutabi.gnavi.co.jp/a/a_348/";
  $imageUrl = "http://c-gurutabi.gnst.jp/public/img/article/83/1f/art000151/article_art000151_thumbnail.jpg?1453783788";
  $media = "ぐるたび";
  $date = "2015.08.06";
  $tag = "";

  $lat = 35.634159;
  $lng = 139.391456;

	$query = "INSERT INTO Curation(
	title,
	url,
	imageUrl,
	location,
  date,
	media
	) VALUES(
  '".$title."',
  '".$url."',
  '".$imageUrl."',
  GeomFromText('POINT(".$lat." ".$lng.")'),
  '".$date."',
  '".$media."'
	)";

	echo $query;

	//DBに挿入
	mysql_query($query) or die(mysql_error());
}














 ?>