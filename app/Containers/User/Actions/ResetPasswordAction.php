<?php

namespace App\Containers\User\Actions;

use App\Containers\User\UI\API\Requests\ResetPasswordRequest;
use App\Ship\Exceptions\InternalErrorException;
use App\Ship\Parents\Actions\Action;
use App\Ship\Parents\Exceptions\Exception;
use App\Ship\Transporters\DataTransporter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordAction extends Action
{
    public function run(ResetPasswordRequest $data): void
    {
        $sanitizedData = $data->sanitizeInput([
            'email',
            'token',
            'password'
        ]);

        $sanitizedData['password_confirmation'] = $data->password;

        try {
            Password::broker()->reset(
                $sanitizedData,
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );
        } catch (Exception $e) {
            throw new InternalErrorException();
        }
    }
}
