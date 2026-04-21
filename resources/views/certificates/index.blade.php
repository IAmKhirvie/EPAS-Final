@extends('layouts.app')

@section('title', 'My Certificates')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header" style="margin-bottom:0;">
                <div class="page-header-left">
                    <h1><i class="fas fa-certificate me-2"></i>My Certificates</h1>
                    <p>View and download your earned certificates</p>
                </div>
            </div>
        </div>
    </div>

    @if($certificates->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-certificate fa-4x text-muted"></i>
                </div>
                <h5>No Certificates Yet</h5>
                <p class="text-muted">Complete courses to earn certificates.</p>
                <a href="{{ route('courses.index') }}" class="btn btn-primary">
                    Browse Courses
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($certificates as $certificate)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="certificate-icon me-3">
                                    <i class="fas fa-award fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">{{ $certificate->course->name ?? 'Certificate' }}</h5>
                                    <small class="text-muted">{{ $certificate->certificate_number }}</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="badge bg-{{ $certificate->status === 'issued' ? 'success' : ($certificate->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($certificate->status) }}
                                </span>
                            </div>

                            @if($certificate->issued_at)
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    Issued: {{ $certificate->issued_at->format('M d, Y') }}
                                </p>
                            @endif

                            @if($certificate->expires_at)
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-clock me-1"></i>
                                    Expires: {{ $certificate->expires_at->format('M d, Y') }}
                                </p>
                            @endif
                        </div>

                        @if($certificate->status === 'issued')
                            <div class="card-footer bg-transparent">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-sm btn-primary flex-fill">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($certificates->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $certificates->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
