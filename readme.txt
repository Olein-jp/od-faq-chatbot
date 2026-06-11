=== OD FAQ Chatbot ===
Contributors: olein
Tags: faq, chatbot, ai
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 0.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress AI Connector を利用して、公開コンテンツを根拠に回答する FAQ チャットボットを提供します。

== Description ==

OD FAQ Chatbot は、公開済み投稿・固定ページ・公開カスタム投稿タイプをナレッジベース化し、フロントにフローティング形式の FAQ チャットを表示するプラグインです。

MVP では Embedding やベクトル検索を使わず、事前生成した JSON ナレッジベースに対するキーワード検索で関連チャンクを抽出します。AI API キーはプラグイン側では保存せず、WordPress AI Connector 側の設定を利用します。

== Installation ==

1. プラグイン ZIP をアップロードします。
2. プラグインを有効化します。
3. WordPress AI Connector を設定します。
4. AI FAQ の管理画面で対象投稿タイプを選択します。
5. ナレッジベースを生成します。
6. フロント画面でチャット表示を確認します。

== Development ==

ローカル開発環境は `@wordpress/env` を利用します。

* `npm run env:start`
* `npm run env:stop`
* `npm run env:cli -- plugin list`
* `npm run lint:php`

== Release ==

`main` ブランチ上のコミットに `1.2.3` のようなバージョン番号タグを付けると、GitHub Actions が WordPress プラグイン用 ZIP を作成し、GitHub Release に `od-faq-chatbot.zip` として添付します。

タグ名は `od-faq-chatbot.php` の `Version` ヘッダーと一致している必要があります。

== Changelog ==

= 0.0.1 =
* Initial MVP scaffold.
