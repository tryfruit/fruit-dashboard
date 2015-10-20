<div class="text-white drop-shadow text-center">
  Your trial has ended. <br> Please subscribe to use the software.
  <a href="{{ route('widget.singlestat', $widget['id']) }}">
    <img src="/img/demonstration/graph_transparent.png" class="locked center-block img-responsive">
  </a>
  <p>
    <a href="{{ route('payment.plans') }}" class="btn btn-primary btn-xs margin-top-sm"> see plans &amp; pricing</a>
  </p>
</div>

@section('widgetScripts')

  <script type="text/javascript">
  $(function() {
    $('.locked').each(function(index) {
      var containerHeight = $(this).closest('li').height();
      var containerWidth = $(this).closest('li').width();
      if (containerHeight > containerWidth) {
        $(this).width(containerWidth-2*30);
      } else {
        $(this).height(containerHeight-2*35);
      };
    });
  });

  </script>

@append