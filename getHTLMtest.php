<?php 
    // header("Content-type: text/html charset=UTF-8"); 
    //対象URL取得
    $geturl = $_GET{'geturl'};

    if($geturl != ""){
        // HTMLソース取得
        $html = file_get_contents($geturl);
        if($html != ""){
            // あれやこれやと整形
            $html = htmlspecialchars($html);
            $html = mb_ereg_replace('\r\n', '<br />', $html);
            $html = mb_ereg_replace('\n', '<br />', $html);
            $html = mb_ereg_replace('\r', '<br />', $html);

            // $html = mb_convert_encoding($html, "sjis-win", "UTF-8, sjis-win, eucjp-win, JIS");
            $html = mb_convert_encoding($html,"SJIS-win", "ASCII,JIS,UTF-8,EUC-JP,SJIS"); 
            // $html = mb_convert_encoding($html, "UTF-8", "UTF-8, sjis-win, eucjp-win, JIS");

             // header("Content-type: text/html charset=Shift_JIS");
            echo $html;
            
            $info_msg = "ファイルの取得に成功しました";
        }else{
            $info_msg = "ファイルの取得に失敗しました";
        }
    }else{
        $info_msg = "取得対象URLを入力して下さい";
    }
?>