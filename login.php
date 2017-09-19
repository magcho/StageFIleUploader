<?php
require_once 'slack.php';
date_default_timezone_set('Asia/Tokyo');


/**
 * ログイン情報が有効か確認し、有効ならばupload.phpへ飛ばし、無効ならば再表示
 */


/**
* 整形されたログを吐くメソッド
* @param  {なんでもいい} $var 中身を見たい変数
*/
function v($var){
  echo '<pre>';
  var_dump($var);
  echo '</pre>';
}

function loginFailed($id,$pass){
  //ログイン失敗
  $data = [
    'id' => $id,
    'pass' => $pass
  ];
  sendSlack('unlogin',$data);
}
/**
 * ログイン情報が乗ってるテーブルを連想配列で返すだけ
 * @return {array} ログイン情報
 */
function fetchLoginJson(){
  chdir(__DIR__);  // ワークディレクトリを戻す
  chdir("./db");  //ワークディレクトリを../dbに変更
  $json = file_get_contents("login.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  return json_decode($json,true); //連想配列に変換
}
/**
 * セッションの発行ログ
 * @param {string} $id      ユーザーid
 * @param {string} $userName ユーザー名（団体名）
 * @param {string} $session 発行したsession
 * @param {int}    $ip      ユーザーのIPアドレス
 * @return {boolean}
 */
function addSesstion($id,$userName,$session,$ip){
  chdir(__DIR__);  // ワークディレクトリを戻す
  chdir("./db");  //ワークディレクトリを../dbに変更
  $json = file_get_contents("sessionLog.json");
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
    "code" =>  substr(mt_rand(10000, 19999), 1, 4),
    "userName" => $userName,
    "ip" => $ip
  ];
  $data[] = $addData;
  file_put_contents('sessionLog.json',sprintf(json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),LOCK_EX));
}



$loginTable = fetchLoginJson();
$id = strtoupper(mb_convert_kana($_POST['id'],'a')); // 全角を半角へ変換し大文字に変換
$pass = mb_convert_kana($_POST['pass'],'a'); // 全角を半角へ変換



foreach ($loginTable as $value) {
  // ログインの正当性チェック
  if($value['id'] == $id){
    if($value['pass'] == $pass){
      $session = md5(date(DATE_RFC2822)); // session発行
      addSesstion($id,$value['userName'],$session,ip2long($_SERVER["REMOTE_ADDR"]));
      $jump = "location: upload.php?id={$id}&session={$session}";
      header($jump);
      exit();
    }else{
      loginFailed($id,$pass);
      break;
    }
  }
}
loginFailed($id,$pass);

?>
<!DOCTYPE html>
<html lang="jp">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LiSA 文化祭委員 ステージ班 音源回収HP</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="./resource/css/common.css" />
    <link rel="stylesheet" href="./resource/css/index.css" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">LiSA 文化祭 ステージ班 音源回収</h1>
          <h2 class="text-center">LiSA Papillon Festival 2017</h1>
        </div>
      </div>
      <div class="alert alert-danger alert-dismissible text-center" role="alert">
        <p class="icon"><span class="glyphicon glyphicon-warning-sign"></span></p><p><strong>ログインできませんでした！</strong></p><p>セットリストに書かれている出演者No.とパスワードを確認してください</p>
      </div>
      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">ログイン</h3>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-md-10 col-md-offset-1 login">
                  <form method="POST" action="./login.php" id="login">
                    <div class="form-group">
                      <label for="no">出演者No.</label>
                      <input type="text" class="form-control" name="id" placeholder="アルファベットと数字で構成されています。(例)「A12」">
                      <p class="help-block">出演者No.は先日配布したセットリストに記載されています</p>
                    </div>
                    <div class="form-group">
                      <label for="pass">パスワード</label>
                      <input type="text" class="form-control" name="pass" placeholder="４桁の数字です">
                      <p class="help-block">パスワードも先日配布したセットリストに記載されています</p>
                    </div>
                    <button id="submit" form="login" name="button" class="btn btn-primary pull-right">ログイン</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>



      </div>
    </div>




    <footer class="container-fluid">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <p class="text-center">Copyright (c) 2017 MagCho</p>
        </div>
      </div>
    </footer>



    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="./resource/js/index.js" charset="utf-8"></script>
    <script>
      +function(){
        $(".alert").alert();
      }
    </script>
  </body>
</html>
