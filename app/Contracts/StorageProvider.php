<?php

namespace App\Contracts;

use App\Models\File;

interface StorageProvider
{
    public function download(string $url, array $headers = []): File;

    public function store(string $content, string $mimeType): File;

    public function delete(File $file): void;

    /**
     * @return string - The content of the file
     */
    public function get(File $file): string;

    public static function form(): array;

    public static function formValidation(): array;
}
