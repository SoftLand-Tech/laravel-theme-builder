<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use SoftLand\ThemeBuilder\Builder\BlockRegistry;
use SoftLand\ThemeBuilder\Contracts\Store;
use SoftLand\ThemeBuilder\Enums\ThemeStatus;
use SoftLand\ThemeBuilder\Models\Theme;
use SoftLand\ThemeBuilder\Models\ThemeRevision;

class BuilderController extends Controller
{
    public function __construct(
        private BlockRegistry $registry,
    ) {}

    /**
     * Render the standalone React editor shell.
     */
    public function edit(Request $request, Theme $theme): View
    {
        $this->authorizeTheme($theme);

        return view('theme-builder::builder.editor', [
            'theme' => $theme,
            'storeName' => $this->storeName(),
            'apiToken' => $this->buildApiToken($theme),
            'blockRegistry' => $this->registry->forEditor(),
            'currentVersion' => ThemeRevision::latestForTheme($theme)?->version ?? '',
        ]);
    }

    /**
     * Autosave builder state (draft overlay when the theme is already live).
     */
    public function save(Request $request, Theme $theme): JsonResponse
    {
        $this->authorizeTheme($theme);

        $data = $this->validatePayload($request);
        $theme->persistBuilderPayload($data);
        $theme->refresh();

        return response()->json([
            'ok' => true,
            'theme' => $this->themePayload($theme),
        ]);
    }

    /**
     * Snapshot the current builder state as a new semantically-versioned revision.
     */
    public function publish(Request $request, Theme $theme): JsonResponse
    {
        $this->authorizeTheme($theme);

        $data = $this->validatePayload($request);
        $theme->persistBuilderPayload($data);
        $theme->refresh();

        $major = (bool) $request->boolean('major');
        $snapshot = $theme->builderWorkingSnapshot();
        $revision = $this->createRevision($theme, $snapshot, $major, (string) $request->input('change_summary', ''));

        return response()->json([
            'ok' => true,
            'version' => $revision->version,
            'theme' => $this->themePayload($theme),
        ]);
    }

    /**
     * Copy draft builder state to the live storefront columns.
     */
    public function promote(Request $request, Theme $theme): JsonResponse
    {
        $this->authorizeTheme($theme);
        abort_unless($theme->status === ThemeStatus::Published, 422);

        $data = $this->validatePayload($request);
        $major = (bool) $request->boolean('major');
        $changeSummary = (string) $request->input('change_summary', '');

        $revision = DB::transaction(function () use ($theme, $data, $major, $changeSummary): ThemeRevision {
            /** @var Theme $locked */
            $locked = Theme::query()->whereKey($theme->id)->lockForUpdate()->firstOrFail();

            abort_unless($locked->status === ThemeStatus::Published, 422);

            $locked->persistBuilderPayload($data);
            $locked->refresh();
            $locked->promoteDraftToPublished();
            $locked->refresh();

            return $this->createRevision(
                $locked,
                $locked->builderWorkingSnapshot(),
                $major,
                $changeSummary,
            );
        });

        return response()->json([
            'ok' => true,
            'version' => $revision->version,
            'theme' => $this->themePayload($theme->fresh()),
        ]);
    }

    /**
     * @param  array{
     *     blocks: array<int, array<string, mixed>>,
     *     settings: array<string, mixed>,
     *     header: array<string, mixed>,
     *     footer: array<string, mixed>,
     *     templates: array<string, mixed>
     * }  $snapshot
     */
    private function createRevision(Theme $theme, array $snapshot, bool $major, string $changeSummary): ThemeRevision
    {
        $revisionNumber = ($theme->revisions()->lockForUpdate()->max('revision_number') ?? 0) + 1;

        return $theme->revisions()->create([
            'store_user_id' => $this->actingUserId(),
            'revision_number' => $revisionNumber,
            'version' => ThemeRevision::nextVersion($theme, $major),
            'change_summary' => $changeSummary !== '' ? $changeSummary : null,
            'blocks' => $snapshot['blocks'],
            'settings' => $snapshot['settings'],
            'header' => $snapshot['header'],
            'footer' => $snapshot['footer'],
            'templates' => $snapshot['templates'],
            'published_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            // `present` (not `required`) so merchants can delete every home block.
            'blocks' => ['present', 'array'],
            'blocks.*.type' => ['required', 'string'],
            'blocks.*.props' => ['required', 'array'],
            'settings' => ['sometimes', 'array'],
            'header' => ['sometimes', 'array'],
            'footer' => ['sometimes', 'array'],
            'templates' => ['sometimes', 'array'],
            'templates.product' => ['sometimes', 'array'],
            'templates.product.before' => ['sometimes', 'array'],
            'templates.product.before.*.type' => ['required_with', 'string'],
            'templates.product.before.*.props' => ['required_with', 'array'],
            'templates.product.after' => ['sometimes', 'array'],
            'templates.product.after.*.type' => ['required_with', 'string'],
            'templates.product.after.*.props' => ['required_with', 'array'],
            'templates.cart' => ['sometimes', 'array'],
            'templates.cart.before' => ['sometimes', 'array'],
            'templates.cart.before.*.type' => ['required_with', 'string'],
            'templates.cart.before.*.props' => ['required_with', 'array'],
            'templates.cart.after' => ['sometimes', 'array'],
            'templates.cart.after.*.type' => ['required_with', 'string'],
            'templates.cart.after.*.props' => ['required_with', 'array'],
            'templates.collection' => ['sometimes', 'array'],
            'templates.collection.before' => ['sometimes', 'array'],
            'templates.collection.before.*.type' => ['required_with', 'string'],
            'templates.collection.before.*.props' => ['required_with', 'array'],
            'templates.collection.after' => ['sometimes', 'array'],
            'templates.collection.after.*.type' => ['required_with', 'string'],
            'templates.collection.after.*.props' => ['required_with', 'array'],
        ]);

        return $this->normalizePayload($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $data['blocks'] = $this->registry->normalizeBlockTree($data['blocks']);

        if (! isset($data['templates']) || ! is_array($data['templates'])) {
            return $data;
        }

        foreach (['product', 'cart', 'collection'] as $page) {
            if (! isset($data['templates'][$page]) || ! is_array($data['templates'][$page])) {
                continue;
            }
            foreach (['before', 'after'] as $slot) {
                if (! isset($data['templates'][$page][$slot]) || ! is_array($data['templates'][$page][$slot])) {
                    continue;
                }
                $data['templates'][$page][$slot] = $this->registry->normalizeBlockTree($data['templates'][$page][$slot]);
            }
        }

        return $data;
    }

    /**
     * Restore blocks from a prior revision into the builder working copy.
     */
    public function restoreRevision(Request $request, Theme $theme, ThemeRevision $revision): JsonResponse
    {
        $this->authorizeTheme($theme);
        abort_unless($revision->theme_id === $theme->id, 404);

        $payload = [
            'blocks' => $revision->blocks ?? [],
            'settings' => $revision->settings,
            'header' => $revision->header,
            'footer' => $revision->footer,
            'templates' => $revision->templates,
        ];

        $theme->persistBuilderPayload($payload);
        $theme->refresh();

        return response()->json([
            'ok' => true,
            'theme' => $this->themePayload($theme),
        ]);
    }

    /**
     * Redirect to the configured exit URL after leaving the editor.
     */
    public function exit(Request $request, Theme $theme): RedirectResponse
    {
        $this->authorizeTheme($theme);

        return redirect(config('theme-builder.exit_url', '/'));
    }

    /**
     * @return array<string, string>
     */
    private function buildApiToken(Theme $theme): array
    {
        $name = (string) config('theme-builder.routes.name', 'theme-builder');

        return [
            'saveUrl' => route("{$name}.themes.save", $theme),
            'publishUrl' => route("{$name}.themes.publish", $theme),
            'promoteUrl' => route("{$name}.themes.promote", $theme),
            'exitUrl' => route("{$name}.themes.exit", $theme),
            'mediaUrl' => route("{$name}.themes.media.index", $theme),
            'mediaUploadUrl' => route("{$name}.themes.media.store", $theme),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function themePayload(Theme $theme): array
    {
        return [
            'id' => $theme->id,
            'name' => $theme->nameTranslations(),
            'blocks' => $theme->builderResolvedBlocks(),
            'settings' => $theme->builderResolvedSettings(),
            'header' => $theme->builderResolvedHeader(),
            'footer' => $theme->builderResolvedFooter(),
            'templates' => $theme->builderResolvedTemplates(),
            'status' => $theme->status->value,
            'usesDraftOverlay' => $theme->usesDraftOverlay(),
            'hasUnpublishedDraft' => $theme->hasUnpublishedDraft(),
        ];
    }

    private function authorizeTheme(Theme $theme): void
    {
        $tenantId = $this->tenantId();

        // Single-tenant installs (no tenant resolver) skip per-tenant isolation.
        if ($tenantId === null) {
            return;
        }

        abort_unless($theme->store_id === $tenantId, 403);
    }

    private function actingUserId(): int|string|null
    {
        $resolver = config('theme-builder.acting_user_resolver');

        return $resolver !== null ? app()->call($resolver) : null;
    }

    private function storeName(): ?string
    {
        return app(Store::class)->getThemeBuilderStoreName();
    }

    private function tenantId(): int|string|null
    {
        return app()->bound('theme-builder.tenant_id')
            ? app('theme-builder.tenant_id')
            : null;
    }
}
