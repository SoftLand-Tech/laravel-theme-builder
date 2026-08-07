<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SoftLand\ThemeBuilder\Models\Theme;

class RevisionController extends Controller
{
    public function index(Request $request, Theme $theme): JsonResponse
    {
        $this->authorizeTheme($theme);

        $revisions = $theme->revisions()
            ->latest('revision_number')
            ->limit(50)
            ->get(['id', 'revision_number', 'version', 'change_summary', 'published_at']);

        return response()->json([
            'data' => $revisions->map(fn ($r) => [
                'id' => $r->id,
                'revision_number' => $r->revision_number,
                'version' => $r->version,
                'change_summary' => $r->change_summary,
                'published_at' => $r->published_at?->toIso8601String(),
            ]),
        ]);
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
