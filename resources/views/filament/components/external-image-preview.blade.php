@php
    /** @var string|null $thumbUrl */
    /** @var string|null $largeUrl */
@endphp

<div class="flex items-start gap-4">
    @if (filled($thumbUrl))
        <a href="{{ $largeUrl ?? $thumbUrl }}" target="_blank" rel="noreferrer">
            <img
                src="{{ $thumbUrl }}"
                alt="Listing image"
                class="h-24 w-24 rounded-md object-cover ring-1 ring-gray-200 dark:ring-gray-700"
            />
        </a>
    @else
        <div class="h-24 w-24 rounded-md bg-gray-100 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700"></div>
    @endif

    <div class="min-w-0 space-y-2">
        <div class="text-sm text-gray-700 dark:text-gray-200">
            @if (filled($largeUrl))
                <a class="underline" href="{{ $largeUrl }}" target="_blank" rel="noreferrer">დიდი სურათი</a>
            @endif
        </div>

        <div class="text-xs text-gray-500 break-all dark:text-gray-400">
            {{ $thumbUrl ?? 'სურათი არ არის' }}
        </div>
    </div>
</div>

