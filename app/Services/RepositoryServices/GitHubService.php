<?php

namespace App\Services\RepositoryServices;

use App\Contracts\StorageProvider;
use App\Jobs\Package\CreateVersion;
use App\Models\Log;
use App\Models\Package;
use Filament\Forms\Components\TextInput;
use Github\AuthMethod;
use Github\Client;
use Illuminate\Database\Eloquent\Model;

class GitHubService extends AbstractRepositoryService
{
    private function client(): Client
    {
        $client = new Client;
        if (isset($this->credentials['token']) && $this->credentials['token']) {
            $client->authenticate($this->credentials['token'], null, AuthMethod::ACCESS_TOKEN);
        }

        return $client;
    }

    public function getRepositoryDownloadUrl(Package $package, array $data, string $type = 'zipball'): ?array
    {
        $team = $package->data['organization'];
        $repository = $package->data['repository'];
        $ref = $data['ref'];

        /**
         * @disregard
         */
        $res = $this->client()->api('repo')->show($team, $repository);
        $archiveUrl = str_replace('{/ref}', '/'.$ref, $res['archive_url']);
        $archiveUrl = str_replace('{archive_format}', $type, $archiveUrl);

        if ($res['private']) {
            /**
             * @var StorageProvider
             **/
            $storageService = $package->storageProvider->getService();

            $file = $storageService->download($archiveUrl, [
                'Authorization' => 'Bearer '.$this->credentials['token'],
            ]);

            return [
                'url' => route('download.file', $file->id),
                'type' => 'zip',
                'file_id' => $file->id,
            ];
        } else {
            return [
                'url' => $archiveUrl,
                'type' => 'zip',
                'file_id' => null,
            ];
        }

        return null;
    }

    public function getRepositoryFile(Package $package, array $data): ?string
    {
        $team = $package->data['organization'];
        $repository = $package->data['repository'];
        $ref = $data['ref'];
        $file = $data['file'];

        /**
         * @disregard
         */
        $res = $this->client()->api('repo')->contents()->show($team, $repository, $file, $ref);

        return \base64_decode($res['content']);
    }

    public static function form(): array
    {
        return [
            TextInput::make('data.token')
                ->label('GitHub Token')
                ->placeholder('Enter your GitHub PAT')
                ->helperText('You can generate a PAT from your GitHub account settings.')
                ->rules(self::formValidation(null)['data.token']),
        ];
    }

    public static function formValidation(?Model $model): array
    {
        return [
            'data.token' => ['required', 'string'],
        ];
    }

    public static function createPackageForm(): array
    {
        return [
            TextInput::make('data.organization')
                ->label('Organization')
                ->placeholder('Enter the organization or users name')
                ->helperText('The name of the organization or user that owns the repository you want to add.')
                ->required()
                ->rules(self::createPackageValidation(null)['data.organization']),
            TextInput::make('data.repository')
                ->label('Repository')
                ->placeholder('Enter the repository name')
                ->helperText('The name of the repository you want to add.')
                ->required()
                ->rules(self::createPackageValidation(null)['data.repository']),
        ];
    }

    public static function createPackageValidation(?Package $model): array
    {
        return [
            'data.organization' => ['required', 'string'],
            'data.repository' => ['required', 'string'],
        ];
    }

    /**
     * @return array{
     *     tags: array,
     *     branches: array,
     * }
     */
    public function fetchVersions(Package $package, bool $includeBranches = false): void
    {
        $client = $this->client();

        /**
         * @disregard
         */
        $tags = $client->api('repo')->tags($package->data['organization'], $package->data['repository']);

        $branches = [];
        if ($includeBranches) {
            /**
             * @disregard
             */
            $branches = $client->api('repo')->branches($package->data['organization'], $package->data['repository']);
        }

        $versions = array_merge($tags, $branches);

        foreach ($versions as $version) {
            $isTag = isset($version['zipball_url']);

            CreateVersion::dispatch($package, [
                'ref' => $version['commit']['sha'],
            ], ! $isTag ? 'dev-'.$version['name'] : $version['name'], $isTag);

            Log::write($package, 'package.version.created', 'Created version '.$version['name'].' from inital fetch.');
        }
    }

    public function setupHook(Package $package): void
    {
        $client = $this->client();

        try {
            /**
             * @disregard
             */
            $client->api('repo')->hooks()->create($package->data['organization'], $package->data['repository'], [
                'name' => 'web',
                'config' => [
                    'url' => route('github.webhook', $package->id),
                    'content_type' => 'json',
                    'secret' => config('services.github.webhook_secret'),
                    'insecure_ssl' => '0',
                ],
                'events' => ['push'],
                'active' => true,
            ]);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function deleteHook(Package $package): void
    {
        $client = $this->client();

        /**
         * @disregard
         */
        $hooks = $client->api('repo')->hooks()->all($package->data['organization'], $package->data['repository']);
        $hooks = collect($hooks)->where('config.url', route('github.webhook', $package->id));

        foreach ($hooks as $hook) {
            /**
             * @disregard
             */
            $client->api('repo')->hooks()->remove($package->data['organization'], $package->data['repository'], $hook['id']);
        }
    }
}
