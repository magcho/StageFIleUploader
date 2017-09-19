
function onDragOver(event) {
    event.preventDefault();
}
/**
 * URLをパースしてGET値のオブジェクトにする
 * @returns {{}} GET値のオブジェクトです。
 */
const purseQuery = function () {
    const result = {};
    const query = decodeURIComponent(location.search);
    const query_ary = query.substring(1).split("&");
    for (let item of query_ary) {
        let match_index = item.search(/=/);
        let key = "";
        if (match_index !== -1) {
            key = item.slice(0, match_index);
        }
        let value = item.slice(item.indexOf("=", 0) + 1);
        if (key !== "") {
            result[key] = value
        }
    }
    return result
};
// ファイルのアップロード処理
var uploadFiles = function(files) {
  $('#upload-log').html('送信中です。');
  $('.progress-bar').attr('aria-valuenow', '0');
  $('.progress-bar').attr('style', 'width: 0%');
  $('.sr-only').html('0% Complete');
  // FormDataオブジェクトを用意
  var fd = new FormData();

  // ファイルの個数を取得
  var filesLength = files.length;

  // ファイル情報を追加
  for (var i = 0; i < filesLength; i++) {
    // filename.append(files[i]["name"]);
    if(files[i]["name"].indexOf('.aac') !== -1 || files[i]["name"].indexOf('.mp3') !== -1){
      if(files[i].size >= 52428800){ // 50MB
        $('#file-list').html('<a href="#" class="list-group-item list-group-item-danger">'+files[i]["name"]+'  <strong>※50MB以下にしてください。</strong></a>'+$('#file-list').html());
        $('#upload-log').html('ファイルサイズが大きすぎます、50MB以下にしてください');
        console.log(files[i].size);
        return false;
      }
      $('#file-list').html('<a href="#" class="list-group-item list-group-item-info">'+files[i]["name"]+'</a>'+$('#file-list').html());
      fd.append("files[]", files[i]);
    }else{
      console.log(files[i]["name"]);
      $('#file-list').html('<a href="#" class="list-group-item list-group-item-danger">'+files[i]["name"]+'  <strong>※aac, mp3以外はアップロードできません</strong></a>'+$('#file-list').html());
      $('#upload-log').html('aac, mp3ファイル以外はアップロードできません、選択し直してください。');
      return false;
    }
  }

  // Ajaxでアップロード処理をするファイルへ内容渡す
  let getValue = purseQuery();
  $.ajax({
    url: './post.php?session='+getValue.session+'&id='+getValue.id+'&code='+codeNumber,
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    xhr: function() {
      var XHR = $.ajaxSettings.xhr();
      XHR.upload.addEventListener('progress', function(e) {
        var progre = parseInt(e.loaded / e.total * 100);
        // $('#prog').val(progre);
        $('.progress-bar').attr('aria-valuenow', progre);
        $('.progress-bar').attr('style', 'width: '+progre+'%');
        $('.sr-only').html(progre+'% Complete');
      });
      return XHR;
    }


  }).done(function(data) {
    console.log(data);
    $('#upload-log').text('提出したいファイルを送信してください。');
    $('#file-list a').removeClass('list-group-item-info');
    $('#file-list a').addClass('list-group-item-success');
    $('#success').click();
  }).fail(function(data) {
    $('#file-list a').addClass('list-group-item-danger');
    console.log(data.responseText);
  });
};

// ファイルドロップ時の処理
$('#drop-zone').on('drop', function(e) {
  // デフォルトの挙動を停止
  e.preventDefault();

  // ファイル情報を取得
  var files = e.originalEvent.dataTransfer.files;
   uploadFiles(files);


  // デフォルトの挙動を停止　これがないと、ブラウザーによりファイルが開かれる
}).on('dragenter', function() {
  return false;
}).on('dragover', function() {
  return false;
});


// ボタンを押した時の処理
// $('#btn').on('click', function() {
//   // ダミーボタンとinput[type="file"]を連動
//   $('#file_selecter').click();
// });

$('#file-upload').on('change', function() {
// ファイル情報を取得
  var files = this.files;
  uploadFiles(files);
});
