@props(['props' => []])

@php
    $html = (string) ($props['html'] ?? '');
@endphp

@if ($html !== '')
    {{-- Safe markup only: scripts, inline handlers, and javascript: URLs are
         stripped in BlockDefinition::sanitizeString() before props are stored. --}}
    {!! $html !!}
@endif
