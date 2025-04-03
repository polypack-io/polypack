<?php

namespace App\Services\StorageServices;

use App\Contracts\StorageProvider;
use App\Models\File;
use App\Models\StorageProvider as ModelsStorageProvider;
use Illuminate\Support\Facades\Http;

abstract class AbstractStorageService implements StorageProvider
{
    protected array $data;

    protected ModelsStorageProvider $storageProvider;

    public function __construct(array $data, ModelsStorageProvider $storageProvider)
    {
        $this->data = $data;
        $this->storageProvider = $storageProvider;
    }

    public function download(string $url, array $headers = []): File
    {
        $request = Http::withHeaders($headers)->get($url);

        $content = $request->body();
        $mimeType = $request->header('Content-Type');

        return $this->store($content, $mimeType);
    }
}
