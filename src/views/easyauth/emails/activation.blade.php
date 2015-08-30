Hi {{ $user->first_name }},

Please use this link to activate your account: {{ url('/auth/register/confirm?token=' . $token) }}