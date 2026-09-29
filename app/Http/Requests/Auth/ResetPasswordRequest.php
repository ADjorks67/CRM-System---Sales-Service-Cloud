<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\NotRecentPassword;
use App\Rules\PasswordComplexity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = User::query()->where('email', $this->string('email')->toString())->first();

        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'password' => array_values(array_filter([
                'required',
                'confirmed',
                new PasswordComplexity,
                $user ? new NotRecentPassword($user) : null,
            ])),
        ];
    }
}
