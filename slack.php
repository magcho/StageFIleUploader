<?php
/**
 * slackへ通知を送信する
 * @param {string} $mode [login|unlogin|upload]
 * @param  {string} $text 送信する文章
 */
function sendSlack($mode,$data) {
	$webhook_url = 'https://hooks.slack.com/services/*****... slackのwebhock URLを指定してください。';
	$channelName = '#チャンネル名を指定してください。';



  switch ($mode) {
    case 'login':
      $title = ':o:新規ログインがありました';
      $icon_emoji = ":o:";
      $text = "「{$data['userName']}」さんがログインしました\nsession: {$data['session']}";
      break;

    case 'unlogin':
    $title = ':x:ログインの失敗がありました';
    $icon_emoji = ":x:";
    $text = "id: {$data['id']},\npass: {$data['pass']}\nへのログインの失敗がありました。";
      break;

    case 'upload':
      $title = ':file_folder:ファイルが提出されました。';
      $icon_emoji = ":file_folder:";
      $text = "「{$data['userName']}」さんがファイルを提出しました\nsession: {$data['session']}";
      break;
    default:
      break;
  }

  $message = [
    'username' => 'BotName',
    // 'text' => $text,
    'attachments' => [
      [
        'title' => $title,
        // 'title_link' => 'http:/lbt.webcrow.jp',
        'text' => $text,
      ]
    ],
    "channel" => $channelName,
    'icon_emoji' => "$icon_emoji"
  ];
  $contents = json_encode($message,JSON_UNESCAPED_UNICODE);
  $options = array(
    'http' => array(
      'method' => 'POST',
      'header' => 'Content-Type: application/json',
      'content' => $contents
    )
  );
  $msg = 'payload=' . urlencode($contents);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $webhook_url);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
  curl_exec($ch);
  curl_close($ch);
}
