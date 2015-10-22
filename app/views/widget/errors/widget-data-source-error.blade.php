<div class="widget-inner fill" id="widget-loading-{{ $widget['id'] }}">
  <div class="widget-heading larger-text">
    {{ Utilities::underscoreToCamelCase($widget['descriptor']->type, TRUE) }}
  </div> <!-- /.widget-heading -->
  <p class="lead text-center">
    Something went wrong during the data collection on this widget, please check the availability of the data source.
  </p>
</div> <!-- /.widget-inner -->