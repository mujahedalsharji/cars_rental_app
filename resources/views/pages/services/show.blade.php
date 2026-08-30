<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
</div>
@extends('layouts.app')

@section('title', $servicePage['meta_title'])
@section('meta_description', $servicePage['meta_description'])

@push('structured_data')
    <x-structured-data :data="app(\App\Support\StructuredData::class)->service($servicePage)" />
    <x-structured-data :data="app(\App\Support\StructuredData::class)->serviceBreadcrumbs($servicePage)" />
@endpush

@section('content')
    <x-service-detail :service="$servicePage" :whatsapp-number="$whatsappNumber" />
@endsection
