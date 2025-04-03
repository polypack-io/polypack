<?php

namespace Database\Seeders;

use App\Actions\Package\Create as CreatePackage;
use App\Enums\Packages;
use App\Enums\Repositories;
use App\Enums\Storage;
use App\Models\RepositoryProvider;
use App\Models\Role;
use App\Models\StorageProvider;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $r = Role::create([
            'name' => 'Admin',
            'level' => 0,
            'read_all_teams' => true,
            'write_all_teams' => true,
            'manage_users' => true,
            'manage_clients' => true,
            'manage_settings' => true,
        ]);

        $u = User::create([
            'name' => 'Polypack',
            'email' => 'hello@polypack.io',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $r->id,
        ]);

        if (env('APP_ENV') === 'local') {
            $team = Team::create([
                'name' => 'Polypack',
            ]);

            $rp = RepositoryProvider::create([
                'name' => 'GitHub',
                'type' => Repositories::GITHUB->value,
                'data' => [
                    'token' => env('SEED_PAT'),
                ],
            ]);

            $sp = StorageProvider::create([
                'name' => 'Example Local Filesystem',
                'type' => Storage::FILE->value,
                'data' => [
                    'path' => 'example',
                ],
            ]);

            $packageCreator = app(CreatePackage::class);

            $packageComposer = $packageCreator->create([
                'name' => 'Polypack Test Package PHP',
                'slug' => 'polypack/polypack-test-package-php',
                'type' => Packages::COMPOSER->value,
                'team_id' => $team->id,
                'repository_provider_id' => $rp->id,
                'storage_provider_id' => $sp->id,
                'is_private' => false,
                'versions_are_private_by_default' => true,
                'data' => [
                    'organization' => 'polypack-io',
                    'repository' => 'polypack-test-package-php',
                ],
            ]);

            $packageNPM = $packageCreator->create([
                'name' => 'Polypack Test Package JS',
                'slug' => 'test-js',
                'type' => Packages::NPM->value,
                'team_id' => $team->id,
                'repository_provider_id' => null,
                'storage_provider_id' => $sp->id,
                'is_private' => false,
                'versions_are_private_by_default' => false,
                'data' => [
                    'organization' => 'polypack-io',
                    'repository' => 'polypack-test-package-js',
                ],
            ]);
        }
    }
}
