{{-- resources/views/components/secondary-button.blade.php --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2.5 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
]) }}>
    {{ $slot }}
</button>