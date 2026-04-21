@extends('layouts.app')

@section('title', 'Two-Factor Authentication Enabled')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">
                Two-Factor Authentication Enabled
            </h2>
            <p class="text-gray-600 mt-2">
                Your account is now protected with two-factor authentication.
            </p>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">
                Save Your Backup Codes
            </h3>
            <p class="text-sm text-yellow-700 mb-4">
                Store these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.
            </p>

            <div class="grid grid-cols-2 gap-2 bg-white p-4 rounded border">
                @foreach($backupCodes as $code)
                    <code class="text-sm font-mono text-gray-700">{{ $code }}</code>
                @endforeach
            </div>
        </div>

        <div class="flex space-x-4">
            <a href="{{ route('dashboard') }}"
                class="flex-1 text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                Go to Dashboard
            </a>
            <button onclick="window.print()"
                class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-200">
                Print Codes
            </button>
        </div>
    </div>
</div>
@endsection
