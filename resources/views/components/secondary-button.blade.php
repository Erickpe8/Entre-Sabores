<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary disabled:opacity-40']) }}>
    {{ $slot }}
</button>
