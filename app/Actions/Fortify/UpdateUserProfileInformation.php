<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $input['phone'] = PhoneNumber::normalize($input['phone'] ?? null);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'phone' => PhoneNumber::rules($user->id),
            'locale' => ['required', 'string', Rule::in(config('twende.locales'))],
            'currency' => ['required', 'string', Rule::in(config('twende.currencies'))],
        ])->validateWithBag('updateProfileInformation');

        $profile = [
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'locale' => $input['locale'],
            'currency' => $input['currency'],
        ];

        if ($input['phone'] !== $user->phone) {
            $profile['phone_verified_at'] = null;
        }

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $profile['email_verified_at'] = null;
            $user->forceFill($profile)->save();
            $user->sendEmailVerificationNotification();

            return;
        }

        $user->forceFill($profile)->save();
    }
}
