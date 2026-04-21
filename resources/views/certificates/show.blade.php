@extends('layouts.app')

@section('title', 'Certificate Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('certificates.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Certificates
            </a>
            <h1 class="h3 mb-0">Certificate Details</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-certificate me-2"></i>
                        {{ $certificate->course->name ?? 'Certificate' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Certificate Number</label>
                            <p class="fw-bold">{{ $certificate->certificate_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $certificate->status === 'issued' ? 'success' : ($certificate->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($certificate->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Recipient</label>
                            <p class="fw-bold">{{ $certificate->user->full_name ?? $certificate->user->first_name . ' ' . $certificate->user->last_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Course</label>
                            <p>{{ $certificate->course->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Issue Date</label>
                            <p>{{ $certificate->issued_at ? $certificate->issued_at->format('F d, Y') : 'Pending' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Expiry Date</label>
                            <p>{{ $certificate->expires_at ? $certificate->expires_at->format('F d, Y') : 'No Expiry' }}</p>
                        </div>
                    </div>

                    @if($certificate->metadata)
                        <div class="mb-4">
                            <label class="form-label text-muted small">Additional Information</label>
                            <div class="bg-light p-3 rounded">
                                @foreach($certificate->metadata as $key => $value)
                                    <p class="mb-1"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if($certificate->status === 'issued')
                    <div class="card-footer">
                        <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i> Download PDF
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Verification</h6>
                </div>
                <div class="card-body text-center">
                    @if($certificate->status === 'issued')
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-3x text-success"></i>
                        </div>
                        <p class="text-success fw-bold">Valid Certificate</p>
                        <p class="small text-muted">
                            This certificate can be verified using the certificate number.
                        </p>
                    @else
                        <div class="mb-3">
                            <i class="fas fa-clock fa-3x text-warning"></i>
                        </div>
                        <p class="text-warning fw-bold">{{ ucfirst($certificate->status) }}</p>
                        <p class="small text-muted">
                            This certificate is not yet issued.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
