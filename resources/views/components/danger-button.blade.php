<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn border border-danger bg-transparent text-danger hover:bg-danger/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/40']) }}>
    {{ $slot }}
</button>
