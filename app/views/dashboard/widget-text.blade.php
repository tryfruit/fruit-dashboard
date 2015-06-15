<li data-id='{{ $widget_data["widget_id"] }}' class="dashboard-widget well" data-row="{{ $widget_data['position']['row'] }}" data-col="{{ $widget_data['position']['col'] }}" data-sizex="3" data-sizey="4">
	<!--a class='link-button' href='' data-toggle="modal" data-target='#widget-settings-{{ $id }}'><span class="gs-option-widgets"></span></a-->
	<a href="{{ URL::route('connect.deletewidget', $id) }}"><span class="gs-close-widgets"></span></a>

	<div class='textShadow widget-text'>{{ $text }}</span>

</li>


@section('pageModals')
	<!-- text settings -->
	
	@include('settings.widget-settings')

	<!-- /text settings -->
@append
