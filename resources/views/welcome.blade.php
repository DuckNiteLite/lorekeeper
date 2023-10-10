@extends('layouts.app')

@section('title') Home @endsection

@section('content')
    @if(Auth::check())
        @include('pages._dashboard')
    @else
        @include('pages._logged_out')
    @endif
    @section('sidebar')
    @include('pages._sidebar')
@endsection
>>>>>>> .merge_file_VjfMo9



