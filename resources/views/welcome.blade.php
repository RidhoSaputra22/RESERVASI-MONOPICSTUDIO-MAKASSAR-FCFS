@extends('layouts.app')

@section('content')
{{-- @include('layouts.navbar') --}}

{{-- Content --}}
@include('components.home.banner')
@include('components.home.tagline-1')
@include('components.home.tagline-2')
@include('components.home.about-us')
@include('components.home.services')
@include('components.home.faq')


{{-- End Content --}}

@include('layouts.footter')
@endsection
