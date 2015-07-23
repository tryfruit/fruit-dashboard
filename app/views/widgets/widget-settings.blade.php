@extends('meta.base-user')

  @section('pageTitle')
    Dashboard
  @stop

  @section('pageStylesheet')
  @stop

  @section('pageContent')

  <div align="center">
  {{ Form::open(array('route' => array('widget.edit-settings', $widget->id))) }}
  @foreach ($widget->getSettingsFields() as $field=>$meta)
    {{ $meta['name'] }}:
    @if ($meta['type'] == "SCHOICE")
      {{ Form::select($field, $widget->$field()) }}
    @else
      {{ Form::text($field, $widget->getSettings()[$field])}}
    @endif
    <br>
  @endforeach
  {{ Form::submit('Submit!') }}
  {{ Form::close() }}
  </div>
  @stop
