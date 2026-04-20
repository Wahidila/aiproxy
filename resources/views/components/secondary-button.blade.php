<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-surface border border-off-black rounded-btn font-medium text-sm text-off-black hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 disabled:opacity-25 btn-intercom transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
