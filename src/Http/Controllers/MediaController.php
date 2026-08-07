<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SoftLand\ThemeBuilder\Contracts\MediaProvider;
use SoftLand\ThemeBuilder\Models\Theme;

class MediaController extends Controller
{
    public function __construct(
        private MediaProvider $media,
    ) {}

    public function index(Request $request, Theme $theme): JsonResponse
    {
        $this->authorizeTheme($theme);

        return response()->json([
            'data' => $this->media->list($theme->id),
        ]);
    }

    public function store(Request $request, Theme $theme): JsonResponse
    {
        $this->authorizeTheme($theme);

        $data = $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
        ]);

        return response()->json([
            'data' => $this->media->store($theme->id, $data['file']),
        ], 201);
    }

    private function authorizeTheme(Theme $theme): void
    {
        $tenantId = app()->bound('theme-builder.tenant_id') ? app('theme-builder.tenant_id') : null;

        if ($tenantId === null) {
            return;
        }

        abort_unless($theme->store_id === $tenantId, 403);
    }
}
