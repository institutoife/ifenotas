@extends('layouts.notes-simulator')

@push('simulator-head')
    <meta name="description" content="Calcula la nota que necesitas en el tercer trimestre de una materia con IFE Notas Ecuador.">
    <link rel="stylesheet" href="{{ asset('css/ife-ecuador-classic.css') }}?v=2">
    <script src="{{ asset('js/ife-grade-engine.js') }}?v=4" defer></script>
    <script src="{{ asset('js/ife-ecuador-engine.js') }}?v=5" defer></script>
    <script src="{{ asset('js/ife-ecuador-records.js') }}?v=1" defer></script>
    <script src="{{ asset('js/ife-ecuador-simulator.js') }}?v=6" defer></script>
@endpush

@section('simulator')
    @include('partials.grade-simulator-styles')
    @include($country['simulator'])
@endsection
