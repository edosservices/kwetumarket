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

        if (array_key_exists('whatsapp', $input)) {
            $input['whatsapp'] = PhoneNumber::normalize($input['whatsapp'] ?? null);
        }

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
            'first_name' => ['sometimes', 'nullable', 'string', 'max:80'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:80'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'country' => ['sometimes', 'nullable', 'string', 'max:80'],
            'province' => ['sometimes', 'nullable', 'string', 'max:80'],
            'city' => ['sometimes', 'nullable', 'string', 'max:80'],
            'commune' => ['sometimes', 'nullable', 'string', 'max:80'],
            'quarter' => ['sometimes', 'nullable', 'string', 'max:80'],
            'address' => ['sometimes', 'nullable', 'string', 'max:180'],
        ])->validateWithBag('updateProfileInformation');

        $profile = [
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'locale' => $input['locale'],
            'currency' => $input['currency'],
        ];

        foreach (['first_name', 'last_name', 'whatsapp', 'country', 'province', 'city', 'commune', 'quarter', 'address'] as $field) {
            if (array_key_exists($field, $input)) {
                $profile[$field] = $input[$field] !== '' ? $input[$field] : null;
            }
        }

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
