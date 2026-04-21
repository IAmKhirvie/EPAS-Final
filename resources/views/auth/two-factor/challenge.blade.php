@extends('layouts.auth-layout')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter the code from your authenticator app
            </p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('two-factor.verify') }}" method="POST">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">
                    Verification Code
                </label>
                <input id="code" name="code" type="text" required
                    class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Enter 6-digit code"
                    maxlength="8"
                    autocomplete="one-time-code"
                    autofocus>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Verify
                </button>
            </div>
        </form>

        <div class="text-center text-sm">
            <p class="text-gray-600">
                Lost access to your authenticator?
                <br>
                Use one of your backup codes instead.
            </p>
        </div>
    </div>
</div>
@endsection
