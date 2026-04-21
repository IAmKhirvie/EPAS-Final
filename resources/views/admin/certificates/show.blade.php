@extends('layouts.app')

@section('title', 'View Certificate')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificate Details</h5>
                    <div>
                        <a href="{{ route('admin.certificates.edit', $certificate) }}" class="btn btn-sm btn-light me-2">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3">{{ $certificate->title }}</h4>
                            <p class="text-muted">{{ $certificate->description }}</p>

                            <hr>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <strong class="text-muted d-block">Certificate Number</strong>
                                    <span class="fs-5 font-monospace">{{ $certificate->certificate_number }}</span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <strong class="text-muted d-block">Status</strong>
                                    @php
                                        $statusColors = [
                                            'pending_instructor' => 'warning',
                                            'pending_admin' => 'info',
                                            'issued' => 'success',
                                            'revoked' => 'danger',
                                            'rejected' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$certificate->status] ?? 'secondary' }} fs-6">
                                        {{ ucfirst(str_replace('_', ' ', $certificate->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <strong class="text-muted d-block">Recipient</strong>
                                    <span>{{ $certificate->user->full_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $certificate->user->email ?? '' }}</small>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <strong class="text-muted d-block">Module</strong>
                                    <span>{{ $certificate->module->module_title ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $certificate->course->course_name ?? '' }}</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <strong class="text-muted d-block">Issue Date</strong>
                                    <span>{{ $certificate->issue_date?->format('F d, Y') ?? 'Not issued yet' }}</span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <strong class="text-muted d-block">Template</strong>
                                    <span>{{ ucfirst($certificate->template_used ?? 'default') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    @if($certificate->status === 'issued' && $certificate->pdf_path)
                                        <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                                        <h6>Certificate PDF</h6>
                                        <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    @else
                                        <i class="fas fa-certificate fa-5x text-muted mb-3"></i>
                                        <h6 class="text-muted">PDF not generated</h6>
                                    @endif
                                </div>
                            </div>

                            @if($certificate->status === 'issued')
                                <div class="mt-3">
                                    <form action="{{ route('admin.certificates.resend-email', $certificate) }}" method="POST" class="d-grid">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info">
                                            <i class="fas fa-envelope me-1"></i> Send/Resend Email
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-2">
                                    <form action="{{ route('admin.certificates.regenerate-pdf', $certificate) }}" method="POST" class="d-grid">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-sync me-1"></i> Regenerate PDF
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approval History --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Approval History</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                <strong>Requested</strong>
                            </div>
                            <span class="text-muted">{{ $certificate->requested_at?->format('M d, Y H:i') ?? $certificate->created_at->format('M d, Y H:i') }}</span>
                        </li>
                        @if($certificate->instructor_approved_at)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Instructor Approved</strong>
                                    <small class="text-muted ms-2">by {{ $certificate->instructorApprovedBy->full_name ?? 'N/A' }}</small>
                                </div>
                                <span class="text-muted">{{ $certificate->instructor_approved_at->format('M d, Y H:i') }}</span>
                            </li>
                        @endif
                        @if($certificate->admin_approved_at)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-double text-success me-2"></i>
                                    <strong>Admin Approved & Issued</strong>
                                    <small class="text-muted ms-2">by {{ $certificate->adminApprovedBy->full_name ?? 'N/A' }}</small>
                                </div>
                                <span class="text-muted">{{ $certificate->admin_approved_at->format('M d, Y H:i') }}</span>
                            </li>
                        @endif
                        @if($certificate->metadata['email_sent'] ?? false)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-envelope text-info me-2"></i>
                                    <strong>Email Sent</strong>
                                </div>
                                <span class="text-muted">{{ $certificate->metadata['email_sent_at'] ?? 'N/A' }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Metadata --}}
            @if($certificate->metadata && count($certificate->metadata) > 0)
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-database me-2"></i>Metadata</h6>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 small"><code>{{ json_encode($certificate->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
