@extends('layouts.app')

@section('title', 'Cannot Take Test')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-lock text-warning" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="mb-3">Cannot Take This Test</h4>

                    <p class="text-muted mb-4">{{ $reason }}</p>

                    @if(isset($details['attempts_used']))
                        <div class="alert alert-info">
                            You have used {{ $details['attempts_used'] }} of {{ $details['max_attempts'] }} attempts.
                        </div>
                    @endif

                    @if(isset($details['best_attempt']))
                        <div class="alert alert-success">
                            <strong>Your Best Score:</strong> {{ number_format($details['best_attempt']->percentage, 1) }}%
                            <br>
                            <small>Achieved on {{ $details['best_attempt']->completed_at->format('M d, Y') }}</small>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Module
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
