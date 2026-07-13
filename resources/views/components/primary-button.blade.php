{{-- resources/views/components/primary-button.blade.php --}}
<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
]) }}>
    {{ $slot }}
</button>