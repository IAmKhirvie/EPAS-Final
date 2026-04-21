@extends('layouts.app')

@section('title', 'Manage Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Two-Factor Authentication
        </h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                {{ session('info') }}
            </div>
        @endif

        @if($isEnabled)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            Two-factor authentication is enabled
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <a href="{{ route('two-factor.backup-codes') }}"
                    class="block w-full text-center bg-gray-100 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-200">
                    View Backup Codes
                </a>

                <form action="{{ route('two-factor.disable') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Enter your password to disable 2FA
                        </label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    </div>
                    <button type="submit"
                        class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700"
                        onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                        Disable Two-Factor Authentication
                    </button>
                </form>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">
                            Two-factor authentication is not enabled
                        </p>
                    </div>
                </div>
            </div>

            <p class="text-gray-600 mb-6">
                Add an extra layer of security to your account by enabling two-factor authentication.
            </p>

            <a href="{{ route('two-factor.setup') }}"
                class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                Enable Two-Factor Authentication
            </a>
        @endif
    </div>
</div>
@endsection
