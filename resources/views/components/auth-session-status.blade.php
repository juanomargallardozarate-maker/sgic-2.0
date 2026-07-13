{{-- resources/views/components/auth-session-status.blade.php --}}
@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-emerald-50 border-l-4 border-emerald-500 p-4']) }}>
        <div class="flex items-start">
            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
            <div>
                <p class="text-sm font-medium text-emerald-800">{{ $status }}</p>
            </div>
        </div>
    </div>
@endif