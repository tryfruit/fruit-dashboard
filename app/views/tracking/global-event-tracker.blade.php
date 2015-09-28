<!-- Google Analytics -->
@include('tracking.google-analytics')
<!-- /Google Analytics -->

<!-- Intercom IO -->
@include('tracking.intercom-io')
<!-- /Intercom IO -->

<!-- Customer IO -->
@include('tracking.customer-io')
<!-- /Customer IO -->

<!-- Mixpanel -->
@include('tracking.mixpanel')
<!-- /Mixpanel -->

<!-- Global tracking function -->
<script type="text/javascript">

function trackAll(mode, eventData){   
    // Lazy mode
    if (mode == 'lazy') {
        // Google analytics data
        var googleEventData = {
            'ec': eventData['en'],
            'ea': eventData['en'],
            'el': eventData['el'],
            'ev': null,
        };

        // Intercom IO data
        var intercomIOEventData = {
            'en': eventData['en'],
            'md': {'metadata': eventData['el']},
        };

        // Customer IO data
        var customerIOData = {
            'en': eventData['en'],
            'md': {'metadata': eventData['el']},
        };

        // Mixpanel data
        var mixpanelEventData = {
            'en': eventData['en'],
            'md': {'metadata': eventData['el']},
        };

    // Detailed mode
    } else {
        // Google analytics data
        var googleEventData = {
            'ec': eventData['ec'],
            'ea': eventData['ea'],
            'el': eventData['el'],
            'ev': eventData['ev'],
        };

        // Intercom IO data
        var intercomIOEventData = {
            'en': eventData['en'],
            'md': eventData['md'],
        };

        var customerIOData = {
            'en': eventData['en'],
            'md': eventData['md'],
        };

        // Mixpanel data
        var mixpanelEventData = {
            'en': eventData['en'],
            'md': eventData['md'],
        };
    };

    // Send events
    // Google Analytics
    ga('send', 'event', 
        googleEventData['ec'],
        googleEventData['ea'],
        googleEventData['el'],
        googleEventData['ev']
    );

    // Intercom IO
    Intercom('trackEvent', 
        intercomIOEventData['en'],
        intercomIOEventData['md']
    );

    // Customer IO
    _cio.track(
        customerIOData['en'], 
        customerIOData['md']
    );

    // Mixpanel
    mixpanel.track(
        mixpanelEventData['en'],
        mixpanelEventData['md']
    );

    // Return
    return true;
}
</script>
<!-- /Global tracking function -->
