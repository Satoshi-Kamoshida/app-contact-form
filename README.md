# App Contact Form

<div align="right">
<p><strong>開発者：鴨志田 悟</strong></p>
<p><strong>開発期間：2026年9月6日〜9月13日</strong></p>
</div>

## 概要

お問い合わせフォームのアプリケーションです。下記を実装致しました。

1. 一般ユーザーの入力・確認・完了ページ
2. Fortifyによる管理者の認証画面（登録・ログイン・ログアウト）
3. 管理画面：お問い合わせ内容の一覧・詳細、検索機能、お問い合わせデータの削除、タグのCRUD機能、CSV形式のデータエクスポート機能
4. 公開API：お問い合わせ一覧・詳細のCRUD機能→結果をJson形式で返す。
5. ユーザー・管理者入力に際した各種バリデーション実装

## 使用技術

- PHP 8.2
- Laravel 10.x
- MySQL 8.0
- Nginx
- Vite
- Tailwind CSS
- Docker
- Laravel Sail
- phpMyAdmin
- Fortify

## 開発環境URL

- [トップ画面](http://localhost)
- [phpMyAdmin](http://localhost:8080)

## 環境構築

### 1. Laravelプロジェクトの作成

作業ディレクトリを作成後、Laravel 10.xを指定してプロジェクトを作成

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
  laravelsail/php82-composer:latest \
  composer create-project laravel/laravel:^10.0 app-contact-form
```

### 2. Laravel Sailのセットアップ

DockerコンテナからLaravel Sailをセットアップ

```bash
docker run --rm \
 -u "$(id -u):$(id -g)" \
 -v "$(pwd):/var/www/html" \
 -w /var/www/html \
 -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
 laravelsail/php82-composer:latest \
 composer require laravel/sail --dev
```

MySQLを指定してSailの設定ファイルをパブリッシュ

```bash
docker run --rm \
 -u "$(id -u):$(id -g)" \
 -v "$(pwd):/var/www/html" \
 -w /var/www/html \
 -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
 laravelsail/php82-composer:latest \
 php artisan sail:install --with=mysql
```

**※エイリアスの設定**<br>

1. Sailをバックグラウンドで起動<br>

```bash
./vendor/bin/sail up -d
```

2. エイリアスを設定して 'sail' だけでコマンドを実行できるようにする

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc
```

### 3. .env ファイルの設定確認

.envファイルが下記と一致している事を確認する。

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 4. PHP / Laravel / MySQLのバージョン確認

確認方法

```bash
sail php --version
sail artisan --version
sail mysql --version
```

> ⚠️ 技術スタックが指定と異なった為、下記要領にてPHP/MySQLを変更

compose.yamlを確認<br>
↓<br>
PHP 8.5 → 8.2<br>
MySQL 8.4 → 8.0<br>
↓<br>
Dockerイメージを再ビルド<br>

```bash
sail down
sail build --no-cache
sail up -d
```

↓<br>
バージョン確認<br>

**MySQLのバージョン変更によるエラー**<br>
MySQL 8.4から8.0へ変更した際、既存のMySQLボリュームに8.4のデータが残っていたため、MySQLのダウングレードエラーが発生。下記コマンドでボリュームを削除し、再構築。

```bash
sail down -v
sail up -d
```

### 5. データベース接続確認

LaravelからMySQLへ接続できることを確認します。

```bash
sail artisan migrate
```

### 6. phpMyAdmin

phpMyAdminをDocker Composeに追加し、ブラウザからMySQLデータベースを確認

**追加したコード（MySQLと同じインデントへ）**

```yaml
phpmyadmin:
    image: "phpmyadmin:latest"
    ports:
        - "${FORWARD_PHPMYADMIN_PORT:-8080}:80"
    environment:
        PMA_HOST: mysql
        PMA_USER: "${DB_USERNAME}"
        PMA_PASSWORD: "${DB_PASSWORD}"
    networks:
        - sail
    depends_on:
        - mysql
```

### 7. フロントエンド環境

提供頂いたbladeファイルに伴い、Tailwind CSS及びAlpine.jsをインストール・設定

**Tailwind CSSのインストール**

```bash
sail npm install -D tailwindcss@^3.4.0 postcss autoprefixer
```

**Alpine.jsのインストール**

```bash
sail npm install alpinejs
```

**Tailwind CSSの設定ファイル作成**

```bash
sail npx tailwindcss init -p
```

**tailwind.config.jsの設定**

```js
/** @type {import("tailwindcss").Config */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
```

**Bladeファイルの配置**<br>
提供されたBladeファイルをresourcesディレクトリへ置換

**Viteの起動**
※Vite：CSSやJavaScriptなどのフロントエンドファイルを監視・ビルドしてくれる開発用サーバー<br>

> ⚠️ 注意点：CSS・JavaScriptなどのフロントエンド処理が発生する場合に、別のターミナルで起動する。

```bash
sail npm run dev
```

## ER図

![ER図](doc/確認テスト-ER図_最終版.png)

## Git開発におけるルーティーン

1. Issueの検討<br>

2. mainへ移動<br>
   git switch main

3. mainを最新化<br>
   git pull origin main

4. Issue用ブランチを作成<br>
   git switch -c feature/作業内容

5. 作業<br>

6. 変更点の確認<br>
   git status

7. ステージング<br>
   git add .

8. コミット<br>
   git commit -m "作業内容"

9. GitHubへPush<br>
   git push -u origin feature/作業内容

10. GitHubでPR作成・Merge<br>

11. mainへ戻る<br>
    git switch main

12. mainを最新化<br>
    git pull origin main

13. 作業ブランチを削除<br>
    git branch -d feature/作業内容

## 環境構築後のアプリ制作手順・詰まった所

### migration・model・seederの作成

1. テーブル作成（外部キー・ユニーク制約設定）
2. モデル作成（リレーション設定）
3. Factoryの設定（お問い合わせ20件登録用）
4. 各Seeder及びDatabaseSeederの作成（初期データ投入）

> ⚠️ **詰まった所**

1. **１〜３個のタグをランダムに取得し、中間テーブルに登録する。**

```php
$tagIds = Tag::inRandomOrder()
    ->limit(fake()->numberBetween(1, 3))
    ->pluck('id');
$contact->tags()->attach($tagIds);
```

2. **外国人データでseedしてしまった為、config/app.phpを変更した。**

```php
'faker_locale' => 'en_US',
```

↓<br>

```php
'faker_locale' => 'ja_JP',
```

3. **Sail testがFail（HTTPステータス：500）**

事前提供bladeの置換の際、デフォルトのresourcesを削除し、welcome.blade.phpを削除した事により、初期のExample Testが機能しなかった。<br>
→画面実装時にルートとテストを修正する。
