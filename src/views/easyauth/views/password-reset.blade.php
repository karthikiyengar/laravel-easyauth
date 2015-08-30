<!-- resources/views/auth/reset.blade.php -->
<style>
    body, h1 {
        font-family: 'Lato', sans-serif;
    }
    tr, td {
        padding:10px;
    }
</style>

<h1>Reset Password</h1>

@if (!empty($message))
    <div class="alert alert-danger">
        <ul>
            <li>{{ $message }}</li>
        </ul>
    </div>
@endif

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ url('auth/password-reset') }}">
    {!! csrf_field() !!}

    <input type="hidden" name="token" value="{{ $token }}">

    <table>
        <tr>
            <td>
                Email
            </td>
            <td>
                <input type="email" name="email" value="{{ old('email') }}">
            </td>
        </tr>

        <tr>
            <td>
                Password
            </td>
            <td>
                <input type="password" name="password">
            </td>
        </tr>

        <tr>
            <td>
                Confirm Password
            </td>
            <td>
                <input type="password" name="password_confirmation">
            </td>
        </tr>
    </table>
    <div>
        <button type="submit">
            Reset Password
        </button>
    </div>
</form>