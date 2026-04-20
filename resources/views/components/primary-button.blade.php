<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-off-black border border-transparent rounded-btn font-medium text-sm text-white hover:bg-surface hover:text-off-black hover:border-off-black focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 active:scale-95 btn-intercom transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
