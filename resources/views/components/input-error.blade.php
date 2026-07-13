{{-- resources/views/components/input-error.blade.php --}}
@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1.5 text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
            </li>
        @endforeach
    </ul>
@endif