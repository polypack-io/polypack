<?php

namespace App\Http\APIControllers;

use App\Models\File;
use App\Models\PackageVersion;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;

class DownloadController extends APIController
{
    #[Get('/download/{file}', name: 'download.file')]
    public function download(Request $request, ?File $file)
    {
        if (! $file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $version = PackageVersion::where('file_id', $file->id)->first();
        if (! $version) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if ($version->is_private || $version->package->is_private) {
            ['user' => $client, 'permissions' => $permissions] = $this->getClient($request, false);

            if ($version->is_private) {
                if (! $client->hasAccessTo($version)) {
                    abort(403);
                }
            }

            if ($version->package->is_private) {
                if (! $client->hasAccessTo($version->package)) {
                    abort(403);
                }
            }
        }

        $content = $file->storageProvider->getService()->get($file);

        return response($content)->header('Content-Type', $file->mime_type);
    }
}
