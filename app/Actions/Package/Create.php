<?php

namespace App\Actions\Package;

use App\Models\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Create
{
    /**
     * @throws ValidationException
     */
    public function create(array $data): Package
    {
        $validated = Validator::make($data, $this->rules());

        if ($validated->fails()) {
            dd($validated->errors());
            throw new ValidationException($validated);
        }

        $package = Package::create($validated->validated());

        if ($package->repositoryProvider) {
            $package->packageProvider()->initializePackage($package);
            $package->repositoryProvider->getService()->deleteHook($package);
            $package->repositoryProvider->getService()->setupHook($package);
        }

        return $package;
    }

    public function rules(bool $isEdit = false, ?Package $package = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'team_id' => ['required', 'exists:teams,id'],
            'is_private' => ['required', 'boolean'],
            'versions_are_private_by_default' => ['required', 'boolean'],
            'repository_provider_id' => ['nullable', 'exists:repository_providers,id'],
            'storage_provider_id' => ['required', 'exists:storage_providers,id'],
            'data' => ['nullable', 'array'],
        ];

        if ($isEdit) {
            $rules['name'][] = 'unique:packages,name,'.$package->id;
            $rules['slug'][] = 'unique:packages,slug,'.$package->id;
        } else {
            $rules['slug'][] = 'unique:packages,slug';
            $rules['name'][] = 'unique:packages,name';
        }

        return $rules;
    }
}
