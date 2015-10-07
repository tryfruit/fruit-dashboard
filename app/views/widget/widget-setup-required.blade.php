<div class="widget-inner fill" id="widget-loading-{{ $widget->id }}">
  <div class="widget-heading">
    {{ucwords(str_replace("_", " ", $widget->descriptor->type))}}
  </div> <!-- /.widget-heading -->
  <p class="lead text-center">
    This widget is broken :(
  </p>
  <p class="text-center">You can try to reset it 
    <a href="{{ URL::route('widget.reset', $widget->id) }}">here</a>.
  </p>
</div> <!-- /.widget-inner -->