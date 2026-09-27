<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\Commerce\ReferralService;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['phone'] = PhoneNumber::normalize($input['phone'] ?? null);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => PhoneNumber::rules(),
            'password' => $this->passwordRules(),
            'referral' => ['nullable', 'string', 'max:16'],
        ])->validate();

        $user = User::query()->create([
            'name' => $input['name'],
            'first_name' => ($input['first_name'] ?? '') !== '' ? $input['first_name'] : null,
            'last_name' => ($input['last_name'] ?? '') !== '' ? $input['last_name'] : null,
            'email' => $input['email'],
            'phone' => $input['phone'],
            'locale' => config('app.locale'),
            'currency' => config('twende.currency.default'),
            'password' => Hash::make($input['password']),
        ]);

        app(ReferralService::class)->attach($user, $input['referral'] ?? null);

        return $user;
    }
}
