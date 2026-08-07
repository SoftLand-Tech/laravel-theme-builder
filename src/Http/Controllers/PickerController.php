<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SoftLand\ThemeBuilder\Contracts\StorefrontDataProvider;

/**
 * Editor picker endpoints. Returns `{ data: PickerItem[] }` — the exact shape
 * the React pickers expect (`fields/*PickerField.tsx`), so the JS needs no
 * changes regardless of host.
 */
class PickerController extends Controller
{
    public function __construct(
        private StorefrontDataProvider $provider,
    ) {}

    public function products(Request $request): JsonResponse
    {
        return $this->respond(
            $this->provider->searchProducts($this->query($request), $this->ids($request))
        );
    }

    public function categories(Request $request): JsonResponse
    {
        return $this->respond(
            $this->provider->searchCategories($this->query($request), $this->ids($request))
        );
    }

    public function blogPosts(Request $request): JsonResponse
    {
        return $this->respond(
            $this->provider->searchBlogPosts($this->query($request), $this->ids($request))
        );
    }

    private function query(Request $request): ?string
    {
        $q = $request->string('q');

        return $q->isNotEmpty() ? $q->toString() : null;
    }

    /**
     * @return array<int, int>
     */
    private function ids(Request $request): array
    {
        $ids = $request->input('ids');

        if ($ids === null) {
            return [];
        }

        $list = is_array($ids) ? $ids : explode(',', (string) $ids);

        return array_values(array_filter(array_map('intval', $list), fn ($v) => $v > 0));
    }

    private function respond(array $data): JsonResponse
    {
        return response()->json(['data' => $data]);
    }
}
