@extends('layouts.app')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Setup Two-Factor Authentication
        </h2>

        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.)
            </p>

            <div class="flex justify-center mb-4">
                {!! $qrCode !!}
            </div>

            <p class="text-sm text-gray-500 text-center mb-4">
                Can't scan the code? Enter this key manually:
            </p>
            <div class="bg-gray-100 p-3 rounded text-center font-mono text-sm break-all">
                {{ $secret }}
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('two-factor.enable') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    Enter the 6-digit code from your app
                </label>
                <input type="text" name="code" id="code" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="000000"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    autocomplete="one-time-code">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Enable Two-Factor Authentication
            </button>
        </form>
    </div>
</div>
@endsection
