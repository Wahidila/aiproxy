@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-off-black']) }}>
    {{ $value ?? $slot }}
</label>
