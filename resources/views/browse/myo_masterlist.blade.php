@extends('layouts.app')

@section('title') MYO List Masterlist @endsection

@section('sidebar')
    @include('browse._sidebar')
@endsection

@section('content')
{!! breadcrumbs(['MYO List Masterlist' => 'myos']) !!}
<h1>MYO List Masterlist</h1>

@include('browse._masterlist_content', ['characters' => $slots])

@endsection

@section('scripts')
@include('browse._masterlist_js')
@endsection