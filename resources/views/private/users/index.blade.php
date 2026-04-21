@extends('layouts.app')

@section('title', $pageTitle ?? 'Users')

@section('content')
<div class="content-area">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <livewire:user-table :routeRoleFilter="$roleFilter ?? null" :pageTitle="$pageTitle ?? 'User Management'" />
</div>
@endsection
