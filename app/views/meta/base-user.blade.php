@extends('meta.meta')

@section('body')

{{-- If not on the dashboard set the background --}}
<body class="body-background" @if(!Request::is('dashboard') and Auth::user()->background->is_enabled) style="background: url({{ Auth::user()->background->url }}) no-repeat center center fixed" @endif>

    @section('navbar')
      @include('meta.navbar')
    @show

    @section('pageContent')

    @show

    @section('footer')
      @include('meta.footer')
    @show
  </body>

@stop