Hi {{ $user->first_name }},

Your password reset link: {{ url('/auth/password-reset?token=' . $token) }}