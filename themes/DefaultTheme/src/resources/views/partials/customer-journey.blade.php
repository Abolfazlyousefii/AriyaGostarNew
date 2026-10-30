@if(config('customer_journey.enabled') && config('customer_journey.client_enabled') && auth()->check())
<script>
window.ARIYA_CUSTOMER_JOURNEY = {
    endpoint: @json(route('customer-journey.event')),
    csrf: @json(csrf_token())
};
</script>
<script src="{{ asset('themes/defaultTheme/js/ariya-customer-journey.js') }}?v=1" defer></script>
@endif
