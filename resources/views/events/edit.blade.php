@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Edit Event</h1>

    <form method="POST" action="{{ route('events.update', $event) }}" class="max-w-2xl rounded-lg border border-slate-200 bg-white p-6">
        @method('PUT')
        @include('events._form', ['cancelUrl' => route('events.show', $event)])
    </form>
@endsection
