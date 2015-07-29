<h2 class="text-white text-center">
  MRR:<br>
  <span>
    @if ($widget->state == 'missing_data')
      Data not present yet!
    @else
      @if ($widget->getSettings()['widget_type'] == 'chart')
        @foreach ($widget->getHistogram() as $histogramEntry)
          ${{$histogramEntry}},
        @endforeach
      @else
        ${{$widget->getLatestData()}}
      @endif
    @endif
  </span>
</h2>

