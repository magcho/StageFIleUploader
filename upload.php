<?php
require_once 'slack.php';
date_default_timezone_set('Asia/Tokyo');

  // ユーザ情報の取得
  chdir(__DIR__);  // ワークディレクトリを戻す
  chdir("./db");  //ワークディレクトリを../dbに変更
  $json = file_get_contents("sessionLog.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  $data = json_decode($json,true); //連想配列に変換;
  foreach ($data as $value) {
    if($value['session'] == $_GET['session']){
      $code = $value['code'];
      $userName = $value['userName'];
      break;
    }
  }

/**
 * このsessionの期限が切れていないかチェック
 * @param  {string} $session [description]
 * @return {boolean}          [description]
 */
function checkSesstion($session){
  chdir(__DIR__);  // ワークディレクトリを戻す
  chdir("./db");  //ワークディレクトリを../dbに変更
  $json = file_get_contents("sessionLog.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  $data = json_decode($json,true); //連想配列に変換;

 // セッションの有効期限は２時間まで
  $nowTime = [
    "month" => date(m),
    "date" => date(d),
    "hour" => date(H),
    "minute" => date(i),
    "second" => date(s)
  ];
  $nowTime = $nowTime['second']+$nowTime['minute']*60+$nowTime['hour']*60*60+$nowTime['date']*60*60*24;
  foreach ($data as $key => $value) {
    if($value['session'] == $session){
      $time = $value['date'];
      $ip = $value['ip'];
    }
  }
  $time = $time['second']+$time['minute']*60+$time['hour']*60*60+$time['date']*60*60*24;
  if($nowTime-$time <= 7200){
    // 2時間 60*60*2 = 7200
      // セッションを発行したIPのみ許可
      if($ip == ip2long($_SERVER["REMOTE_ADDR"])){
        return true;
      }else{
        return false;
      }
  }else{
    return false;
  }
}



if(isset($_GET['session'])){
  if(checkSesstion($_GET['session'])){
    // セッションが有効の場合
  }else{
    // セッションが無効の場合
    header('location: index.php');
  }
}else{
  // セッションがURLにない
  // location: upload.php?id={$id}&session={$session}
  header('location: index.php');
}

//slack通知
$data = [
  'userName'=> $userName,
  'session'=> $_GET['session']
];
sendSlack('login',$data);

?>
<!DOCTYPE html>
<html lang="jp">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LiSA 文化祭 | <?=$userName?>でログインしています。</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="./resource/css/common.css" />
  <link rel="stylesheet" href="./resource/css/upload.css" />
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
  <script>
  let codeNumber = <?=$code?>;
  </script>
  <div class="container">
    <div class="row">
      <div class="col-md-9">
        <h2 class=""><?=$userName?>でログインしています</h2>
      </div>
      <div class="col-md-3">
        <a href="./help.html"><button type="button" name="button" class="btn btn-primary" id="logout"><span class="glyphicon glyphicon-question-sign"></span>  ヘルプ</button></a>
        <a href="./"><button type="button" name="button" class="btn btn-warning" id="logout"><span class="glyphicon glyphicon-log-out"></span>  ログアウト</button></a>
      </div>
      <div class="col-md-12 line"></div>
    </div>


    <!--
      /**
       * https://bootsnipp.com/snippets/KrG5l
       * copyright (c) daleitch
       * create by daleitch
       * LICENSE: MIT license.
      */
      -->
    <div class="row">
      <div class="col-md-7">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>ファイルのアップロード</strong> <small> </small></div>
          <div class="panel-body">

            <div class="upload-drop-zone" id="drop-zone" ondragover="onDragOver(event)">
              <p>ここに提出するファイルをドロップ</p>
              <p>または</p>
              <div class="btn btn-default image-preview-input"> <span class="glyphicon glyphicon-folder-open"></span> <span class="image-preview-input-title">ファイルを選択</span>
                <form enctype="multipart/form-data">
                <input id="file-upload" type="file" accept="audio/mpeg,audio/aac" name="input-file-preview" multiple="multiple" />
                </form>
                <!-- rename it -->
              </div>
            </div>
            <br />
            <!-- Progress Bar -->
            <div class="progress">
              <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                <span class="sr-only"></span>
              </div>
            </div>
            <br />
            <!-- Upload Finished -->
            <div class="js-upload-finished">
              <h4 id="upload-log">提出したいファイルを送信してください。</h4>
              <div class="list-group" id="file-list">
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">過去に提出したファイル</h3>
          </div>
          <div class="panel-body">
            <table class="table table-striped table-hover">
              <thead class="thead-default">
                <tr>
                  <td>提出日</td>
                  <td>ファイル数</td>
                  <td>提出コード</td>
                </tr>
              </thead>
              <tbody>
                <?php
                chdir(__DIR__);  // ワークディレクトリを戻す
                chdir("./db");  //ワークディレクトリを../dbに変更
                $json = file_get_contents("upfilelog.json");
                $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
                $data = json_decode($json,true); //連想配列に変換;
                $exit_flag = false;
                for($i = count($data); $i >= 0; $i--){
                  if($data[$i]['id'] == $_GET['id']){
                    if(count($data[$i]['files']) !== 0){
                      echo '<tr>';
                        echo "<td>{$data[$i]['date']['month']}/{$data[$i]['date']['date']},{$data[$i]['date']['hour']}:{$data[$i]['date']['minute']}</td>";
                        echo "<td>".count($data[$i]['files'])."</td>";
                        echo "<td>".str_pad($data[$i]['code'],4,0,STR_PAD_LEFT)."</td>";
                      echo '</tr>';
                      $exit_flag = true;
                    }
                  }
                }
                if(!$exit_flag){
                  // 過去にアップロードしたことがない場合
                  echo "<td colspan=3 class=text-center >{$userName}が過去に提出したデータはありません</td>";
                }
                 ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>




      <!-- モーダルウィンドウを呼び出すボタン -->
<button type="button" id="success" class="btn btn-primary" data-toggle="modal" data-target="#myModal" style="display:none">クリックするとモーダルウィンドウが開きます。</button>

<!-- モーダルウィンドウの中身 -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-success text-center"><span class="glyphicon glyphicon-check"></span>  アップロードが完了しました。</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5 col-md-offset-1 col-sm-12 col-xs-12">
            <div class="codetitle">提出コード</div>
          </div>
          <div class="code col-md-6 col-xs-10 col-xs-offset-1 col-sm-12 col-sm-offset-1">
            <?php
            echo '<div>'.substr($code,0,1).'</div>';
            echo '<div>'.substr($code,1,1).'</div>';
            echo '<div>'.substr($code,2,1).'</div>';
            echo '<div>'.substr($code,3,1).'</div>';
            ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-10 col-md-offset-1 text-center" id="code-descpretion">
            提出コードをセットリストに書き写してください。
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">閉じる</button>
       </div>
    </div>
  </div>
</div>

    </div><!-- colse row -->

  </div><!-- close container -->
  <footer class="container-fluid">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <p class="text-center">Copyright (c) 2017 MagCho</p>
      </div>
    </div>
  </footer><!-- colse container-fluid -->



  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script src="./resource/js/upload.js"></script>
</body>

</html>
