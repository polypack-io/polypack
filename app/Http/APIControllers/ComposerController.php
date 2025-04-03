<?php

namespace App\Http\APIControllers;

use App\Models\PackageVersion;
use App\Services\PackageServices\ComposerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('composer')]
class ComposerController extends APIController
{
    public function __construct(
        private readonly ComposerService $composerService,
    ) {}

    #[Get('/packages.json', name: 'composer.index')]
    public function index()
    {
        return response()->json([
            'metadata-url' => route('composer.metadata.stub', ['%package%']),
            'notify-batch' => route('composer.notify-batch'),
        ]);
    }

    // This will never do anything. It's used to generate the real metadata URL.
    #[Get('/{package_owner}.json', name: 'composer.metadata.stub')]
    public function metadataStub() {}

    #[Get('/{package_owner}/{package_name}.json', name: 'composer.metadata')]
    public function metadata(Request $request, string $package_owner, string $package_name)
    {
        $dev = Str::endsWith($package_name, '~dev');
        $package_name = Str::beforeLast($package_name, '~dev');
        $package_name = $package_owner.'/'.$package_name;

        $package = $this->composerService->getPackage($package_name);

        ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, false);

        if (! $package) {
            return response()->json([], 404);
        }

        if ($package->is_private) {
            if (! $client) {
                abort(401);
            }

            if (! $client->hasAccessTo($package)) {
                abort(403);
            }
        }

        $versions = $this->composerService->getPackageVersions($package, $dev)->filter(function (PackageVersion $version) use ($client) {
            if ($version->is_private) {
                return $client->hasAccessTo($version);
            }

            return true;
        });

        return response()->json([
            'packages' => [
                $package_name => $versions->map(fn (PackageVersion $version) => $version->data),
            ],
        ], 200, [
            'Last-Modified' => $package->versions_updated_at,
        ]);
    }

    #[Post('/notify-batch', name: 'composer.notify-batch')]
    public function notifyBatch(Request $request)
    {
        foreach ($request->downloads as $download) {
            $package = $this->composerService->getPackage($download['name']);

            if (! $package) {
                Log::info('Package not found', [
                    'name' => $download['name'],
                ]);

                continue;
            }

            $version = $package->versions()->where('version', $download['version'])->first();

            if (! $version) {
                $version = $package->versions()->whereJsonContains('data->version_normalized', $download['version'])->first();

                if (! $version) {
                    Log::info('Version not found', [
                        'name' => $download['name'],
                        'version' => $download['version'],
                    ]);

                    continue;
                }
            }

            $version->installs()->create();
        }
    }
}
