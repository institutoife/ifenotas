@extends('layouts.notes-simulator')

@section('simulator')
    @include('partials.grade-simulator', ['creatorInHeader' => true, 'autoFocus' => false])
@endsection
