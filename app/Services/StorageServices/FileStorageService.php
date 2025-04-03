<?php

namespace App\Services\StorageServices;

use App\Models\File;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;

class FileStorageService extends AbstractStorageService
{
    public function store(string $content, string $mimeType): File
    {
        $file = File::create([
            'storage_provider_id' => $this->storageProvider->id,
            'mime_type' => $mimeType,
            'data' => [],
        ]);

        Storage::put($this->data['path'].'/'.$file->id, $content);

        $file->update([
            'data' => [
                'path' => $this->data['path'].'/'.$file->id,
            ],
        ]);

        return $file;
    }

    public function delete(File $file): void
    {
        Storage::delete($file->data['path']);
        $file->delete();
    }

    public function get(File $file): string
    {
        return Storage::get($file->data['path']);
    }

    public static function form(): array
    {
        return [
            TextInput::make('data.path')
                ->label('Path')
                ->helperText('Enter the path within storage/app/private to store the files')
                ->placeholder('internal/packages')
                ->required(),
        ];
    }

    public static function formValidation(): array
    {
        return [
            'path' => ['required', 'string', 'max:255'],
        ];
    }
}
