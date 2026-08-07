<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

use Illuminate\Http\UploadedFile;
use SoftLand\ThemeBuilder\Contracts\MediaProvider;

/**
 * Default media provider: no media available. The editor's media picker shows
 * an empty gallery and uploads are rejected until the host binds a real
 * MediaProvider.
 */
final class NullMediaProvider implements MediaProvider
{
    public function list(int $themeId): array
    {
        return [];
    }

    public function store(int $themeId, UploadedFile $file): array
    {
        abort(501, 'Theme Builder: no media provider is bound.');
    }

    public function url(int $themeId, string $mediaId): ?string
    {
        return null;
    }
}
