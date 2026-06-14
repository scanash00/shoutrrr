<?php

declare(strict_types=1);

namespace App\Services\Posts;

use App\Models\PostMedia;
use App\Support\SafeImageFetcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    public function __construct(private readonly SafeImageFetcher $fetcher) {}

    /**
     * Store an uploaded image on the public disk and create an orphan PostMedia row.
     */
    public function store(string $workspaceId, UploadedFile $file, ?string $altText = null): PostMedia
    {
        $disk = 'public';
        $path = $file->store('media/'.$workspaceId, $disk);

        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];

        return PostMedia::create([
            'workspace_id' => $workspaceId,
            'post_id' => null,
            'disk' => $disk,
            'path' => $path,
            'mime' => (string) $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'alt_text' => $altText,
            'position' => 0,
        ]);
    }

    /**
     * Download an image from a public URL (SSRF-guarded) and store it as an orphan
     * PostMedia row, mirroring store().
     *
     * @throws \RuntimeException if the URL is blocked or the response is not a valid image.
     */
    public function storeFromUrl(string $workspaceId, string $url, ?string $altText = null): PostMedia
    {
        $image = $this->fetcher->fetch($url);

        $extension = match ($image['mime']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'bin',
        };

        $disk = 'public';
        $path = 'media/'.$workspaceId.'/'.Str::uuid()->toString().'.'.$extension;
        Storage::disk($disk)->put($path, $image['bytes']);

        $dimensions = @getimagesizefromstring($image['bytes']) ?: [null, null];

        return PostMedia::create([
            'workspace_id' => $workspaceId,
            'post_id' => null,
            'disk' => $disk,
            'path' => $path,
            'mime' => $image['mime'],
            'size_bytes' => strlen($image['bytes']),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'alt_text' => $altText,
            'position' => 0,
        ]);
    }
}
