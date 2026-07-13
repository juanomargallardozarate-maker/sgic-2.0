{{-- resources/views/components/dropdown-link.blade.php --}}
<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors']) }}>
    {{ $slot }}
</a>