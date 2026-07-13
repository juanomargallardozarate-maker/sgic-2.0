{{-- resources/views/components/nav-link.blade.php --}}
@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 transition-colors'
    : 'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-600 hover:text-indigo-700 hover:bg-slate-50 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>