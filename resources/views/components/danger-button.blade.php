{{-- resources/views/components/danger-button.blade.php --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
]) }}>
    {{ $slot }}
</button>