@props([
    'class' => 'top-4',
])

@php
    $alerts = [];
    $seen = [];

    $pushAlert = static function (string $type, string $message) use (&$alerts, &$seen): void {
        $message = trim($message);
        if ($message === '' || in_array($message, $seen, true)) {
            return;
        }
        $seen[] = $message;
        $alerts[] = ['type' => $type, 'message' => $message];
    };

    if (session('success')) {
        $pushAlert('success', (string) session('success'));
    }

    $status = session('status');
    if (is_string($status) && $status !== '' && ! session('success')) {
        $successStatuses = ['profile-updated', 'password-updated'];
        $pushAlert(in_array($status, $successStatuses, true) ? 'success' : 'info', $status);
    }

    if (isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag) {
        foreach ($errors->getBags() as $bag) {
            foreach ($bag->all() as $message) {
                $pushAlert('danger', (string) $message);
            }
        }
    }
@endphp

<div
    id="alert-stack"
    {{ $attributes->merge([
        'class' => "fixed {$class} right-4 z-[200] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 pointer-events-none sm:w-full",
        'aria-live' => 'polite',
    ]) }}
>
    @foreach ($alerts as $alert)
        <x-ui.alert :type="$alert['type']" :message="$alert['message']" />
    @endforeach
</div>
