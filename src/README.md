# Attendance System（勤怠管理システム）

## 環境構築

### Docker ビルド

1. git clone https://github.com/starmine448357/attendance-system.git  
2. cd attendance-system/src  
3. docker compose up -d --build  

---

### Laravel 環境構築

1. docker compose exec app bash  
2. composer install  
3. cp .env.example .env  
4. .env ファイルの一部を以下のように編集  

APP_NAME="勤怠管理システム"  
APP_ENV=local  
APP_KEY=base64:A99CPMlWf3eIMZDqQK9ogxMPmgW1iKP7PxZaWPB7MDs=  
APP_DEBUG=true  
APP_URL=http://localhost  

DB_CONNECTION=mysql  
DB_HOST=mysql  
DB_PORT=3306  
DB_DATABASE=laravel_db  
DB_USERNAME=laravel_user  
DB_PASSWORD=laravel_pass  

MAIL_MAILER=smtp  
MAIL_HOST=mailhog  
MAIL_PORT=1025  
MAIL_USERNAME=null  
MAIL_PASSWORD=null  
MAIL_ENCRYPTION=null  
MAIL_FROM_ADDRESS=admin@attendance.local  
MAIL_FROM_NAME="勤怠管理システム"  

5. php artisan key:generate  
6. php artisan migrate --seed  
7. php artisan storage:link  
8. chmod -R 777 storage bootstrap/cache  

---

## ログイン情報

Seeder により初期データとして、管理者アカウント一件と一般ユーザーアカウント複数件が登録されています。  

### 管理者ユーザー  
email: admin@example.com
password: password123 

### 一般ユーザー  
email: employee1@example.com
password: password123  

---

## 使用技術

- PHP 8.2.x-fpm  
- Laravel 10.x  
- MySQL 8.0.x  
- Docker  
- Mailhog（開発用メールツール）  

---

## URL

- 開発環境: http://localhost:8080/  
- phpMyAdmin: http://localhost:8082/  
- Mailhog: http://localhost:8025/  

---

## メール認証について

開発環境では Mailhog を使用しています。  
アプリ内で送信されたメールは以下のURLから確認できます。  

http://localhost:8025/  

※Mailhogはローカル専用のメール受信ツールであり、外部送信は行われません。

---

## ダミーデータについて

Seeder により以下のダミーデータが作成されます。

- 管理者ユーザー（1件）  
- 一般ユーザー（複数件）  
- 勤怠記録データ（出勤・退勤・休憩時間）  

これにより、初期状態からログイン・勤怠一覧・勤怠詳細の表示が確認可能です。

---

## トラブルシューティング

### Permission denied エラー

storage や bootstrap/cache の書き込み権限が不足している場合は、以下を実行してください。

docker exec -it attendance-system-app-1 bash  
chmod -R 777 storage bootstrap/cache  
exit  

---

## ER 図

![ER図](src/public/images/ER.png)

---

## PHPUnitテスト

テストは PHPUnit を使用しています。  
以下のコマンドでテストデータベースを作成し、テストを実行してください。

```bash
docker-compose exec mysql bash
mysql -u root -p
# パスワードは root
create database test_database;

docker-compose exec app bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
