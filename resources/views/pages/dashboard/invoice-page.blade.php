@extends('layout.sidenav-layout')
@section('title', 'Invoices')
@section('content')
    @include('components.invoice.invoice-list')
    @include('components.invoice.invoice-delete')
    @include('components.invoice.invoice-details')
@endsection
