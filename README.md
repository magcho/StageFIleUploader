# StageFIleUploader
文化祭のステージ班が音源提出のために作成したHPです。
どなたでもご自由にお使いいただけます。
MITライセンスで許可された範囲であれば、個人でも商用でも自由にタダで使用、改変、再配布できます。


ソースファイル内のライセンス及びコピーライトの記載は省略します。


UIにはhttps://bootsnipp.com/snippets/KrG5l copyright (c) daleitchを使用させていただきました。

# Usage

* slack.phpを開き8~9行目を設定してください。
  ```
  $webhook_url = 'https://hooks.slack.com/services/*****... slackのwebhock URLを指定してください。';
  $channelName = '#チャンネル名を指定してください。';
  ```

* /db/login.jsonに団体ごとのIDとPWをjson形式で指定してください
  ```
  [{
      "userName": "ダミーデータ",
      "id": "Z01",
      "pass": 9344
  },
  {
      "userName": "ダミーデータ",
      "id": "Z01",
      "pass": 9344
  },
          .
          .
          .
  }]
  ```
  

  | 項目名 | 概要 |
  | :------------- | :------------- |
  | userName       | 団体名を記述してください       |
  | id | ログインIDを指定してください。必ず[A-Z][0-9]{2}の形式にしてください。(アルファベット大文字と2桁数字)
  | pass | パスワードを指定してください。必ず[0-9]{4}の形式にしてください。(４桁数字)

* サーバーのメモリー上限を大きくしてください。

  アップロードできるファイルサイズの上限を上げるために設定を変更してください。サーバにより設定方法は異なりますが、下記のように変更できる場合が多いです。

  ```
  post_max_size = 50M
  upload_max_filesize = 50M
  ```

# コピーライト
必ず以下のコピーライトを最初にアクセスするページ(ログイン画面等)に記載してください。

````
Copyright (c) 2017 MagCho
````


# LICNENSE

MIT License

Copyright (c) 2017 MagCho

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
