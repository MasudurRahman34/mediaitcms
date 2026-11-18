@extends('site.layouts.app')

@section('content')
    @foreach ($sections as $section)
        @includeIf('site.sections.'.$section['type'], ['section' => $section])
    @endforeach

    <x-site.cta-fallback />
@endsection
