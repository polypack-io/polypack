<?php

namespace App\Actions\PersonalAccessToken;

use Illuminate\Database\Eloquent\Model;

class Create
{
    public function execute(Model $tokenable, array $data): string
    {
        if (! isset($data['abilities'])) {
            $data['abilities'] = [];
        }

        $token = $tokenable->createToken($data['name'], $data['abilities']);

        return $token->plainTextToken;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string'],
        ];
    }
}
