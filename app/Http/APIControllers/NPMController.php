<?php

namespace App\Http\APIControllers;

use App\Models\Log as ModelsLog;
use App\Models\PackageVersion;
use App\Services\PackageServices\NPMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

#[Prefix('npm')]
class NPMController extends APIController
{
    public function __construct(
        private readonly NPMService $npmService,
    ) {}

    #[Get('/{scope}/{package_name}', name: 'npm.package')]
    public function package(Request $request, string $scope, string $package_name)
    {
        Log::info('authorization headers', ['headers' => $request->headers->all()]);

        Log::info('npm.package', ['package_name' => $package_name, 'scope' => $scope]);

        $package = $this->npmService->getPackage($package_name);

        ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, false);

        if (! $package) {
            return response()->json([
                'error' => 'Package not found',
            ], 404);
        }

        if ($package->is_private) {
            if (is_null($client)) {
                abort(401);
            }

            if (is_null($client) || ! $client->hasAccessTo($package)) {
                abort(403);
            }
        }

        Log::info('npm.package', ['package' => $package]);

        $versions = $this->npmService->getPackageVersions($package, null)->filter(function (PackageVersion $version) use ($client) {
            if ($version->is_private) {
                return $client->hasAccessTo($version);
            }

            return true;
        });
        $returnVersions = [];

        foreach ($versions as $version) {
            $data = $version->data;
            $data['name'] = $scope.'/'.$package->slug;
            $returnVersions[$version->version] = $data;
        }

        $return = [
            'name' => $scope.'/'.$package->slug,
            'versions' => $returnVersions,
        ];

        $return = array_merge($package->data['meta'], $return);

        return response()->json($return);
    }

    #[Put('/{scope}/{package_name}')]
    #[Put('/{package_name}', name: 'npm.package.put')]
    public function packagePut(Request $request, string $package_name, ?string $package_name2 = null)
    {
        Log::info('payload', ['payload' => $request->all()]);

        Log::info('authorization headers', ['headers' => $request->headers->all()]);

        Log::info('npm.package.put', ['package_name' => $package_name, 'package_name2' => $package_name2]);

        ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, false);

        $scope = null;

        if ($package_name2) {
            $scope = $package_name;
            $package_name = $package_name2;
        }

        $package = $this->npmService->getPackage($package_name);

        if (! $package) {
            return response()->json([
                'error' => 'Package not found',
            ], 404);
        }

        if (is_null($client) || ! $client->hasWriteAccessTo($package) || ! in_array('write', $permissions)) {
            abort(403);
        }

        /**
         * @var \App\Contracts\StorageProvider
         */
        $storageProvider = $package->storageProvider->getService();

        foreach ($request->versions as $version) {
            $attachmentKey = explode('-/', $version['dist']['tarball'])[1];
            $attachment = $request->_attachments[$attachmentKey];

            if ($package->versions()->where('version', $version['version'])->exists()) {
                abort(409, 'Version already exists');
            }

            $file = $storageProvider->store($attachment['data'], $attachment['content_type']);

            $version['name'] = $package->name;
            $version['dist']['tarball'] = route('download.file', $file);

            $package->versions()->create([
                'version' => $version['version'],
                'data' => $version,
                'file_id' => $file->id,
            ]);

            $data = $package->data;

            if (! isset($data['meta'])) {
                $data['meta'] = [];
            }

            if (! isset($data['meta']['dist-tags'])) {
                $data['meta']['dist-tags'] = [];
            }

            foreach ($request['dist-tags'] as $distTag => $distTagVersion) {
                $data['meta']['dist-tags'][$distTag] = $distTagVersion;
            }

            $package->update(['data' => $data]);

            ModelsLog::write($package, 'package.version.created', 'Created version '.$version['version'].' from `npm publish`');
        }

        return response()->json(['message' => 'Package updated']);
    }

    #[Get('-/package/{scope}/{package_name}/dist-tags', name: 'npm.package.dist-tags.get')]
    public function distTags(Request $request, string $scope, string $package_name)
    {
        $package = $this->npmService->getPackage($package_name);

        ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, false);

        if (is_null($client) || ! $client->hasAccessTo($package) || ! in_array('write', $permissions)) {
            abort(403);
        }

        if (! $package) {
            return response()->json([
                'error' => 'Package not found',
            ], 404);
        }

        if (! isset($package->data['meta']['dist-tags'])) {
            return response()->json([
                'error' => 'Dist tags not found',
            ], 404);
        }

        return response()->json($package->data['meta']['dist-tags']);
    }

    #[Put('-/package/{scope}/{package_name}/dist-tags/{dist_tag}', name: 'npm.package.dist-tags.put')]
    public function distTagsPut(Request $request, string $scope, string $package_name, string $dist_tag)
    {
        $package = $this->npmService->getPackage($package_name);

        ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, true);

        if (is_null($client) || ! $client->hasWriteAccessTo($package) || ! in_array('write', $permissions)) {
            abort(403);
        }

        if (! $package) {
            return response()->json([
                'error' => 'Package not found',
            ], 404);
        }

        $data = $package->data;
        $data['meta']['dist-tags'][$dist_tag] = json_decode($request->getContent(), true);

        $package->update(['data' => $data]);

        return response()->json(['message' => 'Dist tag updated']);
    }

    #[Delete('-/package/{scope}/{package_name}/dist-tags/{dist_tag}', name: 'npm.package.dist-tags.delete')]
    public function distTagsDelete(Request $request, string $scope, string $package_name, string $dist_tag)
    {
        $package = $this->npmService->getPackage($package_name);

        if (! $package) {
            return response()->json([
                'error' => 'Package not found',
            ], 404);
        }

        ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, true);

        if (is_null($client) || ! $client->hasWriteAccessTo($package) || ! in_array('write', $permissions)) {
            abort(403);
        }

        $data = $package->data;
        unset($data['meta']['dist-tags'][$dist_tag]);

        $package->update(['data' => $data]);

        return response()->json(['message' => 'Dist tag deleted']);
    }
}
