<?php

namespace App\Jobs;

use App\Models\UserIdentityVerification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessIdentityVerificationImage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $verificationId,
        public string $sourcePath,
        public string $collectionName,
        public string $originalName,
    ) {}

    public function handle(): void
    {
        $verification = UserIdentityVerification::query()->find($this->verificationId);

        if ($verification === null || ! Storage::disk('local')->exists($this->sourcePath)) {
            return;
        }

        $absoluteSourcePath = Storage::disk('local')->path($this->sourcePath);
        $image = @imagecreatefromstring((string) file_get_contents($absoluteSourcePath));

        if ($image === false) {
            Storage::disk('local')->delete($this->sourcePath);

            return;
        }

        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        $tempDirectory = storage_path('app/private/tmp');
        if (! is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0775, true);
        }

        $baseName = pathinfo($this->originalName, PATHINFO_FILENAME);
        $sanitizedBaseName = Str::slug($baseName !== '' ? $baseName : $this->collectionName);
        $convertedPath = $tempDirectory.'/'.Str::uuid().'.webp';

        imagewebp($image, $convertedPath, 85);
        imagedestroy($image);

        $verification
            ->addMedia($convertedPath)
            ->usingFileName($sanitizedBaseName.'.webp')
            ->toMediaCollection($this->collectionName);

        @unlink($convertedPath);
        Storage::disk('local')->delete($this->sourcePath);
    }
}
