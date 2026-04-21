@extends('layouts.app')

@section('title', 'Trash - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Trash'],
    ]" />

    <livewire:trash-table />
</div>
@endsection
