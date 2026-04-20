<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-report-red border border-transparent rounded-btn font-medium text-sm text-white hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-report-red focus:ring-offset-2 btn-intercom transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
