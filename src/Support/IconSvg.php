<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Support;

/**
 * Inline icon SVG path data, shared by Blade views. Kept in lockstep with the
 * editor's `resources/js/builder/config/fields.ts` `iconSvgPath()`.
 */
final class IconSvg
{
    /** @return array<string, string> */
    public static function paths(): array
    {
        return [
            'check' => 'M5 13l4 4L19 7',
            'truck' => 'M3 7h11v8H3zM14 10h4l3 3v2h-7',
            'shield' => 'M12 3l8 3v6c0 4.5-3 7.5-8 9-5-1.5-8-4.5-8-9V6z',
            'phone' => 'M5 4h3l2 5-2 1a11 11 0 005 5l1-2 5 2v3a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2',
            'star' => 'M12 3l2.6 5.3 5.8.8-4.2 4.1 1 5.8L12 16.3 6.8 19l1-5.8L3.6 9.1l5.8-.8z',
            'heart' => 'M12 21s-7-4.5-9.5-9A5 5 0 0112 6a5 5 0 019.5 6c-2.5 4.5-9.5 9-9.5 9z',
            'tag' => 'M3 12l9-9 9 9-9 9zM7.5 7.5h.01',
            'gift' => 'M20 12v8H4v-8M2 7h20v5H2zM12 22V7M12 7S11 3 8.5 3 6 5 6 5s2 2 6 2zM12 7s1-4 3.5-4S18 5 18 5s-2 2-6 2z',
            'card' => 'M2 6h20v12H2zM2 10h20',
            'lock' => 'M5 11h14v9H5zM8 11V7a4 4 0 018 0v4',
            'refresh' => 'M21 12a9 9 0 11-3-6.7M21 4v5h-5',
            'headset' => 'M4 13a8 8 0 0116 0M4 13v4a2 2 0 002 2h1v-6H6a2 2 0 00-2 0zM20 13v4a2 2 0 01-2 2h-1v-6h1a2 2 0 012 0zM18 19a3 3 0 01-3 2h-3',
            'clock' => 'M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v5l3 2',
            'map' => 'M12 22s8-7 8-13a8 8 0 10-16 0c0 6 8 13 8 13zM12 11a2 2 0 100-4 2 2 0 000 4z',
            'mail' => 'M3 5h18v14H3zM3 7l9 6 9-6',
            'box' => 'M3 7l9-4 9 4v10l-9 4-9-4zM3 7l9 4 9-4M12 11v10',
        ];
    }

    public static function path(string $icon): string
    {
        return self::paths()[$icon] ?? self::paths()['check'];
    }
}
