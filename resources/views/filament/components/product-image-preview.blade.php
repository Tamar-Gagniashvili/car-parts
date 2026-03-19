@php
    /** @var string|null $thumbUrl */
@endphp

<div class="flex items-start gap-4">
    @if (filled($thumbUrl))
        <a href="{{ $thumbUrl }}" target="_blank" rel="noreferrer">
            <img
                src="{{ $thumbUrl }}"
                alt="Product image"
                class="h-24 w-24 rounded-md object-cover ring-1 ring-gray-200 dark:ring-gray-700"
            />
        </a>
    @else
        <div class="h-24 w-24 rounded-md bg-gray-100 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700"></div>
    @endif

    <div class="min-w-0 space-y-2">
        <div class="text-xs text-gray-500 break-all dark:text-gray-400">
            {{ $thumbUrl ?? 'სურათი არ არის' }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            სურათების მართვა: ჩანართი “Images”
        </div>
    </div>
</div>

