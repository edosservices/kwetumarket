<?php

namespace App\Enums;

enum OtpPurpose: string
{
    case PhoneVerification = 'phone_verification';
    case Login = 'login';
}
