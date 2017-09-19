<?php
require_once 'slack.php';
date_default_timezone_set('Asia/Tokyo');


/**
 * セッションの発行ログ
 * @param {string} $id      ユーザーid
 * @param {string} $session 発行したsession
 * @return {boolean}
 */
function addfilelog($id,$session){
  chdir(__DIR__);  // ワークディレクトリを戻す
  chdir("./db");  //ワークディレクトリを../dbに変更
  $json = file_get_contents("upfilelog.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  $data = json_decode($json,true); //連想配列に変換;

  $addData = [
    "id" => $id,
    "session" => $session,
    "date" => [
      "month" => date(m),
      "date" => date(d),
      "hour" => date(H),
      "minute" => date(i),
      "second" => date(s)
    ],
    "code" => str_pad($_GET['code'],4, 0, STR_PAD_LEFT)
  ];
  foreach ($_FILES["files"]["name"] as $key => $value) {
    $tmp['originName'] = $value;
    $tmp['saveName'] = $_GET['session'].'@'.$key.'@'.date(mdHis).'@'.$value;
    $fileinfo[] = $tmp;
  }


  // 過去にこのセッションでアップロードしたことがあるかチェック
  $alreadyUpload = false; // 過去にこのセッションでアップロードしたことがあるかフラグ
  foreach ($data as $value) {
    if($_GET['session'] == $value['session']){
      $alreadyUpload = true;
    }
  }
  if($alreadyUpload){
    // このセッションでアップロードしたことがある => 同一セッションのファイル配列に追記
    foreach ($data as $key => $value) {
      if($_GET['session'] ==  $value['session']){
        foreach ($fileinfo as $key1 => $value1) {
          $data[$key]['files'][] = $value1;
        }
      }
    }
  }else{
    // 新規のアップロード => ログの末端に追記
    $addData['files'] = $fileinfo;
    $data[] = $addData;
  }

  file_put_contents('upfilelog.json',sprintf(json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),LOCK_EX));
}



/**
 * [upload description]
 * @return [type] [description]
 */
function upload() {

  $count = count($_FILES['files']['tmp_name']);

  for ($i = 0 ; $i < $count ; $i ++ ) {

    $tmp_name = $_FILES["files"]["tmp_name"][$i];
    if (!is_uploaded_file($tmp_name)) {
      continue;
    }
    move_uploaded_file($tmp_name, './upfile/'.$_GET['session'].'@'.$i.'@'.date(mdHis).'@'.$_FILES["files"]["name"][$i]);
  }
  addfilelog($_GET['id'],$_GET['session']);
}
/**
 * アップロードされるとslackに通知を送信する
 */
function postSlack(){
  // ユーザ情報の取得
  chdir(__DIR__);  // ワークディレクトリを戻す
  chdir("./db");  //ワークディレクトリを../dbに変更
  $json = file_get_contents("login.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  $data = json_decode($json,true); //連想配列に変換;
  foreach ($data as $value) {
    if($value['id'] == $_GET['id']){
      $userName = $value['userName'];
      break;
    }
  }
  $data = [
    'userName' => $userName,
    'session' => $_GET['session']
  ];
  sendSlack('upload',$data);
}
upload();
postSlack();
