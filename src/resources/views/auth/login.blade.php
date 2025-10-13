<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
</head>

<body>
    <h1>ログイン画面</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">メールアドレス</label><br>
        <input id="email" type="email" name="email" required autofocus><br><br>

        <label for="password">パスワード</label><br>
        <input id="password" type="password" name="password" required ><br><br>

        <button type="submit">ログイン</button>

    </form>
</body>

</html>