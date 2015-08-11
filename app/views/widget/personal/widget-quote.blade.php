<div class="text-white text-center drop-shadow quote">
  <div class="margin-top-sm has-margin-vertical-sm">
    <p class="lead body" id="quote">
      {{ $widget->getData()['quote'] }}
    </p>
    <p class="source" id="author">
      {{ $widget->getData()['author'] }}
    </p>
  </div> <!-- /.container -->
</div>

@section('widgetScripts')

<script type="text/javascript">
  function updateWidget(data) {
    $("#quote").html(data['quote']);
    $("#author").html(data['author']);
  }

  $(document).ready(function() {
    @if((Carbon::now()->timestamp - $widget->data->updated_at->timestamp) / 60 > $widget->getSettings()['update_frequency'])
      refreshWidget({{ $widget->id }}, function (data) { updateWidget(data);});
    @endif

     $("#refresh-{{$widget->id}}").click(function () {
      refreshWidget({{ $widget->id }}, function (data) { updateWidget(data);});
     });
   });
 </script>

@append