<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function __construct(
        private IssueAuthTokenAction $issueAuthTokenAction
    ) {}

    public function execute($data, $guestToken)
    {
        $user = User::where('username', $data['username'])->first();

        $this->validate($user, $data['password']);

        return $this->issueAuthTokenAction->execute($user, $guestToken);
    }

    private function validate($user, $password)
    {
        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }
    }
}
