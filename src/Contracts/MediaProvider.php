<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Media access for the builder editor's media picker. Replaces Spatie Media
 * Library at the package boundary so the package has no media dependency.
 *
 * MediaView shape (matches the React MediaPickerField): ['id'=>string, 'url'=>string, 'thumb'=>string, 'name'=>string]
 */
interface MediaProvider
{
    /** @return array<int, array> MediaView[] */
    public function list(int $themeId): array;

    /** @return array MediaView */
    public function store(int $themeId, UploadedFile $file): array;

    public function url(int $themeId, string $mediaId): ?string;
}
