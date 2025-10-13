

@section('content')
<div class="register-container">
    <h1>会員登録</h1>

    <form method="POST" action="{{route('register')}}">
        @csrf

        <div class="form-group">
            <label for="name">名前</label>
            <input id="name" type="text" name="name" value="{{old('name')}}" required autofocus>
            @error('name')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{old('email')}}" required>
            @error('email')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" required>
            @error('password')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">パスワード確認</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
            @error('password_confirmation')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">登録する</button>
    </form>
    <a href="{{ route('login') }}">ログイン</a>
</div>

