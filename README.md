# OD FAQ Chatbot

WordPress AI Connector を利用して、公開コンテンツを根拠に回答する FAQ チャットボットを提供する WordPress プラグインです。公開済み投稿・固定ページ・公開カスタム投稿タイプからナレッジベースを生成し、フロント画面にフローティング形式のチャット UI を表示します。

利用マニュアルは `docs/manual.md` に別途作成します。

## 主な機能

- 公開済み投稿、固定ページ、公開カスタム投稿タイプを対象にしたナレッジベース生成
- 変更コンテンツのみ、または全件のナレッジベース再生成
- WordPress AI Connector 経由の回答生成
- 参照元 URL 付きの回答表示
- チャット表示の有効化、利用対象、除外ページ、除外投稿タイプの設定
- 初回メッセージ、回答口調、AI 人格設定、回答不能時メッセージ、注意文の編集
- 質問ログ、未回答質問ログの管理と CSV エクスポート
- GitHub Releases 経由のプラグイン更新

## 必要なもの

- WordPress 6.5 以上
- PHP 7.4 以上
- WordPress AI Connector
- ローカル開発時:
  - Docker Desktop
  - Node.js
  - npm
  - Composer

## セットアップ

```bash
npm install
composer install
npm run env:start
```

起動後の URL は以下です。

- WordPress: http://localhost:8888
- 管理画面: http://localhost:8888/wp-admin
- ユーザー名: `admin`
- パスワード: `password`

プラグインを有効化します。

```bash
npm run env:cli -- plugin activate od-faq-chatbot
```

## 管理画面

管理画面では、以下のメニューを利用できます。

- AI FAQ: プラグイン状態、AI Connector 接続状態、ナレッジベース、ログの概要
- AI FAQ > ナレッジベース: 対象投稿タイプの選択、全件再生成、変更コンテンツのみ再生成、学習済みページ一覧
- AI FAQ > チャット設定: 初回メッセージ、回答口調、AI 人格設定、回答不能時メッセージ、注意文、参照チャンク数、回答文字数、ログ保存期間
- AI FAQ > 表示設定: チャット表示、利用対象、除外ページ、除外投稿タイプ、アンインストール時のデータ削除
- AI FAQ > ログ: 質問ログ、未回答質問ログ、CSV エクスポート、ログ削除

AI API キーはこのプラグインでは保存しません。回答生成には WordPress AI Connector 側で設定された Provider を利用します。

## よく使うコマンド

```bash
npm run env:start
npm run env:stop
npm run env:cli -- plugin list
npm run env:cli -- plugin activate od-faq-chatbot
npm run lint:php
composer run lint
composer run phpcs
composer run phpcbf
```

## 構成

- `od-faq-chatbot.php`: プラグインのメインファイル
- `includes/`: 設定、ナレッジ生成、検索、AI 連携、REST API、ログ保存などの PHP クラス
- `admin/`: 管理画面の画面、スタイル、保存処理
- `public/`: フロント画面のチャット表示とアセット
- `templates/`: フロント画面に出力するテンプレート
- `readme.txt`: WordPress.org 形式のプラグイン情報
- `.wp-env.json`: ローカル WordPress 環境設定
- `phpcs.xml.dist`: PHP_CodeSniffer / WordPress Coding Standards 設定

## ナレッジベースとログ

ナレッジベースとログは WordPress の uploads 配下にある `ai-faq-chatbot` ディレクトリへ保存されます。アンインストール時に設定、ナレッジベース、ログを削除するかどうかは、管理画面の「表示設定」から選択できます。

## REST API

フロントのチャット UI は以下の REST API を利用します。

```text
POST /wp-json/od-faq-chatbot/v1/ask
```

リクエストには `question` を含めます。REST API は WordPress REST nonce とチャット利用可否を検証します。

## リリース

`main` ブランチ上のコミットに `0.0.1` のようなバージョン番号タグを付けると、GitHub Releases 用のリリースフローで WordPress プラグイン用 ZIP を作成します。

```bash
git tag 0.0.1
git push origin 0.0.1
```

タグ名は `od-faq-chatbot.php` の `Version` ヘッダー、`OD_FAQ_CHATBOT_VERSION`、`readme.txt` の `Stable tag` と一致させてください。

GitHub Release に添付される ZIP は、WordPress プラグインとしてインストールできる形式で作成します。`node_modules/` や `.wp-env/` は含めず、プラグイン本体と Composer 依存の `vendor/` を含めます。

管理画面からのアップデート確認には `inc2734/wp-github-plugin-updater` を利用します。GitHub Releases の最新リリースに添付された ZIP が、WordPress のプラグイン更新パッケージとして使われます。

## ライセンス

GPL-2.0-or-later
