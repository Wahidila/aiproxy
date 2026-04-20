@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-fin-orange text-start text-base font-medium text-off-black bg-fin-orange-light focus:outline-none focus:text-off-black focus:bg-fin-orange-light focus:border-fin-orange transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-muted hover:text-off-black hover:bg-canvas hover:border-oat focus:outline-none focus:text-off-black focus:bg-canvas focus:border-oat transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
