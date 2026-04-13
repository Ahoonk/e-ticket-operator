<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GangguanDocumentStorageService
{
    public function upload(UploadedFile $file, string $directory): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $storedName = Str::uuid()->toString().'.'.$extension;

        $writtenPath = Storage::disk('public')->putFileAs(
            trim($directory, '/'),
            $file,
            $storedName
        );

        if (!$writtenPath) {
            throw new RuntimeException('Gagal menyimpan file ke storage server.');
        }

        $url = Storage::disk('public')->url($writtenPath);

        return [
            'drive_file_id' => $writtenPath,
            'drive_url' => $url,
            'drive_content_url' => $url,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
        ];
    }
}
