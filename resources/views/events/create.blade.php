@extends('layouts.app')

@section('title', 'Buat Event')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Buat Event</h1>

    <form method="POST" action="{{ route('events.store') }}" class="max-w-2xl rounded-lg border border-slate-200 bg-white p-6">
        @include('events._form', ['cancelUrl' => route('events.index')])
    </form>
@endsection
