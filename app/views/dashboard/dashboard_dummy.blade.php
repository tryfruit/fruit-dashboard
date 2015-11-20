@extends('meta.base-user')

@section('pageTitle')
  Dashboard
@stop

@section('pageStylesheet')
@stop

@section('pageContent')
  
  <div class="menu">
    <div class="menu-group">

      {{-- FOR EACH DASHBOARD || IF ACTIVE --> ADD CLASS ACTIVE --}}
      <a href="#" class="menu-item">
        Menu text
      </a> <!-- /.menu-item -->
      {{-- ENDFOREACH --}}
        
        <a href="#" class="menu-item menu-subitem">
          submenu text  
        </a> <!-- /.menu-subitem -->
        <a href="#" class="menu-item menu-subitem">
          another one with a rather long generated name 
        </a> <!-- /.menu-subitem -->

      {{-- FOR MOCKUP --> DELETE --}}
      <a href="#" class="menu-item">
        Middle one
      </a> <!-- /.menu-item -->
      <a href="#" class="menu-item active">
        Active one
      </a> <!-- /.menu-item -->
      <a href="#" class="menu-item">
        Other menu text
      </a> <!-- /.menu-item -->
      {{-- ENDFOR MOCKUP --}}

    </div> <!-- /.menu-group -->
  </div> <!-- /.menu -->
  
  <div id="gridster-{{ $dashboard['id'] }}" class="gridster grid-base fill-height not-visible" data-dashboard-id="{{ $dashboard['id'] }}">

    {{-- Generate all the widgdets --}}
    <div class="gridster-container">

      @foreach ($dashboard['widgets'] as $widget)

        @include('widget.widget-general-layout', ['widget' => $widget['templateData']])

      @endforeach

    </div> <!-- /.gridster-container -->

  </div> <!-- /.gridster -->


@if (GlobalTracker::isTrackingEnabled() and Input::get('tour'))
  @include('dashboard.dashboard-google-converison-scripts')
@endif


@stop

@section('pageScripts')
  <!-- FDJSlibs merged -->
  {{ Minify::javascriptDir('/lib/general') }}
  {{ Minify::javascriptDir('/lib/layouts') }}
  {{ Minify::javascriptDir('/lib/widgets') }}
  <!-- FDJSlibs merged -->
  
  <!-- Gridster scripts -->
  @include('dashboard.dashboard-gridster-scripts')
  <!-- /Gridster scripts -->

  <!-- Hopscotch scripts -->
  @include('dashboard.dashboard-hopscotch-scripts')
  <!-- /Hopscotch scripts -->

  @if (GlobalTracker::isTrackingEnabled() and Input::get('tour'))
  <!-- Send acquisition event -->
  <script type="text/javascript">
    trackAll('lazy', {'en': 'Acquisition goal | Finished SignupWizard', 'el': '{{ Auth::user()->email }}', });
  </script>
  <!-- /Send acquisition event -->
  @endif

  <!-- Init FDGlobalChartOptions -->
  <script type="text/javascript">
      new FDGlobalChartOptions({data:{page: 'dashboard'}}).init();
  </script>
  <!-- /Init FDGlobalChartOptions -->

  <!-- Dashboard etc scripts -->
  <script type="text/javascript">

    function showShareModal(widgetId) {
     $('#share-widget-modal').modal('show');
     $('#share-widget-modal').on('shown.bs.modal', function (params) {
        $("#widget-id").val(widgetId);
        $('#email-addresses').focus()
      });
    }

    $(document).ready(function () {
      @if (Auth::user()->hasUnseenWidgetSharings())
        easyGrowl('info', 'You have unseen widget sharing notifications. You can check them out <a href="{{route('widget.add')}}" class="btn btn-xs btn-primary">here</a>.', 5000)
      @endif
      // Share widget submit.
      $('#share-widget-form').submit(function(event) {
        event.preventDefault();
        var emailAddresses = $('#email-addresses').val();
        var widgetId = $('#widget-id').val();

        if (emailAddresses.length > 0 && widgetId > 0) {
          $.ajax({
            type: "post",
            data: {'email_addresses': emailAddresses},
            url: "{{ route('widget.share', 'widget_id') }}".replace("widget_id", widgetId),
           }).done(function () {
            /* Ajax done. Widget shared. Resetting values. */
            $('#email-addresses-group').removeClass('has-error');
            $("#share-widget-modal").modal('hide');

            /* Resetting values */
            $('#email-addresses').val('');
            $('#widget-id').val(0);

            easyGrowl('success', "You successfully shared the widget.", 3000);
           });
          return
        } else {
          $('#email-addresses-group').addClass('has-error');
        }

      });
    });
  </script>
  <!-- /Dashboard etc scripts -->
@append

