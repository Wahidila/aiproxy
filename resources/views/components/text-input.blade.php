@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-oat focus:border-fin-orange focus:ring-fin-orange rounded-btn bg-surface text-off-black']) !!}>
